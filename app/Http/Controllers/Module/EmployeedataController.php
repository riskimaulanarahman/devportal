<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Employee;
use App\Models\Company;

class EmployeedataController extends Controller
{
    private $model;
    public $company;

    public function __construct()
    {
        $this->model = new Employee();
        $this->company = new Company();
    }

    public function index(Request $request)
    {
        try {

            $getcompany = $this->company->where('isUsed',1)->pluck('CompanyCode');

            $data = $this->model
                    ->where('isActive',1)
                    ->whereIn('companycode',$getcompany)
                    ->get(); // show index data by employee

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();
            $requestData['companycode'] = $this->getCompanyName($request->company_id);

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

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();
            $requestData['companycode'] = $this->getCompanyName($request->company_id);

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
            $data->update([
                'isActive' => 0
            ]);

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
