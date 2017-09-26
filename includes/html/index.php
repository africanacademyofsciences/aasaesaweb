<?php

/*
        ___  _____ ___  ___  ____
|\   | /   \   |    |  /   \ |           /\
| \  | |   |   |    |  |     |___       /  \ 
|  \ | |   |   |    |  |     |         /  | \
|   \| \___/   |   _|_ \___/ |___     /   |  \
                                     /    o   \
									 -----------

THIS FILE IS NO LONGER USED FOR THE HOMEPAGE. 

USE index2.php INSTEAD

*/

$query = "SELECT * from slideshows WHERE msv=1";
if ($results = $db->get_results($query)) {
	$i = 0;
	foreach ($results as $result) {
		print "<!-- Got a slide(".print_r($result, 1)." -->\n";
		$icons[$i]= trim(str_replace(">&nbsp;<", "><", $result->image));
		$carouselHTML .= '
			<div class="item '.($i?'':'active').'">
				<div class="carousel-caption">
					<div class="circle">'.$icons[$i].'</div>
					'.str_replace('a href', 'a class="btn btn-primary" href', $result->secondline).'
					<!-- <div><a href="#" class="btn btn-primary">Call to action</a></div> -->
				</div>
			</div>
		';
		$i++;
	}
}

?>

<!--	Carousel area
====================================== -->
<section class="carousel-background">
	<div id="carousel1" class="carousel slide" data-ride="carousel">
		<ol class="carousel-indicators valign hidden-xs">
        	<?php
			$iconcount = count($icons);
			for ($i = 0; $i<$iconcount; $i++) {
				$iconHTML .= '<li data-target="#carousel1" data-slide-to="'.$i.'" class="'.($i?'':'active').'"><div class="circle">'.$icons[$i].'</div></li>'."\n";
			}
			echo $iconHTML;
			?>
            <!-- 
			<li data-target="#carousel1" data-slide-to="0" class="active"><div class="circle"><i class="ion-ios-flask-outline"></i></div></li>
			<li data-target="#carousel1" data-slide-to="1"><div class="circle"><i class="ion-ios-lightbulb-outline"></i></div></li>
			<li data-target="#carousel1" data-slide-to="2"><div class="circle"><i class="ion-ios-flame-outline"></i></div></li>
			<li data-target="#carousel1" data-slide-to="3"><div class="circle"><i class="ion-ios-pulse"></i></div></li>
			<li data-target="#carousel1" data-slide-to="4"><div class="circle"><i class="ion-ios-download-outline"></i></div></li>
            -->
		</ol>
		
		<div class="container">
			<div class="carousel-inner" role="listbox">
        	
	            <?=$carouselHTML?>
			
				<?php
                /*            
                <div class="item active">
                    <div class="carousel-caption">
                        <div class="circle"><i class="ion-ios-flask-outline"></i></div>
                        <h1>Headline using fewer than eight words</h1>
                        <p class="lead">Google only displays the first 8 words of any headline, so using more words doesn't help you to get good Search Engine Optimisation (SEO). Instead, use this subtitle to explain more.</p>
                        <div><a href="#" class="btn btn-primary">Call to action</a></div>
                    </div>
                </div>
                
                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i class="ion-ios-lightbulb-outline"></i></div>
                        <h1>Icons represent disciplines or subject matter</h1>
                        <p class="lead">These come from the <a href="http://ionicons.com/" target="_blank">ionicons</a> icon service, and you can choose to add any icon you wish. You can also edit this text and the text and link of the button.</p>
                        <div><a href="#" class="btn btn-primary">This can be edited</a></div>
                    </div>
                </div>
                
                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i class="ion-ios-flame-outline"></i></div>
                        <h1>The site is responsive and adaptive</h1>
                        <p class="lead">That means it changes size and design to fit different devices (phone, tablet, laptop, desktop). It also means some content vanishes on small screens. This small text doesn't display on phones.</p>
                        <div><a href="#" class="btn btn-primary">This is a link</a></div>
                    </div>
                </div>
                
                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i class="ion-ios-pulse"></i></div>
                        <h1>Examples of icons shown here </h1>
                        <p class="lead">We've identified icons which can represent medicine, research, life-sciences, earth-sciences, physics, biochemistry, mathmatics, computing and astronomy. </p>
                        <div><a href="#" class="btn btn-primary">This is a link</a></div>
                    </div>
                </div>
                
                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i class="ion-ios-download-outline"></i></div>
                        <h1>Don't have too many slides here </h1>
                        <p class="lead">They're useful to introcuce the site, but not many people will scroll through dozens. Focus on what's really important (important downloads, primary business objectives, announcements, new information) </p>
                        <div><a href="#" class="btn btn-primary">Call to action</a></div>
                    </div>
                </div>
                */
                ?>
                            
            </div>
		</div>

		<div class="carousel-control-container">
			<div class="container">
				<a class="left carousel-control" href="#carousel1" role="button" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left ion-ios-arrow-left" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="right carousel-control" href="#carousel1" role="button" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right ion-ios-arrow-right" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
				
				<div class="col-xs-12 col-sm-10 quick-login">
                	<?php
					include($_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php");
                    ?>
				</div>
			</div>
		</div>
        
	</div>
</section>

<!--	About section
====================================== -->
<section>
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<!--<div class="circle"><i class="ion-ios-information-empty"></i></div>-->
				<div class="intro-container">
					<div class="intro-heading intro">
						<h3 class="intro">African Academy of Sciences</h3>
					</div>
					<div class="intro-text intro">
						<h6>Our <strong>vision</strong> is to be the engine for driving <strong>scientific and technologican development</strong> in Africa.</h6>
						<p><a href="<?=$site->link?>enewsletters/">Join our mailing list</a> to get the latest reports, innovations and policies, and to contribute to our goal of delivering a scientific revolution for Africa. </p>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


<!--	Latest updates area
====================================== -->
<section>
	<div class="container">
		<div class="latest-updates">
			<div class="col-xs-12">
				<h3>Latest updates</h3>
				<div class="filter-buttons">
					<a class="btn btn-default"><i class="ion-ios-keypad-outline hidden-xs"></i> All</a>
					<a class="btn btn-primary"><i class="ion-ios-book-outline hidden-xs"></i> News</a>
					<a class="btn btn-success"><i class="ion-ios-list-outline hidden-xs"></i> Publications</a>
					<a class="btn btn-warning"><i class="ion-ios-calendar-outline hidden-xs"></i> Events</a>
					<a class="btn btn-info"><i class="ion-ios-chatboxes-outline hidden-xs"></i> Blogs</a>
				</div>
			</div>
		</div>
		
		<div class="col-xs-12">
			<ul class="filter-list">
				<li class="blog">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-chatboxes-outline"></i>
							<h6>This is the blog title, about 8 words</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							This is about 20 words which can be automatically derived from the page content, or can be manually added when you create the page
						</div>
					</a>
				</li>
				
				<li class="event">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-calendar-outline"></i>
							<h6>Announce an event with this box</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> Location</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							The height of these boxes depends on the amount of content they contain, but they'll all be the same height on each row.
						</div>
					</a>
				</li>
				
				<li class="publication">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-list-outline"></i>
							<h6>A publication title, ideally using about 8 words</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							Use this abstract to describe the content and encourage users to click. It also helps with SEO.
						</div>
					</a>
				</li>
				
				<li class="news">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-book-outline"></i>
							<h6>A news headline appears here</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							And sometimes you might want to use less text here.
						</div>
					</a>
				</li>
				
				<li class="publication">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-list-outline"></i>
							<h6>A publication title, ideally using about 8 words</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							Use this abstract to describe the content and encourage users to click. It also helps with SEO.
						</div>
					</a>
				</li>
				
				

				<li class="news">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-book-outline"></i>
							<h6>A news headline appears here</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							And sometimes you might want to use less text here.
						</div>
					</a>
				</li>
				
				<li class="blog">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-chatboxes-outline"></i>
							<h6>This is the blog title, about 8 words</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> William McKenzie</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							This is about 20 words which can be automatically derived from the page content, or can be manually added when you create the page
						</div>
					</a>
				</li>
				
				<li class="event">
					<a href="#" class="filter-link">
						<div class="title">
							<i class="ion-ios-calendar-outline"></i>
							<h6>Announce an event with this box</h6>
						</div>
						<div class="meta">
						<p><i class="ion-ios-person-outline"></i> Location</p>
						<p><i class="ion-ios-clock-outline"></i> DD MM YYYY</p>
						</div>
						<div class="abstract">
							The height of these boxes depends on the amount of content they contain, but they'll all be the same height on each row.
						</div>
					</a>
				</li>
			</ul>
		</div>
	</div>
	
</section>



<section class="testimonial">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<?php
                if ($mode=="edit") {
					?>
                    <h3 class="instructions">This is the testimonials section</h3>
                    <p>You can use this section for any content however the intention is that you should add an image, a level 3 heading and a paragraph of text to credit the author.</p>
                    <?php
					echo $news1->draw();
					echo $news2->draw();
					echo $news3->draw();
				}
				else {
					$items = array($news1, $news2, $news3);
					$testHTML = array();
					$i = 0;
					foreach ($items as $item) {
						$itemContent = $item->draw();
						//print "c($itemContent)<br>\n";
						if (preg_match("/img(.*?)src=\"(.*?)\"(.*?)<h3>(.*?)<\/h3>(.*)/ms", $itemContent, $reg)) {
							//print "Matched(".print_r($reg, 1).")<br>\n";
							$testHTML[$i] = '						
							<div class="item">
								<img src="'.$reg[2].'" class="valign" alt="Person photo">
								<div class="content">
									<h3>'.$reg[4].'</h3>
									'.$reg[5].'
								</div>
							</div>
							';
						}
						else $testHTML[$i] = '<div class="item"><div class="user">'.$itemContent.'</div></div>'."\n";
						$i++;
					}
					?>
					<div class="owl-carousel">
                    	<?php
						foreach ($testHTML as $item) echo $item;
						?>
					</div>
                    <?php
				}
				?>
			</div>
		</div>
	</div>
</section>


<!-- Rollover boxes
================================================== -->    
<section class="programs">  

	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<h3>Discover our programmes</h3>
				
				<div class="flip-cards">
					<ul class="card-list">
						<li>
							<a href="/programmes/circle/">
							<div class="card-front" id="card-front-one">
								<div class="contents">
									<h4>Circle</h4>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis condimentum.</p>
								</div>
								<div class="card-bg"></div>
							</div>
							<div class="card-back">
								<h4>Find out more about this programme</h4>
							</div>
							</a>
						</li>
						<li>
							<a href="/programmes/easa/">
							<div class="card-front" id="card-front-two">
								<div class="contents">
									<h4>EASA</h4>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis condimentum.</p>
								</div>
								<div class="card-bg"></div>
							</div>
							<div class="card-back">
								<h4>Find out more about this programme</h4>
							</div>
							</a>
						</li>
						<li>
							<a href="/programmes/cbrm/">
							<div class="card-front" id="card-front-three">
								<div class="contents">
									<h4>CBRM</h4>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis condimentum.</p>
								</div>
								<div class="card-bg"></div>
							</div>
							<div class="card-back">
								<h4>Find out more about this programme</h4>
							</div>
							</a>
						</li>
						
						<li>
							<a href="/programmes/science-equipment/">
							<div class="card-front" id="card-front-four">
								<div class="contents">
									<h4>Science Equipment</h4>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis condimentum.</p>
								</div>
								<div class="card-bg"></div>
							</div>
							<div class="card-back">
								<h4>Find out more about this programme</h4>
							</div>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
 
		
	
	</div>
</section>



