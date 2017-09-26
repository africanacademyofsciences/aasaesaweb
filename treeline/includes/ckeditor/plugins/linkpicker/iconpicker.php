<?php
session_start();
ini_set("display_errors", 1);
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions.php");


// Set the debugging on
$DEBUG = (read($_GET,'debug',false) !== false)?true:false;	
$DEBUG = false;

include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/site.class.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/image.class.php");
include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/ezSQL.class.php");

$siteID = $_SESSION['treeline_user_site_id'];
$site = new Site($siteID);

$linkpicker = "/treeline/includes/ckeditor/plugins/linkpicker/linkpicker.php";
$message = array();
$feedback = "error";

$action = read($_REQUEST,'action','');
$source = read ($_SERVER['REQUEST_METHOD']=="POST"?$_POST:$_GET, "r", '');

if ($action == "global") $global = 1;
else if ($action=="select") $global = read($_POST?$_POST:$_GET, 'global', 0)+0;

//print "action($action) r(".$_GET['r'].") source($source) global($global)<br>\n";

//$global = isset($_REQUEST['global']);

$category = read($_REQUEST,'category','xx');	
$subcategory = read($_REQUEST,'subcategory','xx');	
$mode = read($_REQUEST, "mode", '');

$libraryID = read($_POST?$_POST:$_GET, 'library', 0);
$libraries = array("Font Awesome", "Ion Icons");
$deflibrary = $libraries[$libraryID];
//print "Got library($deflibrary) from id($libraryID)<br>\n";

// If the previous button was pressed go back to the beginning
//print "POST(".print_r($_POST, 1).")<br>\n"; 
if($_POST['prev_btn']){
	unset($_POST);
	$prevlink = "location: linkpicker.php?linktype=".$linktype;
	//print "Would redirect<br>\n";
	header($prevlink);
}
	
//print "got s($category) c($subcategory))<br>";
$search = read($_REQUEST,'search','');

// Need to fix action to upload if we have come from the bloggs page??
if ($_SERVER['REQUEST_METHOD']=="GET") {
	$mode = substr($_SERVER['HTTP_REFERER'], strlen($_SERVER['HTTP_REFERER'])-4);
	if ($mode=="blog") $action="upload";
}
//print "ref(".$_SERVER['HTTP_REFERER'].") mode($mode) action($action) \n";

$uploaded_filename='';
$feedback="error";

//print "action($action)<br>\n";
if ($action == 'list' || $action=="global") {
	
	$perpage = 10;
	$page = read($_REQUEST,'page',1);	
	
	$previous = read($_REQUEST,'previous',false);
	$next = read($_REQUEST,'next',false);				
	//print "page($page) prev($previous) next($next)<br>\n";

	// This indicates whether or not we need a "next page" option
	$nextpage = false;

	if ($previous) {
		// if we're trying to get back to the previous page
		$page = $page-1;
	}
	else if ($next) {
		// If we're trying to go to the next page
		$page = $page+1;
	}

}
else if ($action == 'select') {
	$name = read($_REQUEST,'name','');	
	$image_guid = read($_REQUEST, 'guid', '');
	//print "name($name) guid($image_guid)<br>\n";
}
else if ($action == "getdata") {

	//print "post to getdata, do I need to upload a file????";
	if ($_FILES['upload']) {
		$image = new Image();
		$tmpname = $_SERVER['DOCUMENT_ROOT']."/silo/images/upload/".$site->id."-".date("dmYhis",time());
		//print "up($tmpname)<br>\n";
		$tmpmsg = $image->uploadFromDesktop($_FILES['upload'], $tmpname, 800);
		//print "returns($tmpmsg)<br>\n";
		if (substr($tmpmsg, 0, strlen($tmpname))==$tmpname) {
			$uploaded_filename=$tmpmsg;
		}
		else {
			$message[]=$tmpmsg;
			$action = "upload";
		}
	}
}

