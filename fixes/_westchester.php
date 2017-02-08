<?php
	//***************************new westchester import script, BR 6/6/11 ******************************//
	//					rewrote this script becuase the old one did not work and was very messy		.	//
	//					unforunately I wrote it to use the php dbase extention as per the old code,		//
	//					and learned that it is extremely slow and buggy, so I had to convert the		//
	//					the file from dbf to csv and tweak the code to work on a csv file using			//
	//					the functions I had wrote for php dbase, which reads the database rows as		//
	//					associative arrays																//
	//**************************************************************************************************//
	// import new data into BoE database
	$county = 'westchester';
	$table = $county .'_import';
$tableH = $county .'_convert';
	$import_type = 'csv';

	//voter history mapping to codes used by other cities in boe filter
	$voter_history = array(
'E121106' => 'GE12',
		'E120913' => 'PE12',
		'E120424' => 'PP12',
		'E111108' => 'GE11',
		'E110913' => 'PE11',
		'E101102' => 'GE10',
		'E100914' => 'PE10',
		'E091103' => 'GE09',
		'E090915' => 'PE09',
		'E080205' => 'PP08',
		'E080909' => 'PE08',
		'E081104' => 'GE08',
		'E070911' => 'PE07',
		'E071106' => 'GE07',
		'E061107' => 'GE06'

	);




	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	$sql = <<<EOT
select * from westchester_convert
where westchester_convert.VOTERID IN
(SELECT westchester_import.voter_id from westchester_import)
EOT;
set_time_limit(90);
$result = $link->query($sql);
$numrows = $result->num_rows;
echo "There are $numrows updates to check<br/>";
flush();
$i = 0;
updateProgress($i, 'import');
	


$voter_updates = array();
while ($row = $result->fetch_assoc()) {
    updateProgress(++$i/(2*$numrows), 'import');
    set_time_limit(30);
    $history = array();
    foreach ($voter_history as $col => $code)
    {
        if ($row[$col] > 0)
        {
            $i = count($history)+1;
            if ($i <= 12)
            {
                $index = 'history'.$i;
                $history[$index] = $code;
            }

        }



    }

    if (count($history) > 0)
    {
        $voter_id = $row['VOTERID'];
        $voter_updates[$voter_id]=$history;
    }
}
    foreach ($voter_updates as $vid => $history)
    {
        set_time_limit(60);
        updateProgress(++$i/(2*$numrows), 'import');
        $sql = "UPDATE $table SET ";
        foreach ($history as $col => $val)
        {
            if ($col != 'history1')
            {
                $sql .= ', ';
            }
            $sql .= " $col = '$val' ";
        }
        $sql .= " WHERE voter_id = '$vid'";
        $update = $link->query($sql);
    }



	
	print('Finished fixing voting history');
	function escape_apos(&$val, $key) {
		$val = str_replace("'", "''", $val);
	}
	
	function clear(&$val, $key)
	{
		$val = '';
	}
		
	//fix common errors in data leading to non-CASS as per BOE_Errors
	function cleanup($address){
		$data = $address;
		$corrections = array(
			'(\b(RT|RTE|RTE\.)\b)' => 'ROUTE',
			'(P(\s|\.)+(O|0)(\s|\.)+B(O|0)?X)' => 'PO BOX',
			'(\b(MNT|MTN)\b)' => 'MOUNTAIN',
			'(COUNTY\sHWY)' => 'COUNTY HIGHWAY',
			'(OLD\sSTATE\sHWY|OLD\sRTE\.|OLD\sRT|OLD\sRTE)' => 'OLD ROUTE',
			'(STATE\s(HIGHWAY|HWY))' => 'ROUTE',
			'(COUNTY\sRD)' => 'ROUTE',
			'(BOX|MAIL\sBOX)' => 'BOX'
		);
		foreach ($corrections as $regex => $fix){
			$correct = preg_replace($regex, $fix, $data);
			if ($correct != $data){
				return $correct;
			}
		}
		return $data;
	}
	
	//resolve town code
	function getTown($twd){
		global $town;
		foreach($town as $code=>$name){
			if($code >= $twd)
				return $name;				
		}
	}
	
	//fix second address line
	function fix2($in){
		global $cols;
		$line = trim($in);
		if($line == '0' || $line == '.' || $line == '`')
			return '';
		$leave = '(APT|APARTMENT|FL|FLOOR|LOFT|LOT|HOUSE|BLDG|BUILDING|BASEMENT|BSMT|BMT|BSMN|UNIT|RFD|ROOM|RM|ROUTE|STE|SUITE|STUDIO|UNIT|RD|HALL|UNIV|COLLEGE|SUNY|BOX)';
		if(preg_match($leave, $line) == 1) return $line;
		//check for apartment number
		return ('Apt ' . $in);
		//check for floor	
		
	}
?>
