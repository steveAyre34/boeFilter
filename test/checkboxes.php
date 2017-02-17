<?php 
	if (isset($_POST['fields'])) 
	{
		$fields = $_POST['fields'];
		$count = 0;
		echo '<div id="fields" name="fields">';
		foreach($fields as $field=>$value){
			if($count == count($fields) - 1){
				echo $value;
			}
			else{
				echo $value . ", ";
			}	
			$count++;
		}
		echo '</div>';
	}
?>