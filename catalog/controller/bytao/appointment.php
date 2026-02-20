<?php
namespace Opencart\Catalog\Controller\Bytao;
class Appointment extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/appointment';
	private $C = 'appointment';
	private $ID = 'appointment_id';
	private $model ;
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	
	public function index() {
		
		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('bytao/appointment', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language')));
		}
		$this->getML('ML');
		
		$url = '';
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];	
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/appointment', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . $url)
		];
		
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		$limit = 10;
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keyword'));
			
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['appointments']=[];
		
		
		$appointment_total = $this->model->{$this->getFunc('getTotal','s')}();
		$results = $this->model->{$this->getFunc('get','s')}(($page - 1) * $limit, $limit);
		
		
		if ($results) {
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$image=$this->model_tool_image->resize($result['image'],400,300);
				}
				if (is_file(DIR_IMAGE . $result['bimage'])) {
					$bimage=$this->model_tool_image->resize($result['bimage'],800,600);
				}
				
				$data['appointments'][] = array(
						'url'  => $result['url'],
						'title'  => $result['title'],
						'image' => $image,
						'bimage' => $bimage
					);
				
				
			}
		} 
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $appointment_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('bytao/appointment', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($appointment_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($appointment_total - $limit)) ? $appointment_total : ((($page - 1) * $limit) + $limit), $appointment_total, ceil($appointment_total / $limit));

		$data['continue'] = $this->url->link('account/account', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['cart'] = $this->url->link('bytao/appointment.cart', 'language=' . $this->config->get('config_language'));
		$this->load->model('catalog/product');
		if($appointment_total){
			$data['product'] = $this->model_catalog_product->getProduct(8);
		}else{
			$data['product'] = $this->model_catalog_product->getProduct(7);
		}
		$data['currency'] = $this->session->data['currency'];
		$data['add_to_cart'] = $this->url->link('checkout/cart.add', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		$data['checkout'] = $this->url->link('bytao/appointment.add', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		
		$hData = [
					'route'   => $this->cPth,
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_list', $data));
	}
	
	public function add() {
		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('bytao/appointment', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language')));
		}
		$this->getML('ML');
		
		$data['checkout'] = $this->url->link('bytao/appointment.checkout', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token']);
		
		if (isset($this->request->get['last_date'])) {
			$theday =  date('Y-m-d', strtotime($this->request->get['last_date']));
		}
		
		if (isset($this->request->get['next_date'])) {
			$theday = date('Y-m-d', strtotime($this->request->get['next_date']));
		}
		
		if(!isset($theday)) $theday = date('Y-m-d');
		
		$data['days']=explode(',',$this->language->get('days'));
		$data['today'] = date('Y-m-d');
		$data['theday'] = $theday;
		
		$lastM =  date('Y-m-01', strtotime('-1 month', strtotime($theday)));
		$nextM = date('Y-m-01', strtotime('+1 month', strtotime($theday)));
		
		$data['lastM'] = $this->url->link($this->cPth.'.add', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'].'&last_date='.$lastM);
		$data['nextM'] = $this->url->link($this->cPth.'.add', 'language=' . $this->config->get('config_language') . '&customer_token=' . $this->session->data['customer_token'].'&next_date='.$nextM);
		
		
		$data['days']=explode(',',$this->language->get('days'));
		$data['appointments']=[];
		$filter_data = [
			'date_start' => $theday,
			'date_end' => $nextM
		];
		$items = $this->model->{$this->getFunc('getCalendar','s')}($filter_data);
		
		foreach($items as $item){
			$_date = explode(' ',$item['date_app']);
			$date = $_date[0];
			if(isset($data['appointments'][$date])){
				$data['appointments'][$date][]=[
					'appointment_id' => $item['appointment_id'],
					'status' => $item['status'],
					'date' => $_date[0],
					'time' => $_date[1]
				];
			}else{
				$data['appointments'][$date]=[];
				$data['appointments'][$date][]=[
					'appointment_id' => $item['appointment_id'],
					'status' => $item['status'],
					'date' => $_date[0],
					'time' => $_date[1]
				];
				
			}
		}
		
		
		$hData = [
			'route'   => $this->cPth,
			'cdata'  => [],
			'JS'  => $this->load->view($this->cPth.'/'.$this->C.'_js', $data),
			'tdata'  => [],
			'hmenu'  => false,
			'hfooter'=> false
		];
				
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_calendar', $data));
	}
	
	public function dayappointer(): void {
		$this->getML('ML');
		$json = [];
		if (isset($this->request->get['day']) && isset($this->request->get['month'])&& isset($this->request->get['year'])) {
			$time = $this->request->get['year'].'-'.$this->request->get['month'].'-'.$this->request->get['day'];
			$data['appointments']=[];
			$items = $this->model->{$this->getFunc('getDate','s')}($time);
			
			foreach($items as $item){
				$_date = explode(' ',$item['date_app']);
				$date = $_date[0];
				$data['appointments'][]=[
					'app-id' => $item['appointment_id'],
					'status' => $item['status'],
					'date' => $_date[0],
					'time' => oc_substr($_date[1],0,-3)
				];
			}

		}
		
		$this->response->setOutput($this->load->view($this->cPth.'_calendar_day', $data));
	}
	
	public function cart(): void {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		$logged = $this->customer->isLogged();
		$data['groupId'] = $groupId = $logged ? $this->customer->getGroupId():0;

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', 'language=' . $this->config->get('config_language')), $this->url->link('account/register', 'language=' . $this->config->get('config_language')));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$data['list'] = $this->load->controller('checkout/cart.getList');

			$data['modules'] = [];

			$this->load->model('setting/extension');

			$extensions = $this->model_setting_extension->getExtensionsByType('total');

			foreach ($extensions as $extension) {
				$result = $this->load->controller('extension/' . $extension['extension'] . '/total/' . $extension['code']);

				if (!$result instanceof \Exception) {
					$data['modules'][] = $result;
				}
			}

			$data['checkout'] = $data['continue'] = $this->url->home();
			if ($groupId != 2) {
				$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
			} else {

				$this->load->model('account/address');
				$address_info = $this->model_account_address->getAddress($this->customer->getId(), $this->customer->getAddressId());
				$this->session->data['payment_address'] = $address_info;

				if (isset($this->session->data['shipping_address']['address_id'])) {
					$data['address_id'] = $this->session->data['shipping_address']['address_id'];
				} else {
					$data['address_id'] = $this->customer->getAddressId();
				}



				if ($this->customer->isLogged() && $this->cart->hasShipping()) {
					$data['shipping_address'] = $this->load->controller('checkout/shipping_address');
				} else {
					$data['shipping_address'] = '';
				}


				$data['confirm'] = $this->url->link('checkout/confirm.whole_confirm', 'language=' . $this->config->get('config_language'));
				$data['language'] = $this->config->get('config_language');
				$data['js'] = $this->load->view('checkout/cart_js', $data);
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$hData = array(
			'route'	=> 'checkout/cart',
			'list_name'	=> 'cart-products',
			'hmenu' 	=> 1,
			'hfooter' 	=> 1,
			'mTy'		=> 1,
			'tdata' 	=> $this->getCartProducts()
			);


			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);

			$this->response->setOutput($this->load->view('checkout/cart', $data));
		} else {
			$data['text_error'] = $this->language->get('text_no_results');

			$data['continue'] = $this->url->home();

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',['route'=>'error/not_found']);
			$data['header'] = $this->load->controller('bytao/header',['route'=>'error/not_found']);

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function checkout():void{
		
		$this->getML('ML');
		$data['confirm'] = $this->load->controller('checkout/confirm');
		
		$data['appointment_total'] = $this->model->{$this->getFunc('getTotal','s')}();
		
		$hData = [
					'route'   => $this->cPth,
					'list_name'	=> 'cart-products',
					'js'		=> $this->load->view('checkout/checkout_js', $data),
					'hmenu' 	=> 1,
					'hfooter' 	=> 1,
					'mTy'		=> 1,  
					'tdata' 	=> $this->getCartProducts()
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_shopify', $data));
	}
	
	public function checkout_checkout():void{
		
		if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
			$this->session->data['redirect'] = $this->url->link('bytao/appointment', 'language=' . $this->config->get('config_language'));
			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language')));
		}
		$this->getML('ML');
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			if (!$product['minimum']) {
				$this->response->redirect($this->url->link('account/account', 'language=' . $this->config->get('config_language'), true));

				break;
			}
		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/appointment.checkout', 'language=' . $this->config->get('config_language'))
		];

		
		$data['register'] = '';
		
		if ($this->customer->isLogged()) {
			$data['payment_address'] = $this->load->controller('checkout/payment_address');
			$data['customer_token'] = $this->session->data['customer_token'];
		}
		$data['shipping_method'] = '';
		
		$data['language'] = $this->config->get('config_language');
		
		$data['payment_method'] = $this->load->controller('checkout/payment_method');
		$data['confirm'] = $this->load->controller('checkout/confirm');
		
		$hData = [
					'route'   => $this->cPth,
					'list_name'	=> 'cart-products',
					'js'		=> $this->load->view('checkout/checkout_js', $data),
					'hmenu' 	=> 1,
					'hfooter' 	=> 1,
					'mTy'		=> 1,  
					'tdata' 	=> $this->getCartProducts()
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_checkout', $data));
	}
	
	public function getCartProducts():array {
		$products = [];
		$this->load->model('checkout/cart');
		$products = $this->model_checkout_cart->getProducts();
		
		return $products;
	}
	
	public function widget($lData=array()){
		$this->getML('ML');
		if (isset($lData['item_id'])) {
			$limit = (int)$lData['item_id'];
		} else {
			$limit  = 0;
		}
		
		$data['witems'] = [];
		$items = $this->model->{$this->getFunc('get','s')}($limit);
		if ($items) {
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			$data['title'] = $this->language->get('text_widget_title');
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['witems'][] = array(
						'url'  => $result['url'],
						'title'  => $result['title'],
						//'image' => $this->model_tool_image->resize($result['image'],$lData['thumb_width']?$lData['thumb_width']:400,$lData['thumb_height']?$lData['thumb_height']:400),
						'image' => $this->model_tool_image->resize($result['image'],400,300),
					);
				}
			}
			return $this->load->view($this->cPth.'_widget', $data);
		} 
	}

	public function payment(){
		
		
		
		
		$hData = [
			'route'   => $this->cPth,
			'cdata'  => [],
			'tdata'  => [],
			'hmenu'  => false,
			'hfooter'=> false
		];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_list', $data));
	}

}
