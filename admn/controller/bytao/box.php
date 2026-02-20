<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Box extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/box';
	private $C = 'box';
	private $ID = 'box_id';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f='',$addi=''):string{
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML(string $ML=''):void{
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id'] = 0;
		}
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
	}
	
	public function index():void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getForm();
	}

	public function edit():void {
		$this->load->language($this->cPth);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model($this->cPth);

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_bytao_css->editCss($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

		}

		$this->getForm();
	}

	protected function getForm():void {
		
		$this->document->addStyle('view/javascript/fancy/jquery.fancybox.css');
		$this->document->addScript('view/javascript/fancy/jquery.fancybox.js');		
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->load->model('customer/customer_group');
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		
		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		
		$ROWS = $this->model->{$this->getFunc('get','Groups')}();
		$sort_order=1;
		if($ROWS){
			foreach ($ROWS as $row) {
				
				$data['rows'][$sort_order]= [
				'boxes'  		=> $this->getBox($this->model->{$this->getFunc('get','s')}($row['box_row_id'])),
				'status' 		=> $row['status'],
				'name' 			=> $row['name'],
				'customer_group_id' 	=> $row['customer_group_id'],
				'box_row_id' 	=> $row['box_row_id'],
				'start_date'    => is_null($row['start_date'])? '' : $row['start_date'],
				'end_date'      => is_null($row['end_date'])? '' : $row['end_date']
				];
				
				$sort_order++;
			}
		}
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function save(){
		$this->getML('ML');
		$json = [];
		
		if (!$this->user->hasPermission('modify', 'bytao/box')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->user->hasPermission('modify', 'bytao/box')) {
			$json['error'] =$this->language->get('error_content');
		}else{
			$json['set']['box_row_id_'.$this->request->post['row']] = $this->model->{$this->getFunc('edit')}($this->request->post);
			$json['success'] = $this->language->get('text_success');
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	private function getBox(array $popups = []):array{
		$boxes=[];
		
		foreach ($popups as $popup) {
			
			if (is_file(DIR_IMAGE . $popup['image'])) {
					$image = $popup['image'];
					$thumb = $this->model_tool_image->resize($popup['image'], 100, 100);
				} else {
					$image = '';
					$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
					
				}
			
			
			$positions=explode("-",$popup['position']);
			
			$boxes[] = [
				'customer_group_id' => $popup['customer_group_id'],
				'name'          => $popup['name'],
				'link'          => $popup['link'],
				'image'             => $image,
				'thumb'             => $thumb,
				'description'             => $popup['description'],
				'position'             => $popup['position'],
				'positions'             => $positions,
				'status'             => $popup['status'],
				'row_order'             => $popup['sort_order'],
				'sort_order'             => $popup['sort_order'],
				'date_start'        => ($popup['date_start'] != '0000-00-00') ? $popup['date_start'] : '',
				'date_end'          => ($popup['date_end'] != '0000-00-00') ? $popup['date_end'] : ''
			];
		}
		return $boxes;
	}

	public function getwidget(array $ndata = []) {
		$this->getML('ML');
		
		$json= [];
		
		if( isset($ndata[1])){
			$data['parts'] = $ndata;
		}
		
		$filter_data = [
			'status' => 1
		];
		
		$results = $this->model->{$this->getFunc('get','Groups')}($filter_data);

		foreach ($results as $result) {
			if($result['status']){
				$data['items'][] = [
					'item_id' 	=> $result['box_row_id'],
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