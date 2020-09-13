<?php
	include '../../connection.php';

	$ID = $_POST['ID'];

	$query = "DELETE FROM recibo WHERE ID = '$ID'";
	$mysql->query( $query );
?>
