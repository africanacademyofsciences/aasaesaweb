<?php if ($action=="edit") $title=$panel->getTitle();  ?>

	<form id="treeline" action="<?=$_SERVER['REQUEST_URI']?><?php if ($DEBUG) echo '?debug'?>" method="post">
        <fieldset>
            <legend><?=ucfirst(substr($action,0,-4))?> subscribe panel <?=(($title)?": ".$title:"")?></legend>
            <input type="hidden" name="action" value="<?=$action?>" />
            <input type="hidden" name="guid" value="<?=$guid?>" />
            <input type="hidden" name="mode" value="<?=$mode?>" />
            <p class="instructions">To create a new panel, please complete the form below:</p>
            <div>
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" value="<?=$title?>"/>
            </div>
            <fieldset>
                <legend>Appearance:</legend>
				<?php
					$currentStyle = ($_POST['style']) ? $_POST['style'] : $panel->style_id;
					$currentStyle = ($currentStyle) ? $currentStyle : 8;
					include($_SERVER['DOCUMENT_ROOT'] . "/treeline/includes/functions/pages.php");
					echo drawStyleList($currentStyle, 6);	// The six means draw the styles availble for panels (which have a template id of 6)
		
				?>
            </fieldset>    
            <fieldset class="buttons">		
            	<button type="submit" class="submit">Save &amp; Create Content</button>
            </fieldset>
        </fieldset>
    </form>	
