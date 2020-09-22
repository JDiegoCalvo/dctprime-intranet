<?php
	include '../../../con_intranet.php';

	$customer = $_POST['cliente'];

	$query = "SELECT
		ID 

	FROM clientes 

	WHERE conekta = '$customer'";

	$cliente = $mysql->query( $query )->fetch_object()->ID;

	$data = array();

	$query = "SELECT 
		fecha,
		saldo_insoluto,
		uuid

	FROM ventas 

	WHERE 

	cliente = '$cliente' AND 
	uuid   != ''

	ORDER BY fecha ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['saldo_insoluto'] > 0 )
		{
			array_push( $data, array(
				'fecha'   => $row['fecha'],
				'importe' => $row['saldo_insoluto'],
				'uuid'    => $row['uuid']
			));
		}
	}

	echo json_encode( $data );
?>
