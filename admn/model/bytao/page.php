<?php
namespace Opencart\Admin\Model\Bytao;
class Page extends \Opencart\System\Engine\Model {
	private $language_id=0;
	
	public function addPage(array $data):int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:$store_id;
		$this->load->model('bytao/editor');
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$page_id = $this->db->getLastId();

		foreach($data['page_description'] as $language_id => $value){
			$this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', css = '" . $this->db->escape($value['css']) . "', himage = '" . $this->db->escape($value['himage']) . "', fimage = '" . $this->db->escape($value['fimage']) . "'");
			
			if(isset($value['rows'])){
				$this->model_bytao_editor->addRow(['ctrl'=>'page','rows'=>$value['rows'],'id'=>$page_id,'language_id'=>$language_id]);
			}
		}
		
		if(isset($data['page_store'])){
			foreach($data['page_store'] as $store_id){
				$this->db->query("INSERT INTO " . DB_PREFIX . "page_to_store SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		if (isset($data['page_seo_url'])) {
			$this->load->model('design/seo_url');
			foreach ($data['page_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('page_id', $page_id, $keyword, $store_id, $language_id,0,'','bytao/page');
			}
		}
		
		return $page_id;
	}
	
	public function editPage(int $page_id, array $data):void{
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->load->model('bytao/editor');
		
		$this->model_bytao_editor->deleteRow(['ctrl'=>'page',[],'id'=>$page_id]);
		
		$this->db->query("UPDATE " . DB_PREFIX . "page SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE page_id = '" . (int)$page_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");
		
			
		foreach($data['page_description'] as $language_id => $value){
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "page_description SET page_id = '" . (int)$page_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', css = '" . $this->db->escape($value['css']) . "', himage = '" . $this->db->escape($value['himage']) . "', fimage = '" . $this->db->escape($value['fimage']) . "'");
			
			if(isset($value['rows'])){
				$this->model_bytao_editor->addRow(['ctrl'=>'page','rows'=>$value['rows'],'id'=>$page_id,'language_id'=>$language_id]);
			}
		}
		

		

		if(isset($data['page_store'])){
			$this->db->query("DELETE FROM " . DB_PREFIX . "page_to_store WHERE page_id = '" . (int)$page_id . "'");
			foreach($data['page_store'] as $storeId){
				$this->db->query("INSERT INTO " . DB_PREFIX . "page_to_store SET page_id = '" . (int)$page_id . "', store_id = '" . (int)$storeId . "'");
			}
		}
		

		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlsByKeyValue('page_id', $page_id);
		if (isset($data['page_seo_url'])) {
			foreach ($data['page_seo_url'] as $language_id => $keyword) {
					$this->model_design_seo_url->addSeoUrl('page_id', $page_id, $keyword, $store_id, $language_id,0,'','bytao/page');
			}
		}
		
	}
	
	public function sortOrderPage($page_id,$sort_order):void{
		$this->db->query("UPDATE " . DB_PREFIX . "page SET sort_order='".(int)$sort_order."' WHERE page_id ='".(int)$page_id."'");
	}
	
	public function deletePage(int $page_id):void{
		$this->load->model('bytao/editor');
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "page` WHERE page_id = '" . (int)$page_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "page_description` WHERE page_id = '" . (int)$page_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "page_to_store` WHERE page_id = '" . (int)$page_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE key = 'page_id=" . (int)$page_id . "'");
		$this->model_bytao_editor->deleteRow(['ctrl'=>'page','id'=>$page_id]);
	}

	public function getPage(int $page_id):array{
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "page WHERE page_id = '" . (int)$page_id . "'");
		return $query->row;
	}

	public function getPages(array $data):array {

		$sql = "SELECT * FROM " . DB_PREFIX . "page p LEFT JOIN " . DB_PREFIX . "page_description pd ON (p.page_id = pd.page_id) LEFT JOIN " . DB_PREFIX . "page_to_store p2s ON (p.page_id = p2s.page_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->session->data['store_id'] . "'";

		$sort_data = [
			'pd.title',
			'p.sort_order'
		];

		if(isset($data['sort']) && in_array($data['sort'], $sort_data)){
			$sql .= " ORDER BY " . $data['sort'];
		} else{
			$sql .= " ORDER BY pd.title";
		}

		if(isset($data['order']) && ($data['order'] == 'DESC')){
			$sql .= " DESC";
		} else{
			$sql .= " ASC";
		}

		if(isset($data['start']) || isset($data['limit'])){
			if($data['start'] < 0){
				$data['start'] = 0;
			}

			if($data['limit'] < 1){
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}

	public function getPageDescriptions(int $page_id ):array {
		$page_description_data = [];
		$this->load->model('bytao/editor');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_description WHERE page_id = '" . (int)$page_id . "'");
		
		foreach($query->rows as $result){
			
			$this->load->model('tool/image');
			if ($result['himage'] && is_file(DIR_IMAGE . $result['himage'])) {
				$hthumb = $this->model_tool_image->resize($result['himage'], 100, 100);
			} else {
				$hthumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			if ($result['fimage'] && is_file(DIR_IMAGE . $result['fimage'])) {
				$fthumb = $this->model_tool_image->resize($result['fimage'], 100, 100);
			} else {
				$fthumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$rows = $this->model_bytao_editor->getRows(['ctrl'=>'page','language_id'=>$result['language_id'],'id'=>$page_id]);
			
			$page_description_data[$result['language_id']] = [
				'title'             => $result['title'],
				'header'            => $result['header'],
				'rows'      		=> $rows,
				'style'      		=> $result['style'],
				'meta_title'        => $result['meta_title'],
				'meta_description'  => $result['meta_description'],
				'meta_keyword'      => $result['meta_keyword'],
				'css'     			=> $result['css'],
				'himage'     		=> $result['himage'],
				'fimage'    		=> $result['fimage'],
				'hthumb'    		=> $hthumb,
				'fthumb'    		=> $fthumb
			];
		}

		return $page_description_data;
	}

	public function getPageGroups():array {
		$pages_group_data = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_group");
		foreach($query->rows as $result){
			$pages_group_data[] = [
				'page_group_id'   => $result['page_group_id'],
				'name'            => $result['name'],
				'explain'         => $result['group_explain'],
			];
		}
		return $pages_group_data;
	}
	
	public function addPageGroups(array $gData ):int{
		$this->db->query("INSERT INTO " . DB_PREFIX . "page_group SET name='".$this->db->escape($gData['gname'])."',group_explain='".$this->db->escape($gData['gexp'])."'");
		$page_group_id = $this->db->getLastId();
		return $page_group_id;
	}
	
	public function getPageRows(int $page_id,int $language_id):array{
		$pages_row_data = [];
		$this->language_id = $language_id;
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE group_code='page' AND group_id='".(int)$page_id."' AND language_id='".(int)$language_id."' ORDER BY row_sort_order ASC");

		if(isset($query->rows )){
			foreach($query->rows as $result){
				$pages_row_data[] = [
					'row_id'          => $result['row_id'],
					'page_id'         => $result['group_id'],
					'name'            => $result['name'],
					'row_padding'     => $result['row_padding'],
					'row_margin'      => $result['row_margin'],
					'row_cells'       => $this->getCells($result['row_cells']) ,
					'row_tag_id'      => $result['row_tag_id'],
					'row_class'       => $result['row_class'],
					'image'           => $result['image'],
					'status'          => $result['status'],
					'cols'      	  => $this->getPageRowCols($result['row_id'])
					
				];
			}
		}

		return $pages_row_data;
	}
	
	public function getPageRowCols(int $page_row_id):array{
		$pages_row_col_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE group_code='page' AND row_id = '" . (int)$page_row_id . "'  ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$pages_row_col_data[] = array(
				'page_row_col_id'       => $result['row_col_id'],
				'page_id'            	=> $result['group_id'],
				'col_type'            	=> $result['col_type'],
				'col_content'      		=> html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'      	=> $result['col_content_id'],
				'col_style'      		=> $result['col_style'],
				'col_class'      		=> $result['col_class'],
				'col_tag_id'      		=> isset($result['col_tag_id'])?$result['col_tag_id']:'',
				'col_padding'           => isset($result['col_padding'])?$result['col_padding']:'',
				'col_margin'            => $result['col_margin'],
				'image'      			=> $result['image'],
				'col_images'      		=> $this->getPageRowColImages($result['row_col_id']),
				'sub_row'      			=> $this->getPageSubRows($result['row_col_id'])
			);
		}
		
		return $pages_row_col_data;
	}
	
	public function getPageSubRows(int $parent_id):array{
		$pages_row_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row WHERE group_code='page' AND parent_id = '" . (int)$parent_id . "' AND language_id='".(int)$this->language_id."' ORDER BY row_sort_order ASC");

		foreach($query->rows as $result){
			$pages_row_data[] = [
				'row_id'            => $result['row_id'],
				'page_id'           => $result['page_id'],
				'name'            	=> $result['name'],
				'row_padding'       => $result['row_padding'],
				'row_margin'        => $result['row_margin'],
				'row_cells'         => $result['row_cells'],
				'row_tag_id'        => $result['row_tag_id'],
				'row_class'         => $result['row_class'],
				'image'             => $result['image'],
				'status'            => $result['status'],
				'cols'      		=> $this->getPageSubRowCols($result['row_id'])
				];
		}

		return $pages_row_data;
	}
	
	public function getPageSubRowCols(int $page_row_id):array{
		$pages_row_col_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col WHERE group_code='page' AND row_id = '" . (int)$page_row_id . "' ORDER BY col_sort_order ASC");

		foreach($query->rows as $result){
			$pages_row_col_data[] = [
				'page_row_col_id'    => $result['row_col_id'],
				'page_id'            => $result['page_id'],
				'col_type'           => $result['col_type'],
				'col_content'      	 => html_entity_decode($result['col_content'], ENT_QUOTES, 'UTF-8'),
				'col_content_id'     => $result['col_content_id'],
				'col_style'      	 => $result['col_style'],
				'col_class'      	 => $result['col_class'],
				'col_tag_id'      	 => $result['col_tag_id'],
				'col_padding'        => $result['col_padding'],
				'col_margin'         => $result['col_margin'],
				'image'      		 => $result['image'],
				'col_images'      	 => $this->getPageRowColImages($result['row_col_id'])
			];
		}
		
		return $pages_row_col_data;
	}
	
	public function getPageRowColImages(int $page_row_col_id):array {
		$page_row_col_image_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "row_col_image WHERE group_code='page' AND  col_id = '" . (int)$page_row_col_id . "' ORDER BY sort_order ASC");
		foreach($query->rows as $result){
			$page_row_col_image_data[] = [
				'col_image_id'      => $result['col_image_id'],
				'col_id'            => $result['col_id'],
				'image'      		=> $result['image']
			];
		}
		
		return $page_row_col_image_data;
	}
	
	
	
	
	public function getAllPages(array $data ):array {
		$sql="SELECT DISTINCT *,(SELECT name FROM " . DB_PREFIX . "page_group WHERE page_group_id = p.page_group_id LIMIT 1) AS pgroup FROM " . DB_PREFIX . "page p LEFT JOIN " . DB_PREFIX . "page_description pd ON p.page_id=pd.page_id WHERE p.store_id='".(int)$this->session->data['store_id']."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$sort_data = array(
			'pd.title',
			'p.pages_group_id',
			'p.sort_order'
		);

		if(isset($data['sort']) && in_array($data['sort'], $sort_data)){
			$sql .= " ORDER BY " . $data['sort'];
		} else{
			$sql .= " ORDER BY pd.title";
		}

		if(isset($data['order']) && ($data['order'] == 'DESC')){
			$sql .= " DESC";
		} else{
			$sql .= " ASC";
		}

		if(isset($data['start']) || isset($data['limit'])){
			if($data['start'] < 0){
				$data['start'] = 0;
			}

			if($data['limit'] < 1){
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getPageStores(int $page_id):array {
		$page_store_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "page_to_store WHERE page_id = '" . (int)$page_id . "'");

		foreach($query->rows as $result){
			$page_store_data[] = $result['store_id'];
		}

		return $page_store_data;
	}

	
	public function getPageSeoUrls(int $page_id): array {
		$page_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'page_id' AND `value` = '" . (int)$page_id . "'");

		foreach ($query->rows as $result) {
			$page_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $page_seo_url_data;
	}


	public function getTotalPages():int{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "page");

		return $query->row['total'];
	}
	
	public function getWidget():array{
		$widget=  [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets WHERE store_id='" . (int)$this->session->data['store_id'] . "' AND controller='pages' LIMIT 1");
		if(isset($query->row['store_id'])){
			return $query->row;
		}
		
		$this->db->query("INSERT INTO  " . DB_PREFIX . "widgets SET store_id='" . (int)$this->session->data['store_id'] . "', controller='pages', items_total='6',items='3', image_width='200',image_height='200'");
		
		return array('items_total'=>'6','items'=>'3','image_width'=>'200','image_height'=>'200');
	}
	
	public function getAllWidgets():array{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets");
		return $query->rows;
	}
	
	public function editWidget(array $widgetdata):void{
		$widget=[];
		$this->db->query("UPDATE " . DB_PREFIX . "widgets SET items_total='".(int)$widgetdata['items_total']."',ctrl_group='".(int)$widgetdata['ctrl_group']."',items='".(int)$widgetdata['items']."',image_width='".(int)$widgetdata['image_width']."',image_height='".(int)$widgetdata['image_height']."' WHERE store_id='" . (int)$this->session->data['store_id'] . "' AND controller='pages'");
		
		return;
	}
	
	public function isPageInStore(int $page_id):bool{
		$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "page_to_store WHERE page_id='".(int)$page_id."' AND store_id='".(int)$this->session->data['store_id']."'");
		
		return ($query->row['total']>0)?true:FALSE;
		
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
	
	public function installPage():void{
		
		/*
		* Yeniden oluşturuluyor
		*/
		
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."page'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page` (`page_id` int(11) NOT NULL,`bottom` int(1) NOT NULL DEFAULT 0,`sort_order` int(3) NOT NULL DEFAULT 0,`status` tinyint(1) NOT NULL DEFAULT 1) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_description` (`page_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(64) NOT NULL,`header` varchar(300) NOT NULL,`style` varchar(500) NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL,`himage` varchar(255) NOT NULL,`fimage` varchar(255) NOT NULL,`css` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_to_layout` (`page_id` int(11) NOT NULL,`store_id` int(11) NOT NULL,`layout_id` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_to_store` (`page_id` int(11) NOT NULL,`store_id` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_group` (`page_group_id` int(11)NOT NULL ,`name` varchar(100) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,`group_explain` varchar(300) CHARACTER SET utf8 COLLATE utf8_turkish_ci NOT NULL,`sort_order` int(11) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_group_description` (`page_group_id` int(11) NOT NULL ,`language_id` int(11) NOT NULL,`title` varchar(64) NOT NULL,`header` varchar(300) NOT NULL,`style` varchar(500) NOT NULL,`description` text NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			
			
			
			//satir
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "row` (`row_id` int(11) NOT NULL ,`page_id` int(11) NOT NULL,`row_padding` varchar(20) NOT NULL,`row_margin` varchar(20) NOT NULL,`row_cells` varchar(5) NOT NULL,`row_tag_id` varchar(40) NOT NULL,`row_class` varchar(300) NOT NULL,`row_sort_order` int(3) NOT NULL,`image` varchar(200) NOT NULL,`status` tinyint(1) NOT NULL DEFAULT 1,`language_id` int(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			//sutun
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "row_col` (`col_id` int(11)NOT NULL ,`page_id` int(11) NOT NULL,`row_id` int(11) NOT NULL,`col_type` int(3) NOT NULL,`col_content` text NOT NULL,`col_content_id` varchar(40) NOT NULL,`col_class` varchar(300) NOT NULL,`col_style` varchar(800) NOT NULL,`image` varchar(200) NOT NULL,`col_sort_order` int(3) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			// resim grubu
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "row_col_image` (`col_image_id` int(11) NOT NULL ,`col_id` int(11) NOT NULL,`page_id` int(11) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(255) NOT NULL,`title` varchar(300) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "row_col_image_description` (`col_image_id` int(11) NOT NULL ,`language_id` int(11) NOT NULL,`col_id` int(11) NOT NULL,`title` varchar(64) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "page_to_group` (`page_group_id` int(11) NOT NULL,`page_id` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page` ADD PRIMARY KEY (`page_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_description` ADD PRIMARY KEY (`page_id`,`language_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_to_layout` ADD PRIMARY KEY (`page_id`,`store_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_to_store` ADD PRIMARY KEY (`page_id`,`store_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_group` ADD PRIMARY KEY (`page_group_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_group_description` ADD PRIMARY KEY (`page_group_id`,`language_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_to_group` ADD PRIMARY KEY (`page_group_id`,`page_id`);";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_group` MODIFY `page_group_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "row` MODIFY `row_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "row_col` MODIFY `col_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "row_col_image` MODIFY `col_image_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page` MODIFY  `page_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}



}