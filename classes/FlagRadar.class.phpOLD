<?php
class FlagRadar{

/**
 * FlagRadar()
 *
 * mysql> desc flag_radar;
 *+---------+----------------------------+------+-----+-------------------+----------------+
 *| Field   | Type                       | Null | Key | Default           | Extra          |
 *+---------+----------------------------+------+-----+-------------------+----------------+
 *| id      | int(11)                    | NO   | PRI | NULL              | auto_increment |
 *| who     | enum('user','silo','item') | YES  |     | NULL              |                |
 *| status  | enum('warn','cancel')      | YES  |     | NULL              |                |
 *| cause   | varchar(100)               | NO   |     | NULL              |                |
 *| user_id | int(11)                    | NO   |     | NULL              |                |
 *| silo_id | int(11)                    | NO   |     | NULL              |                |
 *| created | timestamp                  | NO   |     | CURRENT_TIMESTAMP |                |
 *| flag_id | int(11)                    | YES  |     | NULL              |                |
 *| active  | int(3)                     | YES  |     | NULL              |                |
 *| item_id | int(11)                    | YES  |     | NULL              |                |
 *+---------+----------------------------+------+-----+-------------------+----------------+
 *10 rows in set (0.00 sec)
 *
 */
protected $id;
var $who; // who is on the radar
var $status; //status of the radar
var $cause; //what caused this radar
var $user_id; // the user id
var $silo_id; //the silo associated with this radar entry
var $created; //the day this record was created
var $flag_id; // flag->id
var $active; //if this record is 
var $item_id; // items->item_id 

function _construct($id){
	if($id){return $this->Populate($id);}
}


function Populate($id){
	$query = 
		"SELECT * "
		."FROM flag_radar "
		."WHERE "
		."id = '".mysql_real_escape_string($id)."';"
	;
	return $this->PopulateThis($query);

}

function GetIds($value,$key=false,$other_value = false){
	switch(strtolower($key)){
		case "silo":
		case "silo_id": $key = "silo_id"; break;
		case "flag":
		case "flag_id": $key = "flag_id";
		case "status": $key = "status"; break;
		case "who": $key = "who"; break;
		default: $value = "all"; break;
	}
	$query = "SELECT id from flag_radar WHERE ";
	if($value === "all"){
		$query .= 
			"active = '1' "
		;
	}else{
		$query .= 
			"AND `".$key."` = '".mysql_real_escape_string($value)."' "
		;	
	}
	if($other_value === "view_radar"){
		$query .= "AND (hidden IS NULL OR hidden = '0') ";
	}
	
	$query .= "ORDER BY id DESC ";
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){
			$x[] = $row->id;
		}
		return $x;
	}else{return false;}
	
}

function PopulateByItemStatus($item_id,$flag_id,$status){
	$query = 
		"SELECT * "
		."FROM flag_radar "
		."WHERE "
		."`item_id` = '".mysql_real_escape_string($item_id)."' "
		."AND `flag_id` = '".mysql_real_escape_string($flag_id)."' "
		."AND `status` = '".mysql_real_escape_string($status)."' "
		."AND `who` = 'item' "
		."AND `active` = '1' "
	;
	return $this->PopulateThis($query);
}

function PopulateBySiloStatus($silo_id,$flag_id,$status){
	$query = 
		"SELECT * "
		."FROM flag_radar "
		."WHERE "
		."`silo_id` = '".mysql_real_escape_string($silo_id)."' "
		."AND `flag_id` = '".mysql_real_escape_string($flag_id)."' "
		."AND `status` = '".mysql_real_escape_string($status)."' "
		."AND `who` = 'silo' "
		."AND `active` = '1' "
		
	;
	return $this->PopulateThis($query);
}

private function PopulateThis($query){
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		while($row = mysql_fetch_object($result)){
			foreach($row as $key => $value){$this->$key = $value;}
		}
		return $this;
	}else{return false;}
}

