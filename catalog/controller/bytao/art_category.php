<?php
namespace Opencart\Catalog\Controller\Bytao;
class ArtCategory extends \Opencart\System\Engine\Controller {	

	private $error = [];
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
			$limit = $this->config->get('config_pagination');
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];
		
		$job             = '';
		
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
						'href'=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&path=' . $path . $url)
					);
				}
			}



		}
		else
		{
			$art_category_id = 0;
		}

		if(isset($this->request->get['art_category_id'])){
			$art_category_id = $this->request->get['art_category_id'];
		}else
		{
			$art_category_id = 0;
		}
		
		$data['categoryId'] = $art_category_id;

		$category_info = $this->model_bytao_artcategory->getArtCategory($art_category_id);


		if($category_info){

			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);
			$this->document->addLink($this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&art_category_id=' . $art_category_id),false,false);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			
			// Set the last category breadcrumb
			if($job != '')
			{
				$data['breadcrumbs'][] = array(
					'text'=> $this->language->get('text_'.$job),
					'href'=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&art_category_id=' . $art_category_id)
				);
			}
			else
			{
				$data['breadcrumbs'][] = array(
					'text'=> $category_info['name'],
					'href'=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&art_category_id=' . $art_category_id)
				);
			}


			if($category_info['image'])
			{
				$data['thumb'] = $this->model_tool_image->resize(by_move($category_info['image']), $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
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

			$data['categories'] = [];
			$data['arts'] = [];


			if($job != '')
			{

				$this->load->model('bytao/art');
				if($limit == "all")
				{
					$filter_data = [
						'filter_category_id'=> $art_category_id,
						'job'               => $job,
						'filter_filter'     => $filter,
						'sort'              => $sort,
						'order'             => 'a2c.sort_order'
					];
				}
				else
				{
					$filter_data = [
						'filter_category_id'=> $art_category_id,
						'job'               => $job,
						'filter_filter'     => $filter,
						'sort'              => $sort,
						'order'             => $order,
						'start'             => ($page - 1) * $limit,
						'limit'             => $limit
					];
				}


				$art_total = $this->model_bytao_art->getTotalArts($filter_data);
				$artresults    = $this->model_bytao_art->getArts($filter_data);


			}
			else
			{
				$filter_data = [
					'filter_category_id'=> $art_category_id,
					'filter_filter'     => $filter,
					'sort'              => $sort,
					'order'             => $order,
					//'start'             => ($page - 1) * $limit,
					//'limit'             => $limit
				];


				$results = $this->model_bytao_artcategory->getArtCategories($art_category_id);

				foreach($results as $result)
				{
					$filter_datas = [
						'filter_category_id' => $result['art_category_id'],
						'filter_sub_category'=> true
					];

					$data['categories'][] = [
						//'name'  => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_datas) . ')' : ''),
						'name'=> $result['name'],
						'href'=> $this->url->link('product/category', 'language=' . $this->config->get('config_language').'&path=' . $this->request->get['path'] . '_' . $result['art_category_id'] . $url)
					];
				}


				$art_total = $this->model_bytao_art->getTotalArts($filter_data);
				$artresults    = $this->model_bytao_art->getArts($filter_data);

			}

			$aC =1;
			foreach($artresults as $result)
			{

				if($result['image'])
				{
					$image = $this->model_tool_image->resize(by_move($result['image']),200,200);
					$thumb = $this->model_tool_image->resize(by_move($result['image']),200,200);
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
					'col_num'=> $result['col_num'],
					'name'    => $result['name'],
					'desc'    => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
					'href'    => $this->url->link('bytao/art', 'language=' . $this->config->get('config_language').'&art_category_id=' . $this->request->get['art_category_id'] . '&art_id=' . $result['art_id'] . $url)
				];
				$aC++;
				if($aC>3)$aC=1;
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

			$data['sorts'] = [];



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

			$data['limits'] = [];

			$limits = array_unique(array($this->config->get('config_product_limit'),25,50,75,100));

			sort($limits);

			foreach($limits as $value)
			{
				$data['limits'][] = array(
					'text' => $value,
					'value'=> $value,
					'href' => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&art_category_id=' . $this->request->get['art_category_id'] . $url . '&limit=' . $value)
				);
			}
			if($art_total > 9)
			{
				$data['limitall'] = array(
					'text' => $value,
					'value'=> $value,
					'href' => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language').'&art_category_id=' . $this->request->get['art_category_id'] . $url . '&limit=all')
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
				$json['pagination'] = $this->load->controller('common/pagination', [
					'total' => $art_total,
					'page'  => $page,
					'limit' => $limit,
					'url'   => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language') . '&art_category_id=' . $this->request->get['art_category_id'] . $url . '&page={page}&ajax=category')
				]);
				
				$data['results'] = sprintf($this->language->get('text_pagination'), ($art_total) ? (($page - 1) * (int)$limit) + 1 : 0, ((($page - 1) * (int)$limit) > ($art_total - (int)$limit)) ? $art_total : ((($page - 1) * (int)$limit) + (int)$limit), $art_total, ceil($art_total / (int)$limit));
				
			}


			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;
			if($limit>= $art_total){
				$data['dosc'] = 0;
			}else{
				$data['dosc'] = 1;
			}

			$data['continue'] = $this->url->home();
			
			$this->document->addStyle('/cdn/js/art/jquery.fancybox.min.css','stylesheet','screen','18');
			$this->document->addScript('/cdn/js/art/jquery.fancybox.min.js','footer');
			$this->document->addScript('/cdn/js/art/category.js','footer','62');
			

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
		else
		{
			$data['continue'] = $this->url->home();

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language'))
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->home();

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',['route'=>'error/not_found']);
			$data['header'] = $this->load->controller('bytao/header',['route'=>'error/not_found']);

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function imgload()
	{
		$json = [];
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
					$image = $this->model_tool_image->resizeTao(by_move($result['image']),400,400);
					$thumb = $this->model_tool_image->resize(by_move($result['image']),400,400);
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
