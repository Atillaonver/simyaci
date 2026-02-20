<?php
namespace Opencart\Catalog\Model\Bytao;
class Faq extends \Opencart\System\Engine\Model
 {
	public function getFaqQuestions() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq f LEFT JOIN " . DB_PREFIX . "faq_description fd ON (f.faq_id = fd.faq_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.status = '1' AND f.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY f.sort_order ASC");

		return $query->rows;
	}

	
}