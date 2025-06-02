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
        $this->modulename = 'Memorandum';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index()
    {
        try {

            $data = $this->model->all();
            // dd($data);

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function storex(Request $request)
    {
        try {

            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            // $requestData['approvalAction'] = 1;
            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {            
            $code_id = $this->generateCode($this->modulename);
            $user_id = auth()->id();
            $requestData = $request->all();
            $getsys = DB::table('memoExp')
                ->where('id', $requestData['req_id'])
                ->value('sys_id');

            $requestData['module_id'] = $this->getModuleId($request->modulename);
            $requestData['user_id'] = $user_id;
            $requestData['sysid'] = $getsys;
            $requestData['requestStatus'] = 0;
            $requestData['endContract'] = $request->input('endContract');
            $requestData['code_id'] = $code_id;
            $requestData['req_id'] = $request->input('req_id');
            $requestData['sequence'] = $request->input('sequence');
            $requestData['superiorName'] = $request->input('superiorName');
            $requestData['startContract'] = $request->input('startContract');
            $requestData['remarks'] = $request->input('remarks');

            $this->model->create($requestData);
            
            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function getList($id,$modulename)
    {
        try {
            $module = $this->module->select('id','module')->where('module',$modulename)->first();
            if($module) {
                $data = $this->model->select('request_memorandum_his.*', 'codes.code')
                ->leftJoin('codes','request_memorandum_his.code_id','codes.id')
                ->where('req_id',$id)
                ->orderBy('sequence', 'DESC')
                ->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            } else {
                return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function getLost($reqid, $modulename)
    {
        try {
            if (empty($reqid)) {
                return response()->json([
                    "status" => "show",
                    "message" => "Form baru dibuka, tidak ada data kontrak",
                    "data" => []
                ]);
            }
            $historyExists = $this->model->where('req_id', $reqid)->exists();
            if (!$historyExists) {
                return response()->json([
                    "status" => "show",
                    "message" => "Belum ada data kontrak",
                    "data" => []
                ]);
            }
            $moduleExists = $this->module->where('module', $modulename)->exists();        
            if (!$moduleExists) {
                return response()->json(["status" => "error", "message" => $this->getMessage()['errornotfound']]);
            }
            $historyData = $this->model->select('sysid', 'sequence')->where('req_id', $reqid)->first();
            $sysid = $historyData->sysid;
            $selected_sequence = $historyData->sequence;
            if (empty($sysid) || empty($selected_sequence)) {
                return response()->json([
                    "status" => "show",
                    "message" => "Belum ada data kontrak",
                    "data" => []
                ]);
            }
            $contractExists = $this->model
                ->where('sysid', $sysid)
                ->where('sequence', '<', $selected_sequence)
                ->exists();
            if (!$contractExists) {
                return response()->json([
                    "status" => "show",
                    "message" => "Belum ada kontrak",
                    "data" => []
                ]);
            }
            $contractList = $this->model
                ->where('sysid', $sysid)
                ->where('sequence', '<=', $selected_sequence)
                ->orderBy('sequence', 'DESC')
                ->get();
            return response()->json([
                "status" => "show",
                "message" => "List kontrak berhasil diambil",
                "data" => $contractList
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
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