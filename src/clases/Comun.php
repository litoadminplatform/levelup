<?php namespace clases;
class Comun
{
	private $conexion=false;
	//funciones publicas que comparten varias clases.	
	/*
		$conexionset : aveces no es tan necesario estalbecer una conexion para poder usar esta clase por eso este
			parametro es opcional, solo establezcalo cuando sea necesario, de igual forma si no lo establecio 
			inicialmente podra hacerlo usando la funcion setConexion. (que aun no la he hecho jeje)
	*/	
	function __construct(&$conexionset=false){
		if($conexionset){
			$this->conexion=$conexionset;
		}
	}
	
	
	/*
		Convierte una cadena de texto en su url amigable
	*/
	public function generaUrlAmigable($data){
		$data = str_replace(' ', '-', $data); //los espacios se reemplazan por un gion		
		$data = str_replace (array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'), array('a','e','i','o','u','n', 'a','e','i','o','u','n'), $data); //se buscan los caracteres con tilde y se reemplazan por los mismos sin tilde.
		$data = mb_strtolower($data, 'UTF-8'); //se coloca todo en minuscula								
		$permitidos = "abcdefghijklmnnopqrstuvwxyz1234567890-"; //finalmente solo deben quedar las letras por que los esacios y las tildes ya fueron cambiadas		
		for ($i=0; $i<strlen($data); $i++){		  
			if (strpos($permitidos, substr($data,$i,1))===false){								
				$data = str_replace (substr($data,$i,1), '', $data); //los caracteres que no sirven se borran 
			}		    
		}		
		return $data;
	}
	
	/*retorna el numero de dias que hay entre dos fechas las fechas deben estar dadas por el formato AAAA-MM-DD
	   si la fecha final es anterior a la fechacomienzo retornara un número negativo.
	*/
	public function diasEntreDosFechas($fechacomienzo, $fechafinal){
		if($this->fechaValida($fechacomienzo) && $this->fechaValida($fechafinal)){ //nos dice que las dos fechas sean validas.
			$fechai = explode('-', $fechacomienzo);
			$fechaf = explode('-', $fechafinal);
			$timestamp1 = mktime(0,0,0,$fechai[1],$fechai[2],$fechai[0]); 
			$timestamp2 = mktime(0,0,0,$fechaf[1],$fechaf[2],$fechaf[0]);
			$segundos_diferencia = $timestamp2 - $timestamp1; 
			return $segundos_diferencia / (60 * 60 * 24); //estos son los dias de diferencia.
		}else{
			return false;
		}
	}
	
	/*$datetimeinicial : se supone que es la fecha mas antigua, ojo para usar esta funcion en el controlador se debio haber actuvado la misma zona horaria que cuando se genero la fecha inicial.
	los formatos de los date times son asi: 2013-04-11 00:34:19
	*/
	public function segundosEntreDosDateTime($datetimeinicial, $datetimefinal){		
		$segundos = strtotime($datetimefinal) - strtotime($datetimeinicial);
		return $segundos;
	}
		
	/*retorna el numero de dias entre dos una fehca y hoy
		$fecha debe estar formateada con 1985-06-10 (AAAA-MM-DD)
		NOTA: al ejecutar esta funcion debe activar el time zone del pais o ciudad en cuestion(si es necesario).
		NOTA: retorna el numero en negativo cuando la fecha que le pasas es superior al dia de hoy
	*/
	public function diasEntreFechaYhoy($fecha){ 
		$timeStamphoy = mktime(0,0,0,date("m"),date("d"),date("Y")); /*hoy*/
		list($ano, $mes, $dia) = explode('-', $fecha); //(AAAA-MM-DD)
		$timeStampGuardado = mktime(0, 0, 0, $mes, $dia, $ano);
		$segundosquepasaron = $timeStamphoy - $timeStampGuardado;
		$dias_diferencia = $segundosquepasaron / (60 * 60 * 24);		
		//$dias_diferencia = abs($dias_diferencia); //esta vaina hace que no aparezca el negativo.
		$dias_diferencia = floor($dias_diferencia); 
		return $dias_diferencia;
	}
	
	/*
		retorna el numero de segundos que han pasado desde un datetime ('2012-01-09 20:09:46') hasta este
		momento. util para permitir realizar acciones solo durante un lapso de tiempo.
		esta funcion no verifica que el datetime sea correcto por lo tanto valide este echo antes.
	*/
	public function segundosDesdeDatetime($datetime){		
		$segundosahora = mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y'));
		list($fecha, $hora) = explode(' ',$datetime);
		list($ano, $mes, $dia) = explode('-',$fecha);
		list($hora, $minuto, $segundo) = explode(':',$hora);		
		$segundosdatetime = mktime($hora, $minuto, $segundo, $mes, $dia, $ano);		
		return $segundosahora-$segundosdatetime;
	}
	
	/*retorna true si la fecha es valida, la fecha debe venir en formato AAAA-MM-DD
	  retorna false si la fecha no es válida.
	*/
	public function fechaValida($fecha){		
		$pedazos = explode('-', $fecha);
		if(count($pedazos)==3){
			return checkdate($pedazos[1], $pedazos[2], $pedazos[0]);				
		}else{
			return false;
		}	
	}
	
	public function validarDateTime($datetime, $strict=true){
		$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
		if ($strict) {
			$errors = DateTime::getLastErrors();
			if (!empty($errors['warning_count'])) {
				return false;
			}
		}
		return $dateTime !== false;
	}
	
	/*retorna la fecha apartir del numero de dias de la fecha actual por ej si es 1ro de enero y $dias es 2  retornara 3 de enero, todo eso en el formato de las fechas de mysql.
	fecha esta dada por ano-mes-dia como en la base de datos*/
	public function operacion_fecha($fecha, $dias){
		list ($ano,$mes,$dia)=explode("-",$fecha);  
		if (!checkdate($mes,$dia,$ano)){return false;}  
		$dia=$dia+$dias;  
		$fecha=date("Y-m-d", mktime(0,0,0,$mes,$dia,$ano) );  
		return $fecha;  
	}
	
	/*
		convierte un datetime guardado en la base de datos mysql a español
		retorna el string en español de ese datetime mysql
	*/
	public function dateTimeEspanol($datetime, $mescorto=false){					
		$timestamp = strtotime($datetime);		
		switch($lang){
			case 'es':
				//$dias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sábado');
				$de = 'de';
				$meses = array('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
				if($mescorto){
					$meses = array('','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic');
				}
			break;
		}				
		return $meses[date("n", $timestamp)].' '.date("j", $timestamp).' '.$de.' '.date("Y", $timestamp);
	}
	
	/*
		convierte una fecha en formato tiemestamp a formato unix
	*/
	public function convierteTimestampAUnix($fech){			
		$fechunix= strtotime($fech); 	
		return $fechunix;
	}
	
	/*
		$fechunix: es una fecha en formato unix 1263877200
		retorna una fecha en formato 2010-01-19 05:00:00
	*/
	public function convierteUnixATimestamp($fechunix){				
		//$fechnormal= gmdate("Y-m-d\ H:i:s", $fechunix);  //original
		$timezone  = -5;
		$fechnormal=gmdate("Y-m-d\ H:i:s", $fechunix + 3600*($timezone)); 
		return $fechnormal;
	}	
	
	/*
		Convierte una cantidad de segundos a que cantidad de dias horas minutos y segundos es.
	*/
	function segundosATiempo($segundos, $lang){
		$retornar = '';	
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$segundos");		
		switch($lang){
			case 'es':
				$retornar = $dtF->diff($dtT)->format('%a días, %h horas, %i minutos and %s segundos');
			break;
			case 'en':
				$retornar = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
			break;
		}
		return $retornar;
	}
	
	
	/*
		te dice si las cadenas que le pases se encuentran en el stringç
		$textoendondebuscar: el texto donde se realizara la busqueda
		$stringsabuscar: un array de strings los cuales se desean buscar.
	*/
	public function estaEnString($textoendondebuscar, $stringsabuscar = array()){
		$retornar = false;
		$tam = count($stringsabuscar);
		for($i=0; $i<$tam; $i++){
			if(strpos($textoendondebuscar, $stringsabuscar[$i])!==false){
				$retornar = true;				
			}			
		}				
		return $retornar;		
	}
	
	/*
		Retorna los unixtime o los timestamp en los que inicia y finaliza un periodo especifico
		$modo: por defecto es 'unix' , pero si envia 'timestamp' se retora el timestamp y no las fechas en unix
		Convierte un periodo de curso de coruniamericana a los unix respectivos de cuando empieza hasta cuando termina el periido ej: 20162 retornaria un array de dos posiciones con el unix respectivo para 1 julio de 2016 00:00:00 hasta 31 de diciembre de 2016 11:59:59
		20162 => 1673435345 y 1562342342
	*/
	public function convertirPeriodoAUnix($periodo, $modo='unix'){
		$retornar = array();
		$retornar['inicio'] = 0;
		$retornar['fin'] = 0;
		$ano = substr($periodo, 0, 4);
		$semestre = substr($periodo, 4, 1);
		
		$fechainicio = $ano.'-01-01 00:00:00';
		$fechafin = $ano.'-06-30 23:59:59';
		if($semestre=='2'){
			$fechainicio = $ano.'-07-01 00:00:00';
			$fechafin = $ano.'-12-31 23:59:59';
		}		
		switch($modo){
			case 'unix':
				$retornar['inicio'] = $this->convierteTimestampAUnix($fechainicio);
				$retornar['fin'] = $this->convierteTimestampAUnix($fechafin);
			break;
			case 'timestamp':
				$retornar['inicio'] = $this->dateTimeEspanol($fechainicio, true);
				$retornar['fin'] = $this->dateTimeEspanol($fechafin, true);
			break;
		}		
		return $retornar;
	}
	
	/*
		Retorna cada una de las partes del nombrecorto(shorname)  solo si cumple con el estandar de los nombres cortos
		retorna las partes si el nombre corto es correcto.
		retora array vacio (false) si el nombre corto es incorrecto.
	*/
	public function getPartesShortname($shortname){
		$retornar = array();
		if($this->conexion){
			include_once('Filtro.class.php');
			$filtro = new Filtro();		
			//if($filtro->validaNombreCortoCursoLongPort($this->conexion, $shortname)){
			$partes = explode('-', $shortname);
			if(count($partes)==4){
				$retornar['curso'] = $partes[0];
				$retornar['tipo'] = $partes[1];		//I, R, O, B, U:: Inicial, Recurrente, Opcional. Compra(B), Actualizacion(U)
				$retornar['modo'] = $partes[2];  	//V o P: virtual o presencial, 
				$retornar['periodo'] = $partes[3];
				$retornar['idioma'] = $partes[4];						
			}
			//}
		}
		return $retornar;
	}
	
	/*
		Retorna en un array de arrays los textos de cada una de las partes de un shortname, similar a getPartesShortname pero lo que retorna serian los textos en el idioma en cuestion.
		ADVERTENCIA: el shortname ya debioi haber sido validado como un shortname de coruniamericana
	*/
	public function getPartesShornameTextos($tipo, $modo, $periodo, $idioma){
		$retornar = array();
		$retornar['tipo'] = '';
		$retornar['modo'] = '';
		$retornar['periodo'] = '';
		$retornar['idioma'] = '';
		if($this->conexion){
			include_once('Sesion.class.php');
			$sesion = new Sesion($this->conexion);
			$lang = 'es';  //lenguaje predeterminado
			$langpermitidos = array('es', 'en');   //carpetas que tienen lenguaje para esta funcion  es/interfaz.php  en/interfaz.php
			if(in_array($sesion->getLenguajeActual(), $langpermitidos)){
				$lang = $sesion->getLenguajeActual();				
			}				
			include_once('../lang/'.$lang.'/completions.php');
			
			//Periodo					
			$partesfechas = $this->convertirPeriodoAUnix($periodo, 'timestamp');
			$retornar['periodo'] = explode('-', $partesfechas['inicio'])[0];
			$retornar['periodo'].= ' '.txt_a.' '.$partesfechas['fin'];																								
			
			//Tipo
			$retornar['tipo'] = '';
			switch($tipo){
				case 'I':
					$retornar['tipo'] = txt_inicial;
				break;
				case 'R':
					$retornar['tipo'] = txt_recurrente;
				break;
				case 'O':
					$retornar['tipo'] = txt_opcional;
				break;
				case 'B':
					$retornar['tipo'] = txt_compra;
				break;
				case 'U':
					$retornar['tipo'] = txt_actualizacion;
				break;
			}
			
			//Idioma	
			$retornar['idioma'] = '';
			switch($idioma){
				case 'ESP':
					$retornar['idioma'] = txt_espanol;
				break;
				case 'ENG':
					$retornar['idioma'] = txt_ingles;
				break;
				case 'FR':
					$retornar['idioma'] = txt_frances;
				break;													
			}												
						
			$retornar['modo'] = '';
			switch($modo){
				case 'V':
					$retornar['modo'] = txt_virtual;
				break;
				case 'P':
					$retornar['modo'] = txt_presencial;
				break;													
			}
		}	
		return $retornar;
	}
	
	/*
		Separa un mombre completo dados los apellidos primero y luego el nombre, y retorna en un array de array los dos nombres y los dos apellidos
		$nombrecompleto : es el nombre completo dados los apellidos primero.
	*/
	function divideNombres($nombrecompleto){				
		//MARTINEZ DE LA HOZ MELISSA
		//DE LA HOZ DE LA HOZ LUZ ESTHELA
		//$nombre = 'DE LA ROSA SABALZA ANGIE MELISSA';
		$nombre = $nombrecompleto;
		
		$partes = explode(' ', $nombre);
		$tam = count($partes);
		
		$validado = false;
		$apellido = '';
		$apellido2 = '';
		$nombre = '';	
		$nombre2 = '';		
		$username = '';
				
		if($tam==3 || $tam==4){
			$apellido = $partes[0];
			$apellido2 = $partes[1];
			$nombre = $partes[2];	
			if(isset($partes[2])){
				$nombre = $partes[2];
			}
			if(isset($partes[3])){
				$nombre2 = $partes[3];
			}			
		}else{
			if($tam<3){
				$apellido = $partes[0];
				if(isset($partes[1])){
					$nombre = $partes[1];
				}
			}else{  //mayor de 4 necesariamente. aqui es donde estan los del carmen, de avila
				//$palabras = array('de', 'del', 'la', 'el', );
				$grandeapellido = false;
				$grandeapellido2 = false;
				$grandenombre = false;
				$grandenombre2 = false;
				for($i=0; $i<$tam; $i++){
					if($i<=2){ //apellido,   de la rosa peñate mario steven   de la rosa de la rosa mario steven						
						if(!$grandeapellido){  //si no se ha conseguido el apellido grande.
							$tamcadena = strlen($partes[$i]);
							if($tamcadena>=1 && $tamcadena<=3){   //hay; de, la, del, etc	
								if($apellido!=''){ $apellido.=' '; }
								$apellido.=$partes[$i];
							}else{
								if(!$grandeapellido){
									$grandeapellido = true;
									if($apellido!=''){ $apellido.=' '; }
									$apellido.=$partes[$i];
								}else{ 	//hace parte del segundo apellido
									if($apellido2!=''){ $apellido2.=' '; }
									$apellido2.=$partes[$i]; 
									$grandeapellido2 = true;  //se consiguo el segundo apellido enseguida
								}									
							}	
						}else{ 
							if(!$grandeapellido2){
								$tamcadena = strlen ($partes[$i]);																
								if($apellido2!=''){ $apellido2.=' '; }
								$apellido2.=$partes[$i]; 
								
								if($tamcadena>3){
									$grandeapellido2 = true;
								}
								
								
							}else{
								if(!$nombre){   //se agrega a nombre si tiene mas de 3 caracteres, de lo contrario sigue siendo apellido 2
									$tamcadena = strlen ($partes[$i]);
									if($tamcadena>3){ 
										if($nombre!=''){ $nombre.=' '; }
										$nombre.=$partes[$i];
										$grandenombre = true;
									}else{
										if($apellido2!=''){ $apellido2.=' '; }
										$apellido2.=$partes[$i]; 
										//$grandeapellido2 = false;  //si fue pequeño, de desactiva esto, quizas mas adelante aparece el gruedo del apellido 2
									}	
								}								
							}
							
						}
					}else{  //ahora a definir el segundo apellido y el nombre con lo que queda.
						if($i>=3 && $i<=5){   						
							//echo 'colocando'.$partes[$i].'<br>';
							if(!$grandeapellido2){																						
								$tamcadena = strlen ($partes[$i]);
								if($tamcadena>=1 && $tamcadena<=3){   //hay; de, la, del, etc		
									if($apellido2!=''){ $apellido2.=' '; }
									$apellido2.=$partes[$i];
								}else{
									if(!$grandeapellido2){
										$grandeapellido2 = true;
										if($apellido2!=''){ $apellido2.=' '; }
										$apellido2.=$partes[$i];
									}else{ //hace parte del nombre
										if($nombre!=''){ $nombre.=' '; }
										$nombre.=$partes[$i];
										$grandenombre = true;  //se consigio el nombre
									}																			
								}
							}else{
								if(!$grandenombre){ 
									$tamcadena = strlen ($partes[$i]);
									if($tamcadena>3){
										if($nombre!=''){ $nombre.=' '; }
										$nombre.=$partes[$i];
										$grandenombre = true;
									}else{
										if($apellido2!=''){ $apellido2.=' '; }
										$apellido2.=$partes[$i];
									}
								}else{
									if(!$grandenombre2){
										$tamcadena = strlen ($partes[$i]);
										if($nombre2!=''){ $nombre2.=' '; }
										$nombre2.=$partes[$i];
										if($tamcadena>3){											
											$grandenombre2 = true;
										}/*else{
											$nombre2.=' ';
										}*/
									}
								}
							}
						}else{  //tratamos de encontrar el primer nombre y el segundo nombre.
							if(!$grandenombre){
								if($nombre!=''){ $nombre.=' '; }
								$nombre.=$partes[$i];
								$grandenombre = true;
							}else{
								if(!$grandenombre2){  
									if($nombre2!=''){ $nombre2.=' '; }
									$nombre2.=$partes[$i];
									$grandenombre2 = true;
								}
							}				
						}
					}										
				}			
			}
		}
		$nombre = trim($nombre);
		$nombre2 = trim($nombre2);
		$apellido = trim($apellido);
		$apellido2 = trim($apellido2);							
		return array('nombre'=>$nombre, 'segundonombre'=>$nombre2, 'apellido'=>$apellido, 'segundoapellido'=>$apellido2);
	}
	
}	