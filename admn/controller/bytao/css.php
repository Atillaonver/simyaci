<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Css extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/css';
	private $C = 'css';
	private $ID = 'css_id';
	private $Tkn = 'user_token';
	private $model ;
	private $cssTypes = ['Core','Menu','Desktop' ,'tablet' ,'mobil' ,'yazılar' ]; // 1,2,3,4,5,6
	
	
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
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'], true)
		];
		
		$data['text_reset_confirm'] = $this->language->get('text_delete_confirm'); 
		$data['text_loading'] = $this->language->get('text_loading'); 
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_reset'] = $this->language->get('button_reset');
		
		$data['allCss']=[]; 
		$allCss = $this->model->{$this->getFunc('getAll')}();
		$ct=1;
		foreach($this->cssTypes as $css){
			$c=[];
			
			foreach($allCss as $cs){
				if($cs['type'] == $ct){
					$c = [
					'css'=>$cs['css'],
					'css_id'=>$cs['css_id'],
					'type'=>$cs['type'],
					'version'=>$cs['version'],
					];
				}
			}
			$data['allCss'][] = [
				'name' 		=> $css,
				'css_id' 	=> isset($c['css_id'])?$c['css_id']:0,
				'css' 		=> isset($c['css'])?(str_replace(["&amp;", "&gt;","&quot;"], ["&", ">",'"'], $c['css'])):'/*'.$css. '*/',
				'type' 		=> $ct,
				'version' 	=> isset($c['version'])?$c['version']:1
			];
			$ct++;
		}
		
		$data['store_id'] = $this->session->data['store_id'];
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['action'] = $this->url->link($this->cPth.'|edit', $this->Tkn.'='.$this->session->data[$this->Tkn], 'SSL');
		$data['cancel'] = $this->url->link('common/dashboard', $this->Tkn.'='.$this->session->data[$this->Tkn] , 'SSL');
		
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view($this->cPth, $data));
	
	}
	
	
	public function save(){
		$this->getML('ML');
		$json = [];
		
		$code = $this->request->post['code'];
		$css_id = $this->request->get['css_id'];
		$type = $this->request->get['type'];
		if($code){
			$saveData = [
				'css' => $code,
				'type' => $type
			];
			
			if($css_id>0){
				$this->model->{$this->getFunc('update')}($css_id,$saveData);
				$json['css_id']=$css_id;
				$json['version'] = $this->model->{$this->getFunc('get','Version')}($css_id);
			}else{
				$json['css_id'] = $this->model->{$this->getFunc('add')}($saveData);
				$json['version'] = 1;
			}
			$json['success'] = sprintf($this->language->get('text_success'), $this->cssTypes[$type-1],$json['version']);
		}else{
			$json['error'] =$this->language->get('error_content');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}