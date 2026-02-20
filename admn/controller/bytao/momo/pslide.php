<?php
class ControllerBytaoPslide extends Controller {
	private $error = array();
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/pslide';
	private $C = 'pslide';
	private $ID = 'pslide_id';
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
		$this->load->language($this->langPth);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->modelPth);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_momo_pslide->addPslide($this->request->post);

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

			$this->response->redirect($this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language($this->langPth);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->modelPth);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_momo_pslide->editPslide($this->request->get['pslide_id'], $this->request->post);

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
			$this->response->redirect($this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL'));
			else
			$this->response->redirect($this->url->link($this->modelPth.'/edit', 'token=' . $this->session->data['token'] . '&pslide_id=' . $this->request->get['pslide_id'], 'SSL'));
			
		}else{
			if($this->model_momo_pslide->isInstore($this->request->get['pslide_id'])){
				if ($this->user->getGroupId()==1)
					$this->response->redirect($this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL'));
				else
					$this->response->redirect($this->url->link($this->modelPth.'/edit', 'token=' . $this->session->data['token'] . '&pslide_id=' . $this->request->get['pslide_id'], 'SSL'));
			}
			
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language($this->langPth);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->modelPth);

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $pslide_id) {
				$this->model_momo_pslide->deletePslide($pslide_id);
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

			$this->response->redirect($this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		$data['add'] = $this->url->link($this->modelPth.'/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['delete'] = $this->url->link($this->modelPth.'/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$data['pslides'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$pslide_total = $this->model_momo_pslide->getTotalPslides();

		$results = $this->model_momo_pslide->getPslides($filter_data);

		foreach ($results as $result) {
			$data['pslides'][] = array(
				'pslide_id' => $result['pslide_id'],
				'title'      => $result['title'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->modelPth.'/edit', 'token=' . $this->session->data['token'] . '&pslide_id=' . $result['pslide_id'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_title'] = $this->language->get('column_title');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

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

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $pslide_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pslide_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($pslide_total - $this->config->get('config_limit_admin'))) ? $pslide_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $pslide_total, ceil($pslide_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('momo/pslide_list.tpl', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['pslide_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');

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

		$data['button_add'] = $this->language->get('button_add');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_pslide_add'] = $this->language->get('button_pslide_add');
		$data['button_remove'] = $this->language->get('button_remove');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['pslide_description'])) {
			$data['error_pslide_description'] = $this->error['pslide_description'];
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
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL')
		);

		if (!isset($this->request->get['pslide_id'])) {
			$data['action'] = $this->url->link($this->modelPth.'/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link($this->modelPth.'/edit', 'token=' . $this->session->data['token'] . '&pslide_id=' . $this->request->get['pslide_id'] . $url, 'SSL');
		}
		
		if (isset($this->request->get['pslide_id'])){
			if ($this->user->getGroupId()==1){
			$data['cancel'] = $this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL');
			} else{
				$data['cancel'] = $this->url->link($this->modelPth.'/edit', 'token=' . $this->session->data['token'] . '&pslide_id=' . $this->request->get['pslide_id'] . $url, 'SSL');
			}
			
		}else{
			$data['cancel'] = $this->url->link($this->modelPth, 'token=' . $this->session->data['token'] . $url, 'SSL');
		}
		
		
		
		
		if (isset($this->request->get['pslide_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$pslide_info = $this->model_momo_pslide->getPslide($this->request->get['pslide_id']);
			
		}

		$data['token'] = $this->session->data['token'];

		if (isset($this->request->post['pslide_description'])) {
			$data['pslide_description'] = $this->request->post['pslide_description'];
		} elseif (!empty($pslide_info)) {
			$data['pslide_description'] = $this->model_momo_pslide->getPslideProdDescriptions($this->request->get['pslide_id']);
		} else {
			$data['pslide_description'] = array();
		}
		
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($pslide_info)) {
			$data['status'] = $pslide_info['status'];
		} else {
			$data['status'] = true;
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->post['pslide_file'])) {
			$pslide_prods = $this->request->post['pslide_prod'];
		} elseif (isset($this->request->get['pslide_id'])) {
			$pslide_prods = $this->model_momo_pslide->getPslideProds($this->request->get['pslide_id']);
		} else {
			$pslide_prods = array();
		}

		$data['pslide_prods'] = array();

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
			
			$data['pslide_prods'][] = array(
				'pslide_prod_description' => $pslide_prod['pslide_prod_description'],
				'url'                     => $pslide_prod['url'],
				'image'                    => $mImage,
				'image2'                    => $mImage2,
				'thumb'                    => $this->model_tool_image->resize($mThumb, 100, 100),
				'thumb2'                    => $this->model_tool_image->resize($mThumb2, 100, 100)
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->document->addStyle('view/stylesheet/tools/sort_files.css?v010');
		//$this->document->addScript('view/bytao/editor.js?ver=45');

		$data['header'] = $this->load->controller('common/header');
		$data['tabstores'] = $this->load->controller('common/stores');  
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('momo/pslide_form.tpl', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->modelPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
/*
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}


		if (isset($this->request->post['pslide_image'])) {
			foreach ($this->request->post['pslide_image'] as $pslide_image_id => $pslide_image) {
				foreach ($pslide_image['pslide_image_description'] as $language_id => $pslide_image_description) {
					if ((utf8_strlen($pslide_image_description['title']) < 2) || (utf8_strlen($pslide_image_description['title']) > 64)) {
						$this->error['pslide_image'][$pslide_image_id][$language_id] = $this->language->get('error_title');
					}
				}
			}
		}
*/
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->modelPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	
	
}