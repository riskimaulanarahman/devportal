<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Attachment;
use App\Models\Module;

class AttachmentController extends Controller
{

    private $model;
    private $module;

    public function __construct()
    {
        $this->model = new Attachment();
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
            $module = $this->module->select('id','module')->where('module',$request->modulename)->first();
            if($module) {
                $requestData['module_id'] = $module->id;
            }

            // Validasi file ekstensi for JDI Sementara
            $errorMessage = $this->validateFileExtension($request->modulename, $request->remarks, $request->path);
            if ($errorMessage) {
                return response()->json(["status" => "error", "message" => $errorMessage]);
            }

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
                $data = $this->model->where('req_id',$id)->where('module_id',$module->id)->get();
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

            $data = $this->model->findOrFail($id);
            if(!empty($request->path)) {   
                $path = public_path() . '/upload/' . $data->path; // path file yang akan dihapus
                if (file_exists($path)) { // cek apakah file ada di direktori
                    unlink($path); // hapus file dari direktori
                }
            }

            // Menggunakan $request jika ada, jika tidak menggunakan $data
            $remarks = isset($request->remarks) ? $request->remarks : $data->remarks;
            $path = isset($request->path) ? $request->path : $data->path;
            // Validasi file ekstensi for JDI Sementara
            $errorMessage = $this->validateFileExtension($this->getModuleName($data->module_id), $remarks, $path);
            if ($errorMessage) {
                return response()->json(["status" => "error", "message" => $errorMessage]);
            }

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
            $path = $this->copyuploadpath() . $data->path; // path file yang akan dihapus
            // dd($path);
            if (file_exists($path)) { // cek apakah file ada di direktori
                unlink($path); // hapus file dari direktori
            }
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    private function validateFileExtension($modulename, $remarks, $path)
    {
        if ($modulename == 'Jdi') {
            if ($remarks == 'Before' || $remarks == 'After') {
                $allowedExtensions = ['jpeg', 'jpg', 'png'];
                $errorMessage = "Error: Please upload a valid image file (e.g., jpeg, jpg, png).";
            } else {
                $allowedExtensions = ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx'];
                $errorMessage = "Error: Please upload a valid document file (e.g., doc, docx, pdf, xls, xlsx, ppt, pptx).";
            }

            // Mendapatkan ekstensi file dari $path
            $pathInfo = pathinfo($path);
            $extension = strtolower($pathInfo['extension']);

            if (!in_array($extension, $allowedExtensions)) {
                // Ekstensi tidak valid, kembalikan pesan error
                return $errorMessage;
            }
        }

        // Ekstensi valid, kembalikan null (tidak ada error)
        return null;
    }
}
