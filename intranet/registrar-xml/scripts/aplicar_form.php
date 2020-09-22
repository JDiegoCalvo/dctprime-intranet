<?php
	include '../../../con_intranet.php';

	include 'funciones.php';

	date_default_timezone_set( 'America/Mexico_City' );

	$total = $_POST['general'] +
			 $_POST['cero']    +
			 $_POST['exento'];

	$importe = $_POST['general'] +
			   $_POST['cero']    +
			   $_POST['exento']  +
			   ( $_POST['general'] * 0.16 );

	$neto = $_POST['general'] +
			$_POST['cero']    +
			$_POST['exento']  +
			( $_POST['general'] * 0.16 ) -
			$_POST['ISR_retenido'] -
			$_POST['IVA_retenido'];

	if ( $_POST['RFC'] == 'XAXX010101000' )
	{
		$folio_ingresos = random_int( 11111111, 99999999 );

		// Registrar en contabilidad
		crear(
			$mysql,
			'diario',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
				[ 'balance', $_POST['fecha_de_pago'] ],
				[ 'creacion', date( 'Y-m-d H:i:s' ) ],
				[ 'n_uno', explode( ',', $_POST['forma_de_pago'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['forma_de_pago'] )[1] ],
				[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
				[ 'debe', $neto ],
				[ 'folio_id', $folio_ingresos ],
				[ 'libro', 'ingresos' ],
				[ 'descripcion', $_POST['descripcion_realizacion'] ]
			]
		);

		crear(
			$mysql,
			'diario',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
				[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
				[ 'creacion', date( 'Y-m-d H:i:s' ) ],
				[ 'n_uno', explode( ',', $_POST['cuenta'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['cuenta'] )[1] ],
				[ 'haber', $total ],
				[ 'folio_id', $folio_ingresos ],
				[ 'libro', 'ingresos' ],
				[ 'descripcion', $_POST['descripcion_provision'] ]
			]
		);

		if ( $_POST['general'] > 0 )
		{
			crear(
				$mysql,
				'diario',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
					[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
					[ 'creacion', date( 'Y-m-d H:i:s' ) ],
					[ 'n_uno', '208' ],
					[ 'n_dos', '01' ],
					[ 'haber', $_POST['general'] * 0.16 ],
					[ 'folio_id', $folio_ingresos ],
					[ 'libro', 'ingresos' ],
					[ 'descripcion', $_POST['descripcion_provision'] ]
				]
			);
		}

		crear(
			$mysql,
			'CFDI_relacionados',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'UUID', $_POST['folio_fiscal'] ],
				[ 'serie', $_POST['serie'] ],
				[ 'folio', $_POST['folio'] ],
				[ 'importe', $neto ],
				[ 'RFC', $_POST['RFC'] ],
				[ 'folio_id', $folio_ingresos ]
			]
		);

		if ( explode( ',', $_POST['forma_de_pago'] )[0] == '102' )
		{
			if ( $_POST['FP'] == 'Transferencia' )
			{
				$banco_origen = banco( $mysql, explode( ',', $_POST['forma_de_pago'] )[2] );

				crear(
					$mysql,
					'formas_de_pago',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'forma_de_pago', 'Transferencia' ],
						[ 'cta_origen', $banco_origen['cta'] ],
						[ 'banco_origen', $banco_origen['banco'] ],
						[ 'monto', $neto ],
						[ 'cta_destino', $_POST['cta_destino'] ],
						[ 'banco_destino', $_POST['banco_destino'] ],
						[ 'fecha', $_POST['fecha_de_pago'] ],
						[ 'beneficiario', $_POST['nombre'] ],
						[ 'RFC', $_POST['RFC'] ],
						[ 'folio_id', $folio_ingresos ]
					]
				);

			}else if ( $_POST['FP'] == 'Cheque' )
			{
				$banco_origen = banco( $mysql, explode( ',', $_POST['forma_de_pago'] )[2] );

				crear(
					$mysql,
					'formas_de_pago',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'forma_de_pago', 'Cheque' ],
						[ 'no_de_cheque', $_POST['no_de_cheque'] ],
						[ 'cta_origen', $banco_origen['cta'] ],
						[ 'banco_origen', $banco_origen['banco'] ],
						[ 'monto', $neto ],
						[ 'fecha', $_POST['fecha_de_pago'] ],
						[ 'beneficiario', $_POST['nombre'] ],
						[ 'RFC', $_POST['RFC'] ],
						[ 'folio_id', $folio_ingresos ]
					]
				);
			}
		}

		// IVA
		$operacion = 'Otros';

		$clase = 'Otros';

		crear(
			$mysql,
			'IVA',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
				[ 'folio_fiscal', $_POST['folio_fiscal'] ],
				[ 'clase', $clase ],
				[ 'tercero', 'Cliente Nacional' ],
				[ 'operacion', $operacion ],
				[ 'RFC', $_POST['RFC'] ],
				[ 'general', $_POST['general'] ],
				[ 'cero', $_POST['cero'] ],
				[ 'exento', $_POST['exento'] ],
				[ 'IVA_retenido', $_POST['IVA_retenido'] ],
				[ 'factor', 1 ],
				[ 'folio_id', $folio_ingresos ],
				[ 'efecto', 'Ingresos' ],
				[ 'aplicacion', 'Ingreso acumulable' ]
			]
		);

		// ISR
		crear(
			$mysql,
			'ISR',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
				[ 'folio_fiscal', $_POST['folio_fiscal'] ],
				[ 'operacion', $_POST['operacion'] ],
				[ 'RFC', $_POST['RFC'] ],
				[ 'importe', $total ],
				[ 'ISR_retenido', $_POST['ISR_retenido'] ],
				[ 'folio_id', $folio_ingresos ],
				[ 'efecto', 'Ingresos' ],
				[ 'aplicacion', 'Ingreso acumulable' ]
			]
		);
	}else
	{
		if ( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Provisión' )
		{
			$folio_diario = random_int( 11111111, 99999999 );

			// Registrar en contabilidad
			crear(
				$mysql,
				'diario',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha'] ],
					[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
					[ 'creacion', date( 'Y-m-d H:i:s' ) ],
					[ 'n_uno', explode( ',', $_POST['clientes'] )[0] ],
					[ 'n_dos', explode( ',', $_POST['clientes'] )[1] ],
					[ 'n_tres', cliente( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['clientes'] )[1] ) ],
					[ 'debe', $importe ],
					[ 'folio_id', $folio_diario ],
					[ 'libro', 'diario' ],
					[ 'descripcion', $_POST['descripcion_provision'] ]
				]
			);

			crear(
				$mysql,
				'diario',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha'] ],
					[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
					[ 'creacion', date( 'Y-m-d H:i:s' ) ],
					[ 'n_uno', explode( ',', $_POST['cuenta'] )[0] ],
					[ 'n_dos', explode( ',', $_POST['cuenta'] )[1] ],
					[ 'haber', $total ],
					[ 'folio_id', $folio_diario ],
					[ 'libro', 'diario' ],
					[ 'descripcion', $_POST['descripcion_provision'] ]
				]
			);

			if ( $_POST['general'] > 0 )
			{
				crear(
					$mysql,
					'diario',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'fecha', $_POST['fecha'] ],
						[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
						[ 'creacion', date( 'Y-m-d H:i:s' ) ],
						[ 'n_uno', '209' ],
						[ 'n_dos', '01' ],
						[ 'haber', $_POST['general'] * 0.16 ],
						[ 'folio_id', $folio_diario ],
						[ 'libro', 'diario' ],
						[ 'descripcion', $_POST['descripcion_provision'] ]
					]
				);
			}

			crear(
				$mysql,
				'CFDI_relacionados',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'UUID', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'folio_id', $folio_diario ]
				]
			);
		}

		if ( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Realización' )
		{
			$folio_ingresos = random_int( 11111111, 99999999 );

			// Registrar en contabilidad
			crear(
				$mysql,
				'diario',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
					[ 'balance', $_POST['fecha_de_pago'] ],
					[ 'creacion', date( 'Y-m-d H:i:s' ) ],
					[ 'n_uno', explode( ',', $_POST['forma_de_pago'] )[0] ],
					[ 'n_dos', explode( ',', $_POST['forma_de_pago'] )[1] ],
					[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
					[ 'debe', $neto ],
					[ 'folio_id', $folio_ingresos ],
					[ 'libro', 'ingresos' ],
					[ 'descripcion', $_POST['descripcion_realizacion'] ]
				]
			);

			if ( $_POST['general'] > 0 )
			{
				crear(
					$mysql,
					'diario',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
						[ 'balance', $_POST['fecha_de_pago'] ],
						[ 'creacion', date( 'Y-m-d H:i:s' ) ],
						[ 'n_uno', '209' ],
						[ 'n_dos', '01' ],
						[ 'debe', $_POST['general'] * 0.16 ],
						[ 'folio_id', $folio_ingresos ],
						[ 'libro', 'ingresos' ],
						[ 'descripcion', $_POST['descripcion_realizacion'] ]
					]
				);
			}

			if ( $_POST['ISR_retenido'] > 0 )
			{
				crear(
					$mysql,
					'diario',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
						[ 'balance', $_POST['fecha_de_pago'] ],
						[ 'creacion', date( 'Y-m-d H:i:s' ) ],
						[ 'n_uno', '113' ],
						[ 'n_dos', '02' ],
						[ 'debe', $_POST['ISR_retenido'] ],
						[ 'folio_id', $folio_ingresos ],
						[ 'libro', 'ingresos' ],
						[ 'descripcion', $_POST['descripcion_realizacion'] ]
					]
				);
			}

			if ( $_POST['IVA_retenido'] > 0 )
			{
				crear(
					$mysql,
					'diario',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
						[ 'balance', $_POST['fecha_de_pago'] ],
						[ 'creacion', date( 'Y-m-d H:i:s' ) ],
						[ 'n_uno', '113' ],
						[ 'n_dos', '01' ],
						[ 'debe', $_POST['IVA_retenido'] ],
						[ 'folio_id', $folio_ingresos ],
						[ 'libro', 'ingresos' ],
						[ 'descripcion', $_POST['descripcion_realizacion'] ]
					]
				);
			}

			crear(
				$mysql,
				'diario',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
					[ 'balance', $_POST['fecha_de_pago'] ],
					[ 'creacion', date( 'Y-m-d H:i:s' ) ],
					[ 'n_uno', explode( ',', $_POST['clientes'] )[0] ],
					[ 'n_dos', explode( ',', $_POST['clientes'] )[1] ],
					[ 'n_tres', cliente( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['clientes'] )[1] ) ],
					[ 'haber', $importe ],
					[ 'folio_id', $folio_ingresos ],
					[ 'libro', 'ingresos' ],
					[ 'descripcion', $_POST['descripcion_realizacion'] ]
				]
			);

			if ( $_POST['general'] > 0 )
			{
				crear(
					$mysql,
					'diario',
					[
						[ 'cliente', $_POST['cliente'] ],
						[ 'ejercicio', $_POST['ejercicio'] ],
						[ 'periodo', $_POST['periodo'] ],
						[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
						[ 'balance', $_POST['fecha_de_pago'] ],
						[ 'creacion', date( 'Y-m-d H:i:s' ) ],
						[ 'n_uno', '208' ],
						[ 'n_dos', '01' ],
						[ 'haber', $_POST['general'] * 0.16 ],
						[ 'folio_id', $folio_ingresos ],
						[ 'libro', 'ingresos' ],
						[ 'descripcion', $_POST['descripcion_realizacion'] ]
					]
				);
			}

			if ( explode( ',', $_POST['forma_de_pago'] )[0] == '102' )
			{
				if ( $_POST['FP'] == 'Transferencia' )
				{
					$banco_origen = banco( $mysql, explode( ',', $_POST['forma_de_pago'] )[2] );

					crear(
						$mysql,
						'formas_de_pago',
						[
							[ 'cliente', $_POST['cliente'] ],
							[ 'ejercicio', $_POST['ejercicio'] ],
							[ 'periodo', $_POST['periodo'] ],
							[ 'forma_de_pago', 'Transferencia' ],
							[ 'cta_origen', $banco_origen['cta'] ],
							[ 'banco_origen', $banco_origen['banco'] ],
							[ 'monto', $neto ],
							[ 'cta_destino', $_POST['cta_destino'] ],
							[ 'banco_destino', $_POST['banco_destino'] ],
							[ 'fecha', $_POST['fecha_de_pago'] ],
							[ 'beneficiario', $_POST['nombre'] ],
							[ 'RFC', $_POST['RFC'] ],
							[ 'folio_id', $folio_ingresos ]
						]
					);

				}else if ( $_POST['FP'] == 'Cheque' )
				{
					$banco_origen = banco( $mysql, explode( ',', $_POST['forma_de_pago'] )[2] );

					crear(
						$mysql,
						'formas_de_pago',
						[
							[ 'cliente', $_POST['cliente'] ],
							[ 'ejercicio', $_POST['ejercicio'] ],
							[ 'periodo', $_POST['periodo'] ],
							[ 'forma_de_pago', 'Cheque' ],
							[ 'no_de_cheque', $_POST['no_de_cheque'] ],
							[ 'cta_origen', $banco_origen['cta'] ],
							[ 'banco_origen', $banco_origen['banco'] ],
							[ 'monto', $neto ],
							[ 'fecha', $_POST['fecha_de_pago'] ],
							[ 'beneficiario', $_POST['nombre'] ],
							[ 'RFC', $_POST['RFC'] ],
							[ 'folio_id', $folio_ingresos ]
						]
					);
				}
			}

			crear(
				$mysql,
				'CFDI_relacionados',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'UUID', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'folio_id', $folio_ingresos ]
				]
			);

			// IVA
			if ( explode( ',', $_POST['clientes'] )[1] == '01' OR explode( ',', $_POST['clientes'] )[1] == '03' )
			{
				$tercero = 'Cliente Nacional';
			}else if ( explode( ',', $_POST['clientes'] )[1] == '02' OR explode( ',', $_POST['clientes'] )[1] == '04' )
			{
				$tercero = 'Cliente Extranjero';
			}

			$operacion = 'Otros';

			$clase = 'Otros';

			crear(
				$mysql,
				'IVA',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 ) ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'clase', $clase ],
					[ 'tercero', $tercero ],
					[ 'operacion', $operacion ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'general', $_POST['general'] ],
					[ 'cero', $_POST['cero'] ],
					[ 'exento', $_POST['exento'] ],
					[ 'IVA_retenido', $_POST['IVA_retenido'] ],
					[ 'factor', 1 ],
					[ 'folio_id', $folio_ingresos ],
					[ 'efecto', 'Ingresos' ],
					[ 'aplicacion', 'Ingreso acumulable' ]
				]
			);
		}

		if ( $_POST['aplicar_ISR'] == 'Provisión' )
		{
			$folio_ISR = $folio_diario;

			$fecha_de_registro = $_POST['fecha'];

		}else if ( $_POST['aplicar_ISR'] == 'Realización' )
		{
			$folio_ISR = $folio_ingresos;
			
			$fecha_de_registro = $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 );

		}

		// ISR
		crear(
			$mysql,
			'ISR',
			[
				[ 'cliente', $_POST['cliente'] ],
				[ 'ejercicio', $_POST['ejercicio'] ],
				[ 'periodo', $_POST['periodo'] ],
				[ 'fecha', $fecha_de_registro ],
				[ 'folio_fiscal', $_POST['folio_fiscal'] ],
				[ 'operacion', $_POST['operacion'] ],
				[ 'RFC', $_POST['RFC'] ],
				[ 'importe', $total ],
				[ 'ISR_retenido', $_POST['ISR_retenido'] ],
				[ 'folio_id', $folio_ISR ],
				[ 'efecto', 'Ingresos' ],
				[ 'aplicacion', 'Ingreso acumulable' ]
			]
		);
	}
?>