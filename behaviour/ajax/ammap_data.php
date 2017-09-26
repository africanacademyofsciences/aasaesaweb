<?
ini_set("display_errors","on");
error_reporting(E_ALL && ~E_NOTICE);

include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
//include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");
require_once($_SERVER['DOCUMENT_ROOT']."/treeline/includes/page.class.php");
$page = new Page();
header ("content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>'; // Stupid XML line
?>
<map map_file="maps/world.swf" tl_long="0" tl_lat="0" br_long="0" br_lat="0" zoom_x="-1232.5%" zoom_y="-1104%" zoom="2810%">
	<areas>
	<?
    /*
    $q = "Angola Benin Burundi Cameroon Congo Eritrea Ethiopia Gambia Ghana Guinea Kenya Lesotho Liberia Madagascar Malawi Mali Mozambique Nambia Nigeria Rwanda Senegal Somalia South Africa Sudan Swaziland Tanzania Uganda Zambia Zanzibar Zimbabwe";
    $r = split(" ",$q);
    print_r($r);
    foreach($r as $country) {
        $db->query("INSERT INTO `map` (`country`,`colour`) VALUES ('".$country."','".rand(1,4)."')");	
    
    }
    
    */
    
    $query = "SELECT m . * , mc.hex, m.help, c.ISO2 AS code
        FROM `map` m
        LEFT JOIN `map_colours` mc ON m.colour = mc.colourid
        LEFT JOIN `countries` c ON m.country = c.title
        ";
                                    
    if( $dat2 = $db->get_results($query) ){
        foreach($dat2 as $data) {
            $country = htmlentities(html_entity_decode($data->country));
            if(strlen($country) > 0) { 
                $link = ($page->drawLinkByGUID($data->guid) != '//') ? $page->drawLinkByGUID($data->guid) : '';
                $code = ($data->code) ? $data->code :'NA';
                echo '<area title="'.$country.'"  color="'.$data->hex.'" url="'.$link.'" mc_name="'.$code .'" value="">';
                /*
                $help = ($data->help == 'true') ? "-Training Programme\n" : '';
                $technicalAssistance = ($data->technicalAssistance == 'true') ? "-Technical Assistance\n" : '';
                $labProgramme = ($data->labProgramme == 'true') ? "-Laboratory Programme\n" : '';
                $outreachProgramme = ($data->outreachProgramme == 'true') ? "-Outreach Programme\n" : '';
                */
                $query = "SELECT * FROM map_legend
                    LEFT JOIN  map_countryLegends ON map_legend.id = map_countryLegends.legendID
                    AND countryID = ".$data->id;
                $key = "";
                if($results = $db->get_results($query) ){
                    foreach($results as $keyData){
                        if($keyData->countryID){
                            $key.=$keyData->text."\n";
                        }
                    }
                }
    
                echo "<description><![CDATA[<b>$country</b>\n$key]]></description>\n";
                //    echo "<url>".(($data->outreachProgramme == 'true') ? "-We have an outreach programme.\n": "")."</url>";
                echo '</area>'."\n";
            }
        }
    }
                
    ?> 
	</areas>
</map>