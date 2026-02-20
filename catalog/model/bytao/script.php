<?php
namespace Opencart\Catalog\Model\Bytao;
class Script extends \Opencart\System\Engine\Model {	
	
	public function getScript($part,$layoutID):string  {
			$sql="SELECT ".$part." FROM " . DB_PREFIX . "script WHERE store_id ='".(int)$this->config->get('config_store_id') ."' AND layout_id='" . (int)$layoutID . "' LIMIT 1";
			$query = $this->db->query($sql);
		
		return isset($query->row[$part])?$query->row[$part]:'';
	}

	public function getScriptLayoutId($qRoute):int  {
		
			$query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout_route  WHERE store_id ='".(int)$this->config->get('config_store_id') ."' AND route='" . $this->db->escape($qRoute) . "' LIMIT 1");
			
		
		return isset($query->row['layout_id'])?$query->row['layout_id']:0;
	}
	
}