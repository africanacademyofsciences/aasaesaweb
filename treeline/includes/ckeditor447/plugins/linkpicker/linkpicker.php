<?php
$DEBUG=false;

session_start();

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

$_SESSION['skiplogin']=0;
$tl_admin = $_SESSION['treeline_user_id']>0;
if (!$tl_admin) $_SESSION['skiplogin']=1;
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");

$imagepicker = "/treeline/includes/ckeditor/plugins/linkpicker/imagepicker.php";

//print "post(".print_r($_POST, true).")<br>\n";
$linktype = read($_POST?$_POST:$_GET,'linktype',''); // Internal, External, File, Mail, Anchor, Shared file
$linktext = str_replace("'", "`", read($_POST, 'linktext', ''));
$linkimage = read($_POST, 'img_filename', '');
$linkprotocol = '';
print "Got linktype($linktype) linktext($linktext) linkimage($linkimage)<br>\n";

if ($linktype=="int") {
    // Not really using these anymore
    $event_type="Link";
    $event_action="Click";
    $event_detail = $link;
}
// Images are sorted in the image picker
else if ($linktype=="image") redirect($imagepicker);
else if ($_POST['addimage']==1) {
	$redirectURL = $imagepicker."?r=".$_POST['linktype'];
	//print "redirect($redirectURL)<br>\n";
	redirect($redirectURL);
}
else if ($linktype=="media") redirect($imagepicker."?action=media");
else if ($linktype=="embed-image") {
    if ($_POST['source']) $linktype=$_POST['source'];

    $linkimagestyle = ($_POST['img_space_top']+0).'px ';
    $linkimagestyle .= ($_POST['img_space_right']+0).'px ';
    $linkimagestyle .= ($_POST['img_space_bottom']+0).'px ';
    $linkimagestyle .= ($_POST['img_space_left']+0).'px';

    $imagepaddingtop = $_POST['img_space_top']+0;
    $imagepaddingright = $_POST['img_space_right']+0;
    $imagepaddingbottom = $_POST['img_space_bottom']+0;
    $imagepaddingleft = $_POST['img_space_left']+0;

    $linkimagealt = str_replace("'", "\'", $_POST['img_alt_text']);
    $linkimagecaption = read($_POST, 'img_caption', '');
    $linkimagecaption = str_replace("'", "`", $linkimagecaption);
    $linkimagecaption = str_replace('"', "`", $linkimagecaption);
    $linkimagecredit = read($_POST, 'img_credit', '');
    $linkimagecreditto = read($_POST, 'img_credit_to', '');
    $linkimagealign = read($_POST, 'img_align', '');
    $linkimagewidth = read($_POST, 'width', '');
    $linkimageclass = "img".substr($linkimagewidth, 0, -1);

    //print "Get image size($linkimage) sz(".print_r($sz, 1).")<br>\n";
    // find out image dimensions...
    if (!$linkimagewidth) {
            if (file_exists($_SERVER['DOCUMENT_ROOT']."/silo/images/".$linkimage)) {
                    $sz = getimagesize($_SERVER['DOCUMENT_ROOT']."/silo/images/".$linkimage);
                    if ($sz[0]>0) {
                            $linkimagewidth = $sz[0]."px";
                            $linkpadding = $imagepaddingtop."px ".$imagepaddingright."px ".$imagepaddingbottom."px ".$imagepaddingleft."px";
                    }
            }
    }
    else {
            $linkpadding = $imagepaddingtop."px ".floor($imagepaddingright/5)."% ".$imagepaddingbottom."px ".floor($imagepaddingleft/5)."%";
    }

    //print "Link image alt($linkimagealt) caption($linkimagecaption) credit($linkimagecredit) to($linkimagecreditto) align($linkimagealign) w($linkimagewidth) c($linkimageclass) p($linkpadding)<br>\n";
}


$prev_btn 		= read($_POST?$_POST:$_GET,'prev_btn','');			// Check if prev pressed
$submit 		= read($_POST,'submit','');				// Check if form submitted
$submitsection	= read($_POST,'submitsection','');		// Check if we chose a file category
$intlink 		= read($_POST,'intlink','');			// Get internal link data
$intsection 	= read($_POST,'intsection',$site->id);	// Get link section

$extprotocol 	= read($_POST,'extprotocol', '');		// Get external link url
$extlink 		= read($_POST,'extlink','');			// Get external link text
$extlink_target	= read($_POST,'target','');				// Get external link target

$email_to 		= read($_POST,'email_to','');			// Get email to name
$email_addr 	= read($_POST,'email_addr','');			// Get email to address
$email_subject	= read($_POST,'email_subject','');		// Get email subject
$anchor 		= read($_POST,'anchor','');				// Get name of anchor to link to
$category		= read($_POST,'category','');			// Get file category
$filesection 	= read($_POST,'filesection','');		// Get file section
$filesubsection = read($_POST,'filesubsection','');		// Get file subsection
$donate_amt		= read($_POST,'donate',0);


// If the previous button was pressed go back to the beginning
if($prev_btn){
	unset($_POST);
	$prevlink = "location: linkpicker.php?linktype=".$linktype;
	header($prevlink);
}


if ($category!=$filesection) $filesubsection='';
//print "file cat($filesection) subcat($filesubsection)<br>";

// Crappy fix for apostrophes in email links
$email_to = str_replace("'", "`", $email_to);
$email_subject = str_replace("'", "`", $email_subject);


if ($linktype=="link") $asset_title = "Add a link";
else if ($linktype=="slide") $asset_title = "Add a slideshow";
else if ($linktype=="embed-image") $asset_title = "Embed your image";
else $asset_title = "What do you want to do?";

// Do some checking and make sure we have all the required valid information
// to continue to the next stage of the process
// if not save the errors to show later.
$error_list = $warning_list = array();

//print "Got linktype($linktype) linktext($linktext) linkimage($linkimage)<br>\n";

