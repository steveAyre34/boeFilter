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
// displays fields related to selected table
session_start();
	// get table name from POST array
	$databaseTable = $_POST['county'];
	
	// display table name and corresponding headers
	echo "You have selected " . $databaseTable . '<br><br>';
	echo "<b>Database Table Headers: </b><br>";
	$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);
	$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);
	$databaseTableHeaders = trimHeaderInfo($databaseTableHeaders);
	
	// report number of fields in table
	$fields = mysqli_num_fields($getDatabaseTable);
	echo "<br><b>Number of Fields: </b>" . $fields . "<br><br>";
	echo "<label>";
	$count = 0;
	// show each table field as a check box
	foreach ( $databaseTableHeaders as $headers ){
		if($count%5 === 0)
			echo '<br>';
		$count++;
		echo '<label><input type="checkbox" name="checkboxvar[]" value ="' . $headers . '"><span>' . $headers . '</span></input></label>';
	}
	
	echo "</label>";
	echo "<br><br>";
	
	// button to confirm field selection for export
	echo '<button type="button" name="confirm" id="confirm">Confirm Selection</button>';
	echo "<br><br>";
	echo '<div id="selectedFields"></div>';
?>