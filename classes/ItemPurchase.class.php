<?php

class ItemPurchase {

function __construct($id = false){
	if($id){return $this->Populate;}
}

function GetIds($value , $key=false){

}

function Populate($id){
	$query = (
		"select "
		."id,silo_id,item_id,trans_id,amount,paykey,status,ip,created,last_update "
		."from item_purchase "
		."where id = '".mysql_real_escape_string($id)."'"
	);
	$result = mysql_query($query);
	if(mysql_affected_rows() >= 1){
		$row = mysql_fetch_object($result);
		foreach($row as $key => $value){
			$this->$key = $value;
		}
		return $this;
	}else{
		return false;
	}
}

function Get($value){
	switch(strtolower($value)){
		case "": return ;
		default: return;
	}
}

function Set($key,$value){
	switch(strtolower($key)){
		case "": return ;
		default: return;
	}
}


function Save(){
	if(!$this->ip){
		$this->ip = Common::RemoteIp();
	}


	if($this->id){
		return $this->Update();
	}else{
		return $this->Insert();
	}
}

private function Update(){
	$exp = date('Y-m-d H:i:s', strtotime('+1 day'));

	$query = (
		"update item_purchase "
		."set "
		."silo_id = '".mysql_real_escape_string($this->silo_id)."',"
		."item_id = '".mysql_real_escape_string($this->item_id)."',"
		."user_id = '".mysql_real_escape_string($this->user_id)."',"
		."trans_id = '".mysql_real_escape_string($this->trans_id)."',"
		."amount = '".mysql_real_escape_string($this->amount)."',"
		."paylock = '".mysql_real_escape_string($this->paylock)."',"
		."paykey = '".mysql_real_escape_string($this->paykey)."',"
		."status = '".mysql_real_escape_string($this->status)."',"
		."ip = '".mysql_real_escape_string($this->ip)."',"
		."expired_date = '".mysql_real_escape_string($exp)."'"
		."where id = '".mysql_real_escape_string($this->id)."'" 
	);
	mysql_query($query);
	if(mysql_affected_rows() >=1){
		return $this->id;
	}else{
		return false;
	}
}


private function Insert(){
	$exp = date('Y-m-d H:i:s', strtotime('+7 day'));

	$query = (
		"insert into item_purchase "
		."(silo_id,item_id,user_id,trans_id,amount,paylock,paykey,status,ip,created,expired_date) "
		."values "
		."("
			."'".mysql_real_escape_string($this->silo_id)."',"
			."'".mysql_real_escape_string($this->item_id)."',"
			."'".mysql_real_escape_string($this->user_id)."',"
			."'".mysql_real_escape_string($this->trans_id)."',"
			."'".mysql_real_escape_string($this->amount)."',"
			."'".mysql_real_escape_string($this->paylock)."',"
			."'".mysql_real_escape_string($this->paykey)."',"
			."'".mysql_real_escape_string($this->status)."',"
			."'".mysql_real_escape_string($this->ip)."',"
			."'".mysql_real_escape_string(date("Y-m-d H:i:s"))."',"
			."'".mysql_real_escape_string($exp)."'"
		.")"
	);
	error_log($query);
	mysql_query($query);
	$id = mysql_insert_id();
	if(mysql_affected_rows() >= 1 ){
		$this->id = $id;
		return $id;
	}else{
		return false;
	}
	
}

}//end class

?>
