<?php
namespace Opencart\Catalog\Model\Bytao;
class Box extends \Opencart\System\Engine\Model {
    
	public function getBoxes($box_row_id=0) {
			$SQL ="SELECT * FROM " . DB_PREFIX . "box WHERE store_id='".(int)$this->config->get('config_store_id') ."' AND box_row_id='".$box_row_id."' ORDER BY sort_order  ASC";
			$query = $this->db->query($SQL);
			//$this->log->write('Array:'.print_r($SQL,TRUE));
			return $query->rows;
	}
	
	public function getRows($box_row_id=0) {
		$groupId = $this->customer->getGroupId()?$this->customer->getGroupId():1;
		
		$sql="SELECT * FROM " . DB_PREFIX . "box_row WHERE ((start_date = '0000-00-00 00:00:00' OR start_date < NOW()) AND (end_date = '0000-00-00 00:00:00' OR end_date > NOW())) AND store_id='".(int)$this->config->get('config_store_id') ."' AND status='1' AND customer_group_id = '".$groupId."' ORDER BY sort_order  ASC";
		//$this->log->write('Log: '.$sql);
		$query = $this->db->query($sql);
		return $query->rows;
	}


}
