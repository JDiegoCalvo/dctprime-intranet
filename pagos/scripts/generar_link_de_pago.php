<?php
	include '../../con_intranet.php';
	include '../../api_key_conekta.php';

	$cliente    = $_POST['cliente'];

	$query = "SELECT 
		RFC,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido,
		telefono,
		email,
		conekta

	FROM clientes

	WHERE 

	ID = '$cliente'

	LIMIT 1";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			$nombre = $row['denominacion'];
		}else
		{
			$nombre = $row['nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'];
		}

		$telefono     = substr( $row['telefono'], 3, 10 );
		$email        = $row['email'];
		$customer_id  = $row['conekta'];
	}

	$url = 'https://api.conekta.io/checkouts';
	$ApiKey = base64_encode( $api_key_conekta );

	$fecha_actual = date( 'Y-m-d' );
	$fecha = new DateTime( date( 'Y-m-d', strtotime( $fecha_actual . "+ 17 days" ) ) ); 
	$expired_at = $fecha->getTimestamp();

	$line_items = array();
	$l = count( $_POST['uuid'] );

	for ( $i = 0; $i < $l; $i++ )
	{
		array_push( $line_items, array(
			"name" => $_POST['uuid'][$i],
			"unit_price" => str_replace( '.', '', $_POST['unit_price'][$i] ),
			"quantity" => 1
		));
	}

	$body = [
		"name"  => "DCT Prime",
		"type" => "PaymentLink",
		"recurrent" => false,
		"expired_at" => $expired_at,
		"allowed_payment_methods" => [ "cash", "card" ],
		"needs_shipping_contact" => false,
		"order_template" => [
			"line_items" => $line_items,
			"currency" => "MXN",
			"customer_info" => [
				"customer_id" => $customer_id
			]
		]
	];
	
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Accept: application/vnd.conekta-v2.0.0+json',
		'Authorization: Basic ' . $ApiKey,
		'Content-type: application/json'
	]);

	$output = curl_exec( $ch );
	curl_close( $ch );

	$b = json_decode( $output, false );
	$data = $b->url;

	echo json_encode( $data );
?>
