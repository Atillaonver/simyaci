<?php
namespace Opencart\Catalog\Model\Bytao;
class Menu extends \Opencart\System\Engine\Model {
	
	private $_editString = '';
	private $children;
	private $shopUrl ;
	private $megaConfig = array();
	private $_editStringCol = '';
	private $_isLiveEdit = true;
	
	public function getMenuChild( $id,$menu_group_id = 1){
		$store_id = $this->config->get('config_store_id');
		
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'menu m LEFT JOIN '.DB_PREFIX.'menu_description md ON (m.menu_id=md.menu_id ) ';
        $sql .= ' WHERE m.`published`=1 AND m.store_id='.(int)$store_id;
		$sql .= ' AND m.menu_group_id='.$menu_group_id;
		$sql .= ' AND md.language_id='.(int)$this->config->get('config_language_id');
		$sql .= ' ORDER BY `position`  ';
		$query = $this->db->query( $sql );						
		
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getMenuDropdown( $id=0, $selected=0, $menu_group_id = 1 ){
		$this->children = [];
		$children = $this->getMenuChild( $id, $menu_group_id );
		
		foreach($children as $child ){
			$this->children[$child['parent_id']][] = $child;	
		}
		
		$menuData = $this->genMenuOption(0);
		
		return  $menuData;
	}
	
	public function getMenuMaxGroup(){
		$store_id = $this->config->get('config_store_id');
		$sql="SELECT COUNT(*)as TOTAL FROM " . DB_PREFIX . "menu WHERE store_id='".(int)$store_id."' group by menu_group_id";
		
		$query = $this->db->query($sql);
			
		return $query->rows;
	}
	
	public function genMenuOption( $parent, $level=0, $selected=0){
		$output = array();
		if( $this->hasMenuChild($parent) )
		{
			$data = $this->getMenuNodes( $parent );
			
			foreach( $data as $menu){
				$sub = $this->genMenuOption( $menu['menu_id'],$level+1, $selected );
				$output[] = [
					'menu_id'	=> 	$menu['menu_id'],
					'menu' 		=> 	$menu,
					'sub' 		=>	$sub
				];
			}				
		}
		
		return $output;
	}
	
	public function getMenuNodes( $id ){
		return $this->children[$id];
	}
	
	public function hasMenuChild( $id ){
		return isset($this->children[$id]);
	}
	
	
	
	
	
	
	
	
	
	public function getChilds( $id = null, $store_id = 0,$menu_id = 0 ){
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'menu m LEFT JOIN '
		.DB_PREFIX.'menu_description md ON ( m.menu_id=md.menu_id ) ' ;
		//  RIGHT JOIN '.DB_PREFIX.'product_to_category p2c ON (m.item = p2c.category_id)

		$sql .= ' WHERE m.`published`=1 ';
		$sql .= ' AND m.store_id='.(int)$store_id;
		$sql .= ' AND m.menu_id='.(int)$menu_id;
		$sql .= ' AND md.language_id='.(int)$this->config->get('config_language_id');

		if( $id != null ){
			$sql .= ' AND parent_id='.(int)$id;
		}
		$sql .= ' ORDER BY `position`  ';
		$query = $this->db->query( $sql );

		return $query->rows;
	}

	public function hasChild( $id ){
		return isset($this->children[$id]);
	}

	public function getNodes( $id ){
		return $this->children[$id];
	}

	public function getMenu(int $parent , $edit, $params,$menu_id ,$level):string {
		$store_id = (int)$this->config->get('config_store_id');
		
		$this->parserMegaConfig( $params );
		if( $edit ){
			$this->_editString = ' data-id="%s" data-group="%s"  data-cols="%s" ';
		}
		$this->_editStringCol = ' data-colwidth="%s" data-class="%s" ' ;


		$childs = $this->getChilds( null, $store_id ,$menu_id );
		foreach($childs as $child ){
			$child['megaconfig'] = $this->hasMegaMenuConfig( $child );
			if( isset($child['megaconfig']->group) ){
				$child['is_group'] = $child['megaconfig']->group;
			}

			if( isset($child['megaconfig']->submenu) && $child['megaconfig']->submenu == 0){
				$child['menu_class'] = $child['menu_class'] .' disable-menu';
			}

			$this->children[$child['parent_id']][] = $child;
		}

		$parent = 1 ;
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		if(isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))){
			$this->shopUrl = $this->config->get('config_ssl') ;
		}
		else{
			$this->shopUrl = $this->config->get('config_url') ;
		}

