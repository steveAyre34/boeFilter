<?php
	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');

	$county = $_GET['county'];

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Find Records</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="/main.css" type="text/css" />
	<script src="forms.js" type="text/javascript"></script>
</head>

<body>
<form name="frm_search" action="results_nyc.php" method="post" target="_blank">
<input type="hidden" name="todo" value="" />
<input type="hidden" name="county" value="<?= $county ?>" />
<table width="600" border="0" cellpadding="2" cellspacing="2">
	<tr valign="bottom">
		<td class="dcheader">Find Records (<?= $county ?>):</td>
		<td align="right">Data Age: <?= date('Y-m-d', strToTime(getDataAge($link, $county))) ?></td>
	</tr>
	<tr><td colspan="2"><hr size="1"></td></tr>
	<tr>
		<td colspan="2"><strong>Output Columns (or check labels below):</strong></td>
	</tr>
		<td><input type="checkbox" name="cols[]" id="standardcols" value="standard" checked="checked" disabled="disabled" title="Standard output" /><label for="standardcols">VoterID, Full Name, Address/City/State/ZIP</label></td><br />
			<td><?= makeCheckbox('cols[]', 'last_name', 'Last Name') ?><br /></td>
	</tr>
	<tr valign="top" class="dcfieldname">
		<td align="right">Run counts and reports for&nbsp;</td>
		<td><input type="radio" name="household" id="household-y" value="household" checked="checked" title="Return counts and records for households" /><label for="household-y">Households</label><br />
			<input type="radio" name="household" id="household-n" value="individuals" title="Return counts and records for individuals" /><label for="household-n">Individuals</label></td>
	</tr>
</table>
<table border="0" cellpadding="2" cellspacing="2">
<tr valign="top">
<?php
	// print selection lists for this county
	makeMultiList($link, 'zip', $county, 'ZIP', 'zip');
?>
	<td><table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="dcfieldname"><?= makeCheckbox('cols[]', 'dob', 'Age') ?></td>
			<td><input type="text" name="age" size="3" maxlength="3" /></td>
			<td><table width="100%" cellpadding="2" cellspacing="2" border="0">
					<tr><td class="dcrowtitle"><input type="radio" name="age_range" value="1" checked="checked" />and older</td></tr>
					<tr><td class="dcrowtitle"><input type="radio" name="age_range" value="2" />and younger</td></tr>
				</table></td>
		</tr>
		<tr>
			<td class="dcfieldname"><?= makeCheckbox('cols[]', 'sex', 'Sex') ?></td>
			<td colspan="2"><select name="sex">
					<option value="-1" selected="selected">---- Ignore ----</option>
					<option value="m">Male</option>
					<option value="f">Female</option>
				</select></td>
		</tr>
		<tr>
			<td class="dcfieldname" width="150"><?= makeCheckbox('cols[]', 'reg_datetime', 'Registration Date (YYYY-MM-DD)') ?></td>
			<td class="dcrowtitle"><input type="text" name="reg_year" size="4" maxlength="4" />
				<select name="reg_month">
<?
	foreach (array('','January','February','March','April','May','June','July','August','September','October','November','December') as $k=>$v) {
		print "<option value=\"{$k}\">{$v}</option>";
	}
?></select>
				<select name="reg_day"><option value="0"></option>
<?
	for ($i=1; $i<=31; $i++) {
		print "<option value=\"{$i}\">{$i}</option>";
	}
