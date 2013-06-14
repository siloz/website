<?php 
if ($silo->silo_cat_id == "99") { $disaster = "true"; }
if ($silo->issue_receipts == 1) { $tax_ded = "true"; }

if ($silo->silo_type == "private" && !$showDiv) { ?>

<table class='siloInfo<?=$closed_silo?>' style="height: 730px">
	<tr>
		<td class="titleHeading">
			This silo is private. <br><br> You must be invited to this silo in order to pledge items towards it. Private silos are hidden from the general public.
		</td>
	</tr>
</table>

<?php } else { ?>

<table class='siloInfo<?=$closed_silo?>' style="height: 600px">

<?php if (param_get('task') == "view_item") { ?>
	<tr>
		<td class="titleHeading">
			<?=$silo->getShortTitle(35); ?>
		</td>
	</tr>
<?php } ?>

	<tr class="infoSpacer"></tr>
	<tr>
		<td>
					<?php
						$admin = $silo->getAdmin();
						$admin_name = $admin->fname;
						$collected = $silo->getCollectedAmount();
						$pct = round($collected*100.0/floatval($silo->goal));
						if ($pct == 100) { $radius = "border-radius: 4px;"; } else { $radius = "border-top-left-radius: 4px; border-bottom-left-radius: 4px"; }
						
						$c_user_id = $current_user['user_id'];
					?>
			<a href='silos/<?=$silo->shortname?>'><img src="<?php echo ACTIVE_URL.'uploads/silos/'.$silo->photo_file.'?'.$silo->last_update;?>" width='250px' class="siloImg"/>
			<div class="siloImgOverlay">
			<div class="progress-bg"><div class="progress-bar" style="width: <?=$pct?>%; <?=$radius?>"></div></div>
			goal: $<?=number_format($silo->goal)?> (<?=$pct?>%)
			</div></a>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo<?=$closed_silo?>">
			<a href='index.php?task=view_silo&view=members&id=<?=$silo->id;?>'><?=$silo->getTotalMembers();?></a>
			<a href='index.php?task=view_silo&view=items&id=<?=$silo->id;?>'><?=$silo->getTotalItems();?></a>
			<?=$silo->getDaysLeft();?>
			<div style="padding-top: 10px;"></div>
		<?php if (!$tax_ded) { $tax = "<b><u>not</u></b>"; } ?>
		<?php if ($silo->silo_type == "public") { ?>
			<div class="voucherText<?=$closed_silo?>" style="font-size: 10pt; text-align: left"><b>Organization purpose: <?=$silo->org_purpose?></div></b>
		<?php } ?>
			<div class="voucherText<?=$closed_silo?>" style="font-size: 10pt; text-align: left"><b>Silo purpose: <?=$silo->silo_purpose?></div></b>
			<div class="voucherText<?=$closed_silo?>" style="font-size: 10pt; text-align: left"><b>This Administrator has <?=$tax?> provided an EIN number for this fundraiser, and donations are <?=$tax?> tax-deductable.</div></b>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo<?=$closed_silo?>">
			<?php if ($silo->silo_type == "public") { ?>
				Organization name: <?=$silo->org_name?> <br> <br>
			<?php } else { ?>
				Relationship to beneficiary: <?=$silo->title?> <br><br>
			<?php } ?>
			<span class="floatL">
				<img src="<?php echo ACTIVE_URL.'uploads/members/'.$admin->photo_file.'?'.$admin->last_update;?>" class="siloImg" width='100px'/><br>
				<a style="color: #2f8dcb;" class='buttonEmail' href="<?php if($closed_silo) { echo "javascript:popup_show('closed_silo', 'closed_silo_drag', 'closed_silo_exit', 'screen-center', 0, 0);"; } else { echo "javascript:popup_show('contact_admin', 'contact_admin_drag', 'contact_admin_exit', 'screen-center', 0, 0);"; }?>">Email Admin.</a>
			</span>
			<div align="left">
			<span class="infoDetails">
				Administrator:<br>
				<?=$admin_name?><br>
				Official Address:<br>
				<?=$silo->address?><br>
				Telephone:<br>
				<?=$silo->phone_number?>
			</span>
			</div>
		</td>
	</tr>
	<tr class="infoSpacer"></tr>
	<tr>
		<td class="siloInnerInfo<?=$closed_silo?>">
			<div align="left">
		<span class='voucher'>
		<?php if ($disaster) { ?>
			This silo was sanctioned by the benefiting organization. The amount raised (shown on this page), represents 90% of actual funds raised. Of the 10% not represented in the status bar, roughly 7.5% goes to <?=SHORT_URL?>, and rougly 2.5% goes to our payment gateway.</span><br><br>
		<?php } else { ?>
			Donate only to local causes that you know or have researched!</span><br><br>
		<?php include('include/UI/flag_box_silo.php'); } ?>
			<center>Silo ID: <?=$silo->id?></center>
		</div>
		</td>
	</tr>
</table>

<?php } ?>