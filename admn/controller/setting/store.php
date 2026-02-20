<?php
namespace Opencart\Admin\Controller\Setting;
class Store extends \Opencart\System\Engine\Controller {
	
	public function index(): void {
		$data['is_ad']=FALSE;
		// TODO byTAO added
		if($this->user->getGroupId() == $this->config->get('config_store_user_group_id'))
		{
			$data['is_ad']=TRUE;
			if(!isset($this->session->data['store_id'])|| $this->session->data['store_id']==0 ){
				$this->response->redirect($this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token']));
			}else{
				$this->response->redirect($this->url->link('setting/store.form', 'user_token=' . $this->session->data['user_token']));
			}
		}
		// endTODO 
		
		
		$this->load->language('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

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
			'href' => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['add'] = $this->url->link('setting/store.form', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('setting/store.delete', 'user_token=' . $this->session->data['user_token']);

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/store', $data));
	}

	public function list(): void {
		$this->load->language('setting/store');

		$this->response->setOutput($this->getList());
	}

	protected function getList(): string {
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('setting/store.list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['stores'] = [];

		$store_total = 0;

		if ($page == 1) {
			$store_total = 1;

			$data['stores'][] = [
				'store_id' => 0,
				'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
				'url'      => HTTP_CATALOG,
				'edit'     => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'])
			];
		}

		$this->load->model('setting/store');

		$this->load->model('setting/setting');

		$store_total += $this->model_setting_store->getTotalStores();

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = [
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
				'url'      => $result['url'],
				'edit'     => $this->url->link('setting/store.form', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $result['store_id'])
			];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $store_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('setting/store.list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($store_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($store_total - $this->config->get('config_pagination_admin'))) ? $store_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $store_total, ceil($store_total / $this->config->get('config_pagination_admin')));

		return $this->load->view('setting/store_list', $data);
	}

	public function form(): void {
		$this->load->language('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['text_form'] = !isset($this->request->get['store_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		
		

		$url = '';

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
			'href' => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_settings'),
			'href' => $this->url->link('setting/store.form', 'user_token=' . $this->session->data['user_token'] . (isset($this->request->post['store_id']) ? '&store_id=' . $this->request->get['store_id'] : '') . $url)
		];

		$data['save'] = $this->url->link('setting/store.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token']);
		$data['toset'] = $this->url->link('bytao/store.settingstore', 'user_token=' . $this->session->data['user_token']);
		$data['ADM']= $this->user->getGroupId();
		
		if (isset($this->request->get['store_id'])) {
			$this->load->model('setting/setting');
			$store_info = $this->model_setting_setting->getSetting('config', $this->request->get['store_id']);
		}

		if (isset($this->request->get['store_id'])) {
			$data['store_id'] = (int)$this->request->get['store_id'];
		} else {
			$data['store_id'] = 0;
		}

		if (isset($store_info['config_url'])) {
			$data['config_url'] = $store_info['config_url'];
		} else {
			$data['config_url'] = '';
		}

		if (isset($store_info['config_meta_title'])) {
			$data['config_meta_title'] = $store_info['config_meta_title'];
		} else {
			$data['config_meta_title'] = '';
		}

		if (isset($store_info['config_meta_description'])) {
			$data['config_meta_description'] = $store_info['config_meta_description'];
		} else {
			$data['config_meta_description'] = '';
		}

		if (isset($store_info['config_meta_keyword'])) {
			$data['config_meta_keyword'] = $store_info['config_meta_keyword'];
		} else {
			$data['config_meta_keyword'] = '';
		}

		$data['themes'] = [];

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getExtensionsByType('theme');

		foreach ($extensions as $extension) {
			$this->load->language('extension/' . $extension['extension'] . '/theme/' . $extension['code'], 'extension');

			$data['themes'][] = [
				'text'  => $this->language->get('extension_heading_title'),
				'value' => $extension['code']
			];
		}

		if (isset($store_info['config_theme'])) {
			$data['config_theme'] = $store_info['config_theme'];
		} else {
			$data['config_theme'] = '';
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($store_info['config_layout_id'])) {
			$data['config_layout_id'] = $store_info['config_layout_id'];
		} else {
			$data['config_layout_id'] = '';
		}

		if (isset($store_info['config_name'])) {
			$data['config_name'] = $store_info['config_name'];
		} else {
			$data['config_name'] = '';
		}

		if (isset($store_info['config_owner'])) {
			$data['config_owner'] = $store_info['config_owner'];
		} else {
			$data['config_owner'] = '';
		}

		if (isset($store_info['config_address'])) {
			$data['config_address'] = $store_info['config_address'];
		} else {
			$data['config_address'] = '';
		}

		if (isset($store_info['config_geocode'])) {
			$data['config_geocode'] = $store_info['config_geocode'];
		} else {
			$data['config_geocode'] = '';
		}

		if (isset($store_info['config_email'])) {
			$data['config_email'] = $store_info['config_email'];
		} else {
			$data['config_email'] = '';
		}


		if (isset($store_info['config_support'])) {
			$data['config_support'] = $store_info['config_support'];
		} else {
			$data['config_support'] = '';
		}

		if (isset($store_info['config_telephone'])) {
			$data['config_telephone'] = $store_info['config_telephone'];
		} else {
			$data['config_telephone'] = '';
		}

		if (isset($store_info['config_fax'])) {
			$data['config_fax'] = $store_info['config_fax'];
		} else {
			$data['config_fax'] = '';
		}

		if (isset($store_info['config_gsm'])) {
			$data['config_gsm'] = $store_info['config_gsm'];
		} else {
			$data['config_gsm'] = '';
		}

		if (isset($store_info['config_image'])) {
			$data['config_image'] = $store_info['config_image'];
		} else {
			$data['config_image'] = '';
		}
		
		if (isset($store_info['config_date_added'])) {
			$data['config_date_added'] = date($this->language->get('date_format_short'), strtotime($store_info['config_date_added']));
		}else{
			$data['config_date_added'] ='';
		}

		if (isset($store_info['config_path'])) {
			$data['config_path'] = $store_info['config_path'];
		}else{
			$data['config_path'] ='';
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (is_file(DIR_IMAGE . html_entity_decode($data['config_image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($data['config_image'], ENT_QUOTES, 'UTF-8'), 750, 90);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		if (isset($store_info['config_open'])) {
			$data['config_open'] = $store_info['config_open'];
		} else {
			$data['config_open'] = '';
		}

		if (isset($store_info['config_comment'])) {
			$data['config_comment'] = $store_info['config_comment'];
		} else {
			$data['config_comment'] = '';
		}

		$this->load->model('localisation/location');

		$data['locations'] = $this->model_localisation_location->getLocations();

		if (isset($store_info['config_location'])) {
			$data['config_location'] = $store_info['config_location'];
		} else {
			$data['config_location'] = [];
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($store_info['config_country_id'])) {
			$data['config_country_id'] = $store_info['config_country_id'];
		} else {
			$data['config_country_id'] = $this->config->get('config_country_id');
		}

		if (isset($store_info['config_zone_id'])) {
			$data['config_zone_id'] = $store_info['config_zone_id'];
		} else {
			$data['config_zone_id'] = $this->config->get('config_zone_id');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($store_info['config_language'])) {
			$data['config_language'] = $store_info['config_language'];
		} else {
			$data['config_language'] = $this->config->get('config_language');
		}

		// TODO bytao store languages + currency
		if($this->user->getGroupId() == $this->config->get('config_store_user_group_id'))
		{
			$this->load->model('bytao/common');
			$data['languages'] = $this->model_bytao_common->getStoreLanguages();
			$data['is_ad']=false;
		}else{
			$this->load->model('localisation/language');
			$data['languages'] = $this->model_localisation_language->getLanguages();
			$data['is_ad']=TRUE;
		}
		
		
		if (isset($store_info['config_maintenance'])) {
			$data['config_maintenance'] = $store_info['config_maintenance'];
		} 
		
		if (isset($store_info['config_footer_column'])) {
			$data['config_footer_column'] = $store_info['config_footer_column'];
		} else {
			$data['config_footer_column'] = '';
		}
		
		if (isset($store_info['config_store_languages'])) {
			$data['config_store_languages'] = $store_info['config_store_languages'];
		} else {
			$data['config_store_languages'] = [];
		}
		
		if (isset($store_info['config_store_currencies'])) {
			$data['config_store_currencies'] = $store_info['config_store_currencies'];
		} else {
			$data['config_store_currencies'] = [];
		}
		
		if (isset($store_info['config_store_head'])) {
			$data['config_store_head'] = $store_info['config_store_head'];
		} else {
			$data['config_store_head'] = 0;
		}
		
		if (isset($store_info['config_store_core'])) {
			$data['config_store_core'] = $store_info['config_store_core'];
		} else {
			$data['config_store_core'] = '0';
		}
		
		if (isset($store_info['config_store_script_pose'])) {
			$data['config_store_script_pose'] = $store_info['config_store_script_pose'];
		} else {
			$data['config_store_script_pose'] = '0';
		}
		
		if (isset($store_info['config_default_country_id'])) {
			$data['config_default_country_id'] = $store_info['config_default_country_id'];
		} else {
			$data['config_default_country_id'] = '0';
		}
		
		if (isset($store_info['config_url_out'])) {
			$data['config_url_out'] = $store_info['config_url_out'];
		} else {
			$data['config_url_out'] = '0';
		}
		
		if (isset($store_info['config_store_type'])) {
			$data['config_store_type'] = $store_info['config_store_type'];
		} else {
			$data['config_store_type'] = '0';
		}
		
		if (isset($store_info['config_wholesale_admin'])) {
			$data['config_wholesale_admin'] = $store_info['config_wholesale_admin'];
		} else {
			$data['config_wholesale_admin'] = '0';
		}
		
		if (isset($store_info['config_icon'])) {
			$data['config_icon'] = $store_info['config_icon'];
		} else {
			$data['config_icon'] = '';
		}

		if (is_file(DIR_IMAGE . html_entity_decode($data['config_icon'], ENT_QUOTES, 'UTF-8'))) {
			$data['icon'] = $this->model_tool_image->resize(html_entity_decode($data['config_icon'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['icon'] = $data['placeholder'];
		}
		
		$data['config_logo_negative'] =  isset($store_info['config_logo_negative'])?$store_info['config_logo_negative']:'';

		if (isset($store_info['config_logo_negative']) && is_file(DIR_IMAGE . html_entity_decode($store_info['config_logo_negative'], ENT_QUOTES, 'UTF-8'))) {
			$data['logo_negative'] = $this->model_tool_image->resize(html_entity_decode($store_info['config_logo_negative'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['logo_negative'] = $data['placeholder'];
		}
		
		if (isset($store_info['config_header_image'])) {
			$data['config_header_image'] = $store_info['config_header_image'];
		} else {
			$data['config_header_image'] = '';
		}
		if (isset($store_info['config_footer_image'])) {
			$data['config_footer_image'] = $store_info['config_footer_image'];
		} else {
			$data['config_footer_image'] = '';
		}
		if (isset($store_info['config_fligram_image'])) {
			$data['config_fligram_image'] = $store_info['config_fligram_image'];
		} else {
			$data['config_fligram_image'] = '';
		}

		if (is_file(DIR_IMAGE . html_entity_decode($data['config_header_image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb_header'] = $this->model_tool_image->resize(html_entity_decode($data['config_header_image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb_header'] = $data['placeholder'];
		}
		
		if (is_file(DIR_IMAGE . html_entity_decode($data['config_footer_image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb_footer'] = $this->model_tool_image->resize(html_entity_decode($data['config_footer_image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb_footer'] = $data['placeholder'];
		}
		
		if (is_file(DIR_IMAGE . html_entity_decode($data['config_fligram_image'], ENT_QUOTES, 'UTF-8'))) {
			$data['thumb_fligram'] = $this->model_tool_image->resize(html_entity_decode($data['config_fligram_image'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['thumb_fligram'] = $data['placeholder'];
		}
		
		if (isset($store_info['config_maintenance'])) {
			$data['config_maintenance'] = $store_info['config_maintenance'];
		} else {
			$data['config_maintenance'] = 0;
		}
		if (isset($store_info['config_seo_url'])) {
			$data['config_seo_url'] = $store_info['config_seo_url'];
		} else {
			$data['config_seo_url'] = 0;
		}
		
		if (isset($store_info['config_session_expire'])) {
			$data['config_session_expire'] = $store_info['config_session_expire'];
		} else {
			$data['config_session_expire'] = $this->config->get('config_session_expire');
		}
		
		if (isset($store_info['config_session_samesite'])) {
			$data['config_session_samesite'] = $store_info['config_session_samesite'];
		} else {
			$data['config_session_samesite'] = $this->config->get('config_session_samesite');
		}
		
		if (isset($store_info['config_robots'])) {
			$data['config_robots'] =$store_info['config_robots'];
		} else {
			$data['config_robots'] =  $this->config->get('config_robots');
		}

		
		
		
		if (isset($store_info['config_order_key'])) {
			$data['config_order_key'] = $store_info['config_order_key'];
		} else {
			$data['config_order_key'] = 0;
		}
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['config_order_status_id'] = $this->config->get('config_order_status_id');
		
		// Captcha
		$data['config_captcha'] = isset($store_info['config_captcha'])? $store_info['config_captcha']:'';

		$this->load->model('setting/extension');

		$data['captchas'] = [];

		// Get a list of installed captchas
		$extensions = $this->model_setting_extension->getExtensionsByType('captcha');

		foreach ($extensions as $extension) {
			$this->load->language('extension/' . $extension['extension'] . '/captcha/' . $extension['code'], 'extension');

			if ($this->config->get('captcha_' . $extension['code'] . '_status')) {
				$data['captchas'][] = [
					'text'  => $this->language->get('extension_heading_title'),
					'value' => $extension['code']
				];
			}
		}

		if (isset($store_info['config_captcha_page'])) {
		   	$data['config_captcha_page'] = $store_info['config_captcha_page'];
		} else {
			$data['config_captcha_page'] = [];
		}

		$data['captcha_pages'] = [];

		$data['captcha_pages'][] = [
			'text'  => $this->language->get('text_register'),
			'value' => 'register'
		];

		$data['captcha_pages'][] = [
			'text'  => $this->language->get('text_guest'),
			'value' => 'guest'
		];

		$data['captcha_pages'][] = [
			'text'  => $this->language->get('text_review'),
			'value' => 'review'
		];

		$data['captcha_pages'][] = [
			'text'  => $this->language->get('text_return'),
			'value' => 'returns'
		];

		$data['captcha_pages'][] = [
			'text'  => $this->language->get('text_contact'),
			'value' => 'contact'
		];
		
		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();
		
		if (isset($store_info['config_currency'])) {
			$data['config_currency'] = $store_info['config_currency'];
		} else {
			$data['config_currency'] = $this->config->get('config_currency');
		}


		$data['currency_engines'] = [];

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getExtensionsByType('currency');

		foreach ($extensions as $extension) {
			if ($this->config->get('currency_' . $extension['code'] . '_status')) {
				$this->load->language('extension/' . $extension['extension'] . '/currency/' . $extension['code'], 'extension');

				$data['currency_engines'][] = [
					'text'  => $this->language->get('extension_heading_title'),
					'value' => $extension['code']
				];
			}
		}
		
		if (isset($store_info['config_currency_engine'])) {
			$data['config_currency_engine'] = $store_info['config_currency_engine'];
		} else {
			$data['config_currency_engine'] = $this->config->get('config_currency_engine');
		}
		
		if (isset($store_info['config_currency_auto'])) {
			$data['config_currency_auto'] = $store_info['config_currency_auto'];
		} else {
			$data['config_currency_auto'] = $this->config->get('config_currency_auto');
		}
		
		if (isset($store_info['config_fax'])) {
			$data['config_fax'] = $store_info['config_fax'];
		} else {
			$data['config_fax'] = $this->config->get('config_fax');
		}
		if (isset($store_info['config_gsm'])) {
			$data['config_gsm'] = $store_info['config_gsm'];
		} else {
			$data['config_gsm'] = $this->config->get('config_gsm');
		}
		
		if ($this->config->has('config_timezone')) {
			$data['config_timezone'] = $this->config->get('config_timezone');
		} else {
			$data['config_timezone'] = 'UTC';
		}

		$data['timezones'] = [];

		$timestamp = date_create('now');

		$timezones = timezone_identifiers_list();

		foreach ($timezones as $timezone) {
			date_timezone_set($timestamp, timezone_open($timezone));

			$hour = ' (' . date_format($timestamp, 'P') . ')';

			$data['timezones'][] = [
				'text'  => $timezone . $hour,
				'value' => $timezone
			];
		}
		
		
		//endTODO
		
		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		if (isset($store_info['config_currency'])) {
			$data['config_currency'] = $store_info['config_currency'];
		} else {
			$data['config_currency'] = $this->config->get('config_currency');
		}
		
		if (isset($store_info['config_length_class_id'])) {
			$data['config_length_class_id'] = $store_info['config_length_class_id'];
		} else {
			$data['config_length_class_id'] = $this->config->get('config_length_class_id');
		}
		
		$this->load->model('localisation/length_class');
		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
		
		$this->load->model('localisation/weight_class');
		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
		
		if (isset($store_info['config_weight_class_id'])) {
			$data['config_weight_class_id'] = $store_info['config_weight_class_id'];
		} else {
			$data['config_weight_class_id'] = $this->config->get('config_weight_class_id');
		}
		


		// Options
		if (isset($store_info['config_product_description_length'])) {
			$data['config_product_description_length'] = $store_info['config_product_description_length'];
		} else {
			$data['config_product_description_length'] = 100;
		}

		if (isset($store_info['config_pagination'])) {
			$data['config_pagination'] = $store_info['config_pagination'];
		} else {
			$data['config_pagination'] = 15;
		}

		if (isset($store_info['config_product_count'])) {
			$data['config_product_count'] = $store_info['config_product_count'];
		} else {
			$data['config_product_count'] = 10;
		}

		if (isset($store_info['config_cookie_id'])) {
			$data['config_cookie_id'] = $store_info['config_cookie_id'];
		} else {
			$data['config_cookie_id'] = '';
		}

		if (isset($store_info['config_gdpr_id'])) {
			$data['config_gdpr_id'] = $store_info['config_gdpr_id'];
		} else {
			$data['config_gdpr_id'] = '';
		}
		
		if (isset($store_info['config_gdpr_limit'])) {
			$data['config_gdpr_limit'] = $store_info['config_gdpr_limit'];
		} else {
			$data['config_gdpr_limit'] = $this->config->get('config_gdpr_limit');
		}
		

		if (isset($store_info['config_tax'])) {
			$data['config_tax'] = $store_info['config_tax'];
		} else {
			$data['config_tax'] = '';
		}

		if (isset($store_info['config_tax_default'])) {
			$data['config_tax_default'] = $store_info['config_tax_default'];
		} else {
			$data['config_tax_default'] = '';
		}

		if (isset($store_info['config_tax_customer'])) {
			$data['config_tax_customer'] = $store_info['config_tax_customer'];
		} else {
			$data['config_tax_customer'] = '';
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($store_info['config_customer_group_id'])) {
			$data['config_customer_group_id'] = $store_info['config_customer_group_id'];
		} else {
			$data['config_customer_group_id'] = '';
		}

		if (isset($store_info['config_customer_group_display'])) {
			$data['config_customer_group_display'] = $store_info['config_customer_group_display'];
		} else {
			$data['config_customer_group_display'] = [];
		}

		if (isset($store_info['config_customer_price'])) {
			$data['config_customer_price'] = $store_info['config_customer_price'];
		} else {
			$data['config_customer_price'] = '';
		}

		$this->load->model('catalog/information');

		$data['informations'] = $this->model_catalog_information->getInformations();

		if (isset($store_info['config_account_id'])) {
			$data['config_account_id'] = $store_info['config_account_id'];
		} else {
			$data['config_account_id'] = '';
		}

		if (isset($store_info['config_cart_weight'])) {
			$data['config_cart_weight'] = $store_info['config_cart_weight'];
		} else {
			$data['config_cart_weight'] = '';
		}

		if (isset($store_info['config_checkout_guest'])) {
			$data['config_checkout_guest'] = $store_info['config_checkout_guest'];
		} else {
			$data['config_checkout_guest'] = '';
		}

		if (isset($store_info['config_checkout_id'])) {
			$data['config_checkout_id'] = $store_info['config_checkout_id'];
		} else {
			$data['config_checkout_id'] = '';
		}

		if (isset($store_info['config_stock_display'])) {
			$data['config_stock_display'] = $store_info['config_stock_display'];
		} else {
			$data['config_stock_display'] = '';
		}

		if (isset($store_info['config_stock_checkout'])) {
			$data['config_stock_checkout'] = $store_info['config_stock_checkout'];
		} else {
			$data['config_stock_checkout'] = '';
		}

		if (isset($store_info['config_cart_reward'])) {
			$data['config_cart_reward'] = $store_info['config_cart_reward'];
		} else {
			$data['config_cart_reward'] = '0';
		}
		if (isset($store_info['config_cart_shipping'])) {
			$data['config_cart_shipping'] = $store_info['config_cart_shipping'];
		} else {
			$data['config_cart_shipping'] = '0';
		}
		if (isset($store_info['config_cart_coupon'])) {
			$data['config_cart_coupon'] = $store_info['config_cart_coupon'];
		} else {
			$data['config_cart_coupon'] = '0';
		}
		if (isset($store_info['config_cart_voucher'])) {
			$data['config_cart_voucher'] = $store_info['config_cart_voucher'];
		} else {
			$data['config_cart_voucher'] = '0';
		}
		
		// Images
		if (isset($store_info['config_logo'])) {
			$data['config_logo'] = $store_info['config_logo'];
		} else {
			$data['config_logo'] = '';
		}

		if (is_file(DIR_IMAGE . html_entity_decode($data['config_logo'], ENT_QUOTES, 'UTF-8'))) {
			$data['logo'] = $this->model_tool_image->resize(html_entity_decode($data['config_logo'], ENT_QUOTES, 'UTF-8'), 100, 100);
		} else {
			$data['logo'] = $data['placeholder'];
		}

		if (isset($store_info['config_image_category_width'])) {
			$data['config_image_category_width'] = $store_info['config_image_category_width'];
		} else {
			$data['config_image_category_width'] = 80;
		}

		if (isset($store_info['config_image_category_height'])) {
			$data['config_image_category_height'] = $store_info['config_image_category_height'];
		} else {
			$data['config_image_category_height'] = 80;
		}

		if (isset($store_info['config_image_thumb_width'])) {
			$data['config_image_thumb_width'] = $store_info['config_image_thumb_width'];
		} else {
			$data['config_image_thumb_width'] = 228;
		}

		if (isset($store_info['config_image_thumb_height'])) {
			$data['config_image_thumb_height'] = $store_info['config_image_thumb_height'];
		} else {
			$data['config_image_thumb_height'] = 228;
		}

		if (isset($store_info['config_image_popup_width'])) {
			$data['config_image_popup_width'] = $store_info['config_image_popup_width'];
		} else {
			$data['config_image_popup_width'] = 500;
		}

		if (isset($store_info['config_image_popup_height'])) {
			$data['config_image_popup_height'] = $store_info['config_image_popup_height'];
		} else {
			$data['config_image_popup_height'] = 500;
		}

		if (isset($store_info['config_image_product_width'])) {
			$data['config_image_product_width'] = $store_info['config_image_product_width'];
		} else {
			$data['config_image_product_width'] = 228;
		}

		if (isset($store_info['config_image_product_height'])) {
			$data['config_image_product_height'] = $store_info['config_image_product_height'];
		} else {
			$data['config_image_product_height'] = 228;
		}

		if (isset($store_info['config_image_additional_width'])) {
			$data['config_image_additional_width'] = $store_info['config_image_additional_width'];
		} else {
			$data['config_image_additional_width'] = 74;
		}

		if (isset($store_info['config_image_additional_height'])) {
			$data['config_image_additional_height'] = $store_info['config_image_additional_height'];
		} else {
			$data['config_image_additional_height'] = 74;
		}

		if (isset($store_info['config_image_related_width'])) {
			$data['config_image_related_width'] = $store_info['config_image_related_width'];
		} else {
			$data['config_image_related_width'] = 80;
		}

		if (isset($store_info['config_image_related_height'])) {
			$data['config_image_related_height'] = $store_info['config_image_related_height'];
		} else {
			$data['config_image_related_height'] = 80;
		}

		if (isset($store_info['config_image_compare_width'])) {
			$data['config_image_compare_width'] = $store_info['config_image_compare_width'];
		} else {
			$data['config_image_compare_width'] = 90;
		}

		if (isset($store_info['config_image_compare_height'])) {
			$data['config_image_compare_height'] = $store_info['config_image_compare_height'];
		} else {
			$data['config_image_compare_height'] = 90;
		}

		if (isset($store_info['config_image_wishlist_width'])) {
			$data['config_image_wishlist_width'] = $store_info['config_image_wishlist_width'];
		} else {
			$data['config_image_wishlist_width'] = 47;
		}

		if (isset($store_info['config_image_wishlist_height'])) {
			$data['config_image_wishlist_height'] = $store_info['config_image_wishlist_height'];
		} else {
			$data['config_image_wishlist_height'] = 47;
		}

		if (isset($store_info['config_image_cart_width'])) {
			$data['config_image_cart_width'] = $store_info['config_image_cart_width'];
		} else {
			$data['config_image_cart_width'] = 47;
		}

		if (isset($store_info['config_image_cart_height'])) {
			$data['config_image_cart_height'] = $store_info['config_image_cart_height'];
		} else {
			$data['config_image_cart_height'] = 47;
		}

		if (isset($store_info['config_image_location_width'])) {
			$data['config_image_location_width'] = $store_info['config_image_location_width'];
		} else {
			$data['config_image_location_width'] = 268;
		}

		if (isset($store_info['config_image_location_height'])) {
			$data['config_image_location_height'] = $store_info['config_image_location_height'];
		} else {
			$data['config_image_location_height'] = 50;
		}
		
		if (isset($store_info['config_image_article_width'])) {
			$data['config_image_article_width'] = $store_info['config_image_article_width'];
		} else {
			$data['config_image_article_width'] = 100;
		}
		
		if (isset($store_info['config_image_article_height'])) {
			$data['config_image_article_height'] = $store_info['config_image_article_height'];
		} else {
			$data['config_image_article_height'] = 50;
		}
		
		if (isset($store_info['config_image_topic_width'])) {
			$data['config_image_topic_width'] = $store_info['config_image_topic_width'];
		} else {
			$data['config_image_topic_width'] = 100;
		}
		
		if (isset($store_info['config_image_topic_height'])) {
			$data['config_image_topic_height'] = $store_info['config_image_topic_height'];
		} else {
			$data['config_image_topic_height'] = 50;
		}
		
		// Mail
		if (isset($store_info['config_mail_engine'])) {
			$data['config_mail_engine'] = $store_info['config_mail_engine'];
		} else {
			$data['config_mail_engine'] = $this->config->get('config_mail_engine');
		}
		if (isset($store_info['config_mail_parameter'])) {
			$data['config_mail_parameter'] = $store_info['config_mail_parameter'];
		} else {
			$data['config_mail_parameter'] = $this->config->get('config_mail_parameter');
		}
		
		if (isset($store_info['config_mail_smtp_hostname'])) {
			$data['config_mail_smtp_hostname'] = $store_info['config_mail_smtp_hostname'];
		} else {
			$data['config_mail_smtp_hostname'] = $this->config->get('config_mail_smtp_hostname');
		}
		
		if (isset($store_info['config_mail_smtp_username'])) {
			$data['config_mail_smtp_username'] = $store_info['config_mail_smtp_username'];
		} else {
			$data['config_mail_smtp_username'] = $this->config->get('config_mail_smtp_username');
		}
		
		if (isset($store_info['config_mail_smtp_password'])) {
			$data['config_mail_smtp_password'] = $store_info['config_mail_smtp_password'];
		} else {
			$data['config_mail_smtp_password'] = $this->config->get('config_mail_smtp_password');
		}
		
		if (isset($store_info['config_mail_smtp_port'])) {
			$data['config_mail_smtp_port'] = $store_info['config_mail_smtp_port'];
		} else {
			$data['config_mail_smtp_port'] = $this->config->get('config_mail_smtp_port');
		}
		
		if (isset($store_info['config_mail_smtp_timeout'])) {
			$data['config_mail_smtp_timeout'] = $store_info['config_mail_smtp_timeout'];
		} else {
			$data['config_mail_smtp_timeout'] = $this->config->get('config_mail_smtp_timeout');
		}
		
		if (isset($store_info['config_mail_mailgun_api'])) {
			$data['config_mail_mailgun_api'] = $store_info['config_mail_mailgun_api'];
		} else {
			$data['config_mail_mailgun_api'] = '';
		}
		
		if (isset($store_info['config_mail_alert'])) {
			$data['config_mail_alert'] = $store_info['config_mail_alert'];
		} else {
			$data['config_mail_alert'] = $this->config->get('config_mail_alert');
		}
		
		if (isset($store_info['config_mail_alert_email'])) {
			$data['config_mail_alert_email'] = $store_info['config_mail_alert_email'];
		} else {
			$data['config_mail_alert_email'] = $this->config->get('config_mail_alert_email');
		}
		
		

		


		$data['mail_alerts'] = [];

		$data['mail_alerts'][] = [
			'text'  => $this->language->get('text_mail_account'),
			'value' => 'account'
		];

		$data['mail_alerts'][] = [
			'text'  => $this->language->get('text_mail_affiliate'),
			'value' => 'affiliate'
		];

		$data['mail_alerts'][] = [
			'text'  => $this->language->get('text_mail_order'),
			'value' => 'order'
		];

		$data['mail_alerts'][] = [
			'text'  => $this->language->get('text_mail_review'),
			'value' => 'review'
		];

		

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/store_form', $data));
	}

	public function save(): void {
		$this->load->language('setting/store');

		$json = [];

		if (!$this->user->hasPermission('modify', 'setting/store')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['config_url']) {
			$json['error']['url'] = $this->language->get('error_url');
		}

		if (!$this->request->post['config_meta_title']) {
			$json['error']['meta_title'] = $this->language->get('error_meta_title');
		}

		if (!$this->request->post['config_name']) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if ((oc_strlen($this->request->post['config_owner']) < 3) || (oc_strlen($this->request->post['config_owner']) > 64)) {
			$json['error']['owner'] = $this->language->get('error_owner');
		}

		if ((oc_strlen($this->request->post['config_address']) < 3) || (oc_strlen($this->request->post['config_address']) > 256)) {
			$json['error']['address'] = $this->language->get('error_address');
		}

		if ((oc_strlen($this->request->post['config_email']) > 96) || !filter_var($this->request->post['config_email'], FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		if ((oc_strlen($this->request->post['config_telephone']) < 3) || (oc_strlen($this->request->post['config_telephone']) > 32)) {
			$json['error']['telephone'] = $this->language->get('error_telephone');
		}

		if (!empty($this->request->post['config_customer_group_display']) && !in_array($this->request->post['config_customer_group_id'], $this->request->post['config_customer_group_display'])) {
			$json['error']['customer_group_display'] = $this->language->get('error_customer_group_display');
		}

		if (!$this->request->post['config_product_description_length']) {
			$json['error']['product_description_length'] = $this->language->get('error_product_description_length');
		}

		if (!$this->request->post['config_pagination']) {
			$json['error']['pagination'] = $this->language->get('error_pagination');
		}

		if (!$this->request->post['config_image_category_width'] || !$this->request->post['config_image_category_height']) {
			$json['error']['image_category'] = $this->language->get('error_image_category');
		}

		if (!$this->request->post['config_image_thumb_width'] || !$this->request->post['config_image_thumb_height']) {
			$json['error']['image_thumb'] = $this->language->get('error_image_thumb');
		}

		if (!$this->request->post['config_image_popup_width'] || !$this->request->post['config_image_popup_height']) {
			$json['error']['image_popup'] = $this->language->get('error_image_popup');
		}

		if (!$this->request->post['config_image_product_width'] || !$this->request->post['config_image_product_height']) {
			$json['error']['image_product'] = $this->language->get('error_image_product');
		}

		if (!$this->request->post['config_image_additional_width'] || !$this->request->post['config_image_additional_height']) {
			$json['error']['image_additional'] = $this->language->get('error_image_additional');
		}

		if (!$this->request->post['config_image_related_width'] || !$this->request->post['config_image_related_height']) {
			$json['error']['image_related'] = $this->language->get('error_image_related');
		}

		if (!$this->request->post['config_image_compare_width'] || !$this->request->post['config_image_compare_height']) {
			$json['error']['image_compare'] = $this->language->get('error_image_compare');
		}

		if (!$this->request->post['config_image_wishlist_width'] || !$this->request->post['config_image_wishlist_height']) {
			$json['error']['image_wishlist'] = $this->language->get('error_image_wishlist');
		}

		if (!$this->request->post['config_image_cart_width'] || !$this->request->post['config_image_cart_height']) {
			$json['error']['image_cart'] = $this->language->get('error_image_cart');
		}

		if (!$this->request->post['config_image_location_width'] || !$this->request->post['config_image_location_height']) {
			$json['error']['image_location'] = $this->language->get('error_image_location');
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->load->model('setting/store');

			if (!$this->request->post['store_id']) {
				$json['store_id'] = $this->model_setting_store->addStore($this->request->post);

				$this->model_setting_setting->editSetting('config', $this->request->post, $json['store_id']);
			} else {
				$this->model_setting_store->editStore($this->request->post['store_id'], $this->request->post);

				$this->model_setting_setting->editSetting('config', $this->request->post, $this->request->post['store_id']);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->load->language('setting/store');

		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', 'setting/store')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('sale/order');
		$this->load->model('sale/subscription');

		foreach ($selected as $store_id) {
			if (!$store_id) {
				$json['error'] = $this->language->get('error_default');
			}

			$order_total = $this->model_sale_order->getTotalOrdersByStoreId($store_id);

			if ($order_total) {
				$json['error'] = sprintf($this->language->get('error_store'), $order_total);
			}

			$subscription_total = $this->model_sale_subscription->getTotalSubscriptionsByStoreId($store_id);

			if ($subscription_total) {
				$json['error'] = sprintf($this->language->get('error_store'), $subscription_total);
			}
		}

		if (!$json) {
			$this->load->model('setting/store');

			$this->load->model('setting/setting');

			foreach ($selected as $store_id) {
				$this->model_setting_store->deleteStore($store_id);

				$this->model_setting_setting->deleteSetting('config', $store_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
