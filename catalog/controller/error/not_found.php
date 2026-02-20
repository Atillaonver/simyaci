<?php
namespace Opencart\Catalog\Controller\Error;
class NotFound extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('error/not_found');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		if (isset($this->request->get['route'])) {
			$url_data = $this->request->get;

			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url .= '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($route, $url)
			];
		}

		$data['continue'] = $this->url->home();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
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
		if (!defined('EXP_URL')) {
			define("EXP_URL", '404.html');
		}
							
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

		$this->response->setOutput($this->load->view('error/not_found', $data));
	}
}