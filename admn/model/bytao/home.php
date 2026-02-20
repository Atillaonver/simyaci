<?php
namespace Opencart\Admin\Model\Bytao;
class Home extends \Opencart\System\Engine\Model
{
	private $language_id = 0;

	public function editHome(array $data):void
	{
		$this->load->model('bytao/editor');
		$store_id = $this->session->data['store_id'];

		$this->db->query("DELETE FROM " . DB_PREFIX . "home_description WHERE store_id = '" . (int)$store_id . "'");
		$this->model_bytao_editor->deleteRow(['ctrl'=>'home']);

		foreach($data['home_description'] as $language_id => $value)
		{
			$this->db->query("INSERT INTO " . DB_PREFIX . "home_description SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', css = '" . $this->db->escape($value['css']) . "', himage = '" . $this->db->escape($value['himage']) . "', fimage = '" . $this->db->escape($value['fimage']) . "'");

			if(isset($value['rows']))
			{
				$this->model_bytao_editor->addRow(['ctrl'=>'home','rows'=>$value['rows'],'language_id'=>$language_id]);
			}
		}
	}

	public function getHome():void
	{
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "home WHERE store_id='".(int)$this->session->data['store_id']."'");
		if(!isset($query->row['store_id']))
		{
			$this->db->query("INSERT INTO " . DB_PREFIX . "home SET status = '1',store_id='".(int)$this->session->data['store_id']."'");
		}
	}

	public function getHomeDescriptions():array
	{
		$home_description_data = [];

		$sql                   = "SELECT * FROM " . DB_PREFIX . "home_description WHERE store_id = '" . $this->session->data['store_id'] . "'";
		$query                 = $this->db->query($sql);
		$this->load->model('bytao/editor');

		foreach($query->rows as $result)
		{
			
			$home_description_data[$result['language_id']] = [
				'title'           => $result['title'],
				'header'          => $result['header'],
				'rows'            => $this->model_bytao_editor->getRows(['ctrl'=>'home','language_id'=>$result['language_id']]),
				'style'           => $result['style'],
				'meta_title'      => $result['meta_title'],
				'meta_description'=> $result['meta_description'],
				'meta_keyword'    => $result['meta_keyword'],
				'css'             => $result['css'],
				'himage'          => $result['himage'],
				'fimage'          => $result['fimage']
			];
		}
		return $home_description_data;
	}

	public function deleteHome():void
	{
		$store_id = $this->session->data['store_id'];
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "home` WHERE store_id = '" . (int)$store_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "home_description` WHERE store_id = '" . (int)$store_id . "'");
		$this->model_bytao_editor->deleteRow(['ctrl'=>'home']);
	}

	public function getWidget():array
	{
		$widget = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets WHERE store_id='" . (int)$this->session->data['store_id'] . "' AND controller='campaigns' LIMIT 1");
		if(isset($query->row['store_id']))
		{
			return $query->row;
		}

		$this->db->query("INSERT INTO  " . DB_PREFIX . "widgets SET store_id='" . (int)$this->session->data['store_id'] . "', controller='campaigns', items_total='6',items='3', image_width='200',image_height='200'");

		return ['items_total' =>'6','items' =>'3','image_width' =>'200','image_height'=>'200'];
	}

	public function getAllWidgets():array
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "widgets");

		return isset($query->rows)?$query->rows:[];
	}


	public function installHome():void
	{
		$sql   = " SHOW TABLES LIKE '".DB_PREFIX."home'";
		$query = $this->db->query( $sql );

		if( count($query->rows) <= 0 )
		{
			$sql = [];

			$sql[] = "CREATE TABLE `" . DB_PREFIX . "home` (`store_id` int(11) NOT NULL,`status` tinyint(1) NOT NULL DEFAULT 1) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			$sql[] = "CREATE TABLE `" . DB_PREFIX . "home_description` (`store_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(64) NOT NULL,`header` varchar(300) NOT NULL,`style` varchar(500) NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL,`himage` varchar(255) NOT NULL,`fimage` varchar(255) NOT NULL,`css` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			//satir
			$sql[] = "CREATE TABLE `" . DB_PREFIX . "home_row` (`home_row_id` int(11) NOT NULL ,`store_id` int(11) NOT NULL,`row_padding` varchar(20) NOT NULL,`row_margin` varchar(20) NOT NULL,`row_cells` varchar(5) NOT NULL,`row_tag_id` varchar(40) NOT NULL,`row_class` varchar(300) NOT NULL,`row_sort_order` int(3) NOT NULL,`image` varchar(200) NOT NULL,`status` tinyint(1) NOT NULL DEFAULT 1,`language_id` int(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			//sutun
			$sql[] = "CREATE TABLE `" . DB_PREFIX . "home_row_col` (`home_row_col_id` int(11)NOT NULL ,`store_id` int(11) NOT NULL,`home_row_id` int(11) NOT NULL,`col_type` int(3) NOT NULL,`col_content` text NOT NULL,`col_content_id` varchar(40) NOT NULL,`col_class` varchar(300) NOT NULL,`col_style` varchar(800) NOT NULL,`image` varchar(200) NOT NULL,`col_sort_order` int(3) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			// resim grubu
			$sql[] = "CREATE TABLE `" . DB_PREFIX . "home_row_col_image` (`col_image_id` int(11) NOT NULL ,`col_id` int(11) NOT NULL,`store_id` int(11) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(255) NOT NULL,`title` varchar(300) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home` ADD PRIMARY KEY (`store_id`);";

			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_description` ADD PRIMARY KEY (`store_id`,`language_id`);";

			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row` ADD PRIMARY KEY (`home_row_id`);";


			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row_col` ADD PRIMARY KEY (`home_row_col_id`);";


			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row_col_image` ADD PRIMARY KEY (`col_image_id`);";

			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row` MODIFY `home_row_id` int(11) NOT NULL AUTO_INCREMENT;";


			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row_col` MODIFY `home_row_col_id` int(11) NOT NULL AUTO_INCREMENT;";


			$sql[] = "ALTER TABLE `" . DB_PREFIX . "home_row_col_image` MODIFY `col_image_id` int(11) NOT NULL AUTO_INCREMENT;";




			foreach( $sql as $q )
			{
				$query = $this->db->query( $q );
			}
		}
	}

}