$pagetitle = "Add an icon";
//$pagetitle = "Add an image($action)";
if ($action=="select") $pagetitle = "Choose the size of image you want to use";
else if ($action=="getdata") $pagetitle = "Customise the image";
if ($action=="media") $pagetitle = "Add a media code block";
//print "got action($action) source($source)<br>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Treeline | Select an image</title>
<style type="text/css">
@import url('/treeline/style/global.css');
@import url('/treeline/style/tlembed.css');
@import url('/treeline/style/imagePicker.css');
@import url('/treeline/style/iconPicker.css');
@import url('/style/font-awesome.min.css');
@import url('/style/ionicons.min.css');

</style>
<script type="text/javascript">

var editor;
var linkform;
var inserted = false;

function onDialogEvent(e) {
	if (e.name=="load") {
		editor = e.editor;
		var linktext = editor.getSelection().getNative();
		//alert("dialog event("+e+") n("+e.name+")");
		//alert("load with text("+linktext+")");
		//var seltext = editor.getSelection().getSelectedText();
		//alert ("text("+seltext+")");
		if (linktext!='') {
			linkform.linktext.value = linktext;
			if (document.getElementById("linktypeform")) {
				//alert ("set linktypeform linktext value to(1"+linktext+"1)");
				document.getElementById("linktypeform").linktext.value=linktext;
			}
		}
	}
	//alert ("ok pressed");
	if (e.name=="ok") {
		//alert("dialog event("+e+") n("+e.name+")");
		if (!inserted) {
			//alert ("insert("+linkform.linkhref.value+") linktext("+linkform.linktext.value+")");
			if (linkform.linkhref.value) {
				editor.insertHtml('<a href="'+linkform.linkhref.value+'">'+linkform.linktext.value+'</a>');
			}
			else {
				var link = linkform.linktext.value;
				alert ("embed "+link);
				editor.insertHtml(link);
			}
			inserted=true;
		}
	}
	if (e.name=="cancel") {
		//alert("dialog event("+e+") n("+e.name+")");
	}
}

function setMedia(media) {
	//alert ("sM("+media+")");
	if (media) {
		linkform.linktext.value = media;
		//alert ("Show ok button...<br>\n");
		parent.showOkButton(1);	
	}
	else parent.showOkButton(0);
}

parent.showOkButton(0);

</script>

</head>
<body id="treeline" class="imagePicker" style="margin: 0px">

<div id="header">
  <h1><?=$pagetitle?></h1>
</div>

<div id="contentpane"> <!-- linkpicker compatibility -->

<form method="post" id="linkform">
	<input type="hidden" name="linkhref" value="" />
	<input type="hidden" name="linktext" value="<?=$linktext?>" />
</form>

<script type="text/javascript">
	linkform = document.getElementById("linkform");
</script>

