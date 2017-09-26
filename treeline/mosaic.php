<?php
    ini_set("display_errors", 1);
    
    include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/treeline.init.php");
    include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/mosaic.class.php");	

    $action = read($_POST?$_POST:$_GET,'action','');
    if (!$action) header("Location: /treeline/");

    $mid = read($_POST?$_POST:$_GET,'mid',0);
    $tid = read($_POST?$_POST:$_GET,'tid',0);

    // print "Got action($action)<br>\n";
    
    $feedback = read($_REQUEST,'feedback','error');	
    $message = array();
	
    $title = read($_POST,'title','');
    $type = read($_POST,'type','');
    $description = read($_POST,'description','');

    $ttitle = read($_POST,'ttitle','');
    $image = read($_POST,'image','');
    $tdescription = read($_POST,'tdescription','');
    //print "Got tile($ttitle, $tdescription, $image)<br>\n";

    $ssearch = read($_REQUEST, "q", "");

    $thispage = read($_SERVER['REQUEST_METHOD']=="GET"?$_GET:$_POST,'p',1);

    $mos = new Mosaic($site->id);
    $mos->setPage($thispage);
	
    // ****************************************
    // PROCESSING ANY POST ACTION  ************	
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Create a new mosaic 
        if ($action == 'create') {
            if (!$title) $message[] = 'Please enter a title for this mosaic';
            else {
                $mos->setTitle($title);
                $name = $mos->generateName();
                if (!$name) $message[] = 'A mosaic with that name already exists in the library';
                else {
                    $mos->setDescription($description);
                    $mos->image = $_POST['image'];
                    $mos->type = $type;
                    if ($mos->create()) {
                        $nextsteps='<li><a href="/treeline/mosaic/?action=create">Add another new mosaic to the library</a></li>'."\n";
                        $action="edit";
                    }
                    else $message[] = "Failed to create mosiac";
                }
            }
        }

        // Create a new mosaic tile 
        else if ($mid>0 && $action == 'create-tile') {
            if (!$image) $message[] = 'You must select an image for this tile';
            else {
                $mos->loadByID($mid);
                $mos->ttitle = $ttitle;
                $mos->tdescription = $tdescription;
                $mos->image = $image;
                if (!$mos->validImage()) $message[] = "This is not a valid image";
                else if ($mos->create_tile()) {
                    //$nextsteps='<li><a href="/treeline/mosaic/?action=create">Add another new mosaic to the library</a></li>'."\n";
                    $action="edit-tiles";
                }
                else $message[] = "Failed to create tile";
            }
        }

        // Edit a mosaic in the library
        else if ($action == 'edit') {
            //print "Load mos($mid)<Br>\n";
            $mos->loadByID($mid);
            $mos->setTitle($title);
            $mos->image=$_POST['image'];
            $mos->setDescription($description);
            $mos->type = $type;
            if (!$title) $message[] = "You must enter a title for this mosaic";
            if (!count($message)) {
                $mos->save();
                $nextsteps='<li><a href="/treeline/mosaic/?action=create">Add another new mosaic to the library</a></li>'."\n";
                $mid=0;
            }
        }

        // Edit a mosaic tile in the library
        else if ($action == 'edit-tile') {
            //print "Load mos($mid)<Br>\n";
            $mos->loadTileByID($tid);
            $mos->ttitle = $ttitle;
            $mos->image=$_POST['image'];
            if (!$mos->validImage()) $message[]= "You must choose an image";
            else {
                $mos->tdescription = $tdescription;
                $mos->save_tile();
                $action = "edit-tiles";
                $tid = 0;
            }
        }

        // Actually delete a mosaic
        else if ($action == 'delete') {
            $mos->loadByID($mid);
            if($mos->delete()) {
                $nextsteps='<li><a href="/treeline/mosaic/?action=create">Add another new mosaic to the library</a></li>'."\n";
                $action="edit";
                $mid=0;
            }
            else $message[] = "Failed to delete this mosaic";
        }
        
        // Actually delete a tile
        else if ($action == 'delete-tile') {
            $mos->loadTileByID($tid);
            if($mos->delete_tile()) {
                $nextsteps='<li><a href="/treeline/mosaic/?action=create-tile&amp;mid='.$mid.'">Add another new tile to this mosaic</a></li>'."\n";
                $action="edit-tiles";
                $tid=0;
            }
            else $message[] = "Failed to delete this mosaic";
        }

        else {
            print "Got post action($action) not processed<br>\n";
        }
		
    }
    // END OF ACTION PROCESSING ************	

	// PAGE specific HTML settings
    $css = array('forms', 'tables'); // all CSS needed by this page
    $extraCSS = '

    ';	
    $js = array(); // all external JavaScript needed by this page
    $extraJS = '


    '; 
    // // extra on page JavaScript
	
    // Page title	
    $pageTitleH2 =  'Mosaic'.($action?" - ".str_replace("-", " ", $action):"");
    $pageTitle = $pageTitleH2;
	
    $pageClass = 'mosaic';
    include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');	
    
