<?php
namespace Opencart\Admin\Model\Bytao;
class Carousel extends \Opencart\System\Engine\Model {
	
	public function editCarousel($data):int {
		
		$carouselId = isset($data['item_id'])?(int)$data['item_id']:0;
		
		if($data['type_item']==1){
			$_content = isset($data['products'])?$data['products']:[];
			$content = implode(',',$_content);
		}elseif($data['type_item']==2){
			$_content = isset($data['categories'])?$data['categories']:[];
			$content = implode(',',$_content);
		}else{
			$content = isset($data['item_banner_id'])?$data['item_banner_id']:'';
		}
		
		$params = serialize($data['setting']);
		 
		if($carouselId != 0){
			$SQL = "UPDATE " . DB_PREFIX . "carousel SET title = '" .$this->db->escape($data['title_item']) . "', content = '" .$this->db->escape($content) . "',  setting = '" .$this->db->escape($params) . "', type_item = '" . (int)$data['type_item'] . "',max_item = '" . (int)$data['max_item'] . "' WHERE carousel_id=".(int)$carouselId;
			$this->db->query($SQL);
		}else{
			$SQL = "INSERT INTO " . DB_PREFIX . "carousel SET title = '" .$this->db->escape($data['title_item']) . "', content = '" .$this->db->escape($content) . "',setting = '" .$this->db->escape($params) . "', language_id = '" . (int)$data['language_id'] . "',store_id = '" . (int)$this->session->data['store_id'] . "',type_item = '" . (int)$data['type_item'] . "',max_item = '" . (int)$data['max_item'] . "'";
			$this->db->query($SQL);
			$carouselId = $this->db->getLastId();
		}
		
		
		return $carouselId;
	}	
	
	public function getCarouselWidget(int $carousel_id = 0):array {
		$ROW=[];
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "carousel WHERE carousel_id='".(int)$carousel_id."' AND store_id = '" . (int)$this->session->data['store_id'] . "' LIMIT 1");
		
		if(isset($query->row)){
			return $query->row;
		}
		
		return $ROW;
	}
	
	public function getCarouselCats(array $items,int $language_id){
		$RET=[];
		foreach($items as $category_id){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id='".(int)$category_id."' AND language_id = '" . (int)$language_id . "' LIMIT 1");
			if(isset($query->row)){
				$RET[] = [
					'name' => $query->row['name'],
					'category_id' => $category_id
				];
			}
		}
		
		
		return $RET;
	}

	public function getCarouselProds(array $items, int $language_id){
		$RET=[];
		foreach($items as $product_id){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id='".(int)$product_id."' AND language_id = '" . (int)$language_id . "' LIMIT 1");
			if(isset($query->row)){
				$RET[] = [
					'name' => $query->row['name'],
					'product_id' => $product_id
				];
			}
		}
		return $RET;
	}


}