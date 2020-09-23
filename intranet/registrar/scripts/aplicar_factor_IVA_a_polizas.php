<?php
	include '../../../con_intranet.php';

	include 'funciones.php';

	$cliente   = $_POST['cliente'];
	$ejercicio = $_POST['ejercicio'];
	$periodo   = $_POST['periodo'];

	// APLICAR CUANDO SOLO HAYAN INGRESOS EXENTOS COMO ARTEMIO
	$factor = floatval( $_POST['factor'] );

	// PRIMERO ELIMINAMOS EL IVA
	$query = "SELECT 
		ID,
		debe,
		haber

	FROM diario

	WHERE 

	cliente   = '$cliente'   AND 
	ejercicio = '$ejercicio' AND 
	periodo   = '$periodo'   AND 

	( n_uno = '119' AND n_dos = '01' ) OR

	( n_uno = '118' AND n_dos = '01' )";

	$result = $mysql->query( $query );

	while ( $row = $result->fetch_assoc() ) 
	{
		if ( $row['debe'] > 0 AND $row['haber'] == 0 )
		{
			$IVA_acreditable = $row['debe'] * $factor;

			if ( $row['debe'] == $IVA_acreditable )
			{
				eliminar( $mysql, $row['ID'] );
			}else
			{
				actualizar_IVA( $row['ID'], $IVA_acreditable, 'debe' );
			}
		}else if ( $row['debe'] == 0 AND $row['haber'] > 0 )
		{
			$IVA_acreditable = $row['haber'] * $factor;

			if ( $row['haber'] == $IVA_acreditable )
			{
				eliminar( $mysql, $row['ID'] );
			}else
			{
				actualizar_IVA( $row['ID'], $IVA_acreditable, 'haber' );
			}
		}
	}

	// AHORA IGUALAMOS SUMAS Y SALDOS
	$folio_id = '';

	if ( $factor == 1 )
	{
		$query = "SELECT
			debe,
			haber,
			folio_id

		FROM diario

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo'   AND 
		libro     = 'diario'

		ORDER BY haber DESC";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( $folio_id !== $row['folio_id'] )
			{
				$debe = $row['haber'];

				$folio_id = $row['folio_id'];

				$mysql->query( "UPDATE diario SET debe = '$debe' WHERE folio_id = '$folio_id' AND debe != '0.00'" );
			}
		}
	}else
	{
		$query = "SELECT
			debe,
			haber,
			folio_id

		FROM diario

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo'   AND 
		libro     = 'diario'

		ORDER BY haber DESC";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( $folio_id !== $row['folio_id'] )
			{
				$haber = 0;

				$IVA_acreditable = 0;

				$folio_id = $row['folio_id'];

				$consulta = "SELECT
					ID,
					n_uno,
					n_dos,
					debe,
					haber,
					folio_id

				FROM diario

				WHERE 

				cliente   = '$cliente'   AND 
				ejercicio = '$ejercicio' AND 
				periodo   = '$periodo'   AND 
				folio_id  = '$folio_id'  AND
				libro     = 'diario'

				ORDER BY haber DESC";

				$resultado = $mysql->query( $consulta );

				while ( $col = $resultado->fetch_assoc() ) 
				{
					if ( 
						 ( $col['n_uno'] !== '119' AND $col['n_dos'] !== '01' ) OR 
						 ( $col['n_uno'] !== '118' AND $col['n_dos'] !== '01' ) 
					)
					{
						$haber += floatval( $col['haber'] );

						$ID = $col['ID'];
					}else
					{
						$IVA_acreditable += floatval( $col['debe'] );
					}
				}

				$debe = $haber - $IVA_acreditable;

				$mysql->query( "UPDATE diario 

					SET 

					debe = '$debe' 

					WHERE 

					ID = '$ID'"
				);
			}
		}
	}
?>