?>
<div id="primarycontent">
<div id="primary_inner">
    <?php
    echo drawFeedback($feedback,$message);
    
    if ($nextsteps) echo treelineList($nextsteps, $page->drawLabel("tl_generic_next_steps", "Next steps"), "blue");
  
    if ($action=="create") { 

        $page_html='
        <form id="treeline" action="" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <p class="instructions">To add a new mosaic to the library, please complete the form below</p>

            <label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
            <input type="text" name="title" id="title" value="'.$title.'" /><br />

            <label for="f_type">Type:</label>
            <select name="type" id="f_type">
                <option'.($type=="sapegin"?' selected="selected"':'').'>sapegin</option>
                <option'.($type=="gridGallery"?' selected="selected"':'').'>gridGallery</option>
            </select><br />
                
            <label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
            <textarea name="description">'.$description.'</textarea><br />

            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_create", "Create")).'" />
            </fieldset>
        </fieldset>
        </form>
        ';

        echo treelineBox($page_html, "Add new mosaic", "blue");
    }
    else if ($action=="create-tile") { 

        $page_html='
        <form id="treeline" action="" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="mid" value="'.$mid.'" />
            <p><a href="/treeline/mosaic.php?action=edit">Manage mosaics</a></p>
            <p class="instructions">To add a new tile to the mosaic: '.$mos->title.', please complete the form below</p>

            <label for="title">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).':</label>
            <input type="text" name="ttitle" id="title" value="'.$ttitle.'" /><br />

            <label for="f_image">Image:</label>
            <div style="float: left;">
            <textarea name="image">'.$image.'</textarea>
            </div><br />

            <label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
            <div style="float: left;">
                <textarea name="tdescription">'.$tdescription.'</textarea><br />
            </div>
                    
            <fieldset class="buttons">
                <input type="submit" class="submit" value="Create tile" />
            </fieldset>
        </fieldset>
        </form>
        ';

        echo treelineBox($page_html, "Add new tile", "blue");
    }

    
    else if ($mid && $action == 'edit') { 
        $mos->loadByID($mid);
        ?>
        <h2 class="pagetitle rounded">Modify mosaic attributes</h2>
        <?php 		
        $mtype = $_POST?$_POST['type']:$mos->type;
        $page_html = '
        <p><a href="/treeline/mosaic.php?action=edit">Manage mosaics</a></p>
        <form id="treeline" action="" method="post">
        <fieldset>
        
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="mid" value="'.$mid.'" />
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="'.($_POST?$_POST['title']:$mos->title).'" /><br />

            <label for="f_type">Type:</label>
            <select name="type" id="f_type">
                <option'.($mtype=="sapegin"?' selected="selected"':'').'>sapegin</option>
                <option'.($mtype=="gridGallery"?' selected="selected"':'').'>gridGallery</option>
            </select><br />

            <label for="description">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
            <textarea name="description">'.($_POST?$_POST['description']:$mos->description).'</textarea><br />

            <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.$page->drawLabel("tl_generic_save", "Save").'" />
            </fieldset>
        </fieldset>
        </form>
        ';

        echo treelineBox($page_html, "Edit mosaic", "blue");
    }

    else if ($tid && $mid && $action == 'edit-tile') { 
        $mos->loadByID($mid);
        $mos->loadTileByID($tid);
        ?>
        <h2 class="pagetitle rounded">Modify tile attributes</h2>
        <?php 		

        $page_html = '
        <p><a href="/treeline/mosaic.php?action=edit-tiles&amp;mid='.$mos->id.'">Manage tiles</a></p>
        <form id="treeline" action="" method="post">
        <fieldset>
        
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="tid" value="'.$tid.'" />
            <input type="hidden" name="mid" value="'.$mid.'" />
            <label for="ttitle">Title:</label>
            <input type="text" name="ttitle" id="ttitle" value="'.($_POST?$_POST['ttitle']:$mos->ttitle).'" /><br />

            <label for="f_image">Image:</label>
            <div style="float: left;">
                <textarea name="image">'.($_POST?$_POST['image']:$mos->image).'</textarea>
            </div><br />

            <label for="tdescription">'.ucfirst($page->drawLabel("tl_generic_description", "Description")).':</label>
            <div style="float: left;">
                <textarea name="tdescription">'.($_POST?$_POST['tdescription']:$mos->tdescription).'</textarea><br />
            </div>

            <fieldset class="buttons">
                    <input type="submit" class="submit" value="Save tile" />
            </fieldset>
        </fieldset>
        </form>
        ';

        echo treelineBox($page_html, "Edit tile", "blue");
    }

    else if ($mid && $action=="delete") {
	
        $mos->loadByID($mid);
        $page_html = '
        <form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="mid" value="'.$mid.'" />
            <p><strong>You are about to delete this mosaic, are you sure?</strong></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_delete", "Delete")).'" />
            </fieldset>
        </fieldset>
    	</form>
        ';
        echo treelineBox($page_html, "Confirm mosaic delete: ".$mos->title, "blue");
    }

    else if ($tid>0 && $action=="delete-tile") {
	
        $mos->loadTileByID($tid);
        $page_html = '
        <p><a href="/treeline/mosaic.php?action=edit-tiles&amp;mid='.$mid.'">Manage tiles</a></p>
        <form id="treeline" enctype="" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
        <fieldset>
            <input type="hidden" name="action" value="'.$action.'" />
            <input type="hidden" name="mid" value="'.$mid.'" />
            <input type="hidden" name="tid" value="'.$tid.'" />
            <p><strong>You are about to delete this tile, are you sure?</strong></p>
            <fieldset class="buttons">
                <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_delete", "Delete")).'" />
            </fieldset>
        </fieldset>
    	</form>
        ';
        echo treelineBox($page_html, "Confirm tile delete: ".$mos->ttitle, "blue");
    }

    // If we didnt find anything to do and we dont have a guid passed then just show selectable files.
    else if ($tid && $mid && $action=="edit-tile") {
        print "Edit a tile...<br>\n";
    }
    else if ($mid && $action=="edit-tiles" ) {
        ?>
        <h2 class="pagetitle rounded">Search for a tile to manage</h2>
        <?php

        $mos->loadByID($mid);
        $page_html = '
            <p>
                <a href="/treeline/mosaic.php?mid='.$mos->id.'&amp;action=create-tile">Create a new tile</a>
                |
                <a href="/treeline/mosaic.php?action=edit">Manage mosaics</a>
            </p>
            <form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
            <fieldset>
                <input type="hidden" name="action" value="'.$action.'" />
                <label for="ssearch">'.ucfirst($page->drawLabel("tl_generic_keywords", "Kewords")).':</label>
                <input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />
                <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_search", "Search")).'">
                </fieldset>
            </fieldset>
            </form>
            ';
        echo treelineBox($page_html, "Search for a tile by title", "blue");
		
        echo $mos->drawTileList($thispage, $ssearch);
    }
    else if (!$mid ) {

        ?>
        <h2 class="pagetitle rounded">Search for a mosaic to manage</h2>
        <?php

        $page_html = '
            <p><a href="/treeline/mosaic.php?action=create">Create a new mosaic</a></p>
            <form id="treeline" action="'.$_SERVER['REQUEST_URI'].($DEBUG?'?debug':"").'" method="post">
            <fieldset>
                <input type="hidden" name="action" value="search" />
                <label for="ssearch">'.ucfirst($page->drawLabel("tl_generic_keywords", "Kewords")).':</label>
                <input type="text" class="text" name="q" id="f_ssearch" value="'.$ssearch.'" />
                <fieldset class="buttons">
                    <input type="submit" class="submit" value="'.ucfirst($page->drawLabel("tl_generic_search", "Search")).'">
                </fieldset>
            </fieldset>
            </form>
            ';
        echo treelineBox($page_html, "Search for a mosaic by title", "blue");
		
        echo $mos->drawMosaicList($thispage, $ssearch);
    }

    // Erm, got a guid and action but didnt find anything to process it???	
    else {
        print "eek, got mid($mid) and action($action) but could not process<br>\n";
        ?>
            <p>Please go back and try again.</p>
        <?php 
    }

    ?>
</div>
</div>

<?php 
echo $page->initCKE();
?>
<script type="text/javascript">
    CKEDITOR.replace('image', { toolbar:'contentImageOnly', width:'400px', height:'300px' } );
    CKEDITOR.replace('tdescription', { toolbar:'contentImageLink', width:'400px', height:'200px' } );
</script>
<?php
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>
