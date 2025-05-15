<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\MemorandumHis;
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

    public function getList($sysid, $modulename)
    {
        try {
            $module = $this->module
                ->select('id', 'module')
                ->where('module', $modulename)
                ->first();

            if (!$module) {
                return response()->json([
                    "status" => "show",
                    "message" => "Module not found for modulename: $modulename."
                ]);
            }

            // Mengambil data berdasarkan sysid saja
            $data = MemorandumHis::where('sysid', $sysid)->get();

            return response()->json([
                "status" => "show",
                "message" => $this->getMessage()['show'],
                "data" => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
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
