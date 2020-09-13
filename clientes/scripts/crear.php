<?php
	include '../../connection.php';

	$RFC          = $_POST['RFC'];
	$denominacion = $_POST['denominacion'];
	$nombre    = $_POST['nombre'];
	$primer_apellido  = $_POST['primer_apellido'];
	$segundo_apellido = $_POST['segundo_apellido'];
	$telefono  = $_POST['telefono'];
	$email     = $_POST['email'];
	$CIEC      = $_POST['CIEC'];
	$claveFIEL = $_POST['claveFIEL'];

	$query = "SELECT 
		ID

	FROM clientes 

	WHERE 

	RFC = '$RFC'";

	if ( $mysql->query( $query )->num_rows == 0 )
	{
		$query = "INSERT INTO clientes (
			RFC,
			denominacion,
			nombre,
			primer_apellido,
			segundo_apellido,
			telefono,
			email,
			CIEC,
			claveFIEL
		) VALUES (
			'$RFC',
			'$denominacion',
			'$nombre',
			'$primer_apellido',
			'$segundo_apellido',
			'$telefono',
			'$email',
			'$CIEC',
			'$claveFIEL'
		)";

		$mysql->query( $query );
	}
?>
