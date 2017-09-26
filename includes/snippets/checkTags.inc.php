<?php

	// Standard tags page on main site
	if (isset($location[0]) && $location[0]=="tags" && $location[1]) {
		$name = $location[0];
		$_GET['tag']=urldecode($location[1]);
	}
	// Main microsite tags page
	else if (isset($location[1]) && $location[1]=="tags" && $location[2]) {
		$name = $location[1];
		$_GET['tag']=urldecode($location[2]);
	}
	// Language version tags page
	else if (isset($location[2]) && $location[2]=="tags" && $location[3]) {
		$name = $location[2];
		$_GET['tag']=urldecode($location[3]);
	}
?>