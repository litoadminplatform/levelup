<?php  namespace clases; 
class Curso{	
	private $id = '';
	private $fullname = '';
	private $shortname = '';	
	private $summary = '';
	private $category = '';	
	private $startdateesp = '';
	private $visible = '';
	
	
	private $descripcioncorta = '';
	private $descripcionlarga = '';
	private $dirigidoa = '';
	private $prerequisito = '';
	private $itemsprerequisito = array();
	private $precio = '';
	private $horario = '';
	private $intensidadhoraria = '';
	private $acercadelinstructor = '';
	private $destacado = '';
	private $paquetecursos = array();	
	
	private $hainiciado = -1;   //-1 no configurada fecha de inicio, 1 si inicio el curso, 0 no ha iniciado el curso
	
	private $conexion;	
	private $comun;
	private $filtro;
			
	function __construct(&$conexionset, $idcurso=false){
		$this->conexion = $conexionset;		
		$this->comun = new Comun($this->conexion);
		$this->filtro = new Filtro();
		if($idcurso && is_numeric($idcurso)){
			$sql = 'select id, fullname, shortname, summary, category, startdate, visible
					from mdl_course
					where id='.$idcurso.'';
			$result_buscar = $this->conexion->consultar($sql);	
			if($r = pg_fetch_array($result_buscar)){
				$this->id = $r['id'];
				$this->fullname = $r['fullname'];			
				$this->shortname = $r['shortname'];				
				$this->summary = $r['summary'];
				$this->category = $r['category'];				
				$this->visible = $r['visible'];
				
				if($r['startdate']!=0){
					$this->startdateesp = ucwords(strftime("%B %d de %Y", strtotime($this->comun->convierteUnixATimestamp($r['startdate']))));
					
					//se mira si ha iniciado o no
					if((intval($r['startdate'])+86400)>time()){
						$this->hainiciado = 0;		//no ha iniciado
					}else{
						$this->hainiciado = 1;		//ya inició
					}
				}
								
				//obtenemos del summary los datos extras guardados
				include_once(RUTAPROYECTO.'src/clases/simplehtmldom_1_9_1/simple_html_dom.php');
				$html = str_get_html($this->summary);	
				if($html){
					$div = $html->find('div[class=infoc]');								
					if($div){
						$this->descripcioncorta = $html->find('div[class=infoc] div[class=descripcioncorta]') ? $html->find('div[class=infoc] div[class=descripcioncorta]')[0]->innertext : '';
						$this->descripcionlarga = $html->find('div[class=infoc] div[class=descripcionlarga]') ? $html->find('div[class=infoc] div[class=descripcionlarga]')[0]->innertext : '';
						$this->dirigidoa = $html->find('div[class=infoc] div[class=dirigidoa]') ? $html->find('div[class=infoc] div[class=dirigidoa]')[0]->innertext : '';
						$this->prerequisito = $html->find('div[class=infoc] div[class=prerequisito]') ? $html->find('div[class=infoc] div[class=prerequisito]')[0]->innertext : '';
						$this->precio = $html->find('div[class=infoc] div[class=precio]') ? $html->find('div[class=infoc] div[class=precio]')[0]->innertext : '';
						$this->horario = $html->find('div[class=infoc] div[class=horario]') ? $html->find('div[class=infoc] div[class=horario]')[0]->innertext : '';
						$this->intensidadhoraria = $html->find('div[class=infoc] div[class=intensidadhoraria]') ? $html->find('div[class=infoc] div[class=intensidadhoraria]')[0]->innertext : '';						
						$this->acercadelinstructor = $html->find('div[class=infoc] div[class=acercadelinstructor]') ? $html->find('div[class=infoc] div[class=acercadelinstructor]')[0]->innertext : '';						
						$this->destacado = $html->find('div[class=infoc] div[class=destacado]') ? $html->find('div[class=infoc] div[class=destacado]')[0]->innertext : '';	
						
						$prerequisitoitems = $html->find('ul[class=itemsprerequisito] li');
						foreach($prerequisitoitems as $pi){
							if($pi->innertext!=''){
								$this->itemsprerequisito[] = $pi->innertext;								
							}							
						}
						
						$paquetecursos = $html->find('ul[class=paquetecursos] li');
						foreach($paquetecursos as $pc){
							if($pc->innertext!=''){
								$this->paquetecursos[] = $pc->innertext;								
							}							
						}
												
						
					}								
				}				
			}
		}
	}
			
	public function getDato($campo){
		if($this->id){
			$campovalidos = array('id', 'fullname', 'shortname', 'summary', 'category', 'startdateesp', 'visible', 'descripcioncorta', 'descripcionlarga', 'dirigidoa', 'prerequisito', 'itemsprerequisito', 'precio', 'horario', 'intensidadhoraria', 'acercadelinstructor', 'paquetecursos', 'destacado', 'hainiciado');
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
				case 'summary':	//aqui van las areas preferidas del usuario
					if($valor!=''){
						$valido = true;
					}	
				break;				
			}
			if($valido){
				$sql_up='UPDATE mdl_course SET '.$campo.'=\''.$valor.'\' WHERE id=\''.$this->id.'\'';
				if($this->conexion->actualizar($sql_up)){
					$this->$campo = $valor;					
					$retornar = true;
				}
			}
		}
		return $retornar;
	}			
	
	public function getCategoria(){
		$retornar = array();
		if($this->id){
			$sql = 'select id, name
					from mdl_course_categories
					where id='.$this->category.'';
			$result_buscar = $this->conexion->consultar($sql);	
			if($r = pg_fetch_array($result_buscar)){
				$retornar = array('id'=>$r['id'], 'name'=>$r['name']);
			}	
		}
		return $retornar;
	}
	
	/*No usado en LevelUp*/
	public function getNotasUsuario($idusuario){		
		$retornar = array();
		if($this->id){
			$sql6 = 'select a.userid, b.courseid, a.itemid, b.iteminstance, a.finalgrade, b.itemtype, b.itemmodule, b.itemname
					from mdl_grade_grades a, mdl_grade_items b
					where b.courseid=\''.$this->id.'\' and a.itemid=b.id and b.itemtype=\'mod\' and a.userid=\''.$idusuario.'\'
					order by a.userid, b.courseid';
			$result_d6 = $this->conexion->consultar($sql6);
			if($row_d6 = pg_fetch_array($result_d6)){
				do{
					$nota = $row_d6['finalgrade'];
					if(is_numeric($nota)){
						if($nota>5 && $nota<=51){							
							$nota--;
							if($nota>=10){
								$partes = str_split($nota.'', 1);
								$nota = $partes[0].'.'.$partes[1];
							}else{
								$partes = str_split($nota.'', 1);
								$nota = '0.'.$partes[0];
							}
						}else{
							$nota = number_format($nota, 1);
						}
					}else{
						$nota = -1;
					}					
					array_push($retornar, array('userid'=>$row_d6['userid'], 'courseid'=>$row_d6['courseid'], 'itemid'=>$row_d6['itemid'], 'iteminstance'=>$row_d6['iteminstance'], 'finalgrade'=>$nota, 'itemtype'=>$row_d6['itemtype'], 'itemmodule'=>$row_d6['itemmodule'], 'itemname'=>$row_d6['itemname']));
				}while($row_d6 = pg_fetch_array($result_d6));													
			}
		}	
		return $retornar;
	}	
 }	
?>