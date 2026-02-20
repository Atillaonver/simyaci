<?php
namespace Opencart\Admin\Model\Bytao;
class Menu extends \Opencart\System\Engine\Model{
	
	protected $children = [];
	
	public function getMenus():array{
		$sql = " SELECT menu_id FROM " . DB_PREFIX . "menu WHERE store_id ='".(int)(isset($this->session->data['store_id'])?$this->session->data['store_id']:0)."'  GROUP BY menu_id ";
		$query = $this->db->query( $sql );	
		foreach($query->rows as $menu_id){
			$menus[] = array('menu_id' => $menu_id['menu_id'] );
		}
						
		return isset($menus)?$menus:[];	
	}
	
	public function getMenuGroups():array{
		$menus=[];
		$sql = " SELECT menu_group_id FROM " . DB_PREFIX . "menu WHERE store_id ='".(int)(isset($this->session->data['store_id'])?$this->session->data['store_id']:0)."'  GROUP BY menu_group_id ";
		$query = $this->db->query( $sql );	
		if(isset($query->rows)){
			foreach($query->rows as $menu_id){
				$menus[] = $menu_id['menu_group_id'];
			}
		}
		if(!$menus){
			$menus[] = 1;
		}
		return $menus;	
	}
	
	public function getMenuGroupId():int{
		$sql = " SELECT max(menu_group_id)+1 AS menuGroupId FROM " . DB_PREFIX . "menu WHERE store_id ='".(int)(isset($this->session->data['store_id'])?$this->session->data['store_id']:0)."'";
		$query = $this->db->query( $sql );	
		
		return isset($query->row['menuGroupId'])?(int)$query->row['menuGroupId']:1;	
	}
	
	public function getMenuGroupData(int $menu_group_id):array{
		$sql = " SELECT menu_id FROM " . DB_PREFIX . "menu WHERE menu_group_id ='".(int)$menu_group_id ."'  GROUP BY menu_id ";
		$query = $this->db->query( $sql );	
		foreach($query->rows as $menu_id){
			$menus[] = array('menu_id' => $menu_id['menu_id'] );
		}
		return isset($menus)?$menus:[];	
	}
	
	public function getMenuInfo( int $id ):array{	
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'menu m LEFT JOIN '
		.DB_PREFIX.'menu_description md ON m.menu_id = md.menu_id AND language_id='.(int)$this->config->get('config_language_id') ;
	
		$sql .= ' WHERE m.menu_id='.(int)$id;						
	
		$query = $this->db->query( $sql );
		return $query->row;
	}
	
	public function getMenuDescription( int $id ):array{
		$sql = 'SELECT * FROM '.DB_PREFIX."menu_description WHERE menu_id=".$id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}
	
	public function getMenuChild( $id = 0, $menu_group_id = 0):array{
		
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'menu m LEFT JOIN '.DB_PREFIX.'menu_description md ON m.menu_id = md.menu_id AND md.language_id='.(int)$this->config->get('config_language_id') . ' WHERE m.store_id='.(int)$this->session->data['store_id'].' AND m.menu_group_id='.(int)$menu_group_id.' ORDER BY m.position  ';
		
		$query = $this->db->query( $sql );	
						
		return $query->rows;
	}
	
	public function hasMenuChild( int $id ):bool{
		return isset($this->children[$id]);
	}
	
	public function getMenuNodes( int $id ):array{
		return $this->children[$id];
	}
	
	public function deleteMenu( int $id, int $menu_group_id = 1):void{
		$childs = $this->getMenuChild( $id, (int)$this->session->data['store_id'],$menu_group_id );
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$this->recursiveMenuDelete($id); 
	}
	
