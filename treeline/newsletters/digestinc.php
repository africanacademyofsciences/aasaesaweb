<?php

//ini_set("display_errors", "yes");
//error_reporting(E_ALL);

/*require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/functions.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/website.class.php");

$website = new Website();
$website->getSiteConfig(); // site details

$email = $website->config['site_name'].' <'.$website->config['contact_recipient_email'].'>';
$url = $website->config['site_url'];
$siteName = $website->config['site_name'];

// whose the email going to be sent from?
define('NEWSLETTER_FROM_EMAIL', $email);
// Needed to cron job knows the root server for images paths.
define('SERVER_NAME', $url);*/
define('SITE_NAME', "us"); 

define('NEWSLETTER_FROM_EMAIL', "chris.hardy@ichameleon.com");

// Needed to cron job knows the root server for images paths.
define('SERVER_NAME', 'ichameleon.com');
?>