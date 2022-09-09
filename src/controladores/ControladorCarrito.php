<?php namespace controladores;
	
	use clases\Sesion as Sesion;	
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;
	use clases\Curso as Curso;
	use clases\Usuario as Usuario;
	use clases\Factura as Factura;
	use clases\Email as Email;
	use clases\CuponDescuento as CuponDescuento;
	use clases\Sitio as Sitio;
	
	class ControladorCarrito{
		private $conexion = false;
		private $sesion = false;				
		private $filtro = false;
		
		public function __construct(&$conexion){
			global $USER, $SESSION, $PAGE, $CFG;
			$this->conexion = $conexion;
			$this->sesion = new Sesion($this->conexion, $USER->id);
			$this->filtro = new Filtro();
			header("Content-Type: text/html;charset=utf-8");
			set_time_limit(5);  //600: 10 minutos corriendo.
			date_default_timezone_set('America/Bogota');
		}
		
		public function index(){
				
			global $USER;	
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){				
				$respuesta['estado'] = 'ok';
			}else{
				header('location: '.URLBASE.'login');
			}	
			
			return $respuesta;
		}	
		
		public function crear(){		//No funcuiona para el carrito de compra
					
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			return $respuesta;
		}
		
		
		/*	
			PUT
			URL http://localhost/levelupamericana/info/api/carrito/6    (donde 6 es el idcurso que se quiere agregar al carrito)
			Raw a enviar:
			{				
				"cantidad": "1",
				"cupon", "xxasdas"				
			}			
			Listo			
		*/
		public function editar($id){	//LISTO viene PUT AGREGA UN ELEMENTO AL CARRITO DE COMPRA. el $id es el id del curso a agregar al carrito de compras
			//echo 'Editando '.$id.' con data: ';
			
			global $USER;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			//$_SESSION['idusuario'] = '1';
			
			if(isloggedin()){
				if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){					
					$data = array();				
					$json = file_get_contents('php://input');			
					$data = json_decode($json, true);
					
					
					$mysql_datetime = date("Y-m-d H:i:s");
					
					$curso = new Curso($this->conexion, $id);
					if($curso->getDato('id')){
						if($curso->getDato('visible')==1){ //colocar tambien, si no ha iniciado						
							if($curso->getDato('precio')!=''){
								if($curso->getDato('hainiciado')==0){							
									$usuario = new Usuario($this->conexion, $USER->id);
									if($usuario->getDato('id') && $usuario->getDato('suspended')==0 && $usuario->getDato('deleted')==0){																														
										$respuesta['codigo'] = $data['cantidad'];
										if(isset($data['cantidad']) && $this->filtro->soloNumeros($data['cantidad']) && $this->filtro->limiteTamano($data['cantidad'], 1, 2) && $data['cantidad']>0){
											if(isset($data['cupon']) && (($this->filtro->soloAlias($data['cupon']) && $this->filtro->limiteTamano($data['cupon'], 1, 32)) ||  trim($data['cupon'])=='')  ){
												
												//miramos si ya tiene el curso matriculado, para evitar que lo compre de nuevo por error.
												$sitio = new Sitio($this->conexion);
												$matriculado = $sitio->getMatriculados($id, $USER->id, array(3, 5));
												if(count($matriculado)==0){
													//fin de mirar
													
													$pasa = true;
													if($data['cupon']!=''){
														$pasa = false;
														$cupon = new CuponDescuento($this->conexion);
														$datoscupon = $cupon->getCuponPorCodigo(mb_strtoupper($data['cupon'], 'UTF-8'));
														if(count($datoscupon)>0){
															if($datoscupon['estado']==1){
																//verificar que este vigente la fecha y que este activo, luego de eso colocar  $Pasa = true,
																$comun = new Comun();
																if($comun->segundosDesdeDatetime($datoscupon['fechahoravencimiento'])<0){
																																	
																	$cuponactual = $this->sesion->getDatoSesion('cuponporcentaje');
																	if($cuponactual==''){
																		$this->sesion->setDatoSesion('cuponporcentaje', $datoscupon['porcentajedescuento']);	//se establece en la sesión para tomarlo en la funcion que genera el volante y aplicarlo.
																		
																		$respuesta['codigo'] = 'redencion-cupon';																	
																		
																		//Calculamos el precio que tendra el curso y lo retornamos.
																		$nuevoprecio = $curso->getDato('precio')-(($curso->getDato('precio')*$datoscupon['porcentajedescuento'])/100);
																		$nuevoprecio = number_format($nuevoprecio, 0, ',', '.');		//se va a mostrar en pantalla asi que se muestran la separacion de miless
																		$respuesta['datos']['precio'] = number_format($curso->getDato('precio'), 0, ',', '.');
																		$respuesta['datos']['nuevoprecio'] = $nuevoprecio;
																		$respuesta['datos']['porcentajedescuento'] = $datoscupon['porcentajedescuento'];
																																			
																	}else{
																		$pasa = true;
																	}
																	
																	
																}else{
																	$respuesta['codigo'] = 'cupon-vencido';
																}
															}else{
																$respuesta['codigo'] = 'cupon-novalido';
															}
														}else{
															$respuesta['codigo'] = 'cupon-novalido';
														}
													}
													if($pasa){
														//$respuesta['codigo'] = 'pasa5';
														$data['cantidad'] = 1;  //Se forza a 1 ya que solo se van a vender de a 1 en 1 en este sistema.
														
														$totalmomento = number_format($curso->getDato('precio')*$data['cantidad'], 0, '.', '');
														//miramos si hay alguna factura abierta.																			
																								
														$facturaenproceso = $usuario->getFacturas($id, array(2));	//osea que ya existe una generada para este producto por este usuario									
														$pasa = true;	
														$factura = false;
														if(count($facturaenproceso)==0){	//si no existe una abierta
															$pasa = false;												
															$factura = new Factura($this->conexion);
															if($factura->setFactura($usuario->getDato('id'), $id)){
																$pasa = true;
															}
														}else{											
															$pasa = false;
															$factura = new Factura($this->conexion, $facturaenproceso[0]['id']);
															if($factura->getDato('id')){
																$pasa = true;
															}
														}
														if($pasa){
															
															
															$respuesta = $this->solicitarVolanteDePago($factura->getDato('id'));
															//aqui generamos el volante de pago...
															
															
															
															//$respuesta['estado'] = 'ok';
														}
													}
												}else{
													$respuesta['codigo'] = 'ya-esta-matriculado';
												}
											}else{
												$respuesta['codigo'] = 'cupon-caracteres-invalidos';
											}
										}	
									}else{ 
										$respuesta['codigo'] = 'usuario-invalido'; 										
									}
								}else{
									$respuesta['codigo'] = 'el-curso-ya-inicio-o-no-tiene-fecha-de-inicio';
								}
							}else{
								$respuesta['codigo'] = 'sin-precio';
							}
						}else{
							$respuesta['codigo'] = 'no-disponible';
						}
					}
				}
			}else{
				$respuesta['codigo'] = 'sin-sesion';
			}
			return $respuesta;
			
		}
		
		/*
			No es necesario para LevelUP, ya que no se borran facturas o intentos de pago.
			DELETE
			URL http://localhost/levelupamericana/info/carrito/6/1   en donde 6 es el idproducto a quitar y 1 es la cantidad a quitar
			Listo
		*/
		public function borrar($id, $cantidadquitar){	//Listo
			//echo 'Borrando '.$id;
			
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			return $respuesta;
			
		}
		/*			
			GET:
			URL http://localhost/levelupamericana/info/api/carrito/1  el numero no sirve para nada, solo para que se active el get
			Listo
			Retorna todas las facturas del usuario que tiene en progreso o pagadas, etc
		*/
		public function ver($id){	//viene GET y con solo un numero en segunda posicion, para retornar solo los datos de uno solo, si se requiere de otras listas ya ahi si se necesitan las funciones personalizadas.
			
			global $USER;
		
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
									
			if(isloggedin()){
				$usuario = new Usuario($this->conexion, $USER->id);
				$todaslasfacturas =  $usuario->getFacturas(array(2, 3, 4, 7));
				if(count($todaslasfacturas)>0){
					$tam = count($todaslasfacturas);
					for($i=0; $i<$tam; $i++){
						unset($todaslasfacturas[$i]['tituloultimarespuesta']);
						unset($todaslasfacturas[$i]['descripcionultimarespuesta']);
						unset($todaslasfacturas[$i]['respuestadato1']);
						unset($todaslasfacturas[$i]['respuestadato2']);
						unset($todaslasfacturas[$i]['respuestadato3']);
						unset($todaslasfacturas[$i]['respuestadato4']);
						/*$factura = new Factura($this->conexion, $todaslasfacturas[$i]['id']);
						$todaslasfacturas[$i]['estadocheckout'] = $factura->getEstadoCheckout();*/
					}
					$respuesta['datos'] = $todaslasfacturas;	//Se envia tambien las que ya pasaron al proceso de pago.
				}	
			}else{
				$respuesta['codigo'] = 'sin-sesion';
			}			
			return $respuesta;
		}
		
		/*
			GET			
			url: http://localhost/levelupamericana/api/carrito/solicitarvolantedepago/88   //donde 88 es un id factura de este usuario que se le desea generar volante de pago
			Esto se ejecuta cada vez que presiona "Comprar curso", para generar siempre la misma referencia en la pasarela de pagos.
		*/
		public function solicitarVolanteDePago($idfactura){
			
			global $USER;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isloggedin()){
				if(isset($idfactura) && $this->filtro->soloNumeros($idfactura) && $this->filtro->limiteTamano($idfactura, 1, 16)){
					$factura = new Factura($this->conexion, $idfactura);
					if($factura->getDato('idusuario')==$USER->id){	//la factura debe ser del usuario por obligación
						$estadosprevios = array(2);
						if(in_array($factura->getDato('estado'), $estadosprevios)){							
							$respuesta['codigo'] = '';								
							$respuesta = $this->enviarPasarelaDePagos($factura->getDato('id'), false);	//ojo cambiar esto a false cuando ya vaya a produccion.
							if($respuesta['estado']=='ok'){
								/*if(!$factura->setDato('estado', 7)){		//se pone en estado de espera de la pasarela de pagos por la confirmacion del pago
									$respuesta['estado'] = 'error';
									$respuesta['codigo'] = 'error-al-poner-estado-factura-en-7';
								}*/
							}							
						}else{
							$respuesta['codigo'] = 'no-se-puede-pagar-aun';
						}
					}
				}
			}else{
				$respuesta['codigo'] = 'sin-sesion';
			}
			return $respuesta;
		}	

		/*
			Retorna las credenciales de la pasarela de pagos ya sea que se esté en pruebas o no
		*/
		public function getCredenciales($test=false){
			$respuesta = array();
			$respuesta['urlconexion'] = 'https://checkout.payulatam.com/ppp-web-gateway-payu/';
			$respuesta['merchantid'] = 552139;
			$respuesta['accountid'] = 554457;
			$respuesta['apikey'] = 'eKAWYID9hBg3D5za3YJljPwXLN';
			$respuesta['apilogin'] = '';
			$respuesta['publickey'] = '';
			if($test){
				$respuesta['urlconexion'] = 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/';
				$respuesta['merchantid'] = 508029;
				$respuesta['accountid'] = 512321;
				$respuesta['apikey'] = '4Vj8eK4rloUd272L48hsrarnUA';
				$respuesta['apilogin'] = 'pRRXKOl8ikMmt9u';
				$respuesta['publickey'] = 'PKaC6H4cEDJD919n705L544kSU';
			}
			return $respuesta;
		}
						
		/*								
			SE conecta a la pasarela de pagos y genera una orden de pago en ella pasada en un idfactura que estuviera en modo carrito o revision o devuelta al cliente para su revision
			$test: true o false, si se estan haciendo pruebas o no.
		*/
		public function enviarPasarelaDePagos($idfactura, $test){
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if(isset($idfactura) && $this->filtro->soloNumeros($idfactura) && $this->filtro->limiteTamano($idfactura, 1, 16)){
				$factura = new Factura($this->conexion, $idfactura);
				$estadosprevios = array(2, 7);
				if(in_array($factura->getDato('estado'), $estadosprevios)){
					$usuario = new Usuario($this->conexion, $factura->getDato('idusuario'));
					if($usuario->getDato('id')){

						$curso = new Curso($this->conexion, $factura->getDato('idcurso'));
						
						
						//miramos si hay un cupon que aplicar para generar el nuevo total de la factura.
						$cuponactual = $this->sesion->getDatoSesion('cuponporcentaje');
						if($cuponactual!=''){
							$nuevoprecio = $curso->getDato('precio')-(($curso->getDato('precio')*$cuponactual)/100);
							$nuevoprecio = number_format($nuevoprecio, 0, '', '');  //va sin ningun simbolo como se guardan en la base de datos los precios, ya que se est´recalculando
							$this->sesion->setDatoSesion('cuponporcentaje', '');
							$factura->setDato('total', $nuevoprecio);
						}
						//fin de mirar si hay un cupon que aplicar.
						
						$mysql_date = date("Y-m-d");
						$mysql_date2 = date("YmdHis");
						
						$credenciales = $this->getCredenciales($test);
						$urlconexion = $credenciales['urlconexion'];
						
						//obtener los datos para pruebas en este link, incluye tarjeta de credito de pruebas: http://developers.payulatam.com/latam/es/docs/getting-started/test-your-solution.html										
						$merchantId = $credenciales['merchantid'];
						$accountId = $credenciales['accountid'];
						
						$apikey = $credenciales['apikey'];
						$apilogin = $credenciales['apilogin'];
						$publickey = $credenciales['publickey'];							
						
						$firma = $apikey.'~'.$merchantId.'~CEC-'.$factura->getDato('id').'-'.$usuario->getDato('id').'-'.$mysql_date2.'~'.$factura->getDato('total').'~COP';		//cambiar aqui el COP segun el pais
						$firma = md5($firma);							
						
						//creamos el mismo form pero en html
						$formulario = '<form id="formpasarela" method="post" action="'.$urlconexion.'">'; //OJO esta url debe ser cambiada por el action de produccion el cual es : https://gateway.payulatam.com/ppp-web-gateway
							$formulario.='<input name="merchantId"    type="hidden"  value="'.$merchantId.'"   >';
							$formulario.='<input name="accountId"     type="hidden"  value="'.$accountId.'" >';
							$formulario.='<input name="description"   type="hidden"  value="'.$curso->getDato('fullname').' Volante id: '.$factura->getDato('id').'"  >';
							$formulario.='<input name="referenceCode" type="hidden"  value="CEC-'.$factura->getDato('id').'-'.$usuario->getDato('id').'-'.$mysql_date2.'" >';
							$formulario.='<input name="amount"        type="hidden"  value="'.$factura->getDato('total').'"   >';
							$formulario.='<input name="tax"           type="hidden"  value="0"  >';
	
							$formulario.='<input name="taxReturnBase" type="hidden"  value="0" >';
							
							$formulario.='<input name="currency"      type="hidden"  value="COP" >';
							$formulario.='<input name="signature"     type="hidden"  value="'.$firma.'"  >';
							if($test){
								$formulario.='<input name="test"  	  type="hidden"  value="1" >';
							}else{
								$formulario.='<input name="test"      type="hidden"  value="0" >';
							}
							$formulario.='<input name="buyerEmail"    type="hidden"  value="'.$usuario->getDato('email').'" >';
							$formulario.='<input name="responseUrl"    type="hidden"  value="'.URLPROYECTO.'pasarelarespuesta.php" >';
							$formulario.='<input name="confirmationUrl"    type="hidden"  value="'.URLPROYECTO.'pasarelaconfirmacion.php" >';
							$formulario.='<input name="Submit"        type="submit"  value="Enviar" >';
						$formulario.='</form>';
						
						$respuesta['estado'] = 'ok';
						$respuesta['datos'] = $formulario;
							
						
					}
				}
			}	
			return $respuesta;
		}
										
		/*
			Recibe los datos que respuesta que llegan de la pasarela de pagos una vez la pasarela reedirige al usuario al sitio a pararelarespuesta.php
		*/
		public function pasarelaRespuesta($data=array(), $test=false){
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();			
			
			$valoresllegar = array('merchantId', 'referenceCode', 'TX_VALUE', 'currency', 'transactionState', 'signature', 'reference_pol', 'cus', 'description', 'pseBank', 'lapPaymentMethod', 'transactionId', 'polTransactionState', 'polResponseCode');
			foreach($valoresllegar as $va){
				if(!isset($data[$va])){
					$respuesta['datos'][] = $va;
				}				
			}
			if(count($respuesta['datos'])==0){
												
				$partes = explode('-', $data['referenceCode']);
				if(count($partes)==4){
					if(isset($partes[1]) && $this->filtro->soloNumeros($partes[1]) && $this->filtro->limiteTamano($partes[1], 1, 16)){
						$factura = new Factura($this->conexion, $partes[1]);
						if($factura->getDato('id')){
							
							$credenciales = $this->getCredenciales($test);  //el true es que si se está en pruebas
							$apikey = $credenciales['apikey'];
							$merchant_id = $data['merchantId'];
							$referenceCode = $data['referenceCode'];
							$TX_VALUE = $data['TX_VALUE'];
							$New_value = number_format($TX_VALUE, 1, '.', '');
							$currency = $data['currency'];
							$transactionState = $data['transactionState'];
							$firma_cadena = "$apikey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
							$firmacreada = md5($firma_cadena);
							$firma = $data['signature'];
							$reference_pol = $data['reference_pol'];
							$cus = $data['cus'];
							$extra1 = $data['description'];
							$pseBank = $data['pseBank'];
							$lapPaymentMethod = $data['lapPaymentMethod'];
							$transactionId = $data['transactionId'];
							
							if (strtoupper($firma) == strtoupper($firmacreada)){
								$factura->setDato('respuestadato1', json_encode($data));	//se guarda todo lo que llega en respuestadato1 por si acaso.																							
								switch($data['polTransactionState']){
									case 4:		//aprobada
										$factura->setDato('estado', 4);
										//$factura->generarConsecutivo();
										//$res = $factura->matricularEnCurso();   //estaba activado para levelup
									break;
									case 6:		//rechazada
										$factura->setDato('estado', 3);
									break;
									case 5:		//expirada
										$factura->setDato('estado', 3);
									break;
									case 7:		//pendiente
										$factura->setDato('estado', 7);
									break;
									case 14:		//pendiente
										$factura->setDato('estado', 7);
									break;
									case 15:		//pendiente
										$factura->setDato('estado', 7);
									break;
									case 10:		//pendiente
										$factura->setDato('estado', 7);
									break;
									case 12:		//pendiente
										$factura->setDato('estado', 7);
									break;
									case 18:		//pendiente
										$factura->setDato('estado', 7);
									break;
								}
								
								if($data['response_message_pol']=='APPROVED'){	//SE AGREGO POR CORRECCION.
									$factura->setDato('estado', 4);
								}
								
								$factura->setDato('respuestadato3', $data['response_message_pol']);		//Se almacena el codigo alfanumerico del error para más presicion.
								$respuesta['estado'] = 'ok';								
								$respuesta['datos'] = $factura->getDato('id');
							}else{
								$respuesta['codigo'] = 'error-en-firma-digital';
								$factura->setDato('respuestadato4', 'error-en-firma-digital_res');
								$respuesta['datos'] = $factura->getDato('id');
								$factura->setDato('respuestadato1', json_encode($data));  //cuando ya se haya probado lo suficiente ha de quitarse esta linea, ya que no es bueno almacenar la rafaga de datos obre todo si esta corrupta
							}
						}
					}	
				}				
			}else{
				$respuesta['codigo'] = 'faltan-datos';
			}
			return $respuesta;
		}
		
		/*
			Aqui llegan los datos de confirmación de si fue paga o no la factura por parte de la pasarela de pagos.
		*/
		public function pasarelaConfirmacion($data=array(), $test=false){
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();			
			
			$valoresllegar = array('transaction_id', 'reference_sale', 'merchant_id', 'value', 'currency', 'state_pol', 'sign', 'response_code_pol', 'response_message_pol');
			foreach($valoresllegar as $va){
				if(!isset($data[$va])){
					$respuesta['datos'][] = $va;
				}
			}
			if(count($respuesta['datos'])==0){
				$partes = explode('-', $data['reference_sale']);
				if(count($partes)==4){
					if(isset($partes[1]) && $this->filtro->soloNumeros($partes[1]) && $this->filtro->limiteTamano($partes[1], 1, 16)){
						$factura = new Factura($this->conexion, $partes[1]);
						if($factura->getDato('id')){
							$credenciales = $this->getCredenciales($test);  //el true es que si se está en pruebas
							
							$ApiKey = $credenciales['apikey'];
							$merchant_id = $data['merchant_id'];
							$reference_sale = $data['reference_sale'];						
							$currency = $data['currency'];
							$state_pol = $data['state_pol'];
							$sign = $data['sign'];
							$value = $data['value'];
							$value = str_replace(',', '.', $value);  //si acaso viene con una coma, se le coloca un punto, ya que es el que se usa aca, y el que se usa para procesar la firma segun la documentacion oficial.						
							$partes = explode('.', $data['value']);
							if(count($partes)==2){
								$tam = strlen($partes[1]);
								if($tam==2){
									$ultimocaracter = substr($partes[1], -1);
									if($ultimocaracter=='0'){
										$primercaracter = substr($partes[1], 0, 1);
										$value = $partes[0].'.'.$primercaracter;
									}
								}							
							}
							
							$firma_cadena =  "$ApiKey~$merchant_id~$reference_sale~$value~$currency~$state_pol";
							$firmacreada = md5($firma_cadena);	
								
							if (strtoupper($sign) == strtoupper($firmacreada)) {								
								$estadoactual = $factura->getDato('estado');
								$estadonuevo = -1;
								switch($data['state_pol']){
									case 4:		//aprobada
										$estadonuevo = 4;					//se coloca pagada
									break;
									case 6:		//declinada																			
										$estadonuevo = 3;					//se coloca en error de transaccion
									break;
									case 5:		//expirada										
										$estadonuevo = 3;				//se coloca en error de transaccion
									break;
								}
								if($data['response_message_pol']=='APPROVED'){	//SE AGREGO POR CORRECCION.
									$estadonuevo = 4;
								}
								if($estadonuevo!=-1){
									$factura->setDato('estado', $estadonuevo);
								}
								$factura->setDato('respuestadato1', json_encode($data));
								$factura->setDato('respuestadato3', $data['response_message_pol']);
								if($estadonuevo!=-1 && $estadonuevo!=$estadoactual){	//si se cambio el estado y era diferente al estado de factura que tenia antes
									switch($estadonuevo){
										case 4:											
											//$res = $factura->matricularEnCurso();  //estaba activado para levelup
										break;
										case 3:		//Se eliminan todas las posibles invocando una funcion para que lo haga
											//$res = $factura->desmatricularEnCurso();  //estaba activado para levelup
										break;										
									}									
								}
								$respuesta['estado'] = 'ok';																	
							}else{							
								$respuesta['codigo'] = 'error-en-firma-digital';
								$factura->setDato('respuestadato4', 'error-en-firma-digital_conf');
								$factura->setDato('respuestadato1', $firma_cadena.'&'.json_encode($data));  //cuando ya se haya probado lo suficiente ha de quitarse esta linea, ya que no es bueno almacenar la rafaga de datos obre todo si esta corrupta
							}
						}
					}
				}
			}
			return $respuesta;			
		}	
		
		/*
			Pruebas de matriculacion o desmatriculacion
		*/		
		public function matricularUsuarioX(){
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();		
			
			$usuario = new Usuario($this->conexion, 13);
			$res = $usuario->desmatriculaEnCurso(5);
			if($res=='ok'){
				$respuesta['estado'] = 'ok';
			}else{
				$respuesta['codigo'] = $res;
			}
			
			return $respuesta;
		}
		
	}
	
?>