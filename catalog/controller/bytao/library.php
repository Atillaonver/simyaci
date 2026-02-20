<?php
namespace Opencart\Catalog\Controller\Bytao;
class Library extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/library';
	private $C = 'library';
	private $ID = 'library_id';
	private $model ;
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function index(): void {
		$this->getML('ML');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		
		if (isset($this->request->get[$this->ID])) {
			$library_id = (int)$this->request->get[$this->ID];
		} else {
			$library_id = 0;
		}

		$library_info = $this->model->{$this->getFunc('get')}($library_id);
		
		if (isset($this->request->get['keyword'])) {
			$data['keyword'] = $keyword = $this->request->get['keyword'];
		} elseif (isset($this->request->post['keyword'])) {
			$data['keyword'] = $keyword = $this->request->post['keyword'];
		} else {
			$keyword = '';
		}
		
		if (isset($this->request->get['tag_word'])) {
			$data['tag_word'] = $tag = $this->request->get['tag_word'];
		}else {
			$data['tag_word'] = $tag = '';
		}

		if ($library_info) {
			$this->document->setTitle($library_info['meta_title']);
			$this->document->setDescription($library_info['meta_description']);
			$this->document->setKeywords($library_info['meta_keyword']);

			$data['breadcrumbs'][] = [
				'text' => $library_info['title'],
				'href' => $this->url->link($this->cPth, 'language=' . $this->config->get('config_language') . '&information_id=' .  $library_id)
			];

			$data['heading_title'] = $library_info['title'];

			$data['description'] = html_entity_decode($library_info['description'], ENT_QUOTES, 'UTF-8');
			
			
			
			
			$data['HTTP_IMAGE'] = HTTPS_IMAGE;
			
			$data['image'] = $library_info['image']?$library_info['image']:'';
			
			$data['himage'] = !isset($library_info['himage'])?(!$this->config->get('config_header_image')?$this->config->get('config_header_image'):''):$library_info['himage'];
			
			$data['fimage'] = !isset($library_info['fimage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$library_info['fimage'];
			
			$data['timage'] = !isset($library_info['timage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$library_info['timage'];
			

			$data['continue'] = $this->url->home();
			$data['search'] = $this->url->link('bytao/library.search', 'language=' . $this->config->get('config_language'));
			$data['tags'] = $this->getAllTags($tag);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$hData = [
					'ctrl'   => 'information',
					'route'   => $this->cPth,
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
		
			$this->response->setOutput($this->load->view($this->cPth, $data));
		} else if (!isset($this->request->get[$this->ID])) {
			
			
			$this->document->setTitle($this->language->get('text_title'));
			$this->document->setDescription($this->language->get('text_description'));
			$this->document->setKeywords($this->language->get('text_keyword'));
			
			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'p2c.sort_order';
			}

			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'ASC';
			}

			if (isset($this->request->get['page'])) {
				$page = (int)$this->request->get['page'];
			} else {
				$page = 1;
			}
			
			$limitall =FALSE;
			if (isset($this->request->get['limit']) && (int)$this->request->get['limit']) {
				$limit = (int)$this->request->get['limit'];
				$limitall =($this->request->get['limit']=='all')?TRUE:FALSE;
			} else {
				$limit = $this->config->get('config_pagination');
			}
			
			if (isset($this->request->get['keyword'])) {
				$data['keyword'] = $keyword = $this->request->get['keyword'];
			} elseif (isset($this->request->post['keyword'])) {
				$data['keyword'] = $keyword = $this->request->post['keyword'];
			} else {
				$keyword = '';
			}
				
			if (isset($this->request->get['tag_word'])) {
				$data['tag_word'] = $tag = $this->request->get['tag_word'];
			} else {
				$tag = '';
			}
			
			$url = '';

			if (isset($this->request->get['tag_word'])) {
				$url .= '&tag_word=' . $this->request->get['tag_word'];
			}
			if (isset($this->request->get['keyword'])) {
				$url .= '&keyword=' . $this->request->get['keyword'];
			}
			
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			
			
			$data['libraries'] =[];
			if ($limitall){
				$filter_data = [
					'sort'                => $sort,
					'order'               => $order
				];
			}else{		
				$filter_data = [
					'sort'                => $sort,
					'order'               => $order,
					'start'               => ($page - 1) * $limit,
					'limit'               => $limit
				];
			}
			
			$data['search'] = $this->url->link('bytao/library.search', 'language=' . $this->config->get('config_language'));
			$library_total = $this->model->{$this->getFunc('getTotal','s')}($filter_data);
			$results = $this->model->{$this->getFunc('get','s')}($filter_data);
			
			foreach($results AS $result){
				if (is_file(DIR_IMAGE . $result['image'])) {
					$thumb = $this->model_tool_image->resize($result['image'],400,400);
				}else{
					$thumb ='';
				}
				
				$data['libraries'][]=[
					'thumb'		=> $thumb,
					$this->ID	=> $result[$this->ID],
					'header'	=> $result['header'],
					'title'		=> $result['title'],
					'href'		=> $this->url->link($this->cPth.'|info','language=' . $this->config->get('config_language').'&'. $this->ID.'=' . $result[$this->ID].$url)
				];
			}
			
			$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $library_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('bytao/library', 'language=' . $this->config->get('config_language') . '&page={page}'.$url)
			]);
		
			
			$data['tags'] = $this->getAllTags($tag);
			$data['HTTP_IMAGE'] = HTTPS_IMAGE;
			$data['timage'] = !isset($library_info['timage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$library_info['timage'];
			$data['continue'] = $this->url->home();
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$hData = [
					'ctrl'   => 'library',
					'route'   => $this->cPth,
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
		
			$this->response->setOutput($this->load->view($this->cPth.'_list', $data));
		} 
		else 
		{
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link($this->cPth, 'language=' . $this->config->get('config_language') . '&information_id=' . $information_id)
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
			$hData = [
					'ctrl'   => 'not_found',
					'route'   => 'error/not_found',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function info(): void {
		if (isset($this->request->get[$this->ID])) {
			$information_id = (int)$this->request->get[$this->ID];
		} else {
			$information_id = 0;
		}

		$this->load->model('catalog/information');

		$library_info = $this->model_catalog_information->getInformation($information_id);

		if ($library_info) {
			$data['title'] = $library_info['title'];
			$data['description'] = html_entity_decode($library_info['description'], ENT_QUOTES, 'UTF-8');

			$this->response->addHeader('X-Robots-Tag: noindex');
			$this->response->setOutput($this->load->view('bytao/library_info', $data));
		}
	}
	
	public function infoJ(): void {
		$json = [];
			   
		if (isset($this->request->get[$this->ID])) {
			$information_id = (int)$this->request->get[$this->ID];
		} else {
			$information_id = 0;
		}

		$this->load->model('catalog/information');

		$library_info = $this->model_catalog_information->getInformation($information_id);

		if ($library_info) {
			$json['title'] = $library_info['title'];
			$data['description'] = html_entity_decode($library_info['description'], ENT_QUOTES, 'UTF-8');
			$json['view'] = $this->load->view('bytao/library_info', $data);
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function search(){
		$this->getML('ML');
		$this->document->setTitle($this->language->get('text_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keyword'));
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p2c.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
			
		if (isset($this->request->get['keyword'])) {
			$data['keyword'] = $keyword = $this->request->get['keyword'];
		} elseif (isset($this->request->post['keyword'])) {
			$data['keyword'] = $keyword = $this->request->post['keyword'];
		} else {
			$keyword = '';
		}
			
		if (isset($this->request->get['tag_word'])) {
			$data['tag_word'] = $tag = $this->request->get['tag_word'];
		} else {
			$tag = '';
		}
			
			$limitall =FALSE;
			if (isset($this->request->get['limit']) && (int)$this->request->get['limit']) {
				$limit = (int)$this->request->get['limit'];
				$limitall =($this->request->get['limit']=='all')?TRUE:FALSE;
			} else {
				$limit = $this->config->get('config_pagination');
			}
			
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			
			
			$data['libraries'] =[];
			if ($limitall){
				$filter_data = [
					'keyword'                => $keyword,
					'tag_word'                => $tag,
					'sort'                => $sort,
					'order'               => $order
				];
			}else{		
				$filter_data = [
					'sort'                => $sort,
					'order'               => $order,
					'keyword'             => $keyword,
					'tag_word'                => $tag,
					'start'               => ($page - 1) * $limit,
					'limit'               => $limit
				];
			}
			
			
			$url = '';

			if (isset($this->request->get['tag_word'])) {
				$url .= '&tag_word=' . $this->request->get['tag_word'];
			}
			if (isset($this->request->get['keyword'])) {
				$url .= '&keyword=' . $this->request->get['keyword'];
			}
			
			
			$library_total = $this->model->{$this->getFunc('getTotal','s')}($filter_data);
			$results = $this->model->{$this->getFunc('get','s')}($filter_data);
			
			foreach($results AS $result){
				if (is_file(DIR_IMAGE . $result['image'])) {
					$thumb = $this->model_tool_image->resize($result['image'],400,400);
				}else{
					$thumb ='';
				}
				
				$data['libraries'][]=[
					'thumb'		=> $thumb,
					$this->ID	=> $result[$this->ID],
					'header'	=> $result['header'],
					'title'		=> $result['title'],
					'href'		=> $this->url->link($this->cPth.'|info','language=' . $this->config->get('config_language').'&'. $this->ID.'=' . $result[$this->ID])
				];
			}
			
			$url = '';

			if (isset($this->request->get['tag_word'])) {
				$url .= '&tag_word=' . $this->request->get['tag_word'];
			}
			if (isset($this->request->get['keyword'])) {
				$url .= '&keyword=' . $this->request->get['keyword'];
			}
				
			$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $library_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('bytao/library', 'language=' . $this->config->get('config_language') . '&page={page}'.$url)
			]);
		
			$data['tags'] = $this->getAllTags($tag);
			$data['HTTP_IMAGE'] = HTTPS_IMAGE;
			$data['timage'] = !isset($library_info['timage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$library_info['timage'];
			$data['continue'] = $this->url->home();
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$hData = [
					'ctrl'   => 'library',
					'route'   => $this->cPth,
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
		
			$this->response->setOutput($this->load->view($this->cPth.'_list', $data));
	}
	
	private function getAllTags($tag_word){
		$allTags=[];
		$all_tags = $this->model->{$this->getFunc('get','Tags')}();
		
		foreach($all_tags as $Tag){
			
			$metakeywords = explode(',',html_entity_decode(trim($Tag['meta_keyword']), ENT_QUOTES, 'UTF-8'));
			foreach($metakeywords as $tag){
				if($tag){
					$counter = isset($allTags[$tag]['co'])?(int)$allTags[$tag]['co']+1:1;
					$allTags[$tag]= [
					'tag'	=> $tag,
					'href' => $this->url->link($this->cPth.'.search', 'language=' . $this->config->get('config_language') . '&tag_word=' .  trim($tag)),
					'issel' =>($tag_word==$tag)?1:0,
					'co'	=> $counter
				];
				}
				
			}
			
		}
		
		krsort($allTags,SORT_LOCALE_STRING);
		
		return $allTags;
	}
	
}