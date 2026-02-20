<?php
namespace Opencart\Catalog\Controller\Bytao;
class Footer extends \Opencart\System\Engine\Controller {
	public function index(array|string $hData=[]):string {
		$this->load->language('bytao/footer');
		$this->load->model('bytao/page');
		$this->load->model('bytao/footer');
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$server = HTTP_SERVER;
		$logged = $this->customer->isLogged();
		if ($logged) {
			$data['groupId'] = $this->customer->getGroupId();
			$data['logged']=1;
		}else{
			$data['logged']=0;
			$data['groupId'] =0;
		}
		
		$data['rows'] = $this->load->controller('bytao/editor.rowBender',['ctrl'=>'footer']);
		
		
		$data['pages'] = [];
		foreach ($this->model_bytao_page->getPages() as $result){
			if ($result['bottom']) {
				$data['pages'][] = [
					'title' => $result['title'],
					'bottom' => $result['bottom'],
					'href'  => $this->url->link('bytao/page','language=' . $this->config->get('config_language'). '&page_id=' . $result['page_id'])
				];
			}
		}
		
		$this->load->model('catalog/information');

		$data['informations'] = [];

		foreach ($this->model_catalog_information->getInformations() as $result){
			if ($result['bottom']) {
				$data['informations'][] = [
					'title' => $result['title'],
					'bottom' => $result['bottom'],
					'href'  => $this->url->link('information/information','language=' . $this->config->get('config_language'). '&information_id=' . $result['information_id'])
				];
			}
		}
		
		$data['categories'] = [];
		
		if (is_file(DIR_IMAGE . by_move($this->config->get('config_logo')))) {
			$data['logo'] = HTTPS_IMAGE .  $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		

		$data['onam'] = $this->url->link('bytao/forms','language=' . $this->config->get('config_language'). '&forms_id=4');
		$data['blog'] = $this->url->link('cms/blog','language=' . $this->config->get('config_language'),false,false);
		$data['sss'] = $this->url->link('bytao/faq','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['awards'] = $this->url->link('bytao/award','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['analysis'] = $this->url->link('bytao/analysis','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		
		$data['treemenu'] ='';
		$data['telephone'] = $this->config->get('config_telephone');
		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['email'] = $this->config->get('config_email');
		$data['newsletter_widget'] = $this->load->controller('bytao/newsletter','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		
		$data['contact'] = $this->url->link('information/contact','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		
		$data['return'] = $this->url->link('account/return|add','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['sitemap'] = $this->url->link('information/sitemap','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		
		$data['tracking'] = $this->url->link('information/tracking','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['manufacturer'] = $this->url->link('product/manufacturer','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['voucher'] = $this->url->link('account/voucher','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['affiliate'] = $this->url->link('affiliate/login','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['special'] = $this->url->link('product/special','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['account'] = $this->url->link('account/account','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['order'] = $this->url->link('account/order', '','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['wishlist'] = $this->url->link('account/wishlist','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['newsletter'] = $this->url->link('account/newsletter','language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));
		
		$data['track'] = $this->url->link('account/track', '', 'SSL');
		$data['search'] = $this->url->link('product/search', '', 'SSL');
		
		
		
		if(isset($this->session->data['accepte'])) {
			$data['accepte'] = 1;
		}else{
			$data['accepte'] = 0;
		}
		
		$data['accepte'] = $this->load->controller('bytao/accepte');
		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}
		
		$data['PopUp'] = $this->load->controller('bytao/popup');
		
		if(isset($hData['route'])){
			$route = $hData['route'];
		}else{
			$route = 'bytao/home';
		}
		
		$data['fscripts'] = $this->document->getScripts('footer');
		
		
		$data['scrpt_footer'] = $this->load->controller('bytao/script.footer',$hData);
		
		if(isset($hData['ctrl'])){
			switch($hData['ctrl']){
				case 'register':
				case 'guest':
				case 'review':
				case 'returns':
				case 'contact':
					$data['recaptcha'] = $this->load->controller('google/recaptcha');
					break;
				case 'art_search':
				default:
			}
		}
		
		
		$this->load->model('bytao/jqscript');
		$jsResults = $this->model_bytao_jqscript->getJqscriptByStore();
		foreach($jsResults as $jqscript){
			if($jqscript['version']){
				$this->load->controller('bytao/jqscript',$jqscript);
			}
		}
		
		$data['jqscript'] = $this->load->controller('bytao/jqscript.addhead');
		if(isset($hData['JS'])){
			$data['JS'] = $hData['JS'];
		}
		
		return $this->load->view('bytao/footer', $data);
	
	}
	
}
