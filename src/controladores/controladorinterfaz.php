<?php
	header("Content-Type: text/html;charset=utf-8");
	
	require "../../../../../config.php";
	include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/autoload.php');
	
	//require_login();
	$userid = $USER->id;
	
	$conexion = new Conexion();
	$filtro = new Filtro();
		
	set_time_limit(600);  //10 minutos corriendo.
	date_default_timezone_set('America/Bogota');
			
	$a = '';
	if(isset($_GET['a'])){
		$a = $_GET['a'];		
	}else{
		if(isset($_POST['a'])){
			$a = $_POST['a'];
		}
	}
		
	switch($a){
		case 'prueba':
			include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
			$sitio = new Sitio($conexion);
			print_r($sitio->getCursosPopulares(10, 200));
		break;
		case 'getCarrito':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$usuario = new Usuario($conexion, $userid);
				$facturaabierta = $usuario->getFacturas(array(1, 2));
				$respuesta['estado'] = 'ok';
				if(count($facturaabierta)>0){										
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Factura.php');
					$factura = new Factura($conexion, $facturaabierta[0]['id']);
					if($factura->getDato('id')){
						$factura->actualizarPreciosCursos();
						
						$facturaabierta = $usuario->getFacturas(array(1, 2));
						if(count($facturaabierta)>0){
							$facturaabierta[0]['immpuesto'] = number_format($facturaabierta[0]['impuesto'], 0, ',', '.');
							$facturaabierta[0]['subtotal'] = number_format($facturaabierta[0]['subtotal'], 0, ',', '.');
							$facturaabierta[0]['total'] = number_format($facturaabierta[0]['total'], 0, ',', '.');
							
							$respuesta['datos'] = $facturaabierta[0];
							
							$respuesta['datos']['items'] = $factura->getDato('items');
							$tam = count($respuesta['datos']['items']);
							for($i=0; $i<$tam; $i++){							
								$respuesta['datos']['items'][$i]['valorunidadmomento'] = number_format($respuesta['datos']['items'][$i]['valorunidadmomento'], 0, ',', '.');
								$respuesta['datos']['items'][$i]['totalmomento'] = number_format($respuesta['datos']['items'][$i]['totalmomento'], 0, ',', '.');
							}
						}
					}
				}
				$respuesta['datos']['sinredimir'] = array();
				$sinredimir = $usuario->getCursosSinRedimir();
				$tiposredimidosencontrados = array();
				foreach($sinredimir as $sr){
					if(!in_array($sr['tipocurso'], $tiposredimidosencontrados)){
						array_push($tiposredimidosencontrados, $sr['tipocurso']);
						array_push($respuesta['datos']['sinredimir'], $sr);
					}else{
						$tam = count($respuesta['datos']['sinredimir']);
						for($i=0; $i<$respuesta['datos']['sinredimir']; $i++){
							if($respuesta['datos']['sinredimir'][$i]['tipocurso']==$sr['tipocurso']){
								$respuesta['datos']['sinredimir'][$i]['cantidad'] = $respuesta['datos']['sinredimir'][$i]['cantidad']+$sr['cantidad'];
								$respuesta['datos']['sinredimir'][$i]['cantidadredimidas'] = $respuesta['datos']['sinredimir'][$i]['cantidadredimidas']+$sr['cantidadredimidas'];
								break;
							}
						}
					}
				}
				
				
			}
			echo json_encode($respuesta);
		break;
		case 'getFacturasFinalizadas':  //ojo, pendiente por terminar.
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$usuario = new Usuario($conexion, $userid);
				$facturas = $usuario->getFacturas(array(3, 4));
				$respuesta['estado'] = 'ok';
				$tam = count($facturas);
				if($tam>0){
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Factura.php');
					for($i=0; $i<$tam; $i++){
						$factura = new Factura($conexion, $facturas[$i]['id']);
						$facturas[$i]['items'] = $factura->getDato('items');
						array_push($respuesta['datos'], $facturas[$i]);
					}
				}
			}
			echo json_encode($respuesta);	
		break;
		case 'setQuitarItemCarrito':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
			if(isloggedin()){
				if(isset($_POST['tipo']) && $filtro->soloNumeros($_POST['tipo']) && $filtro->limiteTamano($_POST['tipo'], 1, 1) && ($_POST['tipo']=='1' || $_POST['tipo']=='0')){
					if(isset($_POST['tipocurso']) && $filtro->soloNumerosyLetrasMayusculas($_POST['tipocurso']) && $filtro->limiteTamano($_POST['tipocurso'], 1, 32)){
						if(isset($_POST['cantidad']) && $filtro->soloNumeros($_POST['cantidad']) && $filtro->limiteTamano($_POST['cantidad'], 1, 4) && $_POST['cantidad']>0){
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
							$sitio = new Sitio($conexion);
							$tipocurso = $sitio->getTiposCurso($_POST['tipocurso'], true, false, true);
							if(count($tipocurso)>0){
								$tipocurso = $tipocurso[0];
								if($tipocurso['precio']>0){
									include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
									$usuario = new Usuario($conexion, $userid);
									
									include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Comun.php');
									$comun = new Comun();
																		
									$facturaabierta = $usuario->getFacturas(array(1));
									$facturaenproceso = $usuario->getFacturas(array(2));
									//$respuesta['codigo'] = $facturaabierta;
									if((count($facturaabierta)>0 && $_POST['tipo']=='0') || ($_POST['tipo']=='1')){
										include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Factura.php');
										switch($_POST['tipo']){
											case '0':
												$factura = new Factura($conexion, $facturaabierta[0]['id']);
												if($factura->getDato('id')){
													if($factura->quitarItem($_POST['tipocurso'], $_POST['cantidad'], $tipocurso['precio'])){
														$respuesta['estado'] = 'ok';
													}
												}
											break;
											case '1':
												$pasa = false;
												$tipomatriculados = array();
												
												//obtenemos los cursos en los que esta 
												$matriculados = $usuario->getCursos(true);
												foreach($matriculados as $ma){
													$partes = explode('-', $ma['shortname']);
													if(count($partes)==2){
														if($partes[0]==$_POST['tipocurso']){
															array_push($tipomatriculados, $ma);
														}
													}
												}
																								
												if(count($tipomatriculados)==0){
													$pasa = true;
												}else{
													//tiene cursos de este tipo matriculados, para poder matricular miramos que todos los que tenga matriculado hayan superado el tiempo para ser realizados ya sea que los hayan finalizado o no.
													
													
													$todosvencidos = true;
													foreach($tipomatriculados as $tm){
														if(!$usuario->cursoVencido($tm['course_id'])[0]['vencido']){
															$todosvencidos = false;
														}
													}
													
													/*$mysql_datetime = date("Y-m-d");
													$todosvencidos = true;
													foreach($tipomatriculados as $tm){														
														$datosmatriculacion = $usuario->getFechaMatriculacionCurso($tm['course_id'], true);
														if(isset($datosmatriculacion['timestart']) && $datosmatriculacion['timestart']!='0'){
															if($comun->diasEntreDosFechas(explode(' ', $datosmatriculacion['timestart'])[0], $mysql_datetime)>60){
															}else{
																$todosvencidos = false;  //no ha superado los x dias para estar vencido (finalizado satisfactoriamente o no)
															}
														}else{	//nunca deberia entrar aqui, por que todos los usuario-curso deberian tener una fecha de matriculacion
															$todosvencidos = false;
														}
													}*/

													
													if(!$todosvencidos){
														$respuesta['codigo'] = 'cursopendienteporvencimiento';
													}else{
														//Miramos si el curso mas reciente de este tipo no es ninguno de los que tiene matriculados													
														$ultimaversion = $sitio->getIdsUltimasVersionesCursos($_POST['tipocurso']);
														if(count($ultimaversion)>0){
															foreach($tipomatriculados as $tm){
																if($tm['course_id']==$ultimaversion[0]['id']){
																	$respuesta['codigo'] = 'tieneelultimo';
																	break;
																}
															}														
														}
													}
													if($respuesta['codigo']==''){
														$pasa = true;
													}
												}
												
												
												if($pasa){
													$factura = false;
													if(count($facturaabierta)==0){	//si no existe una abierta
														$pasa = false;
														if(count($facturaenproceso)==0){
															$factura = new Factura($conexion);
															if($factura->setFactura($userid)){
																$pasa = true;
															}
														}else{
															$respuesta['codigo'] = 'tieneenproceso';
														}
													}else{
														$pasa = false;
														$factura = new Factura($conexion, $facturaabierta[0]['id']);
														if($factura->getDato('id')){
															$items = $factura->getDato('items');
															foreach($items as $it){
																if($it['tipocurso']==$_POST['tipocurso']){
																	$respuesta['codigo'] = 'yaestaencarrito';
																	break;
																}
															}
															if($respuesta['codigo']==''){
																$pasa = true;
															}
														}
													}
													if($pasa){
														if($factura->setItem($_POST['tipocurso'], $tipocurso['nombre'], $_POST['cantidad'], $tipocurso['precio'])){
															$respuesta['estado'] = 'ok';
															$items = $factura->getDato('items');
															foreach($items as $it){
																if($it['tipocurso']==$_POST['tipocurso']){
																	$respuesta['datos'] = $it['cantidad'];
																}
															}
														}
													}
												}
											break;
										}
									}
								}else{
									$respuesta['codigo'] = 'nodisponible';
								}
							}
						}
					}
				}
			}
			echo json_encode($respuesta);
		break;		
		case 'getDetallesCurso':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){
				if(isset($_POST['tipocurso']) && $filtro->soloNumerosyLetrasMayusculas($_POST['tipocurso']) && $filtro->limiteTamano($_POST['tipocurso'], 1, 32)){
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
					$sitio = new Sitio($conexion);
					$respuesta['datos'] = $sitio->getTiposCurso($_POST['tipocurso'], true, false, true);
					if(count($respuesta['datos'])>0){	//si tiene registros
						$respuesta['datos'] = $respuesta['datos'][0];	//tomamos el primer registro.
						$ultimasversiones = $sitio->getIdsUltimasVersionesCursos();
						
						$respuesta['datos']['course_id'] = 0;
						foreach($ultimasversiones as $uv){
							if($uv['codigocurso']==$_POST['tipocurso']){
								$respuesta['datos']['course_id'] = $uv['id'];
							}
						}
						
						if($respuesta['datos']['idinstructor']!=0 && $respuesta['datos']['idinstructor']!=''){
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
							$usuarioinstructor = new Usuario($conexion, $respuesta['datos']['idinstructor']);	
							$user = new stdClass();
							$user->id = $respuesta['datos']['idinstructor'];
							$user->picture = $usuarioinstructor->getDato('picture');
							$user->firstname = $usuarioinstructor->getDato('firstname');
							$user->lastname = $usuarioinstructor->getDato('lastname');
							$user->firstnamephonetic = '';
							$user->lastnamephonetic = '';
							$user->middlename = '';
							$user->alternatename = '';
							$user->imagealt = $usuarioinstructor->getDato('firstname').' '.$usuarioinstructor->getDato('lastname');
							$user->email = $usuarioinstructor->getDato('email');
							$respuesta['datos']['instructorimagen'] = $OUTPUT->user_picture($user, array('size'=>70, 'alttext'=>false, 'link'=>false, 'class'=>'avatar user-thumb image image--avatar2'));
						}												
						$respuesta['estado'] = 'ok';
					}				
				}
			}			
			echo json_encode($respuesta);
		break;
		case 'buscarCursosDashboard':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = 0;
			$respuesta['datos'] = array();
			if(isloggedin()){
				if (isset($_POST['b'])){
					if($filtro->soloLetrasYespacios($_POST['b']) && $filtro->limiteTamano($_POST['b'], 1, 30)){
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
						$sitio = new Sitio($conexion);
						$respuesta['datos'] = $sitio->getCursosSugerencias($_POST['b'], $userid);
						$respuesta['estado'] = 'ok';
					}
				}			
			}
			echo json_encode($respuesta);
		break;
		case 'getAreas':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = 0;
			$respuesta['datos'] = array();
			$respuesta['seleccionadas'] = array();
					
			include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
			$sitio = new Sitio($conexion);
			$respuesta['datos'] = $sitio->getAreas();
			
			if(isloggedin()){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$usuario = new Usuario($conexion, $userid);
				if($usuario->getDato('id')){
					$respuesta['seleccionadas'] = $usuario->getDato('areaspreferidas');
				}	
			}				
			$respuesta['estado'] = 'ok';
							
			echo json_encode($respuesta);
		break;
		case 'setAreasFavoritas':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$usuario = new Usuario($conexion, $userid);
				if($usuario->getDato('id')){
					//$seleccionadas = $usuario->getDato('areaspreferidas');
					$seleccionadas = array();
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
					$sitio = new Sitio($conexion);
					$areas = $sitio->getAreas();
					foreach($areas as $ar){
						if(isset($_POST['area'.$ar['id']])){
							array_push($seleccionadas, $ar['id']);
						}
					}
					$codificado = json_encode($seleccionadas);
					if(json_last_error()===JSON_ERROR_NONE){
						if($usuario->setDato('description', $codificado)){
							$respuesta['estado'] = 'ok';
						}
					}
				}
			}
			echo json_encode($respuesta);
		break;
		case 'registrarme':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = 0;
			$respuesta['datos'] = array();
			if(!isloggedin()){
				//if(isset($_POST['nombres']) $filtro->soloLetrasYespacios(($_POST['nombres']) && $filtro->limiteTamano($_POST['nombres'], 4, 64)){
				//	if(isset($_POST['nombres']) $filtro->soloLetrasYespacios(($_POST['nombres']) && $filtro->limiteTamano($_POST['nombres'], 4, 64)){
					
				if(isset($_POST['nombres']) && $_POST['apellidos'] && $_POST['correo']  && $_POST['identificacion']){
					$_POST['nombres'] = trim($_POST['nombres']);
					$_POST['apellidos'] = trim($_POST['apellidos']);
					$_POST['correo'] = strtolower($_POST['correo']);
					$_POST['identificacion'] = trim($_POST['identificacion']);
					
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
					$usuario = new Usuario($conexion);
					
					$re = $usuario->validaDatosUsuario(1, $_POST['nombres'], $_POST['apellidos'], $_POST['correo'], $_POST['identificacion']);
					if($re['error']=='no'){
						
						if($sesion->iniciarSesionMoodle('usercreator', 'Ijg1!lUy61F6')){
							
							require_once('../../../../user/externallib.php');
							//require_login();
							$PAGE->set_context(context_system::instance());				
											
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Email.php');
							
							$reportecreacion = array();
							
							$user1 = array(
								'username' => $re['username'],
								'password' => $re['identificacionv'],
								'idnumber' => $re['idnumber'],
								'firstname' => $re['nombresv'],
								'lastname' => $re['apellidosv'],
								'middlename' => '',
								'lastnamephonetic' => '',
								'firstnamephonetic' => '',
								'alternatename' => '',
								'email' => $re['emailv'],
								'description' => '',
								'city' => 'BARRANQUILLA',
								'country' => 'CO',
								'auth'=>'manual',			
								'lang'=>'es',
								/*'preferences' => array(
									array('type'=>'auth_forcepasswordchange', 'value'=>'1')
								)*/
							); //'url'=>$re['contratadov'],
							//print_r($user1);
							
							$contextid = context_system::instance();
							//$roleid = create_role('Dummy role', 'dummyrole', 'dummy role description');
							$roleid = 1;  //Gestor, se espera que el gestor tenga la capacidad de moodle/user:update
							$teniapermiso = has_capability('moodle/user:update', $contextid);
							if(!$teniapermiso){
								//assign_capability('moodle/user:update', CAP_ALLOW, $roleid, $contextid);
								role_assign($roleid, $USER->id, $contextid);
							}
							
							$respuesta['datosx'] = $user1;
							$createdusers = core_user_external::create_users(array($user1));
							
							if(!$teniapermiso){
								role_unassign($roleid, $USER->id, context_system::instance()->id);
								//unassign_capability('moodle/user:update', $roleid, $contextid);										
							}
							
							//if(isloggedin()){
								require_logout();
							//}
							
							$user1['numerolinea'] = $re['numerolinea'];
							if(count($createdusers)>0){  //se agrego, por que solo estamos agregando uno solo.																																																								
								$respuesta['estado'] = 'ok';
								
								//$sesion->iniciarSesionMoodle($re['username'], $re['identificacionv']);
								
								$user1['password'] = $re['identificacionv'];
								$respuesta['id'] = $createdusers[0]['id'];	//no estaba antes.
								$respuesta['codigo'] = 'creado';		//no estaba antes y las dos de abajo tampoco.
								
								/*$email = new EMail();														
								$email->emailRapidoHtml($re['emailv'], 'Cuenta creada en Myedu', '<br>Saludos '.$re['nombresv'].' '.$re['apellidosv'].'.<br><br>Se le informa que le ha sido creada su cuenta en MyEdu. Sus datos para el acceso son:<br><br>Usuario: '.$re['username'].'<br>Contraseña:'.$re['identificacionv'].'<br>Link de acceso: <a href="'.URLBASE.'" target="_blank">'.URLBASE.'</a><br><br><br>Cordialmente,<br>El Equipo de MyEdu.<br><br>');	*/
								
								
								if(function_exists('email_to_user')){
									foreach($createdusers as $createduser) {
										$dbuser = $DB->get_record('user', array('id' => $createduser['id']));
										$usuario = new Usuario($conexion, $createduser['id']);
										
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
										$user2->firstname = 'MyEdu';
										$user2->lastname = 'Soporte';
										$user2->firstnamephonetic = 'MyEdu';
										$user2->lastnamephonetic = 'Soporte';
										$user2->middlename = 'MyEdu';
										$user2->alternatename = 'Soporte';
										$user2->imagealt = '';
										$user2->email = 'soportemyedu@coruniamericana.edu.co';
										$user2->mailformat = 1;
										
										$txt = '<table>
													<tbody>														
														<tr>
															<td style="width:200px;text-align:right;vertical-align:top;">
																<img style="max-width:80%" src="'.URLBASE.'theme/boost/americana/pix/myedulogo.png?v=3">
															</td>
															<td style="text-align:left;vertical-align:top;padding:10px">																
																<font style="font-size:18px;color:#1b232a">
																	Hola, '.$user->firstname.' '.$user->lastname.'
																</font><br>
																<font style="font-size:12px">Te informamos que tu cuenta de MyEdu ha sido creada, para acceder usa los siguientes datos:</font><br>
																<font style="font-size:12px">Nombre de usuario: '.$usuario->getDato('username').'</font><br>
																<font style="font-size:12px">Contraseña: '.$re['identificacionv'].'</font><br>
																------------------------<br>																
																<ul>
																	<font style="color:#5f6971"><em>
																		<li>IP: '.$_SERVER['REMOTE_ADDR'].'</li>
																		<li>Datetime: '.date("Y-m-d H:i:s").'</li>																																	
																	</font>
																</ul>
																<p>Atentamente, el equipo de MyEdu</p>
															</td>
														</tr>
													</tbody>
												</table>';
										email_to_user($user, $user2, 'Cuenta creada en Myedu', $txt, $txt, ", ", true);														
									}	
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
			echo json_encode($respuesta);
		break;		
		case 'checkUrlErrorIframe':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = 0;
			$respuesta['datos'] = array();
			if(isloggedin()){
				
				//ojo aqui comprobar que el usuario este matriculado en el curso
				
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
					if(isset($_POST['idsencuence']) && $filtro->soloNumeros($_POST['idsencuence']) && $filtro->limiteTamano($_POST['idsencuence'], 1, 8)) {
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
						$curso = new Curso($conexion, $_POST['idcurso']);
						if($curso->getDato('id')){
							$resp = $curso->getActividadesCalificablesUsuario(0, $_POST['idsencuence'], false, false);
							foreach($resp as $re){
								foreach($re['actividades'] as $actividad){
									if($actividad['secuence']==$_POST['idsencuence'] && $actividad['tipo']=='url' && $actividad['display']==1){  // && $actividad['urlmodo']==0
										if($actividad['externalurl']!=''){											
											$ch = curl_init();
											$options = array(
												CURLOPT_URL            => $actividad['externalurl'],
												CURLOPT_RETURNTRANSFER => true,
												CURLOPT_HEADER         => true,
												CURLOPT_FOLLOWLOCATION => true,
												CURLOPT_ENCODING       => "",
												CURLOPT_AUTOREFERER    => true,
												CURLOPT_CONNECTTIMEOUT => 120,
												CURLOPT_TIMEOUT        => 120,
												CURLOPT_MAXREDIRS      => 10,
											);
											curl_setopt_array($ch, $options);
											$response = curl_exec($ch);
											$httpCode = curl_getinfo($ch);
											$headers= substr($response, 0, $httpCode['header_size']);
											//echo $headers;
											
											$buscarerrores = array('SAMEORIGIN', 'X-Frame-Options: deny', 'x-frame-options: deny', 'frame-options: sameorigin', 'Connection: close', '302');
											foreach($buscarerrores as $be){
												if(strpos($headers, $be)!==false){
													$respuesta['codigo'] = 2;													
													$curso->setUrlNuevaVentana($actividad['instance']);
													break;
												}
											}
											
											if(strlen($headers)==0){  //hubo una url que no me retornaba headers y er auna reedirecion es esta: http://www.comunidadcontable.com/BancoConocimiento/N/noti-1804201302_%28niif_para_microempresas_en_colombia_patrimonio%29/noti-1804201302_%28niif_para_microempresas_en_colombia_patrimonio%29.asp
												$respuesta['codigo'] = 2;
												$curso->setUrlNuevaVentana($actividad['instance']);
											}
											
											if($respuesta['codigo']!=2){
												$respuesta['codigo'] = 1;
											}	
											
											$md5url = substr(md5($actividad['externalurl']), 0, 3);
											if($md5url!=''){
												if($curso->setUrlModoNotificaciones($userid, $_POST['idsencuence'], $respuesta['codigo'], $md5url)){
													$respuesta['estado'] = 'ok';
												}
											}											
										}
									}
								}
							}
						}
					}
				}
			}
			echo json_encode($respuesta);
		break;
		case 'setFechaRevision':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = array();
			if(isloggedin()){
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
					if(isset($_POST['idsencuence']) && $filtro->soloNumeros($_POST['idsencuence']) && $filtro->limiteTamano($_POST['idsencuence'], 1, 8)) {
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
						$curso = new Curso($conexion, $_POST['idcurso']);
						if($curso->getDato('id')){
							$notificacion = $curso->getNotificacionEspecifica($userid, $_POST['idsencuence'], 1, array());
							if($notificacion){
								
								include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Comun.php');
								$comun = new Comun();
								$fechahora = date("Y-m-d H:i:s");
								$fechaactualunix = $comun->convierteTimestampAUnix($fechahora);
								
								if($curso->updateNotificacion($userid, $_POST['idsencuence'], 1, 'fecharevision', $fechaactualunix)){
									$respuesta['estado'] = 'ok';
								}
								if($respuesta['estado']=='ok'){
									if(isset($_POST['notificacionvista']) && $_POST['notificacionvista']=='1'){
										$respuesta['estado'] = 'error';
										if($curso->updateNotificacion($userid, $_POST['idsencuence'], 1, 'estado', '1')){
											if($curso->updateNotificacion($userid, $_POST['idsencuence'], 1, 'correo', 0)){
												$respuesta['estado'] = 'ok';
											}
										}
									}
								}
							}
						}
					}
				}
			}
			echo json_encode($respuesta);
		break;
		case 'getForoAdjunto':   //obtiene el foro de chats para esta actividad o lo crea si no lo tiene y esta marcada para tener foro segun el tipo y las condiciones de la actividad.
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = array();
			$respuesta['datos']['iddiscusion'] = -1;
			$respuesta['datos']['idforo'] = -1;
			$respuesta['datos']['tipoforo'] = '';
			if(isloggedin()){
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
					if(isset($_POST['idsencuence']) && $filtro->soloNumeros($_POST['idsencuence']) && $filtro->limiteTamano($_POST['idsencuence'], 1, 8)) {
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
						$curso = new Curso($conexion, $_POST['idcurso']);
						if($curso->getDato('id')){
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
							$usuario = new Usuario($conexion, $userid);
							if($usuario->getDato('id')){
								$tipospermitidosforos = array('glosario', 'etiqueta', 'leccion', 'tarea', 'resource', 'scorm', 'url', 'feedback', 'hvp', 'folder');
								$resp = $curso->getActividadesCalificablesUsuario($userid, $_POST['idsencuence']);
								$nombreactividad = '';
								$debetenerforo = true;
								foreach($resp as $re){
									foreach($re['actividades'] as $actividad){
										if(in_array($actividad['tipo'], $tipospermitidosforos)){
											$nombreactividad = $actividad['nombre'];
											include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/simplehtmldom_1_9_1/simple_html_dom.php');
											if($actividad['descripcion']!=''){
												$html = str_get_html($actividad['descripcion']);
												$evitarforo = $html->find('p[class=evitarforo]')[0]->plaintext;
												if($evitarforo!=null){
													$debetenerforo = false;
												}
											}
										}else{
											$respuesta['codigo'] = 'no-se-permiten-foros';
										}
									}
								}
								if($debetenerforo){	//miramos si hay algun foro en todo el curso con el mismo nombre, el foro debe ser simple, de lo contrario miramos si existe un foro con el nombre como este idsecuente en el foro principal del curso.
									$resp = $curso->getActividadesCalificablesUsuario($userid, 0, false, true);
									foreach($resp as $re){
										if($respuesta['datos']['iddiscusion']!=-1){
											break;
										}
										foreach($re['actividades'] as $actividad){
											if($actividad['nombre']==$nombreactividad && $actividad['tipo']=='foro'){
												if($actividad['type']=='single'){
													$respuesta['datos']['tipoforo'] = 'single';
													$respuesta['datos']['idforo'] = $actividad['instance'];
													//buscamos el iddiscusion
													$iddiscusion = $curso->getIdForumDiscussionByIdSecuence($respuesta['datos']['idforo']);  //al ser simple siempre va a retornar algo aquí
													if($iddiscusion!=-1){
														$respuesta['datos']['iddiscusion'] = $iddiscusion;  
														$respuesta['estado'] = 'ok';
													}else{
														$respuesta['codigo'] = 'no-encontrada-discusion-en-foro-de-mismo-nombre';
													}
													break;
												}else{
													$respuesta['codigo'] = 'foro-adjunto-no-es-foro-sencillo';
												}
											}
										}
									}									
									if($respuesta['datos']['idforo']==-1 && $respuesta['codigo']==''){  //si no se encontro una instancia de un foro adjunto, se busca uno de tipo eachuser con nombre chats en todo el curso
										reset($resp);
										foreach($resp as $re){
											if($respuesta['datos']['iddiscusion']!=-1){
												break;
											}
											foreach($re['actividades'] as $actividad){												
												if(strtolower ($actividad['nombre'])=='chats' && $actividad['tipo']=='foro'){
													if($actividad['type']=='general'){
														$respuesta['datos']['idforo'] = $actividad['instance'];
														$respuesta['datos']['tipoforo'] = 'general';
														
														$iddiscusion = $curso->getIdForumDiscussionByIdSecuence($respuesta['datos']['idforo'], 'secuence'.$_POST['idsencuence'].'-');
														if($iddiscusion==-1){	//si no se ha creado, se crea la discusion.
															include_once('../../../../mod/forum/externallib.php');
															$createddiscussion = mod_forum_external::add_discussion($respuesta['datos']['idforo'], 'secuence'.$_POST['idsencuence'].'-', 'Pregunta, responde, y comparte archivos con tus compañeros y docente con respecto a esta actividad');
															$respuesta['datos']['iddiscusion'] = $createddiscussion['discussionid'];
															$respuesta['estado'] = 'ok';
														}else{
															$respuesta['datos']['iddiscusion'] = $iddiscusion;
															$respuesta['estado'] = 'ok';
														}
														break;
													}else{
														$respuesta['codigo'] = 'foro-chats-no-es-general';
													}
												}	
											}
										}
									}
									if($respuesta['datos']['idforo']==-1 && $respuesta['codigo']==''){				
										$respuesta['codigo'] = 'no-fue-encontrado-ningun-foro';
									}
								}else{
									$respuesta['codigo'] = 'foro-adjunto-deshabilitado';
								}
							}
						}	
					}
				}
			}
			echo json_encode($respuesta);
		break;
		case 'getCursosArea':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = array();
			if(isset($_POST['idarea']) && $filtro->soloNumeros($_POST['idarea']) && ($filtro->limiteTamano($_POST['idarea'], 1, 4) || $_POST['idarea']='0')){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
				$sitio = new Sitio($conexion);
				$areas = $sitio->getAreas();
				$respuesta['codigo'] = '1';	
				$encontrado = '';
				if($_POST['idarea']=='0'){
					$encontrado = '0';
				}else{	
					foreach($areas as $ar){
						if($ar['id']==$_POST['idarea']){
							$encontrado = $ar['id'];
							break;
						}					
					}
				}
				if($encontrado!=''){
					$ultimasversionescursos = array();
					//--------
					$datosarea = $sitio->getCursosArea($encontrado, $ultimasversionescursos, true);					
					if($datosarea['estado']=='ok'){
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
						$respuesta['estado'] = 'ok';
						$datosarea = $datosarea['datos'];
						$tamdatosarea = count($datosarea);
						$imagenesinstructores = array();
						$idsinstructoresprocesados = array();
						for($j=0; $j<$tamdatosarea; $j++){
							$datosarea[$j]['fotoinstructor'] = '';
																															
							//imagen y nombre del instructor
							if($datosarea[$j]['idinstructor']!='' && $datosarea[$j]['idinstructor']!='0'){
								$datosarea[$j]['instructornombre'] = ucwords(mb_strtolower($datosarea[$j]['instructornombre'], 'UTF-8'));
								if(!in_array($datosarea[$j]['idinstructor'], $idsinstructoresprocesados)){
									array_push($idsinstructoresprocesados, $datosarea[$j]['idinstructor']);
									$usuarioinstructor = new Usuario($conexion, $datosarea[$j]['idinstructor']);
									
									$user = new stdClass();
									$user->id = $datosarea[$j]['idinstructor'];
									$user->picture = $usuarioinstructor->getDato('picture');
									$user->firstname = $usuarioinstructor->getDato('firstname');
									$user->lastname = $usuarioinstructor->getDato('lastname');
									$user->firstnamephonetic = '';
									$user->lastnamephonetic = '';
									$user->middlename = '';
									$user->alternatename = '';
									$user->imagealt = $usuarioinstructor->getDato('firstname').' '.$usuarioinstructor->getDato('lastname');
									$user->email = $usuarioinstructor->getDato('email');
									$datosarea[$j]['fotoinstructor'] = $OUTPUT->user_picture($user, array('size'=>35, 'alttext'=>false, 'link'=>false, 'class'=>'avatar user-thumb image image--avatar fotoautor'));
									array_push($imagenesinstructores, array('id'=>$datosarea[$j]['idinstructor'], 'imagen'=>$datosarea[$j]['fotoinstructor']));							
								}else{
									foreach($imagenesinstructores as $ii){
										if($ii['id']==$datosarea[$j]['idinstructor']){
											$datosarea[$j]['fotoinstructor'] = $ii['imagen'];
										}
									}
								}
							}else{
								$datosarea[$j]['instructornombre'] = '';
							}
							//fin de imagen y nombre del instructor							
							$respuesta['datos'] = $datosarea;
						}
					}
				}
			}	
			echo json_encode($respuesta);
		break;
		case 'getMateriasPrograma':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = array();			
			if(isset($_POST['codigoprograma']) && $_POST['codigoprograma']!='' && $filtro->limiteTamano($_POST['codigoprograma'], 2, 8)){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$sitio = new Sitio($conexion);
				$programas = $sitio->getProgramas();
				$encontrado = '';
				foreach($programas as $pro){
					if($pro['codigoprograma']==$_POST['codigoprograma']){
						$encontrado = $pro['codigoprograma'];
						break;
					}					
				}
				if($encontrado!=''){
					$respuesta['estado'] = 'ok';
					$respuesta['datos'] = $sitio->getCursosPorPrograma($encontrado);
					$respuesta['datos']	= $respuesta['datos']['datos'];
					
					$tam = count($respuesta['datos']);
					for($i=0; $i<$tam; $i++){						
						$respuesta['datos'][$i]['nombre'] = ucwords(mb_strtolower(format_string($respuesta['datos'][$i]['nombre'], 'UTF-8')));
						$respuesta['datos'][$i]['instructornombre'] = ucwords(mb_strtolower(format_string($respuesta['datos'][$i]['instructornombre'], 'UTF-8')));
						$respuesta['datos'][$i]['descripciongeneral'] = explode('.', $respuesta['datos'][$i]['descripciongeneral'])[0];
						$respuesta['datos'][$i]['precioanterior'] = number_format($respuesta['datos'][$i]['precioanterior'], 0, ',', '.');
						$respuesta['datos'][$i]['precio'] = number_format($respuesta['datos'][$i]['precio'], 0, ',', '.');
						
						$respuesta['datos'][$i]['fotoinstructor'] = '';
						if($respuesta['datos'][$i]['idinstructor']!='' && $respuesta['datos'][$i]['idinstructor']!=0){
							$usuarioinstructor = new Usuario($conexion, $respuesta['datos'][$i]['idinstructor']);
							$user = new stdClass();
							$user->id = $respuesta['datos'][$i]['idinstructor'];
							$user->picture = $usuarioinstructor->getDato('picture');
							$user->firstname = $usuarioinstructor->getDato('firstname');
							$user->lastname = $usuarioinstructor->getDato('lastname');
							$user->firstnamephonetic = '';
							$user->lastnamephonetic = '';
							$user->middlename = '';
							$user->alternatename = '';
							$user->imagealt = $usuarioinstructor->getDato('firstname').' '.$usuarioinstructor->getDato('lastname');
							$user->email = $usuarioinstructor->getDato('email');
							$respuesta['datos'][$i]['fotoinstructor'] = $OUTPUT->user_picture($user, array('size'=>50, 'alttext'=>false, 'link'=>false, 'class'=>'fotoautor'));
						}
					}					
				}
			}
			echo json_encode($respuesta);
		break;
		case 'getCursosDestacados':	
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = '';
			$langpermitidos = array('es', 'en', 'fr');		
			$PAGE->set_context(context_system::instance());	
			if(isset($_POST['lang']) && in_array($_POST['lang'], $langpermitidos)){
				$urltcs = URLBASETCS.'controladores/controladorinterfaz.php';
				$stringautenticacion = 'a=getCursosLanding&categoria=cursosdestacados&idioma='.$_POST['lang'];
				$respuestaremota = $conexion->conexionRemota($urltcs, $stringautenticacion);											
				if($respuestaremota['estado']=='ok'){  //se comunicó bien y obtuvo respuesta (no se sabe si fue una respuesta valida o con errores).			
					$respuestaremotax = (array)json_decode($respuestaremota['datos'], true);						
					if(isset($respuestaremotax['estado'])){
						switch($respuestaremotax['estado']){
							case 'ok':
								$respuesta['estado'] = 'ok';
								$cursos = $respuestaremotax['datos'];
								$tam = count($cursos);
								for($i=0; $i<$tam; $i++){
									if($cursos[$i]['descripcion']!=''){
										$cursos[$i]['descripcion'] = ucwords(strtolower(format_string($cursos[$i]['descripcion'])));
									}
									if($cursos[$i]['detalles']['instructornombre']!='' && $cursos[$i]['detalles']['instructornombre']!='null'){
										$cursos[$i]['detalles']['instructornombre'] = ucwords(strtolower(format_string($cursos[$i]['detalles']['instructornombre'])));
									}
									if($cursos[$i]['detalles']['descripciongeneral']!='' && $cursos[$i]['detalles']['descripciongeneral']!='null'){
										$cursos[$i]['detalles']['descripciongeneral'] = explode('.', $cursos[$i]['detalles']['descripciongeneral'])[0];
									}
								}
								$respuesta['datos'] = $cursos;
							break;
						}
					}
				}	
			}
			echo json_encode($respuesta);
		break;
		case 'enviarMensjaje':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			if(isset($_SESSION['emailenviados'])){
				$_SESSION['emailenviados']++;
			}else{
				$_SESSION['emailenviados'] = 0;
			}
			if($_SESSION['emailenviados']<1){
				if(isset($_POST['name']) && $filtro->soloLetrasYespacios($_POST['name']) && $filtro->limiteTamano($_POST['name'], 1, 16)){
					if(isset($_POST['email']) && $filtro->validaEmail($_POST['email']) && $filtro->limiteTamano($_POST['email'], 8, 32)){
						if(isset($_POST['message']) && $filtro->parrafo($_POST['message']) && $filtro->limiteTamano($_POST['message'], 10, 512)){
							$_SESSION['emailenviados']++;
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Email.php');
							$email = new Email($conexion);											
							$email->emailRapidoHtml('soporte.myedu@coruniamericana.edu.co', 'New Training Contact Page Message', 'Name:'.$_POST['name'].'<br>Email:'.$_POST['email'].'<br><br>Message:<br>'.$_POST['message']);
							$respuesta['estado'] = 'ok';
							$_SESSION['paraempresas'] = 1;  //Para que en el landing pueda negar por los cursos.
						}else{
							$respuesta['codigo'] = 'mensajecorto';
						}
					}else{
						$respuesta['codigo'] = 'noesunemail';
					}
				}else{
					$respuesta['codigo'] = 'nombreinvalido';
				}				
			}else{
				$respuesta['codigo'] = 'superolimite';
			}
			echo json_encode($respuesta);
		break;
		case 'aceptarterminos':
			if (!isloggedin()) {
				require_login();
			}
			$PAGE->set_context(context_system::instance());
			include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
			$usuario = new Usuario($conexion, $userid);
			if($usuario->getDato('id')){
				if($usuario->getDato('policyagreed')==0){
					if($usuario->setDato('policyagreed', 1)){
						$USER->policyagreed = 1;
						unset($SESSION->wantsurl);
						
						if(function_exists('email_to_user')){
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
							 
							//el que envia este correo coruniamericana
							$user2 = new stdClass();
							$user2->id = 2;
							$user2->picture = '';
							$user2->firstname = 'MyEdu';
							$user2->lastname = 'Soporte';
							$user2->firstnamephonetic = 'MyEdu';
							$user2->lastnamephonetic = 'Soporte';
							$user2->middlename = 'MyEdu';
							$user2->alternatename = 'Soporte';
							$user2->imagealt = '';
							$user2->email = 'no-reply-myedu@coruniamericana.edu.co';
							$user2->mailformat = 1;
							
							$txt = '<table>
										<tbody>
											<tr>
												<td>&nbsp;</td>
												<td></td>
											</tr>
											<tr>
												<td style="width:200px;text-align:right;vertical-align:top">
													<img style="max-width:80%" src="'.URLBASE.'theme/boost/americana/pix/myedulogo.png?v=3">
												</td>
												<td style="text-align:left;vertical-align:top;padding:10px">
													<p></p><h1>User:</h1>
													<font style="font-size:18px;color:#1b232a">
														'.$user->firstname.' '.$user->lastname.'
													</font><br>
													<font style="font-size:12px">Username: '.$usuario->getDato('username').'</font><br>
													<font style="font-size:12px">Identification: '.$usuario->getDato('idnumber').' (Moodle id: '.$usuario->getDato('id').' )</font>
													<br>            
													<font style="font-size:12px">
														'.$user->email.'
													</font>
													<br>
													<p></p>                    
													<p>
														<br>
														<font style="color:#5f6971">
															<em>
															Yours faithfully,
															<br>
															<br>
															You have accepted the new terms and conditions of MyEdu.edu.co
															</em>
														</font>
													</p>
													<ul>
														<font style="color:#5f6971"><em>
															<li>IP: '.$_SERVER['REMOTE_ADDR'].'</li>
															<li>Datetime: '.date("Y-m-d H:i:s").'</li>															
															<li>Terms and conditions in this link: <a href="'.URLBASE.'local/staticpage/view.php?page=politicas">'.URLBASE.'local/staticpage/view.php?page=politicas</a></li>
														</font>
													</ul>	
													<p></p>
												</td>
											</tr>
										</tbody>
									</table>';
									
							email_to_user($user, $user2, 'You have accepted the terms and conditions', $txt, $txt, ", ", true);														
						}	
					}
				}
			}	
			header('Location: '.URLBASE);
		break;
		case 'setActivarDesactivarLanding':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			//if(isloggedin()){				
				$actual = $sesion->getDatoSesion('landingactivado');
				if($actual==1){
					$actual = 0;
				}else{
					$actual = 1;
				}
				if($sesion->setDatoSesion('landingactivado', $actual)){
					$respuesta['estado'] = 'ok';
				}
			//}
			echo json_encode($respuesta);
		break;
		case 'setUbicacion':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			if(isloggedin()){
				if(isset($_POST['latitud']) && $filtro->latitudLongitud($_POST['latitud']) && $filtro->limiteTamano($_POST['latitud'], 12, 20)){																			
					if(isset($_POST['longitud']) && $filtro->latitudLongitud($_POST['longitud']) && $filtro->limiteTamano($_POST['longitud'], 12, 20)){
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
						$usuario = new Usuario($conexion, $userid);
						if($usuario->getDato('id')){
							if($usuario->setDato('aim', $_POST['latitud'].'&&'.$_POST['longitud'])){
								$respuesta['estado'] = 'ok';
							}
						}	
					}
				}	
			}
			echo json_encode($respuesta);
		break;
		case 'matricularCursoGratis': 	//se automatricula este usuario a un curso el cual es gratuito
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
			require_login();
			$PAGE->set_context(context_system::instance());
			if(isset($_POST['tipocurso']) && $filtro->soloNumerosyLetrasMayusculas($_POST['tipocurso']) && $filtro->limiteTamano($_POST['tipocurso'], 1, 32)){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
				$sitio = new Sitio($conexion);
				$tipocurso = $sitio->getTiposCurso($_POST['tipocurso'], true, false, true);
				if(count($tipocurso)>0){
					$tipocurso = $tipocurso[0];
					if($tipocurso['gratis']==1){
						
						$maximoscursossinterminar = 5;
						
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
						$usuario = new Usuario($conexion, $userid);
						if($usuario->getDato('id')){
							
							//miramos cuantos cursos gratuitos aun no ha terminado
							$gratissinterminar = 0;
							$finalizaciones = $usuario->getFinalizacionesCurso();
							$todoslostipos = $sitio->getTiposCurso('', false, false, false);
							
							$tiposgratuitos = array();
							foreach($todoslostipos as $tt){
								if($tt['gratis']==1){
									array_push($tiposgratuitos, $tt['codigocurso']);
								}
							}
							foreach($finalizaciones as $fi){
								if($fi['funixfin']==0){
									$partes = explode('-', $fi['shortname']);
									if(count($partes)==2){
										if(in_array($partes[1], $tiposgratuitos)){
											$gratissinterminar++;
										}
									}
								}
							}
							//fin de mirar cuantos cursos gratuitos aun no ha terminado
							if($gratissinterminar<$maximoscursossinterminar){
								$sepuedematricular = true;
								
								$tipomatriculados = array();													
								//obtenemos los cursos en los que esta matriculado de este mismo tipo
								$matriculados = $usuario->getCursos(true);
								foreach($matriculados as $ma){
									$partes = explode('-', $ma['shortname']);
									if(count($partes)==2){
										if($partes[0]==$_POST['tipocurso']){
											array_push($tipomatriculados, $ma);
										}
									}
								}													
								
								$ultimaversion = $sitio->getIdsUltimasVersionesCursos($_POST['tipocurso']);
								if(count($ultimaversion)>0){
									foreach($tipomatriculados as $tm){
										if($tm['course_id']==$ultimaversion[0]['id']){
											$respuesta['codigo'] = 'tieneelultimo';
											$sepuedematricular = false;
											break;
										}
									}
									if($sepuedematricular){
										require_once('../../../../course/lib.php');
										require_once('../../../../course/externallib.php');										
										$enrolplugin = enrol_get_plugin('manual');
										// Lookup the manual enrolment instance for this course.
										$instances = enrol_get_instances($ultimaversion[0]['id'], true);  //1077 ES EL ID DEL CURSO
										foreach ($instances as $instance) {
											if ($instance->enrol === 'manual') {							
												break;
											}
										}
										if ($instance->enrol !== 'manual') {						
											throw new coding_exception('No manual enrol plugin in course');
										}									
										// Enrol the user with the required role					
										$enrolplugin->enrol_user($instance, $userid, 5);
										
										//cambiamos la fecha de inicio del curso y la fecha de vencimiento para que solo pueda hacer el curso en un rango de tiempo:
										include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Comun.php');
										$comun = new Comun();
										$mysql_datetime = date("Y-m-d");
										$expiracion = $comun->operacion_fecha($mysql_datetime, 60);									
										$usuario->updateFechaMatriculacionCurso($ultimaversion[0]['id'], 'inicio', $mysql_datetime);																
										$usuario->updateFechaMatriculacionCurso($ultimaversion[0]['id'], 'fin', $expiracion);
										
										if ($instance->enrol === 'manual') {
											$respuesta['estado'] = 'ok';
											$respuesta['datos'] = $ultimaversion[0]['id'];
										}
									}
								}
							}else{
								$respuesta['codigo'] = 'gratissinterminar';
								$respuesta['datos'] = $maximoscursossinterminar;
							}
						}
					}				
				}
			}
			echo json_encode($respuesta);
		break;
		case 'getCursoVencido':		//Obtiene las fechas de matriculacion y envia un dato si tiene el curso vencido o no.
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
			if(isloggedin()){
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){														
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
					$curso = new Curso($conexion, $_POST['idcurso']);
					if($curso->getDato('id')){
						$respuesta['estado'] = 'ok';	
						$respuesta['datos'] = 'no';
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
						$usuario = new Usuario($conexion, $userid);
						$fechas = $usuario->getFechaMatriculacionCurso($_POST['idcurso'], true);
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Comun.php');
						$comun = new Comun();		
						if(isset($fechas['timeend']) && $comun->fechaValida($fechas['timeend'])){
							if($comun->diasEntreFechaYhoy($fechas['timeend'])>0){
								$respuesta['datos'] = 'si';
							}
						}
					}
				}
			}	
			echo json_encode($respuesta);
		break;
		case 'getDatosProgreso':
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			$respuesta['insignias'] = array();
			$respuesta['datospersonales'] = array();
			$respuesta['stringmesadeayuda'] = '';
			$respuesta['mostrarpopareaspreferidas'] = false;
			//require_login();
			$vermensajefoto = true;
			$vermensajeubicacion = false;
			if(isset($_POST['ref'])){
				switch($_POST['ref']){
					case 'my':
						$vermensajeubicacion = true;	//solo se verá el mensaje de ubicacion cuando esté posicionado en el dashboard.
					break;
					case 'changepassword':
						$vermensajefoto = false;						
					break;
					case 'policy':
						$vermensajefoto = false;						
					break;
					case 'staticpage':
						$vermensajefoto = false;						
					break;
				}
			}
			
			$lang = 'es';  //lenguaje predeterminado
			$langpermitidos = array('es', 'en');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
			if(in_array(current_language(), $langpermitidos)){
				$lang = current_language();				
			}				
			include_once('../lang/'.$lang.'/interfaz.php');
			
			$respuesta['datospersonales']['id'] = '';
			$respuesta['datospersonales']['email'] = '';
			$respuesta['datospersonales']['description'] = '';
			$respuesta['datospersonales']['aim'] = '';
			
			if(isloggedin()){  //necesariamente la persona debio haber iniciado sesion.		
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
				$usuario = new Usuario($conexion, $userid);
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
				$sitio = new Sitio($conexion);
				
				$cursosmatriculados = $usuario->getCursos(false);
				$datosfinalizaciones = $usuario->getFinalizacionesCurso();
				$vencimientos = $usuario->cursoVencido();
				$ultimasversionescursos = $sitio->getIdsUltimasVersionesCursos();
				
				$tam = count($cursosmatriculados);
				
				$idsinstructoresprocesados = array();
				$idscursosmatriculados = array();
				$imagenesinstructores = array();
																
				for($i=0; $i<$tam; $i++){
					$curso = new Curso($conexion, $cursosmatriculados[$i]['course_id']);
					if(!in_array($cursosmatriculados[$i]['course_id'], $idscursosmatriculados)){
						array_push($idscursosmatriculados, $cursosmatriculados[$i]['course_id']);
					}
					$cursosmatriculados[$i]['finalizado'] = '';
					$cursosmatriculados[$i]['matriculado'] = '1';										
					$cursosmatriculados[$i]['puntminima'] = 3;
					$cursosmatriculados[$i]['notafinal'] = '0.0';
					$cursosmatriculados[$i]['vencido'] = false;
					$cursosmatriculados[$i]['diasvencido'] = -1;
					//$cursosmatriculados[$i]['imagencurso'] = $curso->getUrlImagenCurso();	//forma antigua, trae la imagen desde el moodle
					$cursosmatriculados[$i]['imagencurso'] = $curso->getUrlImagenesTipoCurso();
					$datos = $cursosmatriculados[$i]['imagencurso'];
					if(count($cursosmatriculados[$i]['imagencurso'])>0){
						$cursosmatriculados[$i]['imagencurso'] = $cursosmatriculados[$i]['imagencurso']['imagenm'];						
					}else{
						$cursosmatriculados[$i]['imagencurso'] = '';
					}
					
					$cursosmatriculados[$i]['videop'] = $datos['videop'];
					$cursosmatriculados[$i]['videog'] = $datos['videog'];
					$cursosmatriculados[$i]['gratis'] = $datos['gratis'];
					
					$cursosmatriculados[$i]['codigo'] = $datos['codigo'];
					$cursosmatriculados[$i]['urlamigable'] = $datos['urlamigable'];
					
					//--
					$cursosmatriculados[$i]['fotoinstructor'] = '';
					$cursosmatriculados[$i]['nombreinstructor'] = '';
					$cursosmatriculados[$i]['idinstructor'] = '';
					if($datos['idinstructor']!='' && $datos['idinstructor']!='0'){
						$cursosmatriculados[$i]['idinstructor'] = $datos['idinstructor'];
						$cursosmatriculados[$i]['nombreinstructor'] = trim(ucwords(mb_strtolower($datos['firstname'].' '.$datos['lastname'], 'UTF-8')));
						if(!in_array($datos['idinstructor'], $idsinstructoresprocesados)){
							array_push($idsinstructoresprocesados, $datos['idinstructor']);
							$usuarioinstructor = new Usuario($conexion, $datos['idinstructor']);
							
							$user = new stdClass();
							$user->id = $datos['idinstructor'];
							$user->picture = $usuarioinstructor->getDato('picture');
							$user->firstname = $usuarioinstructor->getDato('firstname');
							$user->lastname = $usuarioinstructor->getDato('lastname');
							$user->firstnamephonetic = '';
							$user->lastnamephonetic = '';
							$user->middlename = '';
							$user->alternatename = '';
							$user->imagealt = $usuarioinstructor->getDato('firstname').' '.$usuarioinstructor->getDato('lastname');
							$user->email = $usuarioinstructor->getDato('email');
							$cursosmatriculados[$i]['fotoinstructor'] = $OUTPUT->user_picture($user, array('size'=>35, 'alttext'=>false, 'link'=>false, 'class'=>'avatar user-thumb image image--avatar'));
							array_push($imagenesinstructores, array('id'=>$datos['idinstructor'], 'imagen'=>$cursosmatriculados[$i]['fotoinstructor']));							
						}else{
							foreach($imagenesinstructores as $ii){
								if($ii['id']==$datos['idinstructor']){
									$cursosmatriculados[$i]['fotoinstructor'] = $ii['imagen'];
								}
							}
						}
					}
					//--
					
					$cursosmatriculados[$i]['vencido'] = '0';  //no se usa pero se envia por si acaso hay que mostrar que un curso ya esta cerrado

					foreach($datosfinalizaciones as $df){
						if($cursosmatriculados[$i]['course_id']==$df['course_id']){
							$cursosmatriculados[$i]['finalizado'] = $df['funixfin'];
							$cursosmatriculados[$i]['notafinal'] = $df['finalgrade'];
							
							//arreglar la notafinal
							if(strlen($cursosmatriculados[$i]['notafinal'])>3){
								$cursosmatriculados[$i]['notafinal'] = substr($cursosmatriculados[$i]['notafinal'], 0, 3);
							}
							
							break;
						}
					}
					foreach($vencimientos as $ven){
						if($cursosmatriculados[$i]['course_id']==$ven['idcurso']){
							$cursosmatriculados[$i]['vencido'] = $ven['vencido'];
							$cursosmatriculados[$i]['diasvencido'] = $ven['diasvencido'];
							if($cursosmatriculados[$i]['diasvencido']<0 && $sesion->getPermiso(2)){
								$cursosmatriculados[$i]['diasvencido'] = 0;
							}	
						}	
					}
				}								
				$respuesta['datos'][] = array('tipo'=>'miscursos', 'nombre'=>'Mis Cursos', 'cursos'=>$cursosmatriculados);
				
				//procesamos los cursos de las areas
				$areaspreferidas = $usuario->getDato('areaspreferidas');				
				$areassecciones = array();
				if(count($areaspreferidas)==0){
					$respuesta['mostrarpopareaspreferidas'] = true;
					array_unshift($areaspreferidas, 9999); //9999 seria cursos populares
				}else{
					array_unshift($areaspreferidas, 9999); //9999 seria cursos populares
					$tamareas = count($areaspreferidas);
					for($i=0; $i<$tamareas; $i++){
						if($areaspreferidas[$i]!=9999){
							$datosarea = $sitio->getCursosArea($areaspreferidas[$i], $ultimasversionescursos, true);
						}else{							
							$datosarea = array();							
							$datosarea['datos'] = $sitio->getCursosPopulares(10, 90, $ultimasversionescursos);
							$datosarea['estado'] = 'ok';  //hack
						}
						if($datosarea['estado']=='ok'){							
							$datosarea = $datosarea['datos'];
							$tamdatosarea = count($datosarea);
							$nombrearea = '';
							for($j=0; $j<$tamdatosarea; $j++){
																						
								$datosarea[$j]['finalizado'] = '';
								$datosarea[$j]['matriculado'] = '0';
								if(in_array($datosarea[$j]['course_id'], $idscursosmatriculados)){
									$datosarea[$j]['matriculado'] = '1';
								}	
								$datosarea[$j]['puntminima'] = 3;
								$datosarea[$j]['notafinal'] = '0.0';	
								$datosarea[$j]['codigo'] = $datosarea[$j]['codigocurso'];  //conversion para la interfaz grafica								
								
								$datosarea[$j]['fotoinstructor'] = '';
								$datosarea[$j]['nombreinstructor'] = '';				
								$datosarea[$j]['vencido'] = false;
								$datosarea[$j]['diasvencido'] = -1;
								
								$nombrearea = $datosarea[$j]['nombrearea'];
								
								//imagen y nombre del instructor
								if($datosarea[$j]['idinstructor']!='' && $datosarea[$j]['idinstructor']!='0'){
									$datosarea[$j]['nombreinstructor'] = ucwords(mb_strtolower($datosarea[$j]['instructornombre'], 'UTF-8'));
									if(!in_array($datosarea[$j]['idinstructor'], $idsinstructoresprocesados)){
										array_push($idsinstructoresprocesados, $datosarea[$j]['idinstructor']);
										$usuarioinstructor = new Usuario($conexion, $datosarea[$j]['idinstructor']);
										
										$user = new stdClass();
										$user->id = $datosarea[$j]['idinstructor'];
										$user->picture = $usuarioinstructor->getDato('picture');
										$user->firstname = $usuarioinstructor->getDato('firstname');
										$user->lastname = $usuarioinstructor->getDato('lastname');
										$user->firstnamephonetic = '';
										$user->lastnamephonetic = '';
										$user->middlename = '';
										$user->alternatename = '';
										$user->imagealt = $usuarioinstructor->getDato('firstname').' '.$usuarioinstructor->getDato('lastname');
										$user->email = $usuarioinstructor->getDato('email');
										$datosarea[$j]['fotoinstructor'] = $OUTPUT->user_picture($user, array('size'=>35, 'alttext'=>false, 'link'=>false, 'class'=>'avatar user-thumb image image--avatar'));
										array_push($imagenesinstructores, array('id'=>$datosarea[$j]['idinstructor'], 'imagen'=>$datosarea[$j]['fotoinstructor']));							
									}else{
										foreach($imagenesinstructores as $ii){
											if($ii['id']==$datosarea[$j]['idinstructor']){
												$datosarea[$j]['fotoinstructor'] = $ii['imagen'];
											}
										}
									}
								}
								//fin de imagen y nombre del instructor
								
								foreach($datosfinalizaciones as $df){
									if($datosarea[$j]['course_id']==$df['course_id']){
										$datosarea[$j]['finalizado'] = $df['funixfin'];
										$datosarea[$j]['notafinal'] = $df['finalgrade'];
										//arreglar la notafinal
										if(strlen($datosarea[$j]['notafinal'])>3){
											$datosarea[$j]['notafinal'] = substr($datosarea[$j]['notafinal'], 0, 3);
										}
										break;
									}
								}
								
								foreach($vencimientos as $ven){
									if($datosarea[$j]['course_id']==$ven['idcurso']){
										$datosarea[$j]['vencido'] = $ven['vencido'];
										$datosarea[$j]['diasvencido'] = $ven['diasvencido'];
										if($datosarea[$j]['diasvencido']<0 && $sesion->getPermiso(2)){
											$datosarea[$j]['diasvencido'] = 0;
										}	
									}	
								}
								
							}
							if(count($datosarea)>0){
								$respuesta['datos'][] = array('tipo'=>'area', 'nombre'=>$nombrearea, 'cursos'=>$datosarea);
							}
						}
					}
				}
				//fin de procesar los cursos de las areas
				
				$respuesta['datospersonales']['id'] = $usuario->getDato('id');
				$respuesta['datospersonales']['email'] = $usuario->getDato('email');
				$respuesta['datospersonales']['description'] = $usuario->getDato('description');
				$respuesta['datospersonales']['aim'] = $usuario->getDato('aim');
								
				$respuesta['iduserx'] = $userid;  //cuando tiene la x al final es que esta variable es temporal, de debugueo. y habra que borrarla.
				$respuesta['recargar'] = '0';
				$respuesta['estado'] = 'ok';
				$respuesta['sesskey'] = $USER->sesskey;
				$respuesta['lang'] = $lang;															
				$respuesta['foto'] = '1';
				if($USER->picture==0){
					$mensajefotomostrado = $sesion->getDatoSesion('mensajefotomostrado');
					if(!$mensajefotomostrado && $vermensajefoto && !$respuesta['mostrarpopareaspreferidas']){
						$respuesta['foto'] = '0';
						$sesion->setDatoSesion('mensajefotomostrado', true);
					}
				}
				$respuesta['stringmesadeayuda'] = '';				
				$respuesta['estado'] = 'ok';

				$respuesta['txt'] = array(
					'txt_periododepruebas'=>txt_periododepruebas,
					'txt_pordondevoy'=>txt_pordondevoy,
					'txt_glo_palabratipo'=>txt_glo_palabratipo,
					'txt_foro_palabratipo'=>txt_foro_palabratipo,
					'txt_scorm_palabratipo'=>txt_scorm_palabratipo,
					'txt_quiz_palabratipo'=>txt_quiz_palabratipo,
					'txt_game_palabratipo'=>txt_game_palabratipo,
					'txt_boost_palabratipo'=>txt_boost_palabratipo,
					'txt_lec_palabratipo'=>txt_lec_palabratipo,
					'txt_res_palabratipo'=>txt_res_palabratipo,
					'txt_nopuedesparticipar'=>txt_nopuedesparticipar,
					'txt_debemarcarcomofinalizada'=>txt_debemarcarcomofinalizada,
					'txt_cursofinalizado'=>txt_cursofinalizado,
					'txt_yahazparticipadoen'=>txt_yahazparticipadoen,
					'txt_teinformamosque'=>txt_teinformamosque,
					'txt_marcarfinalizada'=>txt_marcarfinalizada,
					'txt_no'=>txt_no,
					'txt_si'=>txt_si,
					'txt_simarcarcomofinalizada'=>txt_simarcarcomofinalizada,
					'txt_estableciendofinalizada'=>txt_estableciendofinalizada,
					'txt_moduloactual'=>txt_moduloactual,
					'txt_bloqueado'=>txt_bloqueado,
					'txt_deseasmarcarfinalizada'=>txt_deseasmarcarfinalizada,
					'txt_quiz_mejorpuntaje'=>txt_quiz_mejorpuntaje,
					'txt_leermas'=>txt_leermas,
					'txt_cursospara'=>txt_cursospara,
					'txt_enproceso'=>txt_enproceso,
					'txt_cursopendiente'=>txt_cursopendiente,
					'txt_debefinalizarcursoparaseguir'=>txt_debefinalizarcursoparaseguir,
					'txt_nodebeempezarcursohastaque'=>txt_nodebeempezarcursohastaque,
					'txt_cursofinalizado2'=>txt_cursofinalizado2,					
					'txt_notaexamenfinal'=>txt_notaexamenfinal,
					'txt_notaexamenfinalactual'=>txt_notaexamenfinalactual,
					'txt_notafinal'=>txt_notafinal,
					'txt_cursosadicionales'=>txt_cursosadicionales,
					'txt_actualizaciones'=>txt_actualizaciones,
					'txt_unavesmatriculadodebeempezar'=>txt_unavesmatriculadodebeempezar,
					'txt_esperarcierredecurso'=>txt_esperarcierredecurso,
					'txt_seleautomatricularaunaveztermine'=>txt_seleautomatricularaunaveztermine,
					'txt_asistirpresencialunaveztermine'=>txt_asistirpresencialunaveztermine,
					'txt_asistirpresencial'=>txt_asistirpresencial,
					'txt_calificacionactual'=>txt_calificacionactual,
					'txt_calificacionfinal'=>txt_calificacionfinal,
					'txt_cursosobligatgorios'=>txt_cursosobligatgorios,
					'txt_matricularme'=>txt_matricularme,
					'txt_matriculando'=>txt_matriculando,
					'txt_completatuperfil'=>txt_completatuperfil,
					'txt_mastarde'=>txt_mastarde,
					'txt_iraeditarmiperfil'=>txt_iraeditarmiperfil,										
					'txt_subeimagenusuario'=>txt_subeimagenusuario,
					'txt_otroscursosadicionales'=>txt_otroscursosadicionales,
					'txt_comprarcursos'=>txt_comprarcursos,
					'txt_volver'=>txt_volver,
					'txt_herramientas'=>txt_herramientas,
					'txt_espanol'=>txt_espanol,
					'txt_ingles'=>txt_ingles,
					'txt_preguntaconfirmar'=>txt_preguntaconfirmar,
					'txt_comprar'=>txt_comprar,
					'txt_acuerdo'=>txt_acuerdo,
					'txt_debeaceptaracuerdo'=>txt_debeaceptaracuerdo,
					'txt_aceptaracuerdo'=>txt_aceptaracuerdo,
					'txt_aceptar'=>txt_aceptar,
					'txt_titulomesadeayuda'=>txt_titulomesadeayuda,
					'txt_intromesadeayuda'=>txt_intromesadeayuda,
					'txt_linkfaq'=>txt_linkfaq,
					'txt_botonmesadeayuda'=>txt_botonmesadeayuda,
					'txt_vencido'=>txt_vencido,
					'txt_osuperior'=>txt_osuperior,
					'txt_insignias'=>txt_insignias,	
					'txt_recurrentespendientes'=>txt_recurrentespendientes,
					'txt_enelperfilde'=>txt_enelperfilde,
					'txt_debeterminarrecurrentes'=>txt_debeterminarrecurrentes,
					'txt_antesde'=>txt_antesde,
					'txt_nuevorequerimiento'=>txt_nuevorequerimiento,
					'txt_actualizacionespendientes'=>txt_actualizacionespendientes,
					'txt_informacionpersonal'=>txt_informacionpersonal,
					'txt_correodenoti'=>txt_correodenoti,
					'txt_idenplataforma'=>txt_idenplataforma,
					'txt_estado'=>txt_estado,
					'txt_contratado'=>txt_contratado,
					'txt_nocontratado'=>txt_nocontratado,
					'txt_contratadovacaciones'=>txt_contratadovacaciones,
					'txt_nohaymensajes'=>txt_nohaymensajes,
					'txt_nohayinsignias'=>txt_nohayinsignias,
					'txt_filtros'=>txt_filtros,
					'txt_seleccione'=>txt_seleccione,
					'txt_cerrar'=>txt_cerrar,
					'txt_idioma'=>txt_idioma,
					'txt_cerrarsesion'=>txt_cerrarsesion,
					'txt_establecerubicacion'=>txt_establecerubicacion,
					'txt_ubicaciondescripcion'=>txt_ubicaciondescripcion,
					'txt_zoommapa'=>txt_zoommapa,
					'txt_borrarubicacion'=>txt_borrarubicacion,
					'txt_seleccioneubicacion'=>txt_seleccioneubicacion,
					'txt_completatuperfilubicacion'=>txt_completatuperfilubicacion,
					'txt_todosloscursos'=>txt_todosloscursos,
				);

				
			}
			
			echo json_encode($respuesta);
		break;
		case 'getTextosCurso':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
				$curso = new Curso($conexion, $_POST['idcurso']);
				if($curso->getDato('id')){				
					$lang = 'es';  //lenguaje predeterminado
					$shortname = $curso->getDato('shortname');
					$partes = explode('-', $shortname);
					$tampartes = count($partes);
					if($tampartes>2){
						$tampartes--;
						switch($partes[$tampartes]){
							case 'ESP':
								$lang = 'es';
							break;	
							case 'ENG':
								$lang = 'en';
							break;
							case 'FR':
								$lang = 'fr';
							break;
						}
					}
					include_once('../lang/'.$lang.'/interfaz.php');
					
					$respuesta['txt'] = array(
						'txt_laactividadnoestadisponible'=>txt_laactividadnoestadisponible,
						'txt_completarenorden'=>txt_completarenorden,
						'txt_aceptar'=>txt_aceptar,
						'txt_nuevointento'=>txt_nuevointento,
						'txt_infonuevointento'=>txt_infonuevointento,
						'txt_confirmarnuevointento'=>txt_confirmarnuevointento,
						'txt_cerrar'=>txt_cerrar,
						'txt_cerrando'=>txt_cerrando,
						'txt_pantallacompleta'=>txt_pantallacompleta,
					);
					$respuesta['estado'] = 'ok';
				}
			}				
			echo json_encode($respuesta);
		break;
		case 'getTextosEnrolment':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['txt'] = array();
			
			$lang = 'es';  //lenguaje predeterminado
			$langpermitidos = array('es', 'en', 'fr');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
			if(in_array(current_language(), $langpermitidos)){
				$lang = current_language();				
			}				
			include_once('../lang/'.$lang.'/interfaz.php');
			
			$respuesta['txt'] = array(
				'txt_matriculacionvencida'=>txt_matriculacionvencida,
				'txt_matriculacionvencidamensaje'=>txt_matriculacionvencidamensaje,
				'txt_aceptar'=>txt_aceptar,
			);
			$respuesta['estado'] = 'ok';
			echo json_encode($respuesta);
		break;
		case 'getActividadesCursoUsuario':  //esta funcion hay que agregarla tambien al controlador de training...
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';			
			$respuesta['nombrecurso'] = '';
			$respuesta['imagencurso'] = '';
			$respuesta['sesskey'] = '';
						
			if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
				
				$idesecuence = '';
				if(isset($_POST['idsencuence']) && $filtro->soloNumeros($_POST['idsencuence']) && $filtro->limiteTamano($_POST['idsencuence'], 1, 8)) {
					$idesecuence = '&idsencuence='.$_POST['idsencuence'];
				}
				
				$actualizarnotificaciones = false;
				if(isset($_POST['actnotificaciones']) && $filtro->soloNumeros($_POST['actnotificaciones']) && $filtro->limiteTamano($_POST['actnotificaciones'], 1, 1) && $_POST['actnotificaciones']=='1'){
					$actualizarnotificaciones = true;
				}
				
				include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
				$curso = new Curso($conexion, $_POST['idcurso']);
				if($curso->getDato('id')){
									
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
					$usuario = new Usuario($conexion, $userid);
					if($usuario->getDato('id')){					
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Sitio.php');
						$sitio = new Sitio($conexion);
						
						$respuesta['datos'] = $curso->getActividadesCalificablesUsuario($userid, $idesecuence, $actualizarnotificaciones);
						$respuesta['nombrecurso'] = $curso->getDato('fullname');						
						//$respuesta['imagencurso'] = $curso->getUrlImagenCurso(); //esta era la forma de obtener la imagen de moodle.
						
						$respuesta['imagencurso'] = $curso->getUrlImagenesTipoCurso();
						if(count($respuesta['imagencurso'])>0){
							$respuesta['imagencurso'] = $respuesta['imagencurso']['imagenm'];
						}else{
							$respuesta['imagencurso'] = '';
						}
						
						$respuesta['estado'] = 'ok';
					}
											
					//fin de la conexion remota para hacer esta accion.
					$respuesta['sesskey'] = $USER->sesskey;
					$respuesta['txt'] = array(
						'txt_nopuedesparticipar'=>'Aún no puedes participar en esta actividad',
						'txt_bloqueado'=>'Bloqueado',
						'txt_quiz_mejorpuntaje'=>'Tu mejor resultado es de',
						'txt_debemarcarcomofinalizada'=>'Debes marcarla como finalizada.',
						'txt_leermas'=>'Leer más',
						'txt_moduloactual'=>'Módulo actual:',
						'txt_yahazparticipadoen'=>'Ya has participado en',
						'txt_teinformamosque'=>'te informamos que',
						'txt_marcarfinalizada'=>'Marcar como finalizada?',
						'txt_no'=>'No',
						'txt_simarcarcomofinalizada'=>'Si, marcar como finalizada',
						'txt_estableciendofinalizada'=>'Estableciendo como finalizada...',
						'txt_siguienteactividad'=>'Siguiente actividad',
						'txt_acuerdo'=>'Acuerdo de confidencialidad',
						'txt_debeaceptaracuerdo'=>'Debe aceptar el acuerdo de confidencialidad para poder continuar',
						'txt_aceptaracuerdo'=>'Aceptar acuerdo de confidencialidad',
						'txt_aceptar'=>'Aceptar',
						'txt_actividadanterior'=>'Actividad anterior',
						'txt_iralcurso'=>'Ir al curso',
						'txt_siguienteactividad'=>'Siguiente actividad',							
						'txt_completado'=>'Completado',
						'txt_tuprogresosubioa'=>'Tu progrso ha subido a',
					);
				}
			}						
			echo json_encode($respuesta);
		break;
		case 'getPopUpInformativoActividad':
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)) {
				if(isset($_POST['tipoactividad']) && $_POST['tipoactividad']!=''){ 			//   mod/scorm    mod/glossary etc
					$actividadespermitidas = array('forum', 'quiz', 'glossary', 'scorm');					
					if(in_array($_POST['tipoactividad'], $actividadespermitidas)){
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
						$curso = new Curso($conexion, $_POST['idcurso']);
						if($curso->getDato('id')){					
							$lang = 'es';  //lenguaje predeterminado
							$shortname = $curso->getDato('shortname');
							$partes = explode('-', $shortname);
							$tampartes = count($partes);
							if($tampartes>2){
								$tampartes--;
								switch($partes[$tampartes]){
									case 'ESP':
										$lang = 'es';
									break;	
									case 'ENG':
										$lang = 'en';
									break;
									case 'FR':
										$lang = 'fr';
									break;
								}
							}																															
							include_once('../lang/'.$lang.'/interfaz.php');
							if(isset($_SESSION['info_'.$_POST['tipoactividad']])){
								$respuesta['datos']['verpopup'] = $sesion->getDatoSesion('info_'.$_POST['tipoactividad']);
								$respuesta['datos']['txt_nombretipoactividad'] = constant("txt_".$_POST['tipoactividad']);
								$respuesta['datos']['txt_definicion'] = constant("txt_".$_POST['tipoactividad']."_descripcion");
								$respuesta['datos']['txt_actividadnomostrarpop'] = txt_actividadnomostrarpop;
								$respuesta['datos']['txt_aceptar'] = txt_aceptar;
								$respuesta['datos']['lang'] = $lang;
								$respuesta['estado'] = 'ok';
							}
						}
					}
				}
			}
			echo json_encode($respuesta);
		break;
		case 'desactivarPopInfoActividad':
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			if(isset($_POST['tipoactividad']) && $_POST['tipoactividad']!=''){
				$actividadespermitidas = array('forum', 'quiz', 'glossary', 'scorm');
				if(in_array($_POST['tipoactividad'], $actividadespermitidas)){
					if($sesion->setDatoSesion('info_'.$_POST['tipoactividad'], false)){
						$respuesta['estado'] = 'ok';	
					}										
				}																								
			}			
			echo json_encode($respuesta);
		break;
		case 'getIdActividadByIntento':  //dice basado en un intento de quiz si es el examen final y ya fue aprobado para poder dar termado un curso-
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array('parametrizado'=>'0', 'idcurso'=>'', 'nombrecurso'=>'', 'idactividad'=>'', 'puntajeintento'=>'', 'puntajegeneral'=>'', 'datosprogreso'=>array(), 'actividadescurso'=>array());
			$respuesta['parametrizado'] = '0';								
			if(isloggedin()){
				if(isset($_POST['tipoactividad']) && $filtro->soloLetras($_POST['tipoactividad']) && $filtro->limiteTamano($_POST['tipoactividad'], 1, 16)){
					if(isset($_POST['idintento']) && $filtro->soloNumeros($_POST['idintento']) && $filtro->limiteTamano($_POST['idintento'], 1, 16)){						
					
						$lang = 'es';  //lenguaje predeterminado
						$langpermitidos = array('es', 'en', 'fr');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
						if(in_array(current_language(), $langpermitidos)){
							$lang = current_language();
						}				
						include_once('../lang/'.$lang.'/interfaz.php');
						
						//conexion remota para hacer esta accion (necesitamos que el TCS nos diga los datos de la actividad)
						$urltcs = URLBASETCS.'controladores/controladorinterfaz.php';
						$stringautenticacion = 'a=getIdActividadByIntento&tipoactividad='.$_POST['tipoactividad'].'&idintento='.$_POST['idintento'].'&idusuario='.$userid.'&lenguaje='.$lang;
						$respuestaremota = $conexion->conexionRemota($urltcs, $stringautenticacion);											
						if($respuestaremota['estado']=='ok'){  //se comunicó bien y obtuvo respuesta (no se sabe si fue una respuesta valida o con errores).
							$respuestaremotax = (array)json_decode($respuestaremota['datos'], true);
							if(isset($respuestaremotax['estado'])){
								switch($respuestaremotax['estado']){
									case 'ok':
										$respuesta['datos'] = $respuestaremotax['datos'];
										$respuesta['estado'] = 'ok';
									break;
									case 'error':
										$respuesta['codigo'] = 'errorentcs';
									break;
								}							
							}
							
						}					
						//fin de la conexion remota para hacer esta accion.
																										
						$respuesta['txt'] = array(
							'txt_puntajegexamen'=>txt_puntajegexamen,
							'txt_examenaprobado'=>txt_examenaprobado,
							'txt_puntuaciondelintento'=>txt_puntuaciondelintento,
							'txt_puntuacioninsuficiente'=>txt_puntuacioninsuficiente,
							'txt_debealcanzarpuntminima'=>txt_debealcanzarpuntminima,
							'txt_actividadfinalizada'=>txt_actividadfinalizada,
							'txt_puedesseguir'=>txt_puedesseguir,
							'txt_empezaractividad'=>txt_empezaractividad,
							'txt_puedesmatricularte'=>txt_puedesmatricularte,
							'txt_felicitbloquecursos'=>txt_felicitbloquecursos,
							'txt_cursocompletado'=>txt_cursocompletado,
							'txt_hazfinalizadoelcursode'=>txt_hazfinalizadoelcursode,
							'txt_satisfactoriamente'=>txt_satisfactoriamente,
							'txt_aceptar'=>txt_satisfactoriamente,
						);	
					}
				}
			}
			echo json_encode($respuesta);			
		break;
		case 'actividadActualCurso':  //retorna cual es el id de la actividad actual del curso actual
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
			$respuesta['sesskey'] = $USER->sesskey;
			if(isloggedin()){
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){
					
					$lang = 'es';  //lenguaje predeterminado
					$langpermitidos = array('es', 'en', 'fr');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
					if(in_array(current_language(), $langpermitidos)){
						$lang = current_language();
					}				
					include_once('../lang/'.$lang.'/interfaz.php');
					
					//conexion remota para hacer esta accion (necesitamos obtener los datos progreso del usuario de este curso)
					$urltcs = URLBASETCS.'controladores/controladorinterfaz.php';
					$stringautenticacion = 'a=actividadActualCurso&idcurso='.$_POST['idcurso'].'&idusuario='.$userid.'&lenguaje='.$lang;
					$respuestaremota = $conexion->conexionRemota($urltcs, $stringautenticacion);											
					if($respuestaremota['estado']=='ok'){  //se comunicó bien y obtuvo respuesta (no se sabe si fue una respuesta valida o con errores).
						$respuestaremotax = (array)json_decode($respuestaremota['datos'], true);
						if(isset($respuestaremotax['estado'])){
							switch($respuestaremotax['estado']){
								case 'ok':
									$respuesta['datos'] = $respuestaremotax['datos'];
									$respuesta['estado'] = 'ok';
								break;
								case 'error':
									$respuesta['codigo'] = 'errorentcs';
								break;
							}							
						}
						
					}					
					//fin de la conexion remota para hacer esta accion.
					$respuesta['txt'] = array(
						'txt_acuerdo'=>txt_acuerdo,
						'txt_debeaceptaracuerdo'=>txt_debeaceptaracuerdo,
						'txt_aceptaracuerdo'=>txt_aceptaracuerdo,
						'txt_aceptar'=>txt_aceptar,
					);
				}
			}								
			echo json_encode($respuesta);
		break;
		case 'getLenguajesDisponibles':
			$respuesta['estado'] = 'ok';	
			$respuesta['codigo'] = '';	
			$respuesta['datos']['disponibles'] = get_string_manager()->get_list_of_translations();
			$respuesta['datos']['actual'] = current_language();	
			
			$lang = 'es';  //lenguaje predeterminado
			$langpermitidos = array('es', 'en', 'fr');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
			if(in_array(current_language(), $langpermitidos)){
				$lang = current_language();				
			}				
			include_once('../lang/'.$lang.'/interfaz.php');

			$respuesta['txt'] = array(
				'txt_aceptar'=>txt_aceptar,
			);
			
			echo json_encode($respuesta);
		break;
		case 'setLenguajeUsuario':
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = '';
			if(isset($_POST['lenguaje']) && $filtro->soloLetras($_POST['lenguaje']) && $filtro->limiteTamano($_POST['lenguaje'], 2, 2)){
				$_POST['lenguaje'] = strtolower($_POST['lenguaje']);
				if(isloggedin()){
					if(get_string_manager()->translation_exists($_POST['lenguaje'], false)) {
						$SESSION->lang = $_POST['lenguaje'];
						include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.php');
						$usuario = new Usuario($conexion, $userid);
						if($usuario->getDato('id')){
							if($usuario->setDato('lang', $_POST['lenguaje'])){
								$sesion->setDatoSesion('forzarcambiaridioma', false);
								$respuesta['estado'] = 'ok';
							}
						}	
					}	
				}				
			}						
			echo json_encode($respuesta);
		break;		
		case 'setLenguajeLanding':
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = '';
			if(isset($_POST['lenguaje']) && $filtro->soloLetras($_POST['lenguaje']) && $filtro->limiteTamano($_POST['lenguaje'], 2, 2)){
				$_POST['lenguaje'] = strtolower($_POST['lenguaje']);				
				if(get_string_manager()->translation_exists($_POST['lenguaje'], false)) {
					$SESSION->lang = $_POST['lenguaje'];
					$respuesta['estado'] = 'ok';	
				}
			}						
			echo json_encode($respuesta);
		break;
		case 'setPopUpMostrado':
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';	
			$respuesta['datos'] = '';
			$tiposmensajes = array('mensajeubicacionmostrado', 'mensajeperfilesterminadosmostrado', 'mensajefotomostrado');
			if(isset($_POST['tipo']) && $filtro->soloLetras($_POST['tipo']) && $filtro->limiteTamano($_POST['tipo'], 5, 40) && in_array($_POST['tipo'], $tiposmensajes)){
				$sesion->setDatoSesion($_POST['tipo'], true);
				$respuesta['estado'] = 'ok';
			}else{
				$respuesta['codigo'] = 'invalido';	
			}
			echo json_encode($respuesta);
		break;
		
		case 'pruebafinalizacion':	//borrarlo cuando se encuentre una forma de finalizar los cursos por codigo.			
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
						
			require_once($CFG->libdir.'/completionlib.php');																		
			$course = $DB->get_record('course', array('id'=>355), '*', MUST_EXIST);
			$info = new completion_info($course);
			if(!$info->is_course_complete($userid)){								
				require_once($CFG->libdir.'/clilib.php');
				require_once($CFG->libdir.'/cronlib.php');
				
				$task = \core\task\manager::get_scheduled_task('core\task\completion_regular_task');				
				//cron_run_inner_scheduled_task($task);				
				//$task = new \core\task\completion_regular_task();
				//print_r($task);				
				$task->execute();								
				ob_end_clean();
				ob_clean();
				$respuesta['estado'] = 'ok';
				$respuesta['codigo'] = 'completado';
			}else{
				$respuesta['codigo'] = 'estabacompletado';
			}
			echo json_encode($respuesta);
		break;
		
		case 'setCursoCompletado':	//Estabelcer un curso como completado cuando realmente ya completo el curso pero el cron aun no lo ha marcado,   //OJO ESTO NO HA SIDO PROIBADO TOTALMETNE
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = '';
			$respuesta['datos2'] = array();
			if(isloggedin()){
				if(isset($_POST['idcurso']) && $filtro->soloNumeros($_POST['idcurso']) && $filtro->limiteTamano($_POST['idcurso'], 1, 8)){														
					include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Curso.php');
					$curso = new Curso($conexion, $_POST['idcurso']);
					if($curso->getDato('id')){
						
						
						//PROBAR ESTA NUEVA VERSION PARA 3.8.
						require_once($CFG->libdir.'/completionlib.php');																		
						$course = $DB->get_record('course', array('id'=>$_POST['idcurso']), '*', MUST_EXIST);
						$info = new completion_info($course);
						if(!$info->is_course_complete($userid)){
							require_once($CFG->libdir.'/clilib.php');
							require_once($CFG->libdir.'/cronlib.php');
							$task = \core\task\manager::get_scheduled_task('core\task\completion_regular_task');
							$task->execute();
							
							include_once($CFG->dirroot.'/theme/'.$CFG->theme.'/americana/src/modelos/Usuario.class.php');
							$usuario = new Usuario($conexion, $userid);
							if($usuario->getDato('id')){
								$datosfinalizaciones = $usuario->getFinalizacionesCurso();																
								$respuesta['datos'] = $datosfinalizaciones;
								foreach($datosfinalizaciones as $df){									
									if($df['course_id']==$_POST['idcurso']){
										if($df['funixfin']==0){
											sleep(20);
											$task = \core\task\manager::get_scheduled_task('core\task\completion_regular_task');
											$task->execute();
											$datosfinalizaciones = $usuario->getFinalizacionesCurso();								
											$respuesta['datos'] = $datosfinalizaciones;
										}
										if($filtro->soloNumeros($df['finalgrade']) && $df['finalgrade']<3){
											$respuesta['codigo'] = 'noaprobado';
										}										
										break;
									}
								}
							}	
														
							//ob_end_clean();
							ob_clean();
							if($respuesta['codigo']==''){
								$respuesta['estado'] = 'ok';
								$respuesta['codigo'] = 'completado';
							}
						}else{
							$respuesta['codigo'] = 'estabacompletado';
						}
						//FIN DE LA NUEVA VERSION.
						
						/*
							Este es el que funcionaba normalmente
						require_once($CFG->libdir.'/completionlib.php');																		
						$course = $DB->get_record('course', array('id'=>$_POST['idcurso']), '*', MUST_EXIST);
						$info = new completion_info($course);
						if(!$info->is_course_complete($userid)){
							include_once($CFG->dirroot.'/completion/cron.php');
							completion_cron_mark_started();
							completion_cron_criteria();							
							completion_cron_completions();
							ob_clean();
							$respuesta['estado'] = 'ok';
							$respuesta['codigo'] = 'completado';
						}else{
							$respuesta['codigo'] = 'estabacompletado';
						}*/
						
						
						/*global $DB;
						require_once($CFG->libdir.'/completionlib.php');																		
						$course = $DB->get_record('course', array('id'=>$_POST['idcurso']), '*', MUST_EXIST);
						$info = new completion_info($course);
						
						$info = new completion_info($course);
						// If the course is complete
						if ($info->is_course_complete($userid)) {							
							$completion->mark_complete();
							$respuesta['estado'] = 'ok';	
							$respuesta['codigo'] = 'marcadocompletado';
						}else{
							$respuesta['codigo'] = 'nocompletado'.serialize($info);
						}*/
						
						/*$criteria = $info->get_criteria(COMPLETION_CRITERIA_TYPE_ROLE);
						foreach ($criteria as $criterion) {
							$completions = $info->get_completions($userid, COMPLETION_CRITERIA_TYPE_ROLE);
							foreach ($completions as $completion) {
								if ($completion->is_complete()) {
									continue;
								}
								if ($completion->criteriaid === $criterion->id) {
									$criterion->complete($completion);
								}
							}
						}*/
						
						
					}
				}
			}
			echo json_encode($respuesta);
		break;
	}
?>