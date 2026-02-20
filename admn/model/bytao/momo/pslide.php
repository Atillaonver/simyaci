<?php
class ModelMomoPslide extends Model {
	
	public function addPslide($data) {
		$this->event->trigger('pre.admin.pslide.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "pslide SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "'");

		$pslide_id = $this->db->getLastId();
		
		foreach ($data['pslide_description'] as $language_id => $pslide_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_prod_description SET pslide_id = '" . (int)$pslide_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($pslide_description['title']) . "'");
		}

		if (isset($data['pslide_prod'])) {
			foreach ($data['pslide_prod'] as $pslide_prod) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_prod SET pslide_id = '" . (int)$pslide_id . "', url = '" .  $this->db->escape($pslide_prod['url']) . "',image = '" .  $this->db->escape($pslide_prod['image']) . "',image2 = '" .  $this->db->escape($pslide_prod['image2']) . "', sort_order = '" . (int)$pslide_prod['sort_order'] . "'");

				$pslide_prod_id = $this->db->getLastId();

				foreach ($pslide_prod['pslide_prod_description'] as $language_id => $pslide_prod_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_prod_description SET pslide_prod_id = '" . (int)$pslide_prod_id . "', language_id = '" . (int)$language_id . "', pslide_id = '" . (int)$pslide_id . "', title = '" .  $this->db->escape($pslide_prod_description['title']) . "',title2 = '" .  $this->db->escape($pslide_prod_description['title2']) . "'");
				}
				
			}
		}

		$this->event->trigger('post.admin.pslide.add', $pslide_id);

		return $pslide_id;
	}

	public function editPslide($pslide_id, $data) {
		$this->event->trigger('pre.admin.pslide.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "pslide SET status = '" . (int)$data['status'] . "' WHERE pslide_id = '" . (int)$pslide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_description WHERE pslide_id = '" . (int)$pslide_id . "'");
		
		foreach ($data['pslide_description'] as $language_id => $pslide_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_description SET pslide_id = '" . (int)$pslide_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($pslide_description['title']) . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_prod WHERE pslide_id = '" . (int)$pslide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_prod_description WHERE pslide_id = '" . (int)$pslide_id . "'");

		if (isset($data['pslide_prod'])) {
			foreach ($data['pslide_prod'] as $pslide_prod) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_prod SET pslide_id = '" . (int)$pslide_id . "',url = '" .  $this->db->escape($pslide_prod['url']) . "',image = '" .  $this->db->escape($pslide_prod['image']) . "',image2 = '" .  $this->db->escape($pslide_prod['image2']) . "', sort_order = '" . (int)$pslide_prod['sort_order'] . "'");

				$pslide_prod_id = $this->db->getLastId();

				foreach ($pslide_prod['pslide_prod_description'] as $language_id => $pslide_prod_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "pslide_prod_description SET pslide_prod_id = '" . (int)$pslide_prod_id . "', language_id = '" . (int)$language_id . "', pslide_id = '" . (int)$pslide_id . "', title = '" .  $this->db->escape($pslide_prod_description['title']) . "', title2 = '" .  $this->db->escape($pslide_prod_description['title2']) . "'");
				}
				
				
			}
		}

		$this->event->trigger('post.admin.pslide.edit', $pslide_id);
	}

	public function deletePslide($pslide_id) {
		$this->event->trigger('pre.admin.pslide.delete', $pslide_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide WHERE pslide_id = '" . (int)$pslide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_description WHERE pslide_id = '" . (int)$pslide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_prod WHERE pslide_id = '" . (int)$pslide_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "pslide_prod_description WHERE pslide_id = '" . (int)$pslide_id . "'");

		$this->event->trigger('post.admin.pslide.delete', $pslide_id);
	}

	public function getPslide($pslide_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pslide  WHERE pslide_id = '" . (int)$pslide_id . "'");

		return $query->row;
	}

	public function getPslides($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pslide p LEFT JOIN " . DB_PREFIX . "pslide_description pd ON p.pslide_id=pd.pslide_id   WHERE pd.language_id= '".(int)$this->config->get('config_language_id')."' AND  p.store_id = '" . (int)$this->session->data['store_id'] . "'  ORDER BY pd.title ASC";


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
	}

	
	public function getPslideProdDescriptions($pslide_id) {
		$pslide_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_description WHERE pslide_id = '" . (int)$pslide_id . "'");

		foreach ($query->rows as $result) {
			$pslide_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
			);
		}

		return $pslide_description_data;
	}
		
	public function getPslideProds($pslide_id) {
		$pslide_prod_data = array();

		$pslide_prod_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_prod WHERE pslide_id = '" . (int)$pslide_id . "' ORDER BY sort_order ASC");

		foreach ($pslide_prod_query->rows as $pslide_prod) {
			$pslide_prod_description_data = array();

			$pslide_prod_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_prod_description WHERE pslide_prod_id = '" . (int)$pslide_prod['pslide_prod_id'] . "' AND pslide_id = '" . (int)$pslide_id . "'");

			foreach ($pslide_prod_description_query->rows as $pslide_prod_description) {
				$pslide_prod_description_data[$pslide_prod_description['language_id']] = array('title' => $pslide_prod_description['title'],'title2' => $pslide_prod_description['title2']);
			}

			$pslide_prod_data[] = array(
				'pslide_prod_description' => $pslide_prod_description_data,
				'pslide_prod_id'          => $pslide_prod['pslide_prod_id'],
				'url'                     => $pslide_prod['url'],
				'image'                    => $pslide_prod['image'],
				'image2'                    => $pslide_prod['image2'],
				'sort_order'               => $pslide_prod['sort_order']
			);
		}

		return $pslide_prod_data;
	}
	
	public function getTotalPslides() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "pslide WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isInstore($pslide_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "pslide WHERE pslide_id = '" . (int)$pslide_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}


}