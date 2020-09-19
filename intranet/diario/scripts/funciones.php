<?php
	function nombre( $mysql, $cliente )
	{
		$query = "SELECT 
		RFC,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido

		FROM clientes 

		WHERE 

		RFC = '$cliente'";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( strlen( $row['RFC'] ) == 13 )
			{
				return $row['nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido'];
			}else
			{
				return $row['denominacion'];
			}
		}
	}

	function cuenta( $mysql, $codigo )
	{
		$query = "SELECT 
		cuenta 

		FROM cuentas 

		WHERE codigo = '$codigo'";

		return $mysql->query( $query )->fetch_object()->cuenta;
	}

	function n_tres( $mysql, $n_tres )
	{
		if ( $n_tres !== '' )
		{
			$query = "SELECT 

			nombre

			FROM diario_padron 

			WHERE 

			ID    = '$n_tres'";

			return '<span class="font-italic">' . $mysql->query( $query )->fetch_object()->nombre . '</span>';
		}else
		{
			return '';
		}
	}

	function asiento( $mysql, $folio_id )
	{
		$data = array();

		$query = "SELECT 
		n_uno,
		n_dos,
		n_tres,
		debe,
		haber,
		folio_id

		FROM diario 

		WHERE folio_id = '$folio_id'";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			array_push( $data, array(
				'codigo'   => $row['n_uno'] . '.' . $row['n_dos'],
				'cuenta'   => cuenta( $mysql, $row['n_uno'] . '.' . $row['n_dos'] ) . ' ' . n_tres( $mysql, $row['n_tres'] ),
				'debe'     => floatval( $row['debe'] ), 
				'haber'    => floatval( $row['haber'] )
			));			
		}

		return json_decode( json_encode( $data ), true ); 
	}

	function CFDI( $mysql, $folio_id )
	{
		$data = null;

		$query = "SELECT 
		UUID,
		serie,
		folio,
		importe,
		RFC

		FROM CFDI_relacionados 

		WHERE folio_id = '$folio_id'";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			$data = array(
				'UUID'    => $row['UUID'],
				'serie'   => $row['serie'],
				'folio'   => $row['folio'],
				'importe' => $row['importe'],
				'RFC'     => $row['RFC']
			);
		}

		return json_decode( json_encode( $data ), true ); 
	}

	function mes( $mes )
	{
		if ( $mes == '01' )
		{
			return 'enero';
		}else if ( $mes == '02' )
		{
			return 'febrero';
		}else if ( $mes == '03' )
		{
			return 'marzo';
		}else if ( $mes == '04' )
		{
			return 'abril';
		}else if ( $mes == '05' )
		{
			return 'mayo';
		}else if ( $mes == '06' )
		{
			return 'junio';
		}else if ( $mes == '07' )
		{
			return 'julio';
		}else if ( $mes == '08' )
		{
			return 'agosto';
		}else if ( $mes == '09' )
		{
			return 'septiembre';
		}else if ( $mes == '10' )
		{
			return 'octubre';
		}else if ( $mes == '11' )
		{
			return 'noviembre';
		}else if ( $mes == '12' )
		{
			return 'diciembre';
		}
	}
?>
