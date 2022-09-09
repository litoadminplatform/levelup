<?php

	//ESTE DEBE INCLUIRSE EN LOS ARCHIVOS DE LA CARPETA themes/nombre_tema_actual/layout que lo requieran, de la siguiente forma: include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/htmladicionalhead.php');
	global $USER, $SESSION, $PAGE, $CFG, $DB, $OUTPUT;
	include_once('autoload.php');

	/*define('USER', $USER);
	define('SESSION', $SESSION);
	define('PAGE', $PAGE);
	define('CFG', $CFG);*/

	//include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Conexion.php');
	$conexion = new clases\Conexion();

	//$urlescrita = PROTOCOLO.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$urlpartes = array_filter(explode($CFG->wwwroot, $this->page->url));		//reemplazar $this->page->url por $urlescrita si algo falla
	$urlcontenido = end($urlpartes);	//incluye el / inicial
	//echo ' es1:'.$urlcontenido;
	if(substr($urlcontenido, 0, 1)=='/'){
		$urlcontenido = substr($urlcontenido, 1, strlen($urlcontenido));	//le quitamos la barra del cominezo
	}
	//echo ' es2:'.$urlcontenido;
	//if(!isloggedin()){ //vemos si hay que mostrar la landing page si se esta tratando de mostrar el index.php (mas no el ligon/index.php que es otro)
		//vemos si estamos en la pagina de inicio.

		$urlproyecto = $CFG->wwwroot.'/theme/'.$CFG->theme.'/americana/src/';
		$rutaproyecto = $CFG->dirroot.'/theme/'.$CFG->theme.'/americana/';

		$estaenindex = false;
		$reedirectlogin = false;

		$enindex = array('index.php', '');	// 'login/index.php', 'login/'  (tambien tenia estos pero esta pagina no la queremos bloquear.)
		$enlogin = array('login/index.php', 'login/');

		for($i=0; $i<count($enindex); $i++){
			if($urlcontenido==$enindex[$i]){
				$estaenindex = true;
			}
		}
		for($j=0; $j<count($enlogin); $j++){
			if($urlcontenido==$enlogin[$j]){
				$reedirectlogin = true;
			}
		}


		if($estaenindex){
			ob_clean();
			include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/index.php');
			exit;
		}
	/*}else{
		//si inició sesion miramos que este en alguna de estas páginas para reedirecionar.
		$reedirecciones = array(
			array('n'=>'dashboard', 		'c'=>array(URLBASE.'my', URLBASE.'my/'), 'r'=>'dashboard.php'),
			array('n'=>'cambiarcontrasena', 'c'=>array(URLBASE.'login/change_password.php'), 'r'=>'otros.php?pantalla=cambiarcontrasena'),
			array('n'=>'politicas', 		'c'=>array(URLBASE.'user/policy.php'), 'r'=>'otros.php?pantalla=politicas'),
			array('n'=>'imagenusuario', 	'c'=>array(URLBASE.'user/edit.php'), 'r'=>'otros.php?pantalla=imagenusuario'),
			array('n'=>'enroll', 			'c'=>array(URLBASE.'enrol/index.php'), 'r'=>'otros.php?pantalla=verlandingcurso'),
			array('n'=>'notas', 			'c'=>array(URLBASE.'grade/report/user/index.php'), 'r'=>'xxx'),			//solo encontrar y dejar pasar
			array('n'=>'calendario', 		'c'=>array(URLBASE.'calendar/view.php?'), 'r'=>'xxx'),			//solo encontrar y dejar pasar
			array('n'=>'calendarioexport', 	'c'=>array(URLBASE.'calendar/export.php'), 'r'=>'xxx'),			//solo encontrar y dejar pasar
			array('n'=>'calendarioimport', 	'c'=>array(URLBASE.'calendar/managesubscriptions.php'), 'r'=>'xxx'),			//solo encontrar y dejar pasar
			array('n'=>'imagencurso', 		'c'=>array(URLBASE.'pluginfile.php'), 'r'=>'xxxx'),	//solo encontrar y dejar pasar
			array('n'=>'curso', 			'c'=>array(URLBASE.'course/view.php'), 'r'=>'curso.php'),
		);
		$encontrado = false;
		foreach($reedirecciones as $re){
			foreach($re['c'] as $buscar){
				 if(strpos($urlescrita, $buscar)!==false){
					switch($re['n']){
						case 'calendario':
							$encontrado = true;
						break;
						case 'calendarioexport':
							$encontrado = true;
						break;
						case 'calendarioimport':
							$encontrado = true;
						break;
						case 'imagencurso':
							$encontrado = true;
						break;
						case 'notas':
							$encontrado = true;
						break;
						case 'dashboard':
							$encontrado = true;
							ob_clean();
							include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/dashboard.php');
							exit;
						break;
						case 'curso':	//si es el curso necesitamos llegar a su id.
							$partes3 = explode('id=', $urlescrita);
							if(count($partes3)==2){	//si solo tiene el ?id=234 y ya bien, de lo contrario se descarta.
								$encontrado = true;
								ob_clean();
								include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/curso.php');
								exit;
							}
						break;
						case 'imagenusuario':
							$encontrado = true;
							if(isset($_GET['new']) && $_GET['new']=='true'){
								ob_clean();
								include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/otros.php');
								exit;
							}
						break;
						case 'enroll':
							$encontrado = true;
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/model/Filtro.php');
							$filtro = new Filtro();
							if(isset($_GET['id']) && $filtro->soloNumeros($_GET['id']) && $filtro->limiteTamano($_GET['id'], 1, 4)){
								include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
								$curso = new Curso($conexion, $_GET['id']);
								if($curso->getDato('id')){
									$partes = explode('-', $curso->getDato('shortname'));
									if(count($partes)==2){
										include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
										$sitio = new Sitio($conexion);
										$info = $sitio->getTiposCurso($partes[0], true, false, true);
										if(count($info)>0){
											header('Location: '.URLBASE.$info[0]['urlamigable'].'_'.$info[0]['codigocurso']);
											exit;
										}
									}
								}
							}
						break;
						case 'cambiarcontrasena':
							$encontrado = true;
							if(isset($_GET['new']) && $_GET['new']=='true'){
								ob_clean();
								include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/otros.php');
								exit;
							}
						break;
						case 'recuperarcuenta':
							$encontrado = true;
							if(isset($_GET['new']) && $_GET['new']=='true'){
								ob_clean();
								include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/otros.php');
								exit;
							}
						break;
						default:
							$encontrado = true;
							ob_clean();
							include_once($CFG->dirroot.'/theme/boost/americana/plantillainterna/otros.php');
							exit;
						break;
					}

				}
			}
		}
		if(!$encontrado){
			if(strpos($urlescrita, '/mod/')===false){	//si no estas navegando a través de un modulo se sale
				ob_clean();
				header('Location: '.URLBASE.'my');
				exit;
			}
		}

	}	*/

?>