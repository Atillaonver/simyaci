<?php
namespace Opencart\Catalog\Model\Bytao;
class Pdf extends \Opencart\System\Engine\Model {
	
	public function getPdfToken(int $order_id): string
	{
		$query = $this->db->query("SELECT token FROM `" . DB_PREFIX . "order_pdf` WHERE order_id = '" . (int)$order_id . "' LIMIT 1");
		return $query->num_rows ? $query->row['token'] : '';
	}

	public function getOrderIdByToken(string $token): string
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_pdf` WHERE token = '" . $this->db->escape($token) . "' LIMIT 1");
		return $query->num_rows ? $query->row['order_id'] : '';
	}

	public function createPdfToken(int $order_id, string $type = 'contract'): string
	{
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_pdf` WHERE order_id = '" . (int)$order_id . "' AND type = '" . $this->db->escape($type) . "'");
		
		$token = bin2hex(random_bytes(16)); // 32 char
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_pdf` SET order_id = '" . (int)$order_id . "', token = '" . $this->db->escape($token) . "',type = '" . $this->db->escape($type) . "', date_added = NOW()");
		return $token;
	}

	public function isValidPdfToken(int $order_id, string $token): bool
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_pdf` WHERE order_id = '" . (int)$order_id . "' AND token = '" . $this->db->escape($token) . "' LIMIT 1 ");
		return (bool)$query->num_rows;
	}

	public function createContract($info = [])
	{
		$this->load->model('catalog/information');
		$data=[];
		$order_id = isset($info['order_id'])?$info['order_id']:0;
		$token = $this->getPdfToken($order_id);

		$file = DIR_PDF . "contracts/satis-sozlesmesi-".$order_id."-".$token.".pdf";
		if (file_exists($file)) {
			unlink($file);
		}

		$information_id = $this->config->get('config_checkout_id');
		$information_info = $this->model_catalog_information->getInformation($information_id);
		if ($information_info && $order_id ) {

			$this->registry->set('pdf', new \Opencart\System\Library\Pdf($this->registry));

			$this->load->language('bytao/pdf');
			$this->load->model('setting/setting');
			$this->load->model('bytao/common');
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);
			$module_pdf_invoice = $this->model_setting_setting->getSetting("module_pdf_invoice");
			$languages = $this->model_bytao_common->getStoreLanguages();
			$data['store_id'] = (int)$this->config->get('config_store_id') ;
			$data['store'] = $this->model_setting_setting->getSetting("config", $data['store_id']);
			
			$data['config'] = $module_pdf_invoice;
			$data['language_id'] = $this->config->get('config_language_id');
			
			if (isset($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$data['config']['text_align'] = 'right';
			} else {
				$data['config']['text_align'] = 'left';
			}
			$data['heading_title'] = $information_info['title'];
			$language = [];

			$language['a_meta_charset'] = 'UTF-8';
			
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}
			
			$this->load->language('default', 'mail', $language_code);
			$this->load->language('mail/order_add', 'mail', $language_code);

			// Add language vars to the template folder
			$results = $this->language->all('mail');

			foreach ($results as $key => $value) {
				$data[$key] = $value;
			}

			$data['lang'] = $this->language->get('code');
			$data['direction'] = $this->language->get('direction');
			
			$description = explode('|MUSTERI|',$information_info['description']);
			$buyer = $order_info['firstname'] . ' ' . $order_info['lastname'];
			$content2= implode($buyer,$description);
			
			$description6 = explode('|TELEPHON|',$content2);
			$buyer = $order_info['telephone'];
			$content6= implode($buyer,$description6);
			
			$description7 = explode('|EMAIL|',$content6);
			$buyer = $order_info['email'];
			$content7= implode($buyer,$description7);
			

			$description2 = explode('|TARIH|',$content7);
			$date_view = date('d.m.Y', strtotime($order_info['date_added']));
			$content3 = implode($date_view,$description2);
			
			$description3 = explode('|URUNLER|',$content3);
			
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$data['payment_method'] = $order_info['payment_method']['name'];
			$data['shipping_method'] = isset($order_info['shipping_method']['name'])?$order_info['shipping_method']['name']:'';
			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];
			$data['order_id'] = $order_info['order_id'];
			
			// Payment Address
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}' . "\n" . '{zone}' . "\n" . '{country}';
			$find = [
				'{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{telephone}','{zone}','{zone_code}','{country}'];
			$replace = [
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'telephone' => $order_info['telephone'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			];
			$data['payment_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));
			$data['payment'] = $replace;

			// Shipping Address
			$format = '{firstname} {lastname}' . "\n" . '{telephone}' ."\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			$find = ['{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{telephone}','{zone}','{zone_code}','{country}'];
			$replace = [
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'telephone' => $order_info['shipping_telephone'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			];
			$data['shipping_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));
			$data['shipping'] = $replace;
			
			
			$data['products'] = [];
			$order_products = $this->model_checkout_order->getProducts($order_id);
			foreach ($order_products as $order_product) {
				$option_data = [];

				$order_options = $this->model_checkout_order->getOptions($order_info['order_id'], $order_product['order_product_id']);

				foreach ($order_options as $order_option) {
					if ($order_option['type'] != 'file') {
						$value = $order_option['value'];
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

				$this->load->model('checkout/subscription');

				$subscription_info = $this->model_checkout_order->getSubscription($order_info['order_id'], $order_product['order_product_id']);

				if ($subscription_info) {
					if ($subscription_info['trial_status']) {
						$trial_price = $this->currency->format($subscription_info['trial_price'] + ($this->config->get('config_tax') ? $subscription_info['trial_tax'] : 0), $this->session->data['currency']);
						$trial_cycle = $subscription_info['trial_cycle'];
						$trial_frequency = $this->language->get('text_' . $subscription_info['trial_frequency']);
						$trial_duration = $subscription_info['trial_duration'];

						$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
					}

					$price = $this->currency->format($subscription_info['price'] + ($this->config->get('config_tax') ? $subscription_info['tax'] : 0), $this->session->data['currency']);
					$cycle = $subscription_info['cycle'];
					$frequency = $this->language->get('text_' . $subscription_info['frequency']);
					$duration = $subscription_info['duration'];

					if ($duration) {
						$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
					} else {
						$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
					}
				}

				$data['products'][] = [
					'name'         => $order_product['name'],
					'model'        => $order_product['model'],
					'quantity'     => $order_product['quantity'],
					'option'       => $option_data,
					'subscription' => $description,
					'total'        => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? $order_product['tax'] * $order_product['quantity'] : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['vouchers'] = [];
			$order_vouchers = $this->model_checkout_order->getVouchers($order_id);
			foreach ($order_vouchers as $order_voucher) {
				$data['vouchers'][] = [
					'description' => $order_voucher['description'],
					'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['totals'] = [];
			$order_totals = $this->model_checkout_order->getTotals($order_id);
			foreach ($order_totals as $order_total) {
				$data['totals'][$order_total['code']] = [
					'title' => $order_total['title'],
					'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}
			
			$view_products =$this->load->view('bytao/pdf/order_products', $data);
			$content4= implode($view_products,$description3);
			
			
			$description4 = explode('|ALICI|',$content4);
			$view_buyer =$this->load->view('bytao/pdf/order_buyer', $data);
			$content5 = implode($view_buyer,$description4);
			
			$description44 = explode('|ADRES|',$content5);
			$view_buyer =$this->load->view('bytao/pdf/order_address', $data['payment']);
			$content55 = implode($view_buyer,$description44);
			
			$data['description'] = html_entity_decode($content55, ENT_QUOTES, 'UTF-8');
			if (!$token){
				$token = $this->createPdfToken($order_id);
			}
			
			
			$filename = "satis-sozlesmesi-{$order_id}-{$token}";
			$view_content = $this->load->view('bytao/pdf/information_info', $data);

			$this->pdf->tcpdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			$this->pdf->tcpdf->setLanguageArray($language);
			$this->pdf->data = $data;
			$this->pdf->data['html'] = $view_content;
			$this->pdf->Draw();

			$dir = DIR_PDF.'contracts/';
			if (!is_dir($dir) || !is_writable($dir)) {
				mkdir($dir, 0777, true);
			}
			if (!is_dir($dir)) {
				trigger_error('Permissions Error: couldn\'t create directory \'Information\' at: ' . $dir);
				return false;
			}

			if (file_exists($dir.$filename . '.pdf')) {
				unlink($dir.$filename . '.pdf');
			}

			if (ob_get_length()) {
				ob_end_clean();
			}
			$this->pdf->Output($dir.$filename . '.pdf', 'F');

			return false;
		} 
	}
	
	// calisan
	public function getContract($info = [], bool $create_file = false)
	{
		
		$this->load->model('catalog/information');
		$data=[];
		$order_id = isset($info['order_id'])?$info['order_id']:0;
		$token = $this->getPdfToken($order_id);
		$file = DIR_PDF . "contracts/satis-sozlesmesi-".$order_id."-".$token.".pdf";
		if (file_exists($file)) {
			//$this->log->write('is_file:'.print_r($file,TRUE));
			//unlink($file);
		}
		
		$information_id = $this->config->get('config_checkout_id');
		
		$information_info = $this->model_catalog_information->getInformation($information_id);
		if ($information_info && $order_id ) {
			
			$this->registry->set('pdf', new \Opencart\System\Library\Pdf($this->registry));
			
			$this->load->language('bytao/pdf');
			$this->load->model('setting/setting');
			$this->load->model('bytao/common');
			$this->load->model('checkout/order');
			
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$module_pdf_invoice = $this->model_setting_setting->getSetting("module_pdf_invoice");
			$languages = $this->model_bytao_common->getStoreLanguages();
			$data['store_id'] = (int)$this->config->get('config_store_id') ;
			$data['store'] = $this->model_setting_setting->getSetting("config", $data['store_id']);
			
			$data['config'] = $module_pdf_invoice;
			$data['language_id'] = $this->config->get('config_language_id');
			
			if (isset($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$data['config']['text_align'] = 'right';
			} else {
				$data['config']['text_align'] = 'left';
			}
			$data['heading_title'] = $information_info['title'];
			$language = [];

			$language['a_meta_charset'] = 'UTF-8';
			
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}
			
			$this->load->language('default', 'mail', $language_code);
			$this->load->language('mail/order_add', 'mail', $language_code);

			// Add language vars to the template folder
			$results = $this->language->all('mail');

			foreach ($results as $key => $value) {
				$data[$key] = $value;
			}

			$data['lang'] = $this->language->get('code');
			$data['direction'] = $this->language->get('direction');
			
			$description = explode('|MUSTERI|',$information_info['description']);
			$buyer = $order_info['firstname'] . ' ' . $order_info['lastname'];
			$content2= implode($buyer,$description);
			
			$description6 = explode('|TELEPHON|',$content2);
			$buyer = $order_info['telephone'];
			$content6= implode($buyer,$description6);
			
			$description7 = explode('|EMAIL|',$content6);
			$buyer = $order_info['email'];
			$content7= implode($buyer,$description7);
			

			$description2 = explode('|TARIH|',$content7);
			$date_view = date('d.m.Y', strtotime($order_info['date_added']));
			$content3 = implode($date_view,$description2);
			
			$description3 = explode('|URUNLER|',$content3);
			
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$data['payment_method'] = $order_info['payment_method']['name'];
			$data['shipping_method'] = isset($order_info['shipping_method']['name'])?$order_info['shipping_method']['name']:'';
			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];
			$data['order_id'] = $order_info['order_id'];
			
			// Payment Address
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{postcode} {city}' . "\n" . '{zone}' . "\n" . '{country}';
			$find = [
				'{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{telephone}','{zone}','{zone_code}','{country}'];
			$replace = [
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'telephone' => $order_info['telephone'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			];
			$data['payment_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));
			$data['payment'] = $replace;

			// Shipping Address
			$format = '{firstname} {lastname}' . "\n" . '{telephone}' ."\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			$find = ['{firstname}','{lastname}','{company}','{address_1}','{address_2}','{city}','{postcode}','{telephone}','{zone}','{zone_code}','{country}'];
			$replace = [
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'telephone' => isset($order_info['shipping_telephone'])?$order_info['shipping_telephone']:'',
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			];
			$data['shipping_address'] = str_replace(["\r\n", "\r", "\n"], '<br/>', preg_replace(["/\s\s+/", "/\r\r+/", "/\n\n+/"], '<br/>', trim(str_replace($find, $replace, $format))));
			$data['shipping'] = $replace;
			
			
			$data['products'] = [];
			$order_products = $this->model_checkout_order->getProducts($order_id);
			foreach ($order_products as $order_product) {
				$option_data = [];

				$order_options = $this->model_checkout_order->getOptions($order_info['order_id'], $order_product['order_product_id']);

				foreach ($order_options as $order_option) {
					if ($order_option['type'] != 'file') {
						$value = $order_option['value'];
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

				$this->load->model('checkout/subscription');

				$subscription_info = $this->model_checkout_order->getSubscription($order_info['order_id'], $order_product['order_product_id']);

				if ($subscription_info) {
					if ($subscription_info['trial_status']) {
						$trial_price = $this->currency->format($subscription_info['trial_price'] + ($this->config->get('config_tax') ? $subscription_info['trial_tax'] : 0), $this->session->data['currency']);
						$trial_cycle = $subscription_info['trial_cycle'];
						$trial_frequency = $this->language->get('text_' . $subscription_info['trial_frequency']);
						$trial_duration = $subscription_info['trial_duration'];

						$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
					}

					$price = $this->currency->format($subscription_info['price'] + ($this->config->get('config_tax') ? $subscription_info['tax'] : 0), $this->session->data['currency']);
					$cycle = $subscription_info['cycle'];
					$frequency = $this->language->get('text_' . $subscription_info['frequency']);
					$duration = $subscription_info['duration'];

					if ($duration) {
						$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
					} else {
						$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
					}
				}

				$data['products'][] = [
					'name'         => $order_product['name'],
					'model'        => $order_product['model'],
					'quantity'     => $order_product['quantity'],
					'option'       => $option_data,
					'subscription' => $description,
					'total'        => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? $order_product['tax'] * $order_product['quantity'] : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['vouchers'] = [];
			$order_vouchers = $this->model_checkout_order->getVouchers($order_id);
			foreach ($order_vouchers as $order_voucher) {
				$data['vouchers'][] = [
					'description' => $order_voucher['description'],
					'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['totals'] = [];
			$order_totals = $this->model_checkout_order->getTotals($order_id);
			foreach ($order_totals as $order_total) {
				$data['totals'][$order_total['code']] = [
					'title' => $order_total['title'],
					'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}
						
			$view_products =$this->load->view('bytao/pdf/order_products', $data);
			$content4= implode($view_products,$description3);
			
			
			$description4 = explode('|ALICI|',$content4);
			$view_buyer =$this->load->view('bytao/pdf/order_buyer', $data);
			$content5 = implode($view_buyer,$description4);
			
			$description44 = explode('|ADRES|',$content5);
			$view_buyer =$this->load->view('bytao/pdf/order_address', $data['payment']);
			$content55 = implode($view_buyer,$description44);
			
			$data['description'] = html_entity_decode($content55, ENT_QUOTES, 'UTF-8');
			if (!$token){
				$token = $this->createPdfToken($order_id);
			}
			
			
			$filename = "satis-sozlesmesi-{$order_id}-{$token}";
			$view_content = $this->load->view('bytao/pdf/information_info', $data);
			
			$this->pdf->tcpdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			$this->pdf->tcpdf->setLanguageArray($language);
			$this->pdf->data = $data;
			$this->pdf->data['html'] = $view_content;
			$this->pdf->Draw();
			
			
			
			
			$dir = DIR_PDF.'contracts/';
			
			if ($create_file) {
				
				if (!is_dir($dir) || !is_writable($dir)) {
					mkdir($dir, 0777, true);
				}
				if (!is_dir($dir)) {
					trigger_error('Permissions Error: couldn\'t create directory \'Information\' at: ' . $dir);
					return false;
				}

				if (file_exists($dir.$filename . '.pdf')) {
					unlink($dir.$filename . '.pdf');
				}
				
				if (ob_get_length()) {
				ob_end_clean();
				}
				$this->pdf->Output($dir.$filename . '.pdf', 'F');
				
				return false;
				
			} else {
				
				if (ob_get_length()) {
					ob_end_clean();
				}
				$this->pdf->Output($filename . '.pdf', 'I');
				return false;
			}
		}
		return false;
	}
	
	public function getContractLink(int $order_id)
	{
		$token = $this->getPdfToken($order_id);
		return $this->url->link('bytao/pdf.download','token=' . $token,true);
	}
	
	public function getFaqQuestions() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq f LEFT JOIN " . DB_PREFIX . "faq_description fd ON (f.faq_id = fd.faq_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.status = '1' AND f.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY f.sort_order ASC");

		return $query->rows;
	}
	
	public function getForms($forms_id){
		$filename = 'forms-'.$forms_id.'-'.$this->customer->getId() ;
		
		if($forms_id){
			$this->load->language('bytao/forms');
			
			$PDF = new \Opencart\System\Library\pdf($this->registry);
			$this->load->model('bytao/common');
			$this->load->model('bytao/forms');
			$this->load->model('bytao/forms');

			$forms_info = $this->model_bytao_forms->getForms($forms_id);
			$formData = $this->model_bytao_forms->getFormsData($forms_id);
			$data['dateAdded'] =date("F j, Y", strtotime($formData['date_added']));
			$data['title'] = html_entity_decode($forms_info['title'], ENT_QUOTES, 'UTF-8');
			$data['description'] = html_entity_decode($forms_info['description'], ENT_QUOTES, 'UTF-8');
			$data['formData'] = unserialize($forms_info['formdata']);
			$formValue = unserialize($formData['form_data']);
			
			$data['formValue']=[];
			foreach($formValue as $key => $value){
				$data['formValue'][$key]=$value;
			}
			
			$data['language_id'] = $this->config->get('config_language_id');
			$data['store_id'] = $this->config->get('config_store_id');
			
			$data['config']['text_align'] = 'left';
			
			$data['store'] = $this->model_setting_setting->getSetting("config", $this->config->get('config_store_id'));

			unset($data['store']['config_robots']);

            $logo_width = 100;
            $logo_height = 100;


            if (isset($data['config']['module_pdf_invoice_logo'])) {
                $data['store']['config_logo'] = $this->_resize($data['config']['module_pdf_invoice_logo'], $logo_width, $logo_height);
            } elseif ($this->config->get('config_logo') && $logo_width && $logo_height) {
                $data['store']['config_logo'] = $this->_resize($this->config->get('config_logo'), $logo_width, $logo_height);
            } else {
                $data['store']['config_logo'] = false;
            }

			if ($data['store']['config_address']) {
				$data['store']['config_address'] = nl2br($data['store']['config_address']);
			}
			
			$language=[];
			
			$language['a_meta_charset'] = 'UTF-8';

			$data = array_merge($data, $language);

			$PDF->tcpdf->setLanguageArray($language);
			$PDF->tcpdf->setLanguageArray($language);
			$PDF->data = $data;

			$template_filename = 'bytao/pdf/forms';

			if (!empty($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$template_filename .= '_rtl';
			}
			$view =$this->load->view($template_filename, $data);
			$PDF->data['html'] = $view;

			$PDF->Draw();

			if (ob_get_length()) ob_end_clean();
		}
		
		
		
		
		if (empty($PDF->data)) {
			return false;
		}
		
		
		$dir = DIR_CACHE . 'forms/';
		if (!is_dir($dir) || !is_writable($dir)) {
			mkdir($dir, 0777, true);
		}
		if (!is_dir($dir)) {
			trigger_error('Permissions Error: couldn\'t create directory \'forms\' at: ' . $dir);
			return false;
		}

		if (file_exists($dir.$filename . '.pdf')) {
			unlink($dir.$filename . '.pdf');
		}

		$PDF->Output($dir.$filename . '.pdf', 'F');
		$this->request->get['pdf'] = $dir.$filename . '.pdf';
		return $dir.$filename . '.pdf';
			
	}
	
	public function getInvoice($orders, $create_file = false) {
		if (!$this->config->get('module_pdf_invoice_status')) {
			return false;
		}

        if (!is_array($orders)) {
            $orders = array($orders);
        }

		//$this->load->library('pdf');

		$this->load->model('setting/setting');
		$this->load->model('bytao/common');
		$this->load->model('localisation/order_status');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('account/order');
		$this->load->model('account/customer');

		$module_pdf_invoice = $this->model_setting_setting->getSetting("module_pdf_invoice");

		$languages = $this->model_bytao_common->getStoreLanguages();

		$filename = 'order';

		$orders_iteration = 0;

		foreach($orders as $order) {
			if (is_numeric($order)) {
				$order_info = $this->model_account_order->getOrder($order);
			} else {
				$order_info = $order;
			}

			if (!$order_info || (!isset($this->request->post['attach_invoice_pdf']) && ($this->config->get('module_pdf_invoice_order_complete') && !in_array($order_info['order_status_id'], $this->config->get('config_complete_status'))))) {
				continue;
			}

			// Missing order data
			$order_missing_query = $this->db->query("SELECT customer_group_id, custom_field, shipping_custom_field, payment_custom_field FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_info['order_id'] . "'");

			$filename .= '_' . $order_info['order_id'];

			$data = [];

			$data['config'] = $module_pdf_invoice;

			$data['order'] = $order_info;

			$data['orders'] = count($orders);

			$orders_iteration++;
			$data['orders_iteration'] = $orders_iteration;

			$data['language_id'] = ($order_info['language_id']) ? $order_info['language_id'] : $this->config->get('config_language_id');

			$data['store_id'] = ($order_info['store_id']) ? $order_info['store_id'] : 0;

			foreach ($languages as $language) {
				if ($language['language_id'] == $data['language_id']) {
					$oLanguage = new Language($language['code']);

					$oLanguage->load($language['code']);
					$oLanguage->load('account/order');
					$oLanguage->load('extension/module/pdf_invoice');

					continue;
				}
			}

			if (!isset($oLanguage)) {
				trigger_error("Error: unable to find language = '{$data['language_id']}'");
				return false;
			}

			// Customer
			$customer_info = $this->model_account_customer->getCustomer($data['order']['customer_id']);

			if ($customer_info) {
				$data['customer'] = $customer_info;

				// Customer address merge
				if (empty($data['order']['payment_address_1'])) {
					$condition = "customer_id = '" . (int)$data['order']['customer_id'] . "'";

					if ($customer_info['address_id']) {
						$condition .= " AND address_id = '" . (int)$customer_info['address_id'] . "'";
					}

					$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE " . $condition . " LIMIT 1");

					if ($address_query->num_rows) {
						$vars = [
							'firstname',
							'lastname',
							'company',
							'address_1',
							'address_2',
							'city',
							'postcode',
							'zone',
							'zone_code',
							'country'
						];
						foreach ($vars as $var) {
							$data['order']['payment_' . $var] = isset($address_query->row[$var]) ? $address_query->row[$var] : '';
							$data['order']['shipping_' . $var] = isset($address_query->row[$var]) ? $address_query->row[$var] : '';
						}
					}
				}
			}

			$data['order']['shipping_method'] = strip_tags($data['order']['shipping_method']);
			$data['order']['payment_method'] = strip_tags($data['order']['payment_method']);

			$data['order']['date_added'] = date($this->language->get('date_format_short'), strtotime($data['order']['date_added']));

			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order']['order_status'] = $order_status_info['name'];
			} else {
				$data['order']['order_status'] = '';
			}

			$data['order']['totals'] = [];

			$totals = $this->model_account_order->getOrderTotals($order_info['order_id']);

			if ($totals) {
				foreach ($totals as $total) {
					$data['order']['totals'][] = array(
						'title' => $total['title'],
						'text' => (!empty($total['text']) ? $total['text'] : $this->currency->format($total['value'], $data['order']['currency_code'], $data['order']['currency_value'])),
					);
				}
			}

			if (isset($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$data['config']['text_align'] = 'right';
			} else {
				$data['config']['text_align'] = 'left';
			}

			$data['store'] = $this->model_setting_setting->getSetting("config", $data['store_id']);

			unset($data['store']['config_robots']);

            $logo_width = isset($data['config']['module_pdf_invoice_logo_width']) ? $data['config']['module_pdf_invoice_logo_width'] : 200;
            $logo_height = isset($data['config']['module_pdf_invoice_logo_height']) ? $data['config']['module_pdf_invoice_logo_height'] : 60;

            if ($data['config']['module_pdf_invoice_logo']) {
                $data['store']['config_logo'] = $this->_resize($data['config']['module_pdf_invoice_logo'], $logo_width, $logo_height);
            } elseif ($this->config->get('config_logo') && $logo_width && $logo_height) {
                $data['store']['config_logo'] = $this->_resize($this->config->get('config_logo'), $logo_width, $logo_height);
            } else {
                $data['store']['config_logo'] = false;
            }

			if ($data['store']['config_address']) {
				$data['store']['config_address'] = nl2br($data['store']['config_address']);
			}

			// Custom fields
			if ($order_missing_query->row) {
				$this->load->model('account/custom_field');

				$customer_group_id = $order_missing_query->row['customer_group_id'];
				$order_custom_field = json_decode($order_missing_query->row['custom_field'], true);
				$shipping_custom_field = json_decode($order_missing_query->row['shipping_custom_field'], true);
				$payment_custom_field = json_decode($order_missing_query->row['payment_custom_field'], true);

				$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

				if ($custom_fields) {
					foreach ($custom_fields as $custom_field) {
						if (!empty($order_custom_field[$custom_field['custom_field_id']])) {
							$data['order']['custom_field'][$custom_field['custom_field_id']] = array(
								'name'  => $custom_field['name'],
								'value' => $order_custom_field[$custom_field['custom_field_id']]
							);
						}

						if (isset($shipping_custom_field[$custom_field['custom_field_id']])) {
							$data['order']['shipping_custom_field'][$custom_field['custom_field_id']] = array(
								'name'  => $custom_field['name'],
								'value' => $shipping_custom_field[$custom_field['custom_field_id']]
							);
						}

						if (isset($payment_custom_field[$custom_field['custom_field_id']])) {
							$data['order']['payment_custom_field'][$custom_field['custom_field_id']] = array(
								'name'  => $custom_field['name'],
								'value' => $payment_custom_field[$custom_field['custom_field_id']]
							);
						}
					}
				}
			}

			$data['order']['shipping_address'] = $this->formatAddress($data['order'], 'shipping', $data['order']['shipping_address_format']);
			$data['order']['payment_address'] = $this->formatAddress($data['order'], 'payment', $data['order']['payment_address_format']);

			$data['order']['products'] = [];

			$products = $this->model_account_order->getOrderProducts($order_info['order_id']);

			if ($products) {
				foreach ($products as $product) {
					$product_data = $this->model_catalog_product->getProduct($product['product_id']);

					$option_data = [];
					$options = $this->model_account_order->getOrderOptions($order_info['order_id'], $product['order_product_id']);
					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						}
						$option_data[] = array(
							'name' => $option['name'],
							'value' => $value
						);
					}

					$option_string = '';
					if (count($option_data) > 0) {
						foreach ($option_data as $value) {
							$option_string .= '<br />' . $value['name'] . ': ' . $value['value'];
						}
					}

                    $image = false;
                    if (!empty($data['config']['module_pdf_invoice_order_image']) && !empty($product_data['image'])) {
                        $image_width = isset($data['config']['module_pdf_invoice_order_image_width']) ? $data['config']['module_pdf_invoice_order_image_width'] : 200;
                        $image_height = isset($data['config']['module_pdf_invoice_order_image_height']) ? $data['config']['module_pdf_invoice_order_image_height'] : 200;
                        $image = $this->_resize($product_data['image'], $image_width, $image_height);
                    }

					if (!empty($data['config']['module_pdf_invoice_barcode']) && !empty($product_data['sku'])) {
						$params = $this->pdf_invoice->tcpdf->serializeTCPDFtagParameters(array($product_data['sku'], 'C128B', '', '', 0, 0, 0.2, array('position' => 'S', 'stretch' => true, 'fitwidth' => true, 'cellfitalign' => 'C', 'position' => 'C', 'align' => 'C', 'border' => false, 'padding' => 2, 'fgcolor' => array(0, 0, 0), 'bgcolor' => array(255, 255, 255), 'text' => true), 'N'));

						$barcode = '<div><tcpdf method="write1DBarcode" params="'.$params. '" /></div>';
					} else {
						$barcode = false;
					}

					$data['order']['products'][] = array_merge($product_data, array(
						'name' => '<b>' . $product['name'] . '</b>',
						'model' => $product['model'],
						'sku' => isset($product_data['sku']) ? $product_data['sku'] : '',
						'option' => $option_string,
						'image' => $image,
						'barcode' => $barcode,
						'quantity' => $product['quantity'],
						'url' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
						'price' => $this->currency->format($product['price'], $data['order']['currency_code'], $data['order']['currency_value']),
						'total' => $this->currency->format($product['total'], $data['order']['currency_code'], $data['order']['currency_value']),
                        'price_with_vat' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $data['order']['currency_code'], $data['order']['currency_value']),
                        'total_with_vat' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $data['order']['currency_code'], $data['order']['currency_value'])
					));
				}
			}

			// Order - Vouchers
			$data['order']['vouchers'] = [];

			$vouchers = $this->model_account_order->getOrderVouchers($order_info['order_id']);

			if ($vouchers) {
				foreach ($vouchers as $voucher) {
					$data['order']['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount' => $this->currency->format($voucher['amount'], $data['order']['currency_code'], $data['order']['currency_value'])
					);
				}
			}

			$language = [];

			$language['a_meta_charset'] = 'UTF-8';

			$language['text_date_added'] = $oLanguage->get('text_date_added');
			$language['text_order_id'] = $oLanguage->get('text_order_id');
			$language['text_order_status'] = $oLanguage->get('text_order_status');
			$language['text_invoice_no'] = $oLanguage->get('text_invoice_no');
			$language['text_shipping_method'] = $oLanguage->get('text_shipping_method');
			$language['text_shipping_address'] = $oLanguage->get('text_shipping_address');
			$language['text_payment_method'] = $oLanguage->get('text_payment_method');
			$language['text_payment_address'] = $oLanguage->get('text_payment_address');

			$language['column_total'] = $oLanguage->get('column_total');
			$language['column_product'] = $oLanguage->get('column_product');
			$language['column_model'] = $oLanguage->get('column_model');
			$language['column_quantity'] = $oLanguage->get('column_quantity');
			$language['column_price'] = $oLanguage->get('column_price');

			$data = array_merge($data, $language);

			$this->pdf->tcpdf->setLanguageArray($language);

			$this->pdf->data = $data;

			$template_filename = 'bytao/pdf/invoice';

			if (!empty($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$template_filename .= '_rtl';
			}

			$this->pdf->data['html'] = $this->load->view($template_filename, $data);

			$this->pdf->Draw();

			if (ob_get_length()) ob_end_clean();
		}

		if (empty($this->pdf->data)) {
			return false;
		}

		if ($create_file) {
			$dir = DIR_CACHE . 'invoices/';
			if (!is_dir($dir) || !is_writable($dir)) {
				mkdir($dir, 0777, true);
			}
			if (!is_dir($dir)) {
				trigger_error('Permissions Error: couldn\'t create directory \'invoices\' at: ' . $dir);
				return false;
			}

			if (file_exists($dir.$filename . '.pdf')) {
				unlink($dir.$filename . '.pdf');
			}

			$this->pdf->Output($dir.$filename . '.pdf', 'F');

			return $dir.$filename . '.pdf';
		} else {
			$this->pdf->Output($filename . '.pdf', 'I');

			return true;
		}
	}
	
	public function getInformation($order_info = [], bool $create_file = false)
	{
		$this->load->model('catalog/information');
		$data=[];
		$sale_protocol = $this->config->get('config_checkout_id');
		$information_info = $this->model_catalog_information->getInformation($sale_protocol);
		if ($information_info ) {
			//$this->load->library('pdf');
			$this->load->language('bytao/pdf');
			$this->load->model('setting/setting');
			$this->load->model('bytao/common');
			
			$module_pdf_invoice = $this->model_setting_setting->getSetting("module_pdf_invoice");
			$languages = $this->model_bytao_common->getStoreLanguages();
			$data['store_id'] = (int)$this->config->get('config_store_id') ;
			

			$data['store'] = $this->model_setting_setting->getSetting("config", $data['store_id']);
			
			$data['config'] = $module_pdf_invoice;
			
			$data['language_id'] = $this->config->get('config_language_id');
			
			if (isset($data['config']['module_pdf_invoice_rtl_' . $data['language_id']])) {
				$data['config']['text_align'] = 'right';
			} else {
				$data['config']['text_align'] = 'left';
			}
			$data['heading_title'] = $information_info['title'];

			$data['description'] = $information_info['description'];
			
			$language = [];

			$language['a_meta_charset'] = 'UTF-8';

			$language['text_date_added'] = $this->language->get('text_date_added');
			$language['text_order_id'] = $this->language->get('text_order_id');
			$language['text_order_status'] = $this->language->get('text_order_status');
			$language['text_invoice_no'] = $this->language->get('text_invoice_no');
			$language['text_shipping_method'] = $this->language->get('text_shipping_method');
			$language['text_shipping_address'] = $this->language->get('text_shipping_address');
			$language['text_payment_method'] = $this->language->get('text_payment_method');
			$language['text_payment_address'] = $this->language->get('text_payment_address');

			$language['column_total'] = $this->language->get('column_total');
			$language['column_product'] = $this->language->get('column_product');
			$language['column_model'] = $this->language->get('column_model');
			$language['column_quantity'] =$this->language->get('column_quantity');
			$language['column_price'] = $this->language->get('column_price');
			
			$data['lang'] = $this->language->get('code');
			$data['direction'] = $this->language->get('direction');
			
			$data = array_merge($data, $language);
			$this->pdf->tcpdf->setLanguageArray($language);
			$this->pdf->data = $data;
			
			$filename = 'agreement';
			$template_filename = 'bytao/pdf/information_info';
			
			if ($create_file) {
				
				$template_filename = 'bytao/pdf/information_info';
				if (!empty($data['config']['module_pdf_information_rtl_' . $data['language_id']])) {
					$template_filename .= '_rtl';
				}

				$this->pdf->data['html'] = $this->load->view($template_filename, $data);
				$this->pdf->Draw();
				if (ob_get_length())
					ob_end_clean();
				if (empty($this->pdf->data)) {
					return false;
				}
				$dir = DIR_PDF;
				if (!is_dir($dir) || !is_writable($dir)) {
					mkdir($dir, 0777, true);
				}
				if (!is_dir($dir)) {
					trigger_error('Permissions Error: couldn\'t create directory \'Information\' at: ' . $dir);
					return false;
				}

				if (file_exists($dir.$filename . '.pdf')) {
					unlink($dir.$filename . '.pdf');
				}
				$this->pdf->Output($dir.$filename . '.pdf', 'F');
				return $dir.$filename . '.pdf';
				
			} else {
				$this->pdf->data['html'] = $this->load->view($template_filename, $data);
				$this->pdf->Draw();
				$this->pdf->Output($filename . '.pdf', 'I');
				return false;
			}
		}
		return false;
	}

	
	public function formatAddress($address, $address_prefix = '', $format = null) {
		$find = [];
		$replace = [];

		if ($address_prefix != "") {
			$address_prefix = trim($address_prefix, '_') . '_';
		}

		if (is_null($format) || !is_string($format) || $format == '') {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$vars = array(
			'firstname',
			'lastname',
			'telephone',
			'company',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'zone',
			'zone_code',
			'country'
		);

        foreach ($vars as $var) {
            if ($address_prefix && isset($address[$address_prefix.$var])) {
                $value = $address[$address_prefix.$var];
            } elseif (isset($address[$var])) {
                $value = $address[$var];
            } else {
                $value = '';
            }

            if (is_numeric($value) || is_string($value)|| is_null($value)|| is_bool($value)) {
                $find[$var] = '{'.$var.'}';
                $replace[$var] = $value;
            }
        }

        foreach(array('custom_field', $address_prefix . 'custom_field') as $var) {
            if (isset($address[$var]) && is_array($address[$var])) {
                foreach ($address[$var] as $custom_field_id => $custom_field) {
                    if (!isset($custom_field['value'])) {
                        continue;
                    }

                    $var = 'custom_field_' . $custom_field_id;
                    $value = $custom_field['value'];

                    if (is_numeric($value) || is_string($value) || is_null($value) || is_bool($value)) {
                        $find[$var] = '{custom_field_' . $custom_field_id . '}';
                        $replace[$var] = $value;
                    }
                }
            }
        }

		return trim(str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', str_replace($find, $replace, $format))));
	}

    private function _resize($file, $width = 100, $height = 100) {
        if (!$width && !$height) {
            return false;
        }
        if (!file_exists(DIR_IMAGE . $file)) {
            trigger_error('PDF Invoice missing image file: ' . $file);
            return false;
        }

        if (!$width) {
            $width = 100;
        }
        if (!$height) {
            $height = 100;
        }

        $this->load->model('tool/image');

        $logo_size = getimagesize(DIR_IMAGE . $file);

        $imageWidth  = $logo_size[0];
        $imageHeight = $logo_size[1];

        $wRatio = $imageWidth / $width;
        $hRatio = $imageHeight / $height;
        $maxRatio = max($wRatio, $hRatio);

        if ($maxRatio > 1) {
            $outputWidth = round($imageWidth / $maxRatio);
            $outputHeight = round($imageHeight / $maxRatio);
        } else {
            $outputWidth = $imageWidth;
            $outputHeight = $imageHeight;
        }

        $image = $this->model_tool_image->resize($file, $outputWidth, $outputHeight);

        // Convert to path instead of url
        $image = '/' . str_replace([HTTP_SERVER], '', $image);

        return $image;
    }

	public function products($pData): string
	{
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info && !$order_info['order_status_id']) {
			$this->load->language('mail/order_alert');

			$subject = html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']), ENT_QUOTES, 'UTF-8');

			$data['order_id'] = $order_info['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$order_status_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_status` WHERE `order_status_id` = '" . (int)$order_status_id . "' AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

			if ($order_status_query->num_rows) {
				$data['order_status'] = $order_status_query->row['name'];
			} else {
				$data['order_status'] = '';
			}

			$this->load->model('tool/upload');

			$data['products'] = [];

			$order_products = $this->model_checkout_order->getProducts($order_id);

			foreach ($order_products as $order_product) {
				$option_data = [];

				$order_options = $this->model_checkout_order->getOptions($order_info['order_id'], $order_product['order_product_id']);

				foreach ($order_options as $order_option) {
					if ($order_option['type'] != 'file') {
						$value = $order_option['value'];
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

				$this->load->model('checkout/subscription');

				$subscription_info = $this->model_checkout_order->getSubscription($order_info['order_id'], $order_product['order_product_id']);

				if ($subscription_info) {
					if ($subscription_info['trial_status']) {
						$trial_price = $this->currency->format($subscription_info['trial_price'] + ($this->config->get('config_tax') ? $subscription_info['trial_tax'] : 0), $this->session->data['currency']);
						$trial_cycle = $subscription_info['trial_cycle'];
						$trial_frequency = $this->language->get('text_' . $subscription_info['trial_frequency']);
						$trial_duration = $subscription_info['trial_duration'];

						$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
					}

					$price = $this->currency->format($subscription_info['price'] + ($this->config->get('config_tax') ? $subscription_info['tax'] : 0), $this->session->data['currency']);
					$cycle = $subscription_info['cycle'];
					$frequency = $this->language->get('text_' . $subscription_info['frequency']);
					$duration = $subscription_info['duration'];

					if ($duration) {
						$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
					} else {
						$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
					}
				}

				$data['products'][] = [
					'name'         => $order_product['name'],
					'model'        => $order_product['model'],
					'quantity'     => $order_product['quantity'],
					'option'       => $option_data,
					'subscription' => $description,
					'total'        => html_entity_decode($this->currency->format($order_product['total'] + ($this->config->get('config_tax') ? $order_product['tax'] * $order_product['quantity'] : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['vouchers'] = [];

			$order_vouchers = $this->model_checkout_order->getVouchers($order_id);

			foreach ($order_vouchers as $order_voucher) {
				$data['vouchers'][] = [
					'description' => $order_voucher['description'],
					'amount'      => html_entity_decode($this->currency->format($order_voucher['amount'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['totals'] = [];

			$order_totals = $this->model_checkout_order->getTotals($order_id);

			foreach ($order_totals as $order_total) {
				$data['totals'][] = [
					'title' => $order_total['title'],
					'value' => html_entity_decode($this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8')
				];
			}

			$data['comment'] = nl2br($order_info['comment']);

			$data['store'] = html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8');
			$data['store_url'] = $order_info['store_url'];

			return $this->load->view('bytao/pdf/order_products', $data);
		}
	}



}