<?php
namespace Opencart\Admin\Model\Bytao;
class Campaign extends \Opencart\System\Engine\Model {
	
	public function addCampaign(array $data = []): int {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "campaign SET name = '" . $this->db->escape($data['name']) . "', discount = '" . (float)$data['discount'] . "', campaign_type = '" . $this->db->escape($data['campaign_type']) . "',out_type = '" . (int)$data['out_type'] . "',type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', status = '" . (int)$data['status'] . "', buycat = '" . (int)$data['buycat'] . "', getcat = '" . (int)$data['getcat'] . "', date_added = NOW()");

		$campaign_id = $this->db->getLastId();
		$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_to_store SET store_id='" . (int)$this->session->data['store_id'] . "',campaign_id = '" . (int)$campaign_id . "'");
		
		

		if ($data['buycat']==0) {
				foreach ($data['campaign_buy_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_product SET campaign_id = '" . (int)$campaign_id . "', product_id = '" . (int)$product_id . "',buyed='1'");
				}
			}else{
				foreach ($data['campaign_buy_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_category SET campaign_id = '" . (int)$campaign_id . "', category_id = '" . (int)$category_id . "',buyed='1'");
				}
			}
			if ($data['getcat']==0) {
				foreach ($data['campaign_get_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_product SET campaign_id = '" . (int)$campaign_id . "', product_id = '" . (int)$product_id . "',buyed='0'");
				}
			}else{
				
				foreach ($data['campaign_get_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_category SET campaign_id = '" . (int)$campaign_id . "', category_id = '" . (int)$category_id . "',buyed='0'");
				}
			}
		
		return $campaign_id;
	}

	public function editCampaign(int $campaign_id,array $data = []):void {
		
		$this->db->query("UPDATE " . DB_PREFIX . "campaign SET name = '" . $this->db->escape($data['name']) . "',  discount = '" . (float)$data['discount'] . "', campaign_type = '" . $this->db->escape($data['campaign_type']) . "',out_type = '" . (int)$data['out_type'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', status = '" . (int)$data['status'] . "', buycat = '" . (int)$data['buycat'] . "', getcat = '" . (int)$data['getcat'] . "' WHERE campaign_id = '" . (int)$campaign_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign_product WHERE campaign_id = '" . (int)$campaign_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign_category WHERE campaign_id = '" . (int)$campaign_id . "'");
		
			if ($data['buycat']==0) {
				foreach ($data['campaign_buy_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_product SET campaign_id = '" . (int)$campaign_id . "', product_id = '" . (int)$product_id . "',buyed='1'");
				}
			}else{
				foreach ($data['campaign_buy_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_category SET campaign_id = '" . (int)$campaign_id . "', category_id = '" . (int)$category_id . "',buyed='1'");
				}
			}
			if ($data['getcat']==0) {
				foreach ($data['campaign_get_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_product SET campaign_id = '" . (int)$campaign_id . "', product_id = '" . (int)$product_id . "',buyed='0'");
				}
			}else{
				
				foreach ($data['campaign_get_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "campaign_category SET campaign_id = '" . (int)$campaign_id . "', category_id = '" . (int)$category_id . "',buyed='0'");
				}
			}

		
	}

	public function deleteCampaign(int $campaign_id):void {
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign WHERE campaign_id = '" . (int)$campaign_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign_product WHERE campaign_id = '" . (int)$campaign_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign_category WHERE campaign_id = '" . (int)$campaign_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "campaign_history WHERE campaign_id = '" . (int)$campaign_id . "'");

	}

	public function getCampaign(int$campaign_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "campaign WHERE campaign_id = '" . (int)$campaign_id . "'");

		return $query->row;
	}
	
	public function getCampaignByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "campaign WHERE code = '" . (int)$code . "'");

		return $query->row;
	}

	public function getCampaigns($data = []):array {
		$sql = "SELECT c.campaign_id, c.name, c.campaign_type, c.discount, c.date_start, c.date_end, c.status FROM " . DB_PREFIX . "campaign c LEFT JOIN " . DB_PREFIX . "campaign_to_store cc ON (cc.campaign_id = c.campaign_id) WHERE cc.store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = [
			'c.name',
			'c.code',
			'c.discount',
			'c.date_start',
			'c.date_end',
			'c.status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY c.name";
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
	}

	public function getCampaignProducts(int $campaign_id,int $buyed):array  {
		$campaign_product_data = [];
		$SQL = "SELECT cp.campaign_id,cp.product_id,pd.name FROM " . DB_PREFIX . "campaign_product cp LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = cp.product_id ) WHERE cp.campaign_id = '" . (int)$campaign_id . "' AND cp.buyed ='".(int)$buyed."' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
		
		$query = $this->db->query($SQL);

		if(isset($query->rows)){
			foreach ($query->rows as $result) {
				$campaign_product_data[$result['product_id']] = $result['name'];
			}
		}
		

		return $campaign_product_data;
	}
	
	public function getCampaignCategories(int $campaign_id,int $buyed):array {
		$campaign_category_data = [];

		$query = $this->db->query("SELECT cc.category_id,cd.name FROM " . DB_PREFIX . "campaign_category cc LEFT JOIN " . DB_PREFIX . "category_description cd ON (cc.category_id = cd.category_id ) WHERE cc.campaign_id = '" . (int)$campaign_id . "' AND cc.buyed ='".(int)$buyed."'  AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");
		if(isset($query->rows)){
			foreach ($query->rows as $result) {
				$campaign_category_data[$result['category_id']] = $result['name'] ;
			}
		}

		return $campaign_category_data;
	}
	
	public function getTotalCampaigns():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "campaign c LEFT JOIN " . DB_PREFIX . "campaign_to_store cc ON (cc.campaign_id = c.campaign_id) WHERE cc.store_id='" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function getCampaignHistories(int $campaign_id, $start = 0, $limit = 10):array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "campaign_history ch LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id) WHERE ch.campaign_id = '" . (int)$campaign_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return isset($query->rows)?$query->rows:[];
	}

	public function getTotalCampaignHistories(int $campaign_id):int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "campaign_history WHERE campaign_id = '" . (int)$campaign_id . "'");

		return $query->row['total'];
	}

	public function getCampaignStores(int $campaign_id):array {
		$campaign_store_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "campaign_to_store WHERE campaign_id = '" . (int)$campaign_id . "'");

		foreach ($query->rows as $result) {
			$campaign_store_data[] = $result['store_id'];
		}

		return $campaign_store_data;
	}

}
