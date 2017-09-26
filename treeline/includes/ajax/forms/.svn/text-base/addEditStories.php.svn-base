                    <form id="treeline" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
                    	<fieldset>
                    	<input type="hidden" name="guid" value="<?= $guid ?>" />
                        <input type="hidden" name="action" value="<?= $action ?>" />
                        <input type="hidden" name="mode" value="<?= $mode ?>" />
                        	<legend><?= ucfirst($mode) ?> : Personal Story</legend>
                            <p class="instructions">Personal stories also have panels that can be dropped alongside your content and on the homepage of your site.  By filling in the form below, the system will have the details needed to make this for you.</p>
                            
                            <fieldset id="photos">
                            	<legend>Photos</legend>
                                <p class="instructions">Please add one photo that can be used at the top of the story panel.</p>
                                <label for="treeline_image1" class="hide">Photo 1</label>
                            	<textarea name="treeline_image1" id="treeline_image1" class="mceEditor"><?= stripslashes($treeline_image1) ?></textarea>
                               <!-- <label for="treeline_image1" class="hide">Photo 2</label>
                            	<textarea name="treeline_image2" id="treeline_image2" class="mceEditor"><?= stripslashes($treeline_image2) ?></textarea><br />-->
                            </fieldset>
                            
                            <fieldset>
                            	<legend>Details</legend>
                                <div class="hasHelp">
                                    <label for="name">Person's Name</label>
                                    <input type="text" name="name" id="name" value="<?= $name ?>" />
                                    <span class="help">Who is this story about?</span>
                                </div><br />
                                <label for="title">Title</label>
                                <input type="text" name="title" id="title" value="<?= $title ?>" disabled="disabled" class="disabled" /><br />
                                <label for="description">Description</label>
                                <textarea name="description" id="description" disabled="disabled" class="disabled"><?= $meta_desc ?></textarea><br />
                            </fieldset>
                            
                            <fieldset>
                            	<legend>Links</legend>
                                <p class="instructions">Add links to any related pages or files.  Put each link on a new line, Treeline will format them for you.</p>
                                <div class="hasHelp">
                                	<label for="treeline_links" class="hide">Select links</label>
                               		<textarea name="treeline_links" id="treeline_links" class="mceEditor"><?= $treeline_links ?></textarea>
                                    <span class="help">
                                    	To enter links, type the wording for the link itself then click on the 'link' icon then select a page or file to link to. Enter each link on a new line.
                                    </span> 
                                </div>
                            </fieldset>
                            
                            <fieldset>
                            	<legend>Related Stories</legend>
                                <p class="instructions">You can also link this stories to others that have been made.
                                Just choose them from the lists below, you can add up to two or none at all.</p>
                                <label for="related1" class="hide">First related story</label>
                                <?php
                                
                                
                                
								$list = $story->getStoriesList();
								$html = '';
								
								for($i=1; $i<=2; $i++){
									$key = 'related'.$i;
									//echo "<pre>".print_r($list, true)."</pre>"; exit;
									$html .= '<label for="'. $key .'" class="hide">Related story</label>'."\n";
									$html .= '<select name="'. $key .'" class="related">'."\n";
									$html .= "\t".'<option value="">-- Select a story --</option>'."\n";
									foreach($list as $item){
										$selected = (${$key}==$item['guid']) ? ' selected="selected"' : '' ;
										if($item['guid'] != $guid){
											$html .= "\t".'<option value="'. $item['guid'] .'"'. $selected .'>'. $item['title'] .'</option>'."\n";
										}
										else{
											$html .= '';
										}
										
									}
									$html .= '</select>'."\n\n";
								}
								echo $html;
								
								?>
                            </fieldset>
                            
                            <fieldset id="controls">
                                <button type="submit" name="save" class="submit" id="save">Save</button>
                                <span>- or -</span>
                                <button type="submit" name="content" class="submit" id="content">Save &amp; Add full story</button>
                            </fieldset>
                            
                        </fieldset>   
                    </form>