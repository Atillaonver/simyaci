<?php
namespace Opencart\Admin\Model\Bytao;
class Ode extends \Opencart\System\Engine\Model {
	
	public function addOde($data):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "ode SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "'");

		$ode_id = $this->db->getLastId();
		
		foreach ($data['ode_description'] as $language_id => $ode_description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "ode_description SET ode_id = '" . (int)$ode_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($ode_description['title']) . "', description = '" .  $this->db->escape($ode_description['description']) . "'");
		}

		return $ode_id;
	}

	public function editOde($ode_id, $data):void {
		$this->db->query("UPDATE " . DB_PREFIX . "ode SET status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "' WHERE ode_id = '" . (int)$ode_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "ode_description WHERE ode_id = '" . (int)$ode_id . "'");
		foreach ($data['ode_description'] as $language_id => $ode_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "ode_description SET ode_id = '" . (int)$ode_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($ode_description['title']) . "', description = '" .  $this->db->escape($ode_description['description']) . "'");
		}
		
	}

	public function deleteOde($ode_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "ode WHERE ode_id = '" . (int)$ode_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "ode_description WHERE ode_id = '" . (int)$ode_id . "'");
	}

	public function getOde($ode_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "ode  WHERE ode_id = '" . (int)$ode_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getOdes(array $data ):array {
		$sql = "SELECT * FROM " . DB_PREFIX . "ode o LEFT JOIN " . DB_PREFIX . "ode_description od ON o.ode_id=od.ode_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY od.title ASC";
 

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
	
	public function getOdeDescriptions($ode_id):array {
		$ode_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ode_description WHERE ode_id = '" . (int)$ode_id . "'");

		foreach ($query->rows as $result) {
			$ode_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'            => $result['description'],
			);
		}

		return $ode_description_data;
	}
	
	public function getOdeImage(int $ode_id,$type=0):array {
		$news_image_data = [];

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE ode_id = '" . (int)$ode_id . "'  AND type='".(int)$type."' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
			$news_image_description_data = [];

			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND ode_id = '" . (int)$ode_id . "'");

			foreach ($news_image_description_query->rows as $news_image_description) {
				$news_image_description_data[$news_image_description['language_id']] = array('title' => $news_image_description['title'],'subtitle' => $news_image_description['subtitle'],'description' => $news_image_description['description']);
			}

			$news_image_data[] = array(
				'news_image_description' => $news_image_description_data,
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
		
	public function getTotalOdes():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ode WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function getOdeSeoUrls(int $ode_id): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$ode_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'oden_id' AND `store_id` ='".(int)$store_id ."' AND `value` = '" . $ode_id . "'");
		if(isset($query->rows)){
			foreach ($query->rows as $result) {
				$ode_seo_url_data[$result['language_id']] = $result['keyword'];
			}
		}
		return $ode_seo_url_data;
	}
	
	
	
	public function isOdeInstore($ode_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ode WHERE ode_id = '" . (int)$ode_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installOde():void{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."ode'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "ode` (`ode_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "ode_description` (`ode_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "ode` ADD PRIMARY KEY (`ode_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "ode_description` ADD PRIMARY KEY (`ode_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "ode` MODIFY `ode_id` int(11) NOT NULL AUTO_INCREMENT;";
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}