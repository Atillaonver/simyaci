<?php
namespace Opencart\Catalog\Model\Bytao;
class Css extends \Opencart\System\Engine\Model {	
	public function getCss(int $type):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->config->get('config_store_id'). "' AND type='".(int)$type."' LIMIT 1");
		
		return isset($query->row['css_id'])?$query->row:[];
	}
	
	public function getCssVersion(int $css_id):int {
		$query = $this->db->query("SELECT version FROM " . DB_PREFIX . "css WHERE css_id = '" . (int)$css_id. "' LIMIT 1");
			
		return isset($query->row['version'])?$query->row['version']:0;
	}
	
	public function getCssByStore():array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->config->get('config_store_id'). "' ORDER BY type ASC");
			
		return isset($query->rows)?$query->rows:[];
	}
	
}