<?php
namespace Opencart\Catalog\Model\Extension\bytao\Shipping;
class Daytwo extends \Opencart\System\Engine\Model {
	
	function getQuote($address) {
		$this->load->language('extension/bytao/shipping/daytwo');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_daytwo_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_daytwo_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['daytwo'] = [
				'code'         => 'daytwo.daytwo',
				'name'         => $this->language->get('text_description'),
				'cost'         => $this->config->get('shipping_daytwo_cost'),
				'tax_class_id' => $this->config->get('shipping_daytwo_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate((float)$this->config->get('shipping_daytwo_cost'), $this->config->get('shipping_daytwo_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'daytwo',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_daytwo_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}