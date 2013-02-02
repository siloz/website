<?php
class Common{

/**
 * MysqlQuery($query)
 * @param $query;
 * @param $return; what to return
 * @return $result; //or false on error or empty
 *
 */
function MysqlQuery($query,$return = false){
	$result = mysql_query($query);
	$id = mysql_insert_id();
	$count = mysql_affected_rows();
	if($count >= 1){$exist = true;}
	elseif($return != "count"){return false;}
	
	switch(strtolower($return)){
		case "count": return $count; break;
		case "id":
		case "record_id": return $id;
		case "exist":
		case "exists": return $exist; break;
		case "row": return self::MysqlRow($result); break;
		case "extract_count": return self::MysqlExtractCount($result); break;
		default: return self::MysqlResult($result); break;
	}
}

/**
 *
 *
 *
 */
private function MysqlResult($result){
	if(mysql_affected_rows() >= 1){return $result;}
	else{return false;}
}

/**
 *
 *
 *
 */
private function MysqlRow($result){
	return mysql_fetch_object($result);	
}

private function MysqlExtractCount($result){
	while($row = mysql_fetch_object($result)){
			if($row->count < 1 ){return 0;}
			else{return $row->count;}	
	}
}

public function DaysAgo($int){
	return self::AddSubtractTime($int,"subtract","Day");
}

public function AddDays($int){
	return self::AddSubtractTime($int,"add","Day");
}

function AddSubtractTime($int,$operation,$object){
	switch(strtolower($operation)){
		case "add":
		case "+": $o = "+"; break;
		default: $o = "-"; break;
	}
	$t = $object;
	//uncomment the next line for testing changes the intervals to minutes
	//$t = "MINUTE";
	$query = "select (NOW() ".$o." INTERVAL ".$int." ".$t.") as time;";
	error_log($query);
	$result = mysql_query($query);
	$x = mysql_fetch_object($result);
	return $x->time;
} // end AddSubtractTime($int,$operation,$object)

function RemoteIp(){
	return $_SERVER["REMOTE_ADDR"];
}

function MoneyFormat($amount){
	return money_format("%.0n", floatval($amount));
}

}// end class
?>
