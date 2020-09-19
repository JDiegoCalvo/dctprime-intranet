<?php
	include '../../connection.php';

	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	$dia       = $_POST['dia'];

	$fecha = $ejercicio . '-' . $periodo . '-' . $dia;

	$folio_diario = random_int( 11111111, 99999999 );

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T12:00:00' ],
			[ 'balance', $fecha ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '703' ],
			[ 'n_dos', '21' ],
			[ 'debe', $_POST['importe'] ],
			[ 'folio_id', $folio_diario ],
			[ 'libro', 'diario' ],
			[ 'descripcion', $_POST['descripcion'] ]
		]
	);

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T12:00:00' ],
			[ 'balance', $fecha ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', explode( ',', $_POST['forma_de_pago'] )[0] ],
			[ 'n_dos', explode( ',', $_POST['forma_de_pago'] )[1] ],
			[ 'n_tres', explode( ',', $_POST['forma_de_pago'] )[2] ],
			[ 'haber', $_POST['importe'] ],
			[ 'folio_id', $folio_diario ],
			[ 'libro', 'diario' ],
			[ 'descripcion', $_POST['descripcion'] ]
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
			[ 'fecha', $fecha . 'T12:00:00' ],
			[ 'operacion', 'Otros' ],
			[ 'clasificacion', 'Otros' ],
			[ 'importe', $_POST['importe'] ],
			[ 'folio_id', $folio_diario ],
			[ 'efecto', 'Deducciones' ],
			[ 'aplicacion', 'No deducible' ]
		]
	);

	// IVA
	crear(
		$mysql,
		'IVA',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'ejercicio', $_POST['ejercicio'] ],
			[ 'periodo', $_POST['periodo'] ],
			[ 'fecha', $fecha . 'T12:00:00' ],
			[ 'clase', 'Otros' ],
			[ 'tercero', 'Otros' ],
			[ 'operacion', 'Otros' ],
			[ 'exento', $_POST['importe'] ],
			[ 'factor', 1 ],
			[ 'folio_id', $folio_diario ],
			[ 'efecto', 'Deducciones' ],
			[ 'aplicacion', 'No deducible' ]
		]
	);
?>
