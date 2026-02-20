<?php
namespace Opencart\Admin\Controller\Bytao;
class Faq extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/faq';
	private $C = 'faq';
	private $ID = 'faq_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	public function getPth(): string {
		return $this->cPth;
	}
	
	private function getFunc($f='',$addi=''): string {
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
		$this->model->{$this->getFunc('install')}();
		$this->document->setTitle($this->language->get('heading_title'));
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
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getList():string  {
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
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

		$data['faqs'] =[];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$faq_total = $this->model->{$this->getFunc('getTotal','s')}();
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data['faqs'][] = [
				$this->ID 			=> $result[$this->ID],
				'question'          => strip_tags($result['question']),
				'sort_order'     	=> $result['sort_order'],
				'edit'           	=> $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&faq_id=' . $result[$this->ID] . $url, true)
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

		$data['sort_title'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

	
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $faq_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($faq_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($faq_total - $this->config->get('config_pagination_admin'))) ? $faq_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $faq_total, ceil($faq_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
	
		return $this->load->view($this->cPth.'_list', $data);
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['faq_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['question'])) < 3) || (oc_strlen(trim($value['question'])) >255)) {
				$this->error['question'][$language_id] = $this->language->get('error_question');
			}
			
			if (oc_strlen(trim($value['ansver'])) < 3) {
				$this->error['ansver'][$language_id] = $this->language->get('error_ansver');
			}
		}


		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}((int)$this->request->post[$this->ID], $this->request->post);
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

	public function form():void {
		$this->getML('ML');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true)
		);

		$data['save'] = $this->url->link($this->cPth.'|save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		
		if (!isset($this->request->get[$this->ID])) {
			$data['action'] = $this->url->link($this->cPth.'.add', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'.edit', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&faq_id=' . $this->request->get[$this->ID] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url, true);
		$data[$this->ID] =0;
		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$faq_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = $this->request->get[$this->ID];
		}

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$this->load->model('bytao/common');

		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset($faq_info)) {
			$data['faq_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data['faq_description'] = [];
		}
		
		
		

		if (isset($faq_info)) {
			$data['status'] = $faq_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($faq_info)) {
			$data['sort_order'] = $faq_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css');
		$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js');
		$this->document->addStyle('view/javascript/summernote/summernote.min.css');
		$this->document->addScript('view/javascript/summernote/summernote.min.js');
		$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
		$this->document->addScript('view/javascript/summernote/mudur.js');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
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

}