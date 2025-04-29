<?php

namespace App\Http\Controllers\Submission;

use DB;
use COM;
use Log;
use App\Models\User;
use App\Models\Module;

use App\Models\Employee;
use App\Models\Attachment;
use App\Models\Useraccess;
use App\Mail\SubmissionMail;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\MemorandumHis;
use Illuminate\Support\Carbon;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\MemorandumReq;

class MemorandumController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new MemorandumReq();
        $this->modulename = 'MemorandumReq';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        try {
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employeeid = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;
            $historycontract = MemorandumHis::all();

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end 
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id 
                where l.ApprovalAction='1' 
                and l.req_id = request_memorandum.id and l.module_id = '".$module_id."' 
                and request_memorandum.requestStatus='1'
                order by a.sequence)"; 

            // Log::info("Subquery for isPendingOnMe:", [$subquery]);
            // \Log::info("Generated SQL for Main Query:", [$dataquery->toSql()]);
            // \Log::info("Bindings:", $dataquery->getBindings());

            $getAssignment = "(select top 1
            CASE WHEN user_id='".$user_id."' then 1 else 0 end
            from
            (select
            u.id as user_id,
            u.fullname as nama_users
            from tbl_assignment l
            left join employee.tbl_employee e on l.employee_id = e.id
            left join users u on e.LoginName = u.username
            where l.req_id = request_memorandum.id
            and l.module_id = '".$module_id."') as tab1
            where user_id = '".$user_id."')";

            $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
            $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;
            // employee.tbl_employee.deptheadName,
            // employee.tbl_employee.companycode,
            // request_memorandum.sector,
            $data = $dataquery
                ->selectRaw("request_memorandum.id,
                    request_memorandum.user_id,
                    request_memorandum.requestStatus,   
                    request_memorandum.approveddoc,
                    request_memorandum.employee_id,
                    request_memorandum.created_at,
                    request_memorandum.bu,              
                    employee.tbl_employee.FullName,
                    employee.tbl_employee.SAPID,
                    employee.tbl_employee.BirthOfDate, 
                    employee.tbl_level.Level,
                    employee.tbl_designation.DesignationName,
                    codes.code,
                    CASE WHEN request_memorandum.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_memorandum.code_id','codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->leftJoin('tbl_assignment', function($join) use ($module_id) {
                    $join->on('request_memorandum.id', '=', 'tbl_assignment.req_id')
                        ->where('tbl_assignment.module_id', '=', $module_id);
                })
                ->leftJoin('employee.tbl_employee as emp', 'tbl_assignment.employee_id', '=', 'emp.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $getAllview) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $getAllview) {
                            if ($isAdmin) {
                                $query->whereIn("request_memorandum.requestStatus", [1,3,4])
                                    ->where("request_memorandum.user_id", "!=", $user_id);
                            } else if($getAllview) {
                                $query->whereIn("request_memorandum.requestStatus", [3])
                                ->where("request_memorandum.user_id", "!=", $user_id);
                            } else {
                                $query->where("request_memorandum.user_id", "!=", $user_id)
                                ->whereIn("request_memorandum.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_memorandum.user_id", $user_id);
                })
                ->where(function ($query) use ($user_id,$getAssignment, $isAdmin, $getAllview, $subquery) {
                    if(!$isAdmin) {
                        if(!$getAllview) {
                            $query->whereRaw($getAssignment . " = 1")
                            ->orWhere("request_memorandum.user_id", $user_id)
                            ->orWhereRaw($subquery . " = 1");
                        }
                    }
                })               
                
                // 'employee.tbl_employee.companycode',
                // 'employee.tbl_location.Location',
                // 'request_memorandum.sector',
                ->groupBy('request_memorandum.id',
                    'request_memorandum.user_id',
                    'request_memorandum.requestStatus',                    
                    // 'request_memorandum.prStatus',
                    'request_memorandum.approveddoc',
                    'request_memorandum.created_at',
                    'request_memorandum.employee_id',
                    'request_memorandum.bu',
                    'codes.code',
                    'employee.tbl_employee.sys_id',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.SAPID',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                    'employee.tbl_employee.deptheadName',
                )
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_memorandum.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_memorandum.created_at desc")
                ->get();

            $groupedData = [];

            foreach ($data as $row) {
                if (!isset($groupedData[$row->id])) {
                    $groupedData[$row->id] = [
                        'id' => $row->id,   
                        'code' => $row->code,
                        'requestStatus' => $row->requestStatus,
                        // 'companycode' => $row->companycode,
                        // 'prStatus' => $row->prStatus,
                        'isMine' => $row->isMine,
                        'bu' => $row->bu,
                        // 'sector' => $row->sector,
                        'isPendingOnMe' => $row->isPendingOnMe,
                        'isAssignment' => $row->isAssignment,
                        'approveddoc' => $row->approveddoc,
                        'created_at' => $row->created_at,
                        'employee_id' => $row->employee_id,    
                        'sys_id' => $row->sys_id,    
                        // 'employee' => [],                   
                        'FullName' => $row->FullName,
                        'user' => $row->user,
                        // 'Location' => $row->Location,
                        // 'memorandum_his' => [],
                        'SAPID' => $row->SAPID,
                        'BirthOfDate' => $row->BirthOfDate,
                        'Level' => $row->Level,
                        'DesignationName' => $row->DesignationName,
                        'deptheadName' => $row->deptheadName,
                    ];
                }
            }
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => array_values($groupedData)
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            $requestData = $request->all();
            $requestData['user_id'] = $this->getAuth()->id;
            $newData = $this->model->create($requestData);
            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    
    public function show($id)
    {
        $user_id = $this->getAuth()->id;
        $module_id = $this->getModuleId($this->modulename);

        $subquery = "(select TOP 1 CASE WHEN a.user_id='" . $user_id . "'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id
            where l.ApprovalAction='1' and l.req_id = request_memorandum.id and l.module_id = '" . $module_id . "' and request_memorandum.requestStatus='1'
            order by a.sequence)";

        $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
        $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;

        try {
            // Ambil data request_memorandum beserta semua memorandumHistories
            $data = $this->model
                ->select(
                    'request_memorandum.*',
                    'employee.tbl_employee.FullName', 
                    'codes.code', 
                    'employee.tbl_employee.sys_id', 
                    'employee.tbl_employee.SAPID', 
                    'employee.tbl_employee.JoinDate', 
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_employee.contract_status',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_employee.JoinDate',
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                )
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->where('request_memorandum.id', $id)
                ->first();

            if (!$data) {
                return response()->json(["status" => "error", "message" => "Data tidak ditemukan"], 404);
            }

            // Jika code_id null, maka generate kode baru
            if ($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }

            // Tambahkan atribut ismine
            $data->ismine = ($data->employee_id == $user_id);

            // Tambahkan atribut ispendingonme
            $data->isPendingOnMe = $this->model
                ->selectRaw($subquery . " as isPendingOnMe")
                ->where('id', $id)
                ->first()
                ->isPendingOnMe;

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data // Data akan mencakup ismine dan isPendingOnMe
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        // Mulai transaksi database
        DB::beginTransaction();
        
        try {
            $data = $this->model->findOrFail($id);
            $reqStatus = $data->requestStatus;
            // Mengambil semua data dari request
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

            $data->update($requestData);

            //start save history perubahan
            // $fields = [
            //     'prStatus' => ($request->prStatus == 1) ? 'Done' : 'Waiting',
            // ];

            // foreach ($fields as $key => $value) {
            //     if ($value) {
            //         $this->approverAction($this->modulename, $id, $key, 1, $value, null, null);
            //     }
            // }
            //end save history perubahan

            // if(isset($request->prStatus) && $data->requestStatus == 3) {
            //     if($request->prStatus == 1) {

            //         $getSubmissionData = $this->model->findOrFail($id);

            //         $mailData = [
            //             "id" => 30, // final approved
            //             "action_id" => 5, // update id
            //             "submission" => $getSubmissionData,
            //             "email" => $this->getUserByid($getSubmissionData->user_id)->email, // kirim kepada creator
            //             "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
            //             "message" => $this->mailMessage()['newActivity'],
            //             "remarks" => $request->ticketStatus
            //         ];
            //         Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
            //     }

            // }

            // Komit transaksi jika semuanya berjalan lancar
            DB::commit();

            // Mengembalikan data dalam bentuk JSON dengan memberikan status, pesan dan data
            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            // Cari module berdasarkan nama modul
            $module = $this->module->select('id', 'module')->where('module', $this->modulename)->first();
            $user_id = $this->getAuth()->id;

            // Jika module ditemukan, lakukan delete secara atomik
            if ($module) {
                DB::transaction(function () use ($id, $module, $user_id) {
                    // Hapus data pada tabel ApproverListReq
                    ApproverListReq::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    ApproverListHistory::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();

                    // Hapus data pada tabel utama
                    
                    $data = $this->model->where('id',$id)->where('requestStatus',0)->where('user_id',$user_id)->first();
                    if ($data) {
                        $data->delete();
                    } else {
                        throw new \Exception($this->getMessage()['errordestroysubmission']);
                    }
                    
                });

                return  response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

            } else {
                return  response()->json(["status" => "error", "message" => $this->getMessage()['modulenotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function genPdfmemorandumReq(Request $request, $id) {
        $data =  $this->model->select('request_memorandum.*','users.username')
                    ->leftJoin('users','request_memorandum.user_id','users.id')
                    ->where('request_memorandum.id',$id)
                    ->with(['code','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();

        $subimissionDate = $originatorApproval->created_at; // time originator submitted submission

        $dataDetails = DB::table('request_memorandum')->select('*')->where('id',$id)->get(); // data detail
        $emp = Employee::select('*')->with(['location','company'])->where('LoginName',$data->username)->first(); // data employee
        // $dataAppr = DB::table('memoreqApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/memorandum/memo.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;

            // Start Form Data
                $Worksheet->Range("E7")->Value = $subimissionDate->format('Y-m-d');
                $Worksheet->Range("E9")->Value = $emp->location->Location;
                $Worksheet->Range("E11")->Value = $emp->FullName;
                $Worksheet->Range("E13")->Value = $emp->CostCenter;
                $Worksheet->Range("F15")->Value = $data->Location;

                $Worksheet->Range("S2")->Value = $emp->company->CompanyName;
                $Worksheet->Range("S7")->Value = 'DOC NO : '.$data->code->code;

            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }

            // signature originator
            $Worksheet->Range("H36")->Value = $emp->FullName;
            $Worksheet->Range("H37")->Value = $subimissionDate->format('Y-m-d');;
            addPictureToWorksheet($Worksheet, $picpath, 33, 8, 35, $excel);
            
            // signature approver
            // foreach ($dataAppr as $appr) {
            //     if($appr->sequence == 2) {
            //         if($appr->approvalAction == 3) {
            //             $Worksheet->Range("N36")->Value = $appr->apprname;
            //             $Worksheet->Range("N37")->Value = $appr->approvalDate;
            //             addPictureToWorksheet($Worksheet, $picpath, 33, 14, 35, $excel);
            //         }
            //     }
            // }

            $xlShiftDown=-4121;
				$no = 1;
				for ($a=17;$a<17+count($dataDetails);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("D".$a)->Value = $dataDetails[$a-17]->memorandumCode;
					$Worksheet->Range("E".$a)->Value = $dataDetails[$a-17]->description;
					$Worksheet->Range("N".$a)->Value = $dataDetails[$a-17]->part_number;
					$Worksheet->Range("O".$a)->Value = $dataDetails[$a-17]->pg;
					$Worksheet->Range("P".$a)->Value = $dataDetails[$a-17]->uom;
					$Worksheet->Range("Q".$a)->Value = $dataDetails[$a-17]->order;
					$Worksheet->Range("S".$a)->Value = $dataDetails[$a-17]->unit_price;
					$Worksheet->Range("T".$a)->Value = $dataDetails[$a-17]->amount;
					$Worksheet->Range("U".$a)->Value = $dataDetails[$a-17]->remarks;
				}
       
            $Worksheet->Columns("E")->AutoFit();

            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/ecatalog/pdf/' . $fileName);
			$path = $filePath;
			if (file_exists($path)) {
				unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
            $pathfilename = 'public/template/ecatalog/pdf/' . $fileName;

            $updateData = $this->model->find($data->id);
			$updateData->approveddoc = str_replace("\\", "/", $pathfilename);
			$updateData->save();

            $this->processcopy($pathfilename);

			return $pathfilename;

		} catch (\Exception $e) {
            // Log error
            $ip = $request->ip();
            $url = $request->url();
            $action = 'gen-pdf-memo';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }

}