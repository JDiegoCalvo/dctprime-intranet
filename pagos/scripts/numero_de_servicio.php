<?php
	include '../../con_intranet.php';

	$num_servicio = $_POST['num_servicio'];

	$query = "SELECT 
		ID

	FROM clientes 

	WHERE 

	num_servicio = '$num_servicio'";

	$ID = $mysql->query( $query )->fetch_object()->ID;

	$data = new stdClass;

	$query = "SELECT 
		num_servicio,
		RFC,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido,
		calle,
		numero_exterior,
		numero_interior,
		colonia,
		codigo_postal,
		municipio,
		estado

	FROM clientes

	WHERE 

	ID = '$ID'

	LIMIT 1";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() )
	{
		if ( strlen( $row['RFC'] ) == 12 )
		{
			$data->nombre = $row['denominacion'];
		}else
		{
			$data->nombre = $row['nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'];
		}

		$data->num_servicio = $row['num_servicio'];
		$data->RFC       = $row['RFC'];
		$data->calle     = $row['calle'];
		$data->num_ext   = $row['numero_exterior'];
		$data->num_int   = $row['numero_interior'];
		$data->colonia   = $row['colonia'];
		$data->cp        = $row['codigo_postal'];
		$data->municipio = $row['municipio'];
		$data->estado    = $row['estado'];
	}

	echo json_encode( $data );
?>
