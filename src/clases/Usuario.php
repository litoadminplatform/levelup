<?php namespace clases; 
class Usuario{
	private $id = false;
	private $username = false;
	private $firstname = false;
	private $lastname = false;
	private $email = false;
	private $idnumber = false;
	private $suspended = false;
	private $deleted = false;
	private $lastaccess = false;	
	private $firstaccess = false;			
	private $picture  = false;
	private $city = false;
	private $lang = false;
	private $description = false;
	private $hashp = false;
	private $policyagreed = false;
	private $datospersonalizados = array();  //(Custom fields)
	private $completionscursos = array();
	private $actividadesmarcadasfinalizadas = array();
	
	private $areaspreferidas = array();
	
	private $conexion;	
	private $comun;

	function __construct(&$conexionset, $idusuario=false){
		$this->conexion = $conexionset;		
		$this->comun = new Comun($this->conexion);
		
		
		
		if($idusuario && is_numeric($idusuario)){ 
			$sql = 'select id, username, firstname, lastname, email, idnumber, suspended, deleted, firstaccess, lastaccess, picture, city, lang, description, password, policyagreed
					from mdl_user
					where id=\''.$idusuario.'\'';
			$result_buscar = $this->conexion->consultar($sql);	
			if($r = pg_fetch_array($result_buscar)){
				$this->id = $r['id'];
				$this->username = $r['username'];
				$this->firstname = $r['firstname'];
				$this->lastname = $r['lastname'];
				$this->email = $r['email'];
				$this->idnumber = $r['idnumber'];
				$this->suspended = $r['suspended'];
				$this->deleted = $r['deleted'];
				$this->firstaccess = $r['firstaccess'];
				$this->lastaccess = $r['lastaccess'];
				$this->picture = $r['picture'];
				$this->city = $r['city'];
				$this->lang = $r['lang'];
				$this->description = $r['description'];						//aqui se guardan las areas preferidas separadas por coma
				$this->hashp = $r['password'];
				$this->policyagreed = $r['policyagreed'];
				
				if($this->description!=''){
					$filtro = new Filtro();
					$areas = (array)json_decode($this->description);
					if(json_last_error()===JSON_ERROR_NONE){
						foreach($areas as $area){
							//$es = (array)$es; //no se usa esta linea por que internamente no es un array
							if($filtro->soloNumeros($area) && $filtro->limiteTamano($area, 1, 2)){
								array_push($this->areaspreferidas, $area);
							}
						}
					}
				}
			}
		}
	}
	
	/*
		Matricula este usuario en un idcurso especifico
		retorna ok si todo fue bien, o otro mensaje en caso de error
		NOTA: Antes de ejecutar esta funcion verificar que el usuario no esté matriculado en este curso con matriculacion manual.
	*/
	public function matriculaEnCurso($idcurso){
		
		global $PAGE, $DB, $CFG;
		
		$retornar = 'error-desconocido';
		if($this->id){

			require_once($CFG->dirroot.'/course/lib.php');
			require_once($CFG->dirroot.'/course/externallib.php');	
			require_once($CFG->dirroot.'/user/externallib.php');
			
			$PAGE->set_context(\context_system::instance());	
						
			$enrolplugin = enrol_get_plugin('manual');
			$enrolpluginself = enrol_get_plugin('self');
			
			//miramos si el curso tiene la matrculacion manual activada.
			$instance = false;
			$instances = enrol_get_instances($idcurso, true);
			foreach($instances as $instance) {
				if ($instance->enrol === 'manual') {
					break;																
				}
			}
			if($instance->enrol !== 'manual'){
				$retornar = 'curso-sin-matriculacion-manual';
			}else{				
				$enrolplugin->enrol_user($instance, $this->id, 5);
				$retornar = 'ok';
			}			
			//fin de ver si tiene la matriculacion manual activada			
		}
		return $retornar;
	}	
			
