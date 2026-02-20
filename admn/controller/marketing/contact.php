<?php
namespace Opencart\Admin\Controller\Marketing;
class Contact extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('marketing/contact');

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
		
		$data['ADM']= $this->user->getGroupId();
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/contact', 'user_token=' . $this->session->data['user_token'])
		];

		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();
		$data['store_id'] = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/contact', $data));
	}

	public function send(): void {
		$this->load->language('marketing/contact');

		$json = [];

		if (!$this->user->hasPermission('modify', 'marketing/contact')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['subject'] && !$this->request->post['order']) {
			$json['error']['subject'] = $this->language->get('error_subject');
		}

		if (!$this->request->post['message'] && !$this->request->post['order']) {
			$json['error']['message'] = $this->language->get('error_message');
		}

		if (!$json) {
			
			$this->load->model('setting/store');
			$this->load->model('setting/setting');
			$this->load->model('customer/customer');
			$this->load->model('sale/order');
			$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
			$store_info = $this->model_setting_store->getStore($store_id);
			
			if ($store_info) {
				$store_name = $store_info['name'];
			} else {
				$store_name = $this->config->get('config_name');
			}
			$setting = $this->model_setting_setting->getSetting('config', $store_id);
			$store_email = isset($setting['config_email']) ? $setting['config_email'] : $this->config->get('config_email');
			if (isset($this->request->get['page'])) {
				$page = (int)$this->request->get['page'];
			} else {
				$page = 1;
			}

			$limit = 10;
			$email_total = 0;
			$emails = [];

			switch ($this->request->post['to']) 
			{
				case 'newsletter':
					{
						$customer_data = [
							'filter_newsletter' => 1,
							'start'             => ($page - 1) * $limit,
							'limit'             => $limit
						];

						$email_total = $this->model_customer_customer->getTotalCustomers($customer_data);
						$results = $this->model_customer_customer->getCustomers($customer_data);
						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
					}
					break;
				case 'customer_all':
					{
						$customer_data = [
							'start' => ($page - 1) * $limit,
							'limit' => $limit
						];
						$email_total = $this->model_customer_customer->getTotalCustomers($customer_data);
						$results = $this->model_customer_customer->getCustomers($customer_data);
						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
					}
					break;
				case 'guest':
					{
						$customer_data = [
							'start' => ($page - 1) * $limit,
							'limit' => $limit
						];
						
						$results = explode(',',$this->request->post['guest']);
						$email_total = count($results);
						foreach ($results as $result) {
							$emails[] = $result;
						}
					}
					break;
				case 'customer_group':
					{
						$customer_data = [
							'filter_customer_group_id' => $this->request->post['customer_group_id'],
							'start'                    => ($page - 1) * $limit,
							'limit'                    => $limit
						];
						$email_total = $this->model_customer_customer->getTotalCustomers($customer_data);
						$results = $this->model_customer_customer->getCustomers($customer_data);
						foreach ($results as $result) {
							$emails[$result['customer_id']] = $result['email'];
						}
					}
					break;
				case 'customer':
					{
						if (!empty($this->request->post['customer'])) 
						{
							$email_total = count($this->request->post['customer']);
							$customers = array_slice($this->request->post['customer'], ($page - 1) * $limit, $limit);
							foreach ($customers as $customer_id) {
								$customer_info = $this->model_customer_customer->getCustomer($customer_id);
								if ($customer_info) {
									$emails[] = $customer_info['email'];
								}
							}
							if(!empty($this->request->post['order']) && count($customers)== 1 ){
								$order_id = (int)$this->request->post['order'];
								$order_info = $this->model_sale_order->getOrder($order_id);		
							}
						}
					}
					break;
				case 'affiliate_all':
					{
						$affiliate_data = [
							'filter_affiliate' => 1,
							'start'            => ($page - 1) * $limit,
							'limit'            => $limit
						];
						$email_total = $this->model_customer_customer->getTotalCustomers($affiliate_data);
						$results = $this->model_customer_customer->getCustomers($affiliate_data);
						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
					}
					break;
				case 'affiliate':
					{
						if (!empty($this->request->post['affiliate'])) 
						{
							$affiliates = array_slice($this->request->post['affiliate'], ($page - 1) * $limit, $limit);
							foreach ($affiliates as $affiliate_id) {
								$affiliate_info = $this->model_customer_customer->getCustomer($affiliate_id);
								if ($affiliate_info) {
									$emails[] = $affiliate_info['email'];
								}
							}
							$email_total = count($this->request->post['affiliate']);
						}
					}
					break;
				case 'product':
					{
						if (isset($this->request->post['product'])) {
							$email_total = $this->model_sale_order->getTotalEmailsByProductsOrdered($this->request->post['product']);
							$results = $this->model_sale_order->getEmailsByProductsOrdered($this->request->post['product'], ($page - 1) * $limit, $limit);
							foreach ($results as $result) {
								$emails[] = $result['email'];
							}
						}
					}
					break;
				case 'email':
					{
						if (isset($this->request->post['email'])) {
							$emails[] = $this->request->post['email'];
						}
					}
					break;
			}

			if ($emails) {
				
				$json['success'] = $this->language->get('text_success');

				$start = ($page - 1) * $limit;
				$end = $start + $limit;

				$json['success'] = sprintf($this->language->get('text_sent'), $start ? $start : 1, $email_total);

				if ($end < $email_total) {
					$json['next'] = $this->url->link('marketing/contact.send', 'user_token=' . $this->session->data['user_token'] . '&page=' . ($page + 1), true);
				} else {
					$json['next'] = '';
				}
				if(!isset($order_info)){
				        $this->load->model('setting/setting');

					
						$message  = '<html dir="ltr" lang="' . $this->language->get('code') . '">' . "\n";
						$message .= '  <head>' . "\n";
						$message .= '    <title>' . $this->request->post['subject'].date('h:i:s'). '</title>' . "\n";
						$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
						$message .= '  </head>' . "\n";
						//$message .= '  <body>' . html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
						$message .= '  <body>' . html_entity_decode($this->request->post['message'], ENT_NOQUOTES, 'UTF-8') . '</body>' . "\n";
						$message .= '</html>' . "\n";

						if ($this->model_setting_setting->getValue('config_mail_engine', $store_id)) {
							$mail_option = [
								'parameter'     => $this->model_setting_setting->getValue('config_mail_parameter', $store_id),
								'smtp_hostname' => $this->model_setting_setting->getValue('config_mail_smtp_hostname', $store_id),
								'smtp_username' => $this->model_setting_setting->getValue('config_mail_smtp_username', $store_id),
								'smtp_password' => html_entity_decode($this->model_setting_setting->getValue('config_mail_smtp_password', $store_id), ENT_QUOTES, 'UTF-8'),
								'smtp_port'     => $this->model_setting_setting->getValue('config_mail_smtp_port', $store_id),
								'smtp_timeout'  => $this->model_setting_setting->getValue('config_mail_smtp_timeout', $store_id)
							];
                            
                            $this->log->write(print_r($mail_option,true));
                            
							$mail = new \Opencart\System\Library\Mail($this->model_setting_setting->getValue('config_mail_engine', $store_id), $mail_option);

							foreach ($emails as $email) {
								if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
									$mail->setTo(trim($email));
									$mail->setFrom($store_email);
									$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
									$mail->setSubject(html_entity_decode($this->request->post['subject'].' - '.date('h:i:s'), ENT_QUOTES, 'UTF-8'));
									$mail->setHtml($message);
									$mail->send();
								}
							}
						}
				}
				else
				{
					
					$download_status = false;
					$order_products = $this->model_sale_order->getProducts($order_info['order_id']);

					foreach ($order_products as $order_product) {
						// Check if there are any linked downloads
						$product_download_query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_to_download` WHERE `product_id` = '" . (int)$order_product['product_id'] . "'");

						if ($product_download_query->row['total']) {
							$download_status = true;
						}
					}

					$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
					$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

					if (!defined('HTTP_CATALOG')) {
						$store_url = HTTP_SERVER;
					} else {
						$store_url = HTTP_CATALOG;
					}
					
					
					
					$this->load->model('setting/store');

					$store_info = $this->model_setting_store->getStore($order_info['store_id']);

					if ($store_info) {
						$this->load->model('setting/setting');

						$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
						$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
						$store_url = $store_info['url'];
					}

					$this->load->model('localisation/language');

					$language_info = $this->model_localisation_language->getLanguage($order_info['language_id']);

					if ($language_info) {
						$language_code = $language_info['code'];
					} else {
						$language_code = $this->config->get('config_language');
					}
					
					$data['language'] = $language_code; 
					
					$this->load->language($language_code, 'mail', $language_code);
					$this->load->language('mail/order_add', 'mail', $language_code);

					// Add language vars to the template folder
					$results = $this->language->all('mail');

					foreach ($results as $key => $value) {
						$data[$key] = $value;
					}

					$subject = sprintf($this->language->get('mail_text_subject'), $store_name, $order_info['order_key']);

					$this->load->model('tool/image');

					if (is_file(DIR_IMAGE . $store_logo)) {
						$data['logo'] = $store_url . 'image/' . $store_logo;
					} else {
						$data['logo'] = '';
					}

					$data['title'] = sprintf($this->language->get('mail_text_subject'), $store_name, $order_info['order_key']);

					$data['text_greeting'] = sprintf($this->language->get('mail_text_greeting'), $order_info['store_name']);

					$data['store'] = $store_name;
					$data['store_url'] = $order_info['store_url'];

					$data['customer_id'] = $order_info['customer_id'];
					
					if ($order_info['customer_id']) {
						$data['link'] = $order_info['store_url'] . 'index.php?route=account/order.info&order_id=' . $order_info['order_id'];
					} else {
						$data['link'] = $this->url->link('account/track','language=' . $this->config->get('config_language'));
					}

					if ($download_status) {
						$data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
					} else {
						$data['download'] = '';
					}

					$order_id  = $data['order_id'] = $order_info['order_id'];
					$order_key = $data['order_key'] = $order_info['order_key'];
					
					$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
					
					$data['payment_method'] = is_array($order_info['payment_method'])?$order_info['payment_method']['name']:$order_info['payment_method'];
					$data['shipping_method'] = is_array($order_info['shipping_method'])?$order_info['shipping_method']['name']:$order_info['shipping_method'];
					$data['email'] = $order_info['email'];
					$data['telephone'] = $order_info['telephone'];
					$data['ip'] = $order_info['ip'];

					$order_status_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_info['order_status_id'] . "' AND `language_id` = '" . (int)$order_info['language_id'] . "'");

					if ($order_status_query->num_rows) {
						$data['order_status'] = $order_status_query->row['name'];
					} else {
						$data['order_status'] = '';
					}
					
					$order_status = $data['order_status'];

					if (isset($comment)) {
						$data['comment'] = nl2br($comment);
					} else {
						$data['comment'] = '';
					}

					// Payment Address
					if ($order_info['payment_address_format']) {
						$format = $order_info['payment_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}

					$find = [
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{zone_code}',
						'{country}'
					];

					$replace = [
						'firstname' => $order_info['payment_firstname'],
						'lastname'  => $order_info['payment_lastname'],
						'company'   => $order_info['payment_company'],
						'address_1' => $order_info['payment_address_1'],
						'address_2' => $order_info['payment_address_2'],
						'city'      => $order_info['payment_city'],
						'postcode'  => $order_info['payment_postcode'],
						'zone'      => $order_info['payment_zone'],
						'zone_code' => $order_info['payment_zone_code'],
						'country'   => $order_info['payment_country']
					];

					$data['payment_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

					// Shipping Address
					if ($order_info['shipping_address_format']) {
						$format = $order_info['shipping_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}

					$find = [
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{zone_code}',
						'{country}'
					];

					$replace = [
						'firstname' => $order_info['shipping_firstname'],
						'lastname'  => $order_info['shipping_lastname'],
						'company'   => $order_info['shipping_company'],
						'address_1' => $order_info['shipping_address_1'],
						'address_2' => $order_info['shipping_address_2'],
						'city'      => $order_info['shipping_city'],
						'postcode'  => $order_info['shipping_postcode'],
						'zone'      => $order_info['shipping_zone'],
						'zone_code' => $order_info['shipping_zone_code'],
						'country'   => $order_info['shipping_country']
					];

					$data['shipping_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));

					$this->load->model('tool/upload');

					// Products
					$data['products'] = [];

					foreach ($order_products as $order_product) {
						$option_data = [];

						$order_options = $this->model_sale_order->getOptions($order_info['order_id'], $order_product['order_product_id']);
						
						$color_id=0;
						foreach ($order_options as $order_option) {
							if ($order_option['type'] != 'file') {
								$value = $order_option['value'];
								if($order_option['type']=='radio'){
									$color_id = $order_option['product_option_id'];
								}
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($order_option['value']);

								if ($upload_info) {
									$value = $upload_info['name'];
								} else {
									$value = '';
								}
							}

							$option_data[] = [
								'name'  => $order_option['name'],
								'value' => (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value)
							];
						}
						
						$description = '';
						
						$data['products'][] = [
							'thumb'         => $order_product['image'],
							'name'         => $order_product['name'],
							'model'        => $order_product['model'],
							'option'       => $option_data,
							'subscription' => $description,
							'quantity'     => $order_product['quantity'],
							'price'        => $this->currency->format($order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
							'fprice'        => $order_product['price'] + ($this->config->get('config_tax') ? $order_product['tax'] : 0),
							'total'        => $this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
							'ftotal'        => $order_product['total'] + ($this->config->get('config_tax') ? ($order_product['tax'] * $order_product['quantity']) : 0),
							'reward'       => $order_product['reward']
						];
					}

					// Vouchers
					$data['vouchers'] = [];

					$order_vouchers = $this->model_sale_order->getVouchers($order_info['order_id']);

					foreach ($order_vouchers as $order_voucher) {
						$data['vouchers'][] = [
							'description' => $order_voucher['description'],
							'amount'      => $this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
						];
					}

					// Order Totals
					$data['totals'] = [];

					$order_totals = $this->model_sale_order->getTotals($order_info['order_id']);

					foreach ($order_totals as $order_total) {
						$data['totals'][] = [
							'title' => $order_total['title'],
							'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
						];
					}

					$this->load->model('setting/setting');

					$from = $this->model_setting_setting->getValue('config_email', $order_info['store_id']);

					if (!$from) {
						$from = $this->config->get('config_email');
					}
					
					$text  = sprintf($this->language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
					$text .= $this->language->get('text_new_order_id') . ' ' . $order_key . "\n";
					$text .= $this->language->get('text_new_date_added') . ' ' . date($this->language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
					$text .= $this->language->get('text_new_order_status') . ' ' . $order_status . "\n\n";

					
					if (isset($comment)&& $comment && $notify) {
						$text .= $this->language->get('text_new_instruction') . "\n\n";
						$text .= $comment . "\n\n";
					}
					
					
					// Products
					$text .= $this->language->get('text_new_products') . "\n";

					foreach ($order_products as $product) {
							$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

							$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");

							foreach ($order_option_query->rows as $option) {
								if ($option['type'] != 'file') {
									$value = $option['value'];
								} else {
									$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

									if ($upload_info) {
										$value = $upload_info['name'];
									} else {
										$value = '';
									}
								}

								$text .= chr(9) . '-' . $option['name'] . ' ' . (oc_strlen($value) > 20 ? oc_substr($value, 0, 20) . '..' : $value) . "\n";
							}
						}

					foreach ($order_vouchers as $voucher) {
							$text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
						}

					$text .= "\n";

					$text .= $this->language->get('text_new_order_total') . "\n";

					foreach ($order_totals as $total) {
							$text .= $total['title'] . ': ' . html_entity_decode($this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";
						}

					$text .= "\n";

					if ($order_info['customer_id']) {
						$text .= $this->language->get('text_new_link') . "\n";
						$text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_key . "\n\n";
					}

					if ($download_status) {
						$text .= $this->language->get('text_new_download') . "\n";
						$text .= $order_info['store_url'] . 'index.php?route=account/download' . "\n\n";
					}

					// Comment
					if ($order_info['comment']) {
						$text .= $this->language->get('text_new_comment') . "\n\n";
						$text .= $order_info['comment'] . "\n\n";
					}

					$text .= $this->language->get('text_new_footer') . "\n\n";
					
					$view = $this->load->view('mail/order_invoice', $data);
					
					$eol = "\r\n";
					$boundary = '----=_NextPart_' . md5(time());
					
					$message = '--' . $boundary . $eol;
					$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $eol . $eol;
					$message .= '--' . $boundary . '_alt' . $eol;
					$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
					$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;

					/*if (!empty($this->option['text'])) {
						$message .= base64_encode($this->option['text']) . $eol;
					} else {*/
						$message .= base64_encode('This is a HTML email and your email client software does not support HTML email!') . $eol;
					//}

					$message .= '--' . $boundary . '_alt' . $eol;
					$message .= 'Content-Type: text/html; charset="utf-8"' . $eol;
					$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
					$message .= base64_encode($view) . $eol;
					$message .= '--' . $boundary . '_alt--' . $eol;
					
					/*
					$message  = '<html dir="ltr" lang="' . $data['language'] . '">' . "\n";
					$message .= '  <head>' . "\n";
					$message .= '    <title>' . $subject. '</title>' . "\n";
					$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
					$message .= '  </head>' . "\n";
					$message .= '  <body>' . html_entity_decode($view, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
					//$message .= '  <body>test deneme</body>' . "\n";
					$message .= '</html>' . "\n";
					*/
					
					if ($this->config->get('config_mail_engine')) {
							if (filter_var($emails[0], FILTER_VALIDATE_EMAIL)) {
									$email =$emails[0];
									$subject = html_entity_decode('ADM:'.$subject, ENT_QUOTES, 'UTF-8');
									$headers  = "From: $store_email\r\n"; 
    								$headers .= "Content-type: text/html\r\n";
									$result = mail($email, $subject, $view, $headers );
									
									if ( $result ) {
										$this->log->write('mail gitti:');
									} else {
										$this->log->write('Fail:'.print_r($result,TRUE));
									}
								}
								//$mail->setSubject('REP:'.$subject);
								//$mail->setTo('adamnygs@gmail.com');
								//$mail->send();
								
								
								
						}
				}
			
			} else {
				$json['error']['warning'] = $this->language->get('error_email');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function order(): void{
		$json = [];

		if (!$this->user->hasPermission('modify', 'marketing/contact')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->get['customer_id']) {
			$customer_id=$this->request->get['customer_id'];
			$filter_data = [
				'filter_customer_id'     => $customer_id,
			];

			$this->load->model('sale/order');

			$order_total = $this->model_sale_order->getTotalOrders($filter_data);

			$results = $this->model_sale_order->getOrders($filter_data);

			foreach ($results as $result) {
				$json['orders'][] = [
					'order_id'        => $result['order_id'],
					'order_key'       => $result['order_key'],
					'total'           => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
					'date_added'      	=> date($this->language->get('datetime_format'), strtotime($result['date_added']))
					
				];
			}
		
		
		}

		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
