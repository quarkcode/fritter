<?php

/* FUNCTION: TO GET EXTENSION OF FILENAME */
	function getFileExt($archivo) {
		$temp = explode(".",$archivo);
		$temp2 = count($temp) - 1;
		$ext = $temp[$temp2];
		return $ext;
	}	

/* FUNCTION: TO ENCRYPT STRING */
	function encrypt($key, $plain_text) {
		
		$c_t = crypt($plain_text,$key);
		return base64_encode($c_t);
		
	}		
	
/* FUNCTION: TO CATCH ERRORS */
	function customError($errno, $errstr)	{
		 
		global $globals;
		
		if($globals['debug_app'] && $errno != '8' ) {
			echo "<b>ERROR:</b> [$errno] $errstr <br>";
		} 
			
	}	
	
/* FUNCTION: TO CLEAN STRING */
	function cleanString($string) {
		
		#$string = filter_var_array($string, FILTER_SANITIZE_STRING);
	
		$string = str_replace('"', "''", $string);
	
		$string = mysql_real_escape_string($string);
	
		return $string;
		
	}
	
/* FUNCTION: TO GENERATE A RANDOM PASS */
	function randomPassword() {
	    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}
?>