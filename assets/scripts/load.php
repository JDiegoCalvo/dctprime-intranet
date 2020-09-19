<?php
	include '../../connection.php';

	$_POST = json_decode( file_get_contents( 'php://input' ), true );
	$data = array();

	if ( $_POST["theme"] == 'Recientes' )
	{
		$query = "SELECT title, description, URL, imageURL, dates FROM post ORDER BY dates DESC";
		$result = $mysql->query( $query );
	}else
	{
		$query = "SELECT title, description, URL, imageURL, dates FROM post WHERE theme = '$_POST[theme]' ORDER BY dates DESC";
		$result = $mysql->query( $query );
	}

	while ( $row = $result->fetch_row() ) 
	{
		$start = new DateTime( $row[4] );
		$end = new DateTime( date( 'Y-m-d H:i:s' ) );
		$interval = $start->diff( $end );

		if ( $interval->format( '%m' ) == 0 )
		{
			if ( $interval->format( '%d' ) == 0 )
			{
				$timeAgo = 'Hace un momento.';
			}else if ( $interval->format( '%d' ) == 1 ) 
			{
				$timeAgo = 'Ayer.';
			}else if ( $interval->format( '%d' ) >= 2 && $interval->format( '%d' ) <= 6 ) 
			{
				$timeAgo = $interval->format( 'Hace %d días.' );
			}else if ( $interval->format( '%d' ) >= 7 && $interval->format( '%d' ) <= 13 )
			{
				$timeAgo = 'Hace 1 semana.';
			}else if ( $interval->format( '%d' ) >= 14 && $interval->format( '%d' ) <= 20 )
			{
				$timeAgo = 'Hace 2 semanas.';
			}else if ( $interval->format( '%d' ) >= 21 && $interval->format( '%d' ) <= 29 )
			{
				$timeAgo = 'Hace 3 semanas.';
			}
		}else if ( $interval->format( '%m' ) == 1 )
		{
			$timeAgo = $interval->format( 'Hace 1 mes.' );
		}else
		{
			$timeAgo = $interval->format( 'Hace %m meses.' );
		}

		array_push( $data, array(
			'title' => $row[0],
			'description' => $row[1],
			'URL' => $row[2],
			'imageURL' => $row[3],
			'timeAgo' => $timeAgo
		));
	}

	echo json_encode( $data );
?>
