<?php
namespace Opencart\Admin\Controller\Bytao;
class Newsletter extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/newsletter';
	private $C = 'newsletter';
	private $ID = 'newsletter_id';
	private $Tkn = 'user_token';
	private $mdata = [];
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f;//.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void {
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id']= 0;
		}
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function index() {
		$this->getML('ML');
		$this->model->{$this->getFunc('installModule')}();
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		$this->document->addStyle('view/stylesheet/bytao/newsletter.css?v8');
		
		$data["menus"] = [];
		$data["menus"]["create_newsletter"] = ["link"=>$this->url->link($this->cPth.'.create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_create_newsletter'),"icon" =>"fa-solid fa-file-circle-plus"];
		
		$data["menus"]["draft"] = ["link"=>$this->url->link($this->cPth.'.draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_manage_draft_newsletters'),"icon" =>"fa-brands fa-firstdraft"];
		$data["menus"]["subscribes"] = ["link"=>$this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_manage_subscribes'),"icon" =>"fa-solid fa-users-viewfinder"];
		
		$data["menus"]["templates"] = ["link"=>$this->url->link($this->cPth.'.templates', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_templates'),"icon" =>"fa-solid fa-gear"];
		$data["menus"]["modules"] = ["link"=>$this->url->link($this->cPth.'.modules', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_manage_modules'),"icon" =>"fa-solid fa-gears"];
		$data["menus"]["config"] = ["link"=>$this->url->link($this->cPth.'.config', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'),"title"=>$this->language->get('menu_global_config'),"icon" =>"fa-solid fa-gear"];
		
   		

		$data['modules'] = [];
		if (isset($this->request->post['newsletter_module'])) {
			$data['modules'] = $this->request->post['bytaonewsletter_module'];
		} 

		$data['general'] = [];
		
		$this->document->addStyle('view/stylesheet/bytao/newsletter.css?v3');
		$data['toolbar'] = $this->load->view($this->cPth.'/toolbar', $data);


		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C, $data));
	}
	
	public function create_newsletter(){
		$this->getML('ML');
		
		if( isset($this->request->get['id']) ){
			$this->request->post = $this->model->{$this->getFunc('detailDraft')}($this->request->get['id']);
		}
		if (isset($this->request->get['id']) && $this->request->server['REQUEST_METHOD'] != 'POST'  && $this->validateSend()) {
			
			$this->request->post = $this->model->{$this->getFunc('detailDraft')}($this->request->get['id']);

			if (!$this->request->post) {
				$this->response->redirect($this->url->link($this->cPth.'.create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
			}
		}
	
		$data["templates"] = $this->model->{$this->getFunc('getTemplates')}();

		$this->load->language('sale/contact');
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		$data['currencies'] = $this->model_bytao_common->getStoreCurrencies();
		
		$this->load->model('catalog/product');

		$data['defined_products'] = [];

		if (isset($this->request->post['defined_product']) && is_array($this->request->post['defined_product'])) {
			foreach ($this->request->post['defined_product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$data['defined_products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					);
				}
			}
			unset($product_info);
			unset($product_id);
		}
		$data['defined_products_more'] = [];

		if (isset($this->request->post['defined_product_more']) && is_array($this->request->post['defined_product_more'])) {
			foreach ($this->request->post['defined_product_more'] as $dpm) {
				if (!isset($dpm['products'])) {
					$dpm['products'] = [];
				}
				if (!isset($dpm['text'])) {
					$dpm['text'] = '';
				}
				$defined_products_more = array('text' => $dpm['text'], 'products' => array());
				foreach ($dpm['products'] as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						$defined_products_more['products'][] = array(
							'product_id' => $product_info['product_id'],
							'name'       => $product_info['name']
						);
					}
				}
				$data['defined_products_more'][] = $defined_products_more;
			}
			unset($defined_products_more);
			unset($product_info);
			unset($product_id);
		}

		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories([]);

		if (isset($this->request->get['id']) || isset($this->request->post['id'])) {
			$data['id'] = (isset($this->request->get['id']) ?$this->request->get['id'] : $this->request->post['id']);
		} else {
			$data['id'] = false;
		}

		if (isset($this->request->post['defined'])) {
			$data['defined'] = $this->request->post['defined'];
		} else {
			$data['defined'] = false;
		}

		if (isset($this->request->post['defined_categories'])) {
			$data['defined_categories'] = $this->request->post['defined_categories'];
		} else {
			$data['defined_categories'] = false;
		}

		if (isset($this->request->post['defined_category'])) {
			$data['defined_category'] = $this->request->post['defined_category'];
		} else {
			$data['defined_category'] = [];
		}

		if (isset($this->request->post['special'])) {
			$data['special'] = $this->request->post['special'];
		} else {
			$data['special'] = false;
		}

		if (isset($this->request->post['latest'])) {
			$data['latest'] = $this->request->post['latest'];
		} else {
			$data['latest'] = false;
		}

		if (isset($this->request->post['popular'])) {
			$data['popular'] = $this->request->post['popular'];
		} else {
			$data['popular'] = false;
		}

		if (isset($this->request->post['attachments'])) {
			$data['attachments'] = $this->request->post['attachments'];
		} else {
			$data['attachments'] = false;
		}


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = [];
		}

		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = [];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSend()) { 
			$action = isset($this->request->post['action'])?$this->request->post['action']:"";
			set_time_limit(0);

			$emails = [];

			$this->load->model('localisation/language');
			$language = $this->model_localisation_language->getLanguage((isset($this->request->post['language_id']) ? $this->request->post['language_id'] : $this->config->get('config_language_id')));
			
			if($action == 'save_draft'){
				$this->save_draft();
			}
			elseif ($action =='check_spam') {
				$emails['check@isnotspam.com'] = array(
					'firstname' => 'John',
					'lastname' => 'Doe'
				);
			} else {
				switch ($this->request->post['to']) {
					case 'subscriber':
						$customer_data = array(
							'filter_newsletter' => 1
						);

						$results = $this->model->{$this->getFunc('getCustomers')}($customer_data);

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'all':

						$results = $this->model->{$this->getFunc('getCustomers')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'newsletter':
							
							$customer_data = array(
								'filter_action' => 1
							);

							$results = $this->model->{$this->getFunc('getSubscribers')}($customer_data); 

							foreach ($results as $result) {
								// if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								// 	continue;
								// }
								if ($result['store_id'] == $this->request->post['store_id']) {
									$emails[$result['email']] = array(
										'firstname' => 'Mr/Ms',
										'lastname' => 'Guest'
									);
								}
							}
						break;
					case 'customer_all':
						$results = $this->model->{$this->getFunc('getCustomers')}();
						
						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if (empty($result['store_id']) || ($result['store_id'] == $this->request->post['store_id'] )) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'customer_group':
						$customer_data = array(
							'filter_customer_group_id' => $this->request->post['customer_group_id']
						);

						if (isset($this->request->post['customer_group_only_subscribers'])) {
							$customer_data['filter_newsletter'] = 1;
						}

						$results = $this->model->{$this->getFunc('getCustomers')}($customer_data);

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'customer':
						if (isset($this->request->post['customer']) && !empty($this->request->post['customer'])) {
							foreach ($this->request->post['customer'] as $customer_id) {
								$customer_info = $this->model->{$this->getFunc('getCustomer')}($customer_id);

								if ($customer_info) {
									/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
										continue;
									}*/
									$emails[$customer_info['email']] = array(
										'firstname' => $customer_info['firstname'],
										'lastname' => $customer_info['lastname']
									);
								}
							}
						}
						break;
					case 'affiliate_all':
						$results = $this->model->{$this->getFunc('getAffiliates')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname']
							);
						}
						break;
					case 'affiliate':
						if (isset($this->request->post['affiliate']) && !empty($this->request->post['affiliate'])) {
							foreach ($this->request->post['affiliate'] as $affiliate_id) {
								$affiliate_info = $this->model->{$this->getFunc('getAffiliate')}($affiliate_id);

								if ($affiliate_info) {
									/*if (isset($this->request->post['only_selected_language']) && (($language['code'] != $affiliate_info['language_code'] && $affiliate_info['language_code']) || (!$affiliate_info['language_code'] && $language['language_id'] != $this->config->get('config_language_id')))) {
										continue;
									}*/
									$emails[$affiliate_info['email']] = array(
										'firstname' => $affiliate_info['firstname'],
										'lastname' => $affiliate_info['lastname']
									);
								}
							}
						}
						break;
					case 'product':
						if (isset($this->request->post['product']) && $this->request->post['product']) {
							$results = $this->model->{$this->getFunc('getEmailsByProductsOrdered')}($this->request->post['product']);

							foreach ($results as $result) {
								/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
									continue;
								}*/
								if ($result['store_id'] == $this->request->post['store_id']) {
									$emails[$result['email']] = array(
										'firstname' => $result['firstname'],
										'lastname' => $result['lastname']
									);
								}
							}
						}
						break;
					case 'rewards_all':
						$results = $this->model->{$this->getFunc('getRecipientsWithRewardPoints')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname'],
								'reward' => $result['points'],
							);
						}
						break;
					case 'rewards':
						$results = $this->model->{$this->getFunc('getSubscribedRecipientsWithRewardPoints')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname'],
								'reward' => $result['points'],
							);
						}
						break;
				}
			}

			if ($emails) {
				$default = array(
					'attachments_count'	=> 0,
					'defined'			=> 0,
					'defined_product'	=> 0

				);
				$this->request->post = array_merge($default, $this->request->post);
				$data = array(
					'emails' => $emails,
					'to' => $this->request->post['to'],
					'subject' => $this->request->post['subject'],
					'message' => $this->request->post['message'],
					'store_id' => $this->request->post['store_id'],
					'template_id' => $this->request->post['template_id'],
					'language_id' => $language['language_id'],
					'attachments_count' => $this->request->post['attachments_count'],
					'attachments_upload' => $this->request->files,
					'attachments' => false,
					'language_code' => $language['code']
				);
				
				$data_products = [];
				//get list products
				$this->load->model('bytao/product');
				$setting = array(
					'currency' => $this->request->post['currency'],
					'width' => 80,
					'height' => 80,
					'limit' => 5,
				);
			
				
				if($this->request->post['defined'] && isset($this->request->post['defined_product']) ) {
					$defined_product = $this->request->post['defined_product'];
					$selectedProducts = [];
					$products = $this->model->{$this->getFunc('getProducts')}();
					foreach($products as $product) {
						if(in_array($product['product_id'],$defined_product)) {
							$selectedProducts[] = $product;
						}
					}
					$test = $this->getItemProducts($selectedProducts, $setting);
					$data_products['selected'] = $this->getItemProducts($selectedProducts, $setting);
				}
			
				if($this->request->post['special']) {
					$specialProducts = $this->model->{$this->getFunc('getProductSpecials')}($setting['limit']);
					$data_products['special'] = $this->getItemProducts($specialProducts, $setting);
				}
				
				if($this->request->post['latest']) {
					$latestProducts = $this->model->{$this->getFunc('getLatestProducts')}($setting['limit']);
					$data_products['latest'] = $this->getItemProducts($latestProducts, $setting);
				}
				
				if($this->request->post['popular']) {
					$popularProducts = $this->model->{$this->getFunc('getPopularProducts')}($setting['limit']);
					$data_products['popular'] = $this->getItemProducts($popularProducts, $setting);
				}
				
				if( isset($this->request->post['defined_categories']) && isset($this->request->post['defined_category']) ) {
					


					$defined_category = $this->request->post['defined_category'];
					$categoriesProducts = [];
					$products = $this->model->{$this->getFunc('getProducts')}();
					foreach($products as $product) {
						if(in_array($product['product_id'],$defined_category)) {
							$categoriesProducts[] = $product;
						}
					}
					$data_products['category'] = $this->getItemProducts($categoriesProducts, $setting);
				}
				$data['lstproduct'] = $data_products;
				$this->model->{$this->getFunc('send')}($data);
			} else {
				$this->error['warning'] = $this->language->get("text_error_empty_email");
			}
		}
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = '';
		}

		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link($this->cPth.'.create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
    	$data['save'] = $this->url->link($this->cPth.'.save_draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');

    	if (isset($this->request->post['template_id'])) {
			$data['template_id'] = $this->request->post['template_id'];
		} else {
			$data['template_id'] = '';
		}

		
		$data['templates'] = $this->model->{$this->getFunc('getTemplates')}();

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} else {
			$data['store_id'] = '';
		}

		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} else {
			$data['language_id'] = '';
		}

		if (isset($this->request->post['currency'])) {
			$data['currency'] = $this->request->post['currency'];
		} else {
			$data['currency'] = '';
		}


		if (isset($this->request->post['to'])) {
			$data['to'] = $this->request->post['to'];
		} else {
			$data['to'] = '';
		}

		if (isset($this->request->post['customer_group_id'])) {
			$data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$data['customer_group_id'] = '';
		}

		if (isset($this->request->post['customer_group_only_subscribers'])) {
			$data['customer_group_only_subscribers'] = $this->request->post['customer_group_only_subscribers'];
		} else {
			$data['customer_group_only_subscribers'] = '';
		}

		if (isset($this->request->post['only_selected_language'])) {
			$data['only_selected_language'] = $this->request->post['only_selected_language'];
		} else {
			$data['only_selected_language'] = 1;
		}

		$this->load->model('customer/customer_group');
		
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups([]);

		$data['customers'] = [];

		if (isset($this->request->post['customer']) && is_array($this->request->post['customer'])) {
			foreach ($this->request->post['customer'] as $customer_id) {
				$customer_info = $this->model_bytaonewsletter_newsletter->getCustomer($customer_id);

				if ($customer_info) {
					$data['customers'][] = array(
						'customer_id' => $customer_info['customer_id'],
						'name'        => $customer_info['firstname'] . ' ' . $customer_info['lastname']
					);
				}
			}
		}

    $data['affiliates'] = [];

      if (isset($this->request->post['affiliate']) && is_array($this->request->post['affiliate'])) {
          foreach ($this->request->post['affiliate'] as $affiliate_id) {
              $affiliate_info = $this->model_bytaonewsletter_newsletter->getAffiliate($affiliate_id);

              if ($affiliate_info) {
                  $data['affiliates'][] = array(
                      'affiliate_id' => $affiliate_info['affiliate_id'],
                      'name'         => $affiliate_info['firstname'] . ' ' . $affiliate_info['lastname']
                  );
              }
          }
      }
		$this->load->model('catalog/product');

		$data['products'] = [];

		if (isset($this->request->post['product']) && is_array($this->request->post['product'])) {
			foreach ($this->request->post['product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$data['products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					);
				}
			}
		}

		if (isset($this->request->post['subject'])) {
			$data['subject'] = $this->request->post['subject'];
		} else {
			$data['subject'] = '';
		}

		if (isset($this->request->post['message'])) {
			$data['message'] = $this->request->post['message'];
		} else {
			$data['message'] = '';
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$this->response->setOutput($this->load->view($this->cPth.'/form_newsletter',$data));
		
	}
	
	public function modules(){
		$this->getML('ML');
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		$data['save'] = $this->url->link($this->cPth.'.save_module', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link($this->cPth.'.modules', 'user_token=' . $this->session->data['user_token']);
		
		$extensions = $this->model->{$this->getFunc('getNewsletterModules')}();
		$data['extensions']=[];
		foreach($extensions as $extension){
			$data['extensions'][]= [
				'newsletter_module_id' => $extension['newsletter_module_id'],
				'name' => $extension['name'],
				'href' =>  $this->url->link($this->cPth.'.modules', 'user_token=' . $this->session->data['user_token'].'&newsletter_module_id='.$extension['newsletter_module_id'])
			];
		}
		
		$d = [
			'display_mode' => 0,
			'name' => '',
			'status'	 => 1, 
			'description' => '',
			'social'	=> ''
		];
		$module_info = [];
		if (isset($this->request->get['newsletter_module_id']) && $this->request->get['newsletter_module_id'] != '0' ) {
			$module_info = $this->model->{$this->getFunc('getNewsletterModule')}($this->request->get['newsletter_module_id']);
			$data['newsletter_module_id'] = $this->request->get['newsletter_module_id'];
		}
		
		
		 $module_info = array_merge( $d, $module_info ); 
		// status
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 1;
		}

		// name
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		// description
		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($module_info)&& isset($this->request->get['newsletter_module_id'])) {
			$data['description'] = $this->model->{$this->getFunc('getNewsletterModuleDescription')}($this->request->get['newsletter_module_id']);
		} else {
			$data['description'] = [];
		}
		
		$data['display_mode'] = $module_info['display_mode'];

		$modes = array(
			'default' => $this->language->get( 'text_default'), 
			'flybot' => $this->language->get( 'text_flybot'),
			'modalbox' => $this->language->get( 'text_modalshow')
		);
		
		$data['modes'] = $modes;
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->response->setOutput($this->load->view($this->cPth.'/frontend_modules',$data));
	}
	
	public function save_module(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		
		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$json['newsletter_module_id'] = $this->model->{$this->getFunc('updateNewsletterModule')}($this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}








	
	

	
	
	
	
	
	
	


	public function subsribes(){
		$this->getML('ML');
		
		$this->load->model('customer/customer_group');
		$this->load->model('setting/store');
		$data = [];

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			$post = $this->request->post;
			
			if(isset($post) && $post['action'] == "delete" && !empty($post['selected'])){
				$selected = $post['selected'];
				// Delete Subsribes
				foreach ($selected as $key => $value) {
					$this->model->{$this->getFunc('delete')}($value);
				}

			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn]));
		}

		$data['page'] = isset($this->request->get['page'])?$this->request->get['page']:1;
		$data['limit'] = $this->config->get('config_admin_limit');
		$data['filter'] = [];
		$data['filter']['name'] = isset($this->request->get['filter_name'])?$this->request->get['filter_name']:"";
		$data['filter']['email'] = isset($this->request->get['filter_email'])?$this->request->get['filter_email']:"";
		$data['filter']['action'] = isset($this->request->get['filter_action'])?$this->request->get['filter_action']:"";
		$data['filter']['customer_group_id'] = isset($this->request->get['filter_customer_group_id'])?$this->request->get['filter_customer_group_id']:"";
		$data['filter']['store_id'] = isset($this->request->get['filter_store_id'])?$this->request->get['filter_store_id']:"";
		$data['sort'] = isset($this->request->get['sort'])?$this->request->get['sort']:"name";
		$data['order'] = isset($this->request->get['order'])?$this->request->get['order']:"DESC";


		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_action'])) {
			$url .= '&filter_action=' . $this->request->get['filter_action'];
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link($this->cPth.'|subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');

		$data["menu_active"] = "subscribes";

		$subscribe_total = $this->model->{$this->getFunc('getTotalSubscribers')}($data);

		$results = $this->model->{$this->getFunc('getSubscribers')}($data);
		$stores = $this->model_setting_store->getStores();

		$tmp = [];
		$tmp[0] = $this->language->get("text_default_store");
		if(!empty($stores)){
			foreach($stores as $store ){
				$tmp[$store["store_id"]] = $store["name"];
			}
		}
		$stores = $tmp;
		$data["stores"] = $stores;
		$customer_groups = $this->model->{$this->getFunc('getCustomerGroups')}();
		$tmp = [];
		if(!empty($customer_groups)){
			foreach($customer_groups as $group ){
				$tmp[$group["customer_group_id"]] = $group["name"];
			}
		}
		$customer_groups = $tmp;
		$data["customer_groups"] = $customer_groups;
		$data['subscribes'] = [];
		foreach ($results as $result) {
			$action = [];
			$action_name = "";
			if($result['action'] == 1){
				$action_name =  $this->language->get('text_yes');
				$action[] = array(
				'text' => $this->language->get('text_unsubscribe'),
				'href' => $this->url->link($this->cPth.'|unsubsribe', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&subscribe_id=' . $result['subscribe_id'] . $url, 'SSL')
				);
			}else{
				$action_name =  $this->language->get('text_no');
				$action[] = array(
				'text' => $this->language->get('text_subscribe'),
				'href' => $this->url->link($this->cPth.'|subsribe', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&subscribe_id=' . $result['subscribe_id'] . $url, 'SSL')
				);
			}
			$customer_group_name = isset($customer_groups[$result["customer_group_id"]])?$customer_groups[$result["customer_group_id"]]:"";
			$store_name = isset($stores[$result["store_id"]])?$stores[$result["store_id"]]:$this->language->get("text_default_store");
      		$data['subscribes'][] = array(
				'subscribe_id' => $result['subscribe_id'],
				'name'       => $result['name'],
				'email'      => $result['email'],
				'subscribe'      => $action_name,
				'store'    => $store_name,
				'customer_group'   => $customer_group_name,
				'action'     => $action
			);
    	}

    	$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_action'])) {
			$url .= '&filter_action=' . $this->request->get['filter_action'];
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}

		if ($data['order'] == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		$data['sort_name'] = $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=name' . $url, 'SSL');
		$data['sort_email'] = $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=email' . $url, 'SSL');
		$data['sort_subscribe'] = $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=s.action' . $url, 'SSL');
		$data['sort_customer_group_id'] = $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=customer_group_id' . $url, 'SSL');
		$data['sort_store_id'] = $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=s.store_id' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_action'])) {
			$url .= '&filter_action=' . $this->request->get['filter_action'];
		}

		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}

		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $subscribe_total,
			'page'  => $data['page'],
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['filter_name'] = $data['filter']['name'];
		$data['filter_email'] = $data['filter']['email'];
		$data['filter_action'] = $data['filter']['action'];
		$data['filter_store_id'] = $data['filter']['store_id'];
		$data['filter_customer_group_id'] = $data['filter']['customer_group_id'];

		$data['sort'] = $data['sort'];
		$data['order'] = $data['order'];
		$this->response->setOutput($this->load->view($this->cPth.'/subscribes',$data));
	}
	
	public function unsubsribe(){
		$this->getML('ML');
		
		if (isset($this->request->get['subscribe_id'])) {
			$subscribe_id = $this->request->get['subscribe_id'];
		} else {
			$subscribe_id = 0;
		}
		if(!empty($subscribe_id)){
			$this->model->{$this->getFunc('updateAction')}($subscribe_id, 0);
		}
		$this->response->redirect($this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
	}
	
	public function subsribe(){
		$this->getML('ML');
		if (isset($this->request->get['subscribe_id'])) {
			$subscribe_id = $this->request->get['subscribe_id'];
		} else {
			$subscribe_id = 0;
		}
		if(!empty($subscribe_id)){
			$this->model->{$this->getFunc('updateAction')}($subscribe_id, 1);
		}
		$this->response->redirect($this->url->link($this->cPth.'.subsribes', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
	}
	
	public function draft(){
		$this->getML('ML');
		
		
		$data["menu_active"] = "draft";
		$data['cancel'] = $this->url->link($this->cPth.'|templates', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
		$template_id = isset($this->request->get['id'])?$this->request->get['id']:0;
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			  $action = isset($this->request->post["action"])?$this->request->post["action"]:"";
        if($action == "delete"){
            foreach ($this->request->post['selected'] as $draft_id) {
                $this->model->{$this->getFunc('deleteDraft')}($draft_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));

		}
      if (isset($this->request->get['filter_date'])) {
          $filter_date = $this->request->get['filter_date'];
      } else {
          $filter_date = null;
      }

      if (isset($this->request->get['filter_subject'])) {
          $filter_subject = $this->request->get['filter_subject'];
      } else {
          $filter_subject = null;
      }

      if (isset($this->request->get['filter_to'])) {
          $filter_to = $this->request->get['filter_to'];
      } else {
          $filter_to = null;
      }

      if (isset($this->request->get['filter_store'])) {
          $filter_store = $this->request->get['filter_store'];
      } else {
          $filter_store = null;
      }

      if (isset($this->request->get['sort'])) {
          $sort = $this->request->get['sort'];
      } else {
          $sort = 'draft_id';
      }

      if (isset($this->request->get['order'])) {
          $order = $this->request->get['order'];
      } else {
          $order = 'DESC';
      }

      if (isset($this->request->get['page'])) {
          $page = $this->request->get['page'];
      } else {
          $page = 1;
      }

      $url = '';

      if (isset($this->request->get['filter_date'])) {
          $url .= '&filter_date=' . $this->request->get['filter_date'];
      }

      if (isset($this->request->get['filter_subject'])) {
          $url .= '&filter_subject=' . $this->request->get['filter_subject'];
      }

      if (isset($this->request->get['filter_to'])) {
          $url .= '&filter_to=' . $this->request->get['filter_to'];
      }

      if (isset($this->request->get['filter_store'])) {
          $url .= '&filter_store=' . $this->request->get['filter_store'];
      }

      if (isset($this->request->get['sort'])) {
          $url .= '&sort=' . $this->request->get['sort'];
      }

      if (isset($this->request->get['order'])) {
          $url .= '&order=' . $this->request->get['order'];
      }

      if (isset($this->request->get['page'])) {
          $url .= '&page=' . $this->request->get['page'];
      }

      $data = array(
          'filter_date'		=> $filter_date,
          'filter_subject'	=> $filter_subject,
          'filter_to'			=> $filter_to,
          'sort'				=> $sort,
          'order'				=> $order,
          'start'				=> (int)($page - 1) * (int)$this->config->get('config_admin_limit'),
          'limit'				=> $this->config->get('config_admin_limit')
      );

      $total = $this->model->{$this->getFunc('getTotalDraft')}($data);

      $data['draft'] = [];

      $results = $this->model->{$this->getFunc('getListDraft')}($data);

      foreach ($results as $result) {
          $data['draft'][] = array_merge($result, array(
              'selected' => isset($this->request->post['selected']) && is_array($this->request->post['selected']) && in_array($result['draft_id'], $this->request->post['selected'])
          ));
      }
      unset($result);

      $data[$this->Tkn] = $this->session->data[$this->Tkn];

      if (isset($this->error['warning'])) {
          $data['error_warning'] = $this->error['warning'];
      } else {
          $data['error_warning'] = '';
      }

      if (isset($this->session->data['success'])) {
          $data['success'] = $this->session->data['success'];

          unset($this->session->data['success']);
      } else {
          $data['success'] = '';
      }

      $url = '';

      if (isset($this->request->get['filter_date'])) {
          $url .= '&filter_date=' . $this->request->get['filter_date'];
      }

      if (isset($this->request->get['filter_subject'])) {
          $url .= '&filter_subject=' . $this->request->get['filter_subject'];
      }

      if (isset($this->request->get['filter_to'])) {
          $url .= '&filter_to=' . $this->request->get['filter_to'];
      }

      if ($order == 'ASC') {
          $url .= '&order=' .  'DESC';
      } else {
          $url .= '&order=' .  'ASC';
      }

      if (isset($this->request->get['page'])) {
          $url .= '&page=' . $this->request->get['page'];
      }

      $data['sort_date'] = $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=datetime' . $url, 'SSL');
      $data['sort_subject'] = $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=subject' . $url, 'SSL');
      $data['sort_to'] = $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=to' . $url, 'SSL');
      $data['sort_store'] = $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=store_id' . $url, 'SSL');

      $url = '';

      if (isset($this->request->get['filter_date'])) {
          $url .= '&filter_date=' . $this->request->get['filter_date'];
      }

      if (isset($this->request->get['filter_subject'])) {
          $url .= '&filter_subject=' . $this->request->get['filter_subject'];
      }

      if (isset($this->request->get['filter_to'])) {
          $url .= '&filter_to=' . $this->request->get['filter_to'];
      }

      if (isset($this->request->get['sort'])) {
          $url .= '&sort=' . $this->request->get['sort'];
      }

      if (isset($this->request->get['order'])) {
          $url .= '&order=' . $this->request->get['order'];
      }

      $data['detail'] = $this->url->link($this->cPth.'|create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&id=', 'SSL');
      $data['action'] = $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');

	$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
      
      $data['filter_date'] = $filter_date;
      $data['filter_subject'] = $filter_subject;
      $data['filter_to'] = $filter_to;
      
      $data['sort'] = $sort;
      $data['order'] = $order;

      $template = $this->cPth.'/draft_newsletter';
    

      $this->_render($template);
	}
	
	public function preview_newsletter(){

	}
	
	public function get_template(){
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$post = http_build_query($this->request->post, '', '&');

			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$store_url = (defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG);
			} else {
				$store_url = HTTP_CATALOG;
			}

			if (isset($this->request->post['store_id'])) {
				$this->load->model('setting/store');
				$store = $this->model_setting_store->getStore($this->request->post['store_id']);
				if ($store) {
					$url = rtrim($store['url'], '/') . '/index.php?route=bytao/newsletter/get_template/json';
				} else {
					$url = $store_url . 'index.php?route=bytao/newsletter/get_template/json';
				}
			} else {
				$url = $store_url . 'index.php?route=bytao/newsletter/get_template/json';
			}

			$result = $this->do_request(array(
				'url' => $url,
				'header' => array('Content-type: application/x-www-form-urlencoded', "Content-Length: ". strlen($post), "X-Requested-With: XMLHttpRequest"),
				'method' => 'POST',
				'content' => $post
			));

			$response = $result['response'];

			$this->response->addHeader('Content-type: application/json');
			$this->response->setOutput($response);
		} else {
			$this->response->redirect($this->url->link($this->cPth.'|create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
		}
	}
	
	private function do_request($options) {
		$options = $options + array(
			'method' => 'GET',
			'content' => false,
			'header' => false,
			'async' => false,
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $options['url']);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_USERAGENT, 'bytao Newsletter');

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		if ($options['header']) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options['header']);
		}

		if ($options['async']) {
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		} else {
			curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		}

		switch ($options['method']) {
			case 'HEAD':
				curl_setopt($ch, CURLOPT_NOBODY, 1);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $options['content']);
				break;
			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $options['content']);
				break;
			default:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);
				if ($options['content'])
					curl_setopt($ch, CURLOPT_POSTFIELDS, $options['content']);
				break;
		}

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return array(
			'header' => substr($result, 0, $info['header_size']),
			'response' => substr($result, $info['header_size']),
			'status' => $status,
			'info' => $info
		);
	}
	
	public function save_draft(){
		$this->getML('M');
      if ($this->request->server['REQUEST_METHOD'] == 'POST') {
          $this->model->{$this->getFunc('saveDraft')}($this->request->post);
          $this->session->data['success'] = $this->language->get('text_success_save');
      }

      $this->response->redirect($this->url->link($this->cPth.'|draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
	}

	protected function validateSend() {
		$post = array(
			'subject' => '',
			'message' => ''
		);
		$this->request->post = array_merge( $post, $this->request->post );
		
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['subject']) < 1)) {
			$this->error['subject'] = $this->language->get('error_newsletter_subject');
		}

		if ((utf8_strlen($this->request->post['message']) < 1)) {
			$this->error['message'] = $this->language->get('error_newsletter_message');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}



		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	
	

	public function _render($template){
		
		$this->document->addStyle('view/stylesheet/bytao/newsletter.css?v8');
		$data = [];
		$data['toolbar'] = $this->load->view($this->cPth.'/toolbar', $data);
		$data['action_bar'] = $this->load->view($this->cPth.'/action_bar', $data);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($template, $data));
	}
	
	public function templates(){
		$this->getML('ML');
		
		$data["menu_active"] = "templates";
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$action = isset($this->request->post['action'])?$this->request->post['action']:"";
			switch ($action) {
				case 'copy_default':

					break;
				case 'copy':
					$templates = isset($this->request->post["templates"])?$this->request->post["templates"]:[];
					$check = false;
					if(!empty($templates)){
						$check = $this->model->{$this->getFunc('copyTemplate')}($templates);
					}
					if($check){
						$data["success"] = $this->language->get("text_success_copy_template");
					}else{
						$data["error_warning"] = $this->language->get("text_error_cannot_copy_template");
					}
					break;
				case 'insert':
					return $this->template();
					break;
				case 'delete':
					$templates = isset($this->request->post["templates"])?$this->request->post["templates"]:[];
					$check = false;
					if(!empty($templates)){
						$check = $this->model->{$this->getFunc('deleteTemplate')}($templates);
					}
					if($check){
						$data["success"] = $this->language->get("text_delete_template");
					}else{
						$data["error_warning"] = $this->language->get("text_error_delete_template");
					}
					break;
				default:

					break;
			}
		}
		$templates = $this->model->{$this->getFunc('getTemplates')}();
		$data["templates"] = $templates;
		$data["pagination"] = "";
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['insert_link'] = $this->url->link($this->cPth.'|template', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
		$data['action'] = $this->url->link($this->cPth.'|templates', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
		
		$this->_render($this->cPth.'/templates');
	}
	
	public function upload() {
		$this->load->language('sale/order');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
      		$json['error'] = $this->language->get('error_permission');
    	}

		if (!isset($json['error'])) {
			if (!empty($this->request->files['file']['name'])) {
				$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = [];

				$filetypes = explode("\n", $this->config->get('config_file_extension_allowed'));

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = [];

				$filetypes = explode("\n", $this->config->get('config_file_mime_allowed'));

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array($this->request->files['file']['type'], $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}

				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!isset($json['error'])) {
			if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
				$ext = md5(mt_rand());

				$json['filename'] = $filename . '.' . $ext;
				$json['mask'] = $filename;

				move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD .'bytaonewsletter/'. $filename . '.' . $ext);
			}

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function template_from_file(){
		$template = isset($this->request->post["template"])?$this->request->post["template"]:[];
		$template_file = isset($template["template_file"])?$template["template_file"]:"";
		$json = [];
		if(!empty($template_file)){
			$file_path = DIR_DOWNLOAD."bytao/newsletter/".$template_file;
			$json["template"] = file_get_contents($file_path);
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function template(){
		$this->getML('ML');
		
		$template_id = isset($this->request->get['id'])?$this->request->get['id']:0;
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$action = isset($this->request->post["action"])?$this->request->post["action"]:"";
			if($action == "get_template"){

			}else{
				$check = $this->model->{$this->getFunc('insertTemplate')}($this->request->post);
				if($check)
				 	$this->session->data['success'] = $this->language->get('text_bytaonewsletter_success');
				else
					$this->session->data['error_warning'] = $this->language->get('text_bytaonewsletter_error_warning');

				$this->response->redirect($this->url->link($this->cPth.'|templates', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
			}

		}
		$template = $this->model->{$this->getFunc('getTemplate')}($template_id);
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$data['languages'] = $languages;
		$data['template_id'] = $template_id;
		$data["template"] = $template;
		$data["template_description"] = isset($template["template_description"])?$template["template_description"]:[];
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['button_upload'] = $this->language->get('button_upload');
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		if (isset($this->error['filename'])) {
			$data['error_filename'] = $this->error['filename'];
		} else {
			$data['error_filename'] = '';
		}

		$data['action'] = $this->url->link($this->cPth.'.template', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
		$this->_render($this->cPth.'/'.$this->C.'/form_template');
	}
	
	
	
	public function create_newsletter_ex(){
		$this->getML('ML');
		
		if( isset($this->request->get['id']) ){
			$this->request->post = $this->model->{$this->getFunc('detailDraft')}($this->request->get['id']);
		}
		if (isset($this->request->get['id']) && $this->request->server['REQUEST_METHOD'] != 'POST'  && $this->validateSend()) {
			
			$this->request->post = $this->model->{$this->getFunc('detailDraft')}($this->request->get['id']);

			if (!$this->request->post) {
				$this->response->redirect($this->url->link($this->cPth.'|create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
			}
		}
	
		//
		

		$data["menu_active"] = "create_newsletter";
		$data['cancel'] = $this->url->link($this->cPth.'|templates', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');
		$data["templates"] = $this->model->{$this->getFunc('getTemplates')}();

		$this->load->language('sale/contact');
		$this->load->model('bytao/common');
		$data['languages'] =$languages = $this->model_bytao_common->getStoreLanguages();
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$this->load->model('catalog/product');

		$data['defined_products'] = [];

		if (isset($this->request->post['defined_product']) && is_array($this->request->post['defined_product'])) {
			foreach ($this->request->post['defined_product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$data['defined_products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					);
				}
			}
			unset($product_info);
			unset($product_id);
		}
		$data['defined_products_more'] = [];

		if (isset($this->request->post['defined_product_more']) && is_array($this->request->post['defined_product_more'])) {
			foreach ($this->request->post['defined_product_more'] as $dpm) {
				if (!isset($dpm['products'])) {
					$dpm['products'] = [];
				}
				if (!isset($dpm['text'])) {
					$dpm['text'] = '';
				}
				$defined_products_more = array('text' => $dpm['text'], 'products' => array());
				foreach ($dpm['products'] as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						$defined_products_more['products'][] = array(
							'product_id' => $product_info['product_id'],
							'name'       => $product_info['name']
						);
					}
				}
				$data['defined_products_more'][] = $defined_products_more;
			}
			unset($defined_products_more);
			unset($product_info);
			unset($product_id);
		}

		$this->load->model('catalog/category');

		$data['categories'] = $this->model_catalog_category->getCategories([]);

		if (isset($this->request->get['id']) || isset($this->request->post['id'])) {
			$data['id'] = (isset($this->request->get['id']) ?$this->request->get['id'] : $this->request->post['id']);
		} else {
			$data['id'] = false;
		}

		if (isset($this->request->post['defined'])) {
			$data['defined'] = $this->request->post['defined'];
		} else {
			$data['defined'] = false;
		}

		if (isset($this->request->post['defined_categories'])) {
			$data['defined_categories'] = $this->request->post['defined_categories'];
		} else {
			$data['defined_categories'] = false;
		}

		if (isset($this->request->post['defined_category'])) {
			$data['defined_category'] = $this->request->post['defined_category'];
		} else {
			$data['defined_category'] = [];
		}

		if (isset($this->request->post['special'])) {
			$data['special'] = $this->request->post['special'];
		} else {
			$data['special'] = false;
		}

		if (isset($this->request->post['latest'])) {
			$data['latest'] = $this->request->post['latest'];
		} else {
			$data['latest'] = false;
		}

		if (isset($this->request->post['popular'])) {
			$data['popular'] = $this->request->post['popular'];
		} else {
			$data['popular'] = false;
		}

		if (isset($this->request->post['attachments'])) {
			$data['attachments'] = $this->request->post['attachments'];
		} else {
			$data['attachments'] = false;
		}


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = [];
		}

		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = [];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSend()) { 
			$action = isset($this->request->post['action'])?$this->request->post['action']:"";
			set_time_limit(0);

			$emails = [];

			$this->load->model('localisation/language');
			$language = $this->model_localisation_language->getLanguage((isset($this->request->post['language_id']) ? $this->request->post['language_id'] : $this->config->get('config_language_id')));
			
			if($action == 'save_draft'){
				$this->save_draft();
			}
			elseif ($action =='check_spam') {
				$emails['check@isnotspam.com'] = array(
					'firstname' => 'John',
					'lastname' => 'Doe'
				);
			} else {
				switch ($this->request->post['to']) {
					case 'subscriber':
						$customer_data = array(
							'filter_newsletter' => 1
						);

						$results = $this->model->{$this->getFunc('getCustomers')}($customer_data);

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'all':

						$results = $this->model->{$this->getFunc('getCustomers')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'newsletter':
							
							$customer_data = array(
								'filter_action' => 1
							);

							$results = $this->model->{$this->getFunc('getSubscribers')}($customer_data); 

							foreach ($results as $result) {
								// if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								// 	continue;
								// }
								if ($result['store_id'] == $this->request->post['store_id']) {
									$emails[$result['email']] = array(
										'firstname' => 'Mr/Ms',
										'lastname' => 'Guest'
									);
								}
							}
						break;
					case 'customer_all':
						$results = $this->model->{$this->getFunc('getCustomers')}();
						
						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if (empty($result['store_id']) || ($result['store_id'] == $this->request->post['store_id'] )) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'customer_group':
						$customer_data = array(
							'filter_customer_group_id' => $this->request->post['customer_group_id']
						);

						if (isset($this->request->post['customer_group_only_subscribers'])) {
							$customer_data['filter_newsletter'] = 1;
						}

						$results = $this->model->{$this->getFunc('getCustomers')}($customer_data);

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							if ($result['store_id'] == $this->request->post['store_id']) {
								$emails[$result['email']] = array(
									'firstname' => $result['firstname'],
									'lastname' => $result['lastname']
								);
							}
						}
						break;
					case 'customer':
						if (isset($this->request->post['customer']) && !empty($this->request->post['customer'])) {
							foreach ($this->request->post['customer'] as $customer_id) {
								$customer_info = $this->model->{$this->getFunc('getCustomer')}($customer_id);

								if ($customer_info) {
									/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
										continue;
									}*/
									$emails[$customer_info['email']] = array(
										'firstname' => $customer_info['firstname'],
										'lastname' => $customer_info['lastname']
									);
								}
							}
						}
						break;
					case 'affiliate_all':
						$results = $this->model->{$this->getFunc('getAffiliates')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname']
							);
						}
						break;
					case 'affiliate':
						if (isset($this->request->post['affiliate']) && !empty($this->request->post['affiliate'])) {
							foreach ($this->request->post['affiliate'] as $affiliate_id) {
								$affiliate_info = $this->model->{$this->getFunc('getAffiliate')}($affiliate_id);

								if ($affiliate_info) {
									/*if (isset($this->request->post['only_selected_language']) && (($language['code'] != $affiliate_info['language_code'] && $affiliate_info['language_code']) || (!$affiliate_info['language_code'] && $language['language_id'] != $this->config->get('config_language_id')))) {
										continue;
									}*/
									$emails[$affiliate_info['email']] = array(
										'firstname' => $affiliate_info['firstname'],
										'lastname' => $affiliate_info['lastname']
									);
								}
							}
						}
						break;
					case 'product':
						if (isset($this->request->post['product']) && $this->request->post['product']) {
							$results = $this->model->{$this->getFunc('getEmailsByProductsOrdered')}($this->request->post['product']);

							foreach ($results as $result) {
								/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
									continue;
								}*/
								if ($result['store_id'] == $this->request->post['store_id']) {
									$emails[$result['email']] = array(
										'firstname' => $result['firstname'],
										'lastname' => $result['lastname']
									);
								}
							}
						}
						break;
					case 'rewards_all':
						$results = $this->model->{$this->getFunc('getRecipientsWithRewardPoints')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname'],
								'reward' => $result['points'],
							);
						}
						break;
					case 'rewards':
						$results = $this->model->{$this->getFunc('getSubscribedRecipientsWithRewardPoints')}();

						foreach ($results as $result) {
							/*if (isset($this->request->post['only_selected_language']) && $this->request->post['only_selected_language'] ) {
								continue;
							}*/
							$emails[$result['email']] = array(
								'firstname' => $result['firstname'],
								'lastname' => $result['lastname'],
								'reward' => $result['points'],
							);
						}
						break;
				}
			}

			if ($emails) {
				$default = array(
					'attachments_count'	=> 0,
					'defined'			=> 0,
					'defined_product'	=> 0

				);
				$this->request->post = array_merge($default, $this->request->post);
				$data = array(
					'emails' => $emails,
					'to' => $this->request->post['to'],
					'subject' => $this->request->post['subject'],
					'message' => $this->request->post['message'],
					'store_id' => $this->request->post['store_id'],
					'template_id' => $this->request->post['template_id'],
					'language_id' => $language['language_id'],
					'attachments_count' => $this->request->post['attachments_count'],
					'attachments_upload' => $this->request->files,
					'attachments' => false,
					'language_code' => $language['code']
				);
				
				$data_products = [];
				//get list products
				$this->load->model('bytao/product');
				$setting = array(
					'currency' => $this->request->post['currency'],
					'width' => 80,
					'height' => 80,
					'limit' => 5,
				);
			
				
				if($this->request->post['defined'] && isset($this->request->post['defined_product']) ) {
					$defined_product = $this->request->post['defined_product'];
					$selectedProducts = [];
					$products = $this->model->{$this->getFunc('getProducts')}();
					foreach($products as $product) {
						if(in_array($product['product_id'],$defined_product)) {
							$selectedProducts[] = $product;
						}
					}
					$test = $this->getItemProducts($selectedProducts, $setting);
					$data_products['selected'] = $this->getItemProducts($selectedProducts, $setting);
				}
			
				if($this->request->post['special']) {
					$specialProducts = $this->model->{$this->getFunc('getProductSpecials')}($setting['limit']);
					$data_products['special'] = $this->getItemProducts($specialProducts, $setting);
				}
				
				if($this->request->post['latest']) {
					$latestProducts = $this->model->{$this->getFunc('getLatestProducts')}($setting['limit']);
					$data_products['latest'] = $this->getItemProducts($latestProducts, $setting);
				}
				
				if($this->request->post['popular']) {
					$popularProducts = $this->model->{$this->getFunc('getPopularProducts')}($setting['limit']);
					$data_products['popular'] = $this->getItemProducts($popularProducts, $setting);
				}
				
				if( isset($this->request->post['defined_categories']) && isset($this->request->post['defined_category']) ) {
					


					$defined_category = $this->request->post['defined_category'];
					$categoriesProducts = [];
					$products = $this->model->{$this->getFunc('getProducts')}();
					foreach($products as $product) {
						if(in_array($product['product_id'],$defined_category)) {
							$categoriesProducts[] = $product;
						}
					}
					$data_products['category'] = $this->getItemProducts($categoriesProducts, $setting);
				}
				$data['lstproduct'] = $data_products;
				$this->model->{$this->getFunc('send')}($data);
			} else {
				$this->error['warning'] = $this->language->get("text_error_empty_email");
			}
		}
		
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['subject'])) {
			$data['error_subject'] = $this->error['subject'];
		} else {
			$data['error_subject'] = '';
		}

		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link($this->cPth.'|create_newsletter', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');

    	$data['save'] = $this->url->link($this->cPth.'|save_draft', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');

    	if (isset($this->request->post['template_id'])) {
			$data['template_id'] = $this->request->post['template_id'];
		} else {
			$data['template_id'] = '';
		}

		
		$data['templates'] = $this->model->{$this->getFunc('getTemplates')}();

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} else {
			$data['store_id'] = '';
		}

		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} else {
			$data['language_id'] = '';
		}

		$this->load->model('bytao/common');

		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		if (isset($this->request->post['currency'])) {
			$data['currency'] = $this->request->post['currency'];
		} else {
			$data['currency'] = '';
		}

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		if (isset($this->request->post['to'])) {
			$data['to'] = $this->request->post['to'];
		} else {
			$data['to'] = '';
		}

		if (isset($this->request->post['customer_group_id'])) {
			$data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$data['customer_group_id'] = '';
		}

		if (isset($this->request->post['customer_group_only_subscribers'])) {
			$data['customer_group_only_subscribers'] = $this->request->post['customer_group_only_subscribers'];
		} else {
			$data['customer_group_only_subscribers'] = '';
		}

		if (isset($this->request->post['only_selected_language'])) {
			$data['only_selected_language'] = $this->request->post['only_selected_language'];
		} else {
			$data['only_selected_language'] = 1;
		}

		$this->load->model('customer/customer_group');
		
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups([]);

		$data['customers'] = [];

		if (isset($this->request->post['customer']) && is_array($this->request->post['customer'])) {
			foreach ($this->request->post['customer'] as $customer_id) {
				$customer_info = $this->model_bytaonewsletter_newsletter->getCustomer($customer_id);

				if ($customer_info) {
					$data['customers'][] = array(
						'customer_id' => $customer_info['customer_id'],
						'name'        => $customer_info['firstname'] . ' ' . $customer_info['lastname']
					);
				}
			}
		}

    $data['affiliates'] = [];

      if (isset($this->request->post['affiliate']) && is_array($this->request->post['affiliate'])) {
          foreach ($this->request->post['affiliate'] as $affiliate_id) {
              $affiliate_info = $this->model_bytaonewsletter_newsletter->getAffiliate($affiliate_id);

              if ($affiliate_info) {
                  $data['affiliates'][] = array(
                      'affiliate_id' => $affiliate_info['affiliate_id'],
                      'name'         => $affiliate_info['firstname'] . ' ' . $affiliate_info['lastname']
                  );
              }
          }
      }
		$this->load->model('catalog/product');

		$data['products'] = [];

		if (isset($this->request->post['product']) && is_array($this->request->post['product'])) {
			foreach ($this->request->post['product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$data['products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name']
					);
				}
			}
		}

		if (isset($this->request->post['subject'])) {
			$data['subject'] = $this->request->post['subject'];
		} else {
			$data['subject'] = '';
		}

		if (isset($this->request->post['message'])) {
			$data['message'] = $this->request->post['message'];
		} else {
			$data['message'] = '';
		}

		$this->response->setOutput($this->cPth.'/form_newsletter');
	}
	
	
	




	public function module($extension, $module_id):array {
		$module_data = [];
		$this->load->model('extension/extension');
		$this->load->model('extension/module');
		$extensions = $this->model_extension_extension->getInstalled('module');
		$modules = $this->model_extension_module->getModulesByCode($extension);
		foreach ($modules as $module) {
			$module_data[] = array(
				'module_id' => $module['module_id'],
				'name'      => $module['name'],
				'edit'      => $this->url->link($this->cPth.'|modules', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $module_id.'=' . $module['module_id'], 'SSL'),
			);
		}
		$ex[] = array(
			'name'      => $this->language->get("create_module"),
			'module'    => $module_data,
			'edit'      => $this->url->link($this->cPth.'|modules', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL')
		);
		return $ex;
	}

	public function ndelete():void {
		$this->load->model('extension/module');
		if (isset($this->request->get['module_id'])) {
			$this->model_extension_module->deleteModule($this->request->get['module_id']);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link($this->cPth.'|modules', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
		}
	}

	public function config(){
		$this->getML('ML');
		
		
		$this->model->{$this->getFunc('installModule')}();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			//$this->model_setting_setting->editSetting('bytaonewsletter_config', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->cPth.'|config', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL'));
		}

		// Alert
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
    	$data["menu_active"] = "config";
    	$data["mail_protocals"] = array("mail"=>"Mail", "smtp"=>"SMTP");
    	$data['action'] = $this->url->link($this->cPth.'|config', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');

    	// Get Data Setting
		$data['general'] = [];
		if (isset($this->request->post['bytaonewsletter_config'])) {
			$data['general'] = $this->request->post['bytaonewsletter_config'];
		} elseif ($this->config->get('bytaonewsletter_config')) {
			$data['general'] = $this->config->get('bytaonewsletter_config');
		}

		// Render
		$template = $this->cPth.'/config';
		$this->_render($template);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['newsletter_module'])) {

		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getItemProducts(array $results , array $setting):array {
		$this->getML('ML');
		global $registry;	
		
		$this->load->model('tool/image'); 
		
		require_once( DIR_SYSTEM."/library/currency.php");
		$currency = new Currency($registry);
		$products = [];
		$i = 0;
		foreach ($results as $result) {
			if($i < $setting['limit']) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = false;
				}
				
				$price = $currency->format($result['price'], $setting['currency']);
				
				if (isset($result['special'])) {
					$special = $currency->format((float)$result['special'], $setting['currency']);
				} else {
					$special = false;
				}		
				
				$products[] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'price'       => $price,
					'special'     => $special,
					'href'        => str_replace("/admin/","/",$this->url->link('product/product', 'product_id=' . $result['product_id']))
				);
			}
			$i++;
		}
		return $products;
	}
	
	
	
	
	
	public function getwidget(array $ndata = []) {
		$this->getML('ML');
		
		$json= [];
		
		if( isset($ndata[1])){
			$data['parts'] = $ndata;
		}
		
		$filter_data = [
			'sort'  => '',
			'order' => '',
			'start' => '',
			'limit' => ''
		];
		
		$results = $this->model->{$this->getFunc('getNewsletterModules')}();

		foreach ($results as $result) {
			if($result['status']){
				$data['items'][] = [
					'item_id' 	=> $result['newsletter_module_id'],
					'title'     => $result['name'],
				];
			}
		}
		$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_widget_form', $data);
		
		
		if( isset($ndata[1])){
			return $json['view'];
		}else{
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	
	}
}