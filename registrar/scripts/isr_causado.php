<?php
	include '../../connection.php';

	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$folio_diario = random_int( 11111111, 99999999 );

	$fecha = fecha( $_POST['ejercicio'], $_POST['periodo'] );

	$ISR_retenido = ISR_retenido();

	$ISR_a_pagar = $_POST['importe'] - $ISR_retenido;

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T23:59:59' ],
			[ 'balance', $fecha ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '611' ],
			[ 'n_dos', '01' ],
			[ 'debe', $_POST['importe'] ],
			[ 'folio_id', $folio_diario ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'Provisión del ISR causado del mes de ' . mes( $periodo ) . ' de ' . $ejercicio ]
		]
	);

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T23:59:59' ],
			[ 'balance', $fecha ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '213' ],
			[ 'n_dos', '03' ],
			[ 'haber', $ISR_a_pagar ],
			[ 'folio_id', $folio_diario ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'Provisión del ISR causado del mes de ' . mes( $periodo ) . ' de ' . $ejercicio ]
		]
	);

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T23:59:59' ],
			[ 'balance', $fecha ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '113' ],
			[ 'n_dos', '02' ],
			[ 'haber', $ISR_retenido ],
			[ 'folio_id', $folio_diario ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'Provisión del ISR causado del mes de ' . mes( $periodo ) . ' de ' . $ejercicio ]
		]
	);
?>
