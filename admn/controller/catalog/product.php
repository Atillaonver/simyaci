<?php
namespace Opencart\Admin\Controller\Catalog;
class Product extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('catalog/product');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		unset($this->session->data['prod']);

		if (isset($this->request->get['filter_all'])) {
			$filter_all = $this->request->get['filter_all'];
		} else {
			$filter_all = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		$url = '';

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['add'] = $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['copy'] = $this->url->link('catalog/product.copy', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('catalog/product.delete', 'user_token=' . $this->session->data['user_token']);

		
		$data['list'] = $this->getList();
		
		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories([]);
		
		$data['filter_all'] = $filter_all;
		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_category'] = $filter_category;
		$data['filter_status'] = $filter_status;

		$data['user_token'] = $this->session->data['user_token'];
		
		$this->document->addStyle('view/stylesheet/export_import.css');
		
		$data['export_tab'] = $this->load->controller('bytao/export',['type'=>'product']);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product', $data));
	}

	public function list(): void {
		$this->load->language('catalog/product');

		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		if (isset($this->request->get['filter_all'])) {
			$filter_all = $this->request->get['filter_all'];
		} else {
			$filter_all = '';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.product_id';
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

		if (isset($this->request->get['pid'])) {
			$pid = (int)$this->request->get['pid'];
		} else {
			$pid = '';
		}

		$url = '';

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . urlencode(html_entity_decode($this->request->get['filter_all'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['pid'])) {
			$url .= '&pid=' . $this->request->get['pid'];
		}

		$data['action'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['products'] = [];

		$filter_data = [
			'filter_all'     => $filter_all,
			'filter_name'     => $filter_name,
			'filter_model'    => $filter_model,
			'filter_price'    => $filter_price,
			'filter_category' => $filter_category,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'           => $this->config->get('config_pagination_admin')
		];

		$this->load->model('catalog/product');
		$this->load->model('bytao/common');

		$this->load->model('tool/image');

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 200, 200);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 200, 200);
			}

			$special = false;

			$product_specials = $this->model_catalog_product->getSpecials($result['product_id']);

			foreach ($product_specials as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			$more = $this->model_bytao_common->getProductColorCount($result['product_id']);

			$data['products'][] = [
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'color'      => $more,
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => $result['status'],
				'upstts'     => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=status'),
				'salelink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=sale' ),
				'sale'      => $result['sale'],
				'mpagelink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=mpage'),
				'mpage'      => $result['mpage'],
				'clearancelink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=clearance'),
				'clearance'      => $result['clearance'],
				'new_arriwalslink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=new_arriwals'),
				'new_arriwals'      => $result['new_arriwals'],
				'bestlink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=best'),
				'best'      => $result['best'],
				'giftlink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=gift'),
				'gift'      => $result['gift'],
				'cataloglink'      => $this->url->link('catalog/product.update_status', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . '&status=catalog'),
				'catalog'      => $result['catalog'],
				'edit'       => $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . ($result['master_id'] ? '&master_id=' . $result['master_id'] : '') . $url),
				'variant'    => (!$result['master_id'] ? $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . '&master_id=' . $result['product_id'] . $url) : '')
			];
		}

		$url = '';

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . urlencode(html_entity_decode($this->request->get['filter_all'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_id'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.product_id' . $url);
		$data['sort_name'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url);
		$data['sort_model'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url);
		$data['sort_price'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url);
		$data['sort_quantity'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url);
		$data['sort_order'] = $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . urlencode(html_entity_decode($this->request->get['filter_all'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if($filter_all){
			$data['pagination'] ='';
			$data['all_results'] = sprintf($this->language->get('text_pagination'), 1 ,$product_total, $product_total, 1);
		}else{
			$data['pagination'] = $this->load->controller('common/pagination', [
				'total' => $product_total,
				'page'  => $page,
				'limit' => $this->config->get('config_pagination_admin'),
				'url'   => $this->url->link('catalog/product.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
			]);

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($product_total - $this->config->get('config_pagination_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $product_total, ceil($product_total / $this->config->get('config_pagination_admin')));
		}
		
		if (isset($this->request->get['pid'])) {
			$data['pid'] = $this->request->get['pid'];
		}
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('catalog/product_list', $data);
	}

	public function form(): void {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.css');
		$this->document->addStyle('//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.css');
		//$this->document->addStyle('//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
		//$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
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
		
		$data['ADM'] = $uGId =$this->user->getGroupId();


		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['error_upload_size'] = sprintf($this->language->get('error_upload_size'), $this->config->get('config_file_max_size'));

		$data['upload'] = $this->url->link('tool/upload', 'user_token=' . $this->session->data['user_token']);
		$data['config_file_max_size'] = ((int)$this->config->get('config_file_max_size') * 1024 * 1024);

		if (isset($this->request->get['master_id'])) {
			$this->load->model('catalog/product');

			$url = $this->url->link('catalog/product.form', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['master_id']);

			$data['text_variant'] = sprintf($this->language->get('text_variant'), $url, $url);
		} else {
			$data['text_variant'] = '';
		}

		$url = '';

		if (isset($this->request->get['master_id'])) {
			$url .= '&master_id=' . $this->request->get['master_id'];
		}

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . urlencode(html_entity_decode($this->request->get['filter_all'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$url = '';

		if (isset($this->request->get['filter_all'])) {
			$url .= '&filter_all=' . $this->request->get['filter_all'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} elseif (isset($this->request->get['master_id'])) {
			$product_id = (int)$this->request->get['master_id'];
		} else {
			$product_id = 0;
		}
		
		if ($product_id) {
			$url .= '&pid=' . $product_id;
		}
		
		$data['product_id'] = $product_id;
		
		$data['save'] = $this->url->link('catalog/product.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['upload'] = $this->url->link('tool/upload.upload', 'user_token=' . $this->session->data['user_token']);



		if ($product_id) {
			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($product_id);
		}

		if (isset($this->request->get['master_id'])) {
			$data['master_id'] = (int)$this->request->get['master_id'];
		} elseif (!empty($product_info)) {
			$data['master_id'] = $product_info['master_id'];
		} else {
			$data['master_id'] = 0;
		}

		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		if (!empty($product_info)) {
			$data['product_description'] = $this->model_catalog_product->getDescriptions($product_id);
		} else {
			$data['product_description'] = [];
		}

		if (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}

		if (!empty($product_info)) {
			$data['material'] = $product_info['material'];
		} else {
			$data['material'] = '';
		}

		if (!empty($product_info)) {
			$data['sku'] = $product_info['sku'];
		} else {
			$data['sku'] = '';
		}

		if (!empty($product_info)) {
			$data['upc'] = $product_info['upc'];
		} else {
			$data['upc'] = '';
		}

		if (!empty($product_info)) {
			$data['ean'] = $product_info['ean'];
		} else {
			$data['ean'] = '';
		}

		if (!empty($product_info)) {
			$data['jan'] = $product_info['jan'];
		} else {
			$data['jan'] = '';
		}

		if (!empty($product_info)) {
			$data['isbn'] = $product_info['isbn'];
		} else {
			$data['isbn'] = '';
		}

		if (!empty($product_info)) {
			$data['mpn'] = $product_info['mpn'];
		} else {
			$data['mpn'] = '';
		}

		if (!empty($product_info)) {
			$data['location'] = $product_info['location'];
		} else {
			$data['location'] = '';
		}

		if (!empty($product_info)) {
			$data['price'] = $product_info['price'];
		} else {
			$data['price'] = '';
		}

		if (!empty($product_info)) {
			$data['sale_price'] = $product_info['sale_price'];
		} else {
			$data['sale_price'] = '';
		}

		if (!empty($product_info)) {
			$data['whole_sale_price'] = $product_info['whole_sale_price'];
		} else {
			$data['whole_sale_price'] = '';
		}

		if (!empty($product_info)) {
			$data['cost_price'] = $product_info['cost_price'];
		} else {
			$data['cost_price'] = '';
		}


		if (!empty($product_info)) {
			$data['our_price'] = $product_info['our_price'];
		} else {
			$data['our_price'] = '';
		}

		if (!empty($product_info)) {
			$data['retail_price'] = $product_info['retail_price'];
		} else {
			$data['retail_price'] = '';
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (!empty($product_info)) {
			$data['tax_class_id'] = $product_info['tax_class_id'];
		} else {
			$data['tax_class_id'] = 0;
		}

		if (!empty($product_info)) {
			$data['quantity'] = $product_info['quantity'];
		} else {
			$data['quantity'] = 1;
		}

		if (!empty($product_info)) {
			$data['minimum'] = $product_info['minimum'];
		} else {
			$data['minimum'] = 1;
		}

		if (!empty($product_info)) {
			$data['subtract'] = $product_info['subtract'];
		} else {
			$data['subtract'] = 1;
		}

		$this->load->model('localisation/stock_status');

		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		if (!empty($product_info)) {
			$data['stock_status_id'] = $product_info['stock_status_id'];
		} else {
			$data['stock_status_id'] = 0;
		}

		if (!empty($product_info)) {
			$data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
		} else {
			$data['date_available'] = date('Y-m-d');
		}

		if (!empty($product_info)) {
			$data['shipping'] = $product_info['shipping'];
		} else {
			$data['shipping'] = 1;
		}

		if (!empty($product_info)) {
			$data['length'] = $product_info['length'];
		} else {
			$data['length'] = '';
		}

		if (!empty($product_info)) {
			$data['width'] = $product_info['width'];
		} else {
			$data['width'] = '';
		}

		if (!empty($product_info)) {
			$data['height'] = $product_info['height'];
		} else {
			$data['height'] = '';
		}

		$this->load->model('localisation/length_class');

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		if (!empty($product_info)) {
			$data['length_class_id'] = $product_info['length_class_id'];
		} else {
			$data['length_class_id'] = $this->config->get('config_length_class_id');
		}

		if (!empty($product_info)) {
			$data['weight'] = $product_info['weight'];
		} else {
			$data['weight'] = '';
		}

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (!empty($product_info)) {
			$data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
			$data['weight_class_id'] = $this->config->get('config_weight_class_id');
		}

		if (!empty($product_info)) {
			$data['status'] = $product_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if (!empty($product_info)) {
			$data['mpage'] = $product_info['mpage'];
		} else {
			$data['mpage'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['sale'] = $product_info['sale'];
		} else {
			$data['sale'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['clearance'] = $product_info['clearance'];
		} else {
			$data['clearance'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['new_arriwals'] = $product_info['new_arriwals'];
		} else {
			$data['new_arriwals'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['best'] = $product_info['best'];
		} else {
			$data['best'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['gift'] = $product_info['gift'];
		} else {
			$data['gift'] = 0;
		}
		
		if (!empty($product_info)) {
			$data['catalog'] = $product_info['catalog'];
		} else {
			$data['catalog'] = 0;
		}

		if (!empty($product_info)) {
			$data['sort_order'] = $product_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}
		
		if (!empty($product_info)) {
			$data['measurement_id'] = $product_info['measurement_id'];
		} else {
			$data['measurement_id'] = 0;
		}
		if (!empty($product_info)) {
			$data['productcare_id'] = $product_info['productcare_id'];
		} else {
			$data['productcare_id'] = 0;
		}
		if (!empty($product_info)) {
			$data['material_id'] = $product_info['material_id'];
		} else {
			$data['material_id'] = 0;
		}
		if (!empty($product_info)) {
			$data['size_chart_id'] = $product_info['size_chart_id'];
		} else {
			$data['size_chart_id'] = 0;
		}
		
		
		

		$this->load->model('catalog/manufacturer');

		if (!empty($product_info)) {
			$data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

		if (!empty($product_info)) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}
		} else {
			$data['manufacturer'] = '';
		}

		// Categories
		$this->load->model('catalog/category');

		if ($product_id) {
			$categories = $this->model_catalog_product->getCategories($product_id);
		} else {
			$categories = [];
		}

		$data['product_categories'] = [];

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = [
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				];
			}
		}

		// Filters
		$this->load->model('catalog/filter');

		if (!empty($product_info)) {
			$filters = $this->model_catalog_product->getFilters($product_id);
		} else {
			$filters = [];
		}

		$data['product_filters'] = [];

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['product_filters'][] = [
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				];
			}
		}

		// Stores
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

		if ($product_id) {
			$data['product_store'] = $this->model_catalog_product->getStores($product_id);
		} else {
			$data['product_store'] = [0];
		}

		// Downloads
		$this->load->model('catalog/download');

		if ($product_id) {
			$product_downloads = $this->model_catalog_product->getDownloads($product_id);
		} else {
			$product_downloads = [];
		}

		$data['product_downloads'] = [];

		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['product_downloads'][] = [
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				];
			}
		}

		// Related
		if ($product_id) {
			$product_relateds = $this->model_catalog_product->getRelated($product_id);
		} else {
			$product_relateds = [];
		}

		$data['product_relateds'] = [];

		foreach ($product_relateds as $related_id) {
			$related_info = $this->model_catalog_product->getProduct($related_id);

			if ($related_info) {
				$data['product_relateds'][] = [
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				];
			}
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if ($product_id) {
			$product_attributes = $this->model_catalog_product->getAttributes($product_id);
		} else {
			$product_attributes = [];
		}

		$data['product_attributes'] = [];

		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

			if ($attribute_info) {
				$data['product_attributes'][] = [
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				];
			}
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		// Options
		$this->load->model('catalog/option');

		if ($product_id) {
			$product_options = $this->model_catalog_product->getOptions($product_id);
		} else {
			$product_options = [];
		}

		$data['product_options'] = [];

		foreach ($product_options as $product_option) {
			$product_option_value_data = [];

			if (isset($product_option['product_option_value'])) {
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$option_value_info = $this->model_catalog_option->getValue($product_option_value['option_value_id']);

					if ($option_value_info) {
						$product_option_value_data[] = [
							'product_option_value_id' => $product_option_value['product_option_value_id'],
							'option_value_id'         => $product_option_value['option_value_id'],
							'name'                    => $option_value_info['name'],
							'quantity'                => $product_option_value['quantity'],
							'subtract'                => $product_option_value['subtract'],
							'type'                    => $product_option_value['type'],
							'price'                   => $product_option_value['price'],
							'price_prefix'            => $product_option_value['price_prefix'],
							'points'                  => round($product_option_value['points']),
							'points_prefix'           => $product_option_value['points_prefix'],
							'weight'                  => round($product_option_value['weight']),
							'weight_prefix'           => $product_option_value['weight_prefix'],
							'sort_order'           => $product_option_value['sort_order']
						];
					}
				}
			}

			$data['product_options'][] = [
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => isset($product_option['value']) ? $product_option['value'] : '',
				'required'             => $product_option['required']
			];
		}

		$data['option_values'] = [];

		foreach ($data['product_options'] as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				if (!isset($data['option_values'][$product_option['option_id']])) {
					$data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getValues($product_option['option_id']);
				}
			}
		}

		// Variants
		if (!empty($product_info)) {
			$data['variant'] = json_decode($product_info['variant'], true);
		} else {
			$data['variant'] = [];
		}

		// Overrides
		if (!empty($product_info)) {
			$data['override'] = json_decode($product_info['override'], true);
		} else {
			$data['override'] = [];
		}

		$data['options'] = [];

		if (isset($this->request->get['master_id'])) {
			$product_options = $this->model_catalog_product->getOptions($this->request->get['master_id']);

			foreach ($product_options as $product_option) {
				$product_option_value_data = [];

				foreach ($product_option['product_option_value'] as $product_option_value) {
					$option_value_info = $this->model_catalog_option->getValue($product_option_value['option_value_id']);

					if ($option_value_info) {
						$product_option_value_data[] = [
							'product_option_value_id' => $product_option_value['product_option_value_id'],
							'option_value_id'         => $product_option_value['option_value_id'],
							'name'                    => $option_value_info['name'],
							'price'                   => (float)$product_option_value['price'] ? $product_option_value['price'] : false,
							'price_prefix'            => $product_option_value['price_prefix']
						];
					}
				}

				$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

				$data['options'][] = [
					'product_option_id'    => $product_option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $product_option['option_id'],
					'name'                 => $option_info['name'],
					'type'                 => $option_info['type'],
					'value'                => isset($data['variant'][$product_option['product_option_id']]) ? $data['variant'][$product_option['product_option_id']] : $product_option['value'],
					'required'             => $product_option['required']
				];
			}
		}

		// Subscriptions
		$this->load->model('catalog/subscription_plan');

		$data['subscription_plans'] = $this->model_catalog_subscription_plan->getSubscriptionPlans();

		if ($product_id) {
			$data['product_subscriptions'] = $this->model_catalog_product->getSubscriptions($product_id);
		} else {
			$data['product_subscriptions'] = [];
		}

		// Discount
		if ($product_id) {
			$product_discounts = $this->model_catalog_product->getDiscounts($product_id);
		} else {
			$product_discounts = [];
		}

		$data['product_discounts'] = [];

		foreach ($product_discounts as $product_discount) {
			$data['product_discounts'][] = [
				'customer_group_id' => $product_discount['customer_group_id'],
				'quantity'          => $product_discount['quantity'],
				'priority'          => $product_discount['priority'],
				'price'             => $product_discount['price'],
				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
			];
		}

		// Special
		if ($product_id) {
			$product_specials = $this->model_catalog_product->getSpecials($product_id);
		} else {
			$product_specials = [];
		}

		$data['product_specials'] = [];

		foreach ($product_specials as $product_special) {
			$data['product_specials'][] = [
				'customer_group_id' => $product_special['customer_group_id'],
				'priority'          => $product_special['priority'],
				'price'             => $product_special['price'],
				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] : ''
			];
		}

		// Image
		if (!empty($product_info)) {
			$data['image'] = $product_info['image'];
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

		// Images
		if ($product_id) {
			$product_images = $this->model_catalog_product->getImages($product_id);
		} else {
			$product_images = [];
		}

		
		$data['product_images'] = [];

		foreach ($product_images as $product_image) {
			
			if (is_file(DIR_IMAGE. html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $product_image['image'];
				$thumb = $product_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['product_images'][$product_image['color_id']][] = [
				'href'      => DIR_IMAGE. html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8'),
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize(html_entity_decode($thumb, ENT_QUOTES, 'UTF-8'), 100, 100),
				'sort_order' => $product_image['sort_order']
			];
		}
		
		
		$this->load->model('catalog/information');
		
		$informations = $this->model_catalog_information->getInformations();
		
		$data['informations'] = [];
		foreach($informations as $information){
			$data['informations'][$information['type_id']][] = [
				'title' => $information['title'],
				'information_id' => $information['information_id'],
			];
		}
		
		// Points
		if (!empty($product_info)) {
			$data['points'] = $product_info['points'];
		} else {
			$data['points'] = '';
		}

		// Rewards
		if ($product_id) {
			$data['product_reward'] = $this->model_catalog_product->getRewards($product_id);
		} else {
			$data['product_reward'] = [];
		}

		// SEO
		if ($product_id) {
			$data['product_seo_url'] = $this->model_catalog_product->getSeoUrls($product_id);
		} else {
			$data['product_seo_url'] = [];
		}

		// Layouts
		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if ($product_id) {
			$data['product_layout'] = $this->model_catalog_product->getLayouts($product_id);
		} else {
			$data['product_layout'] = [];
		}

		$data['report'] = $this->getReport();
		
		$data['URLIMAGE'] = URL_IMAGE;
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product_form', $data));
	}

	public function save(): void {
		$this->load->language('catalog/product');

		$json = [];

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['name'])) < 1) || (oc_strlen($value['name']) > 255)) {
				$json['error']['name_' . $language_id] = $this->language->get('error_name');
			}

			if ((oc_strlen(trim($value['meta_title'])) < 1) || (oc_strlen($value['meta_title']) > 255)) {
				$json['error']['meta_title_' . $language_id] = $this->language->get('error_meta_title');
			}
		}

		if ((oc_strlen($this->request->post['model']) < 1) || (oc_strlen($this->request->post['model']) > 64)) {
			$json['error']['model'] = $this->language->get('error_model');
		}

		$this->load->model('catalog/product');

		if ($this->request->post['master_id']) {
			$product_options = $this->model_catalog_product->getOptions($this->request->post['master_id']);

			foreach ($product_options as $product_option) {
				if (isset($this->request->post['override']['variant'][$product_option['product_option_id']]) && $product_option['required'] && empty($this->request->post['variant'][$product_option['product_option_id']])) {
					$json['error']['option_' . $product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}
		}

		if ($this->request->post['product_seo_url']) {
			$this->load->model('design/seo_url');
			$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
			
			foreach ($this->request->post['product_seo_url'] as $language_id => $keyword) {
					if ((oc_strlen(trim($keyword)) < 1) || (oc_strlen($keyword) > 100)) {
						$json['error']['keyword_' . $language_id] = $this->language->get('error_keyword');
					}

					$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($keyword, $store_id);

					if ($seo_url_info && ($seo_url_info['key'] != 'product_id' || !isset($this->request->post['product_id']) || $seo_url_info['value'] != (int)$this->request->post['product_id'])) {
						$json['error']['keyword_' .  $language_id] = $this->language->get('error_keyword_exists');
					}
				}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post['product_id']) {
				if (!$this->request->post['master_id']) {
					// Normal product add
					$json['product_id'] = $this->model_catalog_product->addProduct($this->request->post);
				} else {
					// Variant product add
					$json['product_id'] = $this->model_catalog_product->addVariant($this->request->post['master_id'], $this->request->post);
				}
			} else {
				if (!$this->request->post['master_id']) {
					// Normal product edit
					$this->model_catalog_product->editProduct($this->request->post['product_id'], $this->request->post);
				} else {
					// Variant product edit
					$this->model_catalog_product->editVariant($this->request->post['master_id'], $this->request->post['product_id'], $this->request->post);
				}

				// Variant products edit if master product is edited
				$this->model_catalog_product->editVariants($this->request->post['product_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->load->language('catalog/product');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/product');

			foreach ($selected as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function copy(): void {
		$this->load->language('catalog/product');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('catalog/product');

			foreach ($selected as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function report(): void {
		$this->load->language('catalog/product');

		$this->response->setOutput($this->getReport());
	}

	public function getReport(): string {
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->get['page']) && $this->request->get['route'] == 'catalog/product.report') {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$data['reports'] = [];

		$this->load->model('catalog/product');
		$this->load->model('customer/customer');
		$this->load->model('setting/store');

		$results = $this->model_catalog_product->getReports($product_id, ($page - 1) * $limit, $limit);

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
				'store'      => $store,
				'country'    => $result['country'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			];
		}

		$report_total = $this->model_catalog_product->getTotalReports($product_id);

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $report_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('catalog/product.report', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product_id . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($report_total - $limit)) ? $report_total : ((($page - 1) * $limit) + $limit), $report_total, ceil($report_total / $limit));

		return $this->load->view('catalog/product_report', $data);
	}

	public function autocomplete(): void {
		$json = [];

		if (isset($this->request->get['filter_all'])) {
			$filter_all = $this->request->get['filter_all'];
		} else {
			$filter_all = '';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 5;
		}

		$filter_data = [
			'filter_name'  => $filter_name,
			'filter_model' => $filter_model,
			'filter_status' => $filter_status,
			'start'        => 0,
			'limit'        => $limit
		];

		$this->load->model('catalog/product');
		$this->load->model('catalog/option');
		$this->load->model('catalog/subscription_plan');

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			$option_data = [];

			$product_options = $this->model_catalog_product->getOptions($result['product_id']);

			foreach ($product_options as $product_option) {
				$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

				if ($option_info) {
					$product_option_value_data = [];

					foreach ($product_option['product_option_value'] as $product_option_value) {
						$option_value_info = $this->model_catalog_option->getValue($product_option_value['option_value_id']);

						if ($option_value_info) {
							$product_option_value_data[] = [
								'product_option_value_id' => $product_option_value['product_option_value_id'],
								'option_value_id'         => $product_option_value['option_value_id'],
								'name'                    => $option_value_info['name'],
								'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
								'price_prefix'            => $product_option_value['price_prefix']
							];
						}
					}

					$option_data[] = [
						'product_option_id'    => $product_option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'option_id'            => $product_option['option_id'],
						'name'                 => $option_info['name'],
						'type'                 => $option_info['type'],
						'value'                => $product_option['value'],
						'required'             => $product_option['required']
					];
				}
			}

			$subscription_data = [];

			$product_subscriptions = $this->model_catalog_product->getSubscriptions($result['product_id']);

			foreach ($product_subscriptions as $product_subscription) {
				$subscription_plan_info = $this->model_catalog_subscription_plan->getSubscriptionPlan($product_subscription['subscription_plan_id']);

				if ($subscription_plan_info) {
					$subscription_data[] = [
						'subscription_plan_id' => $subscription_plan_info['subscription_plan_id'],
						'name'                 => $subscription_plan_info['name']
					];
				}
			}

			$json[] = [
				'product_id'   => $result['product_id'],
				'name'         => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				'model'        => $result['model'],
				'option'       => $option_data,
				'subscription' => $subscription_data,
				'price'        => $result['price']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getwidget(array $ndata = []) {
		
		$this->load->language('catalog/product');
		$this->load->model('bytao/common');
		
		$json= [];
		$wdata=[];
		$wdata['user_token'] = $this->session->data['user_token'];
		if($ndata){
			$json['new']="0";
			$cData = $ndata;
			array_shift($cData);
			
			if($cData){
				
				$wdata['type'] = $cData[0];
				if($wdata['type']=='1'){
					$wdata['selecteds']=$this->model_bytao_common->getProducts($cData);
				}elseif ($wdata['type']=='4'){
					$wdata['categories']=$this->model_bytao_common->getCategories($cData);
				}
				array_shift($cData);
				if($cData){
					$wdata['pieces'] = $cData[0];
					array_shift($cData);
					$wdata['parts'] = [];
					$wdata['cols'] = 2;
					if($cData){
						$wdata['cols'] = $cData[0];
						array_shift($cData);
						if($cData){
							$wdata['parts'] = $cData;
						}
					}
					
				}else{
					$wdata['pieces'] ='1';
					$wdata['parts'] = [];
				}
				
			}else{
				$wdata['type'] ='0';
				$wdata['selecteds']=[];
				$wdata['categories']=[];
			}
			
		}else{
			$wdata['type']='1';
			$wdata['pieces'] = '1';
			$wdata['parts']=[];
			$json['new']="1";
		}
		
		$json['view'] = $this->load->view('catalog/product_widget_form', $wdata);
		
		if( isset($ndata[1])){
			return $json['view'];
		}else{
			
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}

	public function update_status():void{
		$json= [];
		
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->get['status'])) {
			$type = $this->request->get['status'];
		} else {
			$type = '';
		}
		if($product_id && $type ){
			$this->load->model('catalog/product');
			$this->model_catalog_product->updateStatus($product_id,$type);
			$json['pid']=$product_id;
			$json['type']=$type;
		}else{
			$json['pid']='0';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
