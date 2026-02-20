<?php
namespace Opencart\Catalog\Model\Design;
class SeoUrl extends \Opencart\System\Engine\Model {
	public function getSeoUrlByKeyword(string $keyword): array {
		$query = $this->db->query("SELECT su.*, l.code FROM  `" . DB_PREFIX . "seo_url` su LEFT JOIN `" . DB_PREFIX . "language` l ON (l.`language_id` = su.`language_id`) WHERE ( su.`keyword` = '" . $this->db->escape($keyword) . "' OR su.`keyword` LIKE '" . $this->db->escape('%/' . $keyword) . "') AND su.`store_id` = '" . (int)$this->config->get('config_store_id') . "' ");

		return isset($query->row)?$query->row:[];
	}

	public function getSeoUrlByKeyValue(string $key, string $value, int $language_id = 0): array {
		
		$language_id = $language_id <> 0 ?$language_id:(int)$this->config->get('config_language_id') ;
		
		if($key == 'path'){
			$SQL = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($key) . "' AND `value` LIKE '%" . $value . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$language_id . "' LIMIT 1";
		}else{
			
			$SQL = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($key) . "' AND `value` = '" . $this->db->escape($value) . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$language_id . "' ";

		}
		$query = $this->db->query($SQL);
		return  isset($query->row)?$query->row:[];
	}
	
	public function getSeoUrlByRoute(string $route): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($route) . "' AND `value` = '' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return  isset($query->row)?$query->row:[];
	}
	
	public function getSeoUrlByRouteLi(string $route,string $language_id): array {
		$query = $this->db->query("SELECT su.*, l.code FROM `" . DB_PREFIX . "seo_url` su  LEFT JOIN `" . DB_PREFIX . "language` l ON (l.`language_id` = su.`language_id`) WHERE su.`key` = 'route' AND su.`value` = '" . $this->db->escape($route) . "' AND su.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND su.`language_id` = '" . (int)$language_id . "'");
		
		return  isset($query->row)?$query->row:[];
	}

}
