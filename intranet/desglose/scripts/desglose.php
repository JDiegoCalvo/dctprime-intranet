<?php
	include '../../../con_intranet.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$data = new stdClass;
	$data->CFDIs = array();

	$query = "SELECT 

		fecha,
		folio_fiscal,
		RFC,
		importe,
		ISR_retenido

	FROM ISR 

	WHERE 

	cliente    = '$cliente'    AND 
	ejercicio  = '$ejercicio'  AND 
	periodo    = '$periodo'    AND
	efecto     = 'Deducciones' AND
	aplicacion = 'Deducción autorizada'

	ORDER BY fecha ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		array_push( $data->CFDIs, array(
			'fecha'        => $row['fecha'],
			'folio_fiscal' => $row['folio_fiscal'],
			'RFC'          => $row['RFC'],
			'gravado'      => floatval( $row['importe'] ),
			'exento'       => 0,
			'ISR_retenido' => floatval( $row['ISR_retenido'] )
		));
	}

	$general_trasladado = 0;
	$cero_trasladado    = 0;
	$exento_trasladado  = 0;

	$query = "SELECT 
		periodo,
		general,
		cero,
		exento, 
		IVA_retenido,
		aplicacion 

	FROM IVA 

	WHERE 

	cliente   = '$cliente'    AND 
	ejercicio = '$ejercicio'  AND
	periodo   = '$periodo'    AND 
	efecto    = 'Ingresos' AND

	( aplicacion = 'Ingreso acumulable' OR aplicacion = 'Nota de crédito' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['aplicacion'] == 'Ingreso acumulable' )
		{
			$general_trasladado += $row['general'];

			$cero_trasladado    += $row['cero'];

			$exento_trasladado  += $row['exento'];

		}else if ( $row['aplicacion'] == 'Nota de crédito' )
		{
			$general_trasladado -= $row['general'];

			$cero_trasladado    -= $row['cero'];

			$exento_trasladado  -= $row['exento'];

		}
	}

	$IVA_trasladado = $general_trasladado * 0.16;

	if ( $exento_trasladado > 0 )
	{
		$factor = 
		(
			$general_trasladado + 
			$cero_trasladado 
		)

		/ 

		(
			$general_trasladado +
			$cero_trasladado    +
			$exento_trasladado 
		);

	}else
	{
		$factor = 1;
	}

	$data->general = $general_trasladado;
	$data->cero    = $cero_trasladado;
	$data->exento  = $exento_trasladado;
	$data->total   = $general_trasladado + $cero_trasladado + $exento_trasladado;
	$data->proporcion  = $factor;

	$general_acreditable     = 0;
	$cero_acreditable        = 0;
	$exento_acreditable      = 0;
	$IVA_retenido_trasladado = 0;
	$IVA_retenido_acreditable_pagado = 0;

	$query = "SELECT 
		periodo,
		general,
		cero,
		exento, 
		IVA_retenido,
		aplicacion 

	FROM IVA 

	WHERE 

	cliente   = '$cliente'    AND 
	ejercicio = '$ejercicio'  AND
	periodo   = '$periodo'    AND 
	efecto    = 'Deducciones' AND

	( aplicacion = 'Deducción autorizada' OR aplicacion = 'Nota de crédito' OR aplicacion = 'IVA retenido acreditable' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['aplicacion'] == 'Deducción autorizada' )
		{
			$general_acreditable     += $row['general'];

			$cero_acreditable        += $row['cero'];

			$exento_acreditable      += $row['exento'];

			$IVA_retenido_trasladado += $row['IVA_retenido'];

		}else if ( $row['aplicacion'] == 'Nota de crédito' )
		{
			$general_acreditable     -= $row['general'];

			$cero_acreditable        -= $row['cero'];

			$exento_acreditable      -= $row['exento'];

			$IVA_retenido_trasladado -= $row['IVA_retenido'];

		}else if ( $row['aplicacion'] == 'IVA retenido acreditable' )
		{
			$IVA_retenido_acreditable_pagado += $row['IVA_retenido'];

		}
	}

	$IVA_por_acreditar = ( $general_acreditable * 0.16 ) - $IVA_retenido_trasladado + $IVA_retenido_acreditable_pagado;

	$IVA_acreditable   = $IVA_por_acreditar * $factor;

	$IVA_deducible     = $IVA_por_acreditar - $IVA_acreditable;

	$data->IVA_por_acreditar = $IVA_por_acreditar;
	$data->IVA_acreditable   = $IVA_acreditable;
	$data->IVA_deducible     = $IVA_deducible;

	$data->IVA = array();

	$query = "SELECT 
	
		fecha,
		folio_fiscal,
		RFC,
		general,
		cero,
		exento,
		IVA_retenido

	FROM IVA 

	WHERE 

	cliente    = '$cliente'    AND 
	ejercicio  = '$ejercicio'  AND 
	periodo    = '$periodo'    AND
	efecto     = 'Deducciones' AND
	aplicacion = 'Deducción autorizada'

	ORDER BY fecha ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		array_push( $data->IVA, array(
			'fecha'        => $row['fecha'],
			'folio_fiscal' => $row['folio_fiscal'],
			'RFC'          => $row['RFC'],
			'general'      => floatval( $row['general'] ),
			'cero'         => floatval( $row['cero'] ),
			'exento'       => floatval( $row['exento'] ),
			'IVA_retenido' => floatval( $row['IVA_retenido'] )
		));
	}

	echo json_encode( $data );
?>
