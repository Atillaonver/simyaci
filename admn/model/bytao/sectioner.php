<?php
namespace Opencart\Admin\Model\Bytao;
class Sectioner extends \Opencart\System\Engine\Model {
	
	public function addSectioner(array $data):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "sectioner SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "',status = '" . (int)$data['status'] . "', store_id = '" .  (int)$this->session->data['store_id'] . "', content = '" . $this->db->escape($data['content']) . "', image = '" . $this->db->escape($data['image']) . "'");

		$sectioner_id = $this->db->getLastId();

		return $sectioner_id;
	}

	public function editSectioner(int $sectioner_id, array $data):void {
		
		$this->db->query("UPDATE " . DB_PREFIX . "sectioner SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', status = '" . (int)$data['status'] . "', content = '" . $this->db->escape($data['content']) . "', image = '" . $this->db->escape($data['image']) . "' WHERE sectioner_id = '" . (int)$sectioner_id . "'");

		
	}

	public function deleteSectioner($sectioner_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "sectioner WHERE sectioner_id = '" . (int)$sectioner_id . "'");
		
	}

	public function getSectioner($sectioner_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "sectioner WHERE sectioner_id = '" . (int)$sectioner_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getSectioners($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "sectioner WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

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

	
	public function getTotalSectioners():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "sectioner WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isInstore($sectioner_id):bool{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "sectioner WHERE sectioner_id = '" . (int)$sectioner_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

}