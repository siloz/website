<?php
include 'charts.php';
include '../../utils.php';

$conn = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);	
mysql_select_db(DB_NAME, $conn);

$silo_id = $_REQUEST['silo_id'];
$dayInc = $_REQUEST['days'];

$date = mysql_fetch_array(mysql_query("SELECT created_date, DATEDIFF(end_date, created_date) AS days FROM silos WHERE silo_id = '$silo_id'")); 
$created = $date['created_date'];
$endDays = $date['days'];
$now = strtotime("now");

$i=1;
while ($i <= 7) {
	$dayVar = "day".$i;
	$curDays = $dayInc * $i;
	if ($curDays > $endDays) { $curDays = $endDays; }
	$$dayVar = $curDays;
	$i++;
}

$n=1;
while ($n <= 7) {
	$curDays = $dayInc * $n;
	if ($curDays > $endDays) { $curDays = $endDays; }

	$add = "date_add('".$created."', interval ".$curDays." day)";
	$var = "d".$n;
	$$var = mysql_num_rows(mysql_query("SELECT * FROM items WHERE silo_id = '$silo_id' AND added_date < $add AND (status != 'deleted' OR status != 'flagged')"));

	$new = $curDays + $dayInc;
	$new_date = strtotime($created . " +".$new." day");
	if ($new_date > $now) { $max = $$var + 1; break; } else { $n++; }
}

if ($max < 6) { $max = 6; }

$chart[ 'chart_data' ] = array ( array ( "","Silo Start","Day ".$day1,"Day ".$day2,"Day ".$day3,"Day ".$day4,"Day ".$day5,"Day ".$day6,"Day ".$endDays ), array ( "Region A",0,$d1,$d2,$d3,$d4,$d5,$d6,$d7 ) );


$chart[ 'axis_category' ] = array ( 'size'=>11, 'color'=>"3A3A3A", 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal" ); 
//$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>true, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"222222" ,'position'=>"outside" );
$chart[ 'axis_value' ] = array (  'min'=>0, 'max'=>$max, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );

$chart[ 'chart_border' ] = array ( 'color'=>"3A3A3A", 'top_thickness'=>2, 'bottom_thickness'=>2, 'left_thickness'=>2, 'right_thickness'=>2 );
//$chart[ 'chart_data' ] = array ( array ( "","Silo Start","Day 1","Day 2","Day 3","Day 4","Day 5","Day 6","Day 7" ), array ( "Region A",0,2,5,7,13,25,28,48 ) );
//$chart[ 'chart_data' ] = array ( array ( "","Silo Start","Day 2","Day 4","Day 6","Day 8","Day 10","Day 12","Day 14" ), array ( "Region A",0,2,5,7,13,25,28,48 ) );
//$chart[ 'chart_data' ] = array ( array ( "","Silo Start","Day 3","Day 6","Day 9","Day 12","Day 15","Day 18","Day 21" ), array ( "Region A",0,2,5,7,13,25,28,48 ) );
//$chart[ 'chart_data' ] = array ( array ( "","Day 1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21" ), array ( "Region A",2,5,7,13,25,28,48 ) );
//$chart[ 'chart_data' ] = array ( array ( "","Jan","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31" ), array ( "Region A",10,12,11,15,20,22,21,25,31,32,28,29,40,41,45,50,65,45,50,51,65,60,62,65,45,55,59,52,53,40,45 ) );
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"none", 'fill_shape'=>false );
$chart[ 'chart_rect' ] = array ( 'x'=>40, 'y'=>25, 'width'=>490, 'height'=>80, 'positive_color'=>"000000", 'positive_alpha'=>30, 'negative_color'=>"ff0000",  'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'prefix'=>"", 'suffix'=>" items", 'decimals'=>0, 'separator'=>"", 'position'=>"cursor", 'hide_zero'=>true, 'as_percentage'=>false, 'font'=>"arial", 'bold'=>true, 'size'=>16, 'color'=>"FF642F", 'alpha'=>75 );

$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"ffffff", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>50, 'x'=>-10, 'y'=>348, 'width'=>300, 'height'=>150, 'text'=>"hertz", 'h_align'=>"center", 'v_align'=>"top" ),
                           array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>15, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>60, 'x'=>0, 'y'=>0, 'width'=>320, 'height'=>300, 'text'=>"output", 'h_align'=>"left", 'v_align'=>"bottom" ) );

$chart[ 'legend_rect' ] = array ( 'x'=>-100, 'y'=>-100, 'width'=>10, 'height'=>10, 'margin'=>10 ); 

$chart[ 'series_color' ] = array ( "2f8ECB" );


SendChartData ( $chart );
?>