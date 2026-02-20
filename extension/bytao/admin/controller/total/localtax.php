<?php
namespace Opencart\Admin\Controller\Extension\bytao\Total;
class Localtax extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/bytao/total/localtax');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

	
		$data['save'] = $this->url->link('extension/bytao/total/localtax.save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total');

		
		$data['total_localtax_status'] = $this->config->get('total_localtax_status');
		$data['total_localtax_sort_order'] = $this->config->get('total_localtax_sort_order');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/bytao/total/localtax', $data));
	}
	
	public function save(): void {
		$this->load->language('extension/bytao/total/localtax');

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bytao/total/localtax')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('total_localtax', $this->request->post, $store_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}