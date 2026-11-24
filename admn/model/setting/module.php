<?php

namespace Opencart\Admin\Model\Setting;

use Aws\signer\signerClient;

class Module extends \Opencart\System\Engine\Model
{
	public function addModule(string $code, array $data): int
	{
		$store_id = isset($this->session->data['store_id']) ? $this->session->data['store_id'] : 0;
		$this->db->query("INSERT INTO `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `code` = '" . $this->db->escape($code) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "'");

		$module_id = $this->db->getLastId();
		if ($data['status']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "module_status` SET `module_id` = '" . (int)$module_id . "',store_id=" . $this->session->data['store_id']);
		}

		return (int)$module_id;
	}

	public function editModule(int $module_id, array $data): void
	{
		$store_id = isset($this->session->data['store_id']) ? $this->session->data['store_id'] : 0;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module_status` WHERE `module_id` = '" . (int)$module_id . "' AND store_id=" . $store_id);
		if (!isset($query->row['module_id'])) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "module_status` SET `module_id` = '" . (int)$module_id . "',store_id='" . $store_id . "'");
		} else {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "module_status` WHERE `module_id` = '" . (int)$module_id . "' AND store_id='" . $store_id . "'");
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "' WHERE `module_id` = '" . (int)$module_id . "'");
	}

	public function deleteModule(int $module_id): void
	{
		$store_id = isset($this->session->data['store_id']) ? $this->session->data['store_id'] : 0;
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module_status` WHERE `module_id` = '" .  (int)$module_id  . "'");
	}

	public function getModule(int $module_id): array
	{
		$store_id = isset($this->session->data['store_id']) ? $this->session->data['store_id'] : 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");

		if ($query->row) {
			$rSetting = json_decode($query->row['setting'], true);
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module_status` WHERE `module_id` = '" . (int)$module_id . "' and store_id=" . $store_id);
			if (isset($query->row['module_id'])) {
				$rSetting['status'] = "1";
			} else {
				$rSetting['status'] = "0";
			}

			return $rSetting;
		} else {
			return [];
		}
	}

	public function getModules(): array
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` ORDER BY `code`");

		return $query->rows;
	}

	public function getModulesByCode(string $code): array
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");

		return $query->rows;
	}

	public function deleteModulesByCode(string $code): void
	{
		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `code` = '" . $this->db->escape($code) . "' OR `code` LIKE '" . $this->db->escape($code . '.%') . "'");
	}
}
