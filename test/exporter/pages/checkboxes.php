<?php 
require "connection.php";
session_start();
	// checks to see if field selection has been passed to form
	if (isset($_POST['fields'])) 
	{
		$fields = $_POST['fields'];
		$count = 0;
		
		// display selected fields
		echo '<div id="fields" name="fields">';
		foreach($fields as $field=>$value){
			if($count == count($fields) - 1){
				echo $value . "<br>";
				
				/*if (isset($_POST['county'])){
					$query = "SELECT DISTINCT " . $value . " FROM ".$_POST['county'];
					echo '<br><br>';
					$getdatabaseTable = mysqli_query($conn, $query);
					while ($row = mysqli_fetch_array($getdatabaseTable)){
						echo $row[$field]." ";
				}*/
				//}
			}
			else{
				echo $value . ", ";
			}	
			$count++;
		}
		
		echo '</div>';
	}
	
?>