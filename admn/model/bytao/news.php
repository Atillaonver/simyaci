<?php
namespace Opencart\Admin\Model\Bytao;
class News extends \Opencart\System\Engine\Model {
	
	public function addNews(array $data):int {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "news SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "', type_id = '" . (int)$data['type_id'] . "', store_id = '" . (int)$this->session->data['store_id'] . "'");

		$news_id = $this->db->getLastId();
		$title='';
		foreach ($data['news_description'] as $language_id => $value) {
			if($title==''){
				$title =	$value['title'];
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "news_description SET news_id = '" . (int)$news_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}


		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "news_to_layout SET news_id = '" . (int)$news_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'news_id=" . (int)$news_id . "', keyword = '" . $this->db->escape($data['keyword']) . "',store_id = '" . (int)$this->session->data['store_id'] . "'");
		}

		if (isset($data['news_image'])) {
			foreach ($data['news_image'] as $news_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "news_image SET news_id = '" . (int)$news_id . "', type = '" .  $this->db->escape($news_image['type']) . "', image = '" .  $this->db->escape($news_image['image']) . "', sort_order = '" . (int)$news_image['sort_order'] . "'");

				$news_image_id = $this->db->getLastId();
				if(isset($news_image['news_image_description'])){
					foreach ($news_image['news_image_description'] as $language_id => $news_image_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "news_image_description SET news_image_id = '" . (int)$news_image_id . "', language_id = '" . (int)$language_id . "', news_id = '" . (int)$news_id . "', title = '" .  $this->db->escape($news_image_description['title']) . "', subtitle = '" .  $this->db->escape($news_image_description['subtitle']) . "', description = '" .  $this->db->escape($news_image_description['description']) . "'");
					}
				}
			}
		}
		
		$this->cache->delete('news');

		return $news_id;
	}

	public function editNews(int $news_id, array $data):void {
		
		$this->db->query("UPDATE " . DB_PREFIX . "news SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "', type_id = '" . (int)$data['type_id'] . "' WHERE news_id = '" . (int)$news_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "news_description WHERE news_id = '" . (int)$news_id . "'");

		$title='';
		foreach ($data['news_description'] as $language_id => $value) {
			if($title==''){
				$title =	$value['title'];
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "news_description SET news_id = '" . (int)$news_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_store WHERE news_id = '" . (int)$news_id . "'");

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "news_to_store SET news_id = '" . (int)$news_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");

		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "news_to_layout SET news_id = '" . (int)$news_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'news_id=" . (int)$news_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'news_id=" . (int)$news_id . "', keyword = '" . $this->db->escape($data['keyword']) . "', store_id = '" . (int)$this->session->data['store_id'] . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_image_description WHERE news_id = '" . (int)$news_id . "'");


		if (isset($data['news_image'])) {
			foreach ($data['news_image'] as $news_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "news_image SET news_id = '" . (int)$news_id . "', type = '" .  $this->db->escape($news_image['type']) . "', image = '" .  $this->db->escape($news_image['image']) . "', sort_order = '" . (int)$news_image['sort_order'] . "'");

				$news_image_id = $this->db->getLastId();
			if(isset($news_image['news_image_description'])){
				foreach ($news_image['news_image_description'] as $language_id => $news_image_description) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "news_image_description SET news_image_id = '" . (int)$news_image_id . "', language_id = '" . (int)$language_id . "', news_id = '" . (int)$news_id . "', title = '" .  $this->db->escape($news_image_description['title']) . "', subtitle = '" .  $this->db->escape($news_image_description['subtitle']) . "', description = '" .  $this->db->escape($news_image_description['description']) . "'");
				}
			}
				
				
			}
		}

		$this->cache->delete('news');

	}

	public function deleteNews(int $news_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_description WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE key = 'news_id' AND value='" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_image_description WHERE news_id = '" . (int)$news_id . "'");
		$this->cache->delete('news');
	}

	public function copyNews(int $news_id):void {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "'");

		if ($query->num_rows) {
			$data = [];

			$data = $query->row;
			
			$data = array_merge($data, array('news_description' => $this->getnewsDescriptions($news_id)));
			$data = array_merge($data, array('news_image' => $this->getnewsImages($news_id)));
			$data = array_merge($data, array('news_layout' => $this->getnewsLayouts($news_id)));
			$data = array_merge($data, array('news_store' => $this->getnewsStores($news_id)));
			
			$this->addnews($data);
		}
	}
	
	public function getNews(int $news_id):array {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'news_id=" . (int)$news_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "') AS keyword FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "'");

		return $query->row;
	}

	public function getNewses(array $data = []):array {
		$languageId = 1;
		if((int)$this->config->get('config_language_id')){
			$languageId = $this->config->get('config_language_id');	
		}else{
			$languageId = 1;
		}
		
		
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id) WHERE id.language_id = '" . (int)$languageId . "' AND i.store_id='".(int)$this->session->data['store_id']."'";
			
			if (isset($data['type'])) {
				$sql .= " AND i.type_id = '" . (int)$data['type']."'";
			}

			$sort_data = array(
				'id.title',
				'i2s.store_id',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$news_data = $this->cache->get('news.' . (int)$languageId);

			if (!$news_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news i LEFT JOIN " . DB_PREFIX . "news_description id ON (i.news_id = id.news_id) LEFT JOIN " . DB_PREFIX . "news_to_store i2s ON (i.news_id = i2s.news_id)  WHERE i2s. store_id='".(int)$this->session->data['store_id']."' AND id.language_id = '" . (int)$languageId . "' ORDER BY id.title");

				$news_data = $query->rows;

				$this->cache->set('news.' . (int)$languageId, $news_data);
			}

			return $news_data;
		}
	}

	public function getNewsImages(int $news_id):array {
		$news_image_data = [];

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
			$news_image_description_data = [];

			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND news_id = '" . (int)$news_id . "'");

			foreach ($news_image_description_query->rows as $news_image_description) {
				$news_image_description_data[$news_image_description['language_id']] = array('title' => $news_image_description['title'],'subtitle' => $news_image_description['subtitle'],'description' => $news_image_description['description']);
			}

			$news_image_data[] = array(
				'news_image_description' => $news_image_description_data,
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
	
	public function getNewsImage(int $news_id,$type=0):array {
		$news_image_data = [];

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE news_id = '" . (int)$news_id . "'  AND type='".(int)$type."' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
			$news_image_description_data = [];

			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND news_id = '" . (int)$news_id . "'");

			foreach ($news_image_description_query->rows as $news_image_description) {
				$news_image_description_data[$news_image_description['language_id']] = array('title' => $news_image_description['title'],'subtitle' => $news_image_description['subtitle'],'description' => $news_image_description['description']);
			}

			$news_image_data[] = array(
				'news_image_description' => $news_image_description_data,
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
	
	public function getNewsSeoUrls(int $art_category_id): array {
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'art_category_id' AND `store_id` ='".(int)$store_id ."' AND `value` = '" . $art_category_id . "'");
		if(isset($query->rows)){
			
			foreach ($query->rows as $result) {
				$category_seo_url_data[$result['language_id']] = $result['keyword'];
			}
		}
		

		return $category_seo_url_data;
	}
	
	
	public function getNewsDescriptions(int $news_id):array {
		$news_description_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_description WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$news_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $news_description_data;
	}

	public function getNewsStores(int $news_id):array {
		$news_store_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_store WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$news_store_data[] = $result['store_id'];
		}

		return $news_store_data;
	}

	public function getnewsLayouts(int $news_id):array {
		$news_layout_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$news_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $news_layout_data;
	}

	public function getTotalNewses():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news i  LEFT JOIN " . DB_PREFIX . "news_to_store i2s ON (i.news_id = i2s.news_id) WHERE i2s. store_id='".(int)$this->session->data['store_id']."'");

		return $query->row['total'];
	}

	public function getTotalNewsesByLayoutId(int $layout_id):int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
	
	public function updateNewsSortOrder(int $sort_order,int $news_id):void{
		$this->db->query("UPDATE " . DB_PREFIX . "news SET sort_order = '" . (int)$sort_order . "' WHERE news_id = '" . (int)$news_id . "'");
	}
	
	public function isInstore(int $news_id):bool{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}
	
	public function installNews():void{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."news'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "news` (`news_id` int(11) NOT NULL,`store_id` int(3) NOT NULL,`image` varchar(255) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0,`date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,`status` tinyint(1) NOT NULL DEFAULT 1) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "news_description` (`news_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` varchar(255) NOT NULL,`header` varchar(500) NOT NULL,`description` text NOT NULL,`style` varchar(500) NOT NULL,`meta_title` varchar(255) NOT NULL,`meta_description` varchar(255) NOT NULL,`meta_keyword` varchar(255) NOT NULL,`css` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			// resim grubu
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "news_image` (`news_image_id` int(11) NOT NULL ,`news_id` int(11) NOT NULL,`link` varchar(255) NOT NULL,`image` varchar(255) NOT NULL,`title` varchar(300) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "news` ADD PRIMARY KEY (`news_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "news_description` ADD PRIMARY KEY (`news_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "news_image` ADD PRIMARY KEY (`news_image_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "news` MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}

}