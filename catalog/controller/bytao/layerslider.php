<?php
namespace Opencart\Catalog\Controller\Bytao;
class Layerslider extends \Opencart\System\Engine\Controller
{

	private $version = '1.0.0';
	private $cPth = 'bytao/layerslider';
	private $C = 'layerslider';
	private $ID = 'layerslider_id';
	private $model ;
	private $LYMD = [] ;
	private $Cr = 0 ;

	private function fill():void
	{
		$this->LYMD = [
			'0'  => ['row','w3-row','by-row'],
			'00' => ['container','w3-container','by-container'],
			'11' => ['col-md-12','w3-col','col-md-12'],
			'12' => ['col-md-6','w3-half','col-md-6'],
			'23' => ['col-md-8','w3-twothird','col-md-8'],
			'13' => ['col-md-4','w3-third','col-md-4'],
			'14' => ['col-md-3','w3-quarter','col-md-3'],
			'34' => ['col-md-9','w3-threequarter','col-md-9'],
			'56' => ['col-md-10','w3-col','col-md-10'],
		];
		$this->Cr = $this->config->get('config_store_core');
	}

	private function getFunc($f = '',$addi = ''):string
	{
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}

	private function getML($ML = ''):void
	{
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->
			{
				'model_'.str_replace('/','_',$this->cPth)
			};break;
			default:
		}
	}

	public function index(array $lData = []):string
	{
		$this->load->model('tool/image');
		$this->getML('M');
		by_cdn('image/blank.gif');
		
		$returnData = '';
		$slideData=[];
		if(isset($lData['layerslider_group_id'])|| isset($lData['ids'])){
			
			$content[1] = '<div class="li-row v1 "><div class="row-1-1 y100">||</div></div>';
			$content[2] = '<div class="li-row v9"><div class="row-2-1 y50">||</div><div class="row-2-2 y50">||</div></div>'; 
			$content[3] = '<div class="li-row v10"><div class="row-3-1 y33">||</div><div class="row-3-2 y33">||</div><div class="row-3-3 y33">||</div></div>';
			$content[4] = '<div class="li-row v8"><div class="row-4-1 y25">||</div><div class="row-4-2 y25">||</div><div class="row-4-3 y25">||</div><div class="row-4-4 y25">||</div></div>';
			
			
			$IDS         = isset($lData['ids'])?explode(',',$lData['ids']):(isset($lData['layerslider_group_id'])?explode(',',$lData['layerslider_group_id']):[]);
			$selContent  = $content[count($IDS)];
			$productHTML = '';
			$nH          = explode('||',$selContent);
			$NH =0;
			
			foreach ($IDS as $ID) {

				$group_info = $this->model->{$this->getFunc('get','GroupById')}((int)$ID);
				
				if($group_info){
					
					$returnData = '';
					if( isset($group_info['params']['fullwidth']) && (!empty($group_info['params']['fullwidth']) || $group_info['params']['fullwidth'] == 'boxed') ){
						$group_info['params']['image_cropping'] = false;
					}
					$data['sliderParams'] = $group_info['params'];
					$data['module'] = $ID;
					
					
					$sliders = $this->model->{$this->getFunc('get','sByGroupId')}($ID);
					
					foreach( $sliders as $key => $slider )
					{
						$_slider = [];
						$_slider['params'] = $slider["params"]?unserialize( $slider["params"]):[];
						$_slider['layersparams'] = $slider["layersparams"]?unserialize($slider["layersparams"]):[];
						
						if(isset($group_info['params']['image_cropping']) && $group_info['params']['image_cropping']!="0"){
							$_slider['main_image'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}(by_move($_slider['params']['slider_image']), $group_info['params']['width'],$group_info['params']['height'],'a');
							
						}else{
							$_slider['main_image'] = HTTPS_IMAGE.by_move($_slider['params']['slider_image']);
						}
						
						
						if( isset($group_info['params']['image_cropping']) && $group_info['params']['image_cropping']!="0"){
							
							if( isset($slider['params']['slider_thumbnail'])){
								$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}( by_move($slider['params']['slider_thumbnail']), $group_info['params']['thumbnail_width'],$group_info['params']['thumbnail_height'],'a');
							}else{
								$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}(by_move($slider['image']), $group_info['params']['thumbnail_width'],$group_info['params']['thumbnail_height'],'a');
							}
							
						} else {
							
							if( $_slider['params']['slider_image'] ){
								$_slider['thumbnail'] = HTTPS_IMAGE.by_move($_slider['params']['slider_image']);
							}else{
								$_slider['thumbnail'] = HTTPS_IMAGE.by_move($_slider['image']);
							}
							
						}
						
						$_slider['layers'] = $_slider['layersparams']->layers ? $this->layers($_slider['layersparams']->layers):[];
						$slideData[$key] = $_slider;
					}
					
					$data['sliders'] = $slideData;
					
					if($group_info['params']['type'] == '1'){
						
						if(isset($group_info['params']['fullwidth'])){
							$data['master'] = $group_info['params'];
						}
						
						$this->document->addStyle('cdn/'.by_cdn('js/masterslider/style/masterslider.css'));
						$this->document->addStyle('cdn/'.by_cdn('js/masterslider/skins/light-4/style.css'));
						$this->document->addScript('cdn/'.by_cdn('js/masterslider/jquery.easing.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/masterslider/masterslider.min.js'));
						
						$returnData = $this->load->view($this->cPth.'_master', $data);
					}
					else
					{
						//$this->document->addScript('cdn / js / bootstrap.min.js','footer');
						$this->document->addScript('cdn/'.by_cdn('js/byd.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.tools.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.revolution.min.js'));
						
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.actions.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.carousel.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.kenburn.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.layeranimation.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.migration.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.navigation.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.parallax.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.slideanims.min.js'));
						$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.video.min.js'));
						$returnData = $this->load->view($this->cPth.'_rev', $data);
					}
					
				}
				
				$productHTML .= $nH[$NH] . $returnData;
				$NH++;
				
			}
			$productHTML .= isset($nH[$NH])? $nH[$NH]:'';
			return $productHTML;
			
			
		}else{
			$layer_slider_group_id = 0;
		}
		$this->log->write('layer_slider_group_id:'.print_r($layer_slider_group_id,TRUE));
		$data['module'] = $layer_slider_group_id;
		$data['url'] = $url  = $this->config->get('config_url');
		$group_info = $this->model->{$this->getFunc('get','GroupById')}((int)$layer_slider_group_id);
		
		if($group_info){

			if( isset($group_info['params']['fullwidth']) && (!empty($group_info['params']['fullwidth']) || $group_info['params']['fullwidth'] == 'boxed') ){
				$group_info['params']['image_cropping'] = false;
			}
			$data['sliderParams'] = $group_info['params'];
			
			
			$sliders = $this->model->{$this->getFunc('get','sByGroupId')}($layer_slider_group_id);
			foreach( $sliders as $key => $slider ){
				
				$_slider = [];
				$_slider['params'] = $slider["params"]?unserialize( $slider["params"]):[];
				$_slider['layersparams'] = $slider["layersparams"]?unserialize($slider["layersparams"]):[];

				if(isset($group_info['params']['image_cropping'])){
					$_slider['main_image'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}(by_move($_slider['params']['slider_image']), $group_info['params']['width'],$group_info['params']['height'],'a');
				}else{
					$_slider['main_image'] = HTTPS_IMAGE.by_move($_slider['params']['slider_image']);
				}
				
				if( isset($group_info['params']['image_cropping'])){
					
					if( isset($slider['params']['slider_thumbnail'])){
						$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}( by_move($slider['params']['slider_thumbnail']), $group_info['params']['thumbnail_width'],$group_info['params']['thumbnail_height'],'a');
					}else{
						$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}(by_move($slider['image']), $group_info['params']['thumbnail_width'],$group_info['params']['thumbnail_height'],'a');
					}
					
				} else {
					
					if( $slider['params']['slider_image'] ){
						$_slider['thumbnail'] = HTTPS_IMAGE.by_move($slider['params']['slider_image']);
					}else{
						$_slider['thumbnail'] = HTTPS_IMAGE.by_move($slider['image']);
					}
					
				}
				
				$_slider['layers'] = $_slider['layersparams']->layers ? $this->layers($_slider['layersparams']->layers):[];
				$slideData[$key] = $_slider;
			}
			
			$data['sliders'] = $slideData;
			
			if($group_info['params']['type'] == '1'){
				
				$this->document->addStyle('cdn/'.by_cdn('js/masterslider/style/masterslider.css'));
				$this->document->addStyle('cdn/'.by_cdn('js/masterslider/skins/light-4/style.css'));
				$this->document->addScript('cdn/'.by_cdn('js/masterslider/jquery.easing.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/masterslider/masterslider.min.js'));
				
				$returnData = $this->load->view($this->cPth.'_master', $data);
			}
			else
			{
				//$this->document->addScript('cdn / js / bootstrap.min.js','footer');
				$this->document->addScript('cdn/'.by_cdn('js/byd.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.tools.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.revolution.min.js'));
				
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.actions.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.carousel.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.kenburn.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.layeranimation.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.migration.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.navigation.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.parallax.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.slideanims.min.js'));
				$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.video.min.js'));
				$returnData = $this->load->view($this->cPth.'_rev', $data);
			}
			
		}
		return $returnData;
	}

	public function widget(array $lData = []):array
	{
		$this->load->model('tool/image');
		$this->getML('M');
		$returnData = [];
		
		foreach($lData as $group){
			
			if(isset($group['layerslider_group_id'])){
				$layer_slider_group_id = (int)$group['layerslider_group_id'];
			}
			else
			{
				$layer_slider_group_id = 0;
			}
			$this->log->write('layer_slider_group_id:'.print_r($layer_slider_group_id,TRUE));
			$group_info = $this->model->{$this->getFunc('get','GroupById')}((int)$layer_slider_group_id);
			if($group_info){
				
				$data['url'] = $url     = $this->config->get('config_url');
				
				$data['sliderParams'] = $group_info['params'];

				if( isset($sliderGroup['params']['fullwidth']) && (!empty($sliderGroup['params']['fullwidth']) || $sliderGroup['params']['fullwidth'] == 'boxed') ){
					$sliderGroup['params']['image_cropping'] = false;
				}
				
				$sliders = $this->model->{$this->getFunc('get','sByGroupId')}((int)$layer_slider_group_id);
				
				foreach( $sliders as $key => $slider ){
					$_slider = [];
					$_slider['params'] = $slider["params"]?unserialize( $slider["params"]):[];
					$_slider['layersparams'] = $slider["layersparams"]?unserialize($slider["layersparams"]):[];

					if( isset($sliderGroup['params']['image_cropping'])){
						$_slider['main_image'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}(by_move($slider['image']), $sliderGroup['params']['width'],$sliderGroup['params']['height'],'a');
					}
					else
					{
						$_slider['main_image'] = HTTPS_IMAGE.by_move($slider['image']);
					}
					if( isset($sliderGroup['params']['image_cropping'])){

						if( $slider['params']['slider_thumbnail'] ){
							$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}( by_move($_slider['params']['slider_thumbnail']), $sliderGroup['params']['thumbnail_width'],$sliderGroup['params']['thumbnail_height'],'a');
						}
						else
						{
							$_slider['thumbnail'] = HTTPS_IMAGE.$this->model->{$this->getFunc('resize')}($slider['image'], $sliderGroup['params']['thumbnail_width'],$sliderGroup['params']['thumbnail_height'],'a');
						}
					}
					else
					{
						if(isset($slider['params']['slider_image'] )){
							$_slider['thumbnail'] = HTTPS_IMAGE.by_move($slider['params']['slider_image']);
						}
						else
						{

							$_slider['thumbnail'] = HTTPS_IMAGE.by_move($slider['image']);
						}

					}

					
					$_slider['layers'] = $_slider['layersparams']->layers ? $this->layers($_slider['layersparams']->layers):[];
					$sliders[$key] = $_slider;
				}


				$data['sliders'] = $sliders;
				$data['module'] = $layer_slider_group_id;
				
				if($group['type'] == '1'){
					
					$this->document->addStyle('cdn/'.by_cdn('js/masterslider/style/masterslider.css'));
					$this->document->addStyle('cdn/'.by_cdn('js/masterslider/skins/light-4/style.css'));
					$this->document->addScript('cdn/'.by_cdn('js/masterslider/jquery.easing.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/masterslider/masterslider.min.js'));
				
					$returnData[$group['viewpos']] = $this->load->view($this->cPth.'_master', $data);
				}
				else
				{
					
					$this->document->addScript('cdn/'.by_cdn('js/byd.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.tools.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/jquery.themepunch.revolution.min.js'));
					
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.actions.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.carousel.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.kenburn.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.layeranimation.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.migration.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.navigation.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.parallax.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.slideanims.min.js'));
					$this->document->addScript('cdn/'.by_cdn('js/layerslider/revolution.extension.video.min.js'));
					

					$returnData[$group['viewpos']] = $this->load->view($this->cPth.'_rev', $data);
				}
			}

		}

		return $returnData;
	}
	
	private function layers($layers):array
	{
		$layers_data = [];
		
		$url  = $this->config->get('config_url');
		foreach($layers as $layer){
			$layer_image ='';
			if($layer['layer_type']=='image'){
				if(!$layer['layer_image']){
					$layer_image =HTTPS_IMAGE.by_move($layer['layer_content']);
				}else{
					$layer_image =HTTPS_IMAGE.by_move($layer['layer_image']);
				}
			}
			
			
			$layers_data[] = [
				"layer_id"			=> $layer['layer_id'],
				"layer_type"		=> $layer['layer_type'],
				"layer_caption"		=> $layer['layer_caption'],
				"layer_content"		=> $layer['layer_content'],
				"layer_image"		=> $layer_image,
				"layer_class"		=> $layer['layer_class'],
				"layer_easing"		=> isset($layer['layer_easing'])?$layer['layer_easing']:'',
				"layer_endanimation"=> isset($layer['layer_endanimation'])?$layer['layer_endanimation']:'',
				"layer_endeasing"	=> isset($layer['layer_endeasing'])?$layer['layer_endeasing']:'',
				"layer_endspeed"	=> isset($layer['layer_endspeed'])?$layer['layer_endspeed']:'',
				"layer_linktarget"	=> isset($layer['layer_linktarget'])?$layer['layer_linktarget']:'',
				"layer_link"		=> isset($layer['layer_link'])?$layer['layer_link']:'',
				"layer_linkstatus"	=> isset($layer['layer_linkstatus'])?$layer['layer_linkstatus']:'',
				"layer_fontweight"	=> isset($layer['layer_fontweight'])?$layer['layer_fontweight']:'',
				"layer_fontsize"	=> isset($layer['layer_fontsize'])?$layer['layer_fontsize']:'',
				"layer_fontcolor"	=> isset($layer['layer_fontcolor'])?$layer['layer_fontcolor']:'',
				"layer_lineheight"	=> isset($layer['layer_lineheight'])?$layer['layer_lineheight']:'', 
				"layer_animation" 	=> isset($layer['layer_animation'])?$layer['layer_animation']:'',
				"layer_left"		=> isset($layer['layer_left'])?(int)$layer['layer_left']:'',
				"layer_hpos"		=> isset($layer['layer_hpos'])?substr($layer['layer_hpos'],0,1):'',
				"layer_top"			=> isset($layer['layer_top'])?(int)$layer['layer_top']:'',
				"layer_vpos"		=> isset($layer['layer_vpos'])?substr($layer['layer_vpos'],0,1):'',
				"layer_pos"			=> isset($layer['layer_vpos'])?substr($layer['layer_vpos'],0,1).substr($layer['layer_hpos'],0,1):'',
				"layer_width"		=> isset($layer['layer_width'])?$layer['layer_width']:'',
				"layer_speed"		=> isset($layer['layer_speed'])?$layer['layer_speed']:'',
				"layer_video_height"=> isset($layer['layer_video_height'])?$layer['layer_video_height']:'',
				"layer_video_id"	=> isset($layer['layer_video_id'])?$layer['layer_video_id']:'',
				"layer_video_thumb"	=> isset($layer['layer_video_thumb'])?$layer['layer_video_thumb']:'',
				"layer_video_type"	=> isset($layer['layer_video_type'])?$layer['layer_video_type']:'',
				"layer_video_width"	=> isset($layer['layer_video_width'])?$layer['layer_video_width']:'',
				"layer_starttime"	=> isset($layer['layer_starttime'])?$layer['layer_starttime']:'',
				"layer_endtime"		=> isset($layer['layer_endtime'])?$layer['layer_endtime']:''
				];
		}
		return $layers_data;
	}
	
	
}
