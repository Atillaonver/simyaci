<?php
class ModelBytaoGallery extends Model {
	
	public function getGallery($gallery_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "gallery c LEFT JOIN " . DB_PREFIX . "gallery_description cd ON (c.gallery_id = cd.gallery_id) WHERE c.gallery_id = '" . (int)$gallery_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getGalleryImage($gallery_id,$sort_order) {

		$gallery_image_data = array();

		$gQuery = $this->db->query("SELECT *,cd.title as name FROM " . DB_PREFIX . "gallery c LEFT JOIN " . DB_PREFIX . "gallery_description cd ON (c.gallery_id = cd.gallery_id) WHERE c.gallery_id='" . (int)$gallery_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");
		

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image bi LEFT JOIN " . DB_PREFIX . "gallery_image_description bid ON (bi.gallery_image_id  = bid.gallery_image_id) WHERE bi.gallery_id = '" . (int)$gallery_id . "' AND bid.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bi.sort_order='".(int)$sort_order."' ORDER BY bi.sort_order ASC LIMIT 1");
		foreach ($query->rows as $gallery_image) {
			
				$gallery_image_data[] = array(
				'gallery_image_description' => $gallery_image['title'],
				'link'                     => $gallery_image['link'],
				'image'                    => $gallery_image['image'],
				'sort_order'               => $gallery_image['sort_order'],
				'meta_title'               => $gQuery->row['name'],
				'meta_description'               => $gQuery->row['meta_description'],
				'meta_keyword'               => $gQuery->row['meta_keyword']
				
				
			);
		}

		return $gallery_image_data;
	}
	
	public function getGalleryImages($gallery_id) {

		$gallery_image_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image bi LEFT JOIN " . DB_PREFIX . "gallery_image_description bid ON (bi.gallery_image_id  = bid.gallery_image_id) WHERE bi.gallery_id = '" . (int)$gallery_id . "' AND bid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bi.sort_order ASC");
		foreach ($query->rows as $gallery_image) {
			
				$gallery_image_data[] = array(
				'gallery_image_description' => $gallery_image['title'],
				'link'                     => $gallery_image['link'],
				'image'                    => $gallery_image['image'],
				'sort_order'               => $gallery_image['sort_order']
			);
		}

		return $gallery_image_data;
	}
	
	public function getGalleryLastImages($gallery_id , $limit = 0 , $start = 0) {
		$gallery_image_data = array();
		
		if($limit!=0) {
			$ekSQL=' limit '.$limit;
		}else{
			$ekSQL='';
		}

		$gallery = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "gallery c LEFT JOIN " . DB_PREFIX . "gallery_description cd ON (c.gallery_id = cd.gallery_id) WHERE c.gallery_id = '" . (int)$gallery_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' Limit 1");
		
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image bi LEFT JOIN " . DB_PREFIX . "gallery_image_description bid ON (bi.gallery_image_id  = bid.gallery_image_id) WHERE bi.gallery_id = '" . (int)$gallery_id . "' AND bi.sort_order > '" . (int)$start . "' AND bid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bi.sort_order ASC ".$ekSQL );
		foreach ($query->rows as $gallery_image) {
			$gallery_image_data[] = array(
				'title' =>  $gallery_image['title'],
				'link'                     => $gallery_image['link'],
				'image'                    => $gallery_image['image'],
				'sort_order'                    => $gallery_image['sort_order'],
				'bimage'                    => $gallery->row['image']
			);
		}	
		
		return $gallery_image_data;
	}
	
	public function getGalleryFirstImage() {
		$gallery_image_data = array();


		$gallerys = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery ORDER BY sort_order ASC ");
		
		foreach($gallerys->rows as $gallery){
			
		
		$gallery_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image WHERE gallery_id = ".(int)$gallery['gallery_id']." ORDER BY sort_order DESC LIMIT 1");

		foreach ($gallery_image_query->rows as $gallery_image) {
			$gallery_image_description_data = array();

			$gallery_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_image_description WHERE gallery_image_id = '" . (int)$gallery_image['gallery_image_id'] . "' AND gallery_id = '" . (int)$gallery_image['gallery_id']  . "'");
			
			$colquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_description WHERE gallery_id = '" . (int)$gallery_image['gallery_id'] . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");
			$gallery_name = $colquery->row['title'];
			foreach ($gallery_image_description_query->rows as $gallery_image_description) {
				
			
				
				$gallery_image_description_data[$gallery_image_description['language_id']] = array('title' => $gallery_image_description['title']);
			}

			$gallery_image_data[] = array(
				'title'                     => $gallery_name,
				'gallery_id'                     => $gallery_image['gallery_id'],
				'link'                     => $gallery_image['link'],
				'image'                    => $gallery_image['image'],
				'sort_order'               => $gallery_image['sort_order']
			);
		}
		}

		return $gallery_image_data;
	}

	public function getGalleryDescriptions($gallery_id) {
		$gallery_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gallery_description WHERE gallery_id = '" . (int)$gallery_id . "'");

		foreach ($query->rows as $result) {
			$gallery_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $gallery_description_data;
	}
	
	public function getTotalGallerys() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "gallery");

		return $query->row['total'];
	}

	public function getLastGalleryImages($data = array()) {
		$sql = "SELECT *,cd.title as name FROM " . DB_PREFIX . "gallery c LEFT JOIN " . DB_PREFIX . "gallery_description cd ON (c.gallery_id = cd.gallery_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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
	
}