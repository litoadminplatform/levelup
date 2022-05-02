<!DOCTYPE html>
<html lang="en">
<head>
	<title>LevelUP Americana | Configuración Landing Page</title>
	<meta charset="UTF-8">
	<meta name="description" content="WebUni Education Template">
	<meta name="keywords" content="webuni, education, creative, html">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Favicon -->   
	<link href="<?php echo URLPROYECTO; ?>vistas/img/favicon.ico" rel="shortcut icon"/>

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Raleway:400,400i,500,500i,600,600i,700,700i,800,800i" rel="stylesheet">

	<!-- Stylesheets -->
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/font-awesome.min.css"/>
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/owl.carousel.css"/>
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/style.css?v=5"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/interfaz.css"/>

	
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<style type="text/css">
		.page-info-section {
			height: 290px;
		}
		.signup-form input[type=text]{
			margin-bottom: 0px;
		}
		.signup-form{
			padding: 30px;
		}
		.supbar {
			overflow: hidden;
			background-color: #1900FF;
			position: fixed; /* Set the navbar to fixed position */
			top: 0; /* Position the navbar at the top of the page */
			width: 100%; /* Full width */
			z-index:5;
			padding: 8px 0px;
		}
		.main-menu ul li a:hover {
			color: #FF367D;
		}
	</style>
