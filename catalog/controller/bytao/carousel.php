<?php
namespace Opencart\Catalog\Controller\Bytao;
class Carousel extends \Opencart\System\Engine\Controller
{

	private $version = '1.0.0';
	private $cPth = 'bytao/carousel';
	private $C = 'carousel';
	private $ID = 'carousel_id';
	private $model ;
	private $LYMD = [] ;
	private $Cr = 0 ;

	
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
		by_cdn('image/pallette-BLK-40x40.png');
		
		$returnData = '';
		$slideData=[];
		if(isset($lData['ids'])){
			$IDS         = isset($lData['ids'])?explode(',',$lData['ids']):[];
			//$selContent  = $content[count($IDS)];
			$productHTML = '';
			$this->load->model('catalog/product');
			$this->load->model('catalog/category');
			
			$this->load->model('design/banner');
			$this->load->model('tool/image');
			$this->load->model('bytao/common');
			
			$this->document->addStyle('cdn/'.by_cdn('js/owl-carousel/owl.carousel.css'));
			//$this->document->addStyle('cdn/'.by_cdn('js/owl-carousel/owl.theme.css'));
			$this->document->addStyle('cdn/'.by_cdn('js/owl-carousel/owl.transitions.css'));
			$this->document->addStyle('cdn/'.by_cdn('css/carousel.css'),'stylesheet','screen','2');
			$this->document->addScript('cdn/'.by_cdn('js/owl-carousel/owl.carousel.min.js'));

			foreach($IDS as $ID){
				$carousel_info = $this->model->{$this->getFunc('get')}($ID);
				if($carousel_info){
					$data['carousel_id'] = $carousel_info['carousel_id'];
					$data['carousel_title'] = $carousel_info['title'];
					$data['setting'] = $setting = unserialize($carousel_info['setting']);
					$data['shows'] = explode(',',$setting['show']);
					switch($carousel_info['type_item']){
						case 0: //banner
							{
								
								$data['banners'] = [];
								$results = $this->model_design_banner->getBanner($carousel_info['content']);
								foreach ($results as $result) {
									if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
										$data['banners'][] = [
											'title' => $result['title'],
											'link'  => $result['link'],
											'image' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height'])
										];
									}
								}
								if ($data['banners']) {
									$data['module'] = $module++;

									$data['effect'] = $setting['effect'];
									$data['controls'] = $setting['controls'];
									$data['indicators'] = $setting['indicators'];
									$data['items'] = $setting['items'];
									$data['interval'] = $setting['interval'];
									$data['width'] = $setting['width'];
									$data['height'] = $setting['height'];

									$HTML[] = $this->load->view($this->cPth.'_bnnr', $data);
								} 
							}
							break;
						case 1: // product
							{
								$PRODS = explode(',',$carousel_info['content']);
								if ($PRODS) {
									$products = [];
									$product_data = [];

									foreach ($PRODS as $product_id) {
										$product_info = $this->model_catalog_product->getProduct($product_id);

										if ($product_info) {
											$products[] = $product_info;
										}
									}

									foreach ($products as $product) {
										if ($product['image']) {
											$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
										} else {
											$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
										}

										if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
											$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$price = false;
										}

										if ((float)$product['special']) {
											$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$special = false;
										}
										
										if ((float)$product['old_price']) {
											$old_price = $this->currency->format($this->tax->calculate($product['old_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$old_price = false;
										}
										
										if ((float)$product['retail_price']) {
											$retail_price = $this->currency->format($this->tax->calculate($product['retail_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$retail_price = false;
										}

										if ((float)$product['our_price']) {
											$our_price = $this->currency->format($this->tax->calculate($product['our_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$our_price = false;
										}
										
										if ((float)$product['cost_price']) {
											$cost_price = $this->currency->format($this->tax->calculate($product['cost_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
										} else {
											$cost_price = false;
										}
					
										if ($this->config->get('config_tax')) {
											$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
										} else {
											$tax = false;
										}

										$product_data = [
											'product_id'  => $product['product_id'],
											'thumb'       => $image,
											'name'        => $product['name'],
											'description' => oc_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
											'price'       => $price,
											'special'     => $special,
											'old_price'   => $old_price,
											'our_price'   => $our_price,
											'cost_price'  => $cost_price,
											'retail_price'=> $retail_price,
											'tax'         => $tax,
											'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
											'rating'      => (int)$product['rating'],
											'sale'        => $product['sale'],
											'best'        => $product['best'],
											'gift'        => $product['gift'],
											'clearance'   => $product['clearance'],
											'new_arriwals'=> $product['new_arriwals'],
											'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
										];

										$data['products'][] = $this->load->controller('product/thumb', $product_data);
									}
								}

								if ($data['products']) {
									$HTML[] = $this->load->view($this->cPth, $data);
								}
							}
							break;
						case 2: // category
							{
								$CATS = explode(',',$carousel_info['content']);
								
								foreach($CATS as $category_id){
									$data['products']=[];
									$category_info = $this->model_catalog_category->getCategory($category_id);
									$PRODS =$this->model_catalog_product->getProductCategoryProduct($category_id,$carousel_info['max_item']);
									if ($PRODS) {
										$products = [];
										$product_data = [];

										foreach ($PRODS as $product_id=>$product) {
											//$product_info = $this->model_catalog_product->getProduct($product_id);
											if ($product['image']) {
												$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
											} else {
												$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
											}

											if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
												$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$price = false;
											}

											if ((float)$product['special']) {
												$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$special = false;
											}
											
											if ((float)$product['old_price']) {
												$old_price = $this->currency->format($this->tax->calculate($product['old_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$old_price = false;
											}
											
											if ((float)$product['retail_price']) {
												$retail_price = $this->currency->format($this->tax->calculate($product['retail_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$retail_price = false;
											}

											if ((float)$product['our_price']) {
												$our_price = $this->currency->format($this->tax->calculate($product['our_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$our_price = false;
											}
											
											if ((float)$product['cost_price']) {
												$cost_price = $this->currency->format($this->tax->calculate($product['cost_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
											} else {
												$cost_price = false;
											}

											if ($this->config->get('config_tax')) {
												$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
											} else {
												$tax = false;
											}
											
											$more = $this->model_bytao_common->getProductColorCount($product['product_id']);
											$_webp = explode('.',$image);
											
											if(count($_webp)>1){
												$Arr = array_slice($_webp,0,-1);
												$webp = (implode('.',$Arr )).'.webp';
											}
											
											
											$product_data = [
												'product_id'  => $product['product_id'],
												'webp'       => $webp,
												'thumb'       => $image,
												'name'        => $product['name'],
												'color'        => $more,
												'description' => oc_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
												'price'       => $price,
												'special'     => $special,
												'old_price'   => $old_price,
												'our_price'   => $our_price,
												'cost_price'  => $cost_price,
												'retail_price'=> $retail_price,
												'tax'         => $tax,
												'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
												'rating'      => (int)$product['rating'],
												'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
											];

											$data['products'][] = $this->load->controller('product/thumb', $product_data);
											//$data['products'][] = $product_data;
										}
										
										if ($data['products']) {
											$data['categories'][$category_id] = [
												'category_id'=> $category_id,
												'name'=> $category_info['name'],
												'products'=> $data['products']
											];
											
										}
									}
									
									
								}
							}
							break;
					}
					
				}
			}
			if ($data['categories']) {
				$returnData = $this->load->view($this->cPth.'_cat', $data);
			}
			return $returnData;
		}
		return '';
	}
}
