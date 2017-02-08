<?
require_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');

$counties = array (bronx, brooklyn,chautauqua,columbia,delaware,dutchess,erie,essex,greene,lewis,manhattan,nassau,orange,putnam,queens,rensselaer,rockland,saratoga,schenectady,schoharie,st_lawrence,statenisland,suffolk,sullivan,ulster,westchester);
$tables = array (address, import, verified);

foreach($counties as $county){
	foreach($tables as $table){
		if($table == 'import'){
			dump($county);
			dump($table);
			$sql = "alter table " . $county . "_import add primary key (voter_id)";
			echo($sql);
			if(!($link->query($sql)))
				echo($link->error);
			else
				echo("Updated $county import table <br>");
		}
		else{
			$sql = "alter table " . $county . "_" . $table . " add primary key (voterid)";
			echo($sql);
			if(!($link->query($sql)))
				echo($link->error);
			else
				echo("Updated $county $table table <br>");
		}
	}
}

?>