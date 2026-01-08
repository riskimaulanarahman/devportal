<?php

namespace App\Http\Controllers\Submission\Financial\Capex;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Submission\Financial\Capex;
use App\Models\Submission\Financial\CapexDetail;
use App\Models\Submission\Financial\CapexJustification;
use App\Models\Submission\Financial\CapexQuestion;
use App\Models\Submission\Financial\CapexQuestionCF;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use App\Models\CategoryForm;
use App\Models\Employee;
use DB;
use COM;

class CapexRequestController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Capex();
        $this->modulename = 'Capex';
        $this->module = new Module();
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
            where l.ApprovalAction='1' and l.req_id = request_capex.id and l.module_id = '".$module_id."' and request_capex.requestStatus='1'
            order by a.sequence)";

            $checker = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id 
            where l.req_id = request_capex.id and l.module_id = '".$module_id."' and r.ApprovalType='1st Level Checker' and r.isactive='1'
            order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_capex.*,codes.code,
                    CASE WHEN request_capex.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe,
                    ".$checker." as isChecker
                ")
                ->leftJoin('codes','request_capex.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $checker) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $checker) {
                            if ($isAdmin || $checker) {
                                $query->where("request_capex.user_id", "!=", $user_id)
                                    ->whereIn("request_capex.requestStatus", [1,3,4]);
                            }
                        })
                        ->orWhere("request_capex.user_id", $user_id);
                })
                // ->orderBy("request_capex.requestStatus","asc")
                ->orderByRaw("CASE WHEN request_capex.user_id = '".$user_id."' THEN 0 ELSE 1 END")
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("request_capex.requestStatus asc")
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
        DB::beginTransaction(); // Memulai transaksi
        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['requestStatus'] = 0;
            $requestData['business_type'] = 'Fiber';

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $getList = DB::table('reference.capex_question')->get();
            foreach ($getList as $list) {
                $detail = new CapexQuestion;
                $detail->req_id = $req_id;
                $detail->question_id = $list->id;
                $detail->save();
            }
            $justification = new CapexJustification;
            $justification->req_id = $req_id;
            $justification->save();

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

            $data = $this->model->select('request_capex.*','codes.code')
            ->leftJoin('codes','request_capex.code_id','codes.id')
            ->where('request_capex.id',$id)
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

            // Mengambil semua data dari request
            $module_id = $this->getModuleId($this->modulename);
            $requestData = $request->all();

            
            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            if($request->additional_approver) {
                $this->createApprAdditionalApprover($request->additional_approver, $this->modulename, $id);
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
                    CapexJustification::where('req_id', $id)
                        ->delete();
                    CapexQuestion::where('req_id', $id)
                        ->delete();
                    CapexQuestionCF::where('req_id', $id)
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

    public function checkData($id)
    {
        DB::beginTransaction();
        try {

            // Fetch the data by ID
            $data = $this->model->find($id); 
            $selectedCategory = null;
            $prevCategory = null;

            //////////////////////////////////////////////////////////

            // Calculate the sum of the total column from CapexDetail for the given req_id
            $sumDetail = CapexDetail::where('req_id', $id)->sum('subtotal');
            if(!$sumDetail) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Expenditur Items Details Not Found'
                ]);
            }

            // Compare the data->amount with the sum of total
            // ($sumDetail + additionalBudget) <= approved_budget
            $balance = $sumDetail;
            $budget = $data->approved_budget + $data->additional_budget;

            if(is_null($data->approved_budget)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Approved Budget data not found.'
                ]);
            }

            if ($balance > $budget) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'The total expenditure (' . number_format($balance, 0, '.', ',') . ') exceeds the Budget (' . number_format($budget, 0, '.', ',') . ').'
                ]);
            }

            //////////////////////////////////////////////////////////

            $justificationDetail = CapexJustification::where('req_id', $id)->first(); // Ambil detailnya
            // Periksa apakah data ditemukan
            if (!$justificationDetail) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Justifications data not found.'
                ]);
            }

            // Periksa apakah ada kolom yang null
            foreach ($justificationDetail->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Data Justifications must be filled in, make sure there is no null data."
                    ]);
                }
            }
            
            //////////////////////////////////////////////////////////

            $questionData = CapexQuestion::where('req_id', $id)->get();
            // Validasi
            $errors = [];
            foreach ($questionData as $question) {
                $question_id = $question->question_id;
                $answer = $question->answer;

                // Validasi untuk question_id 1-9
                if ($question_id >= 1 && $question_id <= 7) {
                    if (is_null($answer)) {
                        // $errors[] = "Answer for question_id $question_id is required.";
                        $errors[] = "You have not finished filling in the 'Answer' to question sequence A.";
                    }
                    if (is_null($question->remarks)) {
                        // $errors[] = "Remarks for question_id $question_id is required.";
                        $errors[] = "You have not finished filling in the 'Remarks' to question sequence A.";
                    }
                }

                if ($question_id >= 8 && $question_id <= 9) {
                    if (is_null($answer)) {
                        // $errors[] = "Answer for question_id $question_id is required.";
                        $errors[] = "You have not finished filling in the 'Answer' to question sequence B/C.";
                    }
                }

                // Validasi untuk question_id 9
                if ($question_id == 9) {
                    if ($answer === 'Yes') {
                        // question_id 10-11 required
                        foreach ([10, 11] as $idseq) {
                            $relatedQuestion = $questionData->where('question_id', $idseq)->first();
                            if ($relatedQuestion && is_null($relatedQuestion->answer)) {
                                // $errors[] = "Answer for question_id $idseq is required when question_id 9 is 'yes'.";
                                $errors[] = "You have not finished filling in the 'Answer' to question sequence D.";
                            }
                        }
                    } elseif ($answer === 'No') {
                        // question_id 12-19 required
                        foreach (range(12, 19) as $idseq) {
                            $relatedQuestion = $questionData->where('question_id', $idseq)->first();
                            if ($relatedQuestion && is_null($relatedQuestion->answer)) {
                                // $errors[] = "Answer for question_id $idseq is required when question_id 9 is 'no'.";
                                $errors[] = "You have not finished filling in the 'Answer' to question sequence E/F.";
                            }
                        }
                    }
                }

                // Validasi untuk question_id 20-22
                if ($question_id >= 20 && $question_id <= 22 && is_null($answer)) {
                    // $errors[] = "Answer for question_id $question_id is required.";
                    $errors[] = "You have not finished filling in the 'Answer' to question sequence G.";
                }

                if ($question_id >= 2 && $question_id <= 3) {
                    if($answer === 'Yes') {
                        foreach (range(23, 24) as $idseq) {
                            $relatedQuestion = $questionData->where('question_id', $idseq)->first();
                            if ($relatedQuestion && is_null($relatedQuestion->answer)) {
                                $errors[] = "Sequence (H) is required when Sequence (A) - Question 2 & 3 is 'yes'";
                            }
                        }
                    }
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => $errors
                ]);
            }

            //////////////////////////////////////////////////////////

            $refCategories = CategoryForm::where('module_id',$this->getModuleId($this->modulename))
            ->where('isActive',1)
            ->orderBy('amount', 'asc') // Add this to process higher amounts first
            ->get();

            foreach ($refCategories as $refCategory) {
                $parts = explode(" - ", $refCategory->nameCategory);
                $formType = $parts[0] ?? '';
                $requestType = $parts[1] ?? '';

                // Skip jika form_type atau request_type tidak cocok
                if ($formType !== $data->form_type || $requestType !== $data->request_type) {
                    continue;
                }

                // Jika approved_budget >= amount kategori ini, simpan sebagai calon
                // if ($data->approved_budget >= $refCategory->amount) {$balance
                if ($balance >= $refCategory->amount) {
                    $prevCategory = $refCategory;
                } 
                // Jika approved_budget < amount kategori ini, gunakan kategori sebelumnya (jika ada)
                else {
                    $selectedCategory = $prevCategory;
                    break;
                }
            }

            // Jika tidak ada yang lebih kecil, gunakan kategori terakhir
            if (!$selectedCategory && $prevCategory) {
                $selectedCategory = $prevCategory;
            }

            // dd($selectedCategory->id);

            // Update kategori jika ditemukan
            if ($selectedCategory) {
                $data->category_id = $selectedCategory->id;
                $data->save();
            }

            /////////////////////////////////////////////////////////

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ready for Submission.'
            ]);
            

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function submitDate($id) {
       $data =  $this->model->select('request_capex.*','users.username')
                    ->leftJoin('users','request_capex.user_id','users.id')
                    ->where('request_capex.id',$id)
                    ->with(['code','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = $originatorApproval->created_at; // time originator submitted submission
        
        return $subimissionDate;
    }

    public function genPdfCapex(Request $request, $id) {
        $data =  $this->model->select('request_capex.*','users.username')
                    ->leftJoin('users','request_capex.user_id','users.id')
                    ->where('request_capex.id',$id)
                    ->with(['code','approverHistory'])
                    ->first(); // data submission

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = $originatorApproval->created_at; // time originator submitted submission

        $dataDetails = DB::table('request_capex_detail')->select('*')->where('req_id',$id)->get(); // data detail
        $amountSumDetail = $dataDetails->sum('subtotal');
        $dataCFDetails = DB::table('request_capex_question_cashflow')->select('*')->where('req_id',$id)->get(); // data detail cash flow
        $dataJustifications = DB::table('request_capex_justification')->select('*')->where('req_id',$id)->first(); // data detail
        $dataQuestions = DB::table('request_capex_question')->select('*')->where('req_id',$id)->get(); // data question
        $emp = Employee::select('*')->with(['location','company'])->where('LoginName',$data->username)->first(); // data employee
        $exchangeRate = DB::table('reference.tbl_exchangeRate')->select('*')->first(); // data exchange Rate
        $dataAppr = DB::table('vwCapexApprover')->select('*')->where('id',$id)->orderBy('sequence')->get(); // data approver
        $getApprExt = DB::table('reference.capex_apprExt')->select('*')->where('amount','>=',0)->where('amount','<=',$amountSumDetail)->where('category',$data->form_type.' - '.$data->request_type)->orderBy('sequence')->get();
        // Gabungkan kedua collection dan urutkan berdasarkan sequence
        $mergedApprovers = $dataAppr->merge($getApprExt)->sortBy('sequence');

        // Jika Anda perlu mengubahnya kembali menjadi array
        $resultApprovers = $mergedApprovers->values()->all();
        
        // dd($resultApprovers);
        // dd($data->form_type.' - '.$data->request_type);

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/finance/capex/formcapex.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet2 = $Workbook->Worksheets(2);
			$Worksheet3 = $Workbook->Worksheets(3);
			$Worksheet->Activate;
			$Worksheet2->Activate;
			$Worksheet3->Activate;

            // Start Form Data (1)
                $Worksheet->Range("B3")->Value = $data->code->code;
                $Worksheet->Range("G3")->Value = $subimissionDate->format('Y-m-d');
                $Worksheet->Range("B6")->Value = $emp->FullName;
                $Worksheet->Range("G9")->Value = 'KF-'.$data->bu;
                $Worksheet->Range("G12")->Value = $data->bu;
                $Worksheet->Range("B15")->Value = $data->bu.'-'.$data->estate;
                $Worksheet->Range("G15")->Value = $data->equipment;
                $Worksheet->Range("B18")->Value = $data->cost_center;
                $Worksheet->Range("G18")->Value = $data->form_type;
                $Worksheet->Range("B21")->Value = $data->project_type;
                $Worksheet->Range("G21")->Value = $data->request_type;
                $Worksheet->Range("B24")->Value = $data->title;
                $Worksheet->Range("B27")->Value = ($data->request_type !== 'Budgeted') ? $data->reason_unbudgeted : '';

                $Worksheet->Range("B34")->Value = $exchangeRate->rate;

                $Worksheet->Range("B42")->Value = $amountSumDetail;
                $Worksheet->Range("B45")->Value = ($data->request_type == 'Unbudgeted Swap Available') ? $data->approved_budget+$data->additional_budget : $data->approved_budget;
                
                $Worksheet->Range("B53")->Value = $dataJustifications->justification_1;
                $Worksheet->Range("B58")->Value = $dataJustifications->justification_2;
                $Worksheet->Range("B63")->Value = $dataJustifications->justification_3;
                $Worksheet->Range("B68")->Value = $dataJustifications->justification_4;
                $Worksheet->Range("B73")->Value = $dataJustifications->justification_5;
                $Worksheet->Range("B78")->Value = $dataJustifications->justification_6;
                $Worksheet->Range("B83")->Value = $dataJustifications->justification_7;
                $Worksheet->Range("B88")->Value = $dataJustifications->justification_8;

                foreach ($dataQuestions as $question) {
                    if($question->question_id == 1) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K6")->Value = 'X' : $Worksheet2->Range("M6")->Value = 'X';
                        $Worksheet2->Range("O5")->Value = $question->remarks;
                    }
                    if($question->question_id == 2) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K9")->Value = 'X' : $Worksheet2->Range("M9")->Value = 'X';
                        $Worksheet2->Range("O8")->Value = $question->remarks;
                    }
                    if($question->question_id == 3) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K12")->Value = 'X' : $Worksheet2->Range("M12")->Value = 'X';
                        $Worksheet2->Range("O11")->Value = $question->remarks;
                    }
                    if($question->question_id == 4) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K15")->Value = 'X' : $Worksheet2->Range("M15")->Value = 'X';
                        $Worksheet2->Range("O14")->Value = $question->remarks;
                    }
                    if($question->question_id == 5) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K18")->Value = 'X' : $Worksheet2->Range("M18")->Value = 'X';
                        $Worksheet2->Range("O17")->Value = $question->remarks;
                    }
                    if($question->question_id == 6) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K21")->Value = 'X' : $Worksheet2->Range("M21")->Value = 'X';
                        $Worksheet2->Range("O20")->Value = $question->remarks;
                    }
                    if($question->question_id == 7) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K24")->Value = 'X' : $Worksheet2->Range("M24")->Value = 'X';
                        $Worksheet2->Range("O23")->Value = $question->remarks;
                    }
                    if($question->question_id == 8) {
                        // Reset semua sel
                        $Worksheet2->Range("L27")->Value = null;
                        $Worksheet2->Range("O27")->Value = null;
                        $Worksheet2->Range("R27")->Value = null;

                        // Ternary untuk menentukan sel mana yang dapat 'X'
                        if ($question->answer == 'Must Have') {
                            $Worksheet2->Range("L27")->Value = 'X';
                        } elseif ($question->answer == 'Need to Have') {
                            $Worksheet2->Range("O27")->Value = 'X';
                        } else {
                            $Worksheet2->Range("R27")->Value = 'X';
                        }
                        // ($question->answer == 'Must Have') ? $Worksheet2->Range("L27")->Value = 'X' : ($question->answer == 'Need to Have') ? $Worksheet2->Range("O27")->Value = 'X' : $Worksheet2->Range("R27")->Value = 'X';
                    }
                    if($question->question_id == 9) {
                        ($question->answer == 'Yes') ? $Worksheet2->Range("K32")->Value = 'X' : $Worksheet2->Range("M32")->Value = 'X';
                    }
                    if($question->question_id == 10) {
                        $Worksheet2->Range("K34")->Value = $question->answer;
                    }
                    if($question->question_id == 11) {
                        $Worksheet2->Range("K37")->Value = $question->answer;
                    }
                    if($question->question_id == 12) {
                        $Worksheet2->Range("K40")->Value = $question->answer;
                    }
                    if($question->question_id == 13) {
                        $Worksheet2->Range("K42")->Value = $question->answer;
                    }
                    if($question->question_id == 14) {
                        $Worksheet2->Range("K44")->Value = $question->answer;
                    }

                    if($question->question_id == 15) {
                        $Worksheet2->Range("K47")->Value = $question->answer;
                    }
                    if($question->question_id == 16) {
                        $Worksheet2->Range("K49")->Value = $question->answer;
                    }
                    if($question->question_id == 17) {
                        $Worksheet2->Range("K51")->Value = $question->answer;
                    }
                    if($question->question_id == 18) {
                        $Worksheet2->Range("K53")->Value = $question->answer;
                    }
                    if($question->question_id == 19) {
                        $Worksheet2->Range("K55")->Value = $question->answer;
                    }

                    if($question->question_id == 20) {
                        $date = new \DateTime($question->answer);
                        $Worksheet2->Range("O58")->Value = $date->format('d-m-y');
                    }
                    if($question->question_id == 21) {
                        $date = new \DateTime($question->answer);
                        $Worksheet2->Range("O60")->Value = $date->format('d-m-y');
                    }
                    if($question->question_id == 22) {
                        $Worksheet2->Range("O62")->Value = $question->answer;
                    }
                    
                    if($question->question_id == 23) {
                        $Worksheet2->Range("K73")->Value = $question->answer;
                        $Worksheet2->Range("O71")->Value = $question->remarks;
                    }
                    if($question->question_id == 24) {
                        $Worksheet2->Range("O74")->Value = $question->answer;
                    }
                }
            

                // $Worksheet->Range("E7")->Value = $subimissionDate->format('Y-m-d');
                // $Worksheet->Range("E9")->Value = $emp->location->Location;
                // $Worksheet->Range("E13")->Value = $emp->CostCenter;
                // $Worksheet->Range("F15")->Value = $data->Location;


            // End Form Data

            

            // signature originator
            // $Worksheet->Range("H36")->Value = $emp->FullName;
            // $Worksheet->Range("H37")->Value = $subimissionDate->format('Y-m-d');;
            // addPictureToWorksheet($Worksheet, $picpath, 33, 8, 35, $excel);
            
            // signature approver
           

            $xlShiftDown=-4121;
				$no = 1;
				for ($a=38;$a<38+count($dataDetails);$a++){
					$Worksheet->Rows($a+1)->Copy();
					$Worksheet->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet->Range("B".$a)->Value = $dataDetails[$a-38]->expenditure_item;
					$Worksheet->Range("C".$a)->Value = $dataDetails[$a-38]->quantity;
					$Worksheet->Range("D".$a)->Value = $dataDetails[$a-38]->amount;
				}
                for ($a=67;$a<67+count($dataCFDetails);$a++){
					$Worksheet2->Rows($a+1)->Copy();
					$Worksheet2->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet2->Range("C".$a)->Value = $dataCFDetails[$a-67]->year;
					$Worksheet2->Range("D".$a)->Value = $dataCFDetails[$a-67]->q1;
					$Worksheet2->Range("F".$a)->Value = $dataCFDetails[$a-67]->q2;
					$Worksheet2->Range("H".$a)->Value = $dataCFDetails[$a-67]->q3;
					$Worksheet2->Range("J".$a)->Value = $dataCFDetails[$a-67]->q4;
				}
                // foreach ($dataAppr as $appr) {
                //     if($appr->approvalAction == 3) {
                //         $Worksheet->Range("N36")->Value = $appr->apprname;
                //         $Worksheet->Range("N37")->Value = $appr->approvalDate;
                //         addPictureToWorksheet($Worksheet, $picpath, 33, 14, 35, $excel);
                //     }
                // }
                $picpath = public_path("assets/images/approved.png");
            
                function addPictureToWorksheet($Worksheet3, $picPath, $row, $column, $height, $excel) {
                    $pic = $Worksheet3->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                    $pic->Height = $height;
                    $pic->Top = $excel->Cells($row, $column)->Top;
                    $pic->Left = $excel->Cells($row, $column)->Left;
                }

                for ($a=3;$a<3+count($resultApprovers);$a++){
					$Worksheet3->Rows($a+1)->Copy();
					$Worksheet3->Rows($a+1)->Insert($xlShiftDown);
					$Worksheet3->Range("B".$a)->Value = $resultApprovers[$a-3]->apprname;
					$Worksheet3->Range("C".$a)->Value = $resultApprovers[$a-3]->apprtype;
					$Worksheet3->Range("D".$a)->Value = $resultApprovers[$a-3]->approvalDate;
                    if($resultApprovers[$a-3]->approvalAction == 3) {
                        addPictureToWorksheet($Worksheet3, $picpath, $a, 5, 35, $excel);
                    }
				}
       
            // $Worksheet->Columns("E")->AutoFit();

            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/finance/capex/pdf/' . $fileName);
			$path = $filePath;
			if (file_exists($path)) {
				unlink($path);
			}
            $Worksheet->Select();
            $Worksheet2->Select(false);
            $Worksheet3->Select(false);

			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
            $pathfilename = 'public/template/finance/capex/pdf/' . $fileName;

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
