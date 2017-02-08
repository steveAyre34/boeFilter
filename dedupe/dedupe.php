<?php
	ob_start();
?>

<html>
<head>
<title>Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<?php
	flush();
	echo 'Processing...<BR><BR>';
	flush();
	set_time_limit(1000);
	$link = mssql_connect('localhost', 'sa', 'crst2778');
	mssql_select_db('[dedupe]', $link);

	$sql = "SELECT * FROM dedupe ORDER BY address1, address2";
	$result = mssql_query($sql, $link);
	$count = mssql_num_rows($result);

	// Fetch the first row for comparison
	$row = mssql_fetch_array($result);
	$id = $row[0];
	$prefix = $row[1];
	$first = $row[2];
	$middle = $row[3];
	$last = $row[4];
	$suffix = $row[5];
	$address1 = $row[6];
	$address2 = $row[7];
	$city = $row[8];
	$state = $row[9];
	$zip = $row[10];
	$emcomment = $row[11];
	$family = "";

	$fp = fopen("d:/wwwroot/dedupe.txt", 'w');
	$cust_head = "id\tprefix\tfirst\tmiddle\tlast\tsuffix\tfamily\taddress1\taddress2\tcity\tstate\tzip\temcomment\n";
	fwrite($fp, $cust_head);
	$cust_body = "";
	$out = 0;
	$dupes = 0;

	for($i=0; $i<$count; $i++) {
		$out++;
		if ($out == 100) {
			ob_flush(); flush();
			echo '.';
			ob_flush(); flush();
			$out = 0;
			fwrite($fp, $cust_body);
			$cust_body = "";
		}
		$row = mssql_fetch_array($result);
		// echo '~'.strtolower($row[2]).'~ / ~'.strtolower($last).'~ ---------- ~'.strtolower($row[4]).'~ / ~'.strtolower($address1).'~';
		if( (strtolower($row[4]) == strtolower($last)) && (strtolower($row[6]) == strtolower($address1)) ) {
			$family = "The ".$row[4]." Family";
			// echo ' <-- Match ***';
			$dupes++;
		} else {
			$cust_body = $cust_body.$id."\t".$prefix."\t".$first."\t".$middle."\t".$last."\t".$suffix."\t".$family."\t".
			$address1."\t".$address2."\t".$city."\t".$state."\t".$zip."\t".$emcomment."\n";
			$id = $row[0];
			$prefix = $row[1];
			$first = $row[2];
			$middle = $row[3];
			$last = $row[4];
			$suffix = $row[5];
			$address1 = $row[6];
			$address2 = $row[7];
			$city = $row[8];
			$state = $row[9];
			$zip = $row[10];
			$emcomment = $row[11];
			$family = "";
		}
		// echo '<BR>';
	}
	fwrite($fp, $cust_body);

	flush();
	echo '<BR><BR>Duplicates removed ('.$dupes.'), tab-delimited TXT file created.<BR><BR><BR>';

?>

</body>
</html>
