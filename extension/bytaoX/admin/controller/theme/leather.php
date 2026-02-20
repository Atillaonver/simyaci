<?php
namespace Opencart\Admin\Controller\Extension\Bytao\Theme;
class  Leather extends \Opencart\System\Engine\Controller {
	public function index(): void {	
		$this->load->language('extension/bytao/theme/leather');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

	
		$data['save'] = $this->url->link('extension/bytao/theme/leather.save', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme');

		$data['theme_leather_status'] = $this->config->get('theme_leather_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/bytao/theme/leather', $data));
	}

	public function save(): void {
		$this->load->language('extension/bytao/theme/leather');

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/bytao/theme/leather')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('theme_leather', $this->request->post, $store_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/theme')) {
			// Add startup to catalog
			$startup_data = [
				'code'        => 'leather',
				'description' => 'byTAO Aston theme extension',
				'action'      => 'catalog/extension/bytao/startup/leather',
				'status'      => 1,
				'sort_order'  => 2
			];

			// Add startup for admin
			$this->load->model('setting/startup');
			$this->model_setting_startup->addStartup($startup_data);
		}
	}

	public function uninstall(): void {
		if ($this->user->hasPermission('modify', 'extension/theme')) {
			$this->load->model('setting/startup');
			$this->model_setting_startup->deleteStartupByCode('leather');
		}
	}
}