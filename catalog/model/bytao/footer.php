<?php
namespace Opencart\Catalog\Model\Bytao;
class Footer extends \Opencart\System\Engine\Model {
	
	public function getFooter():array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "footer f LEFT JOIN " . DB_PREFIX . "footer_description fd ON (f.store_id = fd.store_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}
	
	public function getFooterRows():array {
		$footers_row_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE language_id='".(int)$this->config->get('config_language_id')."' AND status = '1' AND parent_id='0' AND group_code='footer' AND store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$footers_row_data[] = array(
				'row_id'            => $result['row_id'],
				'name'            => $result['name'],
				'store_id'            => $result['store_id'],
				'row_padding'            => $result['row_padding'],
				'row_margin'            => $result['row_margin'],
				'row_cells'            => $result['row_cells'],
				'row_tag_id'            => $result['row_tag_id'],
				'row_class'            => $result['row_class'],
				'image'            => $result['image'],
				'cols'      => $this->getFooterRowCols($result['footer_row_id'])
				
			);
		}

		return $footers_row_data;
	}
	
	public function getFooterRowCols($footer_row_id):array {
		$footers_row_col_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$footer_row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$footers_row_col_data[] = array(
				'col_id'            => $result['row_col_id'],
				'footer_id'            => $result['store_id'],
				'col_type'            => $result['col_type'],
				'col_content'      => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'      => $result['col_content_id'],
				'col_style'      => $result['col_style'],
				'col_class'      => $result['col_class'],
				'col_tag_id'      => $result['col_tag_id'],
				'image'      	=> $result['image'],
				'col_images'      => $this->getFooterRowColImages($result['footer_row_col_id']),
				'sub_rows'      => $this->getFooterSubRows($result['footer_row_col_id'])
			);
		}
		
		return $footers_row_col_data;
	}
	
	private function getFooterSubRows(int $parent_id ):array {
		$footers_row_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE language_id='".(int)$this->config->get('config_language_id')."' AND status = '1' AND group_code = 'footer' AND parent_id = '" . (int)$parent_id. "' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$footers_row_data[] = array(
				'row_id'            => $result['row_id'],
				'name'            => $result['name'],
				'store_id'            => $result['store_id'],
				'row_padding'            => $result['row_padding'],
				'row_margin'            => $result['row_margin'],
				'row_cells'            => $result['row_cells'],
				'row_tag_id'            => $result['row_tag_id'],
				'row_class'            => $result['row_class'],
				'image'            => $result['image'],
				'cols'      => $this->getFooterSubRowCols($result['footer_row_id'])
				
			);
		}

		return $footers_row_data;
	}
	
	private function getFooterSubRowCols($footer_row_id):array {
		$footers_row_col_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$footer_row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$footers_row_col_data[] = array(
				'col_id'            => $result['row_col_id'],
				'footer_id'            => $result['store_id'],
				'col_type'            => $result['col_type'],
				'col_content'      => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'      => $result['col_content_id'],
				'col_style'      => $result['col_style'],
				'col_class'      => $result['col_class'],
				'col_tag_id'      => $result['col_tag_id'],
				'image'      	=> $result['image'],
				'col_images'      => $this->getFooterRowColImages($result['row_col_id']),
			);
		}
		
		return $footers_row_col_data;
	}
	
	public function getFooterRowColImages($footer_row_col_id):array {
		$footer_row_col_image_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col_image WHERE col_id = '" . (int)$footer_row_col_id . "' ORDER BY sort_order ASC");
		foreach($query->rows as $result){
			$footer_row_col_image_data[] = array(
				'col_image_id'            => $result['col_image_id'],
				'col_id'            => $result['col_id'],
				'image'      => $result['image']
			);
		}
		
		return $footer_row_col_image_data;
	}
	

}