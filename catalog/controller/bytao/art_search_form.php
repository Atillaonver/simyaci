<?php
namespace Opencart\Catalog\Controller\Bytao;
class ArtSearchForm extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

		$data['language'] = $this->config->get('config_language');
		$data['url_search'] = $this->url->link('bytao/art_search', 'language=' . $this->config->get('config_language'));

		return $this->load->view('bytao/art_search_form', $data);
	}
}