<?php
include 'charts.php';

$chart[ 'axis_category' ] = array ( 'size'=>14, 'color'=>"000000", 'alpha'=>0, 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal" ); 
$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>true, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"222222" ,'position'=>"outside" );
$chart[ 'axis_value' ] = array (  'min'=>0, 'max'=>120, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"ffffff", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );

$chart[ 'chart_border' ] = array ( 'color'=>"000000", 'top_thickness'=>2, 'bottom_thickness'=>2, 'left_thickness'=>2, 'right_thickness'=>2 );
$chart[ 'chart_data' ] = array ( array ( "","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31" ), array ( "Region A",10,12,11,15,20,22,21,25,31,32,28,29,40,41,45,50,65,45,50,51,65,60,62,65,45,55,59,52,53,40,45 ), array ( "Region B",30,32,35,40,42,35,36,31,35,36,40,42,40,38,40,40,38,36,30,29,28,25,28,29,30,40,32,33,34,30,35 ) );
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"none", 'fill_shape'=>false );
$chart[ 'chart_rect' ] = array ( 'x'=>40, 'y'=>25, 'width'=>335, 'height'=>200, 'positive_color'=>"000000", 'positive_alpha'=>30, 'negative_color'=>"ff0000",  'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'position'=>"cursor", 'hide_zero'=>true, 'as_percentage'=>false, 'font'=>"arial", 'bold'=>true, 'size'=>12, 'color'=>"ffffff", 'alpha'=>75 );

$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"ffffff", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>50, 'x'=>-10, 'y'=>348, 'width'=>300, 'height'=>150, 'text'=>"hertz", 'h_align'=>"center", 'v_align'=>"top" ),
                           array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>15, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>60, 'x'=>0, 'y'=>0, 'width'=>320, 'height'=>300, 'text'=>"output", 'h_align'=>"left", 'v_align'=>"bottom" ) );

$chart[ 'legend_rect' ] = array ( 'x'=>-100, 'y'=>-100, 'width'=>10, 'height'=>10, 'margin'=>10 ); 

$chart[ 'series_color' ] = array ( "77bb11", "cc5511" ); 

SendChartData ( $chart );
?>