</head>
<body>

	<?php
		
		//print_r($datos['datos']); //['categorias']
	
	?>

	<!-- Page Preloder -->
	<div id="preloder">
		<div class="loader"></div>
	</div>

	<!-- Header section -->
	<header class="supbar">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-3">
					<div class="site-logo">
						<img src="<?php echo URLPROYECTO; ?>vistas/img/level_up_blanco.png" alt="" style="max-width:170px; max-height:50px; margin-bottom:10px;">
					</div>
					<div class="nav-switch">
						<i class="fa fa-bars"></i>
					</div>
				</div>
				<div class="col-lg-9 col-md-9">
					<?php if(!isloggedin()){?>
					<a href="" class="site-btn header-btn botonlogin">Iniciar sesión</a>
					<?php }else{ ?>
					<a href="<?php echo URLBASE; ?>" class="site-btn header-btn">Volver al área personal</a>
					<?php } ?>
					<nav class="main-menu">
						<ul>
							<li><a href="<?php echo URLBASE; ?>">Inicio</a></li>
							<li><a href="<?php echo URLBASE; ?>/info/curso">Explorar cursos</a></li>
							<?php if(!isloggedin()){?>
							<li><a href="#" class="menuregistrarme">Registrarme</a></li>
							<?php }else{ ?>
							<li><a href="<?php echo URLBASE; ?>/info/usuario/miscompras/0">Mis compras</a></li>	
							<?php } ?>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</header>
	<!-- Header section end -->


	<!-- Page info -->
	<div class="page-info-section set-bg" data-setbg="<?php echo URLPROYECTO; ?>vistas/img/page-bg/1.jpg">
		<div class="container">
			<div class="site-breadcrumb">
				<a href="<?php echo URLBASE; ?>">Inicio</a>
				<span>Configuración</span>
			</div>
		</div>
	</div>
	<!-- Page info end -->

	<!-- course section -->
	<section class="course-section pb-0">
		<div class="course-warp">
			<div class="container" style="margin-top:50px;">				
				<h4>Configuración</h4>
			
				<form id="form-curso" method="post" enctype="multipart/form-data" action="<?php echo URLBASE; ?>/info/curso/<?php echo $datos['datos']['id']; ?>" style="margin-bottom:100px;">
				
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Texto home:</label>
						<input type="text" class="form-control" id="textohome" name="textohome" maxlength="256" placeholder="Ej: Acceda a los mejores cursos en línea" value="<?php echo $datos['datos']['textohome']; ?>">
						<div class="alert alert-danger" role="alert" id="alert_textohome" style="display:none;"></div>
					</div>
					
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Sub-texto home:</label>
						<textarea class="form-control" id="subtextohome" name="subtextohome" placeholder="El Centro de Educación Continuada de la Corporación Universitaria Americana pone a tu disposición una nueva serie de cursos, entre los que destacan los más solicitados por la comunidad académica graduada." rows="3"><?php echo $datos['datos']['subtextohome']; ?></textarea>
						<div class="alert alert-danger" role="alert" id="alert_subtextohome" style="display:none;"></div>
					</div>
					
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Texto Nuevos cursos:</label>
						<input type="text" class="form-control" id="textonuevoscursos" name="textonuevoscursos" maxlength="256"  placeholder="Ej: Nuevos cursos disponibles" value="<?php echo $datos['datos']['textonuevoscursos']; ?>">
						<div class="alert alert-danger" role="alert" id="alert_textonuevoscursos" style="display:none;"></div>
					</div>
					
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Sub Texto Nuevos cursos:</label>
						<textarea class="form-control" id="subtextonuevoscursos" name="subtextonuevoscursos" placeholder="Adquiere en línea cualquiera de los siguientes cursos, explora las categorías y encuentra el curso de tu interés." rows="3"><?php echo $datos['datos']['subtextonuevoscursos']; ?></textarea>
						<div class="alert alert-danger" role="alert" id="alert_subtextonuevoscursos" style="display:none;"></div>
					</div>
					
					
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Texto Únete ahora:</label>						
						<input type="text" class="form-control" id="textouneteahora" name="textouneteahora"  maxlength="256" placeholder="Ej: Únete ahora!" value="<?php echo $datos['datos']['textouneteahora']; ?>">
						<div class="alert alert-danger" role="alert" id="alert_textouneteahora" style="display:none;"></div>
					</div>
					
					<div class="form-group">
						<label for="exampleFormControlTextarea1">Sub Texto Únete ahora:</label>
						<textarea class="form-control" id="subtextouneteahora" name="subtextouneteahora" placeholder="Para acceder a los cursos, primero debes crear una cuenta. Una vez que te hayas registrado, podrás comprar y ver los contenidos, debes tener en cuenta que cada curso tiene una fecha de inicio programada." rows="3"><?php echo $datos['datos']['subtextouneteahora']; ?></textarea>
						<div class="alert alert-danger" role="alert" id="alert_subtextouneteahora" style="display:none;"></div>
					</div>
					
					<div class="form-group">
						<label for="exampleFormControlInput1">Teléfono Whastapp:</label>
						<input type="text" class="form-control" id="telefonowhastapp" name="telefonowhastapp"  maxlength="32" placeholder="Solo el número sin simbolos, EJ: 3014227345" value="<?php echo $datos['datos']['telefonowhastapp']; ?>">
						<div class="alert alert-danger" role="alert" id="alert_telefonowhastapp" style="display:none;"></div>
					</div>

					<div class="form-group">
						<button type="button" class="btn btn-primary" id="botonguardar">Guardar</button>
					</div>
				</form>
			</div>
		</div>
	</section>
	<!-- course section end -->

	<!-- footer section -->
	<footer class="footer-section pb-0">
		<div class="footer-top" style="display:none;">
			<div class="footer-warp">
				<div class="row">
					<div class="widget-item">
						<h4>Contact Info</h4>
						<ul class="contact-list">
							<li>1481 Creekside Lane <br>Avila Beach, CA 931</li>
							<li>+53 345 7953 32453</li>
							<li>yourmail@gmail.com</li>
						</ul>
					</div>
					<div class="widget-item">
						<h4>Engeneering</h4>
						<ul>
							<li><a href="">Applied Studies</a></li>
							<li><a href="">Computer Engeneering</a></li>
							<li><a href="">Software Engeneering</a></li>
							<li><a href="">Informational Engeneering</a></li>
							<li><a href="">System Engeneering</a></li>
						</ul>
					</div>
					<div class="widget-item">
						<h4>Graphic Design</h4>
						<ul>
							<li><a href="">Applied Studies</a></li>
							<li><a href="">Computer Engeneering</a></li>
							<li><a href="">Software Engeneering</a></li>
							<li><a href="">Informational Engeneering</a></li>
							<li><a href="">System Engeneering</a></li>
						</ul>
					</div>
					<div class="widget-item">
						<h4>Development</h4>
						<ul>
							<li><a href="">Applied Studies</a></li>
							<li><a href="">Computer Engeneering</a></li>
							<li><a href="">Software Engeneering</a></li>
							<li><a href="">Informational Engeneering</a></li>
							<li><a href="">System Engeneering</a></li>
						</ul>
					</div>
					<div class="widget-item">
						<h4>Newsletter</h4>
						<form class="footer-newslatter">
							<input type="email" placeholder="E-mail">
							<button class="site-btn">Subscribe</button>
							<p>*We don’t spam</p>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="footer-bottom" style="margin-top: 0;">
			<div class="footer-warp">
				<ul class="footer-menu">
					Cl 72 # 41C-64, Barranquilla, Atlántico<br>
					Centro de Eduación Continuada<br>
					<?php	if($datos['datos']['config']['telefonowhastapp']!=''){  ?>										
					<a href="https://api.whatsapp.com/send?phone=<?php echo $datos['datos']['config']['telefonowhastapp']; ?>&text=Hola%21%20Quisiera%20m%C3%A1s%20informaci%C3%B3n%20sobre%20Varela%202." class="float" target="_blank">
					<svg viewBox="0 0 32 32" class="whatsapp-ico" fill="#FFFFFF"><path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill-rule="evenodd"></path></svg>
					</a>
					<?php } ?>
					<a href="https://www.instagram.com/cec_americana" target="_blank"><i class='fab fa-instagram' style='font-size:24px; margin-right:10px; color:#BB29A6;'></i></a>
					<a href="https://www.facebook.com/cecamericana" target="_blank"><i class='fab fa-facebook' style='font-size:24px; color:#3b5998;'></i></a><br>
					
					<li><a href="#">Términos y Condiciones</a></li>					
					<li><a href="#">Privacidad</a></li>
				</ul>
				<div class="copyright"><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
				<img src="<?php echo URLPROYECTO; ?>vistas/img/americana_azul.png" style="max-width:240px;" alt=""><br><br>
