<?php
namespace Opencart\Admin\Model\Bytao;
class Tool extends \Opencart\System\Engine\Model {
	
	public function getTodayTotalOrders($store_id = 0): int {
		
		$sql = "SELECT COUNT(DISTINCT o.customer_id) AS total FROM `" . DB_PREFIX . "order` o WHERE o.store_id='".(int)$store_id ."' AND (DATE(o.date_added) >= CURDATE()) AND DATE(o.date_added) < CURDATE() + INTERVAL 1 DAY";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getTodayTotalCustomers($store_id = 0): int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer` WHERE store_id='".(int)$store_id ."' AND (DATE(date_added) >= CURDATE()) AND DATE(date_added) < CURDATE() + INTERVAL 1 DAY");

		return $query->row['total'];
	}
	
	public function tester(): void {
		$json = [];
		
		$toMail = isset($this->request->post['mail'])?$this->request->post['mail']:"";
		if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $toMail)) { $toMail = '';}
		
		$api_info = [
				'mail' 			=> $mail
			];
		$this->load->language('mail/forgotten');

		$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$subject = sprintf($this->language->get('text_subject'), $store_name);
		$data['store'] = $store_name;
		$data['store_url'] = $this->config->get('config_store_url');

		if ($this->config->get('config_mail_engine')) {
			$mail_option = [
				'parameter'     => $this->config->get('config_mail_parameter'),
				'smtp_hostname' => 'ssl://smtpout.secureserver.net', //$this->config->get('config_mail_smtp_hostname'),
				'smtp_username' => $this->config->get('config_mail_smtp_username'),
				'smtp_password' => html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'),
				'smtp_port'     => '25',//$this->config->get('config_mail_smtp_port'),
				'smtp_timeout'  => $this->config->get('config_mail_smtp_timeout')
			];

			$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'), $mail_option);
			$mail->setTo($mail);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject($subject);
			$mail->setHtml($this->load->view('mail/forgotten', $data));
			$mail->send();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	



}	
?>