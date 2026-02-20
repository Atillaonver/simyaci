<?php
namespace Opencart\Admin\Model\Bytao;
class Box extends \Opencart\System\Engine\Model {
	
	public function addBox($data) {
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "box WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		
			foreach ($data['popup'] as $popup) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "box SET customer_group_id = '" . (int)$popup['customer_group_id'] . "', name = '" .$this->db->escape($popup['name']) . "', position = '" .$this->db->escape($popup['position']) . "', link = '" . $this->db->escape($popup['link']) . "', image = '" . $this->db->escape($popup['image']) . "', description = '" . $this->db->escape($popup['description']) . "', status = '" . (int)$popup['status'] . "', store_id = '" . (int)$this->session->data['store_id'] . "',row_order = '" . (int)$popup['row_order'] . "',sort_order = '" . (int)$popup['sort_order'] . "', date_start = '" . $this->db->escape($popup['date_start']) . "', date_end = '" . $this->db->escape($popup['date_end']) . "'");
			}
		
	}

	
	public function editBox($data) {
		
		//$this->db->query("DELETE FROM " . DB_PREFIX . "box_row WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "box WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		
		if(isset($data['popup'])){
			foreach($data['popup'] as $row){
				//$this->log->write('row name: '. print_r($row['name'],TRUE));
				if(isset($row['box_row_id'])&& $row['box_row_id']){
					$box_row_id = $row['box_row_id'];
					$this->db->query("UPDATE " . DB_PREFIX . "box_row SET name = '" .$this->db->escape($row['name']) . "',status = '" . (int)$row['status'] . "', customer_group_id = '" . (int)$row['customer_group_id'] . "',sort_order = '" . (int)$row['sort_order'] . "', start_date = '" . $this->db->escape($row['start_date']) . "', end_date = '" . $this->db->escape($row['end_date']) . "' WHERE box_row_id = '" . (int)$box_row_id . "' AND  store_id = '" . (int)$this->session->data['store_id'] . "'");
					
				}else{
					$this->db->query("INSERT INTO " . DB_PREFIX . "box_row SET name = '" .$this->db->escape($row['name']) . "',status = '" . (int)$row['status'] . "', store_id = '" . (int)$this->session->data['store_id'] . "',customer_group_id = '" . (int)$row['customer_group_id'] . "',sort_order = '" . (int)$row['sort_order'] . "', start_date = '" . $this->db->escape($row['start_date']) . "', end_date = '" . $this->db->escape($row['end_date']) . "'");
					$box_row_id = $this->db->getLastId();
				}
				
				
				
				if(isset($row['boxes'])){
					foreach($row['boxes'] as $box){
						//$this->log->write('box: '. print_r($box,TRUE));
						$customer_group_id = isset($box['customer_group_id'])?(int)$box['customer_group_id']:0;
						
						$this->db->query("INSERT INTO " . DB_PREFIX . "box SET name = '" .$this->db->escape($box['name']) . "', position = '" .$this->db->escape($box['position']) . "', link = '" . $this->db->escape($box['link']) . "', image = '" . $this->db->escape($box['image']) . "', description = '" . $this->db->escape($box['description']) . "', box_row_id = '" . (int)$box_row_id . "',store_id = '" . (int)$this->session->data['store_id'] . "',sort_order = '" . (int)$box['sort_order'] . "'");
					}
				}
				
			}
		}
		
		
		return;
		/*
		$this->db->query("DELETE FROM " . DB_PREFIX . "box WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		foreach ($data['popup'] as $popup) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "box SET customer_group_id = '" . isset($popup['customer_group_id'])?(int)$popup['customer_group_id']:0 . "', name = '" .$this->db->escape($popup['name']) . "', position = '" .$this->db->escape($popup['position']) . "', link = '" . $this->db->escape($popup['link']) . "', image = '" . $this->db->escape($popup['image']) . "', description = '" . $this->db->escape($popup['description']) . "', status = '" . (int)$popup['status'] . "', store_id = '" . (int)$this->session->data['store_id'] . "',row_order = '" . (int)$popup['row_order'] . "', sort_order = '" . (int)$popup['sort_order'] . "', date_start = '" . $this->db->escape($popup['date_start']) . "', date_end = '" . $this->db->escape($popup['date_end']) . "'");
			}
	*/
	}

	public function getBoxGroups(array $data=[]):array {
		$sql = "SELECT * FROM " . DB_PREFIX . "box_row WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = array(
			'name',
			'sort_order',
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
		

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getBoxs($box_row_id){
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "box WHERE store_id = '" . (int)$this->session->data['store_id'] . "' AND box_row_id = '" . (int)$box_row_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getBox(){
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "box WHERE date_start <= NOW() AND date_end <= NOW() AND status='1' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		
		return $query->rows;
	}

}