<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LogSuccess;
use App\Models\LogError;
use DB;

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

    // public function update(Request $request,$modname)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $module = $modname;
    //         $file = $request->file('myFile');
    //         $nama_file = $module."_".time()."_".$file->getClientOriginalName();
    //         $tujuan_upload = 'public\\upload';
    //         $file->move($tujuan_upload,$nama_file);
    //         $source_file = $tujuan_upload.'\\'. $nama_file;

    //         // Log success
    //         $username = $request->ip();
    //         $url = $request->url();
            
    //         // echo $source_file;
    //         DB::commit();
            
    //         $this->processcopy($source_file);
    //         $this->logsuccessberkas($username, $url, $nama_file);

    //         return $nama_file;
    //     } catch (\Exception $e){

    //         // Log error
    //         $username = $request->ip();
    //         $url = $request->url();
    //         $this->logerrorberkas($username, $url, $e->getMessage());

    //         return response()->json(["status" => "error", "message" => $e->getMessage()]);
    //     }
 
    // }

    public function update(Request $request, $modname)
    {
        DB::beginTransaction();
        try {
            $module = $modname;
            $file = $request->file('myFile');

            // Dapatkan nama file asli
            $originalName = $file->getClientOriginalName();

            // Filter karakter spesial dari nama file
            $safeName = preg_replace('/[^A-Za-z0-9\_\-\.]/', '_', $originalName);

            // Cek jika safeName berbeda dari originalName
            if ($safeName !== $originalName) {
                return $originalName;
            }

            // Buat nama file dengan modul dan waktu
            $nama_file = $module . "_" . time() . "_" . $safeName;

            // Tentukan tujuan upload
            $tujuan_upload = 'public\\upload';

            // Pindahkan file ke direktori tujuan
            $file->move($tujuan_upload, $nama_file);

            // Tentukan path source file
            $source_file = $tujuan_upload . '\\' . $nama_file;

            // Log success
            $username = $request->ip();
            $url = $request->url();

            // Commit transaksi
            DB::commit();

            // Proses salinan file
            $this->processcopy($source_file);
            
            // Log keberhasilan pengiriman berkas
            $this->logsuccessberkas($username, $url, $nama_file);

            return $nama_file;

        } catch (\Exception $e) {
            // Log error
            $username = $request->ip();
            $url = $request->url();
            $this->logerrorberkas($username, $url, $e->getMessage());

            // Kembalikan response dengan status error
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        //
    }

    function logsuccessberkas($username,$url,$values) {
        $requestData = [
            "user" => $username,
            "url" => $url,
            "action" => 'Attachment',
            "values" => $values
        ];
        LogSuccess::create($requestData);
    }
    
    function logerrorberkas($username,$url,$values) {
        $requestData = [
            "user" => $username,
            "url" => $url,
            "action" => 'Attachment',
            "values" => $values
        ];
        LogError::create($requestData);
    }
}