	public function recursiveMenuDelete(int $parent_id):void{
		$sql = " DELETE FROM ".DB_PREFIX ."menu_description WHERE menu_id=".(int)$parent_id .";";
		$this->db->query($sql);
		$sql = " DELETE FROM ".DB_PREFIX ."menu WHERE store_id = ".(int)$this->session->data['store_id']." AND menu_id=".(int)$parent_id .";";
		$this->db->query($sql);

		if( $this->hasMenuChild($parent_id) ){
			$data = $this->getMenuNodes( $parent_id );
			foreach( $data as $menu ){
				if($menu['menu_id'] > 1){
					$this->recursiveMenuDelete( $menu['menu_id'] );
				}	
			}
		}
	}
	
	public function getMenuTree($mid , int $selected , $menu_group_id ):string{
		
		$this->children = [];
		
		$childs = $this->getMenuChild( $mid, $menu_group_id );
		
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		
		$parent = 0 ;
		$output = $this->genMenuTree( $parent, 1,  $selected );
		
		return $output;
	}
	
	public function emptyMenuChildren():void{
		$this->children = [];
	}
	
	public function genMenuTree( int $parent, int $level, int $selected ):string{
		
		if( $this->hasMenuChild($parent) ){
			
			$data = $this->getMenuNodes( $parent );
			
			$t = $level == 1 ?" sortable":"";
			$output = '<ol class="level'.$level. $t.' ">';

			foreach( $data as $menu ){
				$url  = $this->url->link('bytao/menu', 'id='.$menu['menu_id'].'&user_token=' . $this->session->data['user_token'], 'SSL') ;
				$cls = $menu['menu_id'] == $selected ? 'class="active"':"";
				$output .='<li id="list_'.$menu['menu_id'].'" '.$cls.' >
				<div><span class="disclose"><span></span></span>'.($menu['title']?$menu['title']:"").' (ID:'.$menu['menu_id'].') <a class="quickedit" rel="id_'.$menu['menu_id'].'" href="'.$url .'">E</a><span class="quickdel" rel="id_'.$menu['menu_id'].'">D</span></div>';
				
				if($menu['menu_id'] > 0){
					$output .= $this->genMenuTree( $menu['menu_id'], $level+1,  $selected );
				}
				$output .= '</li>';
			}
			$output .= '</ol>';
			return $output;
		}
		return '';
	}
	
	public function getMenuDropdown( $id, int $selected, $menu_group_id ):string{
		$childs = $this->getMenuChild( $id, $menu_group_id );
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$output = '<select class="form-control" name="menu[parent_id]" >';
		$output .='<option value="0">ROOT</option>';	
		$output .= $this->genMenuOption( 0 ,1, $selected );
		$output .= '</select>';
		return $output ;
	}
	
	public function genMenuOption( int $parent, int $level=0, int $selected=0):string{
		$output = '';
		if( $this->hasMenuChild($parent) ){
			$data = $this->getMenuNodes($parent );
			
			foreach( $data as $menu ){
				$select = $selected == $menu['menu_id'] ? 'selected="selected"':"";
				$output .= '<option value="'.$menu['menu_id'].'" '.$select.'>'.str_repeat("-",$level) ." ".$menu['title'].' (ID:'.$menu['menu_id'].')</option>';
				$output .= $this->genMenuOption(  $menu['menu_id'],$level+1, $selected );
			}				
		}
		
		return $output;
	}
	
	public function massMenuUpdate( array $data, int $root ):void{
		$child = [];
		
		foreach( $data as $id => $parentId ){
			if(is_null($parentId)|| $parentId <=0 ){
				$parentId = $root;
			}
			$child[$parentId][] = $id;
		}
		
		
		foreach( $child as $parentId => $menus ){
			$i = 1;
			foreach( $menus as $menuId ){
				$sql = " UPDATE  ". DB_PREFIX . "menu SET parent_id=".(int)$parentId.', position='.$i.' WHERE menu_id='.(int)$menuId;
				$this->db->query( $sql );
				$i++;
			}
		}
	}
	
	public function checkExitItemMenu(int $category):array{
		$query = $this->db->query("SELECT menu_id FROM ".DB_PREFIX."menu WHERE store_id = ".(int)$this->session->data['store_id']." AND `type`='category' AND item=".$category['category_id']);
		return $query->num_rows;
	}
	
