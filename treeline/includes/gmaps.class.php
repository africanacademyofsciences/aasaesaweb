<?php

class gmaps {

	public $errmsg = array();
	public $title;
	public $longitude, $latitude;
	public $zoom, $mapType, $infoWindowWidth, $mapWidth, $mapHeight;
	
	public $marker;
	public $markers = array();
	
	private $perPage;
	private $page;
	private $totalresults;
	private $counter = 0;
	
	public function GMaps($counter=1, $perPage=10) {
		$this->perPage=$perPage;
		
		// Set some default values
		$this->zoom = 7;
		$this->mapType = "SATELLITE";
		$this->infoWindowWidth=200;
		$this->mapWidth = 300;
		$this->mapHeight = 300;
		$this->marker = new Marker();

		$this->counter = $counter;
		//print "<!-- set counter to(".$this->counter.") -->\n";
	}


	public function create() {
		global $db, $site;
		if ($_POST['title'] && ($_POST['long']+0)!=0 && ($_POST['lat']+0)!=0) {
			$guid = uniqid();
			$query = "INSERT INTO googlemap
				(
					guid, title, date_added, msv, 
					`long`, lat, zoom, map_type,
					height, width,
					date_modified)
				VALUES
				(
					'$guid', '".$db->escape($_POST['title'])."', 
					NOW(), ".$site->id.", 
					".($_POST['long']+0).", ".($_POST['lat']+0).",
					".($_POST['zoom']+0).", '".($_POST['type'])."',
					".($_POST['height']+0).", ".($_POST['width']+0).", 
					NOW()
				)
				";
			if (!$db->query($query)) $this->errmsg[]="Failed to save new map[$query]";
			else {
				$this->guid = $guid;
				return true;
			}
			//print "$query<br>\n";
		}
		else $this->errmsg[]="You must enter a title and longitude/latitude coordinates for you new map";
	}

	public function save() {
		global $db, $site;
		if ($_POST['title'] && $this->guid) {
			$query = "UPDATE googlemap SET
				title='".$db->escape($_POST['title'])."', 
				`long`=".($_POST['long']+0).",
				lat=".($_POST['lat']+0).", 
				zoom=".($_POST['zoom']+0).", 
				map_type='".($_POST['type'])."',
				height=".($_POST['height']+0).", 
				width=".($_POST['width']+0).",
				date_modified=NOW()
				WHERE guid='".$this->guid."'
				";
			if (!$db->query($query)) $this->errmsg[]="Failed to save map data[$query]";
			else return true;
		}
		else $this->errmsg[]="You must enter a title";
	}
	
	public function delete($guid) {
		global $db;
		$query = "DELETE FROM googlemap WHERE guid = '$guid'";
		//print "$query<br>\n";
		if (!$db->query($query)) $this->errmsg[]="Failed to delete map record";
		else {
			$query = "DELETE FROM googlemap_marker WHERE parent = '$guid'";
			//print "$query<br>\n";
			$db->query($query);
			if ($db->last_error) $this->errmsg[]="Failed to delete map markers";
			else return true;
		}
		return false;
	}
	
	public function loadByGUID($guid) {
		global $db;
		if ($guid) {
			$this->guid = $guid;
			$query = "SELECT * FROM googlemap WHERE guid = '$guid' LIMIT 1";
			//print "$query<br>\n";
			if ($row = $db->get_row($query)) {
				$this->title = $row->title;
				$this->longitude = $row->long;
				$this->latitude = $row->lat;
				$this->zoom = $row->zoom;
				$this->mapType = $row->map_type;
				$this->mapHeight = $row->height;
				$this->mapWidth = $row->width;
				
				$this->marker->setParent($this->guid);
				$this->loadMarkers();
				//print "got markers(".print_r($this->markers).")<br>\n";
			}
			else $this->errmsg[]="Failed to log map($guid)";
		}
	}
	
