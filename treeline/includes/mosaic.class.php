<?php
class Mosaic {
    
    public $id;
    public $title;
    public $name;
    public $description, $type;
    public $msv;	
		
    public $tid, $ttitle, $tdescription;
    public $image;
		
    public $totalresults;
    public $perpage;
    public $page;
    public $totalpages;
    public $from;
    public $to;	
		
    public function __construct($msv) {
        // This is loaded when the class is created	
        $this->msv = $msv+0;
        // print "Set msv(".$this->msv.")<br>\n";
    }
		
    public function setTitle($title) {
        $this->title = $title;
    }
		
    public function setName($name) {
            $this->name = $name;
    }
    public function getName() {
            return $this->name;
    }

    public function setPerPage($num){
            $this->perpage = $num;
    }
    public function getPerPage(){
            return $this->perpage;
    }

    public function setTotal($count){
        $this->totalresults = $count;
    }
    public function getTotal(){
        return $this->totalresults;
    }

    public function setPage($page){
        $this->page = $page;
    }

    public function getPage(){
        return $this->page;
    }

    public function setTotalPages($total){
            $this->totalpages = ceil($this->getTotal()/$this->getPerPage());
    }
    public function getTotalPages(){
            return $this->totalpages;
    }


    public function setDescription($description){
        $this->description = $description;
    }
		
    public function create() {
        global $db, $user, $site;

        $title = $db->escape($this->title);	
        $description = $db->escape($this->description);			
        $name = $db->escape($this->name);
        
        $query = "INSERT INTO mosaic 
                    (title, description, name, 
                    type, date_added, msv)
                    VALUES 
                    ('$title', '$description', '$name',
                    '".$this->type."', NOW(), ".$site->id.")";
        //print "$query<br>\n";
        if( $db->query($query) ) return true;
        return false;
    }
    public function create_tile() {
        global $db, $user, $site;

        $title = $db->escape($this->ttitle);	
        $description = $db->escape($this->tdescription);			
        $image = $db->escape($this->image);
        
        $query = "INSERT INTO mosaic_tile
                    (mid, title, description, 
                    date_added, image)
                    VALUES 
                    (".$this->id.", '$title', '$description',
                    NOW(), '$image')";
        //print "$query<br>\n";
        if( $db->query($query) ) return true;
        return false;
    }
    
    public function save() {
        global $db, $user;
	
        if ($this->id>0) {
            $title = $db->escape($this->title);	
            $description = $db->escape($this->description);			

            $query = "UPDATE mosaic
                    SET title='$title', 
                    description = '$description',
                    type = '".$this->type."'
                    WHERE id = ".$this->id;
            //print "$query<br>\n";
            return $db->query($query);
        }
        return false;
    }		

    public function save_tile() {
        global $db, $user;
        if ($this->tid>0) {
            $ttitle = $db->escape($this->ttitle);	
            $tdescription = $db->escape($this->tdescription);			
            $image = $db->escape($this->image);

            $query = "UPDATE mosaic_tile
                    SET title='$ttitle', 
                    description = '$tdescription', 
                    image = '$image'
                    WHERE id = '".$this->tid."'";
            //print "$query<br>\n";
            return $db->query($query);
        }
        return false;
    }		
    
    public function delete() {
        global $db;
        $query = "DELETE FROM mosaic WHERE id='{$this->id}'";
        //print "$query<br>\n";
        return $db->query($query);
    }

    public function delete_tile() {
        global $db;
        $query = "DELETE FROM mosaic_tile WHERE id='{$this->tid}'";
        //print "$query<br>\n";
        return $db->query($query);
    }

    public function validImage($image = '') {
        if (!$image) $image = $this->image;
        if (preg_match("/<img(.*)src=\"(.*?)\"(.*)/", $image, $reg)) {
            $image = $_SERVER['DOCUMENT_ROOT'].$reg[2];
            //print "img(".$image.")<br>\n";
            if (file_exists($image)) return $reg[2];
        }
        return false;
    }
    public function generateName() {
        global $db;
        // Strip everything but letters, numbers and spaces from the title
        $name = preg_replace("/[^A-Za-z0-9 ]/", "", $this->title);
        $name = str_replace(" ",'-',$name);

        $query="SELECT * FROM mosaic WHERE name = '$name' AND msv=".$this->msv;
        $db->query($query);
        if ($db->num_rows > 0) return false;
        else {
            $this->name = strtolower($name);
            return true;
        }
    }		
		
