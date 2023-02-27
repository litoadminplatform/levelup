<!DOCTYPE html>
<html lang="en">
<head>
	<title>Level Up | <?php echo $datos['datos']['fullname']; ?></title>
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
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/style.css?v=6"/>
	<link rel="stylesheet" href="<?php echo URLPROYECTO; ?>vistas/css/interfaz.css"/>	

	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<style type="text/css">
		.page-info-section {
			height: 0px; /*290 antes*/
			margin-top: 70px;
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
		
		
		@media (min-width: 767px) {
			.imageninferior{
				display:none;
			}	
			.page-info-section {
				height: 100% !important;
			}		
		}
		
	</style>
</head>
<body>
	<div id="formsubmit" style="display:none;"></div>
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
	<div class="page-info-section set-bg" data-setbg="<?php if($datos['datos']['imagencurso']==''){ echo URLPROYECTO; ?>vistas/img/page-bg/2.jpg<?php }else{  echo URLBASE.$datos['datos']['imagencurso']; } ?>">
		<div class="container">
			<!--<div class="site-breadcrumb">
				<a href="<?php echo URLBASE; ?>">Inicio</a>
				<a href="<?php echo URLBASE; ?>/info/curso">Cursos</a>
				<span>Detalles de curso</span>
			</div>-->
		</div>
	</div>
	<!-- Page info end -->


	<!-- single course section -->
	<section class="single-course spad pb-0">
		<div class="container">
			<div class="course-meta-area">
				<div class="row">
					<div class="col-lg-10 offset-lg-1">
						<?php
							if($datos['datos']['destacado']=='1'){
								?><div class="course-note">Curso destacado</div><?php
							}							
						?>
						<h3><?php echo $datos['datos']['fullname']; ?></h3>
						<div class="course-metas">
							<div class="course-meta">
								<div class="course-author">
								
									<?php
										if($datos['datos']['docentefoto']!=''){
											?><div class="ca-pic set-bg" data-setbg="<?php echo $datos['datos']['docentefoto']; ?>"></div><?php
										}else{
											?><div class="ca-pic set-bg" data-setbg="<?php echo URLPROYECTO; ?>vistas/img/avatardocente.jpg"></div><?php

										}					
									?>
								
									
									<!--<h6>Instructor</h6>-->
									<?php
										if($datos['datos']['docentenombre']!=''){
											?><p><?php echo $datos['datos']['docentenombre']; ?>, <span>Docente</span></p><?php
										}else{
											?><p>Docente <span>sin especificar</span></p><?php
										}
									?>
									
								</div>
							</div>
							<div class="course-meta">
								<div class="cm-info">
									<h6>Categoría</h6>
									<p><?php echo $datos['datos']['categorianombre']; ?></p>
								</div>
							</div>
							
							<?php
								if($datos['datos']['intensidadhoraria']!=''){
									?>									
										<div class="course-meta">
											<div class="cm-info">
												<h6>Duración</h6>
												<p><?php echo $datos['datos']['intensidadhoraria']; ?></p>
											</div>
										</div>
									<?php
								}
							?>	
							<div class="course-meta">
								<div class="cm-info">
									<h6>Fecha de inicio</h6>
									<p><?php echo $datos['datos']['startdateesp']; ?></p>
								</div>
							</div>
							<!--<div class="course-meta">
								<div class="cm-info">
									<h6>Valoraciones</h6>
									<p>2 Val <span class="rating">
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star is-fade"></i>
									</span></p>
								</div>
							</div>-->
						</div>
						<?php if($datos['datos']['precio']!=''){ ?><a href="#" class="site-btn price-btn">Precio: $<?php echo $datos['datos']['precio']; ?></a><?php } ?>
						<?php if($datos['datos']['precio']!=''){ ?><a href="#" class="site-btn buy-btn" id="botoncomprar" data-idcurso="<?php echo $datos['datos']['id']; ?>">Comprar este curso</a><?php } ?>
					</div>
				</div>
			</div>
			<?php
				if($datos['datos']['imagencurso']!=''){
					?><img class="imageninferior" src="<?php echo URLBASE; ?><?php echo $datos['datos']['imagencurso']; ?>" alt="" class="course-preview" style="width:100%;"><?php
				}else{
					?><img class="imageninferior" src="<?php echo URLPROYECTO; ?>vistas/img/courses/single.jpg" alt="" class="course-preview"><?php
				}
			?>
			
			<div class="row" style="padding-top:37px;">
				<div class="col-lg-10 offset-lg-1 course-list">
					
					<?php 
						if($datos['datos']['descripcionlarga']!=''){
							?>
								<div class="cl-item">
									<h4>Descripción del curso</h4>
									<p><?php echo $datos['datos']['descripcionlarga']; ?></p>
								</div>
							<?php
						}							
					?>						
					
					<?php 
						if($datos['datos']['dirigidoa']!=''){
							?>
								<div class="cl-item">
									<h4>Dirigido a</h4>
									<p><?php echo $datos['datos']['dirigidoa']; ?></p>
								</div>
							<?php
						}							
					?>
					
					
					<?php 
						if($datos['datos']['prerequisito']!='' || count($datos['datos']['itemsprerequisito'])>0){
							?>
								<div class="cl-item">
									<h4>Prerequisitos</h4>
									<?php
										if($datos['datos']['prerequisito']!=''){
											?><p><?php echo $datos['datos']['dirigidoa']; ?></p><?php
										}
									?>
									<?php
										if(count($datos['datos']['itemsprerequisito'])>0){
											?><ul style="list-style:circle; margin-left: 18px; margin-top:10px;"><?php
												foreach($datos['datos']['itemsprerequisito'] as $pre){
													?><li><?php echo $pre; ?></li><?php	
												}
											?></ul><?php
										}
									?>
								</div>
							<?php
						}							
					?>
																								
					<?php 
						if($datos['datos']['acercadelinstructor']!=''){
							?>
								<div class="cl-item">
									<h4>Acerca del docente</h4>
									<p><?php echo $datos['datos']['acercadelinstructor']; ?></p>
								</div>
							<?php
						}							
					?>
					
					<?php 
						if($datos['datos']['horario']!=''){
							?>
								<div class="cl-item">
									<h4>Horario</h4>
									<p><?php echo $datos['datos']['horario']; ?></p>
								</div>
							<?php
						}							
					?>

				</div>
			</div>
		</div>
	</section>
	<!-- single course section end -->


	<!-- Page -->
	<?php if($datos['datos']['tieneotroscursos']){ ?>
	<section class="realated-courses spad">
		<div class="course-warp">
			<h2 class="rc-title">Otros cursos</h2>
			<div class="rc-slider owl-carousel">
				<?php
					foreach($datos['datos']['categorias'] as $ca){
						foreach($ca['cursos'] as $curso){							
							?>
							<!-- course -->								
							<div class="course-item">
								<a href="<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>">
									<div class="course-thumb set-bg" data-setbg="<?php if($curso['imagencurso']!=''){ echo URLBASE.''.$curso['imagencurso']; }else{ echo URLPROYECTO; ?>vistas/img/courses/<?php echo rand(1, 8); ?>.jpg<?php } ?>">
										<?php if($curso['precio']!=''){ ?><div class="price">Precio: $<?php echo $curso['precio']; ?></div><?php } ?>
									</div>
								</a>
								<div class="course-info">
									<div class="course-text">
										<h5 style="cursor: pointer;" onclick="abrirUrl('<?php echo URLBASE; ?>/info/curso/<?php echo $curso['id']; ?>/<?php echo $curso['nombreamigable']; ?>', '', false)"><?php echo $curso['fullname']; ?></h5>
										<p><?php echo $curso['categoria']; ?></p>
										<div class="students">Inicia: <?php echo $curso['startdateesp']; ?></div>
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
							<!-- course -->
							<?php
							
						}
					}						
				?>
			</div>
		</div>
	</section>
	<?php } ?>
	<!-- Page end -->


	<!-- banner section -->
	<?php if(!isloggedin()){?>
	<section class="banner-section spad">
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
	<?php } ?>
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
	<script src="<?php echo URLPROYECTO; ?>vistas/js/interfaz.js"></script>
	<script src='<?php echo URLPROYECTO; ?>vistas/js/kitfontawesome.js'></script> 
	<?php
		include_once('comunbottom.php');				
	?>
	<script style="text/javascript">
		function postOnload(){			
			$('#botoncomprar').on('click', function(e){
				e.preventDefault();				
				let idcurso = $(this).data('idcurso');				
				//$(this).attr('disabled', 'disabled');
				<?php
					if($datos['datos']['tienesesion']){
						?>
							comprarCurso(idcurso, false);
						<?php
					}else{
						?>
							var pop = new Popup('popup', 'Comprar curso', '<br>Por favor inicia sesión y vuelve aquí para comprar el curso, o regístrate si aún no tienes cuenta.<br><br>', 500, 3, function(){								
								pop.cerrar();
							});
							
							pop.changeButton(2, 'Iniciar sesión', function(){
								pop.cerrar();
								iniciarSesion('', '');
							});
							
							
							pop.changeButton(1, 'Registrarme', function(){
								pop.cerrar();
								registrate();
							});
							
							pop.setBoton('Cerrar', 'botonregistrar', 'botonregistrar', 'botoncancelar', function(){
								pop.cerrar();
							});	
							
							var urlactual = window.location.href;
							$.ajax({type: "POST", url: "<?php echo URLBASE; ?>/info/api/sitio/setredirect/"+idcurso, data:'urlredireccionar='+urlactual, success: function(resp3){				
								resp3 = jQuery.parseJSON(resp3);				
								switch(resp3.estado){
									case 'ok':
									break;
								}
							}});
							
						<?php
					}
				?>				
			});
		}
		
		
		function comprarCurso(idcurso, autocheck){
			var inyectar = '<table border="0">';
				inyectar+='<tr>';
					inyectar+='<td style="width:30%;">';
						<?php
							if($datos['datos']['imagencurso']!=''){
								?>
									inyectar+='<img src="<?php echo URLBASE; ?><?php echo $datos['datos']['imagencurso']; ?>" alt="" style="width:100%;">';
								<?php
							}else{
								?>
									inyectar+='<img src="<?php echo URLPROYECTO; ?>vistas/img/courses/single.jpg" alt="">';									
								<?php
							}
						?>
						
						
					inyectar+='</td>';
					inyectar+='<td style="width:70%; padding-left: 10px; vertical-align: top;">';
						inyectar+='<span style="font-weight:bold;">Curso: </span><?php echo $datos['datos']['fullname']; ?><br><span style="font-weight:bold;">Precio:</span> $<?php echo $datos['datos']['precio']; ?><br><span style="font-weight:bold;">Fecha de inicio: </span><?php echo $datos['datos']['startdateesp']; ?>';
					inyectar+='</td>';					
				inyectar+='</tr>';
			inyectar+='</table>';
			
			var pop = new Popup('popup', 'Comprar curso', '<br>'+inyectar+'<br><?php echo $datos['datos']['nombreusuario']; ?>, Al presionar "Ir al pago" se te reedireccionará a la pasarela de pagos PayU, una vez completes tu compra PayU te hará volver aquí, y tan pronto como se notifique el pago aparecerás matriculado en el curso automáticamente, en <span style="font-weight:bold;">"Mis compras"</span> podrás ver el estado del volante de pago virtual.<hr><br><center>Si tienes algún cupón de descuento colócalo aquí:</center><center><input type="text" name="cupon" id="cupon" style="width:50%;" max-length="32" /></center><center><span class="badge badge-danger" id="error-compra"></center></span><br><br>', 650, 3, function(){															
			});				
			pop.changeButton(2, 'Ir al pago', function(){
				
				var cupon = $('#cupon').val();
				var data = {	
					cantidad: "1",
					cupon: cupon
				};
				
				$('#popuppopbutac, #popuppopbutca, #cupon').attr('disabled', 'disabled');
				if(autocheck){
					pop.setTitulo('Generando volante de pago');	
					pop.setMensaje('<br><br><center><img src="<?php echo URLPROYECTO; ?>vistas/img/cargando.gif" style="max-width:240px;" alt=""></center><br>');
				}
				
				
				$.ajax({type: "PUT", url: "<?php echo URLBASE; ?>/info/api/carrito/"+idcurso, data:JSON.stringify(data), success: function(resp3){				
					resp3 = jQuery.parseJSON(resp3);				
					switch(resp3.estado){
						case 'ok': 

							$('#formsubmit').html(resp3.datos);
							setTimeout(function(){ $('#formpasarela').submit(); }, 500);
					
						break;
						case 'error':	
							$('#popuppopbutac, #popuppopbutca, #cupon').attr('disabled', false);
							var titulo = 'Error';
							var mensaje  = '';							
							switch(resp3.codigo){
								case 'ya-esta-matriculado':
									titulo = 'Ya lo tienes!';
									mensaje = 'Ya tienes el curso matriculado, por favor dirígete a tu Área personal.';
								break;
								case 'sin-sesion':		//agregado
									mensaje = 'Se ha cerrado la sesión, por favor inicie sesión e intente de nuevo agregar al carrito';
								break;
								case 'no-disponible':		//agregado
									mensaje = 'El producto no se encuentra disponible para la compra en este momento.';
									$('#errorcarrito').html('Estado invalido.').show();
								break;
								case 'sin-precio':		//agregado
									mensaje = 'El producto no tiene un precio establecido.';
								break;
								case 'el-curso-ya-inicio-o-no-tiene-fecha-de-inicio':	//agregado
									mensaje = 'El curso ya inició, o no tiene fecha de inicio.';
								break;
								case 'usuario-invalido':		//agregado
									mensaje = 'El usuario está inválidado para adquirir cursos.';
								break;
								case 'cupon-caracteres-invalidos':		//ok
									$('#error-compra').html('Cupón con caracteres inválidos.').show();
								break;
								case 'cupon-novalido':	//ok
									$('#error-compra').html('El cupón no existe y no es válido.').show();									
								break;
								case 'cupon-vencido':
									$('#error-compra').html('El cupón está vencido, no es posible comprar con este cupón.').show();									
								break;
								case 'redencion-cupon':
									var datos = resp3.datos;
									var pop2 = new Popup('popup', 'Cupón aplicado', '<br><center><img src="<?php echo URLPROYECTO; ?>vistas/img/cupon.png" style="max-width:240px;" alt=""><br><span style="font-size:24px;">Has aplicado un cupón del <span style="color:green; font-weight:bold;">'+datos.porcentajedescuento+'%</span> a la compra de este curso!<br><span style="text-decoration:line-through;">$'+datos.precio+'</span> >  <span style="font-weight:bold; color:green;;">$'+datos.nuevoprecio+'</span></span></center><br><br>', 650, 2, function(){
										//pop2.cerrar();
									});
									pop.changeButton(2, 'Genial!', function(){
										comprarCurso(idcurso, true);
									});	
								break;
							}							
							if(mensaje!=''){ //popup
								var pop2 = new Popup('popup', titulo, '<br>'+mensaje+'<br><br>', 650, 2, function(){
									pop2.cerrar();
								});
							}							
						break;
					}
				}});
			});
			if(autocheck){		
				
				$('#popuppopbutac').trigger("click");				
			}
		}
		
		
	</script>
</body>
</html>