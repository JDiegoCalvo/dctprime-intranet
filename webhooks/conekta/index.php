<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../con_intranet.php';
	include '../../api_key_conekta.php';
	include '../../api_key_facturapi.php';

	$body = @file_get_contents( 'php://input' );
	$data = json_decode( $body );
	http_response_code( 200 ); // Return 200 OK 

	$json_string = json_encode( $data );
	$file = date( 'Y-m-d H:i:s' ) . '_index.json';
	file_put_contents( $file, $json_string );

	if ( $data->type == 'charge.paid' )
	{
		$conekta = $data->data->object->customer_id;
		$msg     = "Tu pago ha sido comprobado. " . $data->data->object->customer_id;

		mail( "jd.calvo@dctprime.com", "Pago confirmado", $msg );

		if ( $data->data->object->payment_method->object == 'bank_transfer_payment' )
		{
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
		}else
		{
			$url    = 'https://api.conekta.io/orders/' . $data->data->object->order_id;
			$ApiKey = base64_encode( $api_key_conekta );

			$ch = curl_init();
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
			$data = $b->line_items->data;

			$l = count( $data );

			$related = array();

			for ( $i = 0; $i < $l; $i++ )
			{
				$amount = ( floatval( $data[$i]->unit_price ) / 100 ); // Importante para quitar decimales

				array_push( $related, array(
					'uuid'         => $data[$i]->name,
					'installment'  => 1,
					'last_balance' => $amount,
					'amount'       => $amount
				));

				$uuid = $data[$i]->name;

				$query = "SELECT 
					saldo_insoluto

				FROM ventas 

				WHERE 

				uuid = '$uuid'";

				$saldo_insoluto = floatval( $mysql->query( $query )->fetch_object()->saldo_insoluto );

				$nuevo_saldo = $saldo_insoluto - $amount; 

				$query = "UPDATE ventas SET saldo_insoluto = '$nuevo_saldo' WHERE uuid = '$uuid'";
				$mysql->query( $query );
			}

			$url    = 'https://www.facturapi.io/v1/invoices';
			$ApiKey = base64_encode( $api_key_facturapi );

			$query = "SELECT 
				facturapi

			FROM clientes 

			WHERE 

			conekta = '$conekta'";

			$customer = $mysql->query( $query )->fetch_object()->facturapi;

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
		}
	}
?>
