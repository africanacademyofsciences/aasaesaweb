<?php
// This file must be included AFTER session_start is called.
$protect = 0;
$live_site_URL = "http://www.ichameleon.com";

if ($protect) {
	if (!$_SESSION['access_id']>0) {

		// Has somebody posted the form?
		if ($_SERVER['REQUEST_METHOD']=="POST") {
			include ($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
			$query = "SELECT id FROM users WHERE name='".$_POST['username']."' AND password='".$_POST['password']."' LIMIT 1";
			//print "$query<br>\n";
			if ($uid = $db->get_var($query)) {
				$_SESSION['access_id']=$uid;
				header("Location: /\n\n");
			}
			else header("Location: $live_site_URL\n\n");
		}
	
		if (!$_SESSION['access_id']>0) {
			?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
	form {
		border: 1px solid #000;
		padding: 20px;
		margin: 50px 0 0 50px;
		float: left;
	}
	form fieldset {
		border: 0;
	}
	form fieldset label {
		float:left;
		width: 200px;
	}
	form fieldset input.text {
		float:left;
	}
	
</style>
</head>

<body>
    <form method="post">
    	<p>Access to this site is restricted</p>
    	<fieldset>
        	<label for="f_username">Username</label>
        	<input type="text" id="f_username" class="text" name="username" />
        </fieldset>
        <fieldset>
        	<label for="f_password">Password</label>
        	<input id="f_password" type="password" class="text" name="password" />
        </fieldset>
        <fieldset>
        	<label for="f_submit" style="visibility:hidden;">Submit</label>
        	<input type="submit" id="f_submit" class="submit" value="Show site" />
        </fieldset>
    </form>
</body>
</html><?php
			exit();
		}
	}
}

?>