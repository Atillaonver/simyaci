<?php
namespace Opencart\Catalog\Model\Bytao;
class Member extends \Opencart\System\Engine\Model {
	
	public function getMember($member_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "member  WHERE member_id = '" . (int)$member_id . "'");

		return $query->row;
	}
		
	public function getMembers($limit=0) {
		$member_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "member a LEFT JOIN " . DB_PREFIX . "member_description ad ON (a.member_id = ad.member_id ) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.store_id='".(int)$this->config->get('config_store_id')."' ORDER BY a.sort_order ASC".($limit?' LIMIT '.$limit:''));

		return isset($member_query->rows)?$member_query->rows:false;
	}
	
}