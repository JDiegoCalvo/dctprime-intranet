<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../con_intranet.php';
	include '../../api_key_conekta.php';
	include '../../api_key_facturapi.php';

	$body = @file_get_contents( 'php://input' );
	$data = json_decode( $body );
	http_response_code( 200 ); // Return 200 OK 

	$json_string = json_encode( $data );
	$file = date( 'Y-m-d H:i:s' ) . '_oxxo.json';
	file_put_contents( $file, $json_string );

	if ( $data->type == 'inbound_payment.lookup' )
	{
		$response = [
			"payable"    => true,
			"min_amount" => 100,
			"max_amount" => 250000
		];

		return json_encode( $response );

	}else if ( $data->type == 'inbound_payment.payment_attempt' )
	{
		$response = [
			"payable"    => true
		];

		return json_encode( $response );

	}else if ( $data->type = 'charge.paid' )
	{
		$conekta = $data->data->object->customer_id;
		$msg     = "Tu pago ha sido comprobado. " . $data->data->object->customer_id;

		mail( "jd.calvo@dctprime.com", "Pago confirmado", $msg );

		$json_string = json_encode($data);
		$file = 'clientes.json';
		file_put_contents($file, $json_string);

		$date = date( 'Y-m-d H:i:s' );
		$amount = floatval( $data->data->object->amount ) / 100;

		$query = "INSERT INTO pagos_recibidos(
			customer,
			created_at,
			amount,
			estado
		) VALUES (
			'$conekta',
			'$date',
			'$amount',
			'pendiente'
		)";
		$mysql->query( $query );

	}
?>
