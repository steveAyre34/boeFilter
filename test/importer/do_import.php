<?php
/**
	This is a file importer that can be used for the Real Property or Board of Elections File Uploads.
	NOTE: file headers don't need to be in the same ORDER as the database headers, however every file header must have a corresponding column in 
		their designated table.
		This importer checks if this is the case.
*/
require("connection.php");
require("import_createHeaders.php");
/**
	Checks that each of the file headers exists in their corresponding database table.
	Does not check the order of headers in the file -- the structure of our insert statement later renders this unnecessary
*/
function checkHeaders($databaseTableHeaders, $fileHeaders) {
	$return = array();
	print("<b>Database Headers from checkHeaders:</b><br>");
	foreach($databaseTableHeaders as $db) {
		print($db->name . ", ");
		array_push($return, $db->name);
	}
	print("<br><br>");
	print("<b>File Headers from checkHeaders:</b><br>");
	print_r($fileHeaders);
	print("<br><br>");
	foreach($fileHeaders as $f) {
		//print("Checking " . $f . "<br>");
		//$key = array_search($f, $return);
		if(($key = array_search($f, $return))!== false) {
			unset($return[$f]);
		}
		/*$flag = 0;
		foreach($databaseTableHeaders as $db) {
			if(strcmp($f, $db->name) == 0)
				$flag = 1;
		}
		if($flag == 0)
			array_push($return, $f);*/
	}
	
	if(empty($return))
		return 1;
	else
		return $return;
}
/**
	Trims header information to just names and returns array of column names 
	mysqli_fetch_fields returns an object, we only need names
*/
function trimHeaderInfo($databaseTableHeaders) {
	$return = array();
	foreach($databaseTableHeaders as $db) {
		array_push($return, $db->name);
		//print $db->name . "<br>";
	}
	return $return;
}
session_start();
foreach($_FILES['uploadFile']['name'] as $k => $v) {
	//File name will not have county name included
	//Prepend county name based on value chosen from dropdown menu
	$databaseTable = $_POST['county'] . '_' . $v;
	
	//Open file to be uploaded ('countyName_fileName.txt')
	$importFile = fopen($v, "r") or die("Unable to open file.");
	//Removes file extension from filename to give table name  
	$databaseTable = substr($databaseTable, 0, -4);
	//Connect to database, clear old data and then perform SELECT * so we can get table headers later
	$getDatabaseTable = mysqli_query($conn, "DELETE FROM " . $databaseTable);
	$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);
	//Get headers (in order) from specified table in the database and trim unnecessary object info 
	$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);
	$databaseTableHeaderNames = trimHeaderInfo($databaseTableHeaders);
	//Retrieves header layout from first line of file to be uploaded (each field is delimited with a tab)
	$fileHeaders = fgets($importFile);
	$fileHeaders = explode("\t", $fileHeaders);
	//$missingHeaders = array_intersect($databaseTableHeaderNames, $fileHeaders);
	$insertStatement = chooseHeaders($fileHeaders, $databaseTable);
	print($insertStatement . "<br>");
	//Check that each of the file headers has a corresponding column in the database table
	//If there is a file header without a column, upload will not be allowed to proceed because it will not work until column is added
	//if($check == 1) {
		//$uploadCount = 0;
		//$errorCount = 0;
						
			/*Creating the insert statement
			*/
			$insertStatement = "LOAD DATA INFILE '" . $v . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES(";
				
			//Now append the headers to the LOAD DATA INFILE statement, in the order they were retrieved from the file
			//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
			foreach($fileHeaders as $h) {
				$insertStatement .= $h . ", ";
			}
				
			//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
			$insertStatement = substr($insertStatement, 0, -2);
				
			//Now we specify the values to be inserted
			$insertStatement .= ");"; 
				
			/*//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
			$insertStatement = substr($insertStatement, 0, -2);*/
		//}
	
/*		$failedCount = mysqli_query($conn, $insertStatement);
		$checkUpload = "SELECT COUNT(*) FROM " . $databaseTable;
		$uploadCount = mysqli_query($conn, $checkUpload) or die(mysqli_error());
		$uploadCounter = mysqli_fetch_assoc($uploadCount);
		if($failedCount == true)
			print $uploadCounter['COUNT(*)'] . " records added successfully to " . $databaseTable . "<br>";
		/*else
			print $errorCount . " records not added successfully.<br>";
	}
	else {
		foreach($check as $c) {
			print $c . " is not a field in the database and must be added before import can be performed.<br>";
		} 
	}*/
}
?>