    public function loadByID($mid){
        global $db;
        $query = "SELECT * FROM mosaic WHERE id='$mid' LIMIT 1";
        //print "$query<br>\n";
        $row = $db->get_row($query);
        if ($row) {
            //print_r($fileinfo);
            $this->id = $row->id;
            $this->title = $row->title;
            $this->type = $row->type;
            $this->name = $row->name;
            $this->image = $row->image;
            $this->description = $row->description;
        }
        else $this->mid=0;
    }
		
    public function loadTileByID($tid){
        global $db;
        $query = "SELECT * FROM mosaic_tile WHERE id='$tid' LIMIT 1";
        //print "$query<br>\n";
        $row = $db->get_row($query);
        if ($row) {
            $this->tid = $row->id;
            $this->ttitle = $row->title;
            $this->image = $row->image;
            $this->tdescription = $row->description;
        }
        else $this->mid=0;
    }
    
    public function loadTiles() {
        global $db;
        $tiles = array();
        $query = "SELECT mt.* FROM mosaic m
            INNER JOIN mosaic_tile mt on mt.mid=m.id
            WHERE m.msv = ".$this->msv."
            ORDER BY mt.sort_order ";
        //print "$query<br>\n";
        $i = 0;
        $results = $db->get_results($query);
        foreach ($results as $result) {
            $tiles[$i]['tid'] = $result->id;
            $tiles[$i]['mid'] = $result->mid;
            $tiles[$i]['title'] = $result->title;
            $tiles[$i]['image'] = $this->validImage($result->image);
            $tiles[$i]['description'] = $result->description;
            $tiles[$i]['link'] = $this->xlink($result->description);
            $i++;
        }
        return $tiles;
    }
    
    public function xlink($s) {
        $r = '';
        if(preg_match("/ href=\"(.*?)\"(.*)/", $s, $reg)) {
           $r = $reg[1];
        }
        //print "xl($r)<br>\n";
        return $r;
    }

    public function getMosaicList($keywords=''){
            global $db, $site;
            //print "GML($kewords)<br>\n";

            $this->from = $this->getPerPage()*($this->getPage()-1);
            $this->to = $this->getPerPage()*$this->getPage();

            $query = "SELECT 
                    m.id, m.name, m.title, m.description,
                    date_format(m.date_added,'%D %M %Y') datemade,
                    (SELECT count(*) FROM mosaic_tile WHERE mid=m.id) AS tilecount
                    FROM mosaic m 
                    WHERE m.msv=".$site->id." ";
            if ($keywords) $query.="AND m.title LIKE '%$keywords%' ";

            // print "$query<br>\n";
            if ($db->query($query)) { 
                $this->setTotal($db->num_rows);	
                $this->setTotalPages($db->num_rows);	
                $db->flush();
                $query .= "ORDER BY m.date_added DESC, m.title ASC 
                    LIMIT ". $this->from .",". $this->getPerPage();
                //print "$query<br>\n";
                $results = $db->get_results($query);
                if(sizeof($results)>0) return $results;
            }
            return false;
    }

