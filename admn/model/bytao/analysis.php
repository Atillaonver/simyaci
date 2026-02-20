<?php
class ModelBytaoAnalysis extends Model {
	
	public function addAnalysis($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "analysis SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "'");

		$analysis_id = $this->db->getLastId();
		
		foreach ($data['analysis_description'] as $language_id => $analysis_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "analysis_description SET analysis_id = '" . (int)$analysis_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($analysis_description['title']) . "', description = '" .  $this->db->escape($analysis_description['description']) . "'");
		}

		return $analysis_id;
	}

	public function editAnalysis($analysis_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "analysis SET status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "' WHERE analysis_id = '" . (int)$analysis_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "analysis_description WHERE analysis_id = '" . (int)$analysis_id . "'");
		foreach ($data['analysis_description'] as $language_id => $analysis_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "analysis_description SET analysis_id = '" . (int)$analysis_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($analysis_description['title']) . "', description = '" .  $this->db->escape($analysis_description['description']) . "'");
		}
		
	}

	public function deleteAnalysis($analysis_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "analysis WHERE analysis_id = '" . (int)$analysis_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "analysis_description WHERE analysis_id = '" . (int)$analysis_id . "'");
	}

	public function getAnalysis($analysis_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "analysis  WHERE analysis_id = '" . (int)$analysis_id . "'");

		return $query->row;
	}

	public function getAnalysiss($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "analysis o LEFT JOIN " . DB_PREFIX . "analysis_description od ON o.analysis_id=od.analysis_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY od.title ASC";


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
	
	public function getAnalysisDescriptions($analysis_id) {
		$analysis_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analysis_description WHERE analysis_id = '" . (int)$analysis_id . "'");

		foreach ($query->rows as $result) {
			$analysis_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'            => $result['description'],
			);
		}

		return $analysis_description_data;
	}
		
	
	public function getTotalAnalysiss() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "analysis WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isAnalysisInstore($analysis_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "analysis WHERE analysis_id = '" . (int)$analysis_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installAnalysis(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."analysis'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "analysis` (`analysis_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`bimage` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "analysis_description` (`analysis_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "analysis` ADD PRIMARY KEY (`analysis_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "analysis_description` ADD PRIMARY KEY (`analysis_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "analysis` MODIFY `analysis_id` int(11) NOT NULL AUTO_INCREMENT;";
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}