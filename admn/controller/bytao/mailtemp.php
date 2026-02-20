<?php
class ControllerBytaoMailtemp extends Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/mailtemp';
	private $C = 'mailtemp';
	private $ID = 'mailtemp_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id']=$this->storeId;
		}else{
			$this->storeId = $this->session->data['store_id'];
		}
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function install(){
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	
	public function index() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getList();
	}
	
	public function add() {
		$this->getML('ML');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model->{$this->getFunc('add')}($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model->{$this->getFunc('edit')}($this->request->get[$this->ID], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function copy() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model->{$this->getFunc('copy')}($item_id);
			}
			$url='';
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'md.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL')
		);
		
		$data['add'] = $this->url->link($this->cPth.'/add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
		$data['copy'] = $this->url->link($this->cPth.'/copy', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
		$data['delete'] = $this->url->link($this->cPth.'/delete', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');

		$data[$this->C.'s'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$total_items = $this->model->{$this->getFunc('getTotal','s')};

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data[$this->C.'s'][] = array(
				$this->ID => $result[$this->ID],
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'module'     => $result['module'],
				'edit'           => $this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL'),
				'preview'           => $this->url->link($this->cPth.'/preview', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_title'] = $this->language->get('column_title');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_copy'] = $this->language->get('button_copy');
		$data['button_preview'] = $this->language->get('button_preview');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=md.title' . $url, 'SSL');
		$data['sort_module'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=m.module' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=m.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_items;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_items) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_items - $this->config->get('config_limit_admin'))) ? $total_items : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_items, ceil($total_items / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_list', $data));
	}

	protected function getForm() {
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_meta_title'] = $this->language->get('entry_meta_title');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_keyword'] = $this->language->get('entry_keyword');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_bottom'] = $this->language->get('entry_bottom');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_layout'] = $this->language->get('entry_layout');
		
		$data['entry_module'] = $this->language->get('entry_module');

		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['help_bottom'] = $this->language->get('help_bottom');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['tab_design'] = $this->language->get('tab_design');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}
		
		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL')
		);
		
		if (!isset($this->request->get[$this->ID])) {
			$data['action'] = $this->url->link($this->cPth.'/add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $this->request->get[$this->ID] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');

		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['mailtemp_description'])) {
			$data['mailtemp_description'] = $this->request->post['mailtemp_description'];
		} elseif (isset($this->request->get[$this->ID])) {
			$data['mailtemp_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data['mailtemp_description'] = array();
		}

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		

		if (isset($this->request->post['mailtemp_store'])) {
			$data['mailtemp_store'] = $this->request->post['mailtemp_store'];
		} elseif (isset($this->request->get[$this->ID])) {
			$data['mailtemp_store'] = $this->model->{$this->getFunc('get','Stores')}($this->request->get[$this->ID]);
		} else {
			$data['mailtemp_store'] = array(0);
		}

	

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($item_info)) {
			$data['type'] = $item_info['type'];
		} else {
			$data['type'] = 0;
		}
		
		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($item_info)) {
			$data['code'] = $item_info['code'];
		} else {
			$data['code'] = "";
		}
	
		if (isset($this->request->post['module'])) {
			$data['module'] = $this->request->post['module'];
		} elseif (!empty($item_info)) {
			$data['module'] = $item_info['module'];
		} else {
			$data['module'] = "";
		}

		

		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['mailtemp_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}

		}


		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		foreach ($this->request->post['selected'] as $item_id) {
			if ($this->config->get('config_account_id') == $item_id) {
				$this->error['warning'] = $this->language->get('error_account');
			}

			if ($this->config->get('config_checkout_id') == $item_id) {
				$this->error['warning'] = $this->language->get('error_checkout');
			}

			if ($this->config->get('config_affiliate_id') == $item_id) {
				$this->error['warning'] = $this->language->get('error_affiliate');
			}

			if ($this->config->get('config_return_id') == $item_id) {
				$this->error['warning'] = $this->language->get('error_return');
			}

			$store_total = $this->model_setting_store->getTotalStoresByInformationId($item_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function preview() {
		$this->getML('ML');
		$json = array();
		$data = array();
		
		if (isset($this->request->get[$this->ID])) {
			
			$descriptions = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			$data['mail_content']=html_entity_decode($descriptions[(int)$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
			$json['body']=$this->load->view($this->cPth.'_preview', $data);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/*
	public function history() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->getHistoryList();
	}

	public function deletehistory() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model->{$this->getFunc('delete','Mail')}($item_id);
			}

		}

		$this->getHistoryList();
	}

	protected function getHistoryList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_history_title'),
			'href' => $this->url->link($this->cPth.'/history', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL')
		);
		
		$data['delete'] = $this->url->link($this->cPth.'/deletehistory', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');

		$data['mails'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$total_items = $this->model->{$this->getFunc('getTotal','Mails')}();

		$results = $this->model->{$this->getFunc('get','Mails')}($filter_data);

		foreach ($results as $result) {
			$data['mails'][] = array(
				'mail_history_id' => $result['mail_history_id'],
				'mail_subject'          => $result['mail_subject'],
				'date_added'          => date($language->get('date_format_long'),$result['date_added']),
				'preview'           => $this->url->link($this->cPth.'/preview', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&mail_history_id=' . $result['mail_history_id'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_history_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_history_title'] = $this->language->get('column_history_title');
		$data['column_history_subject'] = $this->language->get('column_history_subject');
		$data['column_history_date'] = $this->language->get('column_history_date');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_preview'] = $this->language->get('button_preview');
		$data['button_delete'] = $this->language->get('button_delete');

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_items;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_items) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_items - $this->config->get('config_limit_admin'))) ? $total_items : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_items, ceil($total_items / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_history_list.tpl', $data));
	}

	public function historypreview() {
		$this->getML('ML');
		$json = array();
		$data = array();

		if (isset($this->request->get[$this->ID])) {
			$this->load->model($this->cPth);
			$mail = $this->model->{$this->getFunc('get','Mail')}($this->request->get['mail_history_id']);

			$data['content']=html_entity_decode($mail['content'], ENT_QUOTES, 'UTF-8');
			$json['body']=$this->load->view($this->cPth.'_template_preview.tpl', $data);
		}
		
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function content(){
		$this->getML('ML');
		$json = array();
		
		if (isset($this->request->get[$this->ID])) {
			
			$descriptions = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			$json['mail_content']=html_entity_decode($descriptions[1]['description'], ENT_QUOTES, 'UTF-8');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function send() {
		$this->load->language('marketing/contact');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') 
		{
			
			
		$this->getML('ML');
		
		$this->load->model('setting/setting');
		$this->load->model('setting/store');

		
		$this->load->model('marketing/coupon');
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('sale/order');
		
		$store_id = $this->session->data['store_id'];
		
		$setting = $this->model_setting_setting->getSetting('config', $store_id);
				

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}
				
		if (isset($this->request->get['total'])) {
			$total = $this->request->get['total'];
		} else {
			$total = count($orders);
		}

		$email_total = 0;
		$emails = array();
		$content = array();
		
		
		$descriptions = $this->model->{$this->getFunc('get','Descriptions')}($this->request->post['templates']);
		
		$template = $this->model->{$this->getFunc('get','ById')}($this->request->post['templates']);
	
		$code_id = isset($template['code'])?$template['code']:"";
		$code="&code=".$code_id;
		
		$coupon_info = $this->model_marketing_coupon->getCouponByCode($code_id);;
		
		if($coupon_info)
		{
			$content_main = $descriptions[1]['description'];
			$subject = $descriptions[1]['meta_title'];
			if (isset($orders)){
				$email_total = count($orders);
				$first=true;
				$order_id=$orders[0];
				
				
				$mailContent = array();
				if( $first ){
						$order_info = $this->model_sale_order->getOrder($order_id);
						$Orderid = $order_id;
						if (preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $order_info['email'])) {
							$emails[] = $order_info['email'];
							$first=false;
						}
						
					}
					
				$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
				$totalP = count($order_product_query->rows);
				
				
				$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
				}
				
				
				$url = new Url($setting['config_url'],$setting['config_url']);
				
				foreach ($order_product_query->rows as $order_product) {
					$product = $this->model_catalog_product->getProduct($order_product['product_id']);
					if (!empty($product) && is_file(DIR_IMAGE . $product['image'])) {
						$thumb = $this->model_tool_image->resize($product['image'], 320, 380);
					} else {
						$thumb = $this->model_tool_image->resize('no_image.png', 320, 380);
					}
					
					
					$mailContent['products'][]=array(
						'href' => $url->link('product/product', $code. '&product_id=' . $order_product['product_id']),
						'name' => $order_product['name'],
						'thumb' => $thumb
					);
					
				
				}
				$content[] = array(
					'content' => $this->load->view('bytao/templates/campaign_order_table.tpl', $mailContent)
					);
						
				
			}
			
			if ($emails) {
				
				if ($email_total!=1) {
					
					$json['success'] = sprintf($this->language->get('text_sent'), 1, $total);
					$json['next'] = str_replace('&amp;', '&', $this->url->link($this->cPth.'/send', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&total=' . $total, 'SSL'));
					$json['oid']=$Orderid;
				} else {
					
					$json['success'] = $this->language->get('text_success');
					$json['next'] = '';
					$json['oid']=$Orderid;
				}

				$find = array(
					'{lastvisited}',
					'{coupondate}',
					'{link}'
				);
				if($coupon_info['type_customer']==1){
					
					$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_personal SET code='" . $this->db->escape($code_id). "', email='".$this->db->escape($order_info['email'])."', customer_id='".(int)$order_info['customer_id']."', order_id='".(int)$order_info['order_id']."', date_start= NOW(), date_end='".date('Y-m-d',strtotime('+15 days',$date))."'");
				
					$date = strtotime(date('Y-m-d'));
					$replace = array(
						'lastvisited' => $content[0]['content'],
						'coupondate' => date('l jS \of F Y',strtotime('+15 days',$date)),
						'link' =>$url->link('common/home')
						
					);
				}else{
					$date = strtotime($coupon_info['date_end']);
					$replace = array(
						'lastvisited' => $content[0]['content'],
						'coupondate' => date('l jS \of F Y',strtotime($date)),
						'link' =>$url->link('common/home')
					);
				}
				
				
				
				
				
					
				$content_last = str_replace($find, $replace, $content_main);
				
				
				$message  = '<html dir="ltr" lang="en">' . "\n";
				$message .= '  <head>' . "\n";
				$message .= '    <title>' . $subject . '</title>' . "\n";
				$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
				$message .= '  </head>' . "\n";
				$message .= '  <body>' . html_entity_decode($content_last, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
				$message .= '</html>' . "\n";
				
				$this->load->model($this->cPth);
				
				
				
				if (preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $emails[0])) {
						
						$mail = new Mail();
						
						$mail->protocol = $setting['config_mail_protocol'];
						$mail->parameter = $setting['config_mail_parameter'];
						$mail->smtp_hostname = $setting['config_mail_smtp_hostname'];
						$mail->smtp_username =$setting['config_mail_smtp_username'];
						$mail->smtp_password = html_entity_decode($setting['config_mail_smtp_password'], ENT_QUOTES, 'UTF-8');
						$mail->smtp_port = $setting['config_mail_smtp_port'];
						$mail->smtp_timeout = $setting['config_mail_smtp_timeout'];
				
						$mail->setTo($order_info['email']);
						$mail->setFrom($setting['config_email']);
						$mail->setSender($order_info['store_name']);
						$mail->setSubject($subject);
						$mail->setHtml($message);
						
						
						$mail->send();
						
						
						
						$mail->setTo($this->config->get('config_email'));
						$mail->setSender($order_info['store_name']);
						$mail->setSubject('ADMIN INFORMATION:'.$subject);
						$mail->send();
						
						$emails = explode(',', $this->config->get('config_mail_alert'));
						foreach ($emails as $email) {
							if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
								$mail->setTo($email);
								$mail->send();
							}
						}

						$mail_id = $this->model->{$this->getFunc('add')}addMail($emails[0],$subject,$message);
					}
			}
		
		}
		else
		{
			$json['error'] = $this->language->get('error_code_mail');
		}
				
		
		
		
		
		
		
		
		
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function sendby() {
		$this->load->language('marketing/contact');

		$json = array();
		$api_info = array(
			'product_id' => $this->request->get['pid'],
			'order_id' => $this->request->get['oid'],
			'templates' => $this->request->get['tid'],
		);
		
		$curl = curl_init();

		$this->load->model('setting/setting');
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		if($store_id==0){
			$config_url = HTTPS_CATALOG;
		}else{
			$setting = $this->model_setting_setting->getSetting('config', $store_id);
			$config_url = $setting['config_url'];
		}
		//$this->log->write('config_url:'.print_r( $config_url,true));
		// Set SSL if required
		if (substr($config_url, 0, 5) == 'https') {
			curl_setopt($curl, CURLOPT_PORT, 443);
		}
		
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		//curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $config_url . 'index.php?route=api/coupon/send&bytao=sender');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($api_info));

		$nJson = curl_exec($curl);
		$this->log->write('response:'.print_r( $nJson,true));
		
		if (!$nJson) 
		{
			$data['error'] =  curl_error($curl).' - '. curl_errno($curl);
		} 
		else 
		{
			$response = json_decode($nJson, true);
			if(isset($response['success'])){
				$json['success'] = $response['success'];	
			}
		}	
		curl_close($curl);
		
		$this->load->model($this->cPth);
		$this->load->model('sale/order');
		
		if (isset($this->request->get['oid'])) {
			$order_id = $this->request->get['oid'];
			$order_info = $this->model_sale_order->getOrder($order_id);
			if (preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $order_info['email'])) {
							$emails[] = $order_info['email'];
							
			}
				
			$orders = $this->model_sale_order->getOrdersByEmail($order_info['email']);
			$mails  = $this->model->{$this->getFunc('get','MailsByMail')}($order_info['email']);
			
			$history='';
			$list=array();
			
			foreach ($orders as $order){
				$list[$order['date_added']] ='<li><span class="date-added">'.$order['date_added'].'</span> ORDER - <span class="subject">'.$order['status'].'</span></li>';
			}
			
			foreach ($mails as $mail){
				$list[$mail['date_added']] = '<li><span class="date-added">'.$mail['date_added'].'</span> COUPON - <span  class="subject">'.$mail['mail_subject'].'</span></li>';
				
			}

			//array_multisort($list, SORT_ASC,$time );
			krsort($list,SORT_REGULAR );
			
			foreach ($list as $key => $item){
				$history .= $item;
			}
		
			$data['mail_history']= $history;
			$json['history'] = $history;
			
			
		}
				
	

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function modal(){
		$this->getML('ML');
		$json = array();
		$data = array();
		$data['mail_history']=array();
		$data['product_history']=array();
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$this->load->model('marketing/coupon');
		
		if(isset($this->request->get['order_id'])){
			$order_id = $this->request->get['order_id'];
			$data['status'] = $this->request->get['status'];
			$this->load->model('setting/setting');
			$this->load->model('setting/store');

			$this->load->model('catalog/product');
			$this->load->model('tool/image');
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($order_id);
			if($order_info){
				$store_id = $order_info['store_id'];
				$setting = $this->model_setting_setting->getSetting('config', $store_id);
				
				$orders = $this->model_sale_order->getOrdersByEmail($order_info['email']);
				$mails  = $this->model->{$this->getFunc('get','MailsByMail')}($order_info['email']);
				
				$history='';
				$list=array();
				
				foreach ($orders as $order){
					$list[$order['date_added']] ='<li><span class="date-added">'.$order['date_added'].'</span> ORDER - <span class="subject">'.$order['status'].'</span></li>';
				}
				foreach ($mails as $mail){
					$list[$mail['date_added']] = '<li><span class="date-added">'.$mail['date_added'].'</span> COUPON - <span  class="subject">'.$mail['mail_subject'].'</span></li>';
					
				}
				krsort($list,SORT_REGULAR  );

				foreach ($list as $key=>$item){
					$history .= $item;
				}
				$data['mail_history']= $history;
				$orders_products = $this->model->{$this->getFunc('get','CustomerProductsforMail')}($order_info['email']);
				
				foreach($orders_products as $product){
					
					if (is_file(DIR_IMAGE . $product['image'])) {
						$imge = $this->model_tool_image->resize($product['image'], 100, 100);
						
					} else {
						$imge = $this->model_tool_image->resize('no_image.png', 100, 100);
					}
					
					//$this->log->write('IMAGE:'.$imge);
					
					$data['mail_templates'] = $this->model->{$this->getFunc('get','s')}(array('type'=>1));
					
					$data['product_history'][]=array(
						'name'=> $product['productName'],
						'product_id'=> $product['productId'],
						'order_id'	=> $product['orderId'],
						'status'	=> $product['orderStatus'],
						'thm'   	=> $imge
					);
				}
				
			}
			
		}
		
		
		
		$json['body']=$this->load->view($this->cPth.'_modal.tpl', $data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function mpreview() {
		$this->getML('ML');
		$json = array();
		$data = array();
		
		if (isset($this->request->get['tId'])) {
			
			$this->load->model('setting/setting');
			$this->load->model('setting/store');

			$this->load->model('marketing/coupon');
			
			$this->load->model('catalog/product');
			$this->load->model('tool/image');
			$this->load->model('sale/order');
			
			$store_id = $this->session->data['store_id'];
			if($store_id==0){
				$url = $this->url;
				$config_url = $this->config->get('config_url');
			}else{
				$setting = $this->model_setting_setting->getSetting('config', $store_id);
				$url = new Url($setting['config_url'],$setting['config_url']);	
				$config_url = $setting['config_url'];
			}
			
			
			$descriptions = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get['tId']);
			$content_main = $descriptions[1]['description'];
			
			$template = $this->model->{$this->getFunc('get','ById')}($this->request->get['tId']);
			$code_id = isset($template['code'])?$template['code']:"";
			$code="&code=".$code_id;
			$coupon_info = $this->model_marketing_coupon->getCouponByCode($code_id);
			
			$product = $this->model_catalog_product->getProduct($this->request->get['pId']);
			if (!empty($product) && is_file(DIR_IMAGE . $product['image'])) {
					$thumb = $this->model_tool_image->resize($product['image'], 320, 380);
			} else {
					$thumb = $this->model_tool_image->resize('no_image.png', 320, 380);
			}
					
					
				
			$mailContent['products'][]=array(
						'href' => $url->link('product/product', $code. '&product_id=' . $this->request->get['pId']),
						'name' => $product['name'],
						'thumb' => $thumb
					);
					
				
			$content = $this->load->view('bytao/templates/campaign_order_table.tpl', $mailContent);
			
			
			$find = array(
					'{lastvisited}',
					'{coupondate}',
					'{link}',
					'{code}'
				);
				
			if($coupon_info['type_customer']==1){
					$dateEnd = date('l jS \of F Y', strtotime('+15 days'));
					$replace = array(
						'lastvisited' => $content,
						'coupondate' => $dateEnd ,
						'link' => $config_url.$url->link('common/home'),
						'code' => $code_id
						
					);
			}else{
					$dateEnd = date('l jS \of F Y', strtotime($coupon_info['date_end']));
					$replace = array(
						'lastvisited' => $content,
						'coupondate' => $dateEnd,
						'link' =>$config_url.$url->link('common/home'),
						'code' => $code_id
					);
			}
					
			
			
			$content_last = str_replace($find, $replace, $content_main);
			
					
			$data['mail_content']=html_entity_decode($content_last, ENT_QUOTES, 'UTF-8');
			$json['body'] = $this->load->view($this->cPth.'_template_preview.tpl', $data);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function msend() {
		$this->load->language('marketing/contact');
		$this->getML('ML');
		$json = array();

		
		$this->load->model('setting/setting');
		$this->load->model('setting/store');

		$this->load->model('marketing/coupon');
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('sale/order');
		
		$store_id = $this->session->data['store_id'];
		$setting = $this->model_setting_setting->getSetting('config', $store_id);
		$url = new Url($setting['config_url'],$setting['config_url']);		
		$orders[] = $this->request->get['oId'];
		
		$descriptions = $json['descriptions'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get['tId']);
		
		$template = $this->model->{$this->getFunc('get','ById')}($this->request->get['tId']);
	
		$code_id = isset($template['code'])?$template['code']:"";
		$code="&code=".$code_id;
		
		$coupon_info = $this->model_marketing_coupon->getCouponByCode($code_id);
		
		//$this->log->write('Coupon:'.print_r($coupon_info,true));
		
		if($coupon_info){
			$content_main = html_entity_decode($descriptions[1]['description'], ENT_QUOTES, 'UTF-8');
			
			if (isset($orders)){
				$email_total = count($orders);
				$first=true;
				$order_id=$orders[0];
				
				
				$mailContent = array();
				if( $first ){
						$order_info = $this->model_sale_order->getOrder($order_id);
						$Orderid = $order_id;
						if (preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $order_info['email'])) {
							$emails[] = $order_info['email'];
							$first=false;
						}
						
					}
				
				
				//$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
				$store_info = $this->model_setting_store->getStore($store_id);
				
				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
				}
				
				$product = $this->model_catalog_product->getProduct($this->request->get['pId']);
				if (!empty($product) && is_file(DIR_IMAGE . $product['image'])) {
						$thumb = $this->model_tool_image->resize($product['image'], 320, 380);
				} else {
						$thumb = $this->model_tool_image->resize('no_image.png', 320, 380);
				}
					
					
				$mailContent['products'][]=array(
						'href' => $url->link('product/product', $code. '&product_id=' . $this->request->get['pId']),
						'name' => $product['name'],
						'thumb' => $thumb
					);
				
				$json['success']= $this->load->view('bytao/templates/campaign_order_table.tpl', $mailContent);
			}
			
		
		}else{
			$json['error'] = $this->language->get('error_code_mail');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// uzerinde calisilacak
	public function inbox(){
		
		$mail_server = "{mail.domain.com:110/pop3/notls}INBOX"; // notls sertifika error
		$mail_user = "kullaniciadi@domain.com"; // mail username
		$mail_pass = "abc123"; // mail password
		$imap = imap_open($mail_server, $mail_user, $mail_pass); // connectionz
		
		$message_count = imap_num_msg($imap); // mail count
		for ($i = 1; $i <= $message_count; ++$i) // her bir mail için
		{
		$header = imap_header($imap, $i); // mailin header bilgisini al
		$mailbox = $header->from[0]->mailbox; // @ önceki kısım örnek: kullaniciadi
		$host = $header->from[0]->host; // @ sonraki kısım örnek: domain.com
		$subject = iconv_mime_decode($header->subject, 0, "ISO-8859-9"); // konuyu iso-8859-9 şeklinde çözerek alıyoruz
		$subject = mb_convert_encoding($subject, "UTF-8", "ISO-8859-9"); // konuyu utf-8 çeviriyoruz
		$from = $mailbox."@".$host; // mailbox ve host birleştirerek gönderen emaili oluşturuyoruz. örnek: kullaniciadi@domain.com
		$message = imap_fetchbody($imap,$i,1); // maili plain text olarak çekiyoruz.
		$message = $this->trURLtoCHAR($message); // aldığımız mailde oluşan bozuk Türkçe karakterleri düzeltiyoruz
		$message = mb_convert_encoding($message, "UTF-8", "ISO-8859-9"); // maili utf-8 çeviriyoruz
		imap_delete($imap, $i); // mesajı silmek üzere işaretliyoruz
		$mail_to = "digerkullaniciadi@domain.com"; // mail gönderilecek kişi
		$mail_subject = "Konu"; // Mail konusu
		$mail_message = "Mail icerigi ve mesaj"; // mail içeriği
		$mail_header = "From: Kullanici Adi <kullaniciadi@domain.com>"; // mailin kimden gittiği
		mail($mail_to, $mail_subject, $mail_message, $mail_header); // maili gönderiyoruz
		} // for döngüsü bitti
		imap_expunge($imap); // silmek üzere işaretlediğimiz mailleri siliyoruz
		imap_close($imap); // mail bağlantısını kapatıyoruz


		
	}
	
	private function trURLtoCHAR($text) {
	$url=array( // bozuk karakterler
	"=E7","=C7",
	"=FD","=DD",
	"=FC","=DC",
	"=F6","=D6",
	"=FE","=DE",
	"=F0","=D0",
	"=20"
	);
	$char=array( 
	"ç","Ç",
	"ı","İ",
	"ü","Ü",
	"ö","Ö",
	"ş","Ş",
	"ğ","Ğ",
	"\r\n"
	);
	return str_replace($url,$char,$text); 
	}

	*/

}

