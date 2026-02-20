<?php
namespace Opencart\Admin\Model\Bytao;
class Editor extends \Opencart\System\Engine\Model
{
	private $ctrl;
	private $language_id = 1;
	private $ID = 0;

	public function setCtrl(object $ctrl)
	{
		$this->ctrl = $ctrl;
		return;
	}

	public function addRow(array $hDATA): void
	{
		$store_id = $this->session->data['store_id'];
		$rows     = isset($hDATA['rows'])?$hDATA['rows']:[];
		$this->ID = isset($hDATA['id'])?$hDATA['id']:0;
		$language_id = $this->language_id = isset($hDATA['language_id'])?$hDATA['language_id']:1;
		$this->ctrl = isset($hDATA['ctrl'])?$hDATA['ctrl']:'home';
		
		if($rows)
		{
			foreach($rows as $rorder => $ROW ){
				$row_data = serialize($ROW['row_data']);
				$SQL = "INSERT INTO " . DB_PREFIX . "row SET group_code='".$this->db->escape($this->ctrl)."',";
				if($this->ctrl=='page'){
					$SQL .= "group_id='".$this->ID."',";
				}
				$SQL .= "store_id='".(int)$store_id."',";
				$SQL .= "row_padding='".$this->db->escape($ROW['row_padding'])."',";
				$SQL .= "row_margin='".$this->db->escape($ROW['row_margin'])."',";
				$SQL .= "row_cells='".$this->db->escape($ROW['row_cells'])."',";
				$SQL .= "name='".$this->db->escape($ROW['name'])."',";
				$SQL .= "row_data='".$this->db->escape($row_data)."',";
				$SQL .= "row_tag_id='".$this->db->escape($ROW['row_tag_id'])."',";
				$SQL .= "row_class='".(isset($ROW['row_class'])?$this->db->escape($ROW['row_class']):'')."',";
				$SQL .= "image='".$this->db->escape($ROW['image'])."', ";
				$SQL .= "status='".(int)$ROW['status']."', row_sort_order='".$this->db->escape($ROW['row_sort_order'])."',";
				$SQL .= "language_id ='".(int)$language_id."'";
				$this->db->query($SQL);
				$row_id = $this->db->getLastId();

				foreach($ROW['cols'] as $key => $COL )
				{
					$col_data = serialize($COL['col_data']);
					$sSQL = "INSERT INTO " . DB_PREFIX . "row_col SET group_code='".$this->db->escape($this->ctrl)."',group_id='".(int)$this->ID."',store_id='".(int)$store_id."', row_id='".(int)$row_id."',col_type='".(isset($COL['col_type'])?(int)$COL['col_type']:'')."',col_tag_id='".$this->db->escape($COL['col_tag_id'])."',col_class='".(isset($COL['col_class'])?$this->db->escape($COL['col_class']):'')."',col_cell='".(isset($COL['col_cell'])?$this->db->escape($COL['col_cell']):'')."',col_padding='".$this->db->escape($COL['col_padding'])."',col_margin='".$this->db->escape($COL['col_margin'])."', col_content_id='".$this->db->escape($COL['col_content_id'])."',col_style='".(isset($COL['col_style'])?$this->db->escape($COL['col_style']):'')."', col_content='".$this->db->escape($COL['col_content'])."',col_data='".$this->db->escape($col_data)."',image='".(isset($COL['image'])?$this->db->escape($COL['image']):'')."', col_sort_order='".$key."'";
					
					$this->db->query($sSQL);

					$row_col_id = $this->db->getLastId();
					if(isset($COL['collection']))
					{
						foreach($COL['collection'] as $ind => $C )
						{
							$this->db->query("INSERT INTO " . DB_PREFIX . "row_col_image SET group_code='".$this->db->escape($this->ctrl)."',group_id='".(int)$this->ID."', store_id='".(int)$store_id."', col_id='".(int)$row_col_id."',link='".$this->db->escape($C['link'])."', image='".$this->db->escape($C['image'])."', title='".$this->db->escape($C['title'])."', col_sort_order='".$ind."'");
						}
					}
					// sub - rows
					if(isset($COL['sub_rows']))
					{
						foreach($COL['sub_rows'] as $rorder1 => $ROW1 )
						{
							$row1_data = serialize($ROW1['row_data']);
							
							$this->db->query("INSERT INTO " . DB_PREFIX . "row SET group_code='".$this->db->escape($this->ctrl)."', group_id='".(int)$this->ID."', store_id='".(int)$store_id."',parent_id='".(int)$row_col_id."',row_padding='".$this->db->escape($ROW1['row_padding'])."',row_margin='".$this->db->escape($ROW1['row_margin'])."', row_cells='".$this->db->escape($ROW1['row_cells'])."',name='".$this->db->escape($ROW1['name'])."',row_data='".$this->db->escape($row1_data)."',row_tag_id='".$this->db->escape($ROW1['row_tag_id'])."',row_class='".(isset($ROW1['row_class'])?$this->db->escape($ROW1['row_class']):'')."',image='".$this->db->escape($ROW1['image'])."', status='".(int)$ROW1['status']."', row_sort_order='".$rorder1."',language_id ='".(int)$language_id."'");
							
							$sub_row_id = $this->db->getLastId();
							foreach($ROW1['cols'] as $key1 => $COL1 )
							{
								$col1_data = serialize($COL1['col_data']);
								
								$this->db->query("INSERT INTO " . DB_PREFIX . "row_col SET group_code='".$this->db->escape($this->ctrl)."',group_id='".(int)$this->ID."', store_id='".(int)$store_id."', row_id='".(int)$sub_row_id."',col_type='".(isset($COL1['col_type'])?(int)$COL1['col_type']:'')."',col_tag_id='".$this->db->escape($COL1['col_tag_id'])."',col_class='".(isset($COL1['col_class'])?$this->db->escape($COL1['col_class']):'')."',col_padding='".$this->db->escape($COL1['col_padding'])."',col_margin='".$this->db->escape($COL1['col_margin'])."', col_content_id='".$this->db->escape($COL1['col_content_id'])."',col_data='".$this->db->escape($col1_data)."',col_style='".(isset($COL1['col_style'])?$this->db->escape($COL1['col_style']):'')."', col_content='".$this->db->escape($COL1['col_content'])."',image='".(isset($COL1['image'])?$this->db->escape($COL1['image']):'')."', col_sort_order='".$key1."'");
								$sub_row_col_id = $this->db->getLastId();
								if(isset($COL1['collection']))
								{
									foreach($COL1['collection'] as $ind1 => $C1 )
									{
										$this->db->query("INSERT INTO " . DB_PREFIX . "row_col_image SET group_code='".$this->db->escape($this->ctrl)."',group_id='".(int)$this->ID."',store_id='".(int)$store_id."', col_id='".(int)$sub_row_col_id."',link='".$this->db->escape($C1['link'])."', image='".$this->db->escape($C1['image'])."', title='".$this->db->escape($C1['title'])."', col_sort_order='".$ind1."'");

									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function deleteRow(array $hDATA): void
	{
		$store_id = $this->session->data['store_id'];
		$this->ID = isset($hDATA['id'])?$hDATA['id']:0;
		$this->language_id = isset($hDATA['language_id'])?$hDATA['language_id']:1;
		$this->ctrl = isset($hDATA['ctrl'])?$hDATA['ctrl']:'home';
		if($this->ctrl == 'page'){
			$this->db->query("DELETE FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."' AND group_id='".$this->ID."'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "row_col WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."' AND group_id='".$this->ID."'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "row_col_image WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."' AND group_id='".$this->ID."'");
		}else{
			$this->db->query("DELETE FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "row_col WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "row_col_image WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."'");
			
		}
	}

	public function getRows(array $hDATA):array
	{
		$row_data = [];
		$store_id = $this->session->data['store_id'];
		
		$this->ID = isset($hDATA['id'])?$hDATA['id']:0;
		$this->language_id = isset($hDATA['language_id'])?$hDATA['language_id']:1;
		$this->ctrl = isset($hDATA['ctrl'])?$hDATA['ctrl']:'';
		
		if($this->ctrl == 'page'){
			$SQL = "SELECT * FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."' AND group_id='".$this->ID."' AND language_id='".(int)$this->language_id."' ORDER BY row_sort_order ASC";
		}else{
			$SQL = "SELECT * FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$store_id . "' AND group_code='".$this->ctrl."' AND language_id='".(int)$this->language_id."' ORDER BY row_sort_order ASC";
		}
		
		
		$query    = $this->db->query($SQL);

		foreach($query->rows as $result){
			$row_data[] = [
				'row_id'     => $result['row_id'],
				'name'       => $result['name'],
				'store_id'   => $store_id,
				'row_padding'=> $result['row_padding'],
				'row_margin' => $result['row_margin'],
				'row_cells'  => $result['row_cells'] ,
				'row_tag_id' => $result['row_tag_id'],
				'row_class'  => $result['row_class'],
				'row_data'  => unserialize($result['row_data']),
				'image'      => $result['image'],
				'status'     => $result['status'],
				'row_sort_order'     => $result['row_sort_order'],
				'cols'       => $this->getRowCols($result['row_id'])

			];
		}

		return $row_data;
	}

	public function getRowCols(int $row_id):array
	{
		$row_col_data = [];

		$query        = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			
			$xy =0;
			if($result['col_cell']){
				$x = (int)substr($result['col_cell'],0,1);
				$y = (int)substr($result['col_cell'],1,1);
				$xy = (12/$y)*$x;
			}
			
			$row_col_data[] = [
				'row_col_id'    => $result['row_col_id'],
				'store_id'      => $this->session->data['store_id'],
				'col_type'      => $result['col_type'],
				'col_content'   => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'=> $result['col_content_id'],
				'col_style'     => $result['col_style'],
				'col_class'     => $result['col_class'],
				'col_cell'      => $result['col_cell'],
				'col_cellx'      => $result['col_cell']?$xy:'',
				'col_tag_id'    => $result['col_tag_id'],
				'col_padding'   => $result['col_padding'],
				'col_margin'    => $result['col_margin'],
				'col_data'      => unserialize($result['col_data']),
				'image'         => $result['image'],
				'col_images'    => $this->getRowColImages($result['row_col_id']),
				'sub_rows'      => $this->getSubRows($result['row_col_id'])
			];
		}

		return $row_col_data;
	}

	public function getSubRows(int $parent_id):array
	{
		$row_data = [];

		$query    = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE parent_id = '" . (int)$parent_id . "' AND group_code='".$this->ctrl."' AND language_id='".(int)$this->language_id."' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$row_data[] = [
				'row_id'     => $result['row_id'],
				'name'       => $result['name'],
				'store_id'   => $store_id,
				'row_padding'=> $result['row_padding'],
				'row_margin' => $result['row_margin'],
				'row_cells'  => $result['row_cells'],
				'row_tag_id' => $result['row_tag_id'],
				'row_class'  => $result['row_class'],
				'row_data'   => unserialize($result['row_data']),
				'image'      => $result['image'],
				'status'     => $result['status'],
				'cols'       => $this->getSubRowCols($result['row_id'])

			];
		}

		return $row_data;
	}

	public function getSubRowCols(int $row_id):array
	{
		$row_col_data = [];

		$query        = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE group_code='".$this->ctrl."' AND row_id = '" . (int)$row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$row_col_data[] = [
				'row_col_id'    => $result['row_col_id'],
				'store_id'      => $this->session->data['store_id'],
				'col_type'      => $result['col_type'],
				'col_content'   => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'=> $result['col_content_id'],
				'col_style'     => $result['col_style'],
				'col_class'     => $result['col_class'],
				'col_tag_id'    => $result['col_tag_id'],
				'col_padding'   => $result['col_padding'],
				'col_margin'    => $result['col_margin'],
				'col_data'      => unserialize($result['col_data']),
				'image'         => $result['image'],
				'col_images'    => $this->getRowColImages($result['row_col_id'])
			];
		}

		return $row_col_data;
	}

	public function getRowColImages(int $row_col_id):array
	{
		$row_col_image_data = [];

		$query              = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col_image WHERE col_id = '" . (int)$row_col_id . "' ORDER BY sort_order ASC");
		foreach($query->rows as $result){
			$row_col_image_data[] = [
				'col_image_id'=> $result['col_image_id'],
				'col_id'      => $result['col_id'],
				'image'       => $result['image']
			];
		}

		return $row_col_image_data;
	}

	private function getCells($cells):array
	{
		$celldata = [];
		$celldata[] = $cells;
		$c = explode('_',$cells);
		foreach($c as $ce){
			$num1 = (int)substr($ce,0,1);
			$num2 = (int)substr($ce,1,2);
			$celldata[] = $num2 && $num1 ?(12 / $num2) * $num1:'12';
		}

		return $celldata;
	}

	public function deleteRowCol(int $store_id = 0): void
	{
		$store_id = $store_id?$store_id:$this->session->data['store_id'];

		$this->db->query("DELETE FROM " . DB_PREFIX . "row WHERE store_id = '" . (int)$store_id . "' AND group_code='".$group_code."'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "row_col WHERE store_id = '" . (int)$store_id . "' AND group_code='".$group_code."'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "row_col_image WHERE store_id = '" . (int)$store_id . "' AND group_code='".$group_code."'");

	}


	public function editModule(int $module_id, array $data): void
	{
		$this->db->query("UPDATE `" . DB_PREFIX . "module` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `setting` = '" . $this->db->escape(json_encode($data)) . "' WHERE `module_id` = '" . (int)$module_id . "'");
	}

	public function getModule($module_id):array
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `module_id` = '" . (int)$module_id . "'");

		if($query->row){
			return json_decode($query->row['setting'], true);
		}
		else
		{
			return array();
		}
	}

	public function getEditorColumnTypes():array
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "editor_col_type` ORDER BY sort_order ");
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getEditorCtrlTypes():array
	{
		$typeData=[];
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "editor_ctrl_type` ORDER BY ctrl_type_id ASC ");
		foreach($query->rows as $row){
			$typeData[$row['cntrll']] = $row['name'];
		}
		return $typeData;
	}

}