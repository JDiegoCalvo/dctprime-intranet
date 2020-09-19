<?php
	include '../../con_intranet.php';
	include 'funciones.php';

	$num_servicio = $_POST['num_servicio'];

	$query = "SELECT 
		ID

	FROM clientes 

	WHERE 

	num_servicio = '$num_servicio'";

	$cliente = $mysql->query( $query )->fetch_object()->ID;
	$data = new stdClass;

	$query = "SELECT 
		periodo,
		fecha_limite,
		importe,
		saldo_insoluto

	FROM ventas

	WHERE 

	cliente  = '$cliente'

	ORDER BY folio ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		$data->periodo = $row['periodo'];
		$data->importe = $row['importe'];
		$data->fecha_limite = fecha_limite( $row['fecha_limite'] );

		if ( $row['saldo_insoluto'] == 0 )
		{
			$data->pagado  = true;
		}else
		{
			$data->pagado = false;
		}
	}

	echo json_encode( $data );
?>
