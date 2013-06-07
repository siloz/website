<?php
	$user_id = param_get('id');
	$silo_id = param_get('silo_id');
	$user = new User($user_id);
	$silo_text = " in <b>all</b> Silos";	
	if ($silo_id != '') {
		$sql = "SELECT * FROM items INNER JOIN silos USING (silo_id) WHERE deleted_date = 0 AND user_id = $user_id AND silo_id = $silo_id ORDER BY item_id";
		$tmp = mysql_query("SELECT * FROM silos WHERE silo_id = $silo_id");
		$silo = mysql_fetch_array($tmp);
		$silo_text = " in <b><a href='index.php?task=view_silo&id=".$silo_id."'>".$silo['name']."</a></b> Silo";			
	}
	else {
		$sql = "SELECT * FROM items INNER JOIN silos USING (silo_id) WHERE deleted_date = 0 AND user_id = ".$user->user_id." ORDER BY item_id";		
	}
	$items = mysql_query($sql);
?>
<div class="heading">
	Items listed by <?php echo "<b><a href='index.php?task=view_user&id=".$user->id."'>".$user->username."</a></b>".$silo_text;?>
</div>
<?php
	$n = 0;
	echo "<table cellpadding='3px'>";			
	while ($row = mysql_fetch_array($items)) {
		if ($n == 0)
			echo "<tr>";
		$cell = "<td><div class=plate id='item_".$row['item_id']."' style='color: #000;height: 200px;'>";
		$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><a href='index.php?task=view_item&id=".$row['item_id']."'><b>".substr($row['title'], 0, 40)."</b></a></div><img height=100px width=135px src=uploads/items/300px/".$row['photo_file_1']." style='margin-bottom: 3px'><div style='color: #000;'><b>Status: </b>".$row['status']."<br/><b>Benefiting: </b><a href='index.php?task=view_silo&id=".$row['silo_id']."'>".substr($row['name'],0,30)."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$row['price']."</b></span></td><td align=right><a href='index.php?task=view_item&id=".$row['item_id']. "'><i><b>more...</b></i></a></td></tr></table></div></td>";							
		echo $cell;					
		$n++;
		if ($n == 6) {
			echo "</tr>";
			$n = 0;
		}					
	}
	echo "</table>";			
?>
