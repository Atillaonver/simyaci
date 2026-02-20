<?php
namespace Opencart\Admin\Model\Bytao;
class Column extends \Opencart\System\Engine\Model {
	
	public function addColumn(array $data):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "column SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "',status = '" . (int)$data['status'] . "', store_id = '" .  (int)$this->session->data['store_id'] . "'");

		$column_id = $this->db->getLastId();

		$this->db->query("DELETE FROM " . DB_PREFIX . "column_image WHERE column_id = '" . (int)$column_id . "'");
		$sortOrder=1;
		if (isset($data['column_image'])) {
			foreach ($data['column_image'] as $l_id => $column_images) {
				foreach ($column_images as $column_image ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "column_image SET column_id = '" . (int)$column_id . "',language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($column_image['link'])?$column_image['link']:"") . "', description = '" . (isset($column_image['description'])? $this->db->escape($column_image['description']):'') . "', image = '" .  $this->db->escape(isset($column_image['image'])?$column_image['image']:"") . "', mobile_image = '" . (isset($column_image['mobile_image'])?$this->db->escape($column_image['mobile_image']):'') . "', column_parent_class = '" . (isset($column_image['column_parent_class'])?$this->db->escape($column_image['column_parent_class']):'' ). "',column_style = '" . (isset($sub['column_style'])? $this->db->escape($column_image['column_style']):'') . "', sort_order = '" . (int)$sortOrder . "'");
					
					$parrent_id = $this->db->getLastId();
					$subOrder=1;
					if (isset($column_image['subs'])) {
						foreach ($column_image['subs'] as $sub) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "column_image SET column_id = '" . (int)$column_id . "',parent_id = '" . (int)$parrent_id . "', language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($sub['link'])?$sub['link']:"") . "', column_parent_class = '" . (isset($sub['column_parent_class'])?$this->db->escape($sub['column_parent_class']):'' ). "',column_style = '" . (isset($sub['column_style'])? $this->db->escape($sub['column_style']):'') . "',description = '" . (isset($sub['description'])? $this->db->escape($sub['description']):'') . "', image = '" .  $this->db->escape($sub['image']) . "', sort_order = '" . (int)$subOrder . "'");
							$subOrder++;
						}
					}
					$sortOrder++;
				}
			}
		}

		return $column_id;
	}

	public function editColumn(int $column_id, array $data):void {
		
		$this->db->query("UPDATE " . DB_PREFIX . "column SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', status = '" . (int)$data['status'] . "' WHERE column_id = '" . (int)$column_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "column_image WHERE column_id = '" . (int)$column_id . "'");
		$sortOrder=1;
		if (isset($data['column_image'])) {
			foreach ($data['column_image'] as $l_id => $column_images) {
				
				foreach ($column_images as $column_image ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "column_image SET column_id = '" . (int)$column_id . "',language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($column_image['link'])?$column_image['link']:"") . "', description = '" . (isset($column_image['description'])? $this->db->escape($column_image['description']):'') . "', image = '" .  $this->db->escape(isset($column_image['image'])?$column_image['image']:"") . "', mobile_image = '" . (isset($column_image['mobile_image'])?$this->db->escape($column_image['mobile_image']):'') . "', column_parent_class = '" . (isset($column_image['column_parent_class'])?$this->db->escape($column_image['column_parent_class']):'' ). "',column_style = '" . (isset($column_image['column_style'])? $this->db->escape($column_image['column_style']):'') . "', sort_order = '" . (int)$sortOrder . "'");
					
					$parrent_id = $this->db->getLastId();
					$subOrder=1;
					if (isset($column_image['subs'])) {
						foreach ($column_image['subs'] as $sub) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "column_image SET column_id = '" . (int)$column_id . "',parent_id = '" . (int)$parrent_id . "', language_id = '" . (int)$l_id . "', link = '" .  $this->db->escape(isset($sub['link'])?$sub['link']:"") . "', column_parent_class = '" . (isset($sub['column_parent_class'])?$this->db->escape($sub['column_parent_class']):'' ). "',column_style = '" . (isset($sub['column_style'])? $this->db->escape($sub['column_style']):'') . "',description = '" . (isset($sub['description'])? $this->db->escape($sub['description']):'') . "', image = '" .  $this->db->escape($sub['image']) . "', sort_order = '" . (int)$subOrder . "'");
							$subOrder++;
						}
					}
					$sortOrder++;
				}
			}
		}
	}

	public function deleteColumn($column_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "column WHERE column_id = '" . (int)$column_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "column_image WHERE column_id = '" . (int)$column_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "column_image_description WHERE column_id = '" . (int)$column_id . "'");
	}

	public function getColumn($column_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "column WHERE column_id = '" . (int)$column_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getBanners($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "column WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

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
		$sql = "SELECT * FROM " . DB_PREFIX . "column WHERE parent_id='".$parent_id."' AND store_id = '" . (int)$this->session->data['store_id'] . "'";

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

	public function getBannerImages($column_id):array {
		$column_image_data = [];

		$column_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "column_image WHERE column_id = '" . (int)$column_id . "' AND parent_id='0' ORDER BY sort_order ASC");

		foreach ($column_image_query->rows as $column_image) {
			$column_image_data[] = [
				'description' => $column_image['description'],
				'column_image_id'          => $column_image['column_image_id'],
				'column_parent_class'          => $column_image['column_parent_class'],
				'column_style'          => $column_image['column_style'],
				'link'                     => $column_image['link'],
				'image'                    => $column_image['image'],
				'mobile_image'                    => $column_image['mobile_image'],
				'sort_order'               => $column_image['sort_order']
			];
		}

		return $column_image_data;
	}
	
	public function getSubBannerImages($parent_id):array {
		$column_image_data = [];

		$column_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "column_image WHERE parent_id = '" . (int)$parent_id . "' ORDER BY sort_order ASC");

		foreach ($column_image_query->rows as $column_image) {
			$column_image_data[] = [
				'description' => $column_image['description'],
				'column_parent_class'      => $column_image['column_parent_class'],
				'column_style'      		=> $column_image['column_style'],
				'position'                 => $column_image['position'],
				'link'                     => $column_image['link'],
				'image'                    => $column_image['image'],
				'mobile_image'                    => $column_image['mobile_image'],
				'sort_order'               => $column_image['sort_order']
			];
		}

		return $column_image_data;
	}

	public function getTotalBanners():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "column WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isInstore($column_id):bool{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "column WHERE column_id = '" . (int)$column_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

}