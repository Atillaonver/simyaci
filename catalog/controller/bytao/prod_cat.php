<?php
namespace Opencart\Catalog\Controller\Bytao;
class ProdCat extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('bytao/prod');
		$this->load->model('bytao/prod');
		$this->load->model('tool/image');
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';
		$data['fimage'] = $this->config->get('config_footer_image')?$this->config->get('config_footer_image'):'';
		
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];

		if (isset($this->request->get['prod_cat_id'])) {
			$prod_cat_id = $this->request->get['prod_cat_id'];
		} else {
			$prod_cat_id = 0;
		}
		

		$category_info = $this->model_bytao_prod->getProdCat($prod_cat_id);
		if ($category_info) {
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];

			
			$data['breadcrumbs'][] = [
				'text' => $category_info['name'],
				'href' => $this->url->link('bytao/prod_cat', 'language=' . $this->config->get('config_language') . '&prod_cat_id=' . $prod_cat_id)
			];

			if (is_file(DIR_IMAGE . html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			} else {
				$data['thumb'] = '';
			}
			$data['himage'] = (is_file(DIR_IMAGE . html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8')))?$category_info['image']:$data['himage'];

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['prodcats'] = [];

			$results = $this->model_bytao_prod->getProdCats($prod_cat_id);

			foreach ($results as $result) {
				$prods = [];
				$PROD = $this->model_bytao_prod->getProdCatProds($result['prod_cat_id']);
				foreach ($PROD as $PR) {
					if (is_file(DIR_IMAGE . html_entity_decode($PR['image'], ENT_QUOTES, 'UTF-8'))) {
						$image = $this->model_tool_image->resize(html_entity_decode($PR['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					}

					$prods[] = [
						'prod_id'  => $PR['prod_id'],
						'thumb'       => $image,
						//'name'        => $PR['name'],
						'title'        => $PR['title'],
						'description' => substr(trim(strip_tags(html_entity_decode($PR['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
						'href'        => $this->url->link('bytao/prod', 'language=' . $this->config->get('config_language') . '&prod_id=' . $PR['prod_id'] . $url)
					];

				}
				
				$data['prodcats'][] = [
					'name' => $result['name'],
					'prods' => $prods
				];
			}

			$data['prods'] = [];

			$results = $this->model_bytao_prod->getProdCatProds($prod_cat_id);
			foreach ($results as $result) {
				if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				}

				$data['prods'][] = [
					'prod_id'  => $result['prod_id'],
					'thumb'       => $image,
					'title'        => $result['title'],
					'description' => substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
					'href'        => $this->url->link('bytao/prod', 'language=' . $this->config->get('config_language') . '&prod_id=' . $result['prod_id'] . $url)
				];

			}


			$data['continue'] = $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-prod-cat']);

			$this->response->setOutput($this->load->view('bytao/prod_cat', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('bytao/prod_cat', 'language=' . $this->config->get('config_language') . $url)
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-prod-cat']);

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
