<?php
namespace Opencart\Admin\Model\Design;
class Theme extends \Opencart\System\Engine\Model {
	
	public function editTheme(string $route, string $code): void {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$store_id . "' AND `route` = '" . $this->db->escape($route) . "'");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "theme` SET `store_id` = '" . (int)$store_id . "', `route` = '" . $this->db->escape($route) . "', `code` = '" . $this->db->escape($code) . "', `date_added` = NOW()");
	}

	public function deleteTheme(int $theme_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "theme` WHERE `theme_id` = '" . (int)$theme_id . "'");
	}

	public function getTheme(string $route): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "theme` WHERE `store_id` = '" . (int)$store_id . "' AND `route` = '" . $this->db->escape($route) . "'");

		return $query->row;
	}
	
	public function getThemes(int $start = 0, int $limit = 10): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		if ($start < 0) {
			$start = 0;
		}

		//if ($limit < 1) {
		//	$limit = 10;
		//}		
		
		if($start == 0 && $limit == 0 ){
			$addSql = '';
		}else{
			$addSql = 'LIMIT ' . (int)$start . "," . (int)$limit;
		}
		
		$sql = "SELECT *, (SELECT `name` FROM `" . DB_PREFIX . "store` s WHERE s.`store_id` = t.`store_id`) AS `store` FROM `" . DB_PREFIX . "theme` t WHERE t.store_id ='".(int)$store_id."' ORDER BY t.`date_added` DESC ".$addSql;
		$query = $this->db->query($sql);

		return $query->rows;
	}	
	
	public function getTotalThemes(): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "theme` WHERE store_id='".(int)$store_id."'");

		return (int)$query->row['total'];
	}	
}