<?php	

class Gallery {
	
	public $total;
	public $perpage;
	public $pageguid;
	public $linktext; 	// Needed for embedded gallery links
	private $slideshow;
	
	private $embed_id='';
	
	
	// If we have a gallery id just load the gallery data into an row
	public function Gallery($pageguid='', $gallery_id=0) {
		global $site, $db;
		//print "G($pageguid, $gallery_id)<br>\n";
		
		$this->pageguid=$pageguid;

		if ($gallery_id>0) {
			$query="select g.id as gallery_id, g.main_image_id, g.title, g.description as text, 
				concat(gi.id,'.',gi.image_extension) as filename, gi.title as image_title, gi.caption, gi.description 
				from galleries g
				left join gallery_images gi on g.id=gi.gallery_id 
				where g.msv=".$site->id."
				AND g.live=1
				AND g.id = $gallery_id
				AND gi.id=g.main_image_id
				limit 1";
			//print "$query<br>\n";
			$this->slideshow = $db->get_row($query);
		}
	
	}
	
	
	public function loadVideo($id) {
		global $db;
		$query="select * from files where guid='$id'";
		//print "$query<br>";
		if ($row=$db->get_row($query)) {
			$this->title=$row->title;
			$this->text=$row->description;
			$this->filename=$row->name.".".$row->extension;
		}
	}
	
	
	public function drawGallery($gallery_type, $show_type='', $start=0, $memberonly=0) {
		global $db, $site;
		$this->perpage=$perpage=12;
		//print "drawGallery($gallery_type, $show_type, $start, $memberonly)<br>\n";
		
		// Use this if we need to skip the first x number of records.
		$startpos=($start-1)*$this->perpage;
		$type = $gallery_type;
		
		// Fetch data for this gallery type
		$query="select count(*) as total 
			from galleries g
			left join gallery_images gi on g.id=gi.gallery_id 
			where g.msv=".$site->id."
			AND type='$gallery_type'
			AND g.live=1
			".($this->pageguid?"AND g.pageguid='".$this->pageguid."'":"")."			
			AND g.memberonly=".($memberonly+0)."
			AND gi.id=g.main_image_id";
			
		$this->total=$db->get_var($query);
		
		$end = (($startpos+$perpage)>$this->total?$this->total:$startpos+$perpage);
		if ($this->total>$perpage) $title1="Showing galleries ".($startpos+1)." to $end of ".$this->total;
		else $title1="Showing galleries ".($startpos+1)." to $end";

		$query="select g.id as gallery_id, g.main_image_id, g.title, g.description as text, 
			concat(gi.id,'.',gi.image_extension) as filename, gi.title as image_title, gi.caption, gi.description 
			from galleries g
			left join gallery_images gi on g.id=gi.gallery_id 
			where g.msv=".$site->id."
			AND type='$gallery_type'
			AND g.live=1
			".($this->pageguid?"AND g.pageguid='".$this->pageguid."'":"")."			
			AND g.memberonly=".($memberonly+0)."
			AND gi.id=g.main_image_id
			order by g.sort_order
			limit ".($startpos).", $perpage
			";
		//print "<!-- $query --> \n";
		$data=$db->get_results($query);
		if ($data && $this->total) {
			$html.=$this->draw($type, $title1, $title2, $data, $start);
			return $html;
		}
		return;
	}
	
	private function draw($type, $title1, $title2, $data='', $start) {

		$html.=$this->drawMain($type, $title1, $data, $start);
		if ($html) $html='<h1 class="pagetitle">'.$title1.'</h1><div class="panel_section '.$type.'">'.$html.'</div>';
		return $html;
	}

