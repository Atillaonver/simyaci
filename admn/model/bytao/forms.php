<?php
namespace Opencart\Admin\Model\Bytao;

class Forms extends \Opencart\System\Engine\Model {
	
	public function addForms(array $data): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "forms` SET `sort_order` = '" . (int)$data['sort_order'] . "', `store_id` = '" . (int)$store_id . "',`bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`page_script` = '" . (isset($data['page_script']) ? $this->db->escape($data['page_script']) : '') . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "'");

		$forms_id = $this->db->getLastId();

		foreach ($data['forms_description'] as $language_id => $value) {
			
			$formdata = is_array($value['formdata'])?serialize($value['formdata']):'';
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "forms_description` SET `forms_id` = '" . (int)$forms_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "',`formdata` = '" . $formdata . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// SEO URL
		
		$this->load->model('design/seo_url');

		if (isset($data['forms_seo_url'])) {
			foreach ($data['forms_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('forms_id', $forms_id, $keyword, $store_id, $language_id,0,'','bytao/forms');
			}
		}

		$this->cache->delete('forms');

		return $forms_id;
	}

	public function editForms(int $forms_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "forms` SET `sort_order` = '" . (int)$data['sort_order'] . "', `bimage` = '" . (isset($data['bimage']) ? $this->db->escape($data['bimage']) : '') . "',`timage` = '" . (isset($data['timage']) ? $this->db->escape($data['timage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`page_script` = '" . (isset($data['page_script']) ? $this->db->escape($data['page_script']) : '') . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "' WHERE `forms_id` = '" . (int)$forms_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "forms_description` WHERE `forms_id` = '" . (int)$forms_id . "'");

		foreach ($data['forms_description'] as $language_id => $value) {
			$formdata = isset($value['formdata']) ? serialize($value['formdata']):'';
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "forms_description` SET `forms_id` = '" . (int)$forms_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "',`formdata` = '" . $formdata . "', `meta_title` = '" .(isset($value['meta_title'])? $this->db->escape($value['meta_title']):'') . "', `meta_description` = '" .(isset($value['meta_description'])?$this->db->escape($value['meta_description']):'') . "', `meta_keyword` = '" .(isset($value['meta_keyword'])?$this->db->escape($value['meta_keyword']):'') . "'");
		}
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('forms_id', $forms_id);
		if (isset($data['forms_seo_url'])) {
			foreach ($data['forms_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('forms_id', $forms_id, $keyword, $store_id, $language_id,0,'','bytao/forms');
			}
		}
		
	}

	public function deleteForms(int $forms_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "forms` WHERE `forms_id` = '" . (int)$forms_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "forms_description` WHERE `forms_id` = '" . (int)$forms_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'forms_id' AND `value` = '" . (int)$forms_id . "'");

		$this->cache->delete('forms');
	}

	public function getForms(int $forms_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "forms` WHERE `forms_id` = '" . (int)$forms_id . "'");

		return $query->row;
	}

	public function getFormss(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "forms` i LEFT JOIN `" . DB_PREFIX . "forms_description` id ON (i.`forms_id` = id.`forms_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' and i.`store_id` = '" . (int)$store_id . "'";

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
	
	public function getFormsDescriptions(int $forms_id): array {
		$forms_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "forms_description` WHERE `forms_id` = '" . (int)$forms_id . "'");

		foreach ($query->rows as $result) {
			
			$forms_description_data[$result['language_id']] = [
				'title'            => $result['title'],
				'header'            => $result['header'],
				'description'      => $result['description'],
				'formdata'      	=> unserialize($result['formdata']),
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $forms_description_data;
	}

	
	public function getFormsSeoUrls(int $forms_id): array {
		$forms_seo_url_data = [];
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'forms_id' AND `value` = '" . (int)$forms_id . "' and store_id ='".(int) $store_id."'");

		foreach ($query->rows as $result) {
			$forms_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $forms_seo_url_data;
	}

	public function getTotalFormss(): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "forms` i WHERE i.store_id='".(int)$store_id."'");

		return (int)$query->row['total'];
	}

}
