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
        // dd($data);
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
                    "message" => "The date $key has already been filed in a different submission by the same employee."
                ]);
            }

            // Ambil daftar holiday (pastikan model Holiday -> $table = 'tbl_holiday')
            $holidayDates = Holiday::pluck('HolidayDate')
                ->map(fn($h) => Carbon::parse($h)->toDateString()) // "YYYY-MM-DD"
                ->toArray();

            $isHoliday = in_array($key, $holidayDates);

            // --- DEBUG SNAPSHOT (sementara) ---
            // return response()->json([
            //     'payload'      => $requestData,
            //     'key'          => $key,
            //     'employeeId'   => $employeeId,
            //     'holidayDates' => $holidayDates,
            //     'isHoliday'    => $isHoliday,
            // ], 200);

            // Jalur khusus holiday: quota 2/bulan, bypass cooldown & consecutive Sunday
            if ($isHoliday) {
                $month = $startDate->month;
                $year  = $startDate->year;

                // $holidayCountMonth = DB::table('request_wphc_detail as d')
                //     ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                //     ->where('m.employee_id', $employeeId)
                //     ->whereMonth('d.startDate', $month)
                //     ->whereYear('d.startDate', $year)
                //     ->whereIn(DB::raw('DATE(d.startDate)'), $holidayDates)
                //     ->count();

                $holidayCountMonth = DB::table('request_wphc_detail as d')
                    ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                    ->where('m.employee_id', $employeeId)
                    ->whereMonth('d.startDate', $month)
                    ->whereYear('d.startDate', $year)
                    ->whereIn(DB::raw('CAST(d.startDate AS DATE)'), $holidayDates) // ⬅️ ganti DATE() dengan CAST
                    ->count();

                // --- DEBUG QUOTA (sementara) ---
                // return response()->json([
                //     'month' => $month,
                //     'year'  => $year,
                //     'holidayCountMonth' => $holidayCountMonth,
                // ], 200);

                if ($holidayCountMonth >= 2) {
                    return response()->json([
                        "status"  => "error",
                        "message" => "You have reached maximum number of Holiday submissions (2) for " . $startDate->format('F Y')
                    ]);
                }

                // Simpan data langsung
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
            }

            // Non-holiday → validasi biasa
            if ($startDate->isWeekday()) {
                return response()->json([
                    "status"  => "error",
                    "message" => "The selected date is unavailable because it falls on a weekday."
                ], 422);
            }

            if ($startDate->isSunday()) {
                $prevSundayKey = $startDate->copy()->subWeek()->toDateString();

                // Consecutive Sunday check
                $hasPrevSunday = DB::table('request_wphc_detail as d')
                    ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                    ->leftJoin('tbl_holiday as h', DB::raw('CAST(h.HolidayDate AS DATE)'), '=', DB::raw('CAST(d.startDate AS DATE)'))
                    ->where('m.employee_id', $employeeId)
                    ->whereDate('d.startDate', $prevSundayKey)
                    ->whereNull('h.HolidayDate')
                    ->exists();


                // --- DEBUG PREV SUNDAY (sementara) ---
                // return response()->json([
                //     'prevSundayKey' => $prevSundayKey,
                //     'hasPrevSunday' => $hasPrevSunday,
                // ], 200);

                if ($hasPrevSunday) {
                    return response()->json([
                        "status"  => "error",
                        "message" => "The selected date is not available because it falls on consecutive Sundays."
                    ]);
                }
            }

            $existsBackward = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->leftJoin('tbl_holiday as h', DB::raw('CAST(h.HolidayDate AS DATE)'), '=', DB::raw('CAST(d.startDate AS DATE)'))
                ->where('m.employee_id', $employeeId)
                ->whereBetween('d.startDate', [
                    $startDate->copy()->subDays(7)->startOfDay(),
                    $startDate->copy()->subDay()->endOfDay()
                ])
                ->whereNull('h.HolidayDate')
                ->exists();


            // --- DEBUG BACKWARD (sementara) ---
            // return response()->json([
            //     'rangeBackward' => [
            //         $startDate->copy()->subDays(7)->startOfDay()->toDateTimeString(),
            //         $startDate->copy()->subDay()->endOfDay()->toDateTimeString()
            //     ],
            //     'existsBackward' => $existsBackward,
            // ], 200);

            if ($existsBackward) {
                return response()->json([
                    "status"  => "error",
                    "message" => "The selected date is not available due to a backward cooldown period of seven days by other submission with same employee."
                ]);
            }

            // Cooldown maju — exclude holiday
            $existsForward = DB::table('request_wphc_detail as d')
                ->join('request_wphc as m', 'd.req_id', '=', 'm.id')
                ->leftJoin('tbl_holiday as h', DB::raw('CAST(h.HolidayDate AS DATE)'), '=', DB::raw('CAST(d.startDate AS DATE)'))
                ->where('m.employee_id', $employeeId)
                ->whereBetween('d.startDate', [
                    $startDate->copy()->addDay()->startOfDay(),
                    $startDate->copy()->addDays(7)->endOfDay()
                ])
                ->whereNull('h.HolidayDate')
                ->exists();


            // --- DEBUG FORWARD (sementara) ---
            // return response()->json([
            //     'rangeForward' => [
            //         $startDate->copy()->addDay()->startOfDay()->toDateTimeString(),
            //         $startDate->copy()->addDays(7)->endOfDay()->toDateTimeString()
            //     ],
            //     'existsForward' => $existsForward,
            // ], 200);

            if ($existsForward) {
                return response()->json([
                    "status"  => "error",
                    "message" => "The selected date is not available due to a forward cooldown period of seven days."
                ]);
            }

            // Validasi backdate absolut
            $sevenDaysAgo = Carbon::now('Asia/Makassar')->subDays(7)->startOfDay();
            if ($startDate->lt($sevenDaysAgo)) {
                return response()->json([
                    "status"  => "error",
                    "message" => "The selected date is unavailable because it is a backdate beyond the seven‑day limit."
                ]);
            }

            // Simpan data untuk non-holiday
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
            \Log::error('WPHC store error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        return response()->json([
                "status"  => "error",
                "message" => $e->getMessage() // sementara tampilkan pesan asli
            ], 500);
        }
    }


    public function getList($id, $modulename)
    {
        try {
            $data = WphcDetail::select(
            'request_wphc_detail.*',
            'request_wphc.employee_id as master_employee_id',
            'employee.tbl_employee.fullname as employee_fullname',
            'employee.tbl_department.DepartmentName as department_name'
        )
        ->join('request_wphc', 'request_wphc.id', '=', 'request_wphc_detail.req_id')
        ->join('employee.tbl_employee', 'employee.tbl_employee.id', '=', 'request_wphc.employee_id')
        ->join('employee.tbl_department', 'employee.tbl_department.id', '=', 'employee.tbl_employee.department_id')
        ->where('request_wphc_detail.req_id', $id)
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