<?php
	include '../../connection.php';

	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	$data = new stdClass;

	$data->nombre = nombre( $mysql, $cliente );

	$n_dias = cal_days_in_month( CAL_GREGORIAN, $periodo, $ejercicio );
	$data->fecha = 'Del ' . $ejercicio . '-' . $periodo . '-01 al ' . $ejercicio . '-' . $periodo . '-' . $n_dias;

	$data->diario = array();
	$folios_unicos = array();
	$folio_id = '';
	$debe  = 0;
	$haber = 0;

	$query = "SELECT
		fecha,
		n_uno,
		n_dos,
		n_tres,
		debe,
		haber,
		folio_id,
		descripcion

	FROM diario

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND 
	periodo   = '$periodo'   AND
	libro     = 'ingresos'

	ORDER BY fecha ASC, ID ASC";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $folio_id !== $row['folio_id'] )
		{	
			if ( !in_array( $row['folio_id'], $folios_unicos ) )
			{
				array_push( $folios_unicos, $row['folio_id'] );

				$CFDI = CFDI( $mysql, $row['folio_id'] );
				$forma_de_pago = forma_de_pago( $mysql, $row['folio_id'] );

				$año = substr( $row['fecha'], 0, 4 );
				$mes = substr( $row['fecha'], 5, 2 );
				$dia = substr( $row['fecha'], 8, 2 );

				$fecha = $dia . ' de ' . mes( $mes ) . ' del ' . $año;

				array_push( $data->diario, array(
					'folio_id'    => $row['folio_id'],
					'asiento'     => asiento( $mysql, $row['folio_id'] ),
					'descripcion' => $row['descripcion'],
					'fecha'       => $fecha,
					'UUID'        => $CFDI['UUID'],
					'serie'       => $CFDI['serie'],
					'folio'       => $CFDI['folio'],
					'importe'     => $CFDI['importe'],
					'RFC'         => $CFDI['RFC'],
					'forma_de_pago' => $forma_de_pago['forma_de_pago'],
					'no_de_cheque'  => $forma_de_pago['no_de_cheque'],
					'cta_origen'    => $forma_de_pago['cta_origen'],
					'banco_origen'  => $forma_de_pago['banco_origen'],
					'cta_destino'   => $forma_de_pago['cta_destino'],
					'banco_destino' => $forma_de_pago['banco_destino']
				));

				$folio_id = $row['folio_id'];
			}
		}
	}

	echo json_encode( $data );
?>