	private function drawMain($type, $title, $data='', $start) {
		global $db;
		$html='';
		$i=0;
		foreach ($data as $image) {
			//print "got image($i)
			$i++;
			if ($i==5 || $i==9) {
				$html.='</div><div id="panel_holder_'.$type.'_l'.(($i-1)/4).'" class="panel_holder_big '.$type.'">';
			}
			$html.=$this->drawSlideshow($image, $type, $i);
		}
		if (!$html) $html='<p>No '.(($type=="videos")?$type:'galleries').' loaded</p>';	
		else {
			$html='<div id="panel_holder_'.$type.'" class="panel_holder_big '.$type.'">'.$html.'</div>';
			$html.=$this->drawPagination("?type=".(($type=="reports")?"report":"image"), $this->total, $this->perpage, $start);
		}
		return $html;
	}

	// Function drawSlideshow
	// This function should be able to return slideshows for the gallery
	// Or just the basic html for an embedded slideshow with no gunk attached.
	public function drawSlideshow($image=array(), $type="embed", $index=1) {
		//print "dSs(data, $type, $index)<br>\n";
		
		if (!count($image)) $image = $this->slideshow;
		
		if ($type=="embed") {
			srand();
			$new_rand = mt_rand(1,100);
			//print "got rand($new_rand, ".time()." i($index))<br>\n";
			$new_rand = rand(1,100);
			//print "got rand($new_rand, ".time()." i($index))<br>\n";
			$this->embed_id = $new_rand;
			//print "Set embed ID(".$this->embed_id.")<br>\n";
		}
		
		$src_file='/silo/images/galleries/'.$image->gallery_id.'/t_'.$image->filename;
		$gallery_file='/silo/images/galleries/'.$image->gallery_id.'/b_'.$image->filename;
		if ($image->caption) $lytetext=$image->caption."<br />";
		$lytetext=($image->caption?$image->caption."<br />":"").htmlspecialchars($image->description);
		$lyteshow='lyteshow[gallery-'.$image->gallery_id.$this->embed_id.']';
		$lytelink='href="'.$gallery_file.'" rel="'.$lyteshow.'" title="'.$lytetext.'"';
		
		if ($type=="embed" && $this->linktext) $html.=$this->drawEmbedDiv($type, $index, $lytelink, $image->title, $src_file, $image->text, $start);
		else $html.=$this->drawMainDiv($type, $index, $lytelink, $image->title, $src_file, $image->text);
		$html.=$this->drawLyteGallery($type, $image->gallery_id, $image->main_image_id);
		//print "dS($html)<br>\n";
		return $html;
	}
	
