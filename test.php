<?php
			// If admin setting is on
			if (ADMIN_NOTIF == 'on') {
				echo works;
				$adminSub = "New user on ".SITE_NAME."!";
				$adminMsg = "<h3>A new user has been created!</h3>";
				$adminMsg .= "Remember, this user hasn't been activated (yet).<br/><br/>";
				$adminMsg .= "<b>New user's e-mail:</b> ".$email."<br><br>";
				$adminMsg .= "This e-mail is sent everytime a new user is created through the 'create_account.php' page. To turn off these notifications, look in the config.php file.";
				
				$admin_emails = explode(',', ADMIN_NOTIF_EMAILS);
				foreach($admin_emails as $email) {
					echo "<br>".$email."<br>";
				}

			}
?>
test

