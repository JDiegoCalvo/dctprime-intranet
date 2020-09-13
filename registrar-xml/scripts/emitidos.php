<?php
	include '../../connection.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];
	$tipo      = $_POST['tipo'];

	$data = new stdClass;
	$data->PUE = array();
	$data->PPD = array();
	$data->pago = array();
	$data->nota = array();

	$query = "SELECT 
		folio_fiscal, 
		RFC, 
		nombre, 
		total 

	FROM CFDIs_emitidos 

	WHERE

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo' 

	ORDER BY fecha_emision ASC";

	$result = $mysql->query( $query );


	while ( $row = $result->fetch_row() ) 
	{
		libxml_use_internal_errors( true );

		$xml = new \SimpleXMLElement( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' . $row[0] . '.xml', null, true );
		$ns = $xml->getNamespaces( true );
		$xml->registerXPathNamespace( 'c', $ns['cfdi'] );
		$xml->registerXPathNamespace( 't', $ns['tfd'] );

		foreach ( $xml->xpath( '//cfdi:Comprobante' ) as $cfdiComprobante )
		{
			if ( $cfdiComprobante['Version'] == '3.3' )
			{
				$version = '3.3';

				include 'ordenar_emitidos_v_3_3.php';
			}else
			{
				$version = '3.2';

				include 'ordenar_emitidos_v_3_2.php';
			}
		}

		$query = "SELECT 
			ID 

		FROM CFDI_relacionados 

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo'   AND 
		UUID      = '$row[0]'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			$register = '<i class="fas fa-check-circle text-success"></i>';
		}else
		{
			$register = '';
		}

		if ( $TipoDeComprobante == 'I' AND $MetodoPago == 'PUE' )
		{
			array_push( $data->PUE, array(
				'folioFiscal' => $row[0],
				'nombre'  => $row[2],
				'RFC'     => $row[1],
				'general' => $general,
				'cero'    => $cero,
				'exento'  => $exento,
				'IVA'     => $IVA,
				'IEPS'    => $IEPS,
				'ISRretenido' => $ISRretenido,
				'IVAretenido' => $IVAretenido,
				'importe' => $row[3],
				'uso'     => $uso,
				'formaDePago' => $FormaPago,
				'fecha' => $fecha,
				'CP'    => $CP,
				'serie' => $Serie,
				'folio' => $Folio,
				'register' => $register,
				'metodo_de_pago' => 'PUE'
			));
		}else if ( $TipoDeComprobante == 'I' AND $MetodoPago == 'PPD' )
		{
			array_push( $data->PPD, array(
				'folioFiscal' => $row[0],
				'nombre'  => $row[2],
				'RFC'     => $row[1],
				'general' => $general,
				'cero'    => $cero,
				'exento'  => $exento,
				'IVA'     => $IVA,
				'IEPS'    => $IEPS,
				'ISRretenido' => $ISRretenido,
				'IVAretenido' => $IVAretenido,
				'importe' => $row[3],
				'uso'     => $uso,
				'formaDePago' => $FormaPago,
				'fecha'    => $fecha,
				'CP'       => $CP,
				'serie'    => $Serie,
				'folio'    => $Folio,
				'register' => $register,
				'metodo_de_pago' => 'PPD'
			));
		}else if ( $TipoDeComprobante == 'P' )
		{
			$general = 0;
			$cero    = 0;
			$exento  = 0;
			$IVA     = 0;
			$IEPS    = 0;
			$monto   = 0;

			$xml->registerXPathNamespace( 'p', $ns['pago10'] );

			foreach ( $xml->xpath( '//p:Pago' ) as $pago10Pago )
			{
				$FormaPago = $pago10Pago['FormaDePagoP'];
			}

			foreach ( $xml->xpath( '//p:DoctoRelacionado' ) as $pago10DoctoRelacionado)
			{
				$monto += floatval( $pago10DoctoRelacionado['ImpPagado'] );
				$referencia = $pago10DoctoRelacionado['IdDocumento'];
			}
			
			libxml_use_internal_errors( true );

			$xml = new \SimpleXMLElement( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '-referencias/' . strtoupper( $referencia ) . '.xml', null, true );
			$ns = $xml->getNamespaces( true );
			$xml->registerXPathNamespace( 'c', $ns['cfdi'] );
			$xml->registerXPathNamespace( 't', $ns['tfd'] );

			foreach ( $xml->xpath( '//cfdi:Traslado' ) as $cfdiTraslado )
			{
				// Base
				if ( $cfdiTraslado['Impuesto'] == '002' && $cfdiTraslado['TasaOCuota'] == '0.160000' && floatval( $cfdiTraslado['Base'] ) > 0 )
				{
					$general += floatval( $cfdiTraslado['Base'] );
				}else if ( $cfdiTraslado['Impuesto'] == '002' && $cfdiTraslado['TasaOCuota'] == '0.000000' && floatval( $cfdiTraslado['Base'] ) > 0 )
				{
					$cero += floatval( $cfdiTraslado['Base'] );
				}else if ( $cfdiTraslado['Impuesto'] == '002' && $cfdiTraslado['TipoFactor'] == 'Exento' && floatval( $cfdiTraslado['Base'] ) > 0 )
				{
					$exento += floatval( $cfdiTraslado['Base'] );
				}

				// IVA
				if ( $cfdiTraslado['Impuesto'] == '002' && $cfdiTraslado['TasaOCuota'] == '0.160000' )
				{
					if ( $cfdiTraslado['Importe'] )
					{
						$IVA = floatval( $cfdiTraslado['Importe'] );
					}
					
				}else if ( $cfdiTraslado['Impuesto'] == '003' )
				{
					if ( $cfdiTraslado['Importe'] )
					{
						$IEPS = floatval( $cfdiTraslado['Importe'] );
					}
				}
			}

			if ( $general == 0 AND $cero == 0 AND $exento == 0 AND $IVA == 0 AND $IEPS == 0 )
			{
				$factor = 1;
			}else
			{
				$factor = floatval( $monto ) / ( floatval( $general ) + floatval( $cero ) + floatval( $exento ) + floatval( $IVA ) + floatval( $IEPS ) );
			}

			$general = $factor * $general;
			$cero    = $factor * $cero;
			$exento  = $factor * $exento;
			$IVA     = $factor * $IVA;
			$IEPS    = $factor * $IEPS;

			array_push( $data->pago, array(
				'folioFiscal' => $row[0],
				'nombre'  => $row[2],
				'RFC'     => $row[1],
				'general' => $general,
				'cero'   => $cero,
				'exento' => $exento,
				'IVA'    => $IVA,
				'IEPS'   => $IEPS,
				'ISRretenido' => 0,
				'IVAretenido' => 0,
				'importe'     => $monto,
				'FormaPago' => $FormaPago,
				'fecha'     => $fecha,
				'CP'        => $CP,
				'serie'      => $Serie,
				'folio'      => $Folio,
				'referencia' => $referencia,
				'register'   => $register,
				'metodo_de_pago' => 'Pago'
			));	
		}else if ( $TipoDeComprobante == 'E' )
		{
			array_push( $data->nota, array(
				'folioFiscal' => $row[0],
				'nombre'  => $row[2],
				'RFC'     => $row[1],
				'general' => $general,
				'cero'   => $cero,
				'exento' => $exento,
				'IVA'    => $IVA,
				'IEPS'   => $IEPS,
				'ISRretenido' => 0,
				'IVAretenido' => 0,
				'importe'   => $row[3],
				'uso'       => $uso,
				'FormaPago' => $FormaPago,
				'fecha' => $fecha,
				'CP'    => $CP,
				'serie' => $Serie,
				'folio' => $Folio,
				'register' => $register,
				'metodo_de_pago' => 'Nota de crédito'
			));
		}
	}

	$data->size = count( $data->PUE ) + count( $data->PPD ) + count( $data->pago ) + count( $data->nota );

	echo json_encode( $data );
?>
