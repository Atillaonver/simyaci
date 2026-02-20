<?php
namespace Opencart\Catalog\Model\Setting;
class Setting extends \Opencart\System\Engine\Model {
	public function getSettings(int $store_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' OR `store_id` = 0 ORDER BY `store_id` ASC");

		return $query->rows;
	}

	public function getSetting(string $code, int $store_id = 0): array {
		$setting_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$setting_data[$result['key']] = $result['value'];
			} else {
				$setting_data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $setting_data;
	}
	
	public function getSettingbyKey(string $key, int $store_id = 0) {
		$setting_data = [];
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				return $result['value'];
			} else {
				return json_decode($result['value'], true);
			}
		}
		return '';
	}
	
	public function getValue(string $key, int $store_id = 0): string {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		if (isset($query->num_rows)&& $query->num_rows ) {
			return $query->row['value'];
		} else {
			return '';
		}
	}	
}
