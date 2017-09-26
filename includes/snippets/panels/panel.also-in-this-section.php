
<!-- ALSO IN THIS SECTION PANEL ptm(<?=(time()-$global_start_time)?>) -->

<?php
global $pageGUID; 

//print "Get content for page(".$pageGUID.")<br>\n";
//ini_set("display_errors", 1);


if (!is_object($menu)) global $menu;
if (is_object($menu)) {
	$p = new Page();
	$p->loadByGUID($pageGUID);
	$sectionGUID = $p->getPrimary();
	if ($site->id == 18)
	{
		$section_menu = $menu->drawSecondaryByParent($sectionGUID, $pageGUID);
	}
	else
	{
		$section_menu = $menu->drawSecondaryByParent($sectionGUID, $pageGUID);
	}
	
	//print "sm($section_menu)<br>\n";
	if ($section_menu) {
		if (preg_match_all("/level-(.*)\"/", $section_menu, $reg, PREG_SET_ORDER)) {
			//print "<!-- got levels(".print_r($reg, 1).") --> \n";
			foreach ($reg as $lv) if ($lv[1]>$alsomaxlevel) $alsomaxlevel = $lv[1];
		}
		//print "ds($sectionGUID) p($pageGUID) max($alsomaxlevel) sm($section_menu) \n";

		?>
		<div class="" id="also-in-this-section">
			<?php
			if ($section_menu) {
				?>
				<!-- TAB 1 -->
				<div class="panel panel-warning">
					
					<div class="panel-heading">
						<h3 class="panel-title">Also in this section</h3>
					</div>
					<div class="panel-body">
						<ul class="page-listing">
							<?=$section_menu?>
						</ul>
					</div>
					
				</div>
				<!-- END TAB 1 -->
				<?php
			}
			?>	
		</div>
		<script>
			$( ".page-listing li" ).each(function( index ) {
				
				
				$('a:first-child', this).click(function(event) {
					//.preventDefault();
				});
				
				if (!$(this).hasClass( "subon" ))
				{
					$('ul', this).hide();
				}
				if ($('ul li', this).hasClass("subon"))
				{
					$('ul', this).show();
				}
				//$(this).click(function(){
				//	$(this).find('ul').toggle();
				//});
			});
		</script>
		<?php
	
	
	}
}
?>
<!-- // ALSO IN THIS SECTION PANEL ptm(<?=(time()-$global_start_time)?>) -->