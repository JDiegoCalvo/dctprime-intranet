<?php
	include '../../../con_intranet.php';
	include '../../../api_key_conekta.php';

	$RFC = $_POST['RFC'];

	$query = "SELECT 
		conekta

	FROM clientes 

	WHERE 

	RFC = '$RFC'

	ORDER BY RFC ASC";
	
	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		$customer_id = $row['conekta'];
	}

	$url = 'https://api.conekta.io/customers/'.$customer_id.'/payment_sources/';

	$ApiKey = base64_encode( $api_key_conekta );

	$body = [
		"type" => "oxxo_recurrent"
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
	$oxxo_recurrente = $b->reference;

	$query = "UPDATE clientes SET oxxo_recurrente = '$oxxo_recurrente' WHERE RFC = '$RFC'";
	$mysql->query( $query );
?>
