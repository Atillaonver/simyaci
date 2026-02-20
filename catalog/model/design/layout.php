<?php
namespace Opencart\Catalog\Model\Design;
class Layout extends \Opencart\System\Engine\Model {
	public function getLayout(string $route,int $store_id=0): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout_route` WHERE '" . $this->db->escape($route) . "' LIKE `route` AND `store_id` = '" . (int)$store_id. "' ORDER BY `route` DESC LIMIT 1");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
	
	public function getModules(int $layout_id, string $position): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout_module` WHERE `layout_id` = '" . (int)$layout_id . "' AND `position` = '" . $this->db->escape($position) . "' ORDER BY `sort_order`");
		
		return $query->rows;
	}
	
	public function getRoute(int $layout_id,int $store_id): string {
		$SQL ="SELECT `route` FROM `" . DB_PREFIX . "layout_route` WHERE `layout_id` = '" . (int)$layout_id . "' AND `store_id` = '".(int)$store_id."' LIMIT 1";
		
		$query = $this->db->query($SQL);
		
		return isset($query->row['route'])?$query->row['route']:'';
	}
}
