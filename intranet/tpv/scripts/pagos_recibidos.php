<?php
	include '../../../con_intranet.php';

	$customer = '5f61b59ce49549002635dbce';

	$data = array();

	$query = "SELECT 
		ID,
		created_at,
		amount

	FROM pagos_recibidos 

	WHERE 

	customer = '$customer' AND 
	estado   = 'pendiente'

	ORDER BY created_at ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		array_push( $data, array(
			'ID'      => $row['ID'],
			'fecha'   => $row['created_at'],
			'importe' => $row['amount']
		));
	}

	echo json_encode( $data );
?>
