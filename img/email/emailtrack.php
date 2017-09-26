<?php
chdir(dirname($_SERVER['PHP_SELF']));
//ini_set("display_errors", 1);
//error_reporting(E_ALL ^ E_NOTICE);
//err("running from the ".getcwd()." directory oid(".$_GET['oid'].") \n server: ".print_r($_SERVER, true)."\n");
if ($_GET['oid']>0) {
	// Newsletter includes hopefully this will give us a db variable.
	include(getcwd()."/../../treeline/newsletters/newsinc.php");
	$query = "UPDATE newsletter_outbox SET delivered=1 WHERE id=".$_GET['oid'];
	$db->query($query);
	if ($db->last_error) err("failed to update db() q($query)");
}

//$img = getcwd().'/benlogo.png';
//$img = getcwd().'/top-bg.png';
$img = getcwd().'/tracking-image.png';
if (file_exists($img)) {
	$filesize = filesize($img);
	$buffer = 1024;
	//err ("File $img exists, dump it");
	header("Content-type: image/png");
	//header('Content-Length: ' .$filesize);
	ob_clean();
	flush();
	if ($fp = fopen($img, "rb")) {
		while (!feof($fp)) {
			$buf = fread($fp, $buffer);
			print $buf;
		}
		fclose($fp);
	}
	else err("Failed to open the file($img)<br>\n");
	//err("read $readbytes of size($filesize)");
}
else err("File ($img) does not exist");

exit(0);
function err($s) {
	mail("phil.redclift@ichameleon.com", "news just got a thingy", $s);
}
?>