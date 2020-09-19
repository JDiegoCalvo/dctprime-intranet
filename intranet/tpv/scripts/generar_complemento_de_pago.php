<?php
	include '../../../con_intranet.php';
	include '../../../api_key_facturapi.php';

	$customer = $_POST['customer'];
	$ID       = $_POST['ID'];
	$related  = array();

	$l = count( $_POST['related'] );

	for ( $i = 0; $i < $l; $i++ )
	{ 
		array_push( $related, array(
			'uuid'         => $_POST['related'][$i]['uuid'],
			'installment'  => $_POST['related'][$i]['installment'],
			'last_balance' => $_POST['related'][$i]['last_balance'],
			'amount'       => $_POST['related'][$i]['amount']
		));

		$uuid = $_POST['related'][$i]['uuid'];

		$query = "SELECT 
			saldo_insoluto

		FROM ventas

		WHERE 

		uuid = '$uuid'";

		$saldo_insoluto = floatval( $mysql->query( $query )->fetch_object()->saldo_insoluto );

		$nuevo_saldo = floatval( $_POST['related'][$i]['amount'] ) - $saldo_insoluto;

		$query = "UPDATE ventas SET saldo_insoluto = '$nuevo_saldo' WHERE uuid = '$uuid'";
		$mysql->query( $query );
	}

	$url = 'https://www.facturapi.io/v1/invoices';

	$ApiKey = base64_encode( $api_key_facturapi );

	$body = [
		"type" => "P",
		"customer" => $customer,
		"payments" => [
			[
				"payment_form" => "06",
				"related" => $related
			]
		]
	];

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Authorization: Basic ' . $ApiKey,
		'Content-Type: application/json'
	]);

	$output = curl_exec( $ch );
	curl_close( $ch );

	$query = "UPDATE pagos_recibidos SET estado = 'aplicado' WHERE ID = '$ID'";
	$mysql->query( $query );
?>
