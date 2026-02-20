<?php
namespace Opencart\Admin\Model\Bytao;
class ProdCat extends \Opencart\System\Engine\Model {
	
	public function addProdCat(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "prod_cat` SET `parent_id` = '" . (int)$data['parent_id'] . "', store_id = '" . (int)$this->session->data['store_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW(), `date_added` = NOW()");

		$prod_cat_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "prod_cat` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");
		}

		foreach ($data['prod_cat_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "prod_cat_description` SET `prod_cat_id` = '" . (int)$prod_cat_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['prod_cat_seo_url'])) {
			foreach ($data['prod_cat_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'prod_cat_id', `value` = '" . (int)$prod_cat_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		return $prod_cat_id;
	}

	public function editProdCat(int $prod_cat_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "prod_cat` SET `parent_id` = '" . (int)$data['parent_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW() WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "prod_cat` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "prod_cat_description` WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");

		foreach ($data['prod_cat_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "prod_cat_description` SET `prod_cat_id` = '" . (int)$prod_cat_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'prod_cat_id' AND `value` = '" . (int)$prod_cat_id . "'");
		if (isset($data['prod_cat_seo_url'])) {
			foreach ($data['prod_cat_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'prod_cat_id', `value` = '" . (int)$prod_cat_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
	}

	public function deleteProdCat(int $prod_cat_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "prod_cat` WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "prod_cat_description` WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'prod_cat_id' AND `value` = '" . (int)$prod_cat_id  . "'");
		foreach ($query->rows as $result) {
			$this->deleteProdCat($result['prod_cat_id']);
		}
	}

	public function getProdCat(int $prod_cat_id): array {
		$query = $this->db->query("SELECT DISTINCT *,(SELECT `name` FROM `" . DB_PREFIX . "prod_cat_description` WHERE `prod_cat_id` = pc.`prod_cat_id` AND `language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY `prod_cat_id`) AS `path` FROM `" . DB_PREFIX . "prod_cat` pc LEFT JOIN `" . DB_PREFIX . "prod_cat_description` pcd ON (pc.`prod_cat_id` = pcd.`prod_cat_id`) WHERE pc.`prod_cat_id` = '" . (int)$prod_cat_id . "' AND pcd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProdCats(array $data = []): array {
		$sql = "SELECT pc.`prod_cat_id` AS `prod_cat_id`, cd2.`name`,pc.sort_order FROM `" . DB_PREFIX . "prod_cat` pc LEFT JOIN `" . DB_PREFIX . "prod_cat_description` cd2 ON (pc.`prod_cat_id` = cd2.`prod_cat_id`) WHERE cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND pc.store_id = '" . (int)$this->session->data['store_id'] . "' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.`name` LIKE '" . $this->db->escape('%' . (string)$data['filter_name'] . '%') . "'";
		}

		$sql .= " GROUP BY pc.`prod_cat_id`";

		$sort_data = [
			'cd2.name',
			'pc.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY pc.`sort_order`";
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

	public function getProdCatDescriptions(int $prod_cat_id): array {
		$prod_cat_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "prod_cat_description` WHERE `prod_cat_id` = '" . (int)$prod_cat_id . "'");

		foreach ($query->rows as $result) {
			$prod_cat_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $prod_cat_description_data;
	}

	public function getProdCatSeoUrls(int $prod_cat_id): array {
		$prod_cat_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '" . (int)$this->session->data['store_id'] . "' AND `key` = 'prod_cat_id' AND `value` = '" . (int)$prod_cat_id . "'");

		foreach ($query->rows as $result) {
			$prod_cat_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $prod_cat_seo_url_data;
	}
	
	public function getTotalProdCats(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "prod_cat` WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return (int)$query->row['total'];
	}
}
