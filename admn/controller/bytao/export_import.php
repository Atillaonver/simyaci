<?php
namespace Opencart\Admin\Controller\Bytao;
class ExportImport extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.2';
	private $cPth = 'bytao/export_import';
	private $C = 'export_import';
	private $ID = 'export_import_id';
	private $Tkn = 'user_token';
	private $model;
	protected $method_separator;


	public function __construct(\Opencart\System\Engine\Registry $registry) {
		parent::__construct($registry);
		$this->method_separator = version_compare(VERSION,'4.0.2.0','>=') ? '.' : '|';
	}
	
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
	
	
	public function index():void {
		if (!$this->config->get('other_export_import_status')) {
			$url = $this->url->link('error/not_found','user_token='.$this->session->data['user_token'] );
			$this->response->redirect( $url );
		}
		$this->load->language($this->cPth);
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model($this->cPth);
		$this->getForm();
	}
	
	public function tab():string {
		
		return $this->getTabForm();
	}
	
	public function upload() {
		$this->load->language($this->cPth);
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model($this->cPth);
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateUploadForm())) {
			if ((isset( $this->request->files['upload'] )) && (is_uploaded_file($this->request->files['upload']['tmp_name']))) {
				$file = $this->request->files['upload']['tmp_name'];
				$incremental = ($this->request->post['incremental']) ? true : false;
				if ($this->model->upload($file,$this->request->post['incremental'])==true) {
					$this->session->data['success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] ));
				}
				else {
					$this->session->data['warning'] = $this->language->get('error_upload');
					$href = $this->url->link( 'tool/log', 'user_token='.$this->session->data['user_token'] );
					$this->session->data['warning'] .= "<br />\n".str_replace('%1',$href,$this->language->get( 'text_log_details_3_x' ));
					$this->response->redirect($this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token']));
				}
			}
		}

		$this->getForm();
	}


	protected function return_bytes($val)
	{
		$val = trim($val);
	
		switch (strtolower(substr($val, -1)))
		{
			case 'm': $val = (int)substr($val, 0, -1) * 1048576; break;
			case 'k': $val = (int)substr($val, 0, -1) * 1024; break;
			case 'g': $val = (int)substr($val, 0, -1) * 1073741824; break;
			case 'b':
				switch (strtolower(substr($val, -2, 1)))
				{
					case 'm': $val = (int)substr($val, 0, -2) * 1048576; break;
					case 'k': $val = (int)substr($val, 0, -2) * 1024; break;
					case 'g': $val = (int)substr($val, 0, -2) * 1073741824; break;
					default : break;
				} break;
			default: break;
		}
		return $val;
	}


	public function download() {
		$this->getML('LM');
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDownloadForm()) {
			$export_type = $this->request->post['export_type'];
			switch ($export_type) {
				case 'c':
				case 'p':
				case 'u':
					$min = null;
					if (isset( $this->request->post['min'] ) && ($this->request->post['min']!='')) {
						$min = $this->request->post['min'];
					}
					$max = null;
					if (isset( $this->request->post['max'] ) && ($this->request->post['max']!='')) {
						$max = $this->request->post['max'];
					}
					if (($min==null) || ($max==null)) {
						$this->model->download($export_type, null, null, null, null);
					} else if ($this->request->post['range_type'] == 'id') {
						$this->model->download($export_type, null, null, $min, $max);
					} else {
						$this->model->download($export_type, $min*($max-1-1), $min, null, null);
					}
					break;
				case 'o':
					$this->model->download('o', null, null, null, null);
					break;
				case 'a':
					$this->model->download('a', null, null, null, null);
					break;
				case 'f':
					$this->model->download('f', null, null, null, null);
					break;
				default:
					break;
			}
			$this->response->redirect( $this->url->link( $this->cPth, 'user_token='.$this->request->get['user_token']) );
		}

		$this->getForm();
	}


	public function settings() {
		$this->load->language($this->cPth);
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model($this->cPth);
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validateSettingsForm())) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('export_import', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_settings');
			$this->response->redirect($this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token']));
		}
		$this->getForm();
	}


	protected function getForm() {
		$data = [];

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

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'])
		);

		$data['back'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']);
		$data['button_back'] = $this->language->get( 'button_back' );
		$data['import'] = $this->url->link($this->cPth.'.upload', 'user_token=' . $this->session->data['user_token']);
		$data['export'] = $this->url->link($this->cPth.'.download', 'user_token=' . $this->session->data['user_token']);
		$data['settings'] = $this->url->link($this->cPth.'.settings', 'user_token=' . $this->session->data['user_token']);
		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );

		if (isset($this->request->post['export_type'])) {
			$data['export_type'] = $this->request->post['export_type'];
		} else {
			$data['export_type'] = 'p';
		}

		if (isset($this->request->post['range_type'])) {
			$data['range_type'] = $this->request->post['range_type'];
		} else {
			$data['range_type'] = 'id';
		}

		if (isset($this->request->post['min'])) {
			$data['min'] = $this->request->post['min'];
		} else {
			$data['min'] = '';
		}

		if (isset($this->request->post['max'])) {
			$data['max'] = $this->request->post['max'];
		} else {
			$data['max'] = '';
		}

		if (isset($this->request->post['incremental'])) {
			$data['incremental'] = $this->request->post['incremental'];
		} else {
			$data['incremental'] = '1';
		}

		if (isset($this->request->post['export_import_settings_use_option_id'])) {
			$data['settings_use_option_id'] = $this->request->post['export_import_settings_use_option_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_id' )) {
			$data['settings_use_option_id'] = '1';
		} else {
			$data['settings_use_option_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_option_value_id'])) {
			$data['settings_use_option_value_id'] = $this->request->post['export_import_settings_use_option_value_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_value_id' )) {
			$data['settings_use_option_value_id'] = '1';
		} else {
			$data['settings_use_option_value_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_group_id'])) {
			$data['settings_use_attribute_group_id'] = $this->request->post['export_import_settings_use_attribute_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_group_id' )) {
			$data['settings_use_attribute_group_id'] = '1';
		} else {
			$data['settings_use_attribute_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_id'])) {
			$data['settings_use_attribute_id'] = $this->request->post['export_import_settings_use_attribute_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_id' )) {
			$data['settings_use_attribute_id'] = '1';
		} else {
			$data['settings_use_attribute_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_group_id'])) {
			$data['settings_use_filter_group_id'] = $this->request->post['export_import_settings_use_filter_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_group_id' )) {
			$data['settings_use_filter_group_id'] = '1';
		} else {
			$data['settings_use_filter_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_id'])) {
			$data['settings_use_filter_id'] = $this->request->post['export_import_settings_use_filter_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_id' )) {
			$data['settings_use_filter_id'] = '1';
		} else {
			$data['settings_use_filter_id'] = '0';
		}

		$data['categories'] = [];
		$data['manufacturers'] = [];

		$min_product_id = $this->model->getMinProductId();
		$max_product_id = $this->model->getMaxProductId();
		$count_product = $this->model->getCountProduct();
		$min_category_id = $this->model->getMinCategoryId();
		$max_category_id = $this->model->getMaxCategoryId();
		$count_category = $this->model->getCountCategory();
		$min_customer_id = $this->model->getMinCustomerId();
		$max_customer_id = $this->model->getMaxCustomerId();
		$count_customer = $this->model->getCountCustomer();

		$data['text_welcome'] = str_replace('%1',$this->model->getVersion(),$this->language->get('text_welcome'));
		$data['text_used_category_ids'] = $this->language->get('text_used_category_ids');
		$data['text_used_category_ids'] = str_replace('%1',$min_category_id,$data['text_used_category_ids']);
		$data['text_used_category_ids'] = str_replace('%2',$max_category_id,$data['text_used_category_ids']);
		$data['text_used_product_ids'] = $this->language->get('text_used_product_ids');
		$data['text_used_product_ids'] = str_replace('%1',$min_product_id,$data['text_used_product_ids']);
		$data['text_used_product_ids'] = str_replace('%2',$max_product_id,$data['text_used_product_ids']);

		$data['version_export_import'] = $this->model->getVersion();
		$data['version_opencart'] = VERSION;

		$data['min_product_id'] = $min_product_id;
		$data['max_product_id'] = $max_product_id;
		$data['count_product'] = $count_product;
		$data['min_category_id'] = $min_category_id;
		$data['max_category_id'] = $max_category_id;
		$data['count_category'] = $count_category;
		$data['min_customer_id'] = $min_customer_id;
		$data['max_customer_id'] = $max_customer_id;
		$data['count_customer'] = $count_customer;

		$data['user_token'] = $this->session->data['user_token'];

		$data['method_separator'] = $this->method_separator;

		$this->document->addStyle('../extension/export_import/admin/view/stylesheet/export_import.css');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$view = $this->load->view( $this->cPth, $data);
		$search = '<meta http-equiv="expires" content="0">';
		$add = '<script type="text/javascript">var export_import_alert = window.alert;</script>';
		$view = str_replace($search,$search."\n".$add,$view);
		$this->response->setOutput($view);
	}
	
	protected function getTabForm(){
		$data = [];
		$this->getML('LM');
		
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

		
		$data['import'] = $this->url->link($this->cPth.'.upload', 'user_token=' . $this->session->data['user_token']);
		$data['export'] = $this->url->link($this->cPth.'.download', 'user_token=' . $this->session->data['user_token']);
		$data['settings'] = $this->url->link($this->cPth.'.settings', 'user_token=' . $this->session->data['user_token']);
		$data['post_max_size'] = $this->return_bytes( ini_get('post_max_size') );
		$data['upload_max_filesize'] = $this->return_bytes( ini_get('upload_max_filesize') );

		if (isset($this->request->post['export_type'])) {
			$data['export_type'] = $this->request->post['export_type'];
		} else {
			$data['export_type'] = 'p';
		}

		if (isset($this->request->post['range_type'])) {
			$data['range_type'] = $this->request->post['range_type'];
		} else {
			$data['range_type'] = 'id';
		}

		if (isset($this->request->post['min'])) {
			$data['min'] = $this->request->post['min'];
		} else {
			$data['min'] = '';
		}

		if (isset($this->request->post['max'])) {
			$data['max'] = $this->request->post['max'];
		} else {
			$data['max'] = '';
		}

		if (isset($this->request->post['incremental'])) {
			$data['incremental'] = $this->request->post['incremental'];
		} else {
			$data['incremental'] = '1';
		}

		if (isset($this->request->post['export_import_settings_use_option_id'])) {
			$data['settings_use_option_id'] = $this->request->post['export_import_settings_use_option_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_id' )) {
			$data['settings_use_option_id'] = '1';
		} else {
			$data['settings_use_option_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_option_value_id'])) {
			$data['settings_use_option_value_id'] = $this->request->post['export_import_settings_use_option_value_id'];
		} else if ($this->config->get( 'export_import_settings_use_option_value_id' )) {
			$data['settings_use_option_value_id'] = '1';
		} else {
			$data['settings_use_option_value_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_group_id'])) {
			$data['settings_use_attribute_group_id'] = $this->request->post['export_import_settings_use_attribute_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_group_id' )) {
			$data['settings_use_attribute_group_id'] = '1';
		} else {
			$data['settings_use_attribute_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_attribute_id'])) {
			$data['settings_use_attribute_id'] = $this->request->post['export_import_settings_use_attribute_id'];
		} else if ($this->config->get( 'export_import_settings_use_attribute_id' )) {
			$data['settings_use_attribute_id'] = '1';
		} else {
			$data['settings_use_attribute_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_group_id'])) {
			$data['settings_use_filter_group_id'] = $this->request->post['export_import_settings_use_filter_group_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_group_id' )) {
			$data['settings_use_filter_group_id'] = '1';
		} else {
			$data['settings_use_filter_group_id'] = '0';
		}

		if (isset($this->request->post['export_import_settings_use_filter_id'])) {
			$data['settings_use_filter_id'] = $this->request->post['export_import_settings_use_filter_id'];
		} else if ($this->config->get( 'export_import_settings_use_filter_id' )) {
			$data['settings_use_filter_id'] = '1';
		} else {
			$data['settings_use_filter_id'] = '0';
		}

		$data['categories'] = [];
		$data['manufacturers'] = [];

		$min_product_id = $this->model->getMinProductId();
		$max_product_id = $this->model->getMaxProductId();
		$count_product = $this->model->getCountProduct();
		$min_category_id = $this->model->getMinCategoryId();
		$max_category_id = $this->model->getMaxCategoryId();
		$count_category = $this->model->getCountCategory();
		$min_customer_id = $this->model->getMinCustomerId();
		$max_customer_id = $this->model->getMaxCustomerId();
		$count_customer = $this->model->getCountCustomer();

		$data['text_welcome'] = str_replace('%1',$this->model->getVersion(),$this->language->get('text_welcome'));
		$data['text_used_category_ids'] = $this->language->get('text_used_category_ids');
		$data['text_used_category_ids'] = str_replace('%1',$min_category_id,$data['text_used_category_ids']);
		$data['text_used_category_ids'] = str_replace('%2',$max_category_id,$data['text_used_category_ids']);
		$data['text_used_product_ids'] = $this->language->get('text_used_product_ids');
		$data['text_used_product_ids'] = str_replace('%1',$min_product_id,$data['text_used_product_ids']);
		$data['text_used_product_ids'] = str_replace('%2',$max_product_id,$data['text_used_product_ids']);

		$data['version_export_import'] = $this->model->getVersion();
		$data['version_opencart'] = VERSION;

		$data['min_product_id'] = $min_product_id;
		$data['max_product_id'] = $max_product_id;
		$data['count_product'] = $count_product;
		$data['min_category_id'] = $min_category_id;
		$data['max_category_id'] = $max_category_id;
		$data['count_category'] = $count_category;
		$data['min_customer_id'] = $min_customer_id;
		$data['max_customer_id'] = $max_customer_id;
		$data['count_customer'] = $count_customer;

		$data['user_token'] = $this->session->data['user_token'];

		$data['method_separator'] = $this->method_separator;

		$view = $this->load->view($this->cPth.'_tab', $data);
		//$this->log->write('export:'.$view);
		return $view;
	}


	protected function validateDownloadForm() {
		if (!$this->user->hasPermission('access', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}
		
/*
		if (!$this->config->get( 'export_import_settings_use_option_id' )) {
			$option_names = $this->model->getOptionNameCounts();
			foreach ($option_names as $option_name) {
				if ($option_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_name['name'], $this->language->get( 'error_option_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_option_value_id' )) {
			$option_value_names = $this->model->getOptionValueNameCounts();
			foreach ($option_value_names as $option_value_name) {
				if ($option_value_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_value_name['name'], $this->language->get( 'error_option_value_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_attribute_group_id' )) {
			$attribute_group_names = $this->model->getAttributeGroupNameCounts();
			foreach ($attribute_group_names as $attribute_group_name) {
				if ($attribute_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_group_name['name'], $this->language->get( 'error_attribute_group_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_attribute_id' )) {
			$attribute_names = $this->model->getAttributeNameCounts();
			foreach ($attribute_names as $attribute_name) {
				if ($attribute_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_name['name'], $this->language->get( 'error_attribute_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_filter_group_id' )) {
			$filter_group_names = $this->model->getFilterGroupNameCounts();
			foreach ($filter_group_names as $filter_group_name) {
				if ($filter_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_group_name['name'], $this->language->get( 'error_filter_group_name' ) );
					return false;
				}
			}
		}

		if (!$this->config->get( 'export_import_settings_use_filter_id' )) {
			$filter_names = $this->model->getFilterNameCounts();
			foreach ($filter_names as $filter_name) {
				if ($filter_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_name['name'], $this->language->get( 'error_filter_name' ) );
					return false;
				}
			}
		}
*/
		return true;
	}


	protected function validateUploadForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		} else if (!isset( $this->request->post['incremental'] )) {
			$this->error['warning'] = $this->language->get( 'error_incremental' );
		} else if ($this->request->post['incremental'] != '0') {
			if ($this->request->post['incremental'] != '1') {
				$this->error['warning'] = $this->language->get( 'error_incremental' );
			}
		}

		if (!isset($this->request->files['upload']['name'])) {
			if (isset($this->error['warning'])) {
				$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_name' );
			} else {
				$this->error['warning'] = $this->language->get( 'error_upload_name' );
			}
		} else {
			$ext = strtolower(pathinfo($this->request->files['upload']['name'], PATHINFO_EXTENSION));
			if (($ext != 'xls') && ($ext != 'xlsx') && ($ext != 'ods')) {
				if (isset($this->error['warning'])) {
					$this->error['warning'] .= "<br /\n" . $this->language->get( 'error_upload_ext' );
				} else {
					$this->error['warning'] = $this->language->get( 'error_upload_ext' );
				}
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	protected function validateSettingsForm() {
		if (!$this->user->hasPermission('access', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		if (empty($this->request->post['export_import_settings_use_option_id'])) {
			$option_names = $this->model->getOptionNameCounts();
			foreach ($option_names as $option_name) {
				if ($option_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_name['name'], $this->language->get( 'error_option_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_option_value_id'])) {
			$option_value_names = $this->model->getOptionValueNameCounts();
			foreach ($option_value_names as $option_value_name) {
				if ($option_value_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $option_value_name['name'], $this->language->get( 'error_option_value_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_attribute_group_id'])) {
			$attribute_group_names = $this->model->getAttributeGroupNameCounts();
			foreach ($attribute_group_names as $attribute_group_name) {
				if ($attribute_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_group_name['name'], $this->language->get( 'error_attribute_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_attribute_id'])) {
			$attribute_names = $this->model->getAttributeNameCounts();
			foreach ($attribute_names as $attribute_name) {
				if ($attribute_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $attribute_name['name'], $this->language->get( 'error_attribute_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_filter_group_id'])) {
			$filter_group_names = $this->model->getFilterGroupNameCounts();
			foreach ($filter_group_names as $filter_group_name) {
				if ($filter_group_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_group_name['name'], $this->language->get( 'error_filter_group_name' ) );
					return false;
				}
			}
		}

		if (empty($this->request->post['export_import_settings_use_filter_id'])) {
			$filter_names = $this->model->getFilterNameCounts();
			foreach ($filter_names as $filter_name) {
				if ($filter_name['count'] > 1) {
					$this->error['warning'] = str_replace( '%1', $filter_name['name'], $this->language->get( 'error_filter_name' ) );
					return false;
				}
			}
		}

		return true;
	}


	public function getNotifications() {
		sleep(1); // give the data some "feel" that its not in our system
		$this->load->model($this->cPth);
		$this->load->language( $this->cPth );
		$response = $this->model->getNotifications();
		$json = [];
		if ($response===false) {
			$json['message'] = '';
			$json['error'] = $this->language->get( 'error_notifications' );
		} else {
			$json['message'] = $response;
			$json['error'] = '';
		}
		$this->response->setOutput(json_encode($json));
	}


	public function getCountProduct() {
		$this->load->model($this->cPth);
		$count = $this->model->getCountProduct();
		$json = array( 'count'=>$count );
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>