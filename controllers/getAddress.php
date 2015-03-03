<?php
	require('../models/database.php');
	require('../models/Adresse.php');
	$database = new Database('localhost', 'carnet','root', '');
	$database->connect();
	$address = new Adresse($database);

	echo json_encode($address->findAll());
?>
