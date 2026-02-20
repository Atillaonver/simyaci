<?php
namespace Opencart\Catalog\Controller\Bytao;
class Member extends \Opencart\System\Engine\Controller {	

	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/member';
	private $C = 'member';
	private $ID = 'member_id';
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
	
	
	public function index() {
		$this->getML('ML');
		$data['HTTP_IMAGE'] = HTTP_IMAGE;
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home(true)
		);
		
		$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->cPth)
		);
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keyword'));
			
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['allitems']=array();
		
		$items = $this->model->{$this->getFunc('get','s')}();
		if ($items) {
			$this->load->model('tool/image');
			
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$image=$this->model_tool_image->resize($result['image'],400,300);
				}
				if (is_file(DIR_IMAGE . $result['bimage'])) {
					$bimage=$this->model_tool_image->resize($result['bimage'],800,600);
				}
				
				$data['allitems'][] = array(
						'url'  => $result['url'],
						'title'  => $result['title'],
						'image' => $image,
						'bimage' => $bimage
					);
				
				
			}
		} 
		
		$data['footer'] = $this->load->controller('bytao/footer');
		$data['header'] = $this->load->controller('bytao/header');
		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function getwidget($lData=[]){
		$this->getML('ML');
		$data['witems'] = [];
		
		if (isset($lData['col_content_id'])) {
			$col_content_id = explode(',',$lData['col_content_id']);
		} 
		$data['title'] = isset($col_content_id[1])?$col_content_id[1]:$this->language->get('text_widget_title');
		
		$limit = isset($col_content_id[2])?$col_content_id[2]:6;
		$width = isset($col_content_id[3])?$col_content_id[3]:400;
		$height = isset($col_content_id[4])?$col_content_id[4]:400;
		
		$items = $this->model->{$this->getFunc('get','s')}($limit);
		if ($items) {
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			
			$data['placeholder'] = $this->model_tool_image->resize('no-image.png',$width,$height);
			
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['witems'][] = [
						'url'  => $result['url'],
						'title'  => $result['title'],
						'image' => $this->model_tool_image->resize($result['image'],$width,$height)
					];
				}
			}
			return $limit?$this->load->view($this->cPth.'_widget', $data):$this->load->view($this->cPth.'_widget_all', $data);
		} 
	}
}
