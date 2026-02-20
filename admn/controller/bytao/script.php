<?php
namespace Opencart\Admin\Controller\Bytao;
class Script extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $Tkn = 'user_token';
	private $cPth = 'bytao/script';
	private $C = 'script';
	private $ID = 'layout_id';
	private $model ;
	
	public function getPth():string 
	{
		return $this->cPth;
	}
	
	private function getFunc($f='',$addi=''):string 
	{
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void
	{
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
			
		}
	}
	
	public function install():void
	{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
	}
	
	public function index(): void 
	{
		$this->getML('ML');
		$this->load->model('design/layout');
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['layout_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		
		$data['text_head'] = $this->language->get('text_head');
		$data['text_body_header'] = $this->language->get('text_body_header');
		$data['text_body_footer'] = $this->language->get('text_body_footer');
		$data['text_delete_confirm'] = $this->language->get('text_delete_confirm');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		

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

		
		$data['action'] = $this->url->link($this->cPth.'.edit', $this->Tkn.'='.$this->session->data[$this->Tkn], 'SSL');
		$data['cancel'] = $this->url->link('common/dashboard', $this->Tkn.'='.$this->session->data[$this->Tkn] . $url, 'SSL');
		$data['save'] = $this->url->link('bytao/script.save', $this->Tkn.'='.$this->session->data[$this->Tkn] . $url, 'SSL');
		
		$data['Layouts'] = array();
		
		$filter_data = array(
		
		);
		$data['Scripts'] = array();
		$data['all'] =2;
		$results = $this->model->{$this->getFunc('get','s')}();
		foreach ($results as $result) {
			if($result['layout_id']==0){$data['all'] =1;}
			
			$data['Scripts'][$result['layout_id']] = array(
				'layout_id' => $result['layout_id'],
				'head'      => html_entity_decode($result['head'], ENT_QUOTES, 'UTF-8'),
				'body_header'      => html_entity_decode($result['body_header'], ENT_QUOTES, 'UTF-8'),
				'body_footer'      => html_entity_decode($result['body_footer'], ENT_QUOTES, 'UTF-8'),
				'position'      => html_entity_decode($result['position'], ENT_QUOTES, 'UTF-8'),
			);
		}
		

		$results = $this->model_design_layout->getLayouts($filter_data);
		foreach ($results as $result) {
			$data['Layouts'][$result['layout_id']] = array(
				'layout_id' => $result['layout_id'],
				'name'      => $result['name'],
				'isop'      => isset($data['Scripts'][$result['layout_id']])?1:0
			);
		}
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		
		$this->response->setOutput($this->load->view($this->cPth, $data));
		
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$key = $this->request->post['layout_id'];
			
			$json['id'] = $this->model->{$this->getFunc('edit')}($key,$this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			if (isset($this->request->get[$this->ID])) {
				$key = $this->request->get[$this->ID];
				
				$this->model->{$this->getFunc('delete')}($key);
			} 
			
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}