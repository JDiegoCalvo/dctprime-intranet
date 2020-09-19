<?php
	include '../../con_intranet.php';

	$num_servicio = $_POST['num_servicio'];

	$query = "SELECT 
		ID

	FROM clientes 

	WHERE 

	num_servicio = '$num_servicio'";

	$cliente = $mysql->query( $query )->fetch_object()->ID;
	$data = array();

	$query = "SELECT 
		periodo,
		importe,
		saldo_insoluto

	FROM ventas

	WHERE 

	cliente  = '$cliente'

	ORDER BY folio DESC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		if ( $row['saldo_insoluto'] == 0 )
		{
			$estado = true;
		}else 
		{
			$estado = false;
		}

		array_push( $data, array(
			'periodo' => $row['periodo'],
			'importe' => $row['importe'],
			'pagado'  => $estado
		));
	}

	echo json_encode( $data );
?>
