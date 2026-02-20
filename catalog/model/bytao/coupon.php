<?php
namespace Opencart\Catalog\Model\Bytao;
class Coupon extends \Opencart\System\Engine\Model {
	
	public function getCouponById($coupon_id,$email=''){
		$status = true;
		$coupon_product_data = [];
		//$SQL = "SELECT c.* FROM `" . DB_PREFIX . "coupon` c LEFT JOIN `" . DB_PREFIX . "coupon_to_store` c2s ON (c.coupon_id=c2s.coupon_id) WHERE coupon_id = '" . (int)$coupon_id . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1' AND type_customer = '0' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$SQL = "SELECT c.* FROM `" . DB_PREFIX . "coupon` c LEFT JOIN `" . DB_PREFIX . "coupon_to_store` c2s ON (c.coupon_id = c2s.coupon_id) WHERE c.coupon_id = '" . (int)$coupon_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$coupon_query = $this->db->query($SQL);

		if($coupon_query->num_rows){
			$date_start = $coupon_query->row['date_start'];
			$date_end   = $coupon_query->row['date_end'];
			return [
				'coupon_id'    => $coupon_query->row['coupon_id'],
				'code'         => $coupon_query->row['code'],
				'name'         => $coupon_query->row['name'],
				'type'         => $coupon_query->row['type'],
				'type_customer'=> $coupon_query->row['type_customer'],
				'discount'     => $coupon_query->row['discount'],
				'shipping'     => $coupon_query->row['shipping'],
				'total'        => $coupon_query->row['total'],
				'date_start'   => $date_start,
				'date_end'     => $date_end,
				'uses_total'   => $coupon_query->row['uses_total'],
				'uses_customer'=> $coupon_query->row['uses_customer'],
				'status'       => $coupon_query->row['status'],
				'date_added'   => $coupon_query->row['date_added'],
				'email'        => $email
			];
		}else{
			return [];
		}
	}

	public function getCouponByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
	
}