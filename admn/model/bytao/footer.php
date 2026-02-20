<?php
namespace Opencart\Admin\Model\Bytao;
class Footer extends \Opencart\System\Engine\Model{
	private $language_id=0;
	public function editFooter(array $data):void {
		//$this->event->trigger('pre.admin.footer.edit', $data);
		$store_id = $this->session->data['store_id'];
		$this->load->model('bytao/editor');
		$this->model_bytao_editor->deleteRow(['ctrl'=>'footer']);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "footer_description WHERE store_id = '" . $store_id . "'");
		foreach($data['footer_description'] as $language_id => $value){
			$this->db->query("INSERT INTO " . DB_PREFIX . "footer_description SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', css = '" . $this->db->escape($value['css']) . "', fimage = '" . $this->db->escape($value['fimage']) . "'");
			
			if(isset($value['rows'])){
				$this->model_bytao_editor->addRow(['ctrl'=>'footer','rows'=>$value['rows'],'language_id'=>$language_id]);
			}
		}

		//$this->event->trigger('post.admin.footer.edit', $store_id);
	}

	public function getFooter():void {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "footer WHERE store_id='".(int)$this->session->data['store_id']."'");
		if(!isset($query->row['store_id'])){
			$this->db->query("INSERT INTO " . DB_PREFIX . "footer SET status = '1',store_id='".(int)$this->session->data['store_id']."'");	
		}		
	}
	
	public function getFooterDescriptions():array {
		$footer_description_data = [];
		$this->load->model('bytao/editor');
		$this->load->model('tool/image');
		$sql = "SELECT * FROM " . DB_PREFIX . "footer_description WHERE store_id = '" . $this->session->data['store_id'] . "'";
		$query = $this->db->query($sql);

		foreach($query->rows as $result){
			if ($result['fimage'] && is_file(DIR_IMAGE . $result['fimage'])) {
				$fthumb = $this->model_tool_image->resize($result['fimage'], 100, 100);
			} else {
				$fthumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$footer_description_data[$result['language_id']] = array(
				'rows'      		=> $this->model_bytao_editor->getRows(['ctrl'=>'footer','language_id'=>$result['language_id']]),
				'css'     			=> $result['css'],
				'fimage'    		 => $result['fimage'],
				'fthumb'    		 => $fthumb
			);
		}
		
		
		return $footer_description_data;
	}

	public function deleteFooter():void{
		$store_id = $this->session->data['store_id'];
		$this->load->model('bytao/editor');
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "footer` WHERE store_id = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "footer_description` WHERE store_id = '" . (int)$store_id . "'");
		$this->model_bytao_editor->deleteRow(['ctrl'=>'footer']);
	}
	
	public function getWidget():array{
		$widget=[];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets WHERE store_id='" . (int)$this->session->data['store_id'] . "' AND controller='campaigns' LIMIT 1");
		if(isset($query->row['store_id'])){
			return $query->row;
		}
		
		$this->db->query("INSERT INTO  " . DB_PREFIX . "widgets SET store_id='" . (int)$this->session->data['store_id'] . "', controller='campaigns', items_total='6',items='3', image_width='200',image_height='200'");
		
		return array('items_total'=>'6','items'=>'3','image_width'=>'200','image_height'=>'200');
	}
	
	public function getAllWidgets():array{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets");
		
		return isset($query->rows)?$query->rows:[];
	}	

	private function getCells($cells):array {
		$celldata=[];
		$celldata[]=$cells;
		$c = explode('_',$cells);
		foreach ($c as $ce){
			$num1 = (int)substr($ce,0,1);
			$num2 = (int)substr($ce,1,2);
			$celldata[]= $num2 && $num1 ?(12/$num2)*$num1:'12';
		}
		
		return $celldata;
	}

	public function installFooter():void{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."footer'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "footer` (`store_id` int(11) NOT NULL,`status` tinyint(1) NOT NULL DEFAULT 1) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "footer_description` (`store_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(64) NOT NULL,`header` varchar(300) NOT NULL,`style` varchar(500) NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL,`himage` varchar(255) NOT NULL,`fimage` varchar(255) NOT NULL,`css` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			//satir
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "footer_row` (`footer_row_id` int(11) NOT NULL ,`store_id` int(11) NOT NULL,`row_padding` varchar(20) NOT NULL,`row_margin` varchar(20) NOT NULL,`row_cells` varchar(5) NOT NULL,`row_tag_id` varchar(40) NOT NULL,`row_class` varchar(300) NOT NULL,`row_sort_order` int(3) NOT NULL,`image` varchar(200) NOT NULL,`status` tinyint(1) NOT NULL DEFAULT 1,`language_id` int(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			//sutun
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "footer_row_col` (`footer_row_col_id` int(11)NOT NULL ,`store_id` int(11) NOT NULL,`footer_row_id` int(11) NOT NULL,`col_type` int(3) NOT NULL,`col_content` text NOT NULL,`col_content_id` varchar(40) NOT NULL,`col_class` varchar(300) NOT NULL,`col_style` varchar(800) NOT NULL,`image` varchar(200) NOT NULL,`col_sort_order` int(3) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			// resim grubu
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "footer_row_col_image` (`col_image_id` int(11) NOT NULL ,`col_id` int(11) NOT NULL,`store_id` int(11) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(255) NOT NULL,`title` varchar(300) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer` ADD PRIMARY KEY (`store_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_description` ADD PRIMARY KEY (`store_id`,`language_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row` ADD PRIMARY KEY (`footer_row_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row_col` ADD PRIMARY KEY (`footer_row_col_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row_col_image` ADD PRIMARY KEY (`col_image_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row` MODIFY `footer_row_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row_col` MODIFY `footer_row_col_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "footer_row_col_image` MODIFY `col_image_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}

}