if ($linktype && $linktype!="link" && 
	$linktype != "embed-image" && 
	$linktype != "icon" &&
	!$linktext && 
	!$linkimage) {
		// Probably an error
		if ($linktype=="slide") $warning_list[] = "You need to enter some text or an image to open your slideshow";
		else $error_list[] = "You must enter some text or an image to link to";
		//print "Link error go back?<br>\n";
		$linktype = "link"; // Go back
	//}
}
else if($linktype=='ext' && $submit=='true'){
	if(!$extlink){ 
		$error_list[] = "You need to enter a web address!"; 
	}
	else if (!preg_match('/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', santise_content($extlink), $m)) {
		$error_list[] = "Your web address does not appear to be valid!"; 
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}

// Anchor
else if($linktype=='ank' && $submit=='true'){
	if(!$extlink){ 
		$error_list[] = "You need to enter a web address!"; 
	}
	else if (!preg_match('/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', santise_content($extlink), $m)) {
		$error_list[] = "Your web address does not appear to be valid!"; 
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}

// Link parameter checking
else if($linktype=='int') {
	// Make sure we have selected or added some text for our link
	if ($submitsection=='true' ){
		if(countPages($intsection)<=0){
			$error_list[] = "This section does not appear to have any pages to link to!";
		}
	}
}
else if( $linktype=='int' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='anchor' && $submit=='true' ){
	if( empty($anchor) ){
		$error_list[] = "You need to specify an anchor to link to!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='mail' && $submit=='true' ){
	if(!$email_to){
		$error_list[] = "You have to enter the Recipient's Name!";
	}
	if(!$email_addr){
		$error_list[] = "You have to enter an email address!";
	}else if( !is_email($email_addr) ){
		$error_list[] = "Your email address does not appear to be valid!";
	}
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='file' && $submitsection=='true' ){
	if(countFiles($filesection)<=0){
		$error_list[] = "This section does not appear to have any pages to link to!";
	}
}
else if( $linktype=='file_shared' && $submitsection=='true' ){
	if(countFiles($filesection, true)<=0){
		$error_list[] = "This section does not appear to have any pages to link to!";
	}
}
else if( $linktype=='file' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}		
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='file_shared' && $submit=='true' ){
	if( empty($intlink) ){
		$error_list[] = "You need to specify a page to link to!";
	}		
	else if (!$linktext) $error_list[]="You have not specified any link text";
}
else if( $linktype=='slide' && $submitsection=='true' && !$intlink){
	$error_list[] = "You must select a slideshow!";
}
else if ($linktype=="icon") {
	$linkprotocol = "fa";
	$linktext = $_GET['name'];
}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Treeline: Link picker</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/tlembed.css');
@import url('/treeline/style/linkPicker.css');
@import url('/treeline/style/iconPicker.css');
@import url('/style/font-awesome.min.css');

iframe.cke_dialog_ui_iframe {
    height: 450px;
}
</style>

<script type="text/javascript">
var selected;
var selectedOldClassName;

var editor;
var linkform;
var linktext = "<?=$linktext?>"; 
var inserted = false;

if (!linktext){
    var mySelection = CKEDITOR.editor.getSelection();

    if (CKEDITOR.env.ie) {
        mySelection.unlock(true);
        selectedText = mySelection.getNative().createRange().text;
    } else {
        selectedText = mySelection.getNative();
    }
    linktext = selectedText;
}
alert ("glot linktext "+linktext);
function str_replace(c, nc, s) {
    //alert("replace("+c+") with("+nc+") in("+s+")");
    news = s.replace(new RegExp(c, 'g'), nc);    
    //alert("created news("+news+")")
    return news;
}
function change(id, oldClass, linktext, linkhref, filename) {
	//alert ("c("+id+", "+oldClass+", "+linktext+", "+linkhref+")");
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
	}
	else{
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
	
	if (linkhref) {
		linkform.linktext.value = str_replace('~', '"', linktext);
		linkform.linkhref.value = linkhref;
		linkform.filename.value = filename;
		//alert("set link to("+linkhref+") text("+linktext+") actual text("+linkform.linktext.value+")");
		showOkBtn();
	}
}

function showOkBtn() {
	parent.showOkButton(1);
	//alert("sOB()");
}
function hideOkBtn() {
	parent.showOkButton(0);
	//alert("hOB");
}


function setSlideshow() {
	var f = document.getElementById("intsectionform");
	slideshow = f.intlink[f.intlink.selectedIndex].value;
	//alert ("sS("+slideshow+") f("+f+")");
	//slidetype = f.slidetype.value;
	if (slideshow>0) {
		//alert(); 
		if (f.slidetype.value=="light") setHrefLink('@@LINK-GALLERY-'+slideshow+'@@');
		else if (f.slidetype.value=="mobile") setHrefLink("@@MOBILE-GALLERY-"+slideshow+"@@");
		showOkBtn();	
	}
	else hideOkBtn();
}

function setExternal() {
	var f = document.getElementById("linktypeform");
	//alert ("sE(new"+f.extnew.checked+", self"+f.extself.checked+")");
	if (f.extnew.checked==true) linkform.linktarget.value="_blank";
	else linkform.linktarget.value="";
	linkform.linkprotocol.value = f.extprotocol[f.extprotocol.selectedIndex].value;
	linkform.linkhref.value = f.extlink.value;
	//alert ("ref("+linkform.linkhref.value+") col("+linkform.linkprotocol.value+") tar("+linkform.linktarget.value+")");
	if (linkform.linkhref.value) showOkBtn();
}

function setAnchor() {
	var f = document.getElementById("linktypeform");
	//alert ("sE(new"+f.extnew.checked+", self"+f.extself.checked+")");
	linkform.linkhref.value = '#'+f.extlink.value;
	//alert ("ref("+linkform.linkhref.value+") col("+linkform.linkprotocol.value+") tar("+linkform.linktarget.value+")");
	if (linkform.linkhref.value) showOkBtn();
}

function setDonate() {
	var f = document.getElementById("linktypeform");
	//alert ("donate("+f.donate.value+")");
	if ((f.donate.value+0)>0) {
		linkform.linkhref.value = "/shop/shopping-basket/?donation="+f.donate.value;
		showOkBtn();
	}
	else hideOkBtn();
}

function setEmail() {
	var f = document.getElementById("emaillinkform");
	linkform.emlname.value = f.email_to.value;
	linkform.emlsubject.value = f.email_subject.value;
	linkform.emlemail.value = f.email_addr.value;
	if (linkform.emlname.value && linkform.emlemail.value) showOkBtn();
	else hideOkBtn();
}

function setHrefImage(img_filename, img_alt, img_style, img_caption, img_credit, img_credit_to, img_align, img_width, img_height) {
	//alert("set image to("+img_filename+") alt("+img_alt+") style("+img_style+")");
	//alert("set image cap("+img_caption+") cred("+img_credit+") to("+img_credit_to+") align("+img_align+")");
	linkform.img_file.value = img_filename;
	linkform.img_alt.value = img_alt;
	linkform.img_style.value = img_style;
	linkform.img_align.value = img_align;
	linkform.img_caption.value = img_caption;
	linkform.img_credit.value = img_credit;
	linkform.img_credit_to.value = img_credit_to;
	linkform.img_width.value = img_width;
	linkform.img_height.value = img_height;
}

function setHrefLink(linkhref, linktext) {
	//alert("set link to("+linkhref+") text("+linktext+")");
	if (linktext) linkform.linktext.value = linktext;
	if (linkhref) linkform.linkhref.value = linkhref;
}


editor.on( 'dialogShow', function( dialogShowEvent )
{
    alert("dialogShow");
});

function onDialogEvent(e) {
	alert("dialog event("+e+") n("+e.name+")");
	if (e.name=="load") {
		editor = e.editor;
		var linktext;
		
		//linktext = editor.getSelection().getNative();
		//alert("load with gNtext("+linktext+")");
		linktext = editor.getSelection().getSelectedText();
		alert("load with gsttext("+linktext+")");
		//if (!linktext) {
			//linkhtml = editor.getSelection().getStartElement().getOuterHtml()
			//alert("load with gsetext("+linktext+")");
		//}

		if (!linktext) {
			// This sometimes works as a way to get the image html
			var element = editor.getSelection().getSelectedElement()
			//alert (element);
			if (element) {
				linktext = element.getHtml();
				//alert("load with gsttext("+linktext+")");
				if (!linktext) {
					linktext = element.getOuterHtml();
					//alert("load with getOuterHtml("+linktext+")");
				}
			}
			else {
				//alert ("Nothing selected");
			}
		}
		//alert("load with linktext("+linktext+")");

		//alert ("text("+seltext+")");
		if (linktext!='') {
			linkform.linktext.value = linktext;
			if (document.getElementById("linktypeform")) {
				//alert ("set linktypeform linktext value to(1"+linktext+"1)");
				document.getElementById("linktypeform").linktext.value=linktext;
			}
			var f;
			f = document.getElementById("intlinkform");
			if(f) f.linktext.value=linktext;
			f = document.getElementById("extlinkform");
			if(f) f.linktext.value=linktext;
			f = document.getElementById("anklinkform");
			if(f) f.linktext.value=linktext;
			f = document.getElementById("maillinkform");
			if (f) f.linktext.value=linktext;
			f = document.getElementById("donatelinkform");
			if (f) f.linktext.value=linktext;
			f = document.getElementById("filelinkform");
			if (f) f.linktext.value=linktext;
			f = document.getElementById("fileslinkform");
			if (f) f.linktext.value=linktext;
			f = document.getElementById("slidelinkform");
			if (f) f.linktext.value=linktext;
		}
	}
	//alert ("ok pressed");
	if (e.name=="ok") {
		if (!inserted) {
			var llink;
			var limage;
			var ltarget = linkform.linktarget.value;
			var lprotocol = linkform.linkprotocol.value;
			var lfilename = linkform.filename.value;

			var caption =  linkform.img_caption.value;
			var credit = linkform.img_credit.value;
			
			if (linkform.img_file.value) {
				limage = '<img src="/silo/images/'+linkform.img_file.value+'" ';
				limage = limage + 'alt="'+linkform.img_alt.value+'" '
				if (credit>0 || caption) {
					limage = limage + 'width="100%" ';
				}
				else {
					//limage = limage + 'width="<?=$linkimagewidth?>" ';
					limage = limage + 'class="<?=$linkimageclass?>" ';
					limage = limage + 'style="padding:'+linkform.img_style.value+'" '
				}
				limage = limage + '/>';
			}
				
			if (linkform.linkhref.value) {
				llink = '<a href="';
				if (lprotocol) llink = llink + lprotocol;
				llink = llink + linkform.linkhref.value+'" ';
				if (ltarget) llink = llink + 'target="'+ltarget+'" ';
                                //if (lfilename) alert(lfilename);
                                //if (lfilename=="undefined") alert("filename is not defined");
				if (lfilename && lfilename!="undefined") llink = llink + 'onClick="_gaq.push([\'_trackEvent\', \'Files\', \'Download\', \''+lfilename+'\']);"';
				llink = llink + '>';
				if (limage) {
					// Do we need to create an image block for this link?
					var html = llink + limage + '<\/a>';

					var to = linkform.img_credit_to.value;
					//alert("caption("+caption+") credit("+credit+") to("+to+")");
					if (caption || credit>0) {
						//if (caption) alert ("caption is valid");
						//if (credit) alert("credit is valid");
						html = "<div class=\"img_block <?=$linkimageclass?> img_"+(linkform.img_align.value)+"\" style=\"width:<?=($linkimagewidth)?>;padding:<?=($linkpadding)?>;\" >"+html;
						//html = html + limage;
						if (credit && to) html = html + "<p class=\"credit\">Credit: "+to+"<\/p>";
						if (caption) html = html + "<p class=\"caption\">"+caption+"<\/p>";
						//html = html + "<hr style=\"margin: 0 <?=($imagepaddingright+0)?>px 0 <?=($imagepaddingleft+0)?>px;\" />";
						html = html + "<\/div>";
						llink = html;
					}
					else {
						llink = llink + limage + '<\/a>';
					}
				}
				else llink = llink + linkform.linktext.value + '<\/a>';
				//alert ("insert link("+llink+")");
				editor.insertHtml(llink);
			}
			else if (linkform.emlemail.value) {	
				llink = '<a href="mailto:'+linkform.emlname.value+'<'+linkform.emlemail.value+'>';
				if (linkform.emlsubject.value) llink = llink + "?subject="+linkform.emlsubject.value;
				llink = llink + '">';
				if (limage) llink = llink + limage;
				else llink = llink + linkform.linktext.value;
				llink = llink +'<\/a>';
				//alert(llink);
				editor.insertHtml(llink);
			}
			else if (limage) {
				var html = limage;
				var caption =  linkform.img_caption.value;
				var credit = linkform.img_credit.value;
				var to = linkform.img_credit_to.value;
				//alert ("insert image("+limage+")");
				//alert("caption("+caption+") credit("+credit+") to("+to+")");
				if (caption || credit>0) {
					html = "<div class=\"img_block <?=$linkimageclass?> img_"+(linkform.img_align.value)+"\" style=\"width:<?=($linkimagewidth)?>;padding:<?=($linkpadding)?>;\" >"+html;
                                        if (credit>0 && to) html = html + "<p class=\"credit\">Credit: "+to+"<\/p>";
                                        if (caption) html = html + "<p class=\"caption\">"+caption+"<\/p>";
					html = html + "<\/div>";
				}
				alert(html);
				editor.insertHtml(html);
			}
			else if (lprotocol=="fa") {
				var html = '<i class="fa <?=$_GET['name']?>">&nbsp;</i>';
				//alert(html);
				editor.insertHtml(html);
			}
			else {
				return false;
			}
			inserted=true;
			return true;
		}
	}
}

hideOkBtn();

</script>
</head>

<body>
<div id="header">
  <h1><?=$asset_title?></h1>
</div>

<div id="contentpane">

<form method="post" id="linkform" action="">
	<input type="hidden" name="linkhref" value="" />
	<input type="hidden" name="linktext" value='<?=$linktext?>' />
    <input type="hidden" name="img_file" value="" />
    <input type="hidden" name="img_style" value="" />
    <input type="hidden" name="img_alt" value="" />
    <input type="hidden" name="img_caption" value="" />
    <input type="hidden" name="img_credit" value="" />
    <input type="hidden" name="img_credit_to" value="" />
    <input type="hidden" name="img_width" value="" />
    <input type="hidden" name="img_height" value="" />
    <input type="hidden" name="img_align" value="" />
    <input type="hidden" name="linktarget" value="" />
    <input type="hidden" name="linkprotocol" value="<?=$linkprotocol?>" />
    <input type="hidden" name="filename" value="" />
    <input type="hidden" name="emlname" value="" />
    <input type="hidden" name="emlsubject" value="" />
    <input type="hidden" name="emlemail" value="" />
</form>

<script type="text/javascript">
	linkform = document.getElementById("linkform");
	// linkform.linktext.value = '<?=addslashes($linktext)?>';
	linkform.linktext.value = '<?=$linktext?>';
	setHrefImage('<?=$linkimage?>', '<?=$linkimagealt?>', '<?=$linkimagestyle?>', '<?=$linkimagecaption?>', '<?=$linkimagecredit?>', '<?=$linkimagecreditto?>', '<?=$linkimagealign?>', '<?=$linkimagewidth?>', '<?=$linkimageheight?>');
	
</script>


<?php
if ($linktext) {
	//$_SESSION['linktext'] = addslashes($linktext);
	//$_SESSION['linktext'] = addslashes($linktext);
	//print "Set linktext to ($linktext)<Br>\n";
}

//print "rm(".$_SERVER['REQUEST_METHOD'].") type($linktype)<br>\n";
if($_POST || $linktype=="icon"){

	switch($linktype){

		// *******************************************
		// internal link 
		// *******************************************
		case 'int': 
		
			?>
            <div id="formwrap">
            <?=show_errors($error_list, $warning_list)?>
            <h2>Please select a page from this site</h2>
            <form id="intsectionform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="newlink" />
                <input type="hidden" name="linktext" value='<?=$linktext?>' />
                <input type="hidden" name="linktype" value="int" />
                <input type="hidden" name="img_filename" value='<?=$linkimage?>' />
                <input type="hidden" name="img_style" value='<?=$linkimagestyle?>' />
                <input type="hidden" name="img_alt_text" value='<?=$linkimagealt?>' />
                <input type="hidden" name="submitsection" value="true" />
                <fieldset title="Please choose a page to link to" id="inturl">
                <label for="intsection">Section of the site:</label>
                <?= drawSections($site->id,'',$intsection); ?>
                <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
                </fieldset>
            </form>

            <form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="linktype" value="int" />
                <?php 
				if ($linktext) { 
					?> 
                    <input type="hidden" name="linktext" value='<?=$linktext?>' />  
					<?php 
				} 
				?>
                <input type="hidden" name="submit" value="true" />
                <input type="hidden" name="intlink" id="intlink" value="" />
                <input type="hidden" name="intsection" id="intsection" value="<?php echo $intsection; ?>" />

                <fieldset id="intpages">
                    <div id="intpagelist"> 
                        <?=drawLPPages($intsection); ?> 
                    </div>
				</fieldset>
                
                <fieldset>
                    <div id="footerlinks">
                    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
                    </div>
                    
                </fieldset>
                
            </form>
            </div>
			<?
			break;
		// *******************************************
			

	
		// *******************************************
		/////////////// external link ////////////////
		// *******************************************
		case 'ext':
			?>
            <div id="formwrap">
            <h2>Please select an external website</h2>
            <form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="linktype" value="ext" />
                <input type="hidden" name="linktext" value="<?=$linktext?>" />
	            <?=show_errors($error_list, $warning_list)?>

                <fieldset title="Please enter the address of the website to link to" id="exturl">
                    <label for="extlink">URL:</label>
                    <input type="text" id="extlink" class="text" name="extlink" onkeyup="javascript:setExternal();" value="<?= santise_content($extlink); ?>" />
                </fieldset>
                
                <fieldset>
                    <label for="extprotocol">Protocol:</label>
                    <select name="extprotocol" onchange="javascript:setExternal();">
                        <option value="http://">http://</option>
                        <option value="ftp://">ftp://</option>
                        <option value="https://">https://</option>
                        <option value="feed://">feed://</option>
                    </select>
                </fieldset>

                <fieldset id="linkwindow">
                    <label>Open link in:</label>
                    <div class="newwindow">
                        <fieldset class="radiobox">
                            <input type="radio" id="extnew" name="target" value="new" checked="checked" onclick="javascript:setExternal();" />
                            <label>a new window</label>
                        </fieldset>
                        <fieldset class="radiobox">
                            <input type="radio" id="extself" name="target" value="" onclick="javascript:setExternal();" />
                            <label>the current window</label>
                        </fieldset>
                    </div>
                </fieldset>

                <div id="footerlinks">
                    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
					<!-- <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next" /> -->
                </div>
            </form>
            </div>
            <?php
			break;
		// *******************************************


		// *******************************************
		/////////////// anchor link ////////////////
		// *******************************************
		case 'ank':
			?>
            <div id="formwrap">
            <h2>Please select an anchor on this page</h2>
            <form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="linktype" value="ank" />
                <input type="hidden" name="linktext" value="<?=$linktext?>" />
	            <?=show_errors($error_list, $warning_list)?>

                <fieldset title="Please enter the name of the anchor you wish to link to" id="exturl">
                    <label for="extlink">Name:</label>
                    <input type="text" id="extlink" class="text" name="extlink" onkeyup="javascript:setAnchor();" value="<?= santise_content($extlink); ?>" />
                </fieldset>

                <div id="footerlinks">
                    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
					<!-- <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next" /> -->
                </div>
            </form>
            </div>
            <?php
			break;
		// *******************************************
			
			

		// *******************************************
		/////////////// embed an image////////////////
		// *******************************************
		case "embed-image": 
			//print "Posty(".print_r($_POST, true).")<br>\n";
			?>
            <p>Image selected, press ok to add this image to your page.</p>
            <img src="/silo/images/<?=$linkimage?>" alt="<?=$linkimagealt?>" style="<?=$linkimagestyle?>" />
            <script type="text/javascript">
			showOkBtn();
			</script>
            <?php
			break;
			
		// *******************************************
		/////////////// embed an icon////////////////
		// *******************************************
		case "icon": 
			//print "Getty(".print_r($_GET, true).")<br>\n";
			?>
            <p>Icon selected, press ok to add this icon to your page.</p>
            <i class="fa <?=$_GET['name']?>"></i>
            <script type="text/javascript">
			showOkBtn();
			</script>
            <?php
			break;
			
			
		// *******************************************
		/////////////// email link ////////////////
		// *******************************************
		case 'mail':  

			?>
			<div id="formwrap">
				<h2>Please enter the details of your email link</h2>
				<p>All fields marked with a <strong>*</strong> are compulsory</p>
				<form id="emaillinkform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
				<input type="hidden" name="linktype" value="mail" />
				<input type="hidden" name="newlink" value="" />
				<?php if ($linktext) { ?> <input type="hidden" name="linktext" value='<?=$linktext?>' />  <?php } ?>
				<input type="hidden" name="submit" value="true" />
				<?=show_errors($error_list, $warning_list)?>
				<div id="linktypewrap">
					<fieldset title="Please enter the details of the email link">
                    	<fieldset>
							<label for="email_to">Recipient's Name: <strong>*</strong></label>
							<input name="email_to" onkeyup="javascript:setEmail();" value="<?= $email_to; ?>" />
                        </fieldset>
                    	<fieldset>
							<label for="email_subject">Message Subject:</label>
							<input name="email_subject" value="<?= $email_subject; ?>" />
                        </fieldset>
                    	<fieldset>
							<label for="email_to">Recipient's Email: <strong>*</strong></label>
							<input name="email_addr" onkeyup="javascript:setEmail();" value="<?= $email_addr; ?>" />
                        </fieldset>
					</fieldset>
				</div>

				<div id="footerlinks">
					<input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
				</div>
				</form>
			</div>
			<?php	

			break;
		// *******************************************
		
		
		// *******************************************
		/////////////// donation link ////////////////
		// *******************************************
		case 'donate':
			?>
			<div id="formwrap">
				<h2>Please enter a donation amount</h2>
				<form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<input type="hidden" name="linktype" value="donate" />
					<input type="hidden" name="newlink" value="" />
					<?php if ($linktext) { ?> <input type="hidden" name="linktext" value='<?=$linktext?>' />  <?php } ?>
					<input type="hidden" name="submit" value="true" />
					<?=show_errors($error_list, $warning_list)?>
					<fieldset title="Please enter the amount to donate">
                    	<p>This amount will appear prefilled on the donation form</p>
						<label for="extlink">Amount &pound;</label>
						<input type="text" class="text" onkeyup="javascript:setDonate();" id="f_donate" name="donate" value="<?=$donate;?>" />
					</fieldset>
					<hr />
					<div id="footerlinks">
						<input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
					</div>
				</form>
			</div>
			<?php
			break;
		// *******************************************

		
		// *******************************************
		/////////////// file link ////////////////
		// *******************************************
		case 'file':  

			if( empty($error_list) && ($intlink>'') ){

				$target='target="_blank"';
				$ext='';
				
				//$link = getLinkbyGUID($intlink,'file'); ////change to suit files 
				$link = "/download/".$intlink;
				
				// Turn off attempts to load the multimedia page.
				if (USE_A_MULTIMEDIA_PAGE && 0) {
					if (preg_match("/\/silo\/(.*)\.(\w{3})/", $link, $reg)) {
						$item_file="/silo/".$reg[1].".".$reg[2];
						$ext = substr($item_file,-3,3);
						if ($ext=="mp3" || $ext=="flv") {
							$link="/media-player/?guid=$intlink";
							$target='';
						}
					}
				}
				
				//print "got link($link)<br>";
				$fulllink='<a href="'.$link.'" '.$target.'>'.str_replace("<", "&lt;", urldecode($linktext)).'</a>';
				?>
                    <form>
                    <input type="hidden" name="newlink" />
                    <input type="hidden" name="linktext" value='<?=$fulllink?>' />
                    </form>
					<script type="text/javascript">NewLinkDialog.insert();</script>
                <?php
			}
			else {
				?>
				<div id="formwrap">
	            <?=show_errors($error_list, $warning_list)?>
                <h2>Please select a file to link to</h2>
                <form id="intsectionform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="linktype" value="file" />
                <input type="hidden" name="linktext" value="<?=$linktext?>" />
                <input type="hidden" name="newlink" value="" />

                <input type="hidden" name="img_filename" value='<?=$linkimage?>' />
                <input type="hidden" name="img_style" value='<?=$linkimagestyle?>' />
                <input type="hidden" name="img_alt_text" value='<?=$linkimagealt?>' />

                <input type="hidden" name="submitsection" value="true" />
                <input type="hidden" name="category" value="<?=$filesection?>" />
                <fieldset title="Please choose a page to link to" id="inturl">
                    <label for="intsection">File category:</label>
                    <?php echo drawFileSections('',$filesection); ?>
                    <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
                </fieldset>
                <?php if ($filesection>0) { ?>
                    <fieldset title="Please choose a page to link to" id="inturl">
                        <label for="intsubsection">File subcategory :</label>
                        <?php echo drawFileSubSections('',$filesection, $filesubsection); ?>
                        <input type="submit" class="btn" name="intsection_btn" id="intsection_btn_1" value="OK" />
                    </fieldset>
                <?php } ?>
                </form>

				<?php
				/////// if we have a section, we can show the contents... ///////
				if( ($submitsection=='true' || $submit=='true') && countFiles($filesection, false, $filesubsection)>0 ){ 
				?>
                    <form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" name="linktype" value="file" />
	                <input type="hidden" name="newlink" value="" />
					<?php if ($linktext) { ?> <input type="hidden" name="linktext" value='<?=$linktext?>' />  <?php } ?>
                    <input type="hidden" name="submit" value="true" />
                    <input type="hidden" name="intlink" id="intlink" value="" />
                    <input type="hidden" name="filesection" id="filesection" value="<?php echo $filesection; ?>" />
                    <fieldset id="intpages">
                        <table summary="headings for the list of files below" border="0" cellpadding="0" cellspacing="0">
                        <thead>
                        <tr>
                            <th class="pagename">file name</th>
                            <th  class="pagestatus">file type</th>
                            <th class="pagevisibility">size</th>
                        </tr>
                        </thead>
                        </table>
                        <div id="intpagelist" style="height:300px;"> <?php echo getFileList($filesection,false,$filesubsection); ?> </div>
                    </fieldset>
                    
                    <div id="footerlinks">
                        <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
                    </div>
                    </form>

                <?php } ?>
                </div>
                <?php
			}
			break;
		// *******************************************




		// *******************************************
		// Shared file link 
		// *******************************************
		case 'file_shared':  

		
			if( empty($error_list) && ($intlink>'') ){

				$target='target="_blank"';
				
				//$link = getLinkbyGUID($intlink,'file'); ////change to suit files 
				$link = "/download/".$intlink;
				
				// Turn off attempts to load the multimedia page.
				if (USE_A_MULTIMEDIA_PAGE && 0) {
					if (preg_match("/\/silo\/(.*)\.(\w{3})/", $link, $reg)) {
						$item_file="/silo/".$reg[1].".".$reg[2];
						$ext = substr($item_file,-3,3);
						if ($ext=="mp3" || $ext=="flv") {
							$link="/media-player/?guid=$intlink";
							$target='';
						}
					}
				}
				
				$fulllink='<a href="'.$link.'" '.$target.'>'.str_replace("<", "&lt;", urldecode($linktext)).'</a>';
				//print "got link($fulllink)<br>";
				?>
                    <form>
                    <input type="hidden" name="newlink" />
                    <input type="hidden" name="linktext" value='<?=$fulllink?>' />
                    </form>
					<script type="text/javascript">NewLinkDialog.insert();</script> 
                <?php
				   
			} else { 
				
				?> 
                <div id="formwrap"> 
	            <?=show_errors($error_list, $warning_list)?>
                <h2>Please select a shared file to link to</h2>
                <form id="intsectionform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" name="linktype" value="file_shared" />
	                <input type="hidden" name="newlink" value="" />
					<?php if ($linktext) { ?> <input type="hidden" name="linktext" value='<?=$linktext?>' />  <?php } ?>
                    
                <input type="hidden" name="img_filename" value='<?=$linkimage?>' />
                <input type="hidden" name="img_style" value='<?=$linkimagestyle?>' />
                <input type="hidden" name="img_alt_text" value='<?=$linkimagealt?>' />

                    <input type="hidden" name="submitsection" value="true" />
	                <input type="hidden" name="category" value="<?=$filesection?>" />
                    <fieldset title="Please choose a page to link to" id="inturl">
                    <label for="intsection">File categories:</label>
                    <?= drawFileSections('',$filesection,true); ?>
                    <input type="submit" class="btn" name="intsection_btn" id="intsection_btn" value="OK" />
                    </fieldset>
	                <?php if ($filesection>0) { ?>
                        <fieldset title="Please choose a page to link to" id="inturl">
                            <label for="intsubsection">File subcategory :</label>
                            <?php echo drawFileSubSections('',$filesection, $filesubsection, true); ?>
                            <input type="submit" class="btn" name="intsection_btn" id="intsection_btn_1" value="OK" />
                        </fieldset>
                    <?php } ?>
                </form>

				<?php
				if( ($submitsection=='true' || $submit=='true') && countFiles($filesection, true, $filesubsection)>0 ){ 
					?>
                    <form id="linktypeform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                        <input type="hidden" name="linktype" value="file_shared" />
		                <input type="hidden" name="newlink" value="" />
						<?php if ($linktext) { ?> <input type="hidden" name="linktext" value='<?=$linktext?>' />  <?php } ?>
                        <input type="hidden" name="submit" value="true" />
                        <input type="hidden" name="intlink" id="intlink" value="" />
                        <input type="hidden" name="filesection" id="filesection" value="<?= $filesection; ?>" />
                        <fieldset id="intpages">
                            <table summary="headings for the list of files below" border="0" cellpadding="0" cellspacing="0">
                            <thead>
                              <tr>
                                <th class="pagename">file name</th>
                                <th  class="pagestatus">file type</th>
                                <th class="pagevisibility">size</th>
                              </tr>
                            </thead>
                            </table>
                            <div id="intpagelist" style="height:300px;"> <?= getFileList($filesection,true,$filesubsection); ?> </div>
	                    </fieldset>

                        <div id="footerlinks">
                            <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next" />
                            <input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Previous" />
                        </div>
                    </form>
					<?php 
				}
			}
			break;
		// *******************************************
		
		// *******************************************
		// Link to a slideshow
		// *******************************************
		case 'slide':  
			$slidehtml = drawSlideshows();
			?>
			<form id="intsectionform" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
				<div id="formwrap">
		            <?=show_errors($error_list, $warning_list)?>
					<h2>Please select a slideshow to link to</h2>
					<input type="hidden" name="linktype" value="slide" />
					<input type="hidden" name="linktext" value='<?=$linktext?>' />
					<?php
					if ($linktext) {
						?>
                        <p>Link slideshow to text: <strong><?=$linktext?></strong></p>
                        <?php
					}
					else if ($linkimage) {
						?>
                        <p>Link slideshow to image: <img src="/silo/images/<?=$linkimage?>" /></p>
                        <?php
					}
					
					if ($slidehtml) {
						?>
						<fieldset title="Please choose a slideshow to link to" id="inturl">
							<label for="intsection">Slideshows:</label>
							<select name="intlink" onChange="setSlideshow();">
								<option value="0">Select slideshow</option>
								<?=$slidehtml?>
							</select>
						</fieldset>

						<fieldset id="f_slidetype">
							<label for="f_slidetype">Slideshow type:</label>
							<select name="slidetype" onChange="setSlideshow();">
								<option value="light">Lightbox</option>
								<option value="mobile">Responsive</option>
							</select>
						</fieldset>
						<?php
					}
					else {
						?>
						<p>No slideshows have been configured on this site yet</p>
						<?php
					}
					?>
				</div>
				<div id="footerlinks">
					<!-- <input type="submit" name="next_btn" id="next_btn" class="btn" value="Next page" /> -->
					<input type="submit" name="prev_btn" id="prev_btn" class="btn" value="Restart" />
				</div>
			</form>
			<?php
			break;


		// *******************************************
		// Create a link choices
		// *******************************************
		case "link": 
			
			if ($_POST['linktype']!="slide") {
				?>
    	        <h2>Please select link type</h2>
                <?php
			}
			?>
            <form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            	<div id="formwrap">
		            <?=show_errors($error_list, $warning_list)?>
                    <input type="hidden" name="linktext" value="<?=$linktext?>" />
                    <input type="hidden" name="newlink" value='' />
                    <input type="hidden" name="img_filename" value="<?=$linkimage?>" />
                    <input type="hidden" name="img_style" value="<?=$linkimagestyle?>" />
                    <input type="hidden" name="img_alt_text" value="<?=$linkimagealt?>" />
                    <fieldset title="Please select link type" id="linktypewrap">
                        <label for="linktype">What type of link?</label>
                        <select id="linktype" name="linktype">
                        <option value="int">to a page within the site</option>
                        <option value="ext" <?=($_POST['linktype']=="ext"?'selected="selected"':'')?>>to another website</option>
                        <option value="mail" <?=($_POST['linktype']=="mail"?'selected="selected"':'')?>>to an email address</option>
                        <option value="donate" <?=($_POST['linktype']=="donate"?'selected="selected"':'')?>>to the donate form</option>
						<option value="slide" <?=($_POST['linktype']=="slide"?'selected="selected"':'')?>>to a slideshow</option>
                        <option value="file" <?=($_POST['linktype']=="file"?'selected="selected"':'')?>>to a file on this site</option>
                        <option value="file_shared" <?=($_POST['linktype']=="file_shared"?'selected="selected"':'')?>>to a shared file</option>
                        </select>
                    </fieldset>
    
                    <?php
                    if (!$linktext && !$linkimage) { 
                        ?>
                        <fieldset>
                            <label for="f_linktext">Link text</label>
                            <input type="text" class="text" name="linktext" id="f_linktext" />
                        </fieldset>
                        <fieldset>
                            <label for="f_addimage">Link to an image</label>
                            <input type="checkbox" class="checkbox" name="addimage" id="f_addimage" value="1" />
                            <label for="f_addimageinfo" class="wide">Check this box if you would like to select an image for your link</label>
                        </fieldset>
                        <!-- <p><a href="<?=$imagepicker?>?r=<?=$_POST['linktype']?>">Or choose an image for this <?=($_POST['linktype']=="slide"?"slideshow":"link")?></a></p> -->
                        <?php 
                    } 
                    ?>
                    
                    <div id="footerlinks">
                    <input type="submit" name="prev_btn" id="prev_btn" class="btn" value='Restart' />
                    <input type="submit" name="next_btn" id="next_btn" class="btn" value='Next' />
                    </div>
                </div>
            </form>
            <?php
			break;
				

		} // End of switch
	} // End of if(POST)
	

	// **********************************************
	// FIRST VISIT FIND OUT WHAT TYPE OF LINK NEEDED
	// **********************************************
	else if(!$_POST){
		?>
        <div id="formwrap">

            <ul id="main-menu">
	            <li class="title first"><h1>Add an image</h1></li>
                <?php
				if ($tl_admin) {
					?>
	                <li><a href="/treeline/includes/ckeditor/plugins/linkpicker/imagepicker.php?linktype=image&action=list">Choose an image from your site's image library</a></li>
	                <li><a href="/treeline/includes/ckeditor/plugins/linkpicker/imagepicker.php?linktype=image&action=global">Choose an image from a library you share with other sites</a></li>
                    <?php
				}
				?>
                <li><a href="/treeline/includes/ckeditor/plugins/linkpicker/imagepicker.php?linktype=image&action=upload">Upload an image from your desktop</a></li>
                <?php
               	if ($tl_admin) {
                	?>
	                <li><a href="/treeline/includes/ckeditor/plugins/linkpicker/iconpicker.php?linktype=image&action=list">Add an inline icon</a></li>
                    <?php
				}
				?>
	            
                <li class="title"><h1>Create a link</h1></li>
                <?php
				if ($tl_admin) {
					?>
					<li>
						<form method="post" id="intlinkform">
							<input type="hidden" name="linktype" value="int" />
							<input type="hidden" name="linktext" value="" />                
							<input type="submit" class="submit" value="Link to another page on this website" />
						</form>
					</li>
					<?php
				}
				?>

                <li>
                    <form method="post" id="extlinkform">
                        <input type="hidden" name="linktype" value="ext" />
                        <input type="hidden" name="linktext" value="" />                
                        <input type="submit" class="submit" value="Link to page on another website" />
                    </form>
                </li>

                <li>
                    <form method="post" id="anklinkform">
                        <input type="hidden" name="linktype" value="ank" />
                        <input type="hidden" name="linktext" value="" />                
                        <input type="submit" class="submit" value="Link to an anchor on this page" />
                    </form>
                </li>

                <?php
				if ($tl_admin) {
					?>
                    <li>
                        <form method="post" id="filelinkform">
                            <input type="hidden" name="linktype" value="file" />
                            <input type="hidden" name="linktext" value="" />                
                            <input type="submit" class="submit" value="Create a link to a document" />
                        </form>
                    </li>
                    <?php
				}
				?>

                <?php
				if ($tl_admin) {
					?>
                    <li>
                        <form method="post" id="fileslinkform">
                            <input type="hidden" name="linktype" value="file_shared" />
                            <input type="hidden" name="linktext" value="" />                
                            <input type="submit" class="submit" value="Create a link to a document on another microsite" />
                        </form>
                    </li>
					<?php
				}
				?>
                <li>
                    <form method="post" id="maillinkform">
                        <input type="hidden" name="linktype" value="mail" />
                        <input type="hidden" name="linktext" value="" />                
                        <input type="submit" class="submit" value="Create a link to an email address" />
                    </form>
                </li>

                <li>
                    <form method="post" id="donatelinkform">
                        <input type="hidden" name="linktype" value="donate" />
                        <input type="hidden" name="linktext" value="" />                
                        <input type="submit" class="submit" value="Create a link to the donation form" />
                    </form>
                </li>

				<?php
				if ($tl_admin) {
					?>
                    <li class="title"><h1>Embed content</h1></li>
                    <li>
                        <form method="post" id="slidelinkform">
                            <input type="hidden" name="linktype" value="slide" />
                            <input type="hidden" name="linktext" value="" />                
                            <input type="submit" class="submit" value="Embed a slideshow" />
                        </form>
                    </li>
                    <li><a href="/treeline/includes/ckeditor/plugins/linkpicker/linkpicker.php?linktype=media">Embed media code</a></li>
					<?php
				}
				?>                
            </ul>
        	
            <!--
            <h2>Please select an asset type</h2>
            <form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="linktext" value="" />
            <input type="hidden" name="newlink" value='' />
                <fieldset title="Please select asset type" id="linktypewrap">
                    <label for="linktype">What type of asset?</label>
                    <select id="linktype" name="linktype">
						<option value="slide">Slideshow</option>
                        <option value="media">Media code</option>
                    </select>
                </fieldset>
           		<div id="footerlinks">
              		<input type="submit" name="next_btn" id="next_btn" class="btn" value='Next' />
            	</div>
            </form>
            -->
        </div>
   	 <?php
	}
	
	// *******************************************
	else{	///// this case should never be arrived at - just here to catch anything unusual... ////
		?>
		<div id="formwrap">
        	<p>There seems to be an error!<br />
		 	In the unlikely event that you have arrived at this page, please contact the site administration and quote 'link picker logic error'. </p>
     	</div>
		<div id="footerlinks"> <a href="javascript:this.close();" class="btn">Close window</a> </div>
		<?php
	}
	// *******************************************
	?>
    
</div>



</body>
</html>

<?php


// *************************************************************************
// Function moved here 081029 Phil Redclift.
// Only place its actually used to keep it separate
// Draw a list of site sections at the top of the link picker
// *************************************************************************
function drawSections($msv,$lang,$intsection){
	global $db;
	$html = '';
	$query = "SELECT p.guid, p.name, p.title, p.locked FROM pages p
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE (p.parent=".$msv." OR p.guid=$msv) AND p.msv=$msv
				AND pt.template_php IN ('folder.php','index.php','landing.php', 'news.index.php')
				AND NOT (p.hidden=1 AND p.locked=1 AND 0)
				AND p.offline=0
				ORDER BY p.sort_order ASC, p.title ASC";
	//print "$query<bR>";
	if ($pages = $db->get_results($query)) {
		foreach($pages as $page) {
			$html .= '<option value="'.$page->guid.'" '.($intsection==$page->guid?' selected="selected"':'').'>'.$page->title .'</option>'."\n";
		}
	}
	if ($html) $html = '
<select id="intsection" name="intsection">
'.$html.'
</select>
';
	return $html;
}
// *************************************************************************



// *************************************************************************
function drawLPPages($parent, $indent=0) {

	// This function returns a list of all pages that the current user has the right to edit
	// Note that it should *exclude* pages that have already been edited by someone else
	// [unless the user is a superuser?]. THIS STILL NEEDS DOING.
	global $i,$db,$site, $linktext;
	
	//print "drawLPPages($parent, $indent)<br>\n";

	$indentString = '&nbsp;&nbsp;&nbsp;&nbsp;';
	$html = '';
	$page = new Page();

	//$query = "SELECT guid, title, locked FROM pages WHERE parent = '$parent' AND lang='". $lang ."' 
	//			AND template NOT IN ('folder.php','index.php','landing.php') 
	//			AND NOT (hidden=1 AND locked=1) ORDER BY sort_order";

	$query = "SELECT p.guid, p.title, p.locked, p.hidden FROM pages p
				LEFT JOIN pages_templates pt ON p.template=pt.template_id
				WHERE p.parent = '$parent' AND p.msv=".$site->id."
				AND pt.template_php NOT LIKE '%panel%'
				AND NOT (p.hidden=1 AND p.locked=1)
				AND p.offline=0
				ORDER BY p.title, p.sort_order";
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$num = ($i%2);
			$thisguid = $result->guid;	

			if($num == 0){
				$style='row pale';
				$j=0;
			}
			else {
				$style='row dark';
				$j=1;
			}		
			
			$hs=($result->hidden)?' [<font color="orange">hidden</font>]':"";
			
			//$html .= '<span id="'.$result->guid.'" class="'.$style.'" onclick="change(this.id, '.$j.', \''.addslashes($linktext).'\', \''.$page->drawLinkByGUID($result->guid).'\');">';
			$html .= '<span id="'.$result->guid.'" class="'.$style.'" onclick="change(this.id, '.$j.', \''.str_replace('"', '~', $linktext).'\', \''.$page->drawLinkByGUID($result->guid).'\');">';
			$html .= '<span class="pagename" style="padding-left:'.$indent.'em">'.$result->title.$hs.'</span></span>'."\n";
			$i++;
			if($parent!=1) $html .= drawLPPages($thisguid, $indent+1.2);
		}
	}
	// Add the home page to the top of the list if necessary
	if ($parent == $site->id) $html = '<span id="'.$site->id.'" class="row dark" onclick="change(this.id, 1, \''.str_replace('"', '~', $linktext).'\', \''.$page->drawLinkByGUID($site->id).'\');"><span class="pagename" style="padding-left:0em">Home</span></span>'."\n".$html;
	return $html;
}


