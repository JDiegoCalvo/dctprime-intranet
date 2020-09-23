<?php
	include '../../con_intranet.php';

	$sesion = $_POST['sesion'];

	$query = "SELECT 
		ID 

	FROM sesiones 

	WHERE 

	sesion = '$sesion'";

	if ( $mysql->query( $query )->num_rows == 0 )
	{
		$data = true;
	}else
	{
		$data = false;
	}

	echo $data;
?>
