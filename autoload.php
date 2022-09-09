<?php

	define('DS', DIRECTORY_SEPARATOR);
	define('ROOT', realpath(dirname(__FILE__)).DS);
	
	spl_autoload_register(function($clase){
		$ignorarautoload = array('enrol_cohort_plugin', 'enrol_manual_plugin', 'enrol_guest_plugin', 'enrol_self_plugin');
		if(!in_array($clase, $ignorarautoload)){
			//$ruta = ROOT.'src'.DS.'modelos'.DS.''.$clase.'.php';
			
			$ruta = ROOT.'src'.DS.''.str_replace("\\", "/", $clase).'.php';		
			
			if(is_readable($ruta)){
				require_once $ruta;
			}else{
				echo 'El archivo en autoload no existe: '.$ruta;
			}
		}
	});
?>