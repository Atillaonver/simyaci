<?php

class ModelBytaoMailtemp extends Model {
	
	public function addMailtemp($data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "mailtemp SET status = '" . (int)$data['status'] . "',type = '" . (int)$data['type'] . "',code = '" . $this->db->escape($data['code']) . "',module = '" . $this->db->escape($data['module']) . "'");
		$mailtemp_id = $this->db->getLastId();
		foreach ($data['mailtemp_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mailtemp_description SET mailtemp_id = '" . (int)$mailtemp_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		if (isset($data['mailtemp_store'])) {
			foreach ($data['mailtemp_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mailtemp_to_store SET mailtemp_id = '" . (int)$mailtemp_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		return $mailtemp_id;
	}

	public function editMailtemp($mailtemp_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "mailtemp SET status = '" . (int)$data['status'] . "',type = '" . (int)$data['type'] . "',code = '" . $this->db->escape($data['code']) . "',module = '" . $this->db->escape($data['module']) . "' WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "mailtemp_description WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
		foreach ($data['mailtemp_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "mailtemp_description SET mailtemp_id = '" . (int)$mailtemp_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "mailtemp_to_store WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
		if (isset($data['mailtemp_store'])) {
			foreach ($data['mailtemp_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "mailtemp_to_store SET mailtemp_id = '" . (int)$mailtemp_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function deleteMailtemp($mailtemp_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailtemp WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailtemp_description WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "mailtemp_to_store WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
	}

	public function copyMailtemp($mailtemp_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mailtemp WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");

		if ($query->num_rows) {
			$data = array();
			
			$data = $query->row;
			$data = array_merge($data, array('mailtemp_description' => $this->getMailtempDescriptions($mailtemp_id)));
			$data = array_merge($data, array('mailtemp_store' => $this->getMailtempStores($mailtemp_id)));
			
			$this->addMailtemp($data);
		}
	}

	public function getMailtemp($mailtemp_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "mailtemp WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");

		return $query->row;
	}

	public function getMailtempById($mailtemp_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailtemp WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");

		return $query->row;
	}

	public function getMailtemps($data = array()) {
			$sql = "SELECT * FROM " . DB_PREFIX . "mailtemp m LEFT JOIN " . DB_PREFIX . "mailtemp_description md ON (m.mailtemp_id = md.mailtemp_id) LEFT JOIN " . DB_PREFIX . "mailtemp_to_store mt2s ON (m.mailtemp_id = mt2s.mailtemp_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND mt2s.store_id='".(int)$this->session->data['store_id'] ."'";
			if (isset($data['type'])) {
				$sql .=" AND m.type='".$data['type']."'";
				}

			$sort_data = array(
				'md.title',
				'm.module',
				'm.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY md.title";
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

	public function getMailtempDescriptions($mailtemp_id) {
		$mailtemp_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailtemp_description WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");
		foreach ($query->rows as $result) {
			$mailtemp_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'meta_title'            => $result['meta_title'],
				'description'      => $result['description']
			);
		}
		return $mailtemp_description_data;
	}

	public function getMailtempStores($mailtemp_id) {
		$mailtemp_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mailtemp_to_store WHERE mailtemp_id = '" . (int)$mailtemp_id . "'");

		foreach ($query->rows as $result) {
			$mailtemp_store_data[] = $result['store_id'];
		}

		return $mailtemp_store_data;
	}
	
	public function getTotalMailtemps() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mailtemp m LEFT JOIN " . DB_PREFIX . "mailtemp_to_store m2s ON(m.mailtemp_id = m2s.mailtemp_id) WHERE m2s.store_id ='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}
	
	public function addMailtempMail($who,$subject,$content,$code='') {
		$this->db->query("INSERT INTO " . DB_PREFIX . "mail_history SET who_mail = '" . $this->db->escape($who) . "', mail_subject = '" . $this->db->escape($subject) . "', content='". $this->db->escape($content) . "',code='". $this->db->escape($code) . "',store_id ='".(int)$this->session->data['store_id']."', date_added = NOW()");
		
		return  $this->db->getLastId();
	}

	public function deleteMailtempMail($mail_history_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "mail_history WHERE mail_history_id = '" . (int)$mail_history_id . "'");
	}

	public function getMailtempMail($mail_history_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history WHERE mail_history_id = '" . (int)$mail_history_id . "'");
		return $query->row;
	}
	
	public function getMailtempMailsByMail($mail) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history  WHERE who_mail = '" . $mail. "'");

		return isset($query->rows)?$query->rows:false;
	}
	
	public function getMailtempCustomerProductsforMail($mail){
		
		$query = $this->db->query("SELECT *,op.order_id as orderId,(SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id  AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS orderStatus, op.product_id as productId , pd.name as productName  FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id=op.product_id) LEFT JOIN " . DB_PREFIX . "order o ON (o.order_id=op.order_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE o.email = '" . $mail. "'AND o.store_id='".(int)$this->session->data['store_id']."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.product_id ORDER BY o.date_added DESC");

		return $query->rows;
	}
	
	public function getMailtempMailCountByMail($mail) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mail_history  WHERE who_mail = '" . $mail. "'");

		return $query->row['total'];
	}
	
	public function getMailtempCampaignMailByMail($mail,$order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_personal  WHERE email = '" . $mail. "' AND order_id='".(int)$order_id."'");

		return $query->rows;
	}
	
	public function getMailtempMails($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "mail_history WHERE store_id ='".(int)$this->session->data['store_id']."'";

			$sort_data = array(
				'mail_subject',
				'date_added'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY mail_subject";
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
			
			//$mailtemp_data = $this->cache->get('mail_history');
			//if (!$mailtemp_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "mail_history  WHERE store_id ='".(int)$this->session->data['store_id']."' ORDER BY date_added");

				$mailtemp_data = $query->rows;

			//	$this->cache->set('mail_history', $mailtemp_data);
			//}

			return $mailtemp_data;
		}
	}
	
	public function getTotalMailtempMails() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "mail_history");

		return $query->row['total'];
	}
	
	public function installMailtemp(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."mailtemp'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "mail_history` (`mail_history_id` int(11) NOT NULL,`store_id` int(11) NOT NULL DEFAULT 0,`who_mail` varchar(100) NOT NULL,`mail_subject` varchar(200) NOT NULL,`content` text NOT NULL,`code` varchar(20) NOT NULL,`module` varchar(20) NOT NULL,`date_added` datetime NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "mailtemp` (`mailtemp_id` int(11) NOT NULL,`bottom` int(1) NOT NULL DEFAULT 0,`sort_order` int(3) NOT NULL DEFAULT 0,`status` tinyint(1) NOT NULL DEFAULT 1,`type` int(1) NOT NULL DEFAULT 0,`code` varchar(30) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "mailtemp_description` (`mailtemp_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(64) NOT NULL,`description` text NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  = "CREATE TABLE `" . DB_PREFIX . "mailtemp_to_store` (`mailtemp_id` int(11) NOT NULL,`store_id` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mail_history` ADD PRIMARY KEY (`mail_history_id`);";
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mailtemp` ADD PRIMARY KEY (`mailtemp_id`);";
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mailtemp_description` ADD PRIMARY KEY (`mailtemp_id`,`language_id`);";
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mailtemp_to_store` ADD PRIMARY KEY (`mailtemp_id`,`store_id`);";
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mail_history` MODIFY `mail_history_id` int(11) NOT NULL AUTO_INCREMENT;";
			$sql[]  = "ALTER TABLE `" . DB_PREFIX . "mailtemp` MODIFY `mailtemp_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}