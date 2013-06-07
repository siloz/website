<?php
	$secure = "s";

	$secure_pages = array("payment", "my_account");
	$current_page = param_get('task');
	if (in_array($current_page, $secure_pages)) { $secure = ""; }

	foreach ($secure_pages as $page) {
		echo $page;
	}
echo "<br>";
echo $secure;
?>