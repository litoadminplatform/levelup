<?php namespace clases;
class Factura{
	
	private $id = '';	
	private $idusuario = ''; 
	private $idcurso = '';
	private $consecutivo = '';
	private $fechacarritogenerado = '';
	private $fechafacturagenerada = '';
	private $fechacheckout = '';
	private $total = '';
	private $estado = '';		//(1: abierto carrito, 2 en proceso de pago (aun no rellenado el formulario de pago), 3 error transaccion, 4 pagado, 7 en espera de la respuesta de la pasarela de pagos (llega aqui despues del paso 2)		
		
	//respuestas de la pasarela de pagos
	private $tituloultimarespuesta = '';
	private $descripcionultimarespuesta = '';
	private $respuestadato1 = '';
	private $respuestadato2 = '';
	private $respuestadato3 = '';
	private $respuestadato4 = '';
	
	function __construct(&$conexionset, $idfactura=false){
		$this->conexion = $conexionset;				
		if($idfactura && is_numeric($idfactura)){
			$sql = 'select id, idusuario, idcurso, consecutivo, fechacarritogenerado, fechafacturagenerada, fechacheckout, total, estado, tituloultimarespuesta, descripcionultimarespuesta, respuestadato1, respuestadato2, respuestadato3, respuestadato4
					from factura
					where id='.$idfactura.'';
			$result_buscar = $this->conexion->consultar($sql);	
			if($r = pg_fetch_array($result_buscar)){
				$this->id = $r['id'];	
				$this->idusuario = $r['idusuario']; 				
				$this->idcurso = $r['idcurso'];
				$this->consecutivo = $r['consecutivo'];
				$this->fechacarritogenerado = $r['fechacarritogenerado'];
				$this->fechafacturagenerada = $r['fechafacturagenerada'];
				$this->fechacheckout = $r['fechacheckout'];
				$this->total = $r['total'];
				$this->estado = $r['estado'];
				
				//respuestas de la pasarela de pagos
				$this->tituloultimarespuesta = $r['tituloultimarespuesta'];
				$this->descripcionultimarespuesta = $r['descripcionultimarespuesta'];
				$this->respuestadato1 = $r['respuestadato1'];
				$this->respuestadato2 = $r['respuestadato2'];
				$this->respuestadato3 = $r['respuestadato3'];
				$this->respuestadato4 = $r['respuestadato4'];
			}
		}
	}
	
	/*
		Matricula al usuario de esta factura en el curso o los cursos que dice que está comprando en la misma
		retorna ok si todo fue bien, o otro mensaje en caso de error
	*/
	public function matricularEnCurso(){
		$retornar = 'error-desconocido';
		if($this->id){
			$usuario = new Usuario($this->conexion, $this->idusuario);
			if($usuario->getDato('id')){
				
				$curso = new Curso($this->conexion, $this->idcurso);
				if($curso->getDato('id')){
					$paquetecursos = $curso->getDato('paquetecursos');
					if(count($paquetecursos)==0){	//solo un curso, se matricula en el de la factura.
						$retornar = $usuario->matriculaEnCurso($this->idcurso);
					}else{
						foreach($paquetecursos as $pc){
							$retornar = $usuario->matriculaEnCurso($pc);							
						}						
					}
				}								
			}
		}
		return $retornar;
	}
	
	/*
		Desmatricula al usuario de esta factura en el curso que dice que está comprando en la misma
		retorna ok si todo fue bien, o otro mensaje en caso de error
	*/
	public function desmatricularEnCurso(){
		$retornar = 'error-desconocido';
		if($this->id){
			$usuario = new Usuario($this->conexion, $this->idusuario);
			if($usuario->getDato('id')){
				
				$curso = new Curso($this->conexion, $this->idcurso);
				if($curso->getDato('id')){
					$paquetecursos = $curso->getDato('paquetecursos');
					if(count($paquetecursos)==0){	//solo un curso, se matricula en el de la factura.
						$retornar = $usuario->desmatriculaEnCurso($this->idcurso);
					}else{
						foreach($paquetecursos as $pc){
							$retornar = $usuario->desmatriculaEnCurso($pc);							
						}						
					}
				}								
			}
			/*if($usuario->getDato('id')){
				$retornar = $usuario->desmatriculaEnCurso($this->idcurso);
			}*/
		}
		return $retornar;
	}
	
	
	/*
		Retorna y genera un numero de factura.
		No se usa el LEVEL UP
	*/
	public function generarConsecutivo(){
		$retornar = 0;
		if($this->id){
			if($this->consecutivo==0){			
				$sitio = new Sitio($this->conexion);
				$numeroactual = $sitio->getConfig('numerofacturaactual');	//el numero del utlimo consecutivo generado
				if(!$numeroactual){
					if($sitio->setConfig('numerofacturaactual', 1)){
						$numeroactual = 1;
					}
				}else{
					$numeroactual++;
				}
				if(is_numeric($numeroactual)){
					if($this->setDato('consecutivo', $numeroactual)){
						if($sitio->setConfig('numerofacturaactual', $numeroactual)){	//se guarda el último generado
							$retornar = $numeroactual;
						}	
					}
				}
			}
		}
		return $retornar;
	}
							
