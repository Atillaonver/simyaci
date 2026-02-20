<?php
namespace Opencart\Admin\Model\Bytao;
class Expenses extends \Opencart\System\Engine\Model{
	
	public function getExpenses():void {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "footer WHERE store_id='".(int)$this->session->data['store_id']."'");
		
	}
	
}