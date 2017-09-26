<?
/************************************************************
Store object for Treeline

Author: Dan Donald
Started: 26-03-08
Description:
	This class effectively gets the core data for the store
	such as product listings and category tree.
	The admin side will use the CRUD methods to maintain the 
	database.

************************************************************/

class Store {

	public $config;
	public $breadcrumb;
	public $total;
	public $categories = array();
	public $totalProducts; 
	
	public $imgW = 589;
	public $imgH = 317;
	
	public $dbg;
	public $msg = array();
	public $errmsg = array();

	public function __construct($catID=false){
		if( isset($catID) ){
			$this->catID = $catID;
		}
		$this->getConfig();
	}

//// Get/set methods

	// this can be used to get an attribute, unless a specialised method exists.
	// methods need to be in the format getThisMethodName.
	private function __get($attribute){	
		$method = str_replace(' ','','get'.ucwords( str_replace('_',' ',$attribute) ) );
		
		if( isset($this->$attribute)  ){
			return $this->$attribute;
		} else if( method_exists($this,$method) ){
			return call_user_method($method,$this);
		} else {
			return false;
		}
	}

	private function __set($attribute,$value){
		if( isset($this->$attribute) ){
			$this->$attribute = $value;
			return true;
		}else{
			return false;
		}
	}
	
	
	public function getConfig($editable=false){
		global $db;
		
		$query = "SELECT title, value FROM store_config";
		if ($editable) $query.=" WHERE editable=1";
		if( $data = $db->get_results($query) ){
			$tmp = array();
			foreach( $data as $item ){
				$tmp[$item->title] = $item->value;
			}
			$this->config = $tmp;
			return $tmp;
		}else{
			return false;
		}
	}
	
	
	public function loadByCat( $catName=false, $from=0, $quantity=9 ){
		global $db, $site;
		//print "lBC($catName, $from, $quantity)<br>\n";

		$query = "SELECT sp.product_id, sp.title, sp.name, sp.author,
			sp.short_desc, sp.`priority`, sc.cat_id, sp.physical, sp.page_guid,
			sp.image,
			IF(sc2.title>'',CONCAT(sc2.title,' &raquo; ',sc.title), sc.title) categories,
			(
				SELECT si.price 
				FROM store_inventory si
				WHERE si.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) AS price,
			(
				SELECT CONCAT(MIN(si.price),',',MAX(si.price)) FROM store_inventory si
				WHERE si.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) AS price_range,
			(
				SELECT MAX(si.stock_level) FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id LIMIT 1
			) AS stock_level,
			(
				SELECT si3.item_id FROM store_inventory si3
				LEFT JOIN store_products_variants spv3 ON spv3.item_id=si3.item_id
				WHERE spv3.product_id=sp.product_id LIMIT 1
			) AS item_id,
			(
				SELECT IF(spi.caption>'',
					GROUP_CONCAT(CONCAT(spi.image_id,'::',spi.img_extension,'::',spi.caption) ORDER BY spi.sort_order SEPARATOR ','),
					GROUP_CONCAT(CONCAT(spi.image_id,'::',spi.img_extension) ORDER BY spi.sort_order SEPARATOR ',')
					)
				FROM store_products_images spi WHERE spi.product_id=sp.product_id
			) AS images
			FROM store_products sp
			LEFT JOIN store_categories_products scp ON sp.product_id=scp.product_id
			LEFT JOIN store_categories sc ON scp.cat_id=sc.cat_id
			LEFT JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
			WHERE sp.`status`=1
			AND sp.msv=".($site->id+0)." ";
		if( $catName ){
			$query .= " AND sc.name='". $catName ."'";
		}
		$query .= " GROUP BY sp.product_id ORDER BY sp.priority ASC, sp.title ASC";
		$db->query($query);
		$this->total = $db->num_rows;
		$query .= " LIMIT $from, $quantity";
		//print "Load(".$this->total.") products: $query<br>\n";
		if( $data = $db->get_results($query) ){
			$this->getCategoryBreadcrumb($catName);
			return $data;
		}
		return false;
	}	


	public function loadByName( $name=false, $useStatus=true ){
		global $db;
		
		if ($name=="new") {
			return false;
		}
		
		$query = "SELECT sp.product_id, sp.title, sp.name, sp.author, 
			sp.short_desc, sp.long_desc, sp.`priority`,  sp.image,
			sp.physical, sp.featured, sp.page_guid, sp.about_tab1, 
			sp.about_tab2, sp.`status`, sc.name category, 
			IF(sc2.title>'',CONCAT(sc2.title,' &raquo; ',sc.title), sc.title) categories,
			(
				SELECT si.price 
				FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) price,
			(
				SELECT CONCAT(MIN(si.price),',',MAX(si.price)) FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) price_range,
			(
				SELECT MAX(si.stock_level) FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id LIMIT 1
			) stock_level,
			(
				SELECT si3.item_id FROM store_inventory si3
				LEFT JOIN store_products_variants spv3 ON spv3.item_id=si3.item_id
				WHERE spv3.product_id=sp.product_id LIMIT 1
			) item_id,
			(
				SELECT 
					GROUP_CONCAT(CONCAT(spi.image_id,'::',spi.img_extension,'::',spi.caption) ORDER BY spi.sort_order SEPARATOR ',')
					FROM store_products_images spi 
					WHERE spi.product_id=sp.product_id
			) images
			
			FROM store_products sp
			LEFT JOIN store_categories_products scp ON sp.product_id=scp.product_id
			LEFT JOIN store_categories sc ON scp.cat_id=sc.cat_id
			LEFT JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
			WHERE ";
		$query .= ($useSelected) ? "sp.`status`=1 AND" : "sp.`status`>-1 AND";
		$query .= " sp.name='". $name ."' GROUP BY sp.product_id LIMIT 1";
		//p/rint "lp($query) <Br>\n";
		//print "<!-- $query --> \n";
		if( $data = $db->get_results($query) ){
			$data[0]->breadcrumb = $this->getProductBreadcrumb($data[0]->product_id);
			return $data[0];
		}
		return false;
	}	




