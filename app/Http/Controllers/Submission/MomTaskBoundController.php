<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Submission\MomTaskBound;
use App\Models\Submission\Mom;
use App\Models\Submission\MomTask;
use App\Models\Category;

class MomTaskBoundController extends Controller
{

    private $model;
    public $modulename;
    public $module;
    public $momtask;

    public function __construct()
    {
        $this->model = new MomTaskBound();
        $this->modulename = 'Mom';
        $this->module = new Module();
        $this->momtask = new MomTask();
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

            $getMom = $this->momtask->select('request_mom.*')
            ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
            ->leftJoin('request_mom','tbl_category.req_id','request_mom.id')
            ->where('request_momTask.id',$request->req_id)
            ->first();
            
            $requestData = $request->all();
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            
            $this->addOneDayToDate($requestData);
            if($this->getAuth()->id == $getMom->user_id || $this->getAuth()->id == $getMom->chairman_userid) {
                $this->model->create($requestData);
                return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);
            } else {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

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
                $data = $this->model->where('task_id',$id)
                ->get();
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

            $this->addOneDayToDate($requestData);

            $data = $this->model->findOrFail($id);

            $getMom = $this->momtask->select('request_mom.*')
            ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
            ->leftJoin('request_mom','tbl_category.req_id','request_mom.id')
            ->where('request_momTask.id',$data->task_id)
            ->first();
            if($this->getAuth()->id == $getMom->user_id || $this->getAuth()->id == $getMom->chairman_userid) {
                $data->update($requestData);
            } else {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);

            $getMom = $this->momtask->select('request_mom.*')
            ->leftJoin('tbl_category','request_momTask.category_id','tbl_category.id')
            ->leftJoin('request_mom','tbl_category.req_id','request_mom.id')
            ->where('request_momTask.id',$data->task_id)
            ->first();
            if($this->getAuth()->id == $getMom->user_id || $this->getAuth()->id == $getMom->chairman_userid) {
                $data->delete();
            } else {
                return response()->json(["status" => "error", "message" => $this->getMessage()['nothaveaccess']]);
            }

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
