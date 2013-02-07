<?php
	$itemsPerPage = "12";
	$itemsPerRow = "6";

	$view = param_get('view');

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
	$search_clause .= " AND status = 'pledged' ";
	
	$from = param_get('from') == '' ? 1 : intval(param_get('from'));
	$to = param_get('to') == '' ? $itemsPerPage : intval(param_get('to'));		
	$offset = $to - $from + 1;
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM items WHERE deleted_date = 0 $search_clause"));
	$count_items = $tmp[0];	
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(DISTINCT silo_id) FROM items WHERE deleted_date = 0 $search_clause"));
	$count_silos = $tmp[0];

	$new_sort_order = '';
	$sort_order = param_get('sort_order');
	$img1_path = 'images/none.png';
	$img2_path = 'images/none.png';
	$order_by = param_get('sort_by');
	$order_by_clause = "";
	if ($order_by == 'price')
		$order_by_clause = " ORDER BY price $sort_order ";
	elseif ($order_by == 'date')
		$order_by_clause = " ORDER BY added_date $sort_order ";
	else {
		$order_by_clause = " ORDER BY distance ";
	}

	$sql = "SELECT *, $sqlDist AS distance FROM items INNER JOIN item_categories USING (item_cat_id) WHERE deleted_date = 0 $search_clause $order_by_clause LIMIT ".($from-1).", $offset";
	$tmp = mysql_query($sql);
	$items_html = "<table cellpadding='6px'><tr>";
	$i = 0;
	$items = array();
	while ($item = mysql_fetch_array($tmp)) {
		$i ++;				
		if ($i % $itemsPerRow == 1 && $i > 1) {
			$items_html .= "</tr><tr>";
		}
		else if ($i % $itemsPerRow == 1) {
			$items_html .= "<tr>";
		}
		$it = new Item($item['id']);
		$items[] = $it;
		
		$items_html .= "<td>".$it->getPlate()."</td>";				
	}	
	$items_html .= "</tr></table></div>";

	$prev = "";
	if ($from >= $itemsPerPage)
		$prev = "<a href=index.php?search=item&from=".($from-$itemsPerPage)."&to=".($from-1)."$query class=status><img src='images/prev_arrow.png' height='15px'> <font color='#fff'>Prev</font></a> &nbsp;&nbsp;";
	$next = "";
	if ($to < $count_items)
	 	$next = "<a href=index.php?search=item&from=".($to+1)."&to=".min($to + $itemsPerPage, $count_items)."$query class=status><font color='#fff'>Next</font> <img src='images/next_arrow.png' height='15px'></a>"
?>

<?php
if ($view == "map") {
?>

<div id="result_nav" class="heading">
<table width="100%">
<tr>
	<td width="67%" align="right" valign="top">
		<b>This map is showing <?=$count_items?> items pledged to help <?$count_silos?> silos in your area</b>
	</td>
	<td align="right">
		<i>View:</i> <a href="index.php?search=item&view=map">Map</a> | <a href="index.php?search=item" style="text-decoration:none;">Grid</a>
	</td>
</tr>
</table>
</div>

<div id='map_canvas' style='width: 930px; height: 400px; margin: 20px;'></div>

<br>

<?php
//Get items for map
$qry = mysql_query("SELECT * FROM items");
$num = mysql_num_rows($qry);

    echo "<script> var locations = [";

        while ($map = mysql_fetch_array($qry)){

        echo "['" . $map['title'] . "', " . $map['latitude'] . ", " . $map['longitude'] . "],";

        }

    echo " ];</script>";

?>
</div>

<script  type="text/javascript">

function initialize() {
var styles = [
	{
		featureType: 'water',
		elementType: 'all',
		stylers: [
			{ hue: '#84BFE5' },
			{ saturation: 37 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'landscape.man_made',
		elementType: 'all',
		stylers: [
			{ hue: '#FFFFFF' },
			{ saturation: -100 },
			{ lightness: 100 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.highway',
		elementType: 'all',
		stylers: [
			{ hue: '#FFC92F' },
			{ saturation: 100 },
			{ lightness: -7 },
			{ visibility: 'on' }
		]
	},{
		featureType: 'road.arterial',
		elementType: 'all',
		stylers: [
			{ hue: '#FFE18C' },
			{ saturation: 100 },
			{ lightness: 2 },
			{ visibility: 'on' }
		]
	}
];

siloLat = 33.761272;
siloLong = -84.390556;

var siloLocation = new google.maps.LatLng(siloLat, siloLong);
var options = {
	mapTypeControlOptions: {
		mapTypeIds: [ 'Styled']
	},
	center: siloLocation,
	zoom: 3,
	maxZoom: 13,
	mapTypeId: 'Styled'
};

var div = document.getElementById('map_canvas');
var map = new google.maps.Map(div, options);
var styledMapType = new google.maps.StyledMapType(styles, { name: 'Item Map' });
map.mapTypes.set('Styled', styledMapType);

    var marker, i;

    for (i = 0; i < locations.length; i++) {  
            	marker = new google.maps.Marker({
            	position: new google.maps.LatLng(locations[i][1], locations[i][2]),
            	map: map,
		animation: google.maps.Animation.DROP
            });
}

infoWindow.open(map);
}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<?php
}
?>

<div id="result_nav" class="heading">
	<table width="100%" valign=”top”>
	<tr>
	<td width="10%">
	<?=$prev?> <?=$next?>
	</td>
	<td width="50%" align="right" valign="top">
		<b>Viewing <?php echo $from;?> to <?php echo min($to, $count_items)." of $count_items items pledged to help $count_silos silos in your area";?></b>
	</td>
	<td align="right">
		<?php
				if ($sort_order == 'asc') {
					$new_sort_order = '&sort_order=desc';
					if ($order_by == 'date')
						$img2_path = 'images/up.png';
					else if ($order_by != '')
						$img1_path = 'images/up.png';
				}
				else {
					$new_sort_order = '&sort_order=asc';										
					if ($order_by == 'date')
						$img2_path = 'images/down.png';
					else if ($order_by != '')
						$img1_path = 'images/down.png';
				}
					echo "<b>sort by <a href=index.php?search=item&sort_by=price$new_sort_order style='text-decoration:none;'> price <img src=$img1_path></a> or <a href=index.php?search=item&sort_by=date$new_sort_order style='text-decoration:none;'> list date <img src=$img2_path></a></b>";
			?> &nbsp; &nbsp;
		<i>View:</i> <a href="index.php?search=item&view=map" style="text-decoration:none;">Map</a> | <a href="index.php?search=item">Grid</a>
	</td>
	</tr>
			</table>
			</div>
<table>
	<tr>
		<td valign='top' width="800px">
			<div id="items" style="width: 800;"><?php echo $items_html;?></div>
	</tr>
</table>