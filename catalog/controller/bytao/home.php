<?php
namespace Opencart\Catalog\Controller\Bytao;
class Home extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/home';
	private $C = 'home';
	private $ID = 'home_id';
	private $Tkn = 'user_token';
	
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
	
	
	public function index(): void {
		
		$this->getML('ML');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];
		
		$data['back'] = HTTPS_IMAGE.by_move($this->config->get('site_home_back'));
		$data['footerback'] = HTTPS_IMAGE.by_move($this->config->get('site_home_footerback'));
		$language_id = $this->config->get('config_language_id');

		$home_info = $this->model->{$this->getFunc('get')}();
		
		if ($home_info) {
			
			if($home_info['meta_title']) $this->document->setTitle($home_info['meta_title']);
			else  $this->document->setTitle($this->config->get('config_meta_title_'.$language_id));
			
			if($home_info['meta_title']) $this->document->setDescription($home_info['meta_description']);
			else  $this->document->setTitle($this->config->get('config_meta_description_'.$language_id));
			
			if($home_info['meta_keyword'])$this->document->setKeywords($home_info['meta_keyword']);
			else  $this->document->setTitle($this->config->get('config_meta_keyword_'.$language_id));
			
			$data['breadcrumbs'][] = [
				'text' => $home_info['title'],
				'href' => $this->url->home()
			];
			
			$data['heading_title'] = $home_info['title'];
			
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link($this->cPth);
			$data['rows'] = $this->load->controller('bytao/editor.rowBender',['ctrl'=>'home']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			
			$data['footer'] = $this->load->controller('bytao/footer',['route'=>'bytao/home']);
			$data['header'] = $this->load->controller('bytao/header',['route'=>'bytao/home']);
			
			$this->response->setOutput($this->load->view($this->cPth, $data));
			
		} 
		else 
		{
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->home()
			];

			$this->document->setTitle($this->language->get('text_error'));
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->home();

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',['route'=>'bytao/not_found']);
			$data['header'] = $this->load->controller('bytao/header',['route'=>'error/not_found']);
			
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	public function accepte()
	{
		$json = [];
		$this->session->data['accepte'] = '1';
		$json['accepte'] = '1';
		$this->response->addHeader('Access-Control-Allow-Origin: *');
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	public function trackit():void {
		$this->getML('ML');
		$json = [];
		$this->response->addHeader('Access-Control-Allow-Origin: *');
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
}