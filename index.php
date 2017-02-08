<?php
	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>BoE Filter</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<h1>Board of Elections Data Filter</h1>
<br />
<form method="get" action="/select.php">
Select County:<br />
<select name="county">
<?
	foreach($counties as $c) {
		?><option value="<?= $c ?>"><?= ucwords($c) ?></option><?
	}
?>
</select><input type="submit" value="go" />
</form>
<form method="get" action="/selectnyc.php">
Select NYC Borough:<br />
<select name="county">
<?
	foreach($boroughs as $b) {
		?><option value="<?= $b ?>"><?= ucwords($b) ?></option><?
	}
?>
</select><input type="submit" value="go" />
</form><br />
<? include ($_SERVER['DOCUMENT_ROOT'] .'/Maps/imagemap2.php') ?>
<strong>NEW!</strong> <a href="/search/">Search BoE Data</a><br />
</body>
</html>