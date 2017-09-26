<? 
/*
This basically shows forms/divs dependent on the querystring parameter,
which is evaluated by the switch.
--
All of the process of the forms is done in the header section.
This takes a unique parameter and goes through the POST vars to get key and values we can use.
The data is saved is $save=true;
In a similar manner, the UI for each section is controlled by a switch with the same parameter name...
--
Underscores separate the type of parameter and the value.
--
As simplexml doesn't have much functionality, DOMDocument is used whenever more is needed...

*/ 

//// $view sets which form to display...
if( isset($_GET['p']) && $_SERVER['REQUEST_METHOD']=="GET" ){
	$view = $_GET['p'];
}
if( isset($_POST['p']) && $_SERVER['REQUEST_METHOD']=="POST" ){
	$view = $_POST['p'];
}
//print "got view($view) method(".$_SERVER['REQUEST_METHOD'].") getp(".$_GET['p'].") postp(".$_POST['p'].")<br>";

/// set debug mode on/off
if(isset($_GET['debug']) ){
	$debug=true;
}


$xmlfile = $_SERVER['DOCUMENT_ROOT'] .'/_xml/GetTree.xml';
$xmlfile2 = $_SERVER['DOCUMENT_ROOT'] .'/_xml/countries.xml';

if($view=='country2region') { 

?>
<script type="text/javascript">

function moveIn(selectesdValue){
	Combo = document.getElementById("Country2Region");
	Combo2 = document.getElementById("Country2ThisRegion");
	
	thisItem = Combo.options[Combo.selectedIndex];
	status=true;
	
	for(i=0;i<Combo2.length;i++){
		if(Combo2.options[i].text==thisItem.text){
			status=false;
		}else{
			if(status!=false){
				status=true;
			}
		}
	}
	
	if(status==true){
		// Add item to the right list
		Combo2.length++;
		Combo2.options[Combo2.length-1].text=thisItem.text;
		Combo2.options[Combo2.length-1].value=thisItem.value;
	}

}


function moveOut(selectesdValue){
	Combo2 = document.getElementById("Country2ThisRegion");
	thisItem = Combo2.options[Combo2.selectedIndex];
	Combo2.options[Combo2.selectedIndex]=null;
}




function testSubmit(){
	Combo = document.getElementById("Country2Region");
	Combo2 = document.getElementById("Country2ThisRegion");	
	
	for(i=0; i<Combo2.length; i++){
		Combo2.options[i].selected = true;
	}
	
	//Combo.options[Combo.selectedIndex].selected = false;
	
	return true;
}

</script>
<?php 
}



?>
        <!-- ////////////////////////// Menu ////////////////////////// -->
        <ul class="menu">
            <li><a href="?p=global">Global settings</a></li>
            <li><a href="?p=regions">Manage Regions</a></li>
            <!--//<li><a href="?p=add_region">Add new region</a></li>
            <li><a href="?p=delete_regions">Remove Regions</a></li>//-->
            <li><a href="?p=countries">Manage Countries</a></li>
            <li><a href="?p=country2region">Countries to Regions</a></li>
            <li><a href="?p=entity2country">Country Info</a></li>
        </ul>
<?php



//// If we have POST vars, then evaluate and process
/////////////////////////////////// Processing /////////////////////////////////////////

