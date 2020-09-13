<?php
	include '../../connection.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$query = "SELECT
		ID

	FROM CFDIs_emitidos

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND
	periodo   = '$periodo'   AND
	efecto    != 'Nómina'";

	$CFDIs_emitidos = intval( $mysql->query( $query )->num_rows );

	$query = "SELECT
		ID

	FROM CFDIs_recibidos

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND
	periodo   = '$periodo'   AND
	efecto    != 'Nómina'";

	$CFDIs_recibidos = intval( $mysql->query( $query )->num_rows );

	$data = $CFDIs_emitidos + $CFDIs_recibidos;

	echo $data;
?>
