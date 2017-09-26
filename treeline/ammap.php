<?php
	ini_set("display_errors", 1);
	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/treeline.init.php");	
	include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/ammap.class.php");	

	//$page = new Page();

	
	$action = read($_REQUEST,'action','');
	
	$pageTitleH2 = 'Ammap Creator';
	$pageTitle = $pageTitleH2;
	
	$css = array('forms','tables'); // all CSS needed by this page
	$extraCSS = ''; // extra on page CSS
	
	$js = array(); // all external JavaScript needed by this page
	$extraJS = ''; // extra on page JavaScript
	
	
	$mapName = read($_REQUEST,'name', null);
	$zoomLevel = read($_REQUEST,'zoomlevel', null);
	$zoomLong = read($_REQUEST,'zoomlong', 'EMPTY');
	$zoomLat = read($_REQUEST,'zoomlat', 'EMPTY');
	//New Ammap object
	$map = new Ammap(1, $mapName, $zoomLevel, $zoomLong, $zoomLat);
	
	//print 'BEFORE: '.$mapName.$zoomLevel.$zoomLong.$zoomLat;
	//print '<br>GET: '.$map->mapName.$map->zoomLevel.$map->zoomLong.$map->zoomLat;
	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') 
	{
		if ($action='create')
		{
			$map->create();
		}
	}
	
	
	include($_SERVER['DOCUMENT_ROOT'].'/treeline/includes/templates/header.inc.php');
	
?>


<div id="primarycontent">
	<div id="primary_inner">
		<?php
			//Actions
			//Edit map
			if ($action == 'edit')
			{
					$formHtml .= '<form action="' .$_SERVER['REQUEST_URI']. '" method="post">
							  <fieldset>';
					$formHtml .= '<div class="field">';				
						$formHtml .='<label>Map Name: </label>
									 <input type="text" name="name">';								
					$formHtml .= '</div>';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Level: </label>';
						$formHtml .= '<input type="text" name="zoomlevel">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Longitude: </label>';
						$formHtml .= '<input type="text" name="zoomlong">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Latitude: </label>';
						$formHtml .= '<input type="text" name="zoomlat">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
					$formHtml .= '<input class="submit" value="Submit" type="submit">';
					$formHtml .= '</fieldset></form>';
				
				$html .= treelineBox($formHtml, 'Create', "blue");
				
			}
			//Create new map
			else if ($action == 'create')
			{
				$formHtml .= '<p class="instructions">This section allows users to create a new map. To add a hotspot, enter edit mode after the map is created.</p>';
				$formHtml .= '<form action="' .$_SERVER['REQUEST_URI']. '" method="post">
							  <fieldset>';
					$formHtml .= '<div class="field">';				
						$formHtml .='<label>Map Name: </label>
									 <input type="text" name="name">';								
					$formHtml .= '</div>';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Level: </label>';
						$formHtml .= '<input type="text" name="zoomlevel">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Longitude: </label>';
						$formHtml .= '<input type="text" name="zoomlong">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
						$formHtml .= '<label>Zoom Latitude: </label>';
						$formHtml .= '<input type="text" name="zoomlat">';
					$formHtml .= '</fieldset>';
					$formHtml .= '<fieldset>';
					$formHtml .= '<input class="submit" value="Submit" type="submit">';
				$formHtml .= '</fieldset></form>';
				
				$html .= treelineBox($formHtml, 'Create', "blue");
				$html .='';
			}
			//Default action
			//Show list of maps
			else
			{
				$html .= treelineBox('<a href="?action=create">Create new map</a>', 'Map Options', "blue");
				$html .= '<h2>Edit a current maps</h2>';
			}
			
			
			
			
			print $html;
		?>
	</div>
</div>




<?php
include($_SERVER['DOCUMENT_ROOT']."/treeline/includes/templates/footer.inc.php"); 
?>