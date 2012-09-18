<?php
	$search_clause = "";
	$query = "";
	
	if (strlen(trim(param_get('keywords'))) > 0) {
		$query .= "&keywords=".trim(param_get('keywords'));
		$kws = explode(" ", strtolower(param_get('keywords')));
		foreach ($kws as $kw) {
			if (strlen(trim($kw)) > 0)
			$search_clause .= " AND ( lower(name) LIKE '%$kw%' OR lower(description) LIKE '%$kw%' )";
		}
	}
	if (strlen(param_get('zip_code')) > 0) {
		$query .= "&zip_code=".param_get('zip_code');
		$search_clause .= "";
	}
	if (strlen(param_get('amount_min')) > 0) {
		$query .= "&amount_min=".param_get('amount_min');
		$search_clause .= " AND goal >= ".param_get('amount_min');
	}
	if (strlen(param_get('amount_max')) > 0) {
		$query .= "&amount_max=".param_get('amount_max');
		$search_clause .= " AND goal <= ".param_get('amount_max');
	}
	if (strlen(param_get('category')) > 0) {
		$query .= "&category=".param_get('category');		
		$search_clause .= " AND silo_cat_id = ".param_get('category');
	}
	else {
		$search_clause .= " AND silo_cat_id IN (SELECT silo_cat_id FROM silo_categories WHERE type='Community')";		
	}
	$search_clause .= " AND end_date = '0000-00-00 00:00:00' ";
	$from =	param_get('from') == '' ? 1 : intval(param_get('from'));;
	$to = param_get('to') == '' ? 12 : intval(param_get('to'));		
	$offset = $to - $from + 1;

	$sql = "SELECT COUNT(*) FROM silos WHERE 1 > 0 $search_clause";
	$tmp = mysql_fetch_array(mysql_query($sql));
	$count_silos = $tmp[0];	

	$sql = "SELECT * FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE 1 > 0 $search_clause ORDER BY silo_id DESC LIMIT ".($from-1).", $offset";
	
	$tmp = mysql_query($sql);		
	$closed_silos = array();
	
	$siloz_html = "<table cellpadding='3px' style='border-spacing: 0px'><tr>";
	$i = 0;
	
	while ($s = mysql_fetch_array($tmp)) {	
		$i ++;
		$silo = new Silo($s['id']);
		if ($i % 4 == 1 && $i > 1) {
			$siloz_html .= "</tr><tr>";
		}
		else if ($i % 4 == 1) {
			$siloz_html .= "<tr>";
		}
		$siloz_html .= "<td>";				
		$siloz_html .= $silo->getPlate();
		$siloz_html .= "</td>";
		$closed_silos[] = $silo;		
	}
	$siloz_html .= "</tr></table></div>";
	$prev = "";
	if ($from >= 13)
		$prev = "<a href=index.php?search=silo&from=".($from-12)."&to=".($from-1)."$query class=status><img src='images/prev_arrow.png' height='15px'></a>";
	$next = "";
	if ($to < $count_silos)
	 	$next = "<a href=index.php?search=silo&from=".($to+1)."&to=".min($to + 12, $count_silos)."$query class=status><img src='images/next_arrow.png' height='15px'></a>";	
?>

<table>
	<tr>
		<td valign='top' width="610px">
			<div id="result_nav" style="width: 610px;" class="heading">
				<table width='100%'><tr><td width='20%' align='left'><?php echo $prev;?></td><td width='60%' align='center'><b>Viewing <?php echo $from;?> to <?php echo min($to, $count_silos)." of $count_silos silos in your area...";?></b></td><td width='20%' align=right><?php echo $next?></td></tr></table>				
			</div>
			<div id="silos" style="width: 600;"><?php echo $siloz_html;?></div>			
		</td>
		<td valign='top' width="10px">
		</td>		
		<td valign='top' width="340px">
			<div style="width: 340px; text-align:center" class="heading">
				Detail of highlighted silo...
			</div>			
			<div class="silo_preview" id="silo_preview"></div>
			<div style="color: #000; padding-top: 5px; font-weight: bold; text-align:center">Silo location</div>
			<div id="map_canvas" style="width: 340px; height: 170px;"></div>			
		</td>
	</tr>
</table>


<script  type="text/javascript">
		silo_previews = {};
		positions = {};
		silos = new Array();
		function unhighlight_silo(id) {			
		}
		function highlight_silo(id) {
			for (var i=0; i<silos.length;i++) {
				document.getElementById("silo_"+silos[i]).style.background = "#E6F2FB";				
				document.getElementById("silo_"+silos[i]).style.borderColor = "#E6F2FB";				
			}
			document.getElementById("silo_"+id).style.background = "#fff";			
			document.getElementById("silo_"+id).style.borderColor = "#84bfe5";			
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

	       	var silo_marker = new google.maps.Marker({
	           	map: map,
				animation: google.maps.Animation.DROP,
				icon: 'images/orange_circle.png',
	           	position: pos
	       	});
		 	google.maps.event.addListener(silo_marker, 'click', (function(marker, id) {
		        return function() {
		        }
		      })(silo_marker, id));								

			map.fitBounds(bounds);
			zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
			            if (this.getZoom() > 14) // Change max/min zoom here
			                this.setZoom(14);	
			});
			
		    silo_marker.setAnimation(google.maps.Animation.BOUNCE);				
		}
</script>
<?php
	foreach ($closed_silos as $silo) {
?>		
<script>		
		id = <?php echo "'".$silo->id."'";?>;
		silos.push(id);
		longitude = <?php echo $silo->longitude;?>;
		latitude = <?php echo $silo->latitude;?>;

		silo_previews[id] = "<?php echo $silo->getPreview();?>";		
		pos = new google.maps.LatLng(longitude, latitude);
		positions[id] = pos;	
</script>		
<?php
	}
?>
<script>highlight_silo(<?php echo "'".$closed_silos[0]->id."'";?>)</script>
