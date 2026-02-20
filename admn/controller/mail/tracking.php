<?php
namespace Opencart\Admin\Controller\Mail;
class Tracking extends \Opencart\System\Engine\Controller {
	
	// admin/controller/sale/order/updatesend/after
	public function index(string &$route, array &$args, mixed &$output): void {
		
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}	
		$order_info = $this->model_sale_order->getOrder($order_id);
		if($order_info){
			$data['order_key'] = $order_info['order_key'];
			$data['cargo'] = $order_info['cargo'];
			$data['tracking_no'] = $order_info['tracking_no'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			
			if (!defined('HTTP_CATALOG')) {
				$store_url = HTTP_SERVER;
			} else {
				$store_url = HTTP_CATALOG;
			}

			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($order_info['store_id']);

			if ($store_info) {
				$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $order_info['store_id']), ENT_QUOTES, 'UTF-8');
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

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $store_logo)) {
				$data['logo'] = $store_url . 'image/' . $store_logo;
			} else {
				$data['logo'] = '';
			}

			// Load the language for any mails using a different country code and prefixing it so it does not pollute the main data pool.
			//$this->load->language($language_code, 'mail', $language_code);
			$this->load->language('mail/order_add');

			$subject = sprintf($this->language->get('text_subject_shipped'), $store_name, $order_info['order_key']);
			if ($order_info['customer_id']) {
				$data['link'] = $order_info['store_url'] . 'index.php?route=account/track.info&order_key=' . $order_info['order_key'];
			} else {
				$data['link'] = $this->url->link('account/track','language=' . $this->config->get('config_language'));
			}


			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$this->load->model('setting/setting');

			$from = $this->model_setting_setting->getValue('config_email', $order_info['store_id']);

			if (!$from) {
				$from = $this->config->get('config_email');
			}
			
			$data['language']=$this->language->get('code');
			//$template = $this->load->view('mail/order_track', $data);
			
		$mail_option = [
			'parameter'     => $this->model_setting_setting->getValue('config_mail_parameter', $order_info['store_id']),
			'smtp_hostname' => $this->model_setting_setting->getValue('config_mail_smtp_hostname', $order_info['store_id']),
			'smtp_username' => $this->model_setting_setting->getValue('config_mail_smtp_username', $order_info['store_id']),
			'smtp_password' => html_entity_decode($this->model_setting_setting->getValue('config_mail_smtp_password', $order_info['store_id']), ENT_QUOTES, 'UTF-8'),
			'smtp_port'     => $this->model_setting_setting->getValue('config_mail_smtp_port', $order_info['store_id']),
			'smtp_timeout'  => $this->model_setting_setting->getValue('config_mail_smtp_timeout', $order_info['store_id'])
		];
			
			//$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
			$mail = new \Opencart\System\Library\Mail('phpmailer', $mail_option);
			$mail->setTo($order_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			//$mail->setCcTo($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/order_track', $data));
			$mail->setText($subject);
			$result = $mail->send();
			
			/*
			$headers  = "MIME-Version: 1.0\r\n"; 
			$headers .= "From: $from\r\n"; 
			$headers .= "X-Mailer: PHP/" . phpversion() ."\r\n";
			$headers .= "Content-type: text/html\r\n";
			$headers .= "X-Priority: 1\r\n"; 
			$headers .= "Priority: Urgent\r\n";
			$headers .= "Importance: High\r\n"; 
			$headers .= "X-MSMail-Priority: High\r\n"; 
			$result = mail($order_info['email'], html_entity_decode($subject, ENT_QUOTES, 'UTF-8'), $template, $headers );*/
			if($result){
				$this->log->write(' to '.$order_info['email'].' sended cargo number:'.$order_info['tracking_no'],2);
				$emails = explode(',', $this->model_setting_setting->getValue('config_mail_alert_email', $order_info['store_id']));
				foreach ($emails as $email) {
					if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$mail->setText('ADM: '.$subject);
						$result = $mail->send();
						//mail($email, html_entity_decode('ADM: '.$subject, ENT_QUOTES, 'UTF-8'), $template, $headers );
					}
				}
				$sended = 1 ;
			}else{
				$sended = 0 ;
			}
			
		
			
		
			
		}
	}
}
