<?php
namespace Opencart\Catalog\Controller\Account;
class Logout extends \Opencart\System\Engine\Controller {
	public function index(): void {
		if ($this->customer->isLogged()) {
			$this->customer->logout();

			unset($this->session->data['customer']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['customer_token']);

			$this->response->redirect($this->url->link('account/logout', 'language=' . $this->config->get('config_language')));
		}

		$this->load->language('account/logout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_logout'),
			'href' => $this->url->link('account/logout', 'language=' . $this->config->get('config_language'))
		];

		$data['continue'] = $this->url->home();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');

		$cHead = $this->config->get('config_store_head');
		if((int)$cHead){
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
		} else {
			$hData = [
					'ctrl'   => 'logout',
					'route'   => 'account/logout',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
		}

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
