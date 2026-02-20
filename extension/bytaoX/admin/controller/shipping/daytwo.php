<?php
namespace Opencart\Admin\Controller\Extension\bytao\Shipping;
class Daytwo extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/bytao/shipping/daytwo');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

	
		$data['save'] = $this->url->link('extension/bytao/shipping/daytwo.save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping');

		$data['shipping_daytwo_cost'] = $this->config->get('shipping_daytwo_cost');
		$data['shipping_daytwo_tax_class_id'] = $this->config->get('shipping_daytwo_tax_class_id');
		$data['shipping_daytwo_geo_zone_id'] = $this->config->get('shipping_daytwo_geo_zone_id');
		$data['shipping_daytwo_status'] = $this->config->get('shipping_daytwo_status');
		$data['shipping_daytwo_sort_order'] = $this->config->get('shipping_daytwo_sort_order');

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/bytao/shipping/daytwo', $data));
	}
	
	public function save(): void {
		$this->load->language('extension/bytao/shipping/daytwo');

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bytao/shipping/daytwo')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('shipping_daytwo', $this->request->post, $store_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}