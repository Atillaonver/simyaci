<?php
namespace Opencart\Catalog\Controller\Bytao;
class Footer extends \Opencart\System\Engine\Controller {
	public function index():string {
		$this->load->language('bytao/footer');
		$this->load->model('bytao/page');
		$this->load->model('bytao/footer');
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$server = HTTP_SERVER;
		
		$footer_info = $this->model_bytao_footer->getFooter();
		if($footer_info){
			$rows = $this->model_bytao_footer->getFooterRows();
			$data['rows'] = $this->rowBender($rows);
		}else{
			
		
			$data['pages'] = array();
			foreach ($this->model_bytao_page->getPages() as $result) 								{
			if ($result['bottom']) {
				$data['pages'][] = array(
					'bottom' => $result['bottom'],
					'title' => $result['title'],
					'href'  => $this->url->link('bytao/page', 'page_id=' . $result['page_id'])
				);
			}
		}
			
			$this->load->model('catalog/information');

			$data['informations'] = array();

			foreach ($this->model_catalog_information->getInformations() as $result) 							{
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}
			
			$data['categories'] = array();
			
			/*
			$this->load->model('bytao/common');
			foreach ($this->model_bytao_common->getBottomCategories() as $result) {
				$data['categories'][] = array(
					'title' => $result['name'],
					'href'  => $this->url->link('product/category', 'category_id=' . $result['category_id'])
				);
			}
			*/
			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
			} else {
				$data['logo'] = '';
			}
			$data['text_follow'] = $this->language->get('text_follow');
			$data['text_about'] = $this->language->get('text_about');
			$data['text_blog'] = $this->language->get('text_blog');
			$data['text_faq'] = $this->language->get('text_faq');
			$data['text_analysis'] = $this->language->get('text_analysis');

			$data['blog'] = $this->url->link('bytao/blog');
			$data['sss'] = $this->url->link('bytao/faq');
			$data['awards'] = $this->url->link('bytao/award');
			$data['analysis'] = $this->url->link('bytao/analysis', '', true);
			
			$data['treemenu'] ='';
			$data['telephone'] = $this->config->get('config_telephone');
			$data['store'] = $this->config->get('config_name');
			$data['address'] = nl2br($this->config->get('config_address'));
			$data['geocode'] = $this->config->get('config_geocode');
			$data['email'] = $this->config->get('config_email');
			$data['newsletter_widget'] = $this->load->controller('bytao/newsletter');
			
			$data['contact'] = $this->url->link('information/contact');
			$data['return'] = $this->url->link('account/return/add', '', true);
			$data['sitemap'] = $this->url->link('information/sitemap');
			$data['tracking'] = $this->url->link('information/tracking');
			$data['manufacturer'] = $this->url->link('product/manufacturer');
			$data['voucher'] = $this->url->link('account/voucher', '', true);
			$data['affiliate'] = $this->url->link('affiliate/login', '', true);
			$data['special'] = $this->url->link('product/special');
			$data['account'] = $this->url->link('account/account', '', true);
			$data['order'] = $this->url->link('account/order', '', true);
			$data['wishlist'] = $this->url->link('account/wishlist', '', true);
			$data['newsletter'] = $this->url->link('account/newsletter', '', true);
			$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));
		}
		
		if(isset($this->session->data['accepte'])) {
			$data['accepte'] = 1;
		}else{
			$data['accepte'] = 0;
		}
		
		
		

		
		
		

		
		
		$data['accepte'] = $this->load->controller('bytao/accepte');
		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		$data['styles'] = $this->document->getStyles('footer');
		
		return $this->load->view('bytao/footer', $data);
	
	}
	
	private function rowBender($rows):string {
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
