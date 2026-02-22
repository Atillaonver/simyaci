<?php
namespace Opencart\Admin\Model\Catalog;

class Information extends \Opencart\System\Engine\Model {
	
	public function addInformation(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "information` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`type_id` = '" . (isset($data['type_id']) ? (int)$data['type_id'] : 0) . "',`bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`page_script` = '" . (isset($data['page_script']) ? $this->db->escape($data['page_script']) : '') . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "'");

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
			$description = by_text_move($value['description'],true,URL_IMAGE);
			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_description` SET `information_id` = '" . (int)$information_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($description) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_store` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "'");
		

		// SEO URL
		$this->load->model('design/seo_url');

		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('information_id', $information_id, $keyword, $store_id, $language_id,0,'','information/information');
			}
		}

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('information');

		return $information_id;
	}

	public function editInformation(int $information_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "information` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`type_id` = '" . (isset($data['type_id']) ? (int)$data['type_id'] : 0) . "',`bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`page_script` = '" . (isset($data['page_script']) ? $this->db->escape($data['page_script']) : '') . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "' WHERE `information_id` = '" . (int)$information_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");
		foreach ($data['information_description'] as $language_id => $value) {
			$description = by_text_move($value['description'],true,URL_IMAGE);	
			$this->db->query("INSERT INTO `" . DB_PREFIX . "information_description` SET `information_id` = '" . (int)$information_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($description) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = '" . (int)$information_id . "'");

		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('information_id', $information_id);
		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('information_id', $information_id, $keyword, $store_id, $language_id,0,'','information/information');
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET `information_id` = '" . (int)$information_id . "', `store_id` = '" . (int)$store_id . "', `layout_id` = '" . (int)$layout_id . "'");
			}
		}
		
		$this->cache->delete('information');
	}

	public function deleteInformation(int $information_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = '" . (int)$information_id . "'");

		$this->cache->delete('information');
	}

	public function getInformation(int $information_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "information` WHERE `information_id` = '" . (int)$information_id . "'");

		return $query->row;
	}

	public function getInformations(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$language_id  = isset($this->session->data['store_language_id'])?$this->session->data['store_language_id']:$this->config->get('config_language_id');
		
		$sql = "SELECT *,(SELECT name FROM `" . DB_PREFIX . "information_type` WHERE type_id = i.type_id )as tname FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_description` id ON (i.`information_id` = id.`information_id`) LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.`information_id` = i2s.`information_id`) WHERE id.`language_id` = '" . (int)$language_id. "' and i2s.`store_id` = '" . (int)$store_id . "'";

			$sort_data = [
				'id.title',
				'i.sort_order',
				'i.type_id'
			];

			if (isset($data['type'])) {
				$sql .= " AND i.type_id = '" . $data['type']."'";
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
	
	public function getDescriptions(int $information_id): array {
		$information_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_description` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = [
				'title'            => $result['title'],
				'header'            => $result['header'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $information_description_data;
	}

	public function getStores(int $information_id): array {
		$information_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_to_store` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getSeoUrls(int $information_id): array {
		$information_seo_url_data = [];
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'information_id' AND `value` = '" . (int)$information_id . "' and store_id ='".(int) $store_id."'");

		foreach ($query->rows as $result) {
			$information_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $information_seo_url_data;
	}

	public function getLayouts(int $information_id): array {
		$information_layout_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_to_layout` WHERE `information_id` = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	public function getTotalInformations(): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "information` i LEFT JOIN `" . DB_PREFIX . "information_to_store` i2s ON (i.`information_id` = i2s.`information_id`) WHERE i2s.store_id='".(int)$store_id."'");

		return (int)$query->row['total'];
	}

	public function getTotalInformationsByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "information_to_layout` WHERE `layout_id` = '" . (int)$layout_id . "'");

		return (int)$query->row['total'];
	}

	public function getInformationTypes():array {
		$types=[];
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_type`");

		foreach ($query->rows as $result) {
			$types[] = [
				'type_id' =>$result['type_id'],
				'name' =>$result['name']
			];
		}
		return $types;
	}
}
