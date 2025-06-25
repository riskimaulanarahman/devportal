<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Submission\MemorandumHis;
use App\Models\Useraccess;
use App\Models\MemorandumReq;
use App\Models\Submission\MemorandumReq as SubmissionMemorandumReq;
use Illuminate\Http\Request;
use DB;

class ContractHistoryController extends Controller
{
    
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new MemorandumHis();
        $this->modulename = 'Memo';
        $this->codename = 'Memo';
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
                and l.req_id = request_memorandum_his.id and l.module_id = '".$module_id."'
                and request_memorandum_his.requestStatus='1'
                order by a.sequence)";

            $data = $dataquery
                ->selectRaw("request_memorandum_his.*,codes.code,
                    CASE WHEN request_memorandum_his.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_memorandum_his.code_id','codes.id')
                ->with(['user','approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin) {
                            if ($isAdmin) {
                                $query->where("request_memorandum_his.user_id", "!=", $user_id)
                                    ->whereIn("request_memorandum_his.requestStatus", [1,3,4]);
                            } else {
                                $query->where("request_memorandum_his.user_id", "!=", $user_id)
                                    ->whereIn("request_memorandum_his.requestStatus", [3])
                                    ->where("bu",$this->getEmployeeID()->companycode);
                            }
                        })
                        ->orWhere("request_memorandum_his.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                // ->orderByRaw("CASE WHEN request_memorandum_his.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_memorandum_his.submitDate desc")
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
            $request->validate([
                'startContract' => 'required|date',
                'endContract' => 'required|date|after_or_equal:startContract',
                'remarks' => 'required|string',
                'superiorName' => 'required|string',
                'sequence' => 'required|integer',
            ]);

            $code_id = $this->generateCode($this->modulename);
            $requestData = $request->all();
            $getme = DB::table('memoExp')
                ->where('id', $requestData['req_id'])
                ->select('sys_id', 'bu')
                ->first(); // Gunakan first() agar bisa mengambil lebih dari satu kolom
            $requestData['req_id'] = $requestData['req_id'] ?? DB::table('request_memorandum')
            ->orderBy('id', 'desc')
            ->value('id');
            
            $requestData['sysid'] = $getme->sys_id ?? null; // Beri nilai default jika null
            $requestData['bu'] = $getme->bu ?? null;
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['requestStatus'] = 0;
            $requestData['code_id'] = $code_id;
            $this->model->create($requestData);
            DB::table('request_memorandum')
            ->where('id', $requestData['req_id'])
            ->update(['requestStatus' => 0]);
            
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $data = $this->model
                ->select('request_memorandum_his.*', 'codes.code')                
                ->leftJoin('codes', 'request_memorandum_his.code_id', '=', 'codes.id')
                ->where('request_memorandum_his.id', $id)
                ->with(['user'])
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan untuk ID: ' . $id
                ]);
            }

            if ($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename);
                $data->save();
            }

            return response()->json([
                'status' => 'show',
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    public function getList($id, $modulename)
    {
        try {
            $user_id = $this->getAuth()->id;
            $module = $this->module->select('id', 'module')->where('module', $modulename)->first();      
            $data = $this->model->selectRaw("
                    request_memorandum_his.*, 
                    codes.code,
                    CAST(CASE WHEN request_memorandum_his.user_id = ? THEN 1 ELSE 0 END AS INT) AS isMine,
                    COALESCE((
                        SELECT TOP 1 CAST(CASE WHEN a.user_id = ? THEN 1 ELSE 0 END AS INT)
                        FROM tbl_approverListReq l
                        LEFT JOIN tbl_approver a ON l.approver_id = a.id
                        WHERE l.req_id = request_memorandum_his.id 
                        AND l.module_id = ? 
                        AND request_memorandum_his.requestStatus = '1'
                        ORDER BY a.sequence
                    ), 0) AS isPendingOnMe
                ", [$user_id, $user_id, $module->id]) 
                ->leftJoin('codes', 'request_memorandum_his.code_id', '=', 'codes.id')
                ->with(['user', 'approverlist'])
                ->where('req_id', $id)
                ->orderBy('sequence', 'DESC')
                ->get();
                // dd($data);
                return response()->json([
                    "status" => "show", 
                    "message" => $this->getMessage()['show'], 
                    "data" => $data
                ]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        // DB::beginTransaction();

        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $requestData['user_id'] = $this->getAuth()->id;
            $data->update($requestData);

            // DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $data // Sertakan data yang telah diperbarui
            ]);

        } catch (\Exception $e) {
            // DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Cari data berdasarkan ID
            $data = $this->model->findOrFail($id);
            // $reqId = $data->req_id;

            // Simpan data untuk response sebelum dihapus
            $deletedData = $data->toArray();

            // Hapus data
            $data->delete();
            // Cek apakah masih ada data his yang tersisa untuk req_id yang sama
            // $remaining = DB::table('request_memorandum_his')
            //     ->where('req_id', $reqId)
            //     ->exists();

            // Kalau tidak ada yang tersisa, kembalikan status ke 1 (atau value default kamu)
            // if (!$remaining) {
            //     DB::table('request_memorandum')
            //         ->where('id', $reqId)
            //         ->update(['requestStatus' => 1]); // ganti dengan default status kalau bukan 1
            // }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['destroy'],
                "data" => $deletedData // Sertakan data yang telah dihapus
            ]);

        } catch (\Exception $e) {
            // Rollback jika terjadi error
            DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}