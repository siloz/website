<?php
echo "<h1>ENTER ADMIN</h1>";
/*
 * this script gives the admin the ability to view all items
 *	@todo sold
 *	@todo ended
 *	@todo flagged
 *	@todo cancelled
 *
 */
$item_ids = Item::GetIds($silo_id,"silo","overide");
$n = 0;
?>
<table cellpadding='3px'>
<?php			
	foreach ($item_ids as $item_id) { 
	$Item = new Item($item_id);
	$User = new User($Item->user_id);
	$id = $Item->id;
	$title = substr($Item->title , 0 , 40);
	if($n === 0){ ?> <tr> <?php } ?>
			<td>
				<div class=plate id='item_id-id' style='color: #000;'>							
					<table width=100% height=100%>
						<tr valign=top>
							<td valign=top colspan=2>
								<div style='height: 30px'>
									<a href='index.php?task=view_item&id=item_<?= $id ?>'>
										<b><?= $title ?></b>
									</a>
								</div>
								<img height=100px width=135px src="uploads/items/300px/<?= $Item->photo_file_1 ;?>" style='margin-bottom: 3px'>
								<div style='color: #000;line-height: 120%;'>
									<b>Status: </b><?=$Item->status ?>
									<br/>
									<b>Member: </b>
									<a href='index.php?task=view_user&id=<?= $User->id ;?>'><?= $User->username ?></a>
								</div>
							</td>
						</tr>
						<tr valign=bottom>
							<td align=left align=left>
								<span style='color: #f60'><b>$<?= $Item->price ;?></b></span>
							</td>
							<td align=right>
								<a href='index.php?task=view_item&id=<?= $Item->id ;?>'>
									<i><b>more...</b></i>
								</a>
							</td>
						</tr>
					</table>
				</div>
			</td>			
		
		<?php $n++;
		if ($n == 6) { $n = 0;
		?>
		</tr>	
		<?php }					
	}
?>
</table>

