<?php namespace clases;

//ADVERTENCIA clase utiliza Libfilter.class.php

class Filtro
{		
	//falta: evitar que alguien escriba $_SESSION  o $_SERVER   $_GET  $_POST  $_COOKIE	
	//htmlspecialcharts()  para mostrar, y que no tengan efectos los html que logren pasarse.
	
    function __construct(){
		
    }
	
	/*somete a un texto a pasar por los principales niveles de validación con la intencion de dejarlo obsolutamente limpio: 
		1. validacion utf8
		2. quita caracteres de control
		3. quita htmls (recordemos que aki pueden venir en dec o hexas, es mejor pasarla por aquí primero)
		retorna true si la data es valida (es UTF8) y se pudo procesar, retorna false si no lo es (no es UTF8)
	*/
	public function setData(&$data){		
		if(!$this->Utf8Seguro($data)){
			return false;	
		}else{
			$data = $this->sinCaracteresDeControl($data);
			$this->filtroHtml($data);	//aplica el libFilter			
			return true;
		}
	}
				
	/*se aseugra de que la data que provenga sea utf8.
	 retorna true si es válida, retorna false si no lo es
	 nivel:1 data valida cuando llegue aqui
	*/
	private function Utf8Seguro(&$input){		
		if(!$this->esUtf8Valido($input)){			
			$input = utf8_encode($input);  //la data es sospechosa no continua
			//return false;  //originalmente estaba esto.
			return true;  //est no.
		}else{						
			return true;
		}		
	}
	
	/*ejecuta independientemente el filtrado de html libfilter
		no retorna datos, por que data viene por referencia así que es modificado directamente.
		retorna true en señal de que la funcion se ejecuto hasta el final correctamente.		
	*/
	public function filtroHtml(&$data, $permitidos=array()){
		include_once('Libfilter.class.php');
		$libfilter = new Libfilter($permitidos);
		$data = $libfilter->go($data);
		return true;
	}
	
	/*retorna true o false si el texto de referencia que le pasas es utf8 o no building scalable web sites pagina 101
	nivel:1 data valida
	*/
	private function esUtf8Valido(&$input){
		$rx = '[\xC0-\xDF]([^\x80-\xBF]|$)';
		$rx .= '|[\xE0-\xEF].{0,1}([^\x80-\xBF]|$)';
		$rx .= '|[\xF0-\xF7].{0,2}([^\x80-\xBF]|$)';
		$rx .= '|[\xF8-\xFB].{0,3}([^\x80-\xBF]|$)';
		$rx .= '|[\xFC-\xFD].{0,4}([^\x80-\xBF]|$)';
		$rx .= '|[\xFE-\xFE].{0,5}([^\x80-\xBF]|$)';		
		$rx .= '|[\x00-\x7F][\x80-\xBF]';
		$rx .= '|[\xC0-\xDF].[\x80-\xBF]';
		$rx .= '|[\xE0-\xEF]..[\x80-\xBF]';
		$rx .= '|[\xF0-\xF7]...[\x80-\xBF]';
		$rx .= '|[\xF8-\xFB]....[\x80-\xBF]';
		$rx .= '|[\xFC-\xFD].....[\x80-\xBF]';
		$rx .= '|[\xFE-\xFE]......[\x80-\xBF]';
		$rx .= '|^[\x80-\xBF]';
		return preg_match("!$rx!", $input) ? 0 : 1;
	}
	/*quita todos los caracteres de control (creo que tambien quita retornos de carro)
		nivel 2
	*/
	public function sinCaracteresDeControl(&$data){	
		return preg_replace('!\p{C}!u','',$data);
	}
	
	/*si usas esta funcion no deberias usar sincaracteresdecontrol por q los borra. borra los caracteres de control.
		nivel 2
	*/
	public function normalizarCarros(&$data){	
		return preg_replace('!\n\r?!','\n',$data);
	}
	
