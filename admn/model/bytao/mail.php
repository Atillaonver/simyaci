<?php
namespace Opencart\Admin\Model\Bytao;
class Mail extends \Opencart\System\Engine\Model {
	
	public function addMail_template($data) {
		$this->event->trigger('pre.admin.mail_template.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "mail_template SET status = '" . (int)$data['status'] . "',type = '" . (int)$data['type'] . "',code = '" . $this->db->escape($data['code']) . "'");

		$mail_template_id = $this->db->getLastId();

		foreach ($data['mail_template_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mail_template_description SET mail_template_id = '" . (int)$mail_template_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		if (isset($data['mail_template_store'])) {
			foreach ($data['mail_template_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mail_template_to_store SET mail_template_id = '" . (int)$mail_template_id . "', store_id = '" . (int)$store_id . "'");
			}
		}


		$this->cache->delete('mail_template');

		$this->event->trigger('post.admin.mail_template.add', $mail_template_id);

		return $mail_template_id;
	}

	public function editMail_template($mail_template_id, $data) {
		$this->event->trigger('pre.admin.mail_template.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "mail_template SET status = '" . (int)$data['status'] . "',type = '" . (int)$data['type'] . "',code = '" . $this->db->escape($data['code']) . "' WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_template_description WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		foreach ($data['mail_template_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mail_template_description SET mail_template_id = '" . (int)$mail_template_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_template_to_store WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		if (isset($data['mail_template_store'])) {
			foreach ($data['mail_template_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mail_template_to_store SET mail_template_id = '" . (int)$mail_template_id . "', store_id = '" . (int)$store_id . "'");
			}
		}


		$this->cache->delete('mail_template');

		$this->event->trigger('post.admin.mail_template.edit', $mail_template_id);
	}

	public function deleteMail_template($mail_template_id) {
		$this->event->trigger('pre.admin.mail_template.delete', $mail_template_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_template WHERE mail_template_id = '" . (int)$mail_template_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_template_description WHERE mail_template_id = '" . (int)$mail_template_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_template_to_store WHERE mail_template_id = '" . (int)$mail_template_id . "'");
		$this->cache->delete('mail_template');

		$this->event->trigger('post.admin.mail_template.delete', $mail_template_id);
	}

	public function copyMail_template($mail_template_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mail_template WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		if ($query->num_rows) {
			$data = array();
			
			$data = $query->row;
			$data = array_merge($data, array('mail_template_description' => $this->getMail_templateDescriptions($mail_template_id)));
			$data = array_merge($data, array('mail_template_store' => $this->getMail_templateStores($mail_template_id)));
			
			$this->addMail_template($data);
		}
	}

	public function getMail_template($mail_template_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'mail_template_id=" . (int)$mail_template_id . "') AS keyword FROM " . DB_PREFIX . "mail_template WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		return $query->row;
	}

	public function getMail_templateById($mail_template_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_template WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		return $query->row;
	}

	public function getMail_templates($data = []) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "mail_template i LEFT JOIN " . DB_PREFIX . "mail_template_description id ON (i.mail_template_id = id.mail_template_id) LEFT JOIN " . DB_PREFIX . "mail_template_to_store mt2s ON (i.mail_template_id = mt2s.mail_template_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND mt2s.store_id='".(int)$this->session->data['store_id'] ."'";
			if (isset($data['type'])) {
				$sql .=" AND i.type='".$data['type']."'";
				}

			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
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
		} else {
			
			$mail_template_data = $this->cache->get('mail_template.' . (int)$this->config->get('config_language_id'));

			if (!$mail_template_data) {
				$query = $this->db->query("SELECT i.*,id.* FROM " . DB_PREFIX . "mail_template i LEFT JOIN " . DB_PREFIX . "mail_template_description id ON (i.mail_template_id = id.mail_template_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$mail_template_data = $query->rows;

				$this->cache->set('mail_template.' . (int)$this->config->get('config_language_id'), $mail_template_data);
			}

			return $mail_template_data;
		}
	}

	public function getMail_templateDescriptions($mail_template_id) {
		$mail_template_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_template_description WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		foreach ($query->rows as $result) {
			$mail_template_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'meta_title'            => $result['meta_title'],
				'description'      => $result['description']
			);
		}

		return $mail_template_description_data;
	}

	public function getMail_templateStores($mail_template_id) {
		$mail_template_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_template_to_store WHERE mail_template_id = '" . (int)$mail_template_id . "'");

		foreach ($query->rows as $result) {
			$mail_template_store_data[] = $result['store_id'];
		}

		return $mail_template_store_data;
	}
	
	public function getTotalMail_templates() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mail_template mt LEFT JOIN " . DB_PREFIX . "mail_template_to_store mt2s ON(mt.mail_template_id = mt2s.mail_template_id) WHERE mt2s.store_id ='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}
	
	public function addMail($who,$subject,$content,$code='') {
		$this->db->query("INSERT INTO " . DB_PREFIX . "mail_history SET who_mail = '" . $this->db->escape($who) . "', mail_subject = '" . $this->db->escape($subject) . "', content='". $this->db->escape($content) . "',code='". $this->db->escape($code) . "',store_id ='".(int)$this->session->data['store_id']."', date_added = NOW()");
		
		return  $this->db->getLastId();
	}

	public function deleteMail($mail_history_id) {
		$this->event->trigger('pre.admin.mail_template.delete', $mail_history_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_history WHERE mail_history_id = '" . (int)$mail_history_id . "'");
		
		$this->cache->delete('mail_history');

		$this->event->trigger('post.admin.mail_history.delete', $mail_history_id);
	}

	public function getMail($mail_history_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history WHERE mail_history_id = '" . (int)$mail_history_id . "'");

		return $query->row;
	}
	
	public function getMailsByMail($mail) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history  WHERE to_mail = '" . $mail. "'");

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getCustomerProductsforMail($mail){
		$productData=[];
		
		$SQL  = "SELECT *, p.status as pStatus, o.order_key as orderKey,o.order_id as orderID,"; 
		$SQL .= " op.product_id as productId ,"; 
		$SQL .= " pd.name as productName  FROM " . DB_PREFIX . "order_product op"; 
		$SQL .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id=op.product_id)"; 
		$SQL .= " LEFT JOIN " . DB_PREFIX . "order o ON (o.order_id=op.order_id)"; 
		$SQL .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)"; 
		$SQL .= " WHERE o.email = '" . $mail. "'AND o.store_id='".(int)$this->session->data['store_id']."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'"; 
		$SQL .= " GROUP BY op.product_id ORDER BY o.date_added DESC";
		
		$query = $this->db->query($SQL);
		if(isset($query->rows)){
			foreach($query->rows as $row){
				$productData[]=[
					'productName'			=> $row['productName'],
					'image'			=> $row['image'],
					'productId'		=> $row['productId'],
					'model'			=> $row['model'],
					'orderId'		=> $row['orderKey'],
					'orderStatus'	=> $this->getProductStatuss($mail),
					'productStatus'	=> $row['pStatus'],
					'order_status_id'		=> $row['order_status_id']
				];
			}
		}

		return $productData;
	}
	
