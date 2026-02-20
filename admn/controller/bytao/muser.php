<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Muser extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.1';
	public $mdata;
	private $cPth = 'bytao/muser';
	private $C = 'muser';
	private $ID = 'user_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	public function getPth():string {
		return $this->cPth;
	}
	
	private function getFunc(string $f='',string $addi=''):string {
		//return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
		return $f.$addi;
	}
	
	private function getML(string $ML=''):void {
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id']=$this->storeId;
		}else{
			$this->storeId = $this->session->data['store_id'];
		}
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
		}
	}
	
	public function install():string{
		$this->getML('ML');
		$this->model->{$this->getFunc('installMuser')}();
		$this->getList();
	}
	
	public function index():void {
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

		$data['add'] = $this->url->link($this->cPth.'|add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
		
	}
	
	public function list(): void {
		$this->getML('ML');

		$this->response->setOutput($this->getList());
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

		$data['action'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);


		$data['users'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * (int) $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$user_total = $this->model->{$this->getFunc('getTotalUsers')}();

		$results = $this->model->{$this->getFunc('getUsers')}($filter_data);

		foreach ($results as $result) {
			$data['users'][] = [
				$this->ID    => $result[$this->ID],
				'username'   => $result['username'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'menu'       => $this->url->link($this->cPth.'|menu', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&user_id=' . $result[$this->ID] . $url, true),
				'edit'       => $this->url->link($this->cPth.'|add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&user_id=' . $result[$this->ID] . $url, true)
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

		$data['sort_name'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn]  . '&sort=name' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'|list',$this->Tkn.'=' . $this->session->data[$this->Tkn]  . '&sort=sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $user_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn]  . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($user_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($user_total - $this->config->get('config_pagination_admin'))) ? $user_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $user_total, ceil($user_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view($this->cPth.'_list', $data);
	}
	
	public function form(): string {
		$this->getML('ML');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$user_info = $this->model->{$this->getFunc('getUser')}($this->request->get[$this->ID]);
		}

		if (isset($this->request->get['user_id'])) {
			$data['user_id'] = (int)$this->request->get['user_id'];
		} else {
			$data['user_id'] = 0;
		}

		if (!empty($user_info)) {
			$data['username'] = $user_info['username'];
		} else {
			$data['username'] = '';
		}

		$this->load->model('user/user_group');

		$data['user_groups'] = $this->model_user_user_group->getUserGroups();

		if (!empty($user_info)) {
			$data['user_group_id'] = $user_info['user_group_id'];
		} else {
			$data['user_group_id'] = 0;
		}

		if (!empty($user_info)) {
			$data['firstname'] = $user_info['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (!empty($user_info)) {
			$data['lastname'] = $user_info['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (!empty($user_info)) {
			$data['email'] = $user_info['email'];
		} else {
			$data['email'] = '';
		}

		if (!empty($user_info)) {
			$data['image'] = $user_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		if (!empty($user_info)) {
			$data['status'] = $user_info['status'];
		} else {
			$data['status'] = 0;
		}
		
		
		$data['save'] = $this->url->link($this->cPth.'|saveuser', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		
		return $this->load->view($this->cPth.'_form', $data);
	}
	
	public function saveuser(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ((oc_strlen($this->request->post['username']) < 3) || (oc_strlen($this->request->post['username']) > 20)) {
			$json['error']['username'] = $this->language->get('error_username');
		}

		$this->load->model('user/user');

		$user_info = $this->model_user_user->getUserByUsername($this->request->post['username']);

		if (!$this->request->post['user_id']) {
			if ($user_info) {
				$json['error']['warning'] = $this->language->get('error_username_exists');
			}
		} else {
			if ($user_info && ($this->request->post['user_id'] != $user_info['user_id'])) {
				$json['error']['warning'] = $this->language->get('error_username_exists');
			}
		}

		if ((oc_strlen($this->request->post['firstname']) < 1) || (oc_strlen($this->request->post['firstname']) > 32)) {
			$json['error']['firstname'] = $this->language->get('error_firstname');
		}

		if ((oc_strlen($this->request->post['lastname']) < 1) || (oc_strlen($this->request->post['lastname']) > 32)) {
			$json['error']['lastname'] = $this->language->get('error_lastname');
		}

		if ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		$user_info = $this->model_user_user->getUserByEmail($this->request->post['email']);

		if (!$this->request->post['user_id']) {
			if ($user_info) {
				$json['error']['warning'] = $this->language->get('error_email_exists');
			}
		} else {
			if ($user_info && ($this->request->post['user_id'] != $user_info['user_id'])) {
				$json['error']['warning'] = $this->language->get('error_email_exists');
			}
		}

		if ($this->request->post['password'] || (!isset($this->request->post['user_id']))) {
			if ((oc_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (oc_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
				$json['error']['password'] = $this->language->get('error_password');
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$json['error']['confirm'] = $this->language->get('error_confirm');
			}
		}

		if (!$json) {
			if (!$this->request->post['user_id']) {
				$json['user_id'] = $this->model->{$this->getFunc('addUser')}($this->request->post);
				
			} else {
				$this->model->{$this->getFunc('editUser')}($this->request->post['user_id'],$this->request->post);
			}
			$json['list'] = $this->getList();
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}





	public function add() {
		$this->getML('ML');
		$json = [];
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->post[$this->ID])&&$this->request->post[$this->ID]>0){
				$this->model->{$this->getFunc('editUser')}($this->request->post);
			}else{
				$this->model->{$this->getFunc('addUser')}($this->request->post);
			}
			$json['success']= $this->language->get('text_success');
		}else{
			$json['userForm'] = $this->form();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((oc_strlen($this->request->post['username']) < 3) || (oc_strlen($this->request->post['username']) > 20)) {
			$this->error['username'] = $this->language->get('error_username');
		}

		$user_info = $this->model->{$this->getFunc('getUserByUsername')}($this->request->post['username']);

		if (!isset($this->request->get[$this->ID])) {
			if ($user_info) {
				$this->error['warning'] = $this->language->get('error_exists_username');
			}
		} else {
			if ($user_info && ($this->request->get[$this->ID] != $user_info[$this->ID])) {
				$this->error['warning'] = $this->language->get('error_exists_username');
			}
		}

		if ((oc_strlen(trim($this->request->post['firstname'])) < 1) || (oc_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((oc_strlen(trim($this->request->post['lastname'])) < 1) || (oc_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		$user_info = $this->model->{$this->getFunc('getUserByEmail')}($this->request->post['email']);

		if (!isset($this->request->get[$this->ID])) {
			if ($user_info) {
				$this->error['warning'] = $this->language->get('error_exists_email');
			}
		} else {
			if ($user_info && ($this->request->get[$this->ID] != $user_info[$this->ID])) {
				$this->error['warning'] = $this->language->get('error_exists_email');
			}
		}

		if ($this->request->post['password'] || (!isset($this->request->get[$this->ID]))) {
			if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
				$this->error['password'] = $this->language->get('error_password');
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$this->error['confirm'] = $this->language->get('error_confirm');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $user_id) {
			if ($this->user->getId() == $user_id) {
				$this->error['warning'] = $this->language->get('error_account');
			}
		}

		return !$this->error;
	}
	
	public function menu():void {
		$this->getML('ML');
		$json = [];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['user_id'] = $user_id = isset($this->request->get['user_id'])?$this->request->get['user_id']:0;
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		$data['ctrls'] = $this->getCtrl();
		
		
		
		if(isset($this->session->data['copied'])){
			$data['copied']=$this->session->data['copied'];
		}
		
		$data['HTTPS_IMAGE'] = URL_IMAGE;
		$data['menus'] = $this->model->{$this->getFunc('getMenuDropdown')}(0,0,$user_id);
		$data['tree'] = $this->model->{$this->getFunc('getMenuTree')}(null, 0 ,$user_id );
		
		$json['menuForm'] = $this->load->view($this->cPth.'_tree', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function copied():void {
		$json = [];
		$this->getML('L');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}else{
			$user_id = $this->request->get['uid'];
			$this->session->data['copied'] = $user_id;
			$json['user_id'] = $user_id;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function paste():void {
		$json = [];
		$this->getML('ML');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}else{
			$user_id = $this->request->get['uid'];
			if($this->session->data['copied'] <> $user_id){
				$this->model->{$this->getFunc('pasteMenuTree')}($user_id , $this->session->data['copied']);	
				$json['tree'] = $this->model->{$this->getFunc('getMenuTree')}(null, 0 ,$user_id );
				$json['parent'] = $this->model->{$this->getFunc('getMenuDropdown')}('',1,$user_id  );
				unset($this->session->data['copied']);
				
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
	public function info():void {
		$id=0;
		//$this->getML('ML');
		
		if( isset($this->request->post) && isset($this->request->post['id']) ){
			$id = (int)$this->request->post['id'] ;
		}else if( isset($this->request->get["id"]) ){
			$id = (int)$this->request->get['id'];
		}
		if(isset($this->request->get['store_id'])){
			$store_id = $this->request->get['store_id'];
			$store_param = "&store_id=".$store_id;
		} else{
			$store_id = 0;
			$store_param = "";
		}
		
		if(isset($this->request->get['menu_id'])){
			$menu_id = $this->request->get['menu_id'];
			$menu_param = "&menu_id=".$menu_id;
		} else{
			$menu_id = 0;
			$menu_param = "";
		}
		
		$default = [
			'menu_id'=>'',
			'title' => '',
			'parent_id'=> '',
			'image' => '',
			'is_group'=>'',
			'width'=>'12',
			'menu_class'=>'',
			'submenu_colum_width'=>'',
			'is_group'=>'',
			'submenu_width'=>'12',
			'column_width'=>'200',
			'submenu_column_width'=>'',
			'colums'=>'1',
			'type' => '',
			'item' => '',
			'is_content'=>'',
			'show_title'=>'1',
			'type_submenu'=>'',
			'level_depth'=>'',
			'status'    => '',
			'position'  => '',
			'show_sub' => '',
			'url' => '',
			'targer' => '',
			'level'=> '',
			'content_text'=>'',
			'submenu_content'=>'',
			'menu-information'=>'',
			'menu-static_page'=>'',
			'menu-pages'=>'',
			'menu-pages-group'=>'',
			'menu-product'=>'',
			'menu-category'=>'',
			'published' => 1,
			'menu-manufacturer'=>'',
			'widget_id'=> 0,
			'badges' =>''
		];
		
		
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/information');
		$this->load->model('bytao/page');
		$this->load->model('bytao/common');
		$this->load->model('tool/image');
		
		$this->mdata['no_image'] = $this->model_tool_image->resize('no_image.jpg', 16, 16);
	
		$this->mdata['entry_image'] = 'Image:';
		$this->mdata['text_image_manager'] = $this->language->get('text_image_manager');
		$this->mdata['entry_menu_information'] = $this->language->get('entry_menu_information');
		
		
		$this->mdata['text_clear'] = $this->language->get('text_clear');		
		$this->mdata['text_browse'] = $this->language->get('text_browse');
		$this->mdata['tab_module'] = $this->language->get('tab_module');
		$this->mdata['text_none'] = $this->language->get('text_none');
		
		$this->mdata[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->mdata['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->mdata['informations'] = $this->model_catalog_information->getInformations();
		$this->mdata['pages'] = $this->model_bytao_page->getAllPages();
		$this->mdata['allpagesgroup'] = array();//$this->model_remsan_pages_group->getPagesGroups();
		$this->mdata['yesno'] = array( '0' => $this->language->get('text_no'),'1'=> $this->language->get('text_yes') );
		$this->mdata['controllers'] =$this->controlls;
		/*
		TODO kontollerin cekilecegi yer simdilik bytao
		*/
		
		
		$menu = $this->model->{$this->getFunc('get','Info')}($id);
		$menu = array_merge( $default, $menu );
		
		
		$this->mdata['mId'] = $id;  
		$this->mdata['menus'] = $this->model->{$this->getFunc('get','Dropdown')}(null, $menu['parent_id'], $store_id );
		$this->mdata['thumb'] = $this->model_tool_image->resize($menu['image'], 32, 32);
		$this->mdata['menu_description'] = array();
		$descriptions  = $this->model->{$this->getFunc('get','Description')}( $id );
		
		
		$this->mdata['menutypes'] = $this->menuTypes;
		
		if( $menu['item'] ){
			switch( $menu['type'] ){
				case 'category':
				$category = $this->model_catalog_category->getCategory( $menu['item'] );
				$menu['menu-category'] = isset($category['name'])?$category['name']:"";
					
				break;
				case 'product':
				$product = $this->model_catalog_product->getProduct( $menu['item'] );
				$menu['menu-product'] = isset($product['name'])?$product['name']:"";
				break;
				case 'information':
				$menu['menu-information'] = $menu['item'] ;
				break;
				case 'pages':
				$menu['menu-page'] = $menu['item'] ;
				break;
				case 'page_group':
				$menu['menu-page-group'] = $menu['item'] ;
				break;
				case 'conroller':
				$menu['menu-controller'] = $menu['item'] ;
				break;
				case 'manufacturer':
				$manufacturer = $this->model_catalog_manufacturer->getManufacturer( $menu['item'] );
				$menu['menu-manufacturer'] = isset($manufacturer['name'])?$manufacturer['name']:"";
				break;					
			}
		}
		
		foreach( $descriptions as $d ){
			$this->mdata['menu_description'][$d['language_id']] = $d;
		}

		if( empty($this->mdata['menu_description']) ){
			foreach(  $this->mdata['languages'] as $language ){
				$this->mdata['menu_description'][$language['language_id']]['title'] = '';
				$this->mdata['menu_description'][$language['language_id']]['description'] = '';
			}
		}
		
		if( isset($this->request->post['menu']) ){
			$menu = array_merge($menu, $this->request->post['menu'] );
			
		}
		
		$this->mdata['by_menu'] = $menu;

		
		
		$this->mdata['controllers'] =$this->controlls;
		$this->mdata['submenutypes'] = ['menu'=>'Menu', 'html'=>'HTML' ];
		$this->mdata['text_edit_menu'] = $this->language->get('text_edit_menu');
		$this->mdata['text_create_new'] = $this->language->get('text_create_new');
		$this->response->setOutput($this->load->view($this->cPth.'_form', $this->mdata));

	}
 	
	public function deletemenu():void {
		$json = [];
		
		if(!$this->user->hasPermission('modify',  $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
			die(  $this->error['warning'] );
		}else{
			$this->getML('ML');
			$uid = isset($this->request->get['uid'])?$this->request->get['uid']:0;
			if( isset($this->request->get['id']) ){
				$menu_id = $this->request->get['id'];
				$this->model->{$this->getFunc('deleteMenu')}($menu_id,$uid);
				
				$json['id'] = $menu_id;
				$json['success']="OK";
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function update():void {
		$json = [];
		$this->getML('ML');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}else{
			$data = $this->request->post['list'];
			$root = $this->request->get['root'];
			$this->model->{$this->getFunc('massMenuUpdate')}($data, $root );
			$json['parent'] = $this->model->{$this->getFunc('getDropdown')}(null,1,0,1 );
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function hidemenu():void {
		$json = [];
		$this->getML('ML');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}else{
			$Gid = explode('id_',$this->request->get['mid']);
			$id = $Gid[1];
			$hide = $this->request->get['val'];
			
			$this->model->{$this->getFunc('hideMenuUpdate')}($id, $hide );
			$json['success']= $hide;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validate():bool {
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if(!$this->error){
			return true;
		} else{
			return false;
		}	
	}
	
	public function addnewmenu():void {
		
		$json = [];
		$this->getML('ML');
		
		if(($this->request->server['REQUEST_METHOD'] == 'POST')  && !empty($this->request->post) ){
			if(!$this->user->hasPermission('modify', $this->cPth)){
				$json['warning'] = $this->language->get('error_permission');
			}
			else
			{ 
				$menu = $this->request->post['menu'];
				if( $this->validate() ){
					$user_id = isset($menu['user_id'])?$menu['user_id']:0;
					$json['menuId'] = $this->model->{$this->getFunc('editMenuData')}( $this->request->post );	
					$json['success'] = $this->language->get('text_success');
					$json['tree'] = $this->model->{$this->getFunc('getMenuTree')}(null, 0 ,$user_id );
					$json['parent'] = $this->model->{$this->getFunc('getMenuDropdown')}('',1,$user_id  );
				}
				
				
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
				
	}
	
	public function getmenu():void {
		$json = [];
		$this->getML('ML');
		
		$Gid = explode('id_',$this->request->get['mid']);
		$id = $Gid[1];
		if($Gid){
			
			$default = [
				'menu_id'=>'',
				'user_id'=>'',
				'parent_id'=> '',
				'item' => '',
				'store_id' => '',
				'menu_class'=>'',
				'level'=> '',
				'url' => '',
			];
			$this->load->model('bytao/common');
			$languages = $this->model_bytao_common->getStoreLanguages();
			$json['HTTPS_IMAGE'] = URL_IMAGE;
			
			$menu = $this->model->{$this->getFunc('getMenuInfo')}( $id );
			$menu = array_merge( $default, $menu );
		
			
			$json['mId'] = $id;  
			$json['menus'] = $this->model->{$this->getFunc('getMenuDropdown')}(0, $menu['parent_id'],$menu['user_id']);
			
			$json['menu_user_description'] = [];
			$descriptions  = $this->model->{$this->getFunc('getMenuDescription')}( $id );
			
			foreach( $descriptions as $d ){
				$json['menu_user_description'][$d['language_id']] = $d;
			}
			$json['menu'] = $menu;
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function newmenu():void {
		$json = [];
		$this->getML('ML');
		$this->load->model('setting/setting');
		$this->load->model('tool/image');
		$this->mdata[$this->Tkn] = $this->session->data[$this->Tkn];
		//$this->load->model('bytao/widget');
		$this->mdata['widgets'] = '';//$this->model_bytao_widget->getWidgets();
		//get current language id
		//$this->mdata['language_id'] = $this->session->data['store_language_id'];		
		$this->mdata['megamenutypes'] = $this->menuTypes;
				
			
		$this->mdata['modules'] = [];
			
		if(isset($this->request->post['pavmegamenu_module'])){
			$this->mdata['modules'] = $this->request->post['pavmegamenu_module'];
		} elseif($this->config->get('pavmegamenu_module')){ 
			$this->mdata['modules'] = $this->config->get('pavmegamenu_module');
		}
			
		$tmp = ['layout_id'=>'','position'=>'','status'=>'','sort_order'=>''];				
		if( count($this->mdata['modules']) ){
			$tmp = array_merge($tmp, $this->mdata['modules'][0] );
		}
		$this->mdata['module'] = $tmp;
		$this->load->model('design/layout');
			
		$this->mdata['currentID'] = 0 ;
			
		$this->mdata['n'] = $this->request->get['n'];
			

			
		
		$this->mdata['menu_id'] =  $json['maxi'] = $this->model->{$this->getFunc('getMax')}();
		
		$this->load->model('bytao/common');
		$languages = $this->model_bytao_common->getStoreLanguages();
		$this->mdata['languages'] = $languages;
		$this->mdata['HTTPS_IMAGE'] = HTTPS_IMAGE;
		
		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			
		$this->info();
		$this->mdata['layouts'] = array();
		$this->mdata['layouts'][] = array('layout_id'=>99999, 'name' => $this->language->get('all_page') );		
		$this->mdata['layouts'] = array_merge($this->mdata['layouts'],$this->model_design_layout->getLayouts());

			
		$json['view'] = $this->load->view($this->cPth.'_new', $this->mdata);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function addwidget():void {
		$this->getML('ML');
		$template = 'bytao/taomenu/widget_form.tpl';
		$this->document->addStyle( 'view/stylesheet/taomenu/widget.css');
		
		$this->mdata['heading_title'] = $this->language->get('heading_widget_title');

		$this->load->model('setting/setting');
		$this->load->model( 'bytao/widget' );

		$model = $this->model_bytao_widget; 

		$this->mdata['types'] = $model->getTypes();

		$disabled  		 = false;
		$form 	  		 = '';
		$widget_selected = '';
		$id 			 = 0;
 
		if( isset($this->request->get['id']) && ($id=$this->request->get['id']) ){ 
			$id = (int)$this->request->get['id'];  
		}	


		if( isset($this->request->post['widget']) && isset($this->request->post['params']) ){
			$this->request->post['widget']['params'] = $this->request->post['params'];
			$row = $model->saveData( $this->request->post['widget'] );
			$this->redirect( $this->url->link('bytao/taomenu/addwidget', 'done=1&id='.$row['id'].'&wtype='.$row['type'].'&token=' . $this->session->data['token'], 'SSL') ); 
		}

		$data = $model->getWidetById( $id );

		if( $data['id'] ){
			$disabled = true;
		}

		if( isset($this->request->get['wtype']) ){
			$widget_selected =  trim(strtolower($this->request->get['wtype']));	
			$form = $model->getForm( $widget_selected, $data['params'] );
		}
		$this->mdata['widget_data'] = $data;

		if( isset($this->request->get['done']) ){
			$this->mdata['message'] = $this->language->get('message_update_data_done');
		}
		$this->mdata['id'] 		 = $id;
		$this->mdata['form'] 	 = $form;
		$this->mdata['disabled']  = $disabled; 
		$this->mdata['widget_selected'] = $widget_selected;

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getStoreLanguages();
		$this->mdata['languages'] = $languages;



		$this->mdata['action'] = $this->url->link('bytao/taomenu/addwidget', 'token=' . $this->session->data['token'], 'SSL'); 
		$this->model_bytao_widget->getForm( 'html' );

  
		$this->children = array(
			'common/header',
			'common/footer'
		);
		echo $this->render();
	}
	
	public function updatetree():void {
		$json = [];
		$this->getML('ML');
		$id = $this->request->get['uid'];
		if($id){
			$data = $this->request->post['list'];
			if (($this->request->server['REQUEST_METHOD'] == 'POST')  && !empty($this->request->post) ) {
				$this->model->{$this->getFunc('massMenuUpdate')}( $data );
				
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getCtrl(string $dir='',int $level = 1):array {
		$fileData = [];
		
		$directory = DIR_APPLICATION . 'controller/'.$dir;
		$files = glob($directory.'*');
		
		if ($files) {
			foreach ($files as $file) {
				if(is_dir($file)){
					$dr = str_replace(DIR_APPLICATION. 'controller/','',$file);
					$sub = $this->getCtrl($dr.'/',2);
					if(count($sub)){
						$fileData[$dr] = $sub;
					}
				} elseif (is_file($file)) {
					$ext = pathinfo($file, PATHINFO_EXTENSION);
					if($ext=='php'){
						$fileData[] = basename($file, '.php');
					}
				}				
			}
		}
		
		if($level ==1){
			$fileData['Extensions'] = $this->getExtens();
		}
		
		return $fileData;
	}
	
	public function getExtens(string $dir=''):array {
		$fileData = [];
		
		$directory = DIR_EXTENSION . 'opencart/admin/controller/'.$dir;
		$files = glob($directory.'*');
		
		if ($files) {
			foreach ($files as $file) {
				if(is_dir($file)){
					$dr = str_replace(DIR_EXTENSION . 'opencart/admin/controller/','',$file);
					$sub = $this->getExtens($dr.'/');
					if(count($sub)){
						$fileData['extension/opencart/'.$dr] = $sub;
					}
					
				} elseif (is_file($file)) {
					$ext = pathinfo($file, PATHINFO_EXTENSION);
					if($ext=='php'){
						$fileData[] = basename($file, '.php');
					}
				}				
			}
		}
		return $fileData;
	}
}
