<?php
namespace Opencart\Admin\Model\Bytao;
class Css extends \Opencart\System\Engine\Model {
	
	public function addCss(array $data): int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "css SET css = '" . $this->db->escape($data['css']) . "', store_id = '" . (int)$this->session->data['store_id']. "', type = '" . (int)$data['type']. "',  version = '1'");
		
		return $this->db->getLastId();
	}
	
	public function updateCss(int $css_id,array $data):void {
		$this->db->query("UPDATE " . DB_PREFIX . "css SET css = '" .$this->db->escape( $data['css'] ) . "',  version = version + 1 WHERE css_id = '" . (int)$css_id. "'");
	}
	
	public function getCss(int $css_id ): array {
		if($css_id){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->session->data['store_id']. "' AND css_id=".(int)$css_id);
		}else{
			return false;
		}
		
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getCssByType(int $type):array {
		$query = $this->db->query("SELECT css,version,type FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->session->data['store_id']. "' AND type=".(int)$type);
		return isset($query->row)?$query->row:[];
	}
	
	public function getCssVersion(int $css_id):int {
		$query = $this->db->query("SELECT version FROM " . DB_PREFIX . "css WHERE css_id = '" . (int)$css_id. "' LIMIT 1");
			
		return isset($query->row['version'])?$query->row['version']:0;
	}
	
	public function getAllCss():array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->session->data['store_id']. "'");
		
		return isset($query->rows)?$query->rows:[];
	}
	
	public function installCss():bool{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."css'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			$sql[]  = "	
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."css` (
				  `css_id` int(11) NOT NULL AUTO_INCREMENT,
				  `store_id` int(3) NOT NULL,
				  `type` int(3) NOT NULL,
				  `css` longtext NOT NULL,
				  `version` int(11) NOT NULL,
				  `sort_order` int(2) NOT NULL
				  PRIMARY KEY (`css_id`)
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