<div id="structure">

    <!-- Start of inside -->
    <div id="inside_structure">

		<?php 
		// Do we need to show the category select form?
		if (
			$action=="picker" || $action=="list" || 
			$action == "global" || $action=="select" || 
			$action=="getdata"
			) { 
			?>

            <form method="get" id="categoryselector" action="#">
            <input type="hidden" name="action" value="<?=$action?>" />
            <input type="hidden" name="r" value="<?=$source?>" />

			<?php
			if ($action == 'list' || $action=="global") { 
				$iconLibraryOpts = '';
				foreach ($libraries as $libID=>$libTitle) {
					$iconLibraryOpts .= '<option value="'.$libID.'"'.($libID==$libraryID?'selected="selected" ':'').'>'.$libTitle.'</option>'."\n";
				}
				?>
                <fieldset class="field">
                    <label for="search">Search</label>
                    <input type="text" id="search" class="text" name="search" value="<?=$search?>" />

					<label form="f_library">Icon library</label>
                	<select name="library" id="f_library">
                    	<?=$iconLibraryOpts?>
                    </select>
                    
                    <input type="submit" class="btn silver_btn" name="submit" value="OK" />
                </fieldset>
				<?php 
			}
			?>
            </fieldset>
            </form>
          	<?php 
   		} 
		?>
	</div> 
    <!-- end of inside structure -->
    
    <?php
	//print "f($feedback) m(".print_r($message, true).")<br>\n";
    echo show_errors($message, array());
	?>
    
	<div id="content">
		
		<?php 
		// ***************************************************************
		// Show any matching images.
		if ($action == 'list' || $action=="global") { 
			?>
            <div id="gallery">
                <?=drawIcons($deflibrary)?>
            </div>
            <div id="controls">
                <form method="post" id="imagepicker" action="#">
                <fieldset style="padding-bottom: 0;">
                <input type="hidden" name="page" value="<?=$page?>" />
                <input type="hidden" name="action" value="<?=$action?>" />
                <input type="hidden" name="r" value="<?=$source?>" />
                <input type="hidden" name="category" value="<?=$category?>" />
                <input type="hidden" name="subcategory" value="<?=$subcategory?>" />
                <input type="submit" class="btn" name="prev_btn" value="Restart" />
				<?php
                if ($global) echo '<input type="hidden" name="global" value="1" />';
				if ($nextpage) { 
					?>
                    <input type="submit" id="next" class="btn red_btn hi" name="next" value="Next page" style="margin-right: 0;" />
                	<?php 
				} 
				else { 
					?> 
                    <input type="button" id="next" class="btn red_btn lo" name="next" value="Next page" disabled="disabled" style="margin-right: 0;" />				
	                <?php 
				} 
				if ($page > 1) { 
					?>
                    <input type="submit" id="previous" class="btn hi" name="previous" value="Previous page" />
                	<?php 
				} 
				else { 
					?>
                    <input type="button" id="previous" class="btn lo" name="previous" value="Previous page" disabled="disabled" />
                	<?php 
				} 
				?>
                </fieldset>
                </form>
            </div>
            
			<?php 
		}
		
		// ***************************************************************
		// Basic image select screen 
		else if ($action == 'select') { 

			echo drawSizes($image_guid, $global);
			
		}
		 
		// ***************************************************************
		// Advanced image select screen
		else if ($action=="getdata") { 
		
			// Need to get image data if we are going to add extra info
			if (!$uploaded_filename) {
				$image_id=$_GET['id'];
				$query = "SELECT i.credit, iz.filename FROM images i
					LEFT JOIN images_sizes iz ON i.guid=iz.guid
					WHERE iz.id=".$image_id;
				//print "query($query)<br>\n";
				if ($row=$db->get_row($query)) {
					$image_filename = $row->filename;
					$img_credit=$row->credit;
				}
			}
			else $image_filename=str_replace($_SERVER['DOCUMENT_ROOT']."/silo/images/", '', $uploaded_filename);
						
			?>

            <form method="post" id="imagepicker" action="<?=$linkpicker?>">
            <fieldset>
            <input type="hidden" name="action" value="<?=$action?>" />
            <input type="hidden" name="linktype" value="embed-image" />
            <input type="hidden" name="source" value="<?=$source?>" />
			<input type="hidden" name="align" value="" />
			<input type="hidden" name="src" value="" />
			<input type="hidden" name="alt" value="" />
			<input type="hidden" name="border" value="" />
			<input type="hidden" name="vspace" value="" />
			<input type="hidden" name="hspace" value="" />
			<input type="hidden" name="height" value="" />
            <input type="hidden" name="img_filename" value="<?=$image_filename?>" />
            <input type="hidden" name="gallery_id" value="0" />
            <?php
				if ($global) echo '<input type="hidden" name="global" value="1" />';
			?>
			<!-- end of TinyMCE fields -->
        
        	<div class="img_file">
            	<img src="/silo/images/<?=$image_filename?>" width="100" />
                <div id="img-file-detail">

                    <div class="img_params">
                        <fieldset>
                            <label for="f_alt">Alternative text</label>
                            <input id="img_alt_text" name="img_alt_text" type="text" class="text long" />
    	                    <p class="tip">Tip: An accurate description helps with search engines optimisation</p>
	    				</fieldset>
                    </div>
                
                    <div class="img_params width" style="margin-bottom: 20px;">
                        <fieldset>
                            <label for="f_width">Width</label>
                            <select name="width">
                            	<option value="">Actual width</option>
                            	<option value="100%">100%</option>
                            	<option value="50%">50%</option>
                            	<option value="33%">33%</option>
                            </select>
                        </fieldset>
                    </div>
                    
                    <div class="img_params caption">
                        <fieldset>
                            <label for="f_caption">Caption</label>
                            <input id="f_caption" name="img_caption" type="text" class="text long" />
    	                    <p class="tip">Tip: This will be displayed below the image</p>
                        </fieldset>
                    </div>
        
                
                    <div class="img_params margins">
                        <p class="label">Space around the image in pixels</p>
                        <fieldset class="margin">
                            <label for="f_top">Top</label>
                            <input id="img_space_top" name="img_space_top" type="text" class="text short" value="0" />
                        </fieldset>
                        <fieldset class="margin">                    
                            <label for="f_right">Right</label>
                            <input id="img_space_right" name="img_space_right" type="text" class="text short" value="0" />
                        </fieldset>
                        <fieldset class="margin">                    
                            <label for="f_bottom">Bottom</label>
                            <input id="img_space_bottom" name="img_space_bottom" type="text" class="text short" value="0" />
                        </fieldset>
                        <fieldset class="margin">                    
                            <label for="f_left">Left</label>
                            <input id="img_space_left" name="img_space_left" type="text" class="text short" value="0" />
                        </fieldset>
                    </div>
            
                    <div class="img_params">
                        <fieldset>
                            <label for="f_align">Alignment</label>
                            <select id="f_align" name="img_align">
                                <option value="0">None</option>
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
	                        <p class="tip">Tip: If you have checked the credit box or added a caption and would like your image to appear alongsite your content you should specify here if you would like the image to align to the left or the right of the page. If not then you can use the "Align left" or "Align right" tools after you have added your image to the editor</p>
                        </fieldset>
                    </div>
        
                    <div class="img_params">
                        <?php if ($img_credit) { ?>
                            <p><strong>Credit <input id="img_credit" name="img_credit" type="checkbox" class="checkbox" value="1"/></strong> check this box to have the text '<?=$img_credit?>' appear alongside this image</p>
                        <?php } else { ?>
                            <input type="hidden" id="img_credit" value="0" name="img_credit" value="" />
                        <?php }  ?>
                        <input id="credit_to" name="img_credit_to" type="hidden" value="<?=$img_credit?>" />
                    </div>
                    
                    <div class="img_params"  style="width: 400px;">
                        <input type="submit" class="btn" id="prev_btn" name="prev_btn" value="Restart" />
                        <input type="submit" class="btn red_btn"  name="insert" value="<?=($source?"Select":"Insert")?>" />
                    </div>
        

                </div>
            </div>
            
            </fieldset>
            </form>
			<?php 
		} 


		// ***************************************************************
		// Add a media code block - 11/09 PMR
		else if ($action=="media") { 
		
			$image_filename=str_replace($_SERVER['DOCUMENT_ROOT']."/silo/images/", '', $uploaded_filename);
						
			$query = "SELECT m.guid, m.title, m.code
				FROM media m
				WHERE (m.msv = ".$site->id." OR m.shared=1)
				ORDER BY m.title
				";
			//print "$query<br>\n";
			$media_code = $db->get_results($query);
			?>


			<!-- TinyMCE fields: -->
            <form method="get" id="imagepicker" action="#">
            <fieldset>
                <input type="hidden" name="action" value="<?=$action?>" />
                <input type="hidden" name="img_filename" value="" />
                <input type="hidden" name="img_credit_to" value="" />
                <input type="hidden" name="img_credit" value="" />
                <input type="hidden" name="gallery_id" value="" />
                <!-- end of TinyMCE fields -->
    
                <h2>Please select a media code block to embed</h2>
                <div class="img_params" style="clear: left;">
					<?php
                    if (count($media_code)) {
                        ?>
                        <select name="media_id" class="long" id="f_media" onChange="setMedia(this.value);" >
                            <option value="0">Select media</option>
                            <?php 
                            if ($media_code) {
                                foreach ($media_code as $row) {
                                    echo '<option value="@@MEDIA-'.$row->guid.'@@">'.$row->title.'</option>';
                                }
                            }
                            ?>
                        </select>
                        <?php
                    }
                    else {
                        ?>
                        <p>No media code blocks have been configured for this site yet</p>
                        <?php
                    }
                    ?>
                    <div id="footerlinks">
	                    <input type="submit" id="prev_btn" class="btn" name="cancel" value="Previous" onclick="document.location='<?=$linkpicker?>'" />
                    </div>
                </div>
            </fieldset>
            </form>
			<?php 
		} 

		// Upload your own image from your desktop
		else if ($action=="upload") { 
			//print "msg(".print_r($message, true).")<br>\n";
			?>
            <div class="img_params">          
                <p><strong>Upload your own image</strong></p>
                <p>Please select an image from you computer to user.</p>
                <form method="post" id="imagepicker" action="" enctype="multipart/form-data" >
                <fieldset id="upload-image">
                    <input type="hidden" name="mode" value="<?=$mode?>" />
                    <input type="hidden" name="action" value="getdata" />
                    <input type="hidden" name="r" value="<?=$source?>" />
                    <input type="file" name="upload" class="file" />
                </fieldset>
                <fieldset>
	                <input type="submit" class="btn" id="prev_btn" name="prev_btn" value="Restart" />
                    <input class="btn red_btn" type="submit" value="Load file" />
                </fieldset>
                </form>
                <p>Please note that any images uploaded here will not be resized and will not be added to the image library so you will not be able to share this image with other sites.</p>
            </div>
            
			<?php 
		}
		
		// No action show image picker options. 
		else { 
			?>
			<div id="content">
                <h2>Please select image source</h2>
                <form id="linktypeform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div id="formwrap">
                        <input type="hidden" name="newlink" value='' />
                        <input type="hidden" name="r" value='<?=$source?>'  />
                        <fieldset title="Please select link type" id="linktypewrap">
                            <label for="linktype">What type of link?</label>
                            <select id="imagetype" name="action">
                            <option value="list">library image</option>
                            <option value="global" <?=($_POST['action']=="global"?'selected="selected"':'')?>>global library image</option>
                            <option value="upload" <?=($_POST['action']=="upload"?'selected="selected"':'')?>>upload an image</option>
                            </select>
                        </fieldset>
        
                        <div id="footerlinks">
                        <input type="submit" name="prev_btn" id="prev_btn" class="btn" value='Restart' />
                        <input type="submit" name="next_btn" id="next_btn" class="btn" value='Next' />
                        </div>
                    </div>
                </form>
			</div>


			<?php

			
		} 
		?>
        
     </div>


