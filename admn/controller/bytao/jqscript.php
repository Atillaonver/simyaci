<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Jqscript extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/jqscript';
	private $C = 'jqscript';
	private $ID = 'jqscript_id';
	private $Tkn = 'user_token';
	private $model ;
	private $jqsTypes = ['menu','shop','slider']; // 1,2,3,4,5,6
	
	
	public function getPth():string{
		return $this->cPth;
	}
	
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
		
		$this->document->setTitle($this->language->get('heading_title'));
		
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
		
		
		$data['text_reset_confirm'] = $this->language->get('text_delete_confirm'); 
		$data['text_loading'] = $this->language->get('text_loading'); 
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_reset'] = $this->language->get('button_reset');
		
		$data['allJqscript']=[]; 
		$allJq = $this->model->{$this->getFunc('getAll')}();
		foreach($allJq as $jq){
			$code = html_entity_decode($jq['jqscript']);
			$code = str_replace(['&lt;','&gt;','&amp;'],['<','>','&'],$code);
			
			$data['allJqscript'][] = [
				'jqscript'=>$code,
				'jqscript_id'=>$jq['jqscript_id'],
				'type'=>$jq['type'],
				'version'=>$jq['version'],
			];
		}
		
		
		$data['store_id'] = $this->session->data['store_id'];
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view($this->cPth, $data));
	
	}
	
	
	public function save(){
		$this->getML('ML');
		$json = [];
		
		$jqscript = $this->request->post['jqscript'];
		$jqscript_id = $this->request->get['jqscript_id'];
		$type = $this->request->get['type'];
		
		if($jqscript){
			$saveData = [
				'jqscript' => $jqscript,
				'type' => $type
			];
			
			if($jqscript_id>0){
				$this->model->{$this->getFunc('update')}($jqscript_id,$saveData);
				$json['jqscript_id'] = $jqscript_id;
				$json['version'] = $this->model->{$this->getFunc('get','Version')}($jqscript_id);
			}else{
				$json['jqscript_id'] = $this->model->{$this->getFunc('add')}($saveData);
				$json['version'] = 1;
			}
			$json['success'] = sprintf($this->language->get('text_success'), $type,$json['version']);
		}else{
			$json['error'] =$this->language->get('error_content');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}