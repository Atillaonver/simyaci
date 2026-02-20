<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Editor extends \Opencart\System\Engine\Controller {
	private $version = '1.0.3';
	private $cPth = 'bytao/editor';
	private $C = 'editor';
	private $ID = 'editor_id';
	private $Tkn = 'user_token';
	private $groupId =0;
	private $imgTool;
	private $ctrl;
	private $model ;
	private $ctrlTypes=[]; 
	
	private function getFunc($f='',$addi=''):string {
		return $f; //$f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C)));
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				$this->ctrlTypes = $this->model->{$this->getFunc('getEditorCtrlTypes')}();
				break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':
				$this->load->language($this->cPth);
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				$this->ctrlTypes = $this->model->{$this->getFunc('getEditorCtrlTypes')}();
				break;
			default:
		}
	}
	
	public function index():string {
		$this->getML('ML');
		return $this->load->view($this->cPth.'/'.$this->C, $data);
	}
	
	public function loadedit($comes=[]):string {
		
		$this->getML('ML');
		
		$ctrl = $data['ctrl'] = $comes['control'];
		$ctrl_id = $data[$ctrl.'_id'] = isset($comes[$ctrl.'_id'])?$comes[$ctrl.'_id']:0;
		$language_id = $data['language_id'] = $comes['language_id'];
		$descriptions = $comes['descriptions'];
		$storeId = $this->session->data['store_id'];
		
		$data['collaps'] = isset($this->session->data[$ctrl.$storeId.'-'.$language_id])?$this->session->data[$ctrl.$storeId.'-'.$language_id]:0;
		$data['rows'] = isset($descriptions[$language_id]['rows'])?$descriptions[$language_id]['rows']:[];
		$data['css'] = isset($descriptions[$language_id]['css'])?$descriptions[$language_id]['css']:'';
		$data['himage'] = isset($descriptions[$language_id]['himage'])?$descriptions[$language_id]['himage']:'';
		$data['fimage'] = isset($descriptions[$language_id]['fimage'])?$descriptions[$language_id]['fimage']:'';
		
		$data['ADM']= $this->user->getGroupId();
		
		if ($data['himage'] && is_file(DIR_IMAGE . html_entity_decode($data['himage'], ENT_QUOTES, 'UTF-8'))) {
			$data['himage_thumb']= $this->model_tool_image->resize(html_entity_decode($data['himage'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else{
			$data['himage_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if ($data['fimage'] && is_file(DIR_IMAGE . html_entity_decode($data['fimage'], ENT_QUOTES, 'UTF-8'))) {
			$data['fimage_thumb']= $this->model_tool_image->resize(html_entity_decode($data['fimage'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else{
			$data['fimage_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		
		$data['HTTP_IMAGE'] = URL_IMAGE;
		$data['button_update']=$this->language->get('button_update');
		$data['button_cancel']=$this->language->get('button_cancel');
		$data['mTypes'] = $this->model->{$this->getFunc('getEditorColumnTypes')}();
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		return $this->load->view($this->cPth.'/'.$this->C, $data);
	}
	
	public function js($comes=[]):string {
		
		$this->getML('ML');	
		$data['ctrl'] = $ctrl = $comes['control'];
		$data['button_update']=$this->language->get('button_update');
		$data['button_cancel']=$this->language->get('button_cancel');
		
		$data['entry_himage']=$this->language->get('entry_himage');
		$data['entry_fimage']=$this->language->get('entry_fimage');
		
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		
		$data['mTypes'] = $this->model->{$this->getFunc('getEditorColumnTypes')}();
		
		
		$this->load->model('tool/image');
		
		$data['HTTP_IMAGE'] = URL_IMAGE;
		$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$data['modals'] = $this->load->view($this->cPth.'/'.$this->C.'_modal', $data);
		
		$data['ADM']= $this->user->getGroupId();
		
		
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css');
		$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js');
		$this->document->addStyle('view/javascript/summernote/summernote.min.css');
		$this->document->addScript('view/javascript/summernote/summernote.min.js');
		$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
		$this->document->addScript('view/javascript/summernote/mudur.js');
		
		$this->document->addStyle('view/bytao/css/editorv7.css?v53');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		return $this->load->view($this->cPth.'/'.$this->C.'_js', $data);
	}
	
	public function modal($comes=[]):string {
		
		$this->getML('ML');	
		$data['ctrl'] = $ctrl = $comes['control'];
		
		$data['mTypes'] = $this->model->{$this->getFunc('getEditorColumnTypes')}();
		
		$this->load->model('tool/image');
		
		$data['HTTP_IMAGE'] = URL_IMAGE;
		$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		
		return $this->load->view($this->cPth.'/'.$this->C.'_modal', $data);
	}
	
	public function editmodule():void {
		$this->getML('ML');	
		$json = [];
		if (isset($this->request->get['mID'])) {
			$json['module'] = $this->model->editModule((int)$this->request->get['mID'],$this->request->post);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getmodal():void {
		$json = [];
		
		$this->getML('ML');
		$data['text_select']=$this->language->get('text_select');
		$data['text_none']=$this->language->get('text_none');
		$content = isset($this->request->get['cntnt'])?$this->request->get['cntnt']:'';
		$rw_id = isset($this->request->get['rwid'])?$this->request->get['rwid']:'';
		
		
		if (isset($this->request->get['type'])) {
			
			$mT = $this->request->get['type'];
			
			switch($mT){
				case 4: //Koleksiyon
					break;
				case 5: //Youtube
					break;
				case 6: //Slider
					{
						$data['mTypes'] = $data['mTypes'] = $this->model->{$this->getFunc('getEditorColumnTypes')}();
						$this->load->model('bytao/layerslider');	
						
						$layerslider = $this->model_bytao_layerslider->getLayersliderGroupWidget();
						
						$data['slayders']=[];
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
						$data['carousels']=['Banners','Selected Products','Selected Category'];
						$data['banners'] = [];
						$filter_data = [] ;
						
						$this->load->model('bytao/carousel');
						$this->load->model('design/banner');
						
						$results = $this->model_design_banner->getBanners($filter_data);
						foreach ($results as $result) {
							$data['banners'][$result['banner_id']] = [
								'banner_id' => $result['banner_id'],
								'name'      => $result['name']
							];
						}
			
						$carousel_info = $this->model_bytao_carousel->getCarouselWidget($content);
						
						if($carousel_info){
							$language_id = $carousel_info['language_id'];
							$data['item_category'] = explode(',',$carousel_info['content']);;
							$data['item_id'] = $carousel_info['carousel_id'];
							$data['type_item'] = $carousel_info['type_item'];
							$data['title_item'] = $carousel_info['title'];
							$data['max_item'] = $carousel_info['max_item'];
							$data['setting'] = unserialize($carousel_info['setting']);
							$data['products'] = $this->model_bytao_carousel->getCarouselProds($data['type_item']==1?explode(',',$carousel_info['content']):[],$language_id);
							$data['categories'] = $this->model_bytao_carousel->getCarouselCats($data['type_item']==2?explode(',',$carousel_info['content']):[],$language_id);
							$data['item_banner_id'] = $data['type_item']==0?$carousel_info['content']:'';
						}
						else
						{
							$data['item_category'] = '0';
							$data['item_banner_id'] = '0';
							$data['type_item'] = 0;
							$data['title_item'] = '';
							$data['max_item'] = 4;
							$data['products'] = [];
							$data['categories'] = [];
							$data['setting'] = [];
						}
						
						
						$data[$this->Tkn] = $this->session->data[$this->Tkn];
						
						$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_t'.$mT, $data);
					}
					break;
				case 8: //modul
					{
						$this->load->model('setting/extension');
						$this->load->model('setting/module');
						
						$parts = explode('|',$content);
						$data['moduleN'] = $parts[0];
						$available = [];
						$installed = [];
						

						$extensions = $this->model_setting_extension->getExtensionsByType('module');
						foreach ($extensions as $extension) {
							if (in_array($extension['code'], $available)) {
								$installed[] = $extension['code'];
							} 
						}
						$data['extensions'] = [];
						$results = $this->model_setting_extension->getPaths('%/admin/controller/module/%.php');

						foreach ($results as $result) {
							$available[] = basename($result['path'], '.php');
						}
		
						if ($results) {
							foreach ($results as $result) {
								$extension = substr($result['path'], 0, strpos($result['path'], '/'));

								$code = basename($result['path'], '.php');

								$this->load->language('extension/' . $extension . '/module/' . $code, $code);

								$module_data = [];

								$modules = $this->model_setting_module->getModulesByCode($extension . '.' . $code);

								foreach ($modules as $module) {
									if ($module['setting']) {
										$setting_info = json_decode($module['setting'], true);
									} else {
										$setting_info = [];
									}

									$module_data[] = [
										'name'   => $module['name'],
										'code'      => $code.'/'.$extension.'/'.$module['module_id'] ,
										'status' => $setting_info['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
									];
								}

								if ($module_data) {
									$status = '';
								} else {
									$status = $this->config->get('module_' . $code . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
								}

								$data['extensions'][] = [
									'name'      => $this->language->get($code . '_heading_title'),
									'code'      => $code ,
									'status'    => $status,
									'module'    => $module_data,
									'install'   => $this->url->link('extension/module.install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
									'uninstall' => $this->url->link('extension/module.uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension . '&code=' . $code),
									'installed' => in_array($code, $installed),
									'edit'      => $this->url->link('extension/' . $extension . '/module/' . $code, 'user_token=' . $this->session->data['user_token'])
								];
							}
						}

						$sort_order = [];

						foreach ($data['extensions'] as $key => $value) {
							$sort_order[$key] = $value['name'];
						}

						array_multisort($sort_order, SORT_ASC, $data['extensions']);
						
						$json['view'] = $this->load->view($this->cPth.'/'.$this->C.'_t'.$mT, $data);
					
					}
					break;
				
				case 9: //Controls
					{
						$data['ctrls']=[];
						$cntnt = explode(',',$content);
						
						if(isset($cntnt[1])){
							$data['item_id'] = $cntnt[1];
							$data['selected'] = $cntnt[0];
							$data['sel_content'] = $this->load->controller($cntnt[0].'.getwidget',$cntnt);
						}
						
						foreach($this->ctrlTypes as $key => $ctrl){
							$data['ctrls'][]= [
								'ctrl' => $key,
								'name' => $ctrl
							];
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
	
	public function getwidget():void {
		$json = [];
		$this->getML('M');
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function gethumb():void {
		$json = [];
		if ($this->request->post['thumbs']) {
			$this->load->model('tool/image');
			$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);
			
			$thumbs = explode(',',$this->request->post['thumbs']);
			foreach($thumbs as $thumb){
				$_thumb= explode(':',$thumb);
				if(isset($_thumb[1])){
					
					if (is_file(DIR_IMAGE . html_entity_decode(str_replace('image/','',$_thumb[1]), ENT_QUOTES, 'UTF-8'))) {
						$json[]=$_thumb[0].'|'. $this->model_tool_image->resize(html_entity_decode(str_replace('image/','',$_thumb[1]), ENT_QUOTES, 'UTF-8'), 100, 100);
					} else {
						$json[]=$_thumb[0] .'|'. $placeholder;
					}
				}
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function install():void {
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
	}

}

	