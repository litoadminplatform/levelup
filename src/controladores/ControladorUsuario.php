<?php namespace controladores;
	
	use clases\Sesion as Sesion;	
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;
	use clases\Usuario as Usuario;
	use clases\Email as Email;
	use clases\Factura as Factura;
	use clases\Sitio as Sitio;
	use \stdClass as stdClass;
	
	class ControladorUsuario{
		private $conexion = false;
		private $sesion = false;				
		private $filtro = false;
		
		public function __construct(&$conexion){
			global $USER, $SESSION, $PAGE, $CFG;
			$this->conexion = $conexion;
			$this->sesion = new Sesion($this->conexion, $USER->id);
			$this->filtro = new Filtro();
			header("Content-Type: text/html;charset=utf-8");
			set_time_limit(600);  //10 minutos corriendo.
			date_default_timezone_set('America/Bogota');
		}
		
		public function index(){
			$respuesta['estado'] = 'ok';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
									
	
			return $respuesta;
		}	
		
		public function crear(){
			
			global $USER, $SESSION, $PAGE, $CFG, $DB;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = 0;
			$respuesta['datos'] = array();
			if(!isloggedin()){
				//if(isset($_POST['nombres']) $filtro->soloLetrasYespacios(($_POST['nombres']) && $filtro->limiteTamano($_POST['nombres'], 4, 64)){
				//	if(isset($_POST['nombres']) $filtro->soloLetrasYespacios(($_POST['nombres']) && $filtro->limiteTamano($_POST['nombres'], 4, 64)){
					
				if(isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['correo'])  && isset($_POST['identificacion']) && isset($_POST['telefono']) && isset($_POST['direccion']) && isset($_POST['ciudad'])){
					$_POST['nombres'] = trim($_POST['nombres']);
					$_POST['apellidos'] = trim($_POST['apellidos']);
					$_POST['correo'] = strtolower($_POST['correo']);
					$_POST['identificacion'] = trim($_POST['identificacion']);
					$_POST['telefono'] = trim($_POST['telefono']);
					$_POST['direccion'] = trim($_POST['direccion']);
					$_POST['ciudad'] = trim($_POST['ciudad']);
					
					$usuario = new Usuario($this->conexion);
					
					$re = $usuario->validaDatosUsuario(1, $_POST['nombres'], $_POST['apellidos'], $_POST['correo'], $_POST['identificacion'], $_POST['telefono'], $_POST['direccion'], $_POST['ciudad']);
					if($re['error']=='no'){
						if($this->sesion->iniciarSesionMoodle('usercreator', 'ovsZQjSwsHif')){
							require_once($CFG->dirroot.'/user/externallib.php');
							require_once($CFG->dirroot.'/enrol/manual/lib.php');
							//require_login();
							$PAGE->set_context(\context_system::instance());				
							$reportecreacion = array();
							
							$user1 = array(
								'username' => $re['username'],
								'password' => $re['identificacionv'],
								'idnumber' => $re['idnumber'],
								'firstname' => $re['nombresv'],
								'lastname' => $re['apellidosv'],
								'institution' => $re['ciudadv'].' '.$re['direccionv'],
								'middlename' => '',
								'lastnamephonetic' => '',
								'firstnamephonetic' => '',
								'alternatename' => '',
								'email' => $re['emailv'],
								'phone1' => $re['telefonov'],
								'description' => '',
								'city' => 'BARRANQUILLA',
								'country' => 'CO',
								'auth'=>'manual',			
								'lang'=>'es',
							); //'url'=>$re['contratadov'],
							//print_r($user1);
							
							//'preferences' => array(
								//array('type'=>'auth_forcepasswordchange', 'value'=>'1')
							//)
							
							$contextid = \context_system::instance();
							//$roleid = create_role('Dummy role', 'dummyrole', 'dummy role description');
							$roleid = 1;  //Gestor, se espera que el gestor tenga la capacidad de moodle/user:update
							$teniapermiso = has_capability('moodle/user:update', $contextid);
							if(!$teniapermiso){
								//assign_capability('moodle/user:update', CAP_ALLOW, $roleid, $contextid);
								role_assign($roleid, $USER->id, $contextid);
							}
							
							$createdusers = \core_user_external::create_users(array($user1));
							
							if(!$teniapermiso){
								role_unassign($roleid, $USER->id, \context_system::instance()->id);
								//unassign_capability('moodle/user:update', $roleid, $contextid);										
							}
							
							//if(isloggedin()){
								require_logout();
							//}
							
							$user1['numerolinea'] = $re['numerolinea'];
							if(count($createdusers)>0){  //se agrego, por que solo estamos agregando uno solo.																																																								
								$respuesta['estado'] = 'ok';
								
								//$this->sesion->iniciarSesionMoodle($re['username'], $re['identificacionv']);
								
								$user1['password'] = $re['identificacionv'];
								$respuesta['id'] = $createdusers[0]['id'];	//no estaba antes.
								$respuesta['codigo'] = 'creado';		//no estaba antes y las dos de abajo tampoco.
								
								//$email = new EMail();														
								//$email->emailRapidoHtml($re['emailv'], 'Cuenta creada en Myedu', '<br>Saludos '.$re['nombresv'].' '.$re['apellidosv'].'.<br><br>Se le informa que le ha sido creada su cuenta en MyEdu. Sus datos para el acceso son:<br><br>Usuario: '.$re['username'].'<br>Contraseña:'.$re['identificacionv'].'<br>Link de acceso: <a href="'.URLBASE.'" target="_blank">'.URLBASE.'</a><br><br><br>Cordialmente,<br>El Equipo de MyEdu.<br><br>');
								
								if(function_exists('email_to_user')){
									foreach($createdusers as $createduser) {
										$dbuser = $DB->get_record('user', array('id' => $createduser['id']));
										$usuario = new Usuario($this->conexion, $createduser['id']);
										
										//enviamos el email correspondiente:
										$user = new stdClass();
										$user->id = $usuario->getDato('id');
										$user->picture = '';
										$user->firstname =  $usuario->getDato('firstname');
										$user->lastname =  $usuario->getDato('lastname');
										$user->firstnamephonetic = '';
										$user->lastnamephonetic = '';
										$user->middlename = '';
										$user->alternatename = '';
										$user->imagealt = '';
										$user->email = $usuario->getDato('email');
										$user->mailformat = 1;
										 
										//el que envia este correo myedu
										$user2 = new stdClass();
										$user2->id = 2;
										$user2->picture = '';
										$user2->firstname = 'LevelUp Americana';
										$user2->lastname = 'Soporte';
										$user2->firstnamephonetic = 'LevelUp Americana';
										$user2->lastnamephonetic = 'Soporte';
										$user2->middlename = 'LevelUp Americana';
										$user2->alternatename = 'Soporte';
										$user2->imagealt = '';
										$user2->email = 'levelup-noreply@coruniamericana.edu.co';
										$user2->mailformat = 1;
										
										$txt = '<table>
													<tbody>														
														<tr>
															<td style="width:200px;text-align:right;vertical-align:top;">
																<img style="max-width:80%" src="'.URLPROYECTO.'vistas/pix/levelup_5.png?v=3">
															</td>
															<td style="text-align:left;vertical-align:top;padding:10px">																
																<font style="font-size:18px;color:#1b232a">
																	Hola, '.$user->firstname.' '.$user->lastname.'
																</font><br>
																<font style="font-size:12px">Te informamos que tu cuenta de LevelUp Americana C.E.C ha sido creada, para acceder usa los siguientes datos:</font><br>
																<font style="font-size:12px">Nombre de usuario: '.$usuario->getDato('username').'</font><br>
																<font style="font-size:12px">Contraseña: '.$re['identificacionv'].'</font><br>
																------------------------<br>																
																<ul>
																	<font style="color:#5f6971"><em>
																		<li>IP: '.$_SERVER['REMOTE_ADDR'].'</li>
																		<li>Datetime: '.date("Y-m-d H:i:s").'</li>																																	
																	</font>
																</ul>
																<p>Atentamente, el equipo de LevelUp Americana C.E.C</p>
															</td>
														</tr>
													</tbody>
												</table>';
										email_to_user($user, $user2, 'Cuenta creada en LevelUp Americana', $txt, $txt, ", ", true);														
									}	
								}else{
									$respuesta['datos2'] = 'no_se_envio_correo';
								}
							}else{
								$respuesta['codigo'] = 'fallocreacion';
								$user1['password'] = '';
							}
							$respuesta['datos'] = $user1;
						}
					}else{
						$respuesta['codigo'] = 'errorestados';
						$respuesta['datos'] = $re;
					}				
				}
			}	
			return $respuesta;
		}
		
		public function editar($id){	//viene PUT y con solo un numero en segunda posicion
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			$json = file_get_contents('php://input');			
			$data = json_decode($json,true);
			

			return $respuesta;
			
		}
		public function borrar($id){	//viene DELETE y con solo un numero en segunda posicion
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			return $respuesta;
			
		}
		public function ver($id){	//viene GET y con solo un numero en segunda posicion, para retornar solo los datos de uno solo, si se requiere de otras listas ya ahi si se necesitan las funciones personalizadas.
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			return $respuesta;
		}
	
		/*
			GET
			Retorna el listado de las compras por página
		*/
		public function misCompras($pagina){
			
			global $USER, $SESSION, $PAGE, $CFG, $DB;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			if(isloggedin()){
				if($this->filtro->soloNumeros($pagina) && $this->filtro->limiteTamano($pagina, 1, 8)){
					$usuario = new Usuario($this->conexion, $USER->id);
					if($usuario->getDato('id')){
						
						$respuesta['datos']['administrador'] = false;
						$usuariobuscar = $USER->id;
						$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);
						if(count($permisoeditarcurso)>0){
							$usuariobuscar = 0;
							$respuesta['datos']['administrador'] = true;
						}
						
						$cantidadtotal = 0;
						
						$factura = new Factura($this->conexion);
						$respuesta['datos']['facturas'] = $factura->getTodos($usuariobuscar, -1, $pagina);
						
						$respuesta['datos']['paginaanterior'] = -1;
						$respuesta['datos']['paginaactual'] = $pagina;
						$respuesta['datos']['paginasiguiente'] = -1;
						
						
						//miramos si ponemos el boton siguiente
						if(isset($respuesta['datos']['facturas'][0]['cantidad'])){
							$cantidadtotal = $respuesta['datos']['facturas'][0]['cantidad'];							
						}
						
						if((($pagina+1)*10)<$cantidadtotal){
							$respuesta['datos']['paginasiguiente'] = $pagina+1;						
						}
						if($pagina>0){
							$respuesta['datos']['paginaanterior'] = $pagina-1;
						}
						
						$sitio = new Sitio($this->conexion);
						$respuesta['datos']['config'] = $sitio->getConfig();
						
						$respuesta['estado'] = 'ok';
					}
				}				
			}else{
				exit;
				//$respuesta['codigo'] = 'not-logged';				
			}	
			return $respuesta;
		}
		
	}
	
?>