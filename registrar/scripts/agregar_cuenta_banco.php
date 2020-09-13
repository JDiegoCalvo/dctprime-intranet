<?php
	include '../../connection.php';

	include 'funciones.php';

	if ( substr( $_POST['cuenta'], 0, 3 ) == '102' )
	{
		$naturaleza = 'D';
	}else
	{
		$naturaleza = 'A';
	}

	crear(
		$mysql,
		'diario_padron',
		[
			[ 'cliente', $_POST['cliente'] ],
			[ 'n_uno', substr( $_POST['cuenta'], 0, 3 ) ],
			[ 'n_dos', substr( $_POST['cuenta'], 4, 2 ) ],
			[ 'nombre', $_POST['nombre'] ],
			[ 'otro', $_POST['otro'] ],
			[ 'naturaleza', $naturaleza ]
		]
	);
?>