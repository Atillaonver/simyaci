<?php
namespace Opencart\Admin\Controller\Bytao;
class Popup extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/popup';
	private $C = 'popup';
	private $ID = 'popup_id';
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

		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data[$this->C.'s'] = [];

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => (($page - 1) * (int) $this->config->get('config_limit_admin')),
			'limit' => $this->config->get('config_limit_admin')
		);
		$this->load->model('tool/image');
		
		$item_total = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$thumb = '';
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$thumb = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} 
			
			$data[$this->C.'s'][] = array(
				$this->ID 		=> $result[$this->ID],
				'name'         => $result['name'],
				'thumb'         => $thumb,
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end' => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'edit'          => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
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
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
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
			if($this->model->{$this->getFunc('is','InStore')}($this->request->get[$this->ID])){
				$this->response->redirect($this->url->link($this->cPth,$this->Tkn.'=' . $this->session->data[$this->Tkn]));
			}
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 120, 120);
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = (int)$this->request->get[$this->ID];
			$itemDescription = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		}else{
			$data[$this->ID] = 0;
			$itemDescription = [];
		}
		$language_id=0;
		$data[$this->C.'_description']=[];
		if($itemDescription){
			$des=[];
			foreach($itemDescription as $key => $value){
					if($key=='language_id'){$language_id=$value;}
					if($key=='image'){
						if (is_file(DIR_IMAGE . html_entity_decode($value, ENT_QUOTES, 'UTF-8'))) {
							$des['thumb'] = $this->model_tool_image->resize(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), 120, 120);
						} else {
							$des['thumb'] = $data['placeholder'];
						}
					}
					$des[$key]=$value;
				}
			$data[$this->C.'_description'][$language_id]=$des;	
		}
		
		
		$this->load->model('customer/customer_group');
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		
		if (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}
		

		if (is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}
		
		if (!empty($item_info)) {
			$data['name'] = $item_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (!empty($item_info)) {
			$data['link'] = $item_info['link'];
		} else {
			$data['link'] = '';
		}
		
		if (!empty($item_info)) {
			$data['customer_group_id'] = $item_info['customer_group_id'];
		} else {
			$data['customer_group_id'] = '';
		}

		if (!empty($item_info)) {
			$data['popup_type'] = $item_info['popup_type'];
		} else {
			$data['popup_type'] = 0;
		}

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = TRUE;
		}
		if (!empty($item_info)) {
			$data['date_start'] = ($item_info['date_start'] != '0000-00-00' ? $item_info['date_start'] : '');
		} else {
			$data['date_start'] = date('Y-m-d', time());
		}

		if (!empty($item_info)) {
			$data['date_end'] = ($item_info['date_end'] != '0000-00-00' ? $item_info['date_end'] : '');
		} else {
			$data['date_end'] = date('Y-m-d', strtotime('+1 month'));
		}
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}
	
	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
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
					$this->model->sortOrderPages($val,$start);
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
			$group_id = $this->model->addPagesGroups($this->request->post);
			if($group_id){
				$json['group_id']=$group_id;
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
}
