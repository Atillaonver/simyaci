<?php

namespace Opencart\Catalog\Model\Setting;

class Status extends \Opencart\System\Engine\Model
{
	/**
	 * Verilen modülün belirtilen mağazada aktif olup olmadığını kontrol eder.
	 *
	 * @param int $module_id
	 * @param int $store_id
	 *
	 * @return bool
	 */
	public function isEnabled(int $module_id, int $store_id = 0): bool
	{
		$query = $this->db->query(
			"SELECT `module_id` FROM `" . DB_PREFIX . "module_status`
			 WHERE `module_id` = '" . (int)$module_id . "'
			   AND `store_id`  = '" . (int)$store_id  . "'
			 LIMIT 1"
		);

		return (bool)$query->num_rows;
	}
}
