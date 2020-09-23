<?php 
	include '../../../con_intranet.php';

	include 'funciones.php';

	date_default_timezone_set( 'America/Mexico_City' );

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$folio_id = random_int( 11111111, 99999999 );

	$n_dias = cal_days_in_month( CAL_GREGORIAN, $periodo, $ejercicio );

	$IVA_trasladado = haber_n_dos( $mysql, $cliente, $ejercicio, $periodo, '209.01' );

	$IVA_acreditable = debe_n_dos( $mysql, $cliente, $ejercicio, $periodo, '118.01' );

	// IVA a favor
	$saldo_inicial = saldo_inicial_n_dos( $mysql, $cliente, $ejercicio, $periodo, '113.01' );
	$debe  = debe_n_dos( $mysql, $cliente, $ejercicio, $periodo, '113.01' );
	$haber = haber_n_dos( $mysql, $cliente, $ejercicio, $periodo, '113.01' );
	$IVA_a_favor = $saldo_inicial + $debe - $haber;

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $cliente ],
			[ 'ejercicio', $ejercicio ],
			[ 'periodo', $periodo ],
			[ 'balance', $ejercicio . '-' . $periodo . '-01' ],
			[ 'fecha', $ejercicio . '-' . $periodo . '-' . $n_dias . 'T12:59:59' ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '208' ],
			[ 'n_dos', '01' ],
			[ 'debe', $IVA_trasladado ],
			[ 'folio_id', $folio_id ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'DETERMINACIÓN DEL SALDO A FAVOR DE IVA DEL MES DE ' . mes_2( $periodo ) . ' DEL ' . $ejercicio ]
		]
	);

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $cliente ],
			[ 'ejercicio', $ejercicio ],
			[ 'periodo', $periodo ],
			[ 'balance', $ejercicio . '-' . $periodo . '-01' ],
			[ 'fecha', $ejercicio . '-' . $periodo . '-' . $n_dias . 'T12:59:59' ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '118' ],
			[ 'n_dos', '01' ],
			[ 'haber', $IVA_acreditable ],
			[ 'folio_id', $folio_id ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'DETERMINACIÓN DEL SALDO A FAVOR DE IVA DEL MES DE ' . mes_2( $periodo ) . ' DEL ' . $ejercicio ]
		]
	);

	crear(
		$mysql,
		'diario',
		[
			[ 'cliente', $cliente ],
			[ 'ejercicio', $ejercicio ],
			[ 'periodo', $periodo ],
			[ 'balance', $ejercicio . '-' . $periodo . '-01' ],
			[ 'fecha', $ejercicio . '-' . $periodo . '-' . $n_dias . 'T12:59:59' ],
			[ 'creacion', date( 'Y-m-d H:i:s' ) ],
			[ 'n_uno', '113' ],
			[ 'n_dos', '01' ],
			[ 'haber', $IVA_a_favor ],
			[ 'folio_id', $folio_id ],
			[ 'libro', 'diario' ],
			[ 'descripcion', 'DETERMINACIÓN DEL SALDO A FAVOR DE IVA DEL MES DE ' . mes_2( $periodo ) . ' DEL ' . $ejercicio ]
		]
	);
?>