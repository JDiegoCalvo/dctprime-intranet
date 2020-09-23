<?php
	include '../../../con_intranet.php';

	$data = array();

	$query = "SELECT 
		num_servicio,
		RFC, 
		denominacion, 
		nombre, 
		primer_apellido, 
		segundo_apellido,
		facturapi,
		conekta,
		spei_recurrente,
		oxxo_recurrente

	FROM clientes 

	ORDER BY RFC ASC";
	
	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			array_push( $data, array( 
				'num_servicio' => $row['num_servicio'],
				'RFC'          => $row['RFC'],
				'nombre'       => $row['denominacion'],
				'facturapi'    => $row['facturapi'],
				'conekta'      => $row['conekta'],
				'spei_recurrente' => $row['spei_recurrente'],
				'oxxo_recurrente' => $row['oxxo_recurrente']
			));
		}else
		{
			array_push( $data, array(
				'num_servicio' => $row['num_servicio'],
				'RFC'          => $row['RFC'],
				'nombre'       => $row['nombre']          . ' ' .
						    	  $row['primer_apellido'] . ' ' .
								  $row['segundo_apellido'],
				'facturapi'    => $row['facturapi'],
				'conekta'      => $row['conekta'],
				'spei_recurrente' => $row['spei_recurrente'],
				'oxxo_recurrente' => $row['oxxo_recurrente']
			));
		}
	}

	echo json_encode( $data );
?>
