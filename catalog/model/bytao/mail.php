<?php
namespace Opencart\Catalog\Model\Bytao;
class Mail extends \Opencart\System\Engine\Model {
	
	public function addHistory(array $data):int  {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "mail_history SET  `from_mail` = '" . $this->db->escape((string)$data['from_mail']) . "',`to_mail` = '" . $this->db->escape((string)$data['to_mail']) . "',`subject` = '" . $this->db->escape((string)$data['subject']) . "',`status` = '" . (int)$data['status'] . "',`sended` = '" . $this->db->escape((string)$data['sended']) . "',`template` = '" . $this->db->escape((string)$data['template']) . "',`public_id` = '" . (int)$data['public_id'] . "',`store_id` = '" . (int)$data['store_id'] . "',`date_added` = NOW()");

		return $this->db->getLastId();
	}

}