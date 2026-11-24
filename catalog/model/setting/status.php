<?php

namespace Opencart\Catalog\Model\Setting;

class Status extends \Opencart\System\Engine\Model
{
	/**
	 * @param string $code
	 *
	 * @return int
	 */
	public function moduleIdFromCode(string $code): int
	{
		$pos = strrpos($code, '.');
		if ($pos === false) {
			return 0;
		}
		$id = (int)substr($code, $pos + 1);
		return $id > 0 ? $id : 0;
	}

	/**
	 * Verilen code'un (layout_module.code) belirtilen mağazada aktif olup
	 * olmadığını kontrol eder.
	 *
	 * @param string $code      layout_module.code (ör. "extension/module/banner.42")
	 * @param int    $store_id
	 *
	 * @return bool
	 */
	public function isEnabled(string $code, int $store_id = 0): bool
	{
		$module_id = $this->moduleIdFromCode($code);

		if ($module_id === 0) {
			return true;
		}

		$query = $this->db->query(
			"SELECT `module_id` FROM `" . DB_PREFIX . "module_status`
			 WHERE `module_id` = '" . (int)$module_id . "'
			   AND `store_id`  = '" . (int)$store_id  . "'
			 LIMIT 1"
		);

		return (bool)$query->num_rows;
	}
}
