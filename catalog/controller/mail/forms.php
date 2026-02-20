<?php
namespace Opencart\Catalog\Controller\Mail;
use Mailgun\Mailgun; 
class Forms extends \Opencart\System\Engine\Controller {
	// catalog/model/account/gdpr/addGdpr
	public function index(string &$route, array &$args, mixed &$output): void {
		// $args[0] $code
		// $args[1] $email
		// $args[2] $action
		
		$email = $this->customer->getEmail();


		$this->load->language('bytao/forms');

		$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		if ($this->config->get('config_logo')) {
			$data['logo'] = $this->config->get('config_url') . 'image/' . html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
		} else {
			$data['logo'] = '';
		}

		

		$data['ip'] = $this->request->server['REMOTE_ADDR'];

		$data['store_name'] = $store_name;
		$data['store_url'] = $this->config->get('config_url');
		
		
		if ($this->config->get('config_mail_engine') && $email) {
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => $this->config->get('config_mail_smtp_hostname'),
				'smtp_username' => $this->config->get('config_mail_smtp_username'),
				'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
				'smtp_port'     => $this->config->get('config_mail_smtp_port'),
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];
			
			$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
			$mail->setTo($email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(sprintf($this->language->get('text_subject'), $store_name));
			$mail->setHtml($this->load->view('mail/forms', $data));
			if (isset($this->request->get['pdf']) && file_exists($this->request->get['pdf'])) {
				$mail->AddAttachment($this->request->get['pdf']);
			}
			
			$result = $mail->send();
		}
	}

}
