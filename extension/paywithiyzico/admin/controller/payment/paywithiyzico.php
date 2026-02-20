<?php

	namespace Opencart\Admin\Controller\Extension\paywithiyzico\Payment;

	class paywithiyzico extends \Opencart\System\Engine\Controller
	{

		private $module_version = '2.6.0';
		private $module_product_name = '2.6.0';
		private $store_ID = 0;
		private $store_info = [];	
		private $error = array();

		private $fields = array(
			array(
				'validateField' => 'blank',
				'name' => 'payment_paywithiyzico_status',
			),
			array(
				'validateField' => 'error_order_status',
				'name' => 'payment_paywithiyzico_order_status',
			),
			array(
				'validateField' => 'error_cancel_order_status',
				'name' => 'payment_paywithiyzico_order_cancel_status',
			)

		);

		protected function configGet($searched='')
		{
			//$this->log->write('ARRAY:'.print_r($this->store_info,TRUE));
			if ($this->store_info) {
				return isset($this->store_info[$searched])?$this->store_info[$searched]:'';
			} else {
				$this->load->model('setting/setting');
				$this->store_ID = isset($this->session->data['store_id'] )?$this->session->data['store_id'] :0;
				$this->store_info = $this->model_setting_setting->getSetting('payment_iyzico', $this->store_ID );

				$config = $this->model_setting_setting->getSetting('config', $this->store_ID );
				$this->store_info['config_url'] = $config['config_url'];

				$webhook_key = $this->model_setting_setting->getValue('webhook_iyzico_webhook_url_key', $this->store_ID );
				if ($webhook_key) {
					$this->store_info['webhook_iyzico_webhook_url_key'] = $webhook_key;
				}
				return isset($this->store_info[$searched])?$this->store_info[$searched]:'';
			}
		}


		public function install()
		{

			$this->load->model('extension/paywithiyzico/payment/paywithiyzico');
			$this->model_extension_paywithiyzico_payment_paywithiyzico->install();
		}

		public function uninstall()
		{

			$this->load->model('extension/paywithiyzico/payment/paywithiyzico');
			$this->model_extension_paywithiyzico_payment_paywithiyzico->uninstall();
		}

		public function index(): void
		{
			$this->store_ID = isset($this->session->data['store_id'] )?$this->session->data['store_id'] :0;
			$this->load->language('extension/paywithiyzico/payment/paywithiyzico');
			$this->load->model('setting/setting');
			$this->load->model('user/user');
			$this->load->model('extension/paywithiyzico/payment/paywithiyzico');

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$request = $this->requestIyzico($this->request->post, 'add', '');
				$this->model_setting_setting->editSetting('payment_paywithiyzico', $request, $this->store_ID);
				$this->response->redirect($this->url->link('extension/paywithiyzico/payment/paywithiyzico', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
			}

			/* Get Order Status */
			$this->load->model('localisation/order_status');
			$data['order_statuses']  = $this->model_localisation_order_status->getOrderStatuses();
			$data['cancel']          = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');
			$data['action']          = $this->url->link('extension/paywithiyzico/payment/paywithiyzico', 'user_token=' . $this->session->data['user_token'], true);
			$data['heading_title']   = $this->language->get('heading_title');
			$data['header']          = $this->load->controller('common/header');
			$data['column_left']     = $this->load->controller('common/column_left');
			$data['footer']          = $this->load->controller('common/footer');
			$data['locale']          = $this->language->get('code');
			$data['version']         = $this->module_version;
			$data['pwi_module_logo'] = $this->language->get('pwi_module_setting_logo');

			foreach ($this->fields as $key => $field) {
				if (isset($this->error[$field['validateField']])) {
					$data[$field['validateField']] = $this->error[$field['validateField']];
				} else {
					$data[$field['validateField']] = '';
				}

				if (isset($this->request->post[$field['name']])) {
					$data[$field['name']] = $this->request->post[$field['name']];
				} else {
					$data[$field['name']] = $this->configGet($field['name']);
				}
			}

			$this->response->setOutput($this->load->view('extension/paywithiyzico/payment/paywithiyzico', $data));
		}

		protected function validate()
		{
			if (!$this->user->hasPermission('modify', 'extension/paywithiyzico/payment/paywithiyzico')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}

			foreach ($this->fields as $key => $field) {
				if ($field['validateField'] != 'blank') {
					if (!$this->request->post[$field['name']]) {
						$this->error[$field['validateField']] = $this->language->get($field['validateField']);
					}
				}

			}

			return !$this->error;
		}

		public function requestIyzico($request, $method_type, $extra_request = false)
		{
			$request_modify = array();
			if ($method_type == 'add') {
				foreach ($this->fields as $key => $field) {
					if (isset($request[$field['name']])) {
						if ($field['name'] == 'payment_paywithiyzico_api_key' || $field['name'] == 'payment_paywithiyzico_secret_key')
							$request[$field['name']] = str_replace(' ', '', $request[$field['name']]);
						$request_modify[$field['name']] = $request[$field['name']];
					}
				}
			}

			return $request_modify;
		}


	}
