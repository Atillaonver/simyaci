<?php
namespace Opencart\Catalog\Model\Bytao;
class Library extends \Opencart\System\Engine\Model {
	
	public function getLibrary(int $library_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_description` id ON (i.`library_id` = id.`library_id`) LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE i.`library_id` = '" . (int)$library_id . "' AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'");
		
		return $query->row;
	}
	
	public function getLibraryTags(): array {
		$query = $this->db->query("SELECT DISTINCT id.meta_keyword FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_description` id ON (i.`library_id` = id.`library_id`) LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'");
		
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getLibrarys(array $data=[]): array {
		
		$SQL ="SELECT * FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_description` id ON (i.`library_id` = id.`library_id`) LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1' ";
		
		if(isset($data['keyword'])&&$data['keyword']!=''){
			$SQL .=" AND (id.title LIKE '%". $this->db->escape($data['keyword']) . "%' or LCASE(id.`title`) LIKE '%". $this->db->escape($data['keyword']) . "%')";
		}
		
		if(isset($data['tag_word'])&&$data['tag_word']!=''){
			$SQL .=" AND (id.meta_keyword LIKE '".$this->db->escape('%' . $data['tag_word'] . '%') . "' or LCASE(id.`meta_keyword`) LIKE '". $this->db->escape('%' . $data['tag_word'] . '%') . "')";
		}
		
		
		$SQL .=" ORDER BY id.`title` ASC ";
		if(isset($data['limit'])){
			$SQL .=" LIMIT ";
			if(isset($data['start'])){
				$SQL .=(int)$data['start'].',';
			}
			$SQL .=(int)$data['limit'];
		}
		
		
		$query = $this->db->query($SQL);

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getTotalLibrarys(array $data=[]): int {
		$SQL ="SELECT COUNT(*) as total FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_description` id ON (i.`library_id` = id.`library_id`) LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE i2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'";
		if(isset($data['keyword'])&&$data['keyword']!=''){
			$SQL .=" AND (id.title like '%". $this->db->escape($data['keyword']) . "%' or LCASE(id.`title`)like '%". $this->db->escape($data['keyword']) . "%')";
		}
		
		if(isset($data['tag_word'])&& $data['tag_word']!=''){
			$SQL .=" AND (id.meta_keyword LIKE '".$this->db->escape('%' . $data['tag_word'] . '%') . "' or LCASE(id.`meta_keyword`) LIKE '". $this->db->escape('%' . $data['tag_word'] . '%') . "')";
		}
		
		$query = $this->db->query($SQL);
		
		return $query->row['total'];
	}

	public function getLayoutId(int $library_id): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "library_to_layout` WHERE `library_id` = '" . (int)$library_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
}
