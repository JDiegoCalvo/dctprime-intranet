<?php
	include '../../../con_intranet.php';
	include '../../../api_key_facturapi.php';

	date_default_timezone_set( 'America/Mexico_City' );

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$query = "SELECT 
		ID,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido,
		email

	FROM clientes 

	WHERE 

	RFC = '$cliente'

	LIMIT 1";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $cliente ) == 12 )
		{
			$legal_name = $row['denominacion'];

		}else if ( strlen( $cliente ) == 13 )
		{
			$legal_name = $row['nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'];
			
		}

		$ID    = $row['ID'];
		$email = $row['email'];
	}

	$total = 0;
	$items = array();

	$query = "SELECT
		clave_SAT,
		cantidad,
		descripcion,
		precio

	FROM recibo 

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND 
	periodo   = '$periodo'   AND 
	estado    = '0'";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		array_push( $items, array(
			'quantity' => $row['cantidad'],
			'product'  => [
				"description"   => $row['descripcion'],
				"product_key"   => '84111502',
				"price"         => $row['precio'],
				"tax_included"  => false,
				"taxes"         => [
					[
						"withholding" => false,
						"type"        => "IVA",
						"rate"        => 0.16
					]
				],
				"unit_key"      => 'E48',
				"unit_name"     => 'Unidad de servicio'
			]
		));

		$total += ( $row['cantidad'] * $row['precio'] ) * 1.16;
	}

	$url = 'https://www.facturapi.io/v1/invoices';

	$ApiKey = base64_encode( $api_key_facturapi );

	$body = [
		"customer" => [
			"legal_name" => $legal_name,
			"email" => $email,
			"tax_id" => $cliente
		],
		"items" => $items,
		"payment_method" => "PPD",
		"payment_form" => "99",
		"use" => "G03"
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

	$query = "SELECT 
		folio 

	FROM ventas 

	ORDER BY folio DESC 

	LIMIT 1";

	$result = $mysql->query( $query );

	if ( $result->num_rows > 0 )
	{
		while ( $row = $result->fetch_row() ) 
		{
			$folio = $row[0];
		}

		$folio += 1;
	}else
	{
		$folio = 1;
	}

	$hash = password_hash( $folio, PASSWORD_BCRYPT );
	$hash = str_replace( '#', '', $hash );
	$hash = str_replace( '%', '', $hash );
	$hash = str_replace( '&', '', $hash );
	$hash = str_replace( '*', '', $hash );
	$hash = str_replace( '{', '', $hash );
	$hash = str_replace( '}', '', $hash );
	$hash = str_replace( "'\'", '', $hash );
	$hash = str_replace( '/', '', $hash );
	$hash = str_replace( ':', '', $hash );
	$hash = str_replace( '<', '', $hash );
	$hash = str_replace( '>', '', $hash );
	$hash = str_replace( '?', '', $hash );
	$hash = str_replace( '+', '', $hash );
	$hash = str_replace( '.', '', $hash );

	$hash = substr( $hash, 7, 4 );

	$b = json_decode( $output, false );
	$invoice = $b->id;
	$fecha   = $b->created_at;
	$uuid    = $b->uuid;

	$url = 'https://www.facturapi.io/v1/invoices/'.$invoice.'/pdf';

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Authorization: Basic ' . $ApiKey,
		'Content-Type: application/json'
	]);
	$output = curl_exec( $ch );
	curl_close( $ch );

	file_put_contents( '../../../l/' . $hash . '.pdf', $output ); 

	$query = "UPDATE 
		recibo 

	SET 

	estado = '1' 

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND 
	periodo   = '$periodo'";

	$mysql->query( $query );

	if ( $periodo == 'Sin periodo' )
	{
		$per = $periodo;

		$fecha_limite = 'Inmediatamente'; 
	}else
	{
		$m = [
			null, 'Enero', 'Febrero',
			'Marzo', 'Abril', 'Mayo',
			'Junio', 'Julio', 'Agosto',
			'Septiembre', 'Octubre', 'Noviembre',
			'Diciembre'
		];

		$l = count( $m );

		for ( $i = 0; $i < $l; $i++ )
		{
			if ( intval( $periodo ) == $i )
			{
				$per = $m[$i] . ' - ' . $ejercicio;
			}
		}

		$fecha_a_usar = $ejercicio . '-'. $periodo . '-01';

		$fecha_limite = date( 'Y-m-d', strtotime( $fecha_a_usar . "+ 47 days" ) ); 
	}

	$query = "INSERT INTO ventas ( 
		fecha, 
		periodo,
		fecha_limite,
		folio, 
		cliente, 
		importe, 
		saldo_insoluto,
		hash,
		uuid
	) VALUES ( 
		'$fecha', 
		'$per',
		'$fecha_limite',
		'$folio', 
		'$ID', 
		'$total', 
		'$total',
		'$hash',
		'$uuid'
	)";

	$mysql->query( $query );
?>
