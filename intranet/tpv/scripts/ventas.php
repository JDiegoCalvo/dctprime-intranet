<?php
	include '../../../con_intranet.php';
	include 'funciones.php';

	$data = array();

	$query = "SELECT 
		fecha,
		cliente,
		importe,
		hash

	FROM ventas 

	ORDER BY folio DESC

	LIMIT 15";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		$c           = cliente( $row['cliente'] );
		$cliente     = $c['nombre'];
		$telefono    = $c['telefono'];
		$tratamiento = $c['tratamiento'];

		array_push( $data, array(
			'fecha'       => $row['fecha'],
			'cliente'     => $cliente,
			'importe'     => $row['importe'],
			'hash'        => $row['hash'],
			'telefono'    => $telefono,
			'tratamiento' => $tratamiento . ' Primeramente un cordial saludo. Permítanos entregarle por este medio su recibo del mes. ¡Muchas Gracias por su preferencia!'
		));
	}

	echo json_encode( $data );
?>
