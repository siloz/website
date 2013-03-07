$(function() {
	initializeChangeLocationElements();
	initializeChangeLocationSplash();
});

function initializeChangeLocationElements() {
	$('.change_location').click(function() {
		var str = '<div style="margin-top: 3px; margin-bottom: -6px"><form action="" method="POST" style="display: inline-block;"><input onfocus="select();" placeholder="enter zip code" type="text" name="zip"> <button type="submit" name="location" value="Update">Update</button></form></div>';
		$(this).replaceWith();
		$(".enterLocation").replaceWith( str );
	});
}

function initializeChangeLocationSplash() {
	$('.s_change_location').click(function() {
		var splash = '<form action="" method="POST" style="display: inline-block;"><input onfocus="select();" placeholder="enter zip code" type="text" name="zip"> <button type="submit" name="location" value="Update">Update</button></form>';
		$(this).replaceWith( splash );
	});
}