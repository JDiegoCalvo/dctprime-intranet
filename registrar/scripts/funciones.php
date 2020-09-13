<?php
	function crear ( $mysql, $table, $data )
	{
		$l = count( $data );

		$query = "
			INSERT INTO $table (";

				for ( $i = 0; $i < $l; $i++ )
				{
					if ( $i == 0 )
					{
						$query .= $data[$i][0];
					}else
					{
						$query .= "," . $data[$i][0];
					}
				}

			$query .= ") VALUES (";

				for ( $i = 0; $i < $l; $i++ )
				{
					if ( $i == 0 )
					{
						$query .= "'" . $data[$i][1] . "'";
					}else
					{
						$query .= "," . "'" . $data[$i][1] . "'";
					}
				}

			$query .= ")
		";

		$mysql->query( $query );
	}

	function ISR_retenido()
	{
		global $mysql, $cliente, $ejercicio, $periodo;

		$ISR_retenido_acreditable = 0;

		$query = "SELECT 
			ISR_retenido,
			aplicacion 

		FROM ISR 

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND
		periodo   = '$periodo'   AND 
		efecto    = 'Ingresos'   AND

		( aplicacion = 'Ingreso acumulable' OR aplicacion = 'Nota de crédito' )";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			if ( $row['aplicacion'] == 'Ingreso acumulable' )
			{
				$ISR_retenido_acreditable += $row['ISR_retenido'];

			}else if ( $row['aplicacion'] == 'Nota de crédito' )
			{
				$ISR_retenido_acreditable -= $row['ISR_retenido'];

			}
		}

		return $ISR_retenido_acreditable;
	}

	function fecha( $ejercicio, $periodo )
	{
		if ( $periodo == '01' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '02' )
		{
			return $ejercicio . '-' . $periodo . '-28';
		}else if ( $periodo == '03' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '04' )
		{
			return $ejercicio . '-' . $periodo . '-30';
		}else if ( $periodo == '05' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '06' )
		{
			return $ejercicio . '-' . $periodo . '-30';
		}else if ( $periodo == '07' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '08' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '09' )
		{
			return $ejercicio . '-' . $periodo . '-30';
		}else if ( $periodo == '10' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}else if ( $periodo == '11' )
		{
			return $ejercicio . '-' . $periodo . '-30';
		}else if ( $periodo == '12' )
		{
			return $ejercicio . '-' . $periodo . '-31';
		}
	}

	function mes( $periodo )
	{
		if ( $periodo == '01' )
		{
			return 'Enero';
		}else if ( $periodo == '02' )
		{
			return 'Febrero';
		}else if ( $periodo == '03' )
		{
			return 'Marzo';
		}else if ( $periodo == '04' )
		{
			return 'Abril';
		}else if ( $periodo == '05' )
		{
			return 'Mayo';
		}else if ( $periodo == '06' )
		{
			return 'Junio';
		}else if ( $periodo == '07' )
		{
			return 'Julio';
		}else if ( $periodo == '08' )
		{
			return 'Agosto';
		}else if ( $periodo == '09' )
		{
			return 'Septiembre';
		}else if ( $periodo == '10' )
		{
			return 'Octubre';
		}else if ( $periodo == '11' )
		{
			return 'Noviembre';
		}else if ( $periodo == '12' )
		{
			return 'Diciembre';
		}
	}

	function eliminar ( $mysql, $ID )
	{
		$query = "DELETE FROM
		
		diario

		WHERE ID = '$ID'";

		$mysql->query( $query );
	}

	function actualizar_IVA ( $ID, $IVA_acreditable, $nat )
	{
		global $mysql;

		$query = "UPDATE diario

		SET 

		$nat = '$IVA_acreditable'

		WHERE ID = '$ID'";

		$mysql->query( $query );
	}

	function debe_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		$query = "SELECT 

		SUM( debe ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo'   AND 
		n_uno     = '$n_uno'     AND 
		n_dos     = '$n_dos'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function haber_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );

		$query = "SELECT 

		SUM( haber ) AS total

		FROM diario 

		WHERE 

		cliente   = '$cliente'   AND 
		ejercicio = '$ejercicio' AND 
		periodo   = '$periodo'   AND 
		n_uno     = '$n_uno'     AND 
		n_dos     = '$n_dos'";

		return floatval( $mysql->query( $query )->fetch_object()->total );
	}

	function saldo_inicial_n_dos( $mysql, $cliente, $ejercicio, $periodo, $codigo )
	{
		$n_uno = substr( $codigo, 0, 3 );
		$n_dos = substr( $codigo, 4, 2 );


			if ( intval( $periodo ) < 11 )
			{
				$periodo = '0' . ( intval( $periodo ) - 1 );
			}else
			{
				$periodo = intval( $periodo ) - 1;
			}

			$fin = $ejercicio . '-' . $periodo . '-31';

			$query = "SELECT 

			SUM( debe ) AS total

			FROM diario 

			WHERE 

			cliente   = '$cliente'   AND 
			n_uno     = '$n_uno'     AND 
			n_dos     = '$n_dos'     AND 

			balance BETWEEN '2015-01-01' AND '$fin'";

			$debe = floatval( $mysql->query( $query )->fetch_object()->total );

			$query = "SELECT 

			SUM( haber ) AS total

			FROM diario 

			WHERE 

			cliente   = '$cliente'   AND 
			n_uno     = '$n_uno'     AND 
			n_dos     = '$n_dos'     AND 

			balance BETWEEN '2015-01-01' AND '$fin'";

			$haber = floatval( $mysql->query( $query )->fetch_object()->total );

			$data = $debe - $haber;

			return floatval( $data );

	}

	function mes_2( $mes )
	{
		if ( $mes == '01' )
		{
			return 'ENERO';
		}else if ( $mes == '02' )
		{
			return 'FEBRERO';
		}else if ( $mes == '03' )
		{
			return 'MARZO';
		}else if ( $mes == '04' )
		{
			return 'ABRIL';
		}else if ( $mes == '05' )
		{
			return 'MAYO';
		}else if ( $mes == '06' )
		{
			return 'JUNIO';
		}else if ( $mes == '07' )
		{
			return 'JULIO';
		}else if ( $mes == '08' )
		{
			return 'AGOSTO';
		}else if ( $mes == '09' )
		{
			return 'SEPTIEMBRE';
		}else if ( $mes == '10' )
		{
			return 'OCTUBRE';
		}else if ( $mes == '11' )
		{
			return 'NOVIEMBRE';
		}else if ( $mes == '12' )
		{
			return 'DICIEMBRE';
		}
	}
?>
