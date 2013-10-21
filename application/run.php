<?php 
	
	session_start();
	
	global $webpage_options;
	$webpage_options = new stdClass();
	
	define("RELPATH", "../");
	define("APPPATH", RELPATH."application/");
	
	include APPPATH.'libs/main.php';
	
	# INSTALL???
	if( file_exists(RELPATH."install.phtml") && !file_exists(APPPATH."config.json") && $_GET['page'] != "install" ) header("Location: /install");
	
	if(!isset($_GET['f'])) {
		
		/* LANGUAJE CONTROL???
		 * if($_GET['page'] == 'es' || $_GET['page'] == 'ca' || $_GET['page'] == 'en') {
			$_SESSION['settings']->language = $_GET['page'];
			$_GET['page'] = "index";
			
		} else */
		if($_GET['page']=="D"){
			$web = new web((object) array( "theme" => "download" )); 
			
		} elseif(is_numeric($_GET['page'])){
			$_GET['val1'] = $_GET['page'];
			$_GET['page'] = "index";
			include RELPATH .(($_GET['page'] == '') ? 'index':$_GET['page']).".phtml";
			
		}else{
			include RELPATH .(($_GET['page'] == '') ? 'index':$_GET['page']).".phtml";
			
		}
		
	} else{
			include RELPATH .$_GET['f']."/".(($_GET['page'] == '') ? 'index':$_GET['page']).".phtml";
			
	}
	
	if(is_null($web)) header("Location: /error/404");
	
	$web->launch();
	
?>

