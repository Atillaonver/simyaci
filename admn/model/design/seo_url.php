<?php
namespace Opencart\Admin\Model\Design;
class SeoUrl extends \Opencart\System\Engine\Model {
	
	public function addSeoUrl(string $key, string $value, string $keyword, int $store_id, int $language_id, int $sort_order = 0,string $title = '',string $route=''): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '" . $this->db->escape((string)$title) . "',`route` = '" . $this->db->escape((string)$route) . "', `sort_order` = '" . (int)$sort_order . "'");

		return $this->db->getLastId();
	}

	public function editSeoUrl(int $seo_url_id, string $key, string $value, string $keyword,int $language_id, int $sort_order = 0,string $title='',string $route=''): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `language_id` = '" . (int)$language_id . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', `keyword` = '" . $this->db->escape((string)$keyword) . "',`title` = '" . $this->db->escape((string)$title) . "',`route` = '" . $this->db->escape((string)$route) . "', `sort_order` = '" . (int)$sort_order . "' WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
	}
	
	public function deleteSeoUrl(int $seo_url_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
	}
	
	public function deleteSeoUrlsByKeyValue(string $key, string $value, int $store_id = 0, int $language_id = 0): void {
		$sql = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($key) . "' AND `value` LIKE '" . $this->db->escape($value) . "'";

		if ($store_id) {
			$sql .= " AND `store_id` = '" . (int)$store_id . "'";
		}

		if ($language_id) {
			$sql .= " AND `language_id` = '" . (int)$language_id . "'";
		}

		$this->db->query($sql);
	}

	public function deleteSeoUrlsByLanguageId(int $language_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `language_id` = '" . (int)$language_id . "'");
	}

	public function deleteSeoUrlsByStoreId(int $store_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '" . (int)$store_id . "'");
	}

	public function getSeoUrl(int $seo_url_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");

		return $query->row;
	}
	
	public function getSeoUrls(array $data = []): array {
		$storeId = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "store` `s` WHERE `s`.`store_id` = `su`.`store_id`) AS `store`, (SELECT `name` FROM `" . DB_PREFIX . "language` `l` WHERE `l`.`language_id` = `su`.`language_id`) AS `language` FROM `" . DB_PREFIX . "seo_url` `su`";
	
		$implode = [];

		if (!empty($data['filter_keyword'])) {
			$implode[] = "LCASE(`keyword`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_keyword'])) . "'";
		}

		if (!empty($data['filter_key'])) {
			$implode[] = "LCASE(`key`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_key'])) . "'";
		}

		if (!empty($data['filter_value'])) {
			$implode[] = "LCASE(`value`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_value'])) . "'";
		}

		
		$implode[] = "`store_id` = '" . (int)$storeId . "'";
		

		if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'keyword',
			'key',
			'value',
			'sort_order',
			'store_id',
			'language_id'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `key`";
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

	public function getTotalSeoUrls(array $data = []): int {
		$storeId = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "seo_url`";

		$implode = [];

		if (!empty($data['filter_title'])) {
			$implode[] = "`title` LIKE '" . $this->db->escape((string)$data['filter_title']) . "'";
		}

		if (!empty($data['filter_keyword'])) {
			$implode[] = "`keyword` LIKE '" . $this->db->escape((string)$data['filter_keyword']) . "'";
		}

		if (!empty($data['filter_key'])) {
			$implode[] = "`key` = '" . $this->db->escape((string)$data['filter_key']) . "'";
		}

		if (!empty($data['filter_route'])) {
			$implode[] = "`route` = '" . $this->db->escape((string)$data['filter_route']) . "'";
		}

		if (!empty($data['filter_value'])) {
			$implode[] = "`value` LIKE '" . $this->db->escape((string)$data['filter_value']) . "'";
		}

		$implode[] = "`store_id` = '" . (int)$storeId . "'";

		if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getSeoUrlByKeyValue(string $key, string $value, int $language_id): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:$store_id;
		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($key) . "' AND `value` = '" . $this->db->escape($value) . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'");

		return $query->row;
	}
	

	public function getSeoUrlByKeyword(string $keyword, int $language_id = 0): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:$store_id;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE (`keyword` = '" . $this->db->escape($keyword) . "' OR `keyword` LIKE '" . $this->db->escape('%/' . $keyword) . "') AND `store_id` = '" . (int)$store_id . "'";

		if ($language_id) {
			$sql .= " AND `language_id` = '" . (int)$language_id . "'";
		}

		$query = $this->db->query($sql);

		return $query->row;
	}
	
	public function getSeoUrlsByKeyValue(string $key, string $value): array {
		$seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = '" . $this->db->escape($key) . "' AND `value` LIKE '" . $this->db->escape($value) . "'");

		foreach ($query->rows as $result) {
			$seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $seo_url_data;
	}

	public function getSeoUrlsByStoreId(int $store_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '" . (int)$store_id . "'");

		return $query->rows;
	}
	
	public function getSeoUrlsByLanguageId(int $language_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `language_id` = '" . (int)$language_id . "'");

		return $query->rows;
	}

	
}
