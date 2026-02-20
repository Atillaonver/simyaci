<?php
namespace Opencart\Catalog\Model\Bytao;
class Artcategory extends \Opencart\System\Engine\Model {	
	
	public function getArtCategory($art_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "art_category c LEFT JOIN " . DB_PREFIX . "art_category_description cd ON (c.art_category_id = cd.art_category_id) WHERE c.art_category_id = '" . (int)$art_category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row;
	}

	public function getArtCategories($parent_id = 0,$top = false) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category c LEFT JOIN " . DB_PREFIX . "art_category_description cd ON (c.art_category_id = cd.art_category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1'".($top?" AND c.top=1":"")." ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}

	
	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

		return $query->row['total'];
	}

}