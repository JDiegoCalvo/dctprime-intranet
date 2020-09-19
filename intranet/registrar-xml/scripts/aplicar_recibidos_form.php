<?php
	include '../../connection.php';

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

	if ( 

		( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Provisión' ) AND

		$_POST['ISR'] == 'Deducible'
	)
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
				[ 'n_uno', explode( ',', $_POST['cuenta'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['cuenta'] )[1] ],
				[ 'debe', $total ],
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
					[ 'n_uno', '119' ],
					[ 'n_dos', '01' ],
					[ 'debe', $_POST['general'] * 0.16 ],
					[ 'folio_id', $folio_diario ],
					[ 'libro', 'diario' ],
					[ 'descripcion', $_POST['descripcion_provision'] ]
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
				[ 'fecha', $_POST['fecha'] ],
				[ 'balance', substr( $_POST['fecha'], 0, 10 ) ],
				[ 'creacion', date( 'Y-m-d H:i:s' ) ],
				[ 'n_uno', explode( ',', $_POST['proveedor'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['proveedor'] )[1] ],
				[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
				[ 'haber', $importe ],
				[ 'folio_id', $folio_diario ],
				[ 'libro', 'diario' ],
				[ 'descripcion', $_POST['descripcion_provision'] ]
			]
		);

		if ( $_POST['registro'] == 'Provisión'  )
		{
			$codigo = explode( ',', $_POST['proveedor'] )[0] . '.' . explode( ',', $_POST['proveedor'] )[1];

			crear(
				$mysql,
				'cxp',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'codigo', $codigo ],
					[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ]
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

		if ( $_POST['registro'] == 'Provisión' )
		{
			// ISR
			crear(
				$mysql,
				'ISR',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha'] ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'operacion', $_POST['operacion'] ],
					[ 'clasificacion', $_POST['clasificacion'] ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'importe', $importe ],
					[ 'ISR_retenido', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_diario ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'No deducible' ]
				]
			);

			// IVA
			if ( explode( ',', $_POST['proveedor'] )[1] == '01' OR explode( ',', $_POST['proveedor'] )[1] == '03' )
			{
				$tercero = 'Proveedor Nacional';
			}else if ( explode( ',', $_POST['proveedor'] )[1] == '02' OR explode( ',', $_POST['proveedor'] )[1] == '04' )
			{
				$tercero = 'Proveedor Extranjero';
			}

			if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
			{
				$operacion = 'Servicios profesionales';
			}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
			{
				$operacion = 'Arrendamiento';
			}else
			{
				$operacion = 'Otros';
			}

			$clase = 'Otros';

			crear(
				$mysql,
				'IVA',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'fecha', $_POST['fecha'] ],
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
					[ 'folio_id', $folio_diario ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'No deducible' ]
				]
			);
		}
	}else if ( 

		( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Provisión' ) AND

		$_POST['ISR'] == 'No deducible'
	)
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
				[ 'n_uno', explode( ',', $_POST['cuenta'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['cuenta'] )[1] ],
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
				[ 'n_uno', explode( ',', $_POST['proveedor'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['proveedor'] )[1] ],
				[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
				[ 'haber', $importe ],
				[ 'folio_id', $folio_diario ],
				[ 'libro', 'diario' ],
				[ 'descripcion', $_POST['descripcion_provision'] ]
			]
		);

		if ( $_POST['registro'] == 'Provisión'  )
		{
			$codigo = explode( ',', $_POST['proveedor'] )[0] . '.' . explode( ',', $_POST['proveedor'] )[1];

			crear(
				$mysql,
				'cxp',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'codigo', $codigo ],
					[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ]
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

	if ( 

		( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Realización' ) AND 

		$_POST['ISR'] == 'Deducible' 
	)
	{
		$folio_egresos = random_int( 11111111, 99999999 );

		if ( explode( ',', $_POST['forma_de_pago'] )[0] == '217' OR explode( ',', $_POST['forma_de_pago'] )[0] == '202' )
		{
			$libro = 'diario';
		}else
		{
			$libro = 'egresos';
		}

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
				[ 'n_uno', explode( ',', $_POST['proveedor'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['proveedor'] )[1] ],
				[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
				[ 'debe', $importe ],
				[ 'folio_id', $folio_egresos ],
				[ 'libro', $libro ],
				[ 'descripcion', $_POST['descripcion_realizacion'] ]
			]
		);

		if ( $_POST['general'] > 0 )
		{
			if ( $_POST['IVA_retenido'] > 0 )
			{
				$IVA_acreditable = ( $_POST['general'] * 0.16 ) - $_POST['IVA_retenido'];
			}else
			{
				$IVA_acreditable = $_POST['general'] * 0.16;
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
					[ 'n_uno', '118' ],
					[ 'n_dos', '01' ],
					[ 'debe', $IVA_acreditable ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
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
				[ 'n_uno', explode( ',', $_POST['forma_de_pago'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['forma_de_pago'] )[1] ],
				[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
				[ 'haber', $neto ],
				[ 'folio_id', $folio_egresos ],
				[ 'libro', $libro ],
				[ 'descripcion', $_POST['descripcion_realizacion'] ]
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
						[ 'folio_id', $folio_egresos ]
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
						[ 'folio_id', $folio_egresos ]
					]
				);
			}
		}

		if ( $_POST['general'] > 0 )
		{
			if ( $_POST['IVA_retenido'] > 0 )
			{
				$IVA_acreditable = ( $_POST['general'] * 0.16 ) - $_POST['IVA_retenido'];
			}else
			{
				$IVA_acreditable = $_POST['general'] * 0.16;
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
					[ 'n_uno', '119' ],
					[ 'n_dos', '01' ],
					[ 'haber', $IVA_acreditable ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
					[ 'descripcion', $_POST['descripcion_realizacion'] ]
				]
			);
		}

		if ( $_POST['ISR_retenido'] > 0 )
		{
			if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
			{
				$n_dos = '04';
			}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
			{
				$n_dos = '03';
			}else
			{
				$n_dos = '12';
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
					[ 'n_uno', '216' ],
					[ 'n_dos', $n_dos ],
					[ 'haber', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
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
					[ 'n_uno', '216' ],
					[ 'n_dos', '10' ],
					[ 'haber', $_POST['IVA_retenido'] ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
					[ 'descripcion', $_POST['descripcion_realizacion'] ]
				]
			);
		}

		if ( explode( ',', $_POST['forma_de_pago'] )[0] == '217' OR explode( ',', $_POST['forma_de_pago'] )[0] == '202' )
		{
			crear(
				$mysql,
				'cxp',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'codigo', explode( ',', $_POST['forma_de_pago'] )[0] . '.' . explode( ',', $_POST['forma_de_pago'] )[1] ],
					[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ]
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
				[ 'folio_id', $folio_egresos ]
			]
		);

		if ( $_POST['ISR'] == 'Deducible'  )
		{
			// IVA
			if ( explode( ',', $_POST['proveedor'] )[1] == '01' OR explode( ',', $_POST['proveedor'] )[1] == '03' )
			{
				$tercero = 'Proveedor Nacional';
			}else if ( explode( ',', $_POST['proveedor'] )[1] == '02' OR explode( ',', $_POST['proveedor'] )[1] == '04' )
			{
				$tercero = 'Proveedor Extranjero';
			}

			if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
			{
				$operacion = 'Servicios profesionales';
			}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
			{
				$operacion = 'Arrendamiento';
			}else
			{
				$operacion = 'Otros';
			}

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
					[ 'folio_id', $folio_egresos ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'Deducción autorizada' ]
				]
			);
		}
	}else 	if ( 

		( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Realización' ) AND 

		$_POST['ISR'] == 'No deducible' 
	)
	{
		$folio_egresos = random_int( 11111111, 99999999 );

		if ( explode( ',', $_POST['forma_de_pago'] )[0] == '217' OR explode( ',', $_POST['forma_de_pago'] )[0] == '202' )
		{
			$libro = 'diario';
		}else
		{
			$libro = 'egresos';
		}

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
				[ 'n_uno', explode( ',', $_POST['proveedor'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['proveedor'] )[1] ],
				[ 'n_tres', proveedor( $mysql, $_POST['cliente'], $_POST['nombre'], $_POST['RFC'], explode( ',', $_POST['proveedor'] )[1] ) ],
				[ 'debe', $importe ],
				[ 'folio_id', $folio_egresos ],
				[ 'libro', $libro ],
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
				[ 'balance', $_POST['fecha_de_pago'] ],
				[ 'creacion', date( 'Y-m-d H:i:s' ) ],
				[ 'n_uno', explode( ',', $_POST['forma_de_pago'] )[0] ],
				[ 'n_dos', explode( ',', $_POST['forma_de_pago'] )[1] ],
				[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
				[ 'haber', $importe ],
				[ 'folio_id', $folio_egresos ],
				[ 'libro', $libro ],
				[ 'descripcion', $_POST['descripcion_realizacion'] ]
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
						[ 'folio_id', $folio_egresos ]
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
						[ 'folio_id', $folio_egresos ]
					]
				);
			}
		}

		if ( $_POST['ISR_retenido'] > 0 )
		{
			if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
			{
				$n_dos = '04';
			}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
			{
				$n_dos = '03';
			}else
			{
				$n_dos = '12';
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
					[ 'n_uno', '216' ],
					[ 'n_dos', $n_dos ],
					[ 'haber', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
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
					[ 'n_uno', '216' ],
					[ 'n_dos', '10' ],
					[ 'haber', $_POST['IVA_retenido'] ],
					[ 'folio_id', $folio_egresos ],
					[ 'libro', $libro ],
					[ 'descripcion', $_POST['descripcion_realizacion'] ]
				]
			);
		}

		if ( explode( ',', $_POST['forma_de_pago'] )[0] == '217' OR explode( ',', $_POST['forma_de_pago'] )[0] == '202' )
		{
			crear(
				$mysql,
				'cxp',
				[
					[ 'cliente', $_POST['cliente'] ],
					[ 'ejercicio', $_POST['ejercicio'] ],
					[ 'periodo', $_POST['periodo'] ],
					[ 'codigo', '217.01' ],
					[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
					[ 'folio_fiscal', $_POST['folio_fiscal'] ],
					[ 'serie', $_POST['serie'] ],
					[ 'folio', $_POST['folio'] ],
					[ 'importe', $neto ]
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
				[ 'folio_id', $folio_egresos ]
			]
		);

		// IVA
		if ( explode( ',', $_POST['proveedor'] )[1] == '01' OR explode( ',', $_POST['proveedor'] )[1] == '03' )
		{
			$tercero = 'Proveedor Nacional';
		}else if ( explode( ',', $_POST['proveedor'] )[1] == '02' OR explode( ',', $_POST['proveedor'] )[1] == '04' )
		{
			$tercero = 'Proveedor Extranjero';
		}

		if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
		{
			$operacion = 'Servicios profesionales';
		}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
		{
			$operacion = 'Arrendamiento';
		}else
		{
			$operacion = 'Otros';
		}

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
				[ 'folio_id', $folio_egresos ],
				[ 'efecto', 'Deducciones' ],
				[ 'aplicacion', 'No deducible' ]
			]
		);
	}

	if ( $_POST['aplicar_ISR'] == 'Provisión' )
	{
		$folio_ISR = $folio_diario;

		$fecha_de_registro = $_POST['fecha'];
	}else if ( $_POST['aplicar_ISR'] == 'Realización' )
	{
		$folio_ISR = $folio_egresos;
		
		$fecha_de_registro = $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 );
	}

	if ( $_POST['ISR'] == 'Deducible' )
	{
		if ( $_POST['registro'] == 'De contado' )
		{
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
					[ 'clasificacion', $_POST['clasificacion'] ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'importe', $total ],
					[ 'ISR_retenido', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_ISR ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'Deducción autorizada' ]
				]
			);
		}else if ( $_POST['registro'] == 'Provisión' AND $_POST['aplicar_ISR'] == 'Provisión' )
		{
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
					[ 'clasificacion', $_POST['clasificacion'] ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'importe', $total ],
					[ 'ISR_retenido', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_ISR ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'Deducción autorizada' ]
				]
			);
		}else if ( $_POST['registro'] == 'Realización' AND $_POST['aplicar_ISR'] == 'Realización' )
		{
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
					[ 'clasificacion', $_POST['clasificacion'] ],
					[ 'RFC', $_POST['RFC'] ],
					[ 'importe', $total ],
					[ 'ISR_retenido', $_POST['ISR_retenido'] ],
					[ 'folio_id', $folio_ISR ],
					[ 'efecto', 'Deducciones' ],
					[ 'aplicacion', 'Deducción autorizada' ]
				]
			);
		}
	}else if ( $_POST['ISR'] == 'No deducible' )
	{
		if ( $_POST['registro'] == 'De contado' OR $_POST['registro'] == 'Realización' )
		{
			if ( $_POST['aplicar_ISR'] == 'Provisión' )
			{
				$folio_ISR = $folio_diario;

				$fecha_de_registro = $_POST['fecha'];

			}else if ( $_POST['aplicar_ISR'] == 'Realización' )
			{
				$folio_ISR = $folio_egresos;
				
				$fecha_de_registro = $_POST['fecha_de_pago'] . substr( $_POST['fecha'], 10, 9 );
				
			}
		}else if ( $_POST['registro'] == 'Provisión' )
		{
			$folio_ISR = $folio_diario;

			$fecha_de_registro = $_POST['fecha'];
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
				[ 'clasificacion', $_POST['clasificacion'] ],
				[ 'RFC', $_POST['RFC'] ],
				[ 'importe', $importe ],
				[ 'ISR_retenido', $_POST['ISR_retenido'] ],
				[ 'folio_id', $folio_ISR ],
				[ 'efecto', 'Deducciones' ],
				[ 'aplicacion', 'No deducible' ]
			]
		);
	}

	if ( $_POST['registro'] == 'Provisión' AND $_POST['ISR'] == 'No deducible' )
	{
		// IVA
		if ( explode( ',', $_POST['proveedor'] )[1] == '01' OR explode( ',', $_POST['proveedor'] )[1] == '03' )
		{
			$tercero = 'Proveedor Nacional';
		}else if ( explode( ',', $_POST['proveedor'] )[1] == '02' OR explode( ',', $_POST['proveedor'] )[1] == '04' )
		{
			$tercero = 'Proveedor Extranjero';
		}

		if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '34' )
		{
			$operacion = 'Servicios profesionales';
		}else if ( explode( ',', $_POST['cuenta'] )[0] == '602' AND explode( ',', $_POST['cuenta'] )[1] == '45' )
		{
			$operacion = 'Arrendamiento';
		}else
		{
			$operacion = 'Otros';
		}

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
				[ 'folio_id', $folio_diario ],
				[ 'efecto', 'Deducciones' ],
				[ 'aplicacion', 'No deducible' ]
			]
		);
	}
?>
