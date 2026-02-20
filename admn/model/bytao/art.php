<?php
namespace Opencart\Admin\Model\Bytao;
class Art extends \Opencart\System\Engine\Model {
	
	private $DBs = 'art,art_description,art_to_category';
	
	public function getDBNames(){
		return $this->DBs;
	}
	
	public function addArt($data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "art SET made_year = '" . $this->db->escape($data['made_year']) . "', art_code = '" . $this->db->escape($data['art_code']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "',store_id = '" .(int)$this->session->data['store_id']. "'");

		$art_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "art SET image = '" . $this->db->escape($data['image']) . "' WHERE art_id = '" . (int)$art_id . "'");
		}
		
		if (isset($data['art_description'])) {
			foreach ($data['art_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "art_description SET art_id = '" . (int)$art_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
			}
		}
		
		if (isset($data['art_category'])) {
			foreach ($data['art_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "art_to_category SET art_id = '" . (int)$art_id . "', art_category_id = '" . (int)$category_id . "', col_num='" . (int)$this->session->data['next']. "', store_id = '" . (int)$this->session->data['store_id']. "'");
			}
		}


		return $art_id;
	}

	public function editArt($art_id, $data) {
		
		$this->db->query("UPDATE " . DB_PREFIX .  "art SET made_year = '" . $this->db->escape($data['made_year']) . "', art_code = '" . $this->db->escape($data['art_code']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE art_id = '" . (int)$art_id . "'");
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "art SET image = '" . $this->db->escape($data['image']) . "' WHERE art_id = '" . (int)$art_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_description WHERE art_id = '" . (int)$art_id . "'");
		
		foreach ($data['art_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "art_description SET art_id = '" . (int)$art_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		if (isset($data['updatecategories'])){
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "art_to_category WHERE art_id = '" . (int)$art_id . "'");
			
			if (isset($data['art_category'])) {
				
				foreach ($data['art_category'] as $category_id) {
					$catquery = $this->db->query("SELECT * FROM ". DB_PREFIX . "art_to_category WHERE art_id = '" . (int)$art_id . "' AND art_category_id = '" . (int)$category_id . "'");
					if(!$catquery->rows){
						$this->db->query("INSERT INTO " . DB_PREFIX . "art_to_category SET art_id = '" . (int)$art_id . "', col_num='" . (int)$this->session->data['next']. "', art_category_id = '" . (int)$category_id . "' , store_id = '" .(int)$this->session->data['store_id']. "'");
					}
				}
			}
		
		}
		
		
		
		
		
		$this->event->trigger('post.admin.edit.art', $art_id);
	}

	public function deleteArt($art_id) {
		$this->event->trigger('pre.admin.delete.art', $blog_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "art WHERE art_id = '" . (int)$art_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_description WHERE art_id = '" . (int)$art_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_to_category WHERE art_id = '" . (int)$art_id . "'");


		$this->event->trigger('post.admin.delete.art', $art_id);
	}

	public function getArt($art_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art WHERE art_id = '" . (int)$art_id . "'");

		return $query->row;
	}

	public function getArts($data = []) {
			$sort_data = [];
			$sql = "SELECT DISTINCT *,a.art_id AS artId FROM " . DB_PREFIX . "art a LEFT JOIN " . DB_PREFIX . "art_to_category a2c ON (a.art_id = a2c.art_id) WHERE a.store_id = '" . (int)$this->session->data['store_id'] . "'";

			
			if(isset($data['filter_category'])){
				$sql .= "  AND a2c.art_category_id = '".(int)$data['filter_category']."'";
				
				$sort_data = [
					'ad.title',
					'a2c.sort_order'
				];
			}
			
			if($data['filter_code']){
				$sql .= "  AND a.art_code LIKE '%". $this->db->escape($data['filter_code'])."%'";

				$sort_data = [
					'a.art_code',
					'a2c.sort_order'
				];
			}
			
			if($data['filter_name']){
				$sql .= "  AND (ad.name LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.description LIKE '%". $this->db->escape($data['filter_name'])."%')";
				$sort_data = [
				'ad.title',
				'a.sort_order'
				];
			}
			
			$sql .= " GROUP BY a2c.art_id";
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY a.art_id";
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

	public function getArtDescriptions($art_id):array {
		$art_description_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_description WHERE art_id = '" . (int)$art_id . "'");

		foreach ($query->rows as $result) {
			$art_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $art_description_data;
	}

	public function getArtCategories($art_id):array {
		$art_category_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_to_category WHERE art_id = '" . (int)$art_id . "'");

		foreach ($query->rows as $result) {
			$art_category_data[] = $result['art_category_id'];
		}

		return $art_category_data;
	}
	
	public function getTotalArts(array $data = []):int {
		if (!empty($data['filter_category'])) {
			
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art_to_category a2c WHERE a2c.store_id = '" . (int)$this->session->data['store_id'] . "' AND a2c.art_category_id='".(int)$data['filter_category']."'";
			
		}else{
			$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art a LEFT JOIN " . DB_PREFIX . "art_to_category a2c ON a.store_id = a2c.art_id WHERE a.store_id = '" . (int)$this->session->data['store_id'] . "' ";	
		
			
			if(isset($data['filter_category'])){
				$sql .= "  AND a2c.art_category_id = '".(int)$data['filter_category']."'";

			}
			
			if(isset($data['filter_code'])){
				$sql .= "  AND a.art_code LIKE '%".(int)$data['filter_code']."%'";

			
			}
			
			if(isset($data['filter_name'])){
				$sql .= "  AND (ad.name LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.description LIKE '%". $this->db->escape($data['filter_name'])."%')";
				
			}
			
			//$sql .= " GROUP BY a.art_id";
		}
		
			$query = $this->db->query($sql);

		return isset($query->row['total'])?$query->row['total']:0;
	}

	public function updateArtSortorder($sort_order, $art_id, $art_category_id=0, $colNumber=0){
	
			$this->db->query("UPDATE " . DB_PREFIX . "art_to_category SET sort_order = '" . (int)$sort_order . "', col_num = '" . (int)$colNumber . "' WHERE art_id = '" . (int)$art_id . "' AND art_category_id='".(int)$art_category_id ."'");
		
		
		
	}
	
	public function isArtInStore($art_id) {
		$query = $this->db->query("SELECT count(*) as isIn FROM " . DB_PREFIX . "art WHERE art_id = '" . (int)$art_id . "' AND store_id = '" .(int)$this->session->data['store_id']. "'");

		return $query->row['isIn'];
	}
}