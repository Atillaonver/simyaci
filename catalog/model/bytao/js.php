<?php
namespace Opencart\Catalog\Model\Bytao;
class Jqscript extends \Opencart\System\Engine\Model {	
	public function getJqscript(int $type):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$this->config->get('config_store_id'). "' AND type='".(int)$type."' LIMIT 1");
		
		return isset($query->row['css_id'])?$query->row:[];
	}
	
	public function getJqscriptVersion(int $jqscript_id):int {
		$query = $this->db->query("SELECT version FROM " . DB_PREFIX . "jqscript WHERE jqscript_id = '" . (int)$jqscript_id. "' LIMIT 1");
			
		return isset($query->row['version'])?$query->row['version']:0;
	}
	
	public function getJqscriptByStore():array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$this->config->get('config_store_id'). "' ORDER BY type ASC");
			
		return isset($query->rows)?$query->rows:[];
	}
	
	
}