<?php

namespace Opencart\Admin\Controller\Bytao;
class Export extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $cPth = 'bytao/export';
	private $C = 'export';
	private $ID = 'export_id';
	private $Tkn = 'user_token';
	private $model;
	
	private function getFunc($f='',$addi=''){
		return $f; //$f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C)));
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			case 'L':
				$this->load->language($this->cPth);
				break;
			case 'ML':
			case 'LM':
				$this->load->language($this->cPth);
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			default:
		}
	}

	public function index(array $eData =[]):string
	{
		if(isset($eData['type'])){
			switch($eData['type']){
				case 'product':
					return $this->getProd($eData);
					break;
				case 'order':
					return $this->getOrder($eData);
					break;
				case 'email':
					return $this->getEmail($eData);
					break;
				case 'customer':
					return $this->getCustomer($eData);
					break;
			}
		}
		return '';
	}

	private function getProd($eData){
		$data = [];
		$this->getML('L');
		
		$data['user_token'] = $this->session->data['user_token'];
		$data['ex_type'] = $eData['type'];
		
		$data['filename'] = isset($this->session->data['import_file'])?$this->session->data['import_file']:'';
		$data['import'] = $this->url->link($this->cPth.'.import', 'user_token=' . $this->session->data['user_token']);
		
		
		$data['error_post_max_size'] = str_replace( '%1', ini_get('post_max_size'), $this->language->get('error_post_max_size') );
		$data['error_upload_max_filesize'] = str_replace( '%1', ini_get('upload_max_filesize'), $this->language->get('error_upload_max_filesize') );

		if (!empty($this->session->data['export_import_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['export_import_error']['errstr'];
		} else if (isset($this->session->data['warning'])) {
			$this->error['warning'] = $this->session->data['warning'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['export_import_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['warning']);
		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );
		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));
		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);
		$data['upload'] = $this->url->link('tool/upload.upload', 'user_token=' . $this->session->data['user_token']);
		
		$data['export'] = $this->url->link($this->cPth.'.'.$eData['type'], 'user_token=' . $this->session->data['user_token']);
		
		
		
		return $this->load->view($this->cPth, $data);
	}

	private function getOrder(array $eData){
		$data = [];
		$this->getML('L');
		
		
		if (!empty($this->session->data['export_import_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['export_import_error']['errstr'];
		} else if (isset($this->session->data['warning'])) {
			$this->error['warning'] = $this->session->data['warning'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['export_import_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['warning']);
		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['ex_type'] = 'order';
		$data['export'] = $this->url->link($this->cPth.'.order', 'user_token=' . $this->session->data['user_token']);
		return $this->load->view($this->cPth, $data);
	}
	
	private function getEmail(array $eData){
		$data = [];
		$this->getML('L');
		
		
		if (!empty($this->session->data['export_import_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['export_import_error']['errstr'];
		} else if (isset($this->session->data['warning'])) {
			$this->error['warning'] = $this->session->data['warning'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['export_import_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['warning']);
		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['ex_type'] = 'order';
		$data['export'] = $this->url->link($this->cPth.'.email', 'user_token=' . $this->session->data['user_token']);
		return $this->load->view($this->cPth, $data);
	}
	
	private function getCustomer(array $eData){
		$data = [];
		$this->getML('L');
		
		
		if (!empty($this->session->data['export_import_error']['errstr'])) {
			$this->error['warning'] = $this->session->data['export_import_error']['errstr'];
		} else if (isset($this->session->data['warning'])) {
			$this->error['warning'] = $this->session->data['warning'];
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
			if (!empty($this->session->data['export_import_nochange'])) {
				$data['error_warning'] .= "<br />\n".$this->language->get( 'text_nochange' );
			}
		} else {
			$data['error_warning'] = '';
		}

		unset($this->session->data['warning']);
		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['ex_type'] = 'order';
		$data['export'] = $this->url->link($this->cPth.'.customer', 'user_token=' . $this->session->data['user_token']);
		return $this->load->view($this->cPth, $data);
	}
	
	
	
	
	
	
	

	protected function getList()
	{
		if(!isset($this->session->data['store_id']))
		{
			$this->session->data['store_id'] = $this->config->get('config_store_id');
		}
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text'=> $this->language->get('text_home'),
			'href'=> $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text'=> $this->language->get('heading_title'),
			'href'=> $this->url->link('bytao/export', 'token=' . $this->session->data['token'] , 'SSL')
		);


		$data['viewlist'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&filter_selected=s', 'SSL');
		$data['selCount'] = $this->model_catalog_product->getCountToSelect();


		{
			$data['delete'] = $this->url->link('catalog/product/delete', 'token=' . $this->session->data['token'] , 'SSL');
		}
		$this->load->model('catalog/category');

		$data['categories'] = [];

		//$category_data = $this->cache->get('catgory.empty.'.$this->session->data['store_id'] );

		//if (!$category_data) {
		$results = $this->model_catalog_category->getCategories(array('c2.parent_id'));
		$category_data = [];
		foreach($results as $result)
		{
			$empty = $this->model_catalog_category->isCategoryEmpty($result['category_id']);
			$category_data[] = array(
				'name'       => $result['name'],
				'category_id'=> $result['category_id'],
				'empty'      => $empty
			);
		}
		//}

		if(count($category_data) > 0)
		{
			$data['categories'] = $this->array_msort($category_data, array('name'=>SORT_ASC)); //$category_data;
		}

		$product_total = 0;
		$data['products'] = [];
		$filter_data = array(
			'filter_name'       => null,
			'filter_model'      => null,
			'filter_price'      => null,
			'filter_item'       => null,
			'filter_quantity'   => null,
			'filter_status'     => 1,
			'filter_category'   => '',
			'filter_addcategory'=> '',
			'sort'              => '',
			'order'             => ''
		);


		$this->load->model('tool/image');
		$this->load->model('bytao/product');
		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
		$results       = $this->model_catalog_product->getProducts($filter_data);

		foreach($results as $result){

			if(is_file(DIR_IMAGE . $result['image']))
			{
				$image = $this->model_tool_image->resize($result['image'], 250, 250);
				$bimage= $this->model_tool_image->resize($result['image'], 600, 600,'d');
			}
			else
			{
				$image = $this->model_tool_image->resize('no_image.png', 250,250);
				$bimage= $this->model_tool_image->resize('no_image.png', 600, 600);
			}

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach($product_specials  as $product_special)
			{
				if(($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time()))
				{
					$special = $product_special['price'];

					break;
				}
			}
			
			$thisProdOps = $this->model_catalog_product->getProductOptions($result['product_id']);


			$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'image'       => $image,
				'bimage'      => $bimage,
				'name'        => $result['name'],
				'model'       => $result['model'],
				'price'       => number_format($result['price'],2),
				'special'     => $special,
				'options'     => $thisProdOps,
				'subtract'    => $result['subtract'],
				'quantity'    => $result['quantity'],
				'best'        => $result['best'],
				'gift'        => $result['gift'],
				'sale'        => $result['sale'],
				'sale_price'  => number_format($result['sale_price'],2),
				'clearance'   => $result['clearance'],
				'new_arriwals'=> $result['new_arriwals'],
				'mpage'       => $result['mpage'],
				'status_id'   => $result['status'],
				'status'      => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'        => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] , 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_excel'] = $this->language->get('text_excel');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_stock'] = $this->language->get('text_stock');

		$data['column_image'] = $this->language->get('column_image');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_model'] = $this->language->get('column_model');
		$data['column_price'] = $this->language->get('column_price');
		$data['column_quantity'] = $this->language->get('column_quantity');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');
		$data['column_type'] = $this->language->get('column_type');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_item'] = $this->language->get('entry_item');
		$data['entry_upload'] = $this->language->get( 'entry_upload' );

		$data['button_copy'] = $this->language->get('button_copy');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['button_export'] = $this->language->get('button_export');
		$data['button_import'] = $this->language->get( 'button_import' );
		$data['button_addlist'] = $this->language->get( 'button_addlist' );
		$data['button_viewlist'] = $this->language->get( 'button_viewlist' );
		$data['button_cancel'] = $this->language->get( 'button_cancel' );

		$data['help_import'] = $this->language->get( 'help_import' );
		$data['help_format'] = $this->language->get( 'help_format' );

		$data['error_select_file'] = $this->language->get('error_select_file');
		$data['error_post_max_size'] = str_replace( '%1', ini_get('post_max_size'), $this->language->get('error_post_max_size') );
		$data['error_upload_max_filesize'] = str_replace( '%1', ini_get('upload_max_filesize'), $this->language->get('error_upload_max_filesize') );

		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );

		$data['token'] = $this->session->data['token'];

		if(isset($this->error['warning']))
		{
			$data['error_warning'] = $this->error['warning'];
		}
		else
		{
			$data['error_warning'] = '';
		}

		if(isset($this->session->data['success']))
		{
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		}
		else
		{
			$data['success'] = '';
		}

		if(isset($this->request->post['selected']))
		{
			$data['selected'] = (array)$this->request->post['selected'];
		}
		else
		{
			$data['selected'] = [];
		}



		$data['pagination'] = '';
		$data['results'] = sprintf($this->language->get('text_pagination_all'), $product_total);



		$data['total_items'] = $product_total;

		/*
		$this->document->addScript('view/javascript/jquery/jquery-sortable.js');
		$this->document->addStyle('view/stylesheet/jquery-ui.css');
		$this->document->addScript('view/javascript/jquery/jquery-ui.js');
		*/

		$this->document->addStyle('view/stylesheet/products.css?ver=87');

		//if(isset($filter_all) && isset($filter_all)){
		$this->document->addScript('view/javascript/jquery/ui/jquery-ui.min.js');
		//}

		$this->document->addStyle('view/javascript/jquery/ui/jquery-ui.min.css');
		$this->document->addStyle('view/javascript/jquery/lightbox/css/lightbox.css');
		$this->document->addScript('view/javascript/jquery/lightbox/js/lightbox.js');

		$data['header'] = $this->load->controller('common/header');
		$data['tabstores'] = $this->load->controller('common/stores');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('bytao/product_list.tpl', $data));
	}

	public function imgproperty()
	{
		$json = [];
		$data = [];
		$product_image_id = isset($this->request->get['iId'])? $this->request->get['iId']:0;

		$this->load->model('localisation/language');
		$data['action'] = $this->url->link('bytao/product/edit', 'token=' . $this->session->data['token'].'&iId='.$product_image_id, 'SSL');

		$this->load->model('bytao/product');

		$data['product_image_description'] = $this->model_bytao_product->getProductImageDescriptions($product_image_id);
		$image = $this->model_bytao_product->getImage($product_image_id);

		$data['product_image_class'] = $image['class'];
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$json['body'] = $this->load->view('bytao/productimage_form.tpl', $data);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit()
	{
		$json = [];
		if($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			$this->load->model('bytao/product');
			$this->model_bytao_product->editProductImage($this->request->get['iId'], $this->request->post);
			$json['success'] = 'OK';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function order()
	{
		$this->load->language('bytao/export');
		$this->load->model('bytao/export');
		$this->load->model('sale/order');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('setting/setting');

		$data['orders'] = [];
		$data['texts']['orders'] = $this->language->get( 'text_orders' );

		$orders = [];

		if(isset($this->request->post['selected']))
		{
			$orders = $this->request->post['selected'];
		}
		elseif(isset($this->request->get['order_id']))
		{
			$orders[] = $this->request->get['order_id'];
		}

		foreach($orders as $order_id)
		{
			$order_info = $this->model_sale_order->getOrder($order_id);

			// Make sure there is a shipping method
			if($order_info && $order_info['shipping_code'])
			{
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if($store_info)
				{
					$store_address   = $store_info['config_address'];
					$store_email     = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
					$store_fax       = $store_info['config_fax'];
				}
				else
				{
					$store_address   = $this->config->get('config_address');
					$store_email     = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
					$store_fax       = $this->config->get('config_fax');
				}

				if($order_info['invoice_no'])
				{
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				}
				else
				{
					$invoice_no = '';
				}

				if($order_info['shipping_address_format'])
				{
					$format = $order_info['shipping_address_format'];
				}
				else
				{
					$format = '{company}' . " " . '{address_1}' . " " . '{address_2}' . " " . '{city} {postcode}' . " " . '{zone}' . " " . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname'=> $order_info['shipping_firstname'],
					'lastname' => $order_info['shipping_lastname'],
					'company'  => $order_info['shipping_company'],
					'address_1'=> $order_info['shipping_address_1'],
					'address_2'=> $order_info['shipping_address_2'],
					'city'     => $order_info['shipping_city'],
					'postcode' => $order_info['shipping_postcode'],
					'zone'     => $order_info['shipping_zone'],
					'zone_code'=> $order_info['shipping_zone_code'],
					'country'  => $order_info['shipping_country']
				);

				$shipping_address = preg_replace(array("/\s\s+/","/\r\r+/","/\n\n+/"), ' ', trim(str_replace($find, $replace, $format)));


				$product_data = [];

				$products = $this->model_sale_order->getOrderProducts($order_id);
				$tax = $this->model_sale_order->getOrderTotalTax($order_id);

				foreach($products as $product)
				{
					$man = false;
					$woman = false;
					$child = false;
					$item_id = $product['product_id'];
					$categ = '';
					$categories = $this->model_catalog_product->getCategories($item_id);
					foreach($categories as $category){
						$path = $this->getPath($category['category_id']);
						if($path){
							foreach(explode('_', $path) as $path_id)
							{
								$category_info = $this->model_catalog_category->getCategory($path_id);

								if($category_info)
								{
									if(!$categ)
									{
										$categ = $category_info['name'];
									}
									else
									{
										$categ .= ' > ' . $category_info['name'];
									}
								}
							}
							
							if(strpos($categ , "for Women" ) !== false){
								$woman=TRUE;
							}
							if(strpos($categ , "for Men " ) !== false){
								$man=TRUE;
							}
						}
					}
					
					if($woman && $man){
						$gender = 'Unisex';
					}else if($woman){
						 $gender = 'Female';
					}else if($man) {
						$gender = 'male';
					}else{
						$gender = '';
					}
				
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					$option_data  = [];

					$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

					foreach($options as $option)
					{
						if($option['type'] != 'file')
						{
							$value = $option['value'];
						}
						else
						{
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if($upload_info)
							{
								$value = $upload_info['name'];
							}
							else
							{
								$value = '';
							}
						}

						$option_data[] = array(
							'name' => $option['name'],
							'value'=> $value
						);
					}

					$product_data[] = array(
						'name'    => $product_info['name'],
						'model'   => $product_info['model'],
						'gender'   => $gender,
						'cat'   => $categ,
						'option'  => $option_data,
						'quantity'=> $product['quantity'],
						'location'=> $product_info['location'],
						'sku'     => $product_info['sku'],
						'upc'     => $product_info['upc'],
						'ean'     => $product_info['ean'],
						'jan'     => $product_info['jan'],
						'isbn'    => $product_info['isbn'],
						'mpn'     => $product_info['mpn'],
						'weight'  => $this->weight->format($product_info['weight'], $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'))
					);
				}

				$data['orders'][] = array(
					'order_id'        => $order_id,
					'invoice_no'      => $invoice_no,
					'date_added'      => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'name'            => $order_info['shipping_firstname'].' '.$order_info['shipping_lastname'],
					'store_name'      => $order_info['store_name'],
					'store_url'       => rtrim($order_info['store_url'], '/'),
					'store_address'   => nl2br($store_address),
					'store_email'     => $store_email,
					'store_telephone' => $store_telephone,
					'store_fax'       => $store_fax,
					'email'           => $order_info['email'],
					'status'           => $order_info['status'],
					'telephone'       => $order_info['telephone'],
					'shipping_address'=> $shipping_address,
					'shipping_method' => $order_info['shipping_method'],
					'country'         => $order_info['shipping_country'],
					'zone'            => $order_info['shipping_zone'],
					'total'            => $order_info['total'],
					'tax'            => $tax,
					'product'         => $product_data,
					'comment'         => nl2br($order_info['comment'])
				);
			}
		}

		$data['text_order_titles'] = $this->language->get('text_order_titles');

		$this->model_bytao_export->download($data);

	}

	public function exportxls()
	{
		$this->load->language('bytao/export');
		$this->load->model('bytao/export');

		$data['products'] = [];
		$qry = '';
		if(isset($this->request->post['qry']))
		{
			$qry = $this->request->post['qry'];
		}

		

		$this->model_bytao_export->downloadXLS($qry);

	}
	
	public function exportproductzip()
	{
		$products = [];
		
		if(isset($this->request->post['selected']))
		{
			$products = $this->request->post['selected'];
		}
		elseif(isset($this->request->get['order_id']))
		{
			$products[] = $this->request->get['order_id'];
		}else{
			return false;	
		}


		$zipname = 'image-export-'.date('m-d-Y-H-i').'.zip';
		$zip = new ZipArchive;
		$zip->open(DIR_ZIP.$zipname, ZipArchive::CREATE);
		foreach ($products as $result) {
			$sql = "SELECT * FROM ".DB_PREFIX."product_image WHERE product_id=".$result;
			$pImages = $this->db->query( $sql );
			foreach($pImages->rows as $image ){
				if (is_file(DIR_IMAGE . $image['image'])) {
					$zip->addFile(DIR_IMAGE.$image['image'],$image['image']);
					
				} 

				/*
				$ımgData	=	$this->model_bytao_image->getProductImages($result['product_id']);
				
				foreach($ımgData AS $tImage){
						if (is_file(DIR_IMAGE . $tImage['image'])) {
							$zip->addFile(DIR_IMAGE.$tImage['image'],$tImage['image']);
						} 
				}
				*/
			}
				
		}
		
		$zip->close();
		$this->response->redirect(HTTPS_ZIP.$zipname);

	}

	public function product()
	{
		$this->load->language('bytao/export');
		$this->load->model('bytao/export');
		$this->load->model('catalog/product');
		$this->load->model('setting/setting');

		$data['products'] = [];

		$products = [];

		if(isset($this->request->post['selected']))
		{
			$selected = explode('&',$this->request->post['selected']);
			foreach($selected as $value){
				$con = explode('=',$value);
				if(isset($con[1])){
					$products []=$con[1];
				}
			}
			
			
		}
		elseif(isset($this->request->get['order_id']))
		{
			$products[] = $this->request->get['order_id'];
		}

		$data['productIDs'] = $products;
		$data['texts']['products'] = $this->language->get( 'text_products' );
		$data['texts']['references'] = $this->language->get( 'text_references' );
		$data['texts']['category_id'] = $this->language->get( 'text_category_id' );
		$data['texts']['category_name'] = $this->language->get( 'text_category_name' );
		$data['texts']['last_product_id'] = $this->language->get( 'text_last_product_id' );

		/*
		foreach ($products as $product_id) {
		$product_info = $this->model_catalog_product->getProduct($product_id);
		$category = $this->model_catalog_product->getProductCategoryNames($product_id);

		$data['products'][] = array(
		'image'     => $product_info['image'],
		'category'    => $category,
		'name'    => $product_info['name'],
		'model'    => $product_info['model'],
		'description'    => $product_info['description'],
		'option_name'    => $product_info['price'],
		'option_value'    => $product_info['price'],
		'quantity'    => $product_info['quantity'],
		'price'    => $product_info['price'],
		'total'    => $product_info['price']*$product_info['quantity'],
		);

		}
		*/
		
		
		
		$data['text_product_titles'] = $this->language->get('text_product_titles');

		$this->model_bytao_export->download($data);

	}

	public function customer()
	{

		$this->load->language('bytao/export');

		$this->load->model('sale/customer');

		$data['customers'] = [];

		if(isset($this->request->get['filter_name']))
		{
			$filter_name = $this->request->get['filter_name'];
		}
		else
		{
			$filter_name = null;
		}

		if(isset($this->request->get['filter_email']))
		{
			$filter_email = $this->request->get['filter_email'];
		}
		else
		{
			$filter_email = null;
		}

		if(isset($this->request->get['filter_customer_group_id']))
		{
			$filter_customer_group_id = $this->request->get['filter_customer_group_id'];
		}
		else
		{
			$filter_customer_group_id = null;
		}

		if(isset($this->request->get['filter_status']))
		{
			$filter_status = $this->request->get['filter_status'];
		}
		else
		{
			$filter_status = null;
		}

		if(isset($this->request->get['filter_approved']))
		{
			$filter_approved = $this->request->get['filter_approved'];
		}
		else
		{
			$filter_approved = null;
		}

		if(isset($this->request->get['filter_ip']))
		{
			$filter_ip = $this->request->get['filter_ip'];
		}
		else
		{
			$filter_ip = null;
		}
		if(isset($this->request->get['filter_date_added']))
		{
			$filter_date_added = $this->request->get['filter_date_added'];
		}
		else
		{
			$filter_date_added = null;
		}

		if(isset($this->request->get['sort']))
		{
			$sort = $this->request->get['sort'];
		}
		else
		{
			$sort = 'name';
		}

		if(isset($this->request->get['order']))
		{
			$order = $this->request->get['order'];
		}
		else
		{
			$order = 'ASC';
		}

		$filter_data = array(
			'filter_name'             => $filter_name,
			'filter_email'            => $filter_email,
			'filter_customer_group_id'=> $filter_customer_group_id,
			'filter_status'           => $filter_status,
			'filter_approved'         => $filter_approved,
			'filter_date_added'       => $filter_date_added,
			'filter_ip'               => $filter_ip,
			'sort'                    => $sort,
			'order'                   => $order,
			'start'                   => NULL,
			'limit'                   => NULL
		);

		$results = $this->model_sale_customer->getCustomers($filter_data);

		foreach($results as $result)
		{

			if(!$result['approved'])
			{
				$approve = 0;
			}
			else
			{
				$approve = 1;
			}

			$orders     = $this->model_sale_customer->getTotalOrderCustomer($result['customer_id']);
			$login_info = $this->model_sale_customer->getTotalLoginAttempts($result['email']);

			if($login_info && $login_info['total'] >= $this->config->get('config_login_attempts'))
			{
				$unlock = 0;
			}
			else
			{
				$unlock = 1;
			}

			$data['customers'][] = array(
				'customer_id'   => $result['customer_id'],
				'name'          => $result['name'],
				'email'         => $result['email'],
				'order_counts'  => $orders,
				'customer_group'=> $result['customer_group'],
				'status'        => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'approve'       => $approve
			);
		}

		$this->load->model('bytao/export');
		$this->model_bytao_export->download($data);

	}


	public function import_ex()
	{

		// dimentions
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE option_id=15 OR option_id=13 ");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE option_id=15 OR option_id=13");

		$query = $this->db->query("SELECT * FROM ast_products");
		$p     = count($query->rows);
		foreach($query->rows as $result)
		{
			$p++;
			echo $p." - ";
			if(!is_null($result['ProductName']))
			{

				$product_id = $result['ID'];

				$queryAttr  = $this->db->query("SELECT * FROM ast_productattributes WHERE ProductRef  = '".(int)$product_id ."'");
				foreach($queryAttr->rows as $rowA)
				{
					switch($rowA['AttributeRef'])
					{
						case "2":
						$option_id = 13;
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '".(int)$option_id."',value = '".$rowA['AttrValue']."', required = '1'");


						break;


					}
				}

			}

		}






		// Import Colors
		// *****************
		/*
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=13");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=38");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=39");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=40");

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=13");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=38");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=39");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=40");

		$gQueryn = $this->db->query("SELECT * FROM ast_groups");
		$sOrder=0;
		foreach ($gQueryn->rows as $gResult) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = 'radio', sort_order = '".(int)($sOrder++)."'");
		$option_id = $this->db->getLastId();
		$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '1', name = '" . $this->db->escape($gResult['GroupName']) . "'");
		$oQueryn = $this->db->query("SELECT * FROM ast_groupcolours WHERE GroupRef='".(int)$gResult['ID'] ."'");
		$cOrder=0;
		foreach ($oQueryn->rows as $oResult) {
		$cQueryn = $this->db->query("SELECT ID,ColourName FROM ast_colours WHERE ID = '".(int)$oResult['ColourRef'] ."' LIMIT 1");


		$sQueryn = $this->db->query("SELECT * FROM ast_productswatches WHERE content LIKE '%" . $cQueryn->row['ColourName']."' LIMIT 1");
		$nevImage='';
		if (isset($sQueryn->row['BigImg'])){
		$image = $sQueryn->row['BigImg'];
		$imageEx = explode('/',$image);
		$nevImage='catalog/colors/' .$imageEx[(count($imageEx)-1)];

		if (is_file(DIR_IMAGE2.  $sQueryn->row['BigImg'])) {
		//copy(DIR_IMAGE2.  $sQueryn->row['BigImg'], DIR_IMAGE .$nevImage );
		}
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', image = '" . $this->db->escape(html_entity_decode($nevImage, ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)($cOrder++). "'");

		$option_value_id = $this->db->getLastId();

		$this->db->query("UPDATE ast_colours SET option_id='".(int)$option_id ."', option_value_id='".(int)$option_value_id ."' WHERE ID = '".(int)$cQueryn->row['ID'] ."'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '1', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($cQueryn->row['ColourName']) . "'");


		}

		}
		*/

		/*


		// Kategori
		$c=0;
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_path");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store ");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query like 'category_id=%'");

		$this->cache->delete('category');

		$query = $this->db->query("SELECT * FROM ast_categories");
		$c = count($query->rows);
		foreach ($query->rows as $result) {

		$category_id = $result['ID'];
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET  category_id = '" . (int)$category_id . "', parent_id = '" . (int)$result['ParentCategoryRef'] . "',`image` = '', `top` = '1', `column` = '3', sort_order = '" . (int)$result['SortNumber'] . "', status = '" . (int)$result['Status'] . "', date_modified = NOW(), date_added = NOW()");

		$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', name = '" . $this->db->escape($result['CategoryName']) . "', description = '" . $this->db->escape($result['Info']) . "', meta_title = '" . $this->db->escape($result['CategoryName']) . "', meta_description = '', meta_keyword = ''");



		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query1 = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$result['ParentCategoryRef'] . "' ORDER BY `level` ASC");

		foreach ($query1->rows as $result1) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result1['path_id'] . "', `level` = '" . (int)$level . "'");

		$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");


		$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '0'");


		// Set which layout to use with this category

		$urlLink = explode('.html',$result['GoogleLink']);

		$keyword = $urlLink[0];

		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");

		}

		*/



		/**
		*
		* @var /
		*
		*/
		// Products

		/*
		{


		$p=0;

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=11");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=15");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id=16");


		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=11");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=15");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id=16");



		$this->db->query("DELETE FROM " . DB_PREFIX . "product");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute ");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter ");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image_description");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store");
		$this->db->query("DELETE FROM " . DB_PREFIX . "review");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query like 'product_id=%'");



		$this->cache->delete('product');

		$query = $this->db->query("SELECT * FROM ast_products");
		$p=count($query->rows);
		foreach ($query->rows as $result) {
		$p++;
		echo $p." - ";
		if(!is_null($result['ProductName'])){

		$product_id = $result['ID'];
		//$this->db->query("UPDATE ast_products SET product_id='".(int)$option_id ."' WHERE ID = '".(int)$cQueryn->row['ID'] ."'");

		$directory = DIR_IMAGE . 'catalog/products/' . (int)$product_id;
		//$directory2 = DIR_IMAGE_2 . 'catalog/products/' . (int)$product_id;
		//$directory3 = DIR_IMAGE_3 . 'catalog/products/' . (int)$product_id;

		if (!is_dir($directory)) {
		mkdir($directory , 0777);
		//mkdir($directory2 , 0777);
		//mkdir($directory3 , 0777);

		}

		$sql  = "INSERT INTO " . DB_PREFIX . "product  SET product_id ='".(int)$product_id ."', model = '" . $this->db->escape($result['Style']) . "', sku = '', upc = '', ean = '', jan = '', isbn = '', mpn = '', location = '', quantity = '1', minimum = '1', subtract = '', stock_status_id = '0', date_available = NOW(), manufacturer_id = '', shipping = '1', cost_price = '" . $this->db->escape($result['CostPrice']) . "', sale_price = '" . $this->db->escape($result['SalePrice']) . "', price = '" . $this->db->escape($result['RetailPrice']) . "',our_price = '" . $this->db->escape((isset($result['OurPrice'])?$result['OurPrice']:0)) . "',whole_sale_price = '" . $this->db->escape($result['WholeSalePrice']) . "', points = '', weight = '', weight_class_id = '', length = '', width = '', height = '', length_class_id = '', status = '".$this->db->escape($result['Status'])."', tax_class_id = '0', new_arriwals = '".$this->db->escape($result['New'])."', regular= '".$this->db->escape($result['Regular'])."', clearance= '".$this->db->escape($result['Clearance'])."', whole_sale= '1', gift= '".$this->db->escape($result['Gift'])."', sale= '".$this->db->escape($result['Sale'])."', best= '".$this->db->escape($result['Best'])."'    ,sort_order = '0', date_added = NOW()";

		$this->db->query($sql);



		$meta_keywords = '';
		$meta_description = $result['ProductName'];
		$productDescription = $result['Info'];
		$productName = $result['ProductName'];

		$sql2 = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`,`language_id`,`name`,`description`,`meta_description`,`meta_keyword`, `meta_title` ) VALUES ";
		$sql2 .= "('".(int)$product_id ."','".$this->config->get('config_language_id')."','".$this->db->escape($result['ProductName'])."','".$this->db->escape($result['Info'])."','".$this->db->escape($result['ProductName'])."','','".$this->db->escape($result['ProductName'])."');";
		$this->db->query($sql2);



		// urun ozellikleri SIZE
		// Import Atribute


		$queryAttr = $this->db->query("SELECT * FROM ast_productattributes WHERE ProductRef  = '".(int)$product_id ."'");

		foreach($queryAttr->rows as $rowA){

		switch($rowA['AttributeRef']){
		case "1":
		$option_id = 11;
		break;

		case "2":
		$option_id = 15;
		break;

		case "3":
		$option_id = 16;
		break;
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '".(int)$option_id."', required = '1'");

		$product_option_id = $this->db->getLastId();

		$optiondatas = explode(',',$rowA['AttrValue']);

		if($optiondatas){

		foreach( $optiondatas as $optiondata){
		switch(trim($optiondata)){
		case '2XL':
		case '2XLarge': $deger="XXL"; break;
		case '3XL':
		case '3XLarge': $deger="XXXL"; break;
		case 'XLarge': $deger="XL"; break;
		case 'Large': $deger="L"; break;
		case 'Small': $deger="S"; break;
		case 'Medium': $deger="M"; break;
		default:
		$deger=$optiondata;

		}


		$queryOption = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "' AND  name = '".trim($deger) ."' LIMIT 1");


		if(isset($queryOption->row['option_value_id'])&&($queryOption->row['option_value_id']!="0")){

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', option_value_id = '".(int)$queryOption->row['option_value_id']."', quantity = '1', subtract = '', price = '', price_prefix = '', points = '', points_prefix = '', weight = '', weight_prefix = ''");

		} else{

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "',sort_order = '0'");
		$option_value_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '1', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape(trim($deger)) . "'");


		$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', option_value_id = '".(int)$option_value_id."', quantity = '1', subtract = '', price = '', price_prefix = '', points = '', points_prefix = '', weight = '', weight_prefix = ''");
		}
		}
		}





		}


		$queryOptColor = $this->db->query("SELECT * FROM ast_colours ac RIGHT JOIN ast_productcolours ap ON ( ac.ID = ap.ColourRef)  WHERE ap.ProductRef  = '".(int)$product_id ."' GROUP BY ac.option_id");


		foreach($queryOptColor->rows as $option){

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '".(int)$option['option_id']."', required = '1'");

		}





		$product_option_id = $this->db->getLastId();

		$queryOpt = $this->db->query("SELECT * FROM ast_productcolours WHERE ProductRef  = '".(int)$product_id ."'");

		foreach($queryOpt->rows as $rowO){

		$queryOptVal = $this->db->query("SELECT * FROM ast_colours WHERE ID  = '".(int)$rowO['ColourRef'] ."' LIMIT 1");


		if(isset($queryOptVal->row['option_id'])&&($queryOptVal->row['option_id']!= "0")){
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '".(int)$queryOptVal->row['option_id']."', option_value_id = '".(int)$queryOptVal->row['option_value_id']."', quantity = '1', subtract = '', price = '', price_prefix = '', points = '', points_prefix = '', weight = '', weight_prefix = ''");
		}

		}




		// resimleri



		//


		$queryColors = $this->db->query("SELECT * FROM ast_productcolours WHERE ProductRef  = '".(int)$product_id ."'");


		if($queryColors){
		foreach($queryColors->rows as $rowC){
		$queryImage = $this->db->query("SELECT pd.*, c.option_value_id AS color_id FROM ast_productdetails pd LEFT JOIN ast_productcolours pc ON (pd.ProductColourRef = pc.ID) LEFT JOIN ast_colours c ON pc.ColourRef = c.ID WHERE ProductColourRef  = '".(int)$rowC['ID'] ."'");
		if($queryImage){
		foreach($queryImage->rows as $rowI){
		$image = $rowI['BigImg'];
		$imageEx = explode('/',$image);

		$nevImage='catalog/products/' . (int)$product_id.'/'.$imageEx[(count($imageEx)-1)];


		$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($nevImage). "', color_id='". (int)$rowI['color_id'] . "', sort_order='". (int)$rowI['SortNumber'] . "'");

		$product_image_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_image_description SET product_image_id = '" . (int)$product_image_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', description = '" . $this->db->escape($rowI['Content']) . "'");



		if (is_file(DIR_IMAGE2. $rowI['BigImg'])) {
		//copy(DIR_IMAGE2. $rowI['BigImg'], DIR_IMAGE .$nevImage );
		} else {
		$bigImg=str_replace ('big','thumb',$rowI['BigImg']);
		if (is_file(DIR_IMAGE2. $bigImg)) {
		//copy(DIR_IMAGE2. $bigImg, DIR_IMAGE .$nevImage );
		}

		}

		//if (is_file(DIR_IMAGE .$nevImage)) {
		//	copy(DIR_IMAGE .$nevImage, DIR_IMAGE_2 .$nevImage );
		//	copy(DIR_IMAGE .$nevImage, DIR_IMAGE_3 .$nevImage );
		//}


		}
		}
		}

		}else if($result['BigImg']) {
		$image = $result['BigImg'];
		$imageEx = explode('/',$image);

		$nevImage='catalog/products/' . (int)$product_id.'/'.$imageEx[(count($imageEx)-1)];
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($nevImage). "', color_id='0', sort_order='0'");

		$product_image_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_image_description SET product_image_id = '" . (int)$product_image_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', description = ''");


		if (is_file(DIR_IMAGE_2.  $result['BigImg'])) {
		//copy(DIR_IMAGE_2.  $result['BigImg'], DIR_IMAGE .$nevImage );
		} else {
		$bigImg=str_replace ('big','thumb',$rowI['BigImg']);
		//copy(DIR_IMAGE_2. $bigImg, DIR_IMAGE .$nevImage );
		}
		//copy(DIR_IMAGE .$nevImage, DIR_IMAGE_2 .$nevImage );
		//copy(DIR_IMAGE .$nevImage, DIR_IMAGE_3 .$nevImage );

		}







		$queryP = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		if (isset($queryP->row['image'])){
		$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($queryP->row['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$queryCategory = $this->db->query("SELECT * FROM ast_productcategories WHERE ProductRef  = '".(int)$product_id ."'");

		foreach($queryCategory->rows as $rowCt){
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$rowCt['CategoryRef'] . "' ");


		//			$cat = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$rowCt['CategoryRef'] . "' LIMIT 1");
		//			if( $cat->row['parent_id']!= 0) {
		//				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$cat->row['parent_id'] . "' ");
		//				$cat1 = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$cat->row['parent_id'] . "'  LIMIT 1");
		//				if($cat1->row['parent_id']!= 0) {
		//					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$cat1->row['parent_id'] . "' ");
		//					$cat2 = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$cat1->row['parent_id'] . "' LIMIT 1");
		//					if($cat2->row['parent_id']!= 0) {
		//						$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$cat2->row['parent_id'] . "' ");
		//					}

		//				}

		//			}



		}

		$keyword = $result['GoogleLink'];
		$sql4 = "INSERT INTO `".DB_PREFIX."url_alias` (`query`,`keyword`) VALUES ('product_id=". (int)$product_id ."','".$keyword."');";
		$this->db->query($sql4);

		$sql6 = "INSERT INTO `".DB_PREFIX."product_to_store` (`product_id`,`store_id`) VALUES (". (int)$product_id .",0);";
		$this->db->query($sql6);

		$sql7 = "INSERT INTO `".DB_PREFIX."product_to_layout` (`product_id`,`store_id`,`layout_id`) VALUES (". (int)$product_id .",0,0);";
		$this->db->query($sql7);

		}
		}


		}
		*/
		echo "Import tamamlandi :  ".$p." --  adet urun.";


		/**/


		/*
		// reviews

		$this->db->query("DELETE FROM " . DB_PREFIX . "review");
		$queryReviews = $this->db->query("SELECT * FROM ast_comments");

		foreach ($queryReviews->rows as $result){
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($result['NickName'] ) . "', product_id = '" . (int)$result['ProductRef']  . "', text = '" . $this->db->escape(strip_tags($result['Info'])) . "', rating = '" . (int)$result['StarCount'] . "', mainpage = '1', status = '" . (int)$result['Status'] . "', date_added = '" . $this->db->escape($result['CreateDate']) . "'");

		}
		*/


		/*


		// Opsiyonlar

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '13'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '13'");

		$queryn = $this->db->query("SELECT * FROM ast_colours");

		foreach ($queryn->rows as $result) {
		$option_value_id = $result['ID'];

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '13',option_value_id = '" . (int)$option_value_id . "', image = '', sort_order = '" . $result['ID'] . "'");


		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . $this->config->get('config_language_id') . "', option_id = '13', name = '" . $this->db->escape($result['ColourName']) . "'");

		}

		// Yorumlar
		$this->db->query("DELETE FROM " . DB_PREFIX . "review");

		$queryReviews = $this->db->query("SELECT * FROM ast_comments");

		foreach ($queryReviews->rows as $result){
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($result['NickName'] ) . "', product_id = '" . (int)$result['ProductRef']  . "', text = '" . $this->db->escape(strip_tags($result['Info'])) . "', rating = '" . (int)$result['StarCount'] . "', mainpage = '1', status = '" . (int)$result['Status'] . "', date_added = '" . $this->db->escape($result['CreateDate']) . "'");

		}


		// Renkler
		-------------------------
		ID
		ColourName
		ColourCode
		Status
		CreateDate
		LastUpdate

		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '13'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '13'");


		$query = $this->db->query("SELECT * FROM ast_colours");

		foreach ($query->rows as $result) {
		$option_value_id = $result['ID'];

		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '13',option_value_id = '" . (int)$option_value_id . "', image = '', sort_order = '0'");


		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" .  . "', option_id = '13', name = '" . $this->db->escape($result['ColourName']) . "'");

		}




		kategori alanları
		--------------------
		ID,
		CategoryName,
		ImgPath
		ParentCategoryRef
		Info
		SortNumber
		Status
		CreateDate
		LastUpdate
		LongCategoryName2
		LongCategoryName
		isProduct
		CategoryHeaderLink
		GoogleLink
		ProductCount
		NewArrivalsCount
		ClearanceCount
		GiftIdeasCount
		SaleItemsCount
		BestSellerCount

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_path");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store ");
		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query like 'category_id='");

		$this->cache->delete('category');

		$query = $this->db->query("SELECT * FROM ast_categories");
		foreach ($query->rows as $result) {
		$category_id = $result['ID'];
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET  category_id = '" . (int)$category_id . "', parent_id = '" . (int)$result['ParentCategoryRef'] . "',`image` = '', `top` = '1', `column` = '3', sort_order = '" . (int)$result['SortNumber'] . "', status = '1', date_modified = NOW(), date_added = NOW()");

		$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', name = '" . $this->db->escape($result['CategoryName']) . "', description = '" . $this->db->escape($result['Info']) . "', meta_title = '" . $this->db->escape($result['CategoryName']) . "', meta_description = '', meta_keyword = ''");



		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query1 = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$result['ParentCategoryRef'] . "' ORDER BY `level` ASC");

		foreach ($query1->rows as $result1) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result1['path_id'] . "', `level` = '" . (int)$level . "'");

		$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");


		$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '0'");


		// Set which layout to use with this category

		$urlLink=explode('.html',$result['useolink_en'])
		$keyword = $result['GoogleLink'];

		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");



		}








		*/






	}

	public function upload()
	{
		$json = [];
		$this->getML('LM');
		
		if(isset($this->request->post['filename']) && isset($this->request->post['check'])){
			$this->model->upload(DIR_IMPORT . $this->request->post['filename'],$this->request->post['check']);
			
			$json['success'] = $this->language->get('text_success');
		}else{
			
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	protected function validateUploadForm()
	{

		if(!$this->user->hasPermission('modify', 'bytao/export'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if(!isset($this->request->files['upload']['name']))
		{
			if(isset($this->error['warning']))
			{
				$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_name' );
			}
			else
			{
				$this->error['warning'] = $this->language->get( 'error_upload_name' );
			}
		}
		else
		{
			$ext = strtolower(pathinfo($this->request->files['upload']['name'], PATHINFO_EXTENSION));
			if(($ext != 'xls') && ($ext != 'xlsx') && ($ext != 'ods'))
			{
				if(isset($this->error['warning']))
				{
					$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_ext' );
				}
				else
				{
					$this->error['warning'] = $this->language->get( 'error_upload_ext' );
				}
			}
		}

		if(!$this->error)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function errorOut()
	{
		$json = [];
		$this->load->language('bytao/export');

		if(!empty($this->session->data['export_import_error']['errstr']))
		{
			$json['warning'] = $this->session->data['export_import_error']['errstr'];
		}

		if(isset($json['warning']))
		{
			$json['error_warning'] = $this->error['warning'];
			if(!empty($this->session->data['export_import_nochange']))
			{
				$json['error_warning'] .= "\n".$this->language->get( 'text_nochange' );
			}
		}

		unset($this->session->data['export_import_error']);
		unset($this->session->data['export_import_nochange']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function uploadxls()
	{
		$json = [];

		$this->load->language('bytao/export');
		$this->load->model('bytao/export');

		if(($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateUploadForm()))
		{

			if((isset( $this->request->files['upload'] )) && (is_uploaded_file($this->request->files['upload']['tmp_name'])))
			{


				//$filename = $this->request->files['upload']['name'];
				//$this->log->write('filename:'.$filename);
				$filename = basename(html_entity_decode($this->request->files['upload']['name'], ENT_QUOTES, 'UTF-8'));
				move_uploaded_file($this->request->files['upload']['tmp_name'],  DIR_IMPORT_XLS . $filename);


				$file     = $this->request->files['upload']['tmp_name'];
				$type     = $this->request->post['up_type'];

				if($this->model_bytao_export->uploadXLS(DIR_IMPORT_XLS . $filename,$type) == true)
				{
					$json['success'] = $this->language->get('text_success');
					$json['redirect'] = $this->url->link('tool/export_import', 'token=' . $this->session->data['token'], $this->ssl);

				}
				else
				{
					$json['error'] = $this->language->get('error_upload').'!!!';

				}
			}
			else
			{

			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function uploadzip()
	{
		$json = [];

		$this->load->language('bytao/export');
		$this->load->model('bytao/export');

		if(($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateUploadForm()))
		{

			if((isset( $this->request->files['upload'] )) && (is_uploaded_file($this->request->files['upload']['tmp_name'])))
			{

				$filename = basename(html_entity_decode($this->request->files['upload']['name'], ENT_QUOTES, 'UTF-8'));
				move_uploaded_file($this->request->files['upload']['tmp_name'],  DIR_IMPORT_XLS . $filename);


				$file     = $this->request->files['upload']['tmp_name'];
				$type     = $this->request->post['up_type'];

				if($this->model_bytao_export->uploadZip(DIR_IMPORT_XLS . $filename,$type) == true)
				{
					$json['success'] = $this->language->get('text_success');
					$json['redirect'] = $this->url->link('tool/export_import', 'token=' . $this->session->data['token'], $this->ssl);

				}
				else
				{
					$json['error'] = $this->language->get('error_upload').'!!!';

				}
			}
			else
			{

			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	

	private function array_msort($array, $cols)
	{
		$ret = [];
		$colarr = [];
		if(count($array) > 0)
		{
			foreach($cols as $col => $order)
			{
				$colarr[$col] = [];
				foreach($array as $k => $row)
				{
					$colarr[$col]['_'.$k] = strtolower($row[$col]);
				}
			}
			$eval = 'array_multisort(';
			foreach($cols as $col => $order)
			{
				$eval .= '$colarr[\''.$col.'\'],'.$order.',';
			}
			$eval = substr($eval,0, - 1).');';
			eval($eval);

			foreach($colarr as $col => $arr)
			{
				foreach($arr as $k => $v)
				{
					$k = substr($k,1);
					if(!isset($ret[$k])) $ret[$k] = $array[$k];
					$ret[$k][$col] = $array[$k][$col];
				}
			}

		}

		return $ret;

	}

	private function firstImage($produc_id)
	{
		$product_info = $this->model_catalog_product->getProduct($produc_id);

		if($product_info)
		{

			$data['options'] = [];
			$OPTIONS = $this->model_catalog_product->getProductOptions($produc_id);

			foreach( $OPTIONS as $option)
			{

				$product_option_value_data = [];

				foreach($option['product_option_value'] as $option_value)
				{
					$product_option_value_data[] = array(
						'option_id'              => $option['option_id'],
						'product_option_value_id'=> $option_value['product_option_value_id'],
						'product_option_id'      => $option_value['product_option_id'],
						'option_value_id'        => $option_value['option_value_id'],

					);

				}



				$data['options'][] = array(
					'product_option_id'   => $option['product_option_id'],
					'product_option_value'=> $product_option_value_data,
					'option_id'           => $option['option_id'],
					'type'                => $option['type'],
					'value'               => $option['value'],
					'required'            => $option['required']
				);
			}

			$firstimg_id = 0;
			foreach($data['options'] as $option)
			{
				if($option['type'] == 'radio')
				{
					$vals        = reset($option['product_option_value']);
					$firstimg_id = $vals['option_value_id'];
					break;
				}
			}
			$data['images'] = [];

			$results = $this->model_catalog_product->getProductImages($produc_id);

			foreach($results as $result)
			{
				$data['images'][] = array(
					'image'   => $result['image'],
					'color_id'=>$result['color_id']
				);
			}

			$firstimg = '';

			foreach($data['images'] as $image)
			{
				if($firstimg_id == $image['color_id'])
				{
					$firstimg = $image['image'];
					break;
				}
			}

			if($firstimg != '')
			{
				$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $firstimg . "' WHERE product_id='".(int)$product_info['product_id']."'");

			}

			$this->cache->delete('product');
			$this->cache->delete('mProducts.'.$this->session->data['store_id'] );

		}

	}

	protected function return_bytes($val)
	{
		$val = trim($val);

		switch(strtolower(substr($val, - 1))){
			case 'm': $val = (int)substr($val, 0, - 1) * 1048576; break;
			case 'k': $val = (int)substr($val, 0, - 1) * 1024; break;
			case 'g': $val = (int)substr($val, 0, - 1) * 1073741824; break;
			case 'b':
			switch(strtolower(substr($val, - 2, 1))){
				case 'm': $val = (int)substr($val, 0, - 2) * 1048576; break;
				case 'k': $val = (int)substr($val, 0, - 2) * 1024; break;
				case 'g': $val = (int)substr($val, 0, - 2) * 1073741824; break;
				default : break;
			} break;
			default: break;
		}
		return $val;
	}

	public function prod()
	{
		$json = [];
		if(isset($this->request->get['prod_id']))
		{
			$data['prod_id']=$this->request->get['prod_id'];
			$this->load->model('tool/image');
			$this->load->model('catalog/product');
			$this->load->model('catalog/category');

			$thisProd    = $this->model_catalog_product->getProduct($this->request->get['prod_id']);
			$thisProdOps = $this->model_catalog_product->getProductOptions($this->request->get['prod_id']);

			$sql         = "SELECT p.product_id as prodId FROM `" . DB_PREFIX . "product` p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE (pd.name LIKE LOWER('%" . $this->db->escape($thisProd['name']) . "%') OR p.model LIKE LOWER('%" . $this->db->escape(str_replace('-','',$thisProd['model'])) . "%')) AND p.product_id NOT LIKE ".$thisProd['product_id'];
			$results     = $this->db->query($sql);
			//$this->log->write('Array: '. print_r($results,TRUE));
			$data['column_price'] = 'Price';
			$data['products'] = [];
			if($results)
			{
				foreach($results as $rec){
					$result = $this->model_catalog_product->getProduct($rec['prodId']);
					if($result['product_id'])
					{

						$prOp = $this->model_catalog_product->getProductOptions($result['product_id']);
						
						if(is_file(DIR_IMAGE . $result['image']))
						{
							$image = $this->model_tool_image->resize($result['image'], 150, 150);
						}
						else
						{
							$image = $this->model_tool_image->resize('no_image.png', 150, 150);
						}

						$special = false;

						$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

						foreach($product_specials  as $product_special)
						{
							if(($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time()))
							{
								$special = $product_special['price'];

								break;
							}
						}



						$data['products'][] = array(
							'product_id'  => $result['product_id'],
							'image'       => $image,
							'name'        => $result['name'],
							'model'       => $result['model'],
							'price'       => number_format($result['price'],2),
							'special'     => $special,
							'subtract'    => $result['subtract'],
							'quantity'    => $result['quantity'],
							'options'    => $prOp,
							'best'        => $result['best'],
							'gift'        => $result['gift'],
							'sale'        => $result['sale'],
							'sale_price'  => number_format($result['sale_price'],2),
							'clearance'   => $result['clearance'],
							'new_arriwals'=> $result['new_arriwals'],
							'mpage'       => $result['mpage'],
							'status_id'   => $result['status'],
							'status'      => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
							'edit'        => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'], 'SSL')
						);
					}
				}

			}



			$json['view'] = $this->load->view('bytao/product_sim.tpl', $data);
			$json['success'] = $thisProd['model'];
		}
		else
		{
			$json['error'] = '!!!';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function whoto(){
		$json = [];
		if(isset($this->request->get['who_id']) && isset($this->request->get['to_id']))
		{
			$json['who'] = $whoId=$this->request->get['who_id'];
			$json['to'] = $toId=$this->request->get['to_id'];
			$this->load->model('catalog/product');
			$whopt = $this->getProductOptions($whoId);
			$topt = $this->getProductOptions($toId);
			//$this->log->write('whopt: '. print_r($whopt,TRUE));
			//$this->log->write('topt: '. print_r($topt,TRUE));
			
			
			//$json['opt']=	print_r($topt,TRUE);
			
			foreach($topt as $ind => $opt){
				
				if(isset($whopt[$ind])){
					
					foreach($opt['product_option_value'] AS $in=> $optv){
						
						if(!isset($whopt[$ind]['product_option_value'][$in])){
							//$this->log->write('topt: '. print_r($optv,TRUE));
							if($optv['type']==2){
								$sql="INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$whopt[$ind]['product_option_id'] . "', product_id = '" . (int)$whoId . "', option_id = '" . (int)$ind . "', option_value_id = '" . (int)$in . "', quantity = '" . (int)$optv['quantity'] . "', subtract = '" . (int)$optv['subtract'] . "', price = '" . (float)$optv['price'] . "', price_prefix = '" . $this->db->escape($optv['price_prefix']) . "', points = '" . (int)$optv['points'] . "', points_prefix = '" . $this->db->escape($optv['points_prefix']) . "', weight = '" . (float)$optv['weight'] . "', weight_prefix = '" . $this->db->escape($optv['weight_prefix']) . "',sort_order = '" . (int)$optv['sort_order'] . "',type = '" . (int)$optv['type'] . "' ";
								$this->db->query($sql);
							}
							
							//$json['sql']=	$sql;						
						}
					}
					
				}else{
					
					
					
					
				}
				
			}
			
			
			//$who= $this->db->query("SELECT " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "',sort_order = '" . (int)$product_option_value['sort_order'] . "',type = '" . (int)$product_option_value['type'] . "' ");
			
			
			
			
			
			$json['whopt']='';
			$whopt = $this->getProductOptions($whoId);
			
			foreach($whopt as $ind => $opt){
             	$json['whopt'].= $opt['name'].':';
             	$vals=[];
	             foreach($opt['product_option_value'] as $op){
				 	$vals[]=$op['name'];
				 }
				$json['whopt'].= implode(',',$vals).'<br/>';
			}
			
		}
		else
		{
			$json['error'] = '!!!';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getProductOptions($product_id,$option_id=0 ) {
		$product_option_data = [];

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");
		
		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = [];
			
			if($option_id==0){
				$sql = "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'  ORDER BY pov.sort_order ASC";
				
			}else{
				/*$sql = "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '0' AND pov.option_id = '" . (int)$product_option['option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pov.sort_order ASC";*/
			$sql = "SELECT *,pov.type as typ FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND (pov.type = '0' OR pov.type = '2') ORDER BY pov.sort_order ASC";	
				
			}
			
			
			
			$product_option_value_query = $this->db->query($sql);
			


			foreach ($product_option_value_query->rows as $product_option_value) {
				
				$product_option_value_data[$product_option_value['option_value_id']] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'product_option_id' 		=> $product_option['product_option_id'],
					'option_id' 				=> $product_option['option_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'type'                    => $product_option_value['type'],
					'name'                    => $product_option_value['name'],
					'image'                   => $product_option_value['image'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
					'sort_order'           => $product_option_value['sort_order']
				);
			}

			$product_option_data[$product_option['option_id']] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}




		return $product_option_data;
	}

	protected function getPath($parent_id, $current_path = '')
	{
		$category_info = $this->model_catalog_category->getCategory($parent_id);

		if($category_info)
		{
			if(!$current_path)
			{
				$new_path = $category_info['category_id'];
			}
			else
			{
				$new_path = $category_info['category_id'] . '_' . $current_path;
			}

			$path = $this->getPath($category_info['parent_id'], $new_path);

			if($path)
			{
				return $path;
			}
			else
			{
				return $new_path;
			}
		}
	}








	
	public function uploadf(): void {
		$json = [];
		$cData = [];
		$this->getML('LM');
		
		// Check user has permission
		if (!$this->user->hasPermission('modify', 'bytao/export')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (empty($this->request->files['file']['name']) || !is_file($this->request->files['file']['tmp_name'])) {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			// Sanitize the filename
			$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((oc_strlen($filename) < 3) || (oc_strlen($filename) > 128)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = [];
			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_file_type');
			}

			// Allowed file mime types
			$allowed = [];
			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_file_type');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		}

		if (!$json) {
			
			$_filename = explode('.',$filename);
			$file = by_SEO($_filename[0]).'.'.$_filename[1];
			
			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_IMPORT . $file);

			unset($this->session->data['import_file']);
			
			$cData['params'] = $titles = $this->model->getUpload($file);
			
			$json['filename'] = $this->session->data['import_file'] = $file;
			$json['content'] = $this->load->view('bytao/export_import_content', $cData);
			$json['mask'] = $filename;
			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function uploaded(): void {
		$json = [];
		$cData = [];
		$this->getML('LM');
		
		$filename = explode('.',basename(html_entity_decode($this->request->post['filename'], ENT_QUOTES, 'UTF-8')));
		
		$cData['params'] = json_decode(file_get_contents(DIR_IMPORT.$filename[0].'.json'),true);
		
		$json['content'] = $this->load->view('bytao/export_import_content', $cData);
		$json['success'] = $this->language->get('text_upload');
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function import():void {
		$json = [];
		$cData = [];
		$this->getML('LM');
		
		if(isset($this->request->post['check'])){
			$cData['checks']=$this->request->post['check'];
			$filename = explode('.',basename(html_entity_decode($this->request->post['filename'], ENT_QUOTES, 'UTF-8')));
			$cData['json'] = json_decode(file_get_contents(DIR_IMPORT.$filename[0].'.json'),true);
			$this->model->setImport($cData);
			
			
			$json['success'] = $this->language->get('text_upload');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function download(): void {
		$this->load->language('bytao/export');

		if (isset($this->request->get['filename'])) {
			$filename = basename($this->request->get['filename']);
		} else {
			$filename = '';
		}

		$file = DIR_IMPORT . $filename;

		if (is_file($file)) {
			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				readfile($file, 'rb');
				exit;
			} else {
				exit($this->language->get('error_headers_sent'));
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'])
			];

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}


}