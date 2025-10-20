<?php

namespace App\Http\Controllers\Submission\Ecatalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;
use App\Models\Submission\Ecatalog\Materialdetail;
use App\Models\Ecatalog;
use DB;
use Carbon\Carbon;

class MaterialRequestDetailController extends Controller
{

    private $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Materialdetail();
        $this->listCatalog = new Ecatalog();
        $this->modulename = 'MaterialReq';
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
            
            $listCatalog = $this->listCatalog->find($request->catalog_id);

            if($listCatalog->historicalPrice == 0) {
                return response()->json(["status" => "error", "message" => "The historical price cannot be zero. Please choose a valid price or reach out to procurement staff for assistance."]);
            }

            // Check lastUpdated within last 3 months
            $lastUpdated = Carbon::parse($listCatalog->lastUpdated);
            $threeMonthsAgo = Carbon::now()->subMonths(3);

            if ($lastUpdated->lt($threeMonthsAgo)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Catalog data is outdated (updated before 3 months ago). Please contact procurement staff."
                ]);
            }

            $requestData = $request->all();
            $requestData['materialCode'] = $listCatalog->materialCode;
            $requestData['description'] = $listCatalog->description;
            $requestData['part_number'] = $listCatalog->part_number;
            $requestData['pg'] = $listCatalog->pg;
            $requestData['uom'] = $listCatalog->uom;
            $requestData['unit_price'] = $listCatalog->historicalPrice;
            $requestData['amount'] = $listCatalog->historicalPrice*$request->order;

            // $this->addOneDayToDate($requestData);

            $this->model->create($requestData);

            DB::commit();

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
                $data = $this->model->with('catalog')->where('req_id',$id)->get();
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
        DB::beginTransaction();

        try {
            $data = $this->model->findOrFail($id);
            $catalogid = (isset($request->catalog_id) ? $request->catalog_id : $data->catalog_id);
            $listCatalog = $this->listCatalog->find($catalogid);
            
            $requestData = $request->all();
            $requestData['materialCode'] = $listCatalog->materialCode;
            $requestData['description'] = $listCatalog->description;
            $requestData['part_number'] = $listCatalog->part_number;
            $requestData['pg'] = $listCatalog->pg;
            $requestData['uom'] = $listCatalog->uom;
            $requestData['unit_price'] = $listCatalog->historicalPrice;

            $amount = (isset($request->order)) ? $request->order : $data->order;
            $requestData['amount'] = $listCatalog->historicalPrice*$amount;

            if($listCatalog->historicalPrice == 0) {
                return response()->json(["status" => "error", "message" => "The historical price cannot be zero. Please choose a valid price or reach out to procurement staff for assistance."]);
            }

            // $this->addOneDayToDate($requestData);

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

            DB::commit();

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
