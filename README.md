fritter
=======

Back-end in php, jquery and gumby framework to make easy create dynamical web sites. 


# REQUERIMENTS

	+ PHP 5.2
	+ MySQL (optional)
	+ Mod rewrite enabled
	+ Imagick
	
	
# FILES & FOLDERS

	/: On the root folder are the single pages/views.
		+ admin: Views of admin manager.
		+ application: Content a development files with model, controllers, libs & Boot stramp.
			+ controllers: Files for control the actions of a modules.
			+ libs: Files for main, db, mail libraries.
			+ models: Folder that contain model class of the application.
			- .htaccess: File to block access to application folder.
			- run.php: Basical file for boot the view selected in the url, run install, manage admin redirections.
		+ css: Folder for all css of the web.
		+ fonts: Fonts for font-face css.
		+ img: Web & admin images.
		+ js: Folder for al js files and libraries.
		+ themes: Folder with the themes of the web.
			+ default: Main theme for undefiend web theme in the view.
			+ admin: Admin theme.
		+ upload: Private uploads folder.
			- .htaccess: File to block access to upload folder.

	
# DESIGN

	+ Recursos PSD con el grid de columnas y UI Kit
		- http://gumbyframework.com/docs/designers/#!/modular-scale
		
	+ Logo for Facebook, Apple, G+, etc...
		- image.png  ( 200 x 200 pixels )
		- favicon.png  ( 16 x 16 pixels )
		
	+ Definicion de columnas: 
		- http://gumbyframework.com/docs/grid/#!/basic-grid

	+ Estilos predefinidos con gumby: 
		- http://gumbyframework.com/docs/ui-kit/
	

# TODO

	+ Testing IE7+, Safari, Chrome, Opera ...
	+ Integrar el cambio de idioma.
	+ Como crear un theme/plantilla
	+ Como crear paginas
	+ Como añadir layouts
	+ Como crear un modulo con JSON
	+ Como añadir campos a un modulos con JSON
	+ Gestion de configuraciones del administrador
	

# THANKS TO

	+ JQuery 1.9 ( http://www.jquery.com )
	+ JQuery UI 1.9.2 ( http://jqueryui.com/ )
	+ Gumby CSS Framework ( http://gumbyframework.com )
	+ WYSIWYG - jQuery plugin 0.97 ( https://github.com/akzhan/jwysiwyg )
	+ PHPMailer 2.0.2 ( http://phpmailer.sourceforge.net ) 
	+ PHP Advanced Database Class ( http://slaout.linux62.org/ )
	+ Modernizr ( http://modernizr.com/ )
	
