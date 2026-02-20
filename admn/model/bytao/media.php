<?php
class ModelBytaoMedia extends Model {
	
	public function addMedia($data) {
		$this->event->trigger('pre.admin.media.add', $data);

		$this->db->query("INSERT INTO " . DB_PREFIX . "media SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "',media_type = '" . (int)$data['media_type'] . "',status = '" . (int)$data['status'] . "'");

		$media_id = $this->db->getLastId();

		if (isset($data['media_file'])) {
			foreach ($data['media_file'] as $media_file) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "media_file SET media_id = '" . (int)$media_id . "', link = '" .  $this->db->escape($media_file['link']) . "',media_parent_class = '" .  $this->db->escape($media_file['media_parent_class']) . "',media_style = '" .  (isset($media_file['media_style'])?$this->db->escape($media_file['media_style']):'' ) . "',media_type = '" . (isset($media_file['media_type'])?$this->db->escape($media_file['media_type']):'0' ) . "', file = '" .  $this->db->escape($media_file['image']) . "', sort_order = '" . (int)$media_file['sort_order'] . "'");

				$media_file_id = $this->db->getLastId();

				foreach ($media_file['media_file_description'] as $language_id => $media_file_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "media_file_description SET media_file_id = '" . (int)$media_file_id . "', language_id = '" . (int)$language_id . "', media_id = '" . (int)$media_id . "', title = '" .  $this->db->escape($media_file_description['title']) . "'");
				}
				
				if (isset($media_file['sub'])) {
					foreach ($media_file['sub'] as $sub_media_file) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "media_file SET media_id = '" . (int)$media_id . "', link = '" .  $this->db->escape($sub_media_file['link']) . "',media_parent_class = '" .  $this->db->escape($sub_media_file['media_parent_class']) . "',media_style = '" .   (isset($sub_media_file['media_style'])?$this->db->escape($sub_media_file['media_style']):'' ) . "', media_type = '" .(isset($sub_media_file['media_type'])?$this->db->escape($sub_media_file['media_type']):'0' ). "',  file = '" .  $this->db->escape($sub_media_file['image']) . "', mobile_file = '" . (isset($sub_media_file['mobile_file'])?$this->db->escape($sub_media_file['mobile_file']):'')  . "',parent_id='".$media_file_id."',position = '" . (int)$sub_media_file['position'] . "',  sort_order = '" . (int)$sub_media_file['sort_order'] . "'");

						$sub_media_file_id = $this->db->getLastId();
						foreach ($sub_media_file['media_file_description'] as $language_id => $sub_media_file_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "media_file_description SET media_file_id = '" . (int)$sub_media_file_id . "', language_id = '" . (int)$language_id . "', media_id = '" . (int)$media_id . "', title = '" .  $this->db->escape($sub_media_file_description['title']) . "'");
						}
						
					}	
				}
			}
		}

		$this->event->trigger('post.admin.media.add', $media_id);

		return $media_id;
	}

	public function editMedia($media_id, $data) {
		$this->event->trigger('pre.admin.media.edit', $data);

		$this->db->query("UPDATE " . DB_PREFIX . "media SET name = '" . $this->db->escape($data['name']) . "', width = '" . (int)$data['width'] . "', height = '" . (int)$data['height'] . "', status = '" . (int)$data['status'] . "' WHERE media_id = '" . (int)$media_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "media_file WHERE media_id = '" . (int)$media_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "media_file_description WHERE media_id = '" . (int)$media_id . "'");

		if (isset($data['media_file'])) {
			foreach ($data['media_file'] as $media_file) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "media_file SET media_id = '" . (int)$media_id . "', link = '" .  $this->db->escape($media_file['link']) . "',media_parent_class = '" .  $this->db->escape($media_file['media_parent_class']) . "',media_style = '" .   (isset($media_file['media_style'])?$this->db->escape($media_file['media_style']):'' ) . "', media_type = '" .(isset($media_file['media_type'])?$this->db->escape($media_file['media_type']):'0' ). "',  file = '" .  $this->db->escape($media_file['image']) . "', mobile_file = '" .  $this->db->escape($media_file['mobile_file']) . "', sort_order = '" . (int)$media_file['sort_order'] . "'");

				$media_file_id = $this->db->getLastId();

				foreach ($media_file['media_file_description'] as $language_id => $media_file_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "media_file_description SET media_file_id = '" . (int)$media_file_id . "', language_id = '" . (int)$language_id . "', media_id = '" . (int)$media_id . "', title = '" .  $this->db->escape($media_file_description['title']) . "'");
				}
				
				if (isset($media_file['sub'])) {
					foreach ($media_file['sub'] as $sub_media_file) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "media_file SET media_id = '" . (int)$media_id . "', link = '" .  $this->db->escape($sub_media_file['link']) . "',media_parent_class = '" .  $this->db->escape($sub_media_file['media_parent_class']) . "',media_style = '" .   (isset($sub_media_file['media_style'])?$this->db->escape($sub_media_file['media_style']):'' ) . "', media_type = '" .(isset($sub_media_file['media_type'])?$this->db->escape($sub_media_file['media_type']):'0' ). "',  file = '" .  $this->db->escape($sub_media_file['image']) . "', mobile_file = '" .(isset($sub_media_file['mobile_file'])?$this->db->escape($sub_media_file['mobile_file']):'') . "',parent_id='".$media_file_id."', position = '" . (int)$sub_media_file['position'] . "', sort_order = '" . (int)$sub_media_file['sort_order'] . "'");

						$sub_media_file_id = $this->db->getLastId();
						foreach ($sub_media_file['media_file_description'] as $language_id => $sub_media_file_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "media_file_description SET media_file_id = '" . (int)$sub_media_file_id . "', language_id = '" . (int)$language_id . "', media_id = '" . (int)$media_id . "', title = '" .  $this->db->escape($sub_media_file_description['title']) . "'");
						}
						
					}	
				}
				
			}
		}

		$this->event->trigger('post.admin.media.edit', $media_id);
	}

	public function deleteMedia($media_id) {
		$this->event->trigger('pre.admin.media.delete', $media_id);

		$this->db->query("DELETE FROM " . DB_PREFIX . "media WHERE media_id = '" . (int)$media_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "media_file WHERE media_id = '" . (int)$media_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "media_file_description WHERE media_id = '" . (int)$media_id . "'");

		$this->event->trigger('post.admin.media.delete', $media_id);
	}

	public function getMedia($media_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "media WHERE media_id = '" . (int)$media_id . "'");

		return $query->row;
	}

	public function getMedias($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "media WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

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

		return $query->rows;
	}

	public function getSubFiles($data = array(),$parent_id=0) {
		$sql = "SELECT * FROM " . DB_PREFIX . "media WHERE parent_id='".$parent_id."' AND store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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
	}

	public function getMediaFiles($media_id) {
		$media_file_data = array();

		$media_file_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "media_file WHERE media_id = '" . (int)$media_id . "' AND parent_id='0' ORDER BY sort_order ASC");

		foreach ($media_file_query->rows as $media_file) {
			$media_file_description_data = array();

			$media_media_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "media_file_description WHERE media_file_id = '" . (int)$media_file['media_file_id'] . "' AND media_id = '" . (int)$media_id . "'");

			foreach ($media_media_description_query->rows as $media_file_description) {
				$media_file_description_data[$media_file_description['language_id']] = array('title' => $media_file_description['title']);
			}

			$media_file_data[] = array(
				'media_file_description' => $media_file_description_data,
				'media_file_id'          => $media_file['media_file_id'],
				'media_parent_class'          => $media_file['media_parent_class'],
				'media_style'          => $media_file['media_style'],
				'media_type'          => $media_file['media_type'],
				'link'                     => $media_file['link'],
				'file'                    => $media_file['file'],
				'mobile_file'                    => $media_file['mobile_file'],
				'date_start'                    => $media_file['date_start'],
				'date_end'                    => $media_file['date_end'],
				'status'                    => $media_file['status'],
				'sort_order'               => $media_file['sort_order']
			);
		}

		return $media_file_data;
	}
	
	public function getSubMediaFiles($parent_id) {
		$media_file_data = array();

		$media_file_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "media_file WHERE parent_id = '" . (int)$parent_id . "' ORDER BY sort_order ASC");

		foreach ($media_file_query->rows as $media_file) {
			$media_file_description_data = array();

			$media_file_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "media_file_description WHERE media_file_id = '" . (int)$media_file['media_file_id'] . "' AND media_id = '" . (int)$media_file['media_id'] . "'");


			foreach ($media_file_description_query->rows as $media_file_description) {
				$media_file_description_data[$media_file_description['language_id']] = array('title' => $media_file_description['title']);
			}

			$media_file_data[] = array(
				'media_file_description' => $media_file_description_data,
				'media_parent_class'      => $media_file['media_parent_class'],
				'media_style'      		  => $media_file['media_style'],
				'media_type'      		  => $media_file['media_type'],
				'position'                 => $media_file['position'],
				'link'                     => $media_file['link'],
				'file'                    => $media_file['file'],
				'mobile_file'             => $media_file['mobile_file'],
				'sort_order'               => $media_file['sort_order']
			);
		}

		return $media_file_data;
	}

	public function getTotalMedias() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "media WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function isInstore($media_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "media WHERE media_id = '" . (int)$media_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}


}