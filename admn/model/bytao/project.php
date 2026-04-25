<?php
namespace Opencart\Admin\Model\Bytao;
class Project extends \Opencart\System\Engine\Model {
	
	public function addProject($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "project SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',project_category_id = '" . (isset($data['project_category_id'])?$this->db->escape($data['project_category_id']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "',ref = '" . (isset($data['ref'])?$this->db->escape($data['ref']):'') . "'");

		$project_id = $this->db->getLastId();
		
		foreach ($data['project_description'] as $language_id => $project_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "project_description SET project_id = '" . (int)$project_id . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($project_description['name']) . "',title = '" .  $this->db->escape($project_description['title']) . "', description = '" .  $this->db->escape($project_description['description']) . "',description2 = '" .  $this->db->escape($project_description['description2']) . "',opt = '" . (isset($data['project_opt'][$language_id])?$this->db->escape(json_encode($data['project_opt'][$language_id])):'') . "',image = '" . (isset($project_description['image'])?$this->db->escape($project_description['image']):'') . "',`meta_title` = '" . $this->db->escape($project_description['meta_title']) . "', `meta_description` = '" . $this->db->escape($project_description['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($project_description['meta_keyword']) . "'");
		}

		if (isset($data['project_seo_url'])) {
			foreach ($data['project_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'project_id', `value` = '" . (int)$project_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
		$this->load->model('design/seo_url');

		if (isset($data['project_seo_url'])) {
			foreach ($data['project_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('project_id', $project_id, $keyword, $store_id, $language_id,0,'','bytao/project');
			}
		}
		
		return $project_id;
	}

	public function editProject($project_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "project SET status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',project_category_id = '" . (isset($data['project_category_id'])?$this->db->escape($data['project_category_id']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "',ref = '" . (isset($data['ref'])?$this->db->escape($data['ref']):'') . "' WHERE project_id = '" . (int)$project_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "project_description WHERE project_id = '" . (int)$project_id . "'");
		foreach ($data['project_description'] as $language_id => $project_description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "project_description SET project_id = '" . (int)$project_id . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($project_description['name']) . "',title = '" .  $this->db->escape($project_description['title']) . "', description = '" .  $this->db->escape($project_description['description']) . "',description2 = '" .  $this->db->escape($project_description['description2']) . "',opt = '" . (isset($data['project_opt'][$language_id])?$this->db->escape(json_encode($data['project_opt'][$language_id])):'') . "',image = '" . (isset($project_description['image'])?$this->db->escape($project_description['image']):'') . "',`meta_title` = '" . $this->db->escape($project_description['meta_title']) . "', `meta_description` = '" . $this->db->escape($project_description['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($project_description['meta_keyword']) . "'");
		}
		
		
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('project_id', $project_id);
		if (isset($data['project_seo_url'])) {
			foreach ($data['project_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('project_id', $project_id, $keyword, $store_id, $language_id,0,'','bytao/project');
			}
		}
		
		
		
	}

	public function deleteProject($project_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "project WHERE project_id = '" . (int)$project_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "project_description WHERE project_id = '" . (int)$project_id . "'");
	}

	public function getProject($project_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "project  WHERE project_id = '" . (int)$project_id . "'");

		return $query->row;
	}

	public function getProjects($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "project p LEFT JOIN " . DB_PREFIX . "project_description pd ON p.project_id=pd.project_id   WHERE pd.language_id= '".(int)$this->config->get('config_language_id')."' AND  p.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY pd.title ASC";


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
	
	public function getProjectDescriptions($project_id) {
		$project_description_data = array();
		$this->load->model('tool/image');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "project_description WHERE project_id = '" . (int)$project_id . "'");

		foreach ($query->rows as $result) {
			if (isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])) {
				$thumb= $this->model_tool_image->resize( $result['image'], 100, 100);
			} else{
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			$project_description_data[$result['language_id']] = array(
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

		return $project_description_data;
	}
		
	public function getTotalProjects() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "project WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");
		
		return $query->row['total'];
	}
	
	public function getProjectCatPath($project_cat_id):string {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "project_cat_description WHERE language_id= '".(int)$this->config->get('config_language_id')."' AND project_cat_id = '" . (int)$project_cat_id . "' LIMIT 1");
		
		return isset($query->row['name'])?$query->row['name']:'';
	}

	public function isProjectInstore($project_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "project WHERE project_id = '" . (int)$project_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installProject(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."project'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "project` (`project_id` int(11) NOT NULL,`project_cat_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`bimage` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "project_description` (`project_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL, `image` VARCHAR(255) NOT NULL,`opt` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,`description` text NOT NULL,`description2` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "project` ADD PRIMARY KEY (`project_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "project_description` ADD PRIMARY KEY (`project_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "project` MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;";
		
		
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