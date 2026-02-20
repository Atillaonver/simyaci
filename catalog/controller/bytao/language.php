<?php
namespace Opencart\Catalog\Controller\Bytao;
class Language extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('common/language');

		$url_data = $this->request->get;

		if (isset($url_data['route'])) {
			$route = $url_data['route'];
		} else {
			$route = $this->config->get('action_default');
		}

		unset($url_data['route']);
		unset($url_data['_route_']);
		unset($url_data['language']);
		
		$url = '';
		if ($url_data) {
			$url .= '&' . urldecode(http_build_query($url_data));
		}
		

		$data['languages'] = [];

		$this->load->model('bytao/common');
		$results = $this->model_bytao_common->getStoreLanguages();

		foreach ($results as $result) {
			$data['languages'][] = [
				'name'  => $result['name'],
				'route'  => $route,
				'code'  => $result['code'],
				'image' => $result['image'],
				'href'  => $this->url->link($route, 'language=' . $result['code'].$url, FALSE,FALSE)
			];
		}

		$data['code'] = $this->config->get('config_language');

		return $this->load->view('bytao/language', $data);
	}
}