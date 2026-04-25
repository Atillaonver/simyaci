<?php
namespace Opencart\Admin\Model\Bytao;
class ProjectCategory extends \Opencart\System\Engine\Model {
	
	public function addProjectCategory(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "project_category` SET `parent_id` = '" . (int)$data['parent_id'] . "', store_id = '" . (int)$this->session->data['store_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW(), `date_added` = NOW()");

		$project_category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "project_category` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `project_category_id` = '" . (int)$project_category_id . "'");
		}

		foreach ($data['project_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "project_category_description` SET `project_category_id` = '" . (int)$project_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['project_category_seo_url'])) {
			foreach ($data['project_category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'project_category_id', `value` = '" . (int)$project_category_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		return $project_category_id;
	}

	public function editProjectCategory(int $project_category_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "project_category` SET `parent_id` = '" . (int)$data['parent_id'] . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW() WHERE `project_category_id` = '" . (int)$project_category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "project_category` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `project_category_id` = '" . (int)$project_category_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "project_category_description` WHERE `project_category_id` = '" . (int)$project_category_id . "'");

		foreach ($data['project_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "project_category_description` SET `project_category_id` = '" . (int)$project_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'project_category_id' AND `value` = '" . (int)$project_category_id . "'");
		if (isset($data['project_category_seo_url'])) {
			foreach ($data['project_category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'project_category_id', `value` = '" . (int)$project_category_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
	}

	public function deleteProjectCategory(int $project_category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "project_category` WHERE `project_category_id` = '" . (int)$project_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "project_category_description` WHERE `project_category_id` = '" . (int)$project_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'project_category_id' AND `value` = '" . (int)$project_category_id  . "'");
		
		// Cascading delete - remove child categories
		$query = $this->db->query("SELECT `project_category_id` FROM `" . DB_PREFIX . "project_category` WHERE `parent_id` = '" . (int)$project_category_id . "'");
		foreach ($query->rows as $result) {
			$this->deleteProjectCategory($result['project_category_id']);
		}
	}

	public function getProjectCategory(int $project_category_id): array {
		$query = $this->db->query("SELECT DISTINCT *,(SELECT `name` FROM `" . DB_PREFIX . "project_category_description` WHERE `project_category_id` = pc.`project_category_id` AND `language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY `project_category_id`) AS `path` FROM `" . DB_PREFIX . "project_category` pc LEFT JOIN `" . DB_PREFIX . "project_category_description` pcd ON (pc.`project_category_id` = pcd.`project_category_id`) WHERE pc.`project_category_id` = '" . (int)$project_category_id . "' AND pcd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProjectCategories(array $data = []): array {
		$sql = "SELECT pc.`project_category_id` AS `project_category_id`, cd2.`name`,pc.sort_order FROM `" . DB_PREFIX . "project_category` pc LEFT JOIN `" . DB_PREFIX . "project_category_description` cd2 ON (pc.`project_category_id` = cd2.`project_category_id`) WHERE cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND pc.store_id = '" . (int)$this->session->data['store_id'] . "' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.`name` LIKE '" . $this->db->escape('%' . (string)$data['filter_name'] . '%') . "'";
		}

		$sql .= " GROUP BY pc.`project_category_id`";

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

	public function getProjectCategoryDescriptions(int $project_category_id): array {
		$project_category_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "project_category_description` WHERE `project_category_id` = '" . (int)$project_category_id . "'");

		foreach ($query->rows as $result) {
			$project_category_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $project_category_description_data;
	}

	public function getProjectCategorySeoUrls(int $project_category_id): array {
		$project_category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '" . (int)$this->session->data['store_id'] . "' AND `key` = 'project_category_id' AND `value` = '" . (int)$project_category_id . "'");

		foreach ($query->rows as $result) {
			$project_category_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $project_category_seo_url_data;
	}
	
	public function getTotalProjectCategories(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "project_category` WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return (int)$query->row['total'];
	}
}