</div>
</div>

</body>

<?php if ($action=="slideshow") { ?>
<script type="text/javascript">
var images=new Array();
<?php
	foreach ($galleries as $row) {
		print "images[".$row->id."] = new Image();\n";
		print "images[".$row->id."].src='/silo/images/galleries/".$row->id."/t_".$row->gallery_image_id.".".$row->image_extension."';\n";
	}
?>	

function switchImage(id) {
	var gallery_holder = document.getElementById("gallery_holder");
	var gallery_image = document.getElementById("gallery_image");
	if (id>0) {
		gallery_image.src = images[id].src;
		gallery_holder.style.display='block';
		//alert ("set ("+document.getElementById("imagepicker")+" value to ("+images[id].src+")");
		document.getElementById("imagepicker").img_filename.value=images[id].src;
	}
	else {	
		gallery_holder.style.display="none";
	}
}

</script>
<?php 
} 

?>

</html>



<?php

function drawCategories($category, $global=0) {
	global $db, $site;
	
	// Generate category list for all image on all sites?
	$query = "SELECT ic.id, ic.title as category, 
			IF(sv.msv=".$site->id.",0,sv.msv) as site_order, 
			IF(sv.msv=".$site->id.",'',s.title) as site_title, 
			sv.`language`
			FROM images i
			LEFT JOIN imagecategories ic ON i.category=ic.id
			LEFT JOIN sites_versions sv ON ic.site_id=sv.msv
			Left join sites s on sv.microsite=s.microsite
			WHERE ic.parent=0
			";
	if (!$global) $query.="AND sv.msv=".$site->id." ";
	else $query.="AND i.shared=1 ";
	$query .= "group by site_order, sv.language, ic.title
			ORDER BY site_order, sv.language, ic.title";
	//echo $query;
	$html = '';
	
	if( $categories = $db->get_results($query) ){
		foreach ($categories as $c) {
			$selected = ($c->id == $category)?'selected="selected"':'';
			$html .= "\t".'<option value="'.($c->id).'" '.$selected.'>'.htmlentities($c->category).(($c->site_title)?" (".$c->site_title." - ".$c->language.")":"").'</option>'."\n";
		}
	}
	return $html;
}

