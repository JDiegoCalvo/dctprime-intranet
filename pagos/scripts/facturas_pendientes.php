<?php
	include '../../con_intranet.php';

	$data = array();

	$query = "SELECT 
		fecha,
		saldo_insoluto,
		uuid

	FROM ventas 

	WHERE 

	cliente = '1' AND 
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
