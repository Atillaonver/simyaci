<?php
namespace Opencart\Admin\Controller\Bytao;
class Discount extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/discount';
	private $C = 'discount';
	private $ID = 'discount_id';
	private $Tkn = 'user_token';
	private $model ;
	private $storeId=0;	
	
	
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

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		$data['list'] = $this->getList();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function list(): void {
		
		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		$this->getML('ML');
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'd.sort_order';
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

		$data['action'] = $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

		$data['downloads'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$item_total = $this->model->{$this->getFunc('getTotal','s')}();

		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			$data['discounts'][] = [
				$this->ID => $result[$this->ID],
				'name'        => $result['name'],
				'discount_type'        => $result['discount_type'],
				'discount_value'        => $result['discount_value'],
				'date_start' 	=> date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   	=> date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'        => $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url)
				
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

		$data['sort_order'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=d.sort_order' . $url);
		$data['sort_name'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=dd.name' . $url);
		$data['sort_date_added'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=d.date_added' . $url);

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
			'url'   => $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view($this->cPth.'_list', $data);
	}

	public function form(): void {
		$this->getML('ML');
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		// Use the ini_get('upload_max_filesize') for the max file size
		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), ini_get('upload_max_filesize'));

		$data['config_file_max_size'] = ((int)preg_filter('/[^0-9]/', '', ini_get('upload_max_filesize')) * 1024 * 1024);

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
		
		$data['apply'] = $this->url->link($this->cPth.'.applly', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['reset'] = $this->url->link($this->cPth.'.resetit', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		if (isset($this->request->get[$this->ID])) {
			$this->load->model($this->cPth);
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		}

		if (isset($this->request->get[$this->ID])) {
			$data[$this->ID] = (int)$this->request->get[$this->ID];
		} else {
			$data[$this->ID] = 0;
		}

		$this->load->model('bytao/common');

		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('catalog/category');
		
		if (!empty($item_info)) {
			$categories = $this->model_bytao_discount->getDiscountCategories($item_info['discount_id']);
		} else {
			$categories = [];
		}
		
		
		$data['discount_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['discount_categories'][] = [
					'category_id' => $category_info['category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}

		if (!empty($item_info)) {
			$data['name'] = $item_info['name'];
		} else {
			$data['name'] = true;
		}
		
		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		if (!empty($item_info)) {
			$data['discount_value'] = $item_info['discount_value'];
		} else {
			$data['discount_value'] = 0;
		}

		if (!empty($item_info)) {
			$data['discount_type'] = $item_info['discount_type'];
		} else {
			$data['discount_type'] = 0;
		}
		
		if (!empty($item_info)) {
			$data['toall'] = $item_info['toall'];
		} else {
			$data['toall'] = 0;
		}
		
		if (!empty($item_info)) {
			$data['date_start'] = ($item_info['date_start'] != '0000-00-00' ? $item_info['date_start'] : '');
		} else {
			$data['date_start'] = date('Y-m-d', time());
		}

		if (!empty($item_info)) {
			$data['date_end'] = ($item_info['date_end'] != '0000-00-00' ? $item_info['date_end'] : '');
		} else {
			$data['date_end'] = date('Y-m-d', strtotime('+1 month'));
		}
		
		
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

		if ((oc_strlen(trim($this->request->post['name'])) < 3) || (oc_strlen($this->request->post['name']) > 120)) {
				$json['error']['name'] = $this->language->get('error_name');
		}

		
		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model($this->cPth);

			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}($this->request->post[$this->ID], $this->request->post);
			}
			
			$data['apply'] = $this->url->link($this->cPth.'.applly', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
			$data['reset'] = $this->url->link($this->cPth.'.resetit', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
			
			$json['incontent'] = $this->load->view($this->cPth.'_apply', $data);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function applly(){
		$json = [];
		
		if (isset($this->request->get['discount_id'])&& $this->request->get['discount_id']) {
			$this->getML('ML');
			if($this->model->{$this->getFunc('apply','s')}($this->request->get[$this->ID])){
				$json['success']='Applyed!!!';
			}else{
				$json['error']='not Applyed!!!';
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function resetit(){
		$json = [];
		
		if (isset($this->request->get['discount_id'])&& $this->request->get['discount_id']) {
			$this->getML('ML');
			if($this->model->{$this->getFunc('reset','s')}($this->request->get[$this->ID])){
				$json['success']='Discounts reset !!!';
			}else{
				$json['error']='Discounts could not be reset!!!';
			}
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

		$this->load->model('catalog/product');

		foreach ($selected as $item_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByDownloadId($item_id);

			if ($product_total) {
				$json['error'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		if (!$json) {
			$this->load->model($this->cPth);

			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete')}($item_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function report(): void {
		$this->response->setOutput($this->getReport());
	}

	private function getReport(): string {
		$this->getML('ML');
		
		if (isset($this->request->get[$this->ID])) {
			$item_id = (int)$this->request->get[$this->ID];
		} else {
			$item_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reports'] = [];

		$this->load->model($this->cPth);
		$this->load->model('customer/customer');
		$this->load->model('setting/store');

		$results = $this->model->{$this->getFunc('get','Reports')}($item_id, ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$store_info = $this->model_setting_store->getStore($result['store_id']);

			if ($store_info) {
				$store = $store_info['name'];
			} elseif (!$result['store_id']) {
				$store = $this->config->get('config_name');
			} else {
				$store = '';
			}

			$data['reports'][] = [
				'ip'         => $result['ip'],
				'account'    => $this->model_customer_customer->getTotalCustomersByIp($result['ip']),
				'store'      => $store,
				'country'    => $result['country'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'filter_ip'  => $this->url->link('customer/customer', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&filter_ip=' . $result['ip'])
			];
		}

		$report_total = $this->model->{$this->getFunc('getTotal','Reports')}($item_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $report_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|report', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $item_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($report_total - 10)) ? $report_total : ((($page - 1) * 10) + 10), $report_total, ceil($report_total / 10));

		return $this->load->view($this->cPth.'_report', $data);
	}

	public function upload(): void {
		$this->getML('ML');

		$json = [];

		// Check user has permission
		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (empty($this->request->files['file']['name']) || !is_file($this->request->files['file']['tmp_name'])) {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			// Sanitize the filename
			$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((Helper\Utf8\strlen($filename) < 3) || (Helper\Utf8\strlen($filename) > 128)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = [];

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			
			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_file_type');
			}

			// Allowed file mime types
			$allowed = [];

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_file_type');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);

			$json['filename'] = $file;
			$json['mask'] = $filename;

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function download(): void {
		$this->getML('ML');

		if (isset($this->request->get['filename'])) {
			$filename = basename($this->request->get['filename']);
		} else {
			$filename = '';
		}

		$file = DIR_DOWNLOAD . $filename;

		if (is_file($file)) {
			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				readfile($file, 'rb');
				exit;
			} else {
				exit($this->language->get('error_headers_sent'));
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
			];

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', $this->Tkn.'=' . $this->session->data[$this->Tkn])
			];

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function sort():void {
    	$this->getML('M');
    	if(isset($this->request->post['serial'])){
			$serials = explode('_',$this->request->post['serial']);
			foreach($serials as $sort => $document_id){
				if($document_id) $this->model->{$this->getFunc('sort')}($document_id,$sort);
			}
		}
        
    }
    
	public function autocomplete(): void {
		
		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$this->getML('ML');
			$filter_data = [
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			];

			$results = $this->model->{$this->getFunc('get','s')}($filter_data);

			foreach ($results as $result) {
				$json[] = [
					$this->ID => $result[$this->ID],
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
}
