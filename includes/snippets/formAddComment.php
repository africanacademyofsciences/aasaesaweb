<?php
	$feedback='error';
	$message=array();
	$f_name=$f_comment='';	
	
	// Do we need to run any searchings?
	if ($_SERVER['REQUEST_METHOD']=="POST" && $_POST['comment_flag']) {

		$f_name=$_POST['name'];
		$f_email=$_POST['email'];
		$f_comment=$_POST['comment'];
		$f_terms = $_POST['terms']+0;
		$f_country = $_POST['country']+0;
			
		if (!$f_name) $message[]="You must enter your name";
		if (!$f_country) $message[] = "You must select a country";
		if (!$f_email) $message[]="You must enter your email address";
		else if (!is_email($f_email)) $message[]="Your email address[$f_email] is not valid";
		if (!$f_comment) $message[]="You must enter a comment";
		else if ($f_comment=="Type your comment here") $message[] = "You must enter a comment";
		if (!$recaptcha->validate()) foreach ($recaptcha->errmsg as $tmp) $message[] = $tmp;
		if ($site->getConfig("setup_use_captcha") && !$captcha->valid) $message[]="You must enter the confirmation text correctly.";
		if (!$f_terms) $message[] = "You must accept the terms and conditions";
		
		if (!$message) {

			if ($comment->add($f_name, $f_email, $f_comment, $f_country)) {
				$feedback="success";
				$message[]="Thank you for adding your comments to this page. Once your comment has been checked by a website administrator it will be added to the website";
				$send_data=array();
				$f_name=$f_email=$f_comment=$f_country=$f_terms=$_POST['comment_flag']='';	

				if(addHistory(0, 'Publish', $page->getGUID(), "Comment ".$comment->id." saved", "pages-comments")) {
					// Its not critical if page saves fail to register in the history table.
					$tasks=new Tasks($site->id);
					$pageLink='(<a href="'.$page->drawLinkByGUID($page->getGUID()).'">'.$page->getTitle().'</a>)';
					$sendParams = array(
						"PAGELINK"=>$pageLink
						);
					$tasks->notify("publish-comment", $sendParams, 'Publisher+');
				}
			}
			else $message=$comment->msg;
		}
		
		echo drawFeedback($feedback, $message);
	}
	
	
	$query = "SELECT country_id, title FROM store_countries ORDER BY title";
	if ($results = $db->get_results($query)) {
		foreach ($results as $result) {
			$countryOpts .= '<option value="'.$result->country_id.'" '.($result->country_id==$f_country?'selected="selected"':"").'>'.$result->title.'</option>'."\n";
		}
	}
	
?>

<div class="add-comment">
    <h4 class="text-info">
        <strong>Comments</strong>
    </h4>	
    
    <a class="btn btn-link btn-sm" role="button" data-toggle="collapse" href="#addComment" aria-expanded="false" aria-controls="addComment"><i class="ion-ios-chatboxes-outline"></i> Add a comment</a>
</div>
				
<div class="collapse <?=($_POST['comment_flag']==1?"in":"")?>" id="addComment">
    <div class="well">
		<form method="POST" id="f-add-comment" action="#comments">
		    <input type="hidden" name="comment_flag" value="1" />
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="commentName" class="sr-only">Your name</label>
                            <input type="text" name="name" class="form-control" value="<?=$f_name?>" id="commentName" placeholder="Your name">
                        </div>
                        <div class="form-group">
                            <label for="commentCountry" class="sr-only">Your country</label>
                            <select name="country" class="form-control" id="commentCountry">
                                <option value="0">Select country</option>
                            	<?=$countryOpts?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="commentEmail" class="sr-only">Email address</label>
                            <input name="email" type="email" class="form-control" id="commentEmail" placeholder="Your email address" value="<?=$f_email?>" />
                        </div>
                        <div class="" style="margin-bottom: 10px;">
                            <label for="recapture" style="visibility:hidden;">Recapture</label>
                            <?php
                            echo $recaptcha->draw("page-comment");
                            ?>
                        </div>

                        <label>
                            <input type="checkbox" name="terms" value="1" <?=($f_terms?'checked="checked"':"")?> /> I agree to the <a title="Terms and conditions" data-original-title="" href="<?=$site->link?>terms/">terms and conditions</a>
                        </label>
                        <p class="small text-muted">Your email address is only required for security purposes. It will not be displayed, or used for marketing.</p>
                    </div>
    
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="commentText" class="sr-only">Your comment</label>
                            <textarea rows="7" class="form-control commentbox" id="commentText" name="comment"><?=($f_comment?$f_comment:"Type your comment here")?></textarea>
                        </div>
                        <button type="submit" class="btn btn-info btn-sm pull-right"><i class="ion-checkmark"></i> Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