	public function loadMarkers() {
		global $db;
		$i=0;
		$query = "SELECT id, title, `long`, lat, `type`, content FROM googlemap_marker WHERE parent = '".$this->guid."'";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) {
			foreach ($results as $result) {
				$this->markers[$i]['id'] = $result->id;
				$this->markers[$i]['title'] = $result->title;
				$this->markers[$i]['longitude'] = $result->long;
				$this->markers[$i]['latitude'] = $result->lat;
				$this->markers[$i]['type'] = $result->type;
				$this->markers[$i]['content'] = $result->content;
				$i++;
			}
		}
	}
	
		
	// If admin == true then the map is being previewed in Treeline rather than on the site.
	public function drawMap($admin=false) {

		$exStyle = '';	
		if ($admin) $exStyle='clear:left;margin-bottom:20px;';

		//mt_srand(make_seed());
		$fid = mt_rand(1,100)+$this->counter;
		$html = '
<div id="map-holder-'.$this->guid.'" class="iframe-rwd" style="'.$exStyle.'">
<div id="map-'.($fid.$this->guid).'" class="map-div" style="height:'.$this->mapHeight.'px;width:'.$this->mapWidth.'px;"></div>
<script type="text/javascript">
	// Set mapTypeId to ROADMAP/SATELLITE/HYBRID/TERRAIN
	function initialize'.$fid.'() {
		var latlng = new google.maps.LatLng('.$this->latitude.', '.$this->longitude.');
		var myOptions = {
		  zoom: '.$this->zoom.',
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.'.strtoupper($this->mapType).'
		};
		
		var map'.$fid.' = new google.maps.Map(document.getElementById("map-'.($fid.$this->guid).'"),
			myOptions);
			
		// Show all markers 	
		var thisll;
		var contentstring;
		var image = "/img/icons/house.png";
		';
		
		foreach ($this->markers as $marker) {
			//print "got marker(".print_r($marker, true).")<br>\n";
			$html.='
			thisll = new google.maps.LatLng('.$marker['latitude'].', '.$marker['longitude'].');
			';
			if ($marker['type']=="HOUSE") {
				$html.='
				var marker'.$i.' = new google.maps.Marker({
					position: thisll,
					map: map'.$fid.',
					title: "'.$marker['title'].'",
					icon: image
				});
				';
			}
			else {
				$html.='			
				var marker'.$i.' = new google.maps.Marker({
				  position: thisll,
				  title:"'.$marker['title'].'",
				  map: map
				});
				// To add the marker to the map, call setMap();
				//marker'.$i.'.setMap(map);    
				';
			}
			if ($marker['content']) {
				$html.='
				contentString = "'.$marker['content'].'";
				var infowindow'.$i.' = new google.maps.InfoWindow({
					content: contentString,
					maxWidth: '.$this->infoWindowWidth.'
				});
				google.maps.event.addListener(marker'.$i.', "click", function() {
				  infowindow'.$i.'.open(map,marker'.$i.');
				});
				';
			}
			$i++;
		}		
		
		$html.='
	}
  
	initialize'.$fid.'();
</script>
</div>
		';
		
		return $html;
		
	}

	private function getPerPage() {
		return $this->perPage;
	}

	private function setPage($page) {
		$this->page = $page;
	}
	private function getPage() {
		return $this->page;
	}

	private function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}

	public function drawTotal() {
		$to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
		if($this->getTotal()==1) $msg = 'There is only 1 map in the site';
		else $msg = 'Showing maps '. ($this->from+1) .'-'. $to .' of '. $this->getTotal() .' ';
		return $msg;
	}
	

	public function getMapsList($keywords='') {
		global $db, $site;

		$this->from = $this->getPerPage()*($this->getPage()-1);
		$this->to = $this->getPerPage()*$this->getPage();

		$select = "guid, title, date_added, date_modified, 
			DATE_FORMAT(date_modified, '%D %M %Y') AS modified";
		$from = " 
			FROM googlemap gm
			WHERE msv=".$site->id."
			".($keywords?"AND title LIKE '%$keywords%'":"")."
			ORDER BY gm.date_modified DESC,title ASC 
			";
		$total_query = "SELECT guid $from";
		$query = "SELECT $select $from
			LIMIT ". $this->from .",". $this->getPerPage();

		// Get total results and set number of pages etc...
		//print "$total_query<br>\n";
		$db->query($total_query);
		$this->setTotal($db->num_rows);	
		$db->flush();
		
		////niceError($query);
		//print "$query<br>\n";
		$pages = $db->get_results($query);
		if(sizeof($pages)>0) return $pages;
		else return false;
	}

	// 16th Feb 2011 - Phil Redclift
	// page is for pagination
	public function drawMapsList($page=1, $keywords=''){

    	global $site, $db;
		//print "dPL(page-$page, action-$action, cat-$cat, term-$term, type-$type, format-$format, guid-$guid)<br>\n";
		$this->setPage($page);
		
		$action = $_REQUEST['action'];
		$results = $this->getMapsList($keywords);
		
		if ($results){

			$html = '<table class="tl_list">
<caption>'. $this->drawTotal($format) .'</caption>
<thead>
<tr>
	<th scope="col">Title</th>
	<th scope="col">Status</th>
	<th scope="col">Modified</th>
	<th scope="col">Manage this map</th>
</tr>
</thead>
<tbody>
';
			foreach($results as $result){

				// Have to truncate long titles as mess up my layout.
				$title=(strlen($result->title)>25)?substr($result->title,0,22)."...":$result->title;

				$html .= '<tr>
<td><strong>'.$title .'</strong>
<td>&nbsp;</td>
<td>'.$result->modified.'</td>
<td class="action">
'.$this->drawMapCheckboxes($result->guid).'
</td>
';
			}
			$html .= "</tbody>\n</table>\n";
			//$html .= $this->getPagination($page,$action,$cat,$term);
			//print "dP(".$this->getTotal().", ".$this->getPerPage().", $page, url)<br>\n";
			$html .= drawPagination($this->getTotal(), $this->getPerPage(), $page, '/treeline/googlemap/?action=edit&keywords='.$keywords);
			
			return $html;
		}
		else {
			return '<p>There are no maps to display</p>';
		}
	}
	
	public function drawMapCheckboxes($guid) {
		global $db, $help, $site;
		//print "dECb(g($guid), t($type), tt($template_name), tid($template_id), pub($publishable), lok($locked), off($offline))<br>\n";

		// Set default options...
		$no_link='<span class="no-action"></span>';
		$deletelink=$previewlink=$editlink=$no_link;
		
		$previewlink = '<a '.$help->drawInfoPopup("Preview this map").' class="preview'.$ex_class.'" href="/treeline/googlemap/?action=preview&guid='.$guid.'">Preview</a>';
		$editlink = '<a '.$help->drawInfoPopup("Edit this map").' class="edit" href="/treeline/googlemap/?action=edit&guid='.$guid.'">Edit</a>';
		// Set up publish link and delete link if we are allowed
		if($_SESSION['treeline_user_group']!="Author"){
			$deletelink = '<a '.$help->drawInfoPopup("Delete this map").' class="delete" href="/treeline/googlemap/?action=delete&guid='.$guid.'">Delete</a>';
		}

		$html = $previewlink.$editlink.$deletelink;		
		return $html;
	}
	
	
	public function drawForm($action, $guid="") {

		$form_action = $page_html = '';
		if ($action=="create") $form_action = "Create";
		else if ($action=="edit") $form_action = "Save";
		else if ($action=="create-marker") $form_action = "Save";
		else if ($action=="edit-marker") $form_action = "Save";
		else if ($action=="delete-marker") $form_action = "Save";

		if (!$guid) $guid=$this->guid;
		//print "dF($action, $guid)<br>\n";
		
		if ($form_action) {

			$title = $_POST?$_POST['title']:$this->title;
			$type = $_POST?$_POST['type']:$this->mapType;
			$zoom = $_POST?$_POST['zoom']:$this->zoom;
			$longitude = ($_POST?$_POST['long']+0:$this->longitude);
			$latitude = ($_POST?$_POST['lat']+0:$this->latitude);
			$height = ($_POST?$_POST['height']+0:$this->mapHeight);
			$width = ($_POST?$_POST['width']+0:$this->mapWidth);

			// Touch pesky but if we are creating a marker we need to only use the map data/
			if ($action=="create-marker" || $action=="edit-marker" || $action=="delete-marker") {
				$title = $this->title;
				$type = $this->mapType;
				$zoom = $this->zoom;
				$longitude = $this->longitude;
				$latitude = $this->latitude;
				$height = $this->mapHeight;
				$width = $this->mapWidth;
			}
			
			$page_html='
			<form id="treeline" action="/treeline/googlemap/'.($DEBUG?'?debug':"").'" method="post">
				<fieldset>
					<input type="hidden" name="action" value="'.($form_action=="Create"?"create":"edit").'" />
					<input type="hidden" name="guid" value="'.$guid.'" />
					<p class="instructions">This section allows super-users to add new Google maps.</p>
					<p class="instructions">To embed this map into a page please copy and paste the following placemarker into  your content area: @@GOOGLEMAP-'.$guid.'@@</p>
					<div class="field">
						<label for="f_title" class="required">Title:</label>
						<input type="text" name="title" id="f_title" value="'.$title.'" />
					</div>
					<div class="field">
						<label for="f_long" class="required">Longitude:</label>
						<input type="text" name="long" id="f_long" value="'.$longitude.'" />
					</div>
					<div class="field">
						<label for="f_lat" class="required">Latitude:</label>
						<input type="text" name="lat" id="f_lat" value="'.$latitude.'" />
					</div>
					<div class="field">
						<label for="f_type" class="required">Map type:</label>
						<select name="type" id="f_type">
							<option value="SATELLITE"'.($type=="SATELLITE"?' selected="selected"':"").'>Satellite</option>
							<option value="ROADMAP"'.($type=="ROADMAP"?' selected="selected"':"").'>Road map</option>
							<option value="HYBRID"'.($type=="HYBRID"?' selected="selected"':"").'>Hybrid</option>
							<option value="TERRAIN"'.($type=="TERRAIN"?' selected="selected"':"").'>Terrain</option>
						</select>
					</div>
					<div class="field">
						<label for="f_zoom" class="required">Zoom level:</label>
						<select name="zoom" id="f_zomm">
							<option value="0"'.($zoom==0?' selected="selected"':"").'>0 - Really small</option>
							<option value="2"'.($zoom==2?' selected="selected"':"").'>2</option>
							<option value="4"'.($zoom==4?' selected="selected"':"").'>4 - Pretty small</option>
							<option value="6"'.($zoom==6?' selected="selected"':"").'>6</option>
							<option value="8"'.($zoom==8?' selected="selected"':"").'>8 - Scale in here</option>
							<option value="10"'.($zoom==10?' selected="selected"':"").'>10</option>
							<option value="12"'.($zoom==12?' selected="selected"':"").'>12</option>
							<option value="14"'.($zoom==14?' selected="selected"':"").'>14</option>
							<option value="16"'.($zoom==16?' selected="selected"':"").'>16</option>
							<option value="18"'.($zoom==18?' selected="selected"':"").'>18 - Really close up</option>
						</select>
					</div>

					<div class="field">
						<label for="f_height" class="required">Height:</label>
						<input type="text" name="height" id="f_height" value="'.$height.'" />
					</div>
					<div class="field">
						<label for="f_width" class="required">Width:</label>
						<input type="text" name="width" id="f_width" value="'.$width.'" />
					</div>

					<fieldset class="buttons">
						<input type="submit" class="submit" value="'.$form_action.' map" />
					</fieldset>
				</fieldset>
			</form>
			';
		}
		else if ($action=="preview") {
			$page_html = '<p><a href="/treeline/googlemap/?action=edit&guid='.$guid.'">Edit map details or add markers to this map</a></p>';
		}
		return $page_html;	
	}

}




