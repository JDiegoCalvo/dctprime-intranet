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

	function debe_n_uno( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$inicio = $ejercicio . '-' . $periodo . '-01';
		$fin    = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 
			SUM( debe ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente' AND 
		n_uno     = '$codigo'  AND 
		libro     != ''        AND

		balance BETWEEN '$inicio' AND '$fin'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function haber_n_uno( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$inicio = $ejercicio . '-' . $periodo . '-01';
		$fin    = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 
			SUM( haber ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente' AND 
		n_uno     = '$codigo'  AND 
		libro     != ''        AND

		balance BETWEEN '$inicio' AND '$fin'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function debe_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		$inicio = $ejercicio . '-' . $periodo . '-01';
		$fin    = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 
			SUM( debe ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente' AND 
		n_uno     = '$n_uno'   AND 
		n_dos     = '$n_dos'   AND
		libro     != ''        AND

		balance BETWEEN '$inicio' AND '$fin'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function haber_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		$inicio = $ejercicio . '-' . $periodo . '-01';
		$fin    = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 
			SUM( haber ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente' AND 
		n_uno     = '$n_uno'   AND 
		n_dos     = '$n_dos'   AND
		libro     != ''        AND

		balance BETWEEN '$inicio' AND '$fin'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function saldo_inicial_n_uno( $mysql, $cliente, $ejercicio, $periodo, $codigo, $naturaleza )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		if ( intval( $periodo ) < 11 )
		{
			$periodo = '0' . ( intval( $periodo ) - 1 );
		}else
		{
			$periodo = intval( $periodo ) - 1;
		}

		$fin = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 
			SUM( debe ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		n_uno     = '$n_uno'     AND 

		balance BETWEEN '2015-01-01' AND '$fin'";

		$debe = floatval( $mysql->query( $query )->fetch_object()->total );

		$query = "SELECT 

		SUM( haber ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		n_uno     = '$n_uno'     AND 

		balance BETWEEN '2015-01-01' AND '$fin'";

		$haber = floatval( $mysql->query( $query )->fetch_object()->total );

		if ( $naturaleza == 'D' )
		{
			$data = $debe - $haber;
		}else if ( $naturaleza == 'A' )
		{
			$data = $haber - $debe;
		}else if ( $naturaleza == 'O' )
		{
			$data = $debe + $haber;
		}

		return floatval( $data );
	}

	function saldo_inicial_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo, $naturaleza )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		if ( intval( $periodo ) < 11 )
		{
			$periodo = '0' . ( intval( $periodo ) - 1 );
		}else
		{
			$periodo = intval( $periodo ) - 1;
		}

		$fin = $ejercicio . '-' . $periodo . '-31';

		$query = "SELECT 

		SUM( debe ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		n_uno     = '$n_uno'     AND 
		n_dos     = '$n_dos'     AND 

		balance BETWEEN '2015-01-01' AND '$fin'";

		$debe = floatval( $mysql->query( $query )->fetch_object()->total );

		$query = "SELECT 
			SUM( haber ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		n_uno     = '$n_uno'     AND 
		n_dos     = '$n_dos'     AND 

		balance BETWEEN '2015-01-01' AND '$fin'";

		$haber = floatval( $mysql->query( $query )->fetch_object()->total );

		if ( $naturaleza == 'D' )
		{
			$data = $debe - $haber;
		}else if ( $naturaleza == 'A' )
		{
			$data = $haber - $debe;
		}else if ( $naturaleza == 'O' )
		{
			$data = $debe + $haber;
		}

		return floatval( $data );
	}
?>
