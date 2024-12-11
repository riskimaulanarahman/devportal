<?php
 
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;

trait CopytoserverTrait {

	public function copyuploadpath() {
		$appEnv = env('APP_ENV');
		if ($appEnv == 'production') {
			$path = '\\\\172.18.83.38\\www\\devportal\\public\\upload\\';
		} else {
			$path = 'public\\upload\\';
		}

		return $path;
	}
 
    public function mycopy($s1) {

		$remote_directory = "\\\\172.18.83.38\\www\\devportal\\".$s1;
			
		$path = pathinfo($remote_directory);
		
		if (!file_exists($path['dirname'])) {
			mkdir($path['dirname'], 0777, true);
		}
		try {
			if(copy($s1,$remote_directory)){
				return "success";
			}else{
				$errors= error_get_last();
				$err =  "COPY ERROR: ".$errors['type'];
				$err .= "<br />\n".$errors['message'];
				return $err;
			}
		}catch (Exception $e){
			return $e->getMessage(); 
		}

	}

    public function processcopy($path) {
		try {
			$appEnv = env('APP_ENV');
			if ($appEnv == 'production') {
				$copy = $this->mycopy($path); 
				if ($copy!=="success"){
					echo "500";
				} else {
					unlink($path);
				}
			}
		}catch (Exception $e){
			die(" cannot copy file ".$e->getMessage()); 
		}
	}
 
}