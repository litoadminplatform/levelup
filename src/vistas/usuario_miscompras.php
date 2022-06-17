<!DOCTYPE html>
<html lang="en">
<head>
	<title>LevelUP Americana | Mis compras</title>
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
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/lib/datepicker_gigo/gijgo.min.css">
	
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
		
		.gj-datepicker-md [role=right-icon] {
			right: 50%;
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
				<span>Mis compras</span>
			</div>
		</div>
	</div>
	<!-- Page info end -->

	<!-- course section -->
	<section class="course-section pb-0">
		<div class="course-warp">
			<div class="container" style="margin-top:50px; margin-bottom:100px;">
				<table border="0" width="100%" style="width:100%;">
					<tr>
						<td>
							<?php
								if($datos['datos']['administrador']){
									?><h2>Compras de todos los usuarios</h2><?php
								}else{
									?><h2>Mis compras</h2><?php
								}	
							?>
						</td>
						<td align="right">
							<?php
								if($datos['datos']['administrador']){
									?><button class="site-btn sm" id="botoncupones">Cupones</button><?php
								}	
							?>
						</td>
					</tr>
				</table>
				<?php
					if(count($datos['datos']['facturas'])>0){
						?>
							<table class="table table-striped table-responsive">
								<thead>
									<tr>
										<th scope="col">Referencia</th>
										<th scope="col">Estado</th>
										<?php
											if($datos['datos']['administrador']){
												?>
													<th scope="col">Cliente</th>
													<th scope="col">Teléfono</th>													
												<?php
											}										
										?>
										<th scope="col">Curso</th>
										<th scope="col">Fecha volante</th>
										<th scope="col">Total</th>										
									</tr>
								</thead>
								<tbody>
									<?php
										
										foreach($datos['datos']['facturas'] as $cu){											
											$partes = explode('-', explode(' ', $cu['fechacarritogenerado'])[0]);
											?>
												<tr>
													<td><?php echo 'CEC-'.$cu['id'].'-'.$cu['idusuario'].'-'.$partes[0].$partes[1].$partes[2]; ?></td>
													<td>
														<?php 
															//(1: abierto carrito, 2 en proceso de pago (aun no rellenado el formulario de pago), 3 error transaccion, 4 pagado, 7 en espera de la respuesta de la pasarela de pagos (llega aqui despues del paso 2)		
															switch($cu['estado']){
																case 2:
																	?>Esperando pago<?php
																break;
																case 3:
																	?>Error en transacción<?php
																break;
																case 4:
																	?>Pagado<?php
																break;
																case 7:
																	?>En espera de respuesta de pasarela de pagos<?php
																break;
															} 
														?>
													</td>	
													<?php
														if($datos['datos']['administrador']){
															?>
																<td><?php echo $cu['firstname']; ?> <?php echo $cu['lastname']; ?></td>		
																<td><?php echo $cu['phone1']; ?></td>	
															<?php
														}										
													?>
													<td><?php echo $cu['fullname']; ?> (<?php echo $cu['idcurso']; ?>)</td>
													<td><?php echo $cu['fechacarritogenerado']; ?></td>
													<td>$<?php echo number_format($cu['total'], 0, ',', '.'); ?></td>													
												</tr>	
											<?php											
										}
										
									?>																
								</tbody>
							</table>
							<div class="col-lg-9 col-md-9">
								
								<?php
									if($datos['datos']['paginaanterior']!=-1){
										?>
											<button class="site-btn pasarpagina"  data-paginaver="<?php echo $datos['datos']['paginaanterior']; ?>">Anterior</button>&nbsp;
										<?php
									}
									if($datos['datos']['paginasiguiente']!=-1){
										?>
											<button class="site-btn pasarpagina" data-paginaver="<?php echo $datos['datos']['paginasiguiente']; ?>">Siguiente</button>
										<?php																				
									}	
								?>	
							</div>
						<?php
					}else{
						?><div style="min-height:250px;">No tienes ninguna compra.</div><?php
					}
				?>
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
					<a href="https://api.whatsapp.com/send?phone=<?php echo $datos['datos']['config']['telefonowhastapp']; ?>&text=Hola%21%20Quisiera%20m%C3%A1s%20informaci%C3%B3n%20sobre%20el%20curso%20de%20.." class="float" target="_blank">
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
	<script src='<?php echo URLPROYECTO; ?>vistas/lib/datepicker_gigo/gijgo.min.js'></script>
	
	
	<?php
		include_once('comunbottom.php');				
	?>
	
	<script style="text/javascript">
		var calendario = false;
		window.onload = function(){
			
			
			$(".pasarpagina").each(function(i){
				$(this).on('click', function(){
					var pagina = $(this).data("paginaver");
					document.location.href="<?php echo URLBASE;  ?>/info/usuario/miscompras/"+pagina;		
				});
			});
			
			$('#botoncupones').on('click', function(){				
				document.location.href="<?php echo URLBASE;  ?>/info/cupon";		
			});
			
			
		}
	</script>
	
</body>
</html>