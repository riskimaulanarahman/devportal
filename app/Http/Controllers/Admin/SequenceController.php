<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Sequence;

class SequenceController extends Controller
{

    public function index()
    {
        try {

            $data = Sequence::all();

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            // if($request->is_active) {
            //     ($request->is_active == 'false') ? $requestData['is_active'] = 0 : $requestData['is_active'] = 1;
            // }
            // if($request->is_admin) {
            //     ($request->is_admin == 'false') ? $requestData['is_admin'] = 0 : $requestData['is_admin'] = 1;
            // }

            Sequence::create($requestData);

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

            // if($request->is_active) {
            //     ($request->is_active == 'false') ? $requestData['is_active'] = 0 : $requestData['is_active'] = 1;
            // }
            // if($request->is_admin) {
            //     ($request->is_admin == 'false') ? $requestData['is_admin'] = 0 : $requestData['is_admin'] = 1;
            // }

            $data = Sequence::findOrFail($id);
            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = Sequence::findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
