<?php
	include '../../../con_intranet.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$data = array();

	$query = "SELECT
		ID,
		cantidad,
		descripcion, 
		precio 

	FROM recibo

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND
	periodo   = '$periodo'   AND
	estado    = '0'

	ORDER BY ID ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		$precio = ( $row['cantidad'] * $row['precio'] ) * 1.16;

		array_push( $data, array(
			'ID'          => $row['ID'],
			'cantidad'    => $row['cantidad'],
			'descripcion' => $row['descripcion'],
			'precio'      => $precio
		));
	}

	echo json_encode( $data );
?>
