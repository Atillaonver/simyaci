<?php 
namespace Opencart\Admin\Model\Bytao;
class Layerslider extends \Opencart\System\Engine\Model{
	private $DBs = "layerslider,layerslider_layer";
	
	public function getDBNames():string {
		return $this->DBs;
	}
	
	public function addLayerslider( array $data ):int{
		if($data['slider']){
			$params = serialize($data['slider']);
		}else{
			$params ='';
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "layerslider SET status = '" . (int)$data['status'] . "', name = '" . $this->db->escape($data['name'] ). "', position = '" . (int)$data['position'] . "', position_id = '" . (int)$data['position_id'] . "',viewpos = '" . (int)$data['viewpos'] . "', params = '" . $params . "', start_date='".((int)$data['start_date']? date('Y-m-d',strtotime($this->db->escape($data['start_date']))):'')."', end_date='".((int)$data['end_date']?date('Y-m-d',strtotime($this->db->escape($data['end_date']))):'')."', store_id='".(int)$this->session->data['store_id']."'");

		$layerslider = $this->db->getLastId();
		return $layerslider;
	}
	
	public function updateLayerslider(array $data ):int {
		 if($data['slider']){
			$params = serialize($data['slider']);
		}else{
			$params ='';
		}
		
		$this->db->query("UPDATE " . DB_PREFIX . "layerslider SET status = '" . (int)$data['status'] . "', name = '" . $this->db->escape($data['name'] ). "', position = '" . (int)$data['position'] . "', position_id = '" . (int)$data['position_id'] . "',viewpos = '" . (int)$data['viewpos'] . "', params = '" . $params . "', start_date='".((int)$data['start_date']? date('Y-m-d',strtotime($this->db->escape($data['start_date']))):'')."', end_date='".((int)$data['end_date']?date('Y-m-d',strtotime($this->db->escape($data['end_date']))):'')."' WHERE layerslider_id ='".(int)$data['layerslider_id']."' ");

		 return $data['layerslider_id'];
	}
	
	public function editLayersliderGroup(array $data ):int {
		
		$layerslider_group_id = $data['layerslider_group_id'];
		
		if($data['layerslider_group_id']){
			
			if($data['slider']){
				$params = serialize($data['slider']);
			}else{
				$params ='';
			}
			
			$sql = "UPDATE " . DB_PREFIX . "layerslider_group SET"; 
			$sql .= isset($data['slider']['status'])? " status = '" . (int)$data['slider']['status'] . "'":"";
			$sql .= isset($data['slider']['type'])? ", type = '" . (int)$data['slider']['type'] . "'":"";
			$sql .= isset($data['slider']['name'])? ", name = '" . $this->db->escape($data['slider']['name'] ). "'":"";
			$sql .= isset($data['slider']['position'])? ", position = '" . (int)$data['slider']['position'] . "'":"";
			$sql .= isset($data['slider']['position_id'])? ", position_id = '" . (int)$data['slider']['position_id'] . "'":"";
			$sql .= isset($data['slider']['viewpos'])? ",viewpos = '" . (int)$data['slider']['viewpos'] . "'":"";
			$sql .= ", params = '" . $params . "'";
			$sql .= isset($data['start_date'])? ", start_date='".((int)$data['start_date']? date('Y-m-d',strtotime($this->db->escape($data['start_date']))):'')."'":"";
			$sql .= isset($data['end_date'])? ", end_date='".((int)$data['end_date']?date('Y-m-d',strtotime($this->db->escape($data['end_date']))):'')."'":"";
			$sql .= " WHERE layerslider_group_id ='".(int)$data['layerslider_group_id']."' ";
			
			$this->db->query($sql);
		}else{
			
			if($data['slider']){
				$params = serialize($data['slider']);
			}else{
				$params ='';
			}
		
			$sql = "INSERT INTO " . DB_PREFIX . "layerslider_group SET"; 
			$sql .= isset($data['slider']['status'])? " status = '" . (int)$data['slider']['status'] . "'":"";
			$sql .= isset($data['slider']['type'])? ", type = '" . (int)$data['slider']['type'] . "'":"";
			$sql .= isset($data['slider']['name'])? ", name = '" . $this->db->escape($data['slider']['name'] ). "'":"";
			$sql .= isset($data['slider']['position'])? ", position = '" . (int)$data['slider']['position'] . "'":"";
			$sql .= isset($data['slider']['position_id'])? ", position_id = '" . (int)$data['slider']['position_id'] . "'":"";
			$sql .= isset($data['slider']['viewpos'])? ",viewpos = '" . (int)$data['slider']['viewpos'] . "'":"";
			$sql .= isset($data['slider']['params'])? ", params = '" . $params . "'":"";
			$sql .= isset($data['start_date'])? ", start_date='".((int)$data['start_date']? date('Y-m-d',strtotime($this->db->escape($data['start_date']))):'')."'":"";
			$sql .= isset($data['end_date'])? ", end_date='".((int)$data['end_date']?date('Y-m-d',strtotime($this->db->escape($data['end_date']))):'')."'":"";
			$sql .= ",store_id='".(int)$this->session->data['store_id']."'";
			$this->db->query($sql);

			$layerslider_group_id = $this->db->getLastId();
		}
		return $layerslider_group_id;
	}
	
