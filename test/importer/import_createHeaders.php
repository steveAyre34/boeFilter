<?php
	//mysqli_fetch_fields yields a number for field type. This map will allow us to replace the number.
	$mysql_data_type_map = array(
		1	=>	'tinyint',
		2	=>	'smallint',
		3	=>	'int',
		4	=>	'float',
		5	=>	'double',
		7	=>	'timestamp',
		8	=>	'bigint',
		9	=>	'mediumint',
		10	=>	'date',
		11	=>	'time',
		12	=>	'datetime',
		13	=>	'year',
		16	=>	'bit',
		252	=>	'text',
		253	=>	'varchar',
		254	=>	'char',
		246	=>	'decimal'	
	);
?>
<!DOCTYPE html>
	<html>
	<head>
	<link rel='stylesheet' type='text/css' href='import.css'>
	</head>
		<body>
			<b>File Headers:</b><br>
			These are the file headers for <?php $databaseTable ?> ".txt. Please select a type and length for each field:<br><br>
			<?php foreach($fileHeaders as $f) {
				print $f; ?>
				<select name='headerType' id='headerType'>
				<option value='selected'>Choose a data type</option>
				<?php foreach($mysql_data_type_map as $k => $v) { ?>
					<option value='" . <?php $v ?> . "'><?php $v  ?></option>
			<?php } ?>
				</select>
				<input name='headerLength' id='headerLength' placeholder='Enter field length'></input><br><br>
			<?php } ?>
			<button type="submit" name="btn-upload">Next</button>
		</body>
	</html>
 