<?php namespace clases;
	class Request{
		private $api = false;
		private $controlador;
		private $metodo;
		private $argumento = array();

		public function __construct(){
			if(isset($_GET['url'])){
				$ruta = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
				$ruta = explode('/', $ruta);
				//$ruta = array_filter($ruta);	//se comentó por que eliminada los 0 del array y no queremos eso por que aveces se usan los 0.
				if($ruta[0]=='index.php'){
					$this->controlador = 'index';
					//array_shift($ruta); //creeera que esto debe ir aqui por que tiene que quitar uno
				}else{
					$this->controlador = strtolower(array_shift($ruta));
				}

				if($this->controlador=='api'){		//ESTO NO ESTABA ORIGINALMENTE, si resulta que es la api, se marca como que es una consulta a una api el controlador será el siguiente parametro de la url
					$this->api = true;
					$this->controlador = strtolower(array_shift($ruta));
				}

				$this->metodo = strtolower(array_shift($ruta));		//originalmente era asi, colocaba todo el minuscula del metodo pero no nos sirve en algunas ocasiones

				if(!$this->metodo && $this->metodo!='0'){
					$this->metodo = 'index';
				}
				$this->argumento = $ruta;

				//agregado adicional para ver si quiere obtener, editar borrar o actualizar
				if(is_numeric($this->metodo)){
					switch(strtoupper($_SERVER['REQUEST_METHOD'])){
						case 'GET':
							$temp = $this->argumento;
							$this->argumento = array($this->metodo);
							foreach($temp as $te){
								array_push($this->argumento, $te);
							}
							$this->metodo = 'ver';
							/*if(!$this->api){		//si no es la api, lo que se quiere es visualizar, asi que se cambia para index
								$this->metodo = 'ver';
							}*/
						break;
						case 'POST':
							$this->argumento = array($this->metodo);
							$this->metodo = 'crear';
						break;
						case 'PUT':
							/* 	original

							$this->argumento = array($this->metodo);
							$this->metodo = 'editar';

							*/
							$temp = $this->argumento;
							$this->argumento = array($this->metodo);
							foreach($temp as $te){
								array_push($this->argumento, $te);
							}
							$this->metodo = 'editar';
						break;
						case 'DELETE':
							$temp = $this->argumento;
							$this->argumento = array($this->metodo);
							foreach($temp as $te){
								array_push($this->argumento, $te);
							}
							$this->metodo = 'borrar';
						break;
						default:		//por aqui entra todos los demas y se convierten en un get
							$this->argumento = array($this->metodo);
							$this->metodo = 'ver';
						break;
					}
				}else{
					if(!$this->argumento && strtoupper($_SERVER['REQUEST_METHOD'])=='POST'){
						$this->metodo = 'crear';
					}
				}
				//fin de agregado adicional


			}else{
				$this->controlador = 'index';
				$this->metodo = 'index';
			}
		}

		public function getDato($valor){
			$aceptados = array('api', 'controlador', 'metodo', 'argumento');
			if(in_array($valor, $aceptados)){
				return $this->$valor;
			}else{
				return '';
			}
		}
	}
?>