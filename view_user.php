<?php
	$itemsPerPage = "12";
	$itemsPerRow = "6";

	$view_user_id = param_get('id');
	$silo_id = param_get('silo_id');
	$user = new User($view_user_id);

	$count = "SELECT COUNT(*) AS num FROM items WHERE user_id = ".$user->user_id." AND (items.status = 'pledged' OR items.status = 'offer')";
	$countRow = mysql_fetch_array(mysql_query($count));
	$total_records = $countRow['num'];
	$total_pages = ceil($total_records / $itemsPerPage);

	if (param_get('page')) { $page  = param_get('page'); } else { $page = 1; };
	$start_from = ($page-1) * $itemsPerPage;

	if ($silo_id != '') {
		$sql = "SELECT * FROM items INNER JOIN silos USING (silo_id) WHERE user_id = ".$user->user_id." AND (items.status = 'pledged' OR items.status = 'offer') ORDER BY item_id LIMIT $start_from, $itemsPerPage";		
		$tmp = mysql_query("SELECT * FROM silos WHERE silo_id = $silo_id");
		$silo = mysql_fetch_array($tmp);
		$silo_text = " in <b><a href='index.php?task=view_silo&id=".$silo_id."'>".$silo['name']."</a></b> Silo";			
	}
	else {
		$sql = "SELECT * FROM items INNER JOIN silos USING (silo_id) WHERE user_id = ".$user->user_id." AND (items.status = 'pledged' OR items.status = 'offer') ORDER BY item_id LIMIT $start_from, $itemsPerPage";		
	}
	$items = mysql_query($sql);
?>

<div class="headingPad"></div>

<div class="userNav" align="center">
	<span class="accountHeading">Items listed by <?php echo "<b><a href='index.php?task=view_user&id=".$user->id."'><u>".$user->fname."</u></a></b>";?></span><br>
	<img src="uploads/members/<?=$view_user_id?>.jpg" width="100px" class="user-img">
</div>

<div class="headingPad"></div><br>

	<center>
		<?php
			if ($total_pages == 1) {
				echo '<span class="nb_siloSelected">1</span>';
			}
			elseif (!$total_pages) {}
			else	{
				if ($page != "1") {
					$prev = $page - 1;
						echo '<a href="index.php?task=view_user&id='.$user->id.'&page='.$prev.'" class="nb_silo">< Prev</a> <span class="navPad"></span>';
					}

				for ($i=1; $i<=$total_pages; $i++) {			

					if ($i != $page) {
						echo '<a href="index.php?task=view_user&id='.$user->id.'&page='.$i.'" class="nb_silo">' . $i . '</a> <span class="navPad"></span>';
					} 
					else {
						echo '<span class="nb_siloSelected">'.$i.'</span> <span class="navPad"></span>';
					}
				};
				if ($page != $total_pages) {
					$next = $page + 1;
					echo '<a href="index.php?task=view_user&id='.$user->id.'&page='.$next.'" class="nb_silo">>Next</a>';
				}
			}
		?>
	</center>

<div class="headingPad"></div>

<?php
	$num = mysql_num_rows($items);

	if (!$num) {
    		echo "<br><br><center>This user does not have any active listings right now.</center>";
  	}

		$n = 0;
		echo "<table cellpadding='5px'>";			
		while ($item = mysql_fetch_array($items)) {
			$i = new Item($item['item_id']);
			if ($n == 0)
				echo "<tr>";							
				echo $i->getItemCell($item['silo_id'], $user_id);					
				$n++;
			if ($n == $itemsPerRow) {
				echo "</tr>";
				$n = 0;
			}					
		}
		echo "</table>";
?>
