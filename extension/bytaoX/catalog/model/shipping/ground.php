<?php
namespace Opencart\Catalog\Model\Extension\Bytao\Shipping;
class Ground extends \Opencart\System\Engine\Model {
	function getQuote(array $address): array {
		$this->load->language('extension/bytao/shipping/ground');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('shipping_ground_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('shipping_ground_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if ($this->cart->getSubTotal() < $this->config->get('shipping_ground_cost')) {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];
			$cost = 0.00;
			if ($this->cart->getSubTotal() < $this->config->get('shipping_ground_free_total') ) {
				$cost = $this->config->get('shipping_ground_free_cost');//8.95 ;
			}
			
			$quote_data['ground'] = [
				'code'         => 'ground.ground',
				'name'         => $cost?$this->language->get('text_description'):$this->language->get('text_free'),
				'cost'         => $cost,
				'tax_class_id' => 0,
				'text'         => $this->currency->format($cost, $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'ground',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_ground_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}
