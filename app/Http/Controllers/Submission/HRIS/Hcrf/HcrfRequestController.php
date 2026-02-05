<?php

namespace App\Http\Controllers\Submission\HRIS\Hcrf;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\HRIS\Hris;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use App\Models\Employee;
use DB;
use COM;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class HcrfRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Hris();
        $this->modulename = 'Hris';
        $this->submodulename = "Hcrf";
        $this->module = new Module();
        $this->user = new User();

    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i'); // Mengubah format tanggal
    }

    public function index(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employee_id = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.ApprovalAction='1' and l.req_id = request_hris.id and l.module_id = '".$module_id."' and request_hris.requestStatus='1'
            order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_hris.*,codes.code,employee.tbl_employee.FullName as employee_name,
                    CASE WHEN request_hris.employee_id='".$employee_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_hris.code_id','codes.id')
                ->leftJoin('employee.tbl_employee','request_hris.employee_id','employee.tbl_employee.id')
                // ->leftJoin('request_f_advance', 'request_hris.id', 'request_f_advance.req_id')
                ->with(['user','approverlist','detailHcrf'])
                ->where('category','Hcrf')
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $employee_id) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $employee_id) {
                            if ($isAdmin) {
                                $query->whereIn("request_hris.requestStatus", [0,1,3,4])
                                    ->where("request_hris.user_id", "!=", $user_id);
                            } 
                        })
                        ->orWhere("request_hris.employee_id", $employee_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_hris.employee_id = '".$employee_id."' THEN 0 ELSE 1 END, request_hris.created_at desc")
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

    // public function report(Request $request)
    // {
    //     try {
    //         $startDate = $request->input('startDate');
    //         $endDate = $request->input('endDate');
            
    //         $query = $this->model->selectRaw("request_hris.*,codes.code,employee.tbl_employee.FullName as employee_name,
    //                 request_hris_30.PRType,request_hris_30.RequisitionType,request_hris_30.Reason
    //             ")
    //             ->leftJoin('codes','request_hris.code_id','codes.id')
    //             ->leftJoin('employee.tbl_employee','request_hris.employee_id','employee.tbl_employee.id')
    //             ->leftJoin('request_hris_30', 'request_hris.id', 'request_hris_30.req_id')
    //             ->where('request_hris.category', 'MMF30')
    //             ->whereIn('request_hris.requestStatus', [3]);
               
    //         if ($startDate && $endDate) {
    //             $query->whereBetween('request_hris.created_at', [$startDate, $endDate]);
    //         }
            
    //         $data = $query->orderBy('created_at', 'desc')->with(['user'])->get();
         
    //         return response()->json([
    //             'status' => "show",
    //             'message' => $this->getMessage()['show'],
    //             'data' => $data
    //         ])->setEncodingOptions(JSON_NUMERIC_CHECK);

    //     } catch (\Exception $e) {

    //         return response()->json(["status" => "error", "message" => $e->getMessage()]);
    //     }
    // }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['employee_id'] = $this->getEmployeeID()->id;
            $requestData['category'] = $this->submodulename;
            $requestData['category_id'] = $this->getCategoryFormIdByModule($this->submodulename);
            $requestData['bu'] = $this->getEmployeeID()->companycode;
            // $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);
            // dd($this->getEmployeeID()->level_id);
            if($this->getEmployeeID()->level_id !== 4) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['accessformanageronly']]);
            }
            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;
            $detailData['req_id'] = $req_id;
            $newData->detailHcrf()->create($detailData);

            $newData = $this->model->with('detailHcrf')->find($newData->id);

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
                'request_hris.*',
                'codes.code'
                )
                ->leftJoin('codes','request_hris.code_id','codes.id')
                ->where('request_hris.id',$id)
                ->with('detailHcrf')
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->submodulename);
                $data->save();
            }

            if($data->createdby == $this->getEmployeeID()->id) {
                if($data->user_id == null) {
                    $data->user_id = $this->getAuth()->id;
                    $data->save();
                }
            } else {
                if($data->user_id == null) {
                    $getEmployee = $this->getEmployeeByID($data->createdby); // mendapatkan data employee by id
                    $getUser = $this->user->where('username',$getEmployee->LoginName)->get(); // cari username pada table users

                    if(count($getUser) > 0) {
                        $getuserid = $this->getUser($getEmployee->LoginName);
                        $data->user_id = $getuserid->id;
                        $data->save();
                    } else {
                        $getldap = LdapUser::findBy('samaccountname',$getEmployee->LoginName);

                        if ($getldap) {
                            $createdUser = $this->user->create([
                                "guid" => $getldap->getConvertedGuid(), // Add the "guid" attribute here
                                "domain" => "default",
                                "username" => $getldap['samaccountname'][0],
                                "fullname" => $getldap['name'][0],
                                "email" => $getldap['mail'][0]
                            ]);
                            // setelah terdaftar di users lalu dapatkan id dan save pada user_id
                            $data->user_id = $createdUser->id;
                            $data->save();
                        } else {
                            return response()->json(["status" => "error", "message" => $this->getMessage()['usernotregistered']]);
                        }

                    }
                    
                }
            }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        // Mulai transaksi database
        DB::beginTransaction();
        
        try {
            $data = $this->model->with('detailHcrf')->findOrFail($id);
            $reqStatus = $data->requestStatus;
            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

            // $data->update($requestData);
            
            if (isset($requestData['detail_hcrf'])) {
                $detailData = $requestData['detail_hcrf'];
                
                // dd($data);
                // Cari detail berdasarkan ID, jika ada
                $detail = $data->detailHcrf;
                if ($detail) {
                    // if(isset($detailData['AdvanceForm'])) {
                    //     if($detailData['AdvanceForm'] !== 2) {
                    //         $detail->OpsCategory = null;
                    //     }
                    // }
                    if($request->depthead_id) {
                        $this->createApprGMKF($request->depthead_id, $this->modulename, $id);
                    }
                    if(isset($detailData['level'])) {
                        if($detailData['level'] == 'Manager') {
                            $this->createApprGMKF($this->modulename, $id, 1);
                        } else {
                            $this->createApprGMKF($this->modulename, $id, 0);
                        }
                    }
                    $detail->update($detailData);
                } else {
                    // Jika detail tidak ditemukan, tambahkan data baru
                    $detailData['req_id'] = $data->id;
                    $data->detailHcrf()->create($detailData);
                }
            }

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
            $module = $this->module->select('id', 'module')->where('module', $this->submodulename)->first();
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

    public function genPdfHcrfReq(Request $request, $id) {
        $data =  $this->model->select('request_hris.*','users.username')
                    ->leftJoin('users','request_hris.user_id','users.id')
                    ->where('request_hris.id',$id)
                    ->where('request_hris.category','Hcrf')
                    ->with(['code','detailHcrf','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = ($originatorApproval) ? $originatorApproval->created_at : $data->created_at; // time originator submitted submission
        
        $emp = Employee::select('*')->with(['location','company','department'])->where('LoginName',$data->username)->first(); // data employee
        $dataAppr = DB::table('HcrfreqApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/hris/hcrf/hcrf.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;


            // Fungsi untuk membersihkan dan mengkonversi HTML menjadi teks plain dengan newline
            function cleanAndConvertHTML($text) {
                // Gantikan <br>, <br/>, <br /> dengan karakter newline
                $textWithNewlines = str_replace(['<br>', '<br/>', '</p>', '<br />'], "\n", $text);
                // Hilangkan tag HTML lainnya
                return strip_tags($textWithNewlines);
            }

            // Start Form Data
            
                $Worksheet->Range("D5")->Value = $data->detailHcrf->jobTitle;
                $Worksheet->Range("D6")->Value = $data->detailHcrf->level;
                $Worksheet->Range("D7")->Value = $data->detailHcrf->department;
                $Worksheet->Range("J5")->Value = $data->detailHcrf->neededEmp;
                $Worksheet->Range("J6")->Value = $data->detailHcrf->reportDirectly;
                $Worksheet->Range("J7")->Value = $data->detailHcrf->reportIndirectly;

                $Worksheet->Range("D9")->Value = (($data->detailHcrf->isHeadcount == 1)?'Budgeted':'Replacement');
                $Worksheet->Range("D10")->Value = (($data->detailHcrf->isCriticalPosition == 1)?'Yes':'No');
                $Worksheet->Range("D11")->Value = $data->detailHcrf->expectedDate;

                // Memproses data detail menggunakan fungsi cleanAndConvertHTML
                $jobDescCleaned = cleanAndConvertHTML($data->detailHcrf->jobDesc);
                $trainingPlanCleaned = cleanAndConvertHTML($data->detailHcrf->trainingPlan);
                $careerDevPlanCleaned = cleanAndConvertHTML($data->detailHcrf->careerDevPlan);
                $specialSkillsCleaned = cleanAndConvertHTML($data->detailHcrf->specialSkills);

                $Worksheet->Range("A14")->Value = $jobDescCleaned;

                $Worksheet->Range("D20")->Value = $data->detailHcrf->location;
                $Worksheet->Range("A24")->Value = $trainingPlanCleaned;
                $Worksheet->Range("A28")->Value = $careerDevPlanCleaned;

                $Worksheet->Range("B24")->Value = $data->detailHcrf->education;
                $Worksheet->Range("H24")->Value = $data->detailHcrf->experienceLength;
                $Worksheet->Range("B26")->Value = $data->detailHcrf->language;
                $Worksheet->Range("H26")->Value = $specialSkillsCleaned;


                // Format the text in cell E23
                // $range = $Worksheet->Range("E23");

                // $Worksheet->Rows("14:14")->AutoFit();
                // Mengaktifkan pembungkusan teks dalam sel yang di-merge
                // $range->WrapText = true;

                // Menyesuaikan tinggi semua baris yang bersesuaian (A14:K19)
                // for ($i = 14; $i <= 19; $i++) {
                //     $Worksheet->Rows($i)->AutoFit();
                // }

                // Set "Reason for requisition/purchase:" to red
                // $range->Characters(1, 31)->Font->Color = -16776961; // RGB for red
                // $range->Characters(1, 31)->Font->Underline = true; // underline

                // Set the reason text to black
                // $reasonStart = 32; // Assuming the reason starts immediately after the colon and space
                // $reasonLength = strlen($data->detailHcrf->Reason);
                // $range->Characters($reasonStart, $reasonLength)->Font->Color = 0; // RGB for black

            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }

            // // signature originator
            $Worksheet->Range("D37")->Value = $emp->FullName;
            $Worksheet->Range("D38")->Value = $subimissionDate->format('Y-m-d');
            addPictureToWorksheet($Worksheet, $picpath, 33, 4, 30, $excel);
            
            // // signature approver
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 3) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("F37")->Value = $appr->apprname;
                        $Worksheet->Range("F38")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 33, 6, 30, $excel);
                    }
                }
                if($appr->sequence == 4) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("I37")->Value = $appr->apprname;
                        $Worksheet->Range("I38")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 33, 9, 30, $excel);
                    }
                }
            }
            
            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/hris/hcrf/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/hris/hcrf/pdf/' . $fileName;

            $updateData = $this->model->find($data->id);
			$updateData->approveddoc = str_replace("\\", "/", $pathfilename);
			$updateData->save();

            $this->processcopy($pathfilename);

			return $pathfilename;

		} catch (\Exception $e) {
            // Log error
            $ip = $request->ip();
            $url = $request->url();
            $action = 'gen-pdf-hcrf';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }

}
