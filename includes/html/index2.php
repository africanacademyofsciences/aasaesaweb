<?php

$query = "SELECT * from slideshows WHERE msv=1 ORDER BY sortorder ASC";
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

<!--    Carousel area
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
            <li data-target="#carousel1" data-slide

-to="0" class="active"><div class="circle"><i 

class="ion-ios-flask-outline"></i></div></li>
            <li data-target="#carousel1" data-slide

-to="1"><div class="circle"><i class="ion-ios-

lightbulb-outline"></i></div></li>
            <li data-target="#carousel1" data-slide

-to="2"><div class="circle"><i class="ion-ios-flame

-outline"></i></div></li>
            <li data-target="#carousel1" data-slide

-to="3"><div class="circle"><i class="ion-ios-

pulse"></i></div></li>
            <li data-target="#carousel1" data-slide

-to="4"><div class="circle"><i class="ion-ios-

download-outline"></i></div></li>
            -->
        </ol>

        <div class="container">
            <div class="carousel-inner" role="listbox">

                <?=$carouselHTML?>

                <?php
                /*
                <div class="item active">
                    <div class="carousel-caption">
                        <div class="circle"><i 

class="ion-ios-flask-outline"></i></div>
                        <h1>Headline using fewer 

than eight words</h1>
                        <p class="lead">Google only 

displays the first 8 words of any headline, so using 

more words doesn't help you to get good Search 

Engine Optimisation (SEO). Instead, use this 

subtitle to explain more.</p>
                        <div><a href="#" class="btn 

btn-primary">Call to action</a></div>
                    </div>
                </div>

                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i 

class="ion-ios-lightbulb-outline"></i></div>
                        <h1>Icons represent 

disciplines or subject matter</h1>
                        <p class="lead">These come 

from the <a href="http://ionicons.com/" 

target="_blank">ionicons</a> icon service, and you 

can choose to add any icon you wish. You can also 

edit this text and the text and link of the 

button.</p>
                        <div><a href="#" class="btn 

btn-primary">This can be edited</a></div>
                    </div>
                </div>

                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i 

class="ion-ios-flame-outline"></i></div>
                        <h1>The site is responsive 

and adaptive</h1>
                        <p class="lead">That means 

it changes size and design to fit different devices 

(phone, tablet, laptop, desktop). It also means some 

content vanishes on small screens. This small text 

doesn't display on phones.</p>
                        <div><a href="#" class="btn 

btn-primary">This is a link</a></div>
                    </div>
                </div>

                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i 

class="ion-ios-pulse"></i></div>
                        <h1>Examples of icons shown 

here </h1>
                        <p class="lead">We've 

identified icons which can represent medicine, 

research, life-sciences, earth-sciences, physics, 

biochemistry, mathmatics, computing and astronomy. 

</p>
                        <div><a href="#" class="btn 

btn-primary">This is a link</a></div>
                    </div>
                </div>

                <div class="item">
                    <div class="carousel-caption">
                        <div class="circle"><i 

class="ion-ios-download-outline"></i></div>
                        <h1>Don't have too many 

slides here </h1>
                        <p class="lead">They're 

useful to introcuce the site, but not many people 

will scroll through dozens. Focus on what's really 

important (important downloads, primary business 

objectives, announcements, new information) </p>
                        <div><a href="#" class="btn 

btn-primary">Call to action</a></div>
                    </div>
                </div>
                */
                ?>

            </div>
        </div>

        <div class="carousel-control-container">
            <div class="container">
                <a class="left carousel-control" 

href="#carousel1" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left ion-ios-arrow-left" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#carousel1" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right ion-ios-arrow-right" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>

                <div class="col-xs-12 col-sm-10 quick-login">
				<?php
				if ($_SESSION['member_id']>0) {
					?>
                    <p class="home-members"><a href="<?=$site->link?>member-login/">Member Area</a></p>
                    <?php
				}
				else if ($mode!="edit") {
				$loginforminline = true;
                include($_SERVER['DOCUMENT_ROOT']."/includes/ajax/memberLogin.php");
				unset($loginforminline);
				}
                ?>
                </div>
            </div>
        </div>

    </div>
