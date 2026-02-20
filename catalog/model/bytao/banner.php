<?php
namespace Opencart\Catalog\Model\Bytao;
class Banner extends \Opencart\System\Engine\Model {
	
	public function getBannerImages($banner_id) {
		$sql="SELECT * FROM " . DB_PREFIX . "bybanner_image bi WHERE bi.banner_id = '" . (int)$banner_id . "' AND bi.parent_id=0 AND bi.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bi.sort_order ASC";
		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getSubBannerImages($parrent_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bybanner_image bi LEFT JOIN " . DB_PREFIX . "bybanner_image_description bid ON (bi.banner_image_id  = bid.banner_image_id) WHERE bi.parent_id = '" . (int)$parrent_id . "' AND bid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bi.sort_order ASC");

		return $query->rows;
	}
	
	public function getBanner($banner_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bybanner WHERE banner_id = '" . (int)$banner_id . "' and status = '1' ");

		return $query->row;
	}
	
	public function getBannerProds($banner_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bybanner WHERE banner_id = '" . (int)$banner_id . "' and status = '1' ");

		return $query->row;
	}
	
}