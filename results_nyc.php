<?php
	require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
	define('DEBUG', FALSE);
	define('EXCEL_XML', TRUE);

	//$fs = realpath('./export/result.txt');
	$fs = realpath('./boe_export.xls');
	// look for file delivery
	if (isset($_GET['dl'])) {
		dlFile($fs, 'EXPORT_EXCEL');
	}

#	ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?php
	// sanity check on location
	if (isset($_POST['county']) && !in_array($_POST['county'], $boroughs)) {
		die ("I don't know anything about the borough '{$county}'");
	}
	$county = $_POST['county'];
	$household = $_POST['household'] == 'household' ? 1 : 0;
	$age = $_POST['age'];
	$age_range = $_POST['age_range'];
	$sex = $_POST['sex'];
	$adv_where = $_POST['adv_where'];
	if($_POST['last_year_voted'] != '') 
		$last_voted = $_POST['last_year_voted']; 
	else  
		$last_voted = '-1';
	$last_voted_range = $_POST['last_year_voted_range'];
	if($_POST['reg_year'] != '') { $reg_date = $_POST['reg_year'].'-'.$_POST['reg_month'].'-'.$_POST['reg_day']; } else { $reg_date = '-1'; }
	$reg_range = $_POST['reg_range'];
	$todo = $_POST['todo'];
	$exclude = '||'. implode('|', (isset($_POST['exclude']) && is_array($_POST['exclude']) ? $_POST['exclude'] : array())) .'||';

	// Voter History
	if (isset($_POST['vote_history_type'])) {
		$data_age = $_POST['data_age'];
		$vote_num = (int)$_POST['vote_num'];
		$vote_range = (int)$_POST['vote_range'];
		$vote_rangetype = isset($_POST['vote_rangetype']) ? $_POST['vote_rangetype'] : FALSE;
		$vote_all = isset($_POST['vote_all']);
		$vote_general = isset($_POST['vote_general']);
		$vote_primary = isset($_POST['vote_primary']);
		$vote_pres = isset($_POST['vote_pres']);
		$vote_village = isset($_POST['vote_village']);
		$vote_history_type = $_POST['vote_history_type'];
		$vote_year_and = (int)$_POST['vote_year_and'] == 1;
		$vote_years = isset($_POST['vote_years']) ? $_POST['vote_years'] : FALSE;

		debug('Vote_History_Type --> '. $vote_history_type);

		// do we care about voter history?
		if (in_array($vote_history_type, array('range','year'))) {
			debug('Vote_Num --> '. $vote_num);
			debug('Vote_Range --> '. $vote_range);
			$history_where = FALSE;

			if ($vote_num > 0 && $vote_range > 0 && $vote_range >= $vote_num && in_array($vote_rangetype, array('years', 'elections'))) {
				// "this year" has to be based on the age of the data
				$thisyear = date('y', $data_age);

				// Dirty!! **TODO**
				// if the data doesn't include the November election, start one year back
				if ($data_age < strToTime('November 7, '. date('Y'))) {
					$thisyear = str_pad(--$thisyear, 2, '0', STR_PAD_LEFT);
				}

				// check election types
				$elections = array();
				
				if ($vote_all || (!$vote_general && !$vote_primary && !$vote_pres && !$vote_village)) {
					$elections[] = '%';
				} else {
					if ($vote_general) $elections[] = 'GE';
					if ($vote_primary) $elections[] = 'PE';
					if ($vote_pres) $elections[] = 'PP';
					if ($vote_village) $elections[] = 'VP';
				}

				// check criteria type
				if ($vote_history_type == 'range') {
					// check range type
					if ($vote_rangetype == 'years') {
						$years = array($thisyear);
						for ($i=1; $i<$vote_range; $i++) {
							$years[] = str_pad(--$thisyear, 2, '0', STR_PAD_LEFT);
						}


						$checks = array();

						// loop years
						foreach ($years as $y) {
							$tmp = array();

							// loop history columns
							for ($i=1; $i<=12; $i++) {

								// loop elections
								foreach ($elections as $e) {
									$tmp[] = "history{$i} LIKE '{$e}{$y}'";
								}
							}

							// build check
							$checks[] = implode(' OR ', $tmp);
						}

						if ($vote_num == $vote_range) {
							$history_where = 'AND (('. implode(') AND (', $checks) .'))';
						} else {
							// if we're doing "1 of n" skip this whole thing
							if ($vote_num == 1) {
								$history_where = 'AND ('. implode(' OR ', $checks) .')';
							} else {
								$cases = $permutations = array();
								$i=$vote_num;
								while ($i > 0) {
									$permutations[] = $vote_range - $i;
									$i--;
								}
								$done = FALSE;

								debug('Going through permutations:<br /><table cellspacing="0" cellpadding="0" border="0">', FALSE);
								do {
									$tmp = array();
									$ready = FALSE;

									// grab checks for this iteration
									debug('<tr><td>&nbsp;&nbsp;'. implode(',', $permutations) .'&nbsp;&nbsp;</td>', FALSE);
									reset($permutations);
									foreach ($permutations as $p) $tmp[] = $checks[$p];
									$cases[] = '('. implode(') AND (', $tmp) .')';

									// update permutations array & check if done
									$i = 0;
									while(!$ready) {
										// pick index to work with
										if ($permutations[$i] > $i) {
											$permutations[$i]--;
											while ($i > 0) {
												$i--;
												$permutations[$i] = $permutations[$i+1] - 1;
											}
											$ready = TRUE;
										} else {
											if ($i < count($permutations) -1) {
												$i++;
											} else {
												$done = $ready = TRUE;
											}
										}
									}
								} while (!$done);
								debug('</table>', FALSE);

								$history_where = 'AND (('. implode(') OR (', $cases) .'))';
							}
						}
					} else {
						$years = FALSE;
					}

					debug('Looking for voters in these years --> &apos;'. implode(', &apos;', $years));
					debug('Looking for voters in these election types --> '. implode(', ', $elections));
				}
			} elseif (is_array($vote_years) && count($vote_years) > 0) {
				// check election types
				$elections = array();
				if ($vote_all || (!$vote_general && !$vote_primary && !$vote_pres && !$vote_village)) {
					$elections[] = '%';
				} else {
					if ($vote_general) $elections[] = 'GE';
					if ($vote_primary) $elections[] = 'PE';
					if ($vote_pres) $elections[] = 'PP';
					if ($vote_village) $elections[] = 'VP';
				}

				$checks = array();
				reset ($vote_years);
				foreach ($vote_years as $y) {
					$tmp = array();

					// loop history columns
					for ($i=1; $i<=12; $i++) {

						// loop elections
						foreach ($elections as $e) {
							$tmp[] = "history{$i} LIKE '{$e}{$y}'";
						}
					}

					// build check
					$checks[] = implode(' OR ', $tmp);
				}

				$history_where = 'AND (('. implode(($vote_year_and ? ') AND (' : ') OR ('), $checks) .'))';
			}

			debug('Voter history SQL --> <pre>'. str_replace(')', ")\n", $history_where) .'</pre>');

			// add to advanced criteria where clause
			$adv_where .= $history_where;

			// clean up
			unset($history_where);
			unset($elections);
			unset($years);
			unset($cases);
			unset($done);
			unset($ready);
			unset($i);
			unset($permutations);
			unset($tmp);
			unset($checks);
			unset($p);
			unset($thisyear);
			unset($vote_num);
			unset($vote_range);
			unset($vote_rangetype);
			unset($vote_all);
			unset($vote_general);
			unset($vote_primary);
			unset($vote_pres);
			unset($vote_village);
			unset($vote_history_type);
			unset($vote_year_and);
			unset($vote_years);
		}
	}

	// setup filters
	$filter = array_keys($_POST);
	$skip = array('cols', 'household', 'data_age', 'exclude', 'age', 'age_range', 'sex', 'reg_year', 'reg_month', 'reg_day', 'reg_range','last_voted_range', 'county', 'todo', 'adv_where', 'vote_num', 'vote_range', 'vote_rangetype', 'vote_all', 'vote_general', 'vote_primary', 'vote_pres', 'vote_village', 'vote_history_type', 'vote_year_and', 'vote_years');

	debug('Looking for filters:<br /><table cellspacing="0" cellpadding="0" border="0">', FALSE);
	foreach ($filter as $f) {
		if (in_array($f, $skip)) continue;
		debug("<tr><td>&nbsp;&nbsp;{$f}&nbsp;&nbsp;</td>", FALSE);

		if (is_array($_POST[$f])) {
			$all = "{$f}_all";
			if($_POST[$f][0] == '-1') {
				debug('<td>--</td></tr>', FALSE);
				$$all = '-1';
			} else {
				debug('<td>'. count($_POST[$f]) . (isset($_POST['exclude']) && is_array($_POST['exclude']) && in_array($f, $_POST['exclude']) ? ' - exclude' : '') .'</td></tr>', FALSE);
				$$all = "'". implode("','", $_POST[$f]) ."'" ;
			}
		}
	}
	debug('</table>');


	debug('Looking for custom column selection:<br /><table cellspacing="0" cellpadding="0" border="0">', FALSE);
	$adv_select = array();
	if (isset($_POST['cols'])) {
		foreach ($_POST['cols'] as $c) {
			debug("<tr><td>&nbsp;&nbsp;{$c}&nbsp;&nbsp;</td>", FALSE);
			switch ($c) {
				case 'phone':
					$adv_select[] = "CASE WHEN RTRIM(LTRIM(i.area_code)) <> '' THEN RTRIM(LTRIM('(' + i.area_code + ') ' + i.telephone)) ELSE RTRIM(LTRIM(i.telephone)) END AS [phone]";
					break;

				case 'reg_source':
					$adv_select[] = "i.reg_soruce AS [registration source]";
					break;

				case 'last_year_voted':
					$adv_select[] = "REPLACE(CONVERT(VARCHAR(10), i.{$c}, 102), '.', '-') + 'T00:00:00.000' AS [last year voted]";
					break;

				case 'voter_status':
					$adv_select[] = "i.{$c} AS [voter status]";
					break;

				case 'dob':
					$adv_select[] = "REPLACE(CONVERT(VARCHAR(10), i.{$c}, 102), '.', '-') + 'T00:00:00.000' AS [date of birth]";
					break;


				// skip these, we'll have them anyway
				case 'zip':
				case 'standardcols':
					break;

				case 'district':
					$adv_select[] = "i.{$c} AS [electoral district]";
					break;

				case 'cong_district':
					$adv_select[] = "i.{$c} AS [NYS congressional district]";
					break;

				case 'sen_district':
					$adv_select[] = "i.{$c} AS [NYS senate district]";
					break;

				case 'council_district':
					$adv_select[] = "i.{$c} AS [council district]";
					break;

				case 'leg_district':
					$adv_select[] = "i.{$c} AS [county legislative district]";
					break;

				case 'asm_district':
					$adv_select[] = "i.{$c} AS [NYS assembly district]";
					break;

				case 'civcourt_district':
					$adv_select[] = "i.{$c} AS [civcourt district]";
					break;

				default:
					$adv_select[] = "RTRIM(LTRIM(i.{$c})) AS [{$c}]";
					break;
			}
		}
		debug('</table>');
	}

	$adv_select = count($adv_select) > 0 ? ', '. implode(', ', $adv_select) : '';
	switch ($todo) {
		/***************************************************************/
		/***                   G E T   C O U N T S                				   ***/
		/***************************************************************/
		case 'count':
			ob_flush();
			flush();
			echo 'Processing...<br /><br />';
			ob_flush();
			flush();
			echo 'Unique household count:<br /><br /><br />';

			
			$proc = mssql_init('count_households_nyc', $link);

			mssql_bind($proc, '@grp', strToLower($county), SQLVARCHAR);
			mssql_bind($proc, '@zipcode', $zip_all, SQLTEXT);
			mssql_bind($proc, '@age', $age, SQLVARCHAR);
			mssql_bind($proc, '@age_range', $age_range, SQLVARCHAR);
			mssql_bind($proc, '@sex', $sex, SQLVARCHAR);
			mssql_bind($proc, '@reg_date', $reg_date, SQLVARCHAR);
			mssql_bind($proc, '@reg_range', $reg_range, SQLVARCHAR);
			mssql_bind($proc, '@party', $party_all, SQLTEXT);
			mssql_bind($proc, '@district', $district_all, SQLTEXT);
			mssql_bind($proc, '@cong_district', $cong_district_all, SQLTEXT);
			mssql_bind($proc, '@sen_district', $sen_district_all, SQLTEXT);
			mssql_bind($proc, '@civcourt_district', $civcourt_district_all, SQLTEXT);
			mssql_bind($proc, '@asm_district', $asm_district_all, SQLTEXT);
			mssql_bind($proc, '@council_district', $council_district_all, SQLTEXT);
			mssql_bind($proc, '@last_voted', $last_voted, SQLVARCHAR);
			mssql_bind($proc, '@last_voted_range', $last_voted_range, SQLVARCHAR);
			mssql_bind($proc, '@exclude', $exclude, SQLTEXT);
			mssql_bind($proc, '@adv_where', $adv_where, SQLTEXT);
			mssql_bind($proc, '@household', $household, SQLBIT);

			if (DEBUG) {
				dump(strToLower($county));
				dump($zip_all);
				dump($age);
				dump($age_range);
				dump($sex);
				dump($reg_date);
				dump($reg_range);
				dump($party_all);

				dump($district_all);
				dump($cong_district_all);
				dump($sen_district_all);

				dump($asm_district_all);

				dump($last_voted);
				dump($exclude);
				dump($adv_where);
				dump($household);
			}

			$result = mssql_execute($proc);
			$row = mssql_fetch_array($result);
			echo $row[0] .'<br />';
			debug('<strong>DEBUGGING: DATA MAY NOT BE CORRECT!!!</strong>');

			// Show form to get file
			print '<form name="frm_getfile" action="/results_nyc.php" method="post">';
			reset($_POST);
			foreach ($_POST as $k=>$v) {
				if ($k == 'todo') continue; // skip old todo

				// handle differently if this is an array
				if (is_array($v)) {
					reset($v);
					foreach ($v as $vv) {
						print "<input type=\"hidden\" name=\"{$k}[]\" value=\"{$vv}\" />";
					}
				} else {
					print "<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" />";
				}
			}
			print '<input type="hidden" name="todo" value="file" /><input type="submit" name="" value="Get File &#187;" /></form>';
			break;

		/***************************************************************/
		/***                     G E T   F I L E                     ***/
		/***************************************************************/
		case 'file':
			pp('Retrieving records from the database...<br /><br />');

			$proc = mssql_init('dump_address_nyc', $link);

			mssql_bind($proc, '@grp', $county, SQLVARCHAR);
			mssql_bind($proc, '@zipcode', $zip_all, SQLTEXT);
			mssql_bind($proc, '@age', $age, SQLVARCHAR);
			mssql_bind($proc, '@age_range', $age_range, SQLVARCHAR);
			mssql_bind($proc, '@sex', $sex, SQLVARCHAR);
			mssql_bind($proc, '@reg_date', $reg_date, SQLVARCHAR);
			mssql_bind($proc, '@reg_range', $reg_range, SQLVARCHAR);
			mssql_bind($proc, '@voted_last', $last_voted, SQLVARCHAR);
			mssql_bind($proc, '@party', $party_all, SQLTEXT);
			mssql_bind($proc, '@district', $district_all, SQLTEXT);
			mssql_bind($proc, '@cong_district', $cong_district_all, SQLTEXT);
			mssql_bind($proc, '@sen_district', $sen_district_all, SQLTEXT);
			mssql_bind($proc, '@leg_district', $leg_district_all, SQLTEXT);
			mssql_bind($proc, '@asm_district', $asm_district_all, SQLTEXT);
			#mssql_bind($proc, '@council_district', $council_district_all, SQLTEXT);
			#mssql_bind($proc, '@civcourt_district', $civcourt_district_all, SQLTEXT);
			mssql_bind($proc, '@exclude', $exclude, SQLTEXT);
			mssql_bind($proc, '@adv_select', $adv_select, SQLTEXT);
			mssql_bind($proc, '@adv_where', $adv_where, SQLTEXT);
			mssql_bind($proc, '@village', $village_all, SQLTEXT);
			mssql_bind($proc, '@exclude', $exclude, SQLTEXT);
			mssql_bind($proc, '@adv_select', $adv_select, SQLTEXT);
			mssql_bind($proc, '@adv_where', $adv_where, SQLTEXT);
			set_time_limit(60);
			$rs = mssql_execute($proc);
			if (DEBUG) {
				dump(strToLower($county));
				dump($zip_all);
				dump($age);
				dump($age_range);
				dump($sex);
				dump($reg_date);
				dump($reg_range);
				dump($party_all);

				dump($district_all);
				dump($cong_district_all);
				dump($sen_district_all);

				dump($asm_district_all);

				dump($last_voted);
				dump($exclude);
				dump($adv_where);
				dump($household);
			}
			$count = mssql_num_rows($rs);
			debug("Found {$count} records<br />");

			if ($household) {
				pp('Raw household count dumped... Processing duplicates...<br /><br />');
			} else {
				pp('Raw individuals dumped... Processing duplicates...<br /><br />');
			}

			debug('Opening stream to output file...', FALSE);
			$fs = './boe_export.xls';
			$fp = fopen($fs, 'w');
			debug('done.');

			$pu_interval = 1500; // update progress bar every 'x' records
			$prev = FALSE; // previous row for household comparison

			print '<table cellspacing="0" cellpadding="0" border="0"><tr><td>';
			updateProgress(0); // initialize progress bar
			print '<td><form name="frmDownload" method="get" action="/results_nyc.php" style="margin: 0px; padding: 0px;"><input type="submit" name="dl" value="Please Wait" disabled="disabled" /></form></td></tr></table>';

			// loop through data
			debug('Beginning loop');
			for ($i=0; $i<$count; $i++) {
				set_time_limit(30);
				if ($i%$pu_interval === 0) updateProgress($i/$count); // update progress bar

				// get new record
				$cur = mssql_fetch_assoc($rs);

				// guess salutation based on gender
				switch ($cur['gender']) {
					case 'M':
						$cur['fullname'] = 'Mr. '. $cur['fullname'];
						break;

					case 'F':
						$cur['fullname'] = 'Ms. '. $cur['fullname'];
						break;
				}

				// don't need this anymore, so reset it
				unset($cur['gender']);

				// skip to next if nothing to compare against (first record)
				if ($prev === FALSE) {
					$prev = $cur; // update 'previous' record for next iteration

					// prepare and write headers
					$headers = $prev;
					unset($headers['lastname']);
					$headers = array_keys($headers);
					$headers = array_combine($headers, $headers);
					foreach($headers as $k=>$v) $headers[$k] = ucwords($v);

					if (EXCEL_XML && $count < 655565) {
						writeExcelXML($fp, $headers, 'head', $count+1, count($headers));
					} else {
						fwrite($fp, implode("\t", $headers) ."\n");
					}

					unset($headers);
					continue;
				}

				// compare against previous record to find households
				if ($household                                                         // we're householding, yes!
					&& $cur['address 1'] == $prev['address 1']                           // same street address
					&& ($cur['lastname'] == $prev['lastname']                            // same last name
						|| in_array($prev['lastname'], explode('-', $cur['lastname']))     // previous last name is part of current hypenated name
						|| in_array($cur['lastname'], explode('-', $prev['lastname'])))) { // current last name is part of previous hypenated name
					// skip the hyphenated name
					$cur['fullname'] = 'The '. (strPos('-', $cur['lastname']) > 0 ? $prev['lastname'] : $cur['lastname']) .' Family';
				} else {
					// new family - write last family to file
					// decapitlize
					decap($prev['fullname'], 'NAME');
					decap($prev['address 1'], 'ADDRESS');
					decap($prev['address 2'], 'ADDRESS');
					decap($prev['address 3'], 'ADDRESS');

					// write to output file
					$out = $prev;
					unset($out['lastname']);

					if (EXCEL_XML) {
						writeExcelXML($fp, $out);
					} else {
						fwrite($fp, implode("\t", $out). "\n");
					}

					unset($out);
				}

				$prev = $cur; // update 'previous' record for next iteration
			}

			// write last record to file
			if (is_array($prev)) {
				// decapitlize
				decap($prev['fullname'], 'NAME');
				decap($prev['address 1'], 'ADDRESS');
				decap($prev['address 2'], 'ADDRESS');
				decap($prev['address 3'], 'ADDRESS');

				// write to output file
				unset($prev['lastname']);

				if (EXCEL_XML) {
					writeExcelXML($fp, $prev);
				} else {
					fwrite($fp, implode("\t", $prev). "\n");
				}
			}

			// close XML file
			writeExcelXML($fp, FALSE, 'foot');

			updateProgress(1); // finish progress bar
			print <<<SCRIPTEND
<script type="text/javascript">
	document.frmDownload.dl.value = 'Get File';
	document.frmDownload.dl.disabled = false;
</script>
SCRIPTEND;

			unset($prev);
			unset($cur);
			fclose($fp);
			debug('<b>IN TESTING - DATA MAY NOT BE CORRECT!!!</b>');
			break;
	}

	$_end = microtime(TRUE);
	$_elapsed = $_end - $_start;
	debug('Excution time: '. round((float)$_elapsed,3) .' seconds');
?>
</body>
</html>
