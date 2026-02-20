<?php
namespace Opencart\Admin\Model\Bytao;
class Jqscript extends \Opencart\System\Engine\Model {
	
	public function addJqscript(array $data): int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "jqscript SET jqscript = '" . $this->db->escape($data['jqscript']) . "', store_id = '" . (int)$this->session->data['store_id']. "', type = '" . $this->db->escape($data['type']). "',  version = '1'");
		
		return $this->db->getLastId();
	}
	
	public function updateJqscript(int $jqscript_id,array $data):void {
		$this->db->query("UPDATE " . DB_PREFIX . "jqscript SET jqscript = '" .$this->db->escape($data['jqscript']) . "',type = '" .$this->db->escape( $data['type'] ) . "',  version = version + 1 WHERE jqscript_id = '" . (int)$jqscript_id. "'");
	}
	
	public function getJqscript(int $jqscript_id ): array {
		if($jqscript_id){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$this->session->data['store_id']. "' AND jqscript_id=".(int)$jqscript_id);
		}else{
			return false;
		}
		
		return isset($query->rows)?$query->rows:array();
	}
	
	public function getJqscriptByType(int $type):array {
		$query = $this->db->query("SELECT jqscript,version,type FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$this->session->data['store_id']. "' AND type=".(int)$type);
		return isset($query->row)?$query->row:'';
	}
	
	public function getJqscriptVersion(int $jqscript_id):int {
		$query = $this->db->query("SELECT version FROM " . DB_PREFIX . "jqscript WHERE jqscript_id = '" . (int)$jqscript_id. "' LIMIT 1");
			
		return isset($query->row['version'])?$query->row['version']:0;
	}
	
	public function getAllJqscript():array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$this->session->data['store_id']. "'");
		
		return isset($query->rows)?$query->rows:array();
	}
	
	
	public function installJqscript():bool{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."jqscript'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			$sql[]  = "	
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."jqscript` (
				  `jqscript_id` int(11) NOT NULL AUTO_INCREMENT,
				  `store_id` int(3) NOT NULL,
				  `type` int(3) NOT NULL,
				  `jqscript` longtext NOT NULL,
				  `version` int(11) NOT NULL,
				  `sort_order` int(2) NOT NULL,
				  PRIMARY KEY (`jqscript_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			";
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}
		return true;
	}
	
}

?>