<?php
	function usuario_existe()
	{
		global $usuario, $mysql;

		$query = "SELECT 
			ID 

		FROM usuarios 

		WHERE 

		usuario = '$usuario'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			return true;
		}else
		{
			return false;
		}
	}

	function contraseña_coincide()
	{
		global $usuario, $contraseña, $mysql;

		$query = "SELECT 
			ID 

		FROM usuarios 

		WHERE 

		usuario    = '$usuario' AND 
		contrasena = '$contraseña'";

		if ( $mysql->query( $query )->num_rows > 0 )
		{
			return true;
		}else
		{
			return false;
		}
	}
?>
