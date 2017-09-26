<?php
// This file is designed to be run alone and must be enabled in .htaccess
// This allows file downloads to be tracked internally
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/file.class.php");

$msg='';

$guid=$_GET['id'];
if ($guid) {

	$file=new File();
	$file->loadFileByGUID($_GET['id']);
	$msg.="Load file(".$_GET['id'].") \n";
	
	if ($file->guid==$_GET['id'] && $file->guid>'') {
		$filetype=$file->type;
		$filename=$_SERVER['DOCUMENT_ROOT']."/silo/files/".$file->name.".".$file->extension;
		$msg.="Filetype($filetype) \n";
		$msg.="check if ($filename) exists?<br>";
		if (file_exists($filename)) {
		
			// Log this downloadd to the database and mail summary
			$query="INSERT INTO files_downloads(guid, added, page, member_id) values ('$guid', NOW(), '".$_SERVER['HTTP_REFERER']."', ".($_SESSION['member_id']+0).")";
			$msg.="Log download($query) \n";
			if (!$db->query($query)) {
				$msg.="Failed to log file download";
			}
			
			
			// required for IE, otherwise Content-disposition is ignored
			if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
			
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers 
			header("Content-Type: ".$filetype);
			// change, added quotes to allow spaces in filenames, by Rajkumar Singh
			header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filename));
			readfile("$filename");
	
		}
		else $msg.="File does not exist \n";
	}
	else $msg.="Failed to load file(".$_GET['id'].") \n";
}
else $msg.="No file guid was found to download \n";

if ($msg) {
	//print nl2br($msg);
	$msg.="\n\n".print_r($_SERVER, true)."\n\n";
	//mail("phil.redclift@ichameleon.com", "File downloaded from", getcwd()."\n".$msg);
}

exit();

?>