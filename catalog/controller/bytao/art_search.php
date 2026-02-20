<?php
namespace Opencart\Catalog\Controller\Bytao;
class ArtSearch extends \Opencart\System\Engine\Controller {	

	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/art_search';
	private $C = 'art_search';
	private $ID = 'art_search_id';
	private $model ;
	
	public function index() {
		$this->load->language('bytao/artsearch');
		
		$this->load->model('bytao/art');
		$this->load->model('tool/image');
		
		$data['logged'] = $this->customer->isLogged();
		if ($data['logged']) {
			$data['groupId'] = $this->customer->getGroupId();
		}else{
			$data['groupId'] =0;
		}
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} elseif (isset($this->request->post['search'])) { 
			$search = $this->request->post['search'];
		}else{
			$search = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_pagination');
		}
	
		if($search!=''){
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $search);
		}else{
			$this->document->setTitle($this->language->get('heading_title'));
		}
		
		
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home('SSL')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($search, ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->post['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($search, ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
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

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/artsearch', $url)
		);

		if (isset($this->request->get['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $search;
		} else if (isset($this->request->post['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $search;
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_search'] = $this->language->get('text_search');
		$data['text_keyword'] = $this->language->get('text_keyword');
		
		$data['text_sort'] = $this->language->get('text_sort');
		$data['text_limit'] = $this->language->get('text_limit');

		$data['entry_search'] = $this->language->get('entry_search');
		$data['entry_description'] = $this->language->get('entry_description');

		$data['button_search'] = $this->language->get('button_search');
		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');
		$data['button_list'] = $this->language->get('button_list');
		$data['button_grid'] = $this->language->get('button_grid');

		$this->document->addStyle('/cdn/js/art/jquery.fancybox.min.css','stylesheet','screen','18');
		$this->document->addScript('/cdn/js/art/jquery.fancybox.min.js','footer');
		$this->document->addScript('/cdn/js/art/category.js','footer','62');
		
		if (($search!='') || isset($this->request->get['tag'])) {
			
			$filter_data = ['filter_name' => $search];
			

			$art_total = (int)$this->model_bytao_art->getTotalArts($filter_data);

			$results = $this->model_bytao_art->getArts($filter_data);
			$aC =1;
			
			foreach($results as $result)
			{
				if($result['image'])
				{
					$image = $this->model_tool_image->resize($result['image']);
					$thumb = $this->model_tool_image->resize($result['image'],200,200);
				}
				else
				{
					$image = $thumb = $this->model_tool_image->resize('placeholder.png',200,200);
				}
			
				$data['arts'][$aC][] = [
					'art_id'  => $result['art_id'],
					'image'   => $image,
					'thumb'   => $thumb,
					'art_code'=> $result['art_code'],
					'name'    => $result['name'],
					'desc'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
					'href'    => $this->url->link('bytao/art', 'art_id=' . $result['art_id'] . $url)
				];
				$aC++;
				if($aC>3)$aC=1;
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = [];

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = [
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
			];

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = [];

			$limits = array_unique(array($this->config->get('config_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
				);
			}
			
			if($art_total>9){
				$data['limitall'] = array(
						'text'  => $value,
						'value' => $value,
						'href'  => $this->url->link('product/search', $url .'limit=all')
					);
			}
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

			if ($limit=="all"){
				$data['pagination'] ='';
				$data['results'] = sprintf($this->language->get('text_pagination_all'), $art_total);
			}else{
				$json['pagination'] = $this->load->controller('common/pagination', [
					'total' => $art_total,
					'page'  => $page,
					'limit' => $limit,
					'url'   => $this->url->link('bytao/art_search', 'language=' . $this->config->get('config_language') . $url . '&page={page}&ajax=category')
				]);
				
				$data['results'] = sprintf($this->language->get('text_pagination'), ($art_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($art_total - $limit)) ? $art_total : ((($page - 1) * $limit) + $limit), $art_total, ceil($art_total / $limit));
			}
		}

		$data['search'] = $search;
		$data['description'] = $description;
		$data['category_id'] = $category_id;
		$data['sub_category'] = $sub_category;

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$hData = [
					'route'   => 'bytao/art_category',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
}
