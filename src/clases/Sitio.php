<?php namespace clases; 
	
	use \stdClass as stdClass;
	
	class Sitio{
		
		private $conexion = false;	
		private $filtro = false;
		
		function __construct(&$conexion){
			$this->conexion = $conexion;
			$this->filtro = new Filtro();
		}						
		
		public function guardarConfiguracion($data){
			$retornar = false;
			$sql='select name
					from mdl_config where name=\'configlanding\' ';
			$result_d = $this->conexion->consultar($sql);
			if(!$row_d = pg_fetch_array($result_d)){
				
				$sql= 'insert into mdl_config(name, value) values
						(\'configlanding\', \''.$data.'\')'; 
				if($this->conexion->insertar($sql)){
					$retornar = true;
				}

			}else{
				$sql_up='UPDATE mdl_config SET value=\''.$data.'\' WHERE name=\'configlanding\'';
				if($this->conexion->actualizar($sql_up)){					
					$retornar = true;
				}				
			}
			return $retornar;
		}
		
		/*
			Retorna la configuración de sitio guardada
			{
				textohome: '',
				subtextohome: '',
				textonuevoscursos: '',
				subtextonuevoscursos: '',
				textouneteahora: '',
				subtextouneteahora: '',
				telefonowhastapp: ''
			}
		*/
		public function getConfig(){
			$retornar = array('textohome'=>'', 'subtextohome'=>'', 'textonuevoscursos'=>'', 'subtextonuevoscursos'=>'', 'textouneteahora'=>'', 'subtextouneteahora'=>'', 'telefonowhastapp'=>'');
			
			$sql='select value
					from mdl_config where name=\'configlanding\' ';			
			$result_d = $this->conexion->consultar($sql);
			if($row_d = pg_fetch_array($result_d)){
				//obtenemos del summary los datos extras guardados
				include_once(RUTAPROYECTO.'src/clases/simplehtmldom_1_9_1/simple_html_dom.php');
				$html = str_get_html($row_d['value']);	
				if($html){
					$div = $html->find('div[class=infoc]');								
					if($div){
						$retornar['textohome'] = $html->find('div[class=infoc] div[class=textohome]') ? $html->find('div[class=infoc] div[class=textohome]')[0]->innertext : '';
						$retornar['subtextohome'] = $html->find('div[class=infoc] div[class=subtextohome]') ? $html->find('div[class=infoc] div[class=subtextohome]')[0]->innertext : '';
						$retornar['textonuevoscursos'] = $html->find('div[class=infoc] div[class=textonuevoscursos]') ? $html->find('div[class=infoc] div[class=textonuevoscursos]')[0]->innertext : '';
						$retornar['subtextonuevoscursos'] = $html->find('div[class=infoc] div[class=subtextonuevoscursos]') ? $html->find('div[class=infoc] div[class=subtextonuevoscursos]')[0]->innertext : '';
						$retornar['textouneteahora'] = $html->find('div[class=infoc] div[class=textouneteahora]') ? $html->find('div[class=infoc] div[class=textouneteahora]')[0]->innertext : '';
						$retornar['subtextouneteahora'] = $html->find('div[class=infoc] div[class=subtextouneteahora]') ? $html->find('div[class=infoc] div[class=subtextouneteahora]')[0]->innertext : '';
						$retornar['telefonowhastapp'] = $html->find('div[class=infoc] div[class=telefonowhastapp]') ? $html->find('div[class=infoc] div[class=telefonowhastapp]')[0]->innertext : '';
					}								
				}				
			}
			return $retornar;			
		}
		
		/*
			Retorna el id de la categoria principal donde se encuentran las categorias del sistema
		*/	
		public function getIdCategoriaOfertados(){
			$retornar = -1;
			$sql='select id
					from mdl_course_categories
					where idnumber=\'1\'
					limit 1';	
			$result_d = $this->conexion->consultar($sql);
			if($row_d = pg_fetch_array($result_d)){
				$retornar = $row_d['id'];
			}	
			return $retornar;
		}	
		
		/*
			Retorna las categorias hijas de una padre pasada por id
		*/
		public function getSubCategoriasByIdPadre($idcategoria){
			$retornar = array();
			
			$sql='select id, name
					from mdl_course_categories
					where parent=\''.$idcategoria.'\' and visible=1
					order by sortorder';			
			$result_d = $this->conexion->consultar($sql);
			if($row_d = pg_fetch_array($result_d)){
				do{		 
					$namenormalizado = mb_strtolower($this->conexion->normaliza($row_d['name']), 'UTF-8');
					$this->filtro->limpiaSoloNumerosyLetras($namenormalizado);
					
					$retornar[] = array('id'=>$row_d['id'], 'name'=>$row_d['name'], 'namenormalizado'=>$namenormalizado);
				}while($row_d = pg_fetch_array($result_d));
			}
			return $retornar;
		}
		
		/*
			Retorna todos los cursos ofertados en este momento. los cursos deben estar visibles y con un fecha startdate establecida a una fecha superior a la fecha actual.
		*/
		public function getCursosOfertados($solodestacados=false){
			
			global $OUTPUT;
			
			$respuesta['estado'] = 'error';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			if($this->conexion){		
				
				$idcategoriapadre = $this->getIdCategoriaOfertados();
				if($idcategoriapadre!=-1){
					
					$hijas = $this->getSubCategoriasByIdPadre($idcategoriapadre);
					$inyectarcategorias = '';
					foreach($hijas as $hi){
						if($inyectarcategorias!=''){
							$inyectarcategorias.=' or ';						
						}
						$inyectarcategorias.=' category=\''.$hi['id'].'\' ';
					}
					if($inyectarcategorias!=''){
						$inyectarcategorias = ' and ('.$inyectarcategorias.')';
					}
					
					$inyectardestacados = '';
					if($solodestacados){
						$inyectardestacados = ' and a.summary like \'%<div class="destacado">1</div>%\' ';						
					}
					
					if($inyectarcategorias!=''){
						$respuesta['estado'] = 'ok';
						$comun = new Comun();
						$sql='select a.id, a.category, a.fullname, a.startdate, a.enddate, b.name as categoria
								from mdl_course a, mdl_course_categories b
								where a.visible=1 '.$inyectarcategorias.' and startdate!=0 and a.category=b.id '.$inyectardestacados.'
								order by a.startdate';
						$result_d = $this->conexion->consultar($sql);
						if($row_d = pg_fetch_array($result_d)){
							do{	
								if((intval($row_d['startdate'])+691200)>time()){		//si el curso aun no ha arrancado (se incluye que se pueda comprar el dia que inicia)
									$startdateesp = '';
									if($row_d['startdate']!=0 && $row_d['startdate']!=''){
										$startdateesp = ucwords(strftime("%B %d de %Y", strtotime($comun->convierteUnixATimestamp($row_d['startdate']))));
									}
									
									$enddateesp = '';
									if($row_d['enddate']!=0 && $row_d['enddate']!=''){
										$enddateesp = ucwords(strftime("%B %d de %Y", strtotime($comun->convierteUnixATimestamp($row_d['enddate']))));
									}
									
									//obtenemos el docente del curso.								
									$docenteid = 0;
									$docentenombre = '';
									$docentefoto = '';
									$cursodocente = $this->getMatriculados($row_d['id'], 0, array(3), true);
									$matriculados = $this->getMatriculados($row_d['id'], 0, array(5), true);
									$conteomatriculados = count($matriculados);
									$imagencurso = '';
									if(isset($cursodocente[0])){
										
										$docenteid = $cursodocente[0]['userid'];
										$docentenombre = ucwords(mb_strtolower($cursodocente[0]['firstname'].' '.$cursodocente[0]['lastname'], 'UTF-8'));
										
										if($cursodocente[0]['picture']!=0){
													
											$user = new stdClass();
											$user->id = $cursodocente[0]['userid'];
											$user->picture = $cursodocente[0]['picture'];
											$user->firstname = $cursodocente[0]['firstname'];
											$user->lastname = $cursodocente[0]['lastname'];
											$user->firstnamephonetic = '';
											$user->lastnamephonetic = '';
											$user->middlename = '';
											$user->alternatename = '';
											$user->imagealt = $cursodocente[0]['firstname'].' '.$cursodocente[0]['lastname'];
											$user->email = $cursodocente[0]['email'];
											
											$imagenuser = $OUTPUT->user_picture($user, array('size'=>150, 'alttext'=>false, 'link'=>false, 'class'=>'photodash'));
											$partes = explode('src="', $imagenuser);
											$partes = explode('"', $partes[1]);
											$docentefoto = $partes[0];
										}
									}
									
									//buscamos la imagen del curso..
									$fs = get_file_storage();			
									$context = \context_course::instance($row_d['id']);
									$files   = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);			
									if (count($files) > 0) {
										foreach ($files as $file) {
											if ($file->is_valid_image()) {
												$imagepath = '/' . $file->get_contextid() .
														'/' . $file->get_component() .
														'/' . $file->get_filearea() .
														$file->get_filepath() .
														$file->get_filename();
												$imageurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $imagepath,
														false);
												$imagencurso = \html_writer::tag('div',
														\html_writer::empty_tag('img', array('src' => $imageurl)),
														array('class' => 'courseimage'));
												//usa la primera imagen encontrada.
												
												include_once(RUTAPROYECTO.'src/clases/simplehtmldom_1_9_1/simple_html_dom.php');
												$html = str_get_html($imagencurso);	
												if($html){
													if(trim($html->find('img')[0]->src)!=''){
														$imagencurso = $html->find('img')[0]->src;
													}
												}
												break;
											}	
										}
									}			
									
									//fin de buscar la imagen del curso
									
									
									$categorianormalizado = mb_strtolower($this->conexion->normaliza($row_d['categoria']), 'UTF-8');
									$this->filtro->limpiaSoloNumerosyLetras($categorianormalizado);
									
									//nombre amigable
									$nombreamigable = $comun->generaUrlAmigable($row_d['fullname']);
									
									
									$respuesta['datos'][] = array('id'=>$row_d['id'], 'imagencurso'=>$imagencurso, 'category'=>$row_d['category'], 'categoria'=>$row_d['categoria'], 'categorianormalizado'=>$categorianormalizado, 'fullname'=>$row_d['fullname'], 'nombreamigable'=>$nombreamigable, 'startdate'=>$row_d['startdate'], 'startdateesp'=>$startdateesp, 'enddate'=>$row_d['enddate'], 'enddateesp'=>$enddateesp, 'docenteid'=>$docenteid, 'docentenombre'=>$docentenombre, 'docentefoto'=>$docentefoto, 'conteomatriculados'=>$conteomatriculados);
								}
							}while($row_d = pg_fetch_array($result_d));
						}
					}
				}
			}
			return $respuesta;
		}
		
		/*
			$ver:  -1 todos, 1 solo vigentes, 0 solo vencidos, -2 vigentes y ocultos
		*/
		public function getMatriculados($iducurso=0, $idusuario=0, $roles=array(), $ordenaralazar=false, $ver=-1){
			$retornar = array();
			$comun = new Comun();
			
			$mysql_datetime = date("Y-m-d H:i:s");  //Y-m-d H:i:s
			
			$inyectarcurso = '';
			if($iducurso!=0){
				$inyectarcurso = 'd.id = '.$iducurso.' AND ';			
			}
			
			$insertroles = '';						
			if(count($roles)>0){			
				$tam = count($roles);
				for($i=0; $i<$tam; $i++){
					if($insertroles!=''){
						$insertroles.=' or ';
					}
					$insertroles.=' a.roleid = \''.$roles[$i].'\' ';
				}
			}
			if($insertroles!=''){
				$insertroles = ' and ('.$insertroles.')';
			}				
			$inyectar = '';
			if($idusuario!=0){
				$inyectar = ' and b.id=\''.$idusuario.'\' ';
			}
			
			//$ordenar = ' order by b.id, a.roleid, b.firstname ';	//original
			$ordenar = ' order by d.startdate desc ';
			
			if($ordenaralazar){
				$ordenar = ' ORDER BY RANDOM() ';			
			}
			
			$inyectarvisible = 'and d.visible=1';		//lo normal, que los cursos que se muestran sean los visibles
			if($ver==-2){
				$inyectarvisible = '';
				$ver = 1;
			}
			
			$sql='SELECT d.id as courseid, b.id as userid, b.idnumber, a.roleid, d.fullname as coursename, d.shortname, d.startdate, d.enddate, d.visible, b.firstname, b.lastname, b.email, b.phone1, b.phone2, b.picture, ue.timestart, ue.timeend, ue.id as idenrollment, f.name as nombrecategoria
					FROM mdl_role_assignments a, mdl_user b, mdl_context c, mdl_course d, mdl_user_enrolments ue, mdl_enrol e, mdl_course_categories f
					WHERE  '.$inyectarcurso.' c.contextlevel = 50
					AND a.contextid = c.id
					AND a.userid = b.id
					AND d.category=f.id
					AND c.instanceid = d.id '.$inyectar.' '.$insertroles.'
					AND ue.userid = a.userid AND e.courseid = d.id '.$inyectarvisible.' and ue.enrolid = e.id 
					'.$ordenar.' ';
			$result_d = $this->conexion->consultar($sql); 
			if($row_d = pg_fetch_array($result_d)){				
				do{
					$inycc = '';
					$pasa = true;
					if($ver!=-1){
						$pasa = false;
						switch($ver){
							case 0:
								if($row_d['enddate']!=0){
									$fechafin = gmdate("Y-m-d\ H:i:s", $row_d['enddate'] + 3600*(-5));
									if($mysql_datetime>$fechafin){
										$pasa = true;
									}
								}else{
									if($row_d['startdate']!=0){
										$acumsemana = gmdate("Y-m-d", $row_d['startdate'] + 3600*(-5));									
										
										//se calcula la fecha de finalizacion saltandose las semanas de fin y comenzo de año..
										//se agrego esto para hacer el hueco de fin de año y reiniciar en enero
										for ($i=2; $i<=8;$i++){
											$fechainiciovalida = false;
											while(!$fechainiciovalida){							
												$acumsemana=$comun->operacion_fecha($acumsemana,7); //acumuluador de fechas apartir de la fecha de inicio calculamos las diferentes semanas con intervalo de 7 dias.								
												$numerosemana = date("W", strtotime($acumsemana));
												if($numerosemana>=3 && $numerosemana<=50){
													$fechainiciovalida = true;
												}
											}				
										}
										//fin del hueco de fin de año.
										
										if($mysql_datetime>date("Y-m-d H:i:s", strtotime($acumsemana.' +0 week'))){
											$pasa = true;
										}
									}else{
										$pasa = true;
									}								
								}
							break;
							case 1:		//exactamente igual al case 0 (vendidos) solo que cambia el >  por <=
								if($row_d['enddate']!=0){
									$fechafin = gmdate("Y-m-d\ H:i:s", $row_d['enddate'] + 3600*(-5));
									if($mysql_datetime<=$fechafin){
										$pasa = true;
									}
								}else{
									if($row_d['startdate']!=0){
										$fechainicio = gmdate("Y-m-d", $row_d['startdate'] + 3600*(-5));
										
										//se calcula la fecha de finalizacion saltandose las semanas de fin y comenzo de año..
										//se agrego esto para hacer el hueco de fin de año y reiniciar en enero
										for ($i=2; $i<=8;$i++){
											$fechainiciovalida = false;
											while(!$fechainiciovalida){							
												$fechainicio=$comun->operacion_fecha($fechainicio,7); //acumuluador de fechas apartir de la fecha de inicio calculamos las diferentes semanas con intervalo de 7 dias.								
												$numerosemana = date("W", strtotime($fechainicio));
												if($numerosemana>=3 && $numerosemana<=50){
													$fechainiciovalida = true;
												}
											}				
										}
										//fin del hueco de fin de año.
										
										if($mysql_datetime<=date("Y-m-d H:i:s", strtotime($fechainicio.' +0 week'))){
											$pasa = true;
										}
									}else{
										$pasa = true;
									}								
								}
							break;						
						}
					}
					if($pasa){
						array_push($retornar, array('courseid'=>$row_d['courseid'],	'userid'=>$row_d['userid'], 'roleid'=>$row_d['roleid'], 'coursename'=>$row_d['coursename'], 'shortname'=>$row_d['shortname'], 'startdate'=>$row_d['startdate'], 'enddate'=>$row_d['enddate'], 'firstname'=>$row_d['firstname'], 'lastname'=>$row_d['lastname'], 'idnumber'=>$row_d['idnumber'], 'email'=>$row_d['email'], 'phone1'=>$row_d['phone1'], 'idusuariosinu'=>$row_d['phone2'], 'picture'=>$row_d['picture'], 'timestart'=>$row_d['timestart'], 'timeend'=>$row_d['timeend'], 'idenrollment'=>$row_d['idenrollment'], 'nombrecategoria'=>$row_d['nombrecategoria'], 'visible'=>$row_d['visible']));
					}
				}while($row_d = pg_fetch_array($result_d));			
			}		
			return $retornar;
		}
		
		/*
			Retorna sugerencias de cursos segun el texto digitado, se envia el idusuario podra aparecer en orden de matriculados > en progreso > cerrados > no matriculados
			NOTA: NO PROBADO PARA LEVELUP
		*/
		public function getCursosSugerencias($texto, $idusuario){
			$retornar = array();
			if($this->conexion){
				$cursosprocesados = array();
				$sql = 'SELECT c.id, cat.name categoria, c.fullname curso, c.shortname, round(g.finalgrade, 2) finalgrade, x.timeenrolled, x.timestarted, x.timecompleted
						FROM '.PREFIJO.'course c					
							LEFT JOIN '.PREFIJO.'course_categories cat ON cat.id=c.category
							LEFT JOIN '.PREFIJO.'context cx ON (c.id=cx.instanceid and cx.contextlevel = \'50\')
							LEFT JOIN '.PREFIJO.'grade_items i ON (i.itemtype=\'course\' and i.courseid= cx.instanceid)
							LEFT JOIN '.PREFIJO.'grade_grades g ON (i.id=g.itemid and g.userid = \''.$idusuario.'\' and g.finalgrade IS NOT NULL)
							LEFT JOIN '.PREFIJO.'course_completions x ON (x.userid = g.userid AND x.course = cx.instanceid)
						WHERE (c.fullname like \'%'.$texto.'%\' or c.fullname like \''.$texto.'%\') and c.visible=1	
						ORDER BY x.timeenrolled desc, x.timecompleted desc, c.fullname
						LIMIT 10';   //este ultimo estaba en INNER pero se cambio a LEFT JOIN para mostrar todo los cursos en que esta matriculado sin importar si tienen fecha final e inicio o no.
				$result_d = $this->conexion->consultar($sql); 
				if($row_d = pg_fetch_array($result_d)){
					do{	
						if(!in_array($row_d['id'], $cursosprocesados)){
							array_push($cursosprocesados, $row_d['id']);						
							array_push($retornar, array('link'=>URLBASE.'course/view.php?id='.$row_d['id'], 'id'=>$row_d['id'], 'curso'=>$row_d['curso'], 'shortname'=>$row_d['shortname'], 'finalgrade'=>$row_d['finalgrade'], 'timeenrolled'=>$row_d['timeenrolled'], 'timestarted'=>$row_d['timestarted'], 'timecompleted'=>$row_d['timecompleted']));
						}
					}while($row_d = pg_fetch_array($result_d));
				}
			}
			return $retornar;
		}
		
		/*
			Retorna una lista de usuarios sistema por nombre basado en los parametros pasados. util para el autocomppleta
		*/
		public function getUsariosSistemaPorNombre($text, $incluirsuspendidos=false){
			$retornar = array();
			if($this->conexion){
				$inyectar = ' and suspended=\'0\'';
				if($incluirsuspendidos){
					$inyectar = '';
				}
				$sql='select id, username, CONCAT(firstname,  \' \', lastname) as nombre, suspended
						from mdl_user
						where concat(firstname, \' \', lastname) like \'%'.$text.'%\' and deleted=0 '.$inyectar.' 
						limit 10';
				$result_d = $this->conexion->consultar($sql);
				if($row_d = pg_fetch_array($result_d)){
					do{
						array_push($retornar, array('value'=>$row_d['id'], 'label'=>$row_d['nombre'], 'id'=>$row_d['id'], 'username'=>$row_d['username'], 'nombre'=>$row_d['nombre'], 'suspended'=>$row_d['suspended']));
					}while($row_d = pg_fetch_array($result_d));
				}
			}
			return $retornar;
		}
		
	}
?>