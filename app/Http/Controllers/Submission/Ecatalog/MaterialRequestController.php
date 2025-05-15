<?php

namespace App\Http\Controllers\Submission\Ecatalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionMail;

use App\Models\Submission\Ecatalog\MaterialReq;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\Employee;
use App\Models\User;
use App\Models\Useraccess;
use DB;
use COM;

class MaterialRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new MaterialReq();
        $this->modulename = 'MaterialReq';
        $this->codename = 'Material';
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

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.ApprovalAction='1' and l.req_id = request_material.id and l.module_id = '".$module_id."' and request_material.requestStatus='1'
            order by a.sequence)";

            $getAssignment = "(select top 1
            CASE WHEN user_id='".$user_id."' then 1 else 0 end
            from
            (select
            u.id as user_id,
            u.fullname as nama_users
            from tbl_assignment l
            left join employee.tbl_employee e on l.employee_id = e.id
            left join users u on e.LoginName = u.username
            where l.req_id = request_material.id 
            and l.module_id = '".$module_id."') as tab1
            where user_id = '".$user_id."')";

            $checkUserAccess = Useraccess::where('module_id',$module_id)->where('employee_id',$user_id)->first();
            $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;
            
            $data = $dataquery
                ->selectRaw("request_material.id,
                    request_material.user_id,
                    request_material.requestStatus,
                    request_material.bu,
                    request_material.prStatus,
                    request_material.approveddoc,
                    request_material.created_at,
                    codes.code,
                    CASE WHEN request_material.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe,
                    emp.FullName,
                    ".$getAssignment." as isAssignment
                ")
                ->leftJoin('codes','request_material.code_id','codes.id')
                ->leftJoin('tbl_assignment', function($join) use ($module_id) {
                    $join->on('request_material.id', '=', 'tbl_assignment.req_id')
                         ->where('tbl_assignment.module_id', '=', $module_id);
                })
                ->leftJoin('employee.tbl_employee as emp', 'tbl_assignment.employee_id', '=', 'emp.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $getAllview) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $getAllview) {
                            if ($isAdmin) {
                                $query->whereIn("request_material.requestStatus", [1,3,4])
                                    ->where("request_material.user_id", "!=", $user_id);
                            } else if($getAllview) {
                                $query->whereIn("request_material.requestStatus", [3])
                                ->where("request_material.user_id", "!=", $user_id);
                            } else {
                                $query->where("request_material.user_id", "!=", $user_id)
                                ->whereIn("request_material.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_material.user_id", $user_id);
                })
                ->where(function ($query) use ($user_id,$getAssignment, $isAdmin, $getAllview, $subquery) {
                    if(!$isAdmin) {
                        if(!$getAllview) {
                            $query->whereRaw($getAssignment . " = 1")
                            ->orWhere("request_material.user_id", $user_id)
                            ->orWhereRaw($subquery . " = 1");
                        }
                    }
                })
                ->groupBy('request_material.id',
                    'request_material.user_id',
                    'request_material.requestStatus',
                    'request_material.bu',
                    'request_material.prStatus',
                    'request_material.approveddoc',
                    'request_material.created_at',
                    'codes.code',
                    'emp.FullName'
                )
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_material.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_material.created_at desc")
                ->get();

                $groupedData = [];

                // dd($data);
                foreach ($data as $row) {
                    if (!isset($groupedData[$row->id])) {
                        $groupedData[$row->id] = [
                            'id' => $row->id,
                            'code' => $row->code,
                            'requestStatus' => $row->requestStatus,
                            'bu' => $row->bu,
                            'prStatus' => $row->prStatus,
                            'isMine' => $row->isMine,
                            'isPendingOnMe' => $row->isPendingOnMe,
                            'isAssignment' => $row->isAssignment,
                            'user' => $row->user,
                            'approveddoc' => $row->approveddoc,
                            'created_at' => $row->created_at,
                            'FullName' => []
                        ];
                    }
                    if ($row->FullName) {
                        $groupedData[$row->id]['FullName'][] = $row->FullName;
                    }
                }

                foreach ($groupedData as &$data) {
                    $data['FullName'] = implode(', ', array_unique($data['FullName']));
                }

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                // 'data' => $data
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
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['bu'] = $this->getEmployeeID()->companycode;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $this->createApprManager($requestData['depthead_id'], $this->modulename, $req_id);

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
        try {

            $data = $this->model->select(
                'request_material.*',
                'codes.code'
                )
                ->leftJoin('codes','request_material.code_id','codes.id')
                ->where('request_material.id',$id)
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            // Mengambil semua data dari request
            $requestData = $request->all();
            
            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            // $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);
            $data->update($requestData);

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

    public function genPdfMaterialReq(Request $request, $id) {
        $data =  $this->model->select('request_material.*','users.username')
                    ->leftJoin('users','request_material.user_id','users.id')
                    ->where('request_material.id',$id)
                    ->with(['code','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = $originatorApproval->created_at; // time originator submitted submission

        $dataDetails = DB::table('request_material_detail')->select('*')->where('req_id',$id)->get(); // data detail
        $emp = Employee::select('*')->with(['location','company'])->where('LoginName',$data->username)->first(); // data employee
        $dataAppr = DB::table('MaterialreqApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/ecatalog/lto_form.xlsx");

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
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 2) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("N36")->Value = $appr->apprname;
                        $Worksheet->Range("N37")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 33, 14, 35, $excel);
                    }
                }
            }

            $xlShiftDown=-4121;
				$no = 1;
				for ($a=17;$a<17+count($dataDetails);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("D".$a)->Value = $dataDetails[$a-17]->materialCode;
					$Worksheet->Range("E".$a)->Value = $dataDetails[$a-17]->description;
					$Worksheet->Range("N".$a)->Value = $dataDetails[$a-17]->part_number;
					$Worksheet->Range("O".$a)->Value = $dataDetails[$a-17]->pg;
					$Worksheet->Range("P".$a)->Value = $dataDetails[$a-17]->uom;
					// $Worksheet->Range("Q".$a)->Value = $dataDetails[$a-17]->required;
					// $Worksheet->Range("R".$a)->Value = $dataDetails[$a-17]->available;
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
            $action = 'gen-pdf-ecatalog';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }

}
