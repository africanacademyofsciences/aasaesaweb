<?php

	$host = $_SERVER["SERVER_NAME"];
	$sqlserver = 'shareddb1b.hosting.stackcp.net';

	// These should now be the only things we need to set
	// Need to test newsletter connectivity works ok with these
	$username = 'user-aas';			// Database user  - I'd prefer to get these from admin too.
	$database = 'aas-db-main-1-35544b';	// Database schema
	$client_id = 30;				// From the treeline2_admin.clients table.
	
	$password = "jDry2bT/X9cV1"; // Add password here to bypass collection from admin database
	
	// Ichameleon dev subdomain
	if (preg_match("/^vanilla.ichameleon.com/", $host, $reg) ) {
	}
	
	// Loading outside the webserver for now we will assume this is a cron job/console attempt
	// Load as ichameleon test but this will need to change when we go live.
	else if (!$host && $ezCoreRoot) {
	}
	
	// Load my local copy
	// In need to add my username and password or it will find them for the live version
	else if (preg_match("/^vanilla$/", $host)) { 
		//local copy
		$password = 'banana';
	}	
	
	// Default to the live database
	else
	{
		//$database .= '_live';	// This is an assumption you'll have to change this if you want to buck it
	}


?>
