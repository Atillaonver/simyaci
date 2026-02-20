<?php
namespace Opencart\Catalog\Model\Bytao;
class Firm extends \Opencart\System\Engine\Model {
	
	public function getFirm(int $firm_id):array  {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "firm p LEFT JOIN " . DB_PREFIX . "firm_description pd ON (p.firm_id = pd.firm_id) LEFT JOIN " . DB_PREFIX . "firm_to_store p2s ON (p.firm_id = p2s.firm_id) WHERE p.firm_id = '" . (int)$firm_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1'");

		return $query->row;
	}

	public function getFirms():array  {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "firm p LEFT JOIN " . DB_PREFIX . "firm_description pd ON (p.firm_id = pd.firm_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1' ORDER BY p.sort_order, LCASE(pd.title) ASC");

		return $query->rows;
	}

}