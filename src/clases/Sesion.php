<?php namespace clases;
class Sesion{
	private $conexion;
	private $idusuario = 0;

	function __construct($conexionset, $idusuario){
		global $USER;
		$this->conexion=$conexionset;
		$this->idusuario = $USER->id;


		$webview = false;
		if((strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile/') !== false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari/') == false)) :
			$webview = true;
		elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) :
			$webview = true;
		endif;

		if($webview){
			$_SESSION['tiponavegacion'] = 'webview';
		}else{
			$_SESSION['tiponavegacion'] = 'normal';
		}

		//$_SESSION['landingactivado'] = 0;  //ojo borrar esta linea cuando terminen las pruebas
	}

	/*
		Establece o cambia un dato de la sesion, aquellos que se crean al iniciar una nueva seion, o los mismos que se destruyen al invocar la funcion cerrarSesion()
		retorna true si fue establecido, false si no.
	*/
	public function setDatoSesion($dato, $valor){
		$datospermiitos = array('mensajefotomostrado', 'landingactivado', 'otros', 'stringmesadeayuda', 'cuponporcentaje', 'urlreedireccionar');
		$retornar = false;
		if(in_array($dato, $datospermiitos)){
			$_SESSION[$dato] = $valor;
			$retornar = true;
		}
		return $retornar;
	}

	/*
		Retorna uno de los datos típicos de una sesión, los cuales se crean automaticamente al crearse una nueva sesion, los mismos que se destruyen al invocar cerrarSesion()
		$dato: el dato a obtener
	*/
	public function getDatoSesion($dato){
		$datospermiitos = array('mensajefotomostrado', 'versionandroid', 'tiponavegacion', 'landingactivado', 'otros', 'stringmesadeayuda', 'cuponporcentaje', 'urlreedireccionar');
		$retornar = '';
		if(in_array($dato, $datospermiitos)){
			if(isset($_SESSION[$dato])){
				$retornar = $_SESSION[$dato];
			}
		}
		return $retornar;
	}

	/*
		Retorna los usuarios a los que se  les ha asignado roles distintos a los normales osea aquellos roles que sean superior a 8. de la tabla '.PREFIJO.'role
	*/
	function getUsuariosConRolesEspeciales(){
		$retornar = array();
		$sql='select DISTINCT a.userid, b.name, c.firstname, c.lastname
				from mdl_role_assignments a, mdl_role b, mdl_user c
				where a.roleid>8 and a.roleid=b.id and a.userid=c.id';
		$result_d = $this->conexion->consultar($sql);
		if($row_d = mysqli_fetch_array($result_d)){
			do{
				array_push($retornar, array('userid'=>$row_d['userid'], 'name'=>'name', 'firstname'=>'firstname', 'lastname'=>$row_d['lastname']));
			}while($row_d = mysqli_fetch_array($result_d));
		}
		return $retornar;
	}


	/*
		Inicia sesion en moodle.
		Nota importante: Requiere importar previamente las librerias de moodle:
			require_once('rootmoodle/config.php');
			require_once('rootmoodle/login/lib.php');
		retorna true si inicio sesion, false si no.
	*/
	public function iniciarSesionMoodle($usuario, $contrasena){
		$retornar = false;
		if(function_exists('authenticate_user_login')){
			$rason = null;
			$user = authenticate_user_login($usuario, $contrasena, false, $rason);
			if($user){
				complete_user_login($user);
				$retornar = true;
			}
		}
		return $retornar;
	}


	//exito!
	public function setExito($mensaje){
		$_SESSION['exito'] = $mensaje;
	}

	public function getExito(){
		if(isset($_SESSION['exito'])){
			return $_SESSION['exito'];
		}else{
			return '';
		}
	}

	//error general controlador
	public function setErrorGeneral($mensaje){
		$_SESSION['errorgeneral'] = $mensaje;
	}

	public function getErrorGeneral(){
		if(isset($_SESSION['errorgeneral'])){
			return $_SESSION['errorgeneral'];
		}else{
			return '';
		}
	}
	//$soloformulario solo borrara los datos del fomulario y los demas mensajes  y errores permaneceran. es util para cuando hay un exito y se desea permanecer en la pantalla del formularo mostrando el mensaje pero no mostrando los datos que se usaron para rellenar el forumulario.
	public function resetData($soloformulario = false){
		$_SESSION['data'] = array();
		if(!$soloformulario){	//si no solo el formulario se va a borrar, se borra todo lo demás.
			$_SESSION['error'] = array();
			$_SESSION['exito'] = '';
			$_SESSION['errorgeneral'] = '';
		}
	}


	public function cerrarSesion(){
		if(isset($_SESSION['landingactivado'])){ $_SESSION['landingactivado'] = 1; unset($_SESSION['landingactivado']); }
		if(isset($_SESSION['stringmesadeayuda'])){ $_SESSION['stringmesadeayuda'] = 0; unset($_SESSION['stringmesadeayuda']); }
		if(isset($_SESSION['mensajefotomostrado'])){ $_SESSION['mensajefotomostrado'] = false; unset($_SESSION['mensajefotomostrado']); }
	}
}
?>