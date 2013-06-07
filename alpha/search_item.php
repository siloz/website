<?php
	$search_clause = "";
	$query = "";

	if (strlen(trim(param_get('keywords'))) > 0) {
		$query .= "&keywords=".trim(param_get('keywords'));
		$kws = explode(" ", strtolower(param_get('keywords')));
		foreach ($kws as $kw) {
			if (strlen(trim($kw)) > 0)
			$search_clause .= " AND ( lower(title) LIKE '%$kw%' OR lower(description) LIKE '%$kw%' )";
		}
	}
	if (strlen(param_get('zip_code')) > 0) {
		$query .= "&zip_code=".param_get('zip_code');
		$search_clause .= "";
	}
	if (strlen(param_get('amount_min')) > 0) {
		$query .= "&amount_min=".param_get('amount_min');
		$search_clause .= " AND price >= ".param_get('amount_min');
	}
	if (strlen(param_get('amount_max')) > 0) {
		$query .= "&amount_max=".param_get('amount_max');
		$search_clause .= " AND price <= ".param_get('amount_max');
	}
	if (strlen(param_get('category')) > 0) {
		$query .= "&category=".param_get('category');		
		$search_clause .= " AND item_cat_id = ".param_get('category');
	}

	$from =	param_get('from') == '' ? 1 : intval(param_get('from'));;
	$to = param_get('to') == '' ? 12 : intval(param_get('to'));		
	$offset = $to - $from + 1;
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 $search_clause"));
	$count_items = $tmp[0];	
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(DISTINCT silo_id) FROM items WHERE deleted_date = 0 $search_clause"));
	$count_silos = $tmp[0];
	$sql = "SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE deleted_date = 0 $search_clause ORDER BY id DESC LIMIT ".($from-1).", $offset";
	$tmp = mysql_query($sql);
	$items_html = "<table cellpadding='3px'><tr>";
	$i = 0;
	$items = array();
	while ($item = mysql_fetch_array($tmp)) {
		$i ++;				
		if ($i % 4 == 1 && $i > 1) {
			$items_html .= "</tr><tr>";
		}
		else if ($i % 4 == 1) {
			$items_html .= "<tr>";
		}
		$it = new Item($item['id']);
		$items[] = $it;
		
		$items_html .= "<td>".$it->getPlate()."</td>";				
	}	
	$items_html .= "</tr></table></div>";
	
	$prev = "";
	if ($from >= 13)
		$prev = "<a href=index.php?search=item&from=".($from-12)."&to=".($from-1)."$query class=status><img src='images/prev_arrow.png' height='15px'></a>";
	$next = "";
	if ($to < $count_items)
	 	$next = "<a href=index.php?search=item&from=".($to+1)."&to=".min($to + 12, $count_items)."$query class=status><img src='images/next_arrow.png' height='15px'></a>";
?>
<table>
	<tr>
		<td valign='top' width="610px">
			<div id="result_nav" style="width: 610px;" class="heading">
				<table width='610px'><tr><td width='120px' align='left'><?php echo $prev;?></td><td width='370px' align='center'><b>Viewing <?php echo $from;?> to <?php echo min($to, $count_items)." of $count_items items pledged to help $count_silos silos in your area";?></b></td><td width='120px' align=right><?php echo $next?></td></tr></table>
			</div>
			<div id="items" style="width: 460px;"><?php echo $items_html;?></div>			
		</td>
		<td valign='top' width="10px">
		</td>
		<td valign='top' width="340px" align="center">
			<div style="width:340px" class="heading">
				Hover over items to highlight. Detail of highlighted silo...
			</div>			
			<div class="silo_preview" id="silo_preview"></div>
			<div style="color: #000; padding-top: 5px; font-weight: bold;">Silo location</div>
			<div id="map_canvas" style="width: 340px; height: 170px;"></div>			
		</td>
	</tr>
</table>

<script  type="text/javascript">
		silo_previews = {};
		positions = {};
		items = new Array();
		function unhighlight_item(id) {
		}
		function highlight_item(id) {
			for (var i=0; i<items.length;i++) {
				document.getElementById("item_"+items[i]).style.background = "#E6F2FB";				
				document.getElementById("item_"+items[i]).style.borderColor = "#E6F2FB";				
			}
			document.getElementById("item_"+id).style.background = "#fff";			
			document.getElementById("item_"+id).style.borderColor = "#84bfe5";				
			document.getElementById("silo_preview").innerHTML = silo_previews[id];
			var myOptions = {
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					disableDefaultUI: true,
			        navigationControl: true,
			        navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
			        draggable: true,
			        scaleControl: false,
					scrollwheel: true
					};
			var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);		    
			var bounds = new google.maps.LatLngBounds();
			
			pos = positions[id];
			bounds.extend(pos);

	       	var item_marker = new google.maps.Marker({
	           	map: map,
				animation: google.maps.Animation.DROP,
				icon: 'images/orange_circle.png',
	           	position: pos
	       	});
		 	google.maps.event.addListener(item_marker, 'click', (function(marker, id) {
		        return function() {
		        }
		      })(item_marker, id));								

			map.fitBounds(bounds);
			zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
			            if (this.getZoom() > 14) // Change max/min zoom here
			                this.setZoom(14);	
			});
			
		    // google.maps.event.trigger(item_marker,"click");
		    item_marker.setAnimation(google.maps.Animation.BOUNCE);				
		}
</script>
<?php
	foreach ($items as $item) {
?>		
<script>		
		id = '<?php echo $item->id;?>';
		items.push(id);
		longitude = <?php echo $item->silo->longitude;?>;
		latitude = <?php echo $item->silo->latitude;?>;

		silo_previews[id] = "<?php echo $item->getSiloPreview();?>";		
		pos = new google.maps.LatLng(longitude, latitude);
		positions[id] = pos;
</script>		
<?php
	}
?>
<script>highlight_item('<?php echo $items[0]->id;?>'); </script>
