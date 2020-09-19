<?php
	include '../../connection.php';
	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	$tipo      = $_POST['tipo'];

	cargar_XML();

	insertar_metadata_en_tabla();

	if ( $tipo == 'xml-emitidos/xml' OR $tipo == 'xml-emitidos/xml-referencias' )
	{
		$tabla = 'CFDIs_emitidos';

		$tabla_referencias = 'CFDIs_emitidos_referencias';
	}else if ( $tipo == 'xml-recibidos/xml' OR $tipo == 'xml-recibidos/xml-referencias' )
	{
		$tabla = 'CFDIs_recibidos';

		$tabla_referencias = 'CFDIs_recibidos_referencias';
	}

	$data = new stdClass;

	$data->CFDIs = array();

	$query = "SELECT 
		folio_fiscal,
		RFC,
		nombre,
		fecha_emision,
		fecha_certificacion,
		PAC,
		total,
		efecto

	FROM $tabla

	WHERE 

	cliente    = '$cliente'   AND 
	ejercicio  = '$ejercicio' AND 
	periodo    = '$periodo'

	ORDER BY fecha_certificacion ASC, fecha_emision ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['efecto'] == 'Ingreso' )
		{
			$class = 'class="table-success"';

		}else if ( $row['efecto'] == 'Egreso' )
		{
			$class = 'class="table-warning"';

		}else if ( $row['efecto'] == 'Pago' )
		{
			$class = 'class="table-primary"';
			
		}else if ( $row['efecto'] == 'Nómina' )
		{
			$class = 'class="table-light"';
			
		}else
		{
			$class = 'class="table-danger"';
		}

		array_push( $data->CFDIs, array(
			'folio_fiscal'  => $row['folio_fiscal'],
			'RFC'           => $row['RFC'],
			'nombre'        => $row['nombre'],
			'fecha_emision' => $row['fecha_emision'],
			'fecha_certificacion' => $row['fecha_certificacion'],
			'PAC'           => $row['PAC'],
			'total'         => $row['total'],
			'efecto'        => $row['efecto'],
			'class'            => $class
		));
	}

	$data->referencias = array();

	$query = "SELECT 
		folio_fiscal

	FROM $tabla_referencias

	WHERE 

	cliente    = '$cliente'   AND 
	ejercicio  = '$ejercicio' AND 
	periodo    = '$periodo'";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{	
		if ( $tipo == 'xml-emitidos/xml' )
		{
			$tipo = 'xml-emitidos/xml-referencias';

		}else if ( $tipo == 'xml-recibidos/xml' )
		{
			$tipo = 'xml-recibidos/xml-referencias';
		}

		$nombre_fichero = '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' . $row['folio_fiscal'] . '.xml';

		if ( file_exists( $nombre_fichero ) ) 
		{
			$icon = '<i class="fas fa-check-circle text-success"></i>';
		}else 
		{
			$icon = '';
		}

		array_push( $data->referencias, array(
			'folio_fiscal' => $row['folio_fiscal'],
			'icon'         => $icon
		));
	}

	echo json_encode( $data );
?>
