<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\MemorandumHis;
use Illuminate\Http\Request;
use DB;

class ContractHistoryController extends Controller
{
    private $model;
    private $module;

    public function __construct()
    {
        $this->model = new MemorandumHis();
        $this->module = new Module();
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

        DB::beginTransaction();

        try {
            $requestData = $request->all();
            $requestData['user_id'] = $this->getAuth()->id;
            $newData = $this->model->create($requestData);
            DB::commit();
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

    public function getList($id, $modulename)
    {
        try {
            // Ambil module berdasarkan modulename
            $module = $this->module
                ->select('id', 'module')
                ->where('module', $modulename)
                ->first();

            if ($module) {
                $data = DB::table('request_memorandum_his')
                    ->where('req_id', $id)
                    ->get();

                return response()->json([
                    "status" => "show",
                    "message" => $this->getMessage()['show'],
                    "data" => $data
                ]);
            } else {
                return response()->json([
                    "status" => "show",
                    "message" => "Module not found for modulename: $modulename."
                ]);
            }
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
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id dari user yang sedang login
            $requestData['user_id'] = $this->getAuth()->id;

            // Cari data berdasarkan ID
            $data = $this->model->findOrFail($id);

            // Perbarui data
            $data->update($requestData);

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $data // Sertakan data yang telah diperbarui
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
