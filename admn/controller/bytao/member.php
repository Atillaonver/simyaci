<?php
namespace Opencart\Admin\Controller\Bytao;
class Member extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/member';
	private $C = 'member';
	private $ID = 'member_id';
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

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

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
	
	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}

		$data['action'] = $this->url->link($this->cPth.'|list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data[$this->C.'s'] = [];
		$filter_data = [];
		
		if($filter_all){
			$filter_data = [
				'filter_name'   =>$filter_name,
				'filter_all'   =>$filter_all,
				'filter_status'   =>$filter_status,
				'sort'  => 'a2c.sort_order',
				'order' => $order
				];
		}else{
			$filter_data = [
				'filter_name'   =>$filter_name,
				'filter_all'   =>$filter_all,
				'filter_status'   =>$filter_status,
				'sort'  => 	$sort,
				'order' => 	$order,
				'start' => ($page - 1) * (int)$this->config->get('config_pagination_admin'),
				'limit' => $this->config->get('config_pagination_admin')
			];
		}
		
		
		$item_total = $this->model->{$this->getFunc('getTotal','s')}($filter_data);
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		$this->load->model('tool/image');
		$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		foreach ($results as $result) {
			$thumb = $placeholder;
			if (isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])) {
				$thumb = $this->model_tool_image->resize( $result['image'], 100, 100);
			} 
			
			$data[$this->C.'s'][] = array(
				$this->ID => $result[$this->ID],
				'title'      => $result['title'],
				'thumb'      => $thumb,
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'=' . $result[$this->ID]  . $url)
				
			);
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
		$data['HTTP_IMAGE'] = URL_IMAGE;
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTPS_IMAGE;
		
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
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = $this->request->get[$this->ID];
		}

		if (!empty($item_info)) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = array();
		}
		
		
		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (!empty($item_info)) {
			$data['url'] = $item_info['url'];
		} else {
			$data['url'] = '';
		}
		
		if (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		$this->load->model('tool/image');
		
		if (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}
		
		if (isset( $data['image']) && is_file(DIR_IMAGE .  $data['image'])) {
			$data['thumb']= $this->model_tool_image->resize( $data['image'], 100, 100);
		} else{
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (!empty($item_info)) {
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
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$json[$this->ID] = $this->model->{$this->getFunc('edit')}($this->request->post[$this->ID], $this->request->post);
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
			foreach($serials as $sort => $item_id){
				if($item_id){
					$this->model->{$this->getFunc('sort')}($item_id,$sort);
				}
			}
			$json['sort'] = 'Ok';
		}else{
			$json['sort'] = 'Olmadi';
		}
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
    
    public function getwidget(array $ndata = []){
		$json= [];
		$this->getML('L');
		foreach ([1,2,3,4,6,8] as $result) {
				$json['items'][] = [
					'item_id' 	=> $result,
					'title'     => $result,
				];
		}
		$data['cdata'] = $ndata;
		$json['ope']='.';
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$json['view'] = $this->load->view($this->cPth.'_widget_form', $data);
		
		
		if( isset($ndata[1])){
			return $json['view'];
		}else{
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
	
}