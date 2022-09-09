<?php namespace controladores;
	
	use clases\Sesion as Sesion;	
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;	
	use clases\Curso as Curso;
	use clases\Sitio as Sitio;
	use \controladores\ControladorSitio as ControladorSitio;	
	
	class ControladorIndex{
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
			$respuesta['datos']['categorias'] = array();
			$respuesta['datos']['tienecursosnuevos'] = false;
			
			$controladorsitio = new ControladorSitio($this->conexion);
			$categoriasofertadas = $controladorsitio->getCursosOfertados();
			if(isset($categoriasofertadas['datos']['categorias'])){
				$respuesta['datos']['categorias'] = $categoriasofertadas['datos']['categorias'];
				
				for($i=0; $i<count($respuesta['datos']['categorias']); $i++){
					for($j=0; $j<count($respuesta['datos']['categorias'][$i]['cursos']); $j++){
						$curso = new Curso($this->conexion, $respuesta['datos']['categorias'][$i]['cursos'][$j]['id']);
						$respuesta['datos']['categorias'][$i]['cursos'][$j]['precio'] = number_format($curso->getDato('precio'), 0, ',', '.');
						$respuesta['datos']['categorias'][$i]['cursos'][$j]['descripcioncorta'] = $curso->getDato('descripcioncorta');	
						$respuesta['datos']['tienecursosnuevos'] = true;	
					}
				}
			}	
			
			//buscamos dos cursos destacados para mostrar en grande
			$sitio = new Sitio($this->conexion);
			$respuesta['datos']['cursosdestacados'] = array();
			$destacados = $sitio->getCursosOfertados(true);
			if($destacados['estado']=='ok'){
				$destacados = $destacados['datos'];
				shuffle($destacados);
				for($i=0; $i<2; $i++){
					if(isset($destacados[$i])){
						$curso = new Curso($this->conexion, $destacados[$i]['id']);
						$destacados[$i]['precio'] = number_format($curso->getDato('precio'), 0, ',', '.');
						$destacados[$i]['descripcioncorta'] = $curso->getDato('descripcioncorta');						
						$respuesta['datos']['cursosdestacados'][] = $destacados[$i];
					}
				}				
			}
			//fin de buscar solo cursos destacados.
			
			$respuesta['datos']['config'] = $sitio->getConfig();
			
			
			return $respuesta;
		}	
		
		public function crear(){
		
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
												
			
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
		
	}
	
?>