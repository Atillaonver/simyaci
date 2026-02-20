<?php
namespace Opencart\Catalog\Controller\Bytao;
class Banner extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/banner';
	private $C = 'banner';
	private $ID = 'banner_id';
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	
	public function index():void {
		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function getwidget( array $cDatat=[]):string {
		$this->getML('ML');
		
		if(isset($cDatat['ids'])){
			$parts = explode(',', $cDatat['ids']);
			
			if (isset($parts[1])) {
				${$this->ID} = (int)$parts[1];
			} else {
				${$this->ID}  = $parts[0];
			}
			$data[$this->ID] = ${$this->ID};
			$group_info = $this->model->{$this->getFunc('get')}(${$this->ID});
			
			if ($group_info) {
				$this->load->model('tool/image');
				$data['bybanners'] = [];
				$results = $this->model->{$this->getFunc('get','Images')}(${$this->ID});
				
				foreach ($results as $result) {
					if (is_file(DIR_IMAGE . html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8'))) {
						$data['bybanners'][] = [
							'link'  => $result['link'],
							'title'  => $result['description'],
							'banner_parent_class'  => $result['banner_parent_class'],
							'banner_style'  => $result['banner_style'],
							'image' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')),
							'image2' => $this->model_tool_image->resize(html_entity_decode(by_move($result['mobile_image']), ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'))
						];
					}
				}
				return $this->load->view($this->cPth.'_widget', $data);
			} 
		}
		return '';
	}
	
}
