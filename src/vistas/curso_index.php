<?php
	/*if(!isset($urlplantilla)){
		require "../../../../config.php";	
		$urlplantilla = $CFG->wwwroot.'/theme/'.$CFG->theme.'/americana/';
	}*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>LevelUP Americana | Cursos</title>
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
		.pinfo{
			font-size: calc(0.3em + 1vw) !important;
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

	<div class="supbar">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-3">
					<div class="site-logo">
						<a href="<?php echo URLBASE; ?>"><img src="<?php echo URLPROYECTO; ?>vistas/img/level_up_blanco.png" alt="" style="max-width:170px; max-height:50px; margin-bottom:10px;"></a>
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
							<?php
								if($datos['datos']['editardatoscurso']){
									?><li><a href="<?php echo URLBASE; ?>/info/sitio/formeditar">Configuración</a></li>	<?php
								}
							?>
							<?php } ?>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</div>

	<!-- Page Preloder -->
	<div id="preloder">
		<div class="loader"></div>
	</div>

	<!-- Header section -->
	<header class="header-section" style="display:none;">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-3">
					<div class="site-logo">
						<a href="<?php echo URLBASE; ?>"><img src="<?php echo URLPROYECTO; ?>vistas/img/level_up_blanco.png" alt="" style="max-width:170px;"></a>
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
				<a href="<?php echo $CFG->wwwroot; ?>">Inicio</a>
				<span>Cursos</span>
			</div>
		</div>
	</div>
	<!-- Page info end -->


	<!-- search section -->
	<section class="search-section ss-other-page" style="display:none;">
		<div class="container">
			<div class="search-warp">
				<div class="section-title text-white">
					<h2><span>Search your course</span></h2>
				</div>
				<div class="row">
					<div class="col-lg-10 offset-lg-1">
						<!-- search form -->
						<form class="course-search-form">
							<input type="text" placeholder="Course">
							<input type="text" class="last-m" placeholder="Category">
							<button class="site-btn btn-dark">Search Couse</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- search section end -->


	<!-- course section -->
	<section class="course-section pb-0" style="margin-bottom: 50px;">
		<div class="course-warp">
			<?php if($datos['datos']['tienecursosnuevos']){ ?>
			<ul class="course-filter controls">
				<li class="control active" data-filter="all">Todos</li>
				<?php
					foreach($datos['datos']['categorias'] as $ca){
						if(count($ca['cursos'])>0){
							?><li class="control" data-filter=".<?php echo $ca['namenormalizado']; ?>"><?php echo $ca['name']; ?></li><?php
						}
					}
				?>
			</ul>                                       
			<div class="row course-items-area">
				<?php
					foreach($datos['datos']['categorias'] as $ca){
						foreach($ca['cursos'] as $curso){
							?>
								<!-- course -->
								<div class="mix col-lg-3 col-md-4 col-sm-6 <?php echo $curso['categorianormalizado']; ?>">
									<div class="course-item">
										<a href="<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>">
											<div class="course-thumb set-bg" data-setbg="<?php echo ($curso['imagencursopequena']!='') ? URLBASE.$curso['imagencursopequena'] : (($curso['imagencurso']!='') ? URLBASE.$curso['imagencurso'] : URLPROYECTO.'vistas/img/courses/'.rand(1, 8).'.jpg'); ?>">
												<!--<?php if($curso['precio']!=''){ ?><div class="price">Precio: $<?php echo $curso['precio']; ?></div><?php } ?>-->
											</div>
										</a>
										<div class="course-info">
											<div class="course-text">
												<h5 style="cursor: pointer;" onclick="abrirUrl('<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>', '', false)"><?php echo $curso['fullname']; ?></h5>
												<p><?php if($curso['precio']!=''){ ?>$<?php echo $curso['precio']; ?> | <?php } ?><?php echo $curso['categoria']; ?></p>
												<div class="students">Inicia: <?php echo $curso['startdateesp']; ?> <?php if($datos['datos']['editardatoscurso']){ ?><span style="cursor: pointer; color:blue !important;" onclick="abrirUrl('<?php echo URLBASE; ?>/info/curso/formeditar/<?php echo $curso['id']; ?>', '', false)">Editar</span><?php } ?></div>
											</div>
											<?php
												if($curso['docentenombre']!=''){
													?>
														<div class="course-author">
															<?php
																if($curso['docentefoto']!=''){
																	?><div class="ca-pic set-bg" data-setbg="<?php echo $curso['docentefoto']; ?>"></div><?php
																}else{
																	?><div class="ca-pic set-bg" data-setbg="<?php echo URLPROYECTO; ?>vistas/img/avatardocente.jpg"></div><?php
																	
																}
															?>
															<p><?php echo $curso['docentenombre']; ?>, <span>Docente</span></p>
														</div>
													<?php
												}	
											?>
										</div>
									</div>
								</div>
							<?php							
						}
					}	
				?>	
			</div>
			<?php } ?>
			<?php if(count($datos['datos']['cursosdestacados'])>0){ ?>
			<div class="featured-courses">
				<?php
					$alternar = array('col-lg-6 offset-lg-6 pl-0', 'col-lg-6 pr-0');				
					foreach($datos['datos']['cursosdestacados'] as $key => $curso){
						$indexalternar = 1;
						if (($key % 2) == 0) {
							$indexalternar = 0;
						}						
						?>
							<div class="featured-course course-item">							
								<div class="course-thumb set-bg" style="cursor: pointer;" onclick="abrirUrl('<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>', '', false)" data-setbg="<?php echo ($curso['imagencursopequena']!='') ? URLBASE.$curso['imagencursopequena'] : (($curso['imagencurso']!='') ? URLBASE.$curso['imagencurso'] : URLPROYECTO.'vistas/img/courses/'.rand(1, 8).'.jpg'); ?>">
									<?php if($curso['precio']!=''){ ?><div class="price">Precio: $<?php echo $curso['precio']; ?></div><?php } ?>
								</div>
								<div class="row">
									<div class="<?php echo $alternar[$indexalternar]; ?>">
										<div class="course-info">
											<div class="course-text">
												<div class="fet-note">Curso destacado</div>
												<h5 style="cursor: pointer;" onclick="abrirUrl('<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>', '', false)"><?php echo $curso['fullname']; ?></h5>
												<p><?php echo $curso['descripcioncorta']; ?></p>
												<div class="students"><?php if($curso['startdateesp']!=''){ ?>Inicia el día <?php echo $curso['startdateesp']; } ?></div>
											</div>
											<div class="course-author">
												<?php if($curso['docentenombre']!=''){ ?>
												<div class="ca-pic set-bg" data-setbg="<?php if($curso['docentefoto']!=''){ echo $curso['docentefoto']; }else{ ?><?php echo URLPROYECTO; ?>vistas/img/avatardocente.jpg<?php } ?>"></div>
												<p><?php echo $curso['docentenombre']; ?>, <span>Docente</span></p>
												<?php  }?>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php
					}
				?>
			
			
				
				
			</div>
			<?php } ?>
		</div>
	</section>
	<!-- course section end -->


	<!-- banner section -->
	<?php if(!isloggedin()){?>
	<section class="banner-section spad" <?php if(isloggedin()){ ?> style="display:none;" <?php } ?>>
		<div class="container">
			<div class="section-title mb-0 pb-2">
				<h2><?php echo $datos['datos']['config']['textouneteahora']; ?></h2>
				<p class="pinfo"><?php echo $datos['datos']['config']['subtextouneteahora']; ?></p>
			</div>
			<div class="text-center pt-5">
				<a href="#" class="site-btn menuregistrarme">Registrarme</a>
			</div>
		</div>
	</section>
	<?php  } ?>
	<!-- banner section end -->


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
					<a href="https://api.whatsapp.com/send?phone=<?php echo $datos['datos']['config']['telefonowhastapp']; ?>&text=Hola%21%20Quisiera%20m%C3%A1s%20informaci%C3%B3n%20sobre%20el%20curso%20de%20.." class="float" target="_blank">
					<svg viewBox="0 0 32 32" class="whatsapp-ico" fill="#FFFFFF"><path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill-rule="evenodd"></path></svg>
					</a>
					<?php } ?><a href="https://api.whatsapp.com/send?phone=3022724786" target="_blank"><i class='fab fa-whatsapp' style='font-size:24px; margin-right:10px; color:#00BB2D;'></i></a>
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
	<script src="<?php echo URLPROYECTO; ?>vistas/js/interfaz.js"></script>
	<script src='<?php echo URLPROYECTO; ?>vistas/js/kitfontawesome.js'></script> 
	<?php
		include_once('comunbottom.php');				
	?>  
</body>
</html>