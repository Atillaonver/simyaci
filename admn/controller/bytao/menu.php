<?php

namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Menu extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.1';
	public $mdata;
	private $cPth = 'bytao/menu';
	private $C = 'menu';
	private $ID = 'menu_id';
	private $Tkn = 'user_token';
	private $model ;
	private $controlls = ['pages','references','customer_sector','blog','video','news','logo','prod','art_category','firm'];
	private $menuTypes = ['url' => 'URL','category' => 'Kategori','page' => 'statik Sayfa','information' => 'Bilgi Sayfaları','product' => 'Ürün','controller' => 'Yönetim','html'  => "HTML"];
	private $storeId=0;	
	
	public function getPth():string {
		return $this->cPth;
	}
	
	private function getFunc(string $f='',string $addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
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
	
	public function install():void {
		$this->getML('ML');
		$this->model->{$this->getFunc('install')}();
	}
	
	public function index():void {
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$this->getML('ML');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], true)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] , true)
		];
		
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
		
		
		$this->document->addStyle('view/stylesheet/bytao/menu/menu.css?v9');
		$this->document->addStyle('view/javascript/jquery/jquery-ui/jquery-ui.css');
		
		$this->document->addScript('view/javascript/bytao/menu/jquerycookie.js');
		$this->document->addScript('view/javascript/jquery/jquery-ui/jquery-ui.min.js');
		$this->document->addScript('view/javascript/bytao/menu/jquery.nestable.js');
		$this->document->addScript('https://code.jquery.com/jquery-migrate-3.0.0.min.js');
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$data['maxi'] = $this->model->{$this->getFunc('getMax')}();
		$data['menu_groups'] = $this->model->{$this->getFunc('get','Groups')}();
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add_module'] = $this->language->get('button_add_module');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_import_categories'] = $this->language->get('button_import_categories');
		$data['button_delete_categories'] = $this->language->get('button_delete_categories');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
		
	}
	
	public function loadmenu():void {
		
		$json = [];
		
		$menu_id = $this->request->get['m'];
		$menuCount = $this->request->get['n'];
		$selected = isset($this->request->get['sl'])?1:0;
		
		
		$this->getML('ML');
		$this->load->model('setting/setting');
		$this->load->model('tool/image');
			
		$this->mdata['heading_title'] = $this->language->get('heading_title');
		$this->mdata['text_enabled'] = $this->language->get('text_enabled');
		$this->mdata['text_disabled'] = $this->language->get('text_disabled');
		$this->mdata['text_content_top'] = $this->language->get('text_content_top');
		$this->mdata['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->mdata['text_column_left'] = $this->language->get('text_column_left');
		$this->mdata['text_column_right'] = $this->language->get('text_column_right');
		$this->mdata['text_none'] = $this->language->get('text_none');
		

		$this->mdata['entry_banner'] = $this->language->get('entry_banner');
		$this->mdata['entry_dimension'] = $this->language->get('entry_dimension'); 
		$this->mdata['entry_layout'] = $this->language->get('entry_layout');
		$this->mdata['entry_position'] = $this->language->get('entry_position');
		$this->mdata['entry_status'] = $this->language->get('entry_status');
		$this->mdata['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->mdata['entry_image'] = $this->language->get('entry_image');
		$this->mdata['entry_pages'] = $this->language->get('entry_pages');
		$this->mdata['entry_pages_group'] = $this->language->get('entry_pages_group');
		
		
		$this->mdata['entry_menu_information'] = $this->language->get('entry_menu_information');
		$this->mdata['entry_menu_title'] = $this->language->get('entry_menu_title');
		$this->mdata['entry_description'] = $this->language->get('entry_description');
		$this->mdata['entry_description'] = $this->language->get('entry_description');
		$this->mdata['entry_menu_type'] = $this->language->get('entry_menu_type');
		$this->mdata['entry_type'] = $this->language->get('entry_type');
		$this->mdata['entry_url'] = $this->language->get('entry_url');
		$this->mdata['entry_category'] = $this->language->get('entry_category');
		$this->mdata['entry_product'] = $this->language->get('entry_product');
		$this->mdata['text_explain_input_auto'] = $this->language->get('text_explain_input_auto');
		$this->mdata['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$this->mdata['entry_information'] = $this->language->get('entry_information');
		$this->mdata['entry_html'] = $this->language->get('entry_html');
		$this->mdata['text_explain_input_html'] = $this->language->get('text_explain_input_html');
		$this->mdata['entry_menu_param'] = $this->language->get('entry_menu_param');
		$this->mdata['entry_parent_id'] = $this->language->get('entry_parent_id');
		$this->mdata['entry_image'] = $this->language->get('entry_image');
		$this->mdata['entry_menuclass'] = $this->language->get('entry_menuclass');
		$this->mdata['entry_badges'] = $this->language->get('entry_badges');
		$this->mdata['entry_showtitle'] = $this->language->get('entry_showtitle');
		$this->mdata['entry_isgroup'] = $this->language->get('entry_isgroup');
		$this->mdata['text_explain_group'] = $this->language->get('text_explain_group');
		$this->mdata['entry_iscontent'] = $this->language->get('entry_iscontent');
		$this->mdata['entry_columns'] = $this->language->get('entry_columns');
		$this->mdata['text_explain_columns'] = $this->language->get('text_explain_columns');
		$this->mdata['entry_detail_columns'] = $this->language->get('entry_detail_columns');
		$this->mdata['text_explain_submenu_cols'] = $this->language->get('text_explain_submenu_cols');
		$this->mdata['entry_sub_menutype'] = $this->language->get('entry_sub_menutype');
		$this->mdata['text_explain_submenu_type'] = $this->language->get('text_explain_submenu_type');
		$this->mdata['entry_submenu_content'] = $this->language->get('entry_submenu_content');
		$this->mdata['entry_widget_id'] = $this->language->get('entry_widget_id');
		$this->mdata['entry_publish'] = $this->language->get('entry_publish');
		$this->mdata['text_explain_input_auto'] = $this->language->get('text_explain_input_auto');
		$this->mdata['text_treemenu'] = $this->language->get('text_treemenu');
		$this->mdata['text_explain_drapanddrop'] = $this->language->get('text_explain_drapanddrop');
		$this->mdata['button_update_order'] = $this->language->get('button_update_order');
		$this->mdata['text_new'] = $this->language->get('text_new');
		
		
		$this->mdata['button_save'] = $this->language->get('button_save');
		$this->mdata['button_cancel'] = $this->language->get('button_cancel');
		$this->mdata['button_add_module'] = $this->language->get('button_add_module');
		$this->mdata['button_remove'] = $this->language->get('button_remove');
		$this->mdata['button_import_categories'] = $this->language->get('button_import_categories');
		$this->mdata['button_delete_categories'] = $this->language->get('button_delete_categories');
		$this->mdata['user_token'] = $this->session->data['user_token'];
	
		//$this->load->model('bytao/widget');
		$this->mdata['widgets'] = [];//$this->model_bytao_widget->getWidgets();
		//get current language id
		//$this->log->write('Array:'.print_r($this->session->data,TRUE));
		//$this->mdata['language_id'] = $this->session->data['language_id'];		
		$this->mdata['modules'] = [];
		
		$this->mdata['yesno'] = ['0' => $this->language->get('text_no'),'1'=> $this->language->get('text_yes')];
		$this->mdata['controllers'] =$this->controlls;
			
		if(isset($this->request->post['menu_module'])){
			$this->mdata['modules'] = $this->request->post['menu_module'];
		} elseif($this->config->get('menu_module')){ 
			$this->mdata['modules'] = $this->config->get('menu_module');
		}
		
		$tmp = ['layout_id'=>'','position'=>'','status'=>'','sort_order'=>''];				
		if( count($this->mdata['modules']) ){
			$tmp = array_merge($tmp, $this->mdata['modules'][0] );
		}
		$this->mdata['module'] = $tmp;
		$this->load->model('design/layout');
		
		$this->mdata['currentID'] = 0 ;
		
		$this->mdata['n'] = $this->request->get['n'];
		$json['maxi'] = $this->model->{$this->getFunc('getMax')}();
		
		$this->load->model('bytao/common');
		$languages = $this->model_bytao_common->getStoreLanguages();
		$this->mdata['languages'] = $languages;
		$this->mdata['HTTPS_IMAGE'] = URL_IMAGE;
		$this->mdata['menutypes'] = $this->menuTypes;
		$this->mdata['submenutypes'] = ['menu'=>'Menu', 'html'=>'HTML' ];
		$this->mdata['menus'] = $this->model->{$this->getFunc('get','Dropdown')}(0, $menu_id,1 );
		
		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		//$this->info();
		$this->mdata['layouts'] = [];
		$this->mdata['layouts'][] = ['layout_id'=>99999, 'name' => $this->language->get('all_page')];		
		$this->mdata['layouts'] = array_merge($this->mdata['layouts'],$this->model_design_layout->getLayouts());
		$this->mdata['menu_id'] = $menu_id;
		
		$this->model->{$this->getFunc('empty','Children')}();
		$this->mdata['tree'] = $this->model->{$this->getFunc('get','Tree')}(null, 1 ,$menu_group_id );

		$json['view'] = $this->load->view($this->cPth.'_new', $this->mdata);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function loadmenugroup():void {
		
		$json = [];
		$this->getML('ML');
		$menu_group_id = isset($this->request->get['t'])?$this->request->get['t']:1;
		
		$this->load->model('setting/setting');
		$this->load->model('tool/image');
		$this->load->model('bytao/common');
		$this->load->model('design/layout');
		$tmp = ['layout_id'=>'','position'=>'','status'=>'','sort_order'=>''];			
		$languages = $this->model_bytao_common->getStoreLanguages();
		$json['maxi'] = $this->model->{$this->getFunc('getMax')}();
		$this->mdata['controllers'] =$this->controlls;
		$this->mdata['user_token'] = $this->session->data['user_token'];
		
		$this->mdata['widgets'] = [];//$this->model_bytao_widget->getWidgets();
		$this->mdata['modules'] = [];
		$this->mdata['yesno'] = [ '0' => $this->language->get('text_no'),'1'=> $this->language->get('text_yes') ];
		
		if(isset($this->request->post['menu_module'])){
			$this->mdata['modules'] = $this->request->post['menu_module'];
		} elseif($this->config->get('menu_module')){ 
			$this->mdata['modules'] = $this->config->get('menu_module');
		}
				
		if( count($this->mdata['modules']) ){
			$tmp = array_merge($tmp, $this->mdata['modules'][0] );
		}
		$this->mdata['show_title'] = 1;
		$this->mdata['published'] = 1;
		$this->mdata['colums'] = 1;
		$this->mdata['module'] = $tmp;
		$this->mdata['currentID'] = 0 ;
		$this->mdata['languages'] = $languages;
		$this->mdata['HTTPS_IMAGE'] = URL_IMAGE;
		$this->mdata['menutypes'] = $this->menuTypes;
		$this->mdata['submenutypes'] = ['menu'=>'Menu', 'html'=>'HTML' ];
		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$this->mdata['layouts'] = [];
		$this->mdata['layouts'][] = ['layout_id'=>99999, 'name' => $this->language->get('all_page')];		
		$this->mdata['layouts'] = array_merge($this->mdata['layouts'],$this->model_design_layout->getLayouts());
		$this->mdata['menu_id'] = 0;
		$this->mdata['menu_group_id'] = $menu_group_id;
		$this->mdata['menus'] = $this->model->{$this->getFunc('get','Dropdown')}(0, 0,$menu_group_id );
		
		$this->mdata['tree'] = $this->model->{$this->getFunc('get','Tree')}(1 ,  0 ,$menu_group_id);
		
		$this->load->model('catalog/information');
		$this->load->model('bytao/page');
		
		$this->mdata['informations'] = $this->model_catalog_information->getInformations();
		$this->mdata['pages'] = $this->model_bytao_page->getPages([]);
		
		//$this->model->{$this->getFunc('empty','Children')}();
		//$groups = $this->model->{$this->getFunc('get','GroupData')}($menu_group_id);
		
		$json['tree'] = $this->load->view($this->cPth.'_tree', $this->mdata);
		$json['form'] = $this->load->view($this->cPth.'_form', $this->mdata);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getMenuGroupOrder():void{
		$json = [];
		$this->getML('M');
		
		$json['menu_group_id'] = $this->model->{$this->getFunc('get','GroupId')}();
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	
	
	
	
	
	
	public function info():string {
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
			'parent_class'=>'',
			'submenu_colum_width'=>'',
			'is_group'=>'',
			'submenu_width'=>'12',
			'column_width'=>'200',
			'submenu_column_width'=>'',
			'colums'=>'1',
			'type' => '',
			'login' => '0',
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
			'menu-controller'=>'',
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
		
		$this->mdata['user_token'] = $this->session->data['user_token'];
		$this->mdata['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->mdata['informations'] = $this->model_catalog_information->getInformations();
		$this->mdata['pages'] = $this->model_bytao_page->getAllPages();
		$this->mdata['allpagesgroup'] = [];//$this->model_remsan_pages_group->getPagesGroups();
		$this->mdata['yesno'] = ['0' => $this->language->get('text_no'),'1'=> $this->language->get('text_yes')];
		$this->mdata['controllers'] =$this->controlls;
		/*
		TODO kontollerin cekilecegi yer simdilik bytao
		*/
		
		
		$menu = $this->model->{$this->getFunc('get','Info')}($id);
		
		$menu = array_merge( $default, $menu );
		
		
		$this->mdata['mId'] = $id;  
		$this->mdata['menus'] = $this->model->{$this->getFunc('get','Dropdown')}(null, $menu['parent_id'], $store_id );
		$this->mdata['thumb'] = $this->model_tool_image->resize($menu['image'], 32, 32);
		$this->mdata['menu_description'] = [];
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
			if( isset($this->request->get['id']) ){
				$menu_id = $this->request->get['id'];
				$this->model->{$this->getFunc('delete')}($menu_id,$this->storeId);
				
				$json['id'] = $menu_id;
				$json['success']="OK";
			}
			if( isset($this->request->get['mid']) ){
				$menu_id = $this->request->get['mid'];
				$menu_group_id = $this->request->get['mgid'];
				$this->model->{$this->getFunc('delete')}($menu_id,$this->storeId);
				
				$json['mid'] = $menu_id;
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
			$this->model->{$this->getFunc('mass','Update')}($data, $root );
			$json['parent'] = $this->model->{$this->getFunc('get','Dropdown')}(null,1,0,1 );
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validate():bool {
		$this->getML('L');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if(isset($this->request->post['taomenu_module'])){ 
	
			foreach($this->request->post['pavmegamenu_module'] as $key => $value){
				if(!$value['position'] || !$value['layout_id']){ 
					$this->error['dimension'][$key] = $this->language->get('error_dimension');
				}				
			}
			$languageId = (int)$this->session->data['store_language_id'];
			$d = isset($this->request->post['taomenu_description'][$languageId]['title'])?$this->request->post['taomenu_description'][$languageId]['title']:"";
			if( empty($d) ){  
				$this->error['missing_title'][]=$this->language->get('error_missing_title');
			}
			foreach( $this->request->post['taomenu_description'] as $key => $value){
				if( empty($value['title']) ){ 
					$this->request->post['taomenu_description'][$key]['title'] = $d; 
				}
				
			}
			if( isset($this->error['missing_title']) ){
				$this->error['warning'] = implode( "<br>", $this->error['missing_title'] );
			}
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
				 
			}else{ 
				$id = 0;
				$menu = $this->request->post['menu'];
               
				$menu_param = isset($menu['menu_id'])?'&menu_id='.$menu['menu_id']:'';
				$menu_group_id = isset($menu['menu_group_id'])?$menu['menu_group_id']:1;

				if( $this->validate() ){
					switch($this->request->post['menu']['type']){
						case 'controller':
							$this->request->post['menu']['item'] = $this->request->post['menu-controller'];
							break;
						case 'page':
							$this->request->post['menu']['item'] = $this->request->post['menu-page'];
							break;
							
					}
					
					$json['menuId'] = $this->model->{$this->getFunc('edit','Data')}( $this->request->post );	
				}
				
				
				$json['success'] = $this->language->get('text_success');
				$json['tree'] = $this->model->{$this->getFunc('get','Tree')}('', 0 ,$menu_group_id );
				$json['parent'] = $this->model->{$this->getFunc('get','Dropdown')}('',1,$menu_group_id );
				$json['maxi'] = $this->model->{$this->getFunc('getMax')}();
				/*
				if($this->request->post['save_mode']=='delete-categories'){
				$this->model_bytao_taomenu->deletecategories($this->session->data['store_id'],$taomenu['menu_id']);
				}
				
				if($this->request->post['save_mode']=='import-categories'){
				$this->model_bytao_taomenu->importCategories($this->session->data['store_id'],$taomenu['menu_id']);
				}
				*/
				
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
			$this->load->model('setting/setting');
			$this->load->model('tool/image');
			
			$default = [
				'menu_group_id'=>'',
				'menu_id'=>'',
				'title' => '',
				'parent_id'=> '',
				'image' => '',
				'is_group'=>'',
				'width'=>'12',
				'menu_class'=>'',
				'parent_class'=>'',
				'submenu_colum_width'=>'',
				'is_group'=>'',
				'submenu_width'=>'12',
				'column_width'=>'200',
				'submenu_column_width'=>'',
				'colums'=>'1',
				'login' => '0',
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
				'target' => '',
				'level'=> '',
				'content_text'=>'',
				'submenu_content'=>'',
				'menu-information'=>'',
				'menu-static_page'=>'',
				'menu-pages'=>'',
				'menu-pages-group'=>'',
				'menu-product'=>'',
				'menu-category'=>'',
				'menu-controller'=>'',
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
			$json['no_image'] = $this->model_tool_image->resize('no_image.jpg', 16, 16);
			$languages = $this->model_bytao_common->getStoreLanguages();
			$json['HTTPS_IMAGE'] = URL_IMAGE;
			
			$menu = $this->model->{$this->getFunc('get','Info')}( $id );
			$menu = array_merge( $default, $menu );
		
			
			$json['mId'] = $id;  
			$json['menus'] = $this->model->{$this->getFunc('get','Dropdown')}(0, $menu['parent_id'],$menu['menu_group_id']);
			
			$json['thumb'] = $this->model_tool_image->resize($menu['image'], 32, 32);
			$json['menu_description'] = [];
			$descriptions  = $this->model->{$this->getFunc('get','Description')}( $id );
			
			$json['menutypes'] = $this->menuTypes;
			
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
					case 'controller':
						$menu['menu-controller'] = $menu['item'];
						break;
					case 'information':
						$menu['menu-information'] = $menu['item'] ;
						break;
					case 'page':
						$menu['menu-page'] = $menu['item'] ;
						break;
					case 'pages-group':
						$menu['menu-page-group'] = $menu['item'] ;
						break;
					case 'manufacturer':
						$manufacturer = $this->model_catalog_manufacturer->getManufacturer( $menu['item'] );
						$menu['menu-manufacturer'] = isset($manufacturer['name'])?$manufacturer['name']:"";
						break;
				}
			}
			
			if( $menu['type']=='url' ){
				
				$mUrls = explode(',',$menu['url']);
				$mItm=[];
				foreach($mUrls as $mUrl){
					$mUrl = explode('æ',$mUrl);
					$mItm[$mUrl[0]] = isset($mUrl[1])?$mUrl[1]:'';
				}
				
				foreach($languages as $language){
					if(isset($mItm[$language['language_id']])){
						$menu['urlLang'.$language['language_id']] = $mItm[$language['language_id']];
					}else{
						$menu['urlLang'.$language['language_id']] =' ';
					}
				}
			}
			
			foreach( $descriptions as $d ){
				$json['menu_description'][$d['language_id']] = $d;
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
		$this->mdata['heading_title'] = $this->language->get('heading_title');
		$this->mdata['heading_title'] = $this->language->get('heading_title');
		$this->mdata['text_enabled'] = $this->language->get('text_enabled');
		$this->mdata['text_disabled'] = $this->language->get('text_disabled');
		$this->mdata['text_content_top'] = $this->language->get('text_content_top');
		$this->mdata['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->mdata['text_column_left'] = $this->language->get('text_column_left');
		$this->mdata['text_column_right'] = $this->language->get('text_column_right');
		$this->mdata['text_none'] = $this->language->get('text_none');
		

		$this->mdata['entry_banner'] = $this->language->get('entry_banner');
		$this->mdata['entry_dimension'] = $this->language->get('entry_dimension'); 
		$this->mdata['entry_layout'] = $this->language->get('entry_layout');
		$this->mdata['entry_position'] = $this->language->get('entry_position');
		$this->mdata['entry_status'] = $this->language->get('entry_status');
		$this->mdata['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->mdata['entry_image'] = $this->language->get('entry_image');
		$this->mdata['entry_pages'] = $this->language->get('entry_pages');
		$this->mdata['entry_pages_group'] = $this->language->get('entry_pages_group');
		
		
		$this->mdata['entry_menu_information'] = $this->language->get('entry_menu_information');
		$this->mdata['entry_menu_title'] = $this->language->get('entry_menu_title');
		$this->mdata['entry_description'] = $this->language->get('entry_description');
		$this->mdata['entry_description'] = $this->language->get('entry_description');
		$this->mdata['entry_menu_type'] = $this->language->get('entry_menu_type');
		$this->mdata['entry_type'] = $this->language->get('entry_type');
		$this->mdata['entry_url'] = $this->language->get('entry_url');
		$this->mdata['entry_category'] = $this->language->get('entry_category');
		$this->mdata['entry_product'] = $this->language->get('entry_product');
		$this->mdata['text_explain_input_auto'] = $this->language->get('text_explain_input_auto');
		$this->mdata['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$this->mdata['entry_information'] = $this->language->get('entry_information');
		$this->mdata['entry_html'] = $this->language->get('entry_html');
		$this->mdata['text_explain_input_html'] = $this->language->get('text_explain_input_html');
		$this->mdata['entry_menu_param'] = $this->language->get('entry_menu_param');
		$this->mdata['entry_parent_id'] = $this->language->get('entry_parent_id');
		$this->mdata['entry_image'] = $this->language->get('entry_image');
		$this->mdata['entry_menuclass'] = $this->language->get('entry_menuclass');
		$this->mdata['entry_parent_class'] = $this->language->get('entry_parent_class');
		$this->mdata['entry_badges'] = $this->language->get('entry_badges');
		$this->mdata['entry_showtitle'] = $this->language->get('entry_showtitle');
		$this->mdata['entry_isgroup'] = $this->language->get('entry_isgroup');
		$this->mdata['text_explain_group'] = $this->language->get('text_explain_group');
		$this->mdata['entry_iscontent'] = $this->language->get('entry_iscontent');
		$this->mdata['entry_columns'] = $this->language->get('entry_columns');
		$this->mdata['text_explain_columns'] = $this->language->get('text_explain_columns');
		$this->mdata['entry_detail_columns'] = $this->language->get('entry_detail_columns');
		$this->mdata['text_explain_submenu_cols'] = $this->language->get('text_explain_submenu_cols');
		$this->mdata['entry_sub_menutype'] = $this->language->get('entry_sub_menutype');
		$this->mdata['text_explain_submenu_type'] = $this->language->get('text_explain_submenu_type');
		$this->mdata['entry_submenu_content'] = $this->language->get('entry_submenu_content');
		$this->mdata['entry_widget_id'] = $this->language->get('entry_widget_id');
		$this->mdata['entry_publish'] = $this->language->get('entry_publish');
		$this->mdata['text_explain_input_auto'] = $this->language->get('text_explain_input_auto');
		$this->mdata['text_treemenu'] = $this->language->get('text_treemenu');
		$this->mdata['text_explain_drapanddrop'] = $this->language->get('text_explain_drapanddrop');
		
		$this->mdata['button_update_order'] = $this->language->get('button_update_order');
		$this->mdata['button_save'] = $this->language->get('button_save');
		$this->mdata['button_cancel'] = $this->language->get('button_cancel');
		$this->mdata['button_add_module'] = $this->language->get('button_add_module');
		$this->mdata['button_remove'] = $this->language->get('button_remove');
		$this->mdata['button_import_categories'] = $this->language->get('button_import_categories');
		$this->mdata['button_delete_categories'] = $this->language->get('button_delete_categories');
		
		$this->mdata['user_token'] = $this->session->data['user_token'];
		//$this->load->model('bytao/widget');
		$this->mdata['widgets'] = '';//$this->model_bytao_widget->getWidgets();
		//get current language id
		//$this->mdata['language_id'] = $this->session->data['store_language_id'];		
		$this->mdata['menutypes'] = $this->menuTypes;
				
			
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
		$this->mdata['HTTPS_IMAGE'] = URL_IMAGE;
		
		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			
		$this->info();
		$this->mdata['layouts'] = [];
		$this->mdata['layouts'][] = ['layout_id'=>99999, 'name' => $this->language->get('all_page') ];		
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

		$this->load->model('bytao/common');
		$languages = $this->model_bytao_common->getStoreLanguages();
		$this->mdata['languages'] = $languages;



		$this->mdata['action'] = $this->url->link('bytao/taomenu/addwidget', 'token=' . $this->session->data['token'], 'SSL'); 
		$this->model_bytao_widget->getForm( 'html' );

  
		$this->children = [
			'common/header',
			'common/footer'
		];
		echo $this->render();
	}
	
	public function updatetree():void {
		$json = [];
		$this->getML('ML');
		$data = $this->request->post['list'];
		$root = $this->request->get['menu_group_id'];
	
		if (($this->request->server['REQUEST_METHOD'] == 'POST')  && !empty($this->request->post) ) {
			$this->model->{$this->getFunc('mass','Update')}( $data, 1  );
			
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}
