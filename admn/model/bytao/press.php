<?php
namespace Opencart\Admin\Model\Bytao;
class Press extends \Opencart\System\Engine\Model{
	
	public function addPress($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "press SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "'");

		$press_id = $this->db->getLastId();
		
		foreach ($data['press_description'] as $language_id => $press_description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "press_description SET press_id = '" . (int)$press_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($press_description['title']) . "',description = '" .  $this->db->escape($press_description['description']) . "',meta_title = '" . $this->db->escape($press_description['meta_title']) . "', meta_description = '" . $this->db->escape($press_description['meta_description']) . "', meta_keyword = '" . $this->db->escape($press_description['meta_keyword']) . "',image='" . $this->db->escape($press_description['image']) . "',video='" . $this->db->escape($press_description['video']) . "'");
		}

		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->load->model('design/seo_url');

		if (isset($data['press_seo_url'])) {
			foreach ($data['press_seo_url'] as $language_id => $keyword) {
				$this->model_design_seo_url->addSeoUrl('press_id', $press_id, $keyword, $store_id, $language_id,0,'','bytao/press.ajax');
			}
		}


		return $press_id;
	}

	public function editPress($press_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "press SET status = '" . (int)$data['status'] . "' WHERE press_id = '" . (int)$press_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "press_description WHERE press_id = '" . (int)$press_id . "'");
		
		foreach ($data['press_description'] as $language_id => $press_description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "press_description SET press_id = '" . (int)$press_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($press_description['title']) . "',description = '" .  $this->db->escape($press_description['description']) . "',meta_title = '" . $this->db->escape($press_description['meta_title']) . "', meta_description = '" . $this->db->escape($press_description['meta_description']) . "', meta_keyword = '" . $this->db->escape($press_description['meta_keyword']) . "',image='" . $this->db->escape($press_description['image']). "',video='" . $this->db->escape($press_description['video'])  . "'");
		}
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'press_id' AND `value` = '" . (int)$press_id . "'");
		
		$this->load->model('design/seo_url');
		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('press_id', $press_id);

		if (isset($data['press_seo_url'])) {
			foreach ($data['press_seo_url'] as $language_id => $keyword) {
				$this->model_design_seo_url->addSeoUrl('press_id', $press_id, $keyword, $store_id, $language_id,0,'','bytao/press.ajax');
			}
		}
		
	}
	
	public function deletePress($press_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "press WHERE press_id = '" . (int)$press_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "press_description WHERE press_id = '" . (int)$press_id . "'");
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = '" . (int)$press_id . "'");
		
	}

	public function getPress($press_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "press  WHERE press_id = '" . (int)$press_id . "'");

		return $query->row;
	}
	
	public function getPressDescription($press_id) {
		
		$pressData=[];
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "press_description  WHERE press_id = '" . (int)$press_id . "'");
		
		foreach($query->rows as $row){
			$pressData[$row['language_id']]=[
				'title' => $row['title'],
				'meta_title' => $row['meta_title'],
				'description' => $row['description'],
				'meta_description' => $row['meta_description'],
				'meta_keyword' => $row['meta_keyword'],
				'video' => $row['video'],
				'image' => $row['image']
			];
		}
		return $pressData;
	}

	public function getPresses($data = []):array {
		$sql = "SELECT *,pi.image FROM " . DB_PREFIX . "press p LEFT JOIN " . DB_PREFIX . "press_description pd ON p.press_id=pd.press_id LEFT JOIN " . DB_PREFIX . "press_image pi ON p.press_id=pi.press_id   WHERE pd.language_id= '".(int)$this->config->get('config_language_id')."' AND  p.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY p.sort_order ASC";


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
	
	public function sortPress(int $press_id,int $sort_order):void {
		$this->db->query("UPDATE " . DB_PREFIX . "press set sort_order='".(int)$sort_order."' WHERE press_id = '" . (int)$press_id . "'");
	}
	
	public function getTotalPresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "press WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isPressInstore($press_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "press WHERE press_id = '" . (int)$press_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installPress(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."press'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "press` (`press_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "press_description` (`press_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "press_prod` (`press_prod_id` int(11) NOT NULL,`press_id` int(11) NOT NULL,`position` int(1) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(200) NOT NULL,`image2` varchar(200) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "press_prod_description` (`press_id` int(11) NOT NULL,`press_prod_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`title2` varchar(300) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press` ADD PRIMARY KEY (`press_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press_description` ADD PRIMARY KEY (`press_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press_prod` ADD PRIMARY KEY (`press_prod_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press_prod_description` ADD PRIMARY KEY (`press_id`,`press_prod_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press` MODIFY `press_id` int(11) NOT NULL AUTO_INCREMENT;";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "press_prod` MODIFY `press_prod_id` int(11) NOT NULL AUTO_INCREMENT;";			
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}

	public function updatePressSortOrder(int $sort_order, int $press_id):void
	{
		$this->db->query("UPDATE " . DB_PREFIX . "press SET sort_order = '" . (int)$sort_order . "' WHERE press_id = '" . (int)$press_id . "'");
	}

	public function isInstore(int $press_id):bool
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "press WHERE press_id = '" . (int)$press_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if ($query->row['total']>0)
			return false;
		else
			return true;
	}
	
	public function getPressSeoUrls(int $press_id): array
	{

		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$press_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'press_id' AND `store_id` ='".(int)$store_id ."' AND `value` = '" . $press_id . "'");
		if (isset($query->rows)) {

			foreach ($query->rows as $result) {
				$press_seo_url_data[$result['language_id']] = $result['keyword'];
			}
		}


		return $press_seo_url_data;
	}

	
}