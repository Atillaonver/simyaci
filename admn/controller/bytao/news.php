<?php
namespace Opencart\Admin\Controller\Bytao;
class News extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/news';
	private $C = 'news';
	private $ID = 'news_id';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void{
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function install():void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	
	public function index():void {
		
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		
		
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
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
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

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->document->addStyle('view/bytao/css/news.css?v=11');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getList(): string  {
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'n.sort_order';
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
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
		}
		
		
		$data['action'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn]. $url);
		
		$data['Items'] = [];
		$start = $data['start']=(($page - 1) * (int) $this->config->get('config_pagination_admin'));
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => $start,
			'limit' => $this->config->get('config_pagination_admin')
		];

		$item_total = $this->model->{$this->getFunc('getTotal','es')}();

		$results = $this->model->{$this->getFunc('get','es')}($filter_data);
		$this->load->model('tool/image');
		$placeholder = $this->model_tool_image->resize('no_image.png', 160, 160);

		foreach ($results as $result) {
			$image = $this->model->{$this->getFunc('get','Image')}($result['news_id'],1);
			if (isset($image['image']) && is_file(DIR_IMAGE . html_entity_decode($image['image'], ENT_QUOTES, 'UTF-8'))) {
				$thumb = $this->model_tool_image->resize(html_entity_decode($image['image'], ENT_QUOTES, 'UTF-8'), 160, 160);
			} else {
				$thumb = $placeholder;
			}
			
			
			$data['Items'][] = [
				$this->ID 		=> $result[$this->ID],
				'image'         => $thumb,
				'title'         => $result['title'],
				'sort_order'    => $result['sort_order'],
				'edit'          => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
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

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=nd.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=n.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$data['pagination'] = $this->load->controller('common/pagination',[
			'total' => $item_total,
			'ajax' => $this->C,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		if($filter_all){
			$data['results'] = sprintf($this->language->get('text_pagination_all'), $item_total);
		}else{
			$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		}
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
		
	}
	
	public function form():void {
		$this->getML('ML');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		
		
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
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . $this->request->get['filter_title'];
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
		

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		}

		if (isset($this->request->get[$this->ID])) {
			$data[$this->ID] = (int)$this->request->get[$this->ID];
		} else {
			$data[$this->ID] = 0;
		}
		
		if (!empty($item_info)) {
			$data['image'] = $item_info['image'];
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

		//$this->load->model('localisation/language');
		//$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		

		if (isset($this->request->get[$this->ID])) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = [];
		}
		
		
		if (!empty($item_info)) {
			$data[$this->C.'_seo_url'] = $this->model->{$this->getFunc('get','SeoUrls')}($item_info[$this->ID]);
		} else {
			$data[$this->C.'_seo_url'] = [];
		}
		

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['status'] = true;
			$data['sort_order'] = 0;
		}
		
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		
		$data['store_id'] = $this->session->data['store_id'];
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
		
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((strlen(trim($value['title'])) < 1) || (strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}
			/*
			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
			*/
			
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
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
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	function sort():void {
    	$this->getML('M');
    	$json = [];
    	if(isset($this->request->post['serial'])){
			$serials = explode('_',$this->request->post['serial']);
			foreach($serials as $sort => $item_id){
				if($item_id){
					$this->model->{$this->getFunc('update','SortOrder')}($sort,$item_id);
				}
			}
			$json['sort'] = 'Ok';
		}else{
			$json['sort'] = 'Olmadi';
		}
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
	public function copy() {
		$this->getML('ML');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $item_id) {
				$this->model->{$this->getFunc('copy')}($item_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getlist();
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