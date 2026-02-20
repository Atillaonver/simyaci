<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Footer extends \Opencart\System\Engine\Controller {
	
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/footer';
	private $C = 'footer';
	private $ID = 'footer_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void{
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id'] = $this->storeId;
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
	
	public function install():void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getForm();
	}
	
	public function index():void {
		$this->getForm();
	}
	
	protected function getForm() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		
		
		$this->load->model('tool/image');
		$this->imgTool = $this->model_tool_image;
		
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		${$this->ID}= $this->session->data['store_id'];
		$this->model->{$this->getFunc('get')}();
		
		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], 'SSL');


		//$data['languages'] = $languages = $this->model->{$this->getFunc('get','Languages')}();
		$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}();
		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		
		$sendData = ['control'=>$this->C];
		$data['editJS'] = $this->load->controller('bytao/editor.js',$sendData);
		$data['modals'] = $this->load->controller('bytao/editor.modal',$sendData);
		
		$data['editors']=[];
		
		foreach($languages as $language){
			$sendData=[
				'language_id' 	=> $language['language_id'],
				'control' 		=> $this->C,
				'descriptions'	=> $data[$this->C.'_description']
				];
			$data['editors'][$language['language_id']] = $this->load->controller('bytao/editor.loadedit',$sendData);
		}
		
		
		//$this->load->model('bytao/banner');
		$data['banners'] = array();//$this->model_bytao_banner->getBanners();
		
		//$this->load->model('bytao/media');
		$data['medias'] = array();//$this->model_bytao_media->getMedias();
		
		//$this->load->model('bytao/menu');
		$data['menus'] = array();//$this->model_bytao_menu->getMenus();
		
		//$this->load->model('bytao/custom');
		
		$data['customs'] = array();//$this->model_bytao_custom->getCustoms();
		
		$data['modules'] = [];//$this->model_bytao_home->getAllWidgets();
		
				
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

		if (!$json) {
			$this->model->{$this->getFunc('edit')}($this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}