<?php
namespace Opencart\Admin\Model\Bytao;
class Document extends \Opencart\System\Engine\Model {
	public function addDocument(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "document` SET `simage` = '" . $this->db->escape((string)$data['simage']) . "',`bimage` = '" . $this->db->escape((string)$data['bimage']) . "',`filename` = '" . $this->db->escape((string)$data['filename']) . "', `mask` = '" . $this->db->escape((string)$data['mask']) . "', `date_added` = NOW()");

		$document_id = $this->db->getLastId();

		foreach ($data['document_description'] as $language_id => $value) {
			$title = isset($value['title'])?", `title` = '" . (string)$this->db->escape($value['title']) . "'":"";
			$this->db->query("INSERT INTO `" . DB_PREFIX . "document_description` SET `document_id` = '" . (int)$document_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'".$title);
		}
		
		if (isset($data['document_store'])) {
			foreach ($data['document_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "document_to_store` SET `document_id` = '" . (int)$document_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}
		
		return $document_id;
	}

	public function editDocument(int $document_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "document` SET `simage` = '" . $this->db->escape((string)$data['simage']) . "',`bimage` = '" . $this->db->escape((string)$data['bimage']) . "',`filename` = '" . $this->db->escape((string)$data['filename']) . "', `mask` = '" . $this->db->escape((string)$data['mask']) . "' WHERE `document_id` = '" . (int)$document_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "document_description` WHERE `document_id` = '" . (int)$document_id . "'");

		foreach ($data['document_description'] as $language_id => $value) {
			$title = isset($value['title'])?", `title` = '" . (string)$this->db->escape($value['title']) . "'":"";
			$this->db->query("INSERT INTO `" . DB_PREFIX . "document_description` SET `document_id` = '" . (int)$document_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "'".$title);
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "document_to_store` WHERE `document_id` = '" . (int)$document_id . "'");

		if (isset($data['document_store'])) {
			foreach ($data['document_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "document_to_store` SET `document_id` = '" . (int)$document_id . "', `store_id` = '" . (int)$store_id . "'");
			}
		}
	}

	public function deleteDocument(int $document_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "document` WHERE `document_id` = '" . (int)$document_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "document_description` WHERE `document_id` = '" . (int)$document_id . "'");
	}

	public function getDocument(int $document_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "document` d LEFT JOIN `" . DB_PREFIX . "document_description` dd ON (d.`document_id` = dd.`document_id`) WHERE d.`document_id` = '" . (int)$document_id . "' AND dd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getDocuments(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "document` d LEFT JOIN `" . DB_PREFIX . "document_description` dd ON (d.`document_id` = dd.`document_id`) LEFT JOIN `" . DB_PREFIX . "document_to_store` d2s ON (d.`document_id` = d2s.`document_id`) WHERE dd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND d2s.store_id = '" . (int)$store_id . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND dd.`name` LIKE '" . $this->db->escape((string)$data['filter_name'] . '%') . "'";
		}

		$sort_data = [
			'd.sort_order',
			'dd.name',
			'd.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY d.`sort_order`";
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

	public function getDocumentDescriptions(int $document_id): array {
		$document_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "document_description` WHERE `document_id` = '" . (int)$document_id . "'");

		foreach ($query->rows as $result) {
			$document_description_data[$result['language_id']] = ['name' => $result['name'],'title' => $result['title']];
		}

		return $document_description_data;
	}

	public function getDocumentStores(int $document_id): array {
		$document_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "document_to_store` WHERE `document_id` = '" . (int)$document_id . "'");

		foreach ($query->rows as $result) {
			$document_store_data[] = $result['store_id'];
		}

		return $document_store_data;
	}
	
	public function getTotalDocuments(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "document`");

		return (int)$query->row['total'];
	}
	
	public function sortDocument(int $document_id,int $sort_order):void {
		$this->db->query("UPDATE " . DB_PREFIX . "document SET sort_order = '" . (int)$sort_order . "' WHERE document_id = '" . (int)$document_id . "'");
	}
	
	public function getDocumentReports(int $document_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT `ip`, `store_id`, `country`, `date_added` FROM `" . DB_PREFIX . "document_report` WHERE `document_id` = '" . (int)$document_id . "' ORDER BY `date_added` ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalDocumentReports(int $document_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "document_report` WHERE `document_id` = '" . (int)$document_id . "'");

		return (int)$query->row['total'];
	}
}