	/*
		Desmatricula este usuario en un idcurso especifico
		retorna ok si todo fue bien, o otro mensaje en caso de error
		NOTA: Antes de ejecutar esta funcion verificar que el usuario esté matriculado en este curso con matriculacion manual.
	*/
	public function desmatriculaEnCurso($idcurso){
		
		global $PAGE, $DB, $CFG;
		
		$retornar = 'error-desconocido';
		if($this->id){

			require_once($CFG->dirroot.'/course/lib.php');
			require_once($CFG->dirroot.'/course/externallib.php');	
			require_once($CFG->dirroot.'/user/externallib.php');
			
			$PAGE->set_context(\context_system::instance());	
						
			$enrolplugin = enrol_get_plugin('manual');
			$enrolpluginself = enrol_get_plugin('self');
			
			//miramos si el curso tiene la matrculacion manual activada.
			$instance = false;
			$instances = enrol_get_instances($idcurso, true);
			foreach($instances as $instance) {
				if ($instance->enrol === 'manual') {
					break;																
				}
			}
			if($instance->enrol !== 'manual'){
				$retornar = 'curso-sin-matriculacion-manual';
			}else{				
				$enrolplugin->unenrol_user($instance, $this->id, 5);
				$retornar = 'ok';
			}			
			//fin de ver si tiene la matriculacion manual activada			
		}
		return $retornar;
	}		
	
	/*
		Retrona las facturas cronologicamente de la mas reciente a la mas antigua
		$estado: si se desea que solo retorne las facturas en un estado especifico colocar el codigo del estado aqui.
		LAS PAGINAS SON DE A 10 en 10 Y SE EMPIEZA POR LA 0.  -1 retorna todas, 0 la primera pagina, 1 la segunda pagina..		
	*/
	public function getFacturas($idcurso = -1, $estado = array(), $pagina=-1, $limitepagina=10){
		$retornar = array();
		if($this->id){
			$inyectar = '';
			
			if(count($estado)>0){
				for($i=0; $i<count($estado); $i++){
					if(is_numeric($estado[$i])){
						if($inyectar!=''){
							$inyectar.=' or ';
						}
						$inyectar.=' estado='.$estado[$i].' ';
					}
				}
				if($inyectar!=''){
					$inyectar = ' and ('.$inyectar.')';
				}
			}
			
			$inyectarpagina = '';
			if($pagina!=-1){
				$startfrom = $pagina*$limitepagina;
				$inyectarpagina = ' limit '.$startfrom.', '.$limitepagina; 
			}
			
			$inyectaridcurso = '';
			if($idcurso!=-1){
				$inyectaridcurso = ' and idcurso=\''.$idcurso.'\' ';
			}
			
			$sql='select id, idusuario, idcurso, consecutivo, fechacarritogenerado, fechacheckout, fechafacturagenerada, total, estado, tituloultimarespuesta, descripcionultimarespuesta, respuestadato1, respuestadato2, respuestadato3, respuestadato4
					from factura
					where idusuario=\''.$this->id.'\' '.$inyectaridcurso.' '.$inyectar.'
					order by fechacarritogenerado desc 
					'.$inyectarpagina.'';
			$result_d = $this->conexion->consultar($sql); 
			if($row_d = pg_fetch_array($result_d)){
				do{
					array_push($retornar, array('id'=>$row_d['id'], 'idusuario'=>$row_d['idusuario'], 'idcurso'=>$row_d['idcurso'], 'consecutivo'=>$row_d['consecutivo'], 'fechacarritogenerado'=>$row_d['fechacarritogenerado'], 'fechacheckout'=>$row_d['fechacheckout'], 'fechafacturagenerada'=>$row_d['fechafacturagenerada'],  'total'=>$row_d['total'], 'estado'=>$row_d['estado'], 'tituloultimarespuesta'=>$row_d['tituloultimarespuesta'], 'descripcionultimarespuesta'=>$row_d['descripcionultimarespuesta'], 'respuestadato1'=>$row_d['respuestadato1'], 'respuestadato2'=>$row_d['respuestadato2'], 'respuestadato3'=>$row_d['respuestadato3'], 'respuestadato4'=>$row_d['respuestadato4']));
				}while($row_d = pg_fetch_array($result_d));			
			}
		}
		return $retornar;
	}
	
