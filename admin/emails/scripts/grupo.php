<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../../con_intranet.php';

	$grupo = $_POST['grupo'];
	$data = array();

	$query = "SELECT 
		ID,
		email

	FROM emails 

	WHERE 

	grupo = '$grupo'";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		array_push( $data, array(
			'ID'    => $row['ID'],
			'email' => $row['email']
		));
	}

	echo json_encode( $data );
?>
