<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LogSuccess;
use App\Models\LogError;

class BerkasController extends Controller
{

    public function index()
    {
        //
    }

    public function store(Request $request, $module)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request,$modname)
    {
        try {
            $module = $modname;
            $file = $request->file('myFile');
            $nama_file = $module."_".time()."_".$file->getClientOriginalName();
            $tujuan_upload = 'public\\upload';
            $file->move($tujuan_upload,$nama_file);
            $source_file = $tujuan_upload.'\\'. $nama_file;

            // Log success
            $username = $request->ip();
            $url = $request->url();
            $this->logsuccess($username, $url, $nama_file);

            // echo $source_file;
            $this->processcopy($source_file);

            return $nama_file;
        } catch (\Exception $e){

            // Log error
            $username = $request->ip();
            $url = $request->url();
            $this->logerror($username, $url, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
 
    }

    public function destroy($id)
    {
        //
    }

    function logsuccess($username,$url,$values) {
        $requestData = [
            "user" => $username,
            "url" => $url,
            "action" => 'Attachment',
            "values" => $values
        ];
        LogSuccess::create($requestData);
    }
    
    function logerror($username,$url,$values) {
        $requestData = [
            "user" => $username,
            "url" => $url,
            "action" => 'Attachment',
            "values" => $values
        ];
        LogError::create($requestData);
    }
}
