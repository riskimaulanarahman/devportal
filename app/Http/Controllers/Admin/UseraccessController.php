<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Useraccess;

class UseraccessController extends Controller
{

    public function index()
    {
        try {

            $data = Useraccess::all();

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();
    
            // Check for an existing record with the same module_id and employee_id
            $existingRecord = Useraccess::where('module_id', $requestData['module_id'])
                                        ->where('employee_id', $requestData['employee_id'])
                                        ->first();

            if ($existingRecord) {
                // If a record with the same module_id and employee_id already exists, return an error response
                return response()->json(["status" => "error", "message" => "Duplicate entry found for the provided module and employee."]);
            }

            // If no duplicate is found, proceed to create a new record
            Useraccess::create($requestData);

    return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show(Request $request)
    {
        try {
            $modulename = $request->module_id;
            $userid = $request->employee_id;
            $data = Useraccess::where('module_id',$this->getModuleId($modulename))->where('employee_id',$userid)->first();
            if($data) {
                return $data;
            } else {
                return response()->json(["status" => "error", "message" => 404]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // $requestData = $request->all();
    
            // $data = Useraccess::findOrFail($id);
            // $data->update($requestData);

            // return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

            $requestData = $request->all();

            $data = Useraccess::findOrFail($id);

            // Prepare query to check for duplicates
            $query = Useraccess::query();

            if (isset($requestData['module_id'])) {
                $query->where('module_id', $requestData['module_id']);
            } else {
                $query->where('module_id', $data->module_id); // Use existing value if not provided
            }

            if (isset($requestData['employee_id'])) {
                $query->where('employee_id', $requestData['employee_id']);
            } else {
                $query->where('employee_id', $data->employee_id); // Use existing value if not provided
            }

            // Exclude the current record
            $query->where('id', '!=', $id);

            // Check if any duplicate exists
            $existingRecord = $query->first();

            if ($existingRecord) {
                // If a duplicate record is found, return an error response
                return response()->json(["status" => "error", "message" => "Duplicate entry found for the provided module and employee."]);
            }

            // If no duplicate is found, proceed to update the record
            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);           

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            
            $data = Useraccess::findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
