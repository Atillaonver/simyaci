<?php
class ControllerBytaoSorter extends Controller {
	
	private $error = array();
	private $version = '1.0.2';
	private $cPth = 'bytao/sorter';
	private $C = 'sorter';
	private $ID = 'sorter_id';
	private $Tkn = 'user_token';
	private $groupId =0;
	private $imgTool;
	private $ctrl;
	private $model ;
	private $modalTypes = array('Text-image-movie(zengin editor)','Basit Yazı', 'Basit Resim','Resim Koleksiyon','Youtube Video' , 'Slider','Carousel' , 'Modul','Yönetim');//1,2,3,4,5,6,7,..
	private $ctrlTypes = array('bytao/page'=>'page','bytao/faq'=> 'sss' , 'bytao/blog'=>'blog','bytao/news'=> 'haberler','bytao/pslide'=> 'pslide');//1,2,3,4,5,6,7,..
	private $storeId=0;	
	
	private function getFunc($f='',$addi=''){
		return $f; //$f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C)));
	}
	
	private function getML($ML=''){
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
	
	public function index() {
		$this->getML('ML');
		
		return $this->load->view($this->cPth, $data);
	}
	
	public function loadedit($comes=array()) {
		
		$this->getML('L');
		$data['ctrl'] = $ctrl = $comes['control'];
		$this->load->model('bytao/'.$ctrl);	
		$data['language_id'] = $comes['language_id'];
		$data['rows'] = $comes['rows']?$comes['rows']:$this->{'model_bytao_'.$ctrl}->{'get'.ucfirst($ctrl).'Rows'}($comes[$ctrl.'_id'],$comes['language_id']);
		$data['text_row']=$this->language->get('text_row');
		$data['text_add_row']=$this->language->get('text_add_row');
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		
		$data['css'] = isset($comes[$ctrl][$comes['language_id']]['css'])?$comes[$ctrl][$comes['language_id']]['css']:'';
		$data['himage'] = isset($comes[$ctrl][$comes['language_id']]['himage'])?$comes[$ctrl][$comes['language_id']]['himage']:'';
		$data['fimage'] = isset($comes[$ctrl][$comes['language_id']]['fimage'])?$comes[$ctrl][$comes['language_id']]['fimage']:'';
		$data['button_update']=$this->language->get('button_update');
		$data['button_cancel']=$this->language->get('button_cancel');
		$data['mTypes'] = $this->modalTypes;
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		return $this->load->view($this->cPth, $data);
	}
	
	public function js($comes=array()) {
		
		$this->getML('ML');	
		$data['ctrl'] = $ctrl = $comes['control'];
		$data['button_update']=$this->language->get('button_update');
		$data['button_cancel']=$this->language->get('button_cancel');
		
		$data['entry_himage']=$this->language->get('entry_himage');
		$data['entry_fimage']=$this->language->get('entry_fimage');
		
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		
		$data['mTypes'] = $this->modalTypes;
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->load->model('tool/image');
		$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		
		return $this->load->view($this->cPth.'/'.$this->C.'_js', $data);
	}
	
	
	public function getmodal(){
		$json = array();
		
		$this->getML('ML');
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		
		if (isset($this->request->get['type'])) {
			
			$mT = $this->request->get['type'];
			switch($mT){
				case 4: //Koleksiyon
					break;
				case 5: //Youtube
					break;
				case 6: //Slider
					{
						$data['mTypes'] = $this->modalTypes;
						$this->load->model('bytao/layerslider');	
						
						$layerslider = $this->model_bytao_layerslider->getLayersliderGroupWidget();
						
						$data['slayders']=array();
						foreach($layerslider as $key => $slider){
							$data['slayders'][]= array(
								'layerslider_group_id' => $slider['layerslider_group_id'],
								'name' => $slider['name']
							);
						}
						$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_t'.$mT, $data);
					}
					break;
					
				case 7: //Carousel
					{
						
					}
				
				
					break;
				case 8: //modul
					{
						$this->load->model('setting/extension');
						$this->load->model('setting/module');
						$data['extensions'] = array();
						$extensions = $this->model_setting_extension->getInstalled('module');

						foreach ($extensions as $code) {
							$this->load->language('extension/module/' . $code, 'extension');
							$module_data = array();
							$modules = $this->model_setting_module->getModulesByCode($code);
							foreach ($modules as $module) {
								$module_data[] = array(
									'name' => strip_tags($module['name']),
									'code' => $code . '.' .  $module['module_id']
								);
							}
							if ($this->config->has('module_' . $code . '_status') || $module_data) {
								$data['extensions'][] = array(
									'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
									'code'   => $code,
									'module' => $module_data
								);
							}
						}
						
						
						$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_t'.$mT, $data);
					
					}
					break;
				
				case 9: //Kontrol
					{
						$data['ctrls']=array();
						foreach($this->ctrlTypes as $key => $ctrl){
							$data['ctrls'][]= array(
								'ctrl' => $key,
								'name' => $ctrl
							);
						}
						
						$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_t'.$mT, $data);
					}
					break;
			}
			
			
			
			
			
			
			
			
		}else{
			$json['error']=$this->language->get('error_missing');
		}	
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	

	public function getwidget(){
		$json = array();
		$this->load->model($this->modelPth);
		$this->model=$this->{'model_'.str_replace('/','_',$this->modelPth)};
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function install(){
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
	}
}

	