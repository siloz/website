<?php
require_once("../include/autoload.class.php");
if($_REQUEST["action"] === "flag_silo"){
	$FlagSilo = new FlagSilo();
	$flagged = $FlagSilo->Insert($_REQUEST["user_id"],$_REQUEST["silo_id"],$_REQUEST["flag_id"],$_REQUEST["user_ip"]);
}elseif($_REQUEST["action"] === "flag_item"){
	$FlagItem = new FlagItem();
	$flagged = $FlagItem->Insert($_REQUEST["item_id"],$_REQUEST["user_id"],$_REQUEST["flag_id"],$_REQUEST["user_ip"]);
}
if(!$flagged){
	echo "error";
}else{echo "success";}
?>
