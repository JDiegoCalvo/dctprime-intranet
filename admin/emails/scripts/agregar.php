<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../../con_intranet.php';

	$grupo = $_POST['grupo'];
	$email = $_POST['email'];

	$query = "SELECT 
		ID 

	FROM emails 

	WHERE 

	grupo = '$grupo' AND 
	email = '$email'";

	if ( $mysql->query( $query )->num_rows == 0 )
	{
		$query = "INSERT INTO emails (
			grupo,
			email 
		) VALUES (
			'$grupo',
			'$email'
		)";

		$mysql->query( $query );

		$data = true;
	}else
	{
		$data = false;
	}

	echo json_encode( $data );
?>
