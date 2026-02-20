<?php
namespace Opencart\Admin\Controller\Extension\OcbgTurnstile\Captcha;
/**
 * Class Turnstile
 *
 * @package Opencart\Admin\Controller\Extension\OcbgTurnstile\Captcha
 */
class Turnstile extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/ocbg_turnstile/captcha/turnstile');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=captcha')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ocbg_turnstile/captcha/turnstile', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/ocbg_turnstile/captcha/turnstile.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=captcha');

        $data['captcha_turnstile_key'] = $this->config->get('captcha_turnstile_key');
        $data['captcha_turnstile_secret'] = $this->config->get('captcha_turnstile_secret');
		$data['captcha_turnstile_status'] = $this->config->get('captcha_turnstile_status');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/ocbg_turnstile/captcha/turnstile', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/ocbg_turnstile/captcha/turnstile');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/ocbg_turnstile/captcha/turnstile')) {
			$json['error'] = $this->language->get('error_permission');
		}

        if (!$this->request->post['captcha_turnstile_key']) {
            $json['error']['key'] = $this->language->get('error_key');
        }

        if (!$this->request->post['captcha_turnstile_secret']) {
            $json['error']['secret'] = $this->language->get('error_secret');
        }

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('captcha_turnstile', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    /**
     * Install
     *
     * @return void
     */
    public function install(): void {

        @mail('info@opencartbulgaria.com', 'Cloudflare Turnstile Captcha installed (v0.0.1)', HTTP_CATALOG . ' - ' . $this->config->get('config_name') . "\r\n" . 'version - ' . VERSION . "\r\n" . 'IP - ' . $this->request->server['REMOTE_ADDR'], 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . $this->config->get('config_owner') . ' <' . $this->config->get('config_email') . '>' . "\r\n");

    }
}
