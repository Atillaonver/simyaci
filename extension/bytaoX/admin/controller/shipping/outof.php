<?php
namespace Opencart\Admin\Controller\Extension\bytao\Shipping;
class Outof extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/bytao/shipping/outof');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

	
		$data['save'] = $this->url->link('extension/bytao/shipping/outof.save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping');

		$data['shipping_outof_cost'] = $this->config->get('shipping_outof_cost');
		$data['shipping_outof_tax_class_id'] = $this->config->get('shipping_outof_tax_class_id');
		$data['shipping_outof_geo_zone_id'] = $this->config->get('shipping_outof_geo_zone_id');
		$data['shipping_outof_status'] = $this->config->get('shipping_outof_status');
		$data['shipping_outof_sort_order'] = $this->config->get('shipping_outof_sort_order');
		
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/bytao/shipping/outof', $data));
	}
	
	public function save(): void {
		$this->load->language('extension/bytao/shipping/outof');

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bytao/shipping/outof')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('shipping_outof', $this->request->post, $store_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}