?></select></td>
			<td><table width="100%" cellpadding="2" cellspacing="2" border="0">
					<tr><td class="dcrowtitle"><input type="radio" name="reg_range" value="1" checked="checked" />and after this date</td></tr>
					<tr><td class="dcrowtitle"><input type="radio" name="reg_range" value="2" />and before this date</td></tr>
				</table></td>
			</tr>
		<tr>
			<td class="dcfieldname" width="150"><?= makeCheckbox('cols[]', 'reg_datetime', 'Year Last Voted (YYYY)') ?></td>
			<td class="dcrowtitle"><input type="text" name="last_year_voted" size="4" maxlength="4" /></td>
			<td><table width="100%" cellpadding="2" cellspacing="2" border="0">
					<tr><td class="dcrowtitle"><input type="radio" name="last_year_voted_range" value="1" checked="checked" />and after this year</td></tr>
					<tr><td class="dcrowtitle"><input type="radio" name="last_year_voted_range" value="2" />and before this year</td></tr>
				</table></td>
			</tr>
			<tr>
			<td valign="top" class="dcfieldname" width="150">Advanced Criteria:<br />
				(SQL for WHERE clause)</td>
			<td colspan="2" class="dcrowtitle"><textarea name="adv_where" rows="4" cols="40"></textarea></td>
			</tr>
		</table></td>
	</tr>
</table>


<table border="0" cellpadding="6" cellspacing="0">
	<tr><td colspan="3"><hr size="1" /></td></tr>
	<tr>
 		<td align="left" colspan="2"><input type="reset" class="button" value="Reset Filter" /></td>
		<td align="right"><input type="submit" class="button" value="Count &#187;" onClick="setAction('count');" /></td>
	</tr>
	<tr><td colspan="3"><hr size="1" /></td></tr>
	<tr valign="top">
<?php
	// print selection lists for this county
	makeMultiList($link, 'party', $county, 'Party Affiliation', 'party');
	makeMultiList($link, 'district', $county, 'Electoral District', 'district');
	makeMultiList($link, 'cong_district', $county, 'NYS Congressional District', 'cong_district');
?>
	</tr>
	<tr><td colspan="3"><hr size="1" /></td></tr>
	<tr valign="top">
<?php
	// print selection lists for this county
	makeMultiList($link, 'asm_district', $county, 'NYS Assembly District', 'asm_district');
	makeMultiList($link, 'sen_district', $county, 'NYS Senate District', 'sen_district');
	makeMultiList($link, 'council_district', $county, 'NYC Council District', 'council_district');
?>
	</tr>
	<tr><td colspan="3"><hr size="1" /></td></tr>
	<tr valign="top">
<?php
	// print selection lists for this county
	makeMultiList($link, 'civcourt_district', $county, 'Civil Court District', 'civcourt_district');
?>
	</tr>

	<tr><td colspan="3"><hr size="1" /></td></tr>
	<tr>
 		<td align="left" colspan="2"><input type="reset" class="button" value="Reset Filter" /></td>
		<td align="right"><input type="submit" class="button" value="Count &#187;" onClick="setAction('count');" /></td>
	</tr>
