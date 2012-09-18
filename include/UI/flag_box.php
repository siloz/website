<?php
/* a ui element for flagging
 *
 *
 *
 */
 if($_REQUEST["task"] === "view_item"){
 	$flag_box_type = "item";
 }elseif($_REQUEST["task"] === "view_silo"){
 	$flag_box_type = "silo";
 }
 $Vouch = new Vouch();
 $Flag = new Flag();
 $flag_ids = $Flag->GetIds();
?>
<script type="text/javascript">
function submit_flag(flag_id){
	var url ="submit_flag.php?";
	var params = {
		        action: 'flag_<?php echo $flag_box_type ;?>',
		        silo_id: '<?php echo $silo->silo_id ;?>',
		        user_id: '<?php echo $current_user["user_id"];?>',
		        flag_id: flag_id,
		        item_id: '<?php echo $item->item_id ;?>',
		        user_ip: '<?php echo $_SERVER["REMOTE_ADDR"];?>'
	    	};
	$("#flag_box_display").load(url,params);
}
function show_flag_box(){
	$("#flag_box").show();	
}
function hide_flag_box(){
	$('#flag_box').hide();
	popup_exit('flag_box', 'login_drag', 'login_exit', 'screen-center', 0, 0);

}
</script>
<style type="text/css">
	#silo_security_text .blue{
		font-weight:700;
		color:#2F8DCB;
		margin:0;
	}
	#silo_security_text .flag_text{
		text-align:center;					
	}
	#silo_security_text .flag_text h2{
		margin: 0 0 3px 0;
						
	}
	#silo_security_text .flag_text img{
		float:right;
		height:30px;			
	}
	#silo_security_text tr.click_me:hover{
		cursor:pointer;
	}
	/* pop up form */
	#flag_box {
		background-color: #fff;
		display:none; 
		z-index: 5;
		-moz-border-radius: 10px;
		-webkit-border-radius: 10px;
		-khtml-border-radius: 10px;
		border-radius: 10px;     
		border-width: 0px;    
		padding: 20px;
	}
	#flag_box img {
		float:right;
	}
	#flag_box h1{
		color:#787576;
		font-size:18px;
		text-align:center;
	}
	#flag_box ul{
		list-style-type: none;
	}
	#flag_box li a{
		color:#2F8DCB;
		text-decoration:none;
		text-align:center;
		font-weight:700;
	}
</style>
<table id="silo_security_text">
	<tr>
		<td colspan="99">
			
			<?php if($flag_box_type === "silo"){ 
				$flag_count = $Flag->GetSiloFlaggedCount($silo->silo_id);
			?>
			<span class="blue">Security:</span> Pledge or donate only to those silos whose administrator and whose organization or cause you have either personal knowledge of, or have researched.<span class="blue"><?php echo $Vouch->GetHasPersonallyKnownCount($silo->silo_id);?> members</span> of this silo personally know this organization or cause and its silo administrator, <span class="blue"><?php echo $Vouch->GetHasResearchedCount($silo->silo_id);?> members</span> of this silo have researched this organization or cause and its silo administrator, and <span class="blue"><?php echo $Vouch->GetHasResearchedAndKnownCount($silo->silo_id);?> members</span> of this silo have researched, AND have personal knowledge of same.
			<?php }elseif($flag_box_type === "item"){ 
				$flag_count = $Flag->GetItemFlaggedCount($item->item_id);
			?>
			
			<?php } ?>
		</td>
		<td></td>
	</tr>
	<tr class="click_me" onclick="javascript:popup_show('flag_box', 'login_drag', 'login_exit', 'screen-center', 0, 0);">
		<td class="flag_text">
			<h2 class="blue">Flag this <?php echo ucfirst($flag_box_type); ?></h2>
			<p class="blue">This <?php echo ucfirst($flag_box_type); ?> has <?php echo $flag_count ?> flags</p>
		</td>
		<td>
			<img height="40px" width="auto" src="img/flag.png" alt="Flag this item" />
			
		</td>
	</tr>
</table>
<table class="flag_box" id="flag_box">
	<tr>
		<td id="flag_box_display">
			<div id="login_drag" style="float:right">
				<img onclick="hide_flag_box();" id="login_exit" src="images/close.png"/>
			</div>
			<?php if($current_user["user_id"]){ ?>
				<img  src="img/flag.png" />
				<h1>Reasons for Flagging this <?php echo ucfirst($flag_box_type); ?></h1>
				<ul>
					<?php foreach($flag_ids as $x){ 
						$Flag->Populate($x);
					?>
					<li><a onclick="submit_flag(<?php echo $Flag->id; ?>);" href="javascript: void(0);"><?php echo $Flag->type; ?></a></li>
					<?php } ?>
				</ul>
			<?php }else{ ?>
				<h1 class="blue">Sorry you must be logged in to flag items</h1>
			<?php }?>
		</td>
	</tr>
</table>

