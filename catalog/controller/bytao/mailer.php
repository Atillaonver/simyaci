<?php
namespace Opencart\Catalog\Controller\Bytao;
class Mailer extends \Opencart\System\Engine\Controller {
	public function index():void {
		$this->response->setOutput($this->load->view('error/not_found', $data));
	}
	
	public function coupon(): void {
		$json = [];
		
		$this->log->write('Mailer : Coupon');
		
		$this->load->language('checkout/coupon');
		$this->load->model('catalog/product');
		$this->load->model('bytao/coupon');
		$this->load->model('setting/setting');
		
		$product_id = isset($this->request->post['product_id'])?(int)$this->request->post['product_id']:0;		
		$coupon_id = isset($this->request->post['coupon_id'])?(int)$this->request->post['coupon_id']:0;
		$toMail = isset($this->request->post['mail'])?$this->request->post['mail']:"";
		
		if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $toMail)) { $toMail = '';}
		
		if($product_id && $coupon_id && $toMail){
			$mailContent = [];
			
			
			$this->load->model('bytao/mail');
			$this->load->model('tool/image');
			
			
			$coupon = $this->model_bytao_coupon->getCouponById($coupon_id);
			
			$code = "&code=".$coupon['code'];
			
			$product = $this->model_catalog_product->getProduct($product_id);
			
			$product_link = $this->url->link('product/product', $code. '&product_id=' . $product_id);
			$product_name = $product['name'];
			
			if (!empty($product) && is_file(DIR_IMAGE . $product['image'])) {
				$thumb = $this->model_tool_image->resize($product['image'], 680, 680);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 680, 680);
			}
			
			
			$uses_customer = $coupon['uses_customer'];
			if($coupon['uses_customer'] == '1'){
				
				$email_query = $this->db->query("SELECT cp.* FROM `" . DB_PREFIX . "coupon_personal` cp LEFT JOIN `" . DB_PREFIX . "coupon` c ON (c.code = cp.code)  LEFT JOIN `" . DB_PREFIX . "coupon_to_store` c2s ON (c.coupon_id=c2s.coupon_id) WHERE cp.code = '" . $coupon['code'] . "' AND email = '" . $toMail . "' AND (cp.date_start < NOW()) AND (cp.date_end > NOW()) AND cp.status = '0'");
				
				if(!$email_query->num_rows){
					$date = strtotime(date('Y-m-d'));
					$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_personal SET code='" . $this->db->escape($coupon['code']). "', email='".$this->db->escape($toMail)."',date_start= NOW(), date_end='".date('Y-m-d',strtotime('+15 days',$date))."'");
				}else{
					$date = strtotime($email_query->row['date_end']);
					
				}
				
				
			
				
				$data['content'] = [
					'lastvisited' => '',
					'coupondate' => date('l jS \of F Y',strtotime('+15 days',$date)),
					'link' =>$this->url->home(true),
					'image' => $thumb,
					'name' => $product_name,
					'product_link' => $product_link,
					'discount' => $coupon['discount'],
					'code' => $coupon['code'],
					'type' => $coupon['type_customer']
				];
			}
			else
			{
					$date = strtotime($coupon_info['date_end']);
					$data['content'] = [
						'lastvisited' => '',
						'coupondate' => date('l jS \of F Y',strtotime($date)),
						'link' => $this->url->home(true),
						'image' => $thumb,
						'name' => $product_name,
						'discount' => $coupon['discount'],
						'product_link' => $product_link,
						'code' => $coupon['code'],
						'type' => $coupon['type_customer']
					];
			}
			
			
			
			
			if (!defined('HTTP_CATALOG')) {
				$data['store_url'] = HTTP_SERVER;
			} else {
				$data['store_url'] = HTTP_CATALOG;
			}	
				
			$data['store_name'] = $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$data['store_logo'] = $data['store_url'].'image/'.html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			$data['store_logo_negative'] = $data['store_url'].'image/'.html_entity_decode($this->config->get('config_logo_negative'), ENT_QUOTES, 'UTF-8');
			
			
			$from = $this->config->get('config_email');
			$data['title'] = $subject = html_entity_decode($coupon['name'], ENT_QUOTES, 'UTF-8');
					
			$data['language'] = $this->language->get('code');
			$data['coupon_message'] = sprintf($this->language->get('text_coupon_message_'.$uses_customer),$coupon['code'] );
			$template = $this->load->view('mail/order_coupon', $data);
			/*
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => 'mail.smtp2go.com',
				'smtp_username' => 'shearling.com',
				'smtp_password' => 'rElKXsEpqYlRpLMD',
				'smtp_port'     => '2525',
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];
			*/
			
			/*
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => 'smtp.yandex.com.tr',
				'smtp_username' => 'bilgi@icodingsoft.com',
				'smtp_password' => 'yqmqffhzfyrgsrxe',
				'smtp_port'     => '587',
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];
			*/
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
				'smtp_username' => $this->config->get('config_mail_smtp_username'),
				'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
				'smtp_port'     => $this->config->get('config_mail_smtp_port'),
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];
			//$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
			$mail = new \Opencart\System\Library\Mail('phpmailer', $mail_option);
			$mail->setTo($toMail);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/order_coupon', $data));
			//$mail->setText($subject);
			//$this->load->model('mail/phpmailer_custom'); 
			//$result = $this->model_mail_phpmailer_custom->sendMail($toMail, $subject, wordwrap($this->load->view('mail/order_coupon', $data),70));
			$result = $mail->send();
			$this->log->write('String:'.$result);
			
			$sended =[];
			$send = $result;
			$sended []= substr($toMail,0,4).':'.$result;
			/*
			if($result){
				
				$emails = explode(',', $this->config->get('config_mail_alert_email'));
				$emails [] = $this->config->get('config_email');
				foreach ($emails as $email) {
					if ($email) {
						$mail->setTo($email);
						$res =  $mail->send();
						$this->log->write('String:'.$res);
						if($res){
							$sended []= substr($email,0,4).':1';
						}else{
							$sended []= substr($email,0,4).':0';
						}
					}
				}
				$send = 1 ;
			}else{
				$send = 0 ;
			}
			*/
			
			
			$json['sendedTo']=($sended?implode(',',$sended):'');
			
			$mail_history = [
				'from_mail' => $from,
				'to_mail' 	=> $toMail,
				'status'	=> $send,
				'sended'	=> ($sended?implode(',',$sended):''),
				'subject'	=> $subject,
				'public_id'	=> '',
				'store_id'	=> $this->config->get('config_store_id'),
				'template'	=> 'order_coupon'
			];
			
			$this->addHistory($mail_history);
			
			


			
		}else{
			$json['error']='Missing Arguments';
		}
		
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function addHistory(array $mail_history):void{
		$this->load->model('bytao/mail');
		$this->model_bytao_mail->addHistory($mail_history);
		
	}

	
}