	public function getProductStatuss($mail) {
		$status = [];
		
		$SQL  	= "SELECT *,(SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id  AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS STATUS FROM " . DB_PREFIX . "order_product op"; 
		$SQL   .= " LEFT JOIN " . DB_PREFIX . "order o ON (o.order_id=op.order_id)"; 
		$SQL   .= " WHERE o.email = '" . $mail. "'AND o.store_id='".(int)$this->session->data['store_id']."'"; 
		
		$query = $this->db->query($SQL);
		if(isset($query->rows)){
			foreach($query->rows as $row){
				if($row['STATUS']){
					$status[]= $row['STATUS'];
				}
			}
		}
		return implode('/',$status);
	}
	
	public function getMailCountByMail($mail) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mail_history  WHERE to_mail = '" . $mail. "' AND (template LIKE '%send_coupon%' OR template LIKE '%complete_order_bonus%') ");

		return isset($query->row['total'])?$query->row['total']:0;
	}
	
	public function getCampaignMailByMail($mail,$order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_personal  WHERE email = '" . $mail. "' AND order_id='".(int)$order_id."'");

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getMails(array $data = []) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "mail_history WHERE store_id ='".(int)$this->session->data['store_id']."'";

			$sort_data = array(
				'subject',
				'date_added'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY subject";
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

			return isset($query->rows)?$query->rows:[];
		} else {
			
			//$mail_template_data = $this->cache->get('mail_history');
			//if (!$mail_template_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history  WHERE store_id ='".(int)$this->session->data['store_id']."' ORDER BY date_added");

				$mail_template_data = $query->rows;

			//	$this->cache->set('mail_history', $mail_template_data);
			//}

			return $mail_template_data;
		}
	}
	
	public function getTotalMails() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mail_history");

		return isset($query->row['total'])?$query->row['total']:0;
	}
	
	public function coupon(){
		//mail_coupon_send
	}
	
	public function tester(){
		//mail_coupon_send
	}
	
	
}