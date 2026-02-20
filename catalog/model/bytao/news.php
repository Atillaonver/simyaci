<?php
namespace Opencart\Catalog\Model\Bytao;
class News extends \Opencart\System\Engine\Model {

	public function getNews($news_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id) WHERE i.news_id = '" . (int)$news_id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
	}
	
	public function getLastNews() {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order ASC LIMIT 3;");

		return $query->row;
	}

	public function getNewses(array $data = []) {
		
		$sql = "SELECT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id)  WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' ORDER BY i.sort_order, LCASE(id.title) ASC" ;
		
		if(!isset($data['limit'])){
			//$data['limit'] = 8;
		}
		if(!isset($data['page'])){
			//$sql .= " LIMIT " . (int)$data['page'] . (int)isset($data['limit'])? "," .$data['limit']:'';
		}
	
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getNewsImages($news_id) {
		$news_image_data = array();

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
			$news_image_description_data = array();

			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND news_id = '" . (int)$news_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1 ");

			
			$news_image_data[] = array(
				'image_title' => isset($news_image_description_query->row['title'])?$news_image_description_query->row['title']:'',
				'image_subtitle' => isset($news_image_description_query->row['subtitle'])?$news_image_description_query->row['subtitle']:'',
				'image_description' => isset($news_image_description_query->row['description'])?$news_image_description_query->row['description']:'',
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
	
	public function getNewsImage($news_id,$type=0) {
		$news_image_data = array();

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "'  AND type='".(int)$type."' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
		
			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND news_id = '" . (int)$news_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1 ");
			$news_image_data[] = array(
				'image_title' => isset($news_image_description_query->row['title'])?$news_image_description_query->row['title']:'',
				'image_subtitle' => isset($news_image_description_query->row['subtitle'])?$news_image_description_query->row['subtitle']:'',
				'image_description' => isset($news_image_description_query->row['description'])?$news_image_description_query->row['description']:'',
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
	
	
	
	public function getNewssSitemap() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1' AND i.type_id = '0' ORDER BY i.sort_order, LCASE(id.title) ASC");

		return $query->rows;
	}

	public function getNewsLayoutId($news_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getTotalNewses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news i   WHERE i.store_id='".(int)$this->config->get('config_store_id') ."'");

		return $query->row['total'];
	}

}