		$output = '';
		if( $this->hasChild($parent) ){
			$data = $this->getNodes( $parent );
			// render menu at level 0
			switch( $this->config->get('config_menu_type')){
				case 0: // Standart Menu
				$output = '';
				if($level){
					foreach( $data as $menu ){
						if( isset($menu['megaconfig']->align) ){
							$menu['menu_class'] .= ' '.$menu['megaconfig']->align;
						}
						if( $this->hasChild($menu['megamenu_id']) || $menu['type_submenu'] == 'html'){

							$output .= '<li class="menu-item '.$menu['menu_class'].'" '.$this->renderAttrs($menu).' data-menu-id="'.$menu['megamenu_id'].'">';

							$output .= '<a href="'.$this->getLink( $menu ).'" class="">';

							if($menu['show_title']){
								$output .= html_entity_decode($menu['title']);
							}

							$output .= '</a>';
							if($menu['megamenu_id'] > 1){
								$output .= $this->genTree( $menu['megamenu_id'], 1, $menu );
							}

							$output .= '</li>';
						}
						else
						if( !$this->hasChild($menu['megamenu_id']) && $menu['megaconfig'] && $menu['megaconfig']->rows ){
							$output .= $this->genMegaMenuByConfig( $menu['megamenu_id'], 1, $menu );
						}
						elseif($menu['type'] == 'html'){
							$output .= '<li class="'.$menu['menu_class'].'" '.$this->renderAttrs($menu).' data-menu-id="'.$menu['megamenu_id'].'">';

							if(isset($menu['badges'])){
								$output .= '<span class="'.$menu['badges'].'">'.$menu['badges'].'</span>';
							}


							if($menu['show_title']){
								$output .= $menu['title'];
							}

							$output .= '</li>';
						}
						else{
							$output .= '<li class="menu-item '.$menu['menu_class'].'" '.$this->renderAttrs($menu).' data-menu-id="'.$menu['megamenu_id'].'">';

							$output .= '<a href="'.$this->getLink( $menu ).'">';


							if($menu['show_title']){
								$output .= html_entity_decode($menu['title']);
							}

							$output .= '</a></li>';
						}
					}
				}else{
					foreach( $data as $menu ){
						$output .= '<li class="menu-item">';
						$output .= '<a href="'.$this->getLink( $menu ).'">';
						if($menu['show_title']){
							$output .= html_entity_decode($menu['title']);
						}
						$output .= '</a></li>';
					}
				}
				$output .= '';
				break;


				case 1://W3 menu
				$output = '';
				foreach( $data as $menu ){
					if( isset($menu['megaconfig']->align) ){
						$menu['menu_class'] .= ' '.$menu['megaconfig']->align;
					}
					if( $this->hasChild($menu['megamenu_id']) || $menu['type_submenu'] == 'html'){

						$output .= '<div class="w3-dropdown-hover"><button class="w3-button">';
						if($menu['show_title']){
							$output .= $menu['title'];
						}
						$output .= '</button>';

						if($menu['megamenu_id'] > 1){
							$output .= $this->genTree( $menu['megamenu_id'], 1, $menu );
						}

						$output .= '</div>';
					}
					else
					if( !$this->hasChild($menu['megamenu_id']) && $menu['megaconfig'] && $menu['megaconfig']->rows ){
						$output .= $this->genMegaMenuByConfig( $menu['megamenu_id'], 1, $menu );
					}
					elseif($menu['type'] == 'html'){
						$output .= '<li class="'.$menu['menu_class'].'" '.$this->renderAttrs($menu).'>';

						if(isset($menu['badges'])){
							$output .= '<span class="'.$menu['badges'].'">'.$menu['badges'].'</span>';
						}

						if($menu['show_title']){
							$output .= html_entity_decode($menu['title']);
						}

						$output .= '</li>';
					}
					else{
						$output .= '<li data-menu-id="'.$menu['megamenu_id'].'"><a href="'.$this->getLink( $menu ).'" class="font-bold animated-text-decoration animated-text-decoration-initially-hidden ">';

						if($menu['show_title']){
							$output .= html_entity_decode($menu['title']);
						}

						$output .= '</a></li>';
					}
				}
				$output .= '';

				break;
				case 2:
				case 3:
				default:
			}

		}
		
		
		
