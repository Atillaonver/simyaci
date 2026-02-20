<?php
class ModelBytaoAward extends Model {
	public function getAward($award_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "award  WHERE award_id = '" . (int)$award_id . "'");

		return $query->row;
	}
		
	public function getAwards($limit=0) {
		$ode_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "award a LEFT JOIN " . DB_PREFIX . "award_description ad ON (a.award_id = ad.award_id ) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY a.sort_order ASC".($limit?' LIMIT '.$limit:''));

		return isset($ode_query->rows)?$ode_query->rows:false;
	}
	
}