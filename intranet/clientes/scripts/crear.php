<?php
	include '../../../con_intranet.php';

	$num_servicio = random_int( 11111, 99999 );
	$RFC          = $_POST['RFC'];
	$denominacion = $_POST['denominacion'];
	$nombre       = $_POST['nombre'];
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
			num_servicio,
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
			'$num_servicio',
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
