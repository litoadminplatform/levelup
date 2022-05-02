<?php

	//incluir este archivo cuando ya se haya incluido config.php previamente
	define('URLBASE', $CFG->wwwroot);
	define('URLPROYECTO', $CFG->wwwroot.'/theme/'.$CFG->theme.'/americana/src/');		//Esta es la que se deberia usar.	
	define('URLPLANTILLA', $CFG->wwwroot.'/theme/'.$CFG->theme.'/americana/src/vistas/');		//esta se deberia dejar de usar.
	define('RUTAPROYECTO', $CFG->dirroot.'/theme/'.$CFG->theme.'/americana/');
?>