&copy;<script>document.write(new Date().getFullYear());</script> Todos los derechos reservados | Realizado con <a href="https://colorlib.com" target="_blank">Colorlib</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></div>
			</div>
		</div>
	</footer> 
	<!-- footer section end -->


	<!--====== Javascripts & Jquery ======-->
	<script src="<?php echo URLPROYECTO; ?>vistas/js/jquery-3.2.1.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/bootstrap.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/mixitup.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/circle-progress.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/owl.carousel.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/main.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.min.js"></script>
	<script src="<?php echo URLPROYECTO; ?>vistas/js/interfaz.js"></script>
	<script src='<?php echo URLPROYECTO; ?>vistas/js/kitfontawesome.js'></script> 
	<?php
		include_once('comunbottom.php');				
	?>
	
	<script style="text/javascript">
		
		window.onload = function(){
			
			$('#botonguardar').on('click', function(){
											
				let itemsprerequisitos = '';
				$('textarea[name^="prerequisitoitem"]').each(function() {
					if(itemsprerequisitos!=''){
						itemsprerequisitos+='$$|';
					}
					itemsprerequisitos+=$(this).val();					
				});				
				
				$(this).attr('disabled', 'disabled');
				var datax = {
					textohome: $('#textohome').val(),
					subtextohome: $('#subtextohome').val(),
					textonuevoscursos: $('#textonuevoscursos').val(),
					subtextonuevoscursos: $('#subtextonuevoscursos').val(),
					textouneteahora: $('#textouneteahora').val(),
					subtextouneteahora: $('#subtextouneteahora').val(),
					telefonowhastapp: $('#telefonowhastapp').val()
				};
				$("div[id^='alert_']").each(function(i){  $(this).hide(); });									
				$.ajax({type: "PUT", url: "<?php echo URLBASE; ?>/info/api/sitio/0", data:JSON.stringify(datax), success: function(resp){				
					$('#botonguardar').attr('disabled', false);
					resp = jQuery.parseJSON(resp);			
					switch(resp.estado){
						case 'ok':														
							var pop = new Popup('popup', 'Listo', '<br>Datos guardados.<br><br>', 400, 2, function(){								
								pop.cerrar();
							});
						break;
						case 'error':
							var datos = resp.datos;							
							$.each(datos, function(index, dat){
								$('#alert_'+dat[0]).html(dat[1]).show();
							});
							if(datos.length>0){
								var pop = new Popup('popup', 'Errores', '<br><span style="font-color:red">Hay errores, por favor revisar</span>.<br><br>', 400, 2, function(){								
									pop.cerrar();
								});
							}
						break;
					}
				}});	
				
			});
			$('#botonagregarprerequisito').on('click', function(){
				$('#prerequisitolistado').append('<hr><textarea class="form-control" name="prerequisitoitem[]" rows="2"></textarea>');
				$('#prerequisitolistado').append('<div class="alert alert-danger" role="alert" id="alert_prerequisitoitem_'+contadorprereqisito+'" style="display:none;"></div>');
				contadorprereqisito++;
			});
			
		}
	</script>
	
</body>
</html>