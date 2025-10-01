<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Models\Useraccess;
use App\Models\WphcDetail;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class WphcDetailController extends Controller
{
    
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new WphcDetail();
        $this->modulename = 'Wphc';
        $this->codename = 'Wphc';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        $data = WphcDetail::get();

        return response()->json([
            'status' => "show",
            'message' => $this->getMessage()['show'],
            'data' => $data
        ])->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function store(Request $request)
    {
        
        DB::beginTransaction();

        try {

            $requestData = $request->all();

            $requestData['user_id']   = $this->getAuth()->id;

            if ($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $request->req_id);
            }

            $this->model->create($requestData);

            DB::commit();

            return response()->json([
                "status"  => "success",
                "message" => $this->getMessage()['store']
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Tambahkan rollback agar transaksi aman
            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function show($req_id)
    {
        try {
            $data = $this->model
                ->select('request_wphc_detail.*')                
                ->where('request_wphc_detail.req_id', $req_id)
                ->first();
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan untuk ID: ' . $req_id
                ]);
            }
            if ($data->code_id == null) {
            // Buat kode manual tanpa generateCode()
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
           $data = WphcDetail::select('*')
            ->where('req_id', $id)
            ->get();
            
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

            if ($request->superior_id) {
                $this->createApprSuperiorDepthead($request->superior_id, $this->modulename, $data->req_id);
            }

            // Pastikan relasi Wphc tersedia
            $requestStatus = optional($data->Wphc)->requestStatus;

            if (empty($data->approveddoc) && in_array($requestStatus, [0, 2])) {
                $data->update($requestData);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => $this->getMessage()['nothaveaccess']
                ]);
            }

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['update'],
                "data" => $data
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

            // if(isset($data->approveddoc) || in_array($data->Wphc->requestStatus, [1])) {
            //     return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            // }

            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}