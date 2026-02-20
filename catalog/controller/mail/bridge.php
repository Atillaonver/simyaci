<?php
namespace Opencart\Catalog\Controller\Mail;
class Bridge extends \Opencart\System\Engine\Controller {
	public function index(string &$route, array &$args): void {
		$this->load->language('bytao/bridge');
		$subject = $this->language->get('text_demo_request');
		$from = $this->config->get('config_mail_smtp_username');
		$tomail = $this->config->get('config_support')?$this->config->get('config_support'):$this->config->get('config_email');
		$sended =[];
		if ($this->config->get('config_mail_engine')) {
			$data['email'] = $this->request->post['email'];
			$data['phone'] = $this->request->post['phone'];
			$data['name'] = $this->request->post['name'];
			
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			if ($this->config->get('config_logo')) {
				$data['logo'] = $this->config->get('config_url') . 'image/' . html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
			} else {
				$data['logo'] = '';
			}
			$data['store_name'] = $store_name;
			$data['store_url'] = $this->config->get('config_url');
			
			
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
				'smtp_username' => $this->config->get('config_mail_smtp_username'),
				'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
				'smtp_port'     => $this->config->get('config_mail_smtp_port'),
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];

			$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
			
			$mail->setTo($tomail);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/bridge', $data));
			$mail->setText($subject);
			$send = $mail->send()? 1 : 0 ;
			
			$this->request->get['sended'] = $send;
			
			$emails = explode(',', $this->config->get('config_mail_alert_email'));
			foreach ($emails as $email) {
					if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$mail->setTo(trim($email));
						$res = $mail->send();
						if($res){
							$sended []= substr($email,0,4).':1';
						}else{
							$sended []= substr($email,0,4).':0';
						}
					}
			}
				
			$mail_history = [
				'from_mail' 	=> $tomail,
				'to_mail' 		=> $this->config->get('config_email'),
				'status'		=> $send,
				'sended'		=> ($sended?implode(',',$sended):''),
				'subject'		=> $subject,
				'public_id'		=> '',
				'store_id'		=> $this->config->get('config_store_id'),
				'template'		=> 'new_mail_demoreq'
			];
			
			$this->addHistory($mail_history);
		}
	}
				
	
	private function generateRandomString($order, int $length = 10):string {
		$length = $length - strlen((string)$order);
		$code = substr(str_shuffle(str_repeat($x=strtolower('123456789ABCDEFGHJKLMNPQRSTWXYZ'), ceil($length/strlen($x)) )),1,$length);
	    return $order.$code;
	}
	
	public function addHistory(array $mail_history):void{
		$this->load->model('bytao/mail');
		$this->model_bytao_mail->addHistory($mail_history);
		
	}
}
