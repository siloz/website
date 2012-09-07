
<?php
class Item {
	public $item_id;
	public $user_id;
	public $silo_id;
	public $id;
	public $title;
	public $price;
	public $item_cat_id;
	public $description;
	public $status;
	public $link;
	public $added_date;
	public $sold_date;
	public $sent_date;
	public $received_date;
	public $deleted_date;
	public $photo_file_1;
	public $photo_file_2;
	public $photo_file_3;
	public $photo_file_4;			
	public $category;
	
	public $silo;
	public $owner;
	
	function __construct($id) {
		$id = (string)$id;
		if ($id[0] == '0')		
			$res = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE id = '$id'"));
		else
			$res = mysql_fetch_array(mysql_query("SELECT * FROM items INNER JOIN item_categories USING (item_cat_id) WHERE item_id = $id"));
		$this->item_id = $res['item_id'];
		$this->user_id = $res['user_id'];
		$this->silo_id = $res['silo_id'];
		$this->id = $res['id'];
		$this->title = $res['title'];
		$this->price = $res['price'];
		$this->item_cat_id = $res['item_cat_id'];
		$this->description = $res['description'];
		$this->status = $res['status'];
		$this->link = $res['link'];
		$this->added_date = $res['added_date'];
		$this->sold_date = $res['sold_date'];
		$this->sent_date = $res['sent_date'];
		$this->received_date = $res['received_date'];
		$this->deleted_date = $res['deleted_date'];
		$this->photo_file_1 = $res['photo_file_1'];
		$this->photo_file_2 = $res['photo_file_2'];
		$this->photo_file_3 = $res['photo_file_3'];
		$this->photo_file_4 = $res['photo_file_4'];
		$this->category = $res['category'];

		$this->silo = new Silo($this->silo_id);
		$this->owner = new User($this->user_id);
	}
	
	function getOwner() {
		return $this->owner;
	}
	
	function getSilo() {
		return $this->silo;
	}
	
	function getShortTitle($len) {
		if (strlen($this->title) > $len) {
			return substr($this->title, 0, $len)."..";
		}
		else {
			return $this->title;
		}		
	}
	
	function getPlate() {		
		$cell = "<div class=plate id=item_".$this->id."><a href='index.php?task=view_item&id=".$this->id."' onmouseover=highlight_item('".$this->id."')  onmouseout=unhighlight_item('".$this->id."')>";
		$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><a href='index.php?task=view_item&id=".$this->id."'><b>".$this->getShortTitle(40)."</b></a></div><img height=100px width=135px src=uploads/items/300px/".$this->photo_file_1." style='margin-bottom: 3px'><div style='color: #000;line-height: 120%;'><b>Helps: </b><a href=index.php?task=view_silo&id=".$this->silo->id.">".$this->silo->getShortName(35)."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$this->price."</b></span></td><td align=right><a href='index.php?task=view_item&id=".$this->id."'><i><b>more...</b></i></a></td></tr></table></a></div>";
		return $cell;
	}
	
	public function getSiloPreview() {
		return $this->silo->getPreview();
	}
	
	public function getPhoto($id) {
		if ($id == 1)
			return $this->photo_file_1;
		else if ($id == 2)
			return $this->photo_file_2;
		else if ($id == 3)
			return $this->photo_file_3;
		else if ($id == 4)
			return $this->photo_file_4;
	}
	
	public static function getItems($silo_id, $order_by) {
		$order_by_clause = "";
		if ($order_by == 'name')
			$order_by_clause = " ORDER BY username $sort_order ";
		if ($order_by == 'date')
			$order_by_clause = " ORDER BY joined_date $sort_order ";			
		$sql = "SELECT item_id FROM items INNER JOIN users USING (user_id) WHERE deleted_date = 0 AND silo_id = $silo_id $order_by_clause";
		$items = array();
		$tmp = mysql_query($sql);
		while ($res = mysql_fetch_array($tmp)) {
			$u = new Item($res['item_id']);
			$items[] = $u;
		}
		return $items;
	}
	
	public function getItemCell() {
		$cell = "<td><div class=plate id='item_".$this->id."' style='color: #000;'>";
		$cell .= "<table width=100% height=100%><tr valign=top><td valign=top colspan=2><div style='height: 30px'><a href='index.php?task=view_item&id=".$this->id."'><b>".substr($this->title, 0, 40)."</b></a></div><img height=100px width=135px src=uploads/items/300px/".$this->photo_file_1." style='margin-bottom: 3px'><div style='color: #000;line-height: 120%;'><b>Status: </b>".$this->status."<br/><b>Member: </b><a href='index.php?task=view_user&id=".$this->owner->id."'>".$this->owner->username."</a></div></td></tr><tr valign=bottom><td align=left align=left><span style='color: #f60'><b>$".$this->price."</b></span></td><td align=right><a href='index.php?task=view_item&id=".$this->id. "'><i><b>more...</b></i></a></td></tr></table></div></td>";							
		return $cell;		
	}
	
}
?>