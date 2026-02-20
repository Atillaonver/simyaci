<?php
namespace Opencart\Admin\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Blog extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/blog';
	private $C = 'blog';
	private $ID = 'blog_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	private $widgets =['lastest'=>'Son Yazılar','popular'=>'Popüler Yazılar','related_post'=>'İlgili Yazılar','search'=>'Arama','tags'=>'Etiketler','category'=>'Kategoriler']; 
	
	
	private function getFunc($f=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML='',$addI=''):void {
		if(!isset($this->session->data['store_id'])){
			$this->session->data['store_id']=$this->storeId;
		}else{
			$this->storeId = $this->session->data['store_id'];
		}
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':
				if($addI){
					$this->load->language($this->cPth); 
					$this->load->language($this->cPth.'/'.$addI); 
				} else {
					$this->load->language($this->cPth);
				} 
				break;
			case 'ML':
			case 'LM':
				if($addI){
					$this->load->language($this->cPth); 
					$this->load->language($this->cPth.'/'.$addI); 
				} else {
					$this->load->language($this->cPth);
				} 
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			default:
		}
		$this->document->addStyle('view/stylesheet/blog/blog.css');
		$this->load->model('tool/image');
	}
	
	 function install():void
    {
        $this->load->model('blog/setup');
        $this->model_blog_setup->install();
    }
    
    function uninstall():void
    {
        $this->load->model('blog/setup');
        $this->model_blog_setup->uninstall();
    }
    
   public function widget():void {
		$json= [];
		if(isset($this->request->get['sub'])){
			switch($this->request->get['sub']){
				case 'lastest':
					$json['subs'] = [
						'limit' =>[1,2,3,4,6,8],
						'thumb_width' =>'x',
						'thumb_height' =>'x'
					];
				break;
				case 'popular':
					$json['subs'] = [
						'limit' =>[1,2,3,4,6,8],
						'thumb_width' =>'x',
						'thumb_height' =>'x'
					];
				break;
				case 'related_post':
						$json['subs'] = [
						'limit' =>[1,2,3,4,6,8],
						'thumb_width' =>'x',
						'thumb_height' =>'x',
						'product_id' =>'x'
					];
				break;
				case 'search':
					$json['subs'] = [
						'limit' =>[1,2,3,4,6,8],
						'thumb_width' =>'x',
						'thumb_height' =>'x',
						'product_id' =>'x'
					];
				break;
				case 'tags':
					
				break;
				case 'category':
				break;
				
			}
		}else{
			foreach($this->widgets as $key => $fnc){
				$json['items'][] = [
					'item_id' 	=> $key,
					'title'     => $fnc
				];
			}
			$json['ope']='/';
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
    private function addBasicLang(array $array = [])
    {
        
        $data['link_blog_dashboard'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_categories'] = $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_articles'] = $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_authors'] = $this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_add_edit_comments'] = $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_general_settings'] = $this->url->link($this->cPth.'|settings', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['link_blog_category'] = $this->url->link('bytao/blog_category', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_search'] = $this->url->link('bytao/blog_search', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_latest_post'] = $this->url->link('bytao/blog_latest', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_popular_post'] = $this->url->link('bytao/blog_popular', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_product_related_post'] = $this->url->link('bytao/blog_related_post', 'user_token=' . $this->session->data['user_token'], true);
        $data['link_blog_popular_tags'] = $this->url->link('bytao/blog_tags', 'user_token=' . $this->session->data['user_token'], true);
        
        return array_merge($array, $data);
    }
    
	 
	public function index() {   
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		
        $data = $this->addBasicLang();
        //$front_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
        $front_url = new \Opencart\System\Library\Url($this->session->data['url']);
        
        
        $data['text_blog_front_information'] = sprintf($this->language->get('text_blog_front_information'), $front_url->link($this->cPth));

        $this->document->setTitle($this->language->get('text_blog_dashboard'));
            
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		    unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$data['action'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'], true);
			
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_blog_dashboard'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'], true)
		];
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['current_lang_id'] = $this->config->get('config_language_id');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/blog', $data));
	}
    
    // Article
    
    
    public function article_list()
    {
		$this->getML('ML','article');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getArticlesList();
	}

	public function article_add() {
		$this->getML('ML','article');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateArticleForm()) {
			$this->model->addArticle($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getArticleForm();
	}

	public function article_edit() {
		$this->getML('ML','article');
        $data = $this->addBasicLang();
		$this->document->setTitle($this->language->get('heading_title'));
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateArticleForm()) {
            $this->model->editArticle($this->request->get['article_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getArticleForm();
	}

	public function article_delete() {
		$this->getML('ML','article');
		$this->document->setTitle($this->language->get('heading_title'));
		if (isset($this->request->post['selected']) && $this->validateArticleDelete()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model->deleteArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getArticlesList();
	}

	protected function getArticlesList() {
        $data = $this->addBasicLang();
        
		if (isset($this->request->get['filter_title'])) {
			$filter_title = $this->request->get['filter_title'];
		} else {
			$filter_title = null;
		}
        
        
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_published';
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

		$url = '';
        
        
        if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . urlencode(html_entity_decode($this->request->get['filter_title'], ENT_QUOTES, 'UTF-8'));
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

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_articles'),
			'href' => $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['add'] = $this->url->link($this->cPth.'|article_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link($this->cPth.'|article_delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['articles'] = [];

		$filter_data = [
			'filter_title'  => $filter_title,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$article_total = $this->model->getTotalArticles($filter_data);

		$results = $this->model->getArticles($filter_data);
        
		foreach ($results as $result) {
			$data['articles'][] = [
				'article_id' => $result['article_id'],
				'title'        => $result['title'],
				'date_published'  => $result['date_published'],
				'author'  => '<a target="_blank" href="'.$this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . '&author_id=' . $result['author_id'] . $url, true).'">'.$result['author'] .'</a>',
                'comments'    => $this->model->getTotalArticleComments($result['article_id']),
				'edit'        => $this->url->link($this->cPth.'|article_edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $result['article_id'] . $url, true),
				'delete'      => $this->url->link($this->cPth.'|article_delete', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $result['article_id'] . $url, true)
			];
		}

		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';
        
        if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . urlencode(html_entity_decode($this->request->get['filter_title'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
        
        $data['user_token'] = $this->session->data['user_token'];

        $data['filter_title'] = $filter_title;
		$data['sort_title'] = $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . '&sort=title' . $url, true);
		$data['sort_date_published'] = $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . '&sort=date_published' . $url, true);
		$data['sort_author'] = $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url, true);

		$url = '';

        if (isset($this->request->get['filter_title'])) {
			$url .= '&filter_title=' . urlencode(html_entity_decode($this->request->get['filter_title'], ENT_QUOTES, 'UTF-8'));
		}
        
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $article_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.article_list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);



		$data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($article_total - $this->config->get('config_limit_admin'))) ? $article_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $article_total, ceil($article_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'|partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'|article_list', $data));
	}

	protected function getArticleForm() {
        $data = $this->addBasicLang();
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = [];
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = [];
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_articles'),
			'href' => $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		if (!isset($this->request->get['article_id'])) {
			$data['action'] = $this->url->link($this->cPth.'|article_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'|article_edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $this->request->get['article_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth.'|article_list', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$article_info = $this->model->getArticle($this->request->get['article_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
        
		// Categories
        
		if (isset($this->request->post['article_category'])) {
			$categories = $this->request->post['article_category'];
		} elseif (isset($this->request->get['article_id'])) {
			$categories = $this->model->getArticleCategories($this->request->get['article_id']);
		} else {
			$categories = [];
		}

		$data['article_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model->getCategory($category_id);

			if ($category_info) {
				$data['article_categories'][] = [
					'category_id' => $category_info['category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}
        
        //Author
        $data['authors'] = $this->model->getAuthors();

		if (isset($this->request->post['article_description'])) {
			$data['article_description'] = $this->request->post['article_description'];
		} elseif (isset($this->request->get['article_id'])) {
			$data['article_description'] = $this->model->getArticleDescriptions($this->request->get['article_id']);
		} else {
			$data['article_description'] = [];
		}

		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} else {
			$data['keyword'] = $this->model->getArticleSeoUrl($this->request->get['article_id']);
		} 		
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($article_info)) {
			$data['image'] = $article_info['image'];
		} else {
			$data['image'] = '';
		}

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($category_info) && is_file(DIR_IMAGE . $article_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($article_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['author_id'])) {
			$data['author_id'] = $this->request->post['author_id'];
		} elseif (!empty($article_info)) {
			$data['author_id'] = $article_info['author_id'];
		} else {
			$data['author_id'] = 0;
		}

		if (isset($this->request->post['auhtor'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($article_info)) {
			$data['sort_order'] = $article_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($article_info)) {
			$data['status'] = $article_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['status_comments'])) {
			$data['status_comments'] = $this->request->post['status_comments'];
		} elseif (!empty($article_info)) {
			$data['status_comments'] = $article_info['status_comments'];
		} else {
			$data['status_comments'] = true;
		}
        
		if (isset($this->request->post['article_list_gallery_display'])) {
			$data['article_list_gallery_display'] = $this->request->post['article_list_gallery_display'];
		} elseif (!empty($article_info)) {
			$data['article_list_gallery_display'] = $article_info['article_list_gallery_display'];
		} else {
			$data['article_list_gallery_display'] = true;
		}
        
		if (isset($this->request->post['date_added'])) {
			$data['date_added'] = $this->request->post['date_added'];
		} elseif (!empty($article_info)) {
			$data['date_added'] = $article_info['date_added'];
		} else {
			$data['date_added'] = date('Y-m-d H:i:s');
		}
        
		if (isset($this->request->post['date_updated'])) {
			$data['date_updated'] = $this->request->post['date_updated'];
		} elseif (!empty($article_info)) {
			$data['date_updated'] = $article_info['date_updated'];
		} else {
			$data['date_updated'] = date('Y-m-d H:i:s');
		}
        
		if (isset($this->request->post['date_published'])) {
			$data['date_published'] = $this->request->post['date_published'];
		} elseif (!empty($article_info)) {
			$data['date_published'] = $article_info['date_published'];
		} else {
			$data['date_published'] = date('Y-m-d H:i:s');
		}

        $this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        
        $this->load->model('catalog/product');
        
		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['article_id'])) {
			$products = $this->model->getProductRelated($this->request->get['article_id']);
		} else {
			$products = [];
		}
        
		$data['product_relateds'] = [];

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['product_relateds'][] = [
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				];
			}
		}
        
		if (isset($this->request->post['article_related'])) {
			$articles = $this->request->post['article_related'];
		} elseif (isset($this->request->get['article_id'])) {
			$articles = $this->model->getArticleRelated($this->request->get['article_id']);
		} else {
			$articles = [];
		}
        
		$data['article_relateds'] = [];

		foreach ($articles as $article_id) {
			$related_info = $this->model->getArticle($article_id);

			if ($related_info) {
				$data['article_relateds'][] = [
					'article_id' => $related_info['article_id'],
					'title'       => $related_info['title']
				];
			}
		}
        
        // Images
		if (isset($this->request->post['article_gallery'])) {
			$article_galleries = $this->request->post['article_gallery'];
		} elseif (isset($this->request->get['article_id'])) {
			$article_galleries = $this->model->getArticleGalleries($this->request->get['article_id']);
		} else {
			$article_galleries = [];
		}

		$data['article_galleries'] = [];

		foreach ($article_galleries as $article_gallery) {
			if ($article_gallery['path']) {
				$path = $article_gallery['path'];
				$thumb = $article_gallery['path'];
			} else {
				$path = '';
				$thumb = 'no_image.png';
			}

			$data['article_galleries'][] = [
				'path'      => $path,
				'thumb'      => $article_gallery['type'] == 'IMG' ? $this->model_tool_image->resize($thumb, 100, 100) : null,
                'type'       => $article_gallery['type'],
                'width'       => $article_gallery['width'],
                'height'       => $article_gallery['height'],
  				'sort_order' => $article_gallery['sort_order']
			];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'|partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/article_form', $data));
	}

	protected function validateArticleForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['article_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 2) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}
		
		if ($this->request->post['keyword']) {
			$this->load->model('design/seo_url');
				foreach ($this->request->post['keyword'] as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$language_id] = $this->language->get('error_unique');
						}
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $this->session->data['store_id']) && (!isset($this->request->get['article_id']) || ($seo_url['query'] != 'article_id=' . $this->request->get['article_id']))) {
								$this->error['keyword'][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
		}
        
		return !$this->error;
	}

	protected function validateArticleDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
    
	public function article_autocomplete() {
		$json = [];
		$this->getML('M');
		if (isset($this->request->get['filter_title'])) {
			$filter_data = [
				'filter_title' => $this->request->get['filter_title'],
				'sort'        => 'title',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model->getArticles($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'article_id' => $result['article_id'],
					'title'        => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['title'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
    
    
    // END ARTICLE
    
    
    
    // Category
    
    public function category_list()
    {
    	$this->getML('ML','category');
		$this->document->setTitle($this->language->get('heading_title'));
        $this->getCategoriesList();
	}

	public function category_add() {
		$this->getML('ML','category');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCategoryForm()) {
			$this->model->addCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCategoryForm();
	}

	public function category_edit() {
		$this->getML('ML','category');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCategoryForm()) {
			$this->model->editCategory($this->request->get['category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCategoryForm();
	}

	public function category_delete() {
		$this->getML('ML','category');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateCategoryDelete()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCategoriesList();
	}

	protected function getCategoriesList() {
        $data = $this->addBasicLang();
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_categories'),
			'href' => $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['add'] = $this->url->link($this->cPth.'|category_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link($this->cPth.'|category_delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['categories'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$category_total = $this->model->getTotalCategories();

		$results = $this->model->getCategories($filter_data);

		foreach ($results as $result) {
			$data['categories'][] = [
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'edit'        => $this->url->link($this->cPth.'|category_edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url, true),
				'delete'      => $this->url->link($this->cPth.'|category_delete', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url, true)
			];
		}

		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array) $this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $category_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.category_list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);
		

		$data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($category_total - $this->config->get('config_limit_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $category_total, ceil($category_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'|partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'|category_list', $data));
	}

	protected function getCategoryForm() {
        $data = $this->addBasicLang();
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = [];
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = [];
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_categories'),
			'href' => $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		if (!isset($this->request->get['category_id'])) {
			$data['action'] = $this->url->link($this->cPth.'|category_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'|category_edit', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $this->request->get['category_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth.'|category_list', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model->getCategory($this->request->get['category_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_description'] = $this->model->getCategoryDescriptions($this->request->get['category_id']);
		} else {
			$data['category_description'] = [];
		}

		if (isset($this->request->post['path'])) {
			$data['path'] = $this->request->post['path'];
		} elseif (!empty($category_info)) {
			$data['path'] = $category_info['path'];
		} else {
			$data['path'] = '';
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} else {
			$data['keyword'] = $this->model->getCategorySeoUrl($this->request->get['category_id']);
		} 	

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($category_info)) {
			$data['image'] = $category_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($category_info) && is_file(DIR_IMAGE . $category_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);


		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = true;
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/category_form', $data));
	}

	protected function validateCategoryForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}
		if ($this->request->post['keyword']) {
			$this->load->model('design/seo_url');
				foreach ($this->request->post['keyword'] as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$language_id] = $this->language->get('error_unique');
						}
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $this->session->data['store_id']) && (!isset($this->request->get['category_id']) || ($seo_url['query'] != 'blog_category_id=' . $this->request->get['category_id']))) {
								$this->error['keyword'][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
		}

		return !$this->error;
	}

	protected function validateCategoryDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


	public function category_autocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->getML('M');
			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				];
			}
		}

		$sort_order = [];

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
    
    // END CATEGORY
    
    
    // COMMENT

    public function comment_list() {
    	$this->getML('ML','comment');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getCommentList();
	}

	public function comment_add() {
		$this->getML('ML','comment');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCommentForm()) {
			$this->model->addComment($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCommentForm();
	}

	public function comment_edit() {
		$this->getML('ML','comment');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCommentForm()) {
			$this->model->editComment($this->request->get['comment_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCommentForm();
	}

	public function comment_delete() {
		$this->getML('ML','comment');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateCommentDelete()) {
			foreach ($this->request->post['selected'] as $comment_id) {
				$this->model->deleteComment($comment_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getCommentList();
	}

	protected function getCommentList() {
        $data = $this->addBasicLang();
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
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

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_comments'),
			'href' => $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'], true)
		];
        
		$data['add'] = $this->url->link($this->cPth.'|comment_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link($this->cPth.'|comment_delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['comments'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$comment_total = $this->model->getTotalComments();

		$results = $this->model->getComments($filter_data);

        
		foreach ($results as $result) {
            $article_info = $this->model->getArticle($result['article_id']);
            if(isset($article_info['title'])){
				$data['comments'][] = [
					'comment_id' => $result['comment_id'],
					'content'            => $result['content'],
					'name'            => $result['name'],
					'email'            => $result['email'],
					'status'      => $result['status'],
					'date_added'      => $result['date_added'],
	                'article_title'     => $article_info['title'],
	                'article_href'     => $this->url->link($this->cPth.'|article_edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $result['article_id'], true),
					'edit'            => $this->url->link($this->cPth.'|comment_edit', 'user_token=' . $this->session->data['user_token'] . '&comment_id=' . $result['comment_id'] . $url, true)
				];
			}
			

		}

		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_email'] = $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_date_added'] = $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
		$data['sort_status'] = $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $comment_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.comment_list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($comment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($comment_total - $this->config->get('config_limit_admin'))) ? $comment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $comment_total, ceil($comment_total / $this->config->get('config_limit_admin')));
        
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/comment_list', $data));
	}

	protected function getCommentForm() {
        $data = $this->addBasicLang();
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['comment_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
        
		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}


		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
        
        $this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
        

		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_comments'),
			'href' => $this->url->link($this->cPth.'|comment_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		if (!isset($this->request->get['comment_id'])) {
			$data['action'] = $this->url->link($this->cPth.'|comment_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'|comment_edit', 'user_token=' . $this->session->data['user_token'] . '&comment_id=' . $this->request->get['comment_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth.'|comment', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['comment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$comment_info = $this->model->getComment($this->request->get['comment_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($comment_info)) {
			$data['name'] = $comment_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($comment_info)) {
			$data['email'] = $comment_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['content'])) {
			$data['content'] = $this->request->post['content'];
		} elseif (!empty($comment_info)) {
			$data['content'] = $comment_info['content'];
		} else {
			$data['content'] = '';
		}
        
        if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($comment_info)) {
			$data['status'] = $comment_info['status'];
		} else {
			$data['status'] = true;
		}

        
        $article_info = $this->model->getArticle($comment_info['article_id']);
        $data['article_title'] = $article_info['title'];
        $data['article_href'] = $this->url->link($this->cPth.'|article_edit', 'user_token=' . $this->session->data['user_token'] . '&article_id=' . $comment_info['article_id'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/comment_form', $data));
	}

	protected function validateCommentForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}


		return !$this->error;
	}

	protected function validateCommentDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

    
    // END COMMENT
    
    
     // AUTHOR

    public function author_list() {
    	$this->getML('ML','author');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getAuthorList();
	}

	public function author_add() {
		$this->getML('ML','author');
		$this->document->setTitle($this->language->get('heading_title'));
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAuthorForm()) {
			$this->model->addAuthor($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getAuthorForm();
	}

	public function author_edit() {
		$this->getML('ML','author');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAuthorForm()) {
			$this->model->editAuthor($this->request->get['author_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getAuthorForm();
	}

	public function author_delete() {
		$this->getML('ML','author');
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateAuthorDelete()) {
			foreach ($this->request->post['selected'] as $author_id) {
				$this->model->deleteAuthor($author_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getAuthorList();
	}

	protected function getAuthorList() {
        $data = $this->addBasicLang();
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_authors'),
			'href' => $this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'], true)
		];
        
		$data['add'] = $this->url->link($this->cPth.'|author_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link($this->cPth.'|author_delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['authors'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		];

		$author_total = $this->model->getTotalAuthors();

		$results = $this->model->getAuthors($filter_data);

		foreach ($results as $result) {
			$data['authors'][] = [
				'author_id' => $result['author_id'],
				'name'            => $result['name'],
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link($this->cPth.'|author_edit', 'user_token=' . $this->session->data['user_token'] . '&author_id=' . $result['author_id'] . $url, true)
			];
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = [];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $author_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.author_list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($author_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($author_total - $this->config->get('config_limit_admin'))) ? $author_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $author_total, ceil($author_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'|partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'|author_list', $data));
	}

	protected function getAuthorForm() {
        $data = $this->addBasicLang();
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['author_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');

		$data['entry_name'] = $this->language->get('entry_name');
        $data['entry_description'] = $this->language->get('entry_description');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_customer_group'] = $this->language->get('entry_customer_group');


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
        
        $this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
        
        
        if (isset($this->request->post['author_description'])) {
			$data['author_description'] = $this->request->post['author_description'];
		} elseif (isset($this->request->get['author_id'])) {
			$data['author_description'] = $this->model->getAuthorDescriptions($this->request->get['author_id']);
		} else {
			$data['author_description'] = [];
		}

		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_authors'),
			'href' => $this->url->link($this->cPth.'|author_list', 'user_token=' . $this->session->data['user_token'], true)
		];

		if (!isset($this->request->get['author_id'])) {
			$data['action'] = $this->url->link($this->cPth.'|author_add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link($this->cPth.'|author_edit', 'user_token=' . $this->session->data['user_token'] . '&author_id=' . $this->request->get['author_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link($this->cPth.'|author', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['author_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$author_info = $this->model->getAuthor($this->request->get['author_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($author_info)) {
			$data['name'] = $author_info['name'];
		} else {
			$data['name'] = '';
		}


		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($author_info)) {
			$data['image'] = $author_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($author_info) && is_file(DIR_IMAGE . $author_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($author_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($author_info)) {
			$data['sort_order'] = $author_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/author_form', $data));
	}

	protected function validateAuthorForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}


		return !$this->error;
	}

	protected function validateAuthorDelete() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

    
    // END AUTHOR
    
    // SETTIGNGS
    
    public function settings() {
    	$this->getML('ML','settings');
		$this->document->setTitle($this->language->get('heading_title'));

		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSettingsForm()) {
            $this->model->editSettings($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			$this->response->redirect($this->url->link($this->cPth.'|settings', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getSettingsForm();
	}
	
    
	protected function getSettingsForm() {
        $data = $this->addBasicLang();
        
		$data['heading_title'] = $this->language->get('heading_title');


		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		    unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
        
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = [];
		}

		$url = '';


		$data['breadcrumbs'] = $this->breadscrumbs();
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_settings'),
			'href' => $this->url->link($this->cPth.'|settings', 'user_token=' . $this->session->data['user_token'], true)
		];
		

        $data['action'] = $this->url->link($this->cPth.'|settings', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['cancel'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url, true);

        $settings_info = $this->model->getSettings();

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');
        
        
		$data['article_list_templates'] = [];
		$data['article_detail_templates'] = [];
		$data['article_related_templates'] = [];
		
		$data['templates'] = [];

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);

		foreach ($directories as $directory) {
		     $directory = basename($directory);
			$data['templates'][] = $directory;
			$files_article_list = glob(DIR_CATALOG . 'view/theme/' . $directory . '/template/bytao/article_list/*.twig');
			$files_article_detail = glob(DIR_CATALOG . 'view/theme/' . $directory . '/template/bytao/article_detail/*.twig');
			$files_article_related = glob(DIR_CATALOG . 'view/theme/' . $directory . '/template/bytao/article_related/*.twig');

			if(!empty($files_article_list)) {
			     $data['article_list_templates'][$directory] = [];
			     foreach ($files_article_list as $file) {
			          $data['article_list_templates'][$directory][] = basename($file);
			     }
			}
			
			if(!empty($files_article_detail)) {
			     $data['article_detail_templates'][$directory] = [];
			     foreach ($files_article_detail as $file) {
			          $data['article_detail_templates'][$directory][] = basename($file);
			     }
			}
            
			if(!empty($files_article_related)) {
			     $data['article_related_templates'][$directory] = [];
			     foreach ($files_article_related as $file) {
			          $data['article_related_templates'][$directory][] = basename($file);
			     }
			}
		}
          

		if (isset($this->request->post['article_list_template'])) {
			$data['article_list_template'] = $this->request->post['article_list_template'];
		} elseif (!empty($settings_info)) {
			$data['article_list_template'] = $settings_info['article_list_template'];
		} else {
			$data['article_list_template'] = 'default.tpl';
		}
          

		if (isset($this->request->post['article_detail_template'])) {
			$data['article_detail_template'] = $this->request->post['article_detail_template'];
		} elseif (!empty($settings_info)) {
			$data['article_detail_template'] = $settings_info['article_detail_template'];
		} else {
			$data['article_detail_template'] = 'default.tpl';
		}

		if (isset($this->request->post['article_related_template'])) {
			$data['article_related_template'] = $this->request->post['article_related_template'];
		} elseif (!empty($settings_info)) {
			$data['article_related_template'] = $settings_info['article_related_template'];
		} else {
			$data['article_related_template'] = 'default.tpl';
		}

		if (isset($this->request->post['article_page_limit'])) {
			$data['article_page_limit'] = $this->request->post['article_page_limit'];
		} elseif (!empty($settings_info)) {
			$data['article_page_limit'] = $settings_info['article_page_limit'];
		} else {
			$data['article_page_limit'] = 5;
		}
        

		if (isset($this->request->post['article_related_status'])) {
			$data['article_related_status'] = $this->request->post['article_related_status'];
		} elseif (!empty($settings_info)) {
			$data['article_related_status'] = $settings_info['article_related_status'];
		} else {
			$data['article_related_status'] = 1;
		}
        

		if (isset($this->request->post['article_scroll_related'])) {
			$data['article_scroll_related'] = $this->request->post['article_scroll_related'];
		} elseif (!empty($settings_info)) {
			$data['article_scroll_related'] = $settings_info['article_scroll_related'];
		} else {
			$data['article_scroll_related'] = 1;
		}

		if (isset($this->request->post['article_related_per_row'])) {
			$data['article_related_per_row'] = $this->request->post['article_related_per_row'];
		} elseif (!empty($settings_info)) {
			$data['article_related_per_row'] = $settings_info['article_related_per_row'];
		} else {
			$data['article_related_per_row'] = 6;
		}

		if (isset($this->request->post['product_related_status'])) {
			$data['product_related_status'] = $this->request->post['product_related_status'];
		} elseif (!empty($settings_info)) {
			$data['product_related_status'] = $settings_info['product_related_status'];
		} else {
			$data['product_related_status'] = 1;
		}
        

		if (isset($this->request->post['product_scroll_related'])) {
			$data['product_scroll_related'] = $this->request->post['product_scroll_related'];
		} elseif (!empty($settings_info)) {
			$data['product_scroll_related'] = $settings_info['product_scroll_related'];
		} else {
			$data['product_scroll_related'] = 1;
		}

		if (isset($this->request->post['product_related_per_row'])) {
			$data['product_related_per_row'] = $this->request->post['product_related_per_row'];
		} elseif (!empty($settings_info)) {
			$data['product_related_per_row'] = $settings_info['product_related_per_row'];
		} else {
			$data['product_related_per_row'] = 6;
		}
        

		if (isset($this->request->post['pagination_type'])) {
			$data['pagination_type'] = $this->request->post['pagination_type'];
		} elseif (!empty($settings_info)) {
			$data['pagination_type'] = $settings_info['pagination_type'];
		} else {
			$data['pagination_type'] = "STANDARD";
		}
        

		if (isset($this->request->post['comments_engine'])) {
			$data['comments_engine'] = $this->request->post['comments_engine'];
		} elseif (!empty($settings_info)) {
			$data['comments_engine'] = $settings_info['comments_engine'];
		} else {
			$data['comments_engine'] = "LOCAL";
		}

		if (isset($this->request->post['himage'])) {
			$data['disqus_name'] = $this->request->post['himage'];
		} elseif (!empty($settings_info)) {
			$data['himage'] = $settings_info['himage'];
		} else {
			$data['himage'] = '';
		}
		if ($data['himage'] && is_file(DIR_IMAGE . $data['himage'])) {
			$data['hthumb'] = $this->model_tool_image->resize($data['himage'], 100, 100);
		} else {
			$data['hthumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		
        if (isset($this->request->post['disqus_name'])) {
			$data['disqus_name'] = $this->request->post['disqus_name'];
		} elseif (!empty($settings_info)) {
			$data['disqus_name'] = $settings_info['disqus_name'];
		} else {
			$data['disqus_name'] = '';
		}
        
		if (isset($this->request->post['facebook_id'])) {
			$data['facebook_id'] = $this->request->post['facebook_id'];
		} elseif (!empty($settings_info)) {
			$data['facebook_id'] = $settings_info['facebook_id'];
		} else {
			$data['facebook_id'] = '';
		}
        
		if (isset($this->request->post['comments_approval'])) {
			$data['comments_approval'] = $this->request->post['comments_approval'];
		} elseif (!empty($settings_info)) {
			$data['comments_approval'] = $settings_info['comments_approval'];
		} else {
			$data['comments_approval'] = 1;
		}
        
		if (isset($this->request->post['author_description'])) {
			$data['author_description'] = $this->request->post['author_description'];
		} elseif (!empty($settings_info)) {
			$data['author_description'] = $settings_info['author_description'];
		} else {
			$data['author_description'] = 1;
		}
        
		if (isset($this->request->post['gallery_image_width'])) {
			$data['gallery_image_width'] = $this->request->post['gallery_image_width'];
		} elseif (!empty($settings_info)) {
			$data['gallery_image_width'] = $settings_info['gallery_image_width'];
		} else {
			$data['gallery_image_width'] = 1000;
		}
		if (isset($this->request->post['gallery_image_height'])) {
			$data['gallery_image_height'] = $this->request->post['gallery_image_height'];
		} elseif (!empty($settings_info)) {
			$data['gallery_image_height'] = $settings_info['gallery_image_height'];
		} else {
			$data['gallery_image_height'] = 400;
		}
        
		if (isset($this->request->post['gallery_youtube_width'])) {
			$data['gallery_youtube_width'] = $this->request->post['gallery_youtube_width'];
		} elseif (!empty($settings_info)) {
			$data['gallery_youtube_width'] = $settings_info['gallery_youtube_width'];
		} else {
			$data['gallery_youtube_width'] = 1000;
		}
		if (isset($this->request->post['gallery_youtube_height'])) {
			$data['gallery_youtube_height'] = $this->request->post['gallery_youtube_height'];
		} elseif (!empty($settings_info)) {
			$data['gallery_youtube_height'] = $settings_info['gallery_youtube_height'];
		} else {
			$data['gallery_youtube_height'] = 400;
		}
        
		if (isset($this->request->post['gallery_soundcloud_width'])) {
			$data['gallery_soundcloud_width'] = $this->request->post['gallery_soundcloud_width'];
		} elseif (!empty($settings_info)) {
			$data['gallery_soundcloud_width'] = $settings_info['gallery_soundcloud_width'];
		} else {
			$data['gallery_soundcloud_width'] = 1000;
		}
		if (isset($this->request->post['gallery_soundcloud_height'])) {
			$data['gallery_soundcloud_height'] = $this->request->post['gallery_soundcloud_height'];
		} elseif (!empty($settings_info)) {
			$data['gallery_soundcloud_height'] = $settings_info['gallery_soundcloud_height'];
		} else {
			$data['gallery_soundcloud_height'] = 170;
		}
          
          if (isset($this->request->post['gallery_related_article_width'])) {
          	$data['gallery_related_article_width'] = $this->request->post['gallery_related_article_width'];
          } elseif (!empty($settings_info)) {
          	$data['gallery_related_article_width'] = $settings_info['gallery_related_article_width'];
          } else {
          	$data['gallery_related_article_width'] = 100;
          }
          
          if (isset($this->request->post['gallery_related_article_height'])) {
          	$data['gallery_related_article_height'] = $this->request->post['gallery_related_article_height'];
          } elseif (!empty($settings_info)) {
          	$data['gallery_related_article_height'] = $settings_info['gallery_related_article_height'];
          } else {
          	$data['gallery_related_article_height'] = 100;
          }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $data['handy_box'] = $this->load->view($this->cPth.'/partial/header', $data);
		$this->response->setOutput($this->load->view($this->cPth.'/settings_form', $data));
	}
    
    protected function validateSettingsForm() {
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	
    protected function breadcrumbs():array {
    	$breadcrumbs = [];
		$breadcrumbs[] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];
				
		$breadcrumbs[][] = [
			'text' => $this->language->get('text_blog_dashboard'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'], true)
		];
		return $breadcrumbs;
	}
    
   
}
?>