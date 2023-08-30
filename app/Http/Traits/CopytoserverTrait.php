<?php
 
namespace App\Http\Traits;
use Illuminate\Support\Facades\Auth;

trait CopytoserverTrait {
 
    public function mycopy($s1) {

		// $user = '.\\admin_temp';
		// $password = 'KFPl4nn1ng$3rv3r';
		$user = 'd1\\planning_admin';
		$password = 'Password1';

		// exec('net use "\\\\172.18.83.38\\www" /persistent:no');
		exec('net use "\\\\172.18.83.38\\www" /user:"'.$user.'" "'.$password.'" /persistent:no');
		$remote_directory = "\\\\172.18.83.38\\www\\devportal\\".$s1;
			
		$path = pathinfo($remote_directory);
		print_r($path);
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

		exec('net use "\\\\172.18.83.38\\www" /delete /yes');

	}

    public function processcopy($path) {
		try {
			$copy = $this->mycopy($path); 
			if ($copy!=="success"){
				echo "500";
			} else {
				unlink($path);
			}
		}catch (Exception $e){
			die(" cannot copy file ".$e->getMessage()); 
		}
	}
 
}