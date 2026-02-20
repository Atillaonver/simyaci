<?php
namespace Opencart\Admin\Controller\Blog;
class ArticleCategory extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'blog/article_category';
	private $C = 'article_category';
	private $ID = 'article_category_id';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void{
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function install():void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	
	public function index(): void {
		$this->getML('L');

		$this->document->setTitle($this->language->get('heading_title'));

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

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['repair'] = $this->url->link($this->cPth.'.repair', 'user_token=' . $this->session->data['user_token']);
		$data['add'] = $this->url->link($this->cPth.'.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', 'user_token=' . $this->session->data['user_token']);
		
		
		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function list(): void {
		$this->getML('L');
		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		$this->getML('L');
		
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
			$page = (int)$this->request->get['page'];
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

		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['items'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->getML('M');

		$item_total = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data['items'][] = [
				$this->ID => $result[$this->ID],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'order'        => $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&'.$this->ID.'=' . $result[$this->ID] . $url, 'SSL'),
				'edit'        => $this->url->link($this->cPth.'.form', 'user_token=' . $this->session->data['user_token'] . '&'.$this->ID.'=' . $result[$this->ID] . $url)
			];
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

		$data['sort_name'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form(): void {
		$this->getML('L');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css');
		$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js');
		$this->document->addScript('//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js');
		$this->document->addStyle('view/javascript/summernote/summernote.min.css');
		$this->document->addScript('view/javascript/summernote/summernote.min.js');
		$this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
		$this->document->addScript('view/javascript/summernote/mudur.js?v3');
		
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link($this->cPth.'.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get[$this->ID])) {
			$this->getML('M');
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		}

		if (isset($this->request->get[$this->ID])) {
			$data[$this->ID] = (int)$this->request->get[$this->ID];
		} else {
			$data[$this->ID] = 0;
		}

		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();	

		if (isset($this->request->get[$this->ID])) {
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
		} else {
			$data[$this->C.'_description'] = [];
		}
		
		
		/*
		// Related
		if ($data[$this->ID]) {
			$item_relateds = $this->model->{$this->getFunc('get','Related')}($data[$this->ID]);
		} else {
			$item_relateds = [];
		}

		$data[$this->C.'_relateds'] = [];
		foreach ($item_relateds as $related_id) {
			$related_info = $this->model->{$this->getFunc('get')}($related_id);

			if ($related_info) {
				$data[$this->C.'_relateds'][] = [
					$this->ID => $related_info[$this->ID],
					'name'       => $related_info['name']
				];
			}
		}
		*/
		if (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (is_file(DIR_IMAGE . html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		if (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}


		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		$data[$this->C.'_seo_url'] = [];

		if (isset($this->request->get[$this->ID])) {
			$results = $this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
			
			foreach ($results as $language_id => $keyword) {
				$pos = strrpos($keyword, '/');
				if ($pos !== false) {
					$keyword = substr($keyword, $pos + 1);
				} else {
					$keyword = $keyword;
				}

				$data[$this->C.'_seo_url'][$language_id] = $keyword;
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	public function save(): void {
		$this->getML('L');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		$this->getML('M');

		
		if ($this->request->post[$this->C.'_seo_url']) {
			$this->load->model('design/seo_url');
			$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

			foreach ($this->request->post[$this->C.'_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id);

				if ($seo_url_info && (!isset($this->request->post[$this->ID]) || $seo_url_info['key'] != 'path')) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}($this->request->post[$this->ID], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function repair(): void {
		$this->getML('L');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->getML('M');

			$this->model_catalog_category->repairCategories();

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->getML('L');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->getML('M');
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->getML('M');

			$filter_data = [
				'filter_name' => '%' . $this->request->get['filter_name'] . '%',
				'filter_status' =>  isset($this->request->get['filter_status'])?$this->request->get['filter_status']:'' ,
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => (isset($this->request->get['limit'])?$this->request->get['limit']:15)
			];

			$results = $this->model->{$this->getFunc('get','s')}($filter_data);

			foreach ($results as $result) {
				$json[] = [
					$this->ID => $result[$this->ID],
					'name'        => $result['name']
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

}