    // draw a list of files with options to manage them
    public function drawMosaicList($p=1, $keywords=''){
        global $help, $page;

        $this->setPerPage(10);
        $this->setPage($p);	

        if($results = $this->getMosaicList($keywords) ){
            foreach($results as $result){
                $html .= '<tr>
    <td><strong>'.$result->title.'</strong></td>
    <td>@@MOSAIC-EMBED-'.$result->id.'@@</td>
    <td>'.$result->tilecount.'</td>
    <td nowrap>'.$page->languageDate($result->datemade).'</td>
    <td nowrap class="action">
            <a class="edit" '.$help->drawInfoPopup("Edit this mosaic").' href="/treeline/mosaic.php?action=edit&amp;mid='.$result->id.'">edit this mosaic</a>
            <a class="reject" '.$help->drawInfoPopup("Manage tiles").' href="/treeline/mosaic.php?action=edit-tiles&amp;mid='.$result->id.'">Manage tiles</a>
            <a class="delete" '.$help->drawInfoPopup("Delete this mosaic").' href="/treeline/mosaic.php?action=delete&amp;mid='.$result->id.'">delete this mosaic</a>
    </td>
</tr>
';
                    }
                    $html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
    <tr>
    <th scope="col">'.ucfirst($page->drawLabel("tl_generic_title", "Title")).'</th>
    <th scope="col">Placeholder</th>
    <th scope="col">Tiles</td>
    <th scope="col">'.$page->drawLabel("tl_img_list_created", "Created on").'</th>
    <th scope="col">Manage this mosaic</th>
    </tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
                    //$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
                    $html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/files/?action=$action&amp;q=".$keywords);
                    return $html;
            }
            else return 'There are no mosaics to display';
    }
	

    public function getTileList($keywords=''){
        global $db, $site;
        //print "GML($kewords)<br>\n";

        $this->from = $this->getPerPage()*($this->getPage()-1);
        $this->to = $this->getPerPage()*$this->getPage();

        $query = "SELECT 
                mt.id, mt.title, date_format(mt.date_added,'%D %M %Y') datemade
                FROM mosaic_tile mt 
                WHERE mt.mid=".$this->id." ";
        if ($keywords) $query.="AND m.title LIKE '%$keywords%' ";

        //print "$query<br>\n";
        if ($db->query($query)) { 
            $this->setTotal($db->num_rows);	
            $this->setTotalPages($db->num_rows);	
            $db->flush();
            $query .= "ORDER BY mt.date_added DESC, mt.title ASC 
                LIMIT ". $this->from .",". $this->getPerPage();
            //print "$query<br>\n";
            $results = $db->get_results($query);
            if(sizeof($results)>0) return $results;
        }
        return false;
    }


    // draw a list of files with options to manage them
    public function drawTileList($p=1, $keywords=''){
        global $help, $page;

        $this->setPerPage(10);
        $this->setPage($p);	
        $i=0;

        if($results = $this->getTileList($keywords) ){
            foreach($results as $result){
                $html .= '<tr>
    <td><strong>'.($result->title?$result->title:"Tile - ".$i).'</strong></td>
    <td nowrap>'.$page->languageDate($result->datemade).'</td>
    <td nowrap class="action">
        <a class="edit" '.$help->drawInfoPopup("Edit this tile").' href="/treeline/mosaic.php?action=edit-tile&amp;mid='.$this->id.'&amp;tid='.$result->id.'">edit this tile</a>
        <a class="delete" '.$help->drawInfoPopup("Delete this tile").' href="/treeline/mosaic.php?action=delete-tile&amp;mid='.$this->id.'&amp;tid='.$result->id.'">delete this tile</a>
    </td>
</tr>
';
                $i++;
            }
            $html = '<table class="tl_list">
<caption>'.$this->drawTotal() .'</caption>
<thead>
    <tr>
    <th scope="col">Title</th>
    <th scope="col">'.$page->drawLabel("tl_img_list_created", "Created on").'</th>
    <th scope="col">Manage this tile</th>
    </tr>
</thead>
<tbody>
'.$html.'
</tbody>
</table>
';
                    //$html .= $this->drawPagination("/treeline/files/?action=$action&amp;category=$cat", $this->getTotal(), 10, $page);
                    $html .= drawNewPagination($this->getTotal(), 10, $p, "/treeline/files/?action=$action&amp;mid='.$this->mid.'&amp;q=".$keywords);
                    return $html;
            }
            else return 'There are no tiles to display';
    }
    
    
    public function drawTotal(){
        global $page;
        $to = ($this->getTotal()<$this->to)? $this->getTotal() : $this->to;
        if($this->getTotal()==1) $msg = 'There is only 1 matching mosaic in the library';
        else $msg = 'Showing mosaics '.($this->from+1).'-'.$to.' '.$page->drawLabel("tl_generic_of", "of").' '. $this->getTotal();
        return $msg;
    }

	}
?>
