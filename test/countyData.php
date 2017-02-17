<?php
require("connection.php");

function trimHeaderInfo($databaseTableHeaders) {
	$return = array();
	foreach($databaseTableHeaders as $db) {
		array_push($return, $db->name);
		//print $db->name . "<br>";
	}
	return $return;
}

session_start();
	$databaseTable = $_POST['county'];
	echo "You have selected " . $databaseTable . '<br><br>';
	echo "<b>Database Table Headers: </b><br>";
	$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);
	$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);
	$databaseTableHeaders = trimHeaderInfo($databaseTableHeaders);
	
	$fields = mysqli_num_fields($getDatabaseTable);
	echo "<br><b>Number of Fields: </b>" . $fields . "<br><br>";
	echo "<label>";
	foreach ( $databaseTableHeaders as $headers ){
		echo '<label><input type="checkbox" name="checkboxvar[]" value ="' . $headers . '"><span>' . $headers . '</span></input></label>';
	}
	echo "</label>";
	echo "<br><br>";
	echo '<button type="button" name="confirm" id="confirm">Confirm Selection</button>';
	echo "<br><br>";
	echo '<div id="selectedFields"></div>';
?>