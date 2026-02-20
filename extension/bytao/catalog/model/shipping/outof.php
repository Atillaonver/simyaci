<?php
namespace Opencart\Catalog\Model\Extension\bytao\Shipping;
class Outof extends \Opencart\System\Engine\Model {
	function getQuote($address) {
		$this->load->language('extension/bytao/shipping/outof');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_outof_geo_zone_id') . "' AND country_id  != '" . (int)$address['country_id'] . "' AND (zone_id != '" . (int)$address['zone_id'] . "')");
		

		if (!$this->config->get('shipping_outof_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];
			
			$cost = '';
			$costtotal = $this->cart->getSubTotal() ;
			
			$rates = explode(',', $this->config->get('shipping_outof_cost'));
			foreach ($rates as $rate) {
					$data = explode(':', $rate);
					if ($data[0] <= $costtotal) {
						$cost = $data[1];
						}
			}

			$quote_data['outof'] = [
				'code'         => 'outof.outof',
				'name'         => $this->language->get('text_description'),
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('shipping_outof_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate((float)$cost, $this->config->get('shipping_outof_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'outof',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_outof_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}