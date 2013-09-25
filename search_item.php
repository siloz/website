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
	$tmp = mysql_fetch_array(mysql_query("SELECT COUNT(*), $sqlDist AS distance FROM items WHERE $search_clause"));
	$count_items = $tmp[0];	
	$count_silos = mysql_num_rows(mysql_query("SELECT *, $sqlDist AS distance FROM items WHERE $search_clause AND deleted_date = 0"));

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

	$sql = "SELECT *, $sqlDist AS distance FROM items INNER JOIN item_categories USING (item_cat_id) WHERE $search_clause $order_by_clause LIMIT $start_from, $itemsPerPage";
	$tmp = mysql_query($sql);
	
	$items_html = "<div class='row_search'><div class='span12'><table>";
	$i = 0;
	$items = array();

	while ($item = mysql_fetch_array($tmp)) {
		$i++;				
		if ($i % $itemsPerRow == 1 && $i > 1) {
			$items_html .= "</tr><tr>";
		}
		else if ($i % $itemsPerRow == 1) {
			$items_html .= "<tr>";
		}
		$it = new Item($item['id']);
		$items[] = $it;
		
		$items_html .= "<td>".$it->getItemPlate()."</td>";

		if ($i > $itemsPerRow * 2) {
			$paddingBelow = "&nbsp;";
		}				
	}
	
	$items_html .= "</tr></table></div></div>";	

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

	if ($order_by == 'price') {
		if ($sort_order == 'asc') {
			$low_price = '<u>low</u>'; $high_price = 'high';
		} elseif ($sort_order == 'desc') {
			$high_price = '<u>high</u>'; $low_price = 'low';
		}
	} else {
		$high_price = 'high'; $low_price = 'low';
	}

	if ($order_by == 'date') {
		if ($sort_order == 'asc') {
			$low_date = '<u>low</u>'; $high_date = 'high';
		} elseif ($sort_order == 'desc') {
			$high_date = '<u>high</u>'; $low_date = 'low';
		}
	} else {
		$high_date = 'high'; $low_date = 'low';
	}

	$sortBy = "<b><span style='padding-right: 5px;'>price:</span> <span style='padding-right: 2px;'><a href='index.php?".$saveSearch."&search=item&sort_by=price&sort_order=asc' style='text-decoration:none;'>".$low_price."</a></span> <a href='index.php?".$saveSearch."&search=item&sort_by=price$new_sort_order&sort_order=desc' style='text-decoration:none;'>".$high_price."</a> &nbsp; <span style='padding-right: 5px;'>date:</span> <span style='padding-right: 3px;'><a href='index.php?".$saveSearch."&search=item&sort_by=date&sort_order=asc' style='text-decoration:none;'>".$low_date."</a></span> <a href='index.php?".$saveSearch."&search=item&sort_by=date$new_sort_order&sort_order=desc' style='text-decoration:none;'>".$high_date."</a></b>";
?>

<div class="searchOpt">
<table width="100%" border=0>
<tr>
	<td>
		<?=$sortBy?>
	</td>
	<td>
		<span style="padding-right: 5px;">view:</span>
		<span style="padding-right: 2px; <?php if($view) { echo "text-decoration: underline;"; } ?>"><a href="index.php?<?=$saveSearch?>&search=item&view=map">map</a></span>
		<span style="<?php if(!$view) { echo "text-decoration: underline;"; } ?>"><a href="index.php?search=item<?=$saveSearch?>&view=">grid</a></span>
	</td>
</tr>
</table>
</div>

</div>

<div class="spacer"></div>

<?php
if ($view == "map" && !$no_res) {
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

<div id='map_canvas' class="map-canvas" style='width: 930px; height: 380px; margin: 20px 20px 0 20px;'></div>

<br>

<script src="https://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer.js"></script>
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

	userLat = <?=$userLat?>;
	userLong = <?=$userLong?>;

/* This infowindow stopped working for some reason..

    var infowindow = new InfoBubble({
		maxWidth: 200,
		shadowStyle: 1,
		padding: 0,
		borderRadius: 4,
		arrowSize: 10,
		arrowPosition: 10,
      		arrowStyle: 2,	          
		borderWidth: 0,
		borderColor: '#2c2c2c'
    });
*/

	var userLocation = new google.maps.LatLng(userLat, userLong);

	var options = {
		mapTypeControlOptions: {
			mapTypeIds: [ 'Styled']
		},
		center: userLocation,
		zoom: 5,
		maxZoom: 15,
		mapTypeId: 'Styled'
	};

	var div = document.getElementById('map_canvas');
	var map = new google.maps.Map(div, options);
	var styledMapType = new google.maps.StyledMapType(styles, { name: 'Item Map' });
	map.mapTypes.set('Styled', styledMapType);

	var bounds = new google.maps.LatLngBounds();
	var markers = [];
	bounds.extend(userLocation);
	<?php
	$map = mysql_query($sql);
	while ($res = mysql_fetch_array($map)) {
		$item_id = $res['item_id'];
		$item = new Item($res['id']);
		$plate = $item->getItemCell($silo_id, $c_user_id);
		$plate = str_replace("<td>", "",$plate);
		$plate = str_replace("</td>", "",$plate);
	?>
		var pos<?=$item_id?> = new google.maps.LatLng(<?=$item->latitude?>, <?=$item->longitude?>);				
		
	   	var marker<?=$item_id?> = new google.maps.Marker({
	       	map: map,
			animation: google.maps.Animation.DROP,
			icon: 'images/map-marker.png',
	       	position: pos<?=$item_id?>
	   	});
		markers.push(marker<?=$item_id?>);
		bounds.extend(pos<?=$item_id?>);	    
		google.maps.event.addListener(marker<?=$item_id?>, 'click', (function(marker) {
	        return function() {
	          infowindow.setContent(<?="\"$plate\""?>);
	          infowindow.open(map, marker);
	        }
	      })(marker<?=$item_id?>));
		
		<?php
		}
	?>
	map.fitBounds(bounds);
	var markerCluster = new MarkerClusterer(map, markers, {maxZoom: 13, gridSize:10});
}

function loadScript() {
  var script = document.createElement("script");
  script.type = "text/javascript";
  script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyAPWSU0w9OpPxv60eKx70x3MM5b7TtK9Og&sensor=false&callback=initialize";
  document.body.appendChild(script);
}

window.onload = loadScript;

</script>

<?php
} else {
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

<?=$paddingBelow?>

<?php } ?>

