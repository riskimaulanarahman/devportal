<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\MemorandumHis;
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
        $this->modulename = 'MemorandumHis';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index()
    {
        try {

            $data = $this->model->all();

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {


        try {
            $employeeid = $this->getEmployeeID()->id;
            $requestData = $request->all();
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['sysid'] = $this->getEmployeeID()->sys_id;
            $requestData['requestStatus'] = $this->getEmployeeID()->requestStatus;
            $newData = $this->model->create($requestData);
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
        //
    }

    public function getList($id, $reqid)
    {
        try {
            // Ambil sysid dan sequence berdasarkan reqid yang dipilih
            $historyData = DB::table('request_memorandum_his')
                ->select('sysid', 'sequence')
                ->where('id', $id)
                ->first();

            if (!$historyData) {
                return response()->json(["status" => "error", "message" => "Data sejarah tidak ditemukan"], 404);
            }

            $selected_sequence = $historyData->sequence;
            $sysid = $historyData->sysid;

            // Validasi sequence agar tidak null
            if (is_null($selected_sequence)) {
                return response()->json(["status" => "error", "message" => "Sequence tidak ditemukan"], 404);
            }

            // Ambil data memorandumHistories berdasarkan sequence dan sysid
            $memorandumHistories = DB::table('request_memorandum_his')
                ->selectRaw("
                    MIN(id) AS reqid, 
                    sequence, 
                    startContract,
                    endContract,
                    approveddoc,
                    created_at,
                    remarks,
                    sysid,
                    id
                ")
                ->where('sysid', $sysid)
                ->where('sequence', '<=', $selected_sequence)
                ->groupBy('sequence', 'sysid', 'startContract', 'endContract', 'approveddoc', 'created_at', 'remarks', 'id')
                ->orderBy('sequence', 'DESC')
                ->get();

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $memorandumHistories
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

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
            $data->update($requestData);

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
        DB::beginTransaction();

        try {
            // Cari data berdasarkan ID
            $data = $this->model->findOrFail($id);

            // Simpan data untuk response sebelum dihapus
            $deletedData = $data->toArray();

            // Hapus data
            $data->delete();

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