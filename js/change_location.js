$(function() {
	initializeChangeLocationElements();
});

function initializeChangeLocationElements() {
	$('.change_location').click(function() {
		var str = '<form action="" method="POST"><input onclick=this.value=""; type="text" value="Enter Zip Code" name="zip"> <button type="submit" name="location" value="Update">Update</button></form>';
		$(this).append( str );
		$(this).remove();
	});
}