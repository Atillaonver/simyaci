<?php
namespace Opencart\Catalog\Controller\Error;
class Exception extends \Opencart\System\Engine\Controller {
	public function index(string $message, string $code, string $file, string $line): void {
		$this->load->language('error/exception');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('error/exception', 'user_token=' . $this->session->data['user_token'])
		];

		
		$data['column_left'] = $this->load->controller('common/column_left');
		$hData = [
					'ctrl'   => 'not_found',
					'route'   => 'error/not_found',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
		$data['byFooter'] = $this->load->controller('bytao/footer',$hData);
		$data['byHeader'] = $this->load->controller('bytao/header',$hData);
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('error/exception', $data));
	}
}