		return $output;

	}

	public function genMegaMenuByConfig( $parentId, $level,$menu  ){

		$attrw = '';
		$class = $level > 1 ? "dropdown-submenu":"dropdown";
		$output= '<li class="'.$menu['menu_class'].' parent '.$class.' " '.$this->renderAttrs($menu).'>';

		$output .= '<a href="'.$this->getLink( $menu ).'" class="dropdown-toggle  '.$this->getIs( $menu ).'" data-toggle="dropdown">';

		if(isset($menu['badges'])){
			$output .= '<span class="'.$menu['badges'].'">'.$menu['badges'].'</span>';
		}

		if( $menu['image']){
			$output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu['image'].'\') no-repeat;">';
		}

		$output .= '<span class="menu-title">'.$menu['title']."</span>";
		if( $menu['description'] ){
			$output .= '<span class="menu-desc">' . $menu['description'] . "</span>";
		}
		if( $menu['image']){
			$output .= '</span>';
		}
		$output .= "<b class=\"caret\"></b></a>";

		if( isset($menu['megaconfig']->subwidth) && $menu['megaconfig']->subwidth ){
			$attrw .= ' style="width:'.$menu['megaconfig']->subwidth.'px"' ;
		}
		$class = 'dropdown-menu';
		$output .= '<div class="'.$class.'" '.$attrw.' ><div class="dropdown-menu-inner">';

		foreach( $menu['megaconfig']->rows  as $row ){

			$output .= '<div class="row">';
			foreach( $row->cols as $col ){
				$colclass = isset($col->colclass)?$col->colclass:'';
				$output .= '<div class="mega-col col-xs-12 col-sm-12 col-md-'.$col->colwidth.' '.$colclass.'" '.$this->getColumnDataConfig( $col ).'> <div class="mega-col-inner">';
				$output .= $this->renderWidgetsInCol( $col );
				$output .= '</div></div>';
			}
			$output .= '</div>';
		}
		unset($colclass);

		$output .= '</div></div>';
		$output .= '</li>';
		return $output;
	}

	public function renderWidgetsInCol( $col ){
		if( is_object($col) && isset($col->widgets)  ){
			$widgets = $col->widgets;
			$widgets = explode( '|wid-', '|'.$widgets );
			if( !empty($widgets) ){
				unset( $widgets[0] );
				$this->model_menu_widget->loadWidgets();
				$output = '';
				foreach( $widgets as $wid ){
					$output .= $this->model_menu_widget->renderContent( $wid );
				}

				return $output;
			}
		}
	}

	public function getColumnDataConfig( $col ){
		$output = '';
		/*	if( is_object($col)  && $this->_isLiveEdit ){
		$vars = get_object_vars($col);
		foreach( $vars as $key => $var ){
		$output .= ' data-'.$key.'="'.$var . '" ' ;
		}
		} */
		return $output;
	}
	
	public function getColumnSpans( $col ){

	}

	public function parserMegaConfig( $params ){
		if( !empty($params) ){
			foreach( $params as $param ){
				if( $param && isset($param->id) ){
					$this->megaConfig[$param->id] = $param;
				}
			}
		}
	}

	public function hasMegaMenuConfig( $menu ){
		$id = $menu['menu_id'];
		return isset( $this->megaConfig[$id] )?$this->megaConfig[$id] :array();
	}
	
	public function genTree( $parentId, $level,$parent, $store_id = 0){


		$attrw = '';
		$class = $parent['is_group']?"dropdown-mega":"dropdown-menu";

		if( isset($parent['megaconfig']->subwidth) && $parent['megaconfig']->subwidth ){
			$attrw .= ' style="width:'.$parent['megaconfig']->subwidth.'px"' ;
		}


		if( $parent['type_submenu'] == 'html' ){
			$output = '<div class="'.$class.'"><div class="menu-content">';
			$output .= html_entity_decode($parent['submenu_content']);
			$output .= '</div></div>';
			return $output;
		}
		elseif( $this->hasChild($parentId) ){

			$data = $this->getNodes( $parentId );
			switch( $this->config->get('config_menu_type')){
				case 0: // Standart Menu


				$parent['colums'] = (int)$parent['colums'];
				if( $parent['colums'] > 1  ){
					if( !empty($parent['megaconfig']->rows) ){
						$cols = array_chunk( $data, ceil(count($data) / $parent['colums'])  );
						foreach( $parent['megaconfig']->rows as $rows ){
							foreach( $rows as $rowcols ){
								foreach( $rowcols as $key => $col ){
									$col->colwidth = isset($col->colwidth)?$col->colwidth:6;
									if( isset($col->type) && $col->type == 'menu' && isset($cols[$key]) ){
										//$scol .= '<div class="qua_top_menu" data-parent-id="'.$parentId.'"><ul>';
										//$scol .= '<ul>';
										foreach( $cols[$key] as $menu ){
											$scol .= $this->renderMenuContent( $menu, $level + 1 );
										}
										$scol .= '</ol>';
										//$scol .= '</div>';
									}
									else{
										$scol .= $this->renderWidgetsInCol( $col );
									}
									$output .= $scol;
								}
							}
						}
					}
					else{

						$cols   = array_chunk( $data, ceil(count($data) / $parent['colums'])  );

						$oSpans = $this->getColWidth( $parent, (int)$parent['colums'] );

						foreach( $cols as $i =>  $menus ){


							foreach( $menus as $menu ){
								$output .= $this->renderMenuContent( $menu, $level + 1 );
							}
							$output .= '</ol>';

						}

					}
					return $output;
				}
				else{
					$failse = false;

					if( !empty($parent['megaconfig']->rows) ){
						foreach( $parent['megaconfig']->rows as $rows ){
							foreach( $rows as $rowcols ){

								foreach( $rowcols as $col ){

									if( isset($col->type) && $col->type == 'menu' ){
										$colwidth = isset($col->colwidth)?$col->colwidth:'';
										//$scol .= '<div class="qua_top_menu" data-parent-id="'.$parentId.'"><ul>';
										$scol .= '<ol class="sub-menu lefty">';
										foreach( $data as $menu ){
											$scol .= $this->renderMenuContent( $menu , $level + 1 );
										}
										//$scol .= '</ul></div>';
										$scol .= '</ol>';

									}
									else{

										$scol .= $this->renderWidgetsInCol( $col );
									}

									$output .= $scol;
								}

							}
						}
					}
					else{
						$output = '';
						//$row    = '<div class="qua_top_menu" data-parent-id="'.$parentId.'"><ul>';
						$row    = '<ol class="sub-menu lefty">';
						foreach( $data as $menu ){
							$row .= $this->renderMenuContent( $menu , $level + 1 );
						}
						$row .= '</ol>';
						//$row .= '</ul></div>';
						$output .= $row;
					}

				}

				break;

				case 1:
				$parent['colums'] = (int)$parent['colums'];
				if( $parent['colums'] > 1  ){
					if( !empty($parent['megaconfig']->rows) ){
						$cols = array_chunk( $data, ceil(count($data) / $parent['colums'])  );
						foreach( $parent['megaconfig']->rows as $rows ){
							foreach( $rows as $rowcols ){
								foreach( $rowcols as $key => $col ){
									$col->colwidth = isset($col->colwidth)?$col->colwidth:6;
									if( isset($col->type) && $col->type == 'menu' && isset($cols[$key]) ){
										$scol .= '<div class="w3-dropdown-content w3-bar-block w3-card-2">';
										foreach( $cols[$key] as $menu ){
											$scol .= $this->renderMenuContent( $menu, $level + 1 );
										}
										$scol .= '</div>';
									}
									else{
										$scol .= $this->renderWidgetsInCol( $col );
									}
									$output .= $scol;
								}
							}
						}
					}
					else{

						$cols   = array_chunk( $data, ceil(count($data) / $parent['colums'])  );

						$oSpans = $this->getColWidth( $parent, (int)$parent['colums'] );

						foreach( $cols as $i =>  $menus ){


							foreach( $menus as $menu ){
								$output .= $this->renderMenuContent( $menu, $level + 1 );
							}
							$output .= '</div>';

						}

					}
					return $output;
				}
				else{
					$failse = false;

					if( !empty($parent['megaconfig']->rows) ){
						foreach( $parent['megaconfig']->rows as $rows ){
							foreach( $rows as $rowcols ){

								foreach( $rowcols as $col ){

									if( isset($col->type) && $col->type == 'menu' ){
										$colwidth = isset($col->colwidth)?$col->colwidth:'';
										$scol .= '<div class="w3-dropdown-content w3-bar-block w3-card-2">';
										foreach( $data as $menu ){
											$scol .= $this->renderMenuContent( $menu , $level + 1 );
										}
										$scol .= '</div>';

									}
									else{

										$scol .= $this->renderWidgetsInCol( $col );
									}

									$output .= $scol;
								}

							}
						}
					}
					else{
						$output = '';
						$row    = '<div class="w3-dropdown-content w3-bar-block w3-card-2">';
						foreach( $data as $menu ){
							$row .= $this->renderMenuContent( $menu , $level + 1 );
						}
						$row .= '</div>';
						$output .= $row;
					}

				}




				break;
				case 2:
				case 3:
				default:
			}




			return $output;

		}
		return ;
	}

	public function renderAttrs( $menu ){
		//	$t = sprintf( $this->_editString, $menu['megamenu_id'], $menu['is_group'], $menu['colums']  );
		//if( $this->_isLiveEdit  ){
		//	if( isset($menu['megaconfig']->subwidth) && $menu['megaconfig']->subwidth ){
		//	$t .= ' data - subwidth = "'.$menu['megaconfig']->subwidth.'" ';
		///	}
		//	$t .= ' data - submenu = "'.(isset($menu['megaconfig']->submenu)?$menu['megaconfig']->submenu:$this->hasChild($menu['megamenu_id'])).'"';
		// }
		//	return $t;
	}

	public function renderMenuContent( $menu , $level ){

		$output = '';
		$class  = $menu['is_group']?"mega-group":"";
		$menu['menu_class'] .= ' '.$class;
		if( $menu['type'] == 'html' ){
			$output .= '<li class="'.$menu['menu_class'].'" '.$this->renderAttrs($menu).' data-menu-id="'.$menu['megamenu_id'].'">';
			$output .= '<div class="menu-content">'.html_entity_decode($menu['content_text']).'</div>';
			$output .= '</li>';
			return $output;
		}
		
		switch( $this->config->get('config_menu_type')){
			case 0: // Standart Menu
							
			if( $this->hasChild($menu['megamenu_id']) ){

				$output .= '<li class="menu-item '.$menu['menu_class'].'" '.$this->renderAttrs($menu). ' data-menu-id="'.$menu['megamenu_id'].'">';
				if( $menu['show_title'] ){
					$output .= '<a href="'.$this->getLink( $menu ).'">';
					$t = '%s';
					if( $menu['image']){
						$output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu['image'].'\') no-repeat;">';
					}
					$output .= $menu['title'];
					if( $menu['description'] ){
						$output .= '<span class="menu-desc">' . $menu['description'] . "</span>";
					}
					//$output .= " < b class = \"caret\"></b > ";
					/*if( $menu['image']){
					$output .= '</span>';
					}*/
					$output .= '</a>';
				}
				if($menu['megamenu_id'] > 1){
					$output .= $this->genTree( $menu['megamenu_id'], $level, $menu );
				}
				$output .= '</li>';

			}
			else
			if(  $menu['megaconfig'] && $menu['megaconfig']->rows ){
				$output .= $this->genMegaMenuByConfig( $menu['megamenu_id'], $level, $menu );
			}
			else{
				$output .= '<li class="menu-item '.$menu['menu_class'].'" '.$this->renderAttrs($menu).' data-menu-id="'.$menu['megamenu_id'].'">';
				if( $menu['show_title'] ){
					$output .= '<a href="'.$this->getLink( $menu ).'" class=" '.$this->getIs( $menu ).'">';

					if( $menu['image']){
						$output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu['image'].'\') no-repeat;">';
					}
					$output .= '<span class="menu-title">'.$menu['title']."</span>";
					if( $menu['description'] ){
						$output .= '<span class="menu-desc">' . $menu['description'] . "</span>";
					}
					if( $menu['image']){
						$output .= '</span>';
					}

					$output .= '</a>';
				}
				$output .= '</li>';
			}
				
		
			break;
			
			
			case 1:
			if( $this->hasChild($menu['megamenu_id']) ){

			
				if( $menu['show_title'] ){
					$output .= '<a href="'.$this->getLink( $menu ).'" class="w3-bar-item w3-button  '.$this->getIs( $menu ).'">';
					$t = '%s';
					if( $menu['image']){
						$output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu['image'].'\') no-repeat;">';
					}
					$output .= html_entity_decode($menu['title']);
					if( $menu['description'] ){
						$output .= '<span class="menu-desc">' . $menu['description'] . "</span>";
					}

					$output .= '</a>';
				}
				if($menu['megamenu_id'] > 1){
					$output .= $this->genTree( $menu['megamenu_id'], $level, $menu );
				}
			

			}
			else
			if(  $menu['megaconfig'] && $menu['megaconfig']->rows ){
				$output .= $this->genMegaMenuByConfig( $menu['megamenu_id'], $level, $menu );
			}
			else{
			
				if( $menu['show_title'] ){
					$output .= '<a href="'.$this->getLink( $menu ).'" class ="w3-bar-item w3-button '.$this->getIs( $menu ).'" >';

					if( $menu['image']){
						$output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu['image'].'\') no-repeat;">';
					}
					$output .= '<span class="menu-title">'.$menu['title']."</span>";
					if( $menu['description'] ){
						$output .= '<span class="menu-desc">' . $menu['description'] . "</span>";
					}
					if( $menu['image']){
						$output .= '</span>';
					}

					$output .= '</a>';
				}
			
			}
		
		
			break;
			case 2:
			case 3:
			default:
		}
					
		
		
		
		
		return $output;
	}

	public function getParentCategory($id_child){
		$result = $this->db->query("SELECT `parent_id` FROM `" . DB_PREFIX . "category` WHERE `category_id` = '".$id_child."'");
		return $result->row;
	}

	public function getMenuLink( $menu ){
		$id = (int)$menu['item'];
		
		switch( $menu['type'] ){
			case 'category':
			$parent = $this->getParentCategory($id);
			if( $parent && isset($parent['parent_id']) && $parent['parent_id'] ){
				$id = $parent['parent_id'].'_'.$id;
			}
			return $this->url->link('product/category', 'path=' . $id);
			case 'product':
			return  $this->url->link('product/product', 'product_id=' . $id);
			case 'information':
			return   $this->url->link('information/information', 'information_id=' . $id);
			case 'page':
			return   $this->url->link('bytao/page', 'page_id=' . $id);
			case 'blog':
			return   $this->url->link('bytao/blog', 'blog_id=' . $id);
			case 'manufacturer':
			return  $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $id);
			default:
			return $menu['url'];
		}
	}

	public function getIs( $menu ){
		$id = (int)$menu['item'];
		
		switch( $menu['type'] ){
			case 'category':
			$parent = $this->getParentCategory($id);
			if( $parent && isset($parent['parent_id']) && $parent['parent_id'] ){
				$id = $parent['parent_id'].'_'.$id;
			}
			if(isset($this->request->get['path'])){
				if($id==$this->request->get['path'])
				return 'active';
				else
				return '';
			}
			case 'information':
			return   $this->url->link('information/information', 'information_id=' . $id);
			case 'page':
			return   $this->url->link('bytao/page', 'page_id=' . $id);
			case 'blog':
			return   $this->url->link('bytao/blog', 'blog_id=' . $id);
			case 'manufacturer':
			return  $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $id);
			default:
			return $menu['url'];
		}
	}

	public function getColWidth( $menu, $cols ){
		$output = array();

		$split = preg_split('#\s+#',$menu['submenu_colum_width'] );
		if( !empty($split) && !empty($menu['submenu_colum_width']) ){
			foreach( $split as $sp ){
				$tmp = explode("=",$sp);
				if( count($tmp) > 1 ){
					$output[trim(preg_replace("#col#","",$tmp[0]))] = (int)$tmp[1];
				}
			}
		}
		$tmp   = array_sum($output);
		$spans = array();
		$t = 0;
		for( $i = 1; $i <= $cols; $i++ ){
			if( array_key_exists($i,$output) ){
				$spans[$i] = 'col-sm-'.$output[$i];
			}
			else{
				if( (12 - $tmp) % ($cols - count($output)) == 0 ){
					$spans[$i] = "col-sm-".((12 - $tmp) / ($cols - count($output)));
				}
				else{
					if( $t == 0 ){
						$spans[$i] = "col-sm-".( ((11 - $tmp) / ($cols - count($output))) + 1 ) ;
					}
					else{
						$spans[$i] = "col-sm-".( ((11 - $tmp) / ($cols - count($output))) + 0 ) ;
					}
					$t++;
				}
			}
		}
		return $spans;
	}

	public function getResponsiveTree(){

	}


	public function isInstalled(){
		$sql   = " SHOW TABLES LIKE '".DB_PREFIX."taomenu'";
		$query = $this->db->query( $sql );
		if( count($query->rows) <= 0 ){
			$file = dirname(DIR_APPLICATION).'/admin/model/sample/module.php';
			if( file_exists($file) ){
				require_once( $file );
				$sample = new ModelSampleModule( $this->registry );
				$result = $sample->installSampleQuery( $this->config->get('config_template'),'pavmegamenu', true );
				return true;
			}
		}
		return true;
	}

}