	public function deleteMenuCategories():void{
		$query = $this->db->query("SELECT menu_id FROM ".DB_PREFIX."menu WHERE store_id = ".(int)$this->session->data['store_id']);
		if($query->num_rows){
			foreach($query->rows as $row){
				$this->db->query( "DELETE FROM ".DB_PREFIX ."menu_description WHERE menu_id = ".$row['menu_id'] );
			}
		}
		$this->db->query( "DELETE FROM ".DB_PREFIX ."menu WHERE store_id = ".(int)$this->session->data['store_id'] );
	}
	
	public function importMenuCategories():void{
		$sql = "SELECT cd.`name`,c.* FROM ".DB_PREFIX ."category c
		LEFT JOIN ".DB_PREFIX ."category_description cd ON c.category_id = cd.category_id
		WHERE  cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		ORDER BY parent_id ASC";
		$query = $this->db->query( $sql );
		if($query->num_rows){
			$categories = $query->rows;
		}
		$this->load->model('catalog/category');
		foreach($categories as &$category){
			$category['language'] = $this->model_catalog_category->getCategoryDescriptions($category['category_id']);
			
			if($this->checkExitItemMenu($category) == 0){
				if((int)$category['parent_id'] > 0){
					$query1 = $this->db->query("SELECT menu_id FROM ".DB_PREFIX."menu WHERE store_id = ".(int)$this->session->data['store_id']." AND `type`='category' AND item='".$category['parent_id']."'");
					if($query1->num_rows){
						$menu_parent_id = (int)$query1->row['menu_id'];
					}
				} else{
					$menu_parent_id = 1;
				}
				$this->insertMenuCategory($category, $menu_parent_id);
			}
		}
	}
	
	public function insertMenuCategory(array $category  , int $menu_parent_id):void{
		$data = [];
		$data['menu']['position'] = 99;
		$data['menu']['item'] = $category['category_id'];
		$data['menu']['published'] = 1;
		$data['menu']['parent_id'] = $menu_parent_id;
		$data['menu']['show_title'] = 1;
		$data['menu']['widget_id'] = 1;
		$data['menu']['type_submenu'] = 'menu';
		$data['menu']['type'] = 'category';
		$data['menu']['colums'] = 1;
		$data['menu']['store_id'] = (int)$this->session->data['store_id'];
		$data['menu']['is_group'] = 0;

		$sql = "INSERT INTO ".DB_PREFIX . "menu ( `";
		$tmp = array();
		$vals = array();
		foreach( $data["menu"] as $key => $value ){
			if($key =='url'){
				$tmp[] = $key;
				$lurl =array();
				foreach($value as $lkey=>$val){
					$lurl[]=$lkey.':'.$this->db->escape($val);
				}
				$vals[]=implode(',',$lurl);
			}else{
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}
		}
		$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
		$this->db->query( $sql );
		$data['menu']['menu_id'] = $this->db->getLastId();
	 	
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
	 	
		if( isset($category["language"]) ){
			$sql = " DELETE FROM ".DB_PREFIX ."menu_description WHERE menu_id=".(int)$data["menu"]['menu_id'] ;
			$this->db->query( $sql );
	 		
			foreach( $category["language"] as $key => $categorydes ){
	 			
				$sql = "INSERT INTO ".DB_PREFIX ."menu_description(`language_id`, `menu_id`,`title`)
				VALUES(".$key.",'".$data['menu']['menu_id']."','".$this->db->escape($categorydes['name'])."') ";
				$this->db->query( $sql );
			}
		}
	
	}
	
