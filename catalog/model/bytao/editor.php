<?php
namespace Opencart\Catalog\Model\Bytao;
class Editor extends \Opencart\System\Engine\Model
{
	private $ctrl = 'page';
	private $language_id = 1;

	public function getRows(array $info):array
	{
		$row_data = [];
		$this->ctrl = isset($info['ctrl'])?$info['ctrl']:'';
		
		$SQL='';
		switch($this->ctrl){
			case 'home':
			case 'footer':
				$SQL = "SELECT * FROM " . DB_PREFIX . "row WHERE group_code='". $this->ctrl ."' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND parent_id = '0' AND status = '1' AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY row_sort_order ASC";
				break;
			case 'page':
				if(isset($info['page_id'])){
					$SQL = "SELECT * FROM " . DB_PREFIX . "row WHERE group_code='". $this->ctrl ."' AND group_id = '" . (int)$info['page_id'] . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND parent_id = '0' AND status = '1' AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY row_sort_order ASC";
				}
				break;
			default:
		}
		
		if($SQL){
			
			$query = $this->db->query($SQL);
			
			foreach($query->rows as $result)
			{
				$row_data[] = [
					'row_id'            => $result['row_id'],
					'name'            	=> $result['name'],
					'store_id'          => $result['store_id'],
					'row_padding'       => $result['row_padding'],
					'row_margin'        => $result['row_margin'],
					'row_cells'         => $result['row_cells'],
					'row_tag_id'        => $result['row_tag_id'],
					'row_class'         => $result['row_class'],
					'row_data'  		=> unserialize($result['row_data']),
					'image'             => $result['image'],
					'cols'      		=> $this->getRowCols($result['row_id'])
				];
			}
		}
		return $row_data;
	}

	public function getRowCols($row_id)
	{
		$row_col_data = [];

		$query        = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result)
		{
			$xy =0;
			if($result['col_cell']){
				$x = (int)substr($result['col_cell'],0,1);
				$y = (int)substr($result['col_cell'],1,1);
				$xy = (12/$y)*$x;
			}
			
			$row_col_data[] = [
				'col_id'            => $result['row_col_id'],
				'store_id'          => $result['store_id'],
				'col_type'          => $result['col_type'],
				'col_content'      	=> html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'    => $result['col_content_id'],
				'col_cell'      	=> $result['col_cell']?$xy:'',
				'col_style'     	=> $result['col_style'],
				'col_class'      	=> $result['col_class'],
				'col_tag_id'      	=> $result['col_tag_id'],
				'col_data'  		=> unserialize($result['col_data']),
				'image'      		=> $result['image'],
				'col_images'      	=> $this->getRowColImages($result['row_col_id']),
				'sub_rows'      	=> $this->getSubRows($result['row_col_id'])
			];
		}

		return $row_col_data;
	}

	public function getSubRows($parent_id ):array
	{
		$row_data = [];

		$query    = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE parent_id = '" . (int)$parent_id . "' AND status = '1' AND language_id='".(int)$this->config->get('config_language_id')."' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result)
		{
			$row_data[] = [
				'row_id'            => $result['row_id'],
				'name'            => $result['name'],
				'store_id'            => $result['store_id'],
				'row_padding'            => $result['row_padding'],
				'row_margin'            => $result['row_margin'],
				'row_cells'            => $result['row_cells'],
				'row_tag_id'            => $result['row_tag_id'],
				'row_class'            => $result['row_class'],
				'row_data'  			=> unserialize($result['row_data']),
				'image'            => $result['image'],
				'cols'      	=> $this->getSubRowCols($result['row_id'])

			];
		}

		return $row_data;
	}

	public function getSubRowCols($row_id)
	{
		$row_col_data = [];

		$query        = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result)
		{
			$row_col_data[] = [
				'row_col_id'            => $result['row_col_id'],
				'store_id'            => $result['store_id'],
				'col_type'            => $result['col_type'],
				'col_content'      => html_entity_decode(by_text_move($result['col_content'],false,URL_IMAGE), ENT_QUOTES, 'UTF-8'),
				'col_content_id'      => $result['col_content_id'],
				'col_style'      => $result['col_style'],
				'col_class'      => $result['col_class'],
				'col_tag_id'      => $result['col_tag_id'],
				'col_data'  	=> unserialize($result['col_data']),
				'image'      	=> $result['image'],
				'col_images'      => $this->getRowColImages($result['row_col_id'])

			];
		}

		return $row_col_data;
	}

	public function getRowColImages($row_col_id)
	{
		$row_col_image_data = [];

		$query              = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col_image WHERE col_id = '" . (int)$row_col_id . "' ORDER BY sort_order ASC");
		foreach($query->rows as $result)
		{
			$row_col_image_data[] = [
				'col_image_id'            => $result['col_image_id'],
				'col_id'            => $result['col_id'],
				'image'      => $result['image']
			];
		}

		return $row_col_image_data;
	}


}