class Marker {

	public $parent;
	public $totalresults;
	
	public $id, $title, $longitude, $latitude, $type, $content;
	
	public $errmsg = array();
	
	public function Marker($guid='', $id=0) {
		if ($guid) {
			$this->parent = $guid;
			if ($id) $this->loadByID($id);
		}
		$this->totalresults = 0;
	}


	public function create() {
		global $db, $site;
		if ($_POST['title']) {
			$query = "INSERT INTO googlemap_marker
				(
					title, parent, date_modified,
					`long`, lat, type,
					content)
				VALUES
				(
					'".$db->escape($_POST['title'])."', '".$this->parent."', NOW(),
					".($_POST['long']+0).", ".($_POST['lat']+0).",
					'".($_POST['type'])."',
					'".($_POST['content'])."'
				)
				";
			if (!$db->query($query)) $this->errmsg[]="Failed to save new map[$query]";
			else {
				$this->id = $db->insert_id;
				return true;
			}
			//print "$query<br>\n";
		}
		else $this->errmsg[]="You must enter a title for your new marker";
	}
	
	public function save($id=0) {
		global $db;
		if ($_POST['title'] && $id>0) {
			$query = "UPDATE googlemap_marker SET
					title='".$db->escape($_POST['title'])."',
					date_modified = NOW(),
					`long`=".($_POST['long']+0).", lat=".($_POST['lat']+0).",
					type='".($_POST['type'])."',
					content='".($_POST['content'])."'
					WHERE id=$id
					";
			if (!$db->query($query)) $this->errmsg[]="Failed to save new map[$query]";
			else {
				$this->id = $db->insert_id;
				return true;
			}
			//print "$query<br>\n";
		}
		else $this->errmsg[]="You must enter a title for your new marker";
	}