	public function editMenuData( array $data ):int{

		$query = $this->db->query( "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='badges' AND TABLE_NAME='".DB_PREFIX."menu'");
		if(count($query->rows) <= 0){
			$query = $this->db->query("ALTER TABLE `".DB_PREFIX."menu` ADD COLUMN `badges` text DEFAULT ''");
		}
		
		
		if( $data["menu"] ){
			if(  (int)$data['menu']['menu_id'] > 0 ){
				$sql = " UPDATE  ". DB_PREFIX . "menu SET  ";
				$tmp = array();
				foreach( $data["menu"] as $key => $value ){
					$valN=[];
					if(is_array($value)){
						foreach( $value as $keyN=>$valueN ){
							$valN[]= $keyN.'æ'.$valueN;
						}
						$tmp[] = "`".$key."`='".$this->db->escape(implode(',',$valN))."'";
					} else if( $key != "menu_id" && trim($key) !='' ){
						$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
					}
				}
				$sql .= implode( " , ", $tmp );
				$sql .= " WHERE menu_id=".$data['menu']['menu_id'];
				$this->db->query( $sql );
				
			} 
			else
			{
				$data['menu']['position'] = 99;
				$data['menu']['store_id'] = (int)$this->session->data['store_id'];
				$sql = "INSERT INTO ".DB_PREFIX . "menu ( `";
				$tmp = [];
				$vals = [];
				foreach( $data["menu"] as $key => $value ){
					$valN=[];
					if(is_array($value)){
						foreach( $value as $keyN=>$valueN ){
							$valN[]= $keyN.'æ'.$valueN;
						}
						$tmp[] = $key;
						$vals[]=$this->db->escape(implode(',',$valN));
					} else{
						$tmp[] = $key;
						$vals[]=$this->db->escape($value);
					} 
					
				
				}				
				
				$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
				$this->db->query( $sql );
				$data['menu']['menu_id'] = $this->db->getLastId();
			}
		
		
		}
		$this->load->model('bytao/common');
		$languages = $this->model_bytao_common->getStoreLanguages();
	
		if( isset($data["menu_description"]) ){
			foreach( $languages as $language ){
				
				$this->db->query( " DELETE FROM ".DB_PREFIX ."menu_description WHERE menu_id=".(int)$data["menu"]['menu_id']." AND language_id = ".(int)$language['language_id'] );
				
				if(isset($data["menu_description"][$language['language_id']]['title'])){
					$description = isset($data["menu_description"][$language['language_id']]['description']) ? $data["menu_description"][$language['language_id']]['description']:"";
					
					$sql = "INSERT INTO ".DB_PREFIX ."menu_description(`language_id`, `menu_id`,`title`,`description`)";
					$sql .= " VALUES(".$language['language_id'].",'".$data['menu']['menu_id']."','".$this->db->escape($data["menu_description"][$language['language_id']]['title'])."','".$this->db->escape($description)."') ";
					$this->db->query( $sql );
				}
									
			}
		}
		return $data['menu']['menu_id'];
	}
	
	public function getMaxMenu():int{
		$sql = " SELECT MAX(menu_id)+1 AS ma FROM " . DB_PREFIX . "menu WHERE store_id ='".(int)$this->session->data['store_id']."'";
		$query = $this->db->query( $sql );	
		return $query->row['ma']?$query->row['ma']:1;
		
	}

