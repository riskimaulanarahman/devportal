<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Useraccess;
use App\Models\SpklDetail;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class SpklDetailController extends Controller
{
    
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new SpklDetail();
        $this->modulename = 'Spkl';
        $this->codename = 'Spkl';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        $data = SpklDetail::get();

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
            $requestData['user_id']   = $this->getAuth()->id;
                // Hitung flag moreThanTwoHours
            // $EstimateOvertimeHours = 0;
            $EstimateOvertimeHours = $request->EstimateOvertimeHours ?? 0;
            if ($EstimateOvertimeHours > 2) {
                $requestData['moreThanTwoHours'] = 1;
            } else {
                $requestData['moreThanTwoHours'] = 0;
            }

            if ($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $request->req_id);
            }

            $this->model->create($requestData);
            if ($requestData['moreThanTwoHours'] == 1) {
                DB::table('request_spkl')
                    ->where('id', $request->req_id)
                    ->update(['category_id' => 36]); // 36 = moreThanTwoHours
            }

            DB::commit();

            return response()->json([
                "status"  => "success",
                "message" => $this->getMessage()['store']
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Tambahkan rollback agar transaksi aman
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function show($req_id)
    {
        try {
            $data = $this->model
                ->select('request_spkl_detail.*')                
                ->where('request_spkl_detail.req_id', $req_id)
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

    public function getList($id, $modulename)
    {
        try {
           $data = SpklDetail::select(
                'request_spkl_detail.*',
                'emp.SAPID',
                'emp.designation_id',
                'desig.designationName'
            )
            ->leftJoin('employee.tbl_employee as emp', 'request_spkl_detail.employee_id', '=', 'emp.id')
            ->leftJoin('employee.tbl_designation as desig', 'emp.designation_id', '=', 'desig.id')
            ->where('request_spkl_detail.req_id', $id)
            ->get();

            
            return response()->json([
                "status" => "show", 
                "message" => $this->getMessage()['show'], 
                "data" => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $requestData['user_id'] = $this->getAuth()->id;

            // Ambil nilai dari request jika ada
            $EstimateOvertimeHours = isset($requestData['EstimateOvertimeHours'])
                ? floatval(trim((string) $requestData['EstimateOvertimeHours']))
                : floatval($data->EstimateOvertimeHours ?? 0);

            // Hitung flag moreThanTwoHours
            $requestData['moreThanTwoHours'] = ($EstimateOvertimeHours > 2) ? 1 : 0;

            // Validasi status SPKL
            $requestStatus = optional($data->Spkl)->requestStatus;
            if (!empty($data->approveddoc) || !in_array($requestStatus, [0, 2])) {
                return response()->json([
                    "status" => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }

            // Update detail
            $data->update($requestData);

            // Ambil ulang data setelah update
            $updated = $this->model->findOrFail($id);
            $EOHs = floatval($updated->EstimateOvertimeHours ?? 0);
            $AOHs = floatval($updated->ActualOvertimeHours ?? 0);
            $isExceedPlan = ($AOHs != $EOHs) ? 1 : 0;

            DB::table('request_spkl_detail')
                ->where('id', $updated->id)
                ->update(['isExceedPlan' => $isExceedPlan]);

            $category_id = null;
            if ($updated->tms == 33) {
                $category_id = ($updated->EstimateOvertimeHours > 2) ? 36 : 35;
            } 
            if ($isExceedPlan == 1) {
                $category_id = 35;
            } else {
                $category_id = 0;
            }

            DB::table('request_spkl')
                ->where('id', $updated->req_id)
                ->update(['category_id' => $category_id]);


            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $updated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    // public function update(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $requestData = $request->all();
    //         $data = $this->model->findOrFail($id);
    //         $requestData['user_id'] = $this->getAuth()->id;

    //         $EstimateOvertimeHours = $request->EstimateOvertimeHours ?? 0;
    //         // $EOHs = floatval($data->EstimateOvertimeHours ?? 0);
    //         // $AOHs = floatval($data->ActualOvertimeHours ?? 0);
    //         // $tms = intval($data->tms ?? 0); // 33 = SPKL, 34 = TMS

    //         // $isExceedPlan = ($AOHs > $EOHs) ? 1 : 0;
    //         // DB::table('request_spkl_detail')
    //         //     ->where('id', $data->id)
    //         //     ->update(['isExceedPlan' => $isExceedPlan]);

    //         if (isset($requestData['EstimateOvertimeHours'])) {
    //             $raw = trim((string) $requestData['EstimateOvertimeHours']);
    //             if (is_numeric($raw)) {
    //                 $EstimateOvertimeHours = floatval($raw);
    //             }
    //         }

    //         // Hitung flag moreThanTwoHours
    //         if ($EstimateOvertimeHours > 2) {
    //             $requestData['moreThanTwoHours'] = 1;
    //         } else {
    //             $requestData['moreThanTwoHours'] = 0;
    //         }
    //             $requestStatus = optional($data->Spkl)->requestStatus;

    //             if (empty($data->approveddoc) && in_array($requestStatus, [0, 2])) {
    //                 $data->update($requestData);
    //             } else {
    //                 return response()->json([
    //                     "status" => "error",
    //                     "message" => $this->getMessage()['nothaveaccess']
    //                 ]);
    //             }
    //         $EOHs = floatval($data->EstimateOvertimeHours ?? 0);
    //         $AOHs = floatval($data->ActualOvertimeHours ?? 0);
    //         $tms = intval($data->tms ?? 0); // 33 = SPKL, 34 = TMS

    //         $isExceedPlan = ($AOHs > $EOHs) ? 1 : 0;
    //         DB::table('request_spkl_detail')
    //             ->where('id', $data->id)
    //             ->update(['isExceedPlan' => $isExceedPlan]);

    //         // DB::table('request_spkl')
    //         //     ->where('id', $data->req_id)
    //         //     ->update([
    //         //         'category_id' => ($requestData['moreThanTwoHours'] == 1) ? 36 : 35
    //         //     ]);

    //         $category_id = null;

    //         if ($tms === 33) { // SPKL
    //             $category_id = ($requestData['moreThanTwoHours'] == 1) ? 36 : 35;
    //         } elseif ($tms === 34 && $isExceedPlan == 1) { // TMS
    //             $category_id = 35;
    //         } else {
    //             $category_id = 0;
    //         }

    //         DB::table('request_spkl')
    //             ->where('id', $data->req_id)
    //             ->update(['category_id' => $category_id]);

    //         DB::commit();

    //         return response()->json([
    //             "status" => "success",
    //             "message" => $this->getMessage()['update'],
    //             "data" => $data
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             "status" => "error",
    //             "message" => $e->getMessage()
    //         ]);
    //     }
    // }


    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);

            // if(isset($data->approveddoc) || in_array($data->spkl->requestStatus, [1])) {
            //     return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            // }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}