	public function search($search_term=false, $search_category=false, $filter=false, $filtervalue=false, $from=0, $perPage=20){
		global $db;
		//print "s($search_term, $search_category, $filter)<br>\n";
		
		/*
		// Check if we need to add categories to search
		foreach($_GET as $k=>$v) {
			//print "got $k => $v<br>\n";
			if (substr($k,0,8)=="product_") {
				if (is_array($v)) {
					foreach ($v as $cat_id) {
						++$cat_index;
						if ($cat_index < 10) {
							$catFrom .= 'LEFT JOIN store_products_categories spc'.$cat_index.' ON sp.product_id = spc'.$cat_index.'.product_id '."\n";
							$catWhere .= 'AND spc'.$cat_index.'.category_id='.$cat_id.' '."\n";
						}
						// Only join 10 more tables at selecting too many cats just slows the search down.
					}
				}
			}
		}
		//print "cat(<br>$catFrom<br><br>$catWhere<br>term($search_term)<Br>\n";
		*/		
		
		if((isset($search_term) && $search_term>'') || ($catFrom && $catWhere)){
				
			$query = "select 
`sp`.`title` AS `title`,sp.short_desc, sp.long_desc, 
`sp`.`product_id` AS `product_id`,
(
	SELECT GROUP_CONCAT(DISTINCT si.price ORDER BY si.price SEPARATOR ', ')
	FROM store_inventory si
	LEFT JOIN store_products_variants spv2 ON si.item_id=spv2.item_id
	WHERE spv2.product_id=sp.product_id
) AS price
FROM `store_products` `sp`
LEFT JOIN store_products_variants spv ON sp.product_id=spv.product_id
LEFT JOIN `store_variants_types` `svt` on `spv`.`variant_id` = `svt`.`variant_id`
LEFT JOIN `store_types` `st` ON `svt`.`type_id` = `st`.`type_id`
LEFT JOIN store_categories_products scp ON sp.product_id=scp.product_id
LEFT JOIN store_categories sc ON scp.cat_id=sc.cat_id
".$catFrom."
WHERE 
	sp.`status`=1 AND 
	(
	sp.title LIKE '%$search_term%' OR 
	sp.author LIKE '%$search_term%' OR 
	sp.short_desc LIKE '%$search_term%' OR
	sp.long_desc LIKE '%$search_term%'
	) 
".$catWhere." ";
			if( $search_category>'' ){
				$query .= "AND sc.name='$search_category' ";
			}
			
			
			$query .= "GROUP BY sp.product_id ";
			$db->query($query);
			$this->total = $db->num_rows;
			
			//print "Total Q($query) Got ".$this->total." results<br>\n";
			
			if( $filter && $filtervalue ){
				$query .= " ORDER BY $filter $filtervalue ";
			}			
			$query .= " LIMIT $from,$perPage ";
			//echo nl2br($query).'<br />';
	
			if( $data = $db->get_results($query) ){
				return $data;
			}
		}
		return false;
	}	




	public function getProductBreadcrumb( $productID=false ){
		global $db;
		if( isset($productID) && is_numeric($productID) ){
			$query = "SELECT sc.title, sc.name, sc2.title parent_title, sc2.name parent_name FROM store_products sp
						LEFT JOIN store_categories_products scp ON sp.product_id=scp.product_id
						LEFT JOIN store_categories sc ON scp.cat_id=sc.cat_id
						LEFT OUTER JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
						WHERE sp.product_id=$productID
						GROUP BY sp.product_id";
			//echo $query .'<br />';
			if( $data = $db->get_row($query) ){
				//echo '<pre>'. print_r($product,true) .'</pre>';
				return $data;
			}else{
				return false;
			}		
		}else{
			return false;
		}
	}

	public function getCategoryBreadcrumb( $catName=false ){
		global $db;
		if( isset($catName) && is_string($catName) ){
			$query = "SELECT sc.title, sc.name, sc2.title parent_title, sc2.name parent_name FROM store_categories sc
						LEFT OUTER JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
						WHERE sc.name='$catName' GROUP BY sc.cat_id";
			if( $data = $db->get_row($query) ){
				$this->breadcrumb = $data;
				return $data;
			}else{
				return false;
			}		
		}else{
			return false;
		}
	}
	
	
	