	public function updateLayersliderGroupImage(int $layerslider_group_id,string $image ):void{
		$this->db->query("UPDATE " . DB_PREFIX . "layerslider_group SET image = '" . $this->db->escape($image) . "' WHERE layerslider_group_id ='".(int)$layerslider_group_id."' ");
	}
	
	public function updateLayersliderStatus(int $layerslider_id,int $status = 2 ):int{
		if($status<2){
			$this->db->query("UPDATE " . DB_PREFIX . "layerslider SET status = '" . (int)$status . "' WHERE layerslider_id ='".(int)$layerslider_id."' ");
		}
		 return $status;
	}
	
	public function moveLayerslider( int $layerslider_id,int $language_id):void {
		$this->db->query( "UPDATE ".DB_PREFIX."layerslider SET language_id='".(int)$language_id."' WHERE layerslider_id = ".$layerslider_id );
		$this->db->query( "UPDATE ".DB_PREFIX."layerslider_layer SET language_id='".(int)$language_id."' WHERE parent_id = ".$layerslider_id );
		
	}
	
	public function cloneLayerslider( int $layerslider_id):array {
		// Get SliderLayer By Group
		$layersliderID = 0;
		
		$query = $this->db->query( "SELECT * FROM ".DB_PREFIX."layerslider where layerslider_id = '"  . (int)$layerslider_id."' LIMIT 1" );
		if(isset($query->row['layerslider_id'])){
			$sql = "INSERT INTO ".DB_PREFIX . "layerslider ( `";
			$tmp = [];
			$vals = [];
			
			foreach( $query->row as $key => $value ){
				if($key!='layerslider_id'){
					$tmp[] = $key;
					if($key=='name'){
						$vals[]= 'Copy of '.$value;
					}else{
						$vals[]= $value;
					}
					
				}
			}
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
			$layersliderID = $this->db->getLastId();
			$query->row['name']= 'Copy of '.$query->row['name'];
		}
		
		if($layersliderID){
			$sql = "SELECT * FROM ".DB_PREFIX."layerslider_layer where parent_id = "  . $layerslider_id;
			$query2 = $this->db->query( $sql );
			if(isset($query2->rows)){
				foreach ($query2->rows as $row) {
					$sql = "INSERT INTO ".DB_PREFIX . "layerslider_layer ( `";
					$tmp = [];
					$vals = [];
					foreach( $row as $key => $value ){
						if($key != 'layer_id'){
							$tmp[] = $key;
							if($key == 'parent_id'){
								$vals[]= $layersliderID ;
							}else{
								$vals[]= $value;
							}
						}
					}				
				 	$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
					$this->db->query( $sql );
				}
			}
			$query->row['layerslider_id'] = $layersliderID;
		}
		
		return $query->row;
		
	}
	
