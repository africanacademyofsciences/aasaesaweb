<?php
session_start();
////tmp var

$DEBUG=false;
$siteID = $_SESSION['treeline_user_site_id'];

include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/adminFunctions.php');



$linktype 		= read($_POST,'linktype','');
$prev_btn 		= read($_POST,'prev_btn','');
$submit 		= read($_POST,'submit','');
$submitsection	= read($_POST,'submitsection','');
$intlink 		= read($_POST,'intlink','');
$intsection 	= read($_POST,'intsection',$siteID);
$extprotocol 	= read($_POST,'extprotocol', '');
$extlink 		= read($_POST,'extlink','');
$extlink_target	= read($_POST,'target','');
$email_to 		= read($_POST,'email_to','');
$email_addr 	= read($_POST,'email_addr','');
$email_subject	= read($_POST,'email_subject','');
$anchor 		= read($_POST,'anchor','');
$filesection 	= read($_POST,'filesection','');


if( $prev_btn  ){
	unset($_POST);
	header("location: linkpicker.php");
}

/////check errors ///////

$error_list = array();
	if($linktype=='ext' && $submit=='true'){
		if(!$extlink){ 
			$error_list[] = "You need to enter a web address!"; 
		}else if (!preg_match('/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', santise_content($extlink), $m)) {
			$error_list[] = "Your web address does not appear to be valid!"; 
		}
	}else if( $linktype=='int' && $submitsection=='true' ){
		if(countPages($intsection)<=0){
			$error_list[] = "This section does not appear to have any pages to link to!";
		}
	}else if( $linktype=='int' && $submit=='true' ){
		if( empty($intlink) ){
			$error_list[] = "You need to specify a page to link to!";
		}
	}else if( $linktype=='anchor' && $submit=='true' ){
		if( empty($anchor) ){
			$error_list[] = "You need to specify an anchor to link to!";
		}
	}else if( $linktype=='mail' && $submit=='true' ){
		if(!$email_to){
			$error_list[] = "You have to enter the Recipient's Name!";
		}
		if(!$email_addr){
			$error_list[] = "You have to enter an email address!";
		}else if( !is_email($email_addr) ){
			$error_list[] = "Your email address does not appear to be valid!";
		}
	}else if( $linktype=='file' && $submitsection=='true' ){
		if(countFiles($filesection)<=0){
			$error_list[] = "This section does not appear to have any pages to link to!";
		}
	}else if( $linktype=='file' && $submit=='true' ){
		if( empty($intlink) ){
			$error_list[] = "You need to specify a page to link to!";
		}		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Treeline: Link picker</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/linkPicker.css');
</style>
<script type="text/javascript">
var selected;
var selectedOldClassName;

function change(id,oldClass) {
	var oldClassName='';
	var identity=document.getElementById(id);
	var selectedIdentity = document.getElementById(selected);
	var intlink = document.getElementById('intlink');
	if(oldClass==1){
		oldClassName='row dark';
	}else{
		oldClassName='row pale';
	}

	if( identity.className=='row on' ){
		identity.className=oldClassName;
		selected = '';
		selectedOldClassName = '';
		intlink.value = '';
	}else{
		// this should...
		// set the current row to be selected
		// if there's another row already highlighted, then unset that...
		if(selected>''){
			selectedIdentity.className = selectedOldClassName;  /// set the currently selected to what it was
		}
		selectedOldClassName = oldClassName; 			/// now set the old classname to the newly selected row
		selected = id;										/// the selected row's ID
		identity.className='row on';
		intlink.value = selected;
	}
}

function resize(){
	window.resizeTo(575,625);
	return true;
}
</script>
</head>
<body onload="resize()">
<div id="header">
  <h1>add a link</h1>
</div>
<div id="contentpane">
<?php

	if($_POST){
		switch($linktype){
			case 'int':  /////////////// internal link ////////////////
			if( empty($error_list) && ($intlink>'') ){
				//$link = '/'.getLinkfromGUID($intlink,'page');
				$link = drawLinkByGUID($intlink);
?>
<script type="text/javascript">
						var win = window.opener ? window.opener : window.dialogArguments;
						var tinyMCE = win.tinyMCE;
/*
						if (window.opener) {

							tinyMCE.themes['advanced']._insertLink('<?php echo $link; ?>', '', '', '', '');

							tinyMCE.closeWindow(window);

						}
*/
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?php echo $link;?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;
								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl.substring(linkurl.lastIndexOf('/')+1,linkurl.length);
								window.opener.document.getElementById('target').value = '';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'');
								window.close();
							}
							else {
								tinyMCE.themes['advanced']._insertLink('<?php echo $link; ?>', '', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}
					</script>
<?php
			}else{
	?>
<div id="formwrap">
<?php
		//////// show errors if there are any //////
		show_errors($error_list);
	?>
<h2>Please select a page from this site</h2>
<form id="intsectionform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="int" />
  <input type="hidden" name="submitsection" value="true" />
  <fieldset title="Please choose a page to link to" id="inturl">
  <label for="intsection">Section of the site:</label>
  <?= drawSections($siteID,$lang,$intsection); ?>
  <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
  </fieldset>
</form>
<?
				//echo countPages($intsection);
				/////// if we have a section, we can show the contents... ///////
				//if( ($submitsection=='true' || $submit=='true') && countPages($intsection)>0 ){ 
?>
<form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="int" />
  <input type="hidden" name="submit" value="true" />
  <input type="hidden" name="intlink" id="intlink" value="" />
  <input type="hidden" name="intsection" id="intsection" value="<?php echo $intsection; ?>" />
  <fieldset id="intpages">
  <div id="intpagelist"> <?= drawPages($intsection); ?> </div>
  </fieldset>

  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?
			}
			break;
			case 'anchor'://///////////// anchor link ////////////////
			if( empty($error_list) && ($submit=='true') ){
				$link = '#'.$anchor;
?>
<script type="text/javascript">
						var win = window.opener ? window.opener : window.dialogArguments;
						var tinyMCE = win.tinyMCE;
/*
						if (window.opener) {
							tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '', '', '', '');
							tinyMCE.closeWindow(window);
						}
*/
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?= $link;?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;

								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl;
								window.opener.document.getElementById('target').value = '';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'');
								window.close();
							}
							else {
								tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}

					</script>
<?	}else{	?>
<div id="formwrap">
<h2>Please enter an anchor name</h2>
<form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="anchor" />
  <input type="hidden" name="submit" value="true" />
  <?
					//////// show errors if there are any //////
					show_errors($error_list);
?>
  <fieldset title="Please enter the anchor name to link to" id="exturl">
  <label for="extlink">What is the anchor name you want to link to?</label>
  <span id="extlinkwrap">#
  <input type="text" id="extlink" name="anchor" value="<?= $anchor; ?>" />
  </span>
  </fieldset>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?
			}
			break;
		case 'ext'://///////////// external link ////////////////
			if( empty($error_list) && ($submit=='true') ){
				//$link[] = 'http://'.$extlink;
				$link[] = $extprotocol.$extlink;
				$link[] = $extlink_target;
	?>
<script type="text/javascript">
						var win = window.opener ? window.opener : window.dialogArguments;
						var tinyMCE = win.tinyMCE;				
/*
						if (window.opener) {

							tinyMCE.themes['advanced']._insertLink('<?= $link[0]; ?>', '<?= $link[1]; ?>', '', '', '');

							tinyMCE.closeWindow(window);

						}
*/
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?= $link[0];?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;
								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl;
								window.opener.document.getElementById('target').value = '<?= $link[1];?>';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'<?= $link[1];?>');
								window.close();
							}
							else {
							tinyMCE.themes['advanced']._insertLink('<?= $link[0]; ?>', '<?= $link[1]; ?>', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}


					</script>
<?

			}else{

?>
<div id="formwrap">
<h2>Please select an external website</h2>
<form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="ext" />
  <input type="hidden" name="submit" value="true" />
  <?

					//////// show errors if there are any //////

					show_errors($error_list);

	?>
  <fieldset title="Please enter the address of the website to link to" id="exturl">
  <label for="extlink">What is the address (url) of the site?</label>
  <span id="extlinkwrap">
  	 <select name="extprotocol">
     	<option value="http://">http://</option>
     	<option value="ftp://">ftp://</option>
     	<option value="https://">https://</option>
        <option value="feed://">feed://</option>
     </select>
  <input type="text" id="extlink" name="extlink" value="<?= santise_content($extlink); ?>" />
  </span>
  </fieldset>
  <hr />
  <fieldset id="linkwindow">
  <span>Open link in:</span>
  <label>a new window
  <input type="radio" name="target" value="_blank" checked="checked" />
  </label>
  <label>the current window
  <input type="radio" name="target" value="_parent" />
  </label>
  </fieldset>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?

			}

			break;

		case 'mail':  /////////////// email link ////////////////

			if( empty($error_list) && ($submit=='true') ){

				$link='mailto:';

				if($email_to>''){

					$link .= $email_to ."&lt;{$email_addr}&gt;";

				}else{

					$link .= $email_addr;

				}

				if($email_subject){

					$link .= "?subject={$email_subject}";

				}

				//$link = html_entity_decode($link); /// this is because the < and > symbols caused problems above and were output wrongly here...

				$link = html_entity_decode($link);

?>
<script type="text/javascript">

						var win = window.opener ? window.opener : window.dialogArguments;

						var tinyMCE = win.tinyMCE;

					
/*
						if (window.opener) {

							tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '', '', '', '');

							tinyMCE.closeWindow(window);

						}
*/						
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?= $link;?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;
								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl;
								window.opener.document.getElementById('target').value = '';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'');
								window.close();
							}
							else {
								tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}

					</script>