if($_POST){		

	$alldata = simplexml_load_file($xmlfile);
	$query = $alldata->xpath("//RegionsCountriesCollection[@ID='0']");
	$data = $query[0];
	$query = $alldata->xpath("//RegionsCountriesCollection[@ID='1']/Countries");
	$countrydata = $query[0];
	$message = array();
		
	foreach($_POST as $key => $value){
	//print "checking k($key) = v($value)<br>";
		if( substr_count( $key , '_' ) > 0 ){
			$element = ucfirst( substr($key, 0, strpos($key,'_') ) );
		}else{
			$element = ucfirst( substr($key, 0, strlen($key) ) );
		}
		
		switch( $element ){
		
			case 'Global':
				$item = substr($key, strrpos($key,'_')+1 );
				$data[$item] = $value;	
				$save=true;			
				break;
				
				
				
			case 'Region':
			//print "in region admin section???<br>";
				$position = substr($key, strpos($key,'_')+1 );
				$i=0;
				foreach($data->Region as $item){
				//print "checking item(".print_r($item).") index($i) pos($position)<br><br>";
					if($i == $position){
						$item['Name'] = $value;
					}
					$i++;
				}
				$save=true;
				break;
				
				
				
			case 'Add':
				$type = substr($key, strpos($key,'_')+1 );
				if( $type == 'RegionCode' ){
					// Check that the region doesn't already exist...
					$regions = $data->xpath("//Region[@RegionCode='".$value."']");
					if( count($regions) ){
						$message[] = 'This region code already exists';
						$code = false;
					}else{
						$code = $value;
					}
				}

				if( $type == 'Region' && $code){
					// Check that the region doesn't already exist...
					$regions = $data->xpath("//Region[@Name='". $value."']");
					if( count($regions) ){
						$message[] = 'This regions name already exists';
					}else{
					
						/// We also need to append the global default to this new elements' attributes...
						$newElement = $data->addChild('Region','');
						//$regionName = $value;
						$newElement->addAttribute('RegionCode',$code);
						$newElement->addAttribute('Name',$value);
						$newElement->addAttribute('CentralCountryCode',' ');
						$newElement->addAttribute('ZoomMax',' ');
						$newElement->addAttribute('OutlineColour',$data['OutlineColour']);
						$newElement->addAttribute('Alpha','10');
						$newElement->addAttribute('CountryColour',$data['CountryColour']);
						$newElement->addAttribute('OverState',$data['OverState']);
						$newElement->addAttribute('DownState',$data['DownState']);
						$message[] = 'Region: "'. $value .'" has been added' ;
						$save=true;
						$view='regions';
						unset($_POST);
					}
				}
				
				
				break;
			
			
			case 'Setup':
			//print "in setup :o)<br>";
				$type = substr($key, strpos($key,'_')+1, strpos($key,'_') );
				$item = substr($key, strrpos($key,'_')+1 );
				$selectRegionCode=$item;
				//print "found region code($selectRegionCode)<br>";
				if ($type!="Regio") {
					$selectRegionCode=$_POST['selectRegionCode'];
				//print "found region code($selectRegionCode)<br>";
					$thisregion = $data->xpath("//Region[@RegionCode='".$selectRegionCode."']");
					$thisregion[0][$item] = $value;
					if(!in_array('This region\'s details have been updated',$message) ){
						$message[] = 'This region\'s details have been updated';
					}
					$save=true;	
					$selectRegionCode='';
				}
				break;

			case 'Delete':
				$type = substr($key, strpos($key,'_')+1, strpos($key,'_') );
				$value = substr($key, strrpos($key,'_')+1 );
				$region = $data->xpath("//Region[@RegionCode='". $value."']");
				
				if( count($region[0]->children()) > 0){
					foreach($region[0]->children() as $key => $country){
						$newElement = $countrydata->addChild('Country');
						$newElement->addAttribute('CountryCode',$country['CountryCode']);
						$newElement->addAttribute('ThreeISOCode',$country['ThreeISOCode']);
						$newElement->addAttribute('Name',$country['Name']);
						$newElement->addAttribute('OutlineColour','000000');
						$newElement->addAttribute('Alpha','100');
						$newElement->addAttribute('CountryColour','A6B544');
						$newElement->addAttribute('OverState','DAF430');
						$newElement->addAttribute('DownState','BED23A');
						
						if( count($country->children()) > 0){
							foreach($country as $ckey => $entity){
								$newEntity = $newElement->addChild('Entity');
								$newEntity->addAttribute('ID',$entity['ID']);
								$newEntity->addAttribute('Name',$entity['Name']);
								$newEntity->addAttribute('Link',$entity['Link']);
								$newEntity->addAttribute('Desc',$entity['Desc']);
							}	
						}
					}
					
				}
				
				if( $domdata = dom_import_simplexml($data) ){
					$test = $domdata->getElementsByTagName('Region');
					
					if($test){
						for($i=0;$i<$test->length;$i++){
							//echo $test->item($i)->getAttribute('RegionCode') .'<br />';
							if($test->item($i)->getAttribute('RegionCode') == $value){
								// now delete the node...
								
								$tmp = $test->item($i);
								if( $domdata->removeChild($tmp) ){
									if( !in_array('Region: "'. $value .'" has been deleted',$message) ){
										$message[] = 'Region: "'. $value .'" has been deleted' ;
									}
									$save=true;	
								}
							}
						}
					}
					$data = simplexml_import_dom($domdata);
				}

					if( !in_array('Region: "'. $value .'" has been deleted',$message) ){
						$message[] = 'Region: "'. $value .'" has been deleted' ;
					}
					$save=true;
				break;
			
			
			
			
			case 'Country':
				$item = substr($key, strrpos($key,'_')+1 );
				$thiscountry = $data->xpath("//Country[@ThreeISOCode='".$_POST['SelectCountry']."']");
				$thiscountry[0][$item] = $value;
				if(!in_array('This country\'s details have been updated',$message) ){
					$message[] = 'This country\'s details have been updated';
				}
				$save=true;	
				break;
			
			
			
			case 'Country2ThisRegion':

				$selectedCountries = $_POST['Country2ThisRegion']; // combo box selections
				$selectedregion = $_POST['SelectRegion']; // selected region
				$existingCountries = explode(',',substr($_POST['countrylist'],0,strlen($_POST['countrylist'])-1));	// existing countries for that region

				/*
				echo 'POST vars<br /><pre>';
				print_r($_POST);
				echo '</pre>';	
				
				
				
				echo 'Existing<br /><pre>';
				print_r($existingCountries);
				echo '</pre>';	

				echo 'Selected<br /><pre>';
				print_r($selectedCountries);
				echo '</pre>';
				*/
				
				// Handle new countries
				foreach($selectedCountries as $c){
					if( !in_array($c,$existingCountries) ){
						//echo 'NEW ONE!: '. $c .'<br />';
						// get copy of the country and it's entities and re-create here...
						$query = "//Country[@ThreeISOCode='". $c ."']";
						$country = $countrydata->xpath($query);	
						
						$query = "//Region[@Name='". trim($selectedregion) ."']";
						$region = $data->xpath($query);

						/// We also need to append the global default to this new elements' attributes...
						$newElement = $region[0]->addChild('Country',' ');
						$newElement->addAttribute('CountryCode',$country[0]['CountryCode']);
						$newElement->addAttribute('ThreeISOCode',$country[0]['ThreeISOCode']);
						$newElement->addAttribute('Name',$country[0]['Name']);
						$newElement->addAttribute('OutlineColour',$country[0]['OutlineColour']);
						$newElement->addAttribute('Alpha','100');
						$newElement->addAttribute('CountryColour',$country[0]['CountryColour']);
						$newElement->addAttribute('OverState',$country[0]['OverState']);
						$newElement->addAttribute('DownState',$country[0]['DownState']);
						//$message[] = 'Country: "'. $value .'" has been moved' ;
						$save=true;


					
						// Entities...
						$query = "//Country[@ThreeISOCode='". $c ."']/Entity[@ID>0]";
						$results = $countrydata->xpath($query);

						if(count($results)>0){
						
							foreach($results as $ckey => $entity){
								$newEntity = $newElement->addChild('Entity');
								$newEntity->addAttribute('ID',$entity['ID']);
								$newEntity->addAttribute('Name',$entity['Name']);
								$newEntity->addAttribute('Link',$entity['Link']);
								$newEntity->addAttribute('Desc',$entity['Desc']);
								//$message[] = 'Country: "'. $value .'"\'s entity "'. $entity['Name'] .'" has been moved' ;
							}	
							
						}
						
						
						//// now we need to remove them from countrydata so that we only have one copy...
						if( $domdata = dom_import_simplexml($countrydata) ){
							$test = $domdata->getElementsByTagName('Country');
							
							if($test){
								for($i=0;$i<$test->length;$i++){
									//echo $i.' -> '. $test->item($i)->getAttribute('ThreeISOCode') .'<br />';
									if($test->item($i)->getAttribute('ThreeISOCode') == $c){
										// now delete the node...
										
										$tmp = $test->item($i);
										if( $domdata->removeChild($tmp) ){
											//$message[] = 'Country: "'. $value .'" has been deleted' ;
											$save=true;	
										}
									}
								}
							}
							$data = simplexml_import_dom($domdata);
							//$message[] = 'Countries assigned to <strong>'. $selectedregion .'</strong>';
						}

						
					}
				}		
				
				
				// Drop any we don't need anymore
				foreach($existingCountries as $c){
					if( !in_array($c,$selectedCountries) ){
						//echo 'GONE!: '. $c .'<br />';
						// get copy of the country and it's entities and re-create here...
						$query = "//Country[@ThreeISOCode='". $c ."']";
						$country = $data->xpath($query);	
						/*
						$query = "//Region[@Name='". trim($selectedregion) ."']";
						$region = $data->xpath($query);
						*/
						/// We also need to append the global default to this new elements' attributes...
						if( count($country)>0 ){
							$newElement = $countrydata->addChild('Country',' ');
							$newElement->addAttribute('CountryCode',$country[0]['CountryCode']);
							$newElement->addAttribute('ThreeISOCode',$country[0]['ThreeISOCode']);
							$newElement->addAttribute('Name',$country[0]['Name']);
							$newElement->addAttribute('OutlineColour',$country[0]['OutlineColour']);
							$newElement->addAttribute('Alpha','100');
							$newElement->addAttribute('CountryColour',$country[0]['CountryColour']);
							$newElement->addAttribute('OverState',$country[0]['OverState']);
							$newElement->addAttribute('DownState',$country[0]['DownState']);
							//$message[] = 'Country: "'. $value .'" has been moved' ;
							$save=true;
	
	
						
							// Entities...
							$query = "//Country[@ThreeISOCode='". $c ."']/Entity[@ID>0]";
							$results = $countrydata->xpath($query);
	
							if(count($results)>0){
							
								foreach($results as $ckey => $entity){
									$newEntity = $newElement->addChild('Entity');
									$newEntity->addAttribute('ID',$entity['ID']);
									$newEntity->addAttribute('Name',$entity['Name']);
									$newEntity->addAttribute('Link',$entity['Link']);
									$newEntity->addAttribute('Desc',$entity['Desc']);
									//$message[] = 'Country: "'. $value .'"\'s entity "'. $entity['Name'] .'" has been moved' ;
								}	
								
							}
							
						}
						
						
						if( $domdata = dom_import_simplexml($data) ){
							//echo $domdata->getAttribute('Name').'<br />';
							//$dom = new DOMDocument('1.0');
							//$domdata = $dom->importNode($domdata, true);
							//$domdata = $dom->appendChild($domdata);
							
							$test = $domdata->getElementsByTagName('Country');
							//echo $domdata->childNodes->length.'<br />';
							
							
							if($test){
								for($i=0;$i<$test->length;$i++){
									//echo $i.' -> '. $test->item($i)->getAttribute('ThreeISOCode') .'<br />';
									if($test->item($i)->getAttribute('ThreeISOCode') == $c){
										// now delete the node...
										
										$tmp = $test->item($i);
										//echo $tmp .'<br />';
										//echo $tmp->getAttribute('Name');
										if( $tmp->parentNode->removeChild($tmp) ){
											//$message[] = 'Country: "'. $value .'" has been deleted' ;
											$save=true;	
										}
									}
								}
							}
							$data = simplexml_import_dom($domdata);
							//$message[] = 'Countries removed from <strong>'. $selectedregion .'</strong>';
							
						}
						//}
						//$save=false;
						$msg = 'Changes saved <strong>'. $selectedregion .'</strong>';
						if( !in_array($msg,$message) ){
							$message[] = $msg;
						}
						
					}
				}	
			
				
				break;
			
			
			case 'AddEntity':

				$item = substr($key, strrpos($key,'_')+1 );
				switch( $item ){
					case 'title':
						$thisTitle = $value;
						break;
					case 'description':
						$thisDesc = $value;
						break;
					case 'link':
						$thisLink = $value;
						break;
					case 'parent':
						$thisCountry = $value;
						break;
				}

				if($thisTitle && $thisDesc && $thisLink && $thisCountry ){
				
					$thiscountry = $data->xpath("//Country[@Name='".$thisCountry."']");
					$id = count( $thiscountry[0]->children() ) + 1;
					
					$newElement = $thiscountry[0]->addChild('Entity');
					$newElement->addAttribute('ID', $id );
					$newElement->addAttribute('Name',$thisTitle);
					$newElement->addAttribute('Link',$thisLink);
					$newElement->addAttribute('Desc',$thisDesc);
					
					$msg = 'This country\'s details have been updated';
					if(!in_array($msg,$message) ){
						$message[] = $msg;
						$save=true;	
						$view='entity2country';	
						$selectCountry = $thisCountry;
						unset($_POST);
					}
									
				}
				
				break;




			case 'EditEntity':

				$item = substr($key, strrpos($key,'_')+1 );
				switch( $item ){
					case 'title':
						$thisTitle = $value;
						break;
					case 'description':
						$thisDesc = $value;
						break;
					case 'link':
						$thisLink = $value;
						break;
					case 'parent':
						$thisCountry = $value;
						break;
					case 'code':
						$thisID = $value;
				}

				if($thisTitle && $thisDesc && $thisLink && $thisCountry && $thisID ){
				
					$thisEntity = $data->xpath("//Country[@Name='".$thisCountry."']/Entity[@ID='". $thisID ."']");
					$thisEntity[0]['Name'] = stripslashes($thisTitle);
					$thisEntity[0]['Link'] = $thisLink;
					$thisEntity[0]['Desc'] = str_replace("\n","", strip_tags( stripslashes($thisDesc) ) );
					
					$msg = 'Your changes have been saved';
					if(!in_array($msg,$message) ){
						$message[] = $msg;
						$save = true;	
						$view = 'entity2country';	
						$selectCountry = $thisCountry;
						unset($_POST);
					}
				
				}

				break;			




			case 'DelEntity':
			
				$item = substr($key, strrpos($key,'_')+1 );
				switch( $item ){
					case 'parent':
						$thisCountry = $value;
						break;
					case 'code':
						$thisID = $value;
				}

				if( $thisCountry && $thisID ){
					//echo $thisCountry .' '. $thisID.'<br /><br />';
				
						if( $domdata = dom_import_simplexml($alldata) ){
							//echo $domdata->getAttribute('Name').'<br />';
							//$dom = new DOMDocument('1.0');
							//$domdata = $dom->importNode($domdata, true);
							//$domdata = $dom->appendChild($domdata);
							
							$test = $domdata->getElementsByTagName('Entity');
							//echo $domdata->childNodes->length.'<br />';
							
							
							if($test){
							
								for($i=0;$i<$test->length;$i++){
									//echo $i.' -> '. $test->item($i)->getAttribute('ThreeISOCode') .'<br />';
									if($test->item($i)->parentNode->getAttribute('Name') == $thisCountry){
										// now delete the node...
										
										$tmp = $test->item($i);
										
										//echo $tmp->getAttribute('Name').'<br />';
										
										if($tmp->getAttribute('ID')==$thisID){
											if( $tmp->parentNode->removeChild($tmp) ){
												$message[] = 'Item deleted';
												$data = simplexml_import_dom($domdata);
												$save = true;	
												$view = 'entity2country';	
												$selectCountry = $thisCountry;
											}
										}
										
									}
								}
							}
		
							
					}


					//echo $node->length.'<br />';

					//if($node){
					/*
						for($i=0;$i<$node->length;$i++){
							//echo $i .' = '. $node->item($i)->getAttribute('Name').'<br />';
							if($node->item($i)->getAttribute('Name') == $thisCountry){
								$country = $node->item($i);
								echo '<strong>'. $country->getAttribute('Name').'</strong><br />';
								// now delete the node...
								
								echo  count($country->childNodes) .'<br />';
								
								$childrenCount = count($country->childNodes);
								$children = $country->childNodes;
								
echo $country;
								//for( $j=0; $country->length; $j++ ){
								foreach( $country as $c){
									echo '"'.$c->item(0)->nodeName.'"<br />';
								}
								
								
								//echo $tmp->getAttribute('ID');
								/*
								if( $tmp->parentNode->removeChild($tmp) ){
									//$message[] = 'Country: "'. $value .'" has been deleted' ;
									$save=true;	
								}
								
							}
						}
						*/
					//}
					
					/*
					$msg = 'Your changes have been saved';
					if(!in_array($msg,$message) ){
						$message[] = $msg;
						$save = true;	
						$view = 'entity2country';	
						$selectCountry = $thisCountry;
						unset($_POST);
					}
					*/

				
				}
				
				break;
				

			default:
				break;
		}
		
	}
	
	if(isset($alldata) && $save){
		$data['GeneratedDate'] = date('d/m/Y H:i:s');
		$alldata->asXML($xmlfile);
	}
	unset($data);
}

