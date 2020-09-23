<?php
	include '../../../con_intranet.php';
	include '../../../api_key_conekta.php';

	$url = 'https://api.conekta.io/customers';

	$ApiKey = base64_encode( $api_key_conekta );
	
	$ch = curl_init();
	// curl_setopt( $ch, CURLOPT_POST, true );
	// curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Accept: application/vnd.conekta-v2.0.0+json',
		'Authorization: Basic ' . $ApiKey,
		'Content-type: application/json'
	]);

	echo $output = curl_exec( $ch );
	curl_close( $ch );
?>
