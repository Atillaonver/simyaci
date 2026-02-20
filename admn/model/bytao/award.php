<?php
namespace Opencart\Admin\Model\Bytao;
class Award extends \Opencart\System\Engine\Model {
	
	public function addAward($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "award SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "'");

		$award_id = $this->db->getLastId();
		
		foreach ($data['award_description'] as $language_id => $award_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "award_description SET award_id = '" . (int)$award_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($award_description['title']) . "', description = '" .  $this->db->escape($award_description['description']) . "'");
		}

		return $award_id;
	}

	public function editAward($award_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "award SET status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "' WHERE award_id = '" . (int)$award_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "award_description WHERE award_id = '" . (int)$award_id . "'");
		foreach ($data['award_description'] as $language_id => $award_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "award_description SET award_id = '" . (int)$award_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($award_description['title']) . "', description = '" .  $this->db->escape($award_description['description']) . "'");
		}
		
	}

	public function deleteAward($award_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "award WHERE award_id = '" . (int)$award_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "award_description WHERE award_id = '" . (int)$award_id . "'");
	}

	public function getAward($award_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "award  WHERE award_id = '" . (int)$award_id . "'");

		return $query->row;
	}

	public function getAwards($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "award o LEFT JOIN " . DB_PREFIX . "award_description od ON o.award_id=od.award_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY od.title ASC";


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
	
	public function getAwardDescriptions($award_id) {
		$award_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "award_description WHERE award_id = '" . (int)$award_id . "'");

		foreach ($query->rows as $result) {
			$award_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'            => $result['description'],
			);
		}

		return $award_description_data;
	}
		
	public function getTotalAwards() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "award WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isAwardInstore($award_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "award WHERE award_id = '" . (int)$award_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installAward(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."award'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "award` (`award_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`bimage` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "award_description` (`award_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "award` ADD PRIMARY KEY (`award_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "award_description` ADD PRIMARY KEY (`award_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "award` MODIFY `award_id` int(11) NOT NULL AUTO_INCREMENT;";
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}