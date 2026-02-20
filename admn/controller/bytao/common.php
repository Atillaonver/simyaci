<?php
namespace Opencart\Admin\Controller\Bytao;
class Common extends \Opencart\System\Engine\Controller {	
	public function index():void{
		
	}
	
	public function install():void {
		$this->load->model('bytao/tool');
	}
	/*
	
	-- admin/header/stores 
		Add:ControllerCommonHeader
			$data['allStores'] = $this->load->controller('bytao/common/stores',$data['stores']);
		Add:wiew/common/header/header.twig 
			{{ allStores }}
	*/
	
	public function stores(array $stores ):string {
		$this->load->model('bytao/tool');
		$this->load->model('sale/order');
		$this->load->model('customer/customer');
		$this->load->model('setting/setting');
		
		if (!isset($this->session->data['store_id'])) 
		{
				$store_id = $this->user->getStoreId();
				$data['store_id'] = $store_id?$store_id:(int)$this->config->get('config_store_id');
				$sconfig = $this->model_setting_setting->getSetting('config',$data['store_id']);
				$this->session->data['store_id'] =  $data['store_id'];
				$this->session->data['store_language_id'] =  $this->model_bytao_common->getStoreLanguageByCode($sconfig['config_language']);
				$this->session->data['logo'] = $sconfig['config_icon']?$sconfig['config_icon']:$sconfig['config_logpo'];
				$this->session->data['store_url'] = isset($sconfig['url'])?$sconfig['url']:HTTP_CATALOG;
				$this->session->data['url'] = HTTP_CATALOG;
				$this->session->data['name'] = $sconfig['config_name'];
				$this->session->data['store_path'] = $sconfig['config_path'];
				$this->session->data['type'] = isset($sconfig['config_site_type'])?$sconfig['config_site_type']:0;
				$this->session->data['long_name'] = strtolower(str_replace(' ', '', $sconfig['config_name']));
		}
		else
		{
			$data['store_id'] = $store_id = $this->session->data['store_id'];
			if (!isset($this->session->data['store_path'])) {
				$sconfig = $this->model_setting_setting->getSetting('config',$store_id);
				$this->session->data['store_language_id'] =  $this->model_bytao_common->getStoreLanguageByCode($sconfig['config_language']);
				$this->session->data['logo'] = $sconfig['config_icon']?$sconfig['config_icon']:$sconfig['config_logpo'];
				$this->session->data['store_url'] = isset($sconfig['url'])?$sconfig['url']:HTTP_CATALOG;
				$this->session->data['url'] = HTTP_CATALOG;
				$this->session->data['name'] = $sconfig['config_name'];
				$this->session->data['store_path'] = $sconfig['config_path'];
				$this->session->data['type'] = isset($sconfig['config_site_type'])?$sconfig['config_site_type']:0;
				$this->session->data['long_name'] = strtolower(str_replace(' ', '', $sconfig['config_name']));
			}
		}
		
		foreach ($stores as $result) {
			
			$config = $this->model_setting_setting->getSetting('config',$result['store_id']);
			if($result['store_id']){
				$edit = $this->url->link('setting/store.form', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $result['store_id']);
			}else{
				$edit = $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token']);
			}
			
			
			if (isset($config['config_logo']) && is_file(DIR_IMAGE . $config['config_logo'])) {
				$logo = '../image/' . ($config['config_icon']?$config['config_icon']:$config['config_logo']);
			} else {
				$logo = '';
			}
			
			$data['general_stores'][] = [
			'name' => $result['name'],
			'logo' => $logo,
			'store_id' => $result['store_id'],
			'edit'  => $edit,
			'sitetype' => isset($config['config_site_type'])?$config['config_site_type']:0,
			'url' => isset($config['config_url'])?$config['config_url']:HTTP_CATALOG,
			'store_path' => isset($config['config_path'])?$config['config_path']:'',
			'order' => $this->model_sale_order->getTotalOrders(['filter_date_added' => date('Y-m-d', strtotime('-1 day'))],$result['store_id'],FALSE),
			'customer' => $this->model_customer_customer->getTotalCustomers(['filter_date_added' => date('Y-m-d', strtotime('-1 day'))],$result['store_id'],false)
			];
		}
		$data['gID'] = $this->user->getGroupId();
		
		return $this->load->view('bytao/allstores', $data);
	}
	
