<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Submission\MemorandumDetail;
use App\Models\Useraccess;
use Illuminate\Http\Request;
use DB;

class MemorandumDetailController extends Controller
{
    
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new MemorandumDetail();
        $this->modulename = 'Memorandum';
        $this->codename = 'ContractEmp';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'superior_id' => 'required|integer',
                'startContract' => 'required|date',
                'endContract' => 'required|date|after_or_equal:startContract',
            ]);

            $code_id = $this->generateCode($this->codename);
            $requestData = $request->all();
            $getme = DB::table('memoExp')
                ->where('id', $requestData['req_id'])
                ->select('sys_id', 'bu')
                ->first();

            $getMaxSequence = MemorandumDetail::where('req_id',$requestData['req_id'])->orderBy('sequence','desc')->value('sequence');

            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['sequence'] = $getMaxSequence+1;
            $requestData['code_id'] = $code_id;

            if($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $request->req_id);
            }

            $checkdata = $this->model->where('req_id',$request->req_id)->whereNull('approveddoc')->count();

            if($checkdata > 0) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['contractexist']]);
            }

            // $data = MemorandumDetail::where('req_id', $request->req_id)->first();
            // if (!$data) {
            //     return response()->json([
            //         "status" => "error",
            //         "message" => "Data memorandum detail tidak ditemukan."
            //     ]);
            // }

            // if (!$data->Memorandum) {
            //     return response()->json([
            //         "status" => "error",
            //         "message" => "Relasi ke Memorandum tidak tersedia atau belum dimuat."
            //     ]);
            // }

            // if (in_array($data->Memorandum->requestStatus, [1, 4])) {
            //     return response()->json([
            //         "status" => "error",
            //         "message" => $this->getMessage()['nothaveaccess']
            //     ]);
            // }

            // $data = MemorandumDetail::where('req_id',$request->req_id)->first();
            // if(in_array($data->Memorandum->requestStatus, [1, 4])) {
            //     return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            // }
            
            $this->model->create($requestData);

            DB::commit();
            
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $data = $this->model
                ->select('request_memorandum_detail.*', 'codes.code')                
                ->leftJoin('codes', 'request_memorandum_detail.code_id', '=', 'codes.id')
                ->where('request_memorandum_detail.id', $id)
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
            $data = $this->model->selectRaw("
                    request_memorandum_detail.*, 
                    codes.code,
                    CAST(CASE WHEN request_memorandum_detail.user_id = ? THEN 1 ELSE 0 END AS INT) AS isMine,
                    COALESCE((
                        SELECT TOP 1 CAST(CASE WHEN a.user_id = ? THEN 1 ELSE 0 END AS INT)
                        FROM tbl_approverListReq l
                        LEFT JOIN tbl_approver a ON l.approver_id = a.id
                        WHERE l.req_id = request_memorandum_detail.id 
                        ORDER BY a.sequence
                    ), 0) AS isPendingOnMe
                ", [$user_id, $user_id]) 
                ->leftJoin('codes', 'request_memorandum_detail.code_id', '=', 'codes.id')
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
        DB::beginTransaction();

        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $requestData['user_id'] = $this->getAuth()->id;

            if($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $data->req_id);
            }
            
            if(empty($data->approveddoc) && ($data->Memorandum->requestStatus !== 1)) {
                $data->update($requestData);
            } else {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $data // Sertakan data yang telah diperbarui
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);

            if(isset($data->approveddoc) || in_array($data->Memorandum->requestStatus, [1])) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroyo($id)
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
            // $remaining = DB::table('request_memorandum_detail')
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