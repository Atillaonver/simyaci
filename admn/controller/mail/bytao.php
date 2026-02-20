<?php
namespace Opencart\Admin\Controller\Mail;
class Bytao extends \Opencart\System\Engine\Controller {
	
	public function index(string &$route, array &$args, mixed &$output): void {
		$this->log->write('Array:'.print_r($this->request->post,TRUE));
	}
	
	public function couponsend(string &$route, array &$args, mixed &$output): void {
		
		$this->load->model('setting/store');
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('marketing/coupon');
		
		
		$store_id = $this->session->data['store_id'];
		$store_info = $this->model_setting_store->getStore($store_id);
		if ($store_info) {
			$store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
			$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
			$store_url = $store_info['url'];
		} else {
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			
			if (!defined('HTTP_CATALOG')) {
				$store_url = HTTP_SERVER;
			} else {
				$store_url = HTTP_CATALOG;
			}
		}
			
		$setting = $this->model_setting_setting->getSetting('config', $store_id);

		$from = $this->model_setting_setting->getValue('config_email', $store_id);
		if (!$from) {
			$from = $this->config->get('config_email');
		}
		
		$product_id = isset($this->request->post['product_id'])?$this->request->post['product_id']:"";		
		
		$coupon_id = isset($this->request->post['coupon_id'])?$this->request->post['coupon_id']:"";
		$coupon = $this->model_marketing_coupon->getCoupon($coupon_id);
		
		if($coupon){
			$url = new \Opencart\System\Library\Url($store_url);
			
			$product = $this->model_catalog_product->getProduct($product_id);
			
			if (!empty($product) && is_file(DIR_IMAGE . $product['image'])) {
				$thumb = $this->model_tool_image->resize($product['image'], 320, 380);
				$mailContent['products'][]=[
					'href' => $url->link('product/product', 'product_id=' . $product_id),
					'name' => $product['name'],
					'thumb' => $thumb
				];
			}
			
			$mail = isset($this->request->post['mail'])?$this->request->post['mail']:"";
			if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $mail)) {$mail='';}
			
			
			
			$subject = $coupon['name'];
			
			$language_code = $this->config->get('config_language');
			
			if (is_file(DIR_IMAGE . $store_logo)) {
				$data['logo'] = $store_url . 'image/' . $store_logo;
			} else {
				$data['logo'] = '';
			}
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;
				
			$data['language']=$this->language->get('code');
			$template = $this->load->view('bytao/mail_coupon_send', $data);
			
			$headers  = "MIME-Version: 1.0\r\n"; 
			$headers .= "From: $from\r\n"; 
			$headers .= "X-Mailer: PHP/" . phpversion() ."\r\n";
			$headers .= "Content-type: text/html\r\n";
			$headers .= "X-Priority: 1\r\n"; 
			$headers .= "Priority: Urgent\r\n";
			$headers .= "Importance: High\r\n"; 
			$headers .= "X-MSMail-Priority: High\r\n"; 
			
			$result = mail($mail, html_entity_decode($subject, ENT_QUOTES, 'UTF-8'), $template, $headers );
			if($result){
				$emails = explode(',', $this->config->get('config_mail_alert_email'));
				foreach ($emails as $email) {
					if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
						mail($email, html_entity_decode('ADM: '.$subject, ENT_QUOTES, 'UTF-8'), $template, $headers );
					}
				}
				$sended = 1 ;
			}else{
				$sended = 0 ;
			}
		}
			
	}

	public function testSend():void {
		
			$this->load->language('mail/forgotten');

			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$subject = sprintf($this->language->get('text_subject'), $store_name);
			$data['store'] = $store_name;
			$data['store_url'] = $this->config->get('config_store_url');

			if ($this->config->get('config_mail_engine')) {
				$mail_option = [
					'parameter'     => $this->config->get('config_mail_parameter'),
					'smtp_hostname' => 'smtpout.secureserver.net', //$this->config->get('config_mail_smtp_hostname'),
					'smtp_username' => $this->config->get('config_mail_smtp_username'),
					'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
					'smtp_port'     => '25',//$this->config->get('config_mail_smtp_port'),
					'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
				];

				$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
				$mail->setTo($email);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($store_name);
				$mail->setSubject($subject);
				$mail->setHtml($this->load->view('mail/forgotten', $data));
				$mail->send();
			}
	}
}
