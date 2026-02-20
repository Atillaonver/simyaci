<?php
namespace Opencart\Catalog\Controller\Bytao;
class Testimonial extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/testimonial';
	private $C = 'testimonial';
	private $ID = 'review_id';
	private $Tkn = 'user_token';
	
	public function index() {
		$this->load->language('bytao/testimonial');
		
		$this->load->model('bytao/common');
		$this->load->model('catalog/product');
		$this->load->model('catalog/review');
		$this->load->model('tool/image');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home('SSL')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_testimonial'),
			'href' => $this->url->link('bytao/testimonial')
		);
		
		$reviews = $this->model_catalog_review->getReviews();
		
		$data['products']=[];
		
		foreach($reviews as $review){
			
			$result = $this->model_catalog_product->getProduct($review['product_id'],false);
			
			if ($result) {	
			
				if (is_file(DIR_IMAGE . html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8'))) {
						$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}
					

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}
					

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				$reviews = [];
				
				$previews = $this->model_catalog_review->getReviewsByProductId($review['product_id']);

				foreach ($previews as $preview) {
					$reviews[] = [
						'author'     => $preview['author'],
						'text'       => nl2br($preview['text']),
						'rating'     => (int)$preview['rating'],
						'date_added' => date($this->language->get('date_format_short'), strtotime($preview['date_added']))
					];
				}
				
				$more = $this->model_bytao_common->getProductColorCount($result['product_id']);
				$data['products'][] = [
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'model'      => $result['model'],
						'reviews'      => $reviews,
						'color'        => $more,
						'description' => oc_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
						'rating'      => $result['rating'],
						'sale'       => $result['sale'],
						'best'       => $result['best'],
						'gift'       => $result['gift'],
						'clearance'  => $result['clearance'],
						'new_arriwals'=> $result['new_arriwals'],
						'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
					];
			}
		}
		
		$this->document->setTitle($this->language->get('text_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keyword'));
		
		
		$data['heading_title'] = $this->language->get('text_title');
		$data['continue'] = $this->url->home();
		
	
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$hData = [
					'route'   => 'bytao/testimonial',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
				
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);
		$this->response->setOutput($this->load->view('bytao/testimonial', $data));
	}

	public function getwidget( array $cDatat=[]) {
		
		$vData=[];
		$parts = explode(',', $cDatat['col_content_id']);
		$this->load->model('catalog/product');
		$this->load->model('catalog/review');
		$this->load->model('bytao/common');
		$vData['products'] = [];
		$vData['categories'] = [];
		
		array_shift($parts);
		$proType = $parts[0];
		array_shift($parts);
		$limit = $parts[0];
		array_shift($parts);
		switch($proType){
			case '2':// selected
				$reviews = $this->model_catalog_review->getSelectedReviews(implode("','",$parts));
				
				foreach ($reviews as $result) {
					
					
					if (is_file(DIR_IMAGE . html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8'))) {
						$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					}
					
					$more = $this->model_bytao_common->getProductColorCount($result['product_id']);
					
					$vData['items'][] = [
						'author'  => $result['author'],
						'rating'  => $result['rating'],
						'text'  => $result['text'],
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'color'        => $more,
						'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
					];

				}
				
				break;

			case '1':// latest
				
				$results = $this->model_catalog_review->getLatestReviews($limit);
				foreach ($results as $result) {
					if (is_file(DIR_IMAGE . html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8'))) {
						$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					}

					$more = $this->model_bytao_common->getProductColorCount($result['product_id']);
					
					$vData['items'][] = [
						'author'  => $result['author'],
						'rating'  => $result['rating'],
						'text'  => $result['text'],
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'color'        => $more,
						'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
					];

				}
				break;	
			default: 
		}
		
		return $this->load->view('bytao/review_widget', $vData);	
	}


}