<?php namespace clases;
	
	class Enrutador{
		public static function run(Request $request){
			$conexion = new Conexion();
			$sesion = new Sesion($conexion, $USER->id);
			$controlador = 'Controlador'.ucfirst($request->getDato('controlador'));
			$ruta = ROOT.DS.'src'.DS.'controladores'.DS.$controlador.'.php';						
			
			$metodo = $request->getDato('metodo');
			if($metodo == 'index.php'){
				$metodo = 'index';
			}
			$argumento = $request->getDato('argumento');
			$datos = '';
			if(is_readable($ruta)){
				require_once($ruta);
				$mostrar = 'controladores\\'.$controlador;				
				$controlador = new $mostrar($conexion);
										
				if(!isset($argumento)){
					$datos = call_user_func(array($controlador, $metodo));
				}else{
					try {
						$datos = call_user_func_array(array($controlador, $metodo), $argumento);
					}catch (\ArgumentCountError $th) {
						if(PRUEBAS){
							echo $th->getMessage(); 	//generalmente faltan parametros a la funcion.
						}else{
							//header('Location: '.URLBASE.'error/e404');
						}	
					}
				}				
			}else{
				if(PRUEBAS){
					trigger_error('Error personalizado: Controlador '.$controlador.'.php no existe', E_USER_NOTICE);	echo 'url:'.$_GET['url'].' | ';
				}else{
					header('Location: '.URLBASE.'error/e404');
				}
			}
			
			if($request->getDato('api')){ //si es la api imprime los datos
				if($request->getDato('controlador')!='index'){
					echo json_encode($datos);
				}
			}else{		//si no es una api, está intentando cargar una vista, mostramos la vista que está intentando cargar
				//$ruta = ROOT . 'vistas' . DS . $request->getDato('controlador') . DS . $request->getDato('metodo') . '.php';
				
				$ruta = ROOT . 'src'. DS. 'vistas' . DS . $request->getDato('controlador');  //otra opcion puede ser esta..
				if($request->getDato('metodo')!=''){
					$ruta.='_'.$request->getDato('metodo');
				}
				$ruta.='.php';
				
				//echo 'Cargar vista: '.$ruta;
				if(is_readable($ruta)){		//si no existe seguramente es por que se trata de una funcion de un controlador que solo retorna datos.			
					require_once $ruta;
				}else{
					//http_response_code(404);
					if(PRUEBAS){
						echo ' NO SE ENCONTRÓ '.$ruta;
					}else{
						header('Location: '.URLBASE.'error/e404');
					}
				}
			}
			
		}
	}	
?>