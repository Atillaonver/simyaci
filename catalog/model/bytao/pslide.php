<?php

class ModelBytaoPslide extends Model {
	public function getPslide($pslide_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "pslide  WHERE pslide_id = '" . (int)$pslide_id . "'");

		return $query->row;
	}
		
	public function getPslideProds($pslide_id) {
		$pslide_prod_data = array();

		$pslide_prod_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_prod WHERE pslide_id = '" . (int)$pslide_id . "' ORDER BY sort_order ASC");

		foreach ($pslide_prod_query->rows as $pslide_prod) {
			$pslide_prod_description_data = array();

			$pslide_prod_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_prod_description WHERE pslide_prod_id = '" . (int)$pslide_prod['pslide_prod_id'] . "' AND pslide_id = '" . (int)$pslide_id . "' AND language_id='". (int)$this->config->get('config_language_id')."'");

			
			$pslide_prod_data[] = array(
				'title' => $pslide_prod_description_query->row['title'],
				'title2' => $pslide_prod_description_query->row['title2'],
				'pslide_prod_id'          => $pslide_prod['pslide_prod_id'],
				'url'                     => $pslide_prod['url'],
				'image'                    => $pslide_prod['image'],
				'image2'                    => $pslide_prod['image2'],
				'sort_order'               => $pslide_prod['sort_order']
			);
		}

		return $pslide_prod_data;
	}
	
	public function getPslideDescription($pslide_id) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pslide_description WHERE pslide_id = '" . (int)$pslide_id . "' AND language_id='". (int)$this->config->get('config_language_id')."'");


		return  isset($query->row['title'])?$query->row['title']:'';
	}
	
	
}