	public function installMenu():void {

		$qsql = " SHOW TABLES LIKE '".DB_PREFIX."menu'";
		$query = $this->db->query( $qsql );
		
		if( count($query->rows) <=0 ){
			//$file = DIR_APPLICATION.'model/sample/module.php';
			/*
			$file = (DIR_APPLICATION).'model/sample/'.$this->config->get('config_template').'/sample.php';
			if( file_exists($file) ){
			require_once( DIR_APPLICATION.'model/sample/module.php' );
			$sample = new ModelSampleModule( $this->registry );
			$result = $sample->installSampleQuery( $this->config->get('config_template'),'menu', true );
			$result = $sample->installSample( $this->config->get('config_template'),'menu', true );
			}
			*/
		}	


		$sql = " SHOW TABLES LIKE '".DB_PREFIX."menu_widgets'";
		$query = $this->db->query( $sql );
		$sql = [];
		if( count($query->rows) <=0 ){ 
			$sql[]  = "	
			CREATE TABLE IF NOT EXISTS `".DB_PREFIX."menu_widgets` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(250) NOT NULL,
			`type` varchar(255) NOT NULL,
			`params` text NOT NULL,
			`store_id` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ; ";

			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:\"video_code\";s:168:\"&lt;iframe width=&quot;300&quot; height=&quot;315&quot; src=&quot;//www.youtube.com/embed/cUhPA5qIxDQ&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (2, 'Demo HTML Sample', 'html', 'a:1:{s:4:\"html\";a:1:{i:1;s:275:\"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.\";}}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (3, 'Products Latest', 'product_list', 'a:4:{s:9:\"list_type\";s:6:\"newest\";s:5:\"limit\";s:1:\"6\";s:11:\"image_width\";s:3:\"120\";s:12:\"image_height\";s:3:\"120\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:\"category_id\";s:2:\"20\";s:5:\"limit\";s:1:\"6\";s:11:\"image_width\";s:3:\"120\";s:12:\"image_height\";s:3:\"120\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (5, 'Manufactures', 'banner', 'a:4:{s:8:\"group_id\";s:1:\"8\";s:11:\"image_width\";s:2:\"80\";s:12:\"image_height\";s:2:\"80\";s:5:\"limit\";s:2:\"12\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."menu_widgets` VALUES (6, 'PavoThemes Feed', 'feed', 'a:1:{s:8:\"feed_url\";s:55:\"http://www.pavothemes.com/opencart-themes.feed?type=rss\";}', 0);";

			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}


		$sql = " SHOW TABLES LIKE '".DB_PREFIX."menu'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			$sql[]  = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."menu` (
			`menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`menu_group_id` int(11) NOT NULL DEFAULT '0',
			`image` varchar(255) NOT NULL DEFAULT '',
			`parent_id` int(11) NOT NULL DEFAULT '0',
			`is_group` smallint(6) NOT NULL DEFAULT '2',
			`width` varchar(255) DEFAULT NULL,
			`submenu_width` varchar(255) DEFAULT NULL,
			`colum_width` varchar(255) DEFAULT NULL,
			`submenu_colum_width` varchar(255) DEFAULT NULL,
			`item` varchar(255) DEFAULT NULL,
			`colums` varchar(255) DEFAULT '1',
			`type` varchar(255) NOT NULL,
			`is_content` smallint(6) NOT NULL DEFAULT '2',
			`show_title` smallint(6) NOT NULL DEFAULT '1',
			`type_submenu` varchar(10) NOT NULL DEFAULT '1',
			`level_depth` smallint(6) NOT NULL DEFAULT '0',
			`published` smallint(6) NOT NULL DEFAULT '1',
			`store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
			`position` int(11) unsigned NOT NULL DEFAULT '0',
			`show_sub` smallint(6) NOT NULL DEFAULT '0',
			`url` varchar(255) DEFAULT NULL,
			`target` varchar(25) DEFAULT NULL,
			`privacy` smallint(5) unsigned NOT NULL DEFAULT '0',
			`position_type` varchar(25) DEFAULT 'top',
			`menu_class` varchar(25) DEFAULT NULL,
			`parent_class` varchar(25) DEFAULT NULL,
			`description` text,
			`content_text` text,
			`submenu_content` text,
			`level` int(11) NOT NULL,
			`left` int(11) NOT NULL,
			`right` int(11) NOT NULL,
			`widget_id` int(11) DEFAULT '0',
			`badges` text DEFAULT '',
			PRIMARY KEY (`menu_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;";
			
			$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."menu_description` (
			`menu_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`title` varchar(255) NOT NULL,
			`description` text NOT NULL,
			PRIMARY KEY (`menu_id`,`language_id`),
			KEY `name` (`title`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
			
		}
		
		$query = $this->db->query( "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
			AND COLUMN_NAME='widget_id' AND TABLE_NAME='".DB_PREFIX."menu'");
		if(count($query->rows) <= 0){
			$query = $this->db->query("ALTER TABLE `".DB_PREFIX."menu` ADD COLUMN `widget_id` int DEFAULT '0'");
		}
		
	}
	 
	
}