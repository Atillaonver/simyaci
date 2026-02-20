<?php
namespace Opencart\Catalog\Controller\Bytao;
class Header extends \Opencart\System\Engine\Controller {
	public function index($hData): string {
		// Analytics
		$data['analytics'] = [];

		if (!$this->config->get('config_cookie_id') || (isset($this->request->cookie['policy']) && $this->request->cookie['policy'])) {
			$this->load->model('setting/extension');

			$analytics = $this->model_setting_extension->getExtensionsByType('analytics');

			foreach ($analytics as $analytic) {
				if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
					$data['analytics'][] = $this->load->controller('extension/' . $analytic['extension'] . '/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
				}
			}
		}
		
		
	
		$this->search_engine();
		
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['title'] = $this->document->getTitle();
		$data['base'] = HTTP_SERVER; //$this->config->get('config_url');
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();

		// Hard coding css so they can be replaced via the event's system.
		$data['bootstrap'] = 'https://cdn.simyaci.tr/css/core/bootstrap.css';
		$data['icons'] = 'https://cdn.simyaci.tr/fonts/fontawesome/css/all.min.css';
		//$data['stylesheet'] = 'cdn/css/stylesheet.css';

		// Hard coding scripts so they can be replaced via the event's system.
		//$data['jquery'] = 'https://cdn.simyaci.tr/js/masterslider/jquery.min.js';
		$data['jquery'] = 'https://code.jquery.com/jquery-3.7.1.js';
		$data['ui'] = 'https://code.jquery.com/ui/1.14.1/jquery-ui.js';

		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		
		$this->load->model('bytao/css');
		
		$cssResults = $this->model_bytao_css->getCssByStore();
		foreach($cssResults as $css){
			if($css['version']){
				$this->load->controller('bytao/css',$css);
			}
		}
		
		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . by_move($this->config->get('config_icon')))) {
			//$data['icon'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
			$data['icon'] =  'image/' . $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . by_move($this->config->get('config_logo')))) {
			//$data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
			$data['logo'] =  'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . by_move($this->config->get('config_logo_negative')))) {
			//$data['logo_negative'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo_negative');
			$data['logo_negative'] =  'image/' . $this->config->get('config_logo_negative');
		} else {
			$data['logo_negative'] = '';
		}
		
		$data['logged'] = $this->customer->isLogged();
		$data['track'] = $this->url->link('account/track','language=' . $this->config->get('config_language'));
		
		if (!$this->customer->isLogged()) {
			
			$data['register'] = $this->url->link('account/register', 'language=' . $this->config->get('config_language'));
			$data['retailregister'] = $this->url->link('account/retailregister', 'language=' . $this->config->get('config_language'));
			$data['retaillogin'] = $this->url->link('account/retaillogin','language=' . $this->config->get('config_language'));
			$data['login'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'));
		} else {
			$data['account'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['order'] = $this->url->link('account/order', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['transaction'] = $this->url->link('account/transaction', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['download'] = $this->url->link('account/download', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
			$data['logout'] = $this->url->link('account/logout', 'language=' . $this->config->get('config_language'));
		}

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			$data['islogged'] =1;
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			$data['islogged'] =0;
		}

		$data['home'] = $this->url->home();
		
		$data['contact'] = $this->url->link('information/contact', 'language=' . $this->config->get('config_language'));
		$data['telephone'] = $this->config->get('config_telephone');
		
		
		$this->load->language('bytao/header');
		
		if(isset($this->request->get['route'])){
			$data['class']= str_replace('/','-',$this->request->get['route']);
			$route=$this->request->get['route'];
		}else if(isset($hData['route'])){
			$data['class']= str_replace('/','-',$hData['route']); 
			$route = $hData['route'];
		}else{
			$data['class']='bytao-home';
			$route = 'bytao/home';
		}
		
		if ($this->customer->isLogged()) {
			if($this->customer->getGroupId()==2){
				$data['class'].=' wholesale';
			}
		}
		
		if(isset($this->session->data['browser'])){
			$data['platform'] = $this->session->data['browser'];
		}else{
			$data['platform'] = $this->session->data['platform'] = $this->browser->getPlatform();
		}
		
		if(isset($this->session->data['isMobile'])){
			$data['isMobile'] = $this->session->data['isMobile'];
		}else{
			$data['isMobile'] = $this->session->data['isMobile'] = $this->browser->isMobile()?1:0;
		}
		
		if(isset($this->session->data['isTablet'])){
			$data['isTablet'] = $this->session->data['isTablet'];
		}else{
			$data['isTablet'] = $this->session->data['isTablet'] = $this->browser->isTablet()?1:0;
		}
		
		if(isset($this->session->data['device'])){
			$data['device'] = $this->session->data['device'];
		}else{
			$device = $this->browser->getDevice();
			$data['device'] = $this->session->data['device'] = $device?$device:'none';
		}
		
		$data['theme'] = $this->config->get('config_theme');
		$data['home'] = $this->url->home();
		$data['contact'] = $this->url->link('information/contact', 'language=' . $this->config->get('config_language'));
		
		$data['telephone'] = $this->config->get('config_telephone');
		$data['language'] = $this->load->controller('bytao/language');
		$trData =[
			'logo'			=> $data['logo'],
			'logo_negative'	=> $data['logo_negative'],
			'home'			=> $data['home'],
			'lang'			=> $data['language']
			];
			
		if($this->config->get('config_url_out')=='1'){
			define("OUT", '1');
		}
		
		switch($this->config->get('config_store_type')){
			case '0'://kurumsal
			case '12':// e-ticaret
				$data['wishlist'] = $this->url->link('account/wishlist', 'language=' . $this->config->get('config_language') . (isset($this->session->data['customer_token']) ? '&customer_token=' . $this->session->data['customer_token'] : ''));
		$data['shopping_cart'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'));
		$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
				$data['cart'] = $this->load->controller('bytao/cart');	
				$data['currency'] = $this->load->controller('bytao/currency');
				break;
			case '23'://yaprak
			case '34':// blog
			default:
			
		}
		
		if(isset($hData['ctrl'])){
			switch($hData['ctrl']){
				case 'register':
				case 'guest':
				case 'review':
				case 'returns':
				case 'contact':
					break;
				case 'art_search':
					$data['search_form'] = $this->load->controller('bytao/art_search_form',$hData);	
				default:
					$data['search'] = $this->load->controller('bytao/search',$hData);
			}
		}
		
		
		
		
		$data['menu'] = $this->load->controller('bytao/menu.getdropdown',$trData);
		$data['css'] = $this->load->controller('bytao/css.addhead');
		$data['google'] = $this->load->controller('bytao/google');
		
		$data['jqscript'] = $this->load->controller('bytao/jqscript.addhead');
		$data['scrpt_head'] = $this->load->controller('bytao/script.head',$hData);
		$data['scrpt_body'] = $this->load->controller('bytao/script.body',$hData);
		
		$this->response->addHeader('Content-Type: text/html; charset=UTF-8');
		return $this->load->view('bytao/header', $data);
	}
	
	private function search_engine()
	{
		if(isset($this->request->get['frm'])){
				$this->session->data['frm'] = $this->request->get['frm'];
			}
		
		if(!isset($this->session->data['referer_url']))
		{	$query_string = '';
			$paid='';
			$frm='';
			
			$parts_url = parse_url(HTTP_REFERER);
			$query     = isset($parts_url['query']) ? $parts_url['query'] : (isset($parts_url['fragment']) ? $parts_url['fragment'] : '');
			if(!$query)
			{
			
				parse_str($query, $parts_query);
				
				if( isset($parts_query['q']) )
				{
					$query_string = $parts_query['q'];
				}
				else
				if( isset($parts_query['p'] ) )
				{
					$query_string = $parts_query['p'];
				}
				elseif( isset($parts_query['key'] ) )
				{
					$query_string = $parts_query['key'];
				}
				elseif( isset($parts_query['_nkw'] ) )
				{
					$query_string = $parts_query['_nkw'];
				}
				elseif( isset($parts_query['keyword'] ) )
				{
					$query_string = $parts_query['keyword'];
				}
				elseif( isset($parts_query['searchfor'] ) )
				{
					$query_string = $parts_query['searchfor'];
				}
				elseif( isset($parts_query['search_terms'] ) )
				{
					$query_string = $parts_query['search_terms'];
				}
				elseif( isset($parts_query['field-keywords'] ) )
				{
					$query_string = $parts_query['field-keywords'];
				}
				elseif( isset($parts_query['keywords'] ) )
				{
					$query_string = $parts_query['keywords'];
				}
				elseif( isset($parts_query['query'] ) )
				{
					$query_string = $parts_query['query'];
				}
				elseif( isset($parts_query['ovkey'] ) )
				{
					$query_string = $parts_query['ovkey'];
				}
				elseif( isset($parts_query['search'] ) )
				{
					$query_string = $parts_query['search'];
				}
				elseif( isset($parts_query['term'] ) )
				{
					$query_string = $parts_query['term'];
				}
				elseif( isset($parts_query['on'] ) )
				{
					$query_string = $parts_query['on'];
				}
				elseif( isset($parts_query['w'] ) )
				{
					$query_string = $parts_query['w'];
				}
				elseif( isset($parts_query['text'] ) )
				{
					$query_string = $parts_query['text'];
				}
				elseif( isset($parts_query['strsearchstring'] ) )
				{
					$query_string = $parts_query['strsearchstring'];
				}
				elseif( isset($parts_query['s'] ) )
				{
					$query_string = $parts_query['s'];
				}
				elseif( isset($parts_query['terms'] ) )
				{
					$query_string = $parts_query['terms'];
				}
				elseif( isset($parts_query['qs'] ) )
				{
					$query_string = $parts_query['qs'];
				}
				elseif( isset($parts_query['encquery'] ) )
				{
					$query_string = $parts_query['encquery'];
				}
				elseif( isset($parts_query['wd'] ) )
				{
					$query_string = $parts_query['wd'];
				}
				elseif( isset($parts_query['search_word'] ) )
				{
					$query_string = $parts_query['search_word'];
				}
				elseif( isset($parts_query['qt'] ) )
				{
					$query_string = $parts_query['qt'];
				}
				elseif( isset($parts_query['words'] ) )
				{
					$query_string = $parts_query['words'];
				}
				elseif( isset($parts_query['rdata'] ) )
				{
					$query_string = $parts_query['rdata'];
				}
				elseif( isset($parts_query['szukaj'] ) )
				{
					$query_string = $parts_query['szukaj'];
				}
				elseif( isset($parts_query['k'] ) )
				{
					$query_string = $parts_query['k'];
				}
				elseif( isset($parts_query['route'] ) )
				{
					$query_string = '';
				} 
				else 
				{
					$query_string = '';
				}   
			}
			
			if(strpos(HTTP_REFERER, 'bing.com'))
			{
				$this->session->data['referer_url'] = 'bing' ;
				$this->session->data['words'] = $query_string;
			}
			else
			if(strpos(HTTP_REFERER, 'google.com'))
			{
				$this->session->data['referer_url'] = 'Google';
				$this->session->data['words'] = $query_string;
			}
			else
			if(strpos(HTTP_REFERER, 'yandex.com'))
			{
				$this->session->data['referer_url'] = 'yandex';
				$this->session->data['words'] = $query_string;
			}
			else
			if(strpos(HTTP_REFERER, 'facebook.com'))
			{
				$this->session->data['referer_url'] = 'Facebook';
				$this->session->data['words'] = $query_string;
			}
			else
			if(strpos(HTTP_REFERER, '//www.astonleather.com'))
			{
				$this->session->data['referer_url'] = 'Direct';
				$this->session->data['words'] = '';
			}
			else
			{
				$this->session->data['referer_url'] = HTTP_REFERER;
			}
			  
		}
	}
	
}
