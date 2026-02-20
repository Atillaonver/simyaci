<?php
namespace Opencart\Admin\Controller\Bytao;
class Campaign extends \Opencart\System\Engine\Controller {	
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/campaign';
	private $C = 'campaign';
	private $ID = 'campaign_id';
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
	
	public function index():void {
		
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

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		

		$data['list'] = $this->getList();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function list(): void {
		$this->getML('ML');
		$this->response->setOutput($this->getList());
	}
	
	protected function getList() {
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'c.name';
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
		
		$data['action'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn]. $url);
		
		$data[$this->C.'s'] = [];
		$start = $data['start']=(($page - 1) * (int) $this->config->get('config_limit_admin'));
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => $start,
			'limit' => $this->config->get('config_limit_admin')
		];

		$item_total = $this->model->{$this->getFunc('getTotal','s')}();
		$items = $this->model->{$this->getFunc('get','s')}($filter_data);
		
		$this->load->model('tool/image');
		$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		foreach ($items as $item) {
			$data[$this->C.'s'][] = [
				'campaign_id'  => $item['campaign_id'],
				'name'       => $item['name'],
				'campaign_type'       => $item['campaign_type'],
				'discount'   => $item['discount'],
				'date_start' => date($this->language->get('date_format_short'), strtotime($item['date_start'])),
				'date_end'   => date($this->language->get('date_format_short'), strtotime($item['date_end'])),
				'status'     => ($item['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'       => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&campaign_id=' . $item['campaign_id'] . $url)
			];
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_free'] = $this->language->get('text_free');
		$data['text_discount'] = $this->language->get('text_discount');
		$data['text_second'] = $this->language->get('text_second');
		$data['text_dsecond'] = $this->language->get('text_dsecond');

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		
		$data['sort_name'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.name' . $url, true);
		$data['sort_code'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.code' . $url, true);
		$data['sort_discount'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.discount' . $url, true);
		$data['sort_date_start'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.date_start' . $url,true);
		$data['sort_date_end'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.date_end' . $url, true);
		$data['sort_status'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=c.status' . $url, true);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form():void {
		$this->getML('ML');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
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
		

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		}

		if (isset($this->request->get[$this->ID])) {
			$data[$this->ID] = (int)$this->request->get[$this->ID];
		} else {
			$data[$this->ID] = 0;
		}
		
		if (!empty($item_info)) {
			$data['name'] = $item_info['name'];
		} else {
			$data['name'] = '';
		}

		if (!empty($item_info)) {
			$data['campaign_type'] = $item_info['campaign_type'];
		} else {
			$data['campaign_type'] = '';
		}
		
		if (!empty($item_info)) {
			$data['out_type'] = $item_info['out_type'];
		} else {
			$data['out_type'] = 0;
		}
		
		if (!empty($item_info)) {
			$data['type'] = $item_info['type'];
		} else {
			$data['type'] = '';
		}

		if (!empty($item_info)) {
			$data['discount'] = $item_info['discount'];
		} else {
			$data['discount'] = '';
		}

		if (!empty($item_info)) {
			$data['logged'] = $item_info['logged'];
		} else {
			$data['logged'] = '';
		}

		if (!empty($item_info)) {
			$data['shipping'] = $item_info['shipping'];
		} else {
			$data['shipping'] = '';
		}

		if (!empty($item_info)) {
			$data['total'] = $item_info['total'];
		} else {
			$data['total'] = '';
		}
		
		if (!empty($item_info)) {
			$data['buycat'] = $item_info['buycat'];
		} else {
			$data['buycat'] = '0';
		}
		
		if (!empty($item_info)) {
			$data['getcat'] = $item_info['getcat'];
		} else {
			$data['getcat'] = '0';
		}
		
		$data['campaign_buy_categories'] = [];
		$data['campaign_buy_products'] = [];
		$data['campaign_get_categories'] = [];
		$data['campaign_get_products'] = [];
		
		if (!empty($item_info)) {
			$data['campaign_buy_products'] = $this->model->getCampaignProducts($this->request->get['campaign_id'],1);
			$data['campaign_buy_categories'] = $this->model->getCampaignCategories($this->request->get['campaign_id'],1);
			
			$data['campaign_get_products'] = $this->model->getCampaignProducts($this->request->get['campaign_id'],0);
			$data['campaign_get_categories'] = $this->model->getCampaignCategories($this->request->get['campaign_id'],0);
			
		} 
		

		if (!empty($item_info)) {
			$data['date_start'] = ($item_info['date_start'] != '' ? $item_info['date_start'] :  time());
		} else {
			$data['date_start'] = date('Y-m-d h:m:s', time());
		}

		if (!empty($item_info)) {
			$data['date_end'] = ($item_info['date_end'] != '' ? $item_info['date_end'] :  time());
		} else {
			$data['date_end'] = date('Y-m-d h:m:s', strtotime('+1 month'));
		}

		if (!empty($item_info)) {
			$data['uses_total'] = $item_info['uses_total'];
		} else {
			$data['uses_total'] = 1;
		}

		if (!empty($item_info)) {
			$data['uses_customer'] = $item_info['uses_customer'];
		} else {
			$data['uses_customer'] = 1;
		}

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}
		
		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();

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

		/*foreach ($this->request->post[$this->C.'_description'] as $language_id => $value) {
			if ((strlen(trim($value['title'])) < 1) || (strlen($value['title']) > 64)) {
				$json['error']['title_' . $language_id] = $this->language->get('error_title');
			}
			
			if ((strlen(trim($value['meta_title'])) < 1) || (strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}*/

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
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function history() {
		$this->getML('ML');

		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['column_order_id'] = $this->language->get('column_order_id');
		$data['column_customer'] = $this->language->get('column_customer');
		$data['column_amount'] = $this->language->get('column_amount');
		$data['column_date_added'] = $this->language->get('column_date_added');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = [];

		$results = $this->model_bytao_campaign->getCampaignHistories($this->request->get['campaign_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = [
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $result['amount'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			];
		}

		$history_total = $this->model_bytao_campaign->getTotalCampaignHistories($this->request->get['campaign_id']);
		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $history_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.history', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&campaign_id=' . $this->request->get['campaign_id'] . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view($this->cPth.'_history', $data));
	}	
	
	
	
	
	protected function getList_ex(): string  {
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'n.sort_order';
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
		
		$data['action'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn]. $url);
		
		$data[$this->C.'s'] = [];
		$start = $data['start']=(($page - 1) * (int) $this->config->get('config_limit_admin'));
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => $start,
			'limit' => $this->config->get('config_limit_admin')
		];

		$item_total = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);
		$this->load->model('tool/image');
		$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$thumb = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$thumb = $placeholder;
			}
			
			
			$data[$this->C.'s'][] = [
				$this->ID 		=> $result[$this->ID],
				'thumb'         => $thumb,
				'title'         => $result['title'],
				'sort_order'    => $result['sort_order'],
				'edit'          => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
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

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=nd.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=n.sort_order' . $url, true);

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
			'url'   => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
		
	}
	
}
?>