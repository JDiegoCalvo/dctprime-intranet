<?php
	include '../../con_intranet.php';

	$cliente = $_POST['cliente'];

	$query = "SELECT 
		num_servicio

	FROM servicios_asociados

	WHERE 

	cliente = '$cliente'";

	if ( $mysql->query( $query )->num_rows > 0 )
	{
		$data = $mysql->query( $query )->fetch_object()->num_servicio;
	}else
	{
		$data = false;
	}

	echo json_encode( $data );
?>
