<?php
	
	#READ THE CONFIG INFO FROM application.cpmf
	if(file_exists(APPPATH."config.json") && file_exists(APPPATH."libs/db.php") ) {
		include 'libs/db.php';
		define("withDB", true);
	} else	
		define("withDB", false);

	include APPPATH.'libs/functions.php';
		
	class web {
		
		var $title = "";
		var $meta_keywords = "";
		var $meta_description = "";
		var $page_title = "";
		var $full_title = "";
		var $theme = "default";
		var $application_title = "default";
		var $level =  100;
		var $domain = "www.quarkcode.com";
		var $charset = "utf-8";
		var $logged = false;
		var $listOrder = "id";
		var $listDirection = "asc";
		var $language = "en";
		var $head = null;
		var $content = null;
		var $page = null;
		var $db_table = null;
		var $db = null;
		var $POST = null;
		var $message = null;
		var $message_class = null;
		var $settings = null;
		var $javascript = null;
		var $pageIni = 0;
		var $searching = false;
		
		public function __construct( $settings )	{
			
			
			if(file_exists(APPPATH."config.json")) {
				
				#CARGO LAS CONFIGURACIONES
				$auxConf = json_decode( file_get_contents(APPPATH."config.json") );
				$_SESSION['settings']->code = $auxConf->code;
				
		        foreach($auxConf as $key => $value) {
		        	
		        	switch ($key) {
						default:
							$this->settings->$key = $value;
							break;
					}
		        		
				}
				
			}
			
			if(withDB){
				
				#INICIO BBDD # $this->settings->mysql_prefix
	  			$this->db = new DB(	$this->settings->mysql_db, 
										  			$this->settings->mysql_host, 
										  			$this->settings->mysql_username, 
										  			$this->settings->mysql_password);			
														
				$this->title = $this->config("web_name");
				$this->meta_keywords = $this->config("meta_keywords");
				$this->meta_description = $this->config("meta_description");
				
			} else {
				$this->level = 0;
			}
			
			#SET SETTING OF CONSTRUCTOR
	        foreach($settings as $key => $value) $this->$key = $value;
			
			#SET SETTING OF SESSION
			if($_SESSION['settings'])
	        	foreach($_SESSION['settings'] as $key => $value) $this->$key = $value;

	       #WITH SESSION??
	       if( $this->level > 0 ) {

				if($_SESSION[$this->settings->code] && $_SESSION['user']->id > 0 )	{
						
					if($_SESSION['expired_pass'] && ($this->page != 'account'))  header("Location: /account/");
					
					if($this->level > $_SESSION['user']->level ) header("Location: /");
					
				} else header("Location: /login/");
		   }
		   
			#GET THE GET URL VALUES
			$this->page = $_GET['page'];
			$this->value1 = $_GET['val1'];
			$this->value2 = $_GET['val2'];
			$this->value3 = $_GET['val3'];
			
			#CLEAN AND GET THE POST
			if($_POST){
				foreach($_POST as $nombre_campo => $valor) $this->POST->$nombre_campo = cleanString($valor); 
			} else 
				$this->POST = false;
			
			#COJO EL MODELO DE OBJETO
			if(file_exists(APPPATH."models/".$this->model.".php") && $this->model != "" ) 
				include APPPATH."models/".$this->model.".php";
			
			#CARGO EL CONTROLADOR
			if(file_exists(APPPATH."controllers/".$this->control.".php") && $this->control != "") 
				include APPPATH."controllers/".$this->control.".php";
			
			#SI ES SIN ADMINISTRACION
			if(!withDB && file_exists(APPPATH."config.json")) {
				$this->title = $this->settings->webname;
				$this->meta_description = $this->settings->description;
				$this->meta_keywords = $this->settings->keywords;
			}
			$this->full_title = $this->page_title . ( ( $this->page_title != "" && $this->title != ""  ) ? " - " : "" ) . $this->title;
			
			#GUARDO EL HEAD
			$this->head = $this->headAppend().ob_get_contents();
			ob_end_clean();
			ob_start();
			
		}
	
		public function launch()	{
			
			#GUARDO EN CONTENT
			$this->content=ob_get_contents();
			ob_end_clean();
			
			switch ($this->theme) {
				
				case 'download':
					$file = RELPATH."upload/". $_GET['val1'] ."/".$_GET['val1'].$_GET['val2'];
					foreach (glob($file."*") as $filename) { $file = $filename; }
					$basename = basename($file);
					$length   = sprintf("%u", filesize($file));
					header('Content-Description: File Transfer');
					header('Content-Type: '.mime_content_type($file));
					header('Content-Disposition: attachment; filename="' . $basename . '"');
					header('Content-Transfer-Encoding: Binary');
					header('Connection: Keep-Alive');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . $length);
					readfile($file);
					break;
					
				case 'javascript':
					header("content-type: application/javascript");
					print $this->content;
					break;
					
				case 'blank':
					header('Content-Type: text/html; charset='.$this->charset);
					print $this->head;
					print $this->content;
					print $this->JS();
					break;
				
				default:
					$this->content .= $this->JS();
					include RELPATH.'themes/'.$this->theme."/index.phtml";
					break;
			}
				
			
		}
		
		public function layout($name,$theme = false)	{
			
			if(!$theme) $theme = $this->theme;
			include RELPATH."themes/".$theme."/".$name.".phtml";
			
		}
		
		public function download($name,$id)	{
			if($id>0){
				$file = RELPATH."upload/". $name ."/".$name.$id;
				if(glob( $file ."*" )){
					foreach (glob($file."*") as $filename)	    $file = $filename;
					if ( file_exists($file) && $id > 0 )
						print '<span class="small info btn icon-left entypo icon-down"><a href="/D/'.$name.'/' . $id . '" target="_blank" class="webDelAttach" >download</a></span> ';
						print '<span class="small info btn icon-left entypo icon-trash"><a href="javascript: webDeleteFile(\''.$file.'\'); " class="webDelAttach" >delete</a></span>';
				}
			}
		}
		
		public function image($name,$id,$size = "thumb")	{
			if($id>0){
				switch ($size) {
					case 'thumb':
						$file = RELPATH."img/". $name ."/thumbs/".$name.$id.".png";
						break;
				}				
				
				if ( file_exists($file) ) {
					print "<img src='".$file."?".mt_rand()."' />";
					print '<span class="small info btn icon-left entypo icon-trash" style="margin-left:-55px;"><a href="javascript: webDeleteFile(\''.$file.'\'); " class="webDelAttach" >delete</a></span>';
				}
			}
		}
		
		public function gallery($name,$id,$size = "thumb")	{
			if($id>0){
				switch ($size) {
					case 'thumb':
						$fname = RELPATH."img/". $name ."/thumbs/".$name.$id;
						break;
				}				
				print '<ul class="five_up tiles gallery">';
				if(glob( $fname."*.png" )){
					foreach ( glob( $fname."*.png" ) as $key => $value) {
						$fileName = explode("_",end(explode("/", str_replace(".png", "", $value))));
						if ( file_exists( $value) ) print "
							<li class='rounded image delete-image-gallery'><img src='/".substr($value, 1)."?".mt_rand()."' />
								<a href='javascript: webDeleteFile(\"".$value."\") ' ><i class='small info btn icon-c entypo icon-trash'></i></a>
							</li>";
					}
				}
				print "</ul>";
			}
		}
		
		public function config($value)	{
			if($value!="" && withDB ){
				$res = $this->db->queryUniqueValue("SELECT value FROM ".$this->settings->mysql_prefix ."config WHERE name = '".$value."'");
				return $res;		
			} else {
				return false;
			}
		}
		
		#TRANSLATE TEXT FUNCTION
		function t( $IDtexto ) {
			
			if(withDB)
				$res = $this->db->queryUniqueValue("SELECT texto FROM ".$this->settings->mysql_prefix ."translation WHERE id = '".$IDtexto."' AND language = '".$this->language."' ");
			else 
				return false;

			if(is_null($res))
				return $IDtexto;
			else
				return $res;
			
		}

		/*
		 * FUNCTION TO MAIL HTML
		 * 
			# $data->emailto ===> Email
			# $data->busca ===> busca
			# $data->remplaza ===> remplaza
			# $data->titulo ===> asunto
			# $data->template ===> template
			# $data->bcc ===> bcc
		 */
		function mailTo( $data ) {
			
			include APPPATH."libs/phpmailer/class.phpmailer.php";
		
			$mail = new PHPMailer();
			
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			
			$mail->Host = ( $data->host != "" ) ? $data->host : $this->settings->mail_host;
			$mail->Username = ( $data->username != "" ) ? $data->username : $this->settings->mail_username;
			$mail->Password = ( $data->password != "" ) ? $data->password : $this->settings->mail_password;
			$mail->From = ( $data->from != "" ) ? $data->from : $this->settings->mail_from;
			$mail->FromName = ( $data->fromname != "" ) ? $data->fromname : $this->settings->mail_fromname;
		
			$mail->IsHTML(true);
			$mail->Subject = $data->titulo;
			
			if($data->template!="")
				$auxMail = file_get_contents(APPPATH.'mailing/'.$data->template.'.html');
			else $auxMail = $data->contenido;
			
			$MailFinal = $mail->Body = (str_replace($data->busca, $data->remplaza, $auxMail));
			$mail->AltBody = str_replace(array("\t","\n\r\n\r"), array("",""), strip_tags(html_entity_decode($MailFinal)));
				
			$mail->AddAddress($data->emailto);
			if($data->bcc !="") $mail->AddBCC($data->bcc);
			
			if(!$mail->Send()) {
			  $res = $mail->ErrorInfo;
			} else {
				$res = true;
			}
			
			return $res;
			
		}
				
		/* 
		 * HTML FUNCTION
		 */
		function headAppend() {
			
			$res = "";
			
			if($this->settings->noindexnofollow && $this->theme != "blank")
				$res .= "<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW' />\n\r";
			
			return $res;
			
		}
		
		function filterBy($filters){

			$inputs_form_filter = "";
	        foreach($filters as $filterName => $filter ) {

				$inputs_form_filter .= '<li class="field">';
													
	        	switch ($filter->type) {
	        		
					case 'text':
						$inputs_form_filter .= '<label class="inline">'.$filterName.'</label>';
						$inputs_form_filter .=  '<input type="text" id="'.$filterName.'_webSearch" name="'.$filterName.'" value="'.$filter->value.'" class="wide text input" />';
						break;
						
					case 'checkbox':
						$inputs_form_filter .= '<label class="">'.$filterName.'<span style="margin-left: 14px;"></span><input type="checkbox" '.(($filter->value==$filter->default)?'checked="checked"':'').'  id="'.$filterName.'_webSearch"  name="'.$filterName.'" value="'.$filter->default.'"  /></label>';
		
						break;
						
					case 'select':
						$inputs_form_filter .= '<label class="inline">'.$filterName.'</label>';
						$inputs_form_filter .=  '<div class="picker"><select id="'.$filterName.'_webSearch" name="'.$filterName.'" >';
						foreach ($filter->default as $title => $valor) {
							$title = ($title == "_empty_") ? "":$title;
							$valor = ($valor == "_empty_") ? "":$valor;
							$inputs_form_filter .=  '<option value="'.$valor.'" ' . (($filter->value==$valor)?'selected="selected"':'') . '>'.$title.'</option>';
						}
						$inputs_form_filter .=  '</select></div>';
						break;
					
				}
				$inputs_form_filter .= '</li>';
			}	

			$res =  '
				
				<form id="webFormSearch" enctype="multipart/form-data" method="post" '.((!$this->searching)?'style="display:none"':'').'>
				
					<fieldset>
					
              			<legend><i class="icon-cancel" onclick="webFiltersToggle();"></i>'.$this->t("Filters") .'</legend>
              			
						<ul>
							'.$inputs_form_filter .'
						</ul>

						<div class="medium default btn icon-right entypo icon-search"><a href="#" onclick="webSearch();" >' . $this->t("search") . '</a></div>
												
					</fieldset>
					
					<input type="hidden" id="webActionSearch" name="webAction" value="list" />
					<input type="hidden" id="webObjectSearch" name="webObject" value="user" />
					
					<input type="hidden" id="webListOrder" name="webListOrder" value="'.$this->listOrder.'" />
					<input type="hidden" id="webListDirection" name="webListDirection" value="'.$this->listDirection.'" />
				
				</form>';
			print $res;
			
		}
		
		function orderBy($campo){
			
			if($campo == $this->listOrder)
				
				if($this->listDirection=="asc")
					$res = "<a href='javascript: webTableOrder(\"".$campo."\",\"desc\");' class='selected'>&#x25B2;</a>";
				
				else
					$res = "<a href='javascript: webTableOrder(\"".$campo."\",\"asc\");' class='selected'>&#x25BC;</a>";
				
			else
				$res = "<a href='javascript: webTableOrder(\"".$campo."\",\"asc\");'>&#x25B2;</a> <a href='javascript: webTableOrder(\"".$campo."\",\"desc\");'>&#x25BC;</a>";
				
			print "<span class='order'>$res</span>";
			
		}
		
		function pagination( $data, $tam_pagination = 6 ){
			
			$pages_tam = $data->rows_size;
			$pages_ini = $this->pageIni ;
			$pages_max = $data->rows_total;
			
			if(!isset($pages_ini)) $pages_ini = 0;	
			else {
				if($pages_ini < 0) $pages_ini = 0;
				if($pages_ini >= $pages_max) $pages_ini = $pages_max - $pages_tam;
			}
		
			if($pages_ini < 0 ) $pages_ini = 0;
		
			$pages = $pages_max / $pages_tam;
			$pactual = $pages_ini / $pages_tam;
			$this_pag = $pages_ini;
		
			$pagination .= "\n<ul id='webPagination'>\n";
		
			if($pactual != 0 ){
				$pagination .= "	<li class='previous'><a href=\"javascript: webTablePosition(".($pages_ini-$pages_tam).");\" > &#x25C1; </a></li>\n";
				if($pactual > ($tam_pagination-1)) {
					$pagination .= "	<li><a href=\"javascript: webTablePosition(0);\">1</a></li>\n";
					if($pactual > ($tam_pagination))  $pagination .= "	<li>...</li>\n";
				}
			}
		
			if(ceil($pages)>1) {
				$contp = 0;
				for($i = 0 ; $i < $pages ; $i++) {
					if($contp < ($tam_pagination+$pactual)){
						if($i > ($pactual-$tam_pagination)){
							if ($i == $pactual) {
								if($i==0) 
									$pagination .= "	<li class='selected' id='pos".($i*$pages_tam)."' >".($i+1)."</li>\n";
								else
									$pagination .= "	<li class='selected' id='pos".($i*$pages_tam)."' >".($i+1)."</li>\n";							
							
							} else {
								$pagination .= "	<li><a href=\"javascript: webTablePosition(".($i*$pages_tam).");\">".($i+1)."</a></li>\n";
							} 
						}
					}
					$contp++;
				}
			}
		
			if(($pactual+1) != ceil($pages) ) {
				if($pages_max > $pages_tam){
					if($pactual < ( ($pages) - ($tam_pagination)  ))  {
						if($pactual < ( ($pages) - ($tam_pagination+1)  ))  $pagination .= "	<li>...</li>";
						$pagination .= "\n	<li><a href=\"javascript: webTablePosition(".($pages_tam*(ceil($pages)-1)).");\">".ceil($pages)."</a></li>\n";
					}
					$pagination .= "	<li class='last next'><a href=\"javascript: webTablePosition(".($pages_ini+$pages_tam).");\"> &#x25B7; </a></li>\n";
				}
			}
		
			$pagination .= ($pagination == "\n<ul id='webPagination'>\n" ) ?  "	<li class='selected' id='pos0' ></li>\n" : "";
		 	$pagination .= "</ul>";
				  
			print $pagination ;
						
		}
		
		function INPUT_checked($value){
			if($value)
				return ' checked="checked" ';
		}
		
		function INPUT_selected($value,$option){
			if($value==$option)
				return ' selected="selected" ';
		}
		
		/* 
		 * JAVASCRIPT FUNCTIONS
		 */
		 
		function JS(){
			
			return '<script type="text/javascript">'.$this->javascript.'</script>';
			
		}
		
		function JSform(){
			
			return "
			$('.webSubmit').click(function(){ 
				var overlay = jQuery('<div id=\"webOverlay\"> </div>');
				overlay.appendTo(document.body)
				$('#webForm').ajaxForm({ 
				    target:     '#webWindow', 
				    url:        	'/admin/".$this->page."',
				    success:   function() {
				    	$('#webOverlay').remove();
				    } 
				}).submit(); 
		    	
			});
			
			function webDeleteFile(objecto){
				$('#webAction').attr('value','delete-file');
				$('#webObject').attr('value',objecto);
				$('.webSubmit').click();
			}

			";
			
		}
		
		function JSshowMessage($texto, $tipo = "success", $error_fields=null){

			if(!is_null($error_fields)){
				$i = 0;
				foreach ($error_fields as $key => $value) {
					$auxClassFields .= (($i>0)?",":"") . " #".$value . " ";
					$i++;
				}
			}
						
			return "	if($('#webPagination').length > 0) { webTablePosition($('#webPagination .selected').attr('id').replace('pos','')); }
						webMessage('".addslashes($texto)."', '".$tipo."' );
						$('".$auxClassFields."').parents('.field').addClass('danger');	 ";
						
		}
		
		function JSwindow(){
			
			return "
				var webWindow = '<div id=\"webWindow\">&nbsp;</div>';			
															
				function webWindowLoad(object,id,size){
				    
				    $.post('/admin/'+object, { webAction: 'get', id: id } )
						    .done(function(data) {
						    	if(!$('#webWindow').length){
						    		$('body').append(window.webWindow);
								    $('#webWindow').css('display', 'block');
								    $('#webWindow').fadeOut(0); 
									if(size==undefined) size = '500px';
								    $('#webWindow').css('width', size);
								    $('#webWindow').fadeIn(1000); 
							    }
								$('#webWindow').html(data);
					});
					
				}
				
				function webWindowClose(){
					
			    	if($('#webWindow').length){
						$('#webWindow').fadeOut(1000, function() {
							$('#webWindow').remove();
						}); 
					}
					
				}
				
				function webTablePosition(pageIni){
					
					$.post('/admin/".$this->page."', { webAction: 'list', webPageIni: pageIni, webSearch: $('#webFormSearch').serialize() } )
						    .done(function(data) {
								$('#content').html(data);
					});
						
				}
				
				function webSearch(){
	
					$.post('/admin/".$this->page."', { webAction: 'list', webPageIni: 0, webSearch: $('#webFormSearch').serialize() } )
						    .done(function(data) {
								$('#content').html(data);
					});
			    	
				}
				
				function webTableOrder(campo,dir){
					
					$('#webListOrder').attr('value',campo);
					$('#webListDirection').attr('value',dir);
					
					$.post('/admin/".$this->page."', { webAction: 'list', webPageIni: 0, webSearch: $('#webFormSearch').serialize() } )
						    .done(function(data) {
								$('#content').html(data);
					});
			    	
				}
				

				function webRemove(object,id){
					$('body').append(\"<div id='dialog-confirm' class='warning alert'> \\
												".$this->t("Do you want delete it?")." <br/>\\
												<div class='small default btn icon-left entypo icon-cancel'><a href=\\\"javascript: webDialogClose();\\\">".$this->t("Cancel")."</a></div> \\
												<div class='small default btn icon-left entypo icon-trash'><a href=\\\"javascript: removeObject('\"+object+\"', '\"+id+\"')\\\">".$this->t("Delete")."</a></div> \\
											  </div>\");

				    var height = $(window).height();
				    var width = $(document).width();
				    
					 $('#dialog-confirm').css({
				        'left' : width/2 - ($('#dialog-confirm').width() / 2),
				        'top' : height/2 - ($('#dialog-confirm').height() / 2)
				    });
				    
					 $('#dialog-confirm').fadeIn(500);
					
				}
						
				function removeObject(object,id){
					 $.post('/admin/'+object, { webAction: 'delete', id: id } )
						    .done(function(data) {
							webWindowClose();
							".$this->JSshowMessage('deleted')."
							
					 		webDialogClose();
					});
		
				}

				function webDialogClose(){
					$('#dialog-confirm').fadeOut(500, function(){
					 	$('#dialog-confirm').remove();
					 });
				}
				
			";	
			
		}
		
	}

	ob_start();
	
?>

