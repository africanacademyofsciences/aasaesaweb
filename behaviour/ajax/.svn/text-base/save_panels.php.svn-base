<?php
// This file is called by the menu manage.
// Its used to write the current menu edit to disk for later use.
$s=serialize($_POST);

// Logfile is a tmp file with the current edit
$logfile = $_SERVER['DOCUMENT_ROOT']."/silo/tmp/panels-".$_POST['msv'].".txt";
// Logfile1 is a nice viewable file for ease of testing.
//$logfile1 = $_SERVER['DOCUMENT_ROOT']."/behaviour/ajax/save_menu.txt";

// Open both log files
$fp=fopen($logfile, "wt");
//$fp1=fopen($logfile1, "wt");

// We only really need fp, fp1 is for testing if it fails to open its not important
if ($fp) {
	fputs($fp, $s);
	//fputs($fp1, print_r($_POST, true));
	// Close log files.
	fclose($fp);
	//if ($fp1) fclose($fp1);
}

?>