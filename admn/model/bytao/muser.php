<?php
namespace Opencart\Admin\Model\Bytao;
class Muser extends \Opencart\System\Engine\Model {
	
	protected $children = array();

	public function editMenuData( array $data ):int {
		
		if( $data["menu"] ){
			if( isset($data['menu']['menu_id']) && (int)$data['menu']['menu_id'] > 0 ){
				$sql = " UPDATE  ". DB_PREFIX . "menu_user SET  ";
				$tmp = array();
				foreach( $data["menu"] as $key => $value ){
					if( $key != "menu_id" && trim($key) !='' ){
						$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
					}
				}
				$sql .= implode( " , ", $tmp );
				$sql .= " WHERE menu_id=".$data['menu']['menu_id'];
				$this->db->query( $sql );
			} 
			else{
				$sql = "INSERT INTO ".DB_PREFIX . "menu_user ( `";
				$tmp = array();
				$vals = array();
				foreach( $data["menu"] as $key => $value ){
					$tmp[] = $key;
					$vals[]=$this->db->escape($value);
				}				
				$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
				$this->db->query( $sql );
				
				$data['menu']['menu_id'] = $this->db->getLastId();
				
			}
		
		
		}
		$this->load->model('bytao/common');
		$languages = $this->model_bytao_common->getStoreLanguages();
	
		if( isset($data["menu_user_description"]) ){
			$sql = " DELETE FROM ".DB_PREFIX ."menu_user_description WHERE menu_id=".(int)$data["menu"]['menu_id'] ;
			$this->db->query( $sql );
	 
			foreach( $languages as $language ){
				$sql = "INSERT INTO ".DB_PREFIX ."menu_user_description(`language_id`, `menu_id`,`title`,`description`) VALUES(".$language['language_id'].",'".$data['menu']['menu_id']."','".$this->db->escape($data["menu_user_description"][$language['language_id']]['title'])."','"	.$this->db->escape($data["menu_user_description"][$language['language_id']]['description'])."') ";
				$this->db->query( $sql );					
			}
		}
		return $data['menu']['menu_id'];
	}
	
	public function deleteMenu( int $id ,int $user_id):void {
		$childs = $this->getMenuChild($id,$user_id);
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$this->recursiveMenuDelete($id); 
	}
	
