<?php namespace clases;
class Conexion
{	
	private $linkbd = false;
	private $localx = false;
		
	function __construct(){
		
		//definimos el protocolo del sitio.
		$protocol = 'http://';
		if(isset($_SERVER['HTTPS'])){
		   $protocol = 'https://';
		}
		define('PROTOCOLO', $protocol);
				
		//definimnos las variables globales sobre rutas, dependiendo del sistema operativo.
		
		if(strpos(strtoupper(php_uname('s')), 'LINUX')!==false){
			//LINUX
			//define('RUTASERVIDOR', '/var/www/html/cec/moodle');
			//define('CARPETAPROYECTO', '');
			//define('URLBASE', PROTOCOLO.'cec.americana.edu.co');
		}else{
			//WINDOWS
			//define('RUTASERVIDOR', 'C:\servidor\levelupamericana');
			//define('CARPETAPROYECTO', '/levelupamericana');
			//define('URLBASE', PROTOCOLO.'localhost/levelupamericana/');
			$this->localx = true;
		}
	}
	
	public function conectar(){
		global $CFG;
		//if(!$this->localx){			
			//LINUX
			$this->linkbd = pg_connect("host=".$CFG->dbhost." port=".$CFG->dboptions['dbport']." dbname=".$CFG->dbname." user=".$CFG->dbuser." password=".$CFG->dbpass);
		//}else{
			//WINDOWS
		//	$this->linkbd = pg_connect("host=localhost port=5433 dbname=levelupamericana user=postgres password=1234");
		//}
						
		if($this->linkbd){
			return $this->linkbd;			
		}else{
			echo 'No fue posible conectar a la base de datos';			
		}													
	}
	public function consultar($sql){
		if(!$this->linkbd){
			$this->conectar();
		}
		if($this->linkbd){	
			$result = pg_query($this->linkbd, $sql);
			return $result;
		}else{
			echo 'No hay conexión a la base de datos x';			
		}
	}
	
	public function actualizar($sql){
		if(!$this->linkbd){
			$this->conectar();
		}
		if($this->linkbd){
			if($result_buscar = pg_query($this->linkbd, $sql)){
				return true;
            }else{
				return false;
			}			
		}else{		
			return false;
		}
	}
	
	/*retorna el true si se ejecuto, de lo contrario false en caso de ERROR*/
	public function insertar($sql){
		if(!$this->linkbd){
			$this->conectar();
		}
		if($this->linkbd){
			$result_buscar = pg_query($this->linkbd, $sql);
			if($result_buscar){
               return true;
            }else{
			   return false; 
			}
		}else{			
			return false;
		}
	}
	
	public function getLastError(){
		return pg_last_error($this->linkbd);
	}
	
	public function getLastId($tabla, $campo){
		$retornar = 0;
		$sql = 'SELECT MAX('.$campo.') AS id FROM '.$tabla.'';
		$result_buscar = $this->consultar($sql);
		if($row_buscar = pg_fetch_array($result_buscar)){
			$retornar = $row_buscar['id'];
		}	
		return $retornar;	
	}
	
	public function normaliza($string){
		$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);
		$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);
		$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);
		$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);
		$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);
		$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C',),
			$string
		);
		return $string;
	}
}

?>
