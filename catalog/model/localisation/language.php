<?php
namespace Opencart\Catalog\Model\Localisation;
class Language extends \Opencart\System\Engine\Model {
	private array $data = [];

	public function getLanguage(int $language_id): array {
		if (isset($this->data[$language_id])) {
			return $this->data[$language_id];
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `language_id` = '" . (int)$language_id . "'");

		$language = $query->row;

		if ($language) {
			$language['image'] = HTTP_SERVER;

			if (!$language['extension']) {
				$language['image'] .= 'catalog/';
			} else {
				$language['image'] .= 'extension/' . $language['extension'] . '/catalog/';
			}

			$language['image'] .= 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}

		$this->data[$language_id] = $language;

		return $language;
	}

	public function getLanguageByCode(string $code): array {
		if (isset($this->data[$code])) {
			return $this->data[$code];
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "'");

		$language = $query->row;

		if ($language) {
			$language['image'] = HTTP_SERVER;

			if (!$language['extension']) {
				$language['image'] .= 'catalog/';
			} else {
				$language['image'] .= 'extension/' . $language['extension'] . '/catalog/';
			}

			$language['image'] .= 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}

		$this->data[$code] = $language;

		return $language;
	}
	
	public function getLanguageIdByCode(string $code): int {
		$query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "'");

		return isset($query->row['language_id'])?$query->row['language_id']:0;
	}

	public function getLanguages(): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "language` WHERE `status` = '1' AND `code` IN ('".implode("','",$this->config->get('config_store_languages'))."') ORDER BY `sort_order`, `name`";
		
		$results = $this->cache->get('language.'.$this->config->get('config_store_id').'.' . md5($sql));
		
		if (!$results) {
			$language_data = [];

			$query = $this->db->query($sql);

			$results = $query->rows;

			$this->cache->set('language.'.$this->config->get('config_store_id').'.' . md5($sql), $results);
		}
		
		$language_data = [];

		foreach ($results as $result) {
			$image = HTTP_SERVER;

			if (!$result['extension']) {
				$image .= 'catalog/';
			} else {
				$image .= 'extension/' . $result['extension'] . '/catalog/';
			}

			$language_data[$result['code']] = ['image' => $image . 'language/' . $result['code'] . '/' . $result['code'] . '.png'] + $result;
		}

		return $language_data;
	}
	
	
}