// *************************************************************************
function countPages($parent){
	global $db;
	$db->get_results("SELECT guid, title, locked FROM pages WHERE parent = '$parent' AND hidden = 0 ORDER BY sort_order");
	return $db->num_rows;
}
// *************************************************************************
function countFiles($cat, $shared=false, $subcat=''){
	global $db, $site;
	//print "count files($cat, $shared, $subcat)<br>";
	$query = "SELECT guid FROM files WHERE category = '$cat' ";
	if ($subcat) $query.="AND subcategory=$subcat ";
	if( !$shared ) $query.="AND site_id='".$site->id."'";
	//print "$query<br>";
	$db->get_results($query);
	return $db->num_rows;		
}


// *************************************************************************
function drawFileSections($lang,$filesection,$shared=false){
	global $db,$site,$lang;

	$html = "<select id=\"filesection\" name=\"filesection\">\n";
	$query = "SELECT DISTINCT f.category, fc.title, s.title AS sitename
		FROM files f 
		LEFT JOIN filecategories fc on f.category=fc.id 
		LEFT JOIN sites_versions sv ON f.site_id = sv.msv
		LEFT JOIN sites s on sv.microsite = s.microsite
		WHERE fc.title is not null ";
	if( !$shared ) $query.="AND f.site_id='". $site->id ."' ";
	//print "$query<br>";
	if ($files = @$db->get_results($query)) {
		foreach($files as $file){
			// if($file->locked<1){
				$html .= "\t\t\t\t\t\t<option value=\"". $file->category ."\"";
				if($filesection==$file->category){
					$html .= ' selected="selected"';
				}
				$html .= ">". $file->title.($shared?"(".$file->sitename.")":"")."</option>\n";
			// }
		}
	}
	$html .= "</select>\n\n";
	return $html;
}
// *************************************************************************
function drawFileSubSections($lang,$filesection,$filesubsection,$shared=false){
	global $db,$site,$lang;

	$query = "SELECT DISTINCT f.subcategory, fc.title FROM files f 
		left join filecategories fc on f.subcategory=fc.id 
		WHERE fc.title is not null 
		AND f.category=$filesection ";
	if( !$shared ){
		$query.="AND f.site_id='". $site->id ."' ";
	}
	//print "$query<br>";
	if ($files = @$db->get_results($query)) {
		foreach($files as $file){
			$html .= '<option value="'.$file->subcategory.'"';
			if($filesubsection==$file->subcategory){
				$html .= ' selected="selected"';
			}
			$html .= ">". $file->title ."</option>\n";
		}
	}
	if ($html) $html = '
<select id="filesubsection" name="filesubsection">
	<option value="">Select subcategory</option>
	'.$html.'
</select>
';
	return $html;
}
// *************************************************************************
function getFileList($cat,$shared=false,$subcat=''){
	global $i,$db,$site, $linktext;
	$html = '';			
	$query = "SELECT * FROM files WHERE category='$cat' ";
	if ($subcat) $query.="AND subcategory=$subcat ";
	if( !$shared ) $query.= "AND site_id='".$site->id."' ";
	$query.="ORDER BY title";
	//print "$query<br>";
	if ($files = $db->get_results($query)) {
		foreach ($files as $file) {
			$num = ($i%2);
			
			if($num == 0){
				$style='row pale';
				$j=0;
			}else{
				$style='row dark';
				$j=1;
			}
	
			/*
			$html .= '
<span id="'.$file->guid.'" class="'.$style.'" onclick="change(this.id, '.$j.', \''.addslashes($linktext).'\', \'/download/'.$file->guid.'/\');">
	<span class="pagename">'.$file->title.'</span>
	<span class="pagestatus">'.$file->extension.'</span>
	<span class="pagevisibility">'.pretty_bytes($file->size).'</span>
</span>
';
			*/
			$html .= '
<span id="'.$file->guid.'" class="'.$style.'" onclick="change(this.id, '.$j.', \''.$linktext.'\', \'/download/'.$file->guid.'/\', \''.str_replace("&#039;", "", $file->title).'\');">
	<span class="pagename">'.$file->title.'</span>
	<span class="pagestatus">'.$file->extension.'</span>
	<span class="pagevisibility">'.pretty_bytes($file->size).'</span>
</span>
';
			$i++;
		}
	}
	return $html;		
}


