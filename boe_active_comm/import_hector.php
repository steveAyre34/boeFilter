<?php
//import hector's active comm data (boe) from http://www.elections.state.ny.us:8080/plsql_browser/active_comm
//into a mysql database so we can work with it nicely

$mysqli = new mysqli('localhost', 'development', 'yngew1e!', 'development');
if(mysqli_connect_errno()){
	print "Connection Failed: " . mysqli_connect_errno();
	exit();
}

//get contents of the file
$file = 'active_comm.htm';
$open_file = fopen($file, 'r');
$data = fread($open_file, filesize($file));
fclose($open_file);

//set up excel file
$file_out = "./boe_active_comm.xls";
$file_out = fopen($file_out, 'w');
$headers = array('id', 'name1', 'name2', 'address_line1', 'city_state_zip');
$headers = array_combine($headers, $headers);
writeExcelXML($file_out, $headers, 'head',20000, count($headers));


$data = explode('<hr>', $data);
foreach($data as $row){
	$row = str_replace("\n", '', $row);
	$row = explode('<BR>', $row);
	
	$line[0] = trim($row[0]);
	$line[1] = trim($row[1]);
	$line[2] = trim($row[2]);
	$line[3]  = trim($row[3]);
	$line[4] = trim($row[4]);
	if(count($row) > 6){
		$line[0]  = trim($row[0]);
		$line[1]  = trim($row[1]);
		$line[2]  = trim($row[2]);
		$line[3]  = trim($row[4]);
		$line[4]  = trim($row[5]);
	}
	writeExcelXML($file_out, $line);
}
writeExcelXML($file_out, FALSE, 'foot');
fclose($file_out);

function dump($d) {
	print '<pre>';
	if (is_string($d) || is_int($d)) {
		print $d;
	} else {
		print_r($d);
	}
	print '</pre>';
	ob_flush();
	flush();
}

// write an XML Excel file
	function writeExcelXML($fp, $row, $mode='row', $rows=FALSE, $cols=FALSE) {
		static $headers;
		switch ($mode) {
			default:
			case 'row':
				$out = "\r\n\t<Row>";
				reset($row);
				$k=0;
				foreach($row as $c=>$v) {
					$k++;
					if (empty($v)) continue;
					$type = 'String';
					$style = 's21';

					// look for dates
					if (isset($headers[$c]) && preg_match('/(^date$|^date\s|\sdate$)/i', $headers[$c])) {
						$type = 'DateTime';
						$style = 's22';
					}

					$out .= "\r\n\t\t".'<Cell ss:Index="'. $k .'" ss:StyleID="'. $style .'"><Data ss:Type="'. $type .'">'. htmlentities($v) .'</Data></Cell>';
				}

				$out .= "\r\n\t</Row>";
				break;

			case 'head':
				// hang on to header row for later
				$headers = $row;

				// setup XML output file
				$xml = array();
				$xml['datetime'] = date('Y-m-d\TH:i:s\Z');
				$xml['rowhead'] = '<Cell ss:StyleID="s24"><Data ss:Type="String">'. implode('</Data></Cell>
		<Cell ss:StyleID="s24"><Data ss:Type="String">', $row) .'</Data></Cell>';
				$xml['rowcount'] = $rows;
				$xml['colcount'] = $cols;
				$out = '<?xml version="1.0"?>
		<?mso-application progid="Excel.Sheet"?>
		<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
		 xmlns:o="urn:schemas-microsoft-com:office:office"
		 xmlns:x="urn:schemas-microsoft-com:office:excel"
		 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
		 xmlns:html="http://www.w3.org/TR/REC-html40">';
				$out .= <<<XMLHEADEND
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <LastAuthor>Cornerstone Services, Inc.</LastAuthor>
  <Created>{$xml['datetime']}</Created>
  <Version>11.8132</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12660</WindowHeight>
  <WindowWidth>21900</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>135</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s22">
   <NumberFormat ss:Format="[ENG][$-409]mmmm\ d\,\ yyyy;@"/>
  </Style>
  <Style ss:ID="s24">
   <Font x:Family="Swiss" ss:Bold="1"/>
   <Interior ss:Color="#99CCFF" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s25">
   <Font x:Family="Swiss" ss:Bold="1"/>
   <Interior ss:Color="#99CCFF" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Data">
  <Table ss:ExpandedColumnCount="{$xml['colcount']}" ss:ExpandedRowCount="{$xml['rowcount']}" x:FullColumns="1"
   x:FullRows="1">
   <Row ss:StyleID="s25">
		{$xml['rowhead']}
   </Row>
XMLHEADEND;
				break;

			case 'foot':
				// finish XML output
				$out = <<<XMLFOOTEND
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <Print>
    <ValidPrinterInfo/>
    <HorizontalResolution>1200</HorizontalResolution>
    <VerticalResolution>1200</VerticalResolution>
   </Print>
   <Selected/>
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>1</SplitHorizontal>
   <TopRowBottomPane>1</TopRowBottomPane>
   <ActivePane>2</ActivePane>
   <Panes>
    <Pane>
     <Number>3</Number>
    </Pane>
    <Pane>
     <Number>2</Number>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
XMLFOOTEND;
				break;
		}
		
		fwrite($fp, $out);
	}
?>