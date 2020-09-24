<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../../con_intranet.php';

	$data = array();

	$query = "SELECT 
		RFC,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido,
		email 

	FROM clientes

	WHERE email != 'example@example.com'

	ORDER BY RFC ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			$nombre = $row['denominacion'];
		}else 
		{
			$nombre = $row['nombre']          . ' ' .
					  $row['primer_apellido'] . ' ' .
					  $row['segundo_apellido'];
		}

		array_push( $data, array(
			'nombre' => $nombre,
			'email'  => $row['email']
		));
	}

	echo json_encode( $data );
?>
