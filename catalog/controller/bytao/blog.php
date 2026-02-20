<?php  
namespace Opencart\Catalog\Controller\Bytao;
class Box extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $settings = array();
	private $config = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/blog';
	private $C = 'blog';
	private $ID = 'blog_id';
	private $model ;
	
	 public function __construct($registry) {
        parent::__construct($registry);
        $this->config = $registry->get('config'); 
    }
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
		$this->load->model('tool/image');
		$this->settings = $this->model->getSettings();
	}

	public function index(){
		$notfound = false;
		$this->getML('ML');
		$data['HTTP_IMAGE'] = HTTP_IMAGE;
		if(isset($this->request->get['article_id']))
		{
			$this->response->setOutput($this->getArticle($this->request->get['article_id']));
        }else{
			$this->response->setOutput($this->getArticles());
		}
			
	}

	private function getArticle(){
		$data['config'] = $this->config;
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/css/blog/article.css');
			
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_quantity'] = $this->language->get('text_quantity');
			$data['text_sort'] = $this->language->get('text_sort');
			$data['text_limit'] = $this->language->get('text_limit');
			$data['text_comments'] = $this->language->get('text_comments');
			$data['text_read_more'] = $this->language->get('text_read_more');
			$data['text_posted_by'] = $this->language->get('text_posted_by');
			$data['text_category'] = $this->language->get('text_category');
			$data['text_tags'] = $this->language->get('text_tags');
			$data['text_no_comments'] = $this->language->get('text_no_comments');
			$data['text_leave_reply'] = $this->language->get('text_leave_reply');
			$data['text_required_info'] = $this->language->get('text_required_info');
			$data['text_name'] = $this->language->get('text_name');
			$data['text_email'] = $this->language->get('text_email');
			$data['text_content'] = $this->language->get('text_content');
			$data['text_related_products'] = $this->language->get('text_related_products');
			$data['text_related_articles'] = $this->language->get('text_related_articles');

			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');
			$data['button_read_more'] = $this->language->get('button_read_more');
			$data['button_post_comment'] = $this->language->get('button_post_comment');
			$data['button_cart'] = $this->language->get('button_cart');
            
			$data['settings'] = $this->settings;
            
			$article_info = $this->model->getArticle($this->request->get['article_id']);
			
			
			$article_categories = $this->model->getArticleCategories($article_info['article_id']);
			$article_galleries = $this->model->getArticleGalleries($article_info['article_id']);
            
			//prepare main image/video
			foreach($article_categories as &$category){
				$category['href'] = $this->url->link('bytao/blog', 'path=' . $this->model->getCategoryPath($category['category_id']));
			}

			$data['article'] = array(
				'article_id'  => $article_info['article_id'],
				'title'        => $article_info['title'],
				'description' => html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'),
				'content' => html_entity_decode($article_info['content'], ENT_QUOTES, 'UTF-8'),
				'date_published'   =>  $article_info['date_published'],
				'tags'   =>  $article_info['tags'],
				'status_comments'   =>  $article_info['status_comments'],
				'categories'   =>  $article_categories,
				'article_list_gallery_display'   =>  $article_info['article_list_gallery_display'],
				'gallery'   =>  $article_galleries,
				'comments_count'   =>  $this->model->getTotalCommentsForArticle($article_info['article_id']),
			);
            
			$data['tags'] = array();

			if($article_info['tags']){
				$tags = explode(',', $article_info['tags']);

				foreach($tags as $tag){
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
					);
				}
			}
            
			// Author
			$data['author'] = $this->model->getAuthor($article_info['author_id']);

			if(!empty($data['author'])){
				$data['author']['thumb'] = $this->model_tool_image->resize($data['author']['image'], 160, 160);
				$data['author']['description'] = html_entity_decode($data['author']['description'], ENT_QUOTES, 'UTF-8');
				$data['author']['href'] =  $this->url->link('blog/blog', 'author=' . $article_info['author_id']);
			}               
			// Comments
			$data['comments'] = $this->model->getComments($article_info['article_id']);
			foreach($data['comments'] as &$comment){
				$comment['content'] = html_entity_decode($comment['content'], ENT_QUOTES, 'UTF-8');
			}
            
			// RELATED PRODUCT
			$this->load->model('catalog/product');
            
			$results = $this->model->getProductRelated($article_info['article_id']);
			$data['products'] = array();
            
			if(!empty($results)){
				foreach($results as $product_id){
					$result = $this->model_catalog_product->getProduct($product_id);

					if($result['image']){
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					} else{
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
					}

					if($this->customer->isLogged() || !$this->config->get('config_customer_price')){
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else{
						$price = false;
					}

					if((float)$result['special']){
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else{
						$special = false;
					}

					if($this->config->get('config_tax')){
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else{
						$tax = false;
					}

					if($this->config->get('config_review_status')){
						$rating = (int)$result['rating'];
					} else{
						$rating = false;
					}

					$data['products'][] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}
            
			// RELATED ARTICLE
            
			$results = $this->model->getArticleRelated($article_info['article_id']);
            
			$data['article_id'] = $article_info['article_id'];
			$data['articles'] = array();

			if(!empty($results)){
				foreach($results as $article_id){
					$result = $this->model->getArticle($article_id);
					$thumb = false;
					if(!empty($result['image'])){
						$thumb = $result['image'];
					}
					if($thumb){
						$thumb = $this->model_tool_image->resize($thumb, $this->settings['gallery_related_article_width'], $this->settings['gallery_related_article_height']);
					}
                    
					$tags_array = array();
                    
					if($result['tags']){
						$tags = explode(',', $result['tags']);
						foreach($tags as $tag){
							$tags_array[] = array(
								'tag'  => trim($tag),
								'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
							);
						}
					}

					$data['articles'][] = array(
						'article_id'  => $result['article_id'],
						'title'        => $result['title'],
						'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
						'date_published'   =>  $result['date_published'],
						'tags' => $tags_array,
						'thumb'     => $thumb,
						'href'        => $this->url->link('bytao/blog', (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') .'article_id=' . $result['article_id'])
					);
				}
			}
            
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->home(true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_blog'),
				'href' => $this->url->link('bytao/blog')
			);
            
			$path = '';
			if(isset($this->request->get['path'])){
				$path = $this->request->get['path'];
			} else if(!empty ($article_categories)){
				$first_category = reset($article_categories);
				$path = $this->model->getCategoryPath($first_category['category_id']);
			}
                

			if($path){
				$url = '';

				$path_new = '';

				$parts = explode('_', (string)$path);

				$category_id = (int)array_pop($parts);

				foreach($parts as $path_id){
					if(!$path){
						$path_new = (int)$path_id;
					} else{
						$path_new .= '_' . (int)$path_id;
					}

					$category_info = $this->model->getCategory($path_id);

					if($category_info){
						$data['breadcrumbs'][] = array(
							'text' => $category_info['name'],
							'href' => $this->url->link('bytao/blog', 'path=' . $path_new . $url)
						);
					}
				}
                
				$category_info = $this->model->getCategory($category_id);
				if($category_info){
					// Set the last category breadcrumb
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('bytao/blog', 'path=' . $path)
					);
				}
			} 
            
			$url = '';
			$this->document->setTitle($article_info['meta_title']);
			$this->document->setDescription($article_info['meta_description']);
			$this->document->setKeywords($article_info['meta_keyword']);

			$data['heading_title'] = $article_info['title'];


			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $article_info['title'],
				'href' => $this->url->link('bytao/blog', 'article_id=' . $article_info['article_id'])
			);
			$data['toblog'] = $this->url->link('bytao/blog');
			$data['continue'] = $this->url->home(true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header');

			if($this->settings['comments_engine'] == 'FACEBOOK'){
				$data['header'] = $this->injectFacebookMeta($data['header']);
			}
			return $this->load->view('bytao/article_detail/default', $data);
	}
	
	private function getArticles(){
		$data['config'] = $this->config;
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/css/blog/article.css');
		$this->log->write('articles:');
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
        
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ba.date_published';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_quantity'] = $this->language->get('text_quantity');
		$data['text_sort'] = $this->language->get('text_sort');
		$data['text_limit'] = $this->language->get('text_limit');
		$data['text_comments'] = $this->language->get('text_comments');
		$data['text_read_more'] = $this->language->get('text_read_more');
		$data['text_posted_by'] = $this->language->get('text_posted_by');
		$data['text_category'] = $this->language->get('text_category');
		$data['text_tags'] = $this->language->get('text_tags');
		$data['text_no_comments'] = $this->language->get('text_no_comments');
		$data['text_leave_reply'] = $this->language->get('text_leave_reply');
		$data['text_required_info'] = $this->language->get('text_required_info');
		$data['text_name'] = $this->language->get('text_name');
		$data['text_email'] = $this->language->get('text_email');
		$data['text_content'] = $this->language->get('text_content');
		$data['text_related_products'] = $this->language->get('text_related_products');
		$data['text_related_articles'] = $this->language->get('text_related_articles');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_list'] = $this->language->get('button_list');
		$data['button_grid'] = $this->language->get('button_grid');
		$data['button_read_more'] = $this->language->get('button_read_more');
		$data['button_post_comment'] = $this->language->get('button_post_comment');
		$data['button_cart'] = $this->language->get('button_cart');
        
		$data['settings'] = $this->settings;
        $filter_data = array(
			'filter_title'  => $filter_title,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * 20,
			'limit' => 20
		);
		$data['articles'] = array();
		
		
		$article_total = $this->model->getTotalArticles($filter_data);
		$results = $this->model->getArticles($filter_data);
		
       	if(!empty($results)){
			foreach($results as $result){
					$thumb = false;
					if(!empty($result['image'])){
						$thumb = $result['image'];
					}
					if($thumb){
						$thumb = $this->model_tool_image->resize($thumb, 537, 321);
					}
                    
					$tags_array = array();
                    
					if($result['tags']){
						$tags = explode(',', $result['tags']);
						foreach($tags as $tag){
							$tags_array[] = array(
								'tag'  => trim($tag),
								'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
							);
						}
					}

					$data['articles'][] = array(
						'article_id'  		=> $result['articleId'],
						'title'        		=> $result['title'],
						'description' 		=> html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
						'date_published'   	=>  $result['date_published'],
						'tags' 				=> $tags_array,
						'thumb'     		=> $thumb,
						'href'       		=> $this->url->link('bytao/blog', (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') .'article_id=' . $result['article_id'])
					);
				}
			}
            
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->home(true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_blog'),
				'href' => $this->url->link('bytao/blog')
			);
			
			$url = '';

				if(isset($this->request->get['filter'])){
					$url .= '&filter=' . $this->request->get['filter'];
				}

				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}
				
			$pagination = new Pagination();
			$pagination->total = $article_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('bytao/blog', $url . '&page={page}');

			$data['pagination'] = $pagination->render();
			
			
            /*
			$path = '';
			if(isset($this->request->get['path'])){
				$path = $this->request->get['path'];
			} else if(!empty ($article_categories)){
				$first_category = reset($article_categories);
				$path = $this->model->getCategoryPath($first_category['category_id']);
			}
                

			if($path){
				$url = '';

				$path_new = '';

				$parts = explode('_', (string)$path);

				$category_id = (int)array_pop($parts);

				foreach($parts as $path_id){
					if(!$path){
						$path_new = (int)$path_id;
					} else{
						$path_new .= '_' . (int)$path_id;
					}

					$category_info = $this->model->getCategory($path_id);

					if($category_info){
						$data['breadcrumbs'][] = array(
							'text' => $category_info['name'],
							'href' => $this->url->link('bytao/blog', 'path=' . $path_new . $url)
						);
					}
				}
                
				$category_info = $this->model->getCategory($category_id);
				if($category_info){
					// Set the last category breadcrumb
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('bytao/blog', 'path=' . $path)
					);
				}
			} 
            */
			$url = '';
			$this->document->setTitle($this->language->get('text_blog'));
			$this->document->setDescription($this->language->get('text_blog'));
			$this->document->setKeywords($this->language->get('text_blog'));

			$data['heading_title'] = $this->language->get('text_blog');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header');

			
			return $this->load->view('bytao/article_list/default', $data);
	}
	
	private function getCategory(){
		
	}
	
	private function getCategories(){
		
	}
	
    public function write() {
		
		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			
			$this->getML('ML');
			
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 55)) {
				$json['error'] = $this->language->get('error_name');
			}
			if ((utf8_strlen($this->request->post['email']) < 3) || (utf8_strlen($this->request->post['email']) > 55)) {
				$json['error'] = $this->language->get('error_email');
			}
            
			if ( !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
				$json['error'] = $this->language->get('error_email_syntax');
			}
            

			if ((utf8_strlen($this->request->post['content']) < 10) || (utf8_strlen($this->request->post['content']) > 1000)) {
				$json['error'] = $this->language->get('error_content');
			}


			if (!isset($json['error'])) {
				if($this->model->getSetting('comments_approval') == 1){
                    $this->request->post['status'] = 0;
                    $success = $this->language->get('text_success_approval');
                } else{
                    $this->request->post['status'] = 1;
                    $success = $this->language->get('text_success');
                }
                
                
                
				$this->model->addComment($this->request->get['article_id'], $this->request->post);

				$json['success'] = $success;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
	public function widget_lastest($wData=array()){
		$this->getML('ML');
		$data['button_read_more'] = $this->language->get('button_read_more');
		$data['more'] = $this->url->link('bytao/blog');
		$data['articles'] = array();
		$results = $this->model->getLatestArticles($wData['limit']);
		foreach($results as $result){
			$thumb = false;
			if(!empty($result['image'])){
				$thumb = $result['image'];
			}
			if($thumb){
				$this->load->model('tool/image');
				$thumb = $this->model_tool_image->resize($thumb, $wData['thumb_width'], $wData['thumb_height']);
			}
            
			$tags_array = array();
            
			if($result['tags']){
				$tags = explode(',', $result['tags']);
				foreach($tags as $tag){
					$tags_array[] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
					);
				}
			}
            
			$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'title'        => $result['title'],
				'description' => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
				'date_published'   =>  $result['date_published'],
				'thumb'     => $thumb,
				'tags' => $tags_array,
				'comments_count'   =>  $this->model->getTotalCommentsForArticle($result['article_id']),
				'comments_text'  => $this->language->get('text_comments'),
				'href'        => $this->url->link('bytao/blog', 'article_id=' . $result['article_id'])
			);
		}
        
		return $this->load->view('bytao/blog_latest/default', $data);
        
	}
	
	public function widget_popular($wData=array()){
		$this->getML('ML');
		$data['button_read_more'] = $this->language->get('button_read_more');
		$data['articles'] = array();

		$results = $this->model->getPopularArticles($wData['limit']);

		foreach($results as $result){
            
			$thumb = false;
			if(!empty($result['image'])){
				$thumb = $result['image'];
			}
			if($thumb){
				$thumb = $this->model_tool_image->resize($thumb, $wData['thumb_width'], $wData['thumb_height']);
			}
            
			$tags_array = array();
            
			if($result['tags']){
				$tags = explode(',', $result['tags']);
				foreach($tags as $tag){
					$tags_array[] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('blog/blog', 'tag=' . trim($tag))
					);
				}
			}
            
			$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'title'        => $result['title'],
				'description' => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
				'date_published'   =>  $result['date_published'],
				'thumb'     => $thumb,
				'tags' => $tags_array,
				'comments_count'   =>  $this->model->getTotalCommentsForArticle($result['article_id']),
				'comments_text'  => $this->language->get('text_comments'),
				'href'        => $this->url->link('bytao/blog', (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') .'article_id=' . $result['article_id'])
			);
		}
        
		return $this->load->view('bytao/blog_popular/default', $data);
	}
	
	public function widget_related_post($wData=array()){
		$this->getML('ML');
		$data['button_read_more'] = $this->language->get('button_read_more');
		$data['articles'] = array();
		if(isset($wData['product_id'])){
			$results = $this->model->getArticleToProductRelated($wData['product_id'], $wData['limit']);
			foreach($results as $article_id){
				$result = $this->model->getArticle($article_id);
				$thumb = false;
				if(!empty($result['image'])){
					$thumb = $result['image'];
				}
				if($thumb){
					$this->load->model('tool/image');

					$thumb = $this->model_tool_image->resize($thumb, $wData['thumb_width'], $wData['thumb_height']);
				}
                
				$tags_array = array();
                
				if($result['tags']){
					$tags = explode(',', $result['tags']);
					foreach($tags as $tag){
						$tags_array[] = array(
							'tag'  => trim($tag),
							'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
						);
					}
				}

				$data['articles'][] = array(
					'article_id'  => $result['article_id'],
					'title'        => $result['title'],
					'description' => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
					'date_published'   =>  $result['date_published'],
					'thumb'     => $thumb,
					'tags' => $tags_array,
					'comments_count'   =>  $this->model->getTotalCommentsForArticle($result['article_id']),
					'comments_text'  => $this->language->get('text_comments'),
					'href'        => $this->url->link('bytao/blog', (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') .'article_id=' . $result['article_id'])
				);
			}
		}
        
		return $this->load->view('bytao/blog_related_post/default', $data);
	}
	
	public function widget_search($wData=array()){
		$this->getML('ML');
		
		$data['text_search'] = $this->language->get('text_search');
		$data['text_enter_keywords'] = $this->language->get('text_enter_keywords');
        
		$data['form_action'] = $this->url->link('bytao/blog');

	
		return $this->load->view('bytao/blog/blog_search', $data);
	}
	
	public function widget_tags($wData=array()){
		$this->getML('ML');
		
		$data['config'] = $this->config;
		$data['tags'] = array();
		$tags = $this->model->getPopularTags();
		if(!empty($tags)){
			foreach($tags as $tag => $value){
                
				$data['tags'][] = array(
					'tag'  => trim($tag),
					'value'  => $value,
					'href' => $this->url->link('bytao/blog', 'tag=' . trim($tag))
				);
			}
		}

		return $this->load->view('bytao/blog/blog_tags', $data);
	}
	
	public function widget_category($wData=array()){
		$this->getML('ML');
		$data['button_read_more'] = $this->language->get('button_read_more');
		$data['articles'] = array();
        
		if(isset($wData['path'])){
			$parts = explode('_', (string)$wData['path']);
		} else{
			$parts = array();
		}

		if(isset($parts[0])){
			$data['category_id'] = $parts[0];
		} else{
			$data['category_id'] = 0;
		}

		if(isset($parts[1])){
			$data['child_id'] = $parts[1];
		} else{
			$data['child_id'] = 0;
		}


		$data['categories'] = array();

		$categories = $this->model->getCategories(0);

		foreach($categories as $category){
			$children_data = array();

			$children = $this->model->getCategories($category['category_id']);

			foreach($children as $child){
				$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

				$children_data[] = array(
					'category_id' => $child['category_id'],
					'name' => $child['name'],
					'href' => $this->url->link('bytao/blog', 'path=' . $category['category_id'] . '_' . $child['category_id'])
				);
			}

			$filter_data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'],
				'children'    => $children_data,
				'href'        => $this->url->link('bytao/blog', 'path=' . $category['category_id'])
			);
		}

		return $this->load->view('bytao/blog/blog_category', $data);
        
        
	}
	
	private function injectFacebookMeta($header)
    {
        $meta = '<meta property="fb:admins" content="'.$this->settings['facebook_id'].'"/>
                ';
        $headers = explode('<head>', $header);
        $headers[0] .= $meta;
        return implode('', $headers);
    }

}	
	/*
	public function index_ex(){
		$notfound = false;
		$this->getML('ML');
		$data['HTTP_IMAGE'] = HTTP_IMAGE;
		
		if(isset($this->request->get['article_id']))
		{
			$this->response->setOutput($this->getArticle($this->request->get['article_id']));
            
		}elseif(isset($this->request->get['path'])){
			$category_id = 0;
			$url = '';

				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}
            
				if(isset($this->request->get['search'])){
					$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['tag'])){
					$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
				}

				$path = '';

				$parts = explode('_', (string)$this->request->get['path']);
				$category_info = $this->model->getCategory($category_id);
				$category_id = (int)array_pop($parts);

				foreach($parts as $path_id){
					if(!$path){
						$path = (int)$path_id;
					} else{
						$path .= '_' . (int)$path_id;
					}

					$category_info = $this->model->getCategory($path_id);

					if($category_info){
						$data['breadcrumbs'][] = array(
							'text' => $category_info['name'],
							'href' => $this->url->link('blog/blog', 'path=' . $path . $url)
						);
					}
				}
			
			
		else
		{
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/css/blog/blog.css');
			$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/masonry.pkgd.min.js');
			$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/imagesloaded.pkgd.min.js');
        	if(isset($this->request->get['filter'])){
				$filter = $this->request->get['filter'];
			} else{
				$filter = '';
			}

			if(isset($this->request->get['sort'])){
				$sort = $this->request->get['sort'];
			} else{
				$sort = 'ba.date_published';
			}

			if(isset($this->request->get['order'])){
				$order = $this->request->get['order'];
			} else{
				$order = 'DESC';
			}

			if(isset($this->request->get['page'])){
				$page = $this->request->get['page'];
			} else{
				$page = 1;
			}

			if(isset($this->request->get['limit'])){
				$limit = $this->request->get['limit'];
			} else{
				$limit = $this->settings['article_page_limit'];
			}

			if(isset($this->request->get['author'])){
				$author = $this->request->get['author'];
			} else{
				$author = 0;
			}
        
        
			if(isset($this->request->get['search'])){
				$search = $this->request->get['search'];
			} else{
				$search = '';
			}

			if(isset($this->request->get['tag'])){
				$tag = $this->request->get['tag'];
			} elseif(isset($this->request->get['search'])){
				$tag = $this->request->get['search'];
			} else{
				$tag = '';
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->home(true)
			);
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_blog'),
				'href' => $this->url->link($this->cPth)
			);
        
			if(isset($this->request->get['blog_path'])){
				$this->request->get['path'] = $this->request->get['blog_path'];
			}
        

			if(isset($this->request->get['path'])){
				$url = '';

				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}
            
				if(isset($this->request->get['search'])){
					$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['tag'])){
					$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
				}

				$path = '';

				$parts = explode('_', (string)$this->request->get['path']);

				$category_id = (int)array_pop($parts);

				foreach($parts as $path_id){
					if(!$path){
						$path = (int)$path_id;
					} else{
						$path .= '_' . (int)$path_id;
					}

					$category_info = $this->model->getCategory($path_id);

					if($category_info){
						$data['breadcrumbs'][] = array(
							'text' => $category_info['name'],
							'href' => $this->url->link('blog/blog', 'path=' . $path . $url)
						);
					}
				}
            
			} else{
				$category_id = 0;
			}

			$category_info = $this->model->getCategory($category_id);
			$this->getCategory($category_info);
			$url = '';
        
        
			if($category_info){
				$this->document->setTitle($category_info['meta_title']);
				$this->document->setDescription($category_info['meta_description']);
				$this->document->setKeywords($category_info['meta_keyword']);
				$this->document->addLink($this->url->link('bytao/blog', 'path=' . $this->request->get['path']), 'canonical');

				$data['heading_title'] = $category_info['name'];


				// Set the last category breadcrumb
				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('bytao/blog', 'path=' . $this->request->get['path'])
				);

				$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');



				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}
            
				if(isset($this->request->get['search'])){
					$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['tag'])){
					$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}

				$data['categories'] = array();

				$results = $this->model->getCategories($category_id);

				foreach($results as $result){
					$filter_data = array(
						'filter_category_id'  => $result['category_id'],
						'filter_sub_category' => true
					);

					$data['categories'][] = array(
						'name'  => $result['name'],
						'href'  => $this->url->link($this->cPth, 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
					);
				}
			}
			else{
				$this->document->setTitle($this->language->get('meta_title_default'));
				$this->document->setDescription($this->language->get('meta_descripion_default'));
				$this->document->setKeywords($this->language->get('meta_keywords_default'));
				$data['heading_title'] = $this->language->get('heading_blog');
			}
        
        
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_quantity'] = $this->language->get('text_quantity');
			$data['text_sort'] = $this->language->get('text_sort');
			$data['text_limit'] = $this->language->get('text_limit');
			$data['text_comments'] = $this->language->get('text_comments');
			$data['text_read_more'] = $this->language->get('text_read_more');
			$data['text_posted_by'] = $this->language->get('text_posted_by');
			$data['text_category'] = $this->language->get('text_category');

			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');
			$data['button_read_more'] = $this->language->get('button_read_more');
			$data['button_load_more'] = $this->language->get('button_load_more');

			$data['settings'] = $this->settings;
        
			$data['articles'] = array();

			$filter_data = array(
				'filter_title'       => $search,
				'filter_tag'         => $tag,
				'filter_category_id' => $category_id,
				'filter_author'      => $author,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			$article_total = $this->model->getTotalArticles($filter_data);

			$results = $this->model->getArticles($filter_data);

			if(!empty($results)){
				foreach($results as $result){
					$article_categories = $this->model->getArticleCategories($result['article_id']);
					$article_galleries = $this->model->getArticleGalleries($result['article_id']);
					$article_author = $this->model->getAuthor($result['author_id']);
					if($article_author){
						$article_author['href'] =  $this->url->link($this->cPth, 'author=' . $result['author_id']);
					}else{
						$article_author = false;
					}
                
					//prepare main image/video
					foreach($article_categories as &$category){
						$category['href'] = $this->url->link($this->cPth, 'path=' . $this->model->getCategoryPath($category['category_id']));
					}
                
					$thumb = false;
					if(!empty($result['image'])){
						$thumb = $result['image'];
					}
                
					if($thumb){
						$this->load->model('tool/image');
						$thumb = $this->model_tool_image->resize($thumb, $this->settings['gallery_related_article_width'], $this->settings['gallery_related_article_height']);
					}
                
					$tags_array = array();
                
					if($result['tags']){
						$tags = explode(',', $result['tags']);
						foreach($tags as $tag){
							$tags_array[] = array(
								'tag'  => trim($tag),
								'href' => $this->url->link($this->cPth, 'tag=' . trim($tag))
							);
						}
					}

					$data['articles'][] = array(
						'article_id'  => $result['article_id'],
						'title'        => $result['title'],
						'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
						'date_published'   =>  $result['date_published'],
						'tags'   =>  $tags_array,
						'thumb' => $thumb,
						'author'   =>  $article_author,
						'categories'   =>  $article_categories,
						'article_list_gallery_display'   =>  $result['article_list_gallery_display'],
						'gallery'   =>  $article_galleries,
						'comments_count'   =>  $this->model->getTotalCommentsForArticle($result['article_id']),
						'href'        => $this->url->link($this->cPth, (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') .'article_id=' . $result['article_id'] . $url)
					);
				}


				$url = '';

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['sorts'] = array();

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_default'),
					'value' => 'ba.sort_order-ASC',
					'href'  => $this->url->link($this->cPth, (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') . 'sort=ba.sort_order&order=ASC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_title_asc'),
					'value' => 'bad.title-ASC',
					'href'  => $this->url->link($this->cPth, (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '&' : '') . 'sort=bad.title&order=ASC' . $url)
				);



				$url = '';

				if(isset($this->request->get['filter'])){
					$url .= '&filter=' . $this->request->get['filter'];
				}

				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}

				if(isset($this->request->get['search'])){
					$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['tag'])){
					$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['search'])){
					$data['heading_title'] = $this->language->get('heading_blog') .  ' - ' . $this->request->get['search'];
				}


				$data['limits'] = array();

				$limits = array_unique(array($this->config->get('config_article_limit'), 25, 50, 75, 100));

				sort($limits);

				foreach($limits as $value){
					$data['limits'][] = array(
						'text'  => $value,
						'value' => $value,
						'href'  => $this->url->link('bytao/blog',  (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] : '') . $url . '&limit=' . $value)
					);
				}

				$url = '';

				if(isset($this->request->get['filter'])){
					$url .= '&filter=' . $this->request->get['filter'];
				}

				if(isset($this->request->get['sort'])){
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if(isset($this->request->get['order'])){
					$url .= '&order=' . $this->request->get['order'];
				}

				if(isset($this->request->get['limit'])){
					$url .= '&limit=' . $this->request->get['limit'];
				}

				if(isset($this->request->get['author'])){
					$url .= '&author=' . $this->request->get['author'];
				}

				if(isset($this->request->get['search'])){
					$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
				}

				if(isset($this->request->get['tag'])){
					$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
				}



				$pagination = new Pagination();
				$pagination->total = $article_total;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link('bytao/blog',  (isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] : '') . $url . '&page={page}');

				$data['pagination'] = $pagination->render();

				$data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

				$data['sort'] = $sort;
				$data['order'] = $order;
				$data['limit'] = $limit;
            
				$data['is_more'] =  $article_total >= $limit ? true : false;
            
				$data['template'] = $this->settings['article_list_template'];

				$data['continue'] = $this->url->home(true);

				$data['column_left'] = $this->load->controller('common/column_left');
				$data['column_right'] = $this->load->controller('common/column_right');
				$data['content_top'] = $this->load->controller('common/content_top');
				$data['content_bottom'] = $this->load->controller('common/content_bottom');
				$data['footer'] = $this->load->controller('bytao/footer');
				$data['header'] = $this->load->controller('bytao/header');

				// IF ajax request LOAD MORE
				if(isset($this->request->get['ajax_request']) && $this->request->get['ajax_request'] == 1){
					return $this->response->setOutput($this->load->view('bytao/article_list/default', $data));
				}

				$this->response->setOutput($this->load->view($this->cPth, $data));
           
           
			} 
			else{
				$notfound = true;
			}
		}
		
		if($notfound){
			$url = '';

			if(isset($this->request->get['path'])){
				$url .= '&path=' . $this->request->get['path'];
			}

			if(isset($this->request->get['filter'])){
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if(isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if(isset($this->request->get['order'])){
				$url .= '&order=' . $this->request->get['order'];
			}

			if(isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}

			if(isset($this->request->get['limit'])){
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link($this->cPth, $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link($this->cPth);

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header');

			// IF ajax request LOAD MORE
			if(isset($this->request->get['ajax_request']) && $this->request->get['ajax_request'] == 1){
				return '';
			}
			
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
    
    
*/
?>