<?php

	//la respuesta que llega es esta: https://cec.americana.edu.co/themes/moove/americana/src/pasarelarespuesta.php?merchantId=500238&merchant_name=Test+PayU+Test&merchant_address=Av+123+Calle+12&telephone=7512354&merchant_url=&transactionState=4&lapTransactionState=APPROVED&message=Aprobada&referenceCode=54879761&reference_pol=6404910&transactionId=12c0b8c7-69c5-4361-bf98-718de8a8e3a0&description=Pago+de+la+factura+54879761+en+nikebarranquilla+en+uokki.com&trazabilityCode=00000000&cus=00000000&orderLanguage=es&extra1=&extra2=&extra3=&polTransactionState=4&signature=b54f77548c52995abac7c612288fc086&polResponseCode=1&lapResponseCode=APPROVED&risk=.00&polPaymentMethod=250&lapPaymentMethod=VISA&polPaymentMethodType=2&lapPaymentMethodType=CREDIT_CARD&installmentsNumber=1&TX_VALUE=230000.00&TX_TAX=31724.00&currency=COP&lng=es&pseCycle=&buyerEmail=chicaejemplo2011%40hotmail.com&pseBank=&pseReference1=&pseReference2=&pseReference3=&authorizationCode=00000000&TX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE_RETURN_BASE=.00&processingDate=2015-01-05				
	
	//otra respuesta: https://cec.americana.edu.co/theme/moove/americana/src/pasarelarespuesta.php?merchantId=508029&merchant_name=Test+PayU+Test+comercio&merchant_address=Av+123+Calle+12&telephone=7512354&merchant_url=http%3A%2F%2Fpruebaslapv.xtrweb.com&transactionState=4&lapTransactionState=APPROVED&message=APPROVED&referenceCode=CEC-4-2-20220330&reference_pol=1403576955&transactionId=861d155f-bb3d-4ffc-8b64-7495a7821b0a&description=TALLER+DE+LIQUIDACI%C3%93N+DE+N%C3%93MINA+Volante+id%3A+4&trazabilityCode=CRED+-+666767730&cus=CRED+-+666767730&orderLanguage=es&extra1=&extra2=&extra3=&polTransactionState=4&signature=ed463048dea4219024b80dee8d6bca5b&polResponseCode=1&lapResponseCode=APPROVED&risk=&polPaymentMethod=10&lapPaymentMethod=VISA&polPaymentMethodType=2&lapPaymentMethodType=CREDIT_CARD&installmentsNumber=1&TX_VALUE=200000.00&TX_TAX=.00&currency=COP&lng=es&pseCycle=&buyerEmail=admin%40example.com&pseBank=&pseReference1=&pseReference2=&pseReference3=&authorizationCode=651131&TX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE_RETURN_BASE=.00&processingDate=2022-03-30
	
	ob_start();
	
	require "../.././../../config.php";
	global $USER, $SESSION, $PAGE, $CFG, $DB, $OUTPUT;
	include_once('../defines.php');
	
	include_once('../autoload.php');	
	$conexion = new \clases\Conexion();	
	$controladorcarrito = new \controladores\ControladorCarrito($conexion);		
	$data = array();	
	foreach($_GET as $campop => $valorp){
		$data[$campop.''] = $valorp;
	}
		
	$res = $controladorcarrito->pasarelaRespuesta($data, false);		//el true signfica que está en modo pruebas	
	switch($res['estado']){
		case 'ok':	
			ob_clean();
			header('Location: '.URLBASE.'/info/usuario/miscompras/0');  //   /'.$res['datos'].'/escanear')
		break;
		case 'error':
			switch($res['codigo']){
				case 'error-en-firma-digital':
					?>Error al comparar la firma digital para la compra <?php echo $res['datos']; ?>, por favor avisarle de este error a la administración de LevelUp Americana.<?php
				break;
				case 'faltan-datos':
					?><span style="color:red;">Faltan datos, por favor avisarle de este error a la administración de LevelUp Americana</span>. <?php
					//print_r($res['datos']);
				break;				
				default:
					?>Error desconocido al momento de recibir los datos de la pasarela de pagos.<?php
				break;
			}
		break;
	}
?>