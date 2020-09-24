<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../../con_intranet.php';

	$ID = $_POST['ID'];

	$query = "DELETE FROM emails WHERE ID = '$ID'";
	$mysql->query( $query );
?>
