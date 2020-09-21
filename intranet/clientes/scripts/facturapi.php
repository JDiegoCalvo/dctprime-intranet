<?php
	include '../../../con_intranet.php';
	include '../../../api_key_facturapi.php';

	$RFC = $_POST['RFC'];

	$query = "SELECT 
		RFC, 
		denominacion, 
		nombre, 
		primer_apellido, 
		segundo_apellido,
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
			$legal_name = $row['denominacion'];
		}else
		{
			$legal_name = $row['nombre']           . ' ' .
					      $row['primer_apellido']  . ' ' .
					      $row['segundo_apellido'];
		}

		$email  = $row['email'];
		$tax_id = $row['RFC'];
	}

	$url = 'https://www.facturapi.io/v1/customers';

	$ApiKey = base64_encode( $api_key_facturapi );

	$body = [
		"legal_name" => $legal_name,
		"email"      => $email,
		"tax_id"     => $tax_id
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

	$b         = json_decode( $output, false );
	$facturapi = $b->id;

	$query = "UPDATE clientes SET facturapi = '$facturapi' WHERE RFC = '$RFC'";
	$mysql->query( $query );
?>
