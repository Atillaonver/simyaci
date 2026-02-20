<?php
namespace Opencart\Admin\Model\Bytao;
class Prod extends \Opencart\System\Engine\Model {
	
	public function addProd($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "prod SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',prod_cat_id = '" . (isset($data['prod_cat_id'])?$this->db->escape($data['prod_cat_id']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "',ref = '" . (isset($data['ref'])?$this->db->escape($data['ref']):'') . "'");

		$prod_id = $this->db->getLastId();
		
		foreach ($data['prod_description'] as $language_id => $prod_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "prod_description SET prod_id = '" . (int)$prod_id . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($prod_description['name']) . "',title = '" .  $this->db->escape($prod_description['title']) . "', description = '" .  $this->db->escape($prod_description['description']) . "',description2 = '" .  $this->db->escape($prod_description['description2']) . "',opt = '" . (isset($data['prod_opt'][$language_id])?$this->db->escape(json_encode($data['prod_opt'][$language_id])):'') . "',image = '" . (isset($prod_description['image'])?$this->db->escape($prod_description['image']):'') . "',`meta_title` = '" . $this->db->escape($prod_description['meta_title']) . "', `meta_description` = '" . $this->db->escape($prod_description['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($prod_description['meta_keyword']) . "'");
		}

		if (isset($data['prod_seo_url'])) {
			foreach ($data['prod_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'prod_id', `value` = '" . (int)$prod_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
		$this->load->model('design/seo_url');

		if (isset($data['prod_seo_url'])) {
			foreach ($data['prod_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('prod_id', $information_id, $keyword, $store_id, $language_id,0,'','bytao/prod');
			}
		}
		
		return $prod_id;
	}

	public function editProd($prod_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "prod SET status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',prod_cat_id = '" . (isset($data['prod_cat_id'])?$this->db->escape($data['prod_cat_id']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "',ref = '" . (isset($data['ref'])?$this->db->escape($data['ref']):'') . "' WHERE prod_id = '" . (int)$prod_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "prod_description WHERE prod_id = '" . (int)$prod_id . "'");
		foreach ($data['prod_description'] as $language_id => $prod_description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "prod_description SET prod_id = '" . (int)$prod_id . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($prod_description['name']) . "',title = '" .  $this->db->escape($prod_description['title']) . "', description = '" .  $this->db->escape($prod_description['description']) . "',description2 = '" .  $this->db->escape($prod_description['description2']) . "',opt = '" . (isset($data['prod_opt'][$language_id])?$this->db->escape(json_encode($data['prod_opt'][$language_id])):'') . "',image = '" . (isset($prod_description['image'])?$this->db->escape($prod_description['image']):'') . "',`meta_title` = '" . $this->db->escape($prod_description['meta_title']) . "', `meta_description` = '" . $this->db->escape($prod_description['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($prod_description['meta_keyword']) . "'");
		}
		
		
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('prod_id', $information_id);
		if (isset($data['prod_seo_url'])) {
			foreach ($data['prod_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('prod_id', $information_id, $keyword, $store_id, $language_id,0,'','bytao/prod');
			}
		}
		
		
		
	}

	public function deleteProd($prod_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "prod WHERE prod_id = '" . (int)$prod_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "prod_description WHERE prod_id = '" . (int)$prod_id . "'");
	}

	public function getProd($prod_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "prod  WHERE prod_id = '" . (int)$prod_id . "'");

		return $query->row;
	}

	public function getProds($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "prod o LEFT JOIN " . DB_PREFIX . "prod_description od ON o.prod_id=od.prod_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY od.title ASC";


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
	
	public function getProdDescriptions($prod_id) {
		$prod_description_data = array();
		$this->load->model('tool/image');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prod_description WHERE prod_id = '" . (int)$prod_id . "'");

		foreach ($query->rows as $result) {
			if (isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])) {
				$thumb= $this->model_tool_image->resize( $result['image'], 100, 100);
			} else{
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			$prod_description_data[$result['language_id']] = array(
				'name'            => $result['name'],
				'title'            => $result['title'],
				'opt'            => json_decode($result['opt'], true),
				'image'            => $result['image'],
				'thumb'            => $thumb,
				'description'            => $result['description'],
				'description2'            => $result['description2'],
				'meta_title'            => $result['meta_title'],
				'meta_description'            => $result['meta_description'],
				'meta_keyword'            => $result['meta_keyword']
			);
		}

		return $prod_description_data;
	}
		
	public function getTotalProds() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prod WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		
		return $query->row['total'];
	}
	
	public function getProdCatPath($prod_cat_id):string {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "prod_cat_description WHERE language_id= '".(int)$this->config->get('config_language_id')."' AND prod_cat_id = '" . (int)$prod_cat_id . "' LIMIT 1");
		
		return isset($query->row['name'])?$query->row['name']:'';
	}

	public function isProdInstore($prod_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "prod WHERE prod_id = '" . (int)$prod_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installProd(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."prod'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "prod` (`prod_id` int(11) NOT NULL,`prod_cat_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`bimage` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "prod_description` (`prod_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL, `image` VARCHAR(255) NOT NULL,`opt` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,`description` text NOT NULL,`description2` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "prod` ADD PRIMARY KEY (`prod_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "prod_description` ADD PRIMARY KEY (`prod_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "prod` MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT;";
		
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}
	
	
	/*
	public function editProdSetting(string $code , array $data, int $store_id = 0): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = 'ctrl_prod', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = 'ctrl_prod', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value)) . "', `serialized` = '1'");
				}
			}
		}
	}
	
	public function getProdSetting(string $code, int $store_id = 0): array {
		$setting_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$setting_data[$result['key']] = $result['value'];
			} else {
				$setting_data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $setting_data;
	}
	*/
	
	public function getProdSeoUrls(int $prod_id): array {
		$prod_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'prod_id' AND `value` = '" . (int)$prod_id . "'");

		foreach ($query->rows as $result) {
			$prod_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $prod_seo_url_data;
	}

}