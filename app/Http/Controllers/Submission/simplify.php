<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Submission\MomTaskUpdate;
use App\Models\Submission\MomTaskBound;

class MomTaskUpdateController extends Controller
{
    private $model;
    private $module;
    private $taskbound;

    public function __construct(MomTaskUpdate $momTaskUpdate, Module $module, MomTaskBound $momTaskBound)
    {
        $this->model = $momTaskUpdate;
        $this->module = $module;
        $this->taskbound = $momTaskBound;
    }

    public function index()
    {
        return $this->tryCatch(function () {
            $data = $this->model->all();
            return $this->response('show', $data);
        });
    }

    public function store(Request $request)
    {
        return $this->tryCatch(function () use ($request) {
            $this->validateTaskBound($request->req_id);

            $requestData = array_merge($request->all(), [
                'module_id' => $this->module->where('module', $request->modulename)->firstOrFail()->id,
                'updated_by' => $this->getEmployeeID()->id,
                'date' => date('Y-m-d'),
            ]);

            $this->model->create($requestData);

            return $this->response('store');
        });
    }

    // Implement other methods similarly...

    private function tryCatch($callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    private function validateTaskBound($taskId)
    {
        $count = $this->taskbound->where('task_id', $taskId)
            ->where('employee_id', $this->getEmployeeID()->id)
            ->count();

        if ($count < 1) {
            abort(403, $this->getMessage()['nothaveaccess']);
        }
    }

    private function response($status, $data = null)
    {
        return response()->json([
            "status" => $status,
            "message" => $this->getMessage()[$status],
            'data' => $data,
        ]);
    }

    // Assume getEmployeeID() and getMessage() are implemented elsewhere...
}