function drawSubcategories($category, $subcategory, $global=0) {
	global $db,$site;
	
	if ($category=="xx") return '';
	
	//print "dS($category, $subcategory, $global)<br>";
	$query = "SELECT ic.id, ic.title as subcategory
				FROM images i
				LEFT JOIN imagecategories ic ON i.subcategory=ic.id
				WHERE ic.parent=$category
				";
	if (!$global) $query.="AND i.site_id=".$site->id." ";
	else $query.="AND i.shared=1 ";
	$query.="GROUP BY ic.title
		ORDER BY ic.title";
	//echo $query;
	$html = '';
	
	if( $categories = $db->get_results($query) ){
		foreach ($categories as $c) {
			$selected = ($c->id == $subcategory)?'selected="selected"':'';
			$html .= "\t".'<option value="'.$c->id.'" '.$selected.'>'.htmlentities($c->subcategory).'</option>'."\n";
		}
	}
	return $html;
}

function drawIcons($library) {
	//print "dI($category, $subcategory, $global)<br>\n";
	global $db,$site;
	global $page, $perpage;
	global $category,$search, $source;
	$icona = array();
	
	if ($library == "Font Awesome") {
		$iconprefix = "fa";
		$iconfile = $_SERVER['DOCUMENT_ROOT']."/style/font-awesome.min.css";
		//print "Check file($iconfile)<br>\n";
		if (file_exists($iconfile)) {
			$icons = file_get_contents($iconfile);
			if (preg_match_all("/\.(.*):before/", $icons, $iconmatch, PREG_PATTERN_ORDER)) {
				//print "Got icons\n".print_r($iconmatch, 1)."\n";
				foreach($iconmatch[1] as $icon) {
					$tmp = explode(":before,.", $icon);
					if (count($tmp)) {
						//print "Got matches for($icon) - ".print_r($tmp, 1)."\n";
						foreach ($tmp as $tmp1) {
							//print "Got icon($tmp1) search($search)<br>\n";
							if ($search) {
								if (strpos($tmp1, $search)) $icona[] = $tmp1;
							}
							else $icona[] = $tmp1;
						}
					}
					else print "Got icon($icon)\n";
				}
			}
		}
	}
	else if ($library == "Ion Icons") {
		$iconprefix = "ion";
		//print "So how do we load ion icons?";
		$iconfile = $_SERVER['DOCUMENT_ROOT']."/style/ionicons.min.css";
		//print "Check file($iconfile)<br>\n";
		if (file_exists($iconfile)) {
			$icons = file_get_contents($iconfile);
			//print "Icons($icons)<br>\n";
			if (preg_match_all("/\.ion-(.*?):/", $icons, $iconmatch, PREG_PATTERN_ORDER)) {
				//print "Got icons\n".print_r($iconmatch, 1)."\n";
				foreach($iconmatch[1] as $icon) {
					//print "Got icon($icon) search($search)<br>\n";
					if ($search) {
						if (strpos($icon, $search)) $icona[] = "ion-".$icon;
					}
					else $icona[] = "ion-".$icon;
				}
			}
		}
	}
	else print "Icon library($library) not found<br>\n";
	
	if (count($icona)) {
		foreach ($icona as $icon) {
			$img_link = './linkpicker.php?linktype=icon&amp;name='.$icon.'&amp;r='.$source.'&amp;guid='.$image->guid;
			$iconHTML = ' <span>'.$icon.'</span>';
			//if ($library == "Font Awesome") ; 
			if ($library == "Ion Icons") $iconHTML = ' <span>'.str_replace("-", " ", substr($icon, 4)).'</span>';
			$html .= '
	<div class="image image'.$i.'">
		<a href="'.$img_link.'"><i class="'.$iconprefix.' '.$icon.'">'.$iconHTML.'</i></a>
	</div>
	';
			$i++;
		}
	}
	else {
		$html = '<p>No icons were found</p>'."\n";
	}
	return $html;
}

