<?php
	include '../../../con_intranet.php';

	function crear ( $mysql, $table, $data )
	{
		$l = count( $data );

		$query = "
			INSERT INTO $table (";

				for ( $i = 0; $i < $l; $i++ )
				{
					if ( $i == 0 )
					{
						$query .= $data[$i][0];
					}else
					{
						$query .= "," . $data[$i][0];
					}
				}

			$query .= ") VALUES (";

				for ( $i = 0; $i < $l; $i++ )
				{
					if ( $i == 0 )
					{
						$query .= "'" . $data[$i][1] . "'";
					}else
					{
						$query .= "," . "'" . $data[$i][1] . "'";
					}
				}

			$query .= ")
		";

		$mysql->query( $query );
	}

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	$data      = new stdClass;

	if ( $periodo == '01' )
	{
		$periodo_anterior = '01';
	}else
	{
		if ( intval( $periodo ) < 11 )
		{
			$periodo_anterior = intval( $periodo ) - 1;

			$periodo_anterior = '0' . strval( $periodo_anterior );
		}else
		{
			$periodo_anterior = intval( $periodo ) - 1;
		}
	}

	// ISR
	// Ingresos e ISR retenido acreditable de periodos anteriores
	$ingreso_anterior                  = 0;
	$ISR_retenido_acreditable_anterior = 0;

	if ( $periodo !== '01' )
	{
		$query = "SELECT 
			importe, 
			ISR_retenido,
			aplicacion 

		FROM ISR 

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		efecto    = 'Ingresos'   AND

		periodo BETWEEN '01' AND '$periodo_anterior' AND

		( aplicacion = 'Ingreso acumulable' OR aplicacion = 'Nota de crédito' )";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( $row['aplicacion'] == 'Ingreso acumulable' )
			{
				$ingreso_anterior                  += $row['importe'];

				$ISR_retenido_acreditable_anterior += $row['ISR_retenido'];

			}else if ( $row['aplicacion'] == 'Nota de crédito' )
			{
				$ingreso_anterior                   = $ingreso_anterior - ( $row['importe'] );

				$ISR_retenido_acreditable_anterior -= $row['ISR_retenido'];

			}
		}
	}

	// Ingresos e ISR retenido acreditable del periodo
	$ingresos                 = 0;
	$ISR_retenido_acreditable = 0;

	$query = "SELECT 
	importe, 
	ISR_retenido,
	aplicacion 

	FROM ISR 

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND
	periodo   = '$periodo'   AND 
	efecto    = 'Ingresos'   AND

	( aplicacion = 'Ingreso acumulable' OR aplicacion = 'Nota de crédito' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['aplicacion'] == 'Ingreso acumulable' )
		{
			$ingresos                 += $row['importe'];

			$ISR_retenido_acreditable += $row['ISR_retenido'];

		}else if ( $row['aplicacion'] == 'Nota de crédito' )
		{
			$ingresos                  = $ingresos - ( $row['importe'] );

			$ISR_retenido_acreditable -= $row['ISR_retenido'];

		}
	}

	// Deducciones autorizadas, PTU pagada en el ejercicio
	// y pagos provisionales de ISR de periodos anteriores
	$deduccion_anterior  = 0;
	$PTU_pagada          = 0;
	$pagos_provisionales = 0;

	if ( $periodo_anterior == '01' )
	{
		$deduccion_anterior = 0;
	}else
	{
		$query = "SELECT 
			clase,
			importe

		FROM historial_de_saldos

		WHERE 

		cliente    = '$cliente'   AND 
		ejercicio  = '$ejercicio' AND

		periodo BETWEEN '01' AND '$periodo_anterior'";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( $row['clase'] == 'Deducciones del periodo' )
			{
				$deduccion_anterior += $row['importe'];

			}else if ( $row['clase'] == 'PTU pagada' )
			{
				$PTU_pagada += $row['importe'];

			}else if ( $row['clase'] == 'Pagos provisionales' )
			{
				$pagos_provisionales += $row['importe'];

			}
		}
	}

	// Pérdidas de ejericios anteriores
	$ejercicio_anterior = intval( $ejercicio ) - 1;

	$query = "SELECT 
		SUM( importe ) AS total

	FROM ISR_perdidas

	WHERE 

	cliente = '$cliente' AND 

	ejercicio  BETWEEN '2015' AND '$ejercicio_anterior'";

	$perdidas_ejercicios_anteriores = floatval( $mysql->query( $query )->fetch_object()->total );

	// Deducciones autorizadas e ISR retenido trasladado del periodo
	$deducciones_autorizadas = 0;
	$ISR_retenido_trasladado = 0;

	$query = "SELECT 
		importe, 
		ISR_retenido, 
		aplicacion 

	FROM ISR 

	WHERE 

	cliente   = '$cliente'    AND 
	ejercicio = '$ejercicio'  AND 
	periodo   = '$periodo'    AND
	efecto    = 'Deducciones' AND

	( aplicacion = 'Deducción autorizada' OR aplicacion = 'Nota de crédito' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['aplicacion'] == 'Deducción autorizada' )
		{
			$deducciones_autorizadas += $row['importe'];

			$ISR_retenido_trasladado += $row['ISR_retenido'];

		}else if ( $row['aplicacion'] == 'Nota de crédito' )
		{
			$deducciones_autorizadas -= $row['importe'];

			$ISR_retenido_trasladado += $row['ISR_retenido'];

		}
	}

	// ISR Resumen
	$data->ingresos_de_periodos_anteriores = $ingreso_anterior;
	$data->ingresos_del_periodo            = $ingresos;
	$data->total_de_ingresos_gravados      = $ingreso_anterior + $ingresos;

	$data->deducciones_autorizadas_de_periodos_anteriores = $deduccion_anterior;
	

	$data->PTU_pagada                                 = $PTU_pagada;
	$data->perdidas_fiscales_de_ejercicios_anteriores = $perdidas_ejercicios_anteriores;

	$data->pagos_provisionales_efectuados_con_anterioridad = $pagos_provisionales;
	$data->impuesto_retenido_de_periodos_anteriores        = $ISR_retenido_acreditable_anterior;
	$data->impuesto_retenido_del_periodo                   = $ISR_retenido_acreditable;
	$data->total_de_impuestos_retenidos                    = $ISR_retenido_acreditable_anterior + $ISR_retenido_acreditable;

	// IVA
	$general_trasladado       = 0;
	$cero_trasladado          = 0;
	$exento_trasladado        = 0;
	$IVA_retenido_acreditable = 0;

	$query = "SELECT 
		periodo,
		general,
		cero,
		exento, 
		IVA_retenido,
		aplicacion 

	FROM IVA 

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND
	periodo   = '$periodo'   AND 
	efecto    = 'Ingresos'   AND

	( aplicacion = 'Ingreso acumulable' OR aplicacion = 'Nota de crédito' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['aplicacion'] == 'Ingreso acumulable' )
		{
			$general_trasladado       += $row['general'];

			$cero_trasladado          += $row['cero'];

			$exento_trasladado        += $row['exento'];

			$IVA_retenido_acreditable += $row['IVA_retenido'];

		}else if ( $row['aplicacion'] == 'Nota de crédito' )
		{
			$general_trasladado       -= $row['general'];

			$cero_trasladado          -= $row['cero'];

			$exento_trasladado        -= $row['exento'];

			$IVA_retenido_acreditable -= $row['IVA_retenido'];

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

	// ESPECIAL
	$data->deducciones_autorizadas_del_periodo = $deducciones_autorizadas + $IVA_deducible; // segmento de ISR
	// ESPECIAL

	$data->total_de_deducciones_autorizadas = $data->deducciones_autorizadas_de_periodos_anteriores + $data->deducciones_autorizadas_del_periodo;

	$data->base_gravable_del_pago_provisional = $data->total_de_ingresos_gravados - 
												$data->total_de_deducciones_autorizadas - 
												$data->PTU_pagada - 
												$data->perdidas_fiscales_de_ejercicios_anteriores;
	
	// IVA a favor de periodos anteriores
	if ( $periodo_anterior == '01' )
	{
		$fin = ( intval( $ejercicio ) -1 ) . '-12-01';
	}else
	{
		$fin = $ejercicio . '-' . $periodo_anterior . '-01';
	}

	$query = "SELECT 
	SUM( importe ) AS total

	FROM saldos_a_favor

	WHERE 

	cliente = '$cliente' AND 

	fecha_de_referencia  BETWEEN '2015-01-01' AND '$fin'";

	$IVA_a_favor_anterior = floatval( $mysql->query( $query )->fetch_object()->total );


	// IVA resumen
	$data->tasa_del_16 = $general_trasladado;
	$data->tasa_del_0 = $cero_trasladado;
	$data->exento     = $exento_trasladado;
	$data->proporcion = $factor;
	$data->IVA_trasladado = $IVA_trasladado;
	$data->IVA_acreditable = $IVA_acreditable;
	$data->IVA_retenido = $IVA_retenido_acreditable;
	//$data->saldos_a_favor_de_periodos_anteriores = $IVA_a_favor_anterior;

	// ISR .- Guardar saldo de deducciones autorizadas del periodo
	$query = "SELECT 
	ID

	FROM historial_de_saldos

	WHERE 

	cliente    = '$cliente'   AND 
	ejercicio  = '$ejercicio' AND
	periodo    = '$periodo'   AND 
	clase      = 'Deducciones del periodo'";

	if ( $mysql->query( $query )->num_rows > 0 )
	{
		$query = "UPDATE historial_de_saldos

		SET 

		importe = '$data->deducciones_autorizadas_del_periodo'

		WHERE

		cliente    = '$cliente'   AND 
		ejercicio  = '$ejercicio' AND
		periodo    = '$periodo'   AND 
		clase      = 'Deducciones del periodo'";

		$mysql->query( $query );
	}else
	{
		crear(
			$mysql,
			'historial_de_saldos',
			[
				[ 'cliente', $cliente ],
				[ 'ejercicio', $ejercicio ],
				[ 'periodo', $periodo ],
				[ 'clase', 'Deducciones del periodo' ],
				[ 'importe', $data->deducciones_autorizadas_del_periodo ]
			]
		);
	}

	// IVA .- Guardar saldos a favor del periodo
	// Paso 1 : Obtenemos resultados
	$resultado = $data->IVA_trasladado - $data->IVA_acreditable - $data->IVA_retenido;

	if ( $resultado > 0 ) // Cantidad a cargo
	{
		$data->cantidad_a_favor = 0;

		$data->cantidad_a_cargo = $resultado;

		// Consultamos si tenemos saldos a favor pendientes de acreditar
		if ( $IVA_a_favor_anterior > 0 )
		{
			if ( $data->cantidad_a_cargo >= $IVA_a_favor_anterior ) // IVA por pagar
			{
				$data->IVA_a_cargo = $data->cantidad_a_cargo - $IVA_a_favor_anterior;

				$saldo = $IVA_a_favor_anterior * -1;

				$data->saldo_a_favor_de_periodos_anteriores = $IVA_a_favor_anterior;

			}else // IVA saldado y con remanente
			{
				$data->IVA_a_cargo = 0;

				$saldo = $data->cantidad_a_cargo * -1;

			}
		}else
		{
			$data->IVA_a_cargo = $resultado;
		}

		$query = "SELECT 
		ID

		FROM saldos_a_favor

		WHERE 

		cliente    = '$cliente'   AND 
		ejercicio  = '$ejercicio' AND
		periodo    = '$periodo'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			$query = "UPDATE saldos_a_favor

			SET 

			importe = '$saldo'

			WHERE

			cliente    = '$cliente'   AND 
			ejercicio  = '$ejercicio' AND
			periodo    = '$periodo'";

			$mysql->query( $query );
		}else
		{
			crear(
				$mysql,
				'saldos_a_favor',
				[
					[ 'cliente', $cliente ],
					[ 'ejercicio', $ejercicio ],
					[ 'periodo', $periodo ],
					[ 'fecha_de_referencia', $ejercicio . '-' . $periodo . '-01' ],
					[ 'importe', $saldo ]
				]
			);
		}

	}else // IVA a favor
	{
		$data->cantidad_a_favor = abs( $resultado );

		$data->cantidad_a_cargo = 0;

		$data->saldo_a_favor_de_periodos_anteriores = 0;

		$data->IVA_a_cargo = 0;

		$query = "SELECT 
		ID

		FROM saldos_a_favor

		WHERE 

		cliente    = '$cliente'   AND 
		ejercicio  = '$ejercicio' AND
		periodo    = '$periodo'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			$query = "UPDATE saldos_a_favor

			SET 

			importe = '$data->cantidad_a_favor'

			WHERE

			cliente    = '$cliente'   AND 
			ejercicio  = '$ejercicio' AND
			periodo    = '$periodo'";

			$mysql->query( $query );
		}else
		{
			crear(
				$mysql,
				'saldos_a_favor',
				[
					[ 'cliente', $cliente ],
					[ 'ejercicio', $ejercicio ],
					[ 'periodo', $periodo ],
					[ 'fecha_de_referencia', $ejercicio . '-' . $periodo . '-01' ],
					[ 'importe', $data->cantidad_a_favor ]
				]
			);
		}
	}

	echo json_encode( $data );
?>
