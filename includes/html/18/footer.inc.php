

</div>


<footer>
	<div class="container">
		<div class="row">
			<!--<div class="col-xs-12 share">
				<p>-->
					<!--<a class="btn btn-link"><i class="ion-android-share-alt"></i></a>-->
					<!--<a class="btn btn-link" href="https://twitter.com/aasciences" target="_blank"><i class="ion-social-twitter"></i></a>-->
					<!--<a class="btn btn-link" href="https://www.facebook.com/aasciences/" target="_blank"><i class="ion-social-facebook"></i></a>--?
					<!--<a class="btn btn-link"><i class="ion-social-googleplus"></i></a>-->
					<!--<a class="btn btn-link"><i class="ion-social-pinterest"></i></a>-->
					<!--<a class="btn btn-link" href="https://www.youtube.com/channel/UCtdLgoNICbdUFqkw-ph508g" target="_blank"><i class="ion-social-youtube"></i></a>-->
				<!--</p>
			</div>-->
			<div class="col-xs-12">
				<ul class="list-unstyled left">
                	<li>
                    <?php
					include($_SERVER['DOCUMENT_ROOT']."/includes/snippets/google.translate.inc.php");
					?>
                    </li>
					<li><a href="#">&copy; Copyright 2017 African Academy of Sciences.</a></li>
					<li><a href="http://treelinedigital.co.uk/" target="_blank">Site by Treeline Digital</a> | <a href="http://www.treelinesoftware.com/" target="_blank">Powered by Treeline CMS</a></li>
				</ul>

				
				<ul class="list-unstyled right">
					<li><i class="ion-ios-shuffle"></i> <a href="<?=$site->link?>sitemap/">Site map</a></li>
					<li><i class="ion-ios-locked-outline"></i> <a href="<?=$site->link?>privacy-policy/">Privacy policy</a></li>
					<li><i class="ion-ios-paper-outline"></i> <a href="<?=$site->link?>terms/">Terms and conditions</a></li>
				</ul>
				<ul class="list-unstyled right" style="padding-right: 45px">
					<li><a href="http://aasciences.ac.ke/aesa/en/events/current-events/"><i class="fa fa-calendar" aria-hidden="true"></i> Events</a></li>
					<li><a href="<?=$site->link?>about/news/"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Media</a></li>
					<li><a href="http://aasciences.ac.ke/aesa/en/explore--resources/explore--resources"><i class="fa fa-film" aria-hidden="true"></i> Explore / Resources</a></li>
				</ul>
				<!--<div class="col-xs-12 text-center">
					 
				</div>-->
			</div>
		</div>
	</div>
</footer>
<!-- / FOOTER --> 


<!-- JavaScript
===================================== -->

<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="<?=$site->path?>js/bootstrap.js"></script>


<!-- Valign
===================================== -->
<script src="<?=$site->path?>js/valign.js"></script> 

<!-- Resize header text (or other element) on scroll
================================================== --> 
<?php
if ($site->id != 18)
{
?>
<script src="<?=$site->path?>js/classie.js"></script>
<script src="<?=$site->path?>js/resize-on-scroll.js"></script>
<?php
}
?>

<!-- Initiate carousel 
===================================== -->
<script>
	$('.carousel').carousel({
		interval: 14000
	})
</script>

<!-- Match height
===================================== -->
<script src="<?=$site->path?>js/jquery.matchHeight.js"></script>
<script type="text/javascript">
	/// Why why why???
	$(function() {
		$('.intro').matchHeight();
	});
	$(function() {
		$('.filter-link').matchHeight();
	});
	$(function() {
		$('.title').matchHeight();
	});
	$(function() {
		$('.green-background, .blue-background', '.orange-background').matchHeight();
	});
	
	$(function() {
		$('.match').matchHeight();
	});
	
	$(function() {
		$('.match2').matchHeight();
	});
	
	<?php
	if ($site->id == 18)
	{
		?>
		$(function() {
			$('.landing-panel').matchHeight();
		});
		<?php
	}
	
	?>
</script>




