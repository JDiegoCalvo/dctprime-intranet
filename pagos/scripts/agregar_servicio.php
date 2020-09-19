<?php
	include '../../con_intranet.php';

	$cliente      = $_POST['cliente'];
	$num_servicio = $_POST['num_servicio'];

	$query = "SELECT 
		ID 

	FROM clientes 

	WHERE 

	num_servicio = '$num_servicio'";

	if ( $mysql->query( $query )->num_rows > 0 )
	{
		$query = "SELECT 
			ID 

		FROM servicios_asociados 

		WHERE 

		cliente      = '$cliente' AND
		num_servicio = '$num_servicio'";

		if ( $mysql->query( $query )->num_rows == 0 )
		{
			$query = "INSERT INTO servicios_asociados(
				cliente,
				num_servicio
			) VALUES (
				'$cliente',
				'$num_servicio'
			)";

			$mysql->query( $query );

			$data = true;
		}else
		{
			$data = false;
		}
	}else
	{
		$data = false;
	}

	echo json_encode( $data );
?>
