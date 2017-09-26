<?php

ini_set("display_errors", 1);

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");	
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/functions.php");
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/members/includes/import.class.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/newsletter.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/subscriber.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/member.class.php');
include_once($_SERVER['DOCUMENT_ROOT']."/treeline/newsletters/newsinc.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/treeline/newsletters/includes/email/htmlMimeMail.php');

if ($_SESSION['treeline_user_id']!=1) redirect ("/treeline/");


//print "S(".print_r($_SESSION, 1).")<br>\n";
$action = read($_REQUEST,'action','');

$message = array();
$feedback = read($_REQUEST,'feedback','');

$import_type = read($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "type", "");
$table = $import_type;

$import = new Import();

if ($table == "fellows") {
	$table = "members";
	//$import->newsletter->setTesting("sub");
}

if ($table) {
	$query = "SELECT name FROM data_import WHERE `table` = '$table' ORDER BY id";
	//print "$query<br>\n";
	if ($results = $db->get_results($query)) {
		foreach ($results as $field) {
			$fieldnames.=$field->name.", ";
		}
	}
}
//print "Fieldnames($fieldnames)<br>\n";


if ($_SERVER['REQUEST_METHOD']=="POST") {

	// Check if we have a file to squiggy
	//print "Posted($action)<br>\n";
	
	if ($action=="import") {
	
		if ($_FILES['import']['name']) {
			if ($import->openFile($_FILES['import'])) {
				// Read each line 
				$linecount = 0;
				while ($line = fgets($import->fp)) {
					if (substr($line,-1,1)=="\n") $line = substr($line, 0, -1);
					if (substr($line,-1,1)=="\r") $line = substr($line, 0, -1);
					//print "got line($line)<br>\n";
					$index=0;
					
					if (!$linecount) {
						$line = str_replace(",,",",",$line);
						$data = explode('","', substr($line, 1, -1));
						//print "Fields(".print_r($data, 1).")<br>\n";
						foreach ($data as $tmp) {
							//print "create field for $tmp<br>\n";
							$import->fields[$index]=new Field($table, $tmp);
							//$import->fields[$index]->dump();
							foreach($import->fields[$index]->errmsg as $tmp) $message[]=$tmp;
							$index++;
							
						}				
					}
					else {
						$i = 0;
						$len = strlen($line);
						foreach ($import->fields as $field) {
							$s = "";
							$sep = '"';
							$collecting = false;
							if ($field->numeric) {
								$sep="";
								$collecting = true;
							}
							//print "Try to get value for field(".$field->name.") sep($sep) i($i) len($len)<br>\n";
							
							while ($i<$len) {
								$c = $line[$i];
								//print "Test char(".$c.")<br>\n";
								if ($c==$sep) {
									if ($collecting) $collecting=false;
									else $collecting = true;
									//print "got a sep<br>\n";
								}
								else if (
									($sep=='"' && !$collecting && $c==",") || 	// String field hit a comma while !collecting
									(!$sep && $c==",")  						// Numeric field hit a comma
									) {
									$i++;
									//print "breaking, got a comma..<br>\n";
									break;
								}
								// Numeric field with alpha char - move on
								else if (!$sep && $c==".") $s.=$c;
								else if (!$sep && !is_numeric($c))	{
									//print "Numeric field with alpha char ignored...<br>\n";
								}
								else {
									//print "add $c to $s<br>\n";
									$s .= $c;
								}
								$i++;
							}
							$field->value = $s;
							//print $field->dump();	
						}
						
						if ($linecount) {
							//print "Write this line (".($linecount+1).")<br>\n";
							$import->resetErr();
							$import->writeData($linecount+1);
							if (count($import->errmsg)) {
								$message[] = "Line: $linecount import problem($line)";
								foreach ($import->errmsg as $tmp) $message[]=$tmp;
							}
						}
						
					}
					$linecount++;
				}
				if (!$message) $feedback="success";
				$message[]="Records inserted(".$import->member_inserts.") updated(".($import->member_updates+0).") ignored(".($import->ignored+0).") errors(".$import->errors.")";
			}
			else $message = $import->errmsg;
		}
		else $message[]="You do not appear to have uploaded a file";
	}
	
}


$css = array('forms','tables'); // all CSS needed by this page
$extraCSS = ''; // extra on page CSS

$js = array(); // all external JavaScript needed by this page
$extraJS = ''; // extra on page JavaScript

// Page title	
$pageTitleH2 = $pageTitle = 'Global';
$pageClass = 'global';


include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	

?>
<div id="primarycontent">
<div id="primary_inner">
<?php 


	//print "got message($message) r(".print_r($message, true).")<br>\n";
	echo drawFeedback($feedback, $message);
	if ($nextsteps) echo treelineList($nextsteps, "Next steps", "blue");

	$page_html = '
	<p><a href="/treeline/members/import.php?type=fellows">Import fellows</a></p>
	';
	
	$page_html = '<p>Import complete</p>';

	if ($fieldnames) {
		$page_html .= '
		<form id="'.$action.'form" action="" method="post" enctype="multipart/form-data">
		<fieldset>
			<p class="instructions">The first line of the file should contain the field name specific to that row. Fieldnames must be one of: '.substr($fieldnames, 0, -2).'.</p>
			<fieldset>
				<legend>Data file</legend>
				<input type="hidden" name="action" value="import" />
				<input type="hidden" name="type" value="'.$import_type.'" />
				<label for="file" class="required">File:</label>
				<input type="file" id="f_file" name="import" /><br />
				<label for="submit" style="visibility:hidden;">submit:</label>
				<input type="submit" id="submit" class="submit" value="Upload" />
			</fieldset>
		</fieldset>
		</form>
		';
	}
	else if ($table) $page_html .= '<p>No fields have been configured for this import</p>';
	
	echo treelineBox($page_html, "Import $import_type file to table:".$table, "blue");


	?>
    </div>
</div>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>