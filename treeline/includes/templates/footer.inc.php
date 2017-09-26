      <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/contentcurve.inc.php"); ?>
    </div>
    <?php // include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/menu.inc.php"); ?>
    <?php include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/bottomcurve.inc.php"); ?>
  </div>
</div>
<div id="footer">
	<ul>
    	<?php if(!$noMenu) { ?>
		<li id="terms-link" class="first footer-left"><a href="/treeline/terms/"><?=$page->drawLabel("tl_foot_terms", "Terms and conditions")?></a></li>
	    <li id="about-link" class="footer-left"><a href="http://www.treelinesoftware.com" target="_blank"><?=$page->drawLabel("tl_foot_about", "About Treeline")?></a></li>
	    <!-- <li id="accessibility-link" class="footer-left"><a href="/treeline/accessibility/" accesskey="0"><?=$page->drawLabel("tl_foot_accessibility", "Accessibility information")?></a></li> -->
	    <li id="feedback-link" class="footer-left"><a href="http://treeline.freshdesk.com/support/home" target="_blank" accesskey="9"><?=$page->drawLabel("tl_foot_feedback", "Feedback")?></a></li>
	    <!-- <li id="requests-link" class="footer-left"><a href="/treeline/requests/"><?=$page->drawLabel("tl_foot_funx", "Function requests")?></a></li> -->
	    <!-- <li id="bugs-link" class="footer-left"><a href="/treeline/bugs/">Bug reports</a></li> -->
        <? } ?>
	    <li class="footer-left"><a href="http://www.treelinesoftware.com/" target="_blank">&copy;<?=date('Y')?> Treeline Software Ltd.</a></li>
	</ul>
</div>

<div id="nfoholder" style="display: none; visibility: hidden;">
<div id="nfotopline"></div>
<div id="nfotext">
<p class="bodytext"><span id="nfo"><?=$page->drawLabel("tl_foot_welcome", "Welcome to Treeline")?></span></p></div>
</div>

<?php 
if (!$skipSupport) {
	?>
	<script type="text/javascript" src="http://assets.freshdesk.com/widget/freshwidget.js"></script>
	<script type="text/javascript">
		FreshWidget.init("", {"queryString": "&widgetType=popup&submitThanks=Thank+you+for+contacting+support.+We'll+review+your+message+and+respond.", "widgetType": "popup", "buttonType": "text", "buttonText": "Support", "buttonColor": "white", "buttonBg": "#285965", "alignment": "4", "offset": "235px", "submitThanks": "Thank you for contacting support. We'll review your message and respond.", "formHeight": "500px", "url": "http://treeline.freshdesk.com"} );
	</script>    
    <?php
}

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/commonJSBottom.inc.php"); ?>

</body>


</html>