	public function setFactura($idusuario, $idcurso){
		$retornar = false;
		if(!$this->id){
			$mysql_datetime = date("Y-m-d H:i:s");
			$curso = new Curso($this->conexion, $idcurso);
			if($curso->getDato('id')){
				$precio = $curso->getDato('precio');
				$sql= "insert into factura(idusuario, idcurso, consecutivo, fechacarritogenerado, total, estado, tituloultimarespuesta, descripcionultimarespuesta, respuestadato1, respuestadato2, respuestadato3, respuestadato4) values
						('$idusuario', '$idcurso', '0', '$mysql_datetime', '$precio', '2', '', '', '', '', '', '')";
				if($this->conexion->insertar($sql)){
					$ultimoid = $this->conexion->getLastId('factura', 'id');
					if($ultimoid!=0){
						$this->id = $ultimoid;
						$this->idusuario = $idusuario;
						$this->idcurso = $idcurso;					
						$this->fechacarritogenerado = $mysql_datetime;	
						$this->total = $precio;
						$this->estado = 2;
						$retornar = true;
					}
				}
			}
		}
		return $retornar;
	}
	
	/*
		LAS PAGINAS SON DE A 10 en 10 Y SE EMPIEZA POR LA 0.
	*/
	public function getTodos($idusuario = 0, $estado = -1, $pagina=0){
		$retornar = array();
		if(!$this->id){	
			
			$inyectar = '';
			if($estado!=-1){
				$inyectar = ' and a.estado=\''.$estado.'\' ';
			}
			
			if($idusuario!=0){
				/*if($inyectar!=''){
					$inyectar.=' and ';
				}*/
				$inyectar.=' and a.idusuario=\''.$idusuario.'\' ';
			}
									
			//contamos cuantas son
			$cantidad = -1;
			$sql='select count(*) as cantidad
					from factura a, mdl_course b 
					where a.idcurso=b.id '.$inyectar.'';   //echo $sql;  
			$result_d = $this->conexion->consultar($sql); 
			if($row_d = pg_fetch_array($result_d)){
				$cantidad = $row_d['cantidad'];
			}	
			//fin de contar cuantas son
		
		
			$startfrom = $pagina*10;
			$sql='select a.id, a.idusuario, c.firstname, c.lastname, c.idnumber, c.phone1, c.institution, a.idcurso, b.fullname, a.consecutivo, a.fechacarritogenerado, a.fechafacturagenerada, a.fechacheckout, a.total, a.estado, a.tituloultimarespuesta, a.descripcionultimarespuesta, a.respuestadato1, a.respuestadato2, a.respuestadato3, a.respuestadato4
					from factura a, mdl_course b, mdl_user c
					where a.idcurso=b.id and a.idusuario=c.id '.$inyectar.' 
					order by a.fechacarritogenerado desc
					limit 10 offset '.$startfrom.'; ';  //echo $sql;  
			$result_d = $this->conexion->consultar($sql); 
			if($row_d = pg_fetch_array($result_d)){
				do{					
					array_push($retornar, array('id'=>$row_d['id'], 
											'idusuario'=>$row_d['idusuario'],											
											'firstname'=>$row_d['firstname'],
											'lastname'=>$row_d['lastname'],
											'idnumber'=>$row_d['idnumber'],
											'phone1'=>$row_d['phone1'],
											'institution'=>$row_d['institution'],
											'idcurso'=>$row_d['idcurso'],											
											'fullname'=>$row_d['fullname'],
											'consecutivo'=>$row_d['consecutivo'],
											'fechacarritogenerado'=>$row_d['fechacarritogenerado'],
											'fechafacturagenerada'=>$row_d['fechafacturagenerada'],
											'fechacheckout'=>$row_d['fechacheckout'],
											'total'=>$row_d['total'],
											'estado'=>$row_d['estado'],
											'tituloultimarespuesta'=>$row_d['tituloultimarespuesta'],
											'descripcionultimarespuesta'=>$row_d['descripcionultimarespuesta'],
											'respuestadato1'=>$row_d['respuestadato1'],
											'respuestadato2'=>$row_d['respuestadato2'],
											'respuestadato3'=>$row_d['respuestadato3'],
											'respuestadato4'=>$row_d['respuestadato4'],
											'cantidad'=>$cantidad
										));
				}while($row_d = pg_fetch_array($result_d));
			}
		}	
		return $retornar;
	}
	
	/*
		Borra la factura, segun el caso
	*/
	public function borrar(){
		$retornar = false;
		if($this->id){
			$estadosborrar = array(2, 7, 1, 5);
			if(in_array($this->estado, $estadosborrar)){
				$sql='DELETE from factura WHERE id=\''.$this->id.'\'';
				if($this->conexion->actualizar($sql)){
					$retornar = true;							
				}
			}
		}
		return $retornar;
	}
	
	
	public function getDato($campo){
		if($this->id){
			$campovalidos = array('id', 'idusuario', 'idcurso', 'consecutivo', 'fechacarritogenerado', 'fechafacturagenerada', 'fechacheckout', 'total', 'estado', 'tituloultimarespuesta', 'descripcionultimarespuesta', 'respuestadato1', 'respuestadato2', 'respuestadato3', 'respuestadato4');
			if(in_array($campo, $campovalidos)){
				return $this->$campo;								
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function setDato($campo, $valor){
		$retornar = false;
		if($this->id){
			$valido = false;
			switch($campo){			
				case 'consecutivo':
					if($valor!=''){
						$valido = true;
					}	
				break;
				case 'fechafacturagenerada':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'fechacheckout':
					if($valor!='' || $valor==NULL){
						$valido = true;
					}
				break;
				case 'total':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'estado':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'tituloultimarespuesta':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'descripcionultimarespuesta':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'respuestadato1':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'respuestadato2':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'respuestadato3':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'respuestadato4':
					if($valor!=''){
						$valido = true;
					}
				break;
			}
			if($valido){
				$sql_up='UPDATE factura SET '.$campo.'=\''.$valor.'\' WHERE id=\''.$this->id.'\'';
				if($this->conexion->actualizar($sql_up)){
					$this->$campo = $valor;					
					$retornar = true;
				}
			}
		}
		return $retornar;
	}		
	
}
?>	
