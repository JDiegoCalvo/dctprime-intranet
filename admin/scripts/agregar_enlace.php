<?php
	include '../../con_intranet.php';

	if (
		isset( $_POST['place'] )  AND
		isset( $_POST['name'] )
	)
	{
		$place = $_POST['place'];
		$name  = $_POST['name'];

		$query = "INSERT INTO enlaces (
			place,
			name
		) VALUES (
			'$place',
			'$name'
		)";
		$mysql->query( $query );
	}
?>