function _getImageTag($w, $h) {
	$img_tag='';
	if($w == 190 && $h == 92) $img_tag = 'This size is perfect for homepage panels or right hand panels';
	else if($w == 230) $img_tag='This image is the correct width for right panel buttons or the homepage right hand button';
	else if($w == 204) $img_tag='This image is the correct width for left panel buttons';
	else if($h == 204) $img_tag='This image is the correct height for landing page or multimedia page headings';
	else if($h == 80 && $w==250) $img_tag='This image is perfect for event title bars';
	else if($w == 446) $img_tag='This iimage is the best size for 3 col layout center columns';

	if ($img_tag) $img_tag = '<strong>'.$img_tag.'</strong>';
	return $img_tag;
}
// Need to try to extract the image labels from site config
function getImageTag($w, $h) {
	global $site;
	$label = array();
	
	//print "gIT($w, $h)<br>\n";
	foreach ($site->config['size'] as $i) {
		//print "size(".$i['size'].")<br>\n";
		$sz = explode("x", $i['size']);
		if (preg_match("/^(\d*)(.*?)(\d*)$/", $i['size'], $sz)) {
			//print "got size($w, $h) test(".print_r($sz, true).")<br>\n";
			if ($sz[1]>0 && $sz[1]==$w && $sz[3]>0 && $sz[3]==$h) $label[]='This image is perfect for '.$i['desc'];
			else if ($sz[1]>0 && $sz[1]==$w) $label[]='This image is the right width for '.$i['desc'];
			else if ($sz[3]>0 && $sz[3]==$h) $label[]='This image is the righr height for '.$i['desc'];
		}
		
	}
	return $label;
}

