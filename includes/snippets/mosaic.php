<?php
ob_start();
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="/includes/sapegin/jquery.mosaicflow.min.js"></script>
<style type="text/css">
.mosaicflow {
	width: 100%;
}
.mosaicflow__column {
	float:left;
	}

.mosaicflow__item {
    position:relative;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
    .mosaicflow__item img {
        display:block;
        width:100%;
        max-width:500px;
        height:auto;
        border: 0px solid #fff;
    }

    .mosaicflow__item p {
            position:absolute;
            bottom:0;
            left:0;
            width:96%;
            margin:0;
            padding:2%;
            background:hsla(0,0%,0%,.5);
            color:#fff;
            font-size:14px;
            text-shadow:1px 1px 1px hsla(0,0%,0%,.75);
            opacity:0;
            -webkit-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
               -moz-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
                 -o-transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
                    transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
            }
    .mosaicflow__item:hover p {
            opacity:1;
            }
</style>
<h1>Original mosaic example</h1>
<p>This mosaic is based on the mosaic flow system here: http://sapegin.github.io/jquery.mosaicflow/</p>
<div class="clearfix mosaicflow">
    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/working-lives/silk-workers/">
            <img width="500" height="500" src="/silo/images/baby-3_300x225.jpg" alt="">
        </a>
        <p>Thers is only a bit of a text overlay included in this system</p>
    </div>

    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/protest-politics-and-campaigning-for-change/">
            <img width="500" height="500" src="/silo/images/baby_300x300.jpg" alt="">
        </a>
        <p>This is an effect that happens when you hover over this image<br />
        We can put as much or as little stuff in here but it would have to fit in the image<br />
        The block does not link either so <a href="">the text would need</a> its own links</p>
    </div>

    <div class="mosaicflow__item">
        <a href="http://wcml.treelinesoftware.com/our-collections/creativity-and-culture/">
            <img width="500" height="500" src="/silo/images/baby-3_300x225.jpg" alt="">
        </a>
    </div>

    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/object-of-the-month/">
            <img width="500" height="500" src="/silo/images/baby-3_300x225.jpg" alt="">
        </a>
    </div>

    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/introduction/">
            <img width="500" height="500" src="/silo/images/baby_300x300.jpg" alt="">
        </a>
        <p>This is an effect that happens when you hover over this image<br />
        We can put as much or as little stuff in here but it would have to fit in the image<br />
        The block does not link either so <a href="">the text would need</a> its own links</p>
    </div>

    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/working-lives/silk-workers/">
            <img width="500" height="500" src="/silo/images/baby_300x300.jpg" alt="">
        </a>
        <p>Thers is only a bit of a text overlay included in this system</p>
    </div>

    <div class="mosaicflow__item">
    	<a href="http://wcml.treelinesoftware.com/our-collections/introduction/">
            <img width="500" height="500" src="/silo/images/baby-3_300x225.jpg" alt="">
        </a>
        <p>This is an effect that happens when you hover over this image<br />
        We can put as much or as little stuff in here but it would have to fit in the image<br />
        The block does not link either so <a href="">the text would need</a> its own links</p>
    </div>

</div>

<br /><br  /><br />

<?php
$mosaic1 = ob_get_contents();
ob_end_clean();
ob_start();	
?>


<?php

$images = array();

$i=0;
$image[$i]['img'] = "/silo/images/wcml-exterior-small_400x225.jpg";
$image[$i]['title'] = "Tailors";
$image[$i]['text'] = "The links match the titles but the images don't";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/tailors/";
$i++;
$image[$i]['img'] = "/silo/images/dickie-hawaorths-mill-in-ordsall_400x379.jpg";
$image[$i]['title'] = "Shipwrights";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/shipwrights/";
$i++;
$image[$i]['img'] = "/silo/images/petrloo-headscarf-detail_400x269.jpg";
$image[$i]['title'] = "Gasworks";
$image[$i]['text'] = "Large crowd gathers for free chip butties";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/gasworkers/";
$i++;
$image[$i]['img'] = "/silo/images/gas-workers-emblem_400x269.jpg";
$image[$i]['title'] = "Brushmakers";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/brushmakers/";
$i++;
$image[$i]['img'] = "/silo/images/manchester-ship-canal-plate_400x397.jpg";
$image[$i]['title'] = "Boilermakers";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/boilermakers/";
$i++;
$image[$i]['img'] = "/silo/images/blacksmiths-trade-union-emblem_400x375.jpg";
$image[$i]['title'] = "Blacksmiths";
$image[$i]['text'] = "Men stoking furnaces";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/blacksmiths/";
$i++;
$image[$i]['img'] = "/silo/images/wcml-exterior-small_400x225.jpg";
$image[$i]['title'] = "Tailors";
$image[$i]['text'] = "The links match the titles but the images don't";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/tailors/";
$i++;
$image[$i]['img'] = "/silo/images/dickie-hawaorths-mill-in-ordsall_400x379.jpg";
$image[$i]['title'] = "Shipwrights";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/shipwrights/";
$i++;
$image[$i]['img'] = "/silo/images/petrloo-headscarf-detail_400x269.jpg";
$image[$i]['title'] = "Gasworks";
$image[$i]['text'] = "Large crowd gathers for free chip butties";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/gasworkers/";
$i++;
$image[$i]['img'] = "/silo/images/gas-workers-emblem_400x269.jpg";
$image[$i]['title'] = "Brushmakers";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/brushmakers/";
$i++;
$image[$i]['img'] = "/silo/images/manchester-ship-canal-plate_400x397.jpg";
$image[$i]['title'] = "Boilermakers";
$image[$i]['text'] = "This is some text";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/boilermakers/";
$i++;
$image[$i]['img'] = "/silo/images/blacksmiths-trade-union-emblem_400x375.jpg";
$image[$i]['title'] = "Blacksmiths";
$image[$i]['text'] = "Men stoking furnaces";
$image[$i]['link'] = "http://wcml.treelinesoftware.com/our-collections/working-lives/blacksmiths/";
?>

<!-- STYLE FOR THE PLUGIN -->   
<link rel="stylesheet" href="/includes/urg/plugin/css/gridGallery.css" />
<!-- <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> -->
<style type="text/css">
div.grid-loadMore { display: none; }
</style>
<h1>Second attempt purchased version</h1>
<p>This is the ultimate grid responsive system here: http://www.davidbo.dreamhosters.com/plugins/gridGallery/example1.html</p>
<p>The fact that some images appaer twice is not important however it is no too tricky to find more images, there just are not that many to pick from</p>
<div id="grid">
  
	<?php
	foreach ($image as $img) {
		?>
        <div class="box" data-url="<?=$img['link']?>" data-category="Images">
            <div data-image="<?=$img['img']?>"></div>
            <div class="thumbnail-caption">
                  <h3><?=$img['title']?></h3>
                  <h5><?=$img['text']?></h5>
            </div>
        </div>
        <?php
	}
	?>

  
  
</div> <!-- #grid -->

<!-- SCRIPTS FOR THE PLUGIN -->
<!-- <script src="/includes/url/plugin/js/jquery-1.9.1.min.js"></script> -->
<script src="/includes/urg/plugin/js/rotate-patch.js"></script>
<script src="/includes/urg/plugin/js/gridGallery.min.js"></script>

<script>
  
    $('document').ready(function(){
        //INITIALIZE THE PLUGIN
        //$('#grid').grid();
		$('#grid').grid({ lightBox: true, showFilterBar: false, columnMinWidth: 300 })
    });    
       
</script>


<?php
$mosaic2 .= ob_get_contents();
ob_end_clean();

if (!$_GET['mosaic'] || $_GET['mosaic']==1) $replace = $mosaic1;
//if (!$_GET['mosaic'] || $_GET['mosaic']==2) $replace .= $mosaic2;

$replace .= '<div class="clearfix"></div>'."\n";

?>
