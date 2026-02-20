<?php
class ModelBytaoAdv extends Model {
	
	public function getAdv($adv_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "adv WHERE adv_id = '" . (int)$adv_id . "' and status='1' ");

		return $query->row;
	}

	public function getAdvs($data = array()) {
		if ($data) {
			$userGroup=$this->user->getUserGroup();
			
			$sql = "SELECT * FROM " . DB_PREFIX . "adv and status='1'";
			
			

			$sort_data = array(
				'title',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY date_added";
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

			return $query->rows;
		} else {
			$adv_data = $this->cache->get('advs');

			if (!$adv_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "adv and status='1'");

				$adv_data = $query->rows;

				$this->cache->set('advs', $adv_data);
			}

			return $adv_data;
		}
	}

	public function getTotalAdvs() {
		
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "adv");
		
		return $query->row['total'];
	}

	public function getStoreLayoutAdv($store_id,$layout_id, $position){
		
				/*$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "adv WHERE store_id='".$store_id."' and  layout_id='".$layout_id."' and position='".$position."' and date_start > NOW() and date_stop < NOW() ORDER BY sort_order");*/
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "adv WHERE store_id='".$store_id."' and  layout_id='".$layout_id."' and position='".$position."' and status='1' ORDER BY sort_order");

				$adv_data = $query->rows;
				
	return $adv_data;
	}
	
	
}