</table>
<input type="hidden" name="data_age" value="<?= strToTime(getDataAge($link, $county)) ?>" />
</form>
</body>
</html>
<?php
	// format dislpay text for an item in a list
	function fmtListItem($id, $name, $num, $length=45) {
		$idlen = 3;
		$out = trim($id);

		// check for non-ids, like ZIPs
		if (empty($name) && strLen($out) > $idlen) {
			$c = 3;
			$idlen = strLen($out) + $c;
			$out .= str_repeat('&nbsp;', $c);
			$c = $length - $idlen - strLen(trim($num));
			if ($c > 0) {
				$out .= str_repeat('&nbsp;', $c) . trim($num);
			} else {
				$out .= ' '. trim($num);
			}
		} else {
			$c = $idlen - strLen($out);
			$out .= str_repeat('&nbsp;', $c) .': '. trim($name);
			$c = $length - $idlen - 2 - strLen(trim($name)) - strLen(trim($num));
			if ($c > 0) {
				$out .= str_repeat('&nbsp;', $c) . trim($num);
			} else {
				$out .= ' '. trim($num);
			}
		}

		return $out;
	}

	// return a radio button option
	function makeRadioOption($name, $value, $label, $align='left', $checked=FALSE) {
		$id = preg_replace('/[^A-Za-z0-9\_\-]/', '', $name .'_'. $value);
		$button = '<input id="'. $id .'" type="radio" name="'. $name .'" value="'. $value .'"'. ($checked ? ' checked="checked"' : '') .' />';
		$label = '<span onclick="document.getElementById(\''. $id .'\').checked = !document.getElementById(\''. $id .'\').checked;"  class="cmsCheckbox">'. $label .'</span>';

		if ($align == 'left') {
			$out = $button . $label;
		} else {
			$out = $label .'&nbsp;'. $button;
		}
/*
		$out = <<<OUTEND
<div id="{$id}_bg" onmouseover="CMSrowOver('{$id}_bg');" onmouseout="CMSrowOut('{$id}_bg');">{$out}</div>
OUTEND;
*/
		return $out;
	}


	// return a checkbox
	function makeCheckbox($name, $value, $label, $align='left', $checked=FALSE) {
		$id = preg_replace('/[^A-Za-z0-9\_\-]/', '', $name .'_'. $value);
		$button = '<input id="'. $id .'" type="checkbox" name="'. $name .'" value="'. $value .'"'. ($checked ? ' checked="checked"' : '') .' />';
		$label = '<label for="'. $id .'">'. $label .'</label>';

		if ($align == 'left') {
			$out = $button . $label;
		} else {
			$out = $label .'&nbsp;'. $button;
		}
/*
		$out = <<<OUTEND
<div id="{$id}_bg" onmouseover="CMSrowOver('{$id}_bg');" onmouseout="CMSrowOut('{$id}_bg');">{$out}</div>
OUTEND;
*/
		return $out;
	}


	// display a list
	function makeMultiList($link, $type, $county, $label, $name, $width=FALSE) {
		$proc = mssql_init('get_filter_nyc', $link);
		mssql_bind($proc, '@col', $type, SQLVARCHAR);
		mssql_bind($proc, '@grp', strToLower($county), SQLVARCHAR);
		$result = mssql_execute($proc);
		$count = mssql_num_rows($result);
		$exc = $count > 1 ? '<td align="right">'. makeCheckbox('exclude[]', $name, 'Exclude') .'</td>' : '';
		$label = makeCheckbox('cols[]', $name, $label);

		print <<<LABELEND
<td><div class="dcfieldname"><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td>{$label}</td>
		{$exc}
	</tr>
</table></div>
LABELEND;

		if ($count > 1) {
			// print list
			if ($width !== FALSE) {
				$length = $width - 4;
				$width = ' style="width: '. $width .'ex;"';
			}

			print '<select name="'. $name .'[]" class="dcinput" multiple="multiple" size="8"'. $width .'><option value="-1" selected="selected">---- Ignore ----</option>';
			for($i=0; $i<$count; $i++) {
				$row = mssql_fetch_array($result);
				$id = $row[0];
				$txt = $row[1];
				$cnt = $row[2];
				$txt = $width ? fmtListItem($id, $txt, $cnt, $length) : fmtListItem($id, $txt, $cnt);
				echo '<option value="'. $id .'">'. $txt .'</option>';
			}
			print '</select>';
		} else {
			if ($count == 1) {
				$row = mssql_fetch_array($result);
				$id = $row[0];
				$txt = $row[1];
				$cnt = $row[2];
				if (!empty($id) && (int)$id != 0) {
					print '<em>'. fmtListItem($id, $txt, $cnt, -1) .' records</em>';
				} else {
					print '<em>none found</em>';
				}
			} else {
				print '<em>none found</em>';
			}
			print '<input type="hidden" name="'. $name .'[]" value="-1" />';
		}

		print '</td>';
	}

	// show the age of the data
	function getDataAge($link, $county) {
		$proc = mssql_init('get_data_age', $link);
		mssql_bind($proc, '@grp', $county, SQLVARCHAR);
		$result = mssql_execute($proc);
		$row = mssql_fetch_array($result);
		return $row[0];
	}
?>
