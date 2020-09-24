<?php
	date_default_timezone_set( 'America/Mexico_City' );

	include '../../../con_intranet.php';

	$data = array();

	$grupo_a = 0;
	$grupo_b = 0;
	$grupo_c = 0;
	$grupo_d = 0;
	$grupo_e = 0;
	$grupo_f = 0;
	$grupo_g = 0;

	$query = "SELECT 
		RFC 

	FROM clientes";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( strlen( $row['RFC'] ) == 13 )
		{
			$año =  intval( '19' . substr( $row['RFC'], 4, 2 ) );
			$edad = intval( intval( date( 'Y' ) ) - $año );
			
			// 13-17
			if ( $edad >= 13 AND $edad <= 17 )
			{
				$grupo_a++;

			}else if ( $edad >= 18 AND $edad <= 24 ) // 18-24
			{
				$grupo_b++;

			}else if ( $edad >= 25 AND $edad <= 34 ) // 25-34
			{
				$grupo_c++;

			}else if ( $edad >= 35 AND $edad <= 44 ) // 35-44
			{
				$grupo_d++;

			}else if ( $edad >= 45 AND $edad <= 54 ) // 45-54
			{
				$grupo_e++;

			}else if ( $edad > 55 AND $edad <= 64 ) // 55-64
			{
				$grupo_f++;

			}else if ( $edad > 65 ) // +65
			{
				$grupo_g++;

			}
		}
	}

	array_push( $data, $grupo_a );
	array_push( $data, $grupo_b );
	array_push( $data, $grupo_c );
	array_push( $data, $grupo_d );
	array_push( $data, $grupo_e );
	array_push( $data, $grupo_f );
	array_push( $data, $grupo_g );

	echo json_encode( $data );
?>
