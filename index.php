<?php	

	if(!isset($USER)){
		require "../../../config.php";
		global $USER, $SESSION, $PAGE, $CFG, $DB, $OUTPUT;
	}
	
	include_once('defines.php');
	include_once('autoload.php');	
	clases\Enrutador::run(new clases\Request());	
?>