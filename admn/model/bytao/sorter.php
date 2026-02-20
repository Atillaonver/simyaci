<?php
class ModelBytaoSorter extends Model {
	
	public function setSorter($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . $this->ctrl."_row SET ".$this->ctrl."_id='".(int)$data[$this->ctrl.'_id']."',parent_id='".(int)$data['parent_id']."', row_col='".(int)$data['row_col']."', row_sort_order='".(int)$data['row_sort_order']."',language_id ='".(int)$data['language_id']."'");
		$pages_row_id = $this->db->getLastId();
		return $pages_row_id;
	}
	
	public function getSorter($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . $this->ctrl."_row SET ".$this->ctrl."_id='".(int)$data[$this->ctrl.'_id']."',parent_id='".(int)$data['parent_id']."', row_col='".(int)$data['row_col']."', row_sort_order='".(int)$data['row_sort_order']."',language_id ='".(int)$data['language_id']."'");
		$pages_row_id = $this->db->getLastId();
		return $pages_row_id;
	}
	
}