	public function cloneLayersliderLayer(int $groupID, int $cloneGroupID, int $languageID):void {
		// Get SliderLayer By Group
		$sql = "SELECT * FROM ".DB_PREFIX."layerslider_layer where group_id = "  . $cloneGroupID;
		$query = $this->db->query( $sql );
		$rows = $query->rows;
		
		if( !empty($query->rows) ){
			foreach ($rows as $row) {
				$sql2 = "INSERT INTO ".DB_PREFIX."layerslider_layer (title, parent_id, group_id, params, layersparams, image, `status`, position, language_id) SELECT title, parent_id, '" . $groupID . "', params, layersparams, image, status, position, '" . $languageID . "' FROM ".DB_PREFIX."layerslider AS iv WHERE iv.layerslider_id=".$row['layerslider_id'];
				$this->db->query( $sql2 );
			}
		}
	}
	
	public function getLayersliders(array $data):array {
		$sql=" SELECT * FROM ".DB_PREFIX."layerslider  WHERE store_id='".(int)$this->session->data['store_id'] ."'";
		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE LOWER('%" . $this->db->escape($data['filter_name']) . "%')";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_start_date']) && (int)$data['filter_start_date']>0) {
			$sql .= " AND DATE(start_date) <= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
		}
		
		if (isset($data['filter_end_date']) && (int)$data['filter_end_date']>0) {
			$sql .= " AND DATE(end_date) >= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
		}
		
