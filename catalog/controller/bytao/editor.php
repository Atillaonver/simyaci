<?php
namespace Opencart\Catalog\Controller\Bytao;
class Editor extends \Opencart\System\Engine\Controller
{
	private $version = '1.0.0';
	private $cPth = 'bytao/editor';
	private $C = 'row';
	private $ID = 'row_id';
	private $model ;
	private $LYMD = [] ;
	private $Cr = 0 ;
	private $ids = ['product_id','product_code','path','manufacturer_id','prod_id','prod_cat_id','information_id','project_id'];

	private function getFunc($f='',$addi=''):string {
		//return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
		return $f;
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
		$this->fill();
	}
	
	private function fill():void
	{
		$this->LYMD = [
			'0'  => ['row','w3-row col-container','by-row'],
			'00' => ['container','w3-container','by-container'],
			'11' => ['col-md-12','w3-col','col-md-12'],
			'12' => ['col-md-6','w3-half col','col-md-6'],
			'23' => ['col-md-8','w3-twothird','col-md-8'],
			'13' => ['col-md-4','w3-third','col-md-4'],
			'14' => ['col-md-3','w3-quarter','col-md-3'],
			'34' => ['col-md-9','w3-threequarter','col-md-9'],
			'56' => ['col-md-10','w3-col','col-md-10'],
		];
		$this->Cr = $this->config->get('config_store_core');
	}

	public function index($hData): string
	{
		return $this->load->view('bytao/header', $data);
	}

