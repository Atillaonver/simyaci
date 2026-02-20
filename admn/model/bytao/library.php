<?php
namespace Opencart\Admin\Model\Bytao;

class Library extends \Opencart\System\Engine\Model {
	
	public function addLibrary(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "library` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`image` = '" . (isset($data['image']) ? $this->db->escape($data['image']) : '') . "',`bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "'");

		$library_id = $this->db->getLastId();

		foreach ($data['library_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "library_description` SET `library_id` = '" . (int)$library_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "library_to_store` SET `library_id` = '" . (int)$library_id . "', `store_id` = '" . (int)$store_id . "'");
		

		// SEO URL
		if (isset($data['library_seo_url'])) {
			foreach ($data['library_seo_url'] as $language_id => $keyword) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'library_id', `value` = '" . (int)$library_id . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '".$this->db->escape(isset($data['library_description'][$language_id]['title'])?$data['library_description'][$language_id]['title']:"" )."',route='bytao/library'");
			}

		}
		$this->load->model('design/seo_url');

		if (isset($data['library_seo_url'])) {
			foreach ($data['library_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('library_id', $library_id, $keyword, $store_id, $language_id,0,'','bytao/library');
			}
		}

		if (isset($data['library_layout'])) {
			foreach ($data['library_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "library_to_layout` SET `library_id` = '" . (int)$library_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('library');

		return $library_id;
	}

	public function editLibrary(int $library_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "library` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`image` = '" . (isset($data['image']) ? $this->db->escape($data['image']) : '') . "',`bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',  `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "' WHERE `library_id` = '" . (int)$library_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "library_description` WHERE `library_id` = '" . (int)$library_id . "'");

		foreach ($data['library_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "library_description` SET `library_id` = '" . (int)$library_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('library_id', $library_id);
		if (isset($data['library_seo_url'])) {
			foreach ($data['library_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('library_id', $library_id, $keyword, $store_id, $language_id,0,'','bytao/library');
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "library_to_layout` WHERE `library_id` = '" . (int)$library_id . "'");

		if (isset($data['library_layout'])) {
			foreach ($data['library_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "library_to_layout` SET `library_id` = '" . (int)$library_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}
		
		$this->cache->delete('library');
	}

	public function deleteLibrary(int $library_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "library` WHERE `library_id` = '" . (int)$library_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "library_description` WHERE `library_id` = '" . (int)$library_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "library_to_store` WHERE `library_id` = '" . (int)$library_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "library_to_layout` WHERE `library_id` = '" . (int)$library_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'library_id' AND `value` = '" . (int)$library_id . "'");

		$this->cache->delete('library');
	}

	public function getLibrary(int $library_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "library` WHERE `library_id` = '" . (int)$library_id . "'");

		return $query->row;
	}

	public function getLibrarys(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_description` id ON (i.`library_id` = id.`library_id`) LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' and i2s.`store_id` = '" . (int)$store_id . "'";

			$sort_data = [
				'id.title',
				'i.sort_order',
				'i.status'
			];

			if ($data['filter_title']) {
				$sql .= " AND id.title LIKE '%" . $this->db->escape($data['filter_title'])."%'";
			}
			
			if ($data['filter_status']) {
				$sql .= " AND i.status = '" . (int)$data['filter_status']."'";
			} 
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.`title`";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			if (! $data['filter_all']) {
				if (isset($data['start']) || isset($data['limit'])) {
					if ($data['start'] < 0) {
						$data['start'] = 0;
					}

					if ($data['limit'] < 1) {
						$data['limit'] = 20;
					}
					$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
				}
			}

			$query = $this->db->query($sql);

			return isset($query->rows)?$query->rows:[];
	}
	
	public function getLibraryDescriptions(int $library_id): array {
		$library_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "library_description` WHERE `library_id` = '" . (int)$library_id . "'");

		foreach ($query->rows as $result) {
			$library_description_data[$result['language_id']] = [
				'title'            => $result['title'],
				'header'            => $result['header'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $library_description_data;
	}

	public function getLibrarySeoUrls(int $library_id): array {
		$library_seo_url_data = [];
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'library_id' AND `value` = '" . (int)$library_id . "' and store_id ='".(int) $store_id."'");

		foreach ($query->rows as $result) {
			$library_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $library_seo_url_data;
	}

	public function getLibraryLayouts(int $library_id): array {
		$library_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "library_to_layout` WHERE `library_id` = '" . (int)$library_id . "'");

		foreach ($query->rows as $result) {
			$library_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $library_layout_data;
	}

	public function getTotalLibrarys(array $data = []): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$SQL = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "library` i LEFT JOIN `" . DB_PREFIX . "library_to_store` i2s ON (i.`library_id` = i2s.`library_id`) WHERE i2s.store_id='".(int)$store_id."'";
		
		if ($data['filter_status']) {
			$SQL .= " AND i.status = '" . (int)$data['filter_status']."'";
		} 
		
		$this->log->write('String:'.$SQL);
		$query = $this->db->query($SQL);	
		return (int)$query->row['total'];
	}

	public function getTotalLibrarysByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "library_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}

}
