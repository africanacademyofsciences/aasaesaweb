<?php
	if ($mode=="preview" && $showPreviewMsg) {
		$previewMsgShown=true;
		?>
        <p id="preview_msg">
        	<span>
            	<?=$page->getLabel("tl_inl_preview_message1", true)?>. <a href="javascript:self.close();"><?=$page->getLabel("tl_inl_preview_close")?></a> <?=$page->getLabel("tl_inl_preview_message2", true)?>.
            </span>
        </p>
		<?php
	}

?>