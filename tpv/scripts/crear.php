<?php
	include '../../connection.php';

	$cliente     = $_POST['cliente'];
	$ejercicio   = $_POST['ejercicio'];
	$periodo     = $_POST['periodo'];
	$clave_SAT   = '84111503';
	$cantidad    = $_POST['cantidad'];
	$unidad_SAT  = 'E48';
	$unidad      = 'Servicio';
	$descripcion = $_POST['descripcion'];
	$precio      = $_POST['precio'];

	$query = "INSERT INTO recibo (
		cliente,
		ejercicio,
		periodo,
		clave_SAT, 
		cantidad, 
		unidad_SAT, 
		unidad, 
		descripcion, 
		precio 
	) VALUES ( 
		'$cliente',
		'$ejercicio',
		'$periodo',
		'$clave_SAT', 
		'$cantidad', 
		'$unidad_SAT', 
		'$unidad', 
		'$descripcion', 
		'$precio' 
	)";

	$mysql->query( $query );
?>
