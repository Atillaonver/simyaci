<?php
namespace Opencart\Admin\Model\Bytao;
class Banner extends \Opencart\System\Engine\Model {
	
	public function addBanner(array $data):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "bybanner SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "',status = '" . (int)$data['status'] . "', store_id = '" .  (int)$this->session->data['store_id'] . "'");

		$banner_id = $this->db->getLastId();

		$this->db->query("DELETE FROM " . DB_PREFIX . "bybanner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$sortOrder=1;
		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $l_id => $banner_images) {
				foreach ($banner_images as $banner_image ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "bybanner_image SET banner_id = '" . (int)$banner_id . "',language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($banner_image['link'])?$banner_image['link']:"") . "', description = '" . (isset($banner_image['description'])? $this->db->escape($banner_image['description']):'') . "', image = '" .  $this->db->escape(isset($banner_image['image'])?$banner_image['image']:"") . "', mobile_image = '" . (isset($banner_image['mobile_image'])?$this->db->escape($banner_image['mobile_image']):'') . "', banner_parent_class = '" . (isset($banner_image['banner_parent_class'])?$this->db->escape($banner_image['banner_parent_class']):'' ). "',banner_style = '" . (isset($sub['banner_style'])? $this->db->escape($banner_image['banner_style']):'') . "', sort_order = '" . (int)$sortOrder . "'");
					
					$parrent_id = $this->db->getLastId();
					$subOrder=1;
					if (isset($banner_image['sub'])) {
						foreach ($banner_image['sub'] as $sub) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "bybanner_image SET banner_id = '" . (int)$banner_id . "',parent_id = '" . (int)$parrent_id . "', language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($sub['link'])?$sub['link']:"") . "', banner_parent_class = '" . (isset($sub['banner_parent_class'])?$this->db->escape($sub['banner_parent_class']):'' ). "',banner_style = '" . (isset($sub['banner_style'])? $this->db->escape($sub['banner_style']):'') . "',description = '" . (isset($sub['description'])? $this->db->escape($sub['description']):'') . "', image = '" .  $this->db->escape($sub['image']) . "', type = '" . (int)$sub['type'] . "', position = '" . (int)(isset($sub['position'])?$sub['position']:0) . "', sort_order = '" . (int)$subOrder . "'");
							$subOrder++;
						}
					}
					$sortOrder++;
				}
			}
		}

		return $banner_id;
	}

	public function editBanner(int $banner_id, array $data):void {
		
		$this->db->query("UPDATE " . DB_PREFIX . "bybanner SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', status = '" . (int)$data['status'] . "' WHERE banner_id = '" . (int)$banner_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "bybanner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$sortOrder=1;
		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $l_id => $banner_images) {
				foreach ($banner_images as $banner_image ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "bybanner_image SET banner_id = '" . (int)$banner_id . "',language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($banner_image['link'])?$banner_image['link']:"") . "', description = '" . (isset($banner_image['description'])? $this->db->escape($banner_image['description']):'') . "', image = '" .  $this->db->escape(isset($banner_image['image'])?$banner_image['image']:"") . "', mobile_image = '" . (isset($banner_image['mobile_image'])?$this->db->escape($banner_image['mobile_image']):'') . "', banner_parent_class = '" . (isset($banner_image['banner_parent_class'])?$this->db->escape($banner_image['banner_parent_class']):'' ). "',banner_style = '" . (isset($banner_image['banner_style'])? $this->db->escape($banner_image['banner_style']):'') . "', sort_order = '" . (int)$sortOrder . "'");
					
					$parrent_id = $this->db->getLastId();
					$subOrder=1;
					if (isset($banner_image['sub'])) {
						foreach ($banner_image['sub'] as $sub) {
							$SQL = "INSERT INTO " . DB_PREFIX . "bybanner_image SET banner_id = '" . (int)$banner_id . "',parent_id = '" . (int)$parrent_id . "', language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($sub['link'])?$sub['link']:"") . "', banner_parent_class = '" . (isset($sub['banner_parent_class'])?$this->db->escape($sub['banner_parent_class']):'' ). "',banner_style = '" . (isset($sub['banner_style'])? $this->db->escape($sub['banner_style']):'') . "',description = '" . (isset($sub['description'])? $this->db->escape($sub['description']):'') . "', image = '" .  $this->db->escape(isset($sub['image'])?$sub['image']:"")  . "', type = '" . (int)(isset($sub['type'])?$sub['type']:0) . "', position = '" . (int)(isset($sub['position'])?$sub['position']:0) . "', sort_order = '" . (int)$subOrder . "'";
							
							$this->db->query($SQL);
							$subOrder++;
						}
					}
					$sortOrder++;
				}
			}
		}
	}

	public function deleteBanner($banner_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "bybanner WHERE banner_id = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "bybanner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "bybanner_image_description WHERE banner_id = '" . (int)$banner_id . "'");
	}

	public function getBanner($banner_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "bybanner WHERE banner_id = '" . (int)$banner_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getBanners($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "bybanner WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}

	public function getSubBanners(array $data = [],$parent_id=0):array {
		$sql = "SELECT * FROM " . DB_PREFIX . "bybanner WHERE parent_id='".$parent_id."' AND store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}

	public function getBannerImages($banner_id):array {
		$banner_image_data = [];

		$banner_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bybanner_image WHERE banner_id = '" . (int)$banner_id . "' AND parent_id='0' ORDER BY sort_order ASC");

		foreach ($banner_image_query->rows as $banner_image) {
			$banner_image_data[] = [
				'description' 			=> $banner_image['description'],
				'banner_image_id'       => $banner_image['banner_image_id'],
				'banner_parent_class'   => $banner_image['banner_parent_class'],
				'banner_class'   		=> $banner_image['banner_class'],
				'banner_style'          => $banner_image['banner_style'],
				'language_id'           => $banner_image['language_id'],
				'link'                  => $banner_image['link'],
				'type'                  => $banner_image['type'],
				'image'                 => $banner_image['image'],
				'mobile_image'          => $banner_image['mobile_image'],
				'sort_order'            => $banner_image['sort_order']
			];
		}

		return $banner_image_data;
	}
	
	public function getSubBannerImages($parent_id):array {
		$banner_image_data = [];

		$banner_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bybanner_image WHERE parent_id = '" . (int)$parent_id . "' ORDER BY sort_order ASC");

		foreach ($banner_image_query->rows as $banner_image) {
			$banner_image_data[] = [
				'description' 			   => $banner_image['description'],
				'banner_parent_class'      => $banner_image['banner_parent_class'],
				'banner_class'      	   => $banner_image['banner_class'],
				'banner_style'      	   => $banner_image['banner_style'],
				'position'                 => $banner_image['position'],
				'type'                     => $banner_image['type'],
				'link'                     => $banner_image['link'],
				'image'                    => $banner_image['image'],
				'mobile_image'             => $banner_image['mobile_image'],
				'sort_order'               => $banner_image['sort_order']
			];
		}

		return $banner_image_data;
	}

	public function getTotalBanners():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "bybanner WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isInstore($banner_id):bool{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "bybanner WHERE banner_id = '" . (int)$banner_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

}