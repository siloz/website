<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add fancyBox -->
<link rel="stylesheet" href="js/fancybox/source/jquery.fancybox.css?v=2.1.0" type="text/css" media="screen" />
<script type="text/javascript" src="js/fancybox/source/jquery.fancybox.pack.js?v=2.1.0"></script>

<!-- Optionally add helpers - button, thumbnail and/or media -->
<link rel="stylesheet" href="js/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.3" type="text/css" media="screen" />
<script type="text/javascript" src="js/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.3"></script>
<script type="text/javascript" src="js/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.3"></script>

<link rel="stylesheet" href="js/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.6" type="text/css" media="screen" />
<script type="text/javascript" src="js/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.6"></script>

<h1>Siloz Stories</h1>
<div id="stories" style="padding: 0px 20px;"></div>

<script>
$(window).load(function(){
var playListURL = 'http://gdata.youtube.com/feeds/api/playlists/04C74E561FAC0116?v=2&alt=json&callback=?';
var videoURL= 'http://www.youtube.com/watch?v=';
$.getJSON(playListURL, function(data) {
    var list_data="<table width=100% cellpadding='10px'>";
    $.each(data.feed.entry, function(i, item) {
        var feedTitle = item.title.$t;
        var feedURL = item.link[1].href;
        var fragments = feedURL.split("/");
        var videoID = fragments[fragments.length - 2];
        var url = videoURL + videoID;
		var views = item.yt$statistics.viewCount;
		var author = item.author[0].name.$t;
		var thumb = "http://img.youtube.com/vi/"+ videoID +"/default.jpg";
        list_data += '<tr><td valign=top style="background: #F2F2F2;" width="120px"><a href="'+ url +'?autoplay=1"  class="fancybox-media" title="'+ feedTitle +'"><img alt="'+ feedTitle+'" src="'+ thumb +'"></a></td><td valign=top style="background: #F2F2F2;"><b>' + feedTitle + '</b><div style="color: #A4A4A4"</br>by ' + author + '</br>'+ views + ' views</div></td></tr>';
    });
	list_data += "</table>";
    $(list_data).appendTo("#stories");
});
});
</script>

<script type="text/javascript">
	$('.fancybox-media').fancybox({
			openEffect  : 'none',
			closeEffect : 'none',
			helpers : {
				media : {}
			}
		});
</script>