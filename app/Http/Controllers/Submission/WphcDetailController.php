<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Useraccess;
use App\Models\WphcDetail;
use App\Models\Holiday;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class WphcDetailController extends Controller
{
    
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new WphcDetail();
        $this->modulename = 'Wphc';
        $this->codename = 'Wphc';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        $data = WphcDetail::get();

        return response()->json([
            'status' => "show",
            'message' => $this->getMessage()['show'],
            'data' => $data
        ])->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $requestData = $request->all();

            // Normalize to WITA (Asia/Makassar)
            $date = Carbon::parse($requestData['startDate'])->timezone('Asia/Makassar');

            // Jam kerja: 08:00–17:00 WITA
            $startDate = $date->copy()->setTime(8, 0, 0);
            $endDate   = $date->copy()->setTime(17, 0, 0);

            $key = $startDate->format('Y-m-d');

            // 0) Cegah duplikasi tanggal untuk req_id yang sama
            $existsSameDay = WphcDetail::where('req_id', $requestData['req_id'])
                ->whereDate('startDate', $key)
                ->exists();
            if ($existsSameDay) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tersebut sudah diajukan."
                ], 422);
            }
            // Tambahan: Cooldown ±7 hari
            $existsCooldown = WphcDetail::where('req_id', $requestData['req_id'])
                ->whereBetween('startDate', [
                    $startDate->copy()->subDays(7)->startOfDay(),
                    $startDate->copy()->addDays(7)->endOfDay()
                ])
                ->exists();

            if ($existsCooldown) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tidak tersedia (cooldown ±7 hari)."
                ], 422);
            }

            // 1) Backdate tidak boleh
            if ($startDate->lt(Carbon::now('Asia/Makassar')->startOfDay())) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tidak tersedia (backdate)."
                ], 422);
            }

            // 2) Holiday aktif
            $holidayDates = Holiday::pluck('HolidayDate')
                ->map(fn($h) => Carbon::parse($h)->format('Y-m-d'))
                ->toArray();

            $isHoliday = in_array($key, $holidayDates);

            if (!$isHoliday) {
                // 3) Weekday non-holiday disable
                // Catatan: Carbon::isWeekday() = Mon–Fri.
                if ($startDate->isWeekday()) {
                    return response()->json([
                        "status"  => "error",
                        "message" => "Tanggal tidak tersedia (weekday)."
                    ], 422);
                }

                // 4) Sunday berturut-turut tidak boleh (lintas bulan/tahun)
                if ($startDate->isSunday()) {
                    $prevSunday = $startDate->copy()->subWeek();
                    $prevKey    = $prevSunday->format('Y-m-d');

                    $hasPrevSunday = WphcDetail::where('req_id', $requestData['req_id'])
                        ->whereDate('startDate', $prevKey)
                        ->exists();

                    if ($hasPrevSunday) {
                        return response()->json([
                            "status"  => "error",
                            "message" => "Tanggal tidak tersedia (Sunday berturut-turut)."
                        ], 422);
                    }
                }
            }

            $requestData['user_id']   = $this->getAuth()->id;
            $requestData['startDate'] = $startDate;
            $requestData['endDate']   = $endDate;

            $this->model->create($requestData);

            DB::commit();

            return response()->json([
                "status"  => "success",
                "message" => $this->getMessage()['store'],
                "data"    => [
                    "startDate" => $startDate->toIso8601String(),
                    "endDate"   => $endDate->toIso8601String(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

public function getList($id, $modulename)
    {
        try {
            $data = WphcDetail::select('*')
                ->where('req_id', $id)
                ->get();

            // Opsional: pastikan format ISO agar DevExtreme konsisten timezone
            $data = $data->map(function ($row) {
                $row->startDate = Carbon::parse($row->startDate)->toIso8601String();
                $row->endDate   = Carbon::parse($row->endDate)->toIso8601String();
                return $row;
            });

            return response()->json([
                "status"  => "show",
                "message" => $this->getMessage()['show'],
                "data"    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }


    public function show($req_id)
    {
        try {
            $data = $this->model
                ->select('request_wphc_detail.*')                
                ->where('request_wphc_detail.req_id', $req_id)
                ->first();
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan untuk ID: ' . $req_id
                ]);
            }
            if ($data->code_id == null) {
            // Buat kode manual tanpa generateCode()
            $data->save();
            }

            return response()->json([
                'status' => 'show',
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $requestData['user_id'] = $this->getAuth()->id;
            $requestStatus = optional($data->Wphc)->requestStatus;

            if (empty($data->approveddoc) && in_array($requestStatus, [0, 2])) {
                if (!empty($requestData['startDate'])) {
                    $date = Carbon::parse($requestData['startDate'])->timezone('Asia/Makassar');
                    $startDate = $date->copy()->setTime(8, 0, 0);
                    $endDate   = $date->copy()->setTime(17, 0, 0);

                    // Format ke datetime SQL Server
                    $requestData['startDate'] = $startDate->format('Y-m-d H:i:s');
                    $requestData['endDate']   = $endDate->format('Y-m-d H:i:s');
                }
                $data->update($requestData);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $data
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }


    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);

            // if(isset($data->approveddoc) || in_array($data->Wphc->requestStatus, [1])) {
            //     return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            // }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}