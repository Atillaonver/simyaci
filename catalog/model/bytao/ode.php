<?php
namespace Opencart\Catalog\Model\Bytao;
class Ode extends \Opencart\System\Engine\Model {
	
	public function getOde($award_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "ode  WHERE ode_id = '" . (int)$award_id . "'");

		return $query->row;
	}
		
	public function getOdes($limit=0) {
		$ode_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ode o LEFT JOIN " . DB_PREFIX . "ode_description od ON (o.ode_id = od.ode_id ) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND o.status='1' AND o.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY o.sort_order ASC".($limit?' LIMIT '.$limit:''));

		return isset($ode_query->rows)?$ode_query->rows:false;
	}
	
}