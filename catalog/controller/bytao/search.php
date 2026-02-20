<?php
namespace Opencart\Catalog\Controller\Bytao;
class Search extends \Opencart\System\Engine\Controller {
	public function index($hData): string  {
		$this->load->language('bytao/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}
		if(isset($hData['route']) && $hData['route']=='bytao/art'){
			$data['url_search'] = $this->url->link('bytao/art_search', '', 'SSL');	
		}
		
		
		$data['language'] = $this->config->get('config_language');

		return $this->load->view('bytao/search', $data);
	}
}