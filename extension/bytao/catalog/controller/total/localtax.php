<?php
namespace Opencart\Catalog\Controller\Extension\Bytao\Total;
class Localtax extends \Opencart\System\Engine\Controller {
	public function index(): string {
		if ($this->config->get('total_localtax_status')) {
			$this->load->language('extension/bytao/total/localtax');

			$data['save'] = $this->url->link('extension/bytao/total/localtax.save', 'language=' . $this->config->get('config_language'), true);
			$data['list'] = $this->url->link('checkout/cart.list', 'language=' . $this->config->get('config_language'), true);

			if (isset($this->session->data['localtax'])) {
				$data['localtax'] = $this->session->data['localtax'];
			} else {
				$data['localtax'] = '';
			}

			return $this->load->view('extension/bytao/total/localtax', $data);
		}

		return '';
	}

	public function save(): void {
		$this->load->language('extension/opencart/total/localtax');

		$json = [];

		if (isset($this->request->post['coupon'])) {
			$coupon = $this->request->post['coupon'];
		} else {
			$coupon = '';
		}

		if (!$this->config->get('total_localtax_status')) {
			$json['error'] = $this->language->get('error_status');
		}

		if ($coupon) {
			$this->load->model('marketing/localtax');

			$localtax_info = $this->model_marketing_coupon->getCoupon($coupon);

			if (!$localtax_info) {
				$json['error'] = $this->language->get('error_coupon');
			}
		}

		if (!$json) {
			if ($coupon) {
				$json['success'] = $this->language->get('text_success');

				$this->session->data['coupon'] = $coupon;
			} else {
				$json['success'] = $this->language->get('text_remove');

				unset($this->session->data['coupon']);
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