	public function delete($marker_id) {
		global $db;
		if ($marker_id>0) {
			$query = "DELETE FROM googlemap_marker WHERE id=".$marker_id;
			$db->query($query);
			if ($db->last_error) $this->errmsg[]="Failed to delete marker";
			else return true;
		}
		return false;
	}
	

	public function loadByID($marker_id) {
		global $db;
		$query = "SELECT * FROM googlemap_marker WHERE id=".$marker_id." LIMIT 1";
		if ($row = $db->get_row($query)) {
			$this->id = $row->id;
			$this->title = $row->title;
			$this->latitude = $row->lat;
			$this->longitude = $row->long;
			$this->type = $row->type;
			$this->content = $row->content;
		}
	}
	
	public function setParent($guid){
		$this->parent = $guid;
	}
	
	private function setTotal($count){
		$this->totalresults = $count;
	}
	public function getTotal(){
		return $this->totalresults;
	}
	public function drawTotal() {
		if($this->getTotal()==1) $msg = 'There is only 1 marker in the site';
		else $msg = 'Showing markers 1 - '. $this->getTotal();
		return $msg;
	}
	
	public function getMarkersList($guid) {
		global $db, $site;

		$query = "SELECT id, title, type, DATE_FORMAT(date_modified, '%D %M %Y') AS modified
			FROM googlemap_marker gmm
			WHERE parent='".$guid."'
			ORDER BY title ASC 
			";
		//print "$query<br>\n";
		$markers = $db->get_results($query);
		if(sizeof($markers)>0) {
			$this->setTotal($db->num_rows);	
			return $markers;
		}
		else return false;
	}

