<?php
	include '../../con_intranet.php';

	$RFC  = $_POST['RFC'];
	$CIEC = $_POST['CIEC'];

	$query = "SELECT 
		ID 

	FROM clientes

	WHERE 

	RFC  = '$RFC' AND 
	CIEC = '$CIEC'";

	if ( $mysql->query( $query )->num_rows > 0 )
	{
		$data = true;
	}else
	{
		$data = false;
	}

	echo json_encode( $data );
?>
