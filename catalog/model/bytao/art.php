<?php
namespace Opencart\Catalog\Model\Bytao;
class Art extends \Opencart\System\Engine\Model {	
	
	public function getArt($art_id) {
		//$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art WHERE art_id = '" . (int)$art_id . "' and status='1' ");
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "art a LEFT JOIN " . DB_PREFIX . "art_description ad ON (a.art_id = ad.art_id) WHERE a.art_id = '" . (int)$art_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.store_id = '" . (int)$this->config->get('config_store_id') . "' AND a.status = '1'");

		return $query->row;
	}

	public function getArts($data = array()) {
			
			
			$sql = "SELECT DISTINCT * FROM " . DB_PREFIX . "art a LEFT JOIN " . DB_PREFIX . "art_description ad ON (a.art_id = ad.art_id) LEFT JOIN " . DB_PREFIX . "art_to_category a2c ON (a.art_id = a2c.art_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND a.store_id = '" . (int)$this->config->get('config_store_id') . "' AND a.status = '1'";
			
			if(isset($data['filter_category_id'])){
				$sql .= "  AND a2c.art_category_id = '".(int)$data['filter_category_id']."'";
				$sort_data = array(
				'ad.title',
				'a2c.sort_order'
			);
			}
			
			if(isset($data['filter_name'])){
				$sql .= "  AND ( a.art_code LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.name LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.description LIKE '%". $this->db->escape($data['filter_name'])."%')  GROUP BY a.art_id";
				$sort_data = array(
				'ad.title',
				'a.sort_order'
			);
			}
			
			
			

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY a2c.sort_order";
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
 //$this->log->write('SQL:'.print_r($sql,true));
			$query = $this->db->query($sql);

			return $query->rows;
		
	}

	public function getTotalArts($data = array()) {
		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art a LEFT JOIN " . DB_PREFIX . "art_description ad ON (a.art_id = ad.art_id) LEFT JOIN " . DB_PREFIX . "art_to_category a2c ON (a.art_id = a2c.art_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND a.store_id = '" . (int)$this->config->get('config_store_id') . "' AND a.status = '1'";
			
			if(isset($data['filter_category_id'])){
				$sql .= "  AND a2c.art_category_id = '".(int)$data['filter_category_id']."'";
				
			}
			
			if(isset($data['filter_name'])){
				$sql .= "  AND ( a.art_code LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.name LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.description LIKE '%". $this->db->escape($data['filter_name'])."%') GROUP BY ad.art_id";
				
			}
			
			

			$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	
	
}