</section>

<!--    About section
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
                        <h6>Our <strong>vision</strong> is to be the engine for driving <strong>scientific and technological development</strong> in Africa.</h6>
                        <p><a href="<?=$site->link?>enewsletters/">Join our mailing list</a> to get the latest reports, innovations and policies, and to contribute to our goal of delivering a scientific revolution for Africa. </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!--    Latest updates area
====================================== -->
<section>
    <div class="container">
        <div class="latest-updates">
            <div class="col-xs-12">
                <h3>Latest updates</h3>
                <div class="filter-buttons">
                    <a class="btn btn-default"><i class="ion-ios-keypad-outline hidden-xs"></i> All</a>
	                <a class="btn btn-primary" href="/updates/news/"><i class="ion-ios-book-outline hidden-xs"></i> News</a>
                    <a class="btn btn-success" href="/updates/publications/"><i class="ion-ios-list-outline hidden-xs"></i> Publications</a>
                    <a class="btn btn-warning" href="/updates/events/"><i class="ion-ios-calendar-outline hidden-xs"></i> Events</a>
                    <a class="btn btn-info" href="/updates/blogs/"><i class="ion-ios-chatboxes-outline hidden-xs"></i> Blogs</a>
                </div>
            </div>
        </div>

        <div class="col-xs-12">
            <ul class="filter-list">
            	<?php
				//$order = array('blogs', 'events', 'pubs', 'news', 'pubs', 'news', 'blogs', 'events');
				$order = array('blogs', 'events', 'pubs', 'news');
				$types = array();
				$types['blogs']['name']='blog';
				$types['blogs']['icon']='chatboxes';
				$types['events']['name']='event';
				$types['events']['icon']='calendar';
				$types['pubs']['name']='publication';
				$types['pubs']['icon']='list';
				$types['news']['name']='news';
				$types['news']['icon']='book';


				foreach ($order as $i=>$type) {
					$j=$i>3?1:0;
					$item = $latest[$type][$j];
					//print "<!-- Show t($type) ind($j) (".print_r($item, 1).")-->\n";
					if ($types[$type]['name'] == 'blog')
					{
						$color = '#1386ab';
					}
					else if ($types[$type]['name'] == 'event')
					{
						$color = '#f2a900';
					}
					else if ($types[$type]['name'] == 'publication')
					{
						$color = '#249c5a';
					}
					else if ($types[$type]['name'] == 'news')
					{
						$color = '#003a70';
					}
					?>
                    <li class="<?=$types[$type]['name']?>">
                        <a href="<?=$item['link']?>" class="filter-link">
                            <div class="title">
                                <i class="ion-ios-<?=$types[$type]['icon']?>-outline"></i>
                                <h6 style="color: <?=$color?>;"><?=$item['title']?></h6>
                            </div>
                            <div class="meta">
                            <p><i class="ion-ios-<?=($types[$type]['person']?$types[$type]['person']:"person")?>-outline"></i><?=($item['location']?$item['location']:$item['author'])?></p>
                            <p><i class="ion-ios-clock-outline"></i> <?=$item['date']?></p>
                            </div>
                            <div class="abstract">
                                <?=$item['summary']?>
                            </div>
                        </a>
                    </li>
                    <?php
				}

				/*
				?>
                <li class="blog">
                    <a href="/updates/news/message-from-aas-president--prof-kuku/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-chatboxes-outline"></i>
                            <h6>Message from AAS President-Prof Kuku</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Professor Kuku</p>
                        <p><i class="ion-ios-clock-outline"></i> 01 08 2015</p>
                        </div>
                        <div class="abstract">
                            In the last three AAS Newsletters I have endeavoured to report some of the activities of our Governing Council (GC) aimed at fulfilling our mandate.
                        </div>
                    </a>
                </li>

                <li class="event">
                    <a href="/updates/news/aas-first-pan-african-science-olympiad/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-calendar-outline"></i>
                            <h6>AAS' First Pan African Science Olympiad</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Nigeria</p>
                        <p><i class="ion-ios-clock-outline"></i> 22 08 2015</p>
                        </div>
                        <div class="abstract">
                            AAS' Commission on Science Olympiad is delighted to invite a team to the 1st Pan African Science olympiad.                       </div>
                    </a>
                </li>

                <li class="publication">
                    <a href="/updates/news/call-to-submit-articles-for-the-aas-newsletter-on-the-history-of-sciences-and-indigenous-knowledge-systems/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-list-outline"></i>
                            <h6>AAS' Commission on African Heritage is calling for articles</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> William McKenzie</p>
                        <p><i class="ion-ios-clock-outline"></i> 06 09 2015</p>
                        </div>
                        <div class="abstract">
                            AAS' Commission on African Scientific Heritage is calling for articles on the history of sciences and indigenous knowledge systems.
                        </div>
                    </a>
                </li>

                <li class="news">
                    <a href="/updates/news/aas-and-partners-to-launch-aesa-the-funding-platform-for-research/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-book-outline"></i>
                            <h6>AAS and partners to launch AESA</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Deborah-Fay Ndlovu</p>
                        <p><i class="ion-ios-clock-outline"></i> 07 10 2015</p>
                        </div>
                        <div class="abstract">
                            AAS and the New Partnership for Africa’s​ Development launch Accelerating Excellence in Science in Africa (AESA) on the 10th September.
                        </div>
                    </a>
                </li>

                <li class="publication">
                    <a href="/updates/news/innovation-prize-for-africa/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-list-outline"></i>
                            <h6>Innovation Prize for Africa</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Stuart Johnson</p>
                        <p><i class="ion-ios-clock-outline"></i> 07 09 2015</p>
                        </div>
                        <div class="abstract">
The United Nations Economic Commission for Africa (ECA) and the African Innovation Foundation (AIF) are delighted to announce the Innovation Prize for Africa (IPA) to be awarded for the first time in February 2012. 
                        </div>
                    </a>
                </li>



                <li class="news">
                    <a href="/updates/publications/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-book-outline"></i>
                            <h6>Science policy africa</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Deborah-Fay Ndlovu</p>
                        <p><i class="ion-ios-clock-outline"></i> 27 08 2015</p>
                        </div>
                        <div class="abstract">
                            Is a quarterly newsletter of the African Academy of Sciences. The Newsletter carries information on science and policy issues on the African continent and beyond.
                        </div>
                    </a>
                </li>

                <li class="blog">
                    <a href="/programmes/circle/climate-impact-research-capacity-and-leadership-enhancement-circle/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-chatboxes-outline"></i>
                            <h6>Climate Impact Research Capacity and Leadership Enhancement (CIRCLE)</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> William McKenzie</p>
                        <p><i class="ion-ios-clock-outline"></i> 03 07 2015</p>
                        </div>
                        <div class="abstract">
                            CIRCLE is an initiative to develop the skills and research output of early career African researchers in the field of climate change.
                        </div>
                    </a>
                </li>
                
                <li class="event">
                    <a href="/updates/events/the-9th-international-conference-on-communitybased-adaptation-cba9/" class="filter-link">
                        <div class="title">
                            <i class="ion-ios-calendar-outline"></i>
                            <h6>9th International Conference on Community-Based Adaptation (CBA9)</h6>
                        </div>
                        <div class="meta">
                        <p><i class="ion-ios-person-outline"></i> Nairobi</p>
                        <p><i class="ion-ios-clock-outline"></i> 24 04 2015</p>
                        </div>
                        <div class="abstract">
                            The 9th International Conference on Community-Based Adaptation (CBA9) will take place in Nairobi, Kenya from 24-30 April, 2015.                       </div>
                    </a>
                </li>
				<?php
				*/
				?>
                

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
                else if ($site->id == 19) {
					?>
                    <div class="gca-content">
                    	<?=$news1->draw()?>
                        <?=$news2->draw()?>
                        <?=$news3->draw()?>
                    </div>
                    <?php
				}
				else {
                    $items = array($news1, $news2, $news3);
					// Remove second item
                    $items = array($news1, $news3);
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
                        foreach ($testHTML as $item) 
							echo $item;
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</section>



<?php
$mandate = array();
$mandate[0]['title'] = "Recognising Excellence";
$mandate[0]['text'] = "";
$mandate[0]['link'] = "/recognising-excellence/recognising-excellence/";
$mandate[0]['reverse'] = "Find out more about this programme";

$mandate[1]['title'] = "AESA";
$mandate[1]['text'] = "The Alliance for Accelerating Excellence in Science in Africa.";
$mandate[1]['link'] = "/aesa/";
$mandate[1]['reverse'] = "Find out more about this programme";

$mandate[2]['title'] = "Aesa News";
$mandate[2]['text'] = "";
$mandate[2]['link'] = "/aesa/about/news/";
$mandate[2]['reverse'] = "Find out more about this programme";


if ($site->id==19) {
	$mandate[0]['title'] = "Funding opportunities";
	$mandate[0]['text'] = "";
	$mandate[0]['link'] = $site->link."funding-opportunities/";
	$mandate[0]['reverse'] = "Find out more about this programme";

	$mandate[1]['title'] = "Grant writing resources";
	$mandate[1]['text'] = "";
	$mandate[1]['link'] = $site->link."grant-resources/";
	$mandate[1]['reverse'] = "Find out more about this programme";

	$mandate[2]['title'] = "GCA news and events";
	$mandate[2]['text'] = "";
	$mandate[2]['link'] = $site->link."news-and-events/";
	$mandate[2]['reverse'] = "Find out more about this programme";
}
?>

<!-- Rollover boxes
================================================== 

-->
<section class="programs">

    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h3>Discover our mandate</h3>

                <div class="flip-cards">
                    <ul class="card-list">
                        <li style="width:33.333%">
                            <a href="<?=$mandate[0]['link']?>">
                            <div class="card-front" id="card-front-one">
                                <div class="contents">
                                    <h4><?=$mandate[0]['title']?></h4>
                                    <p><?=$mandate[0]['text']?></p>
                                </div>
                                <div class="card-bg"></div>
                            </div>
                            <div class="card-back">
                                <h4><?=$mandate[0]['reverse']?></h4>
                            </div>
                            </a>
                        </li>
                        <li style="width:33.333%">
                            <a href="<?=$mandate[1]['link']?>">
                            <div class="card-front" id="card-front-two">
                                <div class="contents">
                                    <h4><?=$mandate[1]['title']?></h4>
                                    <p><?=$mandate[1]['text']?></p>
                                </div>
                                <div class="card-bg"></div>
                            </div>
                            <div class="card-back">
                                <h4><?=$mandate[1]['reverse']?></h4>
                            </div>
                            </a>
                        </li>
                        <li style="width:33.333%">
                            <a href="<?=$mandate[2]['link']?>">
                            <div class="card-front" id="card-front-three">
                                <div class="contents">
                                    <h4><?=$mandate[2]['title']?></h4>
                                    <p><?=$mandate[2]['text']?></p>
                                </div>
                                <div class="card-bg"></div>
                            </div>
                            <div class="card-back">
                                <h4><?=$mandate[2]['reverse']?></h4>
                            </div>
                            </a>
                        </li>

                        <!--<li>
                            <a href="/programmes/science-equipment/science-equipment-policy-project/">
                            <div class="card-front" id="card-front-four">
	                              <div class="contents">
                                    <h4>Science Equipment</h4>
                                    <p>Science Equipment Policy Project.</p>
                                </div>
                                <div class="card-bg"></div>
                            </div>
                            <div class="card-back">
                                <h4>Find out more about this programme</h4>
                            </div>
                            </a>
                        </li>-->
                    </ul>
                </div>
            </div>
        </div>



    </div>
</section>


