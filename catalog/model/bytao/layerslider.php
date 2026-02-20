<?php 	
namespace Opencart\Catalog\Model\Bytao;
class Layerslider extends \Opencart\System\Engine\Model {

	public function getLayersliderGroup( int $position = 2 ,int $position_id=0):array {
		$SQL='';
		$sliderGroup = [];
		switch($position){
			case 1: // layout
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider_group WHERE position='".(int)$position . "' AND position_id='".(int)$position_id . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			case 2:// home
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider_group WHERE position='".(int)$position . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			case 3:// category
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider_group WHERE position='".(int)$position . "' AND position_id='".(int)$position_id . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			case 4:// Slider
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider_group WHERE position='".(int)$position . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			default:
		}
		
		if($SQL) {
			$query = $this->db->query( $SQL );
			
			if(isset($query->rows)){
				
				$params = array(
					'title' => '',
					'delay' => '10000',
					'height' => '350',
					'width'  => '960',
					
					'touch_mobile' => 1,
					'stop_on_hover' => 1,
					'shuffle_mode'=>'0',
					'image_cropping' => '0',
					'shadow_type' => '2',
					'show_time_line' => '1',
					'time_line_position' => 'top',
					'background_color' => '#d9d9d9',
					'padding'=> '5px 0px',
					'margin' => '0px 0px',
					'background_image' => '0',
					'background_url'  => '',
					'navigator_type' => 'none',
					'navigator_arrows' => 'verticalcentered',
					'navigation_style' => 'round',
					'offset_horizontal' => '0',
					'offset_vertical'   => '20',
					'show_navigator' => '0',
					'hide_navigator_after' => '200',
					'thumbnail_height' => '50',
					'thumbnail_width'  => '100',
					'thumbnail_amount' => '5',
					'hide_screen_width' => ''
				);
				
		 		foreach($query->rows as $row){
		 			$pass=false;
		 			$now = time(); 
		 			$start_date = (int)$row['start_date']?strtotime($row['start_date']):0;
		 			$end_date = (int)$row['end_date']?strtotime($row['end_date']):0;
		 			//this->log->write('start_date:'.$start_date.' end_date:'.$end_date.' now:'.$now);
		 			
					if(!$start_date && !$end_date){
						$pass=TRUE;
					}else if($start_date  && !$end_date){
						if($now >= $start_date){
							$pass=TRUE;
						}
						
					}else if(!$start_date && $end_date){
						if($now <= $end_date){
							$pass=TRUE;
						}
					}
		 			
		 			if($pass){
						$vp = $row['viewpos'];
						if( $row ){
							$row['params'] = unserialize( $row['params'] );
							$row['params'] = array_merge( $params, $row['params'] );
						}else {
							$row['params'] = $params;
						}
						$sliderGroup[$row['viewpos']]= $row;
					}
					
				}
			}
	 	}
		
		return $sliderGroup;
		
	}
	
	public function getLayerslider( int $position,int $position_id):array {
		$SQL='';
		$sliderGroup = [];
		switch($position){
			case 1: // layout
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider WHERE position='".(int)$position . "' AND position_id='".(int)$position_id . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			case 2:// home
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider WHERE position='".(int)$position . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			
			case 3:// category
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider WHERE position='".(int)$position . "' AND position_id='".(int)$position_id . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			case 4:// Slider
				$SQL = " SELECT * FROM ". DB_PREFIX . "layerslider WHERE position='".(int)$position . "' AND status = 1 AND store_id='".(int)$this->config->get('config_store_id')."'" ;
				break;
			default:
		}
		
		if($SQL) {
			$query = $this->db->query( $SQL );
			if($query->rows){
				$params = array(
					'title' => '',
					'delay' => '10000',
					'height' => '350',
					'width'  => '960',
					
					'touch_mobile' => 1,
					'stop_on_hover' => 1,
					'shuffle_mode'=>'0',
					'image_cropping' => '0',
					'shadow_type' => '2',
					'show_time_line' => '1',
					'time_line_position' => 'top',
					'background_color' => '#d9d9d9',
					'padding'=> '5px 0px',
					'margin' => '0px 0px',
					'background_image' => '0',
					'background_url'  => '',
					'navigator_type' => 'none',
					'navigator_arrows' => 'verticalcentered',
					'navigation_style' => 'round',
					'offset_horizontal' => '0',
					'offset_vertical'   => '20',
					'show_navigator' => '0',
					'hide_navigator_after' => '200',
					'thumbnail_height' => '50',
					'thumbnail_width'  => '100',
					'thumbnail_amount' => '5',
					'hide_screen_width' => ''
				);
				
		 		foreach($query->rows as $row){
		 			$pass=false;
		 			$now = time(); 
		 			$start_date = (int)$row['start_date']?strtotime($row['start_date']):0;
		 			$end_date = (int)$row['end_date']?strtotime($row['end_date']):0;
		 			//this->log->write('start_date:'.$start_date.' end_date:'.$end_date.' now:'.$now);
		 			
					if(!$start_date && !$end_date){
						$pass=TRUE;
					}else if($start_date  && !$end_date){
						if($now >= $start_date){
							$pass=TRUE;
						}
						
					}else if(!$start_date && $end_date){
						if($now <= $end_date){
							$pass=TRUE;
						}
					}
		 			
		 			if($pass){
						$vp = $row['viewpos'];
						if( $row ){
							$row['params'] = unserialize( $row['params'] );
							$row['params'] = array_merge( $params, $row['params'] );
						}else {
							$row['params'] = $params;
						}
						$sliderGroup[$row['viewpos']]= $row;
					}
					
				}
			}
	 	}
		return $sliderGroup;
		
	}
	