//// after ensuring that we've saved whatever output we've had, might as well start fresh with a new instance of $data, eh...
$alldata = simplexml_load_file($xmlfile);
if ($alldata) {
	$data = $alldata->xpath("//RegionsCountriesCollection[@ID='0']");
	$data = $data[0];
	$countrydata = $alldata->xpath("//RegionsCountriesCollection[@ID='1']/Countries");
	$countrydata = $countrydata[0];
	
	$allCountryData = $alldata->xpath("//Country");
	//$allCountryData = $allCountryData[0];
	/*
	echo '<pre>';
	print_r($allCountryData);
	echo '</pre>';
	*/
	$allCountriesList = array();
	
	foreach($allCountryData as $cd){
		$allCountriesList[(string)$cd['Name']] = (string)$cd['ThreeISOCode'];
	}
	ksort($allCountriesList);



	/*
	echo '<pre>';
	print_r($allCountriesList);
	echo '</pre>';
	*/
	
	/*
	echo '<pre>';
	print_r($data[0]->attributes());
	echo '</pre>';
	*/
	
	
	
	
	
	//// $message is an array for any user feedback...
	if($message){
		echo drawFeedback($feedback,$message);
	
		if($debug){
				echo '<pre>';
				print_r($_POST);
				echo '</pre>';
		}
	} 
	
	/////////////////////////////////// Interface /////////////////////////////////////////
	
	switch($view){
		case 'global':
			
		?>
			<!--// GLOBAL SETTINGS //-->
			<form action="#" method="post" name="global">
			<input type="hidden" name="p" value="global" />
			<fieldset>
				<legend>Global settings</legend>
				<ul class="formlist">
				<!--//
				<li>
					<label for="Global_Title">Map title:</label>&nbsp;
					<input type="text" name="Global_Title" class="textfield" size="35" value="<?= $data['Title'] ?>" />
				</li>
				//-->
				<?	
				$i=0;	
				foreach($data->attributes() as $key => $value){
					switch($key){
						case 'OverState':
							$label = 'Mouse Over Colour';
							break;
						case 'DownState':
							$label = 'Mouse Down Colour';
							break;
						default:
							$label = $key;
							break;
					}
					if( in_array( $i , array(2,4,5,6) ) ){
					?>
						<li>
							<label for="Global_<?= $key ?>"><?= $label ?>:</label> 
							<span>#&nbsp;</span>
							<input type="text" name="Global_<?= $key ?>" value="<?= $value ?>" size="10" />
						</li>
					<? 
					}
					$i++;
				}
				?>
				</ul>
				<fieldset class="buttons">
					<button type="submit" class="submit" />submit</button>
				</fieldset>
			</fieldset>
			</form>
	
	
		<?
		break;
		
		
			
		// --------------------------------------------------------------------
		// ---------------------- MANAGE REGIONS PAGE -------------------------
		case 'regions':
		?>
		<!--// EDIT REGIONS //-->
		<form action="#" method="post" name="regions" id="regions">
		<input type="hidden" name="p" value="<?=$view?>" />
		<fieldset>
			<legend>World Regions</legend>
			<p><a href="?p=add_region">Add new region</a></p>
			<?	
			$i=0;	
			foreach($data->Region as $item) {
				?>
				<fieldset>
					<label for="Region_<?=$i?>">Region <?=($i+1)?>:</label> 
					<input type="text" id="Region_<?=$i?>" name="Region_<?= $i ?>" value="<?= stripslashes($item['Name']) ?>" size="50" class="textfield" />
					<fieldset class="buttons">
						<button name="Delete_Region_<?= $item['RegionCode'] ?>" type="submit" class="submit">Delete</button>
						<button name="Setup_Region_<?= $item['RegionCode'] ?>" type="submit" class="submit">Settings</button>
					</fieldset>
				</fieldset>
				<? $i++;
			}
			?>
			<fieldset class="buttons">
				<button type="submit" value="submit" class="submit">submit</button>
			 </fieldset>
		</fieldset>
		</form>
	
		
		<? if($view=='regions' && strlen($selectRegionCode)>0){  ?>
		
		<form action="#" method="post" name="regions" id="regioncode">
		<input type="hidden" name="p" value="<?=$view?>" />
		<input type="hidden" name="selectRegionCode" value="<?= $selectRegionCode ?>" />
		<fieldset>
			<legend><?=ucfirst($selectRegionCode)?> settings</legend>
			<ul class="formlist">
			<?	
				$i=0;
				//print "find region($selectRegionCode)<br>";
				$thisregion = $data->xpath("//Region[@RegionCode='".$selectRegionCode."']");
				foreach($thisregion[0]->attributes() as $key => $value){
					if( in_array( $i , array(3,4,5,6,7) ) ){
					?>
						<li>
							<label for="Setup_<?= $key ?>"><?= $key ?>:</label> 
							<input type="text" name="Setup_<?= $key ?>" value="<?= $value ?>" size="10" />
						</li>
					<? 
					}
					$i++;
				}
				//echo '<li>'. $thiscountry[0]->hasChildren() .'</li>';
			?>
			</ul>
			<fieldset class="buttons">
				<button type="submit" class="submit">submit</button>
			</fieldset>
		</fieldset>
		</form>
		<? 
		} 
		break;
		// END ------------------ MANAGE REGIONS PAGE -------------------------
			
		
		case 'add_region':
		?>
		<!--// ADD NEW REGION //-->
		<form action="#" method="post" name="add_region">
		<input type="hidden" name="p" value="regions" />
		<fieldset>
			<legend>Add new Region</legend>
			<ul class="formlist">
				<li>
					<label for="Add_RegionCode">Short Region Code:</label>
					<input type="text" name="Add_RegionCode" size="10" value="<?= $_POST['Add_RegionCode'] ?>" />
				</li>
				<li>
					<label for="Add_Region">Region Name:</label>
					<input type="text" name="Add_Region" size="50" value="<?= stripslashes($_POST['Add_Region']) ?>" />
				</li>
			</ul>
			<fieldset class="buttons">
				<button type="submit" class="submit">submit</button>
			</fieldset>
		</fieldset>
	</form>
	
	
		
		<?
		break;
			
	
	
	
		case 'delete_regions':
		?>
	
		<!--// REMOVE REGIONS //-->
		<form action="#" method="post" name="delete_regions">
		<input type="hidden" name="p" value="regions" />
		<fieldset>
			<legend>Remove World Regions</legend>
			<p><strong>For the moment, select only one at a time</strong></p>
			<ul class="formlist">
			<?	
				$i=0;	
				foreach($data->Region as $item){
				?>
				<li>
					<input type="checkbox" name="Delete_Region_<?= $item['RegionCode'] ?>"<? if($_POST['Delete_Region_'.$item['RegionCode']] == 'on'){ echo 'checked="checked"'; } ?> /> 
					<label for="Delete_Region_<?= $item['RegionCode'] ?>" style="float:none;display:inline;"><?= stripslashes($item['Name']) ?></label>
				</li>
				<? 
				$i++;
			}
			?>
			</ul>
		<input type="submit" value="submit" class="submit" />
		</fieldset>
		</form>
		<?
		break;
	
	
			
		// ----------------------------------------------------------------------
		// ---------------------- MANAGE COUNTRIES PAGE -------------------------
		case 'countries':
		?>
	
		<!--// COUNTRIES LIST //-->
		<div id="countries" class="formholder" style="padding-top:0;">
		<form action="#" method="post" name="countries">
		<input type="hidden" name="p" value="countries" />
		<fieldset>
				<legend>Countries</legend>
				<ul class="formlist">
					<li>
					<label for="SelectCountry">Countries</label>
					<select name="SelectCountry">
					<?	
						//$countries = simplexml_load_file($xmlfile2);
						foreach($allCountriesList as $country => $iso){
					?>
						<option value="<?= $iso ?>"<? if($_POST['SelectCountry'] == $iso){ echo 'selected="selected"'; } ?>><?= $country ?></option>		
					<?	}	?>
					</select>
					</li>
				</ul>
				<fieldset class="buttons">
				<button type="submit" class="submit">submit</button>
				</fieldset>
			</fieldset>
		</form>
		
	
		<? if($view=='countries' && isset($_POST['SelectCountry'])){  ?>
			<form action="#" method="post" name="countries">
			<input type="hidden" name="p" value="countries" />
			<input type="hidden" name="SelectCountry" value="<?= $_POST['SelectCountry'] ?>" />
			<fieldset>
				<legend>Country settings</legend>
				<ul class="formlist">
				<?	
					$i=0;
					$thiscountry = $data->xpath("//Country[@ThreeISOCode='".$_POST['SelectCountry']."']");
					foreach($thiscountry[0]->attributes() as $key => $value){
						if( in_array( $i , array(3,4,5,6,7) ) ){
						?>
							<li>
								<label for="Country_<?= $key ?>"><?= $key ?>:</label> 
								<input type="text" name="Country_<?= $key ?>" value="<?= $value ?>" size="10" />
							</li>
						<? 
						}
						$i++;
					}
					//echo '<li>'. $thiscountry[0]->hasChildren() .'</li>';
				?>
				</ul>
				<fieldset class="buttons">
					<button type="submit" class="submit">submit</button>
				</fieldset>
			</fieldset>
			</form>
		<? } ?>
	
		</div>
		<?
		break;
		// END ------------------ MANAGE COUNTRIES PAGE -------------------------
			
		
		
		
		
		
		case 'country2region':
		?>
		<!--// ASSIGN COUNTRIES TO A REGION //-->
		<div id="country2region" class="formholder" style="padding-top:0px;">
			<form action="#" method="post" name="regionlist">
			<input type="hidden" name="p" value="<?=$view?>" />
			<fieldset>
				<legend>World Regions</legend>
				<ul class="formlist">
					<li>
						<label for="SelectRegion">Select a Region:</label> 
						<select name="SelectRegion">
					<?	foreach($data->Region as $item){  ?>
						<option<? if($_POST['SelectRegion'] == $item['Name'] ){ echo ' selected="selected"'; } ?>><?= $item['Name'] ?></option>
					<? 	}  ?>
					</li>
				</ul>
				<fieldset class="buttons">
					<button type="submit" class="submit">submit</button>
				</fieldset>
			</fieldset>
			</form>
		
			<? if($_POST['SelectRegion']>''){ ?>
		
			<form ation="#" method="post" name="country2region" id="country2region" <? if($view=='country2region'){ echo 'style="display:block"';} ?>>
			<input type="hidden" name="p" value="<?=$view?>" />
			<input type="hidden" name="SelectRegion" value="<?= $_POST['SelectRegion'] ?>" />
			<fieldset>
					<legend>Assign countries to '<?= $_POST['SelectRegion'] ?>'</legend>
					<?
						$countries = new DOMDocument($xmlfile);
						$countries->load($xmlfile);
						$params = $countries->getElementsByTagName('Region');
						$countrylist = array();
						$countrylibrary = simplexml_load_file($xmlfile);
						
						foreach ($params as $param) {
		
							if( $param->getAttribute('Name') == $_POST['SelectRegion'] ){
							
								// run an xpath query to get the countries we want...	
								$cArray = $countrylibrary->xpath("//Region[@Name='".$_POST['SelectRegion']."']/Country");
								
								if( count($cArray) >0 ){
									foreach($cArray as $node){
										// need to get parameters from the countries/xml file and add them here...
										foreach($node->attributes() as $key => $val){
											if($key=='ThreeISOCode' || $key=='Name'){
												switch($key){
													case 'ThreeISOCode':
														$iso = (string)$val;
														break;
													case 'Name':
														$name = (string)$val;
														break;
												}
											}else{
												$iso = false;
												$name=false;
											}
											
											if($iso && $name){
												array_push($countrylist,array('ThreeISOCode' => $iso,'Name' => $name) );
											}
										}
										
									}
								}else{
									$message[] = 'There was a problem retriving country data from the library';
								}
								
							}
						}		
						unset($countries);				
		
						$allCountries = array();
						
						foreach($countrydata->children() as $cd){
							$allCountries[(string)$cd['Name']] = (string)$cd['ThreeISOCode'];
						}
						ksort($allCountries);
		
					?>
					<ul class="formlist">
						<li>
							<select name="Country2Region" id="Country2Region" size="15" style="margin:0;">
							<?	foreach($allCountries as $country => $iso){	?>
								<option value="<?= $iso ?>"><?= $country ?></option>		
							<?	}  ?>
							</select>
						</li>
						<li>
							<a href="#" id="moveIn" onclick="moveIn()">Add &raquo;</a><br />
							<a href="#" id="moveOut" onclick="moveOut()">&laquo; Delete</a>
						</li>
						<li>
							<select name="Country2ThisRegion[]" id="Country2ThisRegion" size="15" multiple="multiple"  style="margin:0;">
								<? if( isset($countrylist) ){  foreach($countrylist as $c){  ?>
								<option value="<?= $c['ThreeISOCode'] ?>"><?= $c['Name'] ?></option>	
								<? } } ?>	
							</select>
						</li>
					</ul>
					<fieldset class="buttons">
						<button type="submit" class="submit" id="country2regionSelect" onClick="testSubmit()">submit</button>
					</fieldset>
					<input type="hidden" name="countrylist" value="<? if( isset($countrylist) ){ foreach($countrylist as $c){ echo $c['ThreeISOCode'].','; } } ?>" />
					<? 
					unset($countries,$countrylist,$tmplist,$countrylibrary);
					?>
				</fieldset> 
			</form>
		<? } ?>
		</div>
		<?  
		break;
		
		
		
		
			
	//// This manages the data within a country...
		case 'entity2country':
		
		if(!isset($selectCountry)){ $selectCountry = $_POST['SelectCountry']; }
		?>
	
		<!--// ASSIGN DATA TO A COUNTRY //-->
		<div id="entity2country" class="formholder">
		<form action="#" method="post" name="countrylist">
		<input type="hidden" name="p" value="<?=$view?>" />
		<fieldset>
			<legend>Countries</legend>
			<ul class="formlist">
				<li>
					<label for="SelectCountry">Select a Country:</label> 
					<select name="SelectCountry">
				<?	foreach($allCountriesList as $country => $iso){  ?>
					<option<? if($selectCountry == $country ){ echo ' selected="selected"'; } ?>><?= $country ?></option>
				<? 	}  ?>
				</li>
			</ul>
			<fieldset class="buttons">
				<button type="submit" class="submit">submit</button>
			</fieldset>
		</fieldset>
		</form>
		
		<? if($selectCountry>''){ ?>
			<form ation="#" method="post" name="entity2country" id="entity2country" <? if($view=='entity2country'){ echo 'style="display:block"';} ?>>
				<input type="hidden" name="SelectCountry" value="<?= $selectCountry ?>" />
				<fieldset>
					<legend>Information associated with '<?= $selectCountry ?>'</legend>
					<?
						//echo $allCountryData->Country->Name.'<br />';
						foreach( $allCountryData as $c ){
							//echo $c['Name'].'<br />';
							if( $c['Name'] == $selectCountry ){
								if( count($c->children())>0 ){
									foreach($c->children() as $entity){
										$entities[] = $entity;
									}
								}
							}
						}
						
					?>
					<p><a href="?p=addentity&country=<?= urlencode($selectCountry) ?>">Add information to this country</a></p>
					<?
					// Now we can write out our table of existing entities...
					if( sizeof($entities)>0 ){
					?>
					<table id="entities">
						<thead>
							<tr><th>Name</th><th>Functions</th></tr>
						</thead>
						<tbody>
							<? foreach( $entities as $e ){ ?>
							<tr>
								<td class="entityTitle"><?= $e['Name']?></td>
								<td class="entityFunctions">
									<a href="?p=editentity&code=<?=$e['ID'] ?>&country=<?= urlencode($selectCountry) ?>">Edit</a> | 
									<a href="?p=delentity&code=<?=$e['ID'] ?>&country=<?= urlencode($selectCountry) ?>">Delete</a>
								</td>
							</tr>
							<? } ?>
						</tbody>
					</table>
					<fieldset class="buttons">
						<button type="submit" class="submit" id="country2regionSelect" onClick="testSubmit()">submit</button>
					</fieldset>
					<input type="hidden" name="country" value="<?= $selectCountry ?>" />
					<? }else{ ?>
						<p>There is no information stored for this country</p>
					<? } ?>
				<? 
				unset($countries,$countrylist,$tmplist,$countrylibrary);
				?>
				</fieldset> 
			</form>
		<? } ?>
		</div>
		<?
		break;
		
		
		
		
		
		case 'addentity':
		/*
		when we process this we can just use the number of children to give it an ID,
		its unlikely that we'll need to move an entity.  If they do, then cut-n-paste!
		*/
		?>
		<form name="addentity" id="addentity" method="post">
		<fieldset>
			<legend>Add more information to <?= $_GET['country'] ?></legend>
			<ul class="formlist">
			<li>
				<label for="AddEntity_title">Title</label>
				<input type="text" name="AddEntity_title" value="<?= $_POST['AddEntity_title'] ?>" maxlength="70" />
			</li><li>
				<label for="AddEntity_description">Description</label>
				<textarea name="AddEntity_description" id="desc" rows="10" cols="50" maxlength="140"><?= $_POST['AddEntity_description'] ?></textarea>
			</li><li>
				<label for="AddEntity_link">Link</label>
				<input type="text" name="AddEntity_link" value="<?= $_POST['AddEntity_link'] ?>" maxlength="120" />
			</li>
			</ul>
			<input type="hidden" name="AddEntity_parent" value="<?= $_GET['country'] ?>" />
			<fieldset class="buttons">
				<button type="submit" class="submit" value="submit">submit</button>
			</fieldset>
		</fieldset>
		</form>
		<?
		break;
		
		
		
	
		case 'editentity':
		// fill with data...
			foreach( $allCountryData as $c ){
				if( $c['Name'] == $_GET['country'] ){
					if( count($c->children())>0 ){
						foreach($c->children() as $entity){
							if($entity['ID']==$_GET['code']){
								$code = $entity['ID'];
								$title = $entity['Name'];
								$description = $entity['Desc'];
								$link = $entity['Link'];
							}
						}
					}
				}
			}
		?>
	
		<form name="editentity" id="editentity" method="post">
		<fieldset>
			<legend>Edit this information from <?= $_GET['country'] ?></legend>
			<ul class="formlist">
			<li>
				<label for="EditEntity_title">Title</label>
				<input type="text" name="EditEntity_title" value="<?= stripslashes($title) ?>" maxlength="70" />
			</li><li>
				<label for="EditEntity_description">Description</label>
				<textarea name="EditEntity_description" id="desc" rows="10" cols="50" maxlength="140"><?= stripslashes($description) ?></textarea>
			</li><li>
				<label for="EditEntity_link">Link</label></td>
				<input type="text" name="EditEntity_link" value="<?= $link ?>" maxlength="120" />
			</li>
			</ul>
			<input type="hidden" name="EditEntity_code" value="<?= $code ?>" />
			<input type="hidden" name="EditEntity_parent" value="<?= $_GET['country'] ?>" />
			<fieldset class="buttons">
				<button type="submit" class="submit" value="submit">submit</button>
			</fieldset>
		</fieldset>
		</form>
		<?
		break;
		
		
		
		
		
		case 'delentity':
		// fill with data...
			foreach( $allCountryData as $c ){
				//echo $c['Name'].'<br />';
				if( $c['Name'] == $_GET['country'] ){
					if( count($c->children())>0 ){
						foreach($c->children() as $entity){
							if($entity['ID']==$_GET['code']){
								$code = $entity['ID'];
								$title = $entity['Name'];
								$description = $entity['Desc'];
								$link = $entity['Link'];
							}
						}
					}
				}
			}
		?>
		<form name="delentity" id="delentity" method="post">
			<fieldset>
				<legend>Are you sure you want to delete this entity from <?= $_GET['country']?>?</legend>
				<table id="entities">
					<tbody>
						<tr>
							<td class="entityTitle" valign="top">Title</td>
							<td class="entityItem"><?= $title ?></td>
						</tr>
						<tr>
							<td class="entityTitle" valign="top">Description</td>
							<td class="entityItem"><?= $description ?></td>
						</tr>
						<tr>
							<td class="entityTitle" valign="top">Link</td>
							<td class="entityItem"><?= $link ?></td>
						</tr>
					</tbody>
				</table>
				<p class="message">Once you click 'submit', this will be <strong>completely deleted</strong>
				 from the site and cannot be recovered</p>
				<input type="hidden" name="DelEntity_code" value="<?= $code ?>" />
				<input type="hidden" name="DelEntity_parent" value="<?= $_GET['country'] ?>" />
				<fieldset class="button">
					<button type="submit" class="submit">submit</button>
				</fieldset>
			</fieldset>
		</form>	
		<?
		break;
		
	} //end switch
	
	
	
	//// if we're in debug mode then print out the whole XML file to check contents...
	if($debug){
		//// DEBUG ////
		echo '<pre>';
		print_r( $data );
		echo '</pre>';	
	}
	
	unset($data);
	
}
else print "<p style='float:left;clear:left;'>Uh oh - failed to load the xml file</p>";


?>

