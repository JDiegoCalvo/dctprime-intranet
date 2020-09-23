<?php
	include '../../../con_intranet.php';

	include 'funciones.php';
	
	$usuario    = $_POST['usuario'];
	$contraseña = $_POST['contraseña'];

	$data = new stdClass;

	if ( usuario_existe() )
	{
		if ( contraseña_coincide() )
		{
			$data->responde = 'El usuario y la contrasena son correctos';

			$query = "SELECT
				ID 

			FROM usuarios 

			WHERE 

			usuario = '$usuario'";

			$usuario_ID = $mysql->query( $query )->fetch_object()->ID;

			$sesion = md5( date( 'Y-m-d H:i:s' ) );

			$query = "INSERT INTO sesiones (
				usuario,
				sesion
			) VALUES (
				'$usuario_ID',
				'$sesion'
			)";

			$mysql->query( $query );

			$data->sesion = $sesion;
		}else
		{
			$data->responde = 'La contrasena es incorrecta';
		}
	}else
	{
		$data->responde = 'El usuario no existe';
	}

	echo json_encode( $data );
?>
