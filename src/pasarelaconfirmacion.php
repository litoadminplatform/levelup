<?php

	//la respuesta que llega es esta: https://cec.americana.edu.co/themes/moove/americana/src/pasarelarespuesta.php?merchantId=500238&merchant_name=Test+PayU+Test&merchant_address=Av+123+Calle+12&telephone=7512354&merchant_url=&transactionState=4&lapTransactionState=APPROVED&message=Aprobada&referenceCode=54879761&reference_pol=6404910&transactionId=12c0b8c7-69c5-4361-bf98-718de8a8e3a0&description=Pago+de+la+factura+54879761+en+nikebarranquilla+en+uokki.com&trazabilityCode=00000000&cus=00000000&orderLanguage=es&extra1=&extra2=&extra3=&polTransactionState=4&signature=b54f77548c52995abac7c612288fc086&polResponseCode=1&lapResponseCode=APPROVED&risk=.00&polPaymentMethod=250&lapPaymentMethod=VISA&polPaymentMethodType=2&lapPaymentMethodType=CREDIT_CARD&installmentsNumber=1&TX_VALUE=230000.00&TX_TAX=31724.00&currency=COP&lng=es&pseCycle=&buyerEmail=chicaejemplo2011%40hotmail.com&pseBank=&pseReference1=&pseReference2=&pseReference3=&authorizationCode=00000000&TX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE=.00&TX_TAX_ADMINISTRATIVE_FEE_RETURN_BASE=.00&processingDate=2015-01-05

	require "../.././../../config.php";
	global $USER, $SESSION, $PAGE, $CFG, $DB, $OUTPUT;
	include_once('../defines.php');

	include_once('../autoload.php');
	$conexion = new \clases\Conexion();
	$controladorcarrito = new \controladores\ControladorCarrito($conexion);
	$data = array();
	foreach($_POST as $campop => $valorp){
		$data[$campop.''] = $valorp;
	}

	$controladorcarrito->pasarelaConfirmacion($data, false);		//el true signfica que está en modo pruebas
?>