<?php
	global $module;
	include APPPATH.'libs/modules.php';
	
	$isAdd = false;
	$auxPreSQL = ", `";
	
	switch($this->POST->webAction){
		
		case 'add':
			$isAdd = true;
			$auxPreSQL = " ADD `";
			$modul = $this->db->queryUniqueObject("SELECT * FROM `".$this->settings->mysql_prefix . "module`  WHERE id = ".$this->POST->id);
						
		case 'save':
			$aux = json_decode( file_get_contents( $_FILES['data']['tmp_name'] ) );
			
			$aux_title = $aux->title;
			$aux_image_size = $aux->image_size;
			$aux_thumb_size = $aux->thumb_size;
			$aux_rows_size = $aux->rows_size;
			$aux_fields_html = "";
			$aux_image_file = "";
			$aux_attach_file = "";
			$aux_filters = "";
			$aux_list_head = "";
			$aux_list_values = "";
			$aux_upload_files = "";
			$auxJSfields = "";
			$withWysiwyg = false;
			$aux_JSForm = "";
			$sql_create = "CREATE TABLE ".$this->settings->mysql_prefix . $aux->table." (  `id` int(12) NOT NULL auto_increment ";
			
			if($isAdd){
				$aux->table = $modul->name;
				$sql_create = "ALTER TABLE ".$this->settings->mysql_prefix . $modul->name."  ";
			}
			
			$aux_fields = "";
			
			$i = 0;
	        foreach($aux->inputs as $key => $value) {
	        	
				switch ($value->type) {
					
					case 'text':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= 
							'<p class="field">
								<label><?=$web->t("'.$key.'")?></label>
								<input type="text" id="'.$key.'" name="'.$key.'" value="<?=\$'.$aux->table.'->'.$key.'?>" class="text input" />
							</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "text", "default": "", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						
						break;
						
					case 'date':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= 
						'<p class="field">
							<label><?=\$web->t("'.$key.'")?></label>
							<input type="text" id="'.$key.'" name="'.$key.'" value="<?=\$'.$aux->table.'->'.$key.'?>" class="text input" />
							<script> \$(function() { \$( "#'.$key.'" ).datepicker({ dateFormat: "yy-mm-dd" }); }); </script>
						</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "text", "default": "", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` timestamp NULL default NULL ";
						
						break;
						
					case 'password':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= 
						'<p class="field">
							<label><?=$web->t("'.$key.'")?></label>
							<input type="password" id="'.$key.'" name="'.$key.'" value="<?=\$'.$aux->table.'->'.$key.'?>" class="text input" />
						</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "text", "default": "", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						
						break;
						
					case 'hidden':
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						$aux_fields_html .= '<input type="hidden" id="'.$key.'" name="'.$key.'" value="<?=\$'.$aux->table.'->'.$key.'?>" />';
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						break;
						
					case 'textarea':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= '
						<p class="field">
							<label><?=\$web->t("'.$key.'")?></label>
							<textarea id="'.$key.'" name="'.$key.'"  placeholder="Textarea" class="input textarea"><?=\$'.$aux->table.'->'.$key.'?></textarea>
						</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "text", "default": "", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						
						break;
						
					case 'wysiwyg':

						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						$withWysiwyg = true;
						$auxJSfields .= (($auxJSfields!="")?",":"") . "#".$key."";
						$aux_fields_html .= '
						<p>
							<label><?=\$web->t("'.$key.'")?></label>
							<textarea id="'.$key.'" name="'.$key.'"  placeholder="Textarea" class="input textarea" style="width: 100%" rows="3"><?=\$'.$aux->table.'->'.$key.'?></textarea>
						</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "text", "default": "", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						
						break;
						
					case 'select':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= '
						<div class="field">
							<label><?=\$web->t("'.$key.'")?></label>
							<div class="picker">
								<select id="'.$key.'" name="'.$key.'" >';
						
						$aux_filters_select = "{ ";
						$j=0;
						foreach ($value->value as $valor => $title) {
							$title = ($title == "_empty_") ? "":$title;
							$valor = ($valor == "_empty_") ? "":$valor;
							$aux_fields_html .=  '<option value="'.$valor.'" <?=\$web->INPUT_selected(\$'.$aux->table.'->'.$key.', "'.$valor.'") ?> >'.$title.'</option>';
							$aux_filters_select .= ($j>0) ? "," : "" ;
							$aux_filters_select .= ' "'.$title.'" : "'.$valor.'" ';
							$j++;
						}
						$aux_filters_select .= " }";
						$aux_fields_html .=  ' </select>
							</div>
						</div>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "select", "default": '.$aux_filters_select.' , "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` text NOT NULL ";
						
						break;
						
					case 'checkbox':
						
						$aux_fields .= (($i>0)?",":"") . " \"$key\" ";
						
						$aux_fields_html .= '
						<p class="field">
							<label><?=\$web->t("'.$key.'")?> <span></span><input type="checkbox" id="'.$key.'" <?=\$web->INPUT_checked(\$'.$aux->table.'->'.$key.') ?> name="'.$key.'" value="1" /></label>
						</p>';
						
						if($value->filter){
							$aux_filters .= ($aux_filters!="")? ",":"";
							$aux_filters .= '"'.$key.'": { "type": "checkbox", "default": "1", "value": "\'.\$'.$aux->table.'->'.$key.'.\'" }';
						}
						
						if($value->list){
							$aux_list_head .= '<th><?=\$web->t("'.$key.'"); $web->orderBy("'.$key.'"); ?></th>';
							$aux_list_values .= '<td><?=\$'.$aux->table.'->'.$key.'?></td>';
						}
						
						$sql_create .= $auxPreSQL.$key."` VARCHAR(1) NOT NULL ";
						
						break;
						
					case 'image':
						$aux_upload_files .= str_replace("{field}", $key, webImageFile);
						$aux_fields_html .= '
						<p class="field">
							<label>
								<?=\$web->t("'.$key.'")?> 
								<span id="'.$aux->table.'_'.$key.'" class="light label"></span>
								<span class="small info btn icon-left entypo icon-doc-text"><a href="#" onclick="\$(\'#'.$key.'\').click();" ><?=\$web->t("select file") ?></a></span>
							</label>
							<? \$web->image("'.$aux->table.'_'.$key.'",\$'.$aux->table.'->id) ?>
							<span class="hidden"><input type="file" id="'.$key.'" name="'.$key.'" /></span>
							<script>\$("#'.$key.'").on("change", function(){ \$("#'.$aux->table.'_'.$key.'").html(\$("#'.$key.'").val()); });</script>
						</p>';
						break;
						
					case 'gallery':
						$aux_upload_files .= str_replace("{field}", $key, webGalleryFile);
						$aux_fields_html .= '
						<p class="field">
							<label>
								<?=\$web->t("'.$key.'")?> 
								<span id="'.$aux->table.'_'.$key.'" class="light label"></span>
								<span class="small info btn icon-left entypo icon-doc-text"><a href="#" onclick="\$(\'#'.$key.'\').click(); return false;" ><?=\$web->t("select file") ?></a></span>
								<span class="small info btn entypo"><a href="#" class="webSubmit" ><?=\$web->t("upload") ?></a></span>
							</label>
							<? \$web->gallery("'.$aux->table.'_'.$key.'",\$'.$aux->table.'->id) ?>
							<span class="hidden"><input type="file" id="'.$key.'" name="'.$key.'" /></span>
							<script>\$("#'.$key.'").on("change", function(){ \$("#'.$aux->table.'_'.$key.'").html(\$("#'.$key.'").val()); });</script>
						</p>';
						break;
						
					case 'attach':
						$aux_upload_files .= str_replace("{field}", $key, webAttachFile);
						$aux_fields_html .= '
						<p class="field">
							<label>
								<?=\$web->t("'.$key.'")?> 
								<span id="'.$aux->table.'_'.$key.'" class="light label"></span>
								<span class="small info btn icon-left entypo icon-doc-text"><a href="#" onclick="\$(\'#'.$key.'\').click();" ><?=\$web->t("select file") ?></a></span>
							</label>
							<? \$web->download("'.$aux->table.'_'.$key.'",\$'.$aux->table.'->id) ?>
							<span class="hidden"><input type="file" id="'.$key.'" name="'.$key.'" /></span>
							<script>\$("#'.$key.'").on("change", function(){ \$("#'.$aux->table.'_'.$key.'").html(\$("#'.$key.'").val()); });</script>
						</p>';
						break;
					
					default:
						break;
						
				}

				if($i==0 && $isAdd)	$auxPreSQL = ", ".$auxPreSQL;
				$i++;
			}

			if($withWysiwyg){
				$aux_JSForm .= str_replace("{field}", $auxJSfields, webFormJwysiwyg);
			}
			
			if($isAdd) {
				
				$this->db->execute($sql_create);
				
				/* UPDATE MODEL FILE */
				$auxModel = file_get_contents(APPPATH.'models/'.$modul->name.'.php');
				
				$posFields_ini = strpos($auxModel, "campos = array(");
				$posFields_fin = strpos($auxModel, ");", $posFields_ini);
				
				$aux_model_result = substr($auxModel, 0, $posFields_fin);
				$aux_model_result .= ", ". $aux_fields;
				$aux_model_result .= substr($auxModel, $posFields_fin);
				
				if($aux_upload_files!=""){
					$posUploadFields = strpos($aux_model_result, "if(\$files[");
					
					$model_result = substr($aux_model_result, 0, $posUploadFields);
					$model_result .= $aux_upload_files."\r\n";
					$model_result .= substr($aux_model_result, $posUploadFields);
				} else {
					$model_result = $aux_model_result;
				}
				$fp = fopen(APPPATH.'models/'.$modul->name.'.php', 'w');
				fwrite($fp, $model_result);
				fclose($fp);
				
				/* UPDATE FORM FILE */
				$auxForm = file_get_contents(RELPATH.'admin/'.$modul->name.'.phtml');
				$posFields = strpos($auxForm, '<input type="hidden" id="webAction"');
				
				$form_result = substr($auxForm, 0, $posFields);
				$form_result .= stripslashes($aux_fields_html) . "\r\n\r\n\t\t\t";
				$form_result .= substr($auxForm, $posFields);
				
				$fp = fopen(RELPATH.'admin/'.$modul->name.'.phtml', 'w');
				fwrite($fp, $form_result);
				fclose($fp);
				
			} else {
				$sql_create .= ", PRIMARY KEY  (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ; ";
				$this->db->execute($sql_create);
				
				if($aux_filters != "")
					$aux_filters = "<? \$web->filterBy(  json_decode('{
												" .$aux_filters. "
											}') );  ?>" ;
				$auxContents = array($aux_title, $aux_image_size, $aux_thumb_size, $aux_rows_size, $aux_fields, $aux_upload_files, $aux_fields_html, $aux_filters, $aux_list_head, $aux_list_values,$aux_JSForm );
				$toReplace = array("{title}", "{image_size}", "{thumb_size}", "{rows_size}", "{fields}", "{upload_file}", "{fields_html}", "{filters}", "{list_head}", "{list_values}","{JS}");
				
				$web_controller = stripslashes(str_replace($toReplace, $auxContents, webController));
				$fp = fopen(APPPATH.'controllers/'.$aux->table.'.php', 'w');
				fwrite($fp, $web_controller);
				fclose($fp);
				
				$web_model = stripslashes(str_replace($toReplace, $auxContents, webModel));
				$fp = fopen(APPPATH.'models/'.$aux->table.'.php', 'w');
				fwrite($fp, $web_model);
				fclose($fp);
				
				$web_form = stripslashes(str_replace($toReplace, $auxContents, webForm));
				$fp = fopen(RELPATH.'admin/'.$aux->table.'.phtml', 'w');
				fwrite($fp, $web_form);
				fclose($fp);
				
				$web_list = stripslashes(str_replace($toReplace, $auxContents, webList));
				$fp = fopen(RELPATH.'admin/'.$aux->table.'s.phtml', 'w');
				fwrite($fp, $web_list);
				fclose($fp);
				
				$sql_module = "INSERT INTO `".$this->settings->mysql_prefix . "module` (`id`, `name`, `data`) VALUES (NULL, '".$aux->table."', '".json_encode($aux)."');";
				$this->db->execute($sql_module);
				
				$this->javascript = $this->JSform();
				$this->javascript .= $this->JSshowMessage("saved");				
			}
			
			break;
			
		case 'delete':
			$aux = $this->db->queryUniqueObject("SELECT * FROM `".$this->settings->mysql_prefix . "module`  WHERE id = ".$this->POST->id);
			unlink(APPPATH.'controllers/'.$aux->name.'.php');
			unlink(APPPATH.'models/'.$aux->name.'.php');
			unlink(RELPATH.'admin/'.$aux->name.'.phtml');
			unlink(RELPATH.'admin/'.$aux->name.'s.phtml');
			$this->db->execute(" DROP TABLE `".$this->settings->mysql_prefix . $aux->name."` ");
			$this->db->execute(" DELETE FROM `".$this->settings->mysql_prefix . "module` WHERE `id` = ".$this->POST->id);
			break;

	}
	
?>