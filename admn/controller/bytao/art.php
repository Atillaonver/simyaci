<?php
namespace Opencart\Admin\Controller\Bytao;
class Art extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/art';
	private $C = 'art';
	private $ID = 'art_id';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void{
		switch($ML){
			case 'M':
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':
				$this->load->language($this->cPth);
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			default:
		}
	}
	
	public function install():void{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	
	public function list(): void {
		
		$this->response->setOutput($this->getList());
	}
	
	protected function getList(): string  {
		
		$this->getML('ML');
		$this->load->model('tool/image');
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_code'])) {
			$filter_code = $this->request->get['filter_code'];
		} else {
			$filter_code = null;
		}
		
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
			$data['filter_category'] = $filter_category;
		} else {
			$data['filter_category'] = '';
			$filter_category = null;
		}
		
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.title';
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
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . $this->request->get['filter_code'];
		} 

		$data['action'] = $this->url->link($this->cPth.'.list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['Items'] = [];
		$filter_data = [];
		
		if($filter_all){
			$filter_data = [
				'filter_category'=>$filter_category,
				'filter_name'   =>$filter_name,
				'filter_code'   =>$filter_code,
				'filter_all'   =>$filter_all,
				'filter_status'   =>$filter_status,
				'sort'  => 'a2c.sort_order',
				'order' => $order
				];
		}else{
			$filter_data = [
				'filter_category'   =>$filter_category,
				'filter_name'   =>$filter_name,
				'filter_code'   =>$filter_code,
				'filter_all'   =>$filter_all,
				'filter_status'   =>$filter_status,
				'sort'  => 	$sort,
				'order' => 	$order,
				'start' => ($page - 1) * (int)$this->config->get('config_pagination_admin'),
				'limit' => $this->config->get('config_pagination_admin')
			];
		}
		
		$item_total = $this->model->{$this->getFunc('getTotal','s')}($filter_data);
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);
		foreach ($results as $result) {
			if (isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])) {
				$thumb = $this->model_tool_image->resize( $result['image'], 200, 200);
				$bThumb = $this->model_tool_image->resize( $result['image'], 600, 600);
			} else{
				$thumb = $this->model_tool_image->resize('no_image.png', 200, 200);
				$bThumb = $this->model_tool_image->resize( 'no_image.png', 600, 600);
			}
			
			$data['Items'][] = [
				$this->ID 		=> $result[$this->ID],
				'title'          => isset($result['name'])?$result['name']:'',
				'image'          => $thumb,
				'bimage'          => $bThumb,
				'status'          => $result['status'],
				'made_year'          => $result['made_year'],
				'art_code'          => $result['art_code'],
				'sort_order'          => $result['sort_order'],
				'edit'          	=> $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url, true)
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . $this->request->get['filter_code'];
		}
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		} 

		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=pd.title' . $url, true);
		$data['sort_sort_order'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . $this->request->get['filter_code'];
		} 
		
		$data['pagination'] = $this->load->controller('common/pagination',[
			'total' => $item_total,
			'ajax' => $this->C,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);
		
		if($filter_all){
			$data['results'] = sprintf($this->language->get('text_pagination_all'), $item_total);
		}else{
			$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));
		}
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		return $this->load->view($this->cPth.'_list', $data);
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
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
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
		$data['delete'] = $this->url->link($this->cPth.'.delete', 'user_token=' . $this->session->data['user_token']);

		//$data['list'] = $this->getList();
		
		$data['artcategories'] = [];

		$this->load->model('bytao/artcategory');
		$cresults = $this->model_bytao_artcategory->getArtcategories(['c2.parent_id']);
		$category_data = [];
		
		foreach ($cresults as $result) {
			$empty = $this->model_bytao_artcategory->isCategoryEmpty($result['art_category_id']);
			$category_data[] = [
				'name'       => $result['name'],
				'category_id'       => $result['art_category_id'],
				'empty'	=> $empty
				];
		}
		
		
		if(count($category_data)>0){
			$data['artcategories']= $this->array_msort($category_data, ['name'=>SORT_ASC]); //$category_data;
		}

		$this->document->addStyle('view/bytao/css/art.css');
		$this->document->addStyle('view/bytao/css/sortable.css?v=5');
		
		//$this->document->addScript('https://code.jquery.com/ui/1.14.1/jquery-ui.js');
		//$this->document->addStyle('https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css');
		$this->document->addStyle('view/javascript/jquery/lightbox/css/lightbox.css');
		$this->document->addScript('view/javascript/jquery/lightbox/js/lightbox.js');
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

	public function form():void {
		$this->getML('ML');
		if (isset($this->request->get[$this->ID])) {
			if(! $this->model->{$this->getFunc('is','InStore')}((int)$this->request->get[$this->ID])){
				$this->response->redirect($this->url->link('bytao/art',$this->Tkn.'=' . $this->session->data[$this->Tkn]));
			}
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		
		/*
		$alias = $this->db->query("SELECT * FROM oc_url_alias");
		
		if(isset($alias->rows)){
			foreach($alias->rows as $row ){
				$query = explode('=',$row['query']);
				$key = $query[0];
				$value = isset($query[1])?$query[1]:'';
				
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . $row['store_id'] . "', `language_id` = '" . $row['language_id'] . "', `key` = '".$key."', `value`= '" . $value . "', `keyword` = '" . $row['keyword'] . "'");
				
			}
			
		}
		*/
		
		
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

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		
		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = (int)$this->request->get[$this->ID];
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			//$data[$this->C.'_seo_url']=$this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
			$data[$this->C.'_store'] = $this->session->data['store_id'];
		}else{
			$data[$this->ID] = 0;
			$data[$this->C.'_description'] = [];
			$data[$this->C.'_seo_url']=[];
			$data[$this->C.'_store'] = [$this->session->data['store_id']];
		}
		
		
		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');

		$data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset( $item_info['image']) && is_file(DIR_IMAGE .  $item_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize( $item_info['image'], 100, 100);
		} elseif (!empty( $item_info) && is_file(DIR_IMAGE .  $item_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize( $item_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
				
		if (isset($item_info['image'])) {
			$data['image'] = $item_info['image'];
		} else {
			$data['image'] = '';
		}

		if (isset($item_info['art_code'])) {
			$data['art_code'] = $item_info['art_code'];
		} else {
			$data['art_code'] = '';
		}
		
		if (isset($item_info['made_year'])) {
			$data['made_year'] = $item_info['made_year'];
		} else {
			$data['made_year'] = '';
		}

		if (isset($item_info['status'])) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($item_info['sort_order'])) {
			$data['sort_order'] = $item_info['sort_order'];
		}else {
			$data['sort_order'] = '';
		}

		// Categories
		$this->load->model('bytao/artcategory');
		
		if (isset($item_info[$this->ID])) {
			$categories = $this->model->{$this->getFunc('get','Categories')}($this->request->get['art_id']);
		} else {
			$categories = [];
		}
		

		$data['art_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model_bytao_artcategory->getArtcategory($category_id);

			if ($category_info) {
				$data['art_categories'][] = [
					'art_category_id' => $category_info['art_category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}
		/**/
		
		
		
		
		$data['store_id'] = $this->session->data['store_id'];
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
	
	public function orderupdate(){
		$this->getML('ML');
		$json = [];
		$ar = '';
		
		
		
		if (isset($this->request->post['listItem'])) {
			$listing = $this->request->post['listItem'];
			$category_id = isset($this->request->get['category'])?$this->request->get['category']:0;
			
			
			$startSortOrder =0;
			
			$order = [];
			foreach ($listing as $list) {
				foreach ($list as $key => $val) {
					
					if ($val['id'] != '') {
						$this->model_bytao_product->updateSortorder($startSortOrder,$val['id'],$category_id);
						$json['order'][] = array(
						'id' =>	$val['id'],
						'sort' =>	$startSortOrder);
						$startSortOrder++;
					}
				}	
			}
			
			
		} else {
			$json['deneme'] = 'Olmadi';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}

	public function catorder(){
		$this->getML('ML');
		
		$postData = isset($this->request->get['art_category'])?$this->request->get['art_category']:[];
		$data['typ'] = isset($this->request->get['typ'])?$this->request->get['typ']:false;
		$productId = isset($this->request->get['pId'])?$this->request->get['pId']:0;
		
		
		$data['token'] = $this->session->data['token'];
		
		$this->load->model('tool/image');
		$this->load->model('bytao/artcategory');
		$this->load->model('bytao/art');
		$json['cat'] = $this->language->get($data['typ']);
		
		
		
		if($data['typ']){
			$catagoryId=0;
			$catagory_id = $postData[0];
			for($i=0;$i<4;$i++){
				$catagoryId = $this->model_bytao_artcategory->getMainParentCategory($catagory_id);
				if ($catagoryId==0){
					$catagoryId=$catagory_id;
					break;
				}
			}
			
			
			$arts = [];
				$filter_data = array(
					'filter_category'   =>$catagoryId,
					'filter_type'   =>$data['typ']
				);
				$results = $this->model_bytao_product->getAddProducts($filter_data);
				$category_info = $this->model_catalog_category->getCategory($catagoryId);
				foreach ($results as $result) {
					if (is_file(DIR_IMAGE . $result['image'])) {
						$image = $this->model_tool_image->resize($result['image'], 100, 150);
					} else {
						$image = $this->model_tool_image->resize('no_image.png', 100, 150);
					}
					$arts[] = array(
						'art_id' => $result['product_id'],
						'image'      => $image,
						'status' => $result['status'],
						'art_code'      => $result['art_code']
					);
				}
				$data['cats'][]=array(
					'products'=> $products,
					'title' => $category_info['name'],
					'cat_id' =>$catagoryId
					
				);
			
			
			
			
		} else {
			
			$this->load->model('bytao/art');
			$filter_category = $this->request->get['art_category_id'];
			
			$data['cat_id'] = $filter_category;
			$data['typ']='';
			$filter_data = array(
			'filter_category'   => $filter_category,
			'filter_status'   => 1,
			'sort'            => 'a2c.sort_order',
			'order'           => 'ASC'
			);
			
			$results = $this->model_bytao_art->getArts($filter_data);

			$category_info = $this->model_bytao_artcategory->getArtCategory($filter_category);

			foreach ($results as $result) {
					if (is_file(DIR_IMAGE . $result['image'])) {
						$image = $this->model_tool_image->resize($result['image'],150,150);
					} else {
						$image = $this->model_tool_image->resize('no_image.png',150,150);
					}
					
					if($result['col_num']==0){
						$colnum=1;
					}else{
						$colnum=$result['col_num'];
					}
					
					
					$arts[] = array(
						'art_id' => $result['art_id'],
						'status' => $result['status'],
						'image'      => $image,
						'col_num'      => $colnum,
						'art_code'      => $result['art_code']
					);
				}
				$data['cats'][]=array(
					'arts'=> $arts,
					'title' => $category_info['name'],
					'cat_id' =>$filter_category
				);
			
			
		}
		
		

		
		$json['title'] = $this->language->get($data['typ']).'  '.$this->language->get('text_order');
		$json['body']=$this->load->view('bytao/art_order.tpl', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
		
	}

	public function sortorder(){
		if (isset($this->request->get['serial'])) {
			$this->getML('ML');
			
			$serials =explode(',',$this->request->get['serial']);
			$art_category_id = isset($this->request->get['cat_id'])?$this->request->get['cat_id']:0;
			/*
			$cols= explode(':',$this->request->get['serial']);
			$art_category_id = isset($this->request->get['cat_id'])?$this->request->get['cat_id']:0;
			 
			
			 
				$order1= explode(',',$cols[0]);
				$order2= explode(',',$cols[1]);
				$order3= explode(',',$cols[2]);
				$order4= explode(',',$cols[3]);
			$a = count($order1);
			$b = count($order2);
			$c = count($order3);
			$d = count($order4);
			$common=[];
			
			if(($a>$b) && ($a>$c) && ($a>$d)){
				for($i = 0; $i<$a; $i++){
					if(isset($order1[$i])){$common[]=array('id'=>$order1[$i],'col'=>1);}
					if(isset($order2[$i])){$common[]=array('id'=>$order2[$i],'col'=>2);}
					if(isset($order3[$i])){$common[]=array('id'=>$order3[$i],'col'=>3);}
					if(isset($order4[$i])){$common[]=array('id'=>$order4[$i],'col'=>4);}
				}
			}
			if(($b>$c) && ($b>$a) && ($b>$d)){
				for($i = 0; $i<$b; $i++){
					if(isset($order1[$i])){$common[]=array('id'=>$order1[$i],'col'=>1);}
					if(isset($order2[$i])){$common[]=array('id'=>$order2[$i],'col'=>2);}
					if(isset($order3[$i])){$common[]=array('id'=>$order3[$i],'col'=>3);}
					if(isset($order4[$i])){$common[]=array('id'=>$order4[$i],'col'=>4);}
				}
			}
			if(($c>$b) && ($c>$a) && ($c>$d)){
				for($i = 0; $i<$c; $i++){
					if(isset($order1[$i])){$common[]=array('id'=>$order1[$i],'col'=>1);}
					if(isset($order2[$i])){$common[]=array('id'=>$order2[$i],'col'=>2);}
					if(isset($order3[$i])){$common[]=array('id'=>$order3[$i],'col'=>3);}
					if(isset($order4[$i])){$common[]=array('id'=>$order4[$i],'col'=>4);}
				}
			}
			if(($d>$b) && ($d>$a) && ($d>$c)){
				for($i = 0; $i<$d; $i++){
					if(isset($order1[$i])){$common[]=array('id'=>$order1[$i],'col'=>1);}
					if(isset($order2[$i])){$common[]=array('id'=>$order2[$i],'col'=>2);}
					if(isset($order3[$i])){$common[]=array('id'=>$order3[$i],'col'=>3);}
					if(isset($order4[$i])){$common[]=array('id'=>$order4[$i],'col'=>4);}
				}
			}
			
			*/
			$startSortOrder =0;
			$colNumber = 1;
			
			
			foreach ($serials as $art) {
					
					$this->model_bytao_art->updateArtSortorder($startSortOrder,$art,$art_category_id,1);
					$startSortOrder++;
			}
			
			
			$json['res'] = 'OK';
			$json['g'] = implode(',',$serials);
			//$this->model_bytao_product->productAddUpdate();
			
		} else {
			$json['res'] = 'NOT';
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}

	public function autocomplete() {
		$json = [];
		$this->getML('M');
		
		if (isset($this->request->get['filter_all'])) {
			$data['filter_all'] = $filter_all = $this->request->get['filter_all'];
		}else{
			$data['filter_all'] = 0;
			$filter_all = null;
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_code'])) {
			$filter_code = $this->request->get['filter_code'];
		} else {
			$filter_code = null;
		}
		
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
			$data['filter_category'] = $filter_category;
		} else {
			$data['filter_category'] = '';
			$filter_category = null;
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		$filter_data = [
			'filter_category'   =>$filter_category,
			'filter_name'   =>$filter_name,
			'filter_code'   =>$filter_code,
			'filter_all'   =>$filter_all,
			'filter_status'   =>$filter_status,
			'sort'  => 	$sort,
			'order' => 	$order,
			'start' => 0,
			'limit' => 5
		];
		
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);
		$rslt = $filter_code?'art_code':'name';
		foreach ($results as $result) {
			if(isset($result[$rslt])){
				$json[] = [
					'value' => $result['artId'],
					'name'        => strip_tags(html_entity_decode($result[$rslt], ENT_QUOTES, 'UTF-8'))
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

	
	
	
	
	
	
	
	
	public function index_e() {
		$this->load->language('bytao/art');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('bytao/art');

		$this->getList();
	}

	public function add() {
		$this->load->language('bytao/art');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('bytao/art');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			if(isset($this->session->data['next'])){
				$this->session->data['next']++;
				if($this->session->data['next']>4)
				$this->session->data['next']=1;
			}else{
				$this->session->data['next']=1;
			}
			
			$this->model_bytao_art->addArt($this->request->post);

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
			
			if (isset($this->request->get['filter_all'])) {
				$url .= '&filter_all=' . $this->request->get['filter_all'];
			}
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_all'];
			} 

			$this->response->redirect($this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('bytao/art');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('bytao/art');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_bytao_art->editArt($this->request->get['art_id'], $this->request->post);

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
			
			if (isset($this->request->get['filter_all'])) {
				$url .= '&filter_all=' . $this->request->get['filter_all'];
			}
			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_all'];
			} 
			$this->response->redirect($this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete_e() {
		$this->load->language('bytao/art');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('bytao/art');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $art_id) {
				$this->model_bytao_art->deleteArt($art_id);
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
			
			if (isset($this->request->get['filter_all'])) {
				$url .= '&filter_all=' . $this->request->get['filter_all'];
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_all'];
			} 

			$this->response->redirect($this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList_e() {
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_code'])) {
			$filter_code = $this->request->get['filter_code'];
		} else {
			$filter_code = null;
		}
		
		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
			$data['filter_category'] = $filter_category;
		} else {
			$data['filter_category'] = '';
			$filter_category = null;
		}
		
		if (isset($this->request->get['filter_all'])) {
			$filter_all = $this->request->get['filter_all'];
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
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
		
		if (isset($this->request->get['filter_all'])) {
				$url .= '&filter_all=' . $this->request->get['filter_all'];
			}

		if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_all'];
			}
			
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		$data['add'] = $this->url->link('bytao/art/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['copy'] = $this->url->link('bytao/art/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$data['delete'] = $this->url->link('bytao/art/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->document->addStyle('view/stylesheet/art.css?ver=8');
		$this->document->addStyle('view/stylesheet/sortable.css?ver=11');
		
		if(isset($filter_all) && isset($filter_all)){
			$this->document->addScript('view/javascript/jquery/ui/jquery-ui.min.js');
		}
		
		
		$this->document->addStyle('view/javascript/jquery/ui/jquery-ui.min.css');
		$this->document->addStyle('view/javascript/jquery/lightbox/css/lightbox.css');
		$this->document->addScript('view/javascript/jquery/lightbox/js/lightbox.js');
		
		$data['token'] = $this->session->data['token'];
		$data['artcategories'] = [];
		$this->load->model('bytao/artcategory');
		
		$cresults = $this->model_bytao_artcategory->getArtCategories(array('c2.parent_id'));
		$category_data = [];
		
		foreach ($cresults as $result) {
			$empty = $this->model_bytao_artcategory->isCategoryEmpty($result['art_category_id']);
			$category_data[] = array(
				'name'       => $result['name'],
				'art_category_id'       => $result['art_category_id'],
				'empty'	=> $empty
				);
		}
		
		
		if(count($category_data)>0){
			$data['artcategories']= $this->array_msort($category_data, array('name'=>SORT_ASC)); //$category_data;
		}
		
		
		$this->load->model('tool/image');
		
		$data['arts'] = [];
		
		if(isset($filter_all)){
			$filter_data = array(
				'filter_category'=>$filter_category,
				'filter_code'   =>$filter_code,
				'filter_all'   =>$filter_all,
				'sort'  => 'a2c.sort_order',
				'order' => $order
				
			);
		}else{
			$filter_data = array(
				'filter_category'   =>$filter_category,
				'filter_code'   =>$filter_code,
				'sort'  => $sort,
				'order' => $order,
				'start' => ($page - 1) * $this->config->get('config_limit_admin'),
				'limit' => $this->config->get('config_limit_admin')
			);
		}
		

		$art_total = $this->model_bytao_art->getTotalArts($filter_data);

		$results = $this->model_bytao_art->getArts($filter_data);

		foreach ($results as $result) {
			if (isset( $result['image']) && is_file(DIR_IMAGE .  $result['image'])) {
					$thumb = $this->model_tool_image->resize( $result['image'], 200, 200);
					$bThumb = $this->model_tool_image->resize( $result['image'], 600, 600);
				} else{
					$thumb = $this->model_tool_image->resize('no_image.png', 200, 200);
					$bThumb = $this->model_tool_image->resize( 'no_image.png', 600, 600);
				}
				
			$data['arts'][] = array(
				'art_id' => $result['artId'],
				'title'          => isset($result['name'])?$result['name']:'',
				'image'          => $thumb,
				'bimage'          => $bThumb,
				'status'          => $result['status'],
				'made_year'          => $result['made_year'],
				'art_code'          => $result['art_code'],
				'sort_order'          => $result['sort_order'],
				
				'edit'           => $this->url->link('bytao/art/edit', 'token=' . $this->session->data['token'] . '&art_id=' . $result['artId'] . $url, 'SSL')
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_category '] = $this->language->get('entry_category');
		$data['entry_code'] = $this->language->get('entry_code');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_title'] = $this->language->get('column_title');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');
		$data['column_code'] = $this->language->get('column_code');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');

		$data['button_copy'] = $this->language->get('button_copy');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');

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
		
		$data['sort_code'] = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . '&sort=p.code' . $url, 'SSL');
		$data['sort_status'] = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . '&sort=p.status' . $url, 'SSL');
		$data['sort_title'] = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . '&sort=id.title' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . '&sort=i.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $art_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();
		
		if(isset($filter_all)){
			$data['results'] = sprintf($this->language->get('text_pagination_all'), ($art_total) ? $art_total : 0);
		}else{
			$data['results'] = sprintf($this->language->get('text_pagination'), ($art_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($art_total - $this->config->get('config_limit_admin'))) ? $art_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $art_total, ceil($art_total / $this->config->get('config_limit_admin')));
		}
		
		$data['filter_name'] = $filter_name;
		$data['filter_code'] = $filter_code;
		if(isset($filter_all)) $data['filter_all'] = $filter_all;
		$data['filter_status'] = $filter_status;
		$data['filter_category'] = $filter_category;

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('bytao/art_list.tpl', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_form'] = !isset($this->request->get['blog_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		

		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_position'] = $this->language->get('entry_position');
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_stop'] = $this->language->get('entry_date_stop');
		
		$data['entry_script'] = $this->language->get('entry_script');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_meta_title'] = $this->language->get('entry_meta_title');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$data['entry_keyword'] = $this->language->get('entry_keyword');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_bottom'] = $this->language->get('entry_bottom');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_target'] = $this->language->get('entry_target');
		$data['entry_layout'] = $this->language->get('entry_layout');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_code'] = $this->language->get('entry_code');
		$data['entry_made_year'] = $this->language->get('entry_made_year');
		
		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['help_bottom'] = $this->language->get('help_bottom');
		$data['help_spot'] = $this->language->get('help_spot');
		$data['help_image'] = $this->language->get('help_image');
		$data['help_category'] = $this->language->get('help_category');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['art_code'])) {
			$data['error_art_code'] = $this->error['art_code'];
		} else {
			$data['error_art_code'] = [];
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
		
		if (isset($this->request->get['filter_all'])) {
				$url .= '&filter_all=' . $this->request->get['filter_all'];
			}

		if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_all'];
			} 

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		if (!isset($this->request->get['art_id'])) {
			$data['action'] = $this->url->link('bytao/art/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$data['action'] = $this->url->link('bytao/art/edit', 'token=' . $this->session->data['token'] . '&art_id=' . $this->request->get['art_id'] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link('bytao/art', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['art_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$art_info = $this->model_bytao_art->getArt($this->request->get['art_id']);
		}

		$data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('tool/image');

		$data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset( $art_info['image']) && is_file(DIR_IMAGE .  $art_info['image'])) {
					$data['thumb'] = $this->model_tool_image->resize( $art_info['image'], 100, 100);
				} elseif (!empty( $art_info) && is_file(DIR_IMAGE .  $art_info['image'])) {
					$data['thumb'] = $this->model_tool_image->resize( $art_info['image'], 100, 100);
				} else {
					$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
				}
				
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($art_info)) {
			$data['image'] = $art_info['image'];
		} else {
			$data['image'] = '';
		}

		if (isset($this->request->post['art_description'])) {
			$data['art_description'] = $this->request->post['art_description'];
		} elseif (isset($this->request->get['art_id'])) {
			$data['art_description'] = $this->model_bytao_art->getArtDescriptions($this->request->get['art_id']);
		} else {
			$data['art_description'] = [];
		}
		
		if (isset($this->request->post['art_code'])) {
			$data['art_code'] = $this->request->post['art_code'];
		} elseif (!empty($art_info)) {
			$data['art_code'] = $art_info['art_code'];
		} else {
			$data['art_code'] = '';
		}
		
		if (isset($this->request->post['made_year'])) {
			$data['made_year'] = $this->request->post['made_year'];
		} elseif (!empty($art_info)) {
			$data['made_year'] = $art_info['made_year'];
		} else {
			$data['made_year'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($art_info)) {
			$data['status'] = $art_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($art_info)) {
			$data['sort_order'] = $art_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		// Categories
		$this->load->model('bytao/artcategory');

		if (isset($this->request->post['art_category'])) {
			$categories = $this->request->post['art_category'];
		} elseif (isset($this->request->get['art_id'])) {
			$categories = $this->model_bytao_art->getArtCategories($this->request->get['art_id']);
		} else {
			$categories = [];
		}

		$data['art_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model_bytao_artcategory->getArtCategory($category_id);

			if ($category_info) {
				$data['art_categories'][] = array(
					'art_category_id' => $category_info['art_category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

	
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('bytao/art_form.tpl', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'bytao/art')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		/*
		if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 250)) {
				$this->error['title'] = $this->language->get('error_title');
			}
		*/
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'bytao/art')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	private function array_msort($array, $cols)	{
		 $ret = [];
	    $colarr = [];
	    if(count($array)>0){
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
		   
		    foreach ($colarr as $col => $arr) {
		        foreach ($arr as $k => $v) {
		            $k = substr($k,1);
		            if (!isset($ret[$k])) $ret[$k] = $array[$k];
		            $ret[$k][$col] = $array[$k][$col];
		        }
		    }
	    
		}
	    
	    return $ret;

	}

}