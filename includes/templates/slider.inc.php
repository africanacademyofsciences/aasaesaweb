	<?php
	if ($mode != "edit") {
	
		$i = 0;
		$query = "SELECT * FROM slideshows WHERE `msv`='".$site->id."' ORDER BY sortorder ASC ";
		//$query .= "LIMIT 1";
		//echo $query;
		if ($results = $db->get_results($query)) {
			$itemHTML = '';
			foreach($results as $result) {
				$slider_image = pullImage($result->image, true, false);
				//print "Got image($slider_image) from ($result->image)<br>\n";
				if ($slider_image) {
					$slider_url = '';
					if (preg_match("/href=\"(.*?)\"(.*)/", $result->secondline, $reg)) $slider_url = $reg[1];
			
					$itemTitle = '<h3 class="content-title">'.$result->firstline.'</h3>';
					if ($slider_url) $itemTitle = '<h3 class="content-title"><a href="'.$slider_url.'">'.$result->firstline.'</a></h3>';
					
					$itemHTML .= '
					<div class="item '.($i++==0?"active":"").'">
						<img class="full" src="'.$slider_image.'" />
					</div>
					';
					/*
					Full implementation
					$itemHTML .= '
					<div class="item '.($i++==0?"active":"").'">
						<img class="full" src="'.$slider_image.'" />
						<img class="medium" src="'.$slider_image.'" />
						<img class="small" src="'.$slider_image.'" />
						<span class="vcenter">
							<span class="vwrap">
								<div class="message">
									<span class="number">'.$i.'</span>
									'.$itemTitle.'
									'.$result->secondline.'
								</div>
							</span>
						</span>
					</div>
					';
					*/
				}
			}
		}
		
		?>		
		<section id="slider">
			<div id="myCarousel" class="carousel slide">    
				<div class="carousel-inner">
					<?=$itemHTML?>
				</div>
				<!-- Carousel nav -->
				<a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
				<a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>            
			</div>     
		</section>
	
		<?php
	}
	?>
