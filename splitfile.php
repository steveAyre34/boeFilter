<?php
print('Split Large File Into Two');
$county = "manhattan";
$fsr =  $county .'.txt' ;
$fr = fopen($fsr, 'r');
$fswa = 'unverified'.$county.'a.txt';
$fwa = fopen($fswa, 'w');
$fswb = 'unverified'.$county.'b.txt';
$fwb = fopen($fswb, 'w');
if($fr){
	while(!feof($fr)){
	$i = 0;
	$numrows = count(file($fsr, FILE_SKIP_EMPTY_LINES));
	if($i <= $numrows / 2){
		$line = fgets($fr);
		fwrite($fwa, $line);
		unset($line);
	}
	//else{
		//$line = fgets($fr);
		//fwrite($fwb, $line);
		//unset($line);
	//}
	$i++;
	}
}
fclose($fwa);
fclose($fwb);
fclose($fr);
?>