function UpdateStatus($status){
	$Radar = new Radar();
	if($status == "restore"){
		$this->active = 0;
		if($this->who === "item"){
			$Radar->RestoreItem($this->item_id,$this->id);
		}elseif($this->who === "silo"){
			$Radar->RestoreSilo($this->silo_id,$this->id);
		}
		return $this->Save();
	}elseif($status == "cancel"){
		if($this->status != "cancel"){
			$this->hidden = 1;
			$this->Save();
			$this->id = false;
			$this->status = "cancel";
			$this->id = $this->Save();
		}
		if($this->who === "item"){
			$Radar->CancelItem($this->item_id,$this->id);
		}elseif($this->who === "silo"){
			$Radar->CancelSilo($this->silo_id,$this->id);
		}
		return $this->id;
	}else{return false;}
}



function Save(){
	if($this->id){ return $this->Update(); }
	else{ return $this->Insert(); }
}

private function Update(){
	$query = (
		"UPDATE "
		."`flag_radar` "
		."SET "
		."`who` = '".mysql_real_escape_string($this->who)."', "
		."`cause` = '".mysql_real_escape_string($this->cause)."', "
		."`status` = '".mysql_real_escape_string($this->status)."', "
		."`user_id` = '".mysql_real_escape_string($this->user_id)."', "
		."`silo_id` = '".mysql_real_escape_string($this->silo_id)."', "
		."`flag_id` = '".mysql_real_escape_string($this->flag_id)."', "
		."`active` = '".mysql_real_escape_string($this->active)."', "
		."`item_id` = '".mysql_real_escape_string($this->item_id)."', "
		."`hidden` = '".mysql_real_escape_string($this->hidden)."' "
		."WHERE "
		."`id` = '".mysql_real_escape_string($this->id)."'"
	);
	return Common::MysqlQuery($query);
}

private function Insert(){
	
	$query = 
		"INSERT INTO flag_radar "
		."(`who`,`status`,`cause`,`user_id`,`silo_id`,`flag_id`,`item_id`,`created`,`active`) "
		."VALUES "
		."("
			."'".mysql_real_escape_string($this->who)."',"
			."'".mysql_real_escape_string($this->status)."',"
			."'".mysql_real_escape_string($this->cause)."',"
			."'".mysql_real_escape_string($this->user_id)."',"
			."'".mysql_real_escape_string($this->silo_id)."',"
			."'".mysql_real_escape_string($this->flag_id)."',"
			."'".mysql_real_escape_string($this->item_id)."',"
			."'".mysql_real_escape_string(date("Y-m-d H:i:s"))."',"
			."'1'"
		.");"	
	;
	return Common::MysqlQuery($query,"id");
} //end insert

/**
 *
 *
 */
function CheckItemWarnedBeforeCancel($item_id,$flag_id){
	$query = 
		"select id "
		."from flag_radar "
		."where "
		."item_id = '".mysql_real_escape_string($item_id)."' "
		."and flag_id = '".mysql_real_escape_string($flag_id)."' "
		."and who = 'item' "
		."and created < '".mysql_real_escape_string(Common::DaysAgo(3))."' "
		."and active = '1' ORDER BY created DESC limit 1 "
	;
	$result = Common::MysqlQuery($query);
	if($result){
		$row = mysql_fetch_object($result);
		return $row->id;
	}else{ return false; }
} //CheckIfItemWarnedBeforeCancel

/**
 *
 *
 */
function CheckSiloWarnedBeforeCancel($silo_id,$flag_id){
	$query = 
		"select id "
		."from flag_radar "
		."where "
		."silo_id = '".mysql_real_escape_string($silo_id)."' "
		."and flag_id = '".mysql_real_escape_string($flag_id)."' "
		."and who = 'silo' "
		."and created < '".mysql_real_escape_string(Common::DaysAgo(3))."' "
		."and active = '1' ORDER BY created DESC limit 1 "
	;
	$result = Common::MysqlQuery($query);
	if($result){
		$row = mysql_fetch_object($result);
		return $row->id;
	}else{ return false; }
} //CheckIfSiloWarnedBeforeCancel

function GetId(){return $this->id;}
function UnsetId(){$this->id = false;}

}
?>
