<?php
namespace Opencart\Catalog\Model\Extension\bytao\Shipping;
class Aday extends \Opencart\System\Engine\Model {
	
	function getQuote($address) {
		
		$this->load->language('extension/bytao/shipping/aday');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_aday_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		
		
		if (!$this->config->get('shipping_aday_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['aday'] = [
				'code'         => 'aday.aday',
				'name'         => $this->language->get('text_description'),
				'cost'         => $this->config->get('shipping_aday_cost'),
				'tax_class_id' => $this->config->get('shipping_aday_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('shipping_aday_cost'), $this->config->get('shipping_aday_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'aday',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_aday_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}