	public function recursiveMenuDelete(int $parent_id):void{
		$sql = " DELETE FROM ".DB_PREFIX ."menu_user_description WHERE menu_id=".(int)$parent_id .";";
		$this->db->query($sql);
		
		$sql = " DELETE FROM ".DB_PREFIX ."menu_user WHERE menu_id=".(int)$parent_id .";";
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
	
	public function getMenus($user_id):array {
		$this->load->model('user/user');
		$stores = $this->model_user_user->getStores($user_id);
		
		$sql = " SELECT menu_id FROM " . DB_PREFIX . "menu_user mu LEFT JOIN " . DB_PREFIX . "user_to_store u2s ON mu.user_id= u2s.user_id WHERE u2s.store_id IN ('".implode("','",$stores)."')  GROUP BY mu.menu_id ORDER BY mu.sort_order ";
		
		$query = $this->db->query( $sql );	
		
		foreach($query->rows as $menu_id){
			$menus[] = array('menu_id' => $menu_id['menu_id'] );
		}
						
		return isset($menus)?$menus:array();	
	}
	
	public function getMenuChild( $id ,int $user_id):array {
		$this->load->model('user/user');
		$stores = $this->model_user_user->getStores($user_id);
		
		//$sql = "SELECT mu.*, md.title, md.description FROM " . DB_PREFIX . "menu_user mu LEFT JOIN " . DB_PREFIX . "user_to_store u2s ON (mu.user_id = u2s.user_id ) LEFT JOIN ".DB_PREFIX."menu_user_description md ON (mu.menu_id = md.menu_id AND md.language_id = '".(int)$this->config->get('config_language_id')."') WHERE u2s.store_id IN ('".implode("','",$stores)."') AND mu.user_id='".(int)$user_id."'";
		$sql = "SELECT mu.*, md.title, md.description FROM " . DB_PREFIX . "menu_user mu LEFT JOIN " . DB_PREFIX . "user_to_store u2s ON (mu.user_id = u2s.user_id ) LEFT JOIN ".DB_PREFIX."menu_user_description md ON (mu.menu_id = md.menu_id AND md.language_id = '".(int)$this->config->get('config_language_id')."') WHERE u2s.store_id ='".(int)$this->session->data['store_id']."' AND mu.user_id='".(int)$user_id."'";
		
		if( $id ){
			$sql .= ' AND mu.parent_id='.(int)$id;
		}
		$sql .= ' ORDER BY mu.sort_order';
		$query = $this->db->query( $sql );	
						
		return $query->rows;
	}
	
	public function hasMenuChild(int $id ):bool {
		return isset($this->children[$id]);
	}
	
	public function getMenuNodes( int $id ):array {
		return $this->children[$id];
	}
	
	public function MenuTree() : string {
		$this->children = array();
		$childs = $this->getMenuChild( '',$this->user->getId());
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$parent = 0 ;
		$output = $this->MenuTreeOrder( $parent, 1 );
		
		return $output;
	}
	
	public function MenuTreeOrder( int $parent, int $level, int $selected ):string {
		
		if( $this->hasMenuChild($parent) ){
			
			$data = $this->getMenuNodes( $parent );
			
			if($parent){
				$output = '<ul id="collapse'.$level. $t.'" class="collapse">';
			}else{
				$output = 'id="menu"';
			}
			foreach( $data as $menu ){
				if($menu['url']){
					$url  = $menu['url'].'&user_token=' . $this->session->data['user_token'];
				}elseif($menu['ctrl']){
					$url  = $this->url->link($menu['ctrl'], '&user_token=' . $this->session->data['user_token'], 'SSL') ;
				}else{
					$url  = '' ;
				}
				$icon =	$menu['menu_class']?'<i class="fa '.$menu['menu_class'].'"></i> ':'';
				
				$output .= '<li id="'.$menu['menu_id'].'">';
				$output .= '
				<div><span class="disclose"><span></span></span>'.$icon.($menu['title']?$menu['title']:"").' (ID:'.$menu['menu_id'].') <a class="quickdisplay" rel="id_'.$menu['menu_id'].'" href="'.$url .'">'.$show.'</i></a> <a class="quickedit" rel="id_'.$menu['menu_id'].'" href="'.$url .'">E</a><span class="quickdel" rel="id_'.$menu['menu_id'].'">D</span></div>';
				
				if($menu['menu_id'] > 0){
					$output .= $this->MenuTreeOrder( $menu['menu_id'], $level+1,  $selected );
				}
				$output .= '</li>';
			}
			$output .= '</ul>';
			return $output;
		}
		return '';
	}
	
	public function getMenuTree( $mid ,  int $selected , int $user_id):string {
		$this->children = array();
		$childs = $this->getMenuChild( $mid,$user_id);
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$parent = 0 ;
		$output = $this->genMenuTree( $parent, 1,  $selected,$user_id );
		
		return $output;
	}
	
	public function genMenuTree( int $parent, int $level, int $selected, int $user_id ):string{
		
		if( $this->hasMenuChild($parent) ){
			
			$data = $this->getMenuNodes( $parent );
			
			$t = $level == 1?" sortable":"";
			$output = '<ol class="level'.$level. $t.' ">';

			foreach( $data as $menu ){
				$url  = $this->url->link('bytao/menu', 'id='.$menu['menu_id'].'&user_token=' . $this->session->data['user_token'], 'SSL') ;
				$cls = $menu['menu_id'] == $selected ? 'class="active"':"";
				$show =	$menu['hide']?'<i class="fa fa-eye">':'<i class="fa fa-eye-slash">';
				$icon =	$menu['menu_class']?'<i class="fa '.$menu['menu_class'].'"></i> ':'';
				
				$output .= '<li id="list_'.$menu['menu_id'].'" '.$cls.' >
				<div><span class="disclose"><span></span></span>'.$icon.($menu['title']?$menu['title']:"").' (ID:'.$menu['menu_id'].') <a class="quickdisplay" rel="id_'.$menu['menu_id'].'" href="'.$url .'">'.$show.'</i></a> <a class="quickedit" rel="id_'.$menu['menu_id'].'" href="'.$url .'">E</a><span class="quickdel" rel="id_'.$menu['menu_id'].'">D</span></div>';
				
				if($menu['menu_id'] > 0){
					$output .= $this->genMenuTree( $menu['menu_id'], $level+1,  $selected,$user_id );
				}
				$output .= '</li>';
			}
			$output .= '</ol>';
			return $output;
		}
		return '';
	}
	
	public function getMenuInfo( int $id ):array {	
		$sql = ' SELECT mu.*, md.title,md.description FROM ' . DB_PREFIX . 'menu_user mu LEFT JOIN '.DB_PREFIX.'menu_user_description md ON mu.menu_id=md.menu_id AND md.language_id='.(int)$this->config->get('config_language_id') ;
	
		$sql .= ' WHERE mu.menu_id='.(int)$id;						
	
		$query = $this->db->query( $sql );
		return $query->row;
	}
	
	public function getMenuDropdown( $id, int $selected, int $user_id ):string{
		$childs = $this->getMenuChild( $id, $user_id );
		
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		$output = '<select class="form-control" name="menu[parent_id]" >';
		$output .='<option value="0">ROOT</option>';	
		$output .= $this->genMenuOption( 0 ,1, $selected );
		$output .= '</select>';
		return $output ;
	}
	
	public function genMenuOption( int $parent, int $level, int $selected):string{
		$output = '';
		if( $this->hasMenuChild($parent) ){
			$data = $this->getMenuNodes( $parent );
			
			foreach( $data as $menu ){
				$select = ($selected == $menu['menu_id']) ? 'selected="selected"':"";
				$output .= '<option value="'.$menu['menu_id'].'" '.$select.'>'.str_repeat("-",$level) ." ".$menu['title'].' (ID:'.$menu['menu_id'].')</option>';
				$output .= $this->genMenuOption(  $menu['menu_id'],$level+1, $selected );
			}				
		}
		
		return $output;
	}
	
	public function massMenuUpdate( array $data, $root=0 ):void {
		$child = array();
		
		foreach( $data as $id => $parentId ){
			if(is_null($parentId)|| $parentId <=0 ){
				$parentId = $root;
			}
			$child[$parentId][] = $id;
		}
		
		foreach( $child as $parentId => $menus ){
			$i = 1;
			foreach( $menus as $menuId ){
				$sql = " UPDATE  ". DB_PREFIX . "menu_user SET parent_id=".(int)$parentId.', sort_order='.$i.' WHERE menu_id='.(int)$menuId;
				$this->db->query( $sql );
				$i++;
			}
		}
	}
	
	public function hideMenuUpdate( int $menuId ,int $val ):void{	
		$sql = " UPDATE  ". DB_PREFIX . "menu_user SET hide=".(int)$val.' WHERE menu_id='.(int)$menuId;
		$this->db->query( $sql );
	}
	
	public function getMenuRender( $id, int $selected, int $user_id ):array {
		
		$childs = $this->getMenuChild( $id, $user_id );
		
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		return $this->genMenuRender( 0 ,1, $selected );
	}
	
	public function genMenuRender( int $parent, int $level, int $selected):array{
		$rederData=array();
		
		if( $this->hasMenuChild($parent) ){
			$data = $this->getMenuNodes( $parent );
			
			foreach( $data as $menu ){
				if($menu['hide']){
					$children=$this->genMenuRender(  $menu['menu_id'],$level+1, $selected );
					$rederData[$menu['menu_id']] = array(
						'id'       => $menu['menu_id'],
						'icon'	   => $menu['menu_class'],
						'name'	   => $menu['title'],
						'href'     => $menu['ctrl']?$this->url->link($menu['ctrl'], 'user_token=' . $this->session->data['user_token'], true):( $menu['url']? $menu['url']:''),
						'children' => $children
					);
				}
				
			}				
		}
		return $rederData;
	}
	
	public function getMenuGroupData(int $menu_group_id):array {
		$sql = " SELECT menu_id FROM " . DB_PREFIX . "menu WHERE menu_group_id ='".(int)$menu_group_id ."'  GROUP BY menu_id ";
		$query = $this->db->query( $sql );	
		foreach($query->rows as $menu_id){
			$menus[] = array('menu_id' => $menu_id['menu_id'] );
		}
		return isset($menus)?$menus:array();	
	}
	
	public function getMenuDescription( int $id ):array{
		$sql = 'SELECT * FROM '.DB_PREFIX."menu_user_description WHERE menu_id=".$id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}
	
	public function emptyMenuChildren():void{
		$this->children = array();
	}
	
	public function checkExitItemMenu(int $category):array {
		$query = $this->db->query("SELECT menu_id FROM ".DB_PREFIX."menu WHERE store_id = ".(int)$this->session->data['store_id']." AND `type`='category' AND item=".$category['category_id']);
		return $query->num_rows;
	}
	
	public function getMaxMenu():array{
		$sql = " SELECT MAX(menu_id)+1 AS ma FROM " . DB_PREFIX . "menu_user WHERE store_id ='".(int)$this->session->data['store_id']."'";
		$query = $this->db->query( $sql );	
		return $query->row['ma'];
		
	}
	
	public function pasteMenuTree(int $toId, int $fromId ):void{
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		
		$query = $this->db->query("SELECT DISTINCT mu.* FROM " . DB_PREFIX . "menu_user mu LEFT JOIN " . DB_PREFIX . "user_to_store u2s ON (mu.user_id = u2s.user_id) WHERE u2s.store_id ='".(int)$this->session->data['store_id']."' AND mu.user_id = '" . (int)$fromId . "'");
		
		$ids = array();
		if($query->num_rows){
			foreach($query->rows as $rowData ){
				$sql = "INSERT INTO ".DB_PREFIX . "menu_user ( `";
				$tmp = array();
				$vals = array();
				foreach( $rowData as $key => $value ){
					switch($key){
						case 'menu_id':break;
						case 'user_id':
							$tmp[] = $key;
							$vals[]= $toId;
							break;
						case 'parent_id':
							$tmp[] = $key;
							$vals[]= isset($ids[$value])?$ids[$value]:0;
							break;
						default:
						$tmp[] = $key;
						$vals[]= $value;
					}
				}
								
				$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
				$rowData['menu_user_description'] = $this->getMenuDescription($rowData["menu_id"]);
				
				$this->db->query( $sql );
				$menu_id = $this->db->getLastId();
				
				$ids[$rowData["menu_id"]] = $menu_id ;
				
				if( isset($rowData["menu_user_description"])){
					foreach( $rowData["menu_user_description"] as $lData ){
						$sql = "INSERT INTO ".DB_PREFIX ."menu_user_description(`language_id`, `menu_id`,`title`,`description`) 
						VALUES(".$lData['language_id'].",'".(int)$menu_id."','".$this->db->escape($lData['title'])."','"
						.$this->db->escape($lData['description'])."') ";
						
						$this->db->query( $sql );					
					}
				}
			}
		}
	}
	
	public function addUser(array $data):int{
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "user` SET `username` = '" . $this->db->escape((string)$data['username']) . "', `user_group_id` = '" . (int)$data['user_group_id'] . "', `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "', `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `image` = '" . $this->db->escape((string)$data['image']) . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_added` = NOW()");
		$user_id =$this->db->getLastId();
		$this->db->query("INSERT INTO `" . DB_PREFIX . "user_to_store` SET user_id = '" . (int)$user_id . "', store_id = '" . (int)$this->session->data['store_id'] . "'");
	
		return $user_id;
	}

	public function editUser(int $user_id, array $data):void{
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET `username` = '" . $this->db->escape((string)$data['username']) . "', `user_group_id` = '" . (int)$data['user_group_id'] . "', `firstname` = '" . $this->db->escape((string)$data['firstname']) . "', `lastname` = '" . $this->db->escape((string)$data['lastname']) . "', `email` = '" . $this->db->escape((string)$data['email']) . "', `image` = '" . $this->db->escape((string)$data['image']) . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "' WHERE `user_id` = '" . (int)$user_id . "'");

		if ($data['password']) {
			$this->db->query("UPDATE `" . DB_PREFIX . "user` SET `password` = '" . $this->db->escape(password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT)) . "' WHERE `user_id` = '" . (int)$user_id . "'");
		}
	}

