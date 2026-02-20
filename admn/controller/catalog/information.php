<?php
namespace Opencart\Admin\Controller\Catalog;
class Information extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('catalog/information');

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
			'href' => $this->url->link('catalog/information', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['add'] = $this->url->link('catalog/information.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('catalog/information.delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/information', $data));
	}

	public function list(): void {
		$this->load->language('catalog/information');

		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
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

		$data['ADM']= $this->user->getGroupId();
		$data['action'] = $this->url->link('catalog/information.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['informations'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('catalog/information');

		$information_total = $this->model_catalog_information->getTotalInformations();

		$results = $this->model_catalog_information->getInformations($filter_data);

		foreach ($results as $result) {
			$data['informations'][] = [
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
				'type'          => $result['tname'],
				'sort_order'     => $result['sort_order'],
				'edit'           => $this->url->link('catalog/information.form', 'user_token=' . $this->session->data['user_token'] . '&information_id=' . $result['information_id'] . $url)
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

		$data['sort_title'] = $this->url->link('catalog/information.list', 'user_token=' . $this->session->data['user_token'] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/information.list', 'user_token=' . $this->session->data['user_token'] . '&sort=i.sort_order' . $url);
		$data['sort_type'] = $this->url->link('catalog/information.list', 'user_token=' . $this->session->data['user_token'] . '&sort=i.type_id' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $information_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('catalog/information.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($information_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($information_total - $this->config->get('config_pagination_admin'))) ? $information_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $information_total, ceil($information_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('catalog/information_list', $data);
	}

	public function form(): void {
		$this->load->language('catalog/information');
		
		$this->document->setTitle($this->language->get('heading_title'));

		//$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		//$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
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
		$this->document->addScript('view/javascript/summernote/mudur.js');
		
		$data['ADM']= $this->user->getGroupId();
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		
		$data['text_form'] = !isset($this->request->get['information_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('catalog/information', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['save'] = $this->url->link('catalog/information.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('catalog/information', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$this->load->model('catalog/information');
		
		if (isset($this->request->get['information_id'])) {
			

			$information_info = $this->model_catalog_information->getInformation($this->request->get['information_id']);
		}

		if (isset($this->request->get['information_id'])) {
			$data['information_id'] = (int)$this->request->get['information_id'];
		} else {
			$data['information_id'] = 0;
		}

		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();

		if (isset($this->request->get['information_id'])) {
			$data['information_description'] = $this->model_catalog_information->getDescriptions($this->request->get['information_id']);
		} else {
			$data['information_description'] = [];
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

		if (isset($this->request->get['information_id'])) {
			$data['information_store'] = $this->model_catalog_information->getStores($this->request->get['information_id']);
		} else {
			$data['information_store'] = [0];
		}

		if (!empty($information_info)) {
			$data['bottom'] = $information_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

		if (!empty($information_info)) {
			$data['status'] = $information_info['status'];
		} else {
			$data['status'] = true;
		}

		if (!empty($information_info)) {
			$data['sort_order'] = $information_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (!empty($information_info)) {
			$data['type_id'] = $information_info['type_id'];
		} else {
			$data['type_id'] = '';
		}

		if (isset($this->request->get['information_id'])) {
			$data['information_seo_url'] = $this->model_catalog_information->getSeoUrls($this->request->get['information_id']);
			
		} else {
			$data['information_seo_url'] = [];
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($this->request->get['information_id'])) {
			$data['information_layout'] = $this->model_catalog_information->getLayouts($this->request->get['information_id']);
		} else {
			$data['information_layout'] = [];
		}
		
		if (!empty($information_info)) {
			$data['bimage'] = $information_info['bimage'];
		} else {
			$data['bimage'] = '';
		}
		
		if (!empty($information_info)) {
			$data['timage'] = $information_info['timage'];
		} else {
			$data['timage'] = '';
		}
		
		if (!empty($information_info)) {
			$data['fimage'] = $information_info['fimage'];
		} else {
			$data['fimage'] = '';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (is_file(DIR_IMAGE . html_entity_decode($data['bimage'], ENT_QUOTES, 'UTF-8'))) {
			$data['bthumb'] = $this->model_tool_image->resize(html_entity_decode($data['bimage'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['bthumb'] = $data['placeholder'];
		}
		if (is_file(DIR_IMAGE . html_entity_decode($data['timage'], ENT_QUOTES, 'UTF-8'))) {
			$data['tthumb'] = $this->model_tool_image->resize(html_entity_decode($data['timage'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['tthumb'] = $data['placeholder'];
		}
		if (is_file(DIR_IMAGE . html_entity_decode($data['fimage'], ENT_QUOTES, 'UTF-8'))) {
			$data['fthumb'] = $this->model_tool_image->resize(html_entity_decode($data['fimage'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['fthumb'] = $data['placeholder'];
		}
		
		
		$data['types'] = $this->model_catalog_information->getInformationTypes();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/information_form', $data));
	}

	public function save(): void {
		$this->load->language('catalog/information');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/information')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['information_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['title'])) < 1) || (oc_strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post['information_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['information_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}

				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword);

				if ($seo_url_info && (!isset($this->request->post['information_id']) || $seo_url_info['key'] != 'information_id' || $seo_url_info['value'] != (int)$this->request->post['information_id'])) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
				
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model('catalog/information');

			if (!$this->request->post['information_id']) {
				$json['information_id'] = $this->model_catalog_information->addInformation($this->request->post);
			} else {
				$this->model_catalog_information->editInformation($this->request->post['information_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->load->language('catalog/information');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/information')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		foreach ($selected as $information_id) {
			if ($this->config->get('config_account_id') == $information_id) {
				$json['error'] = $this->language->get('error_account');
			}

			if ($this->config->get('config_checkout_id') == $information_id) {
				$json['error'] = $this->language->get('error_checkout');
			}

			if ($this->config->get('config_affiliate_id') == $information_id) {
				$json['error'] = $this->language->get('error_affiliate');
			}

			if ($this->config->get('config_return_id') == $information_id) {
				$json['error'] = $this->language->get('error_return');
			}

			$store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);

			if ($store_total) {
				$json['error'] = sprintf($this->language->get('error_store'), $store_total);
			}
		}

		if (!$json) {
			$this->load->model('catalog/information');

			foreach ($selected as $information_id) {
				$this->model_catalog_information->deleteInformation($information_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