function drawSizes($guid, $global) {
	global $db, $site, $source;
	//print "dS($guid, $global)<br>\n";
	
	$query = "SELECT *, 
		IF (s.filename>0, concat(s.filename, '.', i.extension), '') as migrated_filename
		FROM images i 
		LEFT JOIN images_sizes s ON s.guid=i.guid 
		WHERE i.guid = '$guid' 
		";
	if (!$global) $query .= "AND i.site_id = ".($site->id+0)." ";
	$query .= "ORDER BY s.width, s.height ";
	//print "<!-- $query --> \n";
	$images = $db->get_results($query);
	
	$html = '';
	foreach ($images as $image) {
		$img_tag= getImageTag($image->width, $image->height);

		$filepath=$image->migrated_filename?$image->migrated_filename:$image->filename;
		if (is_numeric($image->filename)) {
			//print "<!-- ".$image->filename." is > 0 --> \n";
			$filepage=$image->filename.".".$image->extension;
		}
		else $filepath=$image->filename;
		
		
		//$link = "document.forms[0].img_filename.value='".$filepath."';";
		$link = "imagepicker.php?action=getdata&amp;id=".$image->id."&amp;r=".$source;
		$html .= '
<div class="size">
	<div class="details">
';
		if (is_array($img_tag)) {
			foreach($img_tag as $this_tag) {
				//print "tag($this_tag)<br>\n";
				$html.='<p class="tag">'.$this_tag.'</p>';
			}
		}
		else if ($img_tag) $html.='<p class="tag">'.$img_tag.'</p>';
		$html .= '
		<p class="dim">'.$image->width.' wide | '.$image->height.' high</p>
	</div>
	<p class="image"><a href="'.$link.'" title="Insert '.$image->description.'"><img src="/silo/images/'.$filepath.'" alt="'.$image->description.'" width="'.$image->width.'" height="'.$image->height.'"/></a></p>
</div>
';
	}
	return $html;

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
?>