	// 16th Feb 2011 - Phil Redclift
	// page is for pagination
	public function drawMarkersList($guid=''){

    	global $site, $db;
		//print "dPL(page-$page, action-$action, cat-$cat, term-$term, type-$type, format-$format, guid-$guid)<br>\n";
		
		if (!$guid) $guid = $this->parent;
		if (!$guid) return false;
		
		$results = $this->getMarkersList($guid);
		
		if ($results){

			$html = '<table class="tl_list">
<caption>'. $this->drawTotal() .'</caption>
<thead>
<tr>
	<th scope="col">Title</th>
	<th scope="col">Marker type</th>
	<th scope="col">Modified</th>
	<th scope="col">Manage this map</th>
</tr>
</thead>
<tbody>
';
			foreach($results as $result){

				// Have to truncate long titles as mess up my layout.
				$title=(strlen($result->title)>25)?substr($result->title,0,22)."...":$result->title;

				$html .= '<tr>
<td><strong>'.$title .'</strong>
<td>'.ucfirst($result->type).'</td>
<td>'.$result->modified.'</td>
<td class="action">
'.$this->drawMarkersCheckboxes($result->id).'
</td>
';
			}
			$html .= "</tbody>\n</table>\n";
			return $html;
		}
		else {
			return '<p>There are no markers to display</p>';
		}
	}

