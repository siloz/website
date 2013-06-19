<script type="text/javascript">
idleTime = 0;
$(document).ready(function () {
    //Increment the idle time counter every minute.
    var idleInterval = setInterval("timerIncrement()", 60000); // 1 minute

    //Zero the idle timer on mouse movement.
    $(this).bind('mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove', function (e) {
        idleTime = 0;
	 var isVisible = $('#timeout').is(':visible');
	if (isVisible) {
  	 document.getElementById("timeout").style.display = "none";
  	 document.getElementById("overlay").style.display = "none";
	}
    });
})
function timerIncrement() {
    idleTime = idleTime + 1;
    if (idleTime > 1) { // 2 minutes
        javascript:popup_show('timeout', 'timeout_drag', 'timeout_exit', 'screen-center', 0, 0);
    }

    if (idleTime > 2) { // 3 minutes
	window.location = 'index.php';
    }
}
</script>

<div class="login" id="timeout" style="width: 300px;">
	<div id="timeout_drag" style="float:right">
		<img id="timeout_exit" src="images/close.png"/>
	</div>
	<div>
		<font color="red" size="3"><b>You have been inactive for over two minutes. <br><br> You will be redirected if inactivity continues.</b></font>
	</div>
</div>