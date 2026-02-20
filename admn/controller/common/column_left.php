<?php
namespace Opencart\Admin\Controller\Common;
class ColumnLeft extends \Opencart\System\Engine\Controller {
	public function index(): string {
		if (isset($this->request->get['user_token']) && isset($this->session->data['user_token']) && ((string)$this->request->get['user_token'] == $this->session->data['user_token'])) {
			$this->load->language('common/column_left');
			
			$gi = $data['gID'] = $this->user->getGroupId();
			
			if($gi == $this->config->get('config_store_user_group_id')){
				$data['uGDI'] = $this->config->get('config_store_user_group_id');
				$this->load->model('bytao/muser');
				$_menus[] = [
					'id'       => 'menu-dashboard',
					'icon'	   => 'fas fa-home',
					'name'	   => $this->language->get('text_dashboard'),
					'href'     => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']),
					'children' => []
				];
				
				$menus = $this->model_bytao_muser->getMenuRender(null,0,$this->user->getId());
				$data['menus'] = $result = array_merge($_menus, $menus);
			}
			else
			{ //Top admin

				$token_style='user_token';
				$token = $this->session->data['user_token'];
				
				// Menu
				$data['menus'][] = [
					'id'       => 'menu-dashboard',
					'icon'	   => 'fas fa-home',
					'name'	   => $this->language->get('text_dashboard'),
					'href'     => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']),
					'children' => []
				];
				
				$this->load->language('bytao/common');
				
				$tools = [];
				
				if($this->user->hasPermission('access', 'google/analytics')){
					$tools[] = [
						'name'	   => $this->language->get('text_google_analytic'),
						'href'     => $this->url->link('google/analytics', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'google/recaptcha')){
					$tools[] = [
						'name'	   => $this->language->get('text_google_recaptcha'),
						'href'     => $this->url->link('google/recaptcha', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/document')){
					$tools[] = [
						'name'	   => $this->language->get('text_document'),
						'href'     => $this->url->link('bytao/document', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/campaign')){
					$tools[] = [
						'name'	   => $this->language->get('text_campaign'),
						'href'     => $this->url->link('bytao/campaign', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/discount')){
					$tools[] = [
						'name'	   => $this->language->get('text_discount'),
						'href'     => $this->url->link('bytao/discount', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/muser')){
					$tools[] = [
						'name'	   => $this->language->get('text_user'),
						'href'     => $this->url->link('bytao/muser', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/analysis')){
					$tools[] = [
						'name'	   => $this->language->get('text_analysis'),
						'href'     => $this->url->link('bytao/analysis', $token_style.'=' . $token, true),
						'children' => []
					];
				}
			
				
			
				if($this->user->hasPermission('access', 'bytao/expenses')){
					$tools[] = [
						'name'	   => $this->language->get('text_expenses'),
						'href'     => $this->url->link('bytao/expenses', $token_style.'=' . $token, true),
						'children' => []
					];
				}
			
				if($this->user->hasPermission('access', 'bytao/export_import')){
					$tools[] = [
						'name'	   => $this->language->get('text_export_import'),
						'href'     => $this->url->link('bytao/export_import', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				$blog= [];
				/*if($this->user->hasPermission('access', 'blog/article')){
					$blog[] = [
						'name'	   => $this->language->get('text_blog_article'),
						'href'     => $this->url->link('blog/article', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'blog/article_category')){
					$blog[] = [
						'name'	   => $this->language->get('text_blog_article_category'),
						'href'     => $this->url->link('blog/article_category', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'blog/article_writer')){
					$blog[] = [
						'name'	   => $this->language->get('text_blog_article_writer'),
						'href'     => $this->url->link('blog/article_writer', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'blog/article_review')){
					$blog[] = [
						'name'	   => $this->language->get('text_blog_article_review'),
						'href'     => $this->url->link('blog/article_review', $token_style.'=' . $token, true),
						'children' => []
					];
				}*/
				
				
				$design = [];
				$child=[];
				if($this->user->hasPermission('access', 'bytao/art')){
					$child[] = [
						'name'	   => $this->language->get('text_art'),
						'href'     => $this->url->link('bytao/art', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/art_category')){
					$child[] = [
						'name'	   => $this->language->get('text_art_category'),
						'href'     => $this->url->link('bytao/art_category', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/art')){
					$design[] = [
						'name'	   => $this->language->get('text_parent_art'),
						'href'     => '',
						'children' => $child
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/trivia')){
					$design[] = [
						'name'	   => $this->language->get('text_trivia'),
						'href'     => $this->url->link('bytao/trivia', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/award')){
					$design[] = [
						'name'	   => $this->language->get('text_award'),
						'href'     => $this->url->link('bytao/award', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/banner')){
					$design[] = [
						'name'	   => $this->language->get('text_banner'),
						'href'     => $this->url->link('bytao/banner', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/appointment')){
					$design[] = [
						'name'	   => $this->language->get('text_appointment'),
						'href'     => $this->url->link('bytao/appointment', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/forms')){
					$design[] = [
						'name'	   => $this->language->get('text_forms'),
						'href'     => $this->url->link('bytao/forms', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/pdf')){
					$design[] = [
						'name'	   => $this->language->get('text_pdf'),
						'href'     => $this->url->link('bytao/pdf', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/css')){
					$design[] = [
						'name'	   => $this->language->get('text_css'),
						'href'     => $this->url->link('bytao/css', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/faq')){
					$design[] = [
						'name'	   => $this->language->get('text_faq'),
						'href'     => $this->url->link('bytao/faq', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/ode')){
					$design[] = [
						'name'	   => $this->language->get('text_ode'),
						'href'     => $this->url->link('bytao/ode', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/menu')){
					$design[] = [
						'name'	   => $this->language->get('text_menu'),
						'href'     => $this->url->link('bytao/menu',  $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/script')){
					$design[] = [
						'name'	   => $this->language->get('text_script'),
						'href'     => $this->url->link('bytao/script', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/jqscript')){
					$design[] = [
						'name'	   => $this->language->get('text_jqscript'),
						'href'     => $this->url->link('bytao/jqscript', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/footer')){
					$design[] = [
						'name'	   => $this->language->get('text_footer'),
						'href'     => $this->url->link('bytao/footer', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/sectioner')){
					$design[] = [
						'name'	   => $this->language->get('text_sectioner'),
						'href'     => $this->url->link('bytao/sectioner', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/home')){
					$design[] = [
						'name'	   => $this->language->get('text_home'),
						'href'     => $this->url->link('bytao/home', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/box')){
					$design[] = [
						'name'	   => $this->language->get('text_box'),
						'href'     => $this->url->link('bytao/box', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/page')){
					$design[] = [
						'name'	   => $this->language->get('text_page'),
						'href'     => $this->url->link('bytao/page', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if ($this->user->hasPermission('access', 'bytao/firm')) {
					$design[] = [
						'name'	   => $this->language->get('text_firm'),
						'href'     => $this->url->link('bytao/firm', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if ($this->user->hasPermission('access', 'catalog/information')) {
					$design[] = [
						'name'	   => $this->language->get('text_information'),
						'href'     => $this->url->link('catalog/information', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}
				
				if ($this->user->hasPermission('access', 'bytao/library')) {
					$design[] = [
						'name'	   => $this->language->get('text_library'),
						'href'     => $this->url->link('bytao/library', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/popup')){
					$design[] = [
						'name'	   => $this->language->get('text_popup'),
						'href'     => $this->url->link('bytao/popup', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/news')){
					$design[] = [
						'name'	   => $this->language->get('text_news'),
						'href'     => $this->url->link('bytao/news', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/member')){
					$design[] = [
						'name'	   => $this->language->get('text_member'),
						'href'     => $this->url->link('bytao/member', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				if($this->user->hasPermission('access', 'bytao/prod')){
					$design[] = [
						'name'	   => $this->language->get('text_prod'),
						'href'     => $this->url->link('bytao/prod', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/prod_cat')){
					$design[] = [
						'name'	   => $this->language->get('text_prod_cat'),
						'href'     => $this->url->link('bytao/prod_cat', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/newsletter')){
					$design[] = [
						'name'	   => $this->language->get('text_newsletter'),
						'href'     => $this->url->link('bytao/newsletter', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/slideshow')){
					$design[] = [
						'name'	   => $this->language->get('text_bytao_slideshow'),
						'href'     => $this->url->link('bytao/slideshow', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/layerslider')){
					$design[] = [
						'name'	   => $this->language->get('text_layerslider'),
						'href'     => $this->url->link('bytao/layerslider', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/pslide')){
					$design[] = [
						'name'	   => $this->language->get('text_pslide'),
						'href'     => $this->url->link('bytao/pslide', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				if($this->user->hasPermission('access', 'bytao/press')){
					$design[] = [
						'name'	   => $this->language->get('text_press'),
						'href'     => $this->url->link('bytao/press', $token_style.'=' . $token, true),
						'children' => []
					];
				}
				
				$bytao = [];
				
				/*$bytao[] = [
					'id'       => 'menu-bytao-blog',
					'icon'	   => 'fa-blog',
					'name'	   => $this->language->get('text_bytao_blog'),
					'href'     => '',
					'children' => $blog
				];*/
				$bytao[] = [
					'id'       => 'menu-bytao-tools',
					'icon'	   => 'fa-tags',
					'name'	   => $this->language->get('text_bytao_tools'),
					'href'     => '',
					'children' => $tools
				];
				
				$bytao[] = [
					'id'       => 'menu-bytao-tools',
					'icon'	   => 'fa-tags',
					'name'	   => $this->language->get('text_bytao_design'),
					'href'     => '',
					'children' => $design
				];
				
				
				if($bytao){
					$data['menus'][] = [
						'id'       => 'menu-bytao',
						'icon'	   => 'fa-solid fa-yin-yang',
						'name'	   => $this->language->get('text_bytao'),
						'href'     => '',
						'children' => $bytao
					];
				} 



				// Catalog
				$catalog = [];

				if ($this->user->hasPermission('access', 'catalog/category')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_category'),
						'href'     => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/product')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_product'),
						'href'     => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/subscription_plan')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_subscription_plan'),
						'href'     => $this->url->link('catalog/subscription_plan', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/filter')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_filter'),
						'href'     => $this->url->link('catalog/filter', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				// Attributes
				$attribute = [];

				if ($this->user->hasPermission('access', 'catalog/attribute')) {
					$attribute[] = [
						'name'     => $this->language->get('text_attribute'),
						'href'     => $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/attribute_group')) {
					$attribute[] = [
						'name'	   => $this->language->get('text_attribute_group'),
						'href'     => $this->url->link('catalog/attribute_group', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($attribute) {
					$catalog[] = [
						'name'	   => $this->language->get('text_attribute'),
						'href'     => '',
						'children' => $attribute
					];
				}

				if ($this->user->hasPermission('access', 'catalog/option')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_option'),
						'href'     => $this->url->link('catalog/option', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/manufacturer')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_manufacturer'),
						'href'     => $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/download')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_download'),
						'href'     => $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'catalog/review')) {
					$catalog[] = [
						'name'	   => $this->language->get('text_review'),
						'href'     => $this->url->link('catalog/review', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				

				if ($catalog) {
					$data['menus'][] = [
						'id'       => 'menu-catalog',
						'icon'	   => 'fas fa-tag',
						'name'	   => $this->language->get('text_catalog'),
						'href'     => '',
						'children' => $catalog
					];
				}

				$cms = [];

				if ($this->user->hasPermission('access', 'cms/topic')) {
					$cms[] = [
						'name'     => $this->language->get('text_topic'),
						'href'     => $this->url->link('cms/topic', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'cms/article')) {
					$cms[] = [
						'name'     => $this->language->get('text_article'),
						'href'     => $this->url->link('cms/article', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'cms/comment')) {
					$cms[] = [
						'name'     => $this->language->get('text_comment'),
						'href'     => $this->url->link('cms/comment', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'cms/antispam')) {
					$cms[] = [
						'name'     => $this->language->get('text_antispam'),
						'href'     => $this->url->link('cms/antispam', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($cms) {
					$data['menus'][] = [
						'id'       => 'menu-cms',
						'icon'     => 'fa-regular fa-newspaper',
						'name'     => $this->language->get('text_cms'),
						'href'     => '',
						'children' => $cms
					];
				}
			
				// Extension
				$marketplace = [];

				if ($this->user->hasPermission('access', 'marketplace/marketplace')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_marketplace'),
						'href'     => $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketplace/installer')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_installer'),
						'href'     => $this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketplace/extension')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_extension'),
						'href'     => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketplace/startup')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_startup'),
						'href'     => $this->url->link('marketplace/startup', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketplace/event')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_event'),
						'href'     => $this->url->link('marketplace/event', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketplace/cron')) {
					$marketplace[] = [
						'name'	   => $this->language->get('text_cron'),
						'href'     => $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($marketplace) {
					$data['menus'][] = [
						'id'       => 'menu-extension',
						'icon'	   => 'fas fa-puzzle-piece',
						'name'	   => $this->language->get('text_extension'),
						'href'     => '',
						'children' => $marketplace
					];
				}

				// Design
				$design = [];

				if ($this->user->hasPermission('access', 'design/layout')) {
					$design[] = [
						'name'	   => $this->language->get('text_layout'),
						'href'     => $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'design/theme')) {
					$design[] = [
						'name'	   => $this->language->get('text_theme'),
						'href'     => $this->url->link('design/theme', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'design/translation')) {
					$design[] = [
						'name'	   => $this->language->get('text_language_editor'),
						'href'     => $this->url->link('design/translation', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'design/banner')) {
					$design[] = [
						'name'	   => $this->language->get('text_banner'),
						'href'     => $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				$seo = [];

				if ($this->user->hasPermission('access', 'design/seo_url')) {
					$design[] = [
						'name'	   => $this->language->get('text_seo_url'),
						'href'     => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($design) {
					$data['menus'][] = [
						'id'       => 'menu-design',
						'icon'	   => 'fas fa-desktop',
						'name'	   => $this->language->get('text_design'),
						'href'     => '',
						'children' => $design
					];
				}

				// Sales
				$sale = [];

				if ($this->user->hasPermission('access', 'sale/order')) {
					$sale[] = [
						'name'	   => $this->language->get('text_order'),
						'href'     => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'sale/subscription')) {
					$sale[] = [
						'name'	   => $this->language->get('text_subscription'),
						'href'     => $this->url->link('sale/subscription', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'sale/returns')) {
					$sale[] = [
						'name'	   => $this->language->get('text_return'),
						'href'     => $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				// Voucher
				$voucher = [];

				if ($this->user->hasPermission('access', 'sale/voucher')) {
					$voucher[] = [
						'name'	   => $this->language->get('text_voucher'),
						'href'     => $this->url->link('sale/voucher', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'sale/voucher_theme')) {
					$voucher[] = [
						'name'	   => $this->language->get('text_voucher_theme'),
						'href'     => $this->url->link('sale/voucher_theme', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($voucher) {
					$sale[] = [
						'name'	   => $this->language->get('text_voucher'),
						'href'     => '',
						'children' => $voucher
					];
				}

				if ($sale) {
					$data['menus'][] = [
						'id'       => 'menu-sale',
						'icon'	   => 'fas fa-shopping-cart',
						'name'	   => $this->language->get('text_sale'),
						'href'     => '',
						'children' => $sale
					];
				}

				// Customer
				$customer = [];

				if ($this->user->hasPermission('access', 'customer/customer')) {
					$customer[] = [
						'name'	   => $this->language->get('text_customer'),
						'href'     => $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'customer/customer_group')) {
					$customer[] = [
						'name'	   => $this->language->get('text_customer_group'),
						'href'     => $this->url->link('customer/customer_group', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'customer/customer_approval')) {
					$customer[] = [
						'name'	   => $this->language->get('text_customer_approval'),
						'href'     => $this->url->link('customer/customer_approval', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'customer/gdpr')) {
					$customer[] = [
						'name'	   => $this->language->get('text_gdpr'),
						'href'     => $this->url->link('customer/gdpr', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'customer/custom_field')) {
					$customer[] = [
						'name'	   => $this->language->get('text_custom_field'),
						'href'     => $this->url->link('customer/custom_field', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($customer) {
					$data['menus'][] = [
						'id'       => 'menu-customer',
						'icon'	   => 'fas fa-user',
						'name'	   => $this->language->get('text_customer'),
						'href'     => '',
						'children' => $customer
					];
				}

				// Marketing
				$marketing = [];

				if ($this->user->hasPermission('access', 'marketing/affiliate')) {
					$marketing[] = [
						'name'	   => $this->language->get('text_affiliate'),
						'href'     => $this->url->link('marketing/affiliate', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketing/marketing')) {
					$marketing[] = [
						'name'	   => $this->language->get('text_marketing'),
						'href'     => $this->url->link('marketing/marketing', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketing/coupon')) {
					$marketing[] = [
						'name'	   => $this->language->get('text_coupon'),
						'href'     => $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'marketing/contact')) {
					$marketing[] = [
						'name'	   => $this->language->get('text_contact'),
						'href'     => $this->url->link('marketing/contact', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($marketing) {
					$data['menus'][] = [
						'id'       => 'menu-marketing',
						'icon'	   => 'fas fa-share-alt',
						'name'	   => $this->language->get('text_marketing'),
						'href'     => '',
						'children' => $marketing
					];
				}

				// System
				$system = [];

				if ($this->user->hasPermission('access', 'setting/setting')) {
					$system[] = [
						'name'	   => $this->language->get('text_setting'),
						'href'     => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				// Users
				$user = [];

				if ($this->user->hasPermission('access', 'user/user')) {
					$user[] = [
						'name'	   => $this->language->get('text_users'),
						'href'     => $this->url->link('user/user', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'user/user_permission')) {
					$user[] = [
						'name'	   => $this->language->get('text_user_group'),
						'href'     => $this->url->link('user/user_permission', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'user/api')) {
					$user[] = [
						'name'	   => $this->language->get('text_api'),
						'href'     => $this->url->link('user/api', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($user) {
					$system[] = [
						'name'	   => $this->language->get('text_users'),
						'href'     => '',
						'children' => $user
					];
				}

				// Localisation
				$localisation = [];

				if ($this->user->hasPermission('access', 'localisation/location')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_location'),
						'href'     => $this->url->link('localisation/location', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/language')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_language'),
						'href'     => $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/currency')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_currency'),
						'href'     => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/stock_status')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_stock_status'),
						'href'     => $this->url->link('localisation/stock_status', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/order_status')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_order_status'),
						'href'     => $this->url->link('localisation/order_status', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/subscription_status')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_subscription_status'),
						'href'     => $this->url->link('localisation/subscription_status', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				// Returns
				$returns = [];

				if ($this->user->hasPermission('access', 'localisation/return_status')) {
					$returns[] = [
						'name'	   => $this->language->get('text_return_status'),
						'href'     => $this->url->link('localisation/return_status', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/return_action')) {
					$returns[] = [
						'name'	   => $this->language->get('text_return_action'),
						'href'     => $this->url->link('localisation/return_action', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/return_reason')) {
					$returns[] = [
						'name'	   => $this->language->get('text_return_reason'),
						'href'     => $this->url->link('localisation/return_reason', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($returns) {
					$localisation[] = [
						'name'	   => $this->language->get('text_return'),
						'href'     => '',
						'children' => $returns
					];
				}

				if ($this->user->hasPermission('access', 'localisation/country')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_country'),
						'href'     => $this->url->link('localisation/country', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/zone')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_zone'),
						'href'     => $this->url->link('localisation/zone', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/geo_zone')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_geo_zone'),
						'href'     => $this->url->link('localisation/geo_zone', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				// Tax
				$tax = [];

				if ($this->user->hasPermission('access', 'localisation/tax_class')) {
					$tax[] = [
						'name'	   => $this->language->get('text_tax_class'),
						'href'     => $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/tax_rate')) {
					$tax[] = [
						'name'	   => $this->language->get('text_tax_rate'),
						'href'     => $this->url->link('localisation/tax_rate', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($tax) {
					$localisation[] = [
						'name'	   => $this->language->get('text_tax'),
						'href'     => '',
						'children' => $tax
					];
				}

				if ($this->user->hasPermission('access', 'localisation/length_class')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_length_class'),
						'href'     => $this->url->link('localisation/length_class', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/weight_class')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_weight_class'),
						'href'     => $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'localisation/address_format')) {
					$localisation[] = [
						'name'	   => $this->language->get('text_address_format'),
						'href'     => $this->url->link('localisation/address_format', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($localisation) {
					$system[] = [
						'name'	   => $this->language->get('text_localisation'),
						'href'     => '',
						'children' => $localisation
					];
				}

				// Tools
				$maintenance = [];

				if ($this->user->hasPermission('access', 'tool/upgrade')) {
					$maintenance[] = [
						'name'	   => $this->language->get('text_upgrade'),
						'href'     => $this->url->link('tool/upgrade', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'tool/backup')) {
					$maintenance[] = [
						'name'	   => $this->language->get('text_backup'),
						'href'     => $this->url->link('tool/backup', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'tool/upload')) {
					$maintenance[] = [
						'name'	   => $this->language->get('text_upload'),
						'href'     => $this->url->link('tool/upload', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'tool/log')) {
					$maintenance[] = [
						'name'	   => $this->language->get('text_log'),
						'href'     => $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($maintenance) {
					$system[] = [
						'name'	   => $this->language->get('text_maintenance'),
						'href'     => '',
						'children' => $maintenance
					];
				}

				if ($system) {
					$data['menus'][] = [
						'id'       => 'menu-system',
						'icon'	   => 'fas fa-cog',
						'name'	   => $this->language->get('text_system'),
						'href'     => '',
						'children' => $system
					];
				}

				$report = [];

				if ($this->user->hasPermission('access', 'report/report')) {
					$report[] = [
						'name'	   => $this->language->get('text_reports'),
						'href'     => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'report/online')) {
					$report[] = [
						'name'	   => $this->language->get('text_online'),
						'href'     => $this->url->link('report/online', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($this->user->hasPermission('access', 'report/statistics')) {
					$report[] = [
						'name'	   => $this->language->get('text_statistics'),
						'href'     => $this->url->link('report/statistics', 'user_token=' . $this->session->data['user_token']),
						'children' => []
					];
				}

				if ($report) {
					$data['menus'][] = [
						'id'       => 'menu-report',
						'icon'	   => 'fas fa-chart-bar',
						'name'	   => $this->language->get('text_reports'),
						'href'     => '',
						'children' => $report
					];
				}
			}
			// Stats
			if ($this->user->hasPermission('access', 'report/statistics')) {
				$this->load->model('sale/order');

				$order_total = (float)$this->model_sale_order->getTotalOrders();

				$this->load->model('report/statistics');

				$complete_total = (float)$this->model_report_statistics->getValue('order_complete');

				if ($complete_total && $order_total) {
					$data['complete_status'] = round(($complete_total / $order_total) * 100);
				} else {
					$data['complete_status'] = 0;
				}

				$processing_total = (float)$this->model_report_statistics->getValue('order_processing');

				if ($processing_total && $order_total) {
					$data['processing_status'] = round(($processing_total / $order_total) * 100);
				} else {
					$data['processing_status'] = 0;
				}

				$other_total = (float)$this->model_report_statistics->getValue('order_other');

				if ($other_total && $order_total) {
					$data['other_status'] = round(($other_total / $order_total) * 100);
				} else {
					$data['other_status'] = 0;
				}

				$data['statistics_status'] = true;
			} else {
				$data['statistics_status'] = false;
			}

			return $this->load->view('common/column_left', $data);
		} else {
			return '';
		}
	}
}
