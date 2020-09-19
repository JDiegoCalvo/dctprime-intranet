<?php
	foreach ( $xml->xpath( '//cfdi:Comprobante' ) as $cfdiComprobante )
	{
		$TipoDeComprobante = $cfdiComprobante['TipoDeComprobante'];
		$MetodoPago = $cfdiComprobante['MetodoPago'];
		$FormaPago = $cfdiComprobante['FormaPago'];
		$fecha = $cfdiComprobante['Fecha'];
		$CP = $cfdiComprobante['LugarExpedicion'];
		$Serie = $cfdiComprobante['Serie'];
		$Folio = $cfdiComprobante['Folio'];
	}

	foreach ( $xml->xpath( '//cfdi:Receptor' ) as $cfdiReceptor )
	{
		$uso = $cfdiReceptor['UsoCFDI'];
	}

	foreach ( $xml->xpath( '//t:TimbreFiscalDigital' ) as $tfdTimbreFiscalDigital )
	{
		$fecha_certificacion = $tfdTimbreFiscalDigital['FechaTimbrado'];
	}

	$general = 0;
	$cero = 0;
	$exento = 0;
	$IVA = 0;
	$IEPS = 0;
	$ISRretenido = 0;
	$IVAretenido = 0;

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
		}

		if ( $cfdiTraslado['Impuesto'] == '003' )
		{
			if ( $cfdiTraslado['Importe'] )
			{
				$IEPS = floatval( $cfdiTraslado['Importe'] );
			}
		}								
	}

	foreach ( $xml->xpath( '//cfdi:Retencion' ) as $cfdiRetencion )
	{
		if ( $cfdiRetencion['Impuesto'] == '001' )
		{
			$ISRretenido = floatval( $cfdiRetencion['Importe'] );
		}else if ( $cfdiRetencion['Impuesto'] == '002' )
		{
			$IVAretenido = floatval( $cfdiRetencion['Importe'] );
		}
	}

	if ( $FormaPago == null )
	{
		$FormaPago = '';
	}

	if ( $Serie == null )
	{
		$Serie = '';
	}

	if ( $Folio == null )
	{
		$Folio = '';
	}
?>
