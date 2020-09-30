<?php
	function crear_directorio( $cliente, $ejercicio, $periodo )
	{
		if ( file_exists( '../../archivos-xml/' . $cliente ) ) 
		{
			if ( file_exists( '../../archivos-xml/' . $cliente . '/' . $ejercicio ) ) 
			{
				if ( file_exists( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo ) ) 
				{
					if ( !file_exists( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos' ) )
					{
						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos', 0777 );
						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos/xml' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos/xml', 0777 );
						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos/xml-referencias' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-emitidos/xml-referencias', 0777 );

						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos', 0777 );
						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos/xml' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos/xml', 0777 );
						mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos/xml-referencias' );
						chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/xml-recibidos/xml-referencias', 0777 );
					}
				}else
				{
					mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo );
					chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo, 0777 );
					crear_directorio( $cliente, $ejercicio, $periodo );
				}
			}else
			{
				mkdir( '../../archivos-xml/' . $cliente . '/' . $ejercicio );
				chmod( '../../archivos-xml/' . $cliente . '/' . $ejercicio, 0777 );
				crear_directorio( $cliente, $ejercicio, $periodo );
			}
		}else
		{
			mkdir( '../../archivos-xml/' . $cliente );
			chmod( '../../archivos-xml/' . $cliente, 0777 );
			crear_directorio( $cliente, $ejercicio, $periodo );
		}
	}

	function cargar_XML()
	{
		$directorio_objetivo = '../../zona-de-prep-xml/';

		$tipos_permitidos = array( 'zip', 'xml' );

		foreach ( $_FILES['xml']['name'] as $key => $val )
		{
			$nombre_del_archivo = basename( $_FILES['xml']['name'][$key] );

			$ruta_objetivo = $directorio_objetivo . $nombre_del_archivo;

			$tipo_del_arhivo = pathinfo( $ruta_objetivo, PATHINFO_EXTENSION );

			if ( $tipo_del_arhivo == 'zip' )
			{
				$zip = new ZipArchive;

				if ( $zip->open( $_FILES['xml']['tmp_name'][$key] ) === TRUE )
				{
					$zip->extractTo( $directorio_objetivo );
					$zip->close();
				}
			}else if ( $tipo_del_arhivo == 'xml' )
			{
				if ( in_array( $tipo_del_arhivo, $tipos_permitidos ) )
				{
					move_uploaded_file( $_FILES['xml']['tmp_name'][$key], $ruta_objetivo );
				}
			}
		}

		acomodar_XML();
	}

	function acomodar_XML()
	{
		global $cliente, $ejercicio, $periodo, $tipo;

		$directorio_objetivo = '../../zona-de-prep-xml/';

		$directorio = opendir( '../../zona-de-prep-xml/' );

		while ( $nombre_del_archivo = readdir( $directorio ) ) 
		{
			if ( !is_dir( $nombre_del_archivo ) AND substr( $nombre_del_archivo, 0, 2 ) != '._' )
			{
				libxml_use_internal_errors( true );

				$xml = new \SimpleXMLElement( '../../zona-de-prep-xml/' . $nombre_del_archivo, null, true );
				$ns = $xml->getNamespaces( true );
				$xml->registerXPathNamespace( 't', $ns['tfd'] );

				foreach ( $xml->xpath( '//t:TimbreFiscalDigital' ) as $tfdTimbreFiscalDigital )
				{
					$folio_fiscal = $tfdTimbreFiscalDigital['UUID'];
				}

				$nuevo_directorio = '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/';

				copy( 
					$directorio_objetivo . $nombre_del_archivo, 
					$nuevo_directorio . strtoupper( $folio_fiscal ) . '.xml'
				);
				
				unlink( '../../zona-de-prep-xml/' . $nombre_del_archivo );
			}
		}
	}

	function insertar_metadata_en_tabla()
	{
		global $mysql, $cliente, $ejercicio, $periodo, $tipo;

		$directorio = opendir( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' );

		while ( $nombre_del_archivo = readdir( $directorio ) ) 
		{
			if ( !is_dir( $nombre_del_archivo ) AND substr( $nombre_del_archivo, 0, 2 ) != '._' )
			{
				libxml_use_internal_errors( true );

				$xml = new \SimpleXMLElement( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' . $nombre_del_archivo, null, true );
				$ns = $xml->getNamespaces( true );
				$xml->registerXPathNamespace( 't', $ns['tfd'] );

				foreach ( $xml->xpath( '//cfdi:Comprobante' ) as $cfdiComprobante )
				{
					if ( $cfdiComprobante['TipoDeComprobante'] == 'I' )
					{
						$efecto = 'Ingreso';
					}else if ( $cfdiComprobante['TipoDeComprobante'] == 'E' )
					{
						$efecto = 'Egreso';
					}else if ( $cfdiComprobante['TipoDeComprobante'] == 'P' )
					{
						$efecto = 'Pago';
					}else if ( $cfdiComprobante['TipoDeComprobante'] == 'N' )
					{
						$efecto = 'Nómina';
					}else
					{
						$efecto = 'Otro';
					}

					$folio_fiscal = strtoupper( substr( $nombre_del_archivo, 0, -4 ) );
					$fecha_emision = $cfdiComprobante['Fecha'];
					$total = $cfdiComprobante['Total']; 
				}

				foreach ( $xml->xpath( '//t:TimbreFiscalDigital' ) as $tfdTimbreFiscalDigital )
				{
					$fecha_certificacion = $tfdTimbreFiscalDigital['FechaTimbrado'];
					$PAC = $tfdTimbreFiscalDigital['RfcProvCertif'];
				}

				if ( $tipo == 'xml-emitidos/xml' )
				{
					foreach ( $xml->xpath( '//cfdi:Receptor' ) as $cfdiReceptor )
					{
						$RFC    = $cfdiReceptor['Rfc'];
						$nombre = $cfdiReceptor['Nombre']; 
					}

					$tabla = 'CFDIs_emitidos';

					$tabla_referencias = 'CFDIs_emitidos_referencias';
				}else if ( $tipo == 'xml-recibidos/xml' )
				{
					foreach ( $xml->xpath( '//cfdi:Emisor' ) as $cfdiEmisor )
					{
						$RFC    = $cfdiEmisor['Rfc'];
						$nombre = $cfdiEmisor['Nombre']; 
					}

					$tabla = 'CFDIs_recibidos';

					$tabla_referencias = 'CFDIs_recibidos_referencias';
				}

				if ( $tipo == 'xml-emitidos/xml' OR $tipo == 'xml-recibidos/xml' )
				{
					$query = "SELECT 
						ID 

					FROM $tabla 

					WHERE 

					cliente      = '$cliente'    AND 
					ejercicio    = '$ejercicio'  AND 
					periodo      = '$periodo'    AND 
					folio_fiscal = '$folio_fiscal'";

					if ( $mysql->query( $query )->num_rows == 0 )
					{
						$query = "INSERT INTO $tabla (
							cliente,
							ejercicio,
							periodo,
							folio_fiscal,
							RFC,
							nombre,
							fecha_emision,
							fecha_certificacion,
							PAC,
							total,
							efecto
						) VALUES(
							'$cliente',
							'$ejercicio',
							'$periodo',
							'$folio_fiscal',
							'$RFC',
							'$nombre',
							'$fecha_emision',
							'$fecha_certificacion',
							'$PAC',
							'$total',
							'$efecto'
						)";

						$mysql->query( $query );
					}
				}
			}
		}

		if ( $tipo == 'xml-emitidos/xml' )
		{

			$tabla = 'CFDIs_emitidos';

			$tabla_referencias = 'CFDIs_emitidos_referencias';
		}else if ( $tipo == 'xml-recibidos/xml' )
		{

			$tabla = 'CFDIs_recibidos';

			$tabla_referencias = 'CFDIs_recibidos_referencias';
		}		

		if ( $tipo == 'xml-emitidos/xml' OR $tipo == 'xml-recibidos/xml' )
		{
			$data = array();

			$query = "SELECT 
				folio_fiscal 

			FROM $tabla 

			WHERE 

			cliente      = '$cliente'    AND 
			ejercicio    = '$ejercicio'  AND 
			periodo      = '$periodo'    AND
			efecto       = 'Pago'";

			$result = $mysql->query( $query );

			while ( $row = $result->fetch_assoc() ) 
			{
				libxml_use_internal_errors( true );

				$xml = new \SimpleXMLElement( '../../archivos-xml/' . $cliente . '/' . $ejercicio . '/' . $periodo . '/' . $tipo . '/' . $row['folio_fiscal'] . '.xml', null, true );
				$ns = $xml->getNamespaces( true );

				$xml->registerXPathNamespace( 'p', $ns['pago10'] );

				foreach ( $xml->xpath( '//p:DoctoRelacionado' ) as $pago10DoctoRelacionado )
				{
					$folio_fiscal = strtoupper( $pago10DoctoRelacionado['IdDocumento'] );

					if ( !in_array( $folio_fiscal, $data ) ) 
					{
						array_push( $data, $folio_fiscal );
					}
				}
			}

			$l = count( $data );

			for ( $i = 0; $i < $l; $i++ )
			{
				$folio_fiscal = $data[$i];

				$query = "SELECT 
					ID 

				FROM $tabla_referencias 

				WHERE 

				cliente      = '$cliente'   AND 
				ejercicio    = '$ejercicio' AND 
				periodo      = '$periodo'   AND 
				folio_fiscal = '$folio_fiscal'";

				if ( $mysql->query( $query )->num_rows == 0 )
				{			
					$query = "INSERT INTO $tabla_referencias (
						cliente,
						ejercicio,
						periodo,
						folio_fiscal
					) VALUES(
						'$cliente',
						'$ejercicio',
						'$periodo',
						'$folio_fiscal'
					)";
					
					$mysql->query( $query );
				}
			}
		}
	}
?>
