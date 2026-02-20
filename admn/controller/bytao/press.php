<?php

namespace Opencart\Admin\Controller\Bytao;
class Press extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/press';
	private $C = 'press';
	private $ID = 'press_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
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
	
	public function install(){
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	
	public function index():void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/bytao/css/press.css','stylesheet','screen',1);
		
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

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function list(): void {
		$this->document->addStyle('view/bytao/css/press.css','stylesheet','screen',1);
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getList():string  {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
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
		
		$data['action'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

		$data['witems'] =[];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 170, 170);
		
		
		$press_total = $this->model->{$this->getFunc('getTotal','es')}();
		$results = $this->model->{$this->getFunc('get','es')}($filter_data);
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode( $result['image'], ENT_QUOTES, 'UTF-8'))) {
					$thumb = $this->model_tool_image->resize(html_entity_decode( $result['image'], ENT_QUOTES, 'UTF-8'), 170, 170);
				} else {
					$thumb = $data['placeholder'];
				}
			
			$data['witems'][] = [
				$this->ID 			=> $result[$this->ID],
				'title'          => strip_tags($result['title']),
				'thumb'          => $thumb,
				'status'     	=> $result['status'],
				'sort_order'     	=> $result['sort_order'],
				'edit'           	=> $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
			];
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

		$data['sort_title'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

	
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $press_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($press_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($press_total - $this->config->get('config_pagination_admin'))) ? $press_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $press_total, ceil($press_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
	
		return $this->load->view($this->cPth.'_list', $data);
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['title'])) < 3) || (oc_strlen(trim($value['title'])) >255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
			
		}


		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}((int)$this->request->post[$this->ID], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->getML('ML');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function form():void {
		$this->getML('ML');
		
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css');
		$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js');
		$this->document->addStyle('view/javascript/summernote/summernote.min.css');
		$this->document->addScript('view/javascript/summernote/summernote.min.js');
		$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
		$this->document->addScript('view/javascript/summernote/mudur.js');
		
		$url = '';


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true)
		);

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		
		if (!isset($this->request->get[$this->ID])) {
			$data['action'] = $this->url->link($this->cPth.'.add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'.edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&faq_id=' . $this->request->get[$this->ID] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true);
		$data[$this->ID] =0;
		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$press_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = $this->request->get[$this->ID];
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$this->load->model('bytao/common');

		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset($press_info)) {
			$itemDescription = $this->model->{$this->getFunc('get','Description')}($this->request->get[$this->ID]);
			foreach($itemDescription as $language_id=> $item){
				
				if (is_file(DIR_IMAGE . html_entity_decode( $item['image'], ENT_QUOTES, 'UTF-8'))) {
					$thumb = $this->model_tool_image->resize(html_entity_decode( $item['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
				} else {
					$thumb = $data['placeholder'];
				}
				
				$data[$this->C.'_description'][$language_id]=[
					'title' => $item['title'],
					'meta_title' => $item['meta_title'],
					'description' => $item['description'],
					'meta_description' => $item['meta_description'],
					'meta_keyword' => $item['meta_keyword'],
					'video' => $item['video'],
					'image' => $item['image'],
					'thumb'	=> $thumb
				];
			}
			
		} else {
			$data[$this->C.'_description'] = [];
		}
		
		if (!empty($press_info)) {
			$data[$this->C.'_seo_url'] = $this->model->{$this->getFunc('get','SeoUrls')}($press_info[$this->ID]);
		} else {
			$data[$this->C.'_seo_url'] = [];
		}
		

		if (isset($press_info)) {
			$data['status'] = $press_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($press_info)) {
			$data['sort_order'] = $press_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	public function sort():void
	{
		$this->getML('M');
		$json = [];
		if (isset($this->request->post['serial'])) {
			$serials = explode('_',$this->request->post['serial']);
			foreach ($serials as $sort => $item_id) {
				if ($item_id) {
					$this->model->{$this->getFunc('update','SortOrder')}($sort,$item_id);
				}
			}
			$json['sort'] = 'Ok';
		} else {
			$json['sort'] = 'Olmadi';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
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
			if ($this->user->getGroupId()==1)
			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
			else
			$this->response->redirect($this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $this->request->get[$this->ID], 'SSL'));
			
		}else{
			if($this->model->{$this->getFunc('is','Instore')}($this->request->get[$this->ID])){
				if ($this->user->getGroupId()==1)
					$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL'));
				else
					$this->response->redirect($this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $this->request->get[$this->ID], 'SSL'));
			}
			
		}

		$this->getForm();
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		
		$data['text_col_left'] = $this->language->get('text_col_left');
		$data['text_col_right'] = $this->language->get('text_col_right');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_pslide_parent_class'] = $this->language->get('entry_pslide_parent_class');
		$data['entry_pslide_class'] = $this->language->get('entry_pslide_class');
		$data['entry_pslide_style'] = $this->language->get('entry_pslide_style');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_end'] = $this->language->get('entry_date_end');
		
		$data['entry_container'] = $this->language->get('entry_container');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_pslide_add'] = $this->language->get('button_pslide_add');
		$data['button_remove'] = $this->language->get('button_remove');
		
		$data['HTTP_IMAGE'] = HTTP_IMAGE;
		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error[$this->C.'_description'])) {
			$data['error_pslide_description'] = $this->error[$this->C.'_description'];
		} else {
			$data['error_pslide_description'] = array();
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
		
		if (isset($this->request->get[$this->ID])){
			if ($this->user->getGroupId()==1){
			$data['cancel'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
			} else{
				$data['cancel'] = $this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $this->request->get[$this->ID] . $url, 'SSL');
			}
			
		}else{
			$data['cancel'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
		}
		
		
		
		
		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		if (isset($this->request->post[$this->C.'_description'])) {
			$data[$this->C.'_description'] = $this->request->post[$this->C.'_description'];
		} elseif (!empty($item_info)) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','ProdDescriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = array();
		}
		
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->post[$this->C.'_file'])) {
			$pslide_prods = $this->request->post[$this->C.'_prod'];
		} elseif (isset($this->request->get[$this->ID])) {
			$pslide_prods = $this->model->{$this->getFunc('get','Prods')}($this->request->get[$this->ID]);
		} else {
			$pslide_prods = array();
		}

		$data[$this->C.'_prods'] = array();

		foreach ($pslide_prods as $pslide_prod) {

			if (isset( $pslide_prod['image']) && (is_file(DIR_IMAGE . $pslide_prod['image']))) {
				$mImage = $pslide_prod['image'];
				$mThumb = $pslide_prod['image'];
			} else {
				$mImage = '';
				$mThumb = 'no_image.png';
			}
			if (isset( $pslide_prod['image2']) && (is_file(DIR_IMAGE . $pslide_prod['image2']))) {
				$mImage2 = $pslide_prod['image2'];
				$mThumb2 = $pslide_prod['image2'];
			} else {
				$mImage2 = '';
				$mThumb2 = 'no_image.png';
			}
			
			$sub_files = array();
			
			$data[$this->C.'_prods'][] = array(
				$this->C.'_prod_description' => $pslide_prod[$this->C.'_prod_description'],
				'url'                     => $pslide_prod['url'],
				'position'                     => $pslide_prod['position'],
				'image'                    => $mImage,
				'image2'                    => $mImage2,
				'thumb'                    => $this->model_tool_image->resize($mThumb, 100, 100),
				'thumb2'                    => $this->model_tool_image->resize($mThumb2, 100, 100)
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->document->addStyle('view/stylesheet/tools/sort_images.css?v010');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function getwidget(){
		$json= [];

		$json['limit'] = array(1,2,3,4,6,8);
		$json['ope']='.';
		$json['view'] = $this->load->view($this->cPth.'_widget_form', $data);
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
}