	public function clickstore(): void{
		$json = [];
		
		
		if (isset($this->request->get['store_id'])) 
		{
			$this->load->model('setting/store');
			$this->load->model('setting/setting');
		    $this->load->model('bytao/common');
		    
			$store_id =  $json['storeId'] = $this->request->get['store_id'];
			
			if($store_id == '0')
			{
				$this->session->data['store_id'] =  $json['storeId'] = $store_id;
				$this->session->data['store_language_id'] =  $this->model_bytao_common->getStoreLanguageByCode($this->config->get('config_language'));
				$this->session->data['logo'] = $this->config->get('config_logo');
				$this->session->data['sitetype'] = $this->config->get('config_store_type');
				$this->session->data['store_url'] = HTTP_CATALOG;
				$this->session->data['url'] = HTTP_CATALOG;
				$this->session->data['name'] = $this->config->get('config_name');
				$this->session->data['store_path'] = $this->config->get('config_path');
				$this->session->data['long_name'] = strtolower(str_replace(' ', '', $this->config->get('config_name')));
			}
			else
			{
				$store = $this->model_setting_store->getStore($store_id);
				$config = $this->model_setting_setting->getSetting('config',$store_id);
				
				if($store){
					$this->session->data['store_id'] = $store['store_id'];
					$this->session->data['store_language_id'] =  $this->model_bytao_common->getStoreLanguageByCode($config['config_language']);
					$this->session->data['store_url'] = $store['url'];
					$this->session->data['sitetype'] = $config['config_store_type'];
					$this->session->data['store_path'] = $config['config_path'];
					$this->session->data['url'] = $store['url'];
					$this->session->data['long_name'] = strtolower(str_replace(' ', '',$store['name']));
					$this->session->data['name'] = strtolower(str_replace(' ', '',$store['name']));
				}
			}
		}
		else
		{
			$this->session->data['store_id'] =  $json['storeId'] = 0;
			$this->session->data['store_language_id'] =  $this->config->get('config_language_id');
			$this->session->data['logo'] = $this->config->get('config_logo');
			$this->session->data['sitetype'] = $this->config->get('config_store_type');
			$this->session->data['store_url'] = HTTP_CATALOG;
			$this->session->data['url'] = HTTP_CATALOG;
			$this->session->data['name'] = $this->config->get('config_name');
			$this->session->data['store_path'] = $this->config->get('config_path');
			$this->session->data['long_name'] = strtolower(str_replace(' ', '', $this->config->get('config_name')));
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function catorder():void
	{
		$this->load->language('catalog/category');
		$this->load->language('bytao/common');
		
		$postData = isset($this->request->get['product_category'])?$this->request->get['product_category']:[];
		
		$data['typ'] = isset($this->request->get['typ'])?$this->request->get['typ']:0;
		
		$productId = isset($this->request->get['pId'])?$this->request->get['pId']:0;

		$this->load->model('tool/image');
		$this->load->model('bytao/common');
		$this->load->model('catalog/category');
		
		
		$json['cat'] = $this->language->get($data['typ']);
		$products = [];


		if($data['typ'])
		{
			
			$catagoryId = 0;
			
			switch($data['typ'])
			{
				case 'mpage':
				break;
				case 'clearance':
				break;
				case 'sale':
				break;
				case 'gift':
				break;
				case 'best':
				break;
				case 'new_arriwals':
				break;
				default:


			}

			$products = [];
			$filter_data = [
				'filter_category'=>$catagoryId,
				'filter_type'    =>$data['typ']
			];
			$results       = $this->model_bytao_common->getAddProducts($filter_data);
			
			foreach($results as $result)
			{
				if(is_file(DIR_IMAGE . $result['image']))
				{
					$image = $this->model_tool_image->resize($result['image'], 200, 200);
				}
				else
				{
					$image = $this->model_tool_image->resize('no_image.png', 200, 200);
				}
				$products[] = [
					'product_id'=> $result['product_id'],
					'image'     => $image,
					'status'    => $result['status'],
					'name'      => $result['name'],
					'model'     => $result['model']
				];
			}
			$data['products'] = $products;
			$data['title'] = $this->language->get('text_sort_order').':'.$this->language->get('text_'.$data['typ']);
			$data['cat_id'] = $catagoryId;
		}
		else
		{
			
			$this->load->model('catalog/product');
			$filter_category = $this->request->get['category_id'];

			$data['cat_id'] = $filter_category;
			$data['typ'] = NULL;
			$filter_data = [
				'filter_category'=> $filter_category,
				'filter_status'  => 1,
				'sort'           => 'p2c.sort_order',
				'order'          => 'ASC'
			];

			$results       = $this->model_catalog_product->getProducts($filter_data);

			$category_info = $this->model_catalog_category->getCategory($filter_category);

			foreach($results as $result)
			{
				
				if(is_file(DIR_IMAGE . $result['image']))
				{
					$image = $this->model_tool_image->resize($result['image'], 200, 200);
				}
				else
				{
					$image = $this->model_tool_image->resize('no_image.png', 200, 200);
				}
				$products[] = [
					'product_id'=> $result['product_id'],
					'status'    => $result['status'],
					'image'     => $image,
					'name'      => $result['name'],
					'model'     => $result['model']
				];
			}
			$data['products'] = $products;
			$data['title'] = $this->language->get('text_sort_order').':'.$category_info['name'];
			$data['cat_id'] = $filter_category;

		}


		$data['user_token'] = $this->session->data['user_token'];

		
		$json['body'] = $this->load->view('bytao/modal_category_order', $data);

		$this->response->setOutput($this->load->view('bytao/modal_category_order', $data));


	}
	
	public function sortorder():void
	{
		$json=[];
		
		if(isset($this->request->post['serials']))
		{
			$this->load->model('bytao/common');
			$category_id = isset($this->request->get['filter_category'])?$this->request->get['filter_category']:0;
			
			$typ = isset($this->request->get['filter_item'])?$this->request->get['filter_item']:'';
			
			$startSortOrder = 0;
			foreach($this->request->post['serials'] as $product_id)
			{
				$this->model_bytao_common->updateProductSortorder($startSortOrder,$product_id,$category_id,$typ);
				$startSortOrder++;
			}
			$json['res'] = 'OK';
		}
		else
		{
			$json['res'] = 'NOT';
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function title_seourl(){
		$this->load->model('bytao/common');
		$this->model_bytao_common->titledSeoUrl();
		$this->response->redirect($this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token']));
	}

	public function onetime_seourl(){
		$this->load->model('bytao/common');
		$this->model_bytao_common->migrateSeoUrl();
		$this->response->redirect($this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token']));
	}
	
	public function onetime_content(){
		$this->load->model('bytao/common');
		$this->model_bytao_common->migrateContent();
		$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']));
	}
	
	public function wordcheck(){
		$json = [];
		
		if (isset($this->request->post['word'])) {
			$this->load->model('bytao/common');
			if($this->model_bytao_common->confirmWord($this->request->post['word'], $this->request->post['key'],$this->request->post['language_id'])){
				$json['confirm']=1;
			}
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function linkautocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 6;
		}
		
		if (isset($this->request->get['language_id'])) {
			$language_id = (int)$this->request->get['language_id'];
		} else {
			$language_id = '1';
		}

		
		$filter_data = [
			'filter_title'     => $filter_title,
			'filter_keyword'     => $filter_name,
			'filter_key'         => '',
			'filter_value'       => '',
			'filter_store_id'    => '',
			'filter_language_id' => $language_id,
			'sort'               => 'title',
			'order'              => '',
			'start'              => 0,
			'limit'              => $limit
		];

		$this->load->model('bytao/common');
		
		$results = $this->model_bytao_common->getSeoUrls($filter_data);
		$store_id = $this->session->data['store_id'];
		if($store_id){
			$url = new \Opencart\System\Library\Url($this->session->data['store_url']);
		}else{
			$url =$this->url;
		}
		
		foreach ($results as $result) {
			switch($result['key']){
				case 'path': $link=$url->link('product/category', 'language=' . $result['language_id'] . '&path=' . $result['value']);break;
				case 'product_id': $link=$url->link('product/product', 'language=' . $result['language_id'] . '&product_id=' . $result['value']);break;
				case 'page_id': $link=$url->link('bytao/page', 'language=' . $result['language_id'] . '&page_id=' . $result['value']);break;
				case 'information_id': $link=$url->link('information/information', 'language=' . $result['language_id']. '&information_id=' . $result['value']);break;
				default: 
					$path=explode('/',$result['key']);
					if (count($path)==3){
						$last = end($path);
						array_pop($path);
						$newKey=(implode('/',$path)).'.'.$last;
						$link = $this->url->link($newKey, 'language=' . $result['language_id']);break;
					}else{
						$link = $url->link($result['key'], 'language=' . $result['language_id']);break;
					}
			}
			
			
			
			$json[] = [
				'title'    		=> $result['title'],
				'keyword'    	=> $result['keyword'],
				'link'      	=> $link,
				'seo_url_id' 	=> $result['seo_url_id'],
				'key'        	=> $result['key'],
				'value'      	=> $result['value']=='route'?$result['route']:$result['value'],
				
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	
	
	public function octoast(){
		/*
		$this->load->model('bytao/common');
		$this->model_bytao_common->OCCustomer();
		$this->log->write('OCCustomer Tamam:');
		$this->model_bytao_common->OCCatalog();
		$this->log->write('OCCatalog Tamam:');
		$this->model_bytao_common->OCOrder();
		$this->log->write('OCOrder Tamam:');
		*/
		$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']));
	}
	
	public function prody(){
		/*
		$query = $this->db->query("SELECT * FROM ast_product_description");
		foreach($query->rows as $ROW_c){
			$cols = explode('::::::',html_entity_decode($ROW_c['description']?$ROW_c['description']:'', ENT_QUOTES , 'UTF-8'));
			if(count($cols)>1){
				
				$paragraph1 = isset($cols[0])?$cols[0]:'';
				if (str_contains($paragraph1, '<p>')) {
					$paragraph1 = $paragraph1. '</p>';
				}	
				
				$sql = "UPDATE " . DB_PREFIX . "product_description SET description = '".$this->db->escape($paragraph1)."' WHERE product_id='".$ROW_c['product_id']."' AND language_id ='".$ROW_c['language_id']. "'";
				
				$this->db->query($sql);
				
				
			}
			
			
		}
		*/
		$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']));
	}	
}
?>