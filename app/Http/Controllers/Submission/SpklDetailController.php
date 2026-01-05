<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\CategoryForm;
use App\Models\Module;
use App\Models\User;
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
            $master = DB::table('request_spkl')
            ->select('id', 'work_date', 'requestStatus')
            ->where('id', $request->req_id)
            ->first();

            if (!$master || !$master->work_date) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Master request not found or work_date is missing."
                ]);
            }

            if (!empty($request->ActualStartWork)) { 
                $requestData['ActualStartWork'] = Carbon::parse($request->ActualStartWork) 
                ->format('Y-m-d H:i:s'); 
            } if (!empty($request->ActualEndWork)) { 
                $requestData['ActualEndWork'] = Carbon::parse($request->ActualEndWork) 
                ->format('Y-m-d H:i:s'); }

            $masterDate = \Carbon\Carbon::parse($master->work_date)->toDateString(); // Y-m-d

            $exists = DB::table('request_spkl_detail as d')
                ->join('request_spkl as m', 'm.id', '=', 'd.req_id')
                ->where('d.employee_id', $request->employee_id)
                ->whereDate('m.work_date', $masterDate)
                ->where('m.id', '!=', $request->req_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    "status"  => "error",
                    "message" => "Duplicate SPKL for the same employee and work date on another request is not allowed."
                ]);
            }

            $requestData = $request->all();
            $requestData['user_id']   = $this->getAuth()->id;
            $EstimateOvertimeHours = $request->EstimateOvertimeHours ?? 0;
            if ($EstimateOvertimeHours > 2) {
                $requestData['moreThanTwoHours'] = 1;
            } else {
                $requestData['moreThanTwoHours'] = 0;
            }

            $catappr = CategoryForm::get();

            if ($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $request->req_id);
            }

            $this->model->create($requestData);
            if ($requestData['moreThanTwoHours'] == 1) {
                $categoryId = $catappr->firstWhere('nameCategory', 'moreThanTwoHours')->id ?? null;
            } else {
                $categoryId = $catappr->firstWhere('nameCategory', 'lessThanTwoHours')->id ?? null;
            }
            if ($categoryId) {
                DB::table('request_spkl')
                    ->where('id', $request->req_id)
                    ->update(['category_id' => $categoryId]);
            }

            DB::commit();

            return response()->json([
                "status"  => "success",
                "message" => $this->getMessage()['store']
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); 
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

            if (!empty($request->ActualStartWork)) {
                $requestData['ActualStartWork'] = Carbon::parse($request->ActualStartWork)
                    ->setTimezone('Asia/Makassar')  
                    ->format('Y-m-d H:i:s');
            }
            if (!empty($request->ActualEndWork)) {
                $requestData['ActualEndWork'] = Carbon::parse($request->ActualEndWork)
                    ->setTimezone('Asia/Makassar')   
                    ->format('Y-m-d H:i:s');
            }

            $EstimateOvertimeHours = isset($requestData['EstimateOvertimeHours'])
                ? floatval(trim((string) $requestData['EstimateOvertimeHours']))
                : floatval($data->EstimateOvertimeHours ?? 0);

            $requestData['moreThanTwoHours'] = ($EstimateOvertimeHours > 2) ? 1 : 0;

            $requestStatus = optional($data->Spkl)->requestStatus;
            if (!empty($data->approveddoc) || !in_array($requestStatus, [0, 2])) {
                return response()->json([
                    "status" => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }

            $data->update($requestData);

            $updated = $this->model->findOrFail($id);

            $EOHs = floatval($updated->EstimateOvertimeHours ?? 0);
            $AOHs = floatval($updated->ActualOvertimeHours ?? 0);
            $isExceedPlan = ($AOHs > $EOHs) ? 1 : 0;
            $moreThanTwoHours = ($EOHs > 2) ? 1 : 0;

            DB::table('request_spkl_detail')
                ->where('id', $updated->id)
                ->update([
                    'isExceedPlan'     => $isExceedPlan,
                    'moreThanTwoHours' => $moreThanTwoHours,
                ]);

            $master = DB::table('request_spkl')
                ->where('id', $updated->req_id)
                ->first();

            $categories = CategoryForm::get()->keyBy('nameCategory');

            $category_id = null;

            if ((int)$master->tms === 33) {
            $category_id = ($moreThanTwoHours === 1)
                ? $categories['moreThanTwoHours']->id
                : $categories['lessThanTwoHours']->id;
            } elseif ((int)$master->tms === 34) {
                $category_id = ($isExceedPlan === 1)
                    ? $categories['isExceedYes']->id
                    : $categories['isExceedNo']->id;
            }
             if ($category_id) {
                DB::table('request_spkl')
                    ->where('id', $updated->req_id)
                    ->update(['category_id' => $category_id]);
            }
            // if (!empty($requestData['DeptHead'])) { $this->createApprDeptHead($requestData['DeptHead'], $this->modulename, $id); }

            DB::commit();

            if (!empty($requestData['DeptHead'])) {
            $this->createApprDeptHead($requestData['DeptHead'], $this->modulename, $id);
            }
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