<?

define("webController",
"<?php
	global \${title};
	global \${title}s; 
	
	switch(\$this->POST->webAction){
		
		case 'save':
			\$this->javascript = \$this->JSform();
			\$this->{title}->id = \$this->{title}->save(\$this->POST);

			if( \$this->{title}->error_msg != \"\" ) {
				foreach (\$this->POST as \$key => \$value)  \$this->POST->\$key = stripslashes(stripslashes(\$value));
				\${title} = \$this->POST;
				\$this->webAction = \"save\";
				\$this->javascript .= \$this->JSshowMessage(\$this->{title}->error_msg, \"danger\", \$this->{title}->error_fieldsJS );
				
			} else {
				\${title} = \$this->{title}->byId(\$this->{title}->id);
				\$this->webAction = \"update\";
				\$this->javascript .= \$this->JSshowMessage(\"saved\");
				
			}
			
			break;
			
		case 'update':
			\$this->webAction = \"update\";
			\$this->javascript = \$this->JSform();
			\$this->{title}->update(\$this->POST);

			if( \$this->{title}->error_msg != \"\" ) {
				foreach (\$this->POST as \$key => \$value)  \$this->POST->\$key = stripslashes(stripslashes(\$value));
				\${title} = \$this->POST;
				\$this->javascript .= \$this->JSshowMessage(\$this->{title}->error_msg, \"danger\",\$this->{title}->error_fieldsJS);
				
			} else {
				\${title} = \$this->{title}->byId(\$this->POST->id);
				\$this->javascript .= \$this->JSshowMessage(\"updated\");
				
			}
			
			break;
			
		case 'delete':
			\$this->{title}->delete(\$this->POST->id);
			break;
			
		case 'delete-file':
			if(file_exists(\$this->POST->webObject)) 
				unlink(\$this->POST->webObject);
			if(file_exists(str_replace(\"/thumbs\", \"\", \$this->POST->webObject))) 
				unlink(str_replace(\"/thumbs\", \"\", \$this->POST->webObject));
			\${title} = \$this->{title}->byId(\$this->POST->id);
			\$this->javascript = \$this->JSform();
			\$this->javascript .= \$this->JSshowMessage(\"image deleted\");
			\$this->webAction = \"update\";
			break;
			
		case 'get':
			if(\$this->POST->id==0){
				\$this->webAction = \"save\";
				
			}else{
				\${title} = \$this->{title}->byId(\$this->POST->id);
				\$this->webAction = \"update\";
				
			}
			\$this->javascript = \$this->JSform();
			break;
			
		case 'list':
		default:
			if(\$this->POST->webSearch){
				parse_str(\$this->POST->webSearch, \$aux);
				\${title} = (object)\$aux;
				\$this->listOrder = (\${title}->webListOrder!=\"\")?\${title}->webListOrder:\"id\";
				\$this->listDirection  =  (\${title}->webListDirection!=\"\")?\${title}->webListDirection:\"asc\";
			}

			\$this->pageIni = \$webPageIni =  (\$this->POST->webPageIni==\"\") ? 0 : \$this->POST->webPageIni;
			\${title}->listOrder = \$this->listOrder;
			\${title}->listDirection = \$this->listDirection;
			
			\${title}s = \$this->{title}->query(\$webPageIni,\${title});
			\$this->searching = \$this->{title}->searching;
			\$this->javascript = \$this->JSwindow();
			break;

	}
	
?>");

define("webModel",
"<?php
	
	class {title}  {
			
		var \$db = NULL;
		var \$web = NULL;
		var \$image_size = {image_size};
		var \$thumb_size = {thumb_size};
		var \$rows_size = {rows_size};
		var \$rows_total = 0;
		var \$tabla = \"{title}\";
		var \$campos = array({fields});
		var \$error = array();
		var \$error_fieldsJS = array();
		var \$error_msg = \"\";
		var \$searching = false;
		var \$error_messages = array ( );
		var \$error_fields = array ( );
		

		public function __construct( \$web )	{
			\$this->web = \$web;
			\$this->db = \$web->db;
			\$this->tabla = \$web->settings->mysql_prefix . \$this->tabla;
		}
		
	    public function get(\${title}) {

			foreach(\${title} as \$campo => \$valor) {
				
		    	switch (\$campo) {
		    		
					default:
						\${title}->\$campo = stripslashes(\$valor);
						break;
				}
				
			}
	    	
			return \${title};
	    }
		
	    public function set(\$key,\$value) {
	    	
	    	switch (\$key) {
				
				default:
					\$res = \$value;
					break;
			}
			
	    	return \$res;
	    }
		
	    public function save(\${title}) {

			\$insert_query_campos =  \"\";
			\$insert_query_values =  \"\";
			\$i = 0;
			
			foreach(\${title} as \$campo => \$valor) {

				if( in_array(\$campo,\$this->campos) ){
					
					\$resValue = \$this->set(\$campo,\$valor);
					
					if( \$resValue !== false  ) {
						if(\$i > 0 ) {
							\$insert_query_values .=  \", \";
							\$insert_query_campos .=  \", \";
						}
						
						if( \$campo != \"id\" ) {
							\$insert_query_campos .=  \$campo;
							\$insert_query_values .= ( \$resValue !== false ) ? \"'\" . \$resValue . \"'\" : \"\";
							\$i++;
						}
					}
					
				}
				
			}
			
			if( count(\$this->error) > 0 ) {
				
				\$this->error_msg = (\$this->error_messages[0]). \"<br/>\";
				
				foreach (\$this->error as \$key => \$value){
					\$this->error_msg .= (\$this->error_messages[\$value]) . \"<br/>\";
					array_push(\$this->error_fieldsJS, \$this->error_fields[\$value]);
				}

			} else {
			
				\$insert_query = \"INSERT INTO  \".\$this->tabla.\" ( \". \$insert_query_campos . \" ) VALUES ( \" . \$insert_query_values . \" )  \";
				\$result = \$this->db->execute(\$insert_query);
				\$id = \$this->db->lastInsertedId();
				\$this->uploadFiles(\$_FILES,\$id);
				
			}
			
			return \$id;
			
	    }
		
	    public function update(\${title}) {
			\$i = 0;
			\$update_query = \"\";

			\$this->uploadFiles(\$_FILES,\${title}->id);

			\$aux_{title} = \$this->db->queryUniqueObject(\"SELECT * FROM \".\$this->tabla.\" WHERE id = \".\${title}->id);
			
			foreach(\$aux_{title} as \$campo => \$valor) {

				if( in_array(\$campo,\$this->campos) ){
					\$val = ( \${title}->\$campo != \$valor ) ? \${title}->\$campo : \$valor ;
		
					\$resValue = \$this->set(\$campo,\$val);
					
					if( \$resValue !== false ){
						
						\$update_query .= ( \$i > 0 ) ? \" ,\" : \"\"; 
						\$update_query .=  \$campo . \" = '\" .  \$resValue . \"' \";		
						\$i++;
						
					}
					
				}
				
			}

			if( count(\$this->error) > 0 ) {
				
				\$this->error_msg = (\$this->error_messages[0]). \"<br/>\";
				
				foreach (\$this->error as \$key => \$value) {
					\$this->error_msg .= (\$this->error_messages[\$value]) . \"<br/>\";
					array_push(\$this->error_fieldsJS, \$this->error_fields[\$value]);
				}

			} else {
				
				\$update_query  = \"UPDATE  \".\$this->tabla.\" SET \" . \$update_query . \" WHERE id = '\" . \${title}->id . \"' \";
				\$result = \$this->db->execute(\$update_query);
				
			}

	    }
		
	    public function delete(\$id) {

			\$this->db->execute(\"DELETE FROM \".\$this->tabla.\" WHERE id = \".\$id);
	    	
	    }
	
	    public function query(\$iniRow=0,\$search = null) {
	    	\$res_data = array();
			
			\$where = \" WHERE 1 \";
			if(!is_null(\$search)){
				foreach(\$search as \$campo => \$valor) {
					if(in_array(\$campo,\$this->campos) && \$valor != \"\"){
						
						\$this->searching = true;
						
				    	switch (\$campo) {
							
							case 'active':
							case 'level':
								\$where .=  \" AND \" . \$campo . \" = '\" .  \$valor. \"' \";	
								break;
								
							default:
								\$where .=  \" AND \" . \$campo . \" LIKE '%\" .  \$valor. \"%' \";	
								break;
						}
						
					}
				}
			}
			
			\$order = (is_null(\$search)) ? \"\" :  \" ORDER BY \".\$search->listOrder.\" \".\$search->listDirection;
			
			\$this->rows_total = \$this->db->queryUniqueValue(\"SELECT count(*) FROM \".\$this->tabla.\" \".\$where.\"\");

			\$aux_query = \$this->db->query(\"SELECT * FROM \".\$this->tabla.\" \".\$where.\" \" . \$order . \"  LIMIT \" . \$iniRow . \", \" .  \$this->rows_size);

			while (\$data = \$this->db->fetchNextObject(\$aux_query)) {
				array_push(\$res_data, \$this->get(\$data));
			}
			
			return \$res_data;
	    }

	    public function byId(\$id) {
	    	
			\$res_data = \$this->db->queryUniqueObject(\"SELECT *  FROM \".\$this->tabla.\" WHERE id = \".\$id);
			
			return \$this->get(\$res_data);
	    }
		
		public function uploadFiles( \$files, \$id ) {
			
			\$fname = get_class(\$this);
			
			{upload_file}
		}
		
	}

	\$this->{title} = new {title}(\$this);
	
?>");

define("webAttachFile","
							if(\$files['{field}']){
				
								\$fname = \$fname . \"_{field}\";
								
								\$uploads_folder = RELPATH.\"upload\";
								if(!is_dir(\$uploads_folder)) mkdir(\$uploads_folder);
								\$uploads_folder = \$uploads_folder . \"/\". \$fname .\"\";
								if(!is_dir(\$uploads_folder)) mkdir(\$uploads_folder);
								
								\$ext =  strtolower(substr(strrchr(\$files['{field}']['name'], \".\"),1));
								
								\$auxFiles = glob( \$uploads_folder.\"/\" . \$fname. \$id . \".*\" );
								if(\$auxFiles)
									array_map( \"unlink\", \$auxFiles );
					
								move_uploaded_file(\$files[\"{field}\"][\"tmp_name\"], \$uploads_folder.\"/\" . \$fname. \$id . \".\" . \$ext );
								
							}");
define("webImageFile","
							if(\$files['{field}']){
								
								\$fname = \$fname . \"_{field}\";
								
								\$images_folder = RELPATH.\"img/\". \$fname .\"\";
								if(!is_dir(\$images_folder)) mkdir(\$images_folder);
								\$imagen = new Imagick(\$files['{field}']['tmp_name']);
								\$imagen->thumbnailImage(\$this->image_size, 0);
								\$imagen->writeImages(\$images_folder.\"/\" . \$fname. \$id . \".png\" ,true);
								
								\$images_folder = RELPATH.\"img/\". \$fname .\"\";
								if(!is_dir(\$images_folder.\"/thumbs\")) mkdir(\$images_folder.\"/thumbs\");
								\$imagen = new Imagick(\$files['{field}']['tmp_name']);
								\$imagen->thumbnailImage(\$this->thumb_size, 0);
								\$imagen->writeImages(\$images_folder.\"/thumbs/\" . \$fname. \$id . \".png\" ,true);
								
								
							}");
define("webGalleryFile","
							if(\$files['{field}']){
				
								\$fname = \$fname . \"_{field}\";
								
								\$images_folder = RELPATH.\"img/\". \$fname .\"\";
								
								\$lastNumImage = 1;
								\$auxFiles = glob( \$images_folder.\"/\".\$fname.\"*.png\" );
								if(\$auxFiles){
									foreach ( \$auxFiles as \$key => \$value) {
										\$auxNum = str_replace(\".png\", \"\", end(explode(\"_\", basename(\$value))));
										if( \$auxNum > \$lastNumImage ){
											\$lastNumImage = \$auxNum ;
										}
									}
								}
								\$lastNumImage++;
								
								if(!is_dir(\$images_folder)) mkdir(\$images_folder);
								\$imagen = new Imagick(\$files['{field}']['tmp_name']);
								\$imagen->thumbnailImage(\$this->image_size, 0);
								\$imagen->writeImages(\$images_folder.\"/\" . \$fname. \$id . \"_\" . \$lastNumImage . \".png\" ,true);
								
								\$images_folder = RELPATH.\"img/\". \$fname .\"\";
								if(!is_dir(\$images_folder.\"/thumbs\")) mkdir(\$images_folder.\"/thumbs\");
								\$imagen = new Imagick(\$files['{field}']['tmp_name']);
								\$imagen->thumbnailImage(\$this->thumb_size, 0);
								\$imagen->writeImages(\$images_folder.\"/thumbs/\" . \$fname. \$id . \"_\" . \$lastNumImage .  \".png\" ,true);
								
							}");
define("webForm",
"<? \$web = new web((object) array(  
														\"model\" => \"{title}\",
														\"control\" => \"{title}\",
														\"theme\" => \"blank\",
														\"level\" => 100
													)); ?>
					
<h2><?=\$web->t('{title}')?></h2>

<form id=\"webForm\" enctype=\"multipart/form-data\" method=\"post\">
	
	<input type=\"hidden\" id=\"id\" name=\"id\" value=\"<?=\${title}->id?>\" />

	{fields_html}

	<input type=\"hidden\" id=\"webAction\" name=\"webAction\" value=\"<?=\$web->webAction?>\" />
	<input type=\"hidden\" id=\"webObject\" name=\"webObject\" value=\"{title}\" />
	
	<div class=\"medium danger icon-right icon-forward btn entypo\" style=\"float: right\"><a href=\"#\" class=\"webSubmit\" ><?=\$web->t(\"save\") ?></a></div>
	
</form>

<i class=\"icon-cancel webButtonClose\" onclick=\"webWindowClose()\"></i>

{JS}");
				
define("webList",
"<? \$web = new web((object) array(  
														\"page_title\" => \"{title}s\",
														\"model\" => \"{title}\",
														\"control\" => \"{title}\",
														\"theme\" =>((!\$_POST)?'admin':'blank'),
														\"level\" => 100
													)); ?>
													
<h1 class=\"webTitle\">
	<?=\$web->t(\"{title}s\") ?>
	<div class=\"small default btn icon-left entypo icon-plus\"><a href=\"#\" onclick=\"webWindowLoad('{title}',0);\" ><?=\$web->t(\"Add\") ?></a></div>
	<div class=\"small default btn icon-left entypo icon-search\"><a href=\"#\" onclick=\"webFiltersToggle();\" ><?=\$web->t(\"Filters\") ?></a></div>
</h1>

{filters}

<table class=\"striped\">
	
	<thead>
		<tr>
			{list_head}
			<th style=\"width: 150px;\"></th>
		</tr>
	</thead>
	
	<tbody>
		<? foreach( \${title}s as \${title} ) { ?>
			<tr>
				{list_values}
				<td>
					<div class=\"small info btn icon-left entypo icon-pencil\"><a href=\"javascript: webWindowLoad('{title}','<?=\${title}->id?>');\" ><?=\$web->t(\"Edit\")?></a></div>
					<div class=\"small info btn icon-left entypo icon-trash\"><a href=\"javascript: webRemove('{title}','<?=\${title}->id?>');\" ><?=\$web->t(\"Delete\")?></a></div>
				</td>
			</tr>
		<? }?>
	</tbody>

</table>

<? \$web->pagination( \$web->{title} ); ?>");
			
define("webFormJwysiwyg",
"<script src=\"/js/jwysiwyg/jwysiwyg.js\" type=\"text/javascript\"></script>
<script src=\"/js/jwysiwyg/wysiwyg.image.js\" type=\"text/javascript\"></script>
<script src=\"/js/jwysiwyg/wysiwyg.link.js\" type=\"text/javascript\"></script>
<script src=\"/js/jwysiwyg/wysiwyg.table.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
(function(\$) {
	\$(document).ready(function() {
		\$('{field}').wysiwyg({
		  controls: {
			bold          : { visible : true },
			italic        : { visible : true },
			underline     : { visible : true },
			strikeThrough : { visible : true },
			
			justifyLeft   : { visible : true },
			justifyCenter : { visible : true },
			justifyRight  : { visible : true },
			justifyFull   : { visible : false },

			indent  : { visible : false },
			outdent : { visible : false },

			code : { visible: false },
			insertTable : { visible: false },
			subscript   : { visible : false },
			superscript : { visible : false },
			
			undo : { visible : true },
			redo : { visible : true },
			insertOrderedList    : { visible : false },
			insertUnorderedList  : { visible : false },
			insertHorizontalRule : { visible : false },

			h1   : { visible : false },
			h2   : { visible : false },
			h3   : { visible : false },

			cut   : { visible : true },
			copy  : { visible : true },
			paste : { visible : true },
			html  : { visible: true },
			
			increaseFontSize : { visible : true },
			decreaseFontSize : { visible : true },
			exam_html: { visible: false }
		  }
		});
	});
})(jQuery);
</script>");	
				
?>