<?

			}else{

?>
<div id="formwrap">
<h2>Please enter the details of your email link</h2>
<span>All fields marked with a <strong>*</strong> are compulsory</span>
<form id="emaillinkform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="mail" />
  <input type="hidden" name="submit" value="true" />
  <?php

					//////// show errors if there are any //////

					show_errors($error_list);

	?>
  <div id="linktypewrap">
    <fieldset title="Please enter the details of the email link">
    <p class="labelwrap">
      <label for="email_to">Recipient's Name: <strong>*</strong></label>
    </p>
    <input name="email_to" value="<?= $email_to; ?>" />
    <span class="element_info">eg. 'John Doe' or 'AnyCo Ltd'</span><br />
    <p class="labelwrap">
      <label for="email_subject">Message Subject:</label>
    </p>
    <input name="email_subject" value="<?= $email_subject; ?>" />
    <span class="element_info">eg. 'Message from link on xyz.com'</span><br />
    <p class="labelwrap">
      <label for="email_to">Recipient's Email: <strong>*</strong></label>
    </p>
    <input name="email_addr" value="<?= $email_addr; ?>" />
    <span class="element_info">eg. 'someone@adomain.com''</span><br />
    </fieldset>
  </div>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?php	

			}	

			break;

		case 'file':  /////////////// file link ////////////////

		

			if( empty($error_list) && ($intlink>'') ){

				//$link = getLinkfromGUID($intlink,'file'); ////change to suit files
				$link = getLinkbyGUID($intlink,'file'); ////change to suit files 
				if (preg_match("/\/silo\/(.*)\.(\w{3})/", $link, $reg)) {
						$item_file="/silo/".$reg[1].".".$reg[2];
						$ext = substr($item_file,-3,3);
						if ($ext=="mp3" || $ext=="flv") {
							$link="/media-player/?guid=$intlink";
						}
				}
				//print "got link($link)<br>"; exit();
				

?>
<script type="text/javascript">

						var win = window.opener ? window.opener : window.dialogArguments;

						var tinyMCE = win.tinyMCE;

					
					/*
						if (window.opener) {

							tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '_blank', '', '', '');

							tinyMCE.closeWindow(window);

						}
						*/
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?= $link;?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;
								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl.substring(linkurl.lastIndexOf('/')+1,linkurl.length);
								window.opener.document.getElementById('target').value = '_blank';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'_blank');
								window.close();
							}
							else {
								tinyMCE.themes['advanced']._insertLink('<?= $link; ?>', '_blank', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}
					</script>
<?php

			}else{

	?>
<div id="formwrap">
<?php

				//////// show errors if there are any //////

				show_errors($error_list);

			?>
<h2>Please select a file to link to</h2>
<form id="intsectionform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="file" />
  <input type="hidden" name="submitsection" value="true" />
  <fieldset title="Please choose a page to link to" id="inturl">
  <label for="intsection">File categories:</label>
  <?php echo drawFileSections('',$filesection); ?>
  <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
  </fieldset>
</form>
<?php

				/////// if we have a section, we can show the contents... ///////

				if( ($submitsection=='true' || $submit=='true') && countFiles($filesection)>0 ){ 

	?>
<form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="file" />
  <input type="hidden" name="submit" value="true" />
  <input type="hidden" name="intlink" id="intlink" value="" />
  <input type="hidden" name="filesection" id="filesection" value="<?php echo $filesection; ?>" />
  <fieldset id="intpages">
  <table summary="headings for the list of files below">
    <thead>
      <tr>
        <th class="pagename">file name</th>
        <th  class="pagestatus">file type</th>
        <th class="pagevisibility">size</th>
      </tr>
    </thead>
  </table>
  <div id="intpagelist"> <?php echo getFileList($filesection); ?> </div>
  </fieldset>
  </div>
  <?php			} ?>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?php

			}

			break;







		case 'file_shared':  /////////////// shared file link ////////////////

		

			if( empty($error_list) && ($intlink>'') ){

				//$link = getLinkfromGUID($intlink,'file'); ////change to suit files
				$link = getLinkbyGUID($intlink,'file'); ////change to suit files 

?>
<script type="text/javascript">

						var win = window.opener ? window.opener : window.dialogArguments;

						var tinyMCE = win.tinyMCE;

					
					/*
						if (window.opener) {

							tinyMCE.themes['advanced']._insertLink('<?php echo $link; ?>', '_blank', '', '', '');

							tinyMCE.closeWindow(window);

						}
						*/
						if (window.opener) {
							if (window.opener.document.getElementById('link')) {
								var linkurl = '<?php echo $link;?>';
								var imgspace = window.opener.document.getElementById('imgspace').innerHTML;
								
								window.opener.document.getElementById('link').value = linkurl;
								window.opener.document.getElementById('shortlink').value = linkurl.substring(linkurl.lastIndexOf('/')+1,linkurl.length);
								window.opener.document.getElementById('target').value = '_blank';
								window.opener.updateContent(window.opener.document.getElementById('imgtag').value,linkurl,'_blank');
								window.close();
							}
							else {
								tinyMCE.themes['advanced']._insertLink('<?php echo $link; ?>', '_blank', '', '', '');
								tinyMCE.closeWindow(window);
							}
							
						}
					</script>
<?php

			}else{

	?>
<div id="formwrap">
<?php

				//////// show errors if there are any //////

				show_errors($error_list);

			?>
<h2>Please select a shared file to link to</h2>
<form id="intsectionform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="file" />
  <input type="hidden" name="submitsection" value="true" />
  <fieldset title="Please choose a page to link to" id="inturl">
  <label for="intsection">File categories:</label>
  <?= drawFileSections('',$filesection,true); ?>
  <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
  </fieldset>
</form>
<?php

				/////// if we have a section, we can show the contents... ///////

				if( ($submitsection=='true' || $submit=='true') && countFiles($filesection,true)>0 ){ 

	?>
<form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
  <input type="hidden" name="linktype" value="file" />
  <input type="hidden" name="submit" value="true" />
  <input type="hidden" name="intlink" id="intlink" value="" />
  <input type="hidden" name="filesection" id="filesection" value="<?= $filesection; ?>" />
  <fieldset id="intpages">
  <table summary="headings for the list of files below">
    <thead>
      <tr>
        <th class="pagename">file name</th>
        <th  class="pagestatus">file type</th>
        <th class="pagevisibility">size</th>
      </tr>
    </thead>
  </table>
  <div id="intpagelist"> <?= getFileList($filesection,true); ?> </div>
  </fieldset>
  </div>
  <?php			} ?>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous page" />
  </div>
</form>
<?php

			}

			break;









		}

	}elseif(!$_POST){

	?>
<div id="formwrap">
<h2>Please select link type</h2>
<form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <fieldset title="Please select link type" id="linktypewrap">
  <label for="linktype">What type of link?</label>
  <select id="linktype" name="linktype">
    <option value="int" selected="selected">to a page within the site</option>
    <option value="anchor">to an anchor on this page</option>
    <option value="ext">to another website</option>
    <option value="mail">to an email address</option>
    <option value="file">to a file on this site</option>
  </select>
  </fieldset>
  </div>
  <div id="footerlinks">
    <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" />
  </div>
</form>
</div>
<?php

	}else{	///// this case should never be arrived at - just here to catch anything unusual... ////

	?>
<div id="formwrap">There seems to be an error!<br />
  In the unlikely event that you have arrived at this page, please contact the site administration and quote 'link picker logic error'. </div>
<div id="footerlinks"> <a href="javascript:this.close();" class="btn">Close window</a> </div>
<?php

	}

	?>
</div>
</body>
</html>
