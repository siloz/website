<?php
	$siloFlagged = mysql_num_rows(mysql_query("SELECT * FROM flag_silo WHERE user_id = '$user_id' AND silo_id = '$silo->silo_id'"));
/* a ui element for flagging
 *
 *
 *
 */
 if($_REQUEST["task"] === "view_item"){
 	$flag_box_type = "item";
 }elseif($_REQUEST["task"] === "view_silo" || "manage_silo"){
 	$flag_box_type = "silo";
 }

 $Vouch = new Vouch();
 $Flag = new Flag();
 $flag_ids = $Flag->GetIds();

?>

<div class="login" id="flagged_silo" style="width: 300px;">
	<h2>You have flagged this silo already. You can only flag each silo once.</h2>
</div>

<script type="text/javascript">
function submit_flag(flag_id){
	var url ="submit_flag.php?";
	var params = {
		        action: 'flag_<?php echo $flag_box_type ;?>',
		        silo_id: '<?php echo $silo->silo_id ;?>',
		        user_id: '<?php echo $user_id;?>',
		        flag_id: flag_id,
		        item_id: '<?php echo $item->item_id ;?>',
		        user_ip: '<?php echo $_SERVER["REMOTE_ADDR"];?>'
	    	};
	$("#flag_box_display").load(url,params);
}
function show_flag_box(){
	$("#flag_box").show();	
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
				if (!$flag_count) { $fCount = "no flags"; } elseif ($flag_count == 1) { $fCount = "1 flag"; } else { $fCount = $fCount = $flag_count." flags"; }
				if (!$tax_ded) { $tax = "<b><u>not</u></b>"; }
				$vouchTotal = $Vouch->GetHasPersonallyKnownCount($silo->silo_id) + $Vouch->GetHasResearchedCount($silo->silo_id);
				$pctV = $vouchTotal/($silo->getTotalMembers());
				$pctVouch = round($pctV * 100);
				$friend_count = $silo->getAdminFCount();
			?>
			<div class="voucherText<?=$closed_silo?>" style="font-size: 10pt"><?=$pctVouch?>% (<?=$vouchTotal?> members) of those who pledged items to this silo either know, or have researched, this Administrator - or both!</div>
			<div class="voucherText<?=$closed_silo?>" style="font-size: 10pt"><?php if ($friend_count) { echo "This Administrator is Facebook Connected with ".$friend_count." friends."; } else { echo "This Administrator is <b><u>not</u></b> Facebook Connected."; } ?></div>
			<?php }elseif($flag_box_type === "item"){ 
				$flag_count = $Flag->GetItemFlaggedCount($item->item_id);
			?>
			
			<?php } ?>
		</td>
		<td></td>
	</tr>
			<?php if($flag_box_type === "silo"){ ?>
	<tr>
		<td style="padding-top: 5px" width="50%" align="center">
			<span class="voucherText<?=$closed_silo?>">This silo has <?=$fCount?></span>
		</td>
	<?php if ($isAdmin) { ?>
		<td style="padding-top: 17px; padding-bottom: 13px" align="center">
			You're the admin.
	<?php } elseif ($siloFlagged) { ?>
		<td style="padding-top: 17px" align="center" class="click_me">
			<a class='fancybox' href='#flagged_silo'>
				<span class="voucherText<?=$closed_silo?>"><b>Silo flagged</b></font></span>
				<div class="floatR"><img height="20px" src="<?=ACTIVE_URL?>img/flag.png" alt="Flag this item" /></div>
			</a>
	<?php } elseif ($closed_silo) { ?>
		<td style="padding-top: 17px" align="center">
		<br>
	<?php } else { ?>
		<td style="padding-top: 17px" align="center" class="click_me">
			<a class='fancybox' href='#flag_box'>
				<span class="voucherText<?=$closed_silo?>"><b>Flag this silo</b></font></span>
				<div class="floatR"><img height="20px" src="<?=ACTIVE_URL?>img/flag.png" alt="Flag this item" /></div>
			</a>
	<?php } ?>

		</td>
	</tr>
<?php } ?>
</table>
<table class="flag_box" id="flag_box">
	<tr>
		<td id="flag_box_display">
				<img src="<?=ACTIVE_URL?>img/flag.png" />
				<h1>Reasons for Flagging this <?php echo ucfirst($flag_box_type); ?></h1>
				<ul>
					<?php foreach($flag_ids as $x){ 
						$Flag->Populate($x);
					?>
					<li><a onclick="submit_flag(<?php echo $Flag->id; ?>);" href="javascript: void(0);"><?php echo $Flag->type; ?></a></li>
					<?php } ?>
				</ul>
		</td>
	</tr>
</table>

