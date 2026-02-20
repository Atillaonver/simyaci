<?php
namespace Opencart\Admin\Controller\Extension\bytao\Payment;
class AuthorizenetAim extends \Opencart\System\Engine\Controller {
	public function index(): void {		
		$this->load->language('extension/bytao/payment/authorizenet_aim');
		
		$data['save'] = $this->url->link('extension/bytao/payment/authorizenet_aim.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');
		
		$data['store_id'] = $store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0; 
		$setting = $this->model_setting_setting->getSetting('payment_authorizenet_aim', $store_id);
		
		$data['payment_authorizenet_aim_login']  = isset($setting['payment_authorizenet_aim_login'])?$setting['payment_authorizenet_aim_login']:'';
		
		$data['payment_authorizenet_aim_key'] = isset($setting['payment_authorizenet_aim_key'])?$setting['payment_authorizenet_aim_key']:'';
		
		$data['payment_authorizenet_aim_hash'] = isset($setting['payment_authorizenet_aim_hash'])?$setting['payment_authorizenet_aim_hash']:'';
		
		$data['payment_authorizenet_aim_server'] = isset($setting['payment_authorizenet_aim_server'])?$setting['payment_authorizenet_aim_server']:'';
		

		$data['payment_authorizenet_aim_mode'] = isset($setting['payment_authorizenet_aim_mode'])?$setting['payment_authorizenet_aim_mode']:'';
		
		$data['payment_authorizenet_aim_method'] = isset($setting['payment_authorizenet_aim_method'])?$setting['payment_authorizenet_aim_method']:'';
		
		$data['payment_authorizenet_aim_total'] = isset($setting['payment_authorizenet_aim_total'])?$setting['payment_authorizenet_aim_total']:'';

		$data['payment_authorizenet_aim_order_status_id'] = isset($setting['payment_authorizenet_aim_order_status_id'])?$setting['payment_authorizenet_aim_order_status_id']:'';
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['payment_authorizenet_aim_geo_zone_id'] = isset($setting['payment_authorizenet_aim_geo_zone_id'])?$setting['payment_authorizenet_aim_geo_zone_id']:'';

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['payment_authorizenet_aim_status'] = isset($setting['payment_authorizenet_aim_status'])?$setting['payment_authorizenet_aim_status']:'';
		$data['payment_authorizenet_aim_sort_order'] = isset($setting['payment_authorizenet_aim_sort_order'])?$setting['payment_authorizenet_aim_sort_order']:'';
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/bytao/payment/authorizenet_aim', $data));
	}

	public function save(): void {
		$this->load->language('extension/bytao/payment/authorizenet_aim');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0; 
		
		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bytao/payment/authorizenet_aim')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		
		if (empty($this->request->post['payment_authorizenet_aim_login'])) {
				$json['error']['login'] = $this->language->get('error_login');
		}
		
		if (empty($this->request->post['payment_authorizenet_aim_key'])) {
				$json['error']['key'] = $this->language->get('error_key');
		}
		
		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('payment_authorizenet_aim', $this->request->post,$store_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
