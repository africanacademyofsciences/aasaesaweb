
<?php if ($page->getMode()=="edit") { ?>


	<p>Form disabled in edit mode</p>


<?php } else { ?>

                <form id="form-where-we-work" method="POST" action="/" class="homepage-form">
		        <fieldset>
                	<select name="country-map" onchange="go(this.value);">
                    	<option value="/"><?=$labels['SELECT']['txt']?></option>
                        <!-- <option value="/"><?=$labels['UK']['txt']?></option> -->
                        <option value="/"><?=$labels['MAIN_MAG']['txt']?></option>
                        <option value="/usa"><?=$labels['USA']['txt']?></option>
                    </select>
			        <input type="submit" value="<?=$labels['OK']['txt']?>" class="submit" />
                </fieldset>
                </form>


<?php } ?>