	/*las siguientes funciones son del nivel 3, que a su ves adentro cada uno ejecuta la funcion setData que implementa los basicos nivel 1 y nivel 2*/	
	/*TRUE:FALSE*/
    public function soloNumeros(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);			
			$permitidos = '1234567890';
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}
		return $retornar;
	}
	
	
	/*TRUE:FALSE
		Mira si el texto es solo numeros o nuomeros decimales
	*/
    public function soloNumerosODecimal(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$puntoencontrado = 0;
			$posicion = -1;
			$data=trim($data);						
			$permitidos = '1234567890.';
			for ($i=0; $i<strlen($data); $i++){		  
				if(strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}else{
					if(substr($data,$i,1)==='.'){
						$posicion = strpos($permitidos, substr($data,$i,1));
						$puntoencontrado++;
					}
				}		    
			}
			if($puntoencontrado>0){
				if($puntoencontrado==1){
					if($posicion==strlen($data)){  //si la posicion donde fue encontrado el punto es la ultima, no se admite
						$retornar=false;
					}
				}else{
					$retornar=false;	//tiene mas de un punto, no es decimal
				}				
			}
		}else{
			$retornar=false;
		}
		return $retornar;
	}
	
	/*TRUE:FALSE*/
	public function soloLetras(&$data){
		$retornar=true;	    		
		if($this->setData($data)){	
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}	
		return $retornar;
	}
	
	/*TRUE:FALSE  sirve para validar nombres*/
	public function soloLetrasYespacios(&$data){
		$retornar=true;	    		
		if($this->setData($data)){	
			$data=trim($data);			
			$permitidos = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJLKMNÑOPQRSTUVWXYZñáéíóúÁÉÍÓÚ ";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}	
		return $retornar;
	}
	
	
	/*TRUE:FALSE  sirve para validar nombres de curso*/
	public function soloNombreCurso(&$data){
		$retornar=true;	    		
		if($this->setData($data)){	
			$data=trim($data);			
			$permitidos = "abcdefghijklmnnñopqrstuvwxyzABCDEFGHIJKLMNÑñOPQRSTUVWXYZáéíóúÁÉÍÓ Ú 1234567890()-.";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}	
		return $retornar;
	}
	
	/*TRUE:FALSE  sirve para validar nombres, a diferencia de la anterior incluye otros caracteres admitidos para un nombre como el guion*/
	public function soloLetrasYespaciosEspecial(&$data){
		$retornar=true;	    		
		if($this->setData($data)){	
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZñáéíóúÁÉÍÓÚ -";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}	
		return $retornar;
	}
	
	/*TRUE:FALSE*/
	public function soloNumerosyLetrasMinusculas(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyz1234567890";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar = false;
		}
		return $retornar;
	}
	
	/*TRUE:FALSE*/
	public function soloNumerosyLetrasMayusculas(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);			
			$permitidos = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar = false;
		}
		return $retornar;
	}
	
	
	/*TRUE:FALSE*/
	public function soloAlias(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar = false;
		}
		return $retornar;
	}
	
	/*TRUE:FALSE*/
	public function soloUrlAmigable(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyz1234567890-";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar = false;
		}
		return $retornar;
	}
			
	/*TRUE:FALSE, solo permite los emails con los caracteres basicos numeros letras(minusculas sin la ñ), guion bajo punto arroba y guion 
		y que el formato sea de un típico email: algo de letras al comienzo, o numeros despues de la arroba un punto y palabra a la derecha y a al izquerda
	
	*/
	public function validaEmail(&$data){
		$retornar=true;	    		
		if($this->setData($data)){
			$data=trim($data);
			$permitidos = "abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ1234567890.-@_";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;
				}		    
			}
			if($retornar){ //si todo va bien entonces vemos si el formato es correcto.				
				$pedazos = explode('@', $data);
				if(count($pedazos)==2){ //solo debe haber una arroba					
					$pedazo0 = $pedazos[0];
					if(strlen($pedazos[0])>=3){ //el primer pedazo drebe contener 3 caracteres o mas											
						$pedazos2 = explode('.', $pedazos[1]);
						if(count($pedazos2)==2 || count($pedazos2)==3){ //el segundo pedazo el que va despues de la arroba solo debe tener uno o dos puntos.												
							if((strlen($pedazos2[0])>=3 || ($pedazos2[0]=='aa')) && strlen($pedazos2[1])>=2){ //ambos pedazos deben tener 3 caracteres por lo menos.															
							}else{ $retornar=false; }
						}else{ $retornar=false; }
					}else{ $retornar=false; }
				}else{ $retornar=false; }
			}
		}
		return $retornar;
	}
	
	/*TRUE:FALSE  sirve para validar los caracteres de una contrasena*/
	public function soloContrasena(&$data){
		$retornar=true;	    		
		if($this->setData($data)){	
			$data=trim($data);			
			$permitidos = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-!*?¿$.";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){
					$retornar=false;				
				}		    
			}
		}else{
			$retornar=false;
		}	
		return $retornar;
	}
	
	/*FILTRO : deja pasar letras (con tilde, mayusculas, minusculas) espacio, guion, y parentesis el resto las borra de la fase.	   	   
	*/
	public function nombre(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$data = ucwords(strtolower($data)); //converte en minusculas las letras y en mayusculas la primera
			$permitidos = "abcdefghijklmnnñopqrstuvwxyzABCDEFGHIJKLMNÑñOPQRSTUVWXYZáéíóúÁÉÍÓ Ú()-";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){								
					$data = str_replace (substr($data,$i,1), '', $data);
				}		    
			}
			$data = ucwords(strtolower($data));	//pone la primera en mayuscula
		}else{	
			$retornar=false;
		}
		return $retornar;
	}
	
	/*FILTRO : deja pasar letras (con tilde, mayusculas, minusculas) espacio, guion, y parentesis el resto las borra de la fase.	   	   
	*/
	public function nombreCurso(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$data = ucwords(strtolower($data)); //converte en minusculas las letras y en mayusculas la primera
			$permitidos = "abcdefghijklmnnñopqrstuvwxyzABCDEFGHIJKLMNÑñOPQRSTUVWXYZáéíóúÁÉÍÓ Ú 1234567890()-.";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){								
					$data = str_replace (substr($data,$i,1), '', $data);
				}		    
			}
			$data = ucwords(strtolower($data));	//pone la primera en mayuscula
		}else{	
			$retornar=false;
		}
		return $retornar;
	}	
	
	/*FILTRO : deja pasar letras (con tilde, mayusculas, minusculas) numeros y espacio.
	*/
	public function numerosLetrasEspacio(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$permitidos = "abcdefghijklmnnñopqrstuvwxyz1234567890ABCDEFGHIJKLMNÑñOPQRSTUVWXYZáéíóúÁÉÍÓ Ú";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){								
					$data = str_replace (substr($data,$i,1), '', $data);
				}		    
			}			
		}else{	
			$retornar=false;
		}
		return $retornar;
	}
	
	/*FILTRO : deja pasar letras (con tilde, mayusculas, minusculas) numeros punto coma punto y coma, y espacio, el resto las borra de la fase.	   	   
	*/
	public function parrafo(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$permitidos = "abcdefghijklmnnñopqrstuvwxyzABCDEFGHIJKLMNÑñOPQRSTUVWXYZ1234567890áéíóúÁÉÍÓÚ =.;";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){								
					$data = str_replace (substr($data,$i,1), '', $data);
				}		    
			}			
		}else{	
			$retornar=false;
		}
		return $retornar;
	}
		
						
	/*true, false: valida una fecha que contega los caracteres comprendidos en este formato: 2011-12-25 21:07:50
		NOTA: esta funcion le falta terminarse por que al parecer strtotime esta retornando true si el mes es de 30 dias y coloco 31 y si los segundos son 60 y no 00 como deberia ser.
	*/
	public function validaFechaHora(&$data){
		$retornar=true;
		if($this->setData($data)){			
			$data=trim($data);
			if(!strtotime($data)){
				$retornar=false;
			}			
		}else{
			$retornar = false;
		}	
		return $retornar;	
	}
	
	/*true, false: valida una fecha que contega los caracteres comprendidos en este formato: 2011-12-25*/
	public function validaFecha2(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);			
			$pedazos = explode('-', $data);
			if(count($pedazos)==3){
				if(!checkdate($pedazos[1], $pedazos[2], $pedazos[0])){
					$retornar = false;	//si no es valida se coloca en false. de lo contrario que siga en true.
				}
			}else{
				$retornar = false;
			}
		}else{
			$retornar = false;
		}	
		return $retornar;	
	}
						
	/*FILTRO : acepta <>=":;-,./_& espacio numeros letras pequenas, letrasgrandes
		$conexion : una conexion es requerida para poder escapar las comillas que se aceptan en esta funcion.
		esta es posiblemente la función de filtro mas peligrosa
		esta funcion escapa las comillas ya que las comillas se aceptan		
	*/
	public function descripcion(&$data, &$conexion){
		if(!$this->Utf8Seguro($data)){	//se aplican manualmente las capas de filtro.
			return false;	
		}else{
			if($this->sinCaracteresDeControl($data)){
				$data=trim($data);			
				$permitidos = 'abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑñOPQRSTUVWXYZ1234567890áéíóúÁÉÍÓÚ <>="\':;-,./_&';
				for ($i=0; $i<strlen($data); $i++){		  
					if (strpos($permitidos, substr($data,$i,1))===false){			
						$data = str_replace (substr($data,$i,1), '', $data);									
					}		    
				}
				//en este momento es que aplicaremos el libfilter, por que pudieron haber quedado agujeros para xss.
				//los de abajo son los tags html que se permitiran para una descripción.
				$permitidos = array(
					'div' => array('style'),	//style="font-style:normal font-size="medium"
					'br' => array('class'),		//class=""					
					'ol' => array(),			
					'li' => array(),
					'i' => array(),
					'span' => array('style'),	//style dentro del span con font-size:10pt o font-size:small
					'b' => array('style'),		//style="font-style:normal" (para la negrita)
					'font' => array('size','face'),	//size="2" y 4?					
					'img' => array('src')		//se le deberia inyectar aqui un max-width:750px
				);
				//echo 'llego1__'.$data;
				if($this->filtroHtml($data, $permitidos)){					
					//echo 'llego2_____'.$data;
					if($data = $this->mysqlrealscapestring($data, $conexion, 'rc_productos')){
						return $data;
					}else{
						return false;
					}
				}else{ return false; }				
			}else{
				return false;
			}			
		}
	}
		
	/*
	nivel 4:
	te dice si la cadena que pases esta entre el rango minimo y maximo
	@cadena es la cadena que se quiere medir.
	@menor, es lo minimo que puede tener en tamaño la cadena
	@maximno es lo máximo que puede tener en tamaño la cadena.
		retorna true, si lo está , retorna false si no lo está.
	*/
	public function limiteTamano($cadena, $menor, $maximo){
		if(strlen($cadena)>=$menor &&  strlen($cadena)<=$maximo){
			return true;
		}else{
			return false;
		}
	}
	
	
	/*nivel 4, escapa todas las comillas que tenga (si se dejaron pasar algunas) para poder incluirlas en una base de datos.*/
	public function mysqlrealscapestring($data, &$conexion, $basededatos){		
		if(function_exists("mysql_real_escape_string")){
			if($link = $conexion->base($basededatos)){
				$data = mysql_real_escape_string($data, $link); 
			}else{
				$data = mysql_real_escape_string($data); 
			}			
		}else{ //for PHP version < 4.3.0 use addslashes
			$data = addslashes( $value );
		}					
		return $data;
	}
						
	/* FILTRO:
		Limpia y valida una cadena de texto para que al final solo queden sus numeros
		retorna la nueva cadena de texto con solo los numeros
		retora false si la data no es valida.
	*/
	public function limpiaSoloNumeros(&$data){
		$retornar = true;
		if($this->setData($data)){
			$data = trim($data);
			$data = preg_replace("/[^0-9\s]/", "", $data);
		}else{
			$retornar = false;
		}
		return $retornar;
	}
			
	/* FILTRO:
		Limpia y valida una cadena de texto para que al final solo queden sus numeros y sus letras
		retorna la nueva cadena de texto con solo los numeros y letras
		retora false si la data no es valida.
	*/
	public function limpiaSoloNumerosyLetras(&$data){
		$retornar = true;
		if($this->setData($data)){
			$data = trim($data);			
			$data = preg_replace('/[^a-zA-Z0-9]/', '', $data);
		}else{
			$retornar = false;
		}
		return $retornar;
	}
	
	/*TRUE FALSE
		solo valida la integridad de los datos y que sean numeros y un punto en medio de dos numeros el primero con de tamano entre 1 y 11, y el segundo de 2 (fijo).
	*/
	public function validaPrecio(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$retornar=true;
			$pedazos = explode('.', $data);
			if(count($pedazos)==2){
				if($this->limiteTamano($pedazos[0], 1, 11)){ //debe estar entre 1 y 11
					if($this->limiteTamano($pedazos[1], 2, 2)){ //debe ser de 2 o nada. por que llega hasta 00
						$permitidos = "1234567890";
						for ($i=0; $i<strlen($pedazos[0]); $i++){
							if (strpos($permitidos, substr($pedazos[0],$i,1))===false){
								$retornar=false;				
							}		    
						}			
						for ($i=0; $i<strlen($pedazos[1]); $i++){
							if (strpos($permitidos, substr($pedazos[1],$i,1))===false){
								$retornar=false;				
							}		    
						}
					}else{ $retornar=false; }
				}else{
					$retornar=false;
				}
			}else{
				$retornar=false;
			}									
		}else{
			$retornar = false;
		}	
		return $retornar;	
	}
		
	/*TRUE - FALSE*/
	public function latitudLongitud(&$data){
		$retornar=true;
		if($this->setData($data)){
			$data=trim($data);
			$permitidos = "-.1234567890";
			for ($i=0; $i<strlen($data); $i++){		  
				if (strpos($permitidos, substr($data,$i,1))===false){								
					$retornar=false;
				}		    
			}
			if($retornar){
				$partes = explode('.', $data); //debe tener un punto.
				if(count($partes)!=2){ //y debe ser un solo punto, por lo tanto esto debe estar dividido en dos partes
					$retornar=false;  //echo 'exploto1';
				}else{
					if(strlen($partes[0])>0 && strlen($partes[1])>0){	//debe haberalgo en esas dos partes, no se admiten vacios.
						$partes2 = explode('-', $partes[1]);
						if(count($partes2)!=1){  //en la segunda parte no debe estar el guion,por lo tanto el explode no puede artir por el - si lo parte no se admite el string.
							$retornar=false; //echo 'exploto2';
						}else{
							$numero = 0;
							$partes2 = explode('-', $partes[0]); //echo 'dividiendo'.$partes[0].' y hay '.count($partes2).'<br>';
							if(count($partes2)>1){  //si encontro el guion en la primera parte...
								if(count($partes2)==2){  //..deben haber dos partes por oblicacion...
									if($partes2[0]!=''){  //si la primera parte de esas dos partes es diferente de vacio, entonces es por que hay algo, entonces esta mal.
										$retornar=false; //echo 'exploto3';
									}else{
										$numero = $partes2[1];
									}
								}else{
									$retornar=false; //echo 'exploto4';
								}
							}else{
								$numero = $partes[0];
							}
							if($retornar){ //es por q ha pasado todas laspruebasy numero tiene algo, pero debemos ver que ese numero no supere 179.
								if($numero>=-179 && $numero<=179){}else{
									$retornar=false; //echo 'exploto5';
								}
							}																				
						}
					}
				}
			}
		}else{	
			$retornar=false;
		}
		return $retornar;
	}
	
	/*
		Le quita las tildes a la cadena que le pases y retorna una copia de la cadena sin tildes.
	*/
	function quitar_tildes($cadena){
		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$texto = str_replace($no_permitidas, $permitidas ,$cadena);
		return $texto;
	}
	
}	
?>