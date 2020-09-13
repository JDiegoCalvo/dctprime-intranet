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

	function periodo( $ejercicio, $periodo )
	{
		if ( $periodo == '1T' )
		{
			$data = array(
				'periodo_query'      => "( periodo = '01' OR periodo = '02' OR periodo = '03' ) AND",
				'trimestre_actual'   => $ejercicio . '-01-01 ' . $ejercicio . '-03-31',
				'trimestre_anterior' => ( intval( $ejercicio ) - 1 ) . '-01-01 ' . ( intval( $ejercicio ) - 1 ) . '-03-31'
			);
		}else if ( $periodo == '2T' )
		{
			$data = array(
				'periodo_query'      => "( periodo = '04' OR periodo = '05' OR periodo = '06' ) AND",
				'trimestre_actual'   => $ejercicio . '-04-01 ' . $ejercicio . '-06-30',
				'trimestre_anterior' => ( intval( $ejercicio ) - 1 ) . '-04-01 ' . ( intval( $ejercicio ) - 1 ) . '-06-30'
			);
		}else if ( $periodo == '3T' )
		{
			$data = array(
				'periodo_query'      => "( periodo = '07' OR periodo = '08' OR periodo = '09' ) AND",
				'trimestre_actual'   => $ejercicio . '-06-01 ' . $ejercicio . '-09-31',
				'trimestre_anterior' => ( intval( $ejercicio ) - 1 ) . '-07-01 ' . ( intval( $ejercicio ) - 1 ) . '-09-31'
			);
		}else if ( $periodo == '4T' )
		{
			$data = array(
				'periodo_query'      => "( periodo = '10' OR periodo = '11' OR periodo = '12' ) AND",
				'trimestre_actual'   => $ejercicio . '-09-01 ' . $ejercicio . '-12-31',
				'trimestre_anterior' => ( intval( $ejercicio ) - 1 ) . '-10-01 ' . ( intval( $ejercicio ) - 1 ) . '-12-31'
			);
		}

		return json_decode( json_encode( $data ), true );
	}

	function ingreso( $mysql, $ejercicio, $cliente, $periodo, $periodo_query )
	{
		if ( $periodo == '1' )
		{
			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE

			n_uno = '401' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$ingreso =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '402' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$nota_de_credito = $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '2' )
		{
			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '401' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$ingreso =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '402' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$nota_de_credito = $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '3' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '401' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$ingreso =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '402' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$nota_de_credito = $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '4' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '401' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$ingreso = $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '402' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$nota_de_credito = $mysql->query( $query )->fetch_object()->total;
		}

		$data =  $ingreso - $nota_de_credito;

		return $data;
	}

	function deudor( $mysql, $cuenta, $ejercicio, $cliente, $periodo, $periodo_query )
	{
		$fin = substr( $periodo_query, -9, -7 );

		if ( $periodo == '1' )
		{
			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '$cuenta' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE

			n_uno = '$cuenta' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '2' )
		{
			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			periodo BETWEEN '01' AND '$fin' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			periodo BETWEEN '01' AND '$fin' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '3' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '4' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			periodo BETWEEN '01' AND '$fin' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			periodo BETWEEN '01' AND '$fin' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente' AND

			libro != ''";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}

		$data =  $debe - $haber;

		return $data;
	}

	function acreedor( $mysql, $cuenta, $ejercicio, $cliente, $periodo, $periodo_query )
	{
		if ( $periodo == '1' )
		{
			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE

			n_uno = '$cuenta' AND

			( periodo = '07' OR periodo = '08' OR periodo = '09' ) AND 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE

			n_uno = '$cuenta' AND

			$periodo_query 

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '2' )
		{
			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '3' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}else if ( $periodo == '4' )
		{
			$ejercicio = intval( $ejercicio ) - 1;

			$query = "SELECT 
				SUM( debe ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$debe =  $mysql->query( $query )->fetch_object()->total;

			$query = "SELECT 
				SUM( haber ) AS total

			FROM diario 

			WHERE 

			n_uno = '$cuenta' AND

			$periodo_query

			ejercicio = '$ejercicio' AND

			cliente   = '$cliente'";

			$haber =  $mysql->query( $query )->fetch_object()->total;
		}

		$data = $haber - $debe;

		return $data;
	}
?>
