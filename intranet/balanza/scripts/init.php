<?php
	include '../../../con_intranet.php';
	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	
	$data = new stdClass;

	$data->nombre = nombre( $mysql, $cliente );

	$n_dias = cal_days_in_month( CAL_GREGORIAN, $periodo, $ejercicio );
	$data->fecha = 'Del ' . $ejercicio . '-' . $periodo . '-01 al ' . $ejercicio . '-' . $periodo . '-' . $n_dias;

	$data->cuenta = array();

	$query = "SELECT 
	nivel,
	codigo,
	cuenta,
	naturaleza

	FROM cuentas 

	ORDER BY codigo ASC, nivel ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['nivel'] == '1' )
		{
			$saldo_inicial = saldo_inicial_n_uno( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'], $row['naturaleza'] );
			$debe  = debe_n_uno( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'] );
			$haber = haber_n_uno( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'] );

			if ( $row['naturaleza'] == 'D' )
			{
				$saldo_final = round( $saldo_inicial + $debe - $haber, 4 );

			}else if ( $row['naturaleza'] == 'A' )
			{
				$saldo_final = round( $saldo_inicial + $haber - $debe, 4 );

			}else if ( $row['naturaleza'] == 'O' )
			{
				$saldo_final = round( $saldo_inicial + $debe + $haber, 4 );

			}

			if ( $saldo_inicial !== 0.00 OR $debe !== 0.00 OR $haber !== 0.00 OR $saldo_final !== 0.00 )
			{
				array_push( $data->cuenta, array(
					'nivel'  => $row['nivel'],
					'codigo' => $row['codigo'],
					'nombre' => $row['cuenta'],
					'saldo_inicial' => $saldo_inicial,
					'debe'   => $debe,
					'haber'  => $haber,
					'saldo_final' => $saldo_final
				));	
			}
		}else if ( $row['nivel'] == '2' )
		{
			$saldo_inicial = saldo_inicial_n_dos( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'], $row['naturaleza'] );
			$debe  = debe_n_dos( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'] );
			$haber = haber_n_dos( $mysql, $cliente, $ejercicio, $periodo, $row['codigo'] );
			
			if ( $row['naturaleza'] == 'D' )
			{
				$saldo_final = round( $saldo_inicial + $debe - $haber, 4 );

			}else if ( $row['naturaleza'] == 'A' )
			{
				$saldo_final = round( $saldo_inicial + $haber - $debe, 4 );

			}else if ( $row['naturaleza'] == 'O' )
			{
				$saldo_final = round( $saldo_inicial + $debe + $haber, 4 );

			}

			if ( $saldo_inicial !== 0.00 OR $debe !== 0.00 OR $haber !== 0.00 OR $saldo_final !== 0.00 )
			{
				array_push( $data->cuenta, array(
					'nivel'  => $row['nivel'],
					'codigo' => $row['codigo'],
					'nombre' => $row['cuenta'],
					'saldo_inicial' => $saldo_inicial,
					'debe'   => $debe,
					'haber'  => $haber,
					'saldo_final' => $saldo_final
				));	
			}
		}

	}

	echo json_encode( $data );
?>
