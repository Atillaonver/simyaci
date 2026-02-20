<?php
namespace Opencart\Admin\Model\Bytao;
class Dashboard extends \Opencart\System\Engine\Model {
	public function getViewed(): array {
		
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT pv.`product_id`, p.`viewed`, p.`model`, pd.`name`, p.`image` FROM `" . DB_PREFIX . "product_viewed` pv LEFT JOIN `" . DB_PREFIX . "product` p ON (pv.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (pv.product_id = p2s.product_id) WHERE p2s.store_id='".(int)$store_id ."' ORDER BY pv.`viewed` DESC LIMIT 0,5");
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getPurchased(): array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT op.`name`, op.`model`,p.`image`,op.`model`, SUM(op.`quantity`) AS quantity,COUNT(op.`product_id`) AS counter, SUM((op.`price` + op.`tax`) * op.`quantity`) AS `total` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.`order_id` = o.`order_id`) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.`product_id` = p.`product_id`) WHERE o.store_id='".(int)$store_id ."' AND o.`order_status_id` > '0' GROUP BY op.`product_id` ORDER BY counter DESC LIMIT 0,5" ;
		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}
	
}