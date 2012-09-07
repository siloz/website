<html>
<head>
<script type="text/javascript" src="http://www.plaxo.com/css/m/js/util.js"></script>
<script type="text/javascript" src="http://www.plaxo.com/css/m/js/basic.js"></script>
<script type="text/javascript" src="http://www.plaxo.com/css/m/js/abc_launcher.js"></script>
<script type="text/javascript"><!--
function onABCommComplete(data) {
  // OPTIONAL: do something here when the new data gets populated in the text area
  // data is an array of selected name/email arrays, i.e. [[name1, email1], [name2, email2], ...]
}
//--></script>

</head>
	<body>
	  <textarea id="recipient_list" rows="3" cols="80"></textarea>
		<br/>
	  <a href="#" onclick="showPlaxoABChooser('recipient_list', 'import_address_book.php'); return true;"><img src="http://www.plaxo.com/images/abc/buttons/add_button.gif" alt="Add from my address book" /></a>
	</body>
</html>