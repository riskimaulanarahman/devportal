<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Jdi;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class JdiRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Jdi();
        $this->modulename = 'Jdi';
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

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.ApprovalAction='1' and l.req_id = request_jdi.id and l.module_id = '".$module_id."' and request_jdi.requestStatus='1'
            order by a.sequence)";

            $getbcidv = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.req_id = request_jdi.id and l.module_id = '".$module_id."' and r.ApprovalType='BCID CI Facilitator' and r.isactive='1'
            order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_jdi.*,codes.code,
                    CASE WHEN request_jdi.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe,
                    ".$getbcidv." as isBCIDv
                ")
                ->leftJoin('codes','request_jdi.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin) {
                            if ($isAdmin) {
                                $query->where("request_jdi.user_id", "!=", $user_id)
                                    ->whereIn("request_jdi.requestStatus", [1,3,4]);
                            } else {
                                $query->where("request_jdi.user_id", "!=", $user_id)
                                    ->whereIn("request_jdi.requestStatus", [3]);
                            }
                        })             
                        ->orWhere("request_jdi.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_jdi.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_jdi.submitDate desc")
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

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            // $this->createApproverList($this->modulename, $req_id);
            
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

            $data = $this->model->select('request_jdi.*','codes.code')
            ->leftJoin('codes','request_jdi.code_id','codes.id')
            ->where('request_jdi.id',$id)
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

            if($request->isSaving == 1) {
                $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus);
            } else {
                $this->createApprSaving($request->isSaving, $this->modulename, $id, $reqStatus);
            }

            if($request->depthead_id) {
                $this->createApprManager($request->depthead_id, $this->modulename, $id);
            }

            if($request->sevenWaste) {
                $requestData['sevenWaste'] = implode("," ,$request->sevenWaste);
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
                    $this->approverAction($this->modulename, $id, $key, 1, $value);
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

    public function genPdfJdi(Request $request, $id) {
        $data = DB::table('jdiDetail')->select('*')->where('id',$id)->first(); // data submission
        $dataAppr = DB::table('jdiApprover')->select('*')->where('id',$id)->get(); // data approver
        $dataAtt = DB::table('jdiAttachment')->select('*')->where('id',$id)->get(); // data attachment

        if($data->noRegistration == null || $data->noRegistration == '') {
            Jdi::where('id',$id)
            ->update(
                [
                    "noRegistration" => $this->generateCodeJdiNoreg($data->bu),
                    "status_jdi" => "Register"
                ]
            );
        }

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/jdi/jdi.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;

            // Start Form Data
                $Worksheet->Range("D3")->Value = $data->submitDate; // Tanggal
                $Worksheet->Range("D4")->Value = $data->bu;
                $Worksheet->Range("D5")->Value = $data->nama_pencetus_ide; // Pencetus Ide
                $Worksheet->Range("D6")->Value = $data->anggota_1;
                $Worksheet->Range("D7")->Value = $data->anggota_2;
                $Worksheet->Range("D8")->Value = $data->deptHead;
                $Worksheet->Range("D9")->Value = $data->title;

                $Worksheet->Range("G3")->Value = $data->objective;
                $Worksheet->Range("I3")->Value = $data->ranking;
                $Worksheet->Range("G4")->Value = $data->departmentName;
                $Worksheet->Range("G5")->Value = $data->sapid_pencetus_ide;
                $Worksheet->Range("I5")->Value = $data->level_pencetus_ide;
                $Worksheet->Range("G6")->Value = $data->sapid_anggota_1;
                $Worksheet->Range("G7")->Value = $data->sapid_anggota_2;
                $Worksheet->Range("G8")->Value = $data->sapid_deptHead;

                $Worksheet->Range("B11")->Value = $data->htk;
                $Worksheet->Range("E11")->Value = $data->perbaikan;
                $Worksheet->Range("G12")->Value = $data->isNotWasteful;
                $Worksheet->Range("G13")->Value = $data->reasonNotWasteful;

                if($data->isSaving == 'Ya') {
                    $Worksheet->Range("B34")->Value = $data->savingFormula;
                    $Worksheet->Range("B36")->Value = $data->totalSaving;
                }

                $Worksheet->Range("H44")->Value = $data->isRollout;
                $Worksheet->Range("E46")->Value = $data->savingInfo;
                $Worksheet->Range("E46")->Value = $data->savingInfo;
                $Worksheet->Range("F37")->Value = $data->noRegistration;
            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }
            
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 1) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("E41")->Value = $appr->apprname;
                        $Worksheet->Range("E43")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 40, 5, 40, $excel);
                    }
                }
                if($appr->sequence == 2) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("F41")->Value = $appr->apprname;
                        $Worksheet->Range("F43")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 40, 8, 40, $excel);

                    }
                }
                if($appr->sequence == 3) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("H41")->Value = $appr->apprname;
                        $Worksheet->Range("H43")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 40, 6, 40, $excel);
                    }
                }
                if($data->isSaving == 'Ya') {
                    if($appr->sequence == 4) {
                        if($appr->approvalAction == 3) {
                            $Worksheet->Range("F49")->Value = $appr->apprname;
                            $Worksheet->Range("F50")->Value = $appr->approvalDate;
                            addPictureToWorksheet($Worksheet, $picpath, 48, 6, 40, $excel);
                        }
                    }
                    if($appr->sequence == 5) {
                        if($appr->approvalAction == 3) {
                            $Worksheet->Range("H49")->Value = $appr->apprname;
                            $Worksheet->Range("H50")->Value = $appr->approvalDate;
                            addPictureToWorksheet($Worksheet, $picpath, 48, 8, 40, $excel);
                        }
                    }
                }
            }

            $rowbefore = 63;
            $rowafter = 153;
            $countBefore = 0;  // Penghitung untuk gambar 'Before'
            $countAfter = 0;   // Penghitung untuk gambar 'After'
            foreach($dataAtt as $att) {
                if($att->remarks == 'Before' && $countBefore < 3) { // kondisi untuk menambahkan foto sebelum dan menampilkan foto tidak lebih dari 3 (max)
                    $Worksheet->Range("B" . $rowbefore)->Value = addPictureToWorksheet($Worksheet, "http://172.18.83.38/devportal/public/upload/" . $att->path, $rowbefore, 2, 300, $excel);
                    $rowbefore+=25;
                    $countBefore++;
                }
                if($att->remarks == 'After' && $countAfter < 3) { // kondisi untuk menambahkan foto sesudah dan menampilkan foto tidak lebih dari 3 (max)
                    $Worksheet->Range("B" . $rowafter)->Value = addPictureToWorksheet($Worksheet, "http://172.18.83.38/devportal/public/upload/" . $att->path, $rowafter, 2, 300, $excel);
                    $rowafter+=25;
                    $countAfter++;
                }
            }

            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/jdi/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/jdi/pdf/' . $fileName;

            $updateData = $this->model->find($data->id);
			$updateData->approveddoc = str_replace("\\", "/", $pathfilename);
			$updateData->save();

            $this->processcopy($pathfilename);

			return $pathfilename;

		} catch (\Exception $e) {
            // Log error
            $ip = $request->ip();
            $url = $request->url();
            $action = 'gen-pdf-jdi';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }
}
