<?php
require("connection.php");

header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set('America/New_York');
$data = json_decode(file_get_contents("php://input"));
if(is_array($data) || is_object($data)){
	foreach ($data as $key => $value) {
		$_POST[$key] = $value;
	}
}

/*Get list of updated counties*/
/*RETURNS: county names*/
if(isset($_POST["get_county_list"])){
	$data_array = array();
	$result = mysqli_query($conn, "SELECT county_name FROM updated_counties");
	while($county = $result->fetch_assoc()){
		array_push($data_array, $county["county_name"]);
	}
	
	echo json_encode($data_array);
}
/*RETURNS: all counties currently in the updated list*/
else if(isset($_POST["get_counties"])){
	$data_array = array();
	$result = mysqli_query($conn, "SELECT county_name FROM updated_counties");
	while($county = $result->fetch_assoc()){
		array_push($data_array, $county["county_name"]);
	}
	
	echo json_encode($data_array);
}
/*Get general information based on column selected*/
/*PARAMS: column, county(lowercase letters)*/
/*RETURNS: count with what the count represents*/
else if(isset($_POST["get_column_info"])){
	$data_array = array();
	$column = $_POST["get_column_info"]->columnName;
	$county = $_POST["get_column_info"]->countyName;
	$table = $county . "_import";
	$result = mysqli_query($conn, "SELECT $column, COUNT(*) AS `num` FROM $table GROUP BY $column");
	$result_codes = mysqli_query($conn, "SELECT * codes WHERE county = '$county'");
	$code_array = getCodes($county, $column);
	$temp_array = array();
	while($info = $result->fetch_assoc()){
		$temp_array[$info[$column]]["count"] = $info["num"];
		if(isset($code_array[$info[$column]])){
			$temp_array[$info[$column]]["textual_representation"] = $code_array[$info[$column]];
		}
		else{
			$temp_array[$info[$column]]["textual_representation"] = "";
		}
		
		$data_array["content"] = $temp_array;
	}
	echo json_encode($data_array);
}

/*Retrieves query along with counts for householded and individual counts
 *PARAMS: county, searchCriteria(type array) {columnName, match, value, type}
 *RETURNS: Query, Individual Count, Householded Count Grouped by last name and address*/
else if(isset($_POST["retrieve_query"])){
	$data_array = array();
	$match_sql_array = array("exact" => "=",
							 "like" => "LIKE",
							 "less than" => "<=",
							 "greater than" => ">="
					);
	$searchCriteria = $_POST["retrieve_query"]->searchCriteria;
	$county = $_POST["retrieve_query"]->countyName;
	$query = "SELECT count(*) as count FROM " . $county . "_import WHERE 1=1";
	for($i = 0; $i < count($searchCriteria); $i++){
		$columnName = $searchCriteria[$i]->columnName;
		$match = $searchCriteria[$i]->match;
		$value = $searchCriteria[$i]->value;
		$type = $searchCriteria[$i]->type;
		
		if($type == "multiple"){ //for all multiple value options of dynamic dropdowns
			if(count($value) > 0){
				$query .= " AND (";
				for($ii = 0; $ii < count($value); $ii++){
					$this_value = $value[$ii];
					if($ii == 0){
						$query .= " $columnName = '$this_value'";
					}
					else{
						$query .= " OR $columnName = '$this_value'";
					}
				}
				$query .= ")";
			}
		}
		else if($type == "single"){ //For Searches with Single Value
			$match = $match_sql_array[$match];
			$query .= " AND ($columnName $match '$value')";
		}
		
		/*SPECIAL CASES*/
		else if($type == "like_history_years" || $type == "like_history_elections"){ //SPECIAL CASE: Voting History where either one or the other is selected(radio buttons or years ONLY)
			$query .= " AND (";
			for($ii = 0; $ii < count($value); $ii++){
				$history_count = 1;
				while($history_count <= 12){
					$history_column = "history" . $history_count;
					$item = $value[$ii];
					if($ii > 0 && $columnName == "Any" && $history_count == 1 && $type == "like_history_years"){
						$query .= " OR ";
					}
					else if($ii > 0 && (($columnName == "All" && $history_count == 1) || ($type == "like_history_elections" && $history_count == 1))){
						$query .= " AND ";
					}
					
					if($history_count == 1){
						$query .= "($history_column LIKE '%{$item}%'";
					}
					else{
						$query .= " OR $history_column LIKE '%{$item}%'";
					}
					$history_count++;
				}
				$query .= ")";
			}
			$query .= ")";
		}
		else if($type == "in_history"){ //For selecting specific elections with years
			$query .= " AND ((";
			for($ii = 0; $ii < count($value); $ii++){
				if($ii > 0 && $columnName == "Any"){
					$query .= " OR (";
				}
				else if($ii > 0 && $columnName == "All"){
					$query .= " AND (";
				}
				for($iii = 0; $iii < count($value[$ii]); $iii++){
					$this_value = $value[$ii][$iii];
					if($iii == 0){
						$query .= "'$this_value' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
					}
					else{
						$query .= " AND '$this_value' IN (history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12)";
					}
				}
				
				$query .= ")";
			}
			
			$query .= ")";
		}
	}
	
	$query_householded_count = $query . " GROUP BY last_name, street_no, street_name, apt_no, city, state, zip";
	$result_householded = mysqli_query($conn, $query_householded_count);
	$result = mysqli_query($conn, $query);
	$count = $result->fetch_assoc()["count"];
	$count_householded = mysqli_num_rows($result_householded);
	$data_array["sql_query"] = $query;
	$data_array["count"] = $count;
	$data_array["count_householded"] = $count_householded;
	echo json_encode($data_array);
}

