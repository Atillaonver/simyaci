<?php
namespace Opencart\Admin\Model\Bytao;
class Artcategory extends \Opencart\System\Engine\Model {

	private $DBs = 'art_category,art_category_description,art_category_path';
	public function getDBNames(){
		return $this->DBs;
	}
	
	public function addArtcategory($data) {
		$this->event->trigger('pre.admin.art_category.add', $data);
		$sql="INSERT INTO " . DB_PREFIX . "art_category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(),store_id='".(int)$this->session->data['store_id']."', date_added = NOW()";
		
		$this->db->query($sql);

		$art_category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "art_category SET image = '" . $this->db->escape($data['image']) . "' WHERE art_category_id = '" . (int)$art_category_id . "'");
		}

		if (isset($data['art_category_description'])) {
			foreach ($data['art_category_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "art_category_description SET art_category_id = '" . (int)$art_category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
			}
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "art_category_path` SET `art_category_id` = '" . (int)$art_category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "art_category_path` SET `art_category_id` = '" . (int)$art_category_id . "', `path_id` = '" . (int)$art_category_id . "', `level` = '" . (int)$level . "'");

		


		if (isset($data['art_category_seo_url'])) {
			foreach ($data['art_category_seo_url'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET `key` = 'art_category_id', `value`='" . (int)$art_category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "',store_id='".(int)$this->session->data['store_id']."',route='bytao/art_category'");
		
			}
		}

		
		return $art_category_id;
	}

	public function editArtcategory($art_category_id, $data) {
		
		$this->db->query("UPDATE " . DB_PREFIX . "art_category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE art_category_id = '" . (int)$art_category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "art_category SET image = '" . $this->db->escape($data['image']) . "' WHERE art_category_id = '" . (int)$art_category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "art_category_description WHERE art_category_id = '" . (int)$art_category_id . "'");

		foreach ($data['art_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "art_category_description SET art_category_id = '" . (int)$art_category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE path_id = '" . (int)$art_category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $art_category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$art_category_path['art_category_id'] . "' AND level < '" . (int)$art_category_path['level'] . "'");

				$path = [];

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$art_category_path['art_category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "art_category_path` SET art_category_id = '" . (int)$art_category_path['art_category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$art_category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "art_category_path` SET art_category_id = '" . (int)$art_category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "art_category_path` SET art_category_id = '" . (int)$art_category_id . "', `path_id` = '" . (int)$art_category_id . "', level = '" . (int)$level . "'");
		}


		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE `key` = 'art_category_id' AND `value`='" . (int)$art_category_id . "' AND store_id='".(int)$this->session->data['store_id']."'");

		if (isset($data['art_category_seo_url'])) {
			foreach ($data['art_category_seo_url'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET `key` = 'art_category_id', `value`='" . (int)$art_category_id . "', keyword = '" . $this->db->escape($value) . "', language_id='".(int)$language_id."',store_id='".(int)$this->session->data['store_id']."',route='bytao/art_category'");
		
			}
		}

	}

	public function deleteArtcategory($art_category_id) {
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_category_path WHERE art_category_id = '" . (int)$art_category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category_path WHERE path_id = '" . (int)$art_category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteArtcategory($result['art_category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "art_category WHERE art_category_id = '" . (int)$art_category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_category_description WHERE art_category_id = '" . (int)$art_category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_category_to_store WHERE art_category_id = '" . (int)$art_category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "art_to_category WHERE art_category_id = '" . (int)$art_category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE key = 'art_category_id' AND value='" . (int)$art_category_id . "'");

		
	}

	public function repairArtcategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $art_category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$art_category['art_category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "art_category_path` WHERE art_category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "art_category_path` SET art_category_id = '" . (int)$art_category['art_category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "art_category_path` SET art_category_id = '" . (int)$art_category['art_category_id'] . "', `path_id` = '" . (int)$art_category['art_category_id'] . "', level = '" . (int)$level . "'");

			$this->repairArtcategories($art_category['art_category_id']);
		}
	}

	public function getArtcategory($art_category_id) {
		$SQL = "SELECT DISTINCT *,";
		$SQL .= " (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;')";
		$SQL .= " FROM " . DB_PREFIX . "art_category_path cp ";
		$SQL .= "LEFT JOIN " . DB_PREFIX . "art_category_description cd1 ON (cp.path_id = cd1.art_category_id AND cp.art_category_id != cp.path_id)";
		$SQL .= " WHERE cp.art_category_id = c.art_category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$SQL .= " GROUP BY cp.art_category_id) AS path";
		//$SQL .= " (SELECT DISTINCT keyword FROM " . DB_PREFIX . "seo_url";
		//$SQL .= " WHERE `key` = 'art_category_id' AND `value`='" . (int)$art_category_id . "' AND store_id='".(int)$this->session->data['store_id']."' LIMIT 1) AS keyword";
		$SQL .= " FROM " . DB_PREFIX . "art_category c ";
		$SQL .= "LEFT JOIN " . DB_PREFIX . "art_category_description cd2 ON (c.art_category_id = cd2.art_category_id)";
		$SQL .= " WHERE c.art_category_id = '" . (int)$art_category_id . "' AND ";
		$SQL .= "cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		
		$query = $this->db->query($SQL);

		return $query->row;
	}

	public function getArtcategories($data = []) {
		
		$sql  = "SELECT * FROM " . DB_PREFIX . "art_category ac ";
		//$sql .= " GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name,";
		//$sql .= " c1.parent_id,";
		//$sql .= " c1.sort_order FROM " . DB_PREFIX . "art_category_path cp";
		//$sql .= " LEFT JOIN " . DB_PREFIX . "art_category c1 ON (cp.art_category_id = c1.art_category_id)";
		//$sql .= " LEFT JOIN " . DB_PREFIX . "art_category c2 ON (cp.path_id = c2.art_category_id)";
		$sql .= " LEFT JOIN " . DB_PREFIX . "art_category_description acd ON (ac.art_category_id = acd.art_category_id)";
		//$sql .= " LEFT JOIN " . DB_PREFIX . "art_category_to_store acd2s ON (ac.art_category_id = acd2s.art_category_id)";
		//$sql .= " LEFT JOIN " . DB_PREFIX . "art_category_description cd2 ON (cp.art_category_id = cd2.art_category_id)";
		$sql .= " WHERE acd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND";
		//$sql .= " cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND";
		$sql .= " ac.store_id='".(int)$this->session->data['store_id']."' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND acd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		//$sql .= " GROUP BY ac.art_category_id";

		$sort_data = array(
			
			'acd.name',
			'c2.parent_id',
			'c2.sort_order',
			'cd2.name'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
			
		} else {
			$sql .= " ORDER BY ac.sort_order";
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

		return $query->rows;
	}

	public function getArtcategoryDescriptions($art_category_id) {
		$art_category_description_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category_description WHERE art_category_id = '" . (int)$art_category_id . "'");

		foreach ($query->rows as $result) {
			$art_category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $art_category_description_data;
	}

	public function getArtcategoryFilters($art_category_id) {
		$art_category_filter_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category_filter WHERE art_category_id = '" . (int)$art_category_id . "'");

		foreach ($query->rows as $result) {
			$art_category_filter_data[] = $result['filter_id'];
		}

		return $art_category_filter_data;
	}

	public function getArtcategoryStores($art_category_id) {
		$art_category_store_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "art_category_to_store WHERE art_category_id = '" . (int)$art_category_id . "'");

		foreach ($query->rows as $result) {
			$art_category_store_data[] = $result['store_id'];
		}

		return $art_category_store_data;
	}


	public function getTotalArtCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art_category WHERE store_id='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}
	
	public function getTotalCategoriesByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art_category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
	
	// byTAO
	public function isCategoryEmpty($art_category_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "art_to_category WHERE art_category_id = '" . (int)$art_category_id . "' AND store_id='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}
	
	public function getMainParentCategory($art_category_id){
		$query = $this->db->query("SELECT parent_id AS parent FROM " . DB_PREFIX . "art_category WHERE art_category_id = '" . (int)$art_category_id . "' AND store_id='".(int)$this->session->data['store_id']."'");
		
		return $query->row['parent'];
	}
	
	public function getSeoUrls(int $art_category_id): array {
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'art_category_id' AND `store_id` ='".(int)$store_id ."' AND `value` = '" . $art_category_id . "'");
		if(isset($query->rows)){
			
			foreach ($query->rows as $result) {
				$category_seo_url_data[$result['language_id']] = $result['keyword'];
			}
		}
		

		return $category_seo_url_data;
	}
	
	public function getPath(int $art_category_id): string {
		return implode('_', array_column($this->getPaths($art_category_id), 'path_id'));
	}

	public function getPaths(int $art_category_id): array {
		$query = $this->db->query("SELECT `art_category_id`, `path_id`, `level` FROM `" . DB_PREFIX . "art_category_path` WHERE `art_category_id` = '" . (int)$art_category_id . "' ORDER BY `level` ASC");

		return $query->rows;
	}
}
