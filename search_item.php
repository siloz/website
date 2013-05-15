<?php
	include_once("include/GoogleAnalytics.php");

	$itemsPerPage = "18";
	$itemsPerRow = "6";

	$view = param_get('view');

	$search_clause = "(status = 'pledged' OR status = 'offer')";
	$saveSearch = "";

	if (strlen(trim(param_get('keywords'))) > 0) {
		$saveSearch .= "&keywords=".trim(param_get('keywords'));
		$kws = explode(" ", strtolower(param_get('keywords')));
		foreach ($kws as $kw) {
			if (strlen(trim($kw)) > 0)
			$search_clause .= " AND ( lower(title) LIKE '%$kw%' OR lower(description) LIKE '%$kw%' )";
		}
	}
	if (strlen(param_get('zip_code')) > 0) {
		$saveSearch .= "&zip_code=".param_get('zip_code');
		$search_clause .= "";
	}
	if (strlen($low) > 0) {
		$saveSearch .= "&price_low=".$low;
		$search_clause .= " AND price >= ".$low;
	}
	if (strlen($high) > 0) {
		$saveSearch .= "&price_high=".$high;
		$search_clause .= " AND price <= ".$high;
	}
	if (strlen(param_get('category')) > 0) {
		$saveSearch .= "&category=".param_get('category');		
		$search_clause .= " AND item_cat_id = ".param_get('category');
	}
	if (strlen(param_get('sort_by')) > 0) {
		$saveSearch .= "&sort_by=".param_get('sort_by');
		$saveSearch .= "&sort_order=".param_get('sort_order');				
	}
	if (strlen(param_get('view')) > 0) {
		$saveSearch .= "&view=map";
	}
	if (strlen(param_get('page')) > 0) {
		$saveSearch .= "&page=".param_get('page');;
	}
	
	$from = param_get('from') == '' ? 1 : intval(param_get('from'));
	$to = param_get('to') == '' ? $itemsPerPage : intval(param_get('to'));		
	$offset = $to - $from + 1;
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*), $sqlDist AS distance FROM items WHERE $search_clause HAVING distance <= 75"));
	$count_items = $tmp[0];	
	$count_silos = mysql_num_rows(mysql_query("SELECT *, $sqlDist AS distance FROM items WHERE $search_clause AND deleted_date = 0 HAVING distance <= 75"));

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

	if ($count_silos == "0") { $no_res = "<center>We cannot find any items in your area that match your search. Please broaden your search!</center>"; }

	$total_pages = ceil($count_items / $itemsPerPage);

	if (param_get('page')) {
		$page  = param_get('page');
	} 
	else { 
		$page = 1;
	};

	$start_from = ($page-1) * $itemsPerPage;

	$sql = "SELECT *, $sqlDist AS distance FROM items INNER JOIN item_categories USING (item_cat_id) WHERE $search_clause HAVING distance <= 75 $order_by_clause LIMIT $start_from, $itemsPerPage";
	$tmp = mysql_query($sql);
	
	$items_html = "<div class='row'><div class='span12'>";
	$i = 0;
	$items = array();
	
	while ($item = mysql_fetch_array($tmp)) {
		if ($i % $itemsPerRow == 0) {
			$items_html .= "<div class='row item_row-search'>";
		}
		
		$it = new Item($item['id']);
		$items[] = $it;
		
		$items_html .= $it->getItemPlate($i % $itemsPerRow == 0);

		if ($i % $itemsPerRow == $itemsPerRow - 1) {
			$items_html .= "</div>";
		}		

		$i++;
	}
	
	if ($i % $itemsPerRow < $itemsPerRow - 1) {
		$items_html .= "</div>";
	}

	if ($page == $total_pages) {
		$items_html .= "</div>";
	}

	if ($i == 5) {
		$items_html .= "</div>";
	}

	if ($i != 6) {
		$items_html .= "</div>";
	}

	if ($view != map) {
		$items_html .= "</div>";
	}
	
	$items_html .= "</div>";

	$user_id = $_SESSION["user_id"];
	$offerUser = mysql_fetch_array(mysql_query("SELECT status, amount FROM offers WHERE buyer_id = '$user_id' AND item_id = '$item->item_id'"));
	$offerStatus = $offerUser['status'];
	$offerAmount = $offerUser['amount'];

	if ($offerStatus == 'accepted') { $price = $offerAmount; } else { $price = $item->price; }
?>

<div class="navBreak">

<?php
	if ($total_pages == 1) {
		echo '<span class="nbSelected">1</span>';
	}
	elseif (!$total_pages) {}
	else	{
		if ($page != "1") {
			$prev = $page - 1;
				echo '<a href="index.php?'.$saveSearch.'&search=item&page='.$prev.'" class="nb">< Prev</a> <span class="navPad"></span>';
			}

		for ($i=1; $i<=$total_pages; $i++) {			

			if ($i != $page) {
				echo '<a href="index.php?'.$saveSearch.'&search=item&page='.$i.'" class="nb">' . $i . '</a> <span class="navPad"></span>';
			} 
			else {
				echo '<span class="nbSelected">'.$i.'</span> <span class="navPad"></span>';
			}
		};
		if ($page != $total_pages) {
			$next = $page + 1;
			echo '<a href="index.php?'.$saveSearch.'&search=item&page='.$next.'" class="nb">>Next</a>';
		}
	}

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
	$sortBy = "<b>sort: <a href=index.php?".$saveSearch."&search=item&sort_by=price$new_sort_order style='text-decoration:none;'> value <img src=$img1_path></a> &nbsp; <a href=index.php?".$saveSearch."&search=item&sort_by=date$new_sort_order style='text-decoration:none;'> date <img src=$img2_path></a></b>";
			?>

<div class="searchOpt">
<table width="100%">
<tr>
	<td style="padding-right: 15px;">
		<?=$sortBy?>
	</td>
	<td style="padding-right: 5px;">
		view:
	</td>
	<td>
		<a href="index.php?<?=$saveSearch?>&search=item&view=map"> map <input style="float: right; margin-top: 3px; margin-left: 5px;" type="radio" <?php if ($view) { echo "CHECKED"; } ?>></input></a>
	</td>
	<td>
		<a href="index.php?search=item<?=$saveSearch?>&view=">grid <input style="float: right; margin-top: 3px; margin-left: 5px;" type="radio" <?php if (!$view) { echo "CHECKED"; } ?>></input></a>
	</td>
</tr>
</table>
</div>

</div>

<div class="spacer"></div>

<?php
if ($view == "map") {
?>

<!-- <div id="result_nav" class="heading">
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
</div> -->

<div id='map_canvas' class="map-canvas" style='width: 930px; height: 400px; margin: 20px;'></div>

<br>

<?php
//Get items for map
$qry = mysql_query("SELECT * FROM items WHERE status = 'pledged' OR status = 'offer'");
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
		animation: google.maps.Animation.DROP,
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

<!--
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
-->

<?=$no_res?>	

<?php echo $items_html;?>

<div style="margin-left: 10px;">