	public function editPassword(int $user_id,string $password):void{
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "', code = '' WHERE user_id = '" . (int)$user_id . "'");
	}

	public function editCode(string $email, string $code):void{
		$this->db->query("UPDATE `" . DB_PREFIX . "user` SET code = '" . $this->db->escape($code) . "' WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function deleteUser(int $user_id):void{
		$this->db->query("DELETE FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
	}

	public function getUser(int $user_id):array {
		$query = $this->db->query("SELECT *, (SELECT ug.name FROM `" . DB_PREFIX . "user_group` ug WHERE ug.user_group_id = u.user_group_id) AS user_group FROM `" . DB_PREFIX . "user` u WHERE u.user_id = '" . (int)$user_id . "'");

		return $query->row;
	}

	public function getUserByUsername(string $username):array{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($username) . "'");

		return $query->row;
	}

	public function getUserByEmail(string $email):array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "user` WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function getUserByCode(string $code):array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE code = '" . $this->db->escape($code) . "' AND code != ''");

		return $query->row;
	}

	public function getUsers(array $data):array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "user` u LEFT JOIN `" . DB_PREFIX . "user_to_store` u2s ON u.user_id = u2s.user_id WHERE u2s.store_id='".(int)$this->session->data['store_id']."'";

		$sort_data = array(
			'u.username',
			'u.status',
			'u.date_added'
		);

		if(isset($data['sort']) && in_array($data['sort'], $sort_data)){
			$sql .= " ORDER BY " . $data['sort'];
		} else{
			$sql .= " ORDER BY u.username";
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

	public function getTotalUsers():int{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` u LEFT JOIN `" . DB_PREFIX . "user_to_store` u2s ON u.user_id = u2s.user_id WHERE u2s.store_id='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}

	public function getTotalUsersByGroupId(int $user_group_id):int{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE user_group_id = '" . (int)$user_group_id . "'");

		return $query->row['total'];
	}

	public function getTotalUsersByEmail(string $email):int{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "user` WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}

	
	public function installMuser():void{

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."menu_user'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			$sql[]  = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."muser` (
			`menu_id` int(11) unsigned NOT NULL,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`parent_id` int(11) NOT NULL DEFAULT '0',
			`item` varchar(255) DEFAULT NULL,
			`store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
			`menu_class` varchar(25) DEFAULT NULL,
			`level` int(11) NOT NULL,
			`sort_order` int(11) NOT NULL,
			PRIMARY KEY (`menu_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";
			
			$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."muser_description` (
			`menu_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`title` varchar(255) NOT NULL,
			`description` text NOT NULL,
			PRIMARY KEY (`menu_id`,`language_id`),
			KEY `name` (`title`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."user_to_store` (
			`user_id` int(11) NOT NULL,
			`store_id` int(11) NOT NULL DEFAULT 0
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
		
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "user_to_store`  ADD PRIMARY KEY (`user_id`,`store_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "muser` ADD PRIMARY KEY (`menu_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "muser` MODIFY `menu_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "muser_description` ADD PRIMARY KEY (`menu_id`,`language_id`);";
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
			
		}
		
	} 
	
}