<?php

namespace App\Http\Controllers\Submission\Financial\Ccm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Submission\Financial\Ccm;
use App\Models\Submission\Financial\CcmDetail;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\User;
use DB;

class CcmRequestController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Ccm();
        $this->modulename = 'Ccm';
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
            where l.ApprovalAction='1' and l.req_id = request_ccm.id and l.module_id = '".$module_id."' and request_ccm.requestStatus='1'
            order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_ccm.*,codes.code,
                    CASE WHEN request_ccm.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_ccm.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin) {
                            if ($isAdmin) {
                                $query->where("request_ccm.user_id", "!=", $user_id)
                                    ->whereIn("request_ccm.requestStatus", [1,3,4]);
                            }
                        })
                        ->orWhere("request_ccm.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_ccm.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_ccm.created_at desc")
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

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $getListFR = DB::table('reference.tbl_fund_request_ccm')->get();
            foreach ($getListFR as $fundRequest) {
                $CcmDetail = new CcmDetail;
                $CcmDetail->req_id = $req_id;
                $CcmDetail->expenses = $fundRequest->expenses;
                $CcmDetail->save();
            }
            // $this->createApprover($this->modulename, $req_id, null, null);
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

            $data = $this->model->select('request_ccm.*','codes.code')
            ->leftJoin('codes','request_ccm.code_id','codes.id')
            ->where('request_ccm.id',$id)
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
            ($data->ticketStatus == null) ? $requestData['ticketStatus'] = 'On Queue' : $request->ticketStatus;
            $data->update($requestData);

            //start save history perubahan
            $fields = [
                'ticketStatus' => $request->ticketStatus,
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

    public function checkBalance($id)
    {
        try {

            // Fetch the CCM data by ID
            $ccmData = $this->model->find($id);
            // Calculate the sum of the expenses_amount column from CcmDetail for the given req_id
            $sumExpenses = CcmDetail::where('req_id', $id)->sum('expenses_amount');

            // Compare the ccmData->amount with the sum of expenses_amount
            if ($ccmData->payment_to_amount != $sumExpenses) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The balance does not match: CCM amount (' . number_format($ccmData->payment_to_amount, 2, '.', ',') . ') vs Expenses sum (' . number_format($sumExpenses, 2, '.', ',') . ').'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'The balance matches.'
            ]);
            

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
