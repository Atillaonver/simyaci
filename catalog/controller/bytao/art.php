<?php
namespace Opencart\Catalog\Controller\Bytao;
class ArtCategory extends \Opencart\System\Engine\Controller {	

	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/art_category';
	private $C = 'art_category';
	private $ID = 'art_category_id';
	private $model ;
	
	public function index()
	{
		$this->load->language('bytao/art_category');
		$this->load->model('bytao/artcategory');
		$this->load->model('bytao/art');
		$this->load->model('tool/image');

		$data['logged'] = $this->customer->isLogged();
		if($data['logged'])
		{
			$data['groupId'] = $this->customer->getGroupId();
		}
		else
		{
			$data['groupId'] = 0;
		}


		if(isset($this->request->get['filter']))
		{
			$filter = $this->request->get['filter'];
		}
		else
		{
			$filter = '';
		}

		if(isset($this->request->get['sort']))
		{
			$sort = $this->request->get['sort'];
		}
		else
		{
			$sort = 'a2c.sort_order';
		}

		if(isset($this->request->get['order']))
		{
			$order = $this->request->get['order'];
		}
		else
		{
			$order = 'ASC';
		}



		if(isset($this->request->get['page']))
		{
			$page = $this->request->get['page'];
		}
		else
		{
			$page = 1;
		}

		if(isset($this->request->get['limit']))
		{
			$limit = $this->request->get['limit'];

		}
		else
		{
			$limit = $this->config->get('config_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'=> $this->language->get('text_home'),
			'href'=> $this->url->home('SSL')
		);

		if(isset($this->request->get['artpath']))
		{
			$url = '';

			if(isset($this->request->get['sort']))
			{
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order']))
			{
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['limit']))
			{
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts= explode('_', (string)$this->request->get['artpath']);

			$job             = '';




			$art_category_id = (int)array_pop($parts);
			foreach($parts as $path_id)
			{
				if(!$path)
				{
					$path = (int)$path_id;
				}
				else
				{
					$path .= '_' . (int)$path_id;
				}
				$category_info = $this->model_bytao_artcategory->getArtCategory($path_id);
				if($category_info)
				{
					$data['breadcrumbs'][] = array(
						'text'=> $category_info['name'],
						'href'=> $this->url->link('bytao/art_category', 'path=' . $path . $url)
					);
				}
			}



		}
		else
		{
			$art_category_id = 0;
		}

		$data['categoryId'] = $art_category_id;

		$this->document->addStyle('view/theme/'. $this->config->get('config_template') . '/js/fancybox/jquery.fancybox.min.css');
		$this->document->addStyle('view/theme/'. $this->config->get('config_template') . '/css/category.css?ver=17');


		$this->document->addScript('view/theme/'. $this->config->get('config_template') . '/js/fancybox/jquery.fancybox.min.js');
		$this->document->addScript('view/theme/'. $this->config->get('config_template') . '/js/category.js?ver=61');

		$category_info = $this->model_bytao_artcategory->getArtCategory($art_category_id);


		if($category_info){

			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);
			$this->document->addLink($this->url->link('bytao/art_category', 'path=' . $this->request->get['artpath']), 'canonical');

			$data['heading_title'] = $category_info['name'];

			$data['text_refine'] = $this->language->get('text_refine');
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_quantity'] = $this->language->get('text_quantity');
			$data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$data['text_model'] = $this->language->get('text_model');
			$data['text_price'] = $this->language->get('text_price');
			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_points'] = $this->language->get('text_points');
			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$data['text_sort'] = $this->language->get('text_sort');
			$data['text_limit'] = $this->language->get('text_limit');
			$data['text_more'] = $this->language->get('text_more');

			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');

			// Set the last category breadcrumb
			if($job != '')
			{
				$data['breadcrumbs'][] = array(
					'text'=> $this->language->get('text_'.$job),
					'href'=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'])
				);
			}
			else
			{
				$data['breadcrumbs'][] = array(
					'text'=> $category_info['name'],
					'href'=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'])
				);
			}


			if($category_info['image'])
			{
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			}
			else
			{
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');

			$url = '';

			if(isset($this->request->get['filter']))
			{
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if(isset($this->request->get['sort']))
			{
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order']))
			{
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['limit']))
			{
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = array();
			$data['arts'] = array();


			if($job != '')
			{

				$this->load->model('bytao/art');
				if($limit == "all")
				{
					$filter_data = array(
						'filter_category_id'=> $art_category_id,
						'job'               => $job,
						'filter_filter'     => $filter,
						'sort'              => $sort,
						'order'             => 'a2c.sort_order'
					);
				}
				else
				{
					$filter_data = array(
						'filter_category_id'=> $art_category_id,
						'job'               => $job,
						'filter_filter'     => $filter,
						'sort'              => $sort,
						'order'             => $order,
						'start'             => ($page - 1) * $limit,
						'limit'             => $limit
					);
				}


				$art_total = $this->model_bytao_art->getTotalArts($filter_data);
				$artresults    = $this->model_bytao_art->getArts($filter_data);


			}
			else
			{
				$filter_data = array(
					'filter_category_id'=> $art_category_id,
					'filter_filter'     => $filter,
					'sort'              => $sort,
					'order'             => $order,
					//'start'             => ($page - 1) * $limit,
					//'limit'             => $limit
				);


				$results = $this->model_bytao_artcategory->getArtCategories($art_category_id);

				foreach($results as $result)
				{
					$filter_datas = array(
						'filter_category_id' => $result['art_category_id'],
						'filter_sub_category'=> true
					);

					$data['categories'][] = array(
						//'name'  => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_datas) . ')' : ''),
						'name'=> $result['name'],
						'href'=> $this->url->link('product/category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['path'] . '_' . $result['art_category_id'] . $url)
					);
				}


				$art_total = $this->model_bytao_art->getTotalArts($filter_data);
				$artresults    = $this->model_bytao_art->getArts($filter_data);

			}
			
			foreach($artresults as $result)
			{

				if($result['image'])
				{
					$image = $this->model_tool_image->resizeTao($result['image']);
					$thumb = $this->model_tool_image->resize($result['image'],200,200);
				}
				else
				{
					$image = $this->model_tool_image->resizeTao('placeholder.png');
					$thumb = $this->model_tool_image->resize('placeholder.png',200,200);
				}
				
				$data['arts'][] = array(
					'art_id'  => $result['art_id'],
					'image'   => $image,
					'thumb'   => $thumb,
					'art_code'=> $result['art_code'],
					'col_num'=> $result['col_num'],
					'name'    => $result['name'],
					'desc'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
					'href'    => $this->url->link('bytao/art', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'] . '&art_id=' . $result['art_id'] . $url)
				);
			}

			$url = '';

			if(isset($this->request->get['filter']))
			{
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if(isset($this->request->get['limit']))
			{
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();



			$url = '';

			if(isset($this->request->get['filter']))
			{
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if(isset($this->request->get['sort']))
			{
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order']))
			{
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('config_product_limit'),25,50,75,100));

			sort($limits);

			foreach($limits as $value)
			{
				$data['limits'][] = array(
					'text' => $value,
					'value'=> $value,
					'href' => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'] . $url . '&limit=' . $value)
				);
			}
			if($art_total > 9)
			{
				$data['limitall'] = array(
					'text' => $value,
					'value'=> $value,
					'href' => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'] . $url . '&limit=all')
				);
			}


			$url = '';

			if(isset($this->request->get['filter']))
			{
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if(isset($this->request->get['sort']))
			{
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order']))
			{
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['limit']))
			{
				$url .= '&limit=' . $this->request->get['limit'];
			}




			if($limit == "all")
			{
				$data['pagination'] = '';
				$data['results'] = sprintf($this->language->get('text_pagination_all'), $art_total);
			}
			else
			{
				$pagination = new Pagination();
				$pagination->total = $art_total;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link('product/category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['artpath'] . $url.'&page={page}');
				$data['pagination'] = $pagination->render();
				$data['results'] = sprintf($this->language->get('text_pagination'), ($art_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($art_total - $limit)) ? $art_total : ((($page - 1) * $limit) + $limit), $art_total, ceil($art_total / $limit));
			}


			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			if($limit>= $art_total){
				$data['dosc'] = 0;
			}else{
				$data['dosc'] = 1;
			}

			$data['continue'] = $this->url->home('SSL');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',array('part_id'=>4));
			$data['header'] = $this->load->controller('bytao/header',array('menu_id'=>0,'amenu'  =>false));

			if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/bytao/art_category.tpl'))
			{
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/bytao/art_category.tpl', $data));
			}
			else
			{
				$this->response->setOutput($this->load->view('default/template/bytao/art_category.tpl', $data));
			}
		}
		else
		{
			$data['continue'] = $this->url->home('SSL');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',array('part_id'=>4));
			$data['header'] = $this->load->controller('bytao/header',array('menu_id'=>0,'amenu'  =>false));

			if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/bytao/not_found.tpl'))
			{
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/bytao/not_found.tpl', $data));
			}
			else
			{
				$this->response->setOutput($this->load->view('default/template/bytao/not_found.tpl', $data));
			}
		}
	}

	public function imgload()
	{
		$json = array();
		$this->load->model('bytao/art');

		$this->load->model('tool/image');
		
		
		$art_category_id = isset($this->request->post['acid'])? $this->request->post['acid']:0;
		$start = isset($this->request->post['start'])?$this->request->post['start']:0;
		
		$filter_data = array(
			'filter_category_id'=> $art_category_id,
			'filter_filter'     => '',
			'sort'              => 'ASC',
			'order'             => 'a2c.sort_order',
			'start'             => $start,
			'limit'             => $this->config->get('config_product_limit')
		);
		

		$art_total = $this->model_bytao_art->getTotalArts($filter_data);
		$artresults = $this->model_bytao_art->getArts($filter_data);

		$sayac      = 0;
		$colon        = 1;
		$type       = 3;


		if(isset($artresults))
		{
			$last = 0;
			foreach($artresults as $result)
			{
				if($result['image'])
				{
					$image = $this->model_tool_image->resizeTao($result['image'],400,400);
					$thumb = $this->model_tool_image->resize($result['image'],400,400);
				}
				else
				{
					$image = $this->model_tool_image->resizeTao('placeholder.png',400,400);
					$thumb = $this->model_tool_image->resize('placeholder.png',400,400);
				}
				
				$imgN ='<div class="imgcontainer" ><a href="'.$image.'" data-fancybox="images" data-caption="<span class=\'w3-right art-code\'>'.$result['art_code'].'</span>'. html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8').'"><img src="'.$thumb.'" alt="'.$result['art_code'].'" width="100%"></a></div>';
				
				if(isset($json['img'][$colon])){$json['img'][$colon].= $imgN;}else{	$json['img'][$colon]= $imgN;}
				
				$colon++;
				$sayac++;
				
				if($colon>3) $colon=1;

			}
		}
		
		$json['start'] = $start + $sayac;
		
		if($art_total>$json['start']){
			$json['doScroll'] = 1;
		}else{
			$json['doScroll'] = 0;
		}
		

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
}
