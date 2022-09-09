<?php namespace controladores;
	
	use clases\Sesion as Sesion;	
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;	
	use clases\CuponDescuento as CuponDescuento;
	use clases\Usuario as Usuario;
	use clases\Sitio as Sitio;
	
	class ControladorCupon{
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
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			if($this->perfilCrearEditarCupones() || true){
				$respuesta = $this->getTodos();
				
				$sitio = new Sitio($this->conexion);
				$respuesta['datos']['config'] = $sitio->getConfig();
				
			}else{
				exit;
			}
			return $respuesta;
		}	
		
		public function crear(){
			
			global $USER;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
																					
			if($this->perfilCrearEditarCupones()){
								
				if(isset($_POST['codigo']) && $this->filtro->soloAlias($_POST['codigo']) && $this->filtro->limiteTamano($_POST['codigo'], 1, 32)){										
				}else{
					$respuesta['datos'][] = 'codigo';
				}
				
				if(isset($_POST['porcentajedescuento']) && $this->filtro->soloNumeros($_POST['porcentajedescuento']) && $this->filtro->limiteTamano($_POST['porcentajedescuento'], 1, 2)){										
				}else{
					$respuesta['datos'][] = 'porcentajedescuento';
				}
				
				if(isset($_POST['fechahoravencimiento']) && $this->filtro->validaFechaHora($_POST['fechahoravencimiento']) && $this->filtro->limiteTamano($_POST['fechahoravencimiento'], 19, 19)){										
				}else{
					$respuesta['datos'][] = 'fechahoravencimiento';
				}
				
				if(count($respuesta['datos'])==0){
					
					$cupon = new CuponDescuento($this->conexion);
					if($cupon->setCuponDescuento(mb_strtoupper($_POST['codigo'], 'UTF-8'), $_POST['porcentajedescuento'], $_POST['fechahoravencimiento'])){
						
						$respuesta['estado'] = 'ok';
						
						
					}else{
						$respuesta['codigo'] = 'error-al-crear';
					}					
				}else{					
					$respuesta['codigo'] = 'error-en-formulario';
				}
				
				
			}else{
				exit;
			}
			
			return $respuesta;
		}
		
		/*
			PUT
			URL: http://localhost/levelupamericana/info/api/cupon/99  (Donde 99 es el id cupon descuento)
			RAW: {
					"codigo": "aaaa",
					"porcentajedescuento": "99",
					"fechahoravencimiento": "2022-07-21 09:33:00",
					"estado": "0"
				}
		*/
		public function editar($id){	//viene PUT y con solo un numero en segunda posicion
			//echo 'Editando '.$id.' con data: ';
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			$json = file_get_contents('php://input');			
			$data = json_decode($json,true);
				
			if($this->perfilCrearEditarCupones()){
				
				if(isset($data['codigo']) && $this->filtro->soloAlias($data['codigo']) && $this->filtro->limiteTamano($data['codigo'], 1, 32)){										
				}else{
					$respuesta['datos'][] = 'codigo'.$data['codigo'];
				}
				
				if(isset($data['porcentajedescuento']) && $this->filtro->soloNumeros($data['porcentajedescuento']) && $this->filtro->limiteTamano($data['porcentajedescuento'], 1, 2)){										
				}else{
					$respuesta['datos'][] = 'porcentajedescuento';
				}
				
				if(isset($data['fechahoravencimiento']) && $this->filtro->validaFechaHora($data['fechahoravencimiento']) && $this->filtro->limiteTamano($data['fechahoravencimiento'], 19, 19)){										
				}else{
					$respuesta['datos'][] = 'fechahoravencimiento';
				}
				
				if(isset($data['estado']) && $this->filtro->soloNumeros($data['estado']) && $this->filtro->limiteTamano($data['estado'], 1, 1) && ($data['estado']=='1' || $data['estado']=='0')){						
				}else{
					$respuesta['datos'][] = 'estado';
				}
												
				if(count($respuesta['datos'])==0){				
				
					if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
						
						$cupon = new CuponDescuento($this->conexion, $id);
						if($cupon->getDato('id')){
							
							$cupon->setDato('codigo', mb_strtoupper($data['codigo'], 'UTF-8'));
							$cupon->setDato('porcentajedescuento', $data['porcentajedescuento']);
							$cupon->setDato('fechahoravencimiento', $data['fechahoravencimiento']);
							$cupon->setDato('estado', $data['estado']);

							$respuesta['estado'] = 'ok';
														
						}	
					}
				}else{
					$respuesta['codigo'] = 'error-en-formulario';
				}
			}
			
			return $respuesta;
			
		}
		public function borrar($id){	//viene DELETE y con solo un numero en segunda posicion
			//echo 'Borrando '.$id;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if($this->perfilCrearEditarCupones()){
				if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
					$cupon = new CuponDescuento($this->conexion, $id);
					if($cupon->getDato('id')){
						if($cupon->borrar()){							
							$respuesta['estado'] = 'ok';
						}
					}
				}
			}
			
			return $respuesta;
			
		}
		
		/*
			GET, USARLO SOLO COMO API YA QUE SE EDITARÁ EN UN POP UP
		*/
		public function ver($id){	//viene GET y con solo un numero en segunda posicion, para retornar solo los datos de uno solo, si se requiere de otras listas ya ahi si se necesitan las funciones personalizadas.
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			if($this->perfilCrearEditarCupones()){
				if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
					$cupon = new CuponDescuento($this->conexion, $id);
					if($cupon->getDato('id')){
						
						$respuesta['datos']['codigo'] = $cupon->getDato('codigo');
						$respuesta['datos']['porcentajedescuento'] = $cupon->getDato('porcentajedescuento');
						$respuesta['datos']['fechahoravencimiento'] = $cupon->getDato('fechahoravencimiento');
						$respuesta['datos']['estado'] = $cupon->getDato('estado');
						$respuesta['estado'] = 'ok';
					}
				}
			}	
			
			return $respuesta;
		}
		
		/*
			GET, USARLO SOLO COMO API YA QUE SE EDITARÁ EN UN POP UP
		*/
		public function getTodos(){	//viene GET y con solo un numero en segunda posicion, para retornar solo los datos de uno solo, si se requiere de otras listas ya ahi si se necesitan las funciones personalizadas.
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			if($this->perfilCrearEditarCupones()){				
				$cupon = new CuponDescuento($this->conexion);					
				$respuesta['datos'] = $cupon->getTodos();					
				$respuesta['estado'] = 'ok';									
			}else{
				exit;
			}	
			
			return $respuesta;
		}
		
		public function getCuponPorCodigo($codigo){
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			return $retornar;
		}
		
		private function perfilCrearEditarCupones(){	
			
			global $USER;
			
			$retornar  = false;
			if(isloggedin()){
				$usuario = new Usuario($this->conexion, $USER->id);
				if($usuario->getDato('id')){					
					$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);					
					if(count($permisoeditarcurso)>0){
						$retornar  = true;
					}
				}
			}			
			
			return $retornar;			
		}
		
	}
	
?>