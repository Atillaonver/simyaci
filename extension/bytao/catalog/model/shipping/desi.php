<?php
namespace Opencart\Catalog\Model\Extension\bytao\Shipping;
class Desi extends \Opencart\System\Engine\Model {

	function getQuote(array $address): array {
		$this->load->language('extension/bytao/shipping/desi');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('shipping_desi_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('shipping_desi_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		$method_data = [];

		if ($status) {

			$products = $this->cart->getProducts();
			$total_weight = 0;

			foreach ($products as $product) {
				$length = $product['length'];
				$width  = $product['width'];
				$height = $product['height'];
				$weight = $product['weight'];

				$desi = ($length * $width * $height) / 3000;
				$final_weight = ($desi > $weight) ? $desi : $weight;
				$total_weight += $final_weight * $product['quantity'];
			}

			// Admin panelden alınan JSON tablo
			$rates = [];
			$desik = $this->config->get('shipping_desi_cost');
			$_desi = explode('|',$desik);
			foreach ($_desi as  $parts) {
				$_parts = explode(':',$parts);
					$rates[$_parts[0]] = $_parts[1];
			}
			$cost = 0;
			
			foreach ($rates as $limit => $price) {
				if ($total_weight <= $limit) {
					$cost = $price;
					break;
				}
			}
			
			if ($cost == 0 and is_array($rates)) {
				$cost = end($rates); // en yüksek fiyat
			}

			$quote_data = [];
			
			$quote_data['desi'] = [
				'code'         => 'desi.desi',
				'name'         => $this->language->get('text_description'),
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('shipping_desi_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_desi_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			];

			$method_data = [
				'code'       => 'desi',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_desi_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}
