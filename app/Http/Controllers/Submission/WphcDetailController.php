<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Useraccess;
use App\Models\WphcDetail;
use App\Models\Submission\Wphc;

use App\Models\Holiday;
use Illuminate\Http\Request;
use DB;
use Auth;
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

    public function checkworkdateemployee()
    {
        try {
            // Ambil user_id dari user login
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    "status"  => "error",
                    "message" => "User belum login."
                ], 401);
            }

            // Ambil semua workdate milik user login
            $workdates = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->where('m.user_id', $userId)
                ->select(
                    'd.id',
                    DB::raw("CAST(d.startDate AS DATE) as workdate"),
                    'm.requestStatus',
                    'd.text'
                )
                ->get()
                ->map(function ($row) {
                    return [
                        'id'            => $row->id,
                        'workdate'      => Carbon::parse($row->workdate)->format('Y-m-d'),
                        'requestStatus' => $row->requestStatus,
                        'text'          => $row->text
                    ];
                });

            return response()->json([
                "status"    => "success",
                "user"      => $userId,
                "workdates" => $workdates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $requestData = $request->all();

            $date = Carbon::parse($requestData['startDate'])->timezone('Asia/Makassar');
            $startDate = $date->copy()->setTime(8, 0, 0);
            $endDate   = $date->copy()->setTime(17, 0, 0);
            $key       = $startDate->format('Y-m-d');

            // Ambil requestStatus dari relasi wphc_request
            $wphcRequest   = Wphc::findOrFail($requestData['req_id']);
            $employeeId    = $wphcRequest->employee_id;
            $requestStatus = $wphcRequest->requestStatus;

            // Validasi: hanya boleh create kalau status 0 atau 2
            if (!in_array($requestStatus, [0, 2])) {
                return response()->json([
                    "status"  => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }

            // Validasi: employee_id + startDate
            $existsSameDay = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->where('m.employee_id', $employeeId)
                ->whereDate('d.startDate', $key)
                ->exists();

            if ($existsSameDay) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal $key sudah diajukan oleh employee $employeeId"
                ], 422);
            }

            // Cooldown mundur: cek apakah ada tanggal lain dalam 7 hari ke belakang
            $existsBackward = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->where('m.employee_id', $employeeId)
                ->whereBetween('d.startDate', [
                    $startDate->copy()->subDays(7)->startOfDay(),   // 7 hari ke belakang
                    $startDate->copy()->subDay()->endOfDay()        // sampai sehari sebelum tanggal diajukan
                ])
                ->exists();

            if ($existsBackward) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tidak tersedia (cooldown mundur ±7 hari)."
                ], 422);
            }

            // Cooldown maju: cek apakah ada tanggal lain dalam 7 hari ke depan
            $existsForward = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->where('m.employee_id', $employeeId)
                ->whereBetween('d.startDate', [
                    $startDate->copy()->addDay()->startOfDay(),     // mulai besok
                    $startDate->copy()->addDays(7)->endOfDay()      // sampai 7 hari ke depan
                ])
                ->exists();

            if ($existsForward) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tidak tersedia (cooldown maju ±7 hari)."
                ], 422);
            }

            // Validasi backdate absolut → ganti dengan toleransi 7 hari
            $sevenDaysAgo = Carbon::now('Asia/Makassar')->subDays(7)->startOfDay();
            if ($startDate->lt($sevenDaysAgo)) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Tanggal tidak tersedia (backdate lebih dari 7 hari)."
                ], 422);
            }

            $holidayDates = Holiday::pluck('HolidayDate')
                ->map(fn($h) => Carbon::parse($h)->format('Y-m-d'))
                ->toArray();

            $isHoliday = in_array($key, $holidayDates);

            if (!$isHoliday) {
                if ($startDate->isWeekday()) {
                    return response()->json([
                        "status"  => "error",
                        "message" => "Tanggal tidak tersedia (weekday)."
                    ], 422);
                }

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
            // Ambil requestStatus dari relasi wphc_request
            $wphcRequest = Wphc::findOrFail($data->req_id);
            $requestStatus = $wphcRequest->requestStatus;

            // Validasi: hanya boleh delete kalau status 0 atau 2
            if (!in_array($requestStatus, [0, 2])) {
                return response()->json([
                    "status" => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }
            $data->delete();
            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}