	public function drawMarkersCheckboxes($id) {
		global $db, $help, $site;

		// Set default options...
		$no_link='<span class="no-action"></span>';
		$deletelink=$editlink=$no_link;
		
		$editlink = '<a '.$help->drawInfoPopup("Edit this marker").' class="edit" href="/treeline/googlemap/?action=edit-marker&mid='.$id.'">Edit</a>';
		// Set up publish link and delete link if we are allowed
		if($_SESSION['treeline_user_group']!="Author"){
			$deletelink = '<a '.$help->drawInfoPopup("Delete this marker").' class="delete" href="/treeline/googlemap/?action=delete-marker&mid='.$id.'">Delete</a>';
		}
		return $editlink.$deletelink;		
	}
	
	public function drawForm($action, $id=0) {
		
		//print "dF($action, $id)<br>\n";
		$form_action = $page_html = '';
		if ($action=="create-marker") $form_action = "Create";
		else if ($action=="edit-marker") $form_action = "Save";

		if ($action=="edit-marker") {
			if (!$id) return false;
			else $this->loadByID($id);
		}
		
		$guid=$this->parent;
		if (!$guid) return $page_html;
		
		if ($form_action) {
			
			$type=$_POST?$_POST['type']:$this->type;
			$page_html='
			<p><a href="/treeline/googlemap/?action=edit&guid='.$guid.'">Edit map data</a></p>
			<form id="treeline" action="/treeline/googlemap/'.($DEBUG?'?debug':"").'" method="post">
				<fieldset>
					<input type="hidden" name="action" value="'.$action.'" />
					<input type="hidden" name="guid" value="'.$guid.'" />
					<input type="hidden" name="mid" value="'.$id.'" />
					<p class="instructions">User this section to add new markers to your map.</p>
					<div class="field">
						<label for="f_title" class="required">Title:</label>
						<input type="text" name="title" id="f_title" value="'.($_POST?$_POST['title']:$this->title).'" />
					</div>
					<div class="field">
						<label for="f_long" class="required">Longitude:</label>
						<input type="text" name="long" id="f_long" value="'.($_POST?$_POST['long']+0:$this->longitude).'" />
					</div>
					<div class="field">
						<label for="f_lat" class="required">Latitude:</label>
						<input type="text" name="lat" id="f_lat" value="'.($_POST?$_POST['lat']+0:$this->latitude).'" />
					</div>
					<div class="field">
						<label for="f_type" class="required">Marker type:</label>
						<select name="type" id="f_type">
							<option value="MARKER"'.($type=="MARKER"?' selected="selected"':"").'>Google marker</option>
							<option value="HOUSE"'.($type=="HOUSE"?' selected="selected"':"").'>House icon</option>
						</select>
					</div>

					<div class="field">
						<label for="f_content" class="required">Content:</label>
						<div class="cke-wrapper">
							<textarea name="content" id="f_content">'.($_POST?$_POST['content']:$this->content).'</textarea>
						</div>
					</div>

					<fieldset class="buttons">
						<input type="submit" class="submit" value="'.$form_action.' marker" />
					</fieldset>
				</fieldset>
			</form>
			';
		}
		return $page_html;	
	}


}

?>