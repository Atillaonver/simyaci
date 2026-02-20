<?php
namespace Opencart\Catalog\Model\Bytao;
class Appointment extends \Opencart\System\Engine\Model {	
	
	public function getAppointment($appointment_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "appointment  WHERE appointment_id = '" . (int)$appointment_id . "'");

		return isset($query->row)?$query->row:[];
	}
	
	public function getAppointments(int $start = 0, int $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_appointment` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY `date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalAppointments(): int {
	
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_appointment` o WHERE `customer_id` = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}	
		
	public function getCalendarAppointments(array $data ):array {
		
		$sql = "SELECT * FROM " . DB_PREFIX . "appointment a    WHERE a.store_id = '" . (int)$this->config->get('config_store_id') . "'";
 		if (isset($data['date_start']) || isset($data['date_end'])) {
			$sql .= " AND a.date_app >'" . $this->db->escape($data['date_start']) . "' AND a.date_app < '" . $this->db->escape($data['date_end'])."' ORDER BY a.date_app";
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
	
	
}