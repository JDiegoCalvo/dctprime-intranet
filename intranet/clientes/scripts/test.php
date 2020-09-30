<?php
	$response = [
		"payable"    => true,
		"min_amount" => 100,
		"max_amount" => 250000
	];

	echo json_encode( $response );
?>
