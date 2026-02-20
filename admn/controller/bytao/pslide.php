<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Pslide extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/pslide';
	private $C = 'pslide';
	private $ID = 'pslide_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	private function getFunc($f='',$addi=''): string{
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''): void{
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
	
	public function install(): void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->index();
	}
	
	public function index(): void {
		$this->getML('ML');

		$this->document->setTitle($this->language->get('heading_title'));

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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function list(): void {
		$this->response->setOutput($this->getList());
	}
	
	protected function getList(): string {
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

		$data['action'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		$data['pslides'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * (int)$this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		${$this->C.'_total'} = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data[$this->C.'s'][] = array(
				$this->ID => $result[$this->ID],
				'title'      => $result['title'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'sort_order'     => $result['sort_order'],
				'edit'      => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url)
			);
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

		$data['sort_title'] = $this->url->link($this->cPth.'|list', 'user_token=' . $this->session->data['user_token'] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'|list', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => ${$this->C.'_total'},
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), (${$this->C.'_total'}) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > (${$this->C.'_total'} - $this->config->get('config_pagination_admin'))) ? ${$this->C.'_total'} : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), ${$this->C.'_total'}, ceil(${$this->C.'_total'} / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		return $this->load->view($this->cPth.'_list', $data);
	}
	
	public function form(): void {
		
		$data['HTTP_IMAGE'] = HTTP_IMAGE;
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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url)
		];

		
		$data['save'] = $this->url->link($this->cPth.'|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url);
		
		
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			
		}

		

		if (isset($this->request->post[$this->C.'_description'])) {
			$data[$this->C.'_description'] = $this->request->post[$this->C.'_description'];
		} elseif (!empty($item_info)) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','ProdDescriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = [];
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
			$pslide_prods = [];
		}

		$data[$this->C.'_prods'] = [];

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
			
			$sub_files = [];
			
			$data[$this->C.'_prods'][] = [
				$this->C.'_prod_description' => $pslide_prod[$this->C.'_prod_description'],
				'url'                     => $pslide_prod['url'],
				'position'                     => $pslide_prod['position'],
				'image'                    => $mImage,
				'image2'                    => $mImage2,
				'thumb'                    => $this->model_tool_image->resize($mThumb, 100, 100),
				'thumb2'                    => $this->model_tool_image->resize($mThumb2, 100, 100)
			];
		}
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->document->addStyle('view/stylesheet/tools/sort_images.css?v010');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
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

	public function delete() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as ${$this->ID}) {
				$this->model->{$this->getFunc('delete')}(${$this->ID});
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

	protected function getList_() {
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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL')
		);

		$data['add'] = $this->url->link($this->cPth.'/add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');
		$data['delete'] = $this->url->link($this->cPth.'/delete', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, 'SSL');

		$data['pslides'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		${$this->C.'_total'} = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data[$this->C.'s'][] = array(
				$this->ID => $result[$this->ID],
				'title'      => $result['title'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'/edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL')
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
		$pagination->total = ${$this->C.'_total'};
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), (${$this->C.'_total'}) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > (${$this->C.'_total'} - $this->config->get('config_limit_admin'))) ? ${$this->C.'_total'} : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), ${$this->C.'_total'}, ceil(${$this->C.'_total'} / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_list', $data));
	}

	public function form_(): void {
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
/*
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}


		if (isset($this->request->post[$this->C.'_image'])) {
			foreach ($this->request->post[$this->C.'_image'] as $pslide_image_id => $pslide_image) {
				foreach ($pslide_image[$this->C.'_image_description'] as $language_id => $pslide_image_description) {
					if ((utf8_strlen($pslide_image_description['title']) < 2) || (utf8_strlen($pslide_image_description['title']) > 64)) {
						$this->error[$this->C.'_image'][$pslide_image_id][$language_id] = $this->language->get('error_title');
					}
				}
			}
		}
*/
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}




	public function widget():void {
		$this->getML('ML');
		
		$json= array();
		
		$filter_data = array(
			'sort'  => '',
			'order' => '',
			'start' => '',
			'limit' => ''
		);
		
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			if($result['status']){
				$json['items'][] = array(
					'item_id' 	=> $result[$this->ID],
					'title'     => $result['title'],
				);
			}
		}
		$json['ope']='.';
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}