	/*
		Retorna si tiene asignado un rol especifico en moodle.
		$contextid:  1 sistema (aqui se asignan los roles de sistema)
		$roleid: el id de rol de sistema
		
	*/
	public function getAsignacionRol($contextid=-1, $roleid=-1){
		$retornar = array();
		if($this->id){
			
			$inyectar = '';
			if($contextid!=-1){
				$inyectar = ' and contextid=\''.$contextid.'\' ';				
			}
			if($roleid!=-1){
				$inyectar.= ' and roleid=\''.$roleid.'\' ';				
			}
			
			$sql = 'select id, roleid, contextid, userid, timemodified
					from mdl_role_assignments
					where userid=\''.$this->id.'\' '.$inyectar.' ';  //echo 'xxx'.$sql.'xxx';
			$result_d = $this->conexion->consultar($sql); 
			if($row_d = pg_fetch_array($result_d)){
				do{
					$retornar[] = array('id'=>$row_d['id'], 'roleid'=>$row_d['roleid'], 'contextid'=>$row_d['contextid'], 'userid'=>$row_d['userid'], 'timemodified'=>$row_d['timemodified']);
					
				}while($row_d = pg_fetch_array($result_d));								
			}
		}
		return $retornar;
	}
	
	/*	NO HA SIDO PROBADO PARA LEVEL UP
		valida los datos para un usuario nuevo.	
		lang: es el lenguaje actual: es, en. etc
		$idpais: envie 0 para que se tome el pais de la estacion automaticmaente y no haya que tener que hacer comparaciones
		Advertencia se debe haber incluido el archivo config.php para que funcione correctamente. (hace uso de la variable global DB)		
	*/
	public function validaDatosUsuario($numerolinea, &$nombres, &$apellidos, &$email, &$identificacion, &$telefono){
		$reportelinea = array();
		global $DB;
		if(!$this->id){
									
			$filtro = new Filtro();
			$sitio = new Sitio($this->conexion);						
									
			$reportelinea = array('numerolinea'=>$numerolinea, 'error'=>'no', 'errorgeneral'=>'', 'username'=>'', 'idnumber'=>'', 'nombresv'=>'', 'nombrese'=>'', 'apellidosv'=>'', 'apellidose'=>'', 'emailv'=>'', 'emaile'=>'', 'identificacionv'=>'', 'identificacione'=>'');
			
			
			//valida nombres
			$nombres = mb_strtoupper($nombres, 'UTF-8');
			if($nombres!=''){
				if($filtro->soloLetrasYespaciosEspecial($nombres) && $filtro->limiteTamano($nombres, 2, 256)){
					$reportelinea['nombresv'] = $nombres;
				}else{
					$reportelinea['nombresv'] = $nombres;
					$reportelinea['nombrese'] = 'Nombre inválido.';					
					$reportelinea['error'] = 'si';
				}
			}else{
				$reportelinea['nombrese'] = 'Dato vacío';
				$reportelinea['error'] = 'si';
			}
			
			//valida apellidos
			$apellidos = mb_strtoupper($apellidos, 'UTF-8');
			if($apellidos!=''){
				if($filtro->soloLetrasYespaciosEspecial($apellidos) && $filtro->limiteTamano($apellidos, 2, 256)){
					$reportelinea['apellidosv'] = $apellidos;
				}else{
					$reportelinea['apellidosv'] = $apellidos;
					$reportelinea['apellidose'] = 'Apellidos inválidos.';
					$reportelinea['error'] = 'si';
				}
			}else{
				$reportelinea['apellidose'] = 'Dato vacío';
				$reportelinea['error'] = 'si';
			}
			
			//email
			//$email = strtolower($email);										
			if($email!=''){
				if($filtro->validaEmail($email) && $filtro->limiteTamano($email, 5, 256)){
					if(!$otrouser=$DB->get_record('user', ['email' => $email])){
						$reportelinea['emailv'] = $email;
					}else{
						$reportelinea['emailv'] = $email;
						//$reportelinea['emaile'] = 'El correo ya se encuentra registrado '.$otrouser->firstname.' '.$otrouser->lastname.' con id: '.$otrouser->id.'';
						$reportelinea['emaile'] = 'El correo ya se encuentra registrado.';
						$reportelinea['error'] = 'si';
					}																								
				}else{
					$reportelinea['emaile'] = 'Formato de correo inválido';
					$reportelinea['error'] = 'si';
				}
			}else{
				$reportelinea['emaile'] = 'Dato vacío';
				$reportelinea['error'] = 'si';
			}
			
			//identificacion
			if($identificacion!=''){
				if($filtro->limiteTamano($identificacion, 6, 64)){
					$reportelinea['idnumber'] = $identificacion;
					if($identificacion!==false){
						if(($filtro->soloNumeros($identificacion)) || $filtro->soloNumerosyLetrasMinusculas(strtolower($identificacion))) {
							$reportelinea['identificacionv'] = $identificacion;
						}else{
							$reportelinea['identificacionv'] = $identificacion;
							$reportelinea['identificacione'] = 'Indentificación debe ser solo números y letras minúsculas.';
							$reportelinea['error'] = 'si';
						}												
					}else{
						$reportelinea['identificacione'] = 'Dato erróneo';
						$reportelinea['error'] = 'si';
					}
				}else{
					$reportelinea['identificacione'] = 'Identificación muy corta.';
					$reportelinea['error'] = 'si';
				}
			}else{
				$reportelinea['identificacione'] = 'Dato vacío';
				$reportelinea['error'] = 'si';
			}
			
			//identificacion
			if($telefono!=''){
				if($filtro->limiteTamano($telefono, 10, 10)){
					$reportelinea['telefono'] = $telefono;
					if($telefono!==false){
						if($filtro->soloNumeros($telefono)) {
							$reportelinea['telefonov'] = $telefono;
						}else{
							$reportelinea['telefonov'] = $telefono;
							$reportelinea['telefonoe'] = 'Teléfono movil debe ser solo los números.';
							$reportelinea['error'] = 'si';
						}												
					}else{
						$reportelinea['telefonoe'] = 'Dato erróneo';
						$reportelinea['error'] = 'si';
					}
				}else{
					$reportelinea['telefonoe'] = 'Número de teléfono incompleto.';
					$reportelinea['error'] = 'si';
				}
			}else{
				$reportelinea['telefonoe'] = 'Dato vacío';
				$reportelinea['error'] = 'si';
			}
						
			//ultimas conficiones:
			if($reportelinea['error']=='no'){  //si no hubo error.							
				//creamos el username
				$reportelinea['username'] = strtolower($reportelinea['emailv']);											
				if($DB->get_record('user', ['username' => $reportelinea['username'], 'deleted'=>'0'])){
					$reportelinea['error'] = 'si';
					$reportelinea['errorgeneral'] = 'El correo ('.$reportelinea['username'].') ya lo tiene otro usuario, si es usted mismo le recomendamos recuperar la cuenta.';					
				}else{
					if($DB->get_record('user', ['idnumber' => $reportelinea['idnumber'], 'deleted'=>'0'])){
						$reportelinea['error'] = 'si';
						$reportelinea['errorgeneral'] = 'La identificación ('.$reportelinea['idnumber'].') ya está siendo usada por otro usuario.';
					}						
				}
			}										
		}
		return $reportelinea;
	}	


	public function getDato($campo){
		if($this->id){
			$campovalidos = array('id', 'username', 'firstname', 'lastname', 'email', 'idnumber', 'suspended', 'deleted', 'firstaccess', 'lastaccess', 'picture', 'city', 'lang', 'description', 'hashp', 'policyagreed', 'areaspreferidas');
			if(in_array($campo, $campovalidos)){
				return $this->$campo;								
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	

	public function setDato($campo, $valor){
		$retornar = false;
		if($this->id){
			$valido = false;
			switch($campo){
				case 'description':	//aqui van las areas preferidas del usuario
					$pruebaerror = (array)json_decode($valor);
					if(json_last_error()===JSON_ERROR_NONE){
						$valido = true;
					}	
				break;
				case 'lang':
					if($valor!=''){
						$valido = true;
					}	
				break;
				case 'policyagreed':
					if($valor==1){
						$valido = true;
					}	
				break;
			}
			if($valido){
				$sql_up='UPDATE mdl_user SET '.$campo.'=\''.$valor.'\' WHERE id=\''.$this->id.'\'';
				if($this->conexion->actualizar($sql_up)){
					$this->$campo = $valor;					
					$retornar = true;
				}
			}
		}
		return $retornar;
	}		
	
 }	
?>