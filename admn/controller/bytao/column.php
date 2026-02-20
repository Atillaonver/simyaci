<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Column extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/column';
	private $C = 'column';
	private $ID = 'column_id';
	private $Tkn = 'user_token';
	private $model ;
	
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

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

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

		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['items'] = [];
		
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
			$data['items'][] = [
				'item_id' => $result[$this->ID],
				'title'      => $result['name'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL')
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
		
		$this->document->addStyle('view/bytao/css/column.css?v5');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
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
		}
		
		$data[$this->ID] = isset($this->request->get[$this->ID])?$this->request->get[$this->ID]:0;

		if (isset($item_info['name'])) {
			$data['name'] = $item_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($item_info['width'])) {
			$data['width'] = $item_info['width'];
		} else {
			$data['width'] = 100;
		}
		
		if (isset($item_info['height'])) {
			$data['height'] = $item_info['height'];
		} else {
			$data['height'] = 100;
		}

		if (isset($item_info['status'])) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->get[$this->ID])) {
			$banner_images = $this->model->{$this->getFunc('get','Images')}($this->request->get[$this->ID]);
		} else {
			$banner_images = [];
		}
		
		$data['banner_images'] = [];

		foreach ($banner_images as $banner_image) {
			
			if (is_file(DIR_IMAGE . $banner_image['image'])) {
				$image = $banner_image['image'];
				$thumb = $banner_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}
			
			if (isset( $banner_image['mobile_image']) && (is_file(DIR_IMAGE . $banner_image['mobile_image']))) {
				$mImage = $banner_image['mobile_image'];
				$mThumb = $banner_image['mobile_image'];
			} else {
				$mImage = '';
				$mThumb = 'no_image.png';
			}
			
			$sub_banner = [];
			
			$subImages =$this->model->{$this->getFunc('getSub','Images')}($this->request->get[$this->ID]); 
			
			foreach( $subImages AS $sub){
				
				if (is_file(DIR_IMAGE . $subImage['image'])) {
					$simage = $subImage['image'];
					$sthumb = $subImage['image'];
				} else {
					$simage = '';
					$sthumb = 'no_image.png';
				}
				
				$sub_banner[] = [
				'description' 				=> $subImage['description'],
				'banner_parent_class'      => $subImage['banner_parent_class'],
				'banner_style'             => $subImage['banner_style'],
				'banner_class'             => $subImage['banner_class'],
				'banner_type'             => $subImage['banner_type'],
				'position'                 => $subImage['position'],
				'link'                     => $subImage['link'],
				'image'                    => $simage,
				'thumb'                    => $this->model_tool_image->resize($sthumb, 150, 150),
				];
				
			}
			
			
			$data['banner_images'][] = [
				'description' 			   => $banner_image['description'],
				'banner_parent_class'      => $banner_image['banner_parent_class'],
				'banner_style'             => $banner_image['banner_style'],
				'link'                     => $banner_image['link'],
				'image'                    => $image,
				'thumb'                    => $this->model_tool_image->resize($thumb, 150, 150),
				'mobile_image'             => $mImage,
				'mobile_thumb'             => $this->model_tool_image->resize($mThumb, 150, 150),
				'sort_order'               => $banner_image['sort_order'],
				'subs'					   => $sub_banner
			];
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 150, 150);
		
		
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

		/*
		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((strlen(trim($value['title'])) < 1) || (strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}

			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}
		*/

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
    
    public function clearcache(){
		$json = [];
		$this->load->model('bytao/common');
		$this->model_bytao_common->cacheClear('home');
		
		$json['result']='Cache Deleted';
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	public function getwidget(array $ndata = []) {
		$this->getML('ML');
		
		$json= [];
		
		if( isset($ndata[1])){
			$data['parts'] = $ndata;
		}
		
		$filter_data = [
			'sort'  => '',
			'order' => '',
			'start' => '',
			'limit' => ''
		];
		
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			if($result['status']){
				$data['items'][] = [
					'item_id' 	=> $result[$this->ID],
					'title'     => $result['name'],
				];
			}
		}
		$json['view'] = $this->load->view($this->cPth.'_widget_form', $data);
		
		
		if( isset($ndata[1])){
			return $json['view'];
		}else{
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	
	}
	
}