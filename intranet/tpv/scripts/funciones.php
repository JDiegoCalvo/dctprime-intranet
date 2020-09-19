<?php
	function cliente( $ID )
	{
		global $mysql;

		$data = new stdClass;

		$query = "SELECT 
			RFC,
			denominacion,
			nombre,
			primer_apellido,
			segundo_apellido,
			telefono,
			tratamiento

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

			}else if ( strlen( $row['RFC'] ) == 13 )
			{
				$data->nombre = $row['nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'];
				
			}

			$data->telefono    = $row['telefono'];
			$data->tratamiento = $row['tratamiento'];
		}

		return json_decode( json_encode( $data ), true );
	}
?>
