<?php
	class Ammap
	{
		
		//No getters or setters use attributes directly
		public $mapId;
		public $mapName;
		public $zoomLevel;
		public $zoomLong;
		public $zoomLat;
		
		
		function __construct($mapId, $mapName, $zoomLevel, $zoomLong, $zoomLat)
		{
			
			//Set attributes
			$this->mapId = $mapId;
			$this->mapName = $mapName;
			$this->zoomLevel = $zoomLevel;
			$this->zoomLong = $zoomLong;
			$this->zoomLat = $zoomLat;
		}
		
		public function loadByGUID($guid) 
		{
			global $db;
			if ($guid) {
				$this->guid = $guid;
				$query = "SELECT * FROM ammap WHERE guid = '$guid' LIMIT 1";
				//print "$query<br>\n";
				if ($row = $db->get_row($query)) {
					$this->mapName = $row->chart_name;
					$this->zoomLevel = $row->zoomlevel;
					$this->zoomLong = $row->zoomlong;
					$this->zoomLat = $row->zoomlat;
					
				}
				else $this->errmsg[]="Failed to log map($guid)";
			}
		}
		
		
		function create()
		{
			global $db, $site;
			//Get current site and generate id
			$MSV = $site->id;
			$guid = uniqid();
			
			$query = "INSERT INTO ammap (id, chart_name, msv, date_added, zoomlevel, zoomlong, zoomlat)
					  VALUES('" .$guid. "', '" .$this->mapName. "', " .$MSV. ", NOW(), " .$this->zoomLevel. ", " .$this->zoomLong. ", " .$this->zoomLat. ")";
					  
			$db->query($query);
		}
		
		function update()
		{
			
		}
		
		public function drawList()
		{
			global $db, $site;
			
			//Query to collect maps
			$query = "SELECT id, chart_name, date_added, date_modified, 
			DATE_FORMAT(date_modified, '%D %M %Y') AS modified
			FROM googlemap gm
			WHERE msv=".$site->id."
			ORDER BY gm.date_modified DESC,chart_name ASC ";
			
			$results = $db->query($query);
			
			//Draw table header
			$html = '<table class="tl_list">
					<thead>
					<tr>
						<th scope="col">Title</th>
						<th scope="col">Status</th>
						<th scope="col">Modified</th>
						<th scope="col">Manage this map</th>
					</tr>
					</thead>
					<tbody>';
			
			foreach ($result as $results)
			{
				$html .= '<tr>
				<td><strong>'.$mapName .'</strong>
				<td>&nbsp;</td>
				<td>'.$result->modified.'</td>
				<td class="action">
				'.$this->drawMapCheckboxes($result->guid).'
				</td>
				</tr>';
			}
			
			//close of table
			$html .= '</tbody></table>';
			
			return $html;
		}
		
		public function drawMapCheckboxes($guid) {
			global $db, $help, $site;
			

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
		
	}
?>