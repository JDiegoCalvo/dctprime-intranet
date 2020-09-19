<?php
	function fecha_limite( $fecha )
	{
		$año   = substr( $fecha, 0, 4 );
		$month = substr( $fecha, 5, 2 );
		$dia   = substr( $fecha, 8, 2 );

		$m = [ 
			null, 'Enero', 'Febrero',
			'Marzo', 'Abril', 'Mayo',
			'Junio', 'Julio', 'Agosto',
			'Septiembre', 'Octubre', 'Noviembre',
			'Diciembre' 
		];

		for ( $i = 0; $i < count( $m ); $i++ )
		{
			if ( intval( $month ) == $i )
			{
				$mes = $m[$i];
			}
		}

		return $dia . ' / '. $mes . ' / ' . $año;
	}
?>
