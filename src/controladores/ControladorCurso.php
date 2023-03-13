<?php namespace controladores;

	use clases\Sesion as Sesion;
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;
	use clases\Usuario as Usuario;
	use clases\Curso as Curso;
	use clases\Sitio as Sitio;
	use \controladores\ControladorSitio as ControladorSitio;
	use \stdClass as stdClass;

	class ControladorCurso{
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

			global $USER;

			$respuesta['estado'] = 'ok';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			$respuesta['datos']['categorias'] = array();
			$respuesta['datos']['editardatoscurso'] = false;
			$respuesta['datos']['tienecursosnuevos'] = false;

			$sitio = new Sitio($this->conexion);
			$respuesta['datos']['config'] = $sitio->getConfig();


			if(isloggedin()){
				$usuario = new Usuario($this->conexion, $USER->id);
				if($usuario->getDato('id')){
					$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);
					if(count($permisoeditarcurso)>0){
						$respuesta['datos']['editardatoscurso'] = true;
					}
				}
			}

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
			return $respuesta;
		}

		public function crear(){


			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();


			return $respuesta;
		}

		public function editar($id){	//viene PUT y con solo un numero en segunda posicion

			global $USER;

			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();

			$json = file_get_contents('php://input');
			$data = json_decode($json,true);

			$puedeeditar = false;
			if(isloggedin()){
				$usuario = new Usuario($this->conexion, $USER->id);
				if($usuario->getDato('id')){
					$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);
					if(count($permisoeditarcurso)>0){
						$puedeeditar = true;
					}
				}
			}
			if($puedeeditar){
				if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
					$curso = new Curso($this->conexion, $id);
					if($curso->getDato('id')){
						$buscar = array('<', '>');
						$reemplazar =  array('', '');

						if(isset($data['descripcioncorta']) && ($this->filtro->limiteTamano($data['descripcioncorta'], 1, 512) || trim($data['descripcioncorta'])=='') ){
							$data['descripcioncorta'] = str_replace($buscar, $reemplazar, trim($data['descripcioncorta']));
						}else{
							$respuesta['datos'][] = array('descripcioncorta', 'Muy largo, se permiten 512 caracteres.');
						}

						if(isset($data['descripcionlarga']) && ($this->filtro->limiteTamano($data['descripcionlarga'], 1, 4096) || trim($data['descripcionlarga'])=='') ){
							$data['descripcionlarga'] = str_replace($buscar, $reemplazar, trim($data['descripcionlarga']));
						}else{
							$respuesta['datos'][] = array('descripcionlarga', 'Muy largo, se permiten 4096 caracteres.');
						}

						if(isset($data['dirigidoa']) && ($this->filtro->limiteTamano($data['dirigidoa'], 1, 2048) || trim($data['dirigidoa'])=='') ){
							$data['dirigidoa'] = str_replace($buscar, $reemplazar, trim($data['dirigidoa']));
						}else{
							$respuesta['datos'][] = array('dirigidoa', 'Muy largo, se permiten 2048 caracteres.');
						}

						if(isset($data['prerequisito']) && ($this->filtro->limiteTamano($data['prerequisito'], 1, 2048) || trim($data['prerequisito'])=='') ){
							$data['prerequisito'] = str_replace($buscar, $reemplazar, trim($data['prerequisito']));
						}else{
							$respuesta['datos'][] = array('prerequisito', 'Muy largo, se permiten 2048 caracteres.');
						}

						$itemsprerequisito = array();
						$cont = 0;
						if(isset($data['itemsprerequisitos'])){
							$partes = explode('$$|', $data['itemsprerequisitos']);
							foreach($partes as $pa){
								if($this->filtro->limiteTamano($pa, 1, 512)){
									$itemsprerequisito[] = $pa;
								}else{
									if($pa!=''){
										$respuesta['datos'][] = array('prerequisitoitem_'.$cont, 'Solo se admiten 512 caracteres.');
									}
								}
								$cont++;
							}
						}


						if(isset($data['precio']) && (($this->filtro->soloNumeros($data['precio']) && $this->filtro->limiteTamano($data['precio'], 1, 7)) || trim($data['precio'])=='') ){
							$data['precio'] = str_replace($buscar, $reemplazar, trim($data['precio']));
						}else{
							$respuesta['datos'][] = array('precio', 'No colque comas, ni puntos, solo escriba los números, limite tamaño 7 caracteres.');
						}

						if(isset($data['horario']) && ($this->filtro->limiteTamano($data['horario'], 1, 128) || trim($data['horario'])=='') ){
							$data['horario'] = str_replace($buscar, $reemplazar, trim($data['horario']));
						}else{
							$respuesta['datos'][] = array('horario', 'Muy largo, se permiten 128 caracteres.');
						}

						if(isset($data['intensidadhoraria']) && ($this->filtro->limiteTamano($data['intensidadhoraria'], 1, 64) || trim($data['intensidadhoraria'])=='') ){
							$data['intensidadhoraria'] = str_replace($buscar, $reemplazar, trim($data['intensidadhoraria']));
						}else{
							$respuesta['datos'][] = array('intensidadhoraria', 'Muy largo, se permiten 64 caracteres.');
						}

						if(isset($data['acercadelinstructor']) && ($this->filtro->limiteTamano($data['acercadelinstructor'], 1, 2048) || trim($data['acercadelinstructor'])=='') ){
							$data['acercadelinstructor'] = str_replace($buscar, $reemplazar, trim($data['acercadelinstructor']));
						}else{
							$respuesta['datos'][] = array('acercadelinstructor', 'Muy largo, se permiten 2048 caracteres.');
						}


						$paquetecursos = array();
						if(isset($data['paquetecursos'])){
							$partes = explode(',', $data['paquetecursos']);
							foreach($partes as $pa){
								$pa = trim($pa);
								if($this->filtro->soloNumeros($pa) && $this->filtro->limiteTamano($pa, 1, 8)){
									$paquetecursos[] = $pa;
								}else{
									if($pa!=''){
										$respuesta['datos'][] = array('paquetecursos', 'Encontrado item(s) incorrecto(s), por lo tanto no guardado(s).');
									}
								}
							}
						}

						if(isset($data['destacado']) && $this->filtro->limiteTamano($data['destacado'], 1, 1) && ($data['destacado']=='0' || $data['destacado']=='1')){
							$data['destacado'] = str_replace($buscar, $reemplazar, trim($data['destacado']));
						}else{
							$respuesta['datos'][] = array('destacado', 'Inválido.');
						}

						if(count($respuesta['datos'])==0){

							$inyectar = '<div class="infoc" style="display:none;">';
								$inyectar.= '<div class="descripcioncorta">';
									$inyectar.= trim($data['descripcioncorta']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="descripcionlarga">';
									$inyectar.= trim($data['descripcionlarga']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="dirigidoa">';
									$inyectar.= trim($data['dirigidoa']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="prerequisito">';
									$inyectar.= trim($data['prerequisito']);
								$inyectar.= '</div>';


								$inyectar.= '<ul class="itemsprerequisito">';
								foreach($itemsprerequisito as $ipr){
									$inyectar.= '<li>'.trim($ipr).'</li>';
								}
								$inyectar.= '</ul>';

								$inyectar.= '<div class="precio">';
									$inyectar.= trim($data['precio']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="horario">';
									$inyectar.= trim($data['horario']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="intensidadhoraria">';
									$inyectar.= trim($data['intensidadhoraria']);
								$inyectar.= '</div>';
								$inyectar.= '<div class="acercadelinstructor">';
									$inyectar.= trim($data['acercadelinstructor']);
								$inyectar.= '</div>';

								$inyectar.= '<ul class="paquetecursos" style="display:none;">';
								foreach($paquetecursos as $pc){
									$inyectar.= '<li>'.trim($pc).'</li>';
								}
								$inyectar.= '</ul>';

								$inyectar.= '<div class="destacado">';
									$inyectar.= trim($data['destacado']);
								$inyectar.= '</div>';


							$inyectar.= '</div>';

							if($curso->setDato('summary', $inyectar)){
								$respuesta['estado'] = 'ok';
							}

						}
					}
				}
			}
			return $respuesta;

		}
		public function borrar($id){	//viene DELETE y con solo un numero en segunda posicion

			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();


			return $respuesta;

		}
		public function ver($id){	//viene GET y con solo un numero en segunda posicion, para retornar solo los datos de uno solo, si se requiere de otras listas ya ahi si se necesitan las funciones personalizadas.

			global $OUTPUT, $USER;
			$this->sesion->setDatoSesion('cuponporcentaje', '');
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();

			if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
				$curso = new Curso($this->conexion, $id);
				if($curso->getDato('id')){

					/*if($urlamigable!='no-redirect'){
						$this->sesion->setDatoSesion('urlreedireccionar', URLBASE.'/info/curso/'.$curso->getDato('id').'/no-redirect');
					}*/

					$sitio = new Sitio($this->conexion);

					$respuesta['estado'] = 'ok';

					$respuesta['datos']['config'] = $sitio->getConfig();

					$respuesta['datos']['tienesesion'] = false;
					$respuesta['datos']['nombreusuario'] = '';
					if(isloggedin()){
						$respuesta['datos']['tienesesion'] = true;
						$respuesta['datos']['nombreusuario'] = $USER->firstname;
					}

					$respuesta['datos']['id'] = $curso->getDato('id');
					$respuesta['datos']['fullname'] = $curso->getDato('fullname');

					$respuesta['datos']['descripcioncorta'] = $curso->getDato('descripcioncorta');
					$respuesta['datos']['descripcionlarga'] = $curso->getDato('descripcionlarga');
					$respuesta['datos']['dirigidoa'] = $curso->getDato('dirigidoa');
					$respuesta['datos']['prerequisito'] = $curso->getDato('prerequisito');
					$respuesta['datos']['itemsprerequisito'] = $curso->getDato('itemsprerequisito');
					$respuesta['datos']['precio'] = number_format($curso->getDato('precio'), 0, ',', '.');
					$respuesta['datos']['horario'] = $curso->getDato('horario');
					$respuesta['datos']['intensidadhoraria'] = $curso->getDato('intensidadhoraria');
					$respuesta['datos']['acercadelinstructor'] = $curso->getDato('acercadelinstructor');
					$respuesta['datos']['paquetecursos'] = $curso->getDato('paquetecursos');
					$respuesta['datos']['destacado'] = $curso->getDato('destacado');
					$respuesta['datos']['cantidadmatriculados'] = count($sitio->getMatriculados($id, 0, array(5), true));
					$respuesta['datos']['startdateesp'] = $curso->getDato('startdateesp');


					$categoria = $curso->getCategoria();
					$respuesta['datos']['categorid'] = $categoria['id'];
					$respuesta['datos']['categorianombre'] = $categoria['name'];



					$respuesta['datos']['docenteid'] = 0;
					$respuesta['datos']['docentenombre'] = '';
					$respuesta['datos']['docentefoto'] = '';
					$respuesta['datos']['imagencurso'] = '';
					$respuesta['datos']['imagencursopequena'] = '';					

					$cursodocente = $sitio->getMatriculados($id, 0, array(3), true);
					if(isset($cursodocente[0])){

						$respuesta['datos']['docenteid'] = $cursodocente[0]['userid'];
						$respuesta['datos']['docentenombre'] = ucwords(mb_strtolower($cursodocente[0]['firstname'].' '.$cursodocente[0]['lastname'], 'UTF-8'));

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
							$respuesta['datos']['docentefoto'] = $partes[0];
						}
					}


					//buscamos la imagen del curso..
					$indeximg = ['', ''];
					$tamimg = [0, 0];
					$fs = get_file_storage();
					$context = \context_course::instance($id);
					$files   = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
					if (count($files) > 0) {
						$cont = 0;
						foreach ($files as $file) {
							if ($file->is_valid_image()) {								
								$imagepath = '/' . $file->get_contextid() .
										'/' . $file->get_component() .
										'/' . $file->get_filearea() .
										$file->get_filepath() .
										$file->get_filename();
								$imageurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $imagepath,
										false);
								$indeximg[$cont] = \html_writer::tag('div',
										\html_writer::empty_tag('img', array('src' => $imageurl)),
										array('class' => 'courseimage'));
								//usa la primera imagen encontrada.
								$tamimg[$cont] = $file->get_filesize();
								include_once(RUTAPROYECTO.'src/clases/simplehtmldom_1_9_1/simple_html_dom.php');
								$html = str_get_html($indeximg[$cont]);
								if($html){
									if(trim($html->find('img')[0]->src)!=''){
										$indeximg[$cont] = $html->find('img')[0]->src;;										
									}
								}
								$cont++;
								if($cont>1){
									break;
								}
							}
						}
					}
					if($tamimg[1]>$tamimg[0]){
						$indeximg = array_reverse($indeximg);
					}
					$respuesta['datos']['imagencurso'] = $indeximg[0];
					$respuesta['datos']['imagencursopequena'] = $indeximg[1];
					//fin de buscar la imagen del curso


					//otros cursos
					$respuesta['datos']['categorias'] = array();
					$controladorsitio = new ControladorSitio($this->conexion);
					$respuesta['datos']['tieneotroscursos'] = false;
					$categoriasofertadas = $controladorsitio->getCursosOfertados();
					if(isset($categoriasofertadas['datos']['categorias'])){
						$respuesta['datos']['categorias'] = $categoriasofertadas['datos']['categorias'];
						shuffle($respuesta['datos']['categorias']);

						for($i=0; $i<count($respuesta['datos']['categorias']); $i++){

							shuffle($respuesta['datos']['categorias'][$i]['cursos']);
							$quitarindex = -1;
							for($j=0; $j<count($respuesta['datos']['categorias'][$i]['cursos']); $j++){
								if($respuesta['datos']['categorias'][$i]['cursos'][$j]['id']!=$id){
									$curso = new Curso($this->conexion, $respuesta['datos']['categorias'][$i]['cursos'][$j]['id']);
									$respuesta['datos']['categorias'][$i]['cursos'][$j]['precio'] = number_format($curso->getDato('precio'), 0, ',', '.');
									$respuesta['datos']['categorias'][$i]['cursos'][$j]['descripcioncorta'] = $curso->getDato('descripcioncorta');
									$respuesta['datos']['tieneotroscursos'] = true;
								}else{
									$quitarindex = $j;
								}
							}
							if($quitarindex!=-1){
								unset($respuesta['datos']['categorias'][$i]['cursos'][$quitarindex]); // remove item at index 0
								$respuesta['datos']['categorias'][$i]['cursos'] = array_values($respuesta['datos']['categorias'][$i]['cursos']); // 'reindex' array
							}
						}

					}
					//fin de otros cursos

				}
			}

			return $respuesta;
		}

		/*
			GET
			Retorna la interfaz para editar los datos de un curso.
			$id: El id del curso a editar.
		*/
		public function formeditar($id){

			global $USER;

			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();

			if(isset($id) && $this->filtro->soloNumeros($id) && $this->filtro->limiteTamano($id, 1, 16)){
				if(isloggedin()){
					$usuario = new Usuario($this->conexion, $USER->id);
					if($usuario->getDato('id')){
						$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);
						if(count($permisoeditarcurso)>0){


							$curso = new Curso($this->conexion, $id);
							if($curso->getDato('id')){

								$respuesta['estado'] = 'ok';
								$respuesta['datos']['id'] = $curso->getDato('id');
								$respuesta['datos']['fullname'] = $curso->getDato('fullname');

								$respuesta['datos']['descripcioncorta'] = $curso->getDato('descripcioncorta');
								$respuesta['datos']['descripcionlarga'] = $curso->getDato('descripcionlarga');
								$respuesta['datos']['dirigidoa'] = $curso->getDato('dirigidoa');
								$respuesta['datos']['prerequisito'] = $curso->getDato('prerequisito');
								$respuesta['datos']['itemsprerequisito'] = $curso->getDato('itemsprerequisito');
								$respuesta['datos']['precio'] = $curso->getDato('precio');
								$respuesta['datos']['horario'] = $curso->getDato('horario');
								$respuesta['datos']['intensidadhoraria'] = $curso->getDato('intensidadhoraria');
								$respuesta['datos']['acercadelinstructor'] = $curso->getDato('acercadelinstructor');
								$respuesta['datos']['destacado'] = $curso->getDato('destacado');
								$respuesta['datos']['paquetecursos'] = $curso->getDato('paquetecursos');

								$sitio = new Sitio($this->conexion);
								$respuesta['datos']['config'] = $sitio->getConfig();

							}

						}
					}
				}
			}
			if($respuesta['estado']=='error'){
				exit;
			}

			return $respuesta;
		}

	}

?>