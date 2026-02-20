<?php
namespace Opencart\Admin\Controller\Bytao;
class Media extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/media';
	private $C = 'media';
	private $ID = 'media_id';
	private $Tkn = 'user_token';
	private $model ;

	private function getFunc($f='', $addi=''):string
	{
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}

	private function getML($ML=''):void
	{
		switch ($ML) {
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}

	public function index(): void
	{
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
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	
	public function index() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function install():void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}

	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getList() {
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/media', 'token=' . $this->session->data['token'] . $url)
		);

		$data['add'] = $this->url->link('bytao/media/add', 'token=' . $this->session->data['token'] . $url);
		$data['delete'] = $this->url->link('bytao/media/delete', 'token=' . $this->session->data['token'] . $url);

		$data['banners'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$media_total = $this->model_bytao_media->getTotalMedias();

		$results = $this->model_bytao_media->getMedias($filter_data);

		foreach ($results as $result) {
			$data['medias'][] = array(
			$this->ID => $result[$this->ID],
				'name'      => $result['name'],
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link('bytao/media/edit', 'token=' . $this->session->data['token'] . '&media_id=' . $result[$this->ID] . $url)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
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
			$data['selected'] = array();
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

		$data['sort_name'] = $this->url->link('bytao/media', 'token=' . $this->session->data['token'] . '&sort=name' . $url);
		$data['sort_status'] = $this->url->link('bytao/media', 'token=' . $this->session->data['token'] . '&sort=status' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $media_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('bytao/media', 'token=' . $this->session->data['token'] . $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($media_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($media_total - $this->config->get('config_limit_admin'))) ? $media_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $media_total, ceil($media_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_list.tpl', $data));
	}

	public function form():void {
		$this->getML('ML');
	}
			
	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((strlen(trim($value['title'])) < 1) || (strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}

			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->request->post[$this->C.'_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post[$this->C.'_seo_url'] as ${$this->ID} => $language) {
				
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$seo_url_ctrl = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, ${$this->ID}, $language_id);

						if ($seo_url_ctrl && (!isset($this->request->post[$this->ID]) || $seo_url_ctrl['key'] != $this->ID || $seo_url_ctrl['value'] != (int)$this->request->post[$this->ID])) {
							$json['error']['keyword_' . ${$this->ID} . '_' . $language_id] = $this->language->get('error_keyword');
						}
					} else {
						//$json['error']['keyword_' . ${$this->ID} . '_' . $language_id] = $this->language->get('error_seo');
					}
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
			
			foreach ($selected as $item) {
				$this->model->{$this->getFunc('delete')}($item);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}