		$sql .= " GROUP BY layerslider_id";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;

	}
	
	public function getTotalLayersliders(array $data):int {
		
			$sql = "SELECT COUNT(DISTINCT layerslider_id) AS total FROM " . DB_PREFIX . "layerslider  WHERE store_id='".(int)$this->session->data['store_id'] ."'";
			
		

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_start_date']) && (int)$data['filter_start_date']>0) {
			$sql .= " AND DATE(start_date) >= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
		}
		
		if (isset($data['filter_end_date']) && (int)$data['filter_end_date']>0) {
			$sql .= " AND DATE(end_date) <= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getLayersliderGroups(array $data):array {
		
		$sql=" SELECT * FROM ".DB_PREFIX."layerslider_group  WHERE store_id='".(int)$this->session->data['store_id'] ."'";
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE LOWER('%" . $this->db->escape($data['filter_name']) . "%')";
		}
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_start_date']) && (int)$data['filter_start_date']>0) {
			$sql .= " AND DATE(start_date) <= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
		}
		
		if (isset($data['filter_end_date']) && (int)$data['filter_end_date']>0) {
			$sql .= " AND DATE(end_date) >= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
		}
		
		$sql .= " GROUP BY layerslider_group_id";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];

	}
	
	public function getTotalLayersliderGroups(array $data):int {
		
			$sql = "SELECT COUNT(DISTINCT layerslider_group_id) AS total FROM " . DB_PREFIX . "layerslider_group  WHERE store_id='".(int)$this->session->data['store_id'] ."'";
			
		

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}
		
		if (isset($data['filter_start_date']) && (int)$data['filter_start_date']>0) {
			$sql .= " AND DATE(start_date) >= DATE('" . $this->db->escape($data['filter_start_date']) . "')";
		}
		
		if (isset($data['filter_end_date']) && (int)$data['filter_end_date']>0) {
			$sql .= " AND DATE(end_date) <= DATE('" . $this->db->escape($data['filter_end_date']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getLayersliderGroupImage( int $layerslider_group_id ):string {
		$sql = 'SELECT image FROM '. DB_PREFIX . 'layerslider_group WHERE layerslider_group_id='.(int)$layerslider_group_id;
		$query = $this->db->query( $sql );

		return isset($query->row['image'])?$query->row['image']:'';
	}
	
	public function getLayersliderGroup( int $layerslider_group_id ):array {
		$sql = 'SELECT * FROM '. DB_PREFIX . 'layerslider_group WHERE layerslider_group_id='.(int)$layerslider_group_id;
		$query = $this->db->query( $sql );
		$sliderGroup = $query->row;
		
	 	$params = array(
			'link' => '',
			'type' => '1',
			'delay' => '9000',
			'height' => '350',
			'width'  => '960',
			'touch_mobile' => 1,
			'stop_on_hover' => 1,
			'shuffle_mode'=>'0',
			'image_cropping' => '0',
			'shadow_type' => '2',
			'show_time_line' => '1',
			'time_line_position' => 'top',
			'background_color' => '#d9d9d9',
			'padding'=> '5px 5px',
			'margin' => '0px 0px 18px',
			'background_image' => '0',
			'background_url'  => '',
			'navigator_type' => 'none',
			'navigator_arrows' => 'verticalcentered',
			'navigation_style' => 'round',
			'offset_horizontal' => '0',
			'offset_vertical'   => '20',
			'show_navigator' => '0',
			'hide_navigator_after' => '200',
			'thumbnail_height' => '50',
			'thumbnail_width'  => '100',
			'thumbnail_amount' => '5',
			'hide_screen_width' => ''
		);

	 	if(is_array($sliderGroup)&&(isset($sliderGroup['params']))){
			$sliderGroup['params'] = unserialize( $sliderGroup['params'] );
			$sliderGroup['params'] = is_array($sliderGroup['params'])?array_merge( $params, $sliderGroup['params'] ):$params;
		}else {
			$sliderGroup['params'] = $params;
		}

		return $sliderGroup ;
	}
	
	public function getLayersliderGroupById(int $layerslider_group_id ):array {
		$sql = ' SELECT * FROM '. DB_PREFIX . 'layerslider_group WHERE layerslider_group_id='.$layerslider_group_id;
		$query = $this->db->query( $sql );
		$sliderGroup = $query->row;
		
	 	$params = array(
			'link' => '',
			'type' => '1',
			'delay' => '9000',
			'height' => '350',
			'width'  => '960',
			'touch_mobile' => 1,
			'stop_on_hover' => 1,
			'shuffle_mode'=>'0',
			'image_cropping' => '0',
			'shadow_type' => '2',
			'show_time_line' => '1',
			'time_line_position' => 'top',
			'background_color' => '#d9d9d9',
			'padding'=> '5px 5px',
			'margin' => '0px 0px 18px',
			'background_image' => '0',
			'background_url'  => '',
			'navigator_type' => 'none',
			'navigator_arrows' => 'verticalcentered',
			'navigation_style' => 'round',
			'offset_horizontal' => '0',
			'offset_vertical'   => '20',
			'show_navigator' => '0',
			'hide_navigator_after' => '200',
			'thumbnail_height' => '50',
			'thumbnail_width'  => '100',
			'thumbnail_amount' => '5',
			'hide_screen_width' => ''
		);

	 	if( $sliderGroup ){
			$sliderGroup['params'] = unserialize( $sliderGroup['params'] );
			$sliderGroup['params'] = array_merge( $params, $sliderGroup['params'] );
		}else {
			$sliderGroup['params'] = $params;
		}

		return $sliderGroup ;
	}
	
	public function getLayerslider( int $layerslider_id ):array {
		$sql = 'SELECT * FROM '. DB_PREFIX . 'layerslider WHERE layerslider_id='.(int)$layerslider_id .' LIMIT 1';
		$query = $this->db->query( $sql );
		
	 	return isset($query->row)?$query->row:[];
	}
	
	public function getLayersliderLayer(int $parent_id ):array {
		$sql = 'SELECT * FROM '. DB_PREFIX . 'layerslider_layer WHERE parent_id='.(int)$parent_id;
		$query = $this->db->query( $sql );
		
	 	return isset($query->rows)?$query->rows:[];
	}
	
	public function getLayersliderLayerId(int $layer_id ):array {
		$sql = "SELECT * FROM ". DB_PREFIX . "layerslider_layer WHERE layer_id='".(int)$layer_id."' LIMIT 1";
		$query = $this->db->query( $sql );
		
	 	return isset($query->row['layer_id'])?$query->row:[];
	}
	
	public function firstLayersliderImage(int $layer_id):void {

		$query = $this->db->query( "SELECT * FROM ". DB_PREFIX . "layerslider_layer WHERE layer_id='".(int)$layer_id ."' LIMIT 1");
		if(isset($query->row['layer_id'])){
			$this->db->query("UPDATE ". DB_PREFIX . "layerslider SET image='".$query->row['image']."' WHERE layerslider_id='".(int)$query->row['group_id']."'");
		}
	}
	
	public function deleteLayersliderSlider( int $id ):void {
		$query = 'DELETE FROM '. DB_PREFIX . "layerslider  WHERE layerslider_id=".$id;
		$this->db->query( $query );
		$query = 'DELETE FROM '. DB_PREFIX . "layerslider_layer  WHERE parent_id=".$id;
		$this->db->query( $query );
	}
	
	public function deleteLayersliderGroups( int $group_id ):void {
		
		$this->db->query( 'DELETE FROM '. DB_PREFIX . "layerslider_group  WHERE layerslider_group_id=".(int)$group_id );
		$this->db->query( 'DELETE FROM '. DB_PREFIX . "layerslider  WHERE layerslider_id=".(int)$layerslider_id );
		$this->db->query( 'DELETE FROM '. DB_PREFIX . "layerslider_layer  WHERE group_id=".(int)$layerslider_id );
		return;
	}
	
	public function getLayersliderGroupWidget():array {
		$sql=" SELECT * FROM ".DB_PREFIX."layerslider_group  WHERE store_id='".(int)$this->session->data['store_id'] ."'";
		
		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];

	}
	
	
	
	public function updateLayersliderLayers( array $data ):int {
		 if( isset($data['layer_id']) && $data['layer_id'] ){
		 	$query = " UPDATE  ". DB_PREFIX . "layerslider_layer SET  ";
		 	$tmp = [];
		 	foreach( $data as $key => $value ){
				if( $key != "layer_id" ){
					$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
				}
			}
			$query .= implode( " , ", $tmp );
			$query .= ' WHERE layer_id='.$data['layer_id'];
			$this->db->query( $query );
		 }else {
	 		$query = "INSERT INTO ".DB_PREFIX . "layerslider_layer ( `";
			$tmp = [];
			$vals = [];
			foreach( $data as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
		 
		 	$query .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $query );
			$data['layer_id'] =  $this->db->getLastId();
		 }

		 return $data['layer_id'];
	}
	
	public function isIn(int $layerslider_id):bool {
		$query = $this->db->query("SELECT * FROM ". DB_PREFIX . "layerslider WHERE layerslider_id='".$layerslider_id."'");
		return isset($query->row['layerslider_id'])?TRUE:FALSE;
	}
	
	public function saveLayersliderData( array $data ):int {
		$layerslider_id =0;
		
		 if( isset($data['slider_id']) && $data['slider_id'] && $this->isIn($data['slider_id']) ){
		 	
		 	$query = " UPDATE  ". DB_PREFIX . "layerslider SET  ";
		 	$tmp = [];
		 	foreach( $data as $key => $value ){
				if($key!='slider_id' && $key!='layers'){
					$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
				}
			}
			$query .= implode( " , ", $tmp );
			$query .= ' WHERE layerslider_id='.$data['slider_id'];
			$this->db->query( $query );
			$data['layerslider_id'] = $layerslider_id = $data['slider_id'];
			
			
		 }else {
	 		$query = "INSERT INTO ".DB_PREFIX . "layerslider ( `";
			$tmp = [];
			$vals = [];
			foreach( $data as $key => $value ){
				if($key!='slider_id'&&$key!='layers'){
					$tmp[] = $key;
					$vals[]=$this->db->escape($value);
				}
				
			}				
		 
		 	$query .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $query );
			$layerslider_id =  $this->db->getLastId();
			
		 }
		
		 if(isset($data['layers']) && $data['layers']){
		 	$this->db->query( "DELETE FROM ".DB_PREFIX . "layerslider_layer WHERE parent_id='".(int)$layerslider_id."' AND group_id='".(int)$data['group_id']."'" );
		 	$lData = [];
		 	
		 	
		 	foreach( $data['layers'] as $layers ){
		 		foreach( $layers as $layer ){
					$lData []= array(
						'params' => serialize($layer),
						'parent_id'     => $this->db->escape($data['slider_id']),
						'group_id'     => $this->db->escape($data['group_id']),
						'image'        => $this->db->escape($layer['layer_image']),
					);
			 		
				}
				if($lData){
					foreach( $lData as $rec ){
						$tmp = [];
		 				$vals = [];
						foreach( $rec as $key => $value ){
								$tmp[] = $key;
								$vals[]=$this->db->escape($value);
						}	
						$query = "INSERT INTO ".DB_PREFIX . "layerslider_layer ( `";
						$query .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
						
				 		$this->db->query( $query );
					}
					
						
					}
			}				
		 	
		 
			
		 }

		 return $layerslider_id;
	}
	
	public function getListLayersliderSliderGroups():array {
		$query = ' SELECT * FROM '. DB_PREFIX . "layerslider   ";

		$query = $this->db->query( $query );
		$row = $query->rows;
 		
	 	return $row;
	}
	
	public function getLayersliderSlidersByGroupId( int $groupID, int $language_id ):array {
		$query  = ' SELECT * FROM '. DB_PREFIX . "layerslider";
		$query .= ' WHERE group_id='.(int)$groupID .' AND language_id='.(int)$language_id.' ORDER BY sort_order ASC';

		$query = $this->db->query( $query );
		return $query->rows;
	}
	
	public function getLayerslidersLayersByGroupId( int $groupID, int $language_id ):array {
		$query  = ' SELECT * FROM '. DB_PREFIX . "layerslider_layer";
		$query .= ' WHERE group_id='.(int)$groupID .' AND language_id='.(int)$language_id.' ORDER BY sort_order ASC';

		$query = $this->db->query( $query );
		return $query->rows;
	}
	
	public function getLayersliderSliderById( int $id ):array {
		$query = ' SELECT * FROM '. DB_PREFIX . "layerslider_layer   ";
		$query .= ' WHERE layer_id='.(int)$id;

		$query = $this->db->query( $query );
		$row = $query->row;
 	
	 	return $row;
	}
	
	public function updateLayersliderSortorder( int $layer_id , int $pos ):void {
		$sql = 'UPDATE '.DB_PREFIX.'layerslider SET `sort_order`='.$pos.' WHERE layerslider_id='.($layer_id);
		$this->db->query( $sql );
	}

	public function installLayerslider():void {
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."layerslider'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "layerslider_group` (`layerslider_group_id` int(11) NOT NULL,`store_id` int(3) NOT NULL,`name` varchar(50) NOT NULL,`position` int(1) NOT NULL,`position_id` int(11) NOT NULL,`viewpos` int(3) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(250) CHARACTER SET utf8 NOT NULL,`params` text CHARACTER SET utf8 NOT NULL,`status` int(1) NOT NULL,`start_date` date DEFAULT NULL,`end_date` date DEFAULT NULL,`date_added` datetime NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider_group` ADD PRIMARY KEY (`layerslider_group_id`);";
			
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider_group` MODIFY `layerslider_group_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "layerslider` (`layerslider_id` int(11) NOT NULL,`group_id` int(11),`name` varchar(50) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(250) CHARACTER SET utf8 NOT NULL,`params` text CHARACTER SET utf8 NOT NULL,`layersparams` text CHARACTER SET utf8 NOT NULL,`language_id` int(11) DEFAULT 1,`sort_order` int(11) NOT NULL DEFAULT 1,`status` int(1) NOT NULL,`start_date` date DEFAULT NULL,`end_date` date DEFAULT NULL,`date_added` datetime NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider` ADD PRIMARY KEY (`layerslider_id`);";
			
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider` MODIFY `layerslider_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "layerslider_layer` (`layer_id` int(11) NOT NULL,`parent_id` int(11) NOT NULL,`group_id` int(11) NOT NULL,`language_id` int(11) DEFAULT 1,`title` varchar(255) NOT NULL,`params` text NOT NULL,`layersparams` text NOT NULL,`image` varchar(255) NOT NULL,`status` tinyint(1) NOT NULL,`position` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider_layer` ADD PRIMARY KEY (`layer_id`), ADD KEY `layer_id` (`layer_id`);";
			
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "layerslider_layer` MODIFY `layer_id` int(11) NOT NULL AUTO_INCREMENT;";
  			
			
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}
}

?>