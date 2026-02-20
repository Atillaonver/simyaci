<?php
class ControllerBytaoPslide extends Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/pslide';
	private $C = 'pslide';
	private $ID = 'pslide_id';
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
	
	
	public function index($lData=array()) {
		$this->getML('ML');
		//var_dump($lData);
		//exit;
		if (isset($lData['item_id'])) {
			${$this->ID} = (int)$lData['item_id'];
		} else {
			${$this->ID}  = 0;
		}

		$group_info = $this->model->{$this->getFunc('get')}(${$this->ID} );

		if ($group_info) {
			
			$this->load->model('tool/image');

			$data['psliders'] = array();

			$data['text_buy'] = $this->language->get('text_buy');
			$data['title'] = $this->model->{$this->getFunc('get','Description')}(${$this->ID});
			$results = $this->model->{$this->getFunc('get','Prods')}(${$this->ID});

			foreach ($results as $result) {
				
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['psliders'][] = array(
						'url'  => $result['url'],
						'title'  => $result['title'],
						'title2'  => $result['title2'],
						'image' => $this->model_tool_image->resize($result['image'],900,900),
						'image2' => $this->model_tool_image->resize($result['image2'],900,900)
					);
				}
			}

			return $this->load->view($this->cPth, $data);
		} 
		return;
	}
	
}
