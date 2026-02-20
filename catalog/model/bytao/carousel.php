<?php
namespace Opencart\Catalog\Model\Bytao;
class Carousel extends \Opencart\System\Engine\Model {
	
	public function getCarousel($carousel_id){
		$SQL = "SELECT * FROM `" . DB_PREFIX . "carousel` WHERE carousel_id = '" . (int)$carousel_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$carousel_query = $this->db->query($SQL);
		if(isset($carousel_query->row)){
			return $carousel_query->row;
		}else{
			return [];
		}
	}

	
}