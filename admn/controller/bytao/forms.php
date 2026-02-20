<?php
namespace Opencart\Admin\Controller\Bytao;
class Forms extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/forms';
	private $C = 'forms';
	private $ID = 'forms_id';
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
	
	public function index() {
		$this->getML('ML');
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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

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
		$data['action'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

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
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'edit'           => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url)
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

		$data['sort_title'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url);
		$data['sort_type'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.type_id' . $url);

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
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form(): void {
		$this->getML('L');
		
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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		$this->getML('M');
		
		if (isset($this->request->get[$this->ID])) {
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
			$data['forms_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			$data['forms_seo_url'] = $this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
		} else {
			$data['forms_description'] = $data['forms_seo_url'] = [];
		}

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		if (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (!empty($item_info)) {
			$data['bimage'] = $item_info['bimage'];
		} else {
			$data['bimage'] = '';
		}
		
		if (!empty($item_info)) {
			$data['timage'] = $item_info['timage'];
		} else {
			$data['timage'] = '';
		}
		
		if (!empty($item_info)) {
			$data['fimage'] = $item_info['fimage'];
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
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

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

		foreach ($this->request->post['forms_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['title'])) < 1) || (oc_strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}
		}

		if ($this->request->post['forms_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['forms_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}

				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword);

				if ($seo_url_info && (!isset($this->request->post[$this->ID]) || $seo_url_info['key'] != $this->ID || $seo_url_info['value'] != (int)$this->request->post[$this->ID])) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
				
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->getML('M');

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
}
