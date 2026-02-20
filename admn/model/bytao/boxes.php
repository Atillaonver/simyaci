<?php
class ModelBytaoBoxes extends Model {
	
	
	public function addBoxes($data) {
		$this->event->trigger('pre.admin.boxes.add', $data);
		$position = '';
		foreach ($data['p'] as $value) {
			$position .=$value.':';
			}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "boxes SET sort_order = '" . (int)$data['sort_order'] . "',status = '" . (int)$data['status'] . "',image='" . $this->db->escape($data['image']) . "',link='" . $this->db->escape($data['link']) . "',href='" . $this->db->escape($data['href']) . "',position='" . $this->db->escape($position) . "',store_id='" . (int)$this->session->data['store_id'] . "',module_id='" . (int)$data['module_id'] . "',date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "'");

		$boxes_id = $this->db->getLastId();

		foreach ($data['boxes_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "boxes_description SET boxes_id = '" . (int)$boxes_id . "', language_id = '" . (int)$language_id . "', header1 = '" . $this->db->escape($value['header1']) . "', header2 = '" . $this->db->escape($value['header2']) . "', header3 = '" . $this->db->escape($value['header3']) . "'");
		}


		$this->cache->delete('boxes');

		$this->event->trigger('post.admin.boxes.add', $boxes_id);

		return $boxes_id;
	}

	public function editBoxes($boxes_id, $data) {
		$this->event->trigger('pre.admin.boxes.edit', $data);
	$position = '';
		foreach ($data['p'] as $value) {
			$position .=$value.':';
			}
		$this->db->query("UPDATE " . DB_PREFIX . "boxes SET sort_order = '" . (int)$data['sort_order'] . "',status = '" . (int)$data['status'] . "',image='" . $this->db->escape($data['image']) . "',link='" . $this->db->escape($data['link']) . "',href='" . $this->db->escape($data['href']) . "',position='" . $this->db->escape($position) . "',store_id='" . (int)$this->session->data['store_id'] . "',module_id='" . (int)$data['module_id'] . "',date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "' WHERE boxes_id = '" . (int)$boxes_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "boxes_description WHERE boxes_id = '" . (int)$boxes_id . "'");

		
		foreach ($data['boxes_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "boxes_description SET boxes_id = '" . (int)$boxes_id . "', language_id = '" . (int)$language_id . "', header1 = '" . $this->db->escape($value['header1']) . "', header2 = '" . $this->db->escape($value['header2']) . "', header3 = '" . $this->db->escape($value['header3']) . "'");
		}

		$this->cache->delete('boxes');

		$this->event->trigger('post.admin.boxes.edit', $boxes_id);
	}

	public function deleteBoxes($boxes_id) {
		$this->event->trigger('pre.admin.boxes.delete', $boxes_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "boxes WHERE boxes_id = '" . (int)$boxes_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "boxes_description WHERE boxes_id = '" . (int)$boxes_id . "'");
	
		$this->cache->delete('boxes');

		$this->event->trigger('post.admin.boxes.delete', $boxes_id);
	}

	public function getBoxes($boxes_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'boxes_id=" . (int)$boxes_id . "') AS keyword FROM " . DB_PREFIX . "boxes WHERE boxes_id = '" . (int)$boxes_id . "'");

		return $query->row;
	}

	public function getBoxess($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "boxes i LEFT JOIN " . DB_PREFIX . "boxes_description id ON (i.boxes_id = id.boxes_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id='" . (int)$this->session->data['store_id'] . "'";

			$sort_data = array(
				'id.header1',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.header1";
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
			$boxes_data = $this->cache->get('boxes.' . (int)$this->config->get('config_language_id'));

			if (!$boxes_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxes i LEFT JOIN " . DB_PREFIX . "boxes_description id ON (i.boxes_id = id.boxes_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.store_id='" . (int)$this->session->data['store_id'] . "' ORDER BY id.header1");

				$boxes_data = $query->rows;

				$this->cache->set('boxes.' . (int)$this->config->get('config_language_id'), $boxes_data);
			}

			return $boxes_data;
		}
	}

	public function getBoxesDescriptions($boxes_id) {
		$boxes_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxes_description WHERE boxes_id = '" . (int)$boxes_id . "'");

		foreach ($query->rows as $result) {
			$boxes_description_data[$result['language_id']] = array(
				'header1'            => $result['header1'],
				'header2'      => $result['header2'],
				'header3'       => $result['header3']
				
			);
		}

		return $boxes_description_data;
	}

	public function getTotalBoxess() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "boxes");

		return $query->row['total'];
	}

}