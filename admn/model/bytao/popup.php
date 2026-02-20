<?php
namespace Opencart\Admin\Model\Bytao;
class popup extends \Opencart\System\Engine\Model {
	
	public function addPopup($popupdata):int {
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "popup SET customer_group_id = '" . (int)$popupdata['customer_group_id'] . "', name = '" .$this->db->escape($popupdata['name']) . "', link = '" . $this->db->escape($popupdata['link']) . "', popup_type = '" . $this->db->escape($popupdata['popup_type']) . "', image = '" . $this->db->escape($popupdata['image']) . "', status = '" . (int)$popupdata['status'] . "', store_id = '" . (int)$this->session->data['store_id'] . "', date_start = '" . $this->db->escape($popupdata['date_start']) . "', date_end = '" . $this->db->escape($popupdata['date_end']) . "'");
			
			$popup_id = $this->db->getLastId();
			
			if(isset($popupdata['popup_description'])){
				foreach($popupdata['popup_description'] as $language_id => $value){
					$this->db->query("INSERT INTO " . DB_PREFIX . "popup_description SET popup_id = '" . (int)$popup_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', status = '" . $this->db->escape($value['status']) . "', image = '" . $this->db->escape($value['image']) . "'");
				}
			
			}
			
			return $popup_id;
	}

	public function editPopup(int $popup_id,array $popupdata):void {
		$this->db->query("UPDATE " . DB_PREFIX . "popup SET customer_group_id = '" . (int)$popupdata['customer_group_id'] . "', name = '" .$this->db->escape($popupdata['name']) . "', link = '" . $this->db->escape($popupdata['link']) . "', popup_type = '" . $this->db->escape($popupdata['popup_type']) . "',image = '" . $this->db->escape($popupdata['image']) . "', status = '" . (int)$popupdata['status'] . "', date_start = '" . $this->db->escape($popupdata['date_start']) . "', date_end = '" . $this->db->escape($popupdata['date_end']) . "' WHERE popup_id='".$popup_id."'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "popup_description WHERE popup_id = '" . (int)$popup_id . "'");
		
		if(isset($popupdata['popup_description'])){
				foreach($popupdata['popup_description'] as $language_id => $value){
					$this->db->query("INSERT INTO " . DB_PREFIX . "popup_description SET popup_id = '" . (int)$popup_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', status = '" . $this->db->escape($value['status']) . "', image = '" . $this->db->escape($value['image']) . "'");
				}
			
			}
			
	}

	public function deletePopup(int $popup_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "popup WHERE popup_id = '" . (int)$popup_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "popup_description WHERE popup_id = '" . (int)$popup_id . "'");
	}

	public function getPopup(int $popup_id):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "popup WHERE popup_id = '" . (int)$popup_id . "'");
		
		return isset($query->row)?$query->row:[];
	}
	
	public function getPopupDescriptions(int $popup_id):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "popup_description WHERE popup_id = '" . (int)$popup_id . "'");
		
		return isset($query->row)?$query->row:[];
	}

	public function getPopups():array {
		
		$sql = "SELECT * FROM " . DB_PREFIX . "popup WHERE store_id = '" . (int)$this->session->data['store_id'] . "' ";
		$sort_data = [
			'name',
			'status'
		];

		if(isset($data['sort']) && in_array($data['sort'], $sort_data)){
			$sql .= " ORDER BY " . $data['sort'];
		} else{
			$sql .= " ORDER BY name";
		}

		if(isset($data['order']) && ($data['order'] == 'DESC')){
			$sql .= " DESC";
		} else{
			$sql .= " ASC";
		}

		if(isset($data['start']) || isset($data['limit'])){
			if($data['start'] < 0){
				$data['start'] = 0;
			}

			if($data['limit'] < 1){
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getTotalPopups():int {
		$query = $this->db->query("SELECT count(*)as total FROM " . DB_PREFIX . "popup WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		return isset($query->row['total'])?$query->row['total']:0;
	}

	public function isPopupInStore(int $popup_id):bool{
		$query = $this->db->query("SELECT count(*)as total FROM " . DB_PREFIX . "popup WHERE store_id = '" . (int)$this->session->data['store_id'] . "' AND popup_id = '" . (int)$popup_id . "'");
		return $query->row['total']?FALSE:TRUE;
	}
	
	public function install(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."popup'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  = "CREATE TABLE `ast_popup` (`popup_id` int(11) NOT NULL,`popup_type` int(2) NOT NULL,`name` varchar(50) NOT NULL,`link` varchar(50) NOT NULL,`description` text NOT NULL,`image` varchar(250) NOT NULL,`store_id` int(11) NOT NULL,`temp_id` int(11) NOT NULL,`customer_group_id` int(11) NOT NULL,`date_start` date NOT NULL ,`date_end` date NOT NULL, `status` int(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

			$sql[]  = "CREATE TABLE `ast_popup_description` (`popup_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`image` text NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
			
			$sql[]  = "ALTER TABLE `ast_popup`  ADD PRIMARY KEY (`popup_id`);";

			$sql[]  = "ALTER TABLE `ast_popup_description` ADD PRIMARY KEY (`popup_id`,`language_id`);";
			
			$sql[]  = "ALTER TABLE `ast_popup` MODIFY `popup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
  			
  			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}

}