<?php

//Recupère les données envoyé par Post par AngularJs
$data= file_get_contents("php://input");
require('../models/database.php');
require('../models/Adresse.php');
$database = new Database('localhost', 'carnet', 'root', '');
$database->connect();
$address = new Adresse($database);

if(!empty($data)){
	$data = json_decode($data);
	$address->lastName = $data->lastname;
	$address->firstName = $data->firstname;
	$address->phone = $data->phone;
	$address->code = $data->code;
	$address->city = $data->city;
	$address->address = $data->address;

	$address->save(true);
	echo 'success';
}
else{
	echo 'fail';
}