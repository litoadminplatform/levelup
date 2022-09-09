<?php namespace controladores;
	
	use clases\Sesion as Sesion;	
	use clases\Filtro as Filtro;
	use clases\Comun as Comun;
	use clases\Sitio as Sitio;
	use clases\Usuario as Usuario;
	
	class ControladorSitio{
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
			$respuesta['estado'] = 'ok';	
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
									
	
			return $respuesta;
		}	
		
		public function crear(){
		
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
												
			
			return $respuesta;
		}
		
		/*	GUARDA LA CONFIGURACIÓN DEL SITIO.
			PUT
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
				
				$buscar = array('<', '>');
				$reemplazar =  array('', '');
								
				if(isset($data['textohome']) && ($this->filtro->limiteTamano($data['textohome'], 1, 256) || trim($data['textohome'])=='') ){
					$data['textohome'] = str_replace($buscar, $reemplazar, trim($data['textohome']));
				}else{
					$respuesta['datos'][] = array('textohome', 'Muy largo, se permiten 256 caracteres.');
				}
				
				if(isset($data['subtextohome']) && ($this->filtro->limiteTamano($data['subtextohome'], 1, 4096) || trim($data['subtextohome'])=='') ){
					$data['subtextohome'] = str_replace($buscar, $reemplazar, trim($data['subtextohome']));
				}else{
					$respuesta['datos'][] = array('subtextohome', 'Muy largo, se permiten 4096 caracteres.');
				}
				
				if(isset($data['textonuevoscursos']) && ($this->filtro->limiteTamano($data['textonuevoscursos'], 1, 256) || trim($data['textonuevoscursos'])=='') ){
					$data['textonuevoscursos'] = str_replace($buscar, $reemplazar, trim($data['textonuevoscursos']));
				}else{
					$respuesta['datos'][] = array('textonuevoscursos', 'Muy largo, se permiten 2048 caracteres.');
				}
				
				if(isset($data['subtextonuevoscursos']) && ($this->filtro->limiteTamano($data['subtextonuevoscursos'], 1, 4096) || trim($data['subtextonuevoscursos'])=='') ){
					$data['subtextonuevoscursos'] = str_replace($buscar, $reemplazar, trim($data['subtextonuevoscursos']));
				}else{
					$respuesta['datos'][] = array('subtextonuevoscursos', 'Muy largo, se permiten 4096 caracteres.');
				}
				
				if(isset($data['textouneteahora']) && ($this->filtro->limiteTamano($data['textouneteahora'], 1, 256) || trim($data['textouneteahora'])=='') ){
					$data['textouneteahora'] = str_replace($buscar, $reemplazar, trim($data['textouneteahora']));
				}else{
					$respuesta['datos'][] = array('textouneteahora', 'Muy largo, se permiten 256 caracteres.');
				}
				
				if(isset($data['subtextouneteahora']) && ($this->filtro->limiteTamano($data['subtextouneteahora'], 1, 4096) || trim($data['subtextouneteahora'])=='') ){
					$data['subtextouneteahora'] = str_replace($buscar, $reemplazar, trim($data['subtextouneteahora']));
				}else{
					$respuesta['datos'][] = array('subtextouneteahora', 'Muy largo, se permiten 4096 caracteres.');
				}
				
				if(isset($data['telefonowhastapp']) && ($this->filtro->limiteTamano($data['telefonowhastapp'], 1, 32) || trim($data['telefonowhastapp'])=='') ){
					$data['telefonowhastapp'] = str_replace($buscar, $reemplazar, trim($data['telefonowhastapp']));
				}else{
					$respuesta['datos'][] = array('telefonowhastapp', 'Muy largo, se permiten 256 caracteres.');
				}

				if(count($respuesta['datos'])==0){
												
					$inyectar = '<div class="infoc" style="display:none;">';
						$inyectar.= '<div class="textohome">';
							$inyectar.= trim($data['textohome']);
						$inyectar.= '</div>';
						$inyectar.= '<div class="subtextohome">';
							$inyectar.= trim($data['subtextohome']);
						$inyectar.= '</div>';
						$inyectar.= '<div class="textonuevoscursos">';
							$inyectar.= trim($data['textonuevoscursos']);
						$inyectar.= '</div>';
						$inyectar.= '<div class="subtextonuevoscursos">';
							$inyectar.= trim($data['subtextonuevoscursos']);
						$inyectar.= '</div>';								
						$inyectar.= '<div class="textouneteahora">';
							$inyectar.= trim($data['textouneteahora']);
						$inyectar.= '</div>';
						$inyectar.= '<div class="subtextouneteahora">';
							$inyectar.= trim($data['subtextouneteahora']);
						$inyectar.= '</div>';
						$inyectar.= '<div class="telefonowhastapp">';
							$inyectar.= trim($data['telefonowhastapp']);
						$inyectar.= '</div>';							
					$inyectar.= '</div>';
					
					$sitio = new Sitio($this->conexion);
					if($sitio->guardarConfiguracion($inyectar)){
						$respuesta['estado'] = 'ok';
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
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			return $respuesta;
		}
		
		/*
			GET
			Retorna la interfaz para editar los la configuracion del sitio.			
		*/
		public function formeditar(){
			
			global $USER;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			
			
			if(isloggedin()){
				$usuario = new Usuario($this->conexion, $USER->id);
				if($usuario->getDato('id')){
					$permisoeditarcurso = $usuario->getAsignacionRol('1', 9);
					if(count($permisoeditarcurso)>0){
						
						
						$sitio = new Sitio($this->conexion);
						$config = $sitio->getConfig();
							
						$respuesta['estado'] = 'ok';
						$respuesta['datos']['textohome'] = $config['textohome'];
						$respuesta['datos']['subtextohome'] = $config['subtextohome'];
						$respuesta['datos']['textonuevoscursos'] = $config['textonuevoscursos'];
						$respuesta['datos']['subtextonuevoscursos'] = $config['subtextonuevoscursos'];
						$respuesta['datos']['textouneteahora'] = $config['textouneteahora'];
						$respuesta['datos']['subtextouneteahora'] = $config['subtextouneteahora'];
						$respuesta['datos']['telefonowhastapp'] = $config['telefonowhastapp'];
						$respuesta['datos']['config'] = $sitio->getConfig();
					}
				}
			}	
			
			if($respuesta['estado']=='error'){
				exit;
			}
			
			return $respuesta;			
		}
		
		
		/*
			POST
		*/
		public function setRedirect(){
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			if(isset($_POST['urlredireccionar'])){
				if(filter_var($_POST['urlredireccionar'], FILTER_VALIDATE_URL)) {
					$respuesta['estado'] = 'ok';
					$this->sesion->setDatoSesion('urlreedireccionar', $_POST['urlredireccionar']);					
					setcookie('urlreedireccionar', $_POST['urlredireccionar'], time() + (86400 * 30), "/");
				}
			}
			return $respuesta;
		}
		
		public function postLoad(){
			$respuesta['estado'] = 'ok';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			$respuesta['datos']['reedireccionar'] = '';						
			
			//si hay algun lugar para reedireccionar enviamos el dato.
			if($this->sesion->getDatoSesion('urlreedireccionar')!='' || isset($_COOKIE['urlreedireccionar'])){	
				if($this->sesion->getDatoSesion('urlreedireccionar')!=''){
					$respuesta['datos']['reedireccionar'] = $this->sesion->getDatoSesion('urlreedireccionar');
					$this->sesion->setDatoSesion('urlreedireccionar', '');
				}else{
					if($_COOKIE['urlreedireccionar']!=''){
						$respuesta['datos']['reedireccionar'] = $_COOKIE['urlreedireccionar'];
						setcookie('urlreedireccionar', '', time() - 3600, "/");
					}
				}												
			}
			
			return $respuesta;
		}
		
		/*
			GET
		*/
		public function getTokenLogin(){
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			if (!isloggedin()) {
				$respuesta['estado'] = 'ok';
				$respuesta['datos'] = s(\core\session\manager::get_login_token());
			}
			return $respuesta;
		}
		
		/*
			GET: Retorna los cursos ofertados en este momento en la plataforma.
		*/
		public function getCursosOfertados(){
			global $OUTPUT;
			
			$respuesta['estado'] = 'error';
			$respuesta['codigo'] = '';
			$respuesta['datos'] = array();
			$respuesta['datos']['categorias'] = array();
			
			$sitio = new Sitio($this->conexion);
			$idcatprincipal = $sitio->getIdCategoriaOfertados();
			if($idcatprincipal!=-1){
				$respuesta['datos']['categorias'] = $sitio->getSubCategoriasByIdPadre($idcatprincipal);	
				$cursos = $sitio->getCursosOfertados();
				$cursos = $cursos['datos'];
				$tam = count($respuesta['datos']['categorias']);
				for($i=0; $i<$tam; $i++){
					$respuesta['datos']['categorias'][$i]['cursos'] = array();	
					reset($cursos);
					foreach($cursos as $cu){
						if($cu['category']==$respuesta['datos']['categorias'][$i]['id']){
							$respuesta['datos']['categorias'][$i]['cursos'][] = $cu;							
						}
					}					
				}	
			}
			
			$respuesta['estado'] = 'ok';
			
			return $respuesta;
		}
		
		public function generarBaseDeDatos(){
			
			//tabla cupones
			$ejecutar = 'CREATE SEQUENCE public.cupon_id_seq
				INCREMENT 1
				START 1
				MINVALUE 1
				MAXVALUE 9223372036854775807
				CACHE 1;

			ALTER SEQUENCE public.cupon_id_seq
				OWNER TO seamitib;

			CREATE TABLE IF NOT EXISTS public.cuponesdedescuento
			(
				id bigint NOT NULL DEFAULT nextval(\'cupon_id_seq\'::regclass),
				codigo character varying(32) COLLATE pg_catalog."default" NOT NULL,
				fechahoravencimiento timestamp without time zone NOT NULL,
				porcentajedescuento smallint NOT NULL,
				estado smallint NOT NULL,
				CONSTRAINT cuponesdedescuento_pkey PRIMARY KEY (id)
			)
			WITH (
				OIDS = FALSE
			)
			TABLESPACE pg_default;

			ALTER TABLE public.cuponesdedescuento
				OWNER to seamitib; ';
			
			//tabla factura
			
			$ejecutar.= ' CREATE SEQUENCE public.factura_seq
				INCREMENT 1
				START 1
				MINVALUE 1
				MAXVALUE 9223372036854775807
				CACHE 1; 				

			CREATE TABLE IF NOT EXISTS public.factura
			(
				id bigint NOT NULL DEFAULT nextval(\'factura_seq\'::regclass),
				idusuario bigint NOT NULL,
				idcurso bigint NOT NULL,
				consecutivo bigint NOT NULL,
				fechacarritogenerado timestamp(0) without time zone NOT NULL,
				fechacheckout timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
				fechafacturagenerada timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
				total character varying(14) COLLATE pg_catalog."default" NOT NULL,
				estado smallint NOT NULL,
				tituloultimarespuesta character varying(32) COLLATE pg_catalog."default" NOT NULL,
				descripcionultimarespuesta character varying(128) COLLATE pg_catalog."default" NOT NULL,
				respuestadato1 character varying(1024) COLLATE pg_catalog."default" NOT NULL,
				respuestadato2 character varying(128) COLLATE pg_catalog."default" NOT NULL,
				respuestadato3 character varying(64) COLLATE pg_catalog."default" NOT NULL,
				respuestadato4 character varying(32) COLLATE pg_catalog."default" NOT NULL,
				CONSTRAINT factura_pkey PRIMARY KEY (id),
				CONSTRAINT factura_ibfk_1 FOREIGN KEY (idusuario)
					REFERENCES public.mdl_user (id) MATCH SIMPLE
					ON UPDATE NO ACTION
					ON DELETE NO ACTION,
				CONSTRAINT factura_ibfk_2 FOREIGN KEY (idcurso)
					REFERENCES public.mdl_course (id) MATCH SIMPLE
					ON UPDATE NO ACTION
					ON DELETE NO ACTION
			)
			WITH (
				OIDS = FALSE
			)
			TABLESPACE pg_default;

			ALTER SEQUENCE public.factura_seq OWNER TO seamitib;

			CREATE INDEX idcurso ON public.factura USING btree (idcurso ASC NULLS LAST) TABLESPACE pg_default;
			CREATE INDEX idusuario ON public.factura USING btree (idusuario ASC NULLS LAST) TABLESPACE pg_default; ';
			
		}
		
	}
	
?>