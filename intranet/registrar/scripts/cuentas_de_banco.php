<?php
	include '../../connection.php';

	$cliente = $_POST['cliente'];

	$data = new stdClass;
	
	$data->efectivo = array();
	$data->TDC      = array();
	$data->terceros = array();

	$query = "SELECT 
		ID,
		n_uno,
		n_dos,
		nombre,
		otro 

	FROM diario_padron

	WHERE 

	cliente = '$cliente' AND 

	( n_uno = '102' OR n_uno = '202' OR n_uno = '217' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		$value = $row['n_uno'] . ',' . $row['n_dos'] . ',' . $row['ID'];

		$text = $row['nombre'] . ' ' . $row['otro'];

		if ( $row['n_uno'] == '102' )
		{
			array_push( $data->efectivo, array(
				'value' => $value,
				'text'  => $text
			));
		}else if ( $row['n_uno'] == '202' )
		{
			array_push( $data->TDC, array(
				'value' => $value,
				'text'  => $text
			));
		}else if ( $row['n_uno'] == '217' )
		{
			array_push( $data->terceros, array(
				'value' => $value,
				'text'  => $text
			));
		}
	}

	echo json_encode( $data );
?>
