<?php
	include '../../../con_intranet.php';
	include '../../../api_key_conekta.php';

	$RFC = $_POST['RFC'];

	$query = "SELECT 
		RFC, 
		denominacion, 
		nombre, 
		primer_apellido, 
		segundo_apellido,
		telefono,
		email

	FROM clientes 

	WHERE 

	RFC = '$RFC'

	ORDER BY RFC ASC";
	
	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			$name = $row['denominacion'];
		}else
		{
			$name = $row['nombre']           . ' ' .
					$row['primer_apellido']  . ' ' .
					$row['segundo_apellido'];
		}

		$email = $row['email'];
		$phone = $row['telefono'];
	}

	$url = 'https://api.conekta.io/customers';

	$ApiKey = base64_encode( $api_key_conekta );

	$body = [
		"name"  => $name,
		"email" => $email,
		"phone" => $phone,
		"payment_sources" => [
			[
				"type" => "spei_recurrent"
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
	$conekta = $b->id;
	$spei_recurrente = $b->payment_sources->data[0]->reference;

	$query = "UPDATE clientes SET conekta = '$conekta', spei_recurrente = '$spei_recurrente' WHERE RFC = '$RFC'";
	$mysql->query( $query );
?>
