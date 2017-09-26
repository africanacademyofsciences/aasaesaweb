<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//include($_SERVER['DOCUMENT_ROOT'].'/index.php');

include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/header.inc.php');

$query = "SELECT * FROM slideshows WHERE msv=".$site->id." ORDER BY sortorder";

$results = $db->get_results($query);

$count = 0;
foreach ($results as $result)
{
	if ($count == 0)
	{
		$first = 'active';
	}
	else
	{
		$first = '';
	}
	
    preg_match( '/src="([^"]*)"/i', $result->image, $array );
	$slideshowImage = $array[1];
	$slideshowContent = $result->secondline;
	
	$slideshowHtml .= '
	<div class="item '.$first.'" style="background-image: url(\''.$slideshowImage.'\'); background-size: cover;">
      <!--<img src="'.$slideshowImage.'" alt="Chania">-->
	  <div class="carousel-caption">
        '.$slideshowContent.'
      </div>
    </div>
	';
	
	$count++;
}

?>
<section class="" style="background-color: #FFFFFF;padding-bottom:0px; padding-top: 0px;">
<!--<div class="bk-image">

</div>-->
<div id="myCarousel" class="carousel slide" data-ride="carousel">

  <!--<ol class="carousel-indicators">
    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
    <li data-target="#myCarousel" data-slide-to="1"></li>
    <li data-target="#myCarousel" data-slide-to="2"></li>
    <li data-target="#myCarousel" data-slide-to="3"></li>
	</ol>-->

  <div class="carousel-inner" role="listbox">
   <?=$slideshowHtml?>
  </div>

  <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>
</section>

<!--  home-tagline -->
<div class="col-xs-12 text-center">
<?=$tagline->draw();?>
<!--<strong><em>Shifting the centre of gravity for African science to Africa</em></strong>-->
</div>

<section>
    <div class="container">
        <div class="row home-blocks">
            <div class="col-xs-12">
			<div class="row">
				<div class="col-sm-4 col-xs-12 orange-background match">
					<div class="inner">
						<?=$contentBox1->draw()?>
					</div>
				</div>
				<div class="col-sm-4 col-xs-12 blue-background match">
					<div class="inner">
						<?=$contentBox2->draw()?>
					</div>
				</div>
				<div class="col-sm-4 col-xs-12 orange-background match">
					<div class="inner">
						<?=$contentBox3->draw()?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4 col-xs-12 blue-background match2">
					<div class="inner">
						<?=$contentBox4->draw()?>
					</div>
				</div>
				<div class="col-sm-8 col-xs-12 orange-background match2">
					<div class="inner">
						<?=$contentBox5->draw()?>
					</div>
				</div>
			</div>
			<div class="row">
			</div>
				<!--<div class="row">
					<div class="col-xs-3">
						<a href="<?=$site->link?>programmes/circle/">
						<div class="col-xs-12 green-background">
							<h2 class="green">Events</h2>
							<p>CIRCLE is a PROGRAMME to develop the skills and research results 
							FOR early career African researchers in the field of climate change.</p>
						</div>
						</a>
					</div>
					<div class="col-xs-3">
						<a href="<?=$site->link?>programmes/deltas/">
						<div class="col-xs-12 blue-background">
							<h2 class="blue">Connect</h2>
							<ul class="blue">
								<li>All of the social channels</li>
								<li>Events</li>
								<li>Contact</li>
							</ul>
						</div>
						</a>
					</div>
					<div class="col-xs-3">
						<a href="<?=$site->link?>programmes/gca/">
						<div class="col-xs-12 green-background">
							<h2 class="green">Media</h2>
							<ul>
								<li>News</li>
								<li>Press Releases</li>
								<li>Contact info</li>
							</ul>
						</div>
						</a>
					</div>
					<div class="col-xs-3">
						<a href="<?=$site->link?>programmes/gfgp/">
						<div class="col-xs-12 blue-background">
							<h2 class="blue">Explore / Resources</h2>
							<ul class="blue">
								<li>Policies</li>
								<li>Reports</li>
								<li>Other publications</li>
							</ul>
						</div>
						</a>
					</div>
				</div>-->
				<!--<div class="row" style="padding-top: 16px;">
				<div class="col-xs-4">
					<a href="<?=$site->link?>programmes/gfgp/">
					<div class="col-xs-12 green-background">
						<h2 class="green">GFGP</h2>
						<p>The Good Financial Grant Practice (GFGP) iS a new PROGRAMME under the
						AESA platform that will involve the development of a pan African standard.</p>
					</div>
					</a>
				</div>
				<div class="col-xs-4">
					<a href="<?=$site->link?>programmes/circle/">
					<div class="col-xs-12 blue-background">
						<h2 class="blue">CIRCLE</h2>
						<p>CIRCLE is a PROGRAMME to develop the skills and research results 
						FOR early career African researchers in the field of climate change.</p>
					</div>
					</a>
				</div>
				<div class="col-xs-4">
					<a href="<?=$site->link?>programmes/deltas/">
					<div class="col-xs-12 green-background">
						<h2 class="green">DELTAS</h2>
						<p>The DELTAS Africa programme, a scheme initiated by the Wellcome 
						Trust in partnership with AESA and other partners, supports the 
						African-led development of world class researchers and research leaders in Africa.</p>
					</div>
					</a>
				</div>
				</div>-->
			</div>
		</div>
	</div>
</section>



<?php

	include($_SERVER['DOCUMENT_ROOT'].'/includes/html/18/footer.inc.php'); 
?>
