<?php
	include '../../connection.php';

	date_default_timezone_set( 'America/Mexico_City' );

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	$fecha_de_emision = date( 'Y-m-d H:i:s' );
	$fecha_limite     = date( 'Y-m-d H:i:s', strtotime( $fecha_de_emision . '+ 15 dias' ) );

	$dia = substr( $fecha_de_emision, 8, 2 );
	$mes = substr( $fecha_de_emision, 5, 2 );
	$año = substr( $fecha_de_emision, 2, 2 );

	$meses = array(
		'00', 'Ene', 'Feb',
		'Mar', 'Abr', 'May',
		'Jun', 'Jul', 'Ago',
		'Sep', 'Oct', 'Nov', 'Dic'
	);

	$i   = intval( $mes );
	$mes = $meses[$i]; 

	$query = "SELECT
		ID,
		RFC,
		denominacion,
		nombre,
		primer_apellido,
		segundo_apellido,
		telefono,
		tratamiento

	FROM clientes 

	WHERE 

	RFC = '$cliente'

	ORDER BY RFC ASC LIMIT 1";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		$ID             = $row['ID'];
		$FTPR           = $row['RFC'];
		$denomination   = $row['denominacion'];
		$name           = $row['nombre'];
		$lastName       = $row['primer_apellido'];
		$secondLastName = $row['segundo_apellido'];
		$telefono       = $row['telefono'];
		$tratamiento    = $row['tratamiento'];
	}

	if ( !empty( $denomination ) )
	{
		$clientName = $denomination;
	}else
	{
		$clientName = $name . ' ' . $lastName . ' ' . $secondLastName;
	}

	$query = "SELECT 
		folio 

	FROM ventas 

	ORDER BY folio DESC LIMIT 1";

	$result = $mysql->query( $query );

	if ( $result->num_rows > 0 )
	{
		while ( $row = $result->fetch_row() ) 
		{
			$folio = $row[0];
		}

		$folio += 1;
	}else
	{
		$folio = 1;
	}

	$HTML = '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta content="width=device-width,initial-scale=1"name="viewport"><meta content="noindex,nofollow"name="robots"><link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"rel="stylesheet"crossorigin="anonymous"integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk"><link href="styles.css"rel="stylesheet"><title>DCT Prime | Tu e-Recibo Folio: '.$folio.'</title></head><body><div class="pt-5"><div class="d-block mx-auto paper"><div class="p-5rem"><p><span class="mb-5 h1">Tu e-Recibo</span>  <span class="h1 font-weight-lighter">DCT Prime®</span></p><p class="h4 mb-3">'.$clientName.'</p><p class="mb-0">Fecha: <span class="font-weight-bold">'.$dia.' '.$mes.' '.$año.'</span></p><p class="mb-5">Recibo No: <span class="font-weight-bold">'.$folio.'</span></p><table class="table"><tbody><tr><td class="bn table-td-head">DESCRIPCIÓN</td><td class="bn table-td-head">IMPORTE</td></tr>'; $total = 0; $query = "SELECT descripcion, cantidad, precio FROM recibo WHERE cliente = '$cliente' AND ejercicio = '$ejercicio' AND periodo = '$periodo' AND estado = '0'"; $result = $mysql->query( $query ); while ( $row = $result->fetch_assoc() ) { $importe = ( $row['cantidad'] * $row['precio'] ) * 1.16; $total += $importe; $HTML .= '<tr><td class="font-weight-bold bn">'.$row['descripcion'].'</td><td class="font-weight-bold bn c-orange">$ '.number_format( $importe, 2 ).'</td></tr>'; } $HTML .= '</tbody></table></div><div class="paper-bottom"><div class="row"><div class="col-4"><p class="mb-0 fs-06rem">CUENTA DE BANCO</p></div><div class="col-4"><p class="mb-0 fs-06rem">PAGAR ANTES DE</p></div><div class="col-4"><p class="mb-0 fs-06rem">TOTAL A PAGAR</p></div></div><hr class="mt-1"><div class="row"><div class="col-4"><p class="mb-0 fs-075rem">Banco: <span class="font-weight-bold">Inbursa</span></p><p class="mb-0 fs-075rem">Cuenta: <span class="font-weight-bold">50041186137</span></p></div><div class="col-4"><p class="font-weight-bold fs-15rem mb-0">17 '.$mes.' '.$año.'</p></div><div class="col-4"><p class="font-weight-bold c-orange fs-15rem mb-0">$ '.number_format( $total, 2 ).'</p></div></div><hr class="mb-5"><div class="row"><div class="col-6"><p class="font-weight-bold text-left text-secondary">¡Muchas Gracias!</p></div><div class="col-6"><p class="fs-075rem text-right text-secondary">dctprime.com</p></div></div></div></div></div></body></html>';

	$hash = password_hash( $folio, PASSWORD_BCRYPT );
	$hash = str_replace( '#', '', $hash );
	$hash = str_replace( '%', '', $hash );
	$hash = str_replace( '&', '', $hash );
	$hash = str_replace( '*', '', $hash );
	$hash = str_replace( '{', '', $hash );
	$hash = str_replace( '}', '', $hash );
	$hash = str_replace( "'\'", '', $hash );
	$hash = str_replace( '/', '', $hash );
	$hash = str_replace( ':', '', $hash );
	$hash = str_replace( '<', '', $hash );
	$hash = str_replace( '>', '', $hash );
	$hash = str_replace( '?', '', $hash );
	$hash = str_replace( '+', '', $hash );
	$hash = str_replace( '.', '', $hash );

	$hash = substr( $hash, 7, 4 );

	file_put_contents( '../../l/' . $hash . '.html', $HTML );

	$query = "UPDATE 
		recibo 

	SET 

	estado = '1' 

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND 
	periodo   = '$periodo'";

	$mysql->query( $query );

	$query = "INSERT INTO ventas ( 
		fecha, 
		folio, 
		cliente, 
		importe, 
		hash 
	) VALUES ( 
		'$fecha_de_emision', 
		'$folio', 
		'$ID', 
		'$total', 
		'$hash' 
	)";

	$mysql->query( $query );
?>
