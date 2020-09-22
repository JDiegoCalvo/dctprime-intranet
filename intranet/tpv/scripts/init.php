<?php
	include '../../../con_intranet.php';

	$data = array();

	$query = "SELECT 
		RFC, 
		denominacion, 
		nombre, 
		primer_apellido, 
		segundo_apellido,
		conekta

	FROM clientes 

	ORDER BY RFC ASC";
	
	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			array_push( $data, array( 
				'RFC'     => $row['RFC'],
				'cliente' => $row['RFC'] . ' - ' . $row['denominacion'],
				'conekta' => $row['conekta']
			));
		}else
		{
			array_push( $data, array(
				'RFC'     => $row['RFC'],
				'cliente' => 
							 $row['RFC']             . ' - ' . 
							 $row['nombre']          . ' ' .
							 $row['primer_apellido'] . ' ' .
							 $row['segundo_apellido'],
				'conekta' => $row['conekta']
			));
		}
	}

	echo json_encode( $data );
?>