	public function rowBender(array $info = []):string
	{
		$this->getML('M');
		
		$added   = [];
		$ICERIK = '';
		$this->load->model('tool/image');
		$this->load->model('bytao/common');
		$rows = $this->model->getRows($info);
		$isLogged = $this->customer->isLogged()?1:0;
		
		if($rows)
		{
			foreach($rows as $ind => $ROW)
			{
				$pass=true;
				$has_not_user = $ROW['row_data']['has_not_user']?1:0;
				$has_user = $ROW['row_data']['has_user']?1:0;
				if($isLogged && $has_not_user){
					$pass=false;
				}else if(!$isLogged && $has_user){
					$pass=false;
				}else if($has_not_user && $has_user){
					$pass=true;
				}else if($isLogged && $has_user){
					$pass=true;
				}
				
				$Divs = explode('_',$ROW['row_cells']);
				if($pass){
					
					if($ROW['cols'])
					{
						$rData      = [];
						$colC       = 0;
						$nclss      = 0;
						$rowclasses = explode(' ',$ROW['row_class']);
						if(in_array("no-rclss", $rowclasses)){$nclss = 1;}
						$row_content = '';
						foreach($ROW['cols'] as $COLS)
						{
							$rcData      = [];
							$resp        = $this->LYMD[$Divs[$colC]][$this->Cr];
							$colC++;
							$sub_content = '';
							if($COLS['sub_rows']){
								foreach($COLS['sub_rows'] as $indx => $SROW)
								{

									$sub_content .= '<div class="SR '.($SROW['row_class']?$SROW['row_class']:'').'"'.($SROW['image']?' data-image="'.HTTP_IMAGE.$SROW['image'].'"':'').' id="'.($SROW['row_tag_id']?$SROW['row_tag_id']:'srow'.$ind).'" >';
									foreach($SROW['cols'] as $SCOLS)
									{
										switch($SCOLS['col_type'])
										{
											case 1:// Text - image - movie(zengin editor)
											{
												
												$sub_content .= '<div class="'.$SCOLS['col_class'].'" id="'.$SCOLS['col_tag_id'].'">';
												if(trim($SROW['row_class']) != 'control')
												{
													$col_content = $SCOLS['col_content'];
													$srcs        = explode('src="',$col_content);

													if(count($srcs) > 1)
													{
														array_shift($srcs);
														foreach($srcs as $src)
														{
															$_src = explode('"',$src);
															if(isset($_src[1]))
															{
																by_move(str_replace('image/','',$_src[0]));
															}
														}
													}
													//$row_content .= html_entity_decode($SCOLS['col_content'], ENT_QUOTES, 'UTF - 8');
													$content = str_replace(['"https://simyaci.tr/','"https://www.simyaci.tr/'],'"',$col_content);
													$sub_content .= html_entity_decode($content, ENT_QUOTES, 'UTF-8');
												}
												else
												{
													$sub_content .= $this->load->controller($SCOLS['col_content']);
												}
												$sub_content .= '</div>';
												
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
												/*
												$images =$this->model_bytao_home->getHomeRowColImages($SCOLS['home_row_col_id']);
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
												*/
											}
											break;
											case 5:// Youtube Video
											{

											}
											break;
											case 6:// Slider
											{
												
												if(isset($SCOLS['col_content_id']))
												{
													$module_data = $this->load->controller('bytao/layerslider',['ids'=>$SCOLS['col_content_id']]);
													if($module_data)
													{
														$sub_content .= $module_data;
														str_replace('ROWID',$COLS['col_content_id'],$sub_content);
													}
												}
											}
											break;
											case 7:// Carousel
											{
												if(isset($SCOLS['col_content_id']))
												{
													$module_data = $this->load->controller('bytao/carousel',['ids'=>$SCOLS['col_content_id']]);

													if($module_data)
													{
														$sub_content .= $module_data;
														str_replace('ROWID',$COLS['col_content_id'],$sub_content);
													}
												}
											}
											break;
											case 8:// Modul
											{
												$this->load->model('setting/module');
												$parts = explode('|', $SCOLS['col_content_id']);
												$part  = explode('.', $parts[0]);
												$m     = explode('/',$parts[0]);

												if(isset($part[1]) && $this->config->get('module_' . $part[1] . '_status'))
												{
													$module_data = $this->load->controller('extension/' .  $part[0] . '/module/' . $part[1]);

													if($module_data)
													{
														$sub_content .= $module_data;
														str_replace('ROWID',$COLS['col_content_id'],$sub_content);
													}
												}


												if(isset($m[2]))
												{
													$setting_info = $this->model_setting_module->getModule($m[2]);
													if($setting_info && $setting_info['status'])
													{
														$output = $this->load->controller('extension/' .  $m[1] . '/module/' . $m[0], $setting_info);
														if($output)
														{
															$sub_content .= $output;
															str_replace('ROWID',$COLS['col_content_id'],$sub_content);
														}
													}
												}
											}
											break;
											case 9:// Yönetim
											{
												$parts = explode(',', $SCOLS['col_content_id']);
												if(isset($parts[1]))
												{

													switch($parts[0])
													{
														case 'catalog/product' : $route = 'product/product'; break;
														case 'catalog/review' : $route = 'bytao/testimonial'; break;
														default : $route = $parts[0];
													}

													$tData     = [
														'col_content_id'=>$SCOLS['col_content_id'],
														'col_id'=>$SCOLS['col_id'],
														'name'=>$SROW['name']
													];
													$ctrl_data = $this->load->controller($route.'.getwidget',$tData);
													if($ctrl_data)
													{
														$sub_content .= html_entity_decode($ctrl_data, ENT_QUOTES, 'UTF-8');
														str_replace('ROWID',$COLS['col_content_id'],$sub_content);
													}
												}
											}
											break;
											case 11:// HTML
											{
												$sub_content .= html_entity_decode($SCOLS['col_content'], ENT_QUOTES, 'UTF-8');
												str_replace('ROWID',$SCOLS['col_content_id'],$sub_content);
											}
											break;
											case 12:// Link Kolonları
											{
												$find    = ['<a href="','" target="','" title="','" class="no-link" alt="','">','</a>'];
												$replace = [' ',';',';',';',';',' '];

												$sub_content .= '<div class="'.$SCOLS['col_class'].'" >';
												$colCont = explode('<div class="linkcol">',html_entity_decode(str_replace(['<ul>','</ul>','</li>','</div>'],'',$SCOLS['col_content']), ENT_QUOTES, 'UTF-8'));
												array_shift($colCont);
												foreach($colCont as $lCo)
												{
													$sub_content .= '<div class="linkcol"><ul>';
													$_lCo = explode('<li>',str_replace('</ul>','',$lCo));
													foreach($_lCo as $Co)
													{
														$Cc = str_replace($find, $replace,$Co);
														$li = explode(';',$Cc);
														if(isset($li[4]))
														{
															$link = $this->link($li);
															$sub_content .= '<li><a href="'.$link.'" target="">';
															$sub_content .= trim($li[4]);
															$sub_content .= '</a></li>';
														}
													}
													$sub_content .= '</ul></div>';
												}

												$sub_content .= '</div>';
												str_replace('ROWID',$SCOLS['col_content_id'],$sub_content);
											}
											break;

											case 13:// Sosyal Medya adresleri
											{
												$sub_content .= html_entity_decode($SCOLS['col_content'], ENT_QUOTES, 'UTF-8');
												str_replace('ROWID',$SCOLS['col_content_id'],$sub_content);
											}
											
											case 14:// Banner
											{
												if (isset($SCOLS['col_content_id'])) {
													$module_data = $this->load->controller('bytao/banner/getwidget',['ids'=>$SCOLS['col_content_id']]);
													if ($module_data) {
														$sub_content .= $module_data;
													}
												}
												str_replace('ROWID',$SCOLS['col_content_id'],$sub_content);
												
											}
											break;
										}

									}
									$sub_content .= '</div>';
								}
							}
							else
							{
								switch($COLS['col_type'])
								{
									case 1:// Text - image - movie(zengin editor)
									{
										$sub_content .= '<div class="RT '.$COLS['col_class'].'" id="'.$COLS['col_tag_id'].'">';
										$colclasses = explode(' ',$COLS['col_class']);

										if(in_array("hasAjaxForm", $colclasses))
										{

											$addText = in_array("hasTrgt", $colclasses)?'data-oc-target="trgt-'.$COLS['col_tag_id'].'"':'';
											$sub_content .= '<form id="form-'.$COLS['col_tag_id'].'" '.$addText.' action="" method="post" data-oc-toggle="ajax">';
										}

										if(trim($ROW['row_class']) != 'control')
										{
											$col_content = $COLS['col_content'];
											$col_content = str_replace(['"https://simyaci.tr/','"https://www.simyaci.tr/','"https://www.bytao.net.tr/'],'"',$col_content);
											$srcs        = explode('src="',$col_content);

											if(count($srcs) > 1)
											{
												array_shift($srcs);
												foreach($srcs as $src)
												{
													$_src = explode('"',$src);
													if(isset($_src[1]))
													{
														by_move(str_replace('image/','',$_src[0]));
													}
												}
											}
											$content = str_replace(['"https://simyaci.tr/','"https://www.simyaci.tr/','"https://www.bytao.net.tr/'],'"',$col_content);
											//$content = str_replace('"https://www.bytao.net.tr/','"',$col_content);
											$sub_content .= html_entity_decode($content, ENT_QUOTES, 'UTF-8');
											str_replace('ROWID',$COLS['col_content_id'],$sub_content);
										}
										else
										{
											$sub_content .= $this->load->controller($COLS['col_content']);
										}

										if(in_array("hasAjaxForm", $colclasses))
										{
											$sub_content .= '</form>';
										}

										$sub_content .= '</div>';
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
										
									}
									break;
									case 5:// Youtube Video
									{

									}
									break;
									case 6:// Slider
									{
										if(isset($COLS['col_content_id']))
										{
											$module_data = $this->load->controller('bytao/layerslider',['ids'=>$COLS['col_content_id']]);
											if($module_data)
											{
												$sub_content .= $module_data;
												str_replace('ROWID',$COLS['col_content_id'],$sub_content);
											}
										}
									}
									break;
									case 7:// Carousel
									{
										if(isset($COLS['col_content_id']))
										{
											$module_data = $this->load->controller('bytao/carousel',['ids'=>$COLS['col_content_id']]);
											if($module_data)
											{
												$sub_content .= $module_data;
												str_replace('ROWID',$COLS['col_content_id'],$sub_content);
											}
										}
									}
									break;
									case 8:// Modul
									{
										$this->load->model('setting/module');
										$parts = explode('|', $COLS['col_content_id']);
										$part  = explode('.', $parts[0]);
										$m     = explode('/',$parts[0]);
										if(isset($part[1]) && $this->config->get('module_' . $part[1] . '_status'))
										{
											$module_data = $this->load->controller('extension/' .  $part[0] . '/module/' . $part[1]);
											if($module_data)
											{
												$sub_content .= $module_data;
												str_replace('ROWID',$COLS['col_content_id'],$sub_content);
											}
										}
										if(isset($m[2]))
										{
											$setting_info = $this->model_setting_module->getModule($m[2]);
											if($setting_info && $setting_info['status'])
											{
												$output = $this->load->controller('extension/' .  $m[1] . '/module/' . $m[0], $setting_info);
												if($output)
												{
													$sub_content .= $output;
													str_replace('ROWID',$COLS['col_content_id'],$sub_content);
												}
											}
										}
									}
									break;
									case 9:// Yönetim
									{
										$parts = explode(',', $COLS['col_content_id']);
										if(isset($parts[1]))
										{
											switch($parts[0])
											{
												case 'catalog/product' : $route = 'product/product'; break;
												case 'catalog/review' : $route = 'bytao/testimonial'; break;
												default : $route = $parts[0];
											}
											$tData     = [
												'col_content_id'=>$COLS['col_content_id'],
												'col_id'		=>$COLS['col_id'],
												'name'			=>$ROW['name']
											];
											$ctrl_data = $this->load->controller($route.'.getwidget',$tData);
											if($ctrl_data)
											{
												$sub_content .= $ctrl_data;
												str_replace('ROWID',$COLS['col_content_id'],$sub_content);
											}
										}
									}
									break;
									case 11:// HTML
									{
										$sub_content .= html_entity_decode($COLS['col_content'], ENT_QUOTES, 'UTF-8');
										str_replace('ROWID',$COLS['col_content_id'],$sub_content);
									}
									break;
									case 12:// link Kolonları
										{
										$this->load->model('design/seo_url');
										$find    = ['<a href="','" target="','" title="','" class="no-link" alt="','">','</a>'];
										$replace = [' ',';',';',';',';',' '];
										$sub_content .= '<div class="'.$COLS['col_class'].'" >';
										$colCont = explode('<div class="linkcol">',html_entity_decode(str_replace(['<ul>','</ul>','</li>','</div>'],'',$COLS['col_content']), ENT_QUOTES, 'UTF-8'));
										array_shift($colCont);
										foreach($colCont as $lCo){
											$sub_content .= '<div class="linkcol"><ul>';
											$_lCo = explode('<li>',str_replace('</ul>','',$lCo));
											foreach($_lCo as $Co)
											{
												$Cc = str_replace($find, $replace,$Co);
												$li = explode(';',$Cc);
												if(isset($li[2]) && isset($li[4]) && ($li[2] != '') && ($li[4] != ''))
												{
													$link = $this->link($li);
													$sub_content .= '<li><a href="'.$link.'" target="">';
													$sub_content .= trim($li[4]);
													$sub_content .= '</a></li>';
												}
											}
											$sub_content .= '</ul></div>';
										}
										$sub_content .= '</div>';
										}
									break;
									case 13:// Sosyal Medya adresleri
									{
										$sub_content .= html_entity_decode($COLS['col_content'], ENT_QUOTES, 'UTF-8');
										str_replace('ROWID',$COLS['col_content_id'],$sub_content);
									}
									break;
									case 14:// Banner
									{
										if (isset($COLS['col_content_id'])) {
											$module_data = $this->load->controller('bytao/banner|getwidget',['ids'=>$COLS['col_content_id']]);
											if ($module_data) {
												$sub_content .= $module_data;
											}
										}
									}
									break;
									
								}
							}
							$rcData['COL'] = [
								'col_cell' => $COLS['col_cell'],
								'resp' => $resp,
								'nclss'=> in_array("no-rclss", $rowclasses)?1:0,
								'sub_content'=> $sub_content
							];
							$row_content .= $this->load->view('bytao/row_col', $rcData);
						}
						$rData['ROW'] = [
							'rowClss'=> $rowclasses,
							'row_class'=>$ROW['row_class'],
							'row_data'=>isset($ROW['row_data'])?$ROW['row_data']:'',//(is_array($ROW['row_data'])?unserialize($ROW['row_data']):$ROW['row_data']):'',
							'row_tag_id'=>$ROW['row_tag_id'],
							'image'=>$ROW['image'],
							'row_ind'=>$ind,
							'HTTPS_IMAGE'=>HTTPS_IMAGE,
							'cr'=>isset($this->LYMD['00'][$this->Cr])?$this->LYMD['00'][$this->Cr]:'',
							'row_content' => $row_content
						];
						$ICERIK .= $this->load->view('bytao/row', $rData);
					}
				}
			}
		}
		return $ICERIK;
	}

	public function link(array $seoli = []): string
	{

		if(!$seoli)
		{
			return '';
		}
		$seo_url_info = [];

		$_URL         = $seoli[0];
		$URL          = explode('?',$_URL);
		$parts        = explode('&',$URL[1]);
		$value        = '';
		$key          = '';
		foreach($parts as $part)
		{
			[$key , $value] = explode('=', $part);
			if(in_array($key,$this->ids))
			{
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyValue((string)$key, (string)$value);
			}
			elseif($key == 'route')
			{
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByRoute((string)$value);
			}
		}

		$Url = $this->url->home();
		if($this->config->get('config_seo_url'))
		{
			return isset($seo_url_info['keyword'])?$Url.$seo_url_info['keyword']:$seoli[0];
		}
		else
		{
			return $seoli[0];
		}
	}

}
