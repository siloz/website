<?php
require_once("include/autoload.class.php");
if($_REQUEST["action"] === "flag_silo"){
	$flag_action = "silo";
	$FlagSilo = new FlagSilo();
	$flagged = $FlagSilo->Insert($_REQUEST["user_id"],$_REQUEST["silo_id"],$_REQUEST["flag_id"],$_REQUEST["user_ip"]);
}elseif($_REQUEST["action"] === "flag_item"){
	$flag_action = "item";
	$FlagItem = new FlagItem();
	$flagged = $FlagItem->Insert($_REQUEST["item_id"],$_REQUEST["user_id"],$_REQUEST["flag_id"],$_REQUEST["user_ip"]);
} ?>
<center>
<?php if(!$flagged){ ?>
	<h1>Error</h1>
	<p>There was an error flagging this <?php echo $flag_action ;?> please try again later</p>
<?php }else{ ?>
	<h1 class="blue">Thank you for flagging this <?php echo $flag_action ;?></h1>
	<p>Your feed back is very important to us</p>  
<?php }
?>
<button style="font-size: 12px;" onclick="hide_flag_box();" type="button">Finish</button>
</center>
