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

	function cliente( $mysql, $cliente, $nombre, $RFC )
	{
		$query = "SELECT
		ID 

		FROM diario_padron 

		WHERE 
		cliente = '$cliente' AND 
		clase   = 'Cliente'  AND 
		tipo    = 'Nacional' AND
		RFC     = '$RFC'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			return $mysql->query( $query )->fetch_object()->ID;
		}else
		{
			crear(
				$mysql,
				'diario_padron',
				[
					[ 'cliente', $cliente ],
					[ 'n_uno', '105' ],
					[ 'n_dos', '01' ],
					[ 'clase', 'Cliente' ],
					[ 'tipo', 'Nacional' ],
					[ 'nombre', $nombre ],
					[ 'RFC', $RFC ]
				]
			);

			$query = "SELECT
			ID 

			FROM diario_padron 

			WHERE 
			cliente = '$cliente' AND 
			clase   = 'Cliente'  AND 
			tipo    = 'Nacional'

			ORDER BY ID DESC";

			return $mysql->query( $query )->fetch_object()->ID;
		}
	}

	function proveedor( $mysql, $cliente, $nombre, $RFC, $n_dos )
	{
		$query = "SELECT
		ID 

		FROM diario_padron 

		WHERE 
		cliente = '$cliente' AND 
		n_uno   = '201'  AND 
		n_dos   = '$n_dos' AND
		RFC     = '$RFC'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			return $mysql->query( $query )->fetch_object()->ID;
		}else
		{
			crear(
				$mysql,
				'diario_padron',
				[
					[ 'cliente', $cliente ],
					[ 'n_uno', '201' ],
					[ 'n_dos', $n_dos ],
					[ 'nombre', $nombre ],
					[ 'RFC', $RFC ],
					[ 'naturaleza', 'A' ]
				]
			);

			$query = "SELECT
			ID 

			FROM diario_padron 

			WHERE 
			cliente = '$cliente' AND 
			n_uno   = '201'  AND 
			n_dos   = '$n_dos' 

			ORDER BY ID DESC";

			return $mysql->query( $query )->fetch_object()->ID;
		}
	}

	function banco( $mysql, $n_tres )
	{
		$data = null;

		$query = "SELECT
		nombre,
		otro 

		FROM diario_padron 

		WHERE 

		ID = '$n_tres'";

		$result = $mysql->query( $query );

		while ( $row = $result->fetch_assoc() ) 
		{
			$data = array(
				'banco' => $row['nombre'],
				'cta'   => $row['otro']
			);
		}

		return json_decode( json_encode( $data ), true );
	}

	function ref_fiscal ( $mysql, $tabla )
	{
		$query = "SELECT
		ID 

		FROM $tabla

		ORDER BY ID DESC 

		LIMIT 1";

		return $mysql->query( $query )->fetch_object()->ID;
	}
?>