	private function draw3main($type, $title, $data='') {
		global $db;
		$html=''; $i=0;
		
		foreach ($data as $image) {
			if ($i++<3) {
				
				if ($type=="videos") {
					$src_file="/silo/images/".$image->filename;
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$src_filename)) {
						$src_file="/img/layout/media_dummy1.gif";
					}
					$lytelink='href="'.$siteLink.'videos/?vid='.$image->id.'"';
				}
				else {
					$src_file='/silo/images/galleries/'.$image->gallery_id.'/t_'.$image->filename;
					$gallery_file='/silo/images/galleries/'.$image->gallery_id.'/b_'.$image->filename;
					$lytetext=($image->caption?$image->caption."<br />":"").htmlspecialchars($image->description);
					$lyteshow='lyteshow[gallery-'.$image->gallery_id.$this->embed_id.']';
					$lytelink='href="'.$gallery_file.'" rel="'.$lyteshow.'" title="'.$lytetext.'"';
				}
				
				$html.=$this->drawMainDiv($type, $i, $lytelink, $image->title, $src_file, $image->text);
				$html.=$this->drawLyteGallery($type, $image->gallery_id, $image->main_image_id);
			}
		}

		//if (!$html) $html='<p>No '.(($type=="videos")?$type:'galleries').' loaded</p>';	
		if ($html) $html='<div id="panel_holder_'.$type.'" class="panel_holder '.$type.'"><h1 class="main">'.$title.'</h1>'.$html.'</div>';
		return $html;
	}

	private function drawMainDiv($type, $i, $link, $title, $image, $text) {
	//print "main div sent text($link)<br>";
		if ($type=="image") $style="landing-panel";
		
		$html='';
		$html.='<div class="panel'.(($i-1)%4).' '.$style.'">';
		//$html.='<h3><a '.$link.'>'.$title.'</a></h3>';
		$html.='<p><a '.$link.'><img src="'.$image.'" alt="'.$type.' pic#'.$i.'" /></a></p>';
		if ($type=="image") $html.='<p>'.$title.'</p>';
		//$html.='<p>'.$text.'</p>';
		$html.='</div>';
		return $html;
	}
	
	private function drawEmbedDiv($type, $i, $link, $title, $image, $text, $start) {
		//print "dED($type, $i, $link, $title, $image, text, $start)<br>\n";
		if ($type=="image") $style="landing-panel";
		
		$html='';
		//$html.='<h3><a '.$link.'>'.$title.'</a></h3>';
		//$html.='<a '.$link.'><img src="'.$image.'" alt="'.$type.' pic#'.$i.'" /></a>';
		$html.='<a '.$link.'>'.$this->linktext.'</a>';
		//print "dMD($html)<br>\n";
		return $html;
	}
	
	private function drawLyteGallery($type, $gallery_id, $main_image_id) {
		global $db;
		$html='';
		//print "dLG($type, $gallery_id, $main_image_id)<br>";
		if ($type=="videos") return ;
		$query="SELECT caption, description, concat(id,'.',image_extension) AS filename 
			FROM gallery_images 
			WHERE gallery_id=".$gallery_id." AND id!=".$main_image_id."
			ORDER BY sort_order";
		//print "$query<br>";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				$gallery_file='/silo/images/galleries/'.$gallery_id.'/b_'.$result->filename;
				$lyteshow='lyteshow[gallery-'.$gallery_id.$this->embed_id.']';
				$lytetext='';
				if ($result->caption) $lytetext=$result->caption."<br />";
				$lytetext.=htmlspecialchars($result->description);
				$html.='<a class="hide" href="'.$gallery_file.'" rel="'.$lyteshow.'" title="'.$lytetext.'"></a>';
			}
		}
		return $html;
	}
		
	private function draw4other($type, $title, $data='', $start, $force_display) {
		global $db;
		$html='';
		$i=0;
		//print "draw4other($type, $title, $start)<br>";
		foreach ($data as $image) {
			$i++;
			if ($i>7) {
				$html.='<a href="?type='.(($type=="reports")?"report":"image").'&start='.($start+1).'">View more '.$type.'</a>';
			}
			else if ($i>3) {
				if ($type=="videos") {
					$src_file='/silo/images/m_'.$image->filename;
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$src_file)) {
						$src_file="/silo/images/".$image->filename;
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].$src_file)) {
							$src_file="/img/layout/media_dummy2.gif";
						}
						else {
							$src_file=$this->generateThumb($src_file, '/silo/images/m_'.$image->filename);
						}
					}
					$lytelink='href="'.$siteLink.'videos/?vid='.$image->id.'"';
					$html.='<p><a class="other-link" style="background-image:url(\''.$src_file.'\');" '.$lytelink.' >'.$image->title.'</a></p>';
				}
				else {
					$src_file='/silo/images/galleries/'.$image->gallery_id.'/m_'.$image->filename;
					$gallery_file='/silo/images/galleries/'.$image->gallery_id.'/b_'.$image->filename;
					$lytetext=$image->caption."<br />".htmlspecialchars($image->description);
					$lyteshow='lyteshow[gallery-'.$image->gallery_id.$this->embed_id.']';
					$html.='<p><a class="other-link" rel="'.$lyteshow.'" style="background-image:url(\''.$src_file.'\');" title="'.$lytetext.'" href="'.$gallery_file.'">'.$image->title.'</a></p>';
					$html.=$this->drawLyteGallery($type, $image->gallery_id, $image->main_image_id);
				}
			}
		}
			
		if ($html || $force_display) {
		$html='<div id="panel_right_'.$type.'" class="panel_right '.$type.'">
	<h1>'.$title.'</h1>
	<div class="panel">
	'.$html.'
	</div>
</div>
';
		}
		return $html;
	}	
	
	
	// Show all related links
	public function drawRelated($vid, $type) {

		global $db, $siteID, $page;
		
		$html='';
		$site_field="site_id";
		
		switch ($type) {
			case "video" : 
				$table="files"; $field="guid"; $tag_type="3"; 
				break;
			case "galleries" : 
				$table="galleries"; $field="id"; $tag_type="4"; $site_field="msv"; 
				break;
			case "links" : 
				$table="pages"; $field="guid"; $tag_type="1"; 
				break;
		}
		
		$query="select tt.*, tr.* from $table tt 
			left join tag_relationships tr on tr.guid=tt.$field
			where tr.type_id=$tag_type 
			and tt.$site_field=".($siteID+0)."
			".(($table=="files")?" AND tt.guid!='$vid' AND tt.category=83 AND tt.subcategory=158":"")."
			and tr.tag_id in 
			(select t.id from files f
			left join tag_relationships tr1 on f.guid=tr1.guid
			left join tags t on t.id=tr1.tag_id
			where f.guid='$vid')
			group by tt.$field
			order by tt.date_created
			limit 4
			";
		print "<!-- $query --> \n";
		if ($results=$db->get_results($query)) {
			foreach($results as $result) {
				if ($type=="galleries") {	
					$query="select gi.id, gi.title as caption, gi.description as text, 
						concat(gi.id, '.', image_extension) as filename from galleries g
						left join gallery_images gi on g.main_image_id=gi.id
						where g.id=".$result->id;
					//print "$query<br>";
					if ($row=$db->get_row($query)) {
						$bgimage=$row->filename;
						$filename="/silo/images/galleries/".$result->id."/m_".$bgimage;
						//print "check if $filename exists<br>";
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].$filename)) {
							$filename="/img/layout/media_dummy2.gif";
						}
						$style="background-image:url('$filename');padding-left:80px;";
					
						$src_file=$filename;
						$gallery_file='/silo/images/galleries/'.$result->id.'/b_'.$bgimage;
						$lytetext=$row->caption."<br />".$row->text;
						$lyteshow='lyteshow[gallery-'.$result->id.$this->embed_id.']';
						$html.='<a class="grey-arrow" rel="'.$lyteshow.'" style="'.$style.'" title="'.$lytetext.'" href="'.$gallery_file.'">'.$result->title.'</a>';
						$html.=$this->drawLyteGallery($type, $result->id, $row->id);				
					}
				}
				else if ($type=="video") {
					$query="select concat('/silo/images/', filename) from images i
						left join images_sizes isz on i.guid=isz.guid
						where i.guid='".$result->img_guid."'
						order by isz.height asc
						limit 1";
					$filename=$db->get_var($query);
					//print "got($filename) from ($query)<br>";
					if (!file_exists($_SERVER['DOCUMENT_ROOT'].$filename)) {
						$filename="/img/layout/media_dummy2.gif";
					}
					$style="background-image:url('$filename');padding-left:80px;";
					$html.='<a class="grey-arrow" style="'.$style.'" href="?vid='.$result->guid.'">'.$result->title.'</a>';
				}
				else $html.='<a class="grey-arrow" style="'.$style.'" href="'.$page->drawLinkByGUID($result->guid).'">'.$result->title.'</a>';
			}
		}
		return $html;
	}
	
	
	// Bit of a fix..... and probably could just look for a file with a set name and the correct dimensions.
	// However its here now and would be a bigger job (and slightly less reliable) to change it now.
	private function generateThumb($src, $dest) {
		
		$src_file=$_SERVER['DOCUMENT_ROOT'].$src;
		$dest_file=$_SERVER['DOCUMENT_ROOT'].$dest;	
		if (file_exists($dest_file)) return $dest;
		if (!file_exists($src_file)) return false;
		
		//print "need to create $dest_file from $src_file<br>";
		$extension=strtolower(substr($src, -3, 3));
		
		// Get image size
		$sz=getimagesize($src_file);
		if (!$sz[0] || !$sz[1]) return $src;

		if ($extension == 'gif') {
			$src_image=imagecreatefromgif($src_file); 
			$new_image = imagecreate(60, 40);
		} else if ($extension == 'png') {
			$src_image = imagecreatefrompng($src_file);
			$new_image = imagecreate(60, 40);
		} else if ($extension == 'jpg') {
			$src_image = imagecreatefromjpeg($src_file);
			$new_image = imagecreatetruecolor(60, 40);
		} else return $src;

		// copy the uploaded file into the image object, resizing at the same time
		imagecopyresampled($new_image, $src_image, 0, 0, 0, 0, 60, 40, $sz[0], $sz[1]);
		if ($extension == 'gif')  imagegif($new_image,$dest_file);
		else if ($extension == 'jpg') imagejpeg($new_image,$dest_file,100);
		else if ($extension == 'png') imagepng($new_image,$dest_file);
		else return $src;
		
		return $dest;
	}
	
	public function drawPagination($currentURL,$totalResults,$perPage=10,$currentPage=1) {
		// $page indicates which page we're on
		//global $search;
       	//print "dp($currentURL, $totalResults, $perPage, $currentPage)<br />";

		$totalpages = ceil($totalResults / $perPage);
		if(!$currentPage || $currentPage==0){
			$currentPage = 1;
		}

		for( $i=0; $i<strlen($currentURL); $i++ ){
			$tmp[] = $currentURL[$i];
		}
	
		if( (!in_array('?',$tmp) && !in_array('&',$tmp)) ){
			$currentURL .= '?';
		}else{
			$currentURL .= '&amp;';
		}
		
		$html = '<ul class="pagination">'."\n";
		
		if ($totalpages == 1) {
			return $html;
		}
		if($currentPage > 2){
			$html .= '<li class="bookend"><a href="'.$currentURL.'">First</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="bookend inactive">First</li>'."\n";
		}
		if ($currentPage > 1) {
			$html .= '<li><a href="'. $currentURL.'p='. ($currentPage-1).'">Previous</a></li>'."\n"; //'<a href="search_results.php?q='.$search['keywords'].'&d='.$search['description'].'&p='.($search['page']-1).'&o='.$search['order'].'">Previous</a> ';
		}
		else{
			$html .= '<li class="inactive">Previous</li>'."\n";
		}
		
		if($totalpages<=10){
			$pagestart=1;
			$pageend = $totalpages;
		}
		else if($currentPage<($totalpages-5)){
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		else if( ($currentPage>($totalpages-5)) && ($currentPage<=$totalpages) ){
			$pagestart = $currentPage-(9-($totalpages-$currentPage));
			$pageend = ($currentPage+($totalpages-$currentPage))+1;
		}
		else{
			$pagestart = ($currentPage>4) ? $currentPage-4 : 1;
			$pageend = $pagestart+9;
		}
		
		// for debugging...
		//echo $pagestart.' > '.$pageend.' - ['.$page.'] of ['. $totalpages .']<br />';
		for ($i=$pagestart; $i<=$pageend; $i++) {
			//// We don't want to show all pages, just a few either side of the page we're on.
			//// If we keep the page we're on centrally (position 5) then when we get to position 6
			//// we'll need to cycle the whole lot down...
			//$class = ($i==$pageend) ? ' class="bookend"' : '';
			if ($i != $currentPage) {
				$html .= '<li'. $class .'><a href="'. $currentURL.'p='. $i.'">'.$i.'</a></li>'."\n"; 
			} else {
				$html .= '<li'. $class .'><strong>'.$i.'</strong></li>'."\n";
			}

		}
		if ($currentPage < $totalpages) {
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. ($currentPage+1).'">Next</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Next</li>';
		}	
		if($currentPage < ($totalpages-1)){
			$html .= '<li class="bookend"><a href="'. $currentURL.'p='. $totalpages.'">Last</a></li>'."\n";
		}else{
			$html .= '<li class="bookend inactive">Last</li>'."\n";
		}
		
		$html .= '</ul>'."\n";
			
		return $html;
	}

		
}

?>