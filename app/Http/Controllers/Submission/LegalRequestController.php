<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Legal;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class LegalRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Legal();
        $this->modulename = 'Legal';
        $this->module = new Module();
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
            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id
                where l.ApprovalAction='1'
                and l.req_id = request_legal.id and l.module_id = '".$module_id."' 
                and request_legal.requestStatus='1'
                order by a.sequence)"; 

            $data = $dataquery
                ->selectRaw("request_legal.*,codes.code,
                    CASE WHEN request_legal.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_legal.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin) {
                            if ($isAdmin) {
                                $query->where("request_legal.user_id", "!=", $user_id)
                                    ->whereIn("request_legal.requestStatus", [1,3,4]);
                            } else {
                                $query->where("request_legal.user_id", "!=", $user_id)
                                    ->whereIn("request_legal.requestStatus", [3])
                                    ->where("bu",$this->getEmployeeID()->companycode);
                            }
                        })      
                        ->orWhere("request_legal.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                // ->orderByRaw("CASE WHEN request_legal.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_legal.submitDate desc")
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

    public function checkattachmentlegal(Request $request)
    {
        try {
            $hasSP = false;
            $hasRFC = false;
            $hasSD = false;

            if ($request->countfamily > 0 ) {
                $data = DB::table('tbl_attachment')
                    ->where('req_id', $request->req_id)
                    ->where('module_id', $this->getModuleId($request->modelname))
                    ->where(function($query) {
                        $query->where('remarks', 'like', 'SP')
                              ->orWhere('remarks', 'like', 'RFC');
                    })
                    ->get();
                    foreach ($data as $attc) {
                        // $countattfamily = ?
                        if ($attc->remarks === 'SP') {
                            // if($request->countfamily == $countattfamily) {
                            //     $hasKTP = true;
                            // }
                            $hasSP = true;
                        }
                        if ($attc->remarks === 'RFC') {
                            $hasRFC = true;
                        }
                    }
        
                    if (!$hasRFC) {
                        return response()->json(["status" => "error", "message" => "Error: Supporting document 'KTP' is required. Please attach it."]);
                    }
        
                    if (!$hasSP) {
                        return response()->json(["status" => "error", "message" => "Error: Supporting document 'KK' is required. Please attach it."]);
                    }
            } else if($request->countguest > 0) {                
                $data = DB::table('tbl_attachment')
                    ->where('req_id', $request->req_id)
                    ->where('module_id', $this->getModuleId($request->modelname))
                    ->where(function($query) {
                        $query->where('remarks', 'like', 'Supporting Document');                         
                    })
                    ->get();
                    // $message = "Supporting document is required!";
                    foreach ($data as $attc) {
                        // $countattfamily = ?
                      
                        if ($attc->remarks === 'Supporting Document') {
                            $hasSD = true;
                        }
                    }
        
                    if (!$hasSD) {
                        return response()->json(["status" => "error", "message" => "Error: Supporting document 'Supporting Document' is required. Please attach it."]);
                    }
            }
            if (count($data) > 0) {
                return response()->json(["status" => "success"]);
            }
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
            $requestData['businessGroup'] = 'KF';
            $requestData['businessType'] = 'Fiber';
            $newData = $this->model->create($requestData);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {

            $data = $this->model->select('request_legal.*','codes.code','archive._tbl_rfc.RFCNo')
            ->leftJoin('codes','request_legal.code_id','codes.id')
            ->leftjoin('archive._tbl_rfc', 'request_legal.rfcnumber', 'archive._tbl_rfc.id')
            ->where('request_legal.id',$id)
            ->with(['user'])
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
        try {
            
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            if($request->Superior) {
                $this->createApprSuperior($request->Superior, $this->modulename, $id);
            }

            $data->update($requestData);

            //start save history perubahan
            $fields = [
                'form_type' => $request->form_type,
                'request_type' => $request->request_type,
            ];
            
            foreach ($fields as $key => $value) {
                if ($value) {
                    $this->approverAction($this->modulename, $id, $key, 1, $value, null, null);
                }
            }
            //end save history perubahan

            if(isset($request->ticketStatus) && $data->requestStatus == 3) {
                $getSubmissionData = $this->model->findOrFail($id);

                $mailData = [
                    "id" => 30, // final approved
                    "action_id" => 5, // update id
                    "submission" => $getSubmissionData,
                    "email" => $this->getUserByid($getSubmissionData->user_id)->email, // kirim kepada creator
                    "fullname" => $this->getUserByid($getSubmissionData->user_id)->fullname,
                    "message" => $this->mailMessage()['newActivity'],
                    "remarks" => $request->ticketStatus
                ];
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$this->modulename,1));
            }

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

    public function genPdfLegal(Request $request, $id) 
    {
        $dataAppr = DB::table('LegalApprover')->select('*')->where('id', $id)->get(); // Data approver
        $data = $this->model->select(
            'request_legal.*',
            'codes.code',
            'users.fullname',
            'archive._tbl_rfc.RFCNo',
        )
        ->leftjoin('codes', 'request_legal.code_id', 'codes.id')
        ->leftJoin('users', 'request_legal.user_id', '=', 'users.id')
        ->leftjoin('archive._tbl_rfc', 'request_legal.rfcnumber', '=', 'archive._tbl_rfc.id')
        ->where('request_legal.id', $id)
        ->first();

        // foreach ($data as $data) {
        //     echo $data->referenceNo; // Sekarang bisa diakses
        // }
        if (!$data || !$dataAppr) {
            return response()->json(["status" => "error", "message" => "Data or dataappr not found"]);
        }

        try {
            $excel = new COM("Excel.Application");
            $excel->Visible = false;

            $file = public_path("template/legal/legal.xlsx");

            if (!file_exists($file)) {
                throw new \Exception("File tidak ditemukan: " . $file);
            }

            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets(1);
            $Worksheet->Activate();

            // Isi Form Data
            $Worksheet->Range("B3")->Value = $data->code;
            $Worksheet->Range("G3")->Value = $data->submitDate;
            $Worksheet->Range("B6")->Value = $data->fullname;
            $Worksheet->Range("G6")->Value = $data->bu;
            $Worksheet->Range("G9")->Value = $data->bu;
            $Worksheet->Range("G12")->Value = $data->bu;
            $Worksheet->Range("B15")->Value = $data->requestType;
            $Worksheet->Range("G15")->Value = $data->formType;
            $Worksheet->Range("B18")->Value = $data->titleOfDocument;
            $Worksheet->Range("G18")->Value = $data->dateOfDocument;
            $Worksheet->Range("B21")->Value = $data->contractNumber;
            $Worksheet->Range("B24")->Value = $data->sk;
            $Worksheet->Range("B27")->Value = $data->RFCNo;
            $Worksheet->Range("B30")->Value = $data->purpose;
            $Worksheet->Range("G21")->Value = $data->countersigningParty;
            $Worksheet->Range("G24")->Value = $data->skNumber;
            $Worksheet->Range("G27")->Value = $data->financialAmount;

            // Tambahkan Gambar Approval jika ada
            $picpath = public_path("assets/images/approved.png");

            if (file_exists($picpath)) {
                function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                    $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                    $pic->Height = $height;
                    $pic->Top = $excel->Cells($row, $column)->Top;
                    $pic->Left = $excel->Cells($row, $column)->Left;
                }

                foreach ($dataAppr as $appr) {
                    if ($appr->sequence == 2 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B38")->Value = $appr->apprname;
                        $Worksheet->Range("D38")->Value = $appr->apprtype;
                        $Worksheet->Range("E38")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 38, 7, 36, $excel);
                    }
                    if ($appr->sequence == 3 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B39")->Value = $appr->apprname;
                        $Worksheet->Range("D39")->Value = $appr->apprtype;
                        $Worksheet->Range("E39")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 39, 7, 36, $excel);
                    }
                    if ($appr->sequence == 4 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B40")->Value = $appr->apprname;
                        $Worksheet->Range("D40")->Value = $appr->apprtype;
                        $Worksheet->Range("E40")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 40, 7, 36, $excel);
                    }
                    if ($appr->sequence == 5 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B41")->Value = $appr->apprname;
                        $Worksheet->Range("D41")->Value = $appr->apprtype;
                        $Worksheet->Range("E41")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 41, 7, 36, $excel);
                    }
                    if ($appr->sequence == 6 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B42")->Value = $appr->apprname;
                        $Worksheet->Range("D42")->Value = $appr->apprtype;
                        $Worksheet->Range("E42")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 42, 7, 36, $excel);
                    }
                    if ($appr->sequence == 7 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B43")->Value = $appr->apprname;
                        $Worksheet->Range("D43")->Value = $appr->apprtype;
                        $Worksheet->Range("E43")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 43, 7, 36, $excel);
                    }
                    if ($appr->sequence == 8 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B44")->Value = $appr->apprname;
                        $Worksheet->Range("D44")->Value = $appr->apprtype;
                        $Worksheet->Range("E44")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 44, 7, 36, $excel);
                    }
                }
            }

            // Ekspor ke PDF
            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/legal/pdf/' . $fileName);
			if (file_exists($filePath)) {
				unlink($filePath);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $filePath, $xlQualityStandard);
			
			$excel->CutCopyMode = false;
            $Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
            $pathfilename = 'public/template/legal/pdf/' . $fileName;
            DB::table('request_legal')
            ->where('id', $id) // Sesuaikan dengan primary key di tabel
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);

			return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-legal', $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Error di " . $e->getFile() . " baris " . $e->getLine() . ": " . $e->getMessage()
            ]);
        }
    }
}