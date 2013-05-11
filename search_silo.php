<?php
	include_once("include/GoogleAnalytics.php");

	$silosPerPage = "10";
	$silosPerRow = "5";

	$view = param_get('view');

	$search_clause = "";
	$saveSearch = "";
	
	if (strlen(trim(param_get('keywords'))) > 0) {
		$saveSearch .= "&keywords=".trim(param_get('keywords'));
		$kws = explode(" ", strtolower(param_get('keywords')));
		foreach ($kws as $kw) {
			if (strlen(trim($kw)) > 0)
			$search_clause .= " AND ( lower(name) LIKE '%$kw%' OR lower(description) LIKE '%$kw%' )";
		}
	}
	if (strlen(param_get('zip_code')) > 0) {
		$saveSearch .= "&zip_code=".param_get('zip_code');
		$search_clause .= "";
	}
	if (strlen($low) > 0) {
		$saveSearch .= "&price_low=".$low;
		$search_clause .= " AND goal >= ".$low;
	}
	if (strlen($high) > 0) {
		$saveSearch .= "&price_high=".$high;
		$search_clause .= " AND goal <= ".$high;
	}
	if (strlen(param_get('category')) > 0) {
		$saveSearch .= "&category=".param_get('category');		
		$search_clause .= " AND silo_cat_id = ".param_get('category');
	}
	else {
		$search_clause .= " AND silo_cat_id IN (SELECT silo_cat_id FROM silo_categories)";		
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

	$search_clause .= " AND status = 'active' AND silo_type = 'public' ";
	$from = param_get('from') == '' ? 1 : intval(param_get('from'));
	$to = param_get('to') == '' ? $silosPerPage : intval(param_get('to'));		
	$offset = $to - $from + 1;

	$new_sort_order = '';
	$sort_order = param_get('sort_order');
	$img1_path = 'images/none.png';
	$img2_path = 'images/none.png';			
	$order_by = param_get('sort_by');
	$order_by_clause = "";
	if ($order_by == 'goal')
		$order_by_clause = " ORDER BY goal $sort_order ";
	elseif ($order_by == 'date')
		$order_by_clause = " ORDER BY start_date $sort_order ";
	else {
		$order_by_clause = " ORDER BY distance ";
	}

	$sql = "SELECT COUNT(*) FROM silos WHERE 1 > 0 $search_clause";
	$tmp = mysql_fetch_array(mysql_query($sql));
	$count_silos = $tmp[0];

	if ($count_silos == "0") { $no_res = "<center>We cannot find any matches. Please broaden your search!</center>"; }

	$total_pages = ceil($count_silos / $silosPerPage);

	if (param_get('page')) {
		$page  = param_get('page');
	} 
	else { 
		$page = 1;
	};

	$start_from = ($page-1) * $silosPerPage;

	$sql = "SELECT *, $sqlDist AS distance FROM silos INNER JOIN silo_categories USING (silo_cat_id) WHERE 1 > 0 $search_clause $order_by_clause LIMIT $start_from, $silosPerPage";
	
	$tmp = mysql_query($sql);		
	$closed_silos = array();
	
	$siloz_html = "<div class='row'><div class='span12'>";
	
	$i = 0;
	
	while ($s = mysql_fetch_array($tmp)) {	
		
		$silo = new Silo($s['id']);
		
		if ($i % $silosPerRow == 0) {
			$siloz_html .= "<div class='row item_row-search'>";
		}
					
		$siloz_html .= $silo->getSiloPlate($i % $silosPerRow == 0);
		
		if ($i % $silosPerRow == $silosPerRow - 1) {
			$siloz_html .= "</div>";
		}
		
		$closed_silos[] = $silo;	

		$i++;
	}
	
	if ($i % $silosPerRow < $silosPerRow - 1) {
		$siloz_html .= "</div>";
	}

	if ($i == 4) {
		$siloz_html .= "</div>";
	}

	if ($view != map) {
		$siloz_html .= "</div>";
	}
	
	$siloz_html .= "</div>";

	$prev = "";
	if ($from >= $silosPerPage)
		$prev = "<a href=index.php?search=silo&from=".($from-$silosPerPage)."&to=".($from-1)."$query class=status><img src='images/prev_arrow.png' height='15px'> <font color='#fff'>Prev</font></a> &nbsp;&nbsp;";
	$next = "";
	if ($to < $count_silos)
	 	$next = "<a href=index.php?search=silo&from=".($to+1)."&to=".min($to + $silosPerPage, $count_silos)."$query class=status><font color='#fff'>Next</font> <img src='images/next_arrow.png' height='15px'></a>"
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
				echo '<a href="index.php?'.$saveSearch.'&search=silo&page='.$prev.'" class="nb">< Prev</a> <span class="navPad"></span>';
			}

		for ($i=1; $i<=$total_pages; $i++) {			

			if ($i != $page) {
				echo '<a href="index.php?'.$saveSearch.'&search=silo&page='.$i.'" class="nb">' . $i . '</a> <span class="navPad"></span>';
			} 
			else {
				echo '<span class="nbSelected">'.$i.'</span> <span class="navPad"></span>';
			}
		};
		if ($page != $total_pages) {
			$next = $page + 1;
			echo '<a href="index.php?'.$saveSearch.'&search=silo&page='.$next.'" class="nb">>Next</a>';
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
	$sortBy = "<b>sort: <a href=index.php?".$saveSearch."&search=silo&sort_by=goal$new_sort_order style='text-decoration:none;'> value <img src=$img1_path></a> &nbsp; <a href=index.php?".$saveSearch."&search=silo&sort_by=date$new_sort_order style='text-decoration:none;'> date <img src=$img2_path></a></b>";
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
		<a href="index.php?<?=$saveSearch?>&search=silo&view=map"> map <input style="float: right; margin-top: 5px; margin-left: 5px;" type="radio" <?php if ($view) { echo "CHECKED"; } ?>></input></a>
	</td>
	<td>
		<a href="index.php?search=silo<?=$saveSearch?>&view=">grid <input style="float: right; margin-top: 5px; margin-left: 5px;" type="radio" <?php if (!$view) { echo "CHECKED"; } ?>></input></a>
	</td>
</tr>
</table>
</div>


</div>

<div class="spacer"></div>

<?php
if ($view == "map") {
?>

<!--
<div id="result_nav" class="heading">
<table width="100%">
<tr>
	<td width="67%" align="right" valign="top">
		<b>This map is showing <?=$count_silos?> silos that are fundraising in your area</b>
	</td>
	<td align="right">
		<i>View:</i> <a href="index.php?search=silo&view=map">Map</a> | <a href="index.php?search=silo" style="text-decoration:none;">Grid</a>
	</td>
</tr>
</table>
</div>
-->

<div id='map_canvas' class="map-canvas" style='width: 930px; height: 400px; margin: 20px;'></div>

<br>

<?php
//Get silos for map
$qry = mysql_query("SELECT * FROM silos WHERE status = 'active'");
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

siloLat = <?=$silo->latitude?>;
siloLong = <?=$silo->longitude?>;

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
var styledMapType = new google.maps.StyledMapType(styles, { name: 'Silo Map' });
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

<!--
<div id="result_nav" class="heading">
	<table width="100%" valign=”top”>
	<tr>
	<td width="10%">
	<?=$prev?> <?=$next?>
	</td>
	<td width="50%" align="right" valign="top">
		<b>Viewing <?=$count_silos?> silos that are fundraising in your area</b>
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
					echo "<b>sort by <a href=index.php?search=silo&sort_by=goal$new_sort_order style='text-decoration:none;'> price <img src=$img1_path></a> or <a href=index.php?search=silo&sort_by=date$new_sort_order style='text-decoration:none;'> list date <img src=$img2_path></a></b>";
			?> &nbsp; &nbsp;
		<i>View:</i> <a href="index.php?search=silo&view=map" style="text-decoration:none;">Map</a> | <a href="index.php?search=silo">Grid</a>
	</td>
	</tr>
			</table>
			</div>
-->

<?=$no_res?>

<?php echo $siloz_html;?>

<div style="margin-left: 10px;">
</div>