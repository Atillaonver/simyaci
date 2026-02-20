<?php
namespace Opencart\Admin\Model\Bytao;
class Script extends \Opencart\System\Engine\Model {
	
	public function editScript(int $key, array $script):int 
	{
		$store_id = $this->session->data['store_id']?$this->session->data['store_id']:0;
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "script WHERE store_id ='".(int)$store_id."' AND layout_id = '" . (int)$key . "'");
			$sql = "INSERT INTO " . DB_PREFIX . "script SET layout_id = '" . (int)$key . "'";
			if(isset($script['head'])) {$sql .= ",head = '" . $this->db->escape($script['head']) . "'";}
			if(isset($script['body_header'])){$sql .= ",body_header = '" . $this->db->escape($script['body_header']) . "'";}
			if(isset($script['body_footer'])){$sql .= ",body_footer = '" . $this->db->escape($script['body_footer']) . "'";}
			if(isset($script['position'])){$sql .= ",position = '" . $this->db->escape($script['position']) . "'";}
			$sql .= ",store_id ='".(int)$store_id ."'";
			
		if((isset($script['head'])) or (isset($script['body_header']) ) or (isset($script['body_footer']))){
			$this->db->query($sql);
		}
		return $key;
	}
	
	public function deleteScript(int $key):void 
	{
		$store_id = $this->session->data['store_id']?$this->session->data['store_id']:0;
		
		if($key){
			$this->db->query("DELETE FROM " . DB_PREFIX . "script WHERE store_id ='".(int)$store_id ."' AND layout_id = '" . $key . "'");
		}
	}
	
	public function getScripts(): array {
		$store_id = $this->session->data['store_id']?$this->session->data['store_id']:0;
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "script WHERE store_id ='".(int)$store_id."' ORDER BY layout_id ASC");

		return isset($query->rows)?$query->rows:[];
	}
	
	
	
	
	public function installScript():bool {
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."script'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "script` (`script_id` int(11) NOT NULL,`store_id` int(3) NOT NULL,`layout_id` int(3) NOT NULL,`head` longtext NOT NULL,`body_header` longtext NOT NULL,`body_footer` longtext NOT NULL,`version` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "script` ADD PRIMARY KEY (`script_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "script`  MODIFY `script_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}
		return true;
	}

}