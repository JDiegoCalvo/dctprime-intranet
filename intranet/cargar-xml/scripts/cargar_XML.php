<?php
	include '../../../api_key_satws.php';
	include '../../../con_intranet.php';
	include 'funciones.php';

	$cliente       = $_POST['cliente'];
	$ejercicio     = $_POST['ejercicio'];
	$periodo       = $_POST['periodo'];
	$tipo          = $_POST['tipo'];
	$del_servidor  = $_POST['del_servidor'];

	if ( $del_servidor )
	{
		if ( $tipo == 'xml-emitidos/xml' )
		{
			$fecha_inicial = $ejercicio . '-' . $periodo . '-01';
			$fecha_final   = $ejercicio . '-' . $periodo . '-31';

			$URL = 'https://api.sat.ws/taxpayers/'.$cliente.'/invoices?issuer.rfc='.$cliente.'&itemsPerPage=100&issuedAt[after]='.$fecha_inicial.'T00:00:00.000Z&issuedAt[before]='.$fecha_final.'T23:59:59.000Z';

			$ch = curl_init( $URL );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, [
				'X-API-Key: ' . $api_key_satws,
				'Accept: application/json'
			]);
			$res = curl_exec( $ch );
			curl_close( $ch );

			$b = json_decode( $res, false );

			$l = count( $b );

			for ( $i = 0; $i < $l; $i++ )
			{
				$satws_id = $b[$i]->id;
				$UUID     = strtoupper( $b[$i]->uuid );

				$URL = 'https://api.sat.ws/invoices/'.$satws_id.'/cfdi';

				$ch = curl_init( $URL );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, [
					'X-API-Key: ' . $api_key_satws,
					'Accept: text/xml'
				]);
				$output = curl_exec( $ch );
				curl_close( $ch );

				file_put_contents( '../../zona-de-prep-xml/' . $UUID . '.xml', $output ); 
			}
		}else if ( $tipo == 'xml-emitidos/xml-referencias' )
		{

		}else if ( $tipo == 'xml-recibidos/xml' )
		{
			$fecha_inicial = $ejercicio . '-' . $periodo . '-01';
			$fecha_final   = $ejercicio . '-' . $periodo . '-31';

			$URL = 'https://api.sat.ws/taxpayers/'.$cliente.'/invoices?receiver.rfc='.$cliente.'&itemsPerPage=100&issuedAt[after]='.$fecha_inicial.'T00:00:00.000Z&issuedAt[before]='.$fecha_final.'T23:59:59.000Z';

			$ch = curl_init( $URL );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, [
				'X-API-Key: ' . $api_key_satws,
				'Accept: application/json'
			]);
			$res = curl_exec( $ch );
			curl_close( $ch );

			$b = json_decode( $res, false );

			$l = count( $b );

			for ( $i = 0; $i < $l; $i++ )
			{
				$satws_id = $b[$i]->id;
				$UUID     = strtoupper( $b[$i]->uuid );

				$URL = 'https://api.sat.ws/invoices/'.$satws_id.'/cfdi';

				$ch = curl_init( $URL );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, [
					'X-API-Key: ' . $api_key_satws,
					'Accept: text/xml'
				]);
				$output = curl_exec( $ch );
				curl_close( $ch );

				file_put_contents( '../../zona-de-prep-xml/' . $UUID . '.xml', $output ); 
			}
		}else if ( $tipo == 'xml-recibidos/xml-referencias' )
		{
			$query = "SELECT 
				folio_fiscal

			FROM CFDIs_recibidos_referencias

			WHERE 

			cliente    = '$cliente'   AND 
			ejercicio  = '$ejercicio' AND 
			periodo    = '$periodo'";

			$result = $mysql->query( $query );

			while ( $row = $result->fetch_assoc() ) 
			{	
				$uuid = strtolower( $row['folio_fiscal'] );
				$URL = 'https://api.sat.ws/taxpayers/'.$cliente.'/invoices?uuid=' . $uuid;

				$ch = curl_init( $URL );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, [
					'X-API-Key: ' . $api_key_satws,
					'Accept: application/json'
				]);
				$res = curl_exec( $ch );
				curl_close( $ch );

				$b = json_decode( $res, false );

				$satws_id = $b[0]->id;
				$UUID     = strtoupper( $b[0]->uuid );

				$URL = 'https://api.sat.ws/invoices/'.$satws_id.'/cfdi';

				$ch = curl_init( $URL );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, [
					'X-API-Key: ' . $api_key_satws,
					'Accept: text/xml'
				]);
				$output = curl_exec( $ch );
				curl_close( $ch );

				file_put_contents( '../../zona-de-prep-xml/' . $UUID . '.xml', $output ); 
			}
		}

		acomodar_XML();
	}else
	{
		cargar_XML();
	}

	insertar_metadata_en_tabla();

	if ( $tipo == 'xml-emitidos/xml' OR $tipo == 'xml-emitidos/xml-referencias' )
	{
		$tabla = 'CFDIs_emitidos';

		$tabla_referencias = 'CFDIs_emitidos_referencias';
	}else if ( $tipo == 'xml-recibidos/xml' OR $tipo == 'xml-recibidos/xml-referencias' )
	{
		$tabla = 'CFDIs_recibidos';

		$tabla_referencias = 'CFDIs_recibidos_referencias';
	}

	$data = new stdClass;

	$data->CFDIs = array();

	$query = "SELECT 
		folio_fiscal,
		RFC,
		nombre,
		fecha_emision,
		fecha_certificacion,
		PAC,
		total,
		efecto

	FROM $tabla

	WHERE 

	cliente    = '$cliente'   AND 
	ejercicio  = '$ejercicio' AND 
	periodo    = '$periodo'

	ORDER BY fecha_certificacion ASC, fecha_emision ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['efecto'] == 'Ingreso' )
		{
			$class = 'class="table-success"';

		}else if ( $row['efecto'] == 'Egreso' )
		{
			$class = 'class="table-warning"';

		}else if ( $row['efecto'] == 'Pago' )
		{
			$class = 'class="table-primary"';
			
		}else if ( $row['efecto'] == 'Nómina' )
		{
			$class = 'class="table-light"';
			
		}else
		{
			$class = 'class="table-danger"';
		}

		array_push( $data->CFDIs, array(
			'folio_fiscal'  => $row['folio_fiscal'],
			'RFC'           => $row['RFC'],
			'nombre'        => $row['nombre'],
			'fecha_emision' => $row['fecha_emision'],
			'fecha_certificacion' => $row['fecha_certificacion'],
			'PAC'           => $row['PAC'],
			'total'         => $row['total'],
			'efecto'        => $row['efecto'],
			'class'            => $class
		));
	}

	$data->referencias = array();

	$query = "SELECT 
		folio_fiscal

	FROM $tabla_referencias

	WHERE 

	cliente    = '$cliente'   AND 
	ejercicio  = '$ejercicio' AND 
	periodo    = '$periodo'";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{	
		if ( $tipo == 'xml-emitidos/xml' )
		{
			$tipo = 'xml-emitidos/xml-referencias';

		}else if ( $tipo == 'xml-recibidos/xml' )
		{
			$tipo = 'xml-recibidos/xml-referencias';
		}

		$nombre_fichero = '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' . $row['folio_fiscal'] . '.xml';

		if ( file_exists( $nombre_fichero ) ) 
		{
			$icon = '<i class="fas fa-check-circle text-success"></i>';
		}else 
		{
			$icon = '';
		}

		array_push( $data->referencias, array(
			'folio_fiscal' => $row['folio_fiscal'],
			'icon'         => $icon
		));
	}

	echo json_encode( $data );
?>
