<?php
	require('../models/database.php');
	require('../models/Adresse.php');
	$database = new Database('localhost', 'carnet','root', '');
	$database->connect();
	$address = new Adresse($database);
	
	$data= file_get_contents("php://input");

	if(!empty($data)){
		$data = json_decode($data);
		$address->id = $data->id;
		if(!$address->delete()){
			echo 'fail';
		}
		else{
			echo 'success';
		}
	}
	else{
		echo 'fail';
	}
?>
