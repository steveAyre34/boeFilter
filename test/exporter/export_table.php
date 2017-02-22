<?php
require ("connection.php");

session_start();
	$databaseTable = $_POST['county'];
	echo $databaseTable;
>