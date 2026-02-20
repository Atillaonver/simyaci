<?php
namespace Opencart\Admin\Model\Extension\Opencart\Report;
class Activity extends \Opencart\System\Engine\Model {
	public function getActivities(): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$query = $this->db->query("SELECT ca.`key`, ca.`data`, ca.`date_added` FROM `" . DB_PREFIX . "customer_activity` ca LEFT JOIN `" . DB_PREFIX . "customer` c ON c.customer_id = ca.customer_id WHERE c.store_id ='".(int)$store_id."'  ORDER BY `date_added` DESC LIMIT 0,5");

		return isset($query->rows)?$query->rows:[];
	}
}
