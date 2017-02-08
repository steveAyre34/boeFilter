<?php
	ob_start();
	set_time_limit(1000);
?>

<html>
<head>
<title>Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

<?php
	ob_flush();
	flush();
	echo 'Processing...<BR><BR>';
	ob_flush();
	flush();
	$i = 1;
	$datapos = 0;
	$buffer = "";

	$handle = @fopen("damon1.txt", "r");
	$fp = fopen("out.txt", "w");
	$header = "AccountName\tBuyer\tDBA\t\tphones\taddress1\taddress2\tcsz\tnotes\temail\n";
	fwrite($fp, $header);

	if ($handle) {
		while (!feof($handle)) {
			if($i < 8) {
			// Continue Getting Data
				$buffer = $buffer.fgets($handle, 4096);
				$i++;
			} else {
			// Process data
				$buffer = $buffer.fgets($handle, 4096);
				// echo $buffer.'<BR><BR>';
				$parts = explode("\n", $buffer);

				// Skip the 1st line of junk data
				$line = explode("\t", $parts[1]);
				$accountname = substr($line[$datapos], 0, (strlen($line[$datapos]) - 6));
				$datapos++;
				if($line[$datapos] == "") { $datapos++; }
				$buyer = substr($line[$datapos], 0, (strlen($line[$datapos]) - 6));
				$datapos = 0;

				//Line #3
				$line = explode("\t", $parts[2]);
				$dba = substr($line[$datapos], 0, (strlen($line[$datapos]) - 5));
				if($dba == "DBA") { $dba = ""; }
				$datapos++;
				if($line[$datapos] == "") { $datapos++; }
				// echo $line[$datapos].'<BR>';
				$phones = $line[$datapos];
				$datapos = 0;

				//Line #4
				$line = explode("\t", $parts[3]);
				$address1 = substr($line[$datapos], 0, (strlen($line[$datapos]) - 6));
				$datapos++;
				$datapos = 0;

				//Line #5
				$line = explode("\t", $parts[4]);
				$address2 = $line[$datapos];
				$datapos++;
				if($line[$datapos] == "") { $datapos++; }
				$datapos++;
				$datapos = 0;

				//Line #6
				$line = explode("\t", $parts[5]);
				$csz = $line[$datapos];
				$datapos++;
				if(strlen($line[$datapos]) == 6) {
				// ZIPCode Found
					$csz = $csz.' '.$line[$datapos];
					$datapos++;
				}
				$notes = substr($line[$datapos], 5, strlen($line[$datapos]));
				$datapos = 0;

				//Line #7
				$line = explode("\t", $parts[6]);
				$country = $line[$datapos];
				$datapos++;
				$datapos = 0;

				//Line #8
				$line = explode("\t", $parts[7]);
				$datapos = $datapos+2;
				if($line[$datapos] == "") { $datapos++; }
				$email = substr($line[$datapos], 6, strlen($line[$datapos]));
				$write = $accountname."\t".$buyer."\t".$dba."\t".$phones."\t".$address1."\t".$address2."\t".$csz."\t".$country."\t".$notes."\t".$email."\n";
				fwrite($fp, $write);

				$i = 1;
				$buffer = "";
				$datapos = 0;
			}
		}

		// echo $line[$datapos].'<BR>';

		fclose($handle);
		fclose($fp);
	}



	?>

</body>
</html>

