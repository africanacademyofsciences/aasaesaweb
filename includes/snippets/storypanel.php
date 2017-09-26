<?php if($page->getMode() == 'edit'){ ?>
<script type="text/javascript">
	function updateThis(value){
		document.forms[0].submit();
	}
</script>
<?php } ?>
<div id="storypanel">
	<?php
            if($page->getMode() == 'edit'){
                $stories = $story->getStoriesList();
            ?>
            <select name="story_guid" id="story_guid"  onchange="updateThis(this)">
                <option value="">Pick a story</option>
                <option value=""<?=(!$story_guid) ? ' selected="selected"' : '' ?>>Random</option>
                <?php 
                foreach($stories as $this_story){ 
                    $selected = ($this_story['guid'] == $story_guid) ? ' selected="selected"': '';
                ?>
                <option value="<?=$this_story['guid']?>"<?=$selected?>><?=$this_story['title']?></option>
                <?php } ?>
            </select>
     <?php } ?>
    <div id="storyimages">
            <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="213" height="208" id="sidebar_v2" align="middle">
            <param name="allowScriptAccess" value="sameDomain" />
            <param name="movie" value="/sidebar_v2.swf?image1=<?= $story->properties['image1'] ?>&amp;image2=<?= $story->properties['image2'] ?>" />
            <param name="quality" value="high" />
            <param name="wmode" value="transparent" />
            <param name="bgcolor" value="#ffffff" />
            <embed src="/sidebar_v2.swf?image1=<?= $story->properties['image1'] ?>&amp;image2=<?= $story->properties['image2'] ?>" quality="high" wmode="transparent" bgcolor="#ffffff" width="213" height="208" name="sidebar_v2" align="middle" allowScriptAccess="sameDomain" swliveconnect="true" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
            </object>
        </div>
        <div id="storycontent">
            <h2 class="special"><?= $story->properties['name'] ?>'<?=(substr($story->properties['name'], -1) == 's')?'':'s'?> Story</h2>
            <p><?= $story->properties['summary'] ?></p>
            <p><?php
					if ($story->getExtendedStory($story->properties['guid'])) {
						
						$link = $page->drawLinkByGUID($story->properties['guid']);
						echo "<a href=". $link ."> Click here for ".$story->properties['name']."'s Story</a>";
					
					} else {
						echo "&nbsp";
					} ?></p>
            
      </div>
        <div id="storylinks">
            <ul class="multimedia">
            <?= $story->properties['link_text'] ?>
            </ul>
        </div>
        <div id="relatedstories">
            <h2 class="special">Other stories</h2>
            <? 	
                // Do we have any related stories?
                $html = '';
                for($i=1; $i<=3; $i++){
                $key = 'related'.$i;
                if( $story->properties[$key]>'' ){
                    ${$key} = new PersonalStory($story->properties[$key]);
                    $link = $page->drawLinkByGUID( ${$key}->properties['guid'] );
                    ${'link'.$i} = $link;
                    //$html .= "\t".'<li><a href="'. $link .'">'. ${$key}->properties['title'] .'</a></li>'."\n";
                    $tmp = ${$key}->properties['image1'];
                    $imgName = substr($tmp,0,strrpos($tmp,'_'));
                    $imgName = substr($imgName,strrpos($imgName,'/')+1);
                    
                    $query = "SELECT filename FROM images_sizes WHERE filename 
                                LIKE '". $imgName ."%' AND width >90 AND height>90 
                                ORDER BY width ASC,height ASC LIMIT 1";

                   	$src = '/silo/images/'.$db->get_var($query);
                    		${'image'.$i.'url'} = (file_exists($_SERVER['DOCUMENT_ROOT'].$src)) ? $src : '/silo/images/african-child-2_96x130.jpg';
                    unset(${$key}, $key, $link);
                    
                }
            }
            if( $html>'' ){
                echo '<ul id="related_stories">'."\n". $html .'</ul>'."\n";
            }
        ?>
        <p><a href="<?= ($link1) ? $link1 : '/personal-stories/' ?>">Read other personal stories</a></p>
        <div id="relatedImages">
            <img src="<?= ($image1url && file_exists($_SERVER['DOCUMENT_ROOT'].'/silo/images/'.$image1url)) ? '/silo/images/'.$image1url : '/silo/images/asian-man_96x130.jpg' ?>" width="85" alt="" class="imageA" />
            <img src="<?= ($image2url && file_exists($_SERVER['DOCUMENT_ROOT'].'/silo/images/'.$image2url)) ? '/silo/images/'.$image2url : '/silo/images/african-child-2_96x130.jpg' ?>" width="85" alt="" class="imageB" />
            <div id="overlayed">&nbsp;</div>
        </div>
    </div>