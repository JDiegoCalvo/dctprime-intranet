<?php
	include '../../../con_intranet.php';

	include 'funciones.php';

	crear_directorio( $_POST['cliente'], $_POST['ejercicio'], $_POST['periodo'] );

	$data = new stdClass;

	$query = "SELECT 
		CIEC, 
		claveFIEL 

	FROM clientes 

	WHERE RFC = '$_POST[cliente]' 

	LIMIT 1";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		$data->cliente_RFC = $_POST['cliente'];
		$data->CIEC        = $row['CIEC'];
		$data->clave_FIEL  = $row['claveFIEL'];
	}

	echo json_encode( $data );
?>