<!-- Owl carousel
===================================== -->
<script src="<?=$site->path?>js/owl.carousel.js"></script>
<script>
	$(document).ready(function() {
		$('.owl-carousel').owlCarousel({
			loop:true,
			autoplay:3000,
			dots:true,
			margin:10,
			nav:false,
			responsive:{
				0:{
					items:1
				},
				600:{
					items:1
				},
				1000:{
					items:1
				}
			}
		})
	});
</script>

<!-- Main carousel touch swipe
===================================== -->
<script src="<?=$site->path?>js/carousel-touch.js"></script>
<script>
	$(document).ready(function() {  
		$("#carousel1").swiperight(function() {  
			$(this).carousel('prev');  
		});  
		$("#carousel1").swipeleft(function() {  
			$(this).carousel('next');  
		});  
	});  
</script>

<!-- amcharts
===================================== -->
<!--<script src="http://www.amcharts.com/lib/3/ammap.js"></script>
<script src="http://www.amcharts.com/lib/3/maps/js/worldLow.js"></script>
<script>
function test()
{
	alert('hello');
}
AmCharts.themes.light = {

	themeName:"light",

	AmChart: {
		color: "#aba8a6"
	},

	AmCoordinateChart: {
		colors: ["#67b7dc", "#fdd400", "#84b761", "#cc4748", "#cd82ad", "#2f4074", "#448e4d", "#b7b83f", "#b9783f", "#b93e3d", "#913167"]
	},

	AmStockChart: {
		colors: ["#67b7dc", "#fdd400", "#84b761", "#cc4748", "#cd82ad", "#2f4074", "#448e4d", "#b7b83f", "#b9783f", "#b93e3d", "#913167"]
	},

	AmSlicedChart: {
		colors: ["#67b7dc", "#fdd400", "#84b761", "#cc4748", "#cd82ad", "#2f4074", "#448e4d", "#b7b83f", "#b9783f", "#b93e3d", "#913167"],
		outlineAlpha: 1,
		outlineThickness: 2,
		labelTickColor: "#000000",
		labelTickAlpha: 0.3
	},

	AmRectangularChart: {
		zoomOutButtonColor: '#000000',
		zoomOutButtonRollOverAlpha: 0.15,
		zoomOutButtonImage: "lens.png"
	},

	AxisBase: {
		axisColor: "#000000",
		axisAlpha: 0.3,
		gridAlpha: 0.1,
		gridColor: "#000000"
	},

	ChartScrollbar: {
		backgroundColor: "#000000",
		backgroundAlpha: 0.12,
		graphFillAlpha: 0.5,
		graphLineAlpha: 0,
		selectedBackgroundColor: "#FFFFFF",
		selectedBackgroundAlpha: 0.4,
		gridAlpha: 0.15
	},

	ChartCursor: {
		cursorColor: "#000000",
		color: "#FFFFFF",
		cursorAlpha: 0.5
	},

	AmLegend: {
		color: "#000000"
	},

	AmGraph: {
		lineAlpha: 0.9
	},
	GaugeArrow: {
		color: "#000000",
		alpha: 0.8,
		nailAlpha: 0,
		innerRadius: "40%",
		nailRadius: 15,
		startWidth: 15,
		borderAlpha: 0.8,
		nailBorderAlpha: 0
	},

	GaugeAxis: {
		tickColor: "#000000",
		tickAlpha: 1,
		tickLength: 15,
		minorTickLength: 8,
		axisThickness: 3,
		axisColor: '#000000',
		axisAlpha: 1,
		bandAlpha: 0.8
	},

	TrendLine: {
		lineColor: "#c03246",
		lineAlpha: 0.8
	},

	// ammap
	AreasSettings: {
		alpha: 1,
		color: "#f7f7f7",
		colorSolid: "#f7f7f7",
		unlistedAreasAlpha: 0.0,
		unlistedAreasColor: "#000000",
		outlineColor: "#f7f7f7",
		outlineAlpha: 0.1,
		outlineThickness: 0.5,
		rollOverColor: "#f7f7f7",
		rollOverOutlineColor: "#f7f7f7",
		selectedOutlineColor: "#f7f7f7",
		selectedColor: "#f7f7f7",
		unlistedAreasOutlineColor: "#f7f7f7",
		unlistedAreasOutlineAlpha: 0.5
	},

	LinesSettings: {
		color: "#000000",
		alpha: 0.8
	},

	ImagesSettings: {
		alpha: 0.8,
		labelColor: "#000000",
		color: "#000000",
		labelRollOverColor: "#78a22f"
	},

	ZoomControl: {
		buttonRollOverColor: "#3e9591",
		buttonFillColor: "#3e9591",
		buttonBorderColor: "#3e9591",
		buttonFillAlpha: 0.8,
		gridBackgroundColor: "#FFFFFF",
		buttonBorderAlpha:0,
		buttonCornerRadius:2,
		gridColor:"#f0f0f0",
		gridBackgroundColor:"#ffffff",
		buttonIconAlpha:0.6,
		gridAlpha: 0.6,
		buttonSize:20
	},

	SmallMap: {
		mapColor: "#000000",
		rectangleColor: "#f15135",
		backgroundColor: "#FFFFFF",
		backgroundAlpha: 0.7,
		borderThickness: 1,
		borderAlpha: 0.8
	},

	// the defaults below are set using CSS syntax, you can use any existing css property
	// if you don't use Stock chart, you can delete lines below
	PeriodSelector: {
		color: "#000000"
	},

	PeriodButton: {
		color: "#000000",
		background: "transparent",
		opacity: 0.7,
		border: "1px solid rgba(0, 0, 0, .3)",
		MozBorderRadius: "5px",
		borderRadius: "5px",
		margin: "1px",
		outline: "none",
		boxSizing: "border-box"
	},

	PeriodButtonSelected: {
		color: "#000000",
		backgroundColor: "#b9cdf5",
		border: "1px solid rgba(0, 0, 0, .3)",
		MozBorderRadius: "5px",
		borderRadius: "5px",
		margin: "1px",
		outline: "none",
		opacity: 1,
		boxSizing: "border-box"
	},

	PeriodInputField: {
		color: "#000000",
		background: "transparent",
		border: "1px solid rgba(0, 0, 0, .3)",
		outline: "none"
	},

	DataSetSelector: {

		color: "#000000",
		selectedBackgroundColor: "#b9cdf5",
		rollOverBackgroundColor: "#a8b0e4"
	},

	DataSetCompareList: {
		color: "#000000",
		lineHeight: "100%",
		boxSizing: "initial",
		webkitBoxSizing: "initial",
		border: "1px solid rgba(0, 0, 0, .3)"
	},

	DataSetSelect: {
		border: "1px solid rgba(0, 0, 0, .3)",
		outline: "none"
	}

};
</script>
<script>
	var map = AmCharts.makeChart( "fellows", {
	
		"type": "map",
		"theme": "light",
		"path": "http://www.amcharts.com/lib/3/",
		balloon: {
			maxWidth: "500"
		},
	
		"dataProvider": {
			"map": "worldLow",
			zoomLevel: 2.5,
			zoomLongitude: 20,
			zoomLatitude: 5,
			"getAreasFromMap": true,
			areas: [ 
				{ title: "Click here to see a list of fellows from Ethiopia.",
				id: "ET",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/programmes/fellows-from-ethiopia/"},
				{ title: "Click here to see a list of fellows from Ghana.",
				id: "GH",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/recognising-excellence/fellows-from-ghana/"},
				{ title: "Click here to see a list of fellows from Kenya.",
				id: "KE",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/recognising-excellence/fellows-from-kenya/"},
				{ title: "Click here to see a list of fellows from Nigeria.",
				id: "NG",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-nigeria/"},
				{ title: "Click here to see a list of fellows from South Africa.",
				id: "ZA",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-south-africa-/"},
				{ title: "Click here to see a list of fellows from Sudan.",
				id: "SD",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-sudan/"},
				{ title: "Click here to see a list of fellows from Tanzania.",
				id: "TZ",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-tanzania/"},
				{ title: "Click here to see a list of fellows from Uganda.",
				id: "UG",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-uganda/"},
				{ title: "Click here to see a list of fellows from Zimbabwe.",
				id: "ZW",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: "http://aas.treelinesoftware.com/aas/en/programmes/fellows-from-zimbabwe/"}	
			]
		},
		"areasSettings": {
		"autoZoom": true,
		"selectedColor": "#01b5cc"
		}
	} );
</script>
<script>
var map2 = AmCharts.makeChart( "deltas", {
	
		"type": "map",
		"theme": "light",
		"path": "http://www.amcharts.com/lib/3/",
	
		"dataProvider": {
			"map": "worldLow",
			zoomLevel: 2.5,
			zoomLongitude: 20,
			zoomLatitude: 5,
			"getAreasFromMap": true,
			areas: [ 
				{ title: "",
				id: "KE",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "University of Ghana",
				id: "GH",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "University of Witwatersrand<br>University of KwaZulu Natal",
				id: "ZA",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "Makerere University",
				id: "UG",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "University of Zimbabwe",
				id: "ZW",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "University of Science Techniques and Technologies of Bamako",
				id: "ML",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "Centre Suisse de Recherches Scientifiques en Côte d’Ivoire (CSRS)",
				id: "CIV",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "KEMRI-Wellcome Trust Research Wellcome Trust Research Programme",
				id: "KE",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "University Cheikh Anta Diop of Dakar",
				id: "SN",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "Makerere University",
				id: "UG",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
	
			]
		},
		"areasSettings": {
		"autoZoom": true,
		"selectedColor": "#01b5cc"
		}
	} );
</script>
<script>
var map2 = AmCharts.makeChart( "chart3", {
	
		"type": "map",
		"theme": "light",
		"path": "http://www.amcharts.com/lib/3/",
	
		"dataProvider": {
			"map": "worldLow",
			zoomLevel: 1.5,
			zoomLongitude: 20,
			zoomLatitude: 5,
			"getAreasFromMap": true,
			areas: [ 
				{ title: "<strong>Algeria</strong><br>Benmouna, Mustapha, Prof.<br>PhysicsBorn in 1946, Algeria (residing in Morocco)",
				id: "DZ",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "<strong>Benin</strong><br>Ezin Onvêhoun, Jean – Pierre, Prof.<br>Fellow of AAS since 2009<br>Mathematics, Differential Geometry<br>Born in 1944, Benin<br>Hounkonnou, Norbert Mahouton, Prof<br>Fellow of AAS since 2005<br>Mathematical Physics<br>Born in 1956, Benin",
				id: "BJ",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "<strong>Cameroon</strong><br>Bekolle, David, Prof<br>Kofane, Timoleon Crepin, Prof<br>Ekosse Ekosse, George-Ivo, Dr.<br>Titanji, Vincent P. K, Prof.",
				id: "CM",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "<strong>Republic of the Congo</strong><br>Bouramoue, Christophe, Prof.<br>Fellow of AAS since 1987, Medicine<br>Born in 1941, Congo<br>Silou Thomas, Prof<br>Fellow of AAS since 1990<br>Chemical Engineering and Chemical Physics<br>Born in 1951, Congo",
				id: "CG",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "<strong>Germany</strong><br>Borgemeister, Christian, Prof.<br>Associate Fellow of AAS since 2011<br>Entomology, biological control,<br>integrated pest management (IPM)<br>Born in 1958, Germany",
				id: "DE",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
				,
				{ title: "<strong>Ghana</strong><br>Allotey Ampenyin Kofi, Francis, ProfAAS Fellow since 1985<br>Danso, Akyea Kofi Seth, Prof.<br>Ayensu, S. Edward, Prof<br>DARKWA, James (Prof.)",
				id: "GH",
				color: "#003A70",
				rollOverColor: "#037d42",
				url: ""}
	
			]
		},
		"areasSettings": {
		"autoZoom": true,
		"selectedColor": "#01b5cc"
		}
	} );
</script>

AESA site our innovators map
<script>
var map2 = AmCharts.makeChart( "innovators-map", {
	
		"type": "map",
		"theme": "light",
		"path": "http://www.amcharts.com/lib/3/",
	
		"dataProvider": {
			"map": "worldLow",
			zoomLevel: 2.5,
			zoomLongitude: 20,
			zoomLatitude: 5,
			"getAreasFromMap": true,
			areas: [ 
				{ title: '<strong>Tunisia</strong><br><img src="/includes/html/images/18/innov-map/solid-grey.png" style="height: 30px;">',
				id: "TN",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Egypt</strong><br><img src="/includes/html/images/18/innov-map/half-yellow-green.png" style="height: 30px;">',
				id: "EG",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Mali</strong><br><img src="/includes/html/images/18/innov-map/solid-grey.png" style="height: 30px;">',
				id: "ML",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Senegal</strong><br><img src="/includes/html/images/18/innov-map/mostly-yellow-green.png" style="height: 30px;">',
				id: "SN",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Sierra Leone</strong><br><img src="/includes/html/images/18/innov-map/mostly-yellow-green.png" style="height: 30px;">',
				id: "SL",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Burkina Faso</strong><br><img src="/includes/html/images/18/innov-map/solid-yellow.png" style="height: 30px;">',
				id: "BF",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Ghana</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "GH",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Benin</strong><br><img src="/includes/html/images/18/innov-map/solid-yellow.png" style="height: 30px;">',
				id: "BJ",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Nigeria</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "NG",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Cameroon</strong><br><img src="/includes/html/images/18/innov-map/grey-yellow.png" style="height: 30px;">',
				id: "CM",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Democratic Republic of the Congo</strong><br><img src="/includes/html/images/18/innov-map/half-yellow-green.png" style="height: 30px;">',
				id: "CD",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Namibia</strong><br><img src="/includes/html/images/18/innov-map/solid-grey.png" style="height: 30px;">',
				id: "NA",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>South Africa</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "ZA",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Mozambique</strong><br><img src="/includes/html/images/18/innov-map/half-grey-green.png" style="height: 30px;">',
				id: "MZ",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Zimbabwe</strong><br><img src="/includes/html/images/18/innov-map/half-yellow-green.png" style="height: 30px;">',
				id: "ZW",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Tanzania</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "TZ",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Kenya</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "KE",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Uganda</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "UG",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>South Sudan</strong><br><img src="/includes/html/images/18/innov-map/solid-yellow.png" style="height: 30px;">',
				id: "SS",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Ethiopia</strong><br><img src="/includes/html/images/18/innov-map/chart-1.png" style="height: 30px;">',
				id: "ET",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Rwanda</strong><br><img src="/includes/html/images/18/innov-map/mostly-yellow-green.png" style="height: 30px;">',
				id: "RW",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Burundi</strong><br><img src="/includes/html/images/18/innov-map/solid-yellow.png" style="height: 30px;">',
				id: "BI",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Madagascar</strong><br><img src="/includes/html/images/18/innov-map/solid-yellow.png" style="height: 30px;">',
				id: "MG",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""},
				{ title: '<strong>Swaziland</strong><br><img src="/includes/html/images/18/innov-map/solid-grey.png" style="height: 30px;">',
				id: "SZ",
				color: "#F26522",
				rollOverColor: "#F7A37A",
				url: ""}
			]
		},
		"areasSettings": {
		"autoZoom": true,
		"selectedColor": "#01b5cc"
		}
	} );
</script>-->

	<?php
	if ($site->id == 18)
	{
		?>
		
		<script src="/behaviour/wow.js"></script>
		<script>
              new WOW().init();
        </script>
		<?php
	}
	/*   
    <div id="footer">
        <div id="copyright">
        	<?php if (is_object($footer)) { ?>
	            <div style="<?=($page->getMode()=="edit"?" height:130px;":"")?>"><?=$footer->draw()?></div>
            <?php } ?>
            <p>
            	<a href="<?=$site->link?>terms-and-conditions/"><?=$page->drawLabel('terms','Terms and conditions')?></a> 
                <a href="<?=$site->link?>privacy-policy/"><?=$page->drawLabel('privacy','Privacy policy')?></a>
            </p>
        </div>
        <div id="footerMenu">
            <p>
				<?=$page->drawLabel('siteby','Site designed by')?> <a href="http://www.chameleoninteractive.com?ref=<?=$site->name?>" rel="external" title="Visit the Chameleon Interactive website">Chameleon Interactive</a> 
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <?=$page->drawLabel('powered','Powered by')?> <a href="http://treelinedemo.ichameleon.com" rel="external" title="View Treeline in action">Treeline CMS</a>
            </p>
        </div>
    </div>
	*/
	?>

<?php 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/editModeBottom.inc.php'); 
include($_SERVER['DOCUMENT_ROOT'].'/includes/templates/commonJSBottom.inc.php'); 
include($_SERVER['DOCUMENT_ROOT']."/includes/templates/stats.inc.php"); 
?>

</body>
</html>