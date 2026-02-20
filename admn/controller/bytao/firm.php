<?php

namespace Opencart\Admin\Controller\Bytao;
class Firm extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/firm';
	private $C = 'firm';
	private $ID = 'firm_id';
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
	
	public function index() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$url = '';
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['firm'])) {
			$url .= '&firm=' . $this->request->get['firm'];
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
		$data['delete'] = $this->url->link($this->cPth.'.delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

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

		if (isset($this->request->get['firm'])) {
			$firm = (int)$this->request->get['firm'];
		} else {
			$firm = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['firm'])) {
			$url .= '&firm=' . $this->request->get['firm'];
		}
		
		$data['ADM']= $this->user->getGroupId();
		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data[$this->C.'s'] = [];

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => (($firm - 1) * (int) $this->config->get('config_limit_admin')),
			'limit' => $this->config->get('config_limit_admin')
		);

		$item_total = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data[$this->C.'s'][] = array(
				$this->ID 		=> $result[$this->ID],
				'title'         => $result['title'],
				'sort_order'    => $result['sort_order'],
				'edit'          => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
			);
		}


		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['firm'])) {
			$url .= '&firm=' . $this->request->get['firm'];
		}

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=pd.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'firm'  => $firm,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&firm={firm}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($firm - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($firm - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($firm - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form():void {
		$this->getML('ML');
		if (isset($this->request->get[$this->ID])) {
			if(! $this->model->{$this->getFunc('is','InStore')}($this->request->get[$this->ID])){
				$this->response->redirect($this->url->link($this->cPth,$this->Tkn.'=' . $this->session->data[$this->Tkn]));
			}
		}
		
		
		$this->document->setTitle($this->language->get('heading_title'));
		//$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		//$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		
		
		$data['ADM']= $this->user->getGroupId();
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['firm'])) {
			$url .= '&firm=' . $this->request->get['firm'];
		}

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = (int)$this->request->get[$this->ID];
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			$data[$this->C.'_seo_url']=$this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
			$data[$this->C.'_store'] = $this->model->{$this->getFunc('get','Stores')}($this->request->get[$this->ID]);
		}else{
			$data[$this->ID] = 0;
			$data[$this->C.'_description'] = [];
			$data[$this->C.'_seo_url']=[];
			$data[$this->C.'_store'] = [$this->session->data['store_id']];
		}
		
		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		
		$sendData = ['control'=>$this->C];
		$data['editJS'] = $this->load->controller('bytao/editor.js',$sendData);
		$data['modals'] = $this->load->controller('bytao/editor.modal',$sendData);
		
		$data['editors']=[];
		
		
		foreach($languages as $language){
			$sendData=[
				$this->ID 		=> isset($this->request->get[$this->ID])?$this->request->get[$this->ID]:0,
				'language_id' 	=> $language['language_id'],
				'control' 		=> $this->C,
				'descriptions'	=> $data[$this->C.'_description']
				];
			$data['editors'][$language['language_id']] = $this->load->controller('bytao/editor.loadedit',$sendData);
		}
		
		
		$this->load->model('setting/store');
		$data['stores'] = [];
		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}
		
		if (!empty($item_info)) {
			$data['bottom'] = $item_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
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

			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post[$this->C.'_seo_url']) {
			$this->load->model('design/seo_url');
			foreach ($this->request->post['firm_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword);
				if ($seo_url_info && (!isset($this->request->post[$this->ID]) || $seo_url_info['key'] != $this->ID || $seo_url_info['value'] != (int)$this->request->post[$this->ID])) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
			}
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
			
			foreach ($selected as $item) {
				$this->model->{$this->getFunc('delete')}($item);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function sortorder():void {
		$json = array();
		$this->load->model($this->modelPth);
		$this->model=$this->{'model_'.str_replace('/','_',$this->modelPth)};
		
		if (isset($this->request->post['itm'])) {
			$list = $this->request->post['itm'];
			$start = isset($this->request->get['start'])?(int)$this->request->get['start']:0;
			$order = array();
	
			foreach ($list as $val) {
					$this->model->sortOrderFirms($val,$start);
					$json['order'][] = array(
					'id' 	=> $val,
					'sort' 	=> $start);
					$start++;
			}	
			
			$json['success']='done';
			
		}else{
			
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function addgroup():void {
		$json = array();
		
		if (isset($this->request->post['gname'])) {
			$this->load->model($this->modelPth);
			$this->model=$this->{'model_'.str_replace('/','_',$this->modelPth)};
			$group_id = $this->model->addFirmsGroups($this->request->post);
			if($group_id){
				$json['group_id']=$group_id;
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
}
