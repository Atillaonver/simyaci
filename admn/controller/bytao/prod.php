<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Prod extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/prod';
	private $C = 'prod';
	private $ID = 'prod_id';
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
	
	public function index() {
		$this->getML('ML');
		$this->load->model('bytao/common');
		
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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();
		$data['user'] = $this->user->getGroupId();
		if($data['user']==1){
			$data['setting'] = $this->getSetting();	
		}
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getSetting() {
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		$data['fsetting'] = $this->url->link($this->cPth.'|setform', 'user_token=' . $this->session->data['user_token']);	
		$data['prodsetting'] =	$this->config->get('ctrl_prod_opt');
		return $this->load->view($this->cPth.'_setting', $data);
	}
	
	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.title';
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

		$data['action'] = $this->url->link($this->cPth.'|list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data[$this->C.'s'] = [];
		
		$start = $data['start']=(($page - 1) * (int) $this->config->get('config_limit_admin'));
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => $start,
			'limit' => $this->config->get('config_limit_admin')
		];
		
		$item_total = $this->model->{$this->getFunc('getTotal','s')}();
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data[$this->C.'s'][] = array(
				$this->ID => $result[$this->ID],
				'title'      => $result['title'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL')
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
		
		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=ad.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=a.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
	}
	
	public function form() {
		$this->getML('ML');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');
		//$data['HTTP_IMAGE'] = HTTP_IMAGE;
		
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');

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

		$data['save'] = $this->url->link($this->cPth.'|save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID]=$this->request->get[$this->ID];
		}

		if (isset($this->request->post[$this->C.'_description'])) {
			$data[$this->C.'_description'] = $this->request->post[$this->C.'_description'];
		} elseif (!empty($item_info)) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = array();
		}
		
		$data['prod_opts'] =	$this->config->get('ctrl_prod_opt');
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (isset($this->request->post['url'])) {
			$data['url'] = $this->request->post['url'];
		} elseif (!empty($item_info)) {
			$data['url'] = $item_info['url'];
		} else {
			$data['url'] = '';
		}
		
		if (isset($this->request->post['ref'])) {
			$data['ref'] = $this->request->post['ref'];
		} elseif (!empty($item_info)) {
			$data['ref'] = $item_info['ref'];
		} else {
			$data['ref'] = '';
		}
		
		if (isset($this->request->post['prod_cat_id'])) {
			$data['prod_cat_id'] = $this->request->post['prod_cat_id'];
			$data['cat_path'] = $this->request->post['cat_path'];
		} elseif (!empty($item_info)) {
			$data['prod_cat_id'] = $item_info['prod_cat_id'];
			$data['cat_path'] = $this->model->{$this->getFunc('get','CatPath')}($item_info['prod_cat_id']);
		} else {
			$data['prod_cat_id'] = 0;
		}
		
		
		
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		$this->load->model('tool/image');
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}
		
		if (isset( $data['image']) && is_file(DIR_IMAGE .  $data['image'])) {
			$data['thumb']= $this->model_tool_image->resize( $data['image'], 100, 100);
		} else{
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		if (isset($this->request->post['bimage'])) {
			$data['bimage'] = $this->request->post['bimage'];
		} elseif (!empty($item_info)) {
			$data['bimage'] = $item_info['bimage'];
		} else {
			$data['bimage'] = '';
		}
		
		if (isset( $data['bimage']) && is_file(DIR_IMAGE .  $data['bimage'])) {
			$data['bthumb']= $this->model_tool_image->resize( $data['bimage'], 100, 100);
		} else{
			$data['bthumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		
		$data['prod_seo_url'] = [];
		$store_id = $data['store_id'] = (int)$this->session->data['store_id'] ;

		if (isset($this->request->get[$this->ID])) {
			$data['prod_seo_url'] = $this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
		}
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}
	
	public function save(): void {
		$json = [];
		$this->getML('ML');

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((strlen(trim($value['title'])) < 1) || (strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}

			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!isset($this->request->post[$this->ID]) || $this->request->post[$this->ID]=='0' ) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}($this->request->post[$this->ID], $this->request->post);
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
			
			foreach ($selected as $item) {
				$this->model->{$this->getFunc('delete')}($item);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function sort():void {
    	$this->getML('M');
    	$json = [];
    	if(isset($this->request->post['serial'])){
			$serials = explode('_',$this->request->post['serial']);
			$start = (int)$this->request->post['start'];
			foreach($serials as $sort => $item_id){
				if($item_id){
					$this->model->{$this->getFunc('sort')}($item_id,$sort+$start);
				}
			}
			$json['sort'] = 'Ok';
		}else{
			$json['sort'] = 'Olmadi';
		}
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function setform(){
		$json= [];
		$this->getML('ML');

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}
		
		
		if (!$json) {
			$this->load->model('bytao/common');
			$this->model_bytao_common->setSetting('ctrl_prod', $this->request->post);
			//$this->model->{$this->getFunc('edit','Setting')}('ctrl_prod', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function loadCats(){
		$json= [];
		$this->getML('ML');
		
		
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
    public function widget(){
		$json= [];

		foreach ([1,2,3,4,6,8] as $result) {
				$json['items'][] = array(
					'item_id' 	=> $result,
					'title'     => $result,
				);
		}
		$json['ope']='.';
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}