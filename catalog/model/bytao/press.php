<?php
namespace Opencart\Catalog\Model\Bytao;
class Press extends \Opencart\System\Engine\Model {

	public function getPress($press_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "press i LEFT JOIN " . DB_PREFIX . "press_description id ON (i.press_id = id.press_id) WHERE i.press_id = '" . (int)$press_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}
	
	public function getLastPress() {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "press i LEFT JOIN " . DB_PREFIX . "press_description id ON (i.press_id = id.press_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order ASC LIMIT 3;");

		return $query->row;
	}

	public function getPresses(array $data = []) {
		
		$sql = "SELECT *,pi.image FROM " . DB_PREFIX . "press i LEFT JOIN " . DB_PREFIX . "press_description id ON (i.press_id = id.press_id) LEFT JOIN " . DB_PREFIX . "press_image pi ON (i.press_id = pi.press_id)  WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' AND pi.type = '1' ORDER BY i.sort_order, LCASE(id.title) ASC  " ;
		
		if(isset($data['page'])){
			//$sql .= " LIMIT " . (int)$data['page'] .((int)isset($data['limit'])? "," .$data['limit']:'');
		}else{
			//$sql .= " LIMIT " . (int)isset($data['limit'])? $data['limit']:'8';
		}
		
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getPressImages($press_id) {
		$press_image_data = array();

		$press_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press_image WHERE press_id = '" . (int)$press_id . "' ORDER BY sort_order ASC");

		foreach ($press_image_query->rows as $press_image) {
			$press_image_description_data = array();

			$press_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press_image_description WHERE press_image_id = '" . (int)$press_image['press_image_id'] . "' AND press_id = '" . (int)$press_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1 ");

			
			$press_image_data[] = array(
				'image_title' => isset($press_image_description_query->row['title'])?$press_image_description_query->row['title']:'',
				'image_subtitle' => isset($press_image_description_query->row['subtitle'])?$press_image_description_query->row['subtitle']:'',
				'image_description' => isset($press_image_description_query->row['description'])?$press_image_description_query->row['description']:'',
				'type'                     => $press_image['type'],
				'image'                    => $press_image['image'],
				'sort_order'               => $press_image['sort_order']
			);
		}

		return $press_image_data;
	}
	
	public function getPressImage($press_id,$type=0) {
		$press_image_data = array();

		$press_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press_image WHERE press_id = '" . (int)$press_id . "'  AND type='".(int)$type."' ORDER BY sort_order ASC");

		foreach ($press_image_query->rows as $press_image) {
		
			$press_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press_image_description WHERE press_image_id = '" . (int)$press_image['press_image_id'] . "' AND press_id = '" . (int)$press_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1 ");
			$press_image_data[] = array(
				'image_title' => isset($press_image_description_query->row['title'])?$press_image_description_query->row['title']:'',
				'image_subtitle' => isset($press_image_description_query->row['subtitle'])?$press_image_description_query->row['subtitle']:'',
				'image_description' => isset($press_image_description_query->row['description'])?$press_image_description_query->row['description']:'',
				'type'                     => $press_image['type'],
				'image'                    => $press_image['image'],
				'sort_order'               => $press_image['sort_order']
			);
		}

		return $press_image_data;
	}
	
	public function getPressesSitemap() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press i LEFT JOIN " . DB_PREFIX . "press_description id ON (i.press_id = id.press_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' AND i.type_id = '0' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}

	public function getPressLayoutId($press_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "press_to_layout WHERE press_id = '" . (int)$press_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalPresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "press WHERE store_id='".(int)$this->config->get('config_store_id') ."'");

		return $query->row['total'];
	}

}