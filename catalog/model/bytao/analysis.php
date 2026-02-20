<?php
class ModelBytaoAnalysis extends Model {
	public function getAnalysis($analysis_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "analysis  WHERE analysis_id = '" . (int)$analysis_id . "'");

		return $query->row;
	}
		
	public function getAnalysiss($limit=0) {
		$ode_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analysis a LEFT JOIN " . DB_PREFIX . "analysis_description ad ON (a.analysis_id = ad.analysis_id ) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY a.sort_order ASC".($limit?' LIMIT '.$limit:''));

		return isset($ode_query->rows)?$ode_query->rows:false;
	}
	
}