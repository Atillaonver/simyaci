<?php
namespace Opencart\Catalog\Controller\Bytao;
class Tools extends \Opencart\System\Engine\Controller {
	public function index():string {
		
		return $this->load->view('bytao/footer', $data);
	
	}
	
	public function rowBender($rows):string {
		$added=[];
		
		$row_content='';
		$this->load->model('tool/image');
		
		if($rows){
			foreach($rows as $ind => $ROW){
				$Divs = explode('_',$ROW['row_cells']);
				if($ROW['cols']){
					$row_content .='<section class="'.($ROW['row_class']?$ROW['row_class']:'').'"'.($ROW['image']?' data-image="'.HTTP_IMAGE.$ROW['image'].'"':'').' id="row'.$ind.'" >';
					$colC=0;
					
					$nclss=0;
					if(count($ROW['cols'])>1){
						$pos = strpos($ROW['row_class'], 'hasContainer');
						if ($pos === false) {
							//$row_content .='<div class="container">';
						}else{
							$row_content .='<div class="container">';
						}
					}
					$pos = strpos($ROW['row_class'], 'no-rclss');
					if ($pos === false) {
						$nclss = 0;
					}else {
						$nclss =1;
					}
					foreach($ROW['cols'] as $COLS){
						$d1 = substr($Divs[$colC],0,1);
						$d2 = substr($Divs[$colC],1,1);
						if((int)$d2){
							$resp = ( 12 / (int)$d2 )* $d1;
						}else{
							$resp ='12';
						}
						
						$row_content .= '<div '.(!$nclss?'class="col-md-'.$resp.'"':'').' id="mod'.$COLS['col_type'].'">';
						switch($COLS['col_type']){
							case 1:// Text-image-movie(zengin editor)
								{
									if(trim($ROW['row_class'])!='control'){
										$row_content .= html_entity_decode($COLS['col_content'], ENT_QUOTES, 'UTF-8');
									}else{
										$row_content .= $this->load->controller($COLS['col_content']);
									}
								}
								break;
							case 2:// Basit Yazı
								{
									
								}
								break;
							case 3:// Basit Resim
								{
									
								}
								break;
							case 4:// Resim Koleksiyon
								{
									$images =$this->model_bytao_home->getHomeRowColImages($COLS['home_row_col_id']);
									$row_content .= '<section class="qua_section campaigns_widget">';
									$row_content .= '<div class="container text-center">';
									$row_content .= '<div class="qua_icon_boxes row text-center">';
							        $row_content .= '<div class="qua_4_slider">';
							        if($images){ 
							            foreach($images as $image){ 
							                $row_content .= '<div class="qua_icon_box qua_team_box">';
							                $row_content .= '<div class="qua_icon_box_photo">';
							                
							                $row_content .= '<img src="'.HTTP_IMAGE.$image['image'].'" alt="'.$image['col_image_id'].'">';
							                
							                $row_content .= '</div>';
							                $row_content .= '</div>'; 
										} 
									} 
							        $row_content .= '</div>';
							        $row_content .= '</div>';
							        $row_content .= '</div>';
									$row_content .= '</section>';
								}
								break;
							case 5:// Youtube Video
								{
									
								}
								break;
							case 6:// Slider
								{
									$parts = explode('::', $COLS['col_content']);
									$part = explode(':', $parts[1]);
									if (isset($part[0])) {
										if(!isset($added['catalog/view/javascript/bytao/layerslider/jquery.themepunch.plugins.min.js'])){
											$this->document->addScript('catalog/view/javascript/bytao/layerslider/jquery.themepunch.plugins.min.js');
											$added['catalog/view/javascript/bytao/layerslider/jquery.themepunch.plugins.min.js'] = 1;
										}
										if(!isset($added['catalog/view/javascript/bytao/layerslider/jquery.themepunch.revolution.min.js'])){
											$this->document->addScript('catalog/view/javascript/bytao/layerslider/jquery.themepunch.revolution.min.js');
											$added['catalog/view/javascript/bytao/layerslider/jquery.themepunch.revolution.min.js'] = 1;
										}
										$module_data = $this->load->controller('bytao/layerslider',['layerslider_group_id'=>$part[0]]);
										
										if ($module_data) {
											$row_content .= $module_data;
										}
									}
								}
								break;
							case 7:// Carousel
								{
									
								}
								break;
							case 8:// Modul
								{
									$this->load->model('setting/module');
									$parts = explode('::', $COLS['col_content']);
									$party = explode(':', $parts[1]);
									$part = explode('.', $party[0]);
									
									if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
										$module_data = $this->load->controller('extension/module/' . $part[0]);
										if ($module_data) {
											$row_content .= $module_data;
										}
									}
									if (isset($part[1])) {
										
										$setting_info = $this->model_setting_module->getModule($part[1]);
										
										if ($setting_info && $setting_info['status']) {
											$output = $this->load->controller('extension/module/' . $part[0], $setting_info);

											if ($output) {
												$row_content .= $output;
											}
										}
									}
								}
								break;
							case 9:// Yönetim
								{
									//bytao/blog/lastest-400x400*4:blog
									//KONTROL::bytao/award.8:award
									$parts = explode('::', $COLS['col_content']);
									$part = explode(':', $parts[1]);
									$party = explode('.', $part[0]);
									
									if (isset($party[0])) {
										switch($party[0]){
											case 'bytao/pslide':
												$id = $party[1];
												$ctrl = $party[0];
												
												$ctrl_data = $this->load->controller($ctrl,['item_id'=>$party[1]]);
												if ($ctrl_data) {
													$row_content .= $ctrl_data;
												}
												break;
											default:
												$p = explode('/',$part[0]);
												if(isset($p[2])){
													$a= explode('-',$p[2]);
													$b= explode('*',$a[1]);
													$c= explode('x',$b[0]);
													$transData=[
														'limit'			=>	$b[1],
														'thumb_width'	=>	$c[0],
														'thumb_height'	=>	$c[1]
													];
													$ctrl = $p[0].'/'.$p[1].'/widget_'.$a[0];
													
													$ctrl_data = $this->load->controller($ctrl,$transData);
													
													if ($ctrl_data) {
														$row_content .= $ctrl_data;
													}
												}
												else
												{
													if(isset($party[1])){
														$transData=[
															'limit'			=>	$party[1]
														];
														$ctrl = $party[0].'/widget';
														$ctrl_data = $this->load->controller($ctrl,$transData);
														if ($ctrl_data) {
															$row_content .= $ctrl_data;
														}
													}
												}
										}
									}
								}
								break;
							default:
								break;
						}
						$row_content .='</div>';
					}
					if(count($ROW['cols'])>1){
						$pos = strpos($ROW['row_class'], 'hasContainer');
						if ($pos === false) {
							//$row_content .='<div class="container">';
						}else{
							$row_content .='</div>';
						}
					}
					$row_content .='</section>';
				}
			}
		}
		return $row_content;
	}

}
