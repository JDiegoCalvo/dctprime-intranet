<?php
	include '../../connection.php';

	include 'funciones.php';
	
	$usuario    = $_POST['usuario'];
	$contraseña = $_POST['contraseña'];

	if ( usuario_existe() )
	{
		if ( contraseña_coincide() )
		{
			$data = 'El usuario y la contraseña son válidos';
		}else
		{
			$data = 'La contraseña es incorrecta';
		}
	}else
	{
		$data = 'El usuario no existe';
	}

	echo $data;
?>