	public function getLayersliderGroupById( int $id ):array {
		$sql = " SELECT * FROM ". DB_PREFIX . "layerslider_group WHERE layerslider_group_id='".(int)$id."';";
		$query = $this->db->query( $sql );
		$sliderGroup = $query->row;
		$sliderparams = isset($sliderGroup['params'])?$sliderGroup['params']:[];
		
 		if(isset($query->row)){
			 	$params = [
					'title' => '',
					'delay' => '10000',
					'height' => '350',
					'width'  => '960',
					'type'  => '1',
					'touch_mobile' => 1,
					'stop_on_hover' => 1,
					'shuffle_mode'=>'0',
					'image_cropping' => '0',
					'shadow_type' => '2',
					'show_time_line' => '1',
					'time_line_position' => 'top',
					'background_color' => '#fff',
					'padding'=> '5px 0px',
					'margin' => '0px 0px',
					'background_image' => '0',
					'background_url'  => '',
					'navigator_type' => 'none',
					'navigator_arrows' => 'verticalcentered',
					'navigation_style' => 'round',
					'offset_horizontal' => '0',
					'offset_vertical'   => '20',
					'show_navigator' => '0',
					'hide_navigator_after' => '200',
					'thumbnail_height' => '50',
					'thumbnail_width'  => '100',
					'thumbnail_amount' => '5',
					'hide_screen_width' => ''
				];

			 	if( $sliderGroup ){
					$sliderGroup['params'] = unserialize( $sliderGroup['params'] );
					$sliderGroup['params'] = array_merge( $params, $sliderGroup['params'] );
				}else {
					$sliderGroup['params'] = $params;
				}
		}

		return $sliderGroup;
	}

	public function getLayerslidersByGroupId( $groupID):array {
		$sql = "SELECT * FROM ". DB_PREFIX . "layerslider WHERE group_id='".(int)$groupID ."' AND `language_id`='".(int)$this->config->get('config_language_id')."' AND `status` = 1 ORDER BY sort_order ASC;";

		$query = $this->db->query( $sql );
		
		return isset($query->rows)?$query->rows:[];
	}

	public function resizeLayerslider($filename, $width, $height, $type = ""):string {
		if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
			return $filename;
		} 
		
		$info = pathinfo($filename);
		
		$extension = $info['extension'];
		
		$old_image = $filename;
		$new_image = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . $type .'.' . $extension;
		
		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
			$path = '';
			
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));
			
			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				
				if (!file_exists(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}		
			}

			list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
		 
			if ($width_orig != $width || $height_orig != $height) {
				$image = new \Opencart\System\Library\Image(DIR_IMAGE . by_move($old_image));
				
				list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
				if ($type == 'a') {
				    if ($width/$height > $width_orig/$height_orig) {
				        $image->resize($width, $height, 'w');
				    } elseif ($width/$height < $width_orig/$height_orig) {
				        $image->resize($width, $height, 'h');
				    }
				} else {
				    $image->resize($width, $height, $type);
				}

				$image->save(DIR_IMAGE . $new_image);
			} else {
				copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
			}
		}
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			return $new_image;
		} else {
			return $new_image;
		}	
	}
}
