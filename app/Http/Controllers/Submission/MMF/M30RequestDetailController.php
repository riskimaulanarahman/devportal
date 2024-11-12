<?php

namespace App\Http\Controllers\Submission\MMF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Submission\MMF\Mmf30detail;

class M30RequestDetailController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Mmf30detail();
        $this->modulename = 'Mmf';
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
        try {

            $requestData = $request->all();
            // $requestData['module_id'] = $this->getModuleId($request->modulename);
            $requestData['mmf30_id'] = $request->req_id;

            // $this->addOneDayToDate($requestData);

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
                $data = $this->model->where('mmf30_id',$id)->get();
                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);
            } else {
                return response()->json(["status" => "show", "message" => $this->getMessage()['errornotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();

            // $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            if(isset($request->UnitPrice)) {
                $requestData['ExtendedPrice'] = $request->UnitPrice*$data->Qty;
            }
            if(isset($request->Qty)) {
                $requestData['ExtendedPrice'] = $data->UnitPrice*$request->Qty;
            }

            $data->update($requestData);

            //start save history perubahan
            // $fields = [
            //     // '' => $request->,
            // ];
            
            // foreach ($fields as $key => $value) {
            //     if ($value) {
            //         $this->approverAction($this->modulename, $data->mmf30_id, $key, 1, $value, null);
            //     }
            // }
            //end save history perubahan

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
