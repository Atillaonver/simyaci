<?php
namespace Opencart\Admin\Controller\Bytao;
class Slideshow extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/slideshow';
	private $C = 'slideshow';
	private $ID = 'slideshow_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	public function getPth(): string {
		return $this->cPth;
	}
	
	private function getFunc($f='',$addi=''): string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''): void{
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
	
	public function install(): void{
		$this->getML('ML');
		$this->model->{$this->getFunc('install')}();
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getList();
		
	}
	
	public function index(): void {
		$this->getML('ML');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

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

		
		if( empty($this->request->post['slider']['name']) ){
			$json['error']  =  $this->language->get('error_missing_title');	
		}
		
		if(!isset($json['error'])){
			
			$groupId = $this->model->{$this->getFunc('edit','Group')}( $this->request->post );
			
			$json['slideshow_id']	=	$groupId;
			
			if($this->request->post['slideshow_id'] == 0 ){
				$json['newlayer']    = $groupId;
				$json['text_added_slider']    = $this->language->get('text_added_slider');
			}else{
				$json['updatelayer']    = $groupId;
				$json['text_updated_slider']    = $this->language->get('text_updated_slider');
			}
			$json['success'] = $this->language->get('text_success');
			
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->getML('ML');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete','Groups')}($item_id);
			}

			$json['success'] = $this->language->get('text_success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	public function form(): void {
		
		$this->getML('ML');		
		$data['yesno'] = [ 1=> $this->language->get('text_yes'), 0=>$this->language->get('text_no') ];
		$data['openclose'] = [ 1=> $this->language->get('text_open'), 0=>$this->language->get('text_close') ];
		$data['usevideo'] = [ '0'=> $this->language->get('No'),'youtube'=>'Youtube','vimeo'=>'Vimeo'];
		
		$data['img_url'] = URL_IMAGE;
		$data['DIR_MEDIA'] = URL_IMAGE;
		$data['HTTP_CATALOG'] = HTTP_CATALOG;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		
		$url = '';

		if(isset($this->request->get['filter_name'])){
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		
		if(isset($this->request->get['filter_all'])){
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if(isset($this->request->get['filter_start_date'])){
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		if(isset($this->request->get['filter_end_date'])){
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		
		if(isset($this->request->get['sort'])){
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if(isset($this->request->get['order'])){
			$url .= '&order=' . $this->request->get['order'];
		}

		if(isset($this->request->get['page'])){
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL')
		];

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['saction'] = $this->url->link($this->cPth.'.add', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL');
		$data['laction'] = $this->url->link($this->cPth.'.savedata', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL');
		
		$this->load->model('bytao/common');
		$data['languages']= $languages = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');
		$data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		
		if(isset($this->request->get['slideshow_id'])){
			$SlideshowId = $this->request->get['slideshow_id'];
			
		} else{
			$SlideshowId = 0;
		}
		
		$data['slideshow_id'] = $SlideshowId;
		
		$item_info = $this->model->{$this->getFunc('get','')}($SlideshowId);
		
		if(isset($item_info['params'])){
			$data['parameters'] = $item_info['params'];
		} else{
			$data['parameters'] = [];
		}
		$params = $data['parameters'];
		
		
		if(isset($item_info['status'])){
			$data['status'] = $item_info['status'];
		} else{
			$data['status'] = 1;
		}

		if(isset($item_info['name'])){
			$data['name'] = $item_info['name'];
		} else{
			$data['name'] = '';
		}
		
		if(isset($item_info['start_date'])){
			$data['start_date'] = (int)$item_info['start_date']?date('d-m-Y', strtotime($item_info['start_date'])):'';
		} else{
			$data['start_date'] = '';
		}
		
		if(isset($item_info['end_date'])){
			$data['end_date'] = (int)$item_info['end_date']?date('d-m-Y', strtotime($item_info['end_date'])):'';
		} else{
			$data['end_date'] = '';
		}
		
		if(isset($item_info['position'])){
			$data['position'] = $item_info['position'];
		} else{
			$data['position'] = 1;
		}
		
		if(isset($item_info['position_id'])){
			$data['position_id'] = $item_info['position_id'];
		} else{
			$data['position_id'] = 0;
		}
		
		if(isset($item_info['viewpos'])){
			$data['viewpos'] = $item_info['viewpos'];
		} else{
			$data['viewpos'] = 0;
		}
		
		if(isset($item_info['link'])){
			$data['link'] = $item_info['link'];
		} else{
			$data['link'] = '';
		}
		
		if(isset($item_info['type'])){
			$data['type'] = $item_info['type'];
		} else{
			$data['type'] = '';
		}
		
		if(isset($item_info['image'])){
			$data['image'] = isset($item_info['image'])?$item_info['image']:'';
		} else{
			$data['image'] = '';
		}
		
		$data['slide_defaults'] = $this->slideshow_default();
		
		
		$this->load->model('setting/setting');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$setting = $this->model_setting_setting->getSetting('config', $store_id);
		
		$this->load->model('bytao/css');
		$css = $this->model_bytao_css->getCssByType(6);
		
		$type_contents =  isset($css['css'])? css_process($css['css'],($setting['config_url']?$setting['config_url']:HTTP_CATALOG).'cdn/'):'';
		
		$this->document->addCss('typo', $type_contents);
		
		$data['actionUpdatePostURL'] = $this->url->link($this->cPth.'.savepos', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL');

		$sliderGroup = $this->model->{$this->getFunc('get','')}( $SlideshowId );
		if( !$sliderGroup ){
			$this->response->redirect( $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL') );
		}


		// $data['text_browse'] = $this->language->get( 'text_browse' );
		$data['sliderGroups'] = $this->model->{$this->getFunc('getList','Slides')}();

		$data['slideInfo']  = $item_info; 
		$data['slideHeight'] = (int) $item_info['params']['height'];
		$data['slideWidth']  = (int) $item_info['params']['width'];  
		$data['thumbHeight']  = (int)($item_info['params']['height'] * 200 /$item_info['params']['width']);
		//// get list  slider
		$data['slides'] = [];
		$language_id = isset($this->request->get['lang'])?$this->request->get['lang']:1;
		
		
		foreach($languages as $language){
			$slides =[];
			$slides = $this->model->{$this->getFunc('get','SlidesById')}( $SlideshowId, $language['language_id'] );
			if($slides){
				foreach($slides as $slide){
					$laparam =  unserialize($slide['params']);
					$th=isset($laparam['th'])?$laparam['th']:120;
					$data['slides'][$language['language_id']][] = [
						'title' => $slide['name'],
						'slide_id' => $slide['slide_id'],
						'image' => $this->model_tool_image->resize($slide['image'], 200, $th),
						'status' => $slide['status'],
				];
					
				}
			}
		}
		
		
		$data['group_id'] = $SlideshowId;
		
		$default = [
			'title' => '',
			'slide_id' => '0',
			'slide_link' => '',
			'slide_usevideo' => '0',
			'slide_videoid' => '',
			'slide_videoplay' => '0',
			'fullwidth'=> '',
			'image' => 'catalog/tools/back900x900a.jpg',
			'layersparams'=> '',
			'slide_transition' => 'random',
			'slide_delay'   => '10000',
			'slide_status'  => 1,
			'slide_transition' => 'random',
			'slide_duration'    => '300',
			'slide_rotation'   => '0',
			'slide_enable_link' => 0,
			'slide_link'  => '',
			'slide_thumbnail' => '',
			'slide_slot' =>'7',	
			'slide_image'   => 'catalog/tools/back900x900a.jpg',
			'layer_id'  => '',
			'slide_title' => '',
			'slide_position' => '',
			'slide_layout' => '',
			'slide_category' => '',
			'slide_viewpos' => '',
			'params' => []
				
	];
 
		
		 
		$slider = $this->model->{$this->getFunc('get','SlideById')}( 0 ); 
		
		$times = [];
		$layers = []; 	
		$slider = array_merge( $default, $slider ); 
		
		if( $slider['layersparams'] ){
			$std = unserialize( $slider['layersparams'] );
			$layers = $std->layers;
			foreach( $layers as $k=>$l ){
				$layers[$k]['layer_caption'] = addslashes( str_replace("'",'"',html_entity_decode( $l['layer_caption'] , ENT_QUOTES, 'UTF-8')) ); 
				$layers[$k]['layer_caption'] = preg_replace( "#\n|\r|\t#","", $layers[$k]['layer_caption']);
			}
		}
		$params = $slider['params'] ? unserialize( $slider['params'] ) : [];	
		$params = array_merge( $default, $params ); 


		if( $params['slide_thumbnail'] ){
			$data['slide_thumbnail'] =  $this->model_tool_image->resize(  $params['slide_thumbnail'], 
				$sliderGroup['params']['thumbnail_width'], $sliderGroup['params']['thumbnail_height'] );
		}else{
			$data['slide_thumbnail'] = '';
		}
  
		
		//$data['slide_title'] = $slider['title'];
		//$data['params'] = $params; 
		$data['layers'] = $layers;
		$data['slideshow_id']  = $SlideshowId;
		$data['slideshow_image'] = isset($slider['image'])?$slider['image']:'';  
		$data['slideshow_image_src'] = URL_IMAGE.'catalog/tools/back900x900a.jpg';
		
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		

		$this->document->addStyle('view/bytao/css/slideshow.css?v=32');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		
		$data['header'] = $this->load->controller('common/header');  
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}
	
	public function slide(): void {
		
		$this->getML('ML');		
		$data['yesno'] = [ 1=> $this->language->get('text_yes'), 0=>$this->language->get('text_no') ];
		$data['openclose'] = [ 1=> $this->language->get('text_open'), 0=>$this->language->get('text_close') ];
		$data['usevideo'] = [ '0'=> $this->language->get('No'),'youtube'=>'Youtube','vimeo'=>'Vimeo'];
		
		$data['img_url'] = URL_IMAGE;
		$data['DIR_MEDIA'] = URL_IMAGE;
		$data['HTTP_CATALOG'] = HTTP_CATALOG;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		
		$url = '';

		if(isset($this->request->get['filter_name'])){
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		
		if(isset($this->request->get['filter_all'])){
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if(isset($this->request->get['filter_start_date'])){
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		if(isset($this->request->get['filter_end_date'])){
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		
		if(isset($this->request->get['sort'])){
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if(isset($this->request->get['order'])){
			$url .= '&order=' . $this->request->get['order'];
		}

		if(isset($this->request->get['page'])){
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL')
		];

		$data['save'] = $this->url->link($this->cPth.'.savedata', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL');
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		
		$this->load->model('bytao/common');
		$data['languages']= $languages = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');
		$data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		
		if(isset($this->request->get['slideshow_id'])){
			$SlideshowId = $this->request->get['slideshow_id'];
			
		} else{
			$SlideshowId = 0;
		}
		
		$data['slideshow_id'] = $SlideshowId;
		
		$item_info = $this->model->{$this->getFunc('get','')}($SlideshowId);
		
		if(isset($item_info['params'])){
			$data['parameters'] = $item_info['params'];
		} else{
			$data['parameters'] = [];
		}
		$params = $data['parameters'];
		
		$data['slide_defaults'] = $this->slideshow_default();
		
		
		$this->load->model('setting/setting');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$setting = $this->model_setting_setting->getSetting('config', $store_id);
		
		$this->load->model('bytao/css');
		$css = $this->model_bytao_css->getCssByType(6);
		
		$type_contents =  isset($css['css'])? css_process($css['css'],($setting['config_url']?$setting['config_url']:HTTP_CATALOG).'cdn/'):'';
		
		$this->document->addCss('typo', $type_contents);
		
		$data['actionUpdatePostURL'] = $this->url->link($this->cPth.'.savepos', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL');

		$sliderGroup = $this->model->{$this->getFunc('get','')}( $SlideshowId );
		if( !$sliderGroup ){
			$this->response->redirect( $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL') );
		}


		// $data['text_browse'] = $this->language->get( 'text_browse' );
		$data['sliderGroups'] = $this->model->{$this->getFunc('getList','Slides')}();

		$data['slideInfo']  = $item_info; 
		$data['slideHeight'] = (int) $item_info['params']['height'];
		$data['slideWidth']  = (int) $item_info['params']['width'];  
		$data['thumbHeight']  = (int)($item_info['params']['height'] * 200 /$item_info['params']['width']);
		//// get list  slider
		$data['slides'] = [];
		$language_id = isset($this->request->get['lang'])?$this->request->get['lang']:1;
		
		
		foreach($languages as $language){
			$slides =[];
			$slides = $this->model->{$this->getFunc('get','SlidesById')}( $SlideshowId, $language['language_id'] );
			if($slides){
				foreach($slides as $slide){
					$laparam =  unserialize($slide['params']);
					$th=isset($laparam['th'])?$laparam['th']:120;
					$data['slides'][$language['language_id']][] = [
						'title' => $slide['name'],
						'slide_id' => $slide['slide_id'],
						'image' => $this->model_tool_image->resize($slide['image'], 200, $th),
						'status' => $slide['status'],
				];
					
				}
			}
		}
		
		
		$data['group_id'] = $SlideshowId;
		
		$default = [
			'title' => '',
			'slide_id' => '0',
			'slide_link' => '',
			'slide_usevideo' => '0',
			'slide_videoid' => '',
			'slide_videoplay' => '0',
			'fullwidth'=> '',
			'image' => 'catalog/tools/back900x900a.jpg',
			'layersparams'=> '',
			'slide_transition' => 'random',
			'slide_delay'   => '10000',
			'slide_status'  => 1,
			'slide_transition' => 'random',
			'slide_duration'    => '300',
			'slide_rotation'   => '0',
			'slide_enable_link' => 0,
			'slide_link'  => '',
			'slide_thumbnail' => '',
			'slide_slot' =>'7',	
			'slide_image'   => 'catalog/tools/back900x900a.jpg',
			'layer_id'  => '',
			'slide_title' => '',
			'slide_position' => '',
			'slide_layout' => '',
			'slide_category' => '',
			'slide_viewpos' => '',
			'params' => []
				
	];
 
		
		 
		$slider = $this->model->{$this->getFunc('get','SlideById')}( 0 ); 
		
		$times = [];
		$layers = []; 	
		$slider = array_merge( $default, $slider ); 
		
		if( $slider['layersparams'] ){
			$std = unserialize( $slider['layersparams'] );
			$layers = $std->layers;
			foreach( $layers as $k=>$l ){
				$layers[$k]['layer_caption'] = addslashes( str_replace("'",'"',html_entity_decode( $l['layer_caption'] , ENT_QUOTES, 'UTF-8')) ); 
				$layers[$k]['layer_caption'] = preg_replace( "#\n|\r|\t#","", $layers[$k]['layer_caption']);
			}
		}
		$params = $slider['params'] ? unserialize( $slider['params'] ) : [];	
		$params = array_merge( $default, $params ); 


		if( $params['slide_thumbnail'] ){
			$data['slide_thumbnail'] =  $this->model_tool_image->resize(  $params['slide_thumbnail'], 
				$sliderGroup['params']['thumbnail_width'], $sliderGroup['params']['thumbnail_height'] );
		}else{
			$data['slide_thumbnail'] = '';
		}
  
		
		//$data['slide_title'] = $slider['title'];
		//$data['params'] = $params; 
		$data['layers'] = $layers;
		$data['slideshow_id']  = $SlideshowId;
		$data['slideshow_image'] = isset($slider['image'])?$slider['image']:'';  
		$data['slideshow_image_src'] = URL_IMAGE.'catalog/tools/back900x900a.jpg';
		
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		

		$this->document->addStyle('view/bytao/css/slideshow.css?v=34');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		
		$data['header'] = $this->load->controller('common/header');  
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_slide', $data));
	}
	
	protected function getList(): string {
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		if(isset($this->request->get['filter_name'])){
			$filter_name = $this->request->get['filter_name'];
		} else{
			$filter_name = null;
		}

		
		if(isset($this->request->get['filter_item'])){
			$filter_item = $this->request->get['filter_item'];
		} else{
			$filter_item = null;
		}
		
		
		if(isset($this->request->get['filter_all'])){
			$filter_all = $this->request->get['filter_all'];
		}

		if(isset($this->request->get['filter_status'])){
			$filter_status = $this->request->get['filter_status'];
		} else{
			$filter_status = null;
		}

		if(isset($this->request->get['sort'])){
			$sort = $this->request->get['sort'];
		} else{
			$sort = 'name';
		}
		if(isset($this->request->get['filter_start_date'])){
			$filter_start_date = $this->request->get['filter_start_date'];
		} else{
			$filter_start_date = null;
		}

		if(isset($this->request->get['filter_end_date'])){
			$filter_end_date = $this->request->get['filter_end_date'];
		} else{
			$filter_end_date = null;
		}
		
		if(isset($this->request->get['order'])){
			$order = $this->request->get['order'];
		} else{
			$order = 'ASC';
		}

		if(isset($this->request->get['page'])){
			$page = $this->request->get['page'];
		} else{
			$page = 1;
		}

		$url = '';

		if(isset($this->request->get['filter_name'])){
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if(isset($this->request->get['filter_start_date'])){
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		
		if(isset($this->request->get['filter_end_date'])){
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		
			
		if(isset($this->request->get['sort'])){
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if(isset($this->request->get['order'])){
			$url .= '&order=' . $this->request->get['order'];
		}

		if(isset($this->request->get['page'])){
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'='. $this->session->data[$this->Tkn], 'SSL')
	];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL')
	];

		
		$data['action'] = $this->url->link($this->cPth.'.list', $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL');
		$data['copy'] = $this->url->link($this->cPth.'.copy', $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL');
		$data['delete'] = $this->url->link($this->cPth.'.deleteshow', $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL');
		
			
		$data['layersliders'] = [];
		
		
		$filter_data = [
			'filter_name'	  => $filter_name,
			'filter_status'   => $filter_status,
			'filter_start_date'   => $filter_start_date,
			'filter_end_date'   => $filter_end_date,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => (($page - 1) * (int)$this->config->get('config_limit_admin')),
			'limit'           => $this->config->get('config_limit_admin')
			];
		
		$this->load->model('tool/image');
		
		${$this->C.'_total'} = $this->model->{$this->getFunc('getTotal','')}($filter_data);
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach($results as $result){
			
			if(isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])){
				$thumb = $this->model_tool_image->resize( $result['image'], 200, 200);
					
			} else{
				$thumb = $this->model_tool_image->resize('no_image.png', 200, 200);
					
			}
			
			$data['slideshows'][] = [
				'slideshow_id' => $result['slideshow_id'],
				'name'       => $result['name'],
				'start_date'       => (int)$result['start_date']?date('d-m-Y', strtotime($result['start_date'])):'',
				'end_date'       => (int)$result['end_date']?date('d-m-Y', strtotime($result['end_date'])):'',
				'thumb'       => $thumb,
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->url->link($this->cPth.'.form', $this->Tkn.'='. $this->session->data[$this->Tkn] . '&slideshow_id=' . $result['slideshow_id'] , true),
				'slide'       => $this->url->link($this->cPth.'.slide', $this->Tkn.'='. $this->session->data[$this->Tkn] . '&slideshow_id=' . $result['slideshow_id'] , true)
		];
		}

		

		if(isset($this->error['warning'])){
			$data['error_warning'] = $this->error['warning'];
		} else{
			$data['error_warning'] = '';
		}

		if(isset($this->session->data['success'])){
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else{
			$data['success'] = '';
		}

		if(isset($this->request->post['selected'])){
			$data['selected'] = (array)$this->request->post['selected'];
		} else{
			$data['selected'] = [];
		}

		$url = '';

		if(isset($this->request->get['filter_name'])){
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
	
		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		
		if(isset($this->request->get['filter_start_date'])){
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		if(isset($this->request->get['filter_end_date'])){
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		
		
		
		
		if($order == 'ASC'){
			$url .= '&order=DESC';
		} else{
			$url .= '&order=ASC';
		}

		if(isset($this->request->get['page'])){
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . '&sort=name' . $url, 'SSL');
		$data['sort_status'] = $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . '&sort=status' . $url, 'SSL');
		$data['sort_start_date'] = $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . '&sort=start_date' . $url, 'SSL');
		$data['sort_end_date'] = $this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . '&sort=end_date' . $url, 'SSL');

		$url = '';

		if(isset($this->request->get['filter_name'])){
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if(isset($this->request->get['filter_start_date'])){
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		
		if(isset($this->request->get['filter_end_date'])){
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		
		
		
		if(isset($this->request->get['sort'])){
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if(isset($this->request->get['order'])){
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if(isset($this->request->get['page'])){
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => ${$this->C.'_total'},
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), (${$this->C.'_total'}) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > (${$this->C.'_total'} - $this->config->get('config_pagination_admin'))) ? ${$this->C.'_total'} : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), ${$this->C.'_total'}, ceil(${$this->C.'_total'} / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
	}

	private function slideshow_default():array {
		$defaults=[];
		$this->getML('ML');
		
		$defaults['positions'] = [
			'content_top',
			'column_left',
			'column_right',
			'content_bottom'
		];
		
		$defaults['shadow_types'] = [
			0  	=> $this->language->get('text_no_shadow'),
			1  => 1,
			2  => 2,
			3  => 3
		];
		
		$defaults['linepositions'] = [
			'top'     => $this->language->get('text_top'),
			'middle'     => $this->language->get('text_top'),
			'bottom'  => $this->language->get('text_bottom')
		];
		
		$defaults['position'] = [
			'1'     => $this->language->get('text_page_widget'),
			'2'  => $this->language->get('text_homeproducts'),
			'3'     => $this->language->get('text_categoryproducts'),
			'4'     => $this->language->get('text_general_use')
			
		];
		
		$defaults['layouts'] = [];
		$this->load->model('design/layout');
		$results = $this->model_design_layout->getLayouts([]);
		foreach($results as $result){
			$defaults['layouts'][$result['layout_id']] = [
				'layout_id' => $result['layout_id'],
				'name'      => $result['name']
			];
		}
		
		
		$this->load->model('catalog/category');
			
		$defaults['categories'] = [];
		$results = $this->model_catalog_category->getCategories(['c2.parent_id']);
		$category_data = [];
		$category_data[] = [
			'name'       => ' -- ',
			'category_id'       => 0,
			];
		foreach($results as $result){
			//$empty = $this->model_catalog_category->isCategoryEmpty($result['category_id']);
			$category_data[] = [
				'name'       => $result['name'],
				'category_id'       => $result['category_id'],
				//'empty'	=> $empty
				];
		}
		if(count($category_data)>0){
			$defaults['categories']= $this->array_msort($category_data, ['name'=>SORT_ASC]); //$category_data;
		}
		
	 	$defaults['layer_styles']=[];
		
		$this->load->model('setting/setting');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$setting = $this->model_setting_setting->getSetting('config', $store_id);
		
		$this->load->model('bytao/css');
		$css = $this->model_bytao_css->getCssByType(6);
		
		if(preg_match_all('/^\.tp-caption.(\w+)/m',html_entity_decode($css['css'], ENT_QUOTES, 'UTF-8'), $matches)){
			$defaults['layer_styles'] = $matches[1];
		}
		
		$type_contents =  isset($css['css'])? css_process($css['css'],($setting['config_url']?$setting['config_url']:HTTP_CATALOG).'cdn/'):'';
		
		
		
		$defaults['navigator_types'] = [
			'none'  => $this->language->get('text_none'),
			'bullet'     => $this->language->get('text_bullet'),
			'thumb'     => $this->language->get('text_thumbnail'),
			'both'     => $this->language->get('text_both')
				
		];
		
		$defaults['navigation_arrows'] = [
			'none'    			 => $this->language->get('text_none'),
			'nexttobullets' 	 => $this->language->get('text_nexttobullets'),
			'verticalcentered'   => $this->language->get('text_verticalcentered')
		];
		
		$defaults['navigation_style'] = [
			'round' 	    => $this->language->get('text_round'),
			'navbar'        => $this->language->get('text_navbar'),
			'round-old'     => $this->language->get('text_round_old') ,
			'square-old'    => $this->language->get('text_square_old') ,
			'navbar-old'    => $this->language->get('text_navbar_old') 
		];
		
		$defaults['fullwidth'] = [ 
			'boxed' 		   => $this->language->get('Boxed'),
			'fullwidth'  => $this->language->get('Fullwidth'),
			'fullscreen' => $this->language->get('Fullscreen') ,
			'autofill' => $this->language->get('autofill') ,
			'partialview' => $this->language->get('partialview') 
		];
		
		
		$defaults['transtions'] = [
			'notransition' => 'No Transition',
			'random' => 'Randdom',
			'randomflat' => 'Randdom Flat',
			'slidehorizontal'=> 'Slide Horizontal',
			'slidevertical' => 'Slide Vertical',
			'boxslide' => 'Box Slide',
			'boxfade' => 'Box Fade',
			'slotzoom-horizontal'=> 'Slot Zoom Horizontal1',
			'slot-zoom-horizontal'=> 'Slot Zoom Horizontal2',
			'slotslide-horizontal'=> 'Slot Slide Horizontal',
			'slotfade-horizontal'=> 'Slot Fade Horizontal',
			'slotzoom-vertical'=> 'Slot Zoom Vertical',
			'slotslide-vertical'=> 'Slot Slide Vertical',
			'slotfade-vertical'=> 'Slot Fade Vertical',
			'curtain-1' => 'Curtain 1',
			'curtain-2' => 'Curtain 2',
			'curtain-3' => 'Curtain 3',
			'slideleft' => 'Slide Left',
			'slideright' => 'Slide Right',
			'slideup' => 'Slide Up',
			'slidedown' => 'Slide Down',
			'papercut' => 'Page Cut',
			'3dcurtain-horizontal'=> '3dcurtain Horizontal',
			'3dcurtain-vertical'=> '3dcurtain Vertical',
			'flyin'=> 'Fly In',
			'turnoff' => 'Turn Off',
			'turnoff' => 'Turn Off',
			'custom-1' => 'Custom 1',
			'custom-2' => 'Custom 2',
			'custom-3' => 'Custom 3',
			'custom-4' => 'Custom 4'
		];
		$defaults_r['transtions'] = [
			'Randdom',
			'andom Flat',
			'Random Premium',
			'Random Flat and Premium',
			'Fade',
			'Fade Cross',
			'Slide To Bottom',
			'Slide To Top',
			'Slide To Left',
			'Slide To Right',
			'Fade Through Transparent',
			'Slide Over To Right',
			'Slide Over To Left',
			'Slide Over To Top',
			'Slide Over To Bottom',
			'Slide Slots Horizontal',
			'Slide Slots Vertical',
			'Fade Boxes',
			'Zoom Slots Horizontal',
			'Zoom Slots Vertical',
			'Curtain from Left',
			'Curtain from Right',
			'Curtain from Middle',
			'3D Curtain Horizontal',
			'3D Curtain Vertical',
			'In Cube Vertical',
			'In Cube Horizontal',
			'TurnOff Horizontal',
			'TurnOff Vertical',
			
		];
		
		$defaults['start_animation']= [
			'fade' => 'Fade In',
			'sft' => 'Short from Top',
			'sfb'=>'Short from Bottom',
			'sfr'=>'Short from Right',
			'sfl'=>'Short from Left',
			'lft'=>'Long from Top',
			'lfb'=>'Long from Bottom',
			'lfr'=>'Long from Right',
			'lfl'=>'Long from Left',
			'randomrotate'=>'Rasgele2'
		];
		
		$defaults['end_animation'] = [
			'stt'=>'Short to Top',
			'stb'=>'Short to Bottom',
			'stl'=>'Short to Left',
			'str'=>'Short to Right',
			'ltt'=>'Long to Top',
			'ltb'=>'Long to Bottom',
			'ltl'=>'Long to Left',
			'ltr'=>'Long to Right',
			'skewtoright'=>'Skew To Right',
			'skewtoleft'=>'Skew To Left',
			'skewtorightshort'=>'Skew To Right Short',
			'skewtoleftshort'=>'Skew To Left Short',
			'randomrotateout'=>'Rasgele'		
		];
		
		$defaults['easies'] = [
			'Linear.easeNone'=>'Linear.easeNone',
			'Power0.easeIn'=>'Power0.easeIn (linear)',
			'Power0.easeInOut'=>'Power0.easeInOut  (linear)',
			'Power0.easeOut'=>'Power0.easeOut  (linear)',
			'Power1.easeIn'=>'Power1.easeIn',
			'Power1.easeInOut'=>'Power1.easeInOut',
			'Power1.easeOut'=>'Power1.easeOut',
			'Power2.easeIn'=>'Power2.easeIn',
			'Power2.easeInOut'=>'Power2.easeInOut',
			'Power2.easeOut'=>'Power2.easeOut',
			'Power3.easeIn'=>'Power3.easeIn',
			'Power3.easeInOut'=>'Power3.easeInOut',
			'Power3.easeOut'=>'Power3.easeOut',
			'Power4.easeIn'=>'Power4.easeIn',
			'Power4.easeInOut'=>'Power4.easeInOut',
			'Power4.easeOut'=>'Power4.easeOut',
			'Quad.easeIn'=>'Quad.easeIn  (same as Power1.easeIn)',
			'Quad.easeInOut'=>'Quad.easeInOut  (same as Power1.easeInOut)',
			'Quad.easeOut'=>'Quad.easeOut  (same as Power1.easeOut)',
			'Cubic.easeIn'=>'Cubic.easeIn  (same as Power2.easeIn)',
			'Cubic.easeInOut'=>'Cubic.easeInOut  (same as Power2.easeInOut)',
			'Cubic.easeOut'=>'Cubic.easeOut  (same as Power2.easeOut)',
			'Quart.easeIn'=>'Quart.easeIn  (same as Power3.easeIn)',
			'Quart.easeInOut'=>'Quart.easeInOut  (same as Power3.easeInOut)',
			'Quart.easeOut'=>'Quart.easeOut  (same as Power3.easeOut)',
			'Quint.easeIn'=>'Quint.easeIn (same as Power4.easeIn)',
			'Quint.easeInOut'=>'Quint.easeInOut(same as Power4.easeInOut)',
			'Quint.easeOut'=>'Quint.easeOut(same as Power4.easeOut)',
			'Strong.easeIn'=>'Strong.easeIn(same as Power4.easeIn)',
			'Strong.easeInOut'=>'Strong.easeInOut(same as Power4.easeInOut)',
			'Strong.easeOut'=>'Strong.easeOut(same as Power4.easeOut)',
			'Back.easeIn'=>'Back.easeIn',
			'Back.easeInOut'=>'Back.easeInOut',
			'Back.easeOut'=>'Back.easeOut',
			'Bounce.easeIn'=>'Bounce.easeIn',
			'Bounce.easeInOut'=>'Bounce.easeInOut',
			'Bounce.easeOut'=>'Bounce.easeOut',
			'Circ.easeIn'=>'Circ.easeIn',
			'Circ.easeInOut'=>'Circ.easeInOut',
			'Circ.easeOut'=>'Circ.easeOut',
			'Elastic.easeIn'=>'Elastic.easeIn',
			'Elastic.easeInOut'=>'Elastic.easeInOut',
			'Elastic.easeOut'=>'Elastic.easeOut',
			'Expo.easeIn'=>'Expo.easeIn',
			'Expo.easeInOut'=>'Expo.easeInOut',
			'Expo.easeOut'=>'Expo.easeOut',
			'Sine.easeIn'=>'Sine.easeIn',
			'Sine.easeInOut'=>'Sine.easeInOut',
			'Sine.easeOut'=>'Sine.easeOut',
			'SlowMo.ease'=>'SlowMo.ease',
			'easeOutBack'=>'easeOutBack',
			'easeInQuad'=>'easeInQuad',
			'easeOutQuad'=>'easeOutQuad',
			'easeInOutQuad'=>'easeInOutQuad',
			'easeInCubic'=>'easeInCubic',
			'easeOutCubic'=>'easeOutCubic',
			'easeInOutCubic'=>'easeInOutCubic',
			'easeInQuart'=>'easeInQuart',
			'easeOutQuart'=>'easeOutQuart',
			'easeInOutQuart'=>'easeInOutQuart',
			'easeInQuint'=>'easeInQuint',
			'easeOutQuint'=>'easeOutQuint',
			'easeInOutQuint'=>'easeInOutQuint',
			'easeInSine'=>'easeInSine',
			'easeOutSine'=>'easeOutSine',
			'easeInOutSine'=>'easeInOutSine',
			'easeInExpo'=>'easeInExpo',
			'easeOutExpo'=>'easeOutExpo',
			'easeInOutExpo'=>'easeInOutExpo',
			'easeInCirc'=>'easeInCirc',
			'easeOutCirc'=>'easeOutCirc',
			'easeInOutCirc'=>'easeInOutCirc',
			'easeInElastic'=>'easeInElastic',
			'easeOutElastic'=>'easeOutElastic',
			'easeInOutElastic'=>'easeInOutElastic',
			'easeInBack'=>'easeInBack',
			'easeInOutBack'=>'easeInOutBack',
			'easeInBounce'=>'easeInBounce',
			'easeOutBounce'=>'easeOutBounce',
			'easeInOutBounce'=>'easeInOutBounce'
		];
		
		
		
		return $defaults;
	}
	
	private function layer_default():array {
		$defaults=[
			"layer_id"			=> "",
			"layer_type"		=> "image",
			"layer_caption"		=> "IMG",
			"layer_content"		=> "",
			"layer_image"		=> "",
			"layer_class"		=> "",
			"layer_easing"		=> "easeOutExpo",
			"layer_endanimation"=> "fadeout",
			"layer_endeasing"	=> "nothing",
			"layer_endspeed"	=> "300",
			"layer_linktarget"	=> "_self",
			"layer_link"		=> "",
			"layer_linkstatus"	=> "1",
			"layer_fontweight"	=> "bold",
			"layer_fontsize"	=> "20px",
			"layer_fontcolor"	=> "",  //#000
			"layer_lineheight"	=> "", // px - em vs
			"layer_animation" 	=> "fade",
			"layer_left"		=> "509",
			"layer_hpos"		=> "left",
			"layer_top"			=> "22",
			"layer_vpos"		=> "top",
			"layer_width"		=> "22",
			"layer_speed"		=> "350",
			"layer_video_height"=> "200",
			"layer_video_id"	=> "",
			"layer_video_thumb"	=> "",
			"layer_video_type"	=> "youtube",
			"layer_video_width"	=> "300",
			"layer_starttime"	=> "",
			"layer_endtime"		=> "0"
		];
		
		
		
		
		
		return $defaults;
	}
	
	public function lydelete():void {
		$json = [];
		
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
			$group_id = 0; 
		}else{
			$this->getML('M');
			if( isset($this->request->get['layer_id']) ){ 
				$this->model->{$this->getFunc('delete','Slider')}( $this->request->get['layer_id'] );
				$json['success']= $this->request->get['layer_id'];
			}
			//$group_id = $this->request->get['group_id'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
 	
	public function deleteshow():string {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if(isset($this->request->post['selected']) && $this->validateDelete()){
			foreach($this->request->post['selected'] as $item){
				$this->model->{$this->getFunc('delete')}($item);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if(isset($this->request->get['filter_name'])){
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if(isset($this->request->get['filter_status'])){
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if(isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order'])){
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, true));
		}

		$this->getList();
	}

	public function copy():void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		if(isset($this->request->post['selected']) && $this->validateCopy()){
		
			foreach($this->request->post['selected'] as $item){
				$this->model->{$this->getFunc('clone')}($item);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if(isset($this->request->get['filter_name'])){
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if(isset($this->request->get['filter_status'])){
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			if(isset($this->request->get['filter_start_date'])){
				$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
			}
			if(isset($this->request->get['filter_end_date'])){
				$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
			}
			
			if(isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order'])){
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth, $this->Tkn.'='. $this->session->data[$this->Tkn] . $url, 'SSL'));
		}

		$this->getList();
	}
	
	public function layer_new():void {
		$json = [];
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function savestatus():void {
		$json = [];
		
		if(isset($this->request->get['layer_id'])){
			$layer_id = $this->request->get['layer_id'];
			$status = $this->request->get['status'];
			
			$this->getML('M');
			$json['status'] = $this->model->{$this->getFunc('update','Status')}($layer_id,$status);	
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function savepos():void {
		$json = [];
		$this->getML('M');
		
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$json['error'] = $this->language->get('error_permission');
			
		}
		if( !isset($json['error'])&& isset($this->request->post['id'])  && is_array($this->request->post['id']) ){
			$first=TRUE;
			foreach( $this->request->post['id'] as $id => $pos ){
				$json['id']=$id;
				$_layer = explode('-',$id);
				$layer_id =(int)end($_layer);
				
				if($first){
					$this->model->{$this->getFunc('first','Image')}($layer_id);
					$first=false;
				}
			 		
				$this->model->{$this->getFunc('update','Sortorder')}($layer_id, $pos );
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function ajaxGet():void {
		$json = [];
		if(isset($this->request->post['t'])){
			$this->getML('ML');
			$this->load->model('tool/image');
			
			switch($this->request->post['t']){
				case 's'://tek slayt
					if($this->request->post['a']=='g')//get
					{
						if(isset($this->request->post['slide_id'])){
							$slide_id= $this->request->post['slide_id'];
							$slideshow_id = $this->request->post['slideshow_id'];
							
							$placeholder = $this->model_tool_image->resize('no_image.jpg', 100, 100);
							$slide = $this->model->{$this->getFunc('get','Slide')}($slide_id);
							
							if(isset($slide['image'])){
								$thumb = $this->model_tool_image->resize($slide['image'], 100, 100);
								$default = [
									'slide_id'  =>  $slide_id,
									'slider_title' => '',
									'slider_link' => '',
									'slider_usevideo' => '0',
									'slider_videoid' => '',
									'slider_videoplay' => '0',
									'fullwidth'=> '',
									'image' => $placeholder,
									'layersparams'=> '',
									'slider_transition' => 'random',
									'slider_delay'   => '0',
									'slider_status'  => 1,
									'slider_duration'    => '300',
									'slider_rotation'   => '0',
									'slider_enable_link' => 0,
									'slider_thumbnail' => '',
									'slider_slot' =>'7',	
									'params' => [],
									'slider_image'   => $placeholder
								];	
								$times = [];
								$layers = [];
								$layerdata = []; 	
								$SLIDE = array_merge( $default, $slide ); 
								$layers = $this->model->{$this->getFunc('get','SlideLayer')}($slide_id);
								
								foreach($layers as $layer)
								{
									$layerParam = unserialize($layer['params']);
									$layerdata[$layer['layer_id']] = $layerParam;
									$layerdata[$layer['layer_id']]['layer_caption'] = $layerParam['layer_caption']? addslashes( str_replace("'",'"',html_entity_decode( $layerParam['layer_caption'] , ENT_QUOTES, 'UTF-8')) ):''; 
									$layerdata[$layer['layer_id']]['layer_caption'] = preg_replace( "#\n|\r|\t#","", $layerdata[$layer['layer_id']]['layer_caption']);
								}
								$params = $SLIDE['params'] ? unserialize( $SLIDE['params'] ) : [];
								
								$params = array_merge( $default, $params ); 
								$params['slideshow_id'] = $slideshow_id;
								$params['slide_id'] = $slide_id;
								$json['params'] = $params; 
								$json['layers'] = $layerdata; 
								
							}else{
								/*
								$slider = $this->model->{$this->getFunc('get','LayerId')}($slideshow_id);
								
								$params = $slider['params'] ? unserialize( $slider['params'] ) : [];
								$params['params'] = $slider['params'];	
								$thumb = $this->model_tool_image->resize($params['slide_image'], 100, 100);
								$default = [
									'slide_id'  =>  0,
									'slide_title' => '',
									'slide_link' => '',
									'slide_usevideo' => '0',
									'slide_videoid' => '',
									'slide_videoplay' => '0',
									'fullwidth'=> '',
									'image' => $placeholder,
									'layersparams'=> '',
									'slide_transition' => 'random',
									'slide_delay'   => '0',
									'slide_status'  => 1,
									'slide_duration'    => '300',
									'slide_rotation'   => '0',
									'slide_enable_link' => 0,
									'slide_thumbnail' => '',
									'slide_slot' =>'7',	
									'slide_image'   => $placeholder,
									'params' => []
								];	
								
								$times = [];
								$layerdata = []; 	
								$slide = array_merge( $default, $params ); 
								unset($slide['id']);
								$layers = $slider['layersparams'] ? unserialize( $slider['layersparams'] ) : [];	
								foreach($layers as $_layers)
								{
									
									foreach($_layers as $layer)
									{
									$layerdata = [
										"layer_id"			=> "",
										"layer_type"		=> isset($layer['layer_type'])?$layer['layer_type']:'text',
										"layer_caption"		=> isset($layer['layer_caption'])? addslashes( str_replace("'",'"',html_entity_decode( $layer['layer_caption'] , ENT_QUOTES, 'UTF-8')) ):'',
										"layer_content"		=> isset($layer['layer_content'])? addslashes( str_replace("'",'"',html_entity_decode( $layer['layer_content'] , ENT_QUOTES, 'UTF-8')) ):'',
										"layer_image"		=> ($layer['layer_type']=='image')?html_entity_decode( $layer['layer_content'] , ENT_QUOTES, 'UTF-8'):"",
										"layer_class"		=> isset($layer['layer_class'])?$layer['layer_class']:"",
										"layer_easing"		=> isset($layer['layer_easing'])?$layer['layer_easing']:"",
										"layer_endanimation"=> isset($layer['layer_endanimation'])?$layer['layer_endanimation']:"",
										"layer_endeasing"	=> isset($layer['layer_endeasing'])?$layer['layer_endeasing']:"",
										"layer_endspeed"	=> isset($layer['layer_endspeed'])?$layer['layer_endspeed']:"",
										"layer_linktarget"	=> isset($layer['layer_linktarget'])?$layer['layer_linktarget']:"",
										"layer_link"		=> isset($layer['layer_link'])?$layer['layer_link']:"",
										"layer_linkstatus"	=> isset($layer['layer_linkstatus'])?$layer['layer_linkstatus']:"1",
										"layer_fontweight"	=> isset($layer['layer_fontweight'])?$layer['layer_fontweight']:"",
										"layer_fontsize"	=> isset($layer['layer_fontsize'])?$layer['layer_fontsize']:"",
										"layer_fontcolor"	=> isset($layer['layer_fontcolor'])?$layer['layer_fontcolor']:"",  //#000
										"layer_lineheight"	=> isset($layer['layer_lineheight'])?$layer['layer_lineheight']:"", // px - em vs
										"layer_animation" 	=> isset($layer['layer_animation'])?$layer['layer_animation']:"",
										"layer_left"		=> isset($layer['layer_left'])?$layer['layer_left']:"50",
										"layer_hpos"		=> isset($layer['layer_hpos'])?$layer['layer_hpos']:"",
										"layer_top"			=> isset($layer['layer_top'])?$layer['layer_top']:"50",
										"layer_vpos"		=> isset($layer['layer_vpos'])?$layer['layer_vpos']:"",
										"layer_width"		=> isset($layer['layer_width'])?$layer['layer_width']:"",
										"layer_speed"		=> isset($layer['layer_speed'])?$layer['layer_speed']:"",
										"layer_video_height"=> isset($layer['layer_video_height'])?$layer['layer_video_height']:"",
										"layer_video_id"	=> isset($layer['layer_video_id'])?$layer['layer_video_id']:"",
										"layer_video_thumb"	=> isset($layer['layer_video_thumb'])?$layer['layer_video_thumb']:"",
										"layer_video_type"	=> isset($layer['layer_video_type'])?$layer['layer_video_type']:"youtube",
										"layer_video_width"	=> isset($layer['layer_video_width'])?$layer['layer_video_width']:"",
										"layer_starttime"	=> isset($layer['layer_starttime'])?$layer['layer_starttime']:"",
										"layer_endtime"		=> isset($layer['layer_endtime'])?$layer['layer_endtime']:"0"
									];
									
									$json['layers'][] =  $layerdata ; 
									}
								}
								$json['params'] = $params; 
								
								*/
							}
							
							
							
							
							
						}
						else
						{
						$json = [
							'slide_title' => '',
							'slide_link' => '',
							'slide_usevideo' => '0',
							'slide_videoid' => '',
							'slide_videoplay' => '0',
							'fullwidth'=> '',
							'image' => 'slide_image.jpg',
							'layersparams'=> '',
							'slide_transition' => 'random',
							'slide_delay'   => '0',
							'slide_status'  => 1,
							'slide_duration'    => '300',
							'slide_rotation'   => '0',
							'slide_enable_link' => 0,
							'slide_thumbnail' => '',
							'slide_slot' =>'7',	
							'slide_image'   => 'slide_image.jpg',
							'slide_id'   => '',
							'layer_id'  => '',
							'params' => []
						];
					}
					}
					else //set
					{
					}
					break;
				
				case 'l'://layer
					break;
					
				case 'G'://Slayt Şov Slaytları
					
					if(isset($this->request->post['slideshow_id'])){
						$slideshowId = $this->request->post['slideshow_id'];
						$this->load->model('bytao/common');
						$languages = $this->model_bytao_common->getStoreLanguages();
						
						foreach($languages as $language){
							$slides =[];
							//$slides = $this->model->{$this->getFunc('get','sLayersByGroupId')}( $layersliderGroupId, $language['language_id'] );
							$slides = $this->model->{$this->getFunc('get','SlidesById')}( $slideshowId, $language['language_id'] );
							
							if($slides){
								$tData['slides'] = [];
								foreach($slides as $slide){
									$laparam =  unserialize($slide['params']);
									if (isset($laparam['id'])|| isset($slide['slideshow_id'])){
										$tData['slides'] [] = [
											'title' => $laparam['slider_title'],
											'slide_id' => isset($slide['slider_id'])?$slide['slider_id']:(isset($slide['slide_id'])?$slide['slide_id']:''),
											'image' => $this->model_tool_image->resize(by_move($slide['image']), 200, 120),
											'status' => $slide['status'],
										];
									}
								}
								$json['slides'][$language['language_id']][]= $this->load->view($this->cPth.'_thumb', $tData);	
							}
						}
					}
					break;
				case 'U':// Upgrade
					{
						
						$placeholder = $this->model_tool_image->resize('no_image.jpg', 100, 100);
					$sql = "SELECT * FROM `oc_slideshow`";
					$slides = $this->db->query($sql);
					foreach($slides->rows as $slide){
						
							$group_id = ($slide['slideshow_id']==14)?6:($slide['slideshow_id']==15?7:$slide['slideshow_id']);
							$ssql = "SELECT * FROM `oc_slideshow_layer`  WHERE group_id='".(int)$slide['slideshow_id'] ."' ORDER BY position";
							$layers = $this->db->query($ssql);
						
							foreach($layers->rows as $_layer){
								$params =isset($_layer['params'])?unserialize( $_layer['params'] ) : [];
								$params['slideshow_id'] = $group_id;
								$params['slideshow_title'] = $_layer['title'];
								$default = [
										'name' => $_layer['title'],
										'group_id' => $group_id,
										'layersparams'=> isset($_layer['layersparams'])?$_layer['layersparams']  : '',
										'status' => $_layer['status'],
										'sort_order' => $_layer['position'],
										'language_id' =>$_layer['language_id'],	
										'image'   => $_layer['image']?$_layer['image']:$placeholder,
										'params' => serialize($params)
									];
									
								$query = "INSERT INTO ".DB_PREFIX . "slideshow_slide ( `";
								$tmp = [];
								$vals = [];
								foreach( $default as $key => $value ){
									if($key!='slide_id'&& $key!='layers'){
										$tmp[] = $key;
										$vals[]=$this->db->escape($value);
									}
								}	
											
							 	$query .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
								$this->db->query( $query );
								$parent_id =  $this->db->getLastId();
								
								$layerParams = $_layer['layersparams'] ? unserialize( $_layer['layersparams'] ) : [];	
								
								foreach($layerParams as $_layers){
									if(is_array($_layers)){
										foreach($_layers as $sort_order => $layer){
												$layerdata = [
													"layer_id"			=> "",
													"layer_type"		=> isset($layer['layer_type'])?$layer['layer_type']:'text',
													"layer_caption"		=> isset($layer['layer_caption'])? addslashes( str_replace("'",'"',html_entity_decode( $layer['layer_caption'] , ENT_QUOTES, 'UTF-8')) ):'',
													"layer_content"		=> isset($layer['layer_content'])? addslashes( str_replace("'",'"',html_entity_decode( $layer['layer_content'] , ENT_QUOTES, 'UTF-8')) ):'',
													"layer_image"		=> ($layer['layer_type']=='image')?html_entity_decode( $layer['layer_content'] , ENT_QUOTES, 'UTF-8'):"",
													"layer_class"		=> isset($layer['layer_class'])?$layer['layer_class']:"",
													"layer_easing"		=> isset($layer['layer_easing'])?$layer['layer_easing']:"",
													"layer_endanimation"=> isset($layer['layer_endanimation'])?$layer['layer_endanimation']:"",
													"layer_endeasing"	=> isset($layer['layer_endeasing'])?$layer['layer_endeasing']:"",
													"layer_endspeed"	=> isset($layer['layer_endspeed'])?$layer['layer_endspeed']:"",
													"layer_linktarget"	=> isset($layer['layer_linktarget'])?$layer['layer_linktarget']:"",
													"layer_link"		=> isset($layer['layer_link'])?$layer['layer_link']:"",
													"layer_linkstatus"	=> isset($layer['layer_linkstatus'])?$layer['layer_linkstatus']:"1",
													"layer_fontweight"	=> isset($layer['layer_fontweight'])?$layer['layer_fontweight']:"",
													"layer_fontsize"	=> isset($layer['layer_fontsize'])?$layer['layer_fontsize']:"",
													"layer_fontcolor"	=> isset($layer['layer_fontcolor'])?$layer['layer_fontcolor']:"",  //#000
													"layer_lineheight"	=> isset($layer['layer_lineheight'])?$layer['layer_lineheight']:"", // px - em vs
													"layer_animation" 	=> isset($layer['layer_animation'])?$layer['layer_animation']:"",
													"layer_left"		=> isset($layer['layer_left'])?$layer['layer_left']:"50",
													"layer_hpos"		=> isset($layer['layer_hpos'])?$layer['layer_hpos']:"",
													"layer_top"			=> isset($layer['layer_top'])?$layer['layer_top']:"50",
													"layer_vpos"		=> isset($layer['layer_vpos'])?$layer['layer_vpos']:"",
													"layer_width"		=> isset($layer['layer_width'])?$layer['layer_width']:"",
													"layer_speed"		=> isset($layer['layer_speed'])?$layer['layer_speed']:"",
													"layer_video_height"=> isset($layer['layer_video_height'])?$layer['layer_video_height']:"",
													"layer_video_id"	=> isset($layer['layer_video_id'])?$layer['layer_video_id']:"",
													"layer_video_thumb"	=> isset($layer['layer_video_thumb'])?$layer['layer_video_thumb']:"",
													"layer_video_type"	=> isset($layer['layer_video_type'])?$layer['layer_video_type']:"youtube",
													"layer_video_width"	=> isset($layer['layer_video_width'])?$layer['layer_video_width']:"",
													"layer_starttime"	=> isset($layer['layer_starttime'])?$layer['layer_starttime']:"",
													"layer_endtime"		=> isset($layer['layer_endtime'])?$layer['layer_endtime']:"0"
												];
												$lDefault = [
													'title' => $_layer['title'],
													'parent_id' => $parent_id,
													'group_id' => $group_id,
													'language_id' =>$_layer['language_id'],
													'sort_order' => $sort_order,
													'status' => $_layer['status'],
													'image'   => $_layer['image']?$_layer['image']:$placeholder,
													'params' => serialize($layerdata)
												];
												
												$lquery = "INSERT INTO ".DB_PREFIX . "slideshow_slide_layer ( `";
												$ltmp = [];
												$lvals = [];
												foreach( $lDefault as $key => $value ){
													if($key!='slide_id'&& $key!='layers'){
														$ltmp[] = $key;
														$lvals[]=$this->db->escape($value);
													}
												}				
											 	$lquery .= implode("` , `",$ltmp)."`) VALUES ('".implode("','",$lvals)."') ";
												$this->db->query( $lquery );
											}
									}
								}
							}
						}
					
					
					/**/
					}
					$json['reload']='reload';
					break;
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
	public function lyclone():void {
		
		$json = [];
		$this->getML('M');
		
		
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$json['error'] = $this->language->get('error_permission');
		}
	 	
		if( isset($this->request->get['layer_id']) ){
			$lang = isset($this->request->get['lang'])?$this->request->get['lang']:1;
	 		
			$id = (int) $this->request->get['layer_id'];
			$slider = $this->model->{$this->getFunc('clone')}( $id );
			
			if(!isset($json['error']) && isset($slider['slideshow_id'])){
				$this->load->model('tool/image');
				if($slider['image']){
					$json['thumbnail']    = $this->model_tool_image->resize($slider['image'], 200, 115);;
				}
				$json['thumbnail']    = $this->model_tool_image->resize('no_image.jpg', 200, 115);
				$json['name'] = $slider['name'];
				$json['newlayer'] = $slider['slideshow_id'];
			}
		}
		
	 	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function movelang():void {
		
		$json = [];
		$this->getML('M');
		
		
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$json['error'] = $this->language->get('error_permission');
		}
	 	
		if( isset($this->request->get['layer_id']) ){
			$lang = isset($this->request->get['lang'])?$this->request->get['lang']:1;
			$id = (int) $this->request->get['layer_id'];
			
			$slider = $this->model->{$this->getFunc('move')}( $id,$lang );
		}
	 	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
	public function preview():void {

		if(isset($this->request->get['filter_name'])){
			$filter_name = $this->request->get['filter_name'];
		} else{
			$filter_name = '';
		}

		if(isset($this->request->get['filter_model'])){
			$filter_model = $this->request->get['filter_model'];
		} else{
			$filter_model = '';
		}

		if(isset($this->request->get['filter_price'])){
			$filter_price = $this->request->get['filter_price'];
		} else{
			$filter_price = '';
		}

		if(isset($this->request->get['filter_quantity'])){
			$filter_quantity = $this->request->get['filter_quantity'];
		} else{
			$filter_quantity = '';
		}
		
		if(isset($this->request->get['filter_item'])){
			$filter_addcategory = $filter_item = $this->request->get['filter_item'];
			
		} else{
			$filter_addcategory = $filter_item = '';
		}
		
		if(isset($this->request->get['filter_category'])){
			$filter_category = $this->request->get['filter_category'];
			$data['filter_category'] = $filter_category;
		} else{
			$data['filter_category'] = '';
			$filter_category = '';
		}
		
		
		if(isset($this->request->get['filter_status'])){
			$filter_status = $this->request->get['filter_status'];
		} else{
			$filter_status = '';
		}

		$url = '';

		$filter_data = [
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_item'	  => $filter_item,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'filter_category'   => $filter_category,
			'filter_addcategory'   => $filter_addcategory,
			'sort'            => 'p2c.sort_order',
			'order'           => 'ASC'
		];
		
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		if($filter_addcategory){
			$results = $this->model_catalog_product->getAddCatProducts($filter_data);
		}else{
			$results = $this->model_catalog_product->getProducts($filter_data);
		}
		
		//$this->log->write('Products:'.print_r($results ,true));
		
		if(isset($this->request->post['cellrow'])){
			$selrow = $this->request->post['cellrow'];
		} else if(isset($this->request->post['row'])){
			$selrow = $this->request->post['row'];
		}else{
			$selrow = 0;
		}
		
		$row = isset($this->request->post['row'])?$this->request->post['row']:0;
		$cells = isset($this->request->post['cells'])?$this->request->post['cells']:0;
		$rowOrders = $this->model_catalog_category->getCategoryRowOrder($filter_category,$filter_addcategory);
		// duzeltme
		if($row != 0 ){
			
			$uRows = explode(',',$rowOrders);
			
			if(count($uRows)>$row){
				$rowOrders='';
				$uRowCount=0;
				foreach($uRows as $uRow){
					$uRowCount++;
					$nRow = explode(':',$uRow);
					
					if(isset($nRow[1])){
						if($nRow[0] == $row){
							$rowOrders.= $uRowCount.':'.$cells.',';
						}else{
							$rowOrders.= isset($nRow[1]) ? $uRowCount.':'.$nRow[1].',':$uRowCount.':8,';
						}
					}
				}
			
			}else{
				$rowOrders.= $row.':8,';
			}

			//$this->log->write('ROW edited Orders:'.$rowOrders);
		}
		
		$products= [];
		$rows_sub = explode(',',$rowOrders);
		$rows = $rows_sub;
		
		//$results = $this->model_catalog_product->getProducts($filter_data);
		
		
		
		$partCount = 0;
		
		if(count($rows_sub)==1){
			$rowsCount = 1;
			$rowOrders.= $rowsCount.':8,';
		}else{
			$rowsCount = 0;
		}
		
		foreach($results as $result){
			if(is_file(DIR_IMAGE . $result['image'])){
				$image = $this->model_tool_image->resize($result['image'], 110, 110);
			} else{
				$image = $this->model_tool_image->resize('no_image.png', 110, 110);
			}

			$products[] = [
				'product_id' => $result['product_id'],
				'image'      => $image
			];
		}
		

		$rows = explode(',',$rowOrders);
		
		$this->model_catalog_category->setCategoryRowOrder($filter_category,$rowOrders,$filter_addcategory);
		
		$content['1'] = '<div class="row1 prow"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['2'] = '<div class="row2 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50 parent-row"><div class="h50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50 bos"></div></div></div>';
		$content['3'] = '<div class="row3 prow"><div class="y50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['4'] = '<div class="row4 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50"><div class="h50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div></div>';
		$content['5'] = '<div class="row5 prow"><div class="y50 pRel"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['6'] = '<div class="row6 prow"><div class="y25 bos"></div><div class="y25 bos"></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content[7] = '<div class="row7 prow"><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25 bos"></div><div class="y25 bos"></div></div>';
		$content['8'] = '<div class="row8 prow"><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['9'] = '<div class="row9 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['10'] = '<div class="row10 prow"><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['11'] = '<div class="row11 prow"><div class="y66" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div></div>';
		$content['12'] = '<div class="row12 prow"><div class="y33"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div><div class="y66" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';

		$pContent='';
		$lContainer='';
		$data['html']='';
		if(isset($products)){ 
			$pcount =0;
			$partCount =0;
			$nrow =1;
 			
			foreach($products as $product){ 
			
				if($pContent==''){
					$parts = explode(':',$rows[$nrow-1]);
					$type_order = isset($parts[1])?$parts[1]:8;
					$pContent = $content[$type_order];
					$cArrs = explode('::IMG::',$pContent);
				}
				else{
					$cArrs = explode('::IMG::',$pContent);
				}
			
				if(isset($cArrs[1])){
					$s=0;
					$pC='';
					foreach($cArrs as $cArr){
						if($s == 0){
							$pC.=str_replace('::pid::',$product['product_id'],$cArr).$product['image'];
						}else if($s == (count($cArrs)-1)){
							$pC.=$cArr;
						}else{
							$pC.= $cArr.'::IMG::';
						}
						$s++;
					}
					$pContent = $pC;
				
					$cArr = explode('::IMG::',$pContent);
					if(!isset($cArrs[1])){
						if($nrow == $selrow ){
							$active=' active';
						}else{
							$active='';
						}
						$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="'.$parts[1].'" data-row="'.$nrow.'" >'.$pContent.'</li>';
						$nrow++;
						$parts = explode(':',$rows[$nrow-1]);
						$type_order = isset($parts[1])?$parts[1]:8;
						$pContent =$content[$type_order];
					}
				}
				else{
					if($nrow == $selrow ){
						$active=' active';
					}else{
						$active='';
					}
					$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="'.$parts[1].'" data-row="'.$nrow.'" >'.$pContent.'</li>';
					$nrow++;
					$parts = explode(':',$rows[$nrow-1]);
					$type_order = isset($parts[1])?$parts[1]:8;
					$pContent =$content[$type_order];
					$cArrs = explode('::IMG::',$pContent);
					if(isset($cArrs[1])){
						$s=0;
						$pC='';
						foreach($cArrs as $cArr){
							if($s == 0){
								$pC.=str_replace('::pid::',$product['product_id'],$cArr).$product['image'];
							}else if($s == (count($cArrs)-1)){
								$pC.=$cArr;
							}else{
								$pC.= $cArr.'::IMG::';
							}
							$s++;
						}
						$pContent = $pC;
					}
				}
				$pcount++;
			
			}
		
			if($nrow == $selrow ){
				$active=' active';
			}else{
				$active='';
			}
			$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="8" data-row="'.$nrow.'" >'.str_replace('::IMG::','',$pContent).'</li>';
								
		
		
		
		}
		$json['s'] = $selrow;
		$json['v'] = $this->load->view('catalog/product_list_preview', $data);
		
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function prepos():void{

		
		$slideshow_id = isset($this->request->get['slideshow_id'])?$this->request->get['slideshow_id']:0;
		$filter_addcategory = isset($this->request->post['addcategory'])?$this->request->post['addcategory']:'';
		$filter_category = isset($this->request->post['category_id'])?$this->request->post['category_id']:'';
		$sc = isset($this->request->post['sc'])?$this->request->post['sc']:'';
		$th = isset($this->request->post['th'])?$this->request->post['th']:120;
		$row = isset($this->request->post['row'])?$this->request->post['row']:0;
		$cells = isset($this->request->post['cells'])?$this->request->post['cells']:0;
		
		$url = '';
		if($filter_addcategory){
			$filter_data = [
				'filter_status'   => 1,
				'filter_category'   => $filter_category,
				'filter_addcategory'   => $filter_addcategory,
				'filter_item'   => $filter_addcategory,
				'sort'            => 'p2c.sort_order',
				'order'           => 'ASC'
		];
		}else{
			$filter_data = [
				'filter_status'   => 1,
				'filter_category'   => $filter_category,
				'sort'            => 'p2c.sort_order',
				'order'           => 'ASC'
			];
		}
		
		$this->getML('M');		
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$_slide= $this->model->{$this->getFunc('get','Image')}($slideshow_id);
		
		$slide = $this->model_tool_image->resize($_slide,  $this->config->get('config_image_compare_width'), $this->config->get('config_image_compare_height'));
		$no_image = $this->model_tool_image->resize('no_image.png',  $this->config->get('config_image_compare_width'), $this->config->get('config_image_compare_height'));
		
		$rowOrders = $this->model_catalog_category->getCategoryRowOrder($filter_category,$filter_addcategory);
		
		if($filter_addcategory){
			$results = $this->model_catalog_product->getAddCatProducts($filter_data);
		}else{
			$results = $this->model_catalog_product->getProducts($filter_data);
		}	
		
		$pCount = ($sc)?count($results)+1:count($results);
		
		
		
		
		$content['1'] = '<div class="row1 prow"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['2'] = '<div class="row2 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50 parent-row"><div class="h50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50 bos"></div></div></div>';
		$content['3'] = '<div class="row3 prow"><div class="y50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['4'] = '<div class="row4 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50"><div class="h50 bos"></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div></div>';
		$content['5'] = '<div class="row5 prow"><div class="y50 pRel"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['6'] = '<div class="row6 prow"><div class="y25 bos"></div><div class="y25 bos"></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content[7] = '<div class="row7 prow"><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25 bos"></div><div class="y25 bos"></div></div>';
		$content['8'] = '<div class="row8 prow"><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['9'] = '<div class="row9 prow"><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['10'] = '<div class="row10 prow"><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		$content['11'] = '<div class="row11 prow"><div class="y66" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y33"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div></div>';
		$content['12'] = '<div class="row12 prow"><div class="y33"><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div><div class="y66" data-pid="::pid::" data-so="::so::"><img src="::IMG::" /></div></div>';
		
		
		
		$pc=0;
		$html ='';
		$uRows = explode(',',$rowOrders);
		if((trim($rowOrders)!='') && count($uRows)>0){
			foreach($uRows as $uRow){
				$nRow = explode(':',$uRow);
				if(isset($nRow[1])){
					if(isset($selrow)&& $nRow[0] == $row ) $active=' active'; else $active='';
					switch($nRow[1]){
						case 1: $pc +=1;break;
						case 2: $pc +=2;break;
						case 3: $pc +=1;break;
						case 4: $pc +=3;break;
						case 5: $pc +=3;break;
						case 6: $pc +=2;break;
						case 7: $pc +=2;break;
						case 8: $pc +=4;break;
						case 9: $pc +=2;break;
						case 10: $pc +=3;break;
						case 11: $pc +=3;break;
						case 12: $pc +=3;break;
						default: $pc +=4;
					}
					$html .= '<li class="sort-order-content '.$active.'" data-cells="'.$nRow[1].'" data-row="'.$nRow[1].'" >'.$content[$nRow[1]].'</li>';
				}
			}
			
			
			if( $pc < $pCount){
				$html .= '<li class="sort-order-content" data-cells="8" data-row="'.(count($uRows)+1).'" >'.$content[8].'</li>';
			}
			
			
		}
		else{
			$nR=1;
			for($pc = 1; $pc < $pCount; $pc +=4){
				$html .= '<li class="sort-order-content" data-cells="8" data-row="'.$nR.'" >'.$content[8].'</li>';
				$nR++;
			}

		}
		
		
		$so=1;
		$data['html']='';
		$nH = explode('::IMG::',$html);
		
		foreach($results as $result){
			
			if(is_file(DIR_IMAGE . $result['image'])){
				$image = $this->model_tool_image->resize($result['image'],  $this->config->get('config_image_compare_width'), $this->config->get('config_image_compare_height'));
			} else{
				$image = $no_image;
			}
					
			if(isset($nH[1])){
				if($sc != '' && ( $sc == $so)){
					$nH[0]= str_replace('::pid::', 0, $nH[0]);
					$nH[0]= str_replace('::so::',$so, $nH[0]);
					$data['html'].=$nH[0].$slide;
					array_shift($nH);
					$so++;
					$nH[0]= str_replace('::pid::', $result['product_id'], $nH[0]);
					$nH[0]= str_replace('::so::',$so, $nH[0]);
					$data['html'].=$nH[0].$image;
					array_shift($nH);
				}else{
					
					$nH[0]= str_replace(['::pid::','::so::'], [$result['product_id'],$so], $nH[0]);
					$data['html'].= $nH[0].$image;
					array_shift($nH);
				}
				$so++;
			}else{
				
			}
		}
		$data['html'].= $nH[0];
		
		$json['s'] = $row;
		$json['sc'] = $sc;
		$json['v'] = $this->load->view('catalog/product_list_preview', $data);
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function preload():void {
	
	}

	protected function validateForm():bool{
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if($this->error && !isset($this->error['warning'])){
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete():bool{
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy():bool {
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete():void {
		$json = [];

		if(isset($this->request->get['filter_name'])){
			
			$this->getML('M');

			if(isset($this->request->get['filter_name'])){
				$filter_name = $this->request->get['filter_name'];
			} else{
				$filter_name = '';
			}

			
			$filter_data = [
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => 5
			];

			$results = $this->model->{$this->getFunc('get','s')}($filter_data);

			foreach($results as $result){
				$option_data = [];

				$json[] = [
					$this->ID => $result[$this->ID],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delDir($target):void {
		if(is_dir($target)){
			$files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
			foreach( $files as $file ){
				$this->delDir( $file );      
			}
	         
			if(is_dir($target)){rmdir( $target );}
	         
		} elseif(is_file($target)){
			unlink( $target );  
		}
	}

	private function array_msort($array, $cols):array {
		$ret = [];
		$colarr = [];
		if(count($array)>0){
			foreach($cols as $col => $order){
				$colarr[$col] = [];
				foreach($array as $k => $row){ $colarr[$col]['_'.$k] = strtolower($row[$col]); }
			}
			$eval = 'array_multisort(';
			foreach($cols as $col => $order){
				$eval .= '$colarr[\''.$col.'\'],'.$order.',';
			}
			$eval = substr($eval,0,-1).');';
			eval($eval);
		   
			foreach($colarr as $col => $arr){
				foreach($arr as $k => $v){
					$k = substr($k,1);
					if(!isset($ret[$k])) $ret[$k] = $array[$k];
					$ret[$k][$col] = $array[$k][$col];
				}
			}
	    
		}
		return $ret;
	}
	
	public function savedata ():void {
		$json = [];
		$this->getML('ML');
		if(!$this->user->hasPermission('modify', $this->cPth)){
			$this->error['warning'] = $this->language->get('error_permission');
			die(  $this->language->get('error_permission') );
		}


		if( empty($this->request->post['slide_title']) ){
			$json['error']  =  $this->language->get('error_missing_title');	
		}
		if( $this->request->post ){
			$slider_id = $this->request->post['slide_id'];
			$layersparams = new \stdClass();
			$layersparams->layers = [];
			
			$params = serialize( $this->request->post );
			$languageId = $this->request->post['slide_language_id'];
			unset( $this->request->post['slide_language_id'] );
			
			if( isset($this->request->post['layers'])  && !empty($this->request->post['layers']) ){
				
				$layersparams = new \stdClass();
				//$start_times 		 	= $this->request->post['layer_start_time'];
				//$end_times 		 	= $this->request->post['layer_end_time'];
				//$tmp 			= $this->request->post['layers'];	

				$layers = $this->request->post['layers'];
				$i=1;
				foreach(  $layers as $key => $value ){
					//$value['time_start'] = $start_times[$i];
					//$value['time_end'] = $end_times[$i];
					$times[$i] = $value;
					$i++;
				}

				$k = 1;
				foreach( $times as $key => $value ){
					if( is_array($times) ){
						$value['layer_id'] = $k+1;
						$layersparams->layers[$k] = $value;
						$k++;
					}
				}
			
				unset( $this->request->post['layer_time'] );
				unset( $this->request->post['layers'] );


				$params = serialize( $this->request->post ); 
			}
			
			if(!isset($json['error'])){
				
				$data = [
					'layersparams' => serialize($layersparams),
					'layers' 	   => $layersparams,
					'group_id'     => $this->request->post['slide_group_id'],
					'name'   	   => $this->request->post['slide_title'],
					'slide_id'	   => $this->request->post['slide_id'],
					'image'        => $this->request->post['slide_image'],
					'params'	   =>  $params,	
					'status'       => $this->request->post['slide_status'],
					'language_id'  => $languageId,
				];
				
				$sliderId = $this->model->{$this->getFunc('save','Data')}( $data );
				$this->load->model('tool/image');
				$th = isset($this->request->post['th'])?$this->request->post['th']:120;
				
				$json['thumbnail']    = $this->model_tool_image->resize('no_image.jpg', 200, $th);
				$json['slide_id']	=	$sliderId;
				$json['slide_title']=$this->request->post['slide_title'];
				$json['slide_status']=$this->request->post['slide_status'];
				
				if($this->request->post['slide_image']){
					$json['thumbnail']    = $this->model_tool_image->resize($this->request->post['slide_image'], 200, $th);
					if($json['slide_status'] && $this->request->post['slide_image']){
						$this->model->{$this->getFunc('update','GroupImage')}( $this->request->post['slide_group_id'],$this->request->post['slide_image'] );
					}
				}
				
				if($this->request->post['slide_id'] ==0 ){
					$json['newlayer']    = $sliderId;
					$json['text_added_slider']    = $this->language->get('text_added_slider');
				}else{
					$json['updatelayer']    = $sliderId;
					$json['text_updated_slider']    = $this->language->get('text_updated_slider');
				}
				$json['success'] = $this->language->get('text_success');
			 	
			}
			
		}else{
			$json['error']  = $this->language->get('text_could_not_save');
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	function getwidget(array $ndata = []) {
		$this->getML('ML');
		$json= [];
		
		
		if($this->request->get['type']){
			
			
		}else{
		
			
		}
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
					'item_id' 	=> $result['slideshow_id'],
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