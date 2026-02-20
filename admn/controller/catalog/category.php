<?php
namespace Opencart\Admin\Controller\Catalog;
class Category extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('catalog/category');

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
			'href' => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['repair'] = $this->url->link('catalog/category.repair', 'user_token=' . $this->session->data['user_token']);
		$data['add'] = $this->url->link('catalog/category.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('catalog/category.delete', 'user_token=' . $this->session->data['user_token']);
		
		
		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category', $data));
	}

	public function list(): void {
		$this->load->language('catalog/category');

		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		
		$this->document->addStyle('view/bytao/css/products.css?v14');
		$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
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

		$data['action'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['categories'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('catalog/category');

		$category_total = $this->model_catalog_category->getTotalCategories();

		$results = $this->model_catalog_category->getCategories($filter_data);

		foreach ($results as $result) {
			$data['categories'][] = [
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'order'        => $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url, 'SSL'),
				'edit'        => $this->url->link('catalog/category.form', 'user_token=' . $this->session->data['user_token'] . '&category_id=' . $result['category_id'] . $url)
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

		$data['sort_name'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url);

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
			'url'   => $this->url->link('catalog/category.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($category_total - $this->config->get('config_pagination_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $category_total, ceil($category_total / $this->config->get('config_pagination_admin')));
		
		$data['mpage_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=mpage');
		$data['catalog_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=catalog');
		$data['clearance_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=clearance');
		$data['new_arriwals_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=new_arriwals');
		$data['best_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=best');
		$data['gift_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=gift');
		$data['sale_order'] = $this->url->link('bytao/common.catorder', 'user_token=' . $this->session->data['user_token'] . '&typ=sale');

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('catalog/category_list', $data);
	}

	public function form(): void {
		$this->load->language('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

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
		$this->document->addScript('view/javascript/summernote/mudur.js?v4');
		$this->document->addStyle('view/bytao/css/baytao2.css?v8');
		$this->document->addStyle('view/bytao/css/products.css?v1');
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;


		$data['text_form'] = !isset($this->request->get['category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link('catalog/category.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['category_id'])) {
			$this->load->model('catalog/category');
			$category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
		}

		if (isset($this->request->get['category_id'])) {
			$data['category_id'] = (int)$this->request->get['category_id'];
		} else {
			$data['category_id'] = 0;
		}

		/*$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();*/
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();	

		if (isset($this->request->get['category_id'])) {
			$data['category_description'] = $this->model_catalog_category->getDescriptions($this->request->get['category_id']);
		} else {
			$data['category_description'] = [];
		}

		if (!empty($category_info)) {
			$data['path'] = $category_info['path'];
		} else {
			$data['path'] = '';
		}

		if (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$this->load->model('catalog/filter');

		if (isset($this->request->get['category_id'])) {
			$filters = $this->model_catalog_category->getFilters($this->request->get['category_id']);
		} else {
			$filters = [];
		}

		$data['category_filters'] = [];

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['category_filters'][] = [
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				];
			}
		}
		
		// Related
		if ($data['category_id']) {
			$category_relateds = $this->model_catalog_category->getRelated($data['category_id']);
		} else {
			$category_relateds = [];
		}

		$data['category_relateds'] = [];

		foreach ($category_relateds as $related_id) {
			$related_info = $this->model_catalog_category->getCategory($related_id);

			if ($related_info) {
				$data['category_relateds'][] = [
					'category_id' => $related_info['category_id'],
					'name'       => $related_info['name']
				];
			}
		}

		$this->load->model('catalog/information');

		
		$results = $this->model_catalog_information->getInformations(['type'  => '1']);
		foreach ($results as $result) {
			$data['size_charts'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
			];
		}
		if (!empty($category_info)) {
			$data['size_chart_id'] = $category_info['size_chart_id'];
		} else {
			$data['size_chart_id'] = '';
		}
		
		
		$results = $this->model_catalog_information->getInformations(['type'  => '2']);
		foreach ($results as $result) {
			$data['materials'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
			];
		}
		
		if (!empty($category_info)) {
			$data['material_id'] = $category_info['material_id'];
		} else {
			$data['material_id'] = '';
		}
		
		
		$results = $this->model_catalog_information->getInformations(['type'  => '3']);
		foreach ($results as $result) {
			$data['productcares'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
			];
		}
		if (!empty($category_info)) {
			$data['productcare_id'] = $category_info['productcare_id'];
		} else {
			$data['productcare_id'] = '';
		}
		
		
		$results = $this->model_catalog_information->getInformations(['type'  => '4']);
		foreach ($results as $result) {
			$data['measurements'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
			];
		}
		if (!empty($category_info)) {
			$data['measurement_id'] = $category_info['measurement_id'];
		} else {
			$data['measurement_id'] = '';
		}
		
		if (!empty($category_info)) {
			$data['ctype'] = $category_info['ctype'];
		} else {
			$data['ctype'] = '';
		}
		
		

		$this->load->model('setting/store');

		$data['stores'] = [];
		
		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}

		if (isset($this->request->get['category_id'])) {
			$data['category_store'] = $this->model_catalog_category->getStores($this->request->get['category_id']);
		} else {
			$data['category_store'] = [0];
		}

		if (!empty($category_info)) {
			$data['image'] = $category_info['image'];
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

		if (!empty($category_info)) {
			$data['top'] = $category_info['top'];
		} else {
			$data['top'] = 0;
		}

		if (!empty($category_info)) {
			$data['column'] = $category_info['column'];
		} else {
			$data['column'] = 1;
		}

		if (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}


		if (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = true;
		}

		$data['category_seo_url'] = [];

		if (isset($this->request->get['category_id'])) {
			$results = $this->model_catalog_category->getSeoUrls($this->request->get['category_id']);
			
			foreach ($results as $language_id => $keyword) {
				$pos = strrpos($keyword, '/');
				if ($pos !== false) {
					$keyword = substr($keyword, $pos + 1);
				} else {
					$keyword = $keyword;
				}

				$data['category_seo_url'][$language_id] = $keyword;
			}
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($this->request->get['category_id'])) {
			$data['category_layout'] = $this->model_catalog_category->getLayouts($this->request->get['category_id']);
		} else {
			$data['category_layout'] = [];
		}
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_form', $data));
	}

	public function save(): void {
		$this->load->language('catalog/category');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		$this->load->model('catalog/category');

		if (isset($this->request->post['category_id']) && $this->request->post['parent_id']) {
			$results = $this->model_catalog_category->getPaths($this->request->post['parent_id']);
			
			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->post['category_id']) {
					$json['error']['parent'] = $this->language->get('error_parent');
					
					break;
				}
			}
		}

		if ($this->request->post['category_seo_url']) {
			$this->load->model('design/seo_url');
			$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

			foreach ($this->request->post['category_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id);

				if ($seo_url_info && (!isset($this->request->post['category_id']) || $seo_url_info['key'] != 'path' || $seo_url_info['value'] != $this->model_catalog_category->getPath($this->request->post['category_id']))) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post['category_id']) {
				$json['category_id'] = $this->model_catalog_category->addCategory($this->request->post);
			} else {
				$this->model_catalog_category->editCategory($this->request->post['category_id'], $this->request->post);
			}
			
			//$this->cache->delete('module.category.' . (int)$this->config->get('config_language_id').'.'.$groupId.'.'.$this->config->get('config_store_id'));

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function repair(): void {
		$this->load->language('catalog/category');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/category');

			$this->model_catalog_category->repairCategories();

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->load->language('catalog/category');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/category');

			foreach ($selected as $category_id) {
				$this->model_catalog_category->deleteCategory($category_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = [
				'filter_name' => '%' . $this->request->get['filter_name'] . '%',
				'filter_status' =>  isset($this->request->get['filter_status'])?$this->request->get['filter_status']:'' ,
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => (isset($this->request->get['limit'])?$this->request->get['limit']:15)
			];

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = [
					'category_id' => $result['category_id'],
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



	public function preview(){

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = null;
		}
		
		if (isset($this->request->get['filter_item'])) {
			$filter_addcategory = $filter_item = $this->request->get['filter_item'];
		} else {
			$filter_addcategory = $filter_item = null;
		}
		
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
			$data['filter_category'] = $filter_category;
		} else {
			$data['filter_category'] = '';
			$filter_category = null;
		}
		
		$filter_status = 1;
		

		$url = '';

		$filter_data =[
			'filter_name'	  		=> $filter_name,
			'filter_model'	  		=> $filter_model,
			'filter_price'	  		=> $filter_price,
			'filter_item'	  		=> $filter_item,
			'filter_quantity' 		=> $filter_quantity,
			'filter_status'   		=> $filter_status,
			'filter_category'   	=> $filter_category,
			'filter_type'   	=> $filter_addcategory,
			'sort'            		=> 'p2c.sort_order',
			'order'           		=> 'ASC'
			];
		
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('bytao/common');
		if($filter_addcategory){
			$results = $this->model_bytao_common->getAddProducts($filter_data);
		}else{
			$results = $this->model_catalog_product->getProducts($filter_data);
		}
		
		if(isset($this->request->post['cellrow'])) {
			$selrow = $this->request->post['cellrow'];
		} else if (isset($this->request->post['row'])) {
			$selrow = $this->request->post['row'];
		}else{
			$selrow = 0;
		}
		
		$row = isset($this->request->post['row'])?$this->request->post['row']:0;
		$cells = isset($this->request->post['cells'])?$this->request->post['cells']:0;
		$rowOrders = $this->model_bytao_common->getCategoryRowOrder($filter_category,$filter_addcategory);
		
		// duzeltme
		if($row != 0 )
		{
			$uRows = explode(',',$rowOrders);
			if(count($uRows)>$row){
				$rowOrders='';
				$uRowCount=0;
				foreach ($uRows as $uRow) {
					$uRowCount++;
					$nRow = explode(':',$uRow);
					
					if(isset($nRow[1])){
						if($nRow[0] == $row){
							$rowOrders.= $uRowCount.':'.$cells.',';
						}else{
							$rowOrders.= isset($nRow[1]) ? $uRowCount.':'.$nRow[1].',':$uRowCount.':8,';
						}
					}
				}
			}else{
				$rowOrders.= $row.':8,';
			}
		}
		
		$products= [];
		$rows_sub = explode(',',$rowOrders);
		$rows = $rows_sub;
		//$results = $this->model_catalog_product->getProducts($filter_data);
		$partCount = 0;
		
		if(count($rows_sub)==1){
			$rowsCount = 1;
			$rowOrders.= $rowsCount.':8,';
		}else{
			$rowsCount = 0;
		}
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 110, 110);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 110, 110);
			}

			$products[] = [
				'product_id' => $result['product_id'],
				'image'      => $image
			];
		}
		

		$rows = explode(',',$rowOrders);
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_bytao_common->setCategoryRowOrder($filter_category,$rowOrders,$filter_addcategory);
		}
		
		$content['1'] = '<div class="row1 prow"><div class="y100" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['2'] = '<div class="row2 prow"><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50 parent-row"><div class="h50 bos"></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50 bos"></div></div></div>';
		$content['3'] = '<div class="row3 prow"><div class="y50 bos"></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['4'] = '<div class="row4 prow"><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50"><div class="h50 bos"></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div></div></div>';
		$content['5'] = '<div class="row5 prow"><div class="y50 pRel"><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['6'] = '<div class="row6 prow"><div class="y25 bos"></div><div class="y25 bos"></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content[7] = '<div class="row7 prow"><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25 bos"></div><div class="y25 bos"></div></div>';
		$content['8'] = '<div class="row8 prow"><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div><div class="y25" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['9'] = '<div class="row9 prow"><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div><div class="y50" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['10'] = '<div class="row10 prow"><div class="y33" data-pid="::pid::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::"><img src="::IMG::" /></div><div class="y33" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$content['11'] = '<div class="row11 prow"><div class="y66" data-pid="::pid::"><img src="::IMG::" /></div><div class="y33"><div class="y100" data-pid="::pid::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::"><img src="::IMG::" /></div></div></div>';
		$content['12'] = '<div class="row12 prow"><div class="y33"><div class="y100" data-pid="::pid::"><img src="::IMG::" /></div><div class="y100" data-pid="::pid::"><img src="::IMG::" /></div></div><div class="y66" data-pid="::pid::"><img src="::IMG::" /></div></div>';
		$type_order = 8;
		$pContent='';
		$lContainer='';
		$data['html']='';
		if (isset($products)){ 
 			$pcount =0;
 			$partCount =0;
 			$nrow =1;
 			
	 		 foreach ($products as $product) { 
				if($pContent==''){
					$parts = explode(':',$rows[$nrow-1]);
				 	$type_order = isset($parts[1])?$parts[1]:8;
				 	$pContent = $content[$type_order];
				 	$cArrs = explode('::IMG::',$pContent);
				}
				else{
					$cArrs = explode('::IMG::',$pContent);
				}
				
				if(isset($cArrs[1]))
				{
					$s=0;
					$pC='';
					foreach($cArrs as $cArr)
					{
						if($s == 0){
							$pC.=str_replace('::pid::',$product['product_id'],$cArr).$product['image'];
						}else if($s == (count($cArrs)-1)){
							$pC.=$cArr;
						}else{
							$pC.= $cArr.'::IMG::';
						}
						$s++;
					}
					$pContent = $pC;
					
					$cArr = explode('::IMG::',$pContent);
					if(!isset($cArrs[1])){
						if($nrow == $selrow ){
							$active=' active';
						}else{
							$active='';
						}
						$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="8" data-row="'.$nrow.'" >'.$pContent.'</li>';
						$nrow++;
						$parts = explode(':',$rows[$nrow-1]);
						$type_order = isset($parts[1])?$parts[1]:8;
						$pContent =$content[$type_order];
					}
				}
				else
				{
					if($nrow == $selrow ){
						$active=' active';
					}else{
						$active='';
					}
					$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="'.$type_order.'" data-row="'.$nrow.'" >'.$pContent.'</li>';
					$nrow++;
					$parts = isset($rows[$nrow-1])?explode(':',$rows[$nrow-1]):array();
					$type_order = isset($parts[1])?$parts[1]:8;
					$pContent =$content[$type_order];
					$cArrs = explode('::IMG::',$pContent);
					if(isset($cArrs[1]))
					{
						$s=0;
						$pC='';
						foreach($cArrs as $cArr)
						{
							if($s == 0){
								$pC.=str_replace('::pid::',$product['product_id'],$cArr).$product['image'];
							}else if($s == (count($cArrs)-1)){
								$pC.=$cArr;
							}else{
								$pC.= $cArr.'::IMG::';
							}
							$s++;
						}
						$pContent = $pC;
					}
				}
				$pcount++;
			}
		
		if($nrow == $selrow ){
				$active=' active';
			}else{
				$active='';
			}
			$data['html'] .= '<li class="sort-order-content '.$active.'" data-cells="'.$type_order.'" data-row="'.$nrow.'" >'.str_replace('::IMG::','',$pContent).'</li>';
		}
		$json['s'] = $selrow;
		$json['v'] = $this->load->view('catalog/category_list_preview', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	

}
