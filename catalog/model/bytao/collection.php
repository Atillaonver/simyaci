<?php
class ModelBytaoCollection extends Model {
	
	public function getCollection($collection_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "collection c LEFT JOIN " . DB_PREFIX . "collection_description cd ON (c.collection_id = cd.collection_id) WHERE c.collection_id = '" . (int)$collection_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCollections($data = array()) {
		$sql = "SELECT *,cd.title as name FROM " . DB_PREFIX . "collection c LEFT JOIN " . DB_PREFIX . "collection_description cd ON (c.collection_id = cd.collection_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'cd.title',
			'c.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cd.title";
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

	public function getCollectionImages($collection_id) {
		$collection_image_data = array();

		$collection_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_image WHERE collection_id = '" . (int)$collection_id . "' ORDER BY sort_order ASC");

		foreach ($collection_image_query->rows as $collection_image) {
			$collection_image_description_data = array();

			$collection_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_image_description WHERE collection_image_id = '" . (int)$collection_image['collection_image_id'] . "' AND collection_id = '" . (int)$collection_id . "'");

			foreach ($collection_image_description_query->rows as $collection_image_description) {
				$collection_image_description_data[$collection_image_description['language_id']] = array('title' => $collection_image_description['title']);
			}

			$collection_image_data[] = array(
				'collection_image_description' => $collection_image_description_data,
				'link'                     => $collection_image['link'],
				'image'                    => $collection_image['image'],
				'sort_order'               => $collection_image['sort_order']
			);
		}

		return $collection_image_data;
	}
	
	public function getCollectionFirstImage() {
		$collection_image_data = array();


		$collections = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection ORDER BY sort_order ASC ");
		
		foreach($collections->rows as $collection){
			
		
		$collection_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_image WHERE collection_id = ".(int)$collection['collection_id']." ORDER BY sort_order DESC LIMIT 1");

		foreach ($collection_image_query->rows as $collection_image) {
			$collection_image_description_data = array();

			$collection_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_image_description WHERE collection_image_id = '" . (int)$collection_image['collection_image_id'] . "' AND collection_id = '" . (int)$collection_image['collection_id']  . "'");
			
			$colquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_description WHERE collection_id = '" . (int)$collection_image['collection_id'] . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");
			$collection_name = $colquery->row['title'];
			foreach ($collection_image_description_query->rows as $collection_image_description) {
				
			
				
				$collection_image_description_data[$collection_image_description['language_id']] = array('title' => $collection_image_description['title']);
			}

			$collection_image_data[] = array(
				'title'                     => $collection_name,
				'collection_id'                     => $collection_image['collection_id'],
				'link'                     => $collection_image['link'],
				'image'                    => $collection_image['image'],
				'sort_order'               => $collection_image['sort_order']
			);
		}
		}

		return $collection_image_data;
	}

	public function getCollectionDescriptions($collection_id) {
		$collection_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "collection_description WHERE collection_id = '" . (int)$collection_id . "'");

		foreach ($query->rows as $result) {
			$collection_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $collection_description_data;
	}
	
	public function getTotalCollections() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "collection");

		return $query->row['total'];
	}
}