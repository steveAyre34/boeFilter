<?php
require ("connection.php");

function trimHeaderInfo($databaseTableHeaders) {
	$return = array();
	foreach($databaseTableHeaders as $db) {
		array_push($return, $db->name);
		//print $db->name . "<br>";
	}
	return $return;
}

function printTable($getDatabaseTable, $databaseTableHeaders){
	if($getDatabaseTable->num_rows > 0){
		while($row = $getDatabaseTable->fetch_assoc()){
			$currentHeader = 0;
			foreach($databaseTableHeaders as $headers)
			{
				echo "<b>".$headers.": </b>".$row[$databaseTableHeaders[$currentHeader]]." ";
				$currentHeader++;
			}
			echo "<br><br>";
			//echo "<b>ID:</b>" . $row[$databaseTableHeaders[0]] . " <b>Full Name: </b>" . $row[$databaseTableHeaders[1]] . " <b>Address 1: </b>" . $row[$databaseTableHeaders[2]] . " <b>Address 2: </b>" . $row[$databaseTableHeaders[3]] . " <b>City: </b>" . $row[$databaseTableHeaders[4]] . " <b>State: </b>" . $row[$databaseTableHeaders[5]] . " <b>Zip: </b>" . $row[$databaseTableHeaders[6]] . " <b>Party: </b>" . $row[$databaseTableHeaders[7]] . "<br>";
		}
	}
}

function printCSV($getDatabaseTable, $databaseTableHeaders){
	
	$fields = mysqli_num_fields($getDatabaseTable);
	$header = '';
	$data = '';
	for( $i = 0; $i < $fields; $i++ ){
		$header .= $databaseTableHeaders[$i] . ",";
	}
	$rowNum = 0;
	while( $row = mysqli_fetch_row($getDatabaseTable) ){
		if($rowNum == 0){
			$rowNum++;
			continue;
		}
		$line = '';
		foreach( $row as $value){
			if( ( !isset( $value ) ) || ( $value == ",")){
				$value = ",";
			}
			else{
				$value = str_replace('"', '""', $value);
				$value = '"'.$value.'"'.",";
			}
			$line.=$value;
		}
		$data .= trim( $line ) . "\n";
	}
	$data = str_replace( "\r", "", $data );
	if ( $data == "" ){
		$data = "\n(0) Records Found!\n";
	}
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=yourReport.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$header\n$data";
}

session_start();
	$databaseTable = $_POST['county'];
	//echo "You have selected " . $databaseTable . " County" . '<br><br>';
	//echo "<b>Database Table Headers: </b><br>";
	$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);
	$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);
	$databaseTableHeaders = trimHeaderInfo($databaseTableHeaders);
	
	$fields = mysqli_num_fields($getDatabaseTable);
	//echo "<br><b>Number of Fields: </b>" . $fields . "<br><br>";
	
	//foreach ( $databaseTableHeaders as $headers ){
		//echo $headers . ", ";
	//}
	//echo "<br><br>";
	//printTable($getDatabaseTable, $databaseTableHeaders);
	printCSV($getDatabaseTable, $databaseTableHeaders);
	/*if($getDatabaseTable->num_rows > 0){
		while($row = $getDatabaseTable->fetch_assoc()){
			$currentHeader = 0;
			foreach($databaseTableHeaders as $headers)
			{
				echo "<b>".$headers.": </b>".$row[$databaseTableHeaders[$currentHeader]]." ";
				$currentHeader++;
			}
			echo "<br><br>";
			//echo "<b>ID:</b>" . $row[$databaseTableHeaders[0]] . " <b>Full Name: </b>" . $row[$databaseTableHeaders[1]] . " <b>Address 1: </b>" . $row[$databaseTableHeaders[2]] . " <b>Address 2: </b>" . $row[$databaseTableHeaders[3]] . " <b>City: </b>" . $row[$databaseTableHeaders[4]] . " <b>State: </b>" . $row[$databaseTableHeaders[5]] . " <b>Zip: </b>" . $row[$databaseTableHeaders[6]] . " <b>Party: </b>" . $row[$databaseTableHeaders[7]] . "<br>";
		}
	}*/
?>