	public function getProductItems( $productID=false ){
		global $db;
		
		//print "ID($productID)<br>\n";
		if( $productID ){
			$query = "SELECT si.*
				FROM store_inventory si
				WHERE si.product_id=$productID
				";
			//print "gPI($query)<br>\n";
			if( $data = $db->get_results($query) ){
				return $data;
			}
		}
		return false;
	}
	

	
	public function getProductVariants( $productID=false ){
		global $db;
		
		if( $productID ){
			$query = "SELECT DISTINCT st.title, count(st.title) total, GROUP_CONCAT(sv.title) variants FROM store_products_variants spv
						LEFT JOIN store_variants_types stv ON spv.variant_id=stv.variant_id
						LEFT JOIN store_variants sv ON spv.variant_id=sv.variant_id
						LEFT JOIN store_types st ON stv.type_id=st.type_id
						WHERE product_id=$productID
						GROUP BY st.title";
			if( $data = $db->get_results($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	
	public function getItemVariants($productID=false,$itemID=false){
		global $db;
		
		if( $productID && $itemID ){
			$query = "SELECT GROUP_CONCAT(variant_id SEPARATOR ',') FROM store_products_variants WHERE product_id=$productID AND item_id=$itemID";
			if( $data = $db->get_var($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	
	public function getProductVariantsSelect( $productID=false ){
		global $db;
		if( $productID ){
			$query = "SELECT si.item_id, si.price, si.tagline, si.stock_level, 
				si.tagline AS title 
				FROM store_inventory si
				WHERE si.product_id=$productID
				ORDER BY si.tagline
				";
			if($data = $db->get_results($query)) return $data;
		}
		return false;
	}

	
	public function showProductVariants( $productID=false ){
		if( $data = $this->getProductVariants($productID) ){
			$tmp = array();
			foreach( $data as $item ){
				if( $item->total>1 ){
					$tmp[] = strtolower($item->title).'s';
				}
			}
			return $tmp;
		}else{
			return false;
		}
	}
	
	
	public function getVariantList( $type=false ){
		global $db;
		
		if( $type>0 ){
			$query = "SELECT sv.* FROM store_variants sv
						LEFT JOIN store_variants_types svt ON sv.variant_id=svt.variant_id
						WHERE svt.type_id=$type ORDER BY sv.sort_order ASC, sv.variant_id ASC";
			if( $data = $db->get_results($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	

	public function getVariantTypes($productID=false,$selectedOnly=false){
		global $db, $site;
		$selected = array();
		if( $productID ){
			$query = "SELECT GROUP_CONCAT(type_id SEPARATOR ',') 
				FROM store_products_variants_types 
				WHERE product_id=$productID
				";
			//print "Get selected($query)<br>\n";
			if( $tmp = $db->get_var($query) ){
				$selected = explode(',',$tmp);
			}
		}
		$query = "SELECT * FROM store_types WHERE msv=".($site->id+0);
		//print "$query<br>\n";
		if( $selectedOnly && count($selected)){
			$tmp = implode(',',$selected);
			$query .= " AND type_id IN ($tmp)";
		}
		$query .= " ORDER BY title";
		//print "Get VT($query)<br>\n";
		if( $data = $db->get_results($query) ){
			if( $selected ){
				return array($data,$selected);
			}else{
				return array($data,array());
			}
		}else{
			return false;
		}
		
	}
	public function _getVariantTypes($productID=false,$selectedOnly=false){
		global $db;
		if( $productID ){
			$query = "SELECT GROUP_CONCAT(type_id SEPARATOR ',') FROM store_products_variants_types WHERE product_id=$productID";
			if( $selected = $db->get_var($query) ){
				$selected = explode(',',$selected);
			}
		}
		$query = "SELECT * FROM store_types";
		if( $selectedOnly ){
			$tmp = implode(',',$selected);
			$query .= " WHERE type_id IN ($tmp)";
		}
		$query .= " ORDER BY title";
		if( $data = $db->get_results($query) ){
			if( $selected ){
				return array($data,$selected);
			}else{
				return $data;
			}
		}else{
			return false;
		}
		
	}
	
	
	public function addVariant($parent=false, $title=false){
		global $db;
		if( $parent && $title ){
			if( $name = $this->generateName(false,$title,'variants') ){
				$query = "INSERT INTO store_variants (title,name) VALUES ('". $db->escape($title) ."','$name')";
				if( $db->query($query) ){
					$varID = $db->insert_id;
					$query = "INSERT INTO store_variants_types (type_id,variant_id) VALUES ($parent,$varID)";
					$db->query($query);
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function addVariantType($title=false){
		global $db;
		if( $title ){
			if( $name = $this->generateName(false,$title,'types') ){
				$query = "INSERT INTO store_types (title,name) VALUES ('". $db->escape($title) ."','$name')";
				if( $db->query($query) ){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	
	
	public function checkStockLevel( $itemID=false ){
		global $db;
		
		if( isset($itemID) && $itemID ){
			$query = "SELECT stock_level FROM store_inventory WHERE item_id=$itemID";
			if( $level = $db->get_var($query) ){
				$level -= $this->config['sold_out'];
				return $level;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function checkEventCapacity($entry_id=0){
		global $db;
		
		//print "check cap for event($entry_id)<br>\n";
		$query = "SELECT capacity,
			(
				SELECT count(*) 
				FROM event_entry ee1 
				WHERE ee1.event_guid=ee.event_guid 
				AND ee1.registered>0
			) AS booked
			FROM events e
			LEFT JOIN event_entry ee ON ee.event_guid=e.guid
			WHERE ee.id=".$entry_id;
		//print "$query<br>\n";
		if ($row = $db->get_row($query)) {
			return $row->capacity - $row->booked;
		}
		return false;
		
	}
	
	
	public function getSimilarProducts( $productID=false ){
		global $db;

		if( isset($productID ) && $productID ){
			$query = "SELECT sp.title,sp.name,sp.product_id,
						(SELECT si.price FROM store_inventory si
						LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
						WHERE spv.product_id=spr.child_product_id
						ORDER BY si.price ASC LIMIT 1 ) price,
						(SELECT GROUP_CONCAT(spi.image_id ORDER BY spi.sort_order SEPARATOR ',')
						FROM store_products_images spi WHERE spi.product_id=sp.product_id) images
						FROM store_products sp
						LEFT JOIN store_products_related spr ON sp.product_id=spr.child_product_id
						WHERE spr.product_id=$productID";
			if( $data = $db->get_results($query) ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	
	public function getShippingZones(){
		global $db;
		
		$query = "SELECT * FROM store_shipping_zones ORDER BY sort_order";
		if( $data = $db->get_results($query) ){
			return $data;
		}else{
			return false;
		}
	}



	public function getCategories($parent=0){
		global $db, $site;
		$tmp = array();
		$query = "SELECT * FROM store_categories WHERE parent_id=$parent AND msv=".($site->id+0)." ";
		//print "$query<br>\n";
		if( $data = $db->get_results($query) ){
			foreach($data as $item){
				//$html .= '<option value="'. $item->name .'"'. ($item->name==$selected ? ' selected="selected"' :'') .'>'. $item->title .'</option>'."\n";
				$data = array('id'=>$item->cat_id,'name'=>$item->name,'title'=>$item->title);
				$query = "SELECT * FROM store_categories WHERE parent_id={$item->cat_id} AND msv=".($site->id)." ";
				//print "q($query)<br>\n";
				if( $children = $db->get_results($query) ){
					foreach($children as $child){
						//print "Add child(".print_r($child, true).")<br>\n";
						$data['children'][] = array('id'=>$child->cat_id,'name'=>$child->name,'title'=>$child->title);
						//$html .= '<option value="'. $child->name .'" class="childCat"'. ($child->name==$selected ? ' selected="selected"' :'') .'>'. $child->title .'</option>'."\n";
					}
				}
				$tmp[] = $data;
			}
			//print_r($tmp);
			return $tmp;
		}
		return false;
	}



	
	public function getSelectCategories($parent=0, $selected=false, $showChildCategories=true, $showDefault=true, $defaultTxt=false){
		global $db, $site;
		$html = '';
		$query = "SELECT * FROM store_categories WHERE parent_id=$parent AND msv=".($site->id+0)." ";
		//print "$query<br>\n";
		if( $data = $db->get_results($query) ){
			$html .= ($showDefault) ? '<option value="">'. ($defaultTxt>'' ? $defaultTxt : '-- select category --').'</option>'."\n" : '';
			foreach($data as $item){
				$html .= '<option value="'. $item->name .'"'. ($item->name==$selected ? ' selected="selected"' :'') .'>'. $item->title .'</option>'."\n";
				if( $showChildCategories ){
					$query = "SELECT * FROM store_categories WHERE parent_id={$item->cat_id} AND msv = ".($site->id+0)." ";
					if( $children = $db->get_results($query) ){
						foreach($children as $child){
							$html .= '<option value="'. $child->name .'" class="childCat"'. ($child->name==$selected ? ' selected="selected"' :'') .'>&nbsp;&nbsp;&nbsp;&nbsp;'. $child->title .'</option>'."\n";
						}
					}
				}
			}
			return $html;
		}else{
			return false;
		}
	}

	// Just to be consistent :o)
	// This func draws all ephemeral categories as multiselect drop downs
	// While the one above does real categories.
 	public function drawCategorySelects($cats = array("key-stage", "subject", "themes")) {
		global $db; 
		foreach ($cats as $cat) {
			$opts = '';
			//print_r($_POST);
			$catname = ucwords(str_replace("-", " ", $cat));
			$query = "SELECT * FROM category WHERE title = '$cat' ORDER BY sort_order, name";
			//print "$query<br>\n";
			if ($results = $db->get_results($query)) {
				foreach ($results as $result) {
					
					$values = array();
					if ($_SERVER['REQUEST_METHOD']=="POST") {
						// Search form was posted
						if (is_array($_POST['product_'.$cat])) $values = $_POST["product_".$cat];
					}
					else {
						if (isset($_GET['product_'.$cat])) {
							// Search results pagination I think
							$values = $_GET['product_'.$cat];
						}
						else if (is_array($this->categories[$cat])) {
							// Loaded product - don't show categories anymore
							//print "set values on loaded data<br>\n";
							//$values = $this->categories[$cat];
							$values=array();
						}
						else $values = array();
					}
					
					$opts .= '<option value="'.$result->id.'"'.(in_array($result->id, $values)?' selected="selected"':"").'>'.$result->name.'</option>';
				}
				if ($opts) $html .= '<div class = "category-select fields">
	<label for="f_'.$cat.'">'.$catname.'</label>
	<select name="product_'.$cat.'[]" multiple size="4">'.$opts.'</select>
</div>
';
			}
		}
		return $html;
	}
	


	public function getCategoryMenu($parent=0, $selected=false){
		global $db, $storeURL;
		$query = "SELECT * FROM store_categories WHERE parent_id=$parent";
		if( $data = $db->get_results($query) ){
			$html .= '<a'. ($selected=='' ? ' class="selected"' : '') .' href="'. $storeURL .'/" id="allProducts">All Products</a>'."\n";
			$html .= '<ul id="categoryMenu">'."\n";
			foreach($data as $item){
				$html .= '<li>
							<a'. ($item->name==$selected ? ' class="selected"' : '') .' href="'. $storeURL .'/'. $item->name .'/">'. $item->title .'</a>';
				$query = "SELECT * FROM store_categories WHERE parent_id={$item->cat_id}";
				if( $children = $db->get_results($query) ){
					$html .= "\t".'<ul>'."\n";
					foreach($children as $child){
						$html .= "\t\t".'<li>
											<a'. ($child->name==$selected ? ' class="selected"' : '') .' href="'. $storeURL .'/'. $item->name .'/'. $child->name .'/">'. $child->title .'</a>
										</li>'."\n";
					}
					$html .= "\t".'</ul>'."\n";
				}
				$html .= '</li>'."\n";
			}
			$html .= '</ul>';
			return $html;
		}else{
			return false;
		}
	}
	
	
	
	public function saveCategories($data=false){
		global $db;
		if( is_array($data) ){
			foreach($data as $key=>$item){
				if( $item['delete']==1 ){
					$query = "DELETE FROM store_categories WHERE cat_id=$key";
					$db->query($query);
				}else{
					$query = "UPDATE store_categories SET title='". $db->escape($item['title']) ."', 
								name='". $db->escape($item['title']) ."' WHERE cat_id=$key";
					$db->query($query);
				}
			}
			return true;
		}else{
			return false;
		}
	}



	public function getOrders($from=0, $to=20, $filterBy=false, $filterOrder='asc', $filterValue=false){
		global $db, $site, $totalOrders;
		//print "getOrders($from, $to, $filterBy, $filterOrder, $filterValue)<br>";

		$query = "SELECT so.*, m.title as mem_title, 
			CONCAT(m.firstname, ' ', m.surname) customer_name, 
			IF(sc.title>'',sc.title,IF(sc2.title>'',sc2.title,'N/A')) country_title,
			(
				SELECT sum(sod.quantity*sod.price) FROM store_orders_details sod
				WHERE sod.order_id=so.order_id
			) AS products_total,
			IF(sodon.donation_amount IS NULL,0,sodon.donation_amount) donation_total,
			(
				SELECT IF(sosp.amount IS NULL,0,sum(sosp.amount)) 
				FROM store_orders_sponsorships sosp 
				WHERE so.order_id=sosp.order_id
			) sponsorship_total,
			(
				SELECT IF(e.price IS NULL,0,SUM(e.price)) 
				FROM `events` e 
				LEFT JOIN event_entry ee ON ee.event_guid = e.guid
				LEFT JOIN store_orders_events soe ON e.guid=soe.entry_id 
				WHERE soe.order_id=so.order_id
			) events_total
			FROM store_orders so
			LEFT JOIN members m ON so.member_id=m.member_id
			LEFT OUTER JOIN store_orders_donations sodon ON so.order_id=sodon.order_id
			LEFT OUTER JOIN store_address_book sab ON so.shipping_addr_id=sab.addr_id
			LEFT OUTER JOIN store_address_book sab2 ON so.billing_addr_id=sab2.addr_id
			LEFT OUTER JOIN store_countries sc ON sab.country_id=sc.country_id
			LEFT OUTER JOIN store_countries sc2 ON sab2.country_id=sc2.country_id
			WHERE so.msv=".($site->id+0)." 
			";

		if( $filterBy && $filterValue ){
			switch($filterBy){
				case 'status':
					$query .= "AND so.`status`='$filterValue' ";
					$orderBy = "ORDER BY so.`status` $filterOrder ";
					break;
				case 'name':
					$query .= "AND (m.firstname LIKE '%$filterValue%' OR m.surname LIKE '%$filterValue%') ";
					$orderBy = "ORDER BY m.firstname $filterOrder, m.surname $filterOrder ";
					break;
				case 'order':
					$query .= "AND so.order_id='$filterValue' ";
					$orderBy = "ORDER BY so.order_id $filterOrder ";
					break;
			}		
		}
		
		if( !$filterBy || $filterBy!='status' ) $query .= "AND so.`status`>0 ";			

		$query .= "GROUP BY so.order_id ";
		if( $orderBy ) {
			$query .= $orderBy;
			$query .= ", so.date_order_started DESC,date_cart_started DESC ";
		} 
		else {
			$query .= "ORDER BY so.date_order_started DESC, date_cart_started DESC ";
		}
					
		$db->query($query);
		$totalOrders = $db->num_rows;
		//print "got total($totalOrders) from($query)<br>\n";		
		$query .= "LIMIT $from, $to ";

			//echo "$query <br>\n ";
		if( $data = $db->get_results($query) ){
			return $data;
		}else{
			return false;
		}
	}


	public function getOrderDetails( $orderID=false ){
		global $db;
		
		if( $orderID ){
			$query = "SELECT so.*, m.title, CONCAT(m.firstname, ' ', m.surname) customer_name, 
				m.telephone, m.email, sc.title country_title,
				UNIX_TIMESTAMP(date_cart_started) f_date_cart_started,
				UNIX_TIMESTAMP(date_order_started) f_date_order_started,
				UNIX_TIMESTAMP(date_order_completed) f_date_order_completed,
				sod.use_gift_aid, sos.use_gift_aid AS use_gift_aid1, sod.donation_written
				FROM store_orders so
				LEFT JOIN members m ON so.member_id=m.member_id
				LEFT OUTER JOIN store_orders_donations sodon ON so.order_id=sodon.order_id
				LEFT OUTER JOIN store_address_book sab ON so.shipping_addr_id=sab.addr_id
				LEFT OUTER JOIN store_countries sc ON sab.country_id=sc.country_id
				LEFT OUTER JOIN store_orders_donations sod ON so.order_id=sod.order_id
				LEFT OUTER JOIN store_orders_sponsorships sos ON so.order_id=sos.order_id
				WHERE so.order_id='$orderID'
				GROUP BY so.order_id
				LIMIT 1
				";
			//echo 'query: '. $query .'<br />';
			if( $data = $db->get_row($query) ){
				//echo '<pre>'. print_r($data,true) .'</pre>';
				if ($data->title) $data->customer_name = $data->title." ".$data->customer_name;
				if (!$data->use_gift_aid && $data->use_gift_aid1) $data->use_gift_aid=$data->use_gift_aid1;
				return $data;
			}
		}
		return false;
	}



	public function getProductList($keywords=false, $perPage=20, $currentPage=1, $status=false){
		
		global $db, $site, $totalProducts;
		
		$select = "SELECT 
			sp.product_id, sp.title, sp.name, sp.short_desc, sp.long_desc, 
			sp.`status`, sp.`priority`,  sp.physical, sp.page_guid, sp.about_tab1, sp.about_tab2,
			IF(sc2.title>'',CONCAT(sc2.title,' &raquo; ',sc.title), sc.title) categories,
			(
				SELECT si.price FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) AS price,
			(
				SELECT si.stock_level FROM store_inventory si
				LEFT JOIN store_products_variants spv ON spv.item_id=si.item_id
				WHERE spv.product_id=sp.product_id LIMIT 1
			) AS stock_level,
			(
				SELECT si3.item_id FROM store_inventory si3
				LEFT JOIN store_products_variants spv3 ON spv3.item_id=si3.item_id
				WHERE spv3.product_id=sp.product_id LIMIT 1
			) AS item_id
			";
		$query = "
			FROM store_products sp
			LEFT JOIN store_categories_products scp ON sp.product_id=scp.product_id
			LEFT JOIN store_categories sc ON scp.cat_id=sc.cat_id
			LEFT JOIN store_categories sc2 ON sc.parent_id=sc2.cat_id
			WHERE sp.`status`>-1 
			AND sp.msv=".($site->id+0)." ";
			
		$query .= ($keywords ? "AND (sp.title LIKE '%$keywords%' OR sp.short_desc LIKE '%$keywords%' OR sp.long_desc LIKE '%$keywords%') " : '');
		$query .= ($status>='0' ? "AND sp.`status`=$status " : '');
		$query .= "GROUP BY sp.product_id ORDER BY sp.priority ASC, sp.title ASC ";
		
		$limits = "LIMIT ".getQueryLimits($perPage, $currentPage);
		//echo "SELECT sp.product_id $query <br />\n";
		
		if ($db->get_results("SELECT sp.product_id ".$query)) {
			$this->totalProducts = $db->num_rows;
		
			//echo "$select.$query.$limits<br>\n";
			return $db->get_results($select.$query.$limits);
		}
		return false;
	}



	public function getProductImages($name=false){
		global $db, $site;
		if( $name ){
			$query = "SELECT spi.* FROM store_products_images spi
				LEFT JOIN store_products sp ON spi.product_id=sp.product_id
				WHERE sp.name='". $db->escape($name) ."'
				AND sp.msv = ".$site->id."
				ORDER BY spi.sort_order";
			if( $data = $db->get_results($query,"ARRAY_A") ){
				return $data;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}


	public function getFeatured($cat) {
		global $db, $site;
		//print "gF($cat)<br>\n";
		$query = "SELECT 
			sp.product_id, sp.title, sp.name, sp.short_desc, sp.physical, sp.image,
			(
				SELECT si.price 
				FROM store_inventory si
				WHERE si.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) AS price,
			(
				SELECT CONCAT(MIN(si.price),',',MAX(si.price)) FROM store_inventory si
				WHERE si.product_id=sp.product_id
				ORDER BY price ASC LIMIT 1 
			) AS price_range,
			(
				SELECT GROUP_CONCAT(item_id) FROM store_products_variants spv
				WHERE spv.product_id = sp.product_id
				GROUP BY product_id
			) variants
			FROM store_products sp
			WHERE sp.msv=".($site->id+0)." 
			AND sp.featured = 1
			";
		if ($cat) $query.="AND (sc.title='$cat' || sc2.title='$cat') ";
		$query .= "ORDER BY RAND() LIMIT 5 ";
		//print "$query<br>\n";
		if ($results = $db->get_results($query)) return $results;
		return false;
		
	}


	public function drawFeatured($cat) {

		return '
<div id="store-featured">
</div>
    
<div id="headerimgstore">
	<div id="headerimg1" class="headerimg">
		<div class="headernav"></div>
	</div>
	<div id="headerimg2" class="headerimg">
		<div class="headernav"></div>
	</div>

	<div id="headertxt">

		<div id="fader-details">
			<h2><a id="firstline"></a></h2>
			<p class="infobox">
				<span id="secondline">Some text</span>
				<span><a id="readmore">More details</a></span>
			</p>
		</div>
		<div id="fader-spec">
			<p id="fader-pprice"><span id="fader-price"></span> <span id="fader-priceinfo">+ P&P</span></p>
			<p><a id="fader-basket">Add to your items</a></p>
			<p><a id="fader-more">Find out more</a></p>
		</div>

		<div id="header-button">
			<div id="header-back" class="btn">&laquo</div>
			<div id="header-first" class="btn"></div>
			<div id="header-second" class="btn"></div>
			<div id="header-third" class="btn"></div>
			<div id="header-fourth" class="btn"></div>
			<div id="header-fifth" class="btn"></div>
			<div id="header-next" class="btn">&raquo</div>
		</div>
	</div>
	
</div>

';
	}

	public function updateProductImages($productID=false, $images=false){
		global $db;
		
		if( $productID && is_array($images) ){
			//echo 'we have images!<br />';
			//echo '<pre>'. print_r($images,true) .'</pre>';
			
			foreach ($images as $image){
				
				if ($image['marked_for_deletion']){
					// Delete db record and actual files
					$query = "DELETE FROM store_products_images WHERE image_id=". $image['id'];
					$path = $_SERVER['DOCUMENT_ROOT'].'/store/images/'. $productID .'/';
					if( $db->query($query) ){			
						$sizes = array('','_m','_sm','_vsm');
						foreach( $sizes as $s ){	
							@unlink($path.'/'.$image['id'] . $s .'.jpg');
						}
					}
				} 
				else {
					$query = "UPDATE store_products_images SET sort_order='". $image['sort_order'] ."', caption='". $db->escape($image['caption']) ."' WHERE image_id=". $image['id'];
					$db->query($query);
				}
			}
			return true;
		}
		return false;
	}
	
	
	
	public function saveProduct($productID=false, $properties=false){
		global $db, $site;
		//print "sP($productID, props)<br>\n";
		$this->dbg=true;
		reset($this->msg);
		
		//print "Save product with title(".$properties['title'].")<br>\n";
		if (!$properties['title']) {
			$this->msg[] = "No product title was entered";
			return false;
		}

		//$physical = strtolower($properties['category'])=="donations"?0:1;
		$physical = $properties['physical']=="on"?1:0;
		if( is_array($properties) ){
			//print_r($properties);
			if( $productID>0 ){
				
				// Update relevant categories first
				//$this->updateCategories($productID, $properties);

				// This is an edit...UPDATE
				$query = "UPDATE store_products 
					SET title='". $db->escape($properties['title']) ."', 
					name='". $db->escape($properties['name']) ."', 
					author='". $db->escape($properties['author']) ."', 
					short_desc='". $db->escape($properties['short_desc']) ."', 
					long_desc='". $db->escape($properties['long_desc']) ."', 
					status='". $db->escape($properties['status']) ."', 
					physical=$physical, featured=".($properties['featured']?1:0).",
					about_tab1='". $db->escape($properties['info']) ."', 
					about_tab2='". $db->escape($properties['care']) ."' 
					WHERE product_id=$productID";
				$db->query($query);
				//print "$query<br>\n";
				if( $db->affected_rows>=0 ){
					if( is_array($properties['variants']) ){
						$query = "DELETE FROM store_products_variants_types WHERE product_id=$productID";
						$db->query($query);
						foreach($properties['variants'] as $v){
							$query = "INSERT INTO store_products_variants_types (product_id,type_id) VALUES ($productID,$v)";
							$db->query($query);
						}
					}
					if( isset($properties['category']) && $properties['category'] ){
						$sort_order = $db->get_var("SELECT MAX(sort_order)+1 FROM store_categories_products");
						$query = "REPLACE INTO store_categories_products (cat_id, product_id, sort_order) 
									VALUES ((SELECT cat_id FROM store_categories WHERE name='". $properties['category'] ."'),
									$productID,".($sort_order+0).")";
						$db->query($query);
					}
				}
				else {
					if ($this->dbg) $this->msg[]="No rows affected by update";
					return false;
				}
			}
			// No ID, you must be new around here...
			else {
				
				if( $this->generateName(false,$properties['title'],'products') ){
					
					$query = "INSERT INTO store_products 
						(
							title, name, author, msv,
							short_desc, long_desc, status,
							priority, physical, featured,
							about_tab1, about_tab2
						) 
						VALUES 
						(
						'". $db->escape($properties['title']) ."', 
						'". $db->escape($properties['name']) ."', 
						'". $db->escape($properties['author']) ."', 
						".($site->id+0).",
						'". $db->escape($properties['short_desc']) ."', 
						'". $db->escape(nl2br($properties['long_desc'])) ."',
						'". $db->escape($properties['status']) ."', 
						'1',$physical, ".($properties['featured']?1:0).",
						'". $db->escape($properties['info']) ."', 
						'". $db->escape($properties['care']) ."'
						)";
					//print "$query<br>\n";
					if( $db->query($query) ){
						$productID = $db->insert_id;
						if( is_array($properties['variants']) ){
							foreach($properties['variants'] as $v){
								$query = "INSERT INTO store_products_variants_types (product_id,type_id) VALUES ($productID,$v)";
								$db->query($query);
							}
						}
						
						if( isset($properties['category']) && $properties['category'] ){
							$query = "SELECT MAX(sort_order)+1 FROM store_categories_products";
							//print "$query<br>\n";
							$sort_order = $db->get_var($query);
							$query = "REPLACE INTO store_categories_products 
								(cat_id, product_id, sort_order) 
								VALUES 
								(
									(SELECT cat_id FROM store_categories WHERE name='". $properties['category'] ."'),
									$productID, ".($sort_order+0)."
								)";
							//print "$query<br>\n";
							$db->query($query);
						}

						// Update relevant categories 
						//$this->updateCategories($productID, $properties);
								
						$this->loadByName($properties['name']);
						return $productID;
					}
					else {
						if ($this->dbg) array_push($this->msg, "Insert query($query) failed");
						return false;
					}
				}
				else {
					$this->msg[]="A product by this name already exists";
					return false;
				}
			}
		}
		else {
			if ($this->dbg) $this->msg[]="Invalid properties array";
			return false;
		}
		
		return $productID;
	}
	
	public function saveImage($productID, $image) {
		global $db, $storeVersion;
		
		//$this->errmsg[] = "upload(".print_r($image, true).")";
		if ($image['error']) $this->errmsg[] = "Image upload error[".$image['error']."]";

		switch($image['type']) {
			case "image/jpeg": $extension = "jpg"; break;
			case "image/png": $extension = "png"; break;
			case "image/gif": $extension = "gif"; break;
		}
		if (!$extension) {
			$this->errmsg[] = "File type[".$image['type']."] is not allowed";
			return false;
		}
		
		// Get directory to copy to.
		$dest = $_SERVER['DOCUMENT_ROOT']."/store/".$storeVersion."/images/".$productID;
		//$dest = $_SERVER['DOCUMENT_ROOT']."/store/images/".$productID;
		if (!file_exists($dest)) {
			mkdir($dest);
			chmod($dest, "755");
		}
		//$this->errmsg[] = "Copy to ".$dest;
		if (file_exists($dest)) {
			
			// Check image dimensions
			$sz = getimagesize($image['tmp_name']);
			//$this->errmsg[] = "Size(".print_r($sz, true).")";
			
			if ($sz[0]==$this->imgW && $sz[1]==$this->imgH ) {
				$fname="/img-".$productID.".".$extension;			
				//$this->errmsg[] = "Copy(".$image['tmp_name']."\n to file to($dest)<br>\n";
				if (move_uploaded_file($image['tmp_name'], $dest.$fname)) {
					$query = "UPDATE store_products SET image='".$fname."' WHERE product_id=".$productID;
					//print "$query<br>\n";
					$db->query($query);
					if (!$db->last_error) return true;
					else {
						$this->errmsg[] = "Failed to add product image to the database";
					}
				}
				else {
					$this->errmsg[]="Failed to copy product file to server";
				}
			}
			else {
				$this->errmsg[] = "Product image dimensions must be exactly ".$this->imgW."x".$this->imgH." pixels";
			}
		}
		else {
			$this->errmsg[]="Failed to create directory for product image";
		}
		return false;
		
	}
	
	
	public function loadImage($productID) {
		global $db, $storeVersion;
		//print "lI($productID)<br>\n";
		if ($productID) {
			$query = "SELECT image FROM store_products WHERE product_id = ".$productID;
			//print "$query<br>\n";
			if ($img = $db->get_var($query)) {
				$file = $_SERVER['DOCUMENT_ROOT']."/store/".$storeVersion."/images/".$productID.$img;
				//print "file($file)<br>\n";
				if (file_exists($file)) return '
<div class="field" style="margin-bottom:10px;">
	<label for="f_img" style="display:none;">Image</label>
	<img id="f_img" src="/store/'.$storeVersion.'/images/'.($productID.$img).'" />
</div>
';
				//else print "File($file) does not exist<br>\n";
			}
		}
	}
	
	
	public function deleteProduct($productID=false){
		global $db;
		if( $productID ){
			/* 
			we don't actually delete this stuff - we need to keep 
			the basic elements around for order references! 
			*/
			// product record
			// product/category relationship
			// product images
			// product_variant_types
			// inventory
			// item(s)/variants
			$query = "UPDATE store_products SET `status`='-1' WHERE product_id=$productID";
			if( $db->query($query) ) {
				return true;
			}
		}
		return false;
	}
	
	
	/*
	THINK THIS WAS ALL JUST FOR DEP
	public function updateCategories($prod_id, $data, $cats = array("key-stage", "subject", "themes")) {
	
		global $db;
		
		$query = "DELETE FROM store_products_categories WHERE product_id = $prod_id";
		$db->query($query);
		
		foreach ($cats as $cat) {
			if (is_array($data[$cat])) {
				foreach ($data[$cat] as $cat_id) {
					//print "got cat id($cat_id) for cat($cat)<br>\n";
					$query = "INSERT INTO store_products_categories 
						(product_id, category_id)
						VALUES
						(".$prod_id.", ".$cat_id.")
						";
					//print "$query<br>\n";
					if (!$db->query($query)) print "Failed ($query)<br>\n";
				}
			}
		}
	}
	*/

	// modified version of the same method found in the page class...
	public function generateName($ID=false,$title=false,$table='products') {
		global $db, $site;
		if( $title ){
			$title = $db->escape($title);	
			
			// Strip everything but letters, numbers and spaces from the title
			$name = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
			// Replace spaces with dashes
			$name = str_replace(" ",'-',$name);
			$name = strtolower($name);

			switch($table){		
				case 'products':
					$query = "SELECT * FROM store_products WHERE name = '$name'";
					$query .= ($ID) ? " AND product_id!='$ID' " : '';
					break;
				case 'categories':
					$query = "SELECT * FROM store_categories WHERE name = '$name'";
					$query .= ($ID) ? " AND cat_id!='$ID' " : '';
					break;
				case 'variants':
					$query = "SELECT * FROM store_variants WHERE title = '$title'";
					$query .= ($ID) ? " AND variant_id!='$ID' " : '';
					break;
				case 'types':
					$query = "SELECT * FROM store_types WHERE title = '$title'";
					$query .= ($ID) ? " AND type_id!='$ID' " : '';
					break;
			}
			$query .="AND msv=".($site->id+0)." ";
			$data = $db->get_row($query);
			if ($db->num_rows > 0 ) {
				return false;
			} 
			else {
				return $name;
			}
		}
		else{
			return false;
		}
	}	
	
	
	public function updateInventory($productID=false, $properties=false){
		global $db;

		if( $productID && is_array($properties) ){
			// first we format the main query for updating each item record
			foreach( $properties as $key=>$item ){
				if( $item['delete']==1 ){
					$query = "DELETE FROM store_inventory WHERE item_id=$key";
					$db->query($query);
					$query = "DELETE FROM store_products_variants WHERE item_id=$key AND product_id=$productID";
					$db->query($query);
				}
				else{
					$query = "UPDATE store_inventory SET price='".$item['price']."', stock_level='".$item['stock_level']."', 
								tagline='".$db->escape($item['tagline'])."', weight='".$item['weight']."' WHERE item_id=".$key;
					//echo $query .'<br />';
					$db->query($query);
					//$vars = $item['variants'];
					if( ($vars = $item['variants']) && is_array($vars) ){
						$query = "DELETE FROM store_products_variants WHERE item_id=$key AND product_id=$productID";
						$db->query($query);
						foreach( $vars as $v ){
							$query = "REPLACE INTO store_products_variants (item_id, product_id, variant_id) 
										VALUES ($key,$productID,$v)";
							//echo '&nbsp;&nbsp;'.$query .'<br />';
							$db->query($query);
						}
					}
					// then, if we have variants we can update/replace those for this item
				}
			}
			return true;
		}else{
			return false;
		}
	}
	
	
	
	public function addInventory($productID=false, $properties=false){
		global $db;

		if( $productID && is_array($properties) ){
			// first we format the main query for updating each item record
			foreach( $properties as $key=>$item ){
				$price = ($item['price']>'' ? $item['price'] : '0');
				$stock_level = ($item['stock_level']>'' ? $item['stock_level'] : '0');
				$weight = ($item['weight']>'' ? $item['weight'] : '0');
				$tagline = ($item['tagline']>'' ? $item['tagline'] : '0');
				
				$query = "INSERT INTO store_inventory 
					(price, stock_level, weight, tagline, product_id) 
					VALUES 
					(".$price.",".$stock_level.", ".$weight.", '".$db->escape($tagline)."', $productID)";
				//echo $query .'<br />';	
				if($db->query($query) ){
					$itemID = $db->insert_id;
				}

			}
			return true;
		}
		else{
			return false;
		}
	}
	

	
	public function updateStock( $basket=false ){
		global $db;
		
		if( is_object($basket) ){
			// each product-item needs to be decremented against their stock level
			foreach( $basket->basket as $item ){
				$query = "UPDATE store_inventory SET stock_level=(stock_level-". $item->quantity .") WHERE item_id=".$item->item_id;
				$db->query($query);
			}
			return true;
		}else{
			return false;
		}
	}



	public function saveSortOrder($inventory=false){
		global $db;
		
		if( isset($inventory) && is_array($inventory) ){
			foreach($inventory as $key=>$val){
				$query = "UPDATE store_products SET priority=$val WHERE name='". $db->escape($key) ."'";
				//echo $query .'<br />';
				$db->query($query);
			}
			return true;
		}else{
			return false;
		}
	}


}


?>
