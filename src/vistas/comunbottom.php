<script style="text/javascript">

	window.onload = function() {
				
		$('.botonlogin').on('click', function(e){
			e.preventDefault();
			iniciarSesion('', '');
		});
		$('.menuregistrarme').on('click', function(e){
			e.preventDefault();
			registrate();
		});
		
		if (typeof postOnload !== 'undefined'){
			postOnload();
		}
	};
	
	
	/*
		Abre una url:
		dataadicional: si es diferente de vacio se creara un popup mostrando el texto mientras se reedirige a la url.
	*/
	function abrirUrl(laurl, dataadicional, nuevaventana){
		if(dataadicional!=''){
			var pop = new Popup('popup', '', '<center><img src="<?php echo URLPROYECTO; ?>vistas/pix/cargando.gif"></center><br><center>'+dataadicional+'.</center><br>', 300, 0, function(){
			});				
		}
		if(nuevaventana==false){				
			document.location.href=laurl;
		}else{
			window.open(laurl, '_blank');
		}
	}
	
	var iniciando = false;
	function registrate(){
		var inyectar = '';
		inyectar+='<form id="formregistro" class="signup-form counter_form_content d-flex flex-column align-items-center justify-content-center" action="#" style="margin-bottom:25px;">';
			inyectar+='<h4>Regístrate</h4>';
			
			inyectar+='<p>Nombres <span style="color:blue; font-size:10px;">(Aparecerá en certificados)</span></p>';
			inyectar+='<input type="text" id="nombres" name="nombres" class="campologin" placeholder="" required="required" maxlength="64" style="text-transform: uppercase;">';
			
			inyectar+='<p>Apellidos <span style="color:blue; font-size:10px;">(Aparecerá en certificados)</span></p>';
			inyectar+='<input type="text" id="apellidos" name="apellidos" class="campologin" placeholder="" required="required" maxlength="64" style="text-transform: uppercase;">';
			
			inyectar+='<p>Correo electrónico</p>';
			inyectar+='<input type="text" id="correo" name="correo" class="campologin" placeholder="" required="required" maxlength="64" style="text-transform: lowercase;">';
						
			inyectar+='<p>Número de identificación</p>';
			inyectar+='<input type="text" id="identificacion" name="identificacion" class="campologin" placeholder="" required="required"  minlength="6" maxlength="32" style="text-transform: lowercase;">';
						
			inyectar+='<p>Ciudad <span style="color:blue; font-size:10px;">(Para las facturas en las compras)</span></p>';
			inyectar+='<input type="text" id="ciudad" name="ciudad" class="campologin" placeholder="" required="required" maxlength="32" style="text-transform: uppercase;">';			
						
			inyectar+='<p>Dirección <span style="color:blue; font-size:10px;">(Para las facturas en las compras)</span></p>';
			inyectar+='<input type="text" id="direccion" name="direccion" class="campologin" placeholder="" required="required" maxlength="128" style="text-transform: uppercase;">';
			
			inyectar+='<p>Teléfono móvil</p>';
			inyectar+='<input type="text" id="telefono" name="telefono" class="campologin" placeholder="" required="required"  minlength="10" maxlength="10" style="text-transform: lowercase;">';
									
			inyectar+='<input type="hidden" id="logintoken" name="logintoken" value="<?php echo s(\core\session\manager::get_login_token()); ?>" />';				
			inyectar+='<center><img id="cargangologin" src="<?php echo URLPROYECTO; ?>vistas/pix/cargando.gif" style="margin-top:15px; visibility:hidden;" /></center>';
			inyectar+='<center><span id="registroerror" class="label-danger" style="display:none;">Error al registrarse, revise los campos.</span></center>';
		inyectar+='</form>';
		var pop = new Popup('popup', '', inyectar, 550, 3, false);
		pop.changeButton(1, 'Cerrar', function(){ pop.cerrar(); });
		pop.changeButton(2, 'Registrarme', function(){
			
			if($('#nombres').val()!='' && $('#apellidos').val()!='' && $('#correo').val()!='' && $('#identificacion').val()!='' && $('#telefono').val()!=''){
				iniciando = true;
				$('#registroerror').hide();
				$('#cargangologin').css('visibility', 'visible');
				$('#popuppopbutac').attr('disabled', 'disabled');
				$('#popuppopbutca').attr('disabled', 'disabled');			
				var serializado = $('#formregistro').serialize();
				$('#nombres, #apellidos, #correo, #identificacion, #telefono').attr('disabled', 'disabled');
				$.ajax({type: "POST", url: "<?php echo URLBASE; ?>/info/api/usuario", data:serializado, success: function(resp){
					$('#cargangologin').css('visibility', 'hidden');
					resp = jQuery.parseJSON(resp);
					switch(resp.estado){
						case 'ok':
							var datos =  resp.datos;
							var mensaje = '';
							mensaje+='<div class="titulologin colorsecundariofuente" style="margin-top:20px;">Cuenta creada!</div>';
							mensaje+='Se ha creado tu cuenta en LevelUP Americana, para iniciar sesión utiliza los siguientes datos, no pierdas estos datos:<br><br>';
							mensaje+='<span style="font-weight:bold;">Nombre de usuario:</span> '+datos.username+'<br>';
							mensaje+='<span style="font-weight:bold;">Contraseña:</span> '+datos.password+'<br>';
							pop.setMensaje(mensaje);
							$('#popuppopbutca, #popuppopbutac').attr('disabled', false);
							pop.quitaBoton(1);
							pop.changeButton(2, 'Listo!', function(){
								iniciarSesion('', '');
							});
						break;
						case 'error':
							$('#nombres, #apellidos, #correo, #identificacion, #telefono').attr('disabled', false);
							  //<a href="<?php echo URLBASE; ?>/login/forgot_password.php?new=true" style="float:right; margin-left:15px;">Forgot password?</a>							
							$('#popuppopbutca, #popuppopbutac').attr('disabled', false);
							switch(resp.codigo){
								case 'errorestados':
									var datos =  resp.datos;
									if(datos.nombrese!=''){
										$('#registroerror').html(datos.nombrese).show();
									}else{
										if(datos.apellidose!=''){
											$('#registroerror').html(datos.apellidose).show();
										}else{
											if(datos.emaile!=''){
												$('#registroerror').html(datos.emaile).show();
											}else{
												if(datos.identificacione!=''){
													$('#registroerror').html(datos.identificacione).show();
												}else{
													if(datos.ciudade!=''){
														$('#registroerror').html(datos.ciudade).show();
													}else{
														if(datos.direccione!=''){
															$('#registroerror').html(datos.direccione).show();
														}else{													
															if(datos.telefonoe!=''){
																$('#registroerror').html(datos.telefonoe).show();
															}else{
																if(datos.errorgeneral!=''){
																	$('#registroerror').html(datos.errorgeneral).show();
																}else{	
																	$('#registroerror').html('Error desconocido al registarse, por favor comunicar esto al sistema de soporte.').show();	
																}
															}
														}
													}
												}	
											}	
										}
									}
								break;
								case 'fallocreacion':
									//aqui no se sabe por que no se creo
								break;								
							}
						break;
					}				
				}});
			}		
		});
	}
	function iniciarSesion(username, password){
		if(username=='' && password==''){
			var inyectar = '';
			inyectar+='<form id="formlogin" class="signup-form counter_form_content d-flex flex-column align-items-center justify-content-center" action="#" style="margin-bottom:25px;">';
				//inyectar+='<div class="titulologin colorsecundariofuente" style="margin-top:20px;">Iniciar sesión</div>';
				inyectar+='<h4>Iniciar sesión</h4><br>';
				inyectar+='<input type="text" id="username" name="username" class="campologin" placeholder="Nombre de ususario" required="required">';
				inyectar+='<input type="password" id="password" name="password" class="campologin" placeholder="Contraseña" required="required">';
				inyectar+='<input type="hidden" id="logintoken" name="logintoken" value="<?php echo s(\core\session\manager::get_login_token()); ?>" />';
				inyectar+='<div style="width:100%; margin-top:15px; text-align:right; font-size:18px; font-weight:bold;"><a href="<?php echo URLBASE; ?>/login/forgot_password.php?new=true" style="color:<?php if(isset($disenoempresa) && $disenoempresa['colorprincipal']!=''){ echo $disenoempresa['colorprincipal']; }else{ ?>#FF367D;<?php } ?>">Olvidó la contraseña?</a></div>';
				inyectar+='<center><img id="cargangologin" src="<?php echo URLPROYECTO; ?>vistas/pix/cargando.gif" style="margin-top:15px; visibility:hidden;" /></center>';
				inyectar+='<center><span id="loginerror" class="label-danger" style="display:none;">Nombre de usuario o contraseña incorrectos</span></center>';
			inyectar+='</form>';
			var pop = new Popup('popup', '', inyectar, 550, 3, false);
			pop.changeButton(1, 'Cerrar', function(){ pop.cerrar(); });
			pop.changeButton(2, 'Entrar', function(){
				iniciarSesion(false);
			});
			$('#password').bind('keyup', function(event){
				var keycode = (event.keyCode ? event.keyCode : event.which);
				if(keycode == '13') {  //enter
					if(!iniciando){
						iniciarSesion(false);
					}
				}
			});
			//Se edita el boton de login.
			var cont = 0;
			$('#popuphijo button').each(function(i){				
				if(cont==0){
					$(this).removeClass('btn-primary');
					$(this).addClass('comment_button');
					$(this).css('height', '38px');
					$(this).css('margin-top', '0px');
					$(this).css('text-transform', 'none');
					cont++;;	
				}				
			});
			setLoginToken();
		}else{
			if($('#username').val()!='' && $('#password').val()!=''){
				iniciando = true;
				$('#loginerror').hide();
				$('#cargangologin').css('visibility', 'visible');
				$('#popuppopbutac').attr('disabled', 'disabled');
				$('#popuppopbutca').attr('disabled', 'disabled');			
				var serializado = $('#formlogin').serialize();
				$('#username').attr('disabled', 'disabled');
				$('#password').attr('disabled', 'disabled');
				$.ajax({type: "POST", url: "<?php echo URLBASE; ?>/login/index.php", data:'a=iniciarSesion&'+serializado, success: function(resp){
					var falloinicio = resp.indexOf('id="login"');  //antes estaba esto resp.indexOf("class=\"loginerrors\"");
					if(falloinicio!=-1){
						iniciando = false;
						$('#cargangologin').css('visibility', 'hidden');
						$('#password').val('');
						$('#loginerror').html('Nombre de usuario o contraseña incorrectos.');  //<a href="<?php echo URLBASE; ?>/login/forgot_password.php?new=true" style="float:right; margin-left:15px;">Forgot password?</a>
						$('#loginerror').show();
						//$('#popuppopbutac').attr('disabled', false);
						$('#popuppopbutca').attr('disabled', false);
						$('#username, #password').removeAttr('disabled');
						setLoginToken();
					}else{
						document.location.href="<?php echo URLBASE; ?>/my";
					}
				}});
			}
		}		
	}
	
	function setLoginToken(){
		$('#popuppopbutac').attr('disabled', 'disabled');
		$.get("<?php echo URLBASE; ?>/info/api/sitio/gettokenlogin", "", function(resp){
			resp = jQuery.parseJSON(resp);
			switch(resp.estado){
				case 'ok':
					$('#logintoken').val(resp.datos);
					$('#popuppopbutac').attr('disabled', false);
				break;
				case 'error':
				break;
			}
		});
	}
</script>