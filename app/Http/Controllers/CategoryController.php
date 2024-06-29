<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Category;
use DB;

class CategoryController extends Controller
{
    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Category();
        $this->modulename = 'Mom';
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
            $requestData['module_id'] = $this->getModuleId($request->modulename);
            
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

                // Subquery untuk menghitung status
                $statusCounts = DB::table('request_momTask as rt')
                ->select('rt.category_id')
                ->selectRaw("SUM(CASE WHEN rt.status = 'Open' THEN 1 ELSE 0 END) as open_count")
                ->selectRaw("SUM(CASE WHEN rt.status = 'Progress' THEN 1 ELSE 0 END) as progress_count")
                ->selectRaw("SUM(CASE WHEN rt.status = 'Done' THEN 1 ELSE 0 END) as done_count")
                ->groupBy('rt.category_id');

                $rawData = DB::table('tbl_category as c')
                ->select('c.id', 'c.category','emp.FullName')
                ->selectSub(function ($query) use ($statusCounts) {
                    $query->fromSub($statusCounts, 'statusCounts')
                        ->selectRaw("CONCAT('Open : ', open_count, ' | Progress : ', progress_count, ' | Done : ', done_count, ' | Total : ', open_count + progress_count + done_count) as status_summary")
                        ->whereColumn('c.id', 'statusCounts.category_id');
                }, 'status_summary')
                ->leftJoin('request_mom as rm', 'c.req_id', '=', 'rm.id')
                ->leftJoin('request_momTask as rt', 'c.id', '=', 'rt.category_id')
                ->leftJoin('request_momTaskBound as rtb', 'rt.id', '=', 'rtb.task_id')
                ->leftJoin('employee.tbl_employee as emp', 'rtb.employee_id', '=', 'emp.id')
                ->where('c.module_id', $module->id)
                ->where('c.req_id', $id)
                ->groupBy('c.id', 'c.category', 'emp.FullName')
                ->orderBy('c.id')
                ->get();

                $groupedData = [];

                foreach ($rawData as $row) {
                    if (!isset($groupedData[$row->id])) {
                        $groupedData[$row->id] = [
                            'id' => $row->id,
                            'category' => $row->category,
                            'status_summary' => $row->status_summary,
                            'FullName' => []
                        ];
                    }
                    if ($row->FullName) {
                        $groupedData[$row->id]['FullName'][] = $row->FullName;
                    }
                }

                foreach ($groupedData as &$data) {
                    $data['FullName'] = implode(', ', array_unique($data['FullName']));
                }

                return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => array_values($groupedData)]);
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

            $data = $this->model->findOrFail($id);
            $data->update($requestData);

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
