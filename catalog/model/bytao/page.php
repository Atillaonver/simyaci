<?php
namespace Opencart\Catalog\Model\Bytao;
class Page extends \Opencart\System\Engine\Model {
	
	public function getPage(int $page_id):array  {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "page p LEFT JOIN " . DB_PREFIX . "page_description pd ON (p.page_id = pd.page_id) LEFT JOIN " . DB_PREFIX . "page_to_store p2s ON (p.page_id = p2s.page_id) WHERE p.page_id = '" . (int)$page_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1'");

		return $query->row;
	}

	public function getPages():array  {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page p LEFT JOIN " . DB_PREFIX . "page_description pd ON (p.page_id = pd.page_id) LEFT JOIN " . DB_PREFIX . "page_to_store p2s ON (p.page_id = p2s.page_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1' ORDER BY p.sort_order, LCASE(pd.title) ASC");

		return $query->rows;
	}

	public function getPageLayoutId($page_id):int {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_to_layout WHERE page_id = '" . (int)$page_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
	
}