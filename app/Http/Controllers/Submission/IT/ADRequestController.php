<?php

namespace App\Http\Controllers\Submission\IT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionMail;

use App\Models\Submission\IT\ActiveDirectory;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\Assignmentto;
use App\Models\User;
use App\Models\Employee;
use DB;
use COM;

class ADRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new ActiveDirectory();
        $this->modulename = 'ActiveDirectory';
        $this->module = new Module();
        $this->employee = new Employee();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.ApprovalAction='1' and l.req_id = request_it_activedirectory.id and l.module_id = '".$module_id."' and request_it_activedirectory.requestStatus='1'
            order by a.sequence)";

            $getIT = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.module_id = '".$module_id."' and r.ApprovalType='IT' and r.isactive='1'
            order by a.sequence)"; // show for all IT for update username&password

            $data = $dataquery
                ->selectRaw("request_it_activedirectory.*,codes.code,
                    CASE WHEN request_it_activedirectory.user_id='".$user_id."' then 1 else 0 end as isMine,
                    employee.tbl_employee.LoginName,
                    ".$subquery." as isPendingOnMe,
                    ".$getIT." as isIT
                ")
                ->leftJoin('codes','request_it_activedirectory.code_id','codes.id')
                ->leftJoin('employee.tbl_employee','request_it_activedirectory.employee_id','employee.tbl_employee.id')
                ->with(['user','approverlist','employee'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin) {
                            if ($isAdmin) {
                                $query->where("request_it_activedirectory.user_id", "!=", $user_id)
                                    ->whereIn("request_it_activedirectory.requestStatus", [1,3,4]);
                            } else {
                                $query->where("request_it_activedirectory.user_id", "!=", $user_id)
                                    ->whereIn("request_it_activedirectory.requestStatus", [1,3]);
                            }
                        })             
                        ->orWhere("request_it_activedirectory.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_it_activedirectory.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_it_activedirectory.created_at desc")
                ->get();

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
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
            $requestData['requestStatus'] = 0;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($request->employee_id);
            $requestData['bu'] = $this->getEmployeeByID($request->employee_id)->companycode;

            // Buat data baru pada tabel utama
            // $newData = $this->model->create($requestData);

            if($request->pic_empid) {
                $getemployee = $this->employee->find( $request->pic_empid);
                $getuser = $this->user->where('username',$getemployee->LoginName)->get();
                
                if(count($getuser) > 0) {
                    $newData = $this->model->create($requestData);
                } else {
                    $getldap = LdapUser::findBy('samaccountname',$getemployee->LoginName);

                    if ($getldap) {
                        $this->user->create([
                            "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                            "domain" => "default",
                            "username" => $getldap['samaccountname'][0],
                            "fullname" => $getldap['name'][0],
                            "email" => $getldap['mail'][0]
                        ]);
                        $newData = $this->model->create($requestData);
                    } else {
                        return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                    }

                }
            }

            // Simpan id dari data baru
            $req_id = $newData->id;

            if($newData->code_id == null) {
                $newData->code_id = $this->generateCode($this->modulename);
                $newData->save();
            }

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

            $data = $this->model->select('request_it_activedirectory.*','codes.code')
            ->leftJoin('codes','request_it_activedirectory.code_id','codes.id')
            ->where('request_it_activedirectory.id',$id)
            ->with('employee')
            // ->with(['employee' => function($query) {
            //     $query->leftJoin('employee.tbl_department', 'employee.tbl_employee.department_id', '=', 'employee.tbl_department.id');
            // }])
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename);
                $data->save();
            }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            
            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            // $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);
            ($data->ticketStatus == null) ? $requestData['ticketStatus'] = 'On Queue' : $request->ticketStatus;
            $data->update($requestData);

            //start save history perubahan
            $fields = [
                'ticketStatus' => $request->ticketStatus,
            ];
            
            foreach ($fields as $key => $value) {
                if ($value) {
                    $this->approverAction($this->modulename, $id, $key, 1, $value, null);
                }
            }
            //end save history perubahan

            if(isset($request->password_temp) && $data->requestStatus == 3) {
                if(!empty($request->password_temp)) {

                    $getSubmissionData = $this->model->findOrFail($id);

                    $mailData = [
                        "id" => 30, // final approved
                        "action_id" => 5, // update id
                        "submission" => $getSubmissionData,
                        "email" => $this->getUserByid($getSubmissionData->user_id)->email, // kirim kepada creator
                        "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
                        "message" => $this->mailMessage()['accountInfoAD'],
                        "remarks" => null
                    ];
                    Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
                }

            }

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
                    $attachments = Attachment::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->get();
                    Attachment::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                        foreach ($attachments as $attachment) {
                            unlink($this->copyuploadpath() .$attachment->path);
                        }
                    Assignmentto::where('req_id', $id)
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

    public function genPdfAD(Request $request, $id) {
        $data = DB::table('ItActiveDirectoryDetail')->select('*')->where('id',$id)->first(); // data submission
        $dataAppr = DB::table('ItActiveDirectoryApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/activedirectory/ad_template.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;

            // Start Form Data
                $Worksheet->Range("F7")->Value = $data->FullName;
                $Worksheet->Range("F9")->Value = $data->SAPID;
                $Worksheet->Range("F11")->Value = $data->DesignationName;
                $Worksheet->Range("F13")->Value = $data->companycode;
                $Worksheet->Range("F15")->Value = $data->Location;
                $Worksheet->Range("F19")->Value = $data->DepartmentName;
                $Worksheet->Range("F31")->Value = $data->validFrom;
			    $Worksheet->Range("R31")->Value = $data->validTo;
                $Worksheet->Range("K37")->Value = $data->companycode;

            //condition
                if($data->requestType == 'Create Account') {
                    $Worksheet->Range("F22")->Value = 'x';
                }else {
                    $Worksheet->Range("L22")->Value = 'x';
                }

                if($data->isVip == 1) {
					$Worksheet->Range("F24")->Value = 'x';
				}else {
					$Worksheet->Range("P24")->Value = 'x';
				}

				if($data->accessType == 'TS Account') {
					$Worksheet->Range("F26")->Value = 'x';
				}else if($data->accessType == 'Non-TS Account') {
					$Worksheet->Range("P26")->Value = 'x';
				}
	
				if($data->accountType == 'Permanent') {
					$Worksheet->Range("F28")->Value = 'x';
				}else if($data->accountType == 'Temporary') {
					$Worksheet->Range("P28")->Value = 'x';
				}

				
			//end condition

            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }
            
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 2) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("B50")->Value = $appr->apprname;
                        $Worksheet->Range("B51")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 46, 1, 35, $excel);
                    }
                }
                if($appr->sequence == 3) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("I50")->Value = $appr->apprname;
                        $Worksheet->Range("I51")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 46, 7, 35, $excel);

                    }
                }
                if($appr->sequence == 4) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("P50")->Value = $appr->apprname;
                        $Worksheet->Range("P51")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 46, 14, 35, $excel);
                    }
                }
                if($appr->sequence == 5) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("W50")->Value = $appr->apprname;
                        $Worksheet->Range("W51")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 46, 21, 35, $excel);
                    }
                }
            }

            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/activedirectory/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/activedirectory/pdf/' . $fileName;

            $updateData = $this->model->find($data->id);
			$updateData->approveddoc = str_replace("\\", "/", $pathfilename);
			$updateData->save();

            $this->processcopy($pathfilename);

			return $pathfilename;

		} catch (\Exception $e) {
            // Log error
            $ip = $request->ip();
            $url = $request->url();
            $action = 'gen-pdf-AD';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }
}
