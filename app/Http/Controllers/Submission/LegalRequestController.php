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

    public function store(Request $request)
    {
        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            
            $employee = $this->getEmployeeID();

            if ($employee->companycode === 'KPSI') {
                $employee->companycode = 'IHM';
            }

            $requestData['bu'] = $employee->companycode;

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $this->createApproverList($this->modulename, $req_id);
            
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

            $data = $this->model->select('request_legal.*','codes.code')
            ->leftJoin('codes','request_legal.code_id','codes.id')
            ->where('request_legal.id',$id)
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename);
                $data->save();
            }

            // if($data->noRegistration == null) {
                
            //     $data->save();
            // }

            // if($data->depthead_id !== null) {
            //     $this->createApprManager($data->depthead_id, $this->modulename, $id, $data->requestStatus);
            // }

            // Transform the 'sevenWaste' field from string "1,2" to array [1,2]
                if (isset($data->sevenWaste) && is_string($data->sevenWaste)) {
                    $data->sevenWaste = explode(',', $data->sevenWaste);
                }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->model->findOrFail($id);
            $reqStatus = $data->requestStatus;
            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();
            if($request->isSaving == 1) {
                $requestData['category_id'] = 4;
            } else {
                $requestData['category_id'] = null;
            }

            if(isset($request->isSaving)) {
                if($request->isSaving == 1) {
                    // $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus, $this->getEmployeeID()->companycode);
                    $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus, $data->bu);
                } else {
                    // $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus, $this->getEmployeeID()->companycode);
                    $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus, $data->bu);
                }
            }

            if($request->depthead_id) {
                $this->createApprManager($request->depthead_id, $this->modulename, $id);
            }

            if (is_array($request->sevenWaste) && !empty($request->sevenWaste)) {
                $requestData['sevenWaste'] = implode(",", $request->sevenWaste);
            } else {
                // Handle the case where it's not an array or is empty
                $requestData['sevenWaste'] = null; // or however you want to default it
            }
            
            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            $this->addOneDayToDate($requestData);

            $data->update($requestData);

            //start save history perubahan
            $fields = [
                'objective' => $request->objective,
                'ranking' => $request->ranking,
                // 'isRollout' => $request->isRollout,
                'savingInfo' => $request->savingInfo,
            ];
            
            foreach ($fields as $key => $value) {
                if ($value) {
                    $this->approverAction($this->modulename, $id, $key, 1, $value, null);
                }
            }
            //end save history perubahan

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
        $dataAppr = DB::table('LegalApprover')->where('id', $id)->get(); // Data approver
        $data = $this->model->select(
            'request_legal.*',
            'codes.code',
            'employee.tbl_employee.FullName as FullName'
        )
        ->leftjoin('codes', 'request_legal.code_id', 'codes.id')
        ->leftJoin('employee.tbl_employee', 'request_legal.employee_id', '=', 'employee.tbl_employee.id')
        ->first();

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
            $Worksheet->Range("B3")->Value = $data->referenceNo;
            $Worksheet->Range("G3")->Value = $data->submitDate;
            $Worksheet->Range("B6")->Value = $data->FullName;
            $Worksheet->Range("G6")->Value = $data->bu;
            $Worksheet->Range("G9")->Value = $data->bu;
            $Worksheet->Range("G12")->Value = $data->bu;
            $Worksheet->Range("B15")->Value = $data->requestType;
            $Worksheet->Range("G15")->Value = $data->formType;
            $Worksheet->Range("B18")->Value = $data->titleOfDocument;
            $Worksheet->Range("G18")->Value = $data->dateOfDocument;
            $Worksheet->Range("B21")->Value = $data->skNumber;
            $Worksheet->Range("B24")->Value = $data->sk;
            $Worksheet->Range("B27")->Value = $data->purpose;
            $Worksheet->Range("G21")->Value = $data->countersigningParty;
            $Worksheet->Range("G24")->Value = $data->financialAmount;

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
                    if ($appr->sequence == 1 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B36")->Value = $appr->apprname;
                        $Worksheet->Range("D36")->Value = $appr->apprtype;
                        $Worksheet->Range("E36")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 36, 7, 36, $excel);
                    }
                    if ($appr->sequence == 2 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B37")->Value = $appr->apprname;
                        $Worksheet->Range("D37")->Value = $appr->apprtype;
                        $Worksheet->Range("E37")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 37, 7, 36, $excel);
                    }
                    if ($appr->sequence == 3 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B38")->Value = $appr->apprname;
                        $Worksheet->Range("D38")->Value = $appr->apprtype;
                        $Worksheet->Range("E38")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 38, 7, 36, $excel);
                    }
                    if ($appr->sequence == 4 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B39")->Value = $appr->apprname;
                        $Worksheet->Range("D39")->Value = $appr->apprtype;
                        $Worksheet->Range("E39")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 39, 7, 36, $excel);
                    }
                    if ($appr->sequence == 5 && $appr->approvalAction == 3) {
                        $Worksheet->Range("B40")->Value = $appr->apprname;
                        $Worksheet->Range("D40")->Value = $appr->apprtype;
                        $Worksheet->Range("E40")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 40, 7, 36, $excel);
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