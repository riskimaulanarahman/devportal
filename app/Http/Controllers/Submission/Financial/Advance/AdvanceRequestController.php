<?php

namespace App\Http\Controllers\Submission\Financial\Advance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Financial\Financial;
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

class AdvanceRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;

    public function __construct()
    {
        $this->model = new Financial();
        $this->modulename = 'Financial';
        $this->submodulename = "Advance";
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
            where l.ApprovalAction='1' and l.req_id = request_financial.id and l.module_id = '".$module_id."' and request_financial.requestStatus='1'
            order by a.sequence)";

            // $getProcHead = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            // from tbl_approverListReq l
            // left join tbl_approver a on l.approver_id=a.id
            // left join tbl_approvaltype r on a.approvaltype_id = r.id 
            // where l.req_id = request_financial.id and l.module_id = '".$module_id."' and r.ApprovalType='Procurement Head' and r.isactive='1'
            // order by a.sequence)";

            // $getBuyer = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            // from tbl_approverListReq l
            // left join tbl_approver a on l.approver_id=a.id
            // left join tbl_approvaltype r on a.approvaltype_id = r.id 
            // where l.req_id = request_financial.id and l.module_id = '".$module_id."' and r.ApprovalType='Buyer' and r.isactive='1'
            // order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_financial.*,codes.code,employee.tbl_employee.FullName as employee_name,
                    CASE WHEN request_financial.createdby='".$employee_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_financial.code_id','codes.id')
                ->leftJoin('employee.tbl_employee','request_financial.createdby','employee.tbl_employee.id')
                // ->leftJoin('request_f_advance', 'request_financial.id', 'request_f_advance.req_id')
                ->with(['user','approverlist','detailAdvance'])
                ->where('category','Advance')
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $employee_id) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $employee_id) {
                            if ($isAdmin) {
                                $query->whereIn("request_financial.requestStatus", [0,1,3,4])
                                    ->where("request_financial.user_id", "!=", $user_id);
                            } 
                        })
                        ->orWhere("request_financial.createdby", $employee_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_financial.createdby = '".$employee_id."' THEN 0 ELSE 1 END, request_financial.created_at desc")
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

    public function historyApprover(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employee_id = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);

            $data = $this->model->selectRaw("request_mmf.*,codes.code,employee.tbl_employee.FullName as employee_name,
                    request_mmf_30.PRType,request_mmf_30.RequisitionType,request_mmf_30.Reason
                ")
                ->leftJoin('codes','request_mmf.code_id','codes.id')
                ->leftJoin('tbl_approverListReq', 'request_mmf.id', '=', 'tbl_approverListReq.req_id')
                ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                ->leftJoin('employee.tbl_employee','request_mmf.employee_id','employee.tbl_employee.id')
                ->leftJoin('request_mmf_30', 'request_mmf.id', 'request_mmf_30.req_id')
                ->where('request_mmf.category', 'MMF30')
                ->where('request_mmf.requestStatus', 3)
                ->where('tbl_approverListReq.module_id', $module_id)
                ->where('tbl_approver.employee_id', $employee_id)
                ->with(['user'])
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

    public function report(Request $request)
    {
        try {
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            
            $query = $this->model->selectRaw("request_mmf.*,codes.code,employee.tbl_employee.FullName as employee_name,
                    request_mmf_30.PRType,request_mmf_30.RequisitionType,request_mmf_30.Reason
                ")
                ->leftJoin('codes','request_mmf.code_id','codes.id')
                ->leftJoin('employee.tbl_employee','request_mmf.employee_id','employee.tbl_employee.id')
                ->leftJoin('request_mmf_30', 'request_mmf.id', 'request_mmf_30.req_id')
                ->where('request_mmf.category', 'MMF30')
                ->whereIn('request_mmf.requestStatus', [3]);
            
            if ($startDate && $endDate) {
                $query->whereBetween('request_mmf.created_at', [$startDate, $endDate]);
            }
            
            $data = $query->orderBy('created_at', 'desc')->with(['user'])->get();
         
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
            $requestData['createdby'] = $this->getEmployeeID()->id;
            $requestData['employee_id'] = $this->getEmployeeID()->id;
            $requestData['category'] = $this->submodulename;
            $requestData['bu'] = $this->getEmployeeID()->companycode;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;
            $detailData['req_id'] = $req_id;
            $newData->detailAdvance()->create($detailData);

            $this->createApprManager($requestData['depthead_id'], $this->submodulename, $req_id);

            $newData = $this->model->with('detailAdvance')->find($newData->id);
            
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
                'request_financial.*',
                'codes.code'
                )
                ->leftJoin('codes','request_financial.code_id','codes.id')
                ->where('request_financial.id',$id)
                ->with('detailAdvance')
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode('Advance');
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
            $data = $this->model->with('detailAdvance')->findOrFail($id);
            $reqStatus = $data->requestStatus;
            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            $this->addOneDayToDate($requestData);

            // $data->update($requestData);
            
            if (isset($requestData['detail_advance'])) {
                $detailData = $requestData['detail_advance'];
                
                // dd($data);
                // Cari detail berdasarkan ID, jika ada
                $detail = $data->detailAdvance;
                if ($detail) {
                    if(isset($detailData['AdvanceForm'])) {
                        if($detailData['AdvanceForm'] !== 2) {
                            $detail->OpsCategory = null;
                        }
                    }
                    $detail->update($detailData);
                } else {
                    // Jika detail tidak ditemukan, tambahkan data baru
                    $detailData['req_id'] = $data->id;
                    $data->detailAdvance()->create($detailData);
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
        
        $subimissionDate = ($originatorApproval) ? $originatorApproval->created_at : $data->created_at; // time originator submitted submission
        
        $dataDetails = DB::table('request_mmf_30_detail')->select('*')->where('mmf30_id',$data->detail30->id)->get(); // data detail
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
                $Worksheet->Range("I8")->Value = (($data->detail30->RequisitionType == 5)?$data->detail30->RequisitionOther:'');

                $Worksheet->Range("C9")->Value = $data->code->code;
                $Worksheet->Range("E9")->Value = $subimissionDate->format('Y-m-d');
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
