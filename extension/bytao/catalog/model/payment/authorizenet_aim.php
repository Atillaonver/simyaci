<?php
namespace Opencart\Catalog\Model\Extension\Bytao\Payment;	
class AuthorizenetAim extends \Opencart\System\Engine\Model {	

	public function getMethods(array $address = []): array {
		$this->load->language('extension/bytao/payment/authorizenet_aim');
		
		if ($this->cart->hasSubscription()) {
			$status = false;
		} elseif (!$this->config->get('config_checkout_payment_address')) {
			$status = true;
		} elseif (!$this->config->get('payment_authorizenet_aim_geo_zone_id')) {
			$status = true;
		} else {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('payment_authorizenet_aim_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

			if ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		}

		$method_data = [];

		if ($status) {
			$option_data['authorizenet_aim'] = [
				'code' => 'authorizenet_aim.authorizenet_aim',
				'name' => $this->language->get('heading_title')
			];

			$method_data = [
				'code'       => 'authorizenet_aim',
				'title'      => $this->language->get('heading_title'),
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_authorizenet_aim_sort_order')
			];
		}

		return $method_data;
	}
}