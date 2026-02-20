<?php
namespace Opencart\Admin\Controller\Bytao;
class ArtCategory extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/art_category';
	private $C = 'art_category';
	private $ID = 'art_category_id';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f=''):string {
		return $f;
	}
	
	private function getML($ML=''):void{
		$cPth = str_replace('_','',$this->cPth);
		switch($ML){
			case 'M':
				$this->load->model($cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':
				$this->load->language($this->cPth);
				$this->load->model(str_replace('_','',$cPth)); 
				$this->model = $this->{'model_'.str_replace('/','_',$cPth)};break;
			default:
		}
	}

	public function index() {
		$this->getML('ML');
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
		$this->getML('ML');
		
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'acd.name';
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

		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data[$this->Tkn] . $url,FALSE,FALSE);

		$data['Items'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$category_total = $this->model->{$this->getFunc('getTotalArtCategories')}();

		$results = $this->model->{$this->getFunc('getArtcategories')}($filter_data);

		foreach ($results as $result) {
			$data['Items'][] = [
				'art_category_id' => $result[$this->ID],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'order'        => $this->url->link($this->cPth.'.catorder', 'user_token=' . $this->session->data[$this->Tkn] . '&art_category_id=' . $result[$this->ID] . $url, 'SSL'),
				'edit'        => $this->url->link($this->cPth.'.form', 'user_token=' . $this->session->data[$this->Tkn] . '&art_category_id=' . $result[$this->ID] . $url)
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

		$data['sort_name'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data[$this->Tkn] . '&sort=name' . $url);
		$data['sort_sort_order'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data[$this->Tkn] . '&sort=sort_order' . $url);

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
			'url'   => $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($category_total - $this->config->get('config_pagination_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $category_total, ceil($category_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form(): void {
		$this->getML('ML');

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
		$this->document->addScript('view/javascript/summernote/mudur.js');
		
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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data[$this->Tkn])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, 'user_token=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['save'] = $this->url->link($this->cPth.'.save', 'user_token=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, 'user_token=' . $this->session->data[$this->Tkn] . $url);

		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('getArtcategory')}($this->request->get[$this->ID]);
		}

		if (!empty($item_info)) {
			$data[$this->ID] = (int)$item_info[$this->ID];
		} else {
			$data[$this->ID] = 0;
		}

		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		
		if (!empty($item_info)) {
			$data['art_category_description'] = $this->model->{$this->getFunc('getArtcategoryDescriptions')}($item_info[$this->ID]);
		} else {
			$data['art_category_description'] = [];
		}
		
		if (!empty($item_info)) {
			$data['art_category_seo_url'] = $this->model->{$this->getFunc('getSeoUrls')}($item_info[$this->ID]);
		} else {
			$data['art_category_seo_url'] = [];
		}

		if (!empty($item_info)) {
			$data['path'] = $item_info['path'];
		} else {
			$data['path'] = '';
		}

		if (!empty($item_info)) {
			$data['parent_id'] = $item_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		if (!empty($item_info)) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (!empty($item_info) && is_file(DIR_IMAGE . $item_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($item_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (!empty($item_info)) {
			$data['top'] = $item_info['top'];
		} else {
			$data['top'] = 0;
		}

		if (!empty($item_info)) {
			$data['column'] = $item_info['column'];
		} else {
			$data['column'] = 1;
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
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['art_category_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		

		if (isset($this->request->post[$this->cPth]) && $this->request->post['parent_id']) {
			$results = $this->model->{$this->getFunc('getPaths')}($this->request->post['parent_id']);
			
			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->post[$this->cPth]) {
					$json['error']['parent'] = $this->language->get('error_parent');
					
					break;
				}
			}
		}

		if ($this->request->post['art_category_seo_url']) {
			$this->load->model('design/seo_url');
			$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

			foreach ($this->request->post['art_category_seo_url'] as $language_id => $keyword) {
				if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
				}
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id);

				if ($seo_url_info && (!isset($this->request->post[$this->ID]) || $seo_url_info['key'] != 'path' || $seo_url_info['value'] != $this->model->{$this->getFunc('getPath')}($this->request->post[$this->ID]))) {
					$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword_exists');
				}
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('addArtcategory')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('editArtcategory')}($this->request->post[$this->ID], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function repair(): void {
		$this->getML('ML');
		$json = [];
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->model->{$this->getFunc('repairArtcategories')}();
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->getML('ML');
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
			foreach ($selected as $category_id) {
				$this->model->{$this->getFunc('deleteArtcategory')}($category_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}





	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'bytao/art_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->getML('ML');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model->{$this->getFunc('getArtcategories')}($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'art_category_id' => $result['art_category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
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

	private function array_msort($array, $cols)	{
	    $colarr = [];
	    foreach ($cols as $col => $order) {
	        $colarr[$col] = [];
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
	    }
	    $eval = 'array_multisort(';
	    foreach ($cols as $col => $order) {
	        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
	    }
	    $eval = substr($eval,0,-1).');';
	    eval($eval);
	    $ret = [];
	    foreach ($colarr as $col => $arr) {
	        foreach ($arr as $k => $v) {
	            $k = substr($k,1);
	            if (!isset($ret[$k])) $ret[$k] = $array[$k];
	            $ret[$k][$col] = $array[$k][$col];
	        }
	    }
	    return $ret;

	}
}