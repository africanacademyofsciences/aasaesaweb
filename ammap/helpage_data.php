<?
// This file is designed to be run alone and must be enabled in .htaccess
// This allows file downloads to be tracked internally
ini_set("display_errors","on");
error_reporting(E_ALL && ~E_NOTICE);
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ezSQL.class.php");
header ("content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>'; // Stupid XML line
?>

<map map_file="maps/world.swf" tl_long="-168.49" tl_lat="86.06" br_long="190.3" br_lat="-55.58" zoom_x="0%" zoom_y="-20%" zoom="119%">
  <areas>
<?

$query = "SELECT pci.newname AS country,c.code2, p.title AS country2, p.name AS countrylink, pcalc.over_60_tot AS pop1, p.guid,
					 pcol.colour AS col, pc.social_pension FROM pension_country pc 

				LEFT JOIN `pension_countries_info` pci on pc.country = pci.oldname
				LEFT JOIN `pension_calc` pcalc ON `pci`.`oldname` = pcalc.`country`
				LEFT JOIN `pension_country_colours` pcol on pci.colourid = pcol.colourid
				LEFT JOIN `pages` p on pci.oldname = p.title
				LEFT JOIN `country` c on c.title=pci.newname				

WHERE p.guid IS NOT NULL
GROUP BY `country` ASC";
			if( $dat2 = $db->get_results($query) ){
				foreach($dat2 as $data) {
					$country = htmlentities(html_entity_decode($data->country));
					$country3 = htmlentities(html_entity_decode($data->country));
					$country3 = str_replace("COTE D'IVOIRE","COTE D'IVOIRE",$country3);
					
					if(strlen($country) > 0 && $country != 'region') { 
					
					$c = $data->code2;
					if($country == "COTE D'IVOIRE") $c = 'CI';
					
						echo '<area title="'.$country.'"  color="'.$data->col.'" url="/pensions/country-fact-file/'.$data->countrylink.'" mc_name="'.$c.'" value="">';
							echo "<description>".number_format($data->pop1)." people over the age of 60 in ".$data->country2." \n\nSocial Pension? ".$data->social_pension."</description>";
						echo '</area>'."\n";
					}
				}
			}
			
?> 
 </areas>
</map>

<?
function cCode($country) {
$query = "SELECT code2 FROM `country` WHERE `title`='$country'";
	if( $fetch = $db->get_results($query) ){
	$code = $fetch->code2;
} else {
	$code = "NA";	
}
return $code;
}
?>
