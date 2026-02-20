<?php
namespace Opencart\Catalog\Model\Bytao;
class Document extends \Opencart\System\Engine\Model {
	
	public function getDocuments(): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "document` d LEFT JOIN `" . DB_PREFIX . "document_description` dd ON (d.`document_id` = dd.`document_id`) LEFT JOIN `" . DB_PREFIX . "document_to_store` d2s ON (d.`document_id` = d2s.`document_id`) WHERE dd.`language_id` = '" . (int)$this->config->get('config_language_id'). "' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY d.sort_order ASC";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getDocument(int $document_id): array {
		$implode = [];
		$query = $this->db->query("SELECT `filename`, `mask` FROM `" .DB_PREFIX . "document` WHERE `document_id` = '" . (int)$document_id . "'");
		return $query->row;
	}

	public function addReport(int $document_id, string $ip, string $country = ''): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "download_report` SET `download_id` = '" . (int)$document_id . "', `store_id` = '" . (int)$this->config->get('config_store_id') . "', `ip` = '" . $this->db->escape($ip) . "', `country` = '" . $this->db->escape($country) . "', `date_added` = NOW()");
	}
}
