<?php namespace clases;
class CuponDescuento{

	private $id = '';
	private $codigo = '';
	private $porcentajedescuento = '';
	private $fechahoravencimiento = '';
	private $estado = '';

	private $conexion = false;
	private $comun = false;


	function __construct(&$conexionset, $idcupon=false){
		$this->conexion = $conexionset;
		if($idcupon && is_numeric($idcupon)){
			$sql = 'select id, codigo, porcentajedescuento, fechahoravencimiento, estado
					from cuponesdedescuento
					where id='.$idcupon.';';
			$result_buscar = $this->conexion->consultar($sql);
			if($r = pg_fetch_array($result_buscar)){
				$this->id = $r['id'];
				$this->codigo = $r['codigo'];
				$this->porcentajedescuento = $r['porcentajedescuento'];
				$this->fechahoravencimiento = $r['fechahoravencimiento'];
				$this->estado = $r['estado'];
			}
		}
	}

	/*
		Retorna todos los cupones
	*/
	public function getTodos(){
		$retornar = array();
		if(!$this->id){
			$sql='select id, codigo, porcentajedescuento, fechahoravencimiento, estado
					from cuponesdedescuento
					order by estado';
			$result_d = $this->conexion->consultar($sql);
			if($row_d = pg_fetch_array($result_d)){
				do{
					array_push($retornar, array('id'=>$row_d['id'], 'codigo'=>$row_d['codigo'], 'porcentajedescuento'=>$row_d['porcentajedescuento'], 'fechahoravencimiento'=>$row_d['fechahoravencimiento'], 'estado'=>$row_d['estado']));
				}while($row_d = pg_fetch_array($result_d));
			}
		}
		return $retornar;
	}


	public function setCuponDescuento($codigo, $porcentajedescuento, $fechahoravencimiento){
		$retornar = false;
		if(!$this->id){
			$mysql_datetime = date("Y-m-d H:i:s");
			$sql= "insert into cuponesdedescuento(codigo, porcentajedescuento, fechahoravencimiento, estado) values
					('$codigo', '$porcentajedescuento', '$fechahoravencimiento', 1)";
			if($this->conexion->insertar($sql)){
				$ultimoid = $this->conexion->getLastId('cuponesdedescuento', 'id');
				if($ultimoid!=0){
					$this->id = $ultimoid;
					$this->codigo = $codigo;
					$this->porcentajedescuento = $porcentajedescuento;
					$this->fechahoravencimiento = $fechahoravencimiento;
					$this->estado = 1;
					$retornar = true;
				}
			}
		}
		return $retornar;
	}

	public function getDato($campo){
		if($this->id){
			$campovalidos = array('id', 'codigo', 'porcentajedescuento', 'fechahoravencimiento', 'estado');
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
				case 'codigo':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'porcentajedescuento':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'fechahoravencimiento':
					if($valor!=''){
						$valido = true;
					}
				break;
				case 'estado':
					if($valor!=''){
						$valido = true;
					}
				break;
			}
			if($valido){
				$sql_up='UPDATE cuponesdedescuento SET '.$campo.'=\''.$valor.'\' WHERE id=\''.$this->id.'\'';
				if($this->conexion->actualizar($sql_up)){
					$this->$campo = $valor;
					$retornar = true;
				}
			}
		}
		return $retornar;
	}

	public function borrar(){
		$retornar = false;
		if($this->id){

			$sql='DELETE from cuponesdedescuento WHERE id=\''.$this->id.'\'';
			if($this->conexion->actualizar($sql)){
				$retornar = true;
			}

		}
		return $retornar;
	}

	public function getCuponPorCodigo($codigo){
		$retornar = array();
		$sql='select id, codigo, porcentajedescuento, fechahoravencimiento, estado
				from cuponesdedescuento
				where codigo=\''.$codigo.'\' ';
		$result_d = $this->conexion->consultar($sql);
		if($row_d = pg_fetch_array($result_d)){
			$retornar = array('id'=>$row_d['id'], 'codigo'=>$row_d['codigo'], 'porcentajedescuento'=>$row_d['porcentajedescuento'], 'fechahoravencimiento'=>$row_d['fechahoravencimiento'], 'estado'=>$row_d['estado']);
		}
		return $retornar;
	}
}

?>