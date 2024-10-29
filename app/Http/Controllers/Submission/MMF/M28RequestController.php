<?php

namespace App\Http\Controllers\Submission\MMF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\MMF\Mmf;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;
use COM;

class M28RequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Mmf();
        $this->modulename = 'Mmf';
        $this->module = new Module();
        $this->user = new User();
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
            where l.ApprovalAction='1' and l.req_id = request_mmf.id and l.module_id = '".$module_id."' and request_mmf.requestStatus='1'
            order by a.sequence)";

            $getProcHead = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.req_id = request_mmf.id and l.module_id = '".$module_id."' and r.ApprovalType='Procurement Head' and r.isactive='1'
            order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_mmf.*,codes.code,employee.tbl_employee.FullName as employee_name,
                    request_mmf_28.MaterialDescr,request_mmf_28.Symptomps,
                    CASE WHEN request_mmf.employee_id='".$employee_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe,
                    ".$getProcHead." as isProcHead
                ")
                ->leftJoin('codes','request_mmf.code_id','codes.id')
                ->leftJoin('employee.tbl_employee','request_mmf.employee_id','employee.tbl_employee.id')
                ->leftJoin('request_mmf_28', 'request_mmf.id', 'request_mmf_28.req_id')
                ->with(['user','approverlist'])
                ->where('category','MMF28')
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $employee_id) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $employee_id) {
                            if ($isAdmin) {
                                $query->whereIn("request_mmf.requestStatus", [0,1,3,4])
                                    ->where("request_mmf.user_id", "!=", $user_id);
                            } 
                        })
                        ->orWhere("request_mmf.employee_id", $employee_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_mmf.employee_id = '".$employee_id."' THEN 0 ELSE 1 END, request_mmf.created_at desc")
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
            $requestData['employee_id'] = $this->getEmployeeID()->id;
            $requestData['category'] = 'MMF28';
            $requestData['bu'] = $this->getEmployeeID()->companycode;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $detailData['req_id'] = $req_id;
            $newData->detail28()->create($detailData);

            $this->createApprManager($requestData['depthead_id'], $this->modulename, $req_id);
            
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
                'request_mmf.*',
                'codes.code'
                )
                ->leftJoin('codes','request_mmf.code_id','codes.id')
                ->where('request_mmf.id',$id)
                ->with('detail28')
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename.'28');
                $data->save();
            }

            if($data->employee_id == $this->getEmployeeID()->id && $data->user_id == null) {
                $data->user_id = $this->getAuth()->id;
                $data->save();
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
            $data = $this->model->with('detail28')->findOrFail($id);
            $reqStatus = $data->requestStatus;
            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

            $data->update($requestData);

            if (isset($requestData['detail28'])) {
                $detailData = $requestData['detail28'];
                // Cari detail berdasarkan ID, jika ada
                $detail = $data->detail28;
                if ($detail) {
                    if(isset($detailData['RequiredType'])) {
                        if($detailData['RequiredType'] !== 4) {
                            $detail->RequiredOther = null;
                        }
                    }
                    if(isset($detailData['isHazardousChemical'])) {
                        if($detailData['isHazardousChemical'] !== 1) {
                            $detail->HazChemicalName = null;
                            $detail->isDecontaminated = null;
                            $detail->isNonChemical = null;
                            $detail->isNotContaminated = null;
                            $detail->isNonHazardous = null;
                            $detail->NotContaminatedReason = null;
                            $detail->NonHazChemicalName = null;
                        }
                    }
                    if(isset($detailData['isNotContaminated'])) {
                        if($detailData['isNotContaminated'] !== 1) {
                            $detail->NotContaminatedReason = null;
                        }
                    }
                    if(isset($detailData['isNonHazardous'])) {
                        if($detailData['isNonHazardous'] !== 1) {
                            $detail->NonHazChemicalName = null;
                        }
                    }
                    
                    $detail->update($detailData);
                } else {
                    // Jika detail tidak ditemukan, tambahkan data baru
                    $detailData['req_id'] = $data->id;
                    $data->detail28()->create($detailData);
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

    public function genPdfMmfReq(Request $request, $id) {
        $data =  $this->model->select('request_mmf.*','users.username')
                    ->leftJoin('users','request_mmf.user_id','users.id')
                    ->where('request_mmf.id',$id)
                    ->where('request_mmf.category','MMF30')
                    ->with(['code','detail30','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = $originatorApproval->created_at; // time originator submitted submission
        
        $dataDetails = DB::table('request_mmf_30_detail')->select('*')->where('mmf30_id',$data->id)->get(); // data detail
        $emp = Employee::select('*')->with(['location','company','department'])->where('LoginName',$data->username)->first(); // data employee
        $dataAppr = DB::table('Mmf30reqApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/mmf/mmf30.xls");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;

            function formatCurrency($amount, $decimals = 0, $decimalSeparator = '.', $thousandSeparator = ',')
            {
                return number_format($amount, $decimals, $decimalSeparator, $thousandSeparator);
            }

            // Start Form Data
            
                $Worksheet->Range("A4")->Value = (($data->detail30->PRType == 1)?'X':'');
                $Worksheet->Range("A5")->Value = (($data->detail30->PRType == 2)?'X':'');
                $Worksheet->Range("A6")->Value = (($data->detail30->PRType == 3)?'X':'');
                $Worksheet->Range("A7")->Value = (($data->detail30->PRType == 4)?'X':'');

                $Worksheet->Range("G4")->Value = (($data->detail30->RequisitionType == 1)?'X':'');
                $Worksheet->Range("G5")->Value = (($data->detail30->RequisitionType == 2)?'X':'');
                $Worksheet->Range("G6")->Value = (($data->detail30->RequisitionType == 3)?'X':'');
                $Worksheet->Range("G7")->Value = (($data->detail30->RequisitionType == 4)?'X':'');
                $Worksheet->Range("G8")->Value = (($data->detail30->RequisitionType == 5)?'X':'');

                $Worksheet->Range("C9")->Value = $data->code->code;
                $Worksheet->Range("E9")->Value = $data->created_at->format('Y-m-d');
                $Worksheet->Range("H9")->Value = $data->detail30->CostCode;

                $Worksheet->Range("C10")->Value = $emp->FullName;
                $Worksheet->Range("E10")->Value = $data->detail30->DeliverTo;
                $Worksheet->Range("H10")->Value = $emp->department->DepartmentName;

                $Worksheet->Range("C13")->Value = $data->detail30->SupplierName;
                $Worksheet->Range("D13")->Value = 'Supplier Address: '.$data->detail30->SupplierAddress;
                $Worksheet->Range("F13")->Value = 'Email / Fax: '.$data->detail30->SupplierEmailFax;
                $Worksheet->Range("I13")->Value = 'Contract No.: '.$data->detail30->ContractNo;

                $Worksheet->Range("A19")->Value = 'Remarks : '.$data->detail30->RemarksU;
                // Set the initial value of the cell
                $Worksheet->Range("E23")->Value = 'Reason for requisition/purchase: ' . $data->detail30->Reason;

                // Format the text in cell E23
                $range = $Worksheet->Range("E23");

                // Set "Reason for requisition/purchase:" to red
                $range->Characters(1, 31)->Font->Color = -16776961; // RGB for red
                $range->Characters(1, 31)->Font->Underline = true; // underline

                // Set the reason text to black
                $reasonStart = 32; // Assuming the reason starts immediately after the colon and space
                $reasonLength = strlen($data->detail30->Reason);
                $range->Characters($reasonStart, $reasonLength)->Font->Color = 0; // RGB for black

            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }

            // // signature originator
            $Worksheet->Range("C23")->Value = $emp->FullName;
            $Worksheet->Range("D23")->Value = '/ '.$subimissionDate->format('Y-m-d');
            addPictureToWorksheet($Worksheet, $picpath, 23, 3, 30, $excel);
            
            // // signature approver
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 2) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("C25")->Value = $appr->apprname;
                        $Worksheet->Range("D25")->Value = '/ '.$appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 25, 3, 30, $excel);
                    }
                }
                if($appr->sequence == 3) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("C30")->Value = $appr->apprname.' / '.$appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 30, 3, 30, $excel);
                    }
                }
                if($appr->sequence == 4) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("E30")->Value = $appr->apprname.' / '.$appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 30, 5, 30, $excel);
                    }
                }
            }
            
            $totalExtendedPrice = 0;
            $xlShiftDown=-4121;
				$no = 1;
				for ($a=16;$a<16+count($dataDetails);$a++){
                    $totalExtendedPrice += $dataDetails[$a-16]->ExtendedPrice;
				}
                $Worksheet->Range("J19")->Value = formatCurrency($totalExtendedPrice);

                for ($a=16;$a<16+count($dataDetails);$a++){
                    $totalExtendedPrice += $dataDetails[$a-16]->ExtendedPrice;

					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("A".$a)->Value = $no++;
					$Worksheet->Range("B".$a)->Value = $dataDetails[$a-16]->MaterialCode;
					$Worksheet->Range("C".$a)->Value = $dataDetails[$a-16]->MaterialDescr;
					$Worksheet->Range("D".$a)->Value = $dataDetails[$a-16]->PartNumber;
					$Worksheet->Range("E".$a)->Value = $dataDetails[$a-16]->BrandManufacturer;
					$Worksheet->Range("F".$a)->Value = formatCurrency($dataDetails[$a-16]->Qty);
					$Worksheet->Range("G".$a)->Value = $dataDetails[$a-16]->Unit;
					$Worksheet->Range("H".$a)->Value = $dataDetails[$a-16]->Currency;
					$Worksheet->Range("I".$a)->Value = formatCurrency($dataDetails[$a-16]->UnitPrice);
					$Worksheet->Range("J".$a)->Value = formatCurrency($dataDetails[$a-16]->ExtendedPrice);

                    // Enable text wrapping for the MaterialDescr cell
                    $Worksheet->Cells($a, 3)->WrapText = true;

                    // Auto-fit the row height
                    $Worksheet->Rows($a)->AutoFit();

				}
            
            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code->code);
			$fileName = $data->id . '_30' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/mmf/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/mmf/pdf/' . $fileName;

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