// *************************************************************************
// return filesize value in nice format
function pretty_bytes($size)
{
	$a = array("bytes", "kb", "MB", "GB", "TB", "PB");
	
	$pos = 0;
	while ($size >= 1024) {
				   $size /= 1024;
				   $pos++;
	}
	
	return round($size,2)."".$a[$pos];
}

// *************************************************************************
// Generate a link to a file in the library from its file guid
function getLinkByGUID($guid,$type='page'){
	global $db;

	switch($type){
		case 'page':
			$query = "SELECT name FROM pages WHERE guid = '$guid' limit 1";
			$results = $db->get_row($query);
			$link = $results->name .'/'; //.html removed and replaced by a  slash
			break;
		case 'file':
			$query = "SELECT CONCAT(name,'.',extension) filename FROM files WHERE guid='$guid'";
			$results = $db->get_row($query);
			$link = '/silo/files/'. $results->filename;
			break;
	}
	
	return $link;
}



function drawSlideshows() {
	global $db, $site;
	$opts = '';
	$query = "SELECT g.id, g.title 
		FROM galleries g 
		INNER JOIN gallery_images gi ON gi.gallery_id = g.id
		WHERE g.live = 1 AND g.msv = ".($site->id+0)."
		GROUP BY g.id
		";
	//print "$query<br>\n";
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$opts .= '<option value="'.$result->id.'">'.$result->title.'</option>'."\n";
		}
	}
	return $opts;
}

// ************************************************************************
// 16/12/2008 Phil Redclift
// Show input errors 
function show_errors($error_list, $warning_list){
	$html='';
	if( !empty($error_list) ){
		foreach($error_list as $err) $html .= '<br />'.$err;
		if ($html) $html = '<div id="error_list"><strong>ERROR!</strong>'.$html.'</div>';
	}
	else if(!empty($warning_list) ){
		foreach($warning_list as $err) $html .= '<br />'.$err;
		if ($html) $html = '<div id="warning_list"><strong>Please note</strong>'.$html.'</div>';
	}
	return $html;
}

////////////////////////////////////////////////////////

?>