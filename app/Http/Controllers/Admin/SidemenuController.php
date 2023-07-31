<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SideMenu;

class SidemenuController extends Controller
{
    public function __construct()
    {
        //
    }

    public function index()
    {
        try {

            $data = SideMenu::all();

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
            // if($request->is_parent) {
            //     ($request->is_parent == 'false') ? $requestData['is_parent'] = 0 : $requestData['is_parent'] = 1;
            // }
            // if($request->is_secondary_menu) {
            //     ($request->is_secondary_menu == 'false') ? $requestData['is_secondary_menu'] = 0 : $requestData['is_secondary_menu'] = 1;
            // }
            // if($request->must_full_title) {
            //     ($request->must_full_title == 'false') ? $requestData['must_full_title'] = 0 : $requestData['must_full_title'] = 1;
            // }
            
            SideMenu::create($requestData);

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
            // if($request->is_parent) {
            //     ($request->is_parent == 'false') ? $requestData['is_parent'] = 0 : $requestData['is_parent'] = 1;
            // }
            // if($request->is_secondary_menu) {
            //     ($request->is_secondary_menu == 'false') ? $requestData['is_secondary_menu'] = 0 : $requestData['is_secondary_menu'] = 1;
            // }
            // if($request->must_full_title) {
            //     ($request->must_full_title == 'false') ? $requestData['must_full_title'] = 0 : $requestData['must_full_title'] = 1;
            // }
    
            $data = SideMenu::findOrFail($id);
            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            
            $data = SideMenu::findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