/*Add query to queue
 *PARAMS: name, query
 *RETURNS: success message*/

else if(isset($_POST["add_to_queue"])){
	session_start();
	$name = $_POST["add_to_queue"]->name;
	$query = $_POST["add_to_queue"]->query;
	if(isset($_SESSION["queue"])){
		$_SESSION["queue"][$name] = $query;
	}
	else{
		$_SESSION["queue"] = array();
		$_SESSION["queue"][$name] = $query;
	}
	
	echo json_encode("Success");
}

/*Set county*/
else if(isset($_POST["county_session"])){
	session_start();
	$county = $_POST["county_session"]->countyName;
	$_SESSION["county"] = $county;
	echo json_encode("success");
}
/*Unset queue when cleared or leaving page*/
else if(isset($_POST["clear_queue"])){
	session_start();
	unset($_SESSION["queue"]);
	echo json_encode("success");
}

/*Export into CSV file
 *REQUIRES: Session queue to be set*/
else{
	ini_set("memory_limit", -1);
	gc_enable();
	gc_collect_cycles();
	session_start();
	$queries = $_SESSION["queue"];
	$county = $_SESSION["county"];
	$verified = $county . "_verified";
	$columns_selected = " voter_id, first_name, middle_name, last_name, street_no, street_name, apt_no, city, state, zip, zip4, party, history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12 ";
	$union_queries = array();
	
	$file_name = array_keys($queries)[0];
	
	foreach($queries as $query){
		$query_statement_1 = explode(" count(*) as count ", $query)[0];
		$query_statement_2 = explode(" count(*) as count ", $query)[1];
		array_push($union_queries, "(" . $query_statement_1 . $columns_selected . $query_statement_2 . ")");
	}
	
	$union_select = implode(" UNION ", $union_queries);
	$final_query = "SELECT t2.voter_id as voter_id, first_name, middle_name, last_name, street_no, street_name, apt_no, party, history1, history2, history3, history4, history5, history6, history7, history8, history9, history10, history11, history12, t2.city, t2.state, t2.zip, t2.zip4, count(t2.voter_id) as count FROM ("
					. $union_select . ") as t2 GROUP BY last_name, street_no, street_name, apt_no, city, state, zip";
	
	
	$verified_query = "SELECT voter_id, address1, address2, city, state, zip, zip4, crrt, dp3, foreign_city, foreign_country, foreign_pc FROM $verified";
	
	$file = fopen("php://memory", "w");
	$result_final = mysqli_query($conn, $final_query);
	$result_verified = mysqli_query($conn, $verified_query);
	$result_final_array = array();
	
	while($voter = $result_final->fetch_assoc()){
		$voter = array_map('rtrim', $voter);
		$voter_id = $voter["voter_id"];
		$unset_array = array("street_no", "street_name", "apt_no", "city", "state", "zip", "zip4");
		for($i = 0; $i < count($unset_array); $i++){
			unset($voter[$unset_array[$i]]);
		}
		if($voter["count"] == 1){
			$voter["first_name"] = ucfirst(strtolower($voter["first_name"]));
			$voter["middle_name"] = ucfirst(strtolower($voter["middle_name"]));
			$voter["last_name"] = ucfirst(strtolower($voter["last_name"]));
			$result_final_array[$voter_id] = $voter;
		}
		else{
			$voter["voter_id"] = "";
			$voter["first_name"] = "";
			$voter["middle_name"] = "";
			$voter["last_name"] = "The " . ucfirst(strtolower($voter["last_name"])) . " Family";
			$result_final_array[$voter_id] = $voter;
		}
		
		$result_final_array[$voter_id]["address1"] = "";
		$result_final_array[$voter_id]["address2"] = "";
		$result_final_array[$voter_id]["city"] = "";
		$result_final_array[$voter_id]["state"] = "";
		$result_final_array[$voter_id]["zip"] = "";
		$result_final_array[$voter_id]["zip4"] = "";
		$result_final_array[$voter_id]["crrt"] = "";
		$result_final_array[$voter_id]["dp3"] = "";
		$result_final_array[$voter_id]["foreign_city"] = "";
		$result_final_array[$voter_id]["foreign_country"] = "";
		$result_final_array[$voter_id]["foreign_pc"] = "";
		$result_final_array[$voter_id]["party"] = $voter["party"];
		$result_final_array[$voter_id]["count"] = $voter["count"];
		$result_final_array[$voter_id]["history1"] = $voter["history1"];
		$result_final_array[$voter_id]["history2"] = $voter["history2"];
		$result_final_array[$voter_id]["history3"] = $voter["history3"];
		$result_final_array[$voter_id]["history4"] = $voter["history4"];
		$result_final_array[$voter_id]["history5"] = $voter["history5"];
		$result_final_array[$voter_id]["history6"] = $voter["history6"];
		$result_final_array[$voter_id]["history7"] = $voter["history7"];
		$result_final_array[$voter_id]["history8"] = $voter["history8"];
		$result_final_array[$voter_id]["history9"] = $voter["history9"];
		$result_final_array[$voter_id]["history10"] = $voter["history10"];
		$result_final_array[$voter_id]["history11"] = $voter["history11"];
		$result_final_array[$voter_id]["history12"] = $voter["history12"];
	}
	
	while($voter = $result_verified->fetch_assoc()){
		if(array_key_exists($voter["voter_id"], $result_final_array)){
			$voter = array_map('rtrim', $voter);
			$voter_id = $voter["voter_id"]; 
			$result_final_array[$voter_id]["address1"] = $voter["address1"];
			$result_final_array[$voter_id]["address2"] = $voter["address2"];
			$result_final_array[$voter_id]["city"] = $voter["city"];
			$result_final_array[$voter_id]["state"] = $voter["state"];
			$result_final_array[$voter_id]["zip"] = $voter["zip"];
			$result_final_array[$voter_id]["zip4"] = $voter["zip4"];
			$result_final_array[$voter_id]["crrt"] = $voter["crrt"];
			$result_final_array[$voter_id]["dp3"] = $voter["dp3"];
			$result_final_array[$voter_id]["foreign_city"] = $voter["foreign_city"];
			$result_final_array[$voter_id]["foreign_country"] = $voter["foreign_country"];
			$result_final_array[$voter_id]["foreign_pc"] = $voter["foreign_pc"];
		}
	}
	
	fputcsv($file, ["Voter ID", "Name", "Address Line 1", "Address Line 2", "City", "State", "ZIP", "ZIP4", "CRRT", "DP3", "Party", "Foreign City", "Foreign Country", "Foreign Postal Code", "Family Members", "History 1", "History 2", "History 3", "History 4", "History 5", "History 6", "History 7", "History 8", "History 9", "History 10", "History 11", "History 12"]);
	foreach($result_final_array as $voter){
		$full_name = preg_replace('/\s\s+/', ' ', $voter["first_name"] . " " . $voter["middle_name"] . " " . $voter["last_name"]);
		$full_name = trim($full_name);
		fputcsv($file, array($voter["voter_id"], 
							 $full_name,
							 $voter["address1"],
							 $voter["address2"],
							 $voter["city"],
							 $voter["state"],
							 $voter["zip"],
							 $voter["zip4"],
							 $voter["crrt"],
							 $voter["dp3"],
							 $voter["party"],
							 $voter["foreign_city"],
							 $voter["foreign_country"],
							 $voter["foreign_pc"],
							 $voter["count"],
							 $voter["history1"],
							 $voter["history2"],
							 $voter["history3"],
							 $voter["history4"],
							 $voter["history5"],
							 $voter["history6"],
							 $voter["history7"],
							 $voter["history8"],
							 $voter["history9"],
							 $voter["history10"],
							 $voter["history11"],
							 $voter["history12"]));
	}
	
	gc_collect_cycles();
	fseek($file, 0);
	header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $file_name . '.txt";');
	fpassthru($file);
}

/*Return Code definitions from codes table*/
/*PARAMS: county, column*/
/*RETURNS: Assoc array with key as code and value as description*/
function getCodes($county, $column){
	require("connection.php");
	$codes_array = array();
	$county_capital = ucwords($county);
	$result = mysqli_query($conn, "SELECT code, description FROM codes WHERE county = '$county_capital' AND category = '$column'");
	while($code = $result->fetch_assoc()){
		$codes_array[$code["code"]] = $code["description"];
	}
	return $codes_array;
}
?>