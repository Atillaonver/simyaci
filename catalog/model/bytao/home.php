<?php
namespace Opencart\Catalog\Model\Bytao;
class Home extends \Opencart\System\Engine\Model {
	
	public function getHome():array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "home h LEFT JOIN " . DB_PREFIX . "home_description hd ON (h.store_id = hd.store_id) WHERE hd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND h.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}
	
	public function getHomeRows():array {
		$homes_row_data = [];
		
		
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$this->config->get('config_store_id') . "' AND parent_id = '0' AND status = '1' AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$homes_row_data[] = [
				'row_id'            => $result['row_id'],
				'name'            => $result['name'],
				'store_id'            => $result['store_id'],
				'row_padding'            => $result['row_padding'],
				'row_margin'            => $result['row_margin'],
				'row_cells'            => $result['row_cells'],
				'row_tag_id'            => $result['row_tag_id'],
				'row_class'            => $result['row_class'],
				'image'            => $result['image'],
				'cols'      => $this->getHomeRowCols($result['row_id'])
				
			];
		}

		return $homes_row_data;
	}
	
	public function getHomeRowCols($row_id){
		$homes_row_col_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$homes_row_col_data[] = [
				'col_id'            => $result['row_col_id'],
				'store_id'            => $result['store_id'],
				'col_type'            => $result['col_type'],
				'col_content'      => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'      => $result['col_content_id'],
				'col_style'      => $result['col_style'],
				'col_class'      => $result['col_class'],
				'col_tag_id'      => $result['col_tag_id'],
				'image'      	=> $result['image'],
				'col_images'      => $this->getHomeRowColImages($result['row_col_id']),
				'sub_rows'      => $this->getHomeSubRows($result['row_col_id'])
			];
		}
		
		return $homes_row_col_data;
	}
	
	public function getHomeSubRows($parent_id ):array {
		$homes_row_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE parent_id = '" . (int)$parent_id . "' AND status = '1' AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$homes_row_data[] = [
				'row_id'            => $result['row_id'],
				'name'            => $result['name'],
				'store_id'            => $result['store_id'],
				'row_padding'            => $result['row_padding'],
				'row_margin'            => $result['row_margin'],
				'row_cells'            => $result['row_cells'],
				'row_tag_id'            => $result['row_tag_id'],
				'row_class'            => $result['row_class'],
				'image'            => $result['image'],
				'cols'      => $this->getHomeSubRowCols($result['row_id'])
				
			];
		}

		return $homes_row_data;
	}
	
	public function getHomeSubRowCols($row_id){
		$homes_row_col_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$homes_row_col_data[] = [
				'row_col_id'            => $result['row_col_id'],
				'store_id'            => $result['store_id'],
				'col_type'            => $result['col_type'],
				'col_content'      => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'      => $result['col_content_id'],
				'col_style'      => $result['col_style'],
				'col_class'      => $result['col_class'],
				'col_tag_id'      => $result['col_tag_id'],
				'image'      	=> $result['image'],
				'col_images'      => $this->getHomeRowColImages($result['row_col_id'])
				
			];
		}
		
		return $homes_row_col_data;
	}
	
	public function getHomeRowColImages($home_row_col_id){
		$home_row_col_image_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col_image WHERE col_id = '" . (int)$home_row_col_id . "' ORDER BY sort_order ASC");
		foreach($query->rows as $result){
			$home_row_col_image_data[] = [
				'col_image_id'            => $result['col_image_id'],
				'col_id'            => $result['col_id'],
				'image'      => $result['image']
			];
		}
		
		return $home_row_col_image_data;
	}
	

}