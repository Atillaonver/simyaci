<?php
class ModelBytaoBlog extends Model {
    
	public function getArticle($article_id) {
		$query = $this->db->query("SELECT *
                FROM " . DB_PREFIX . "blog_article ba   
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                WHERE ba.article_id = '" . (int)$article_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'
                      AND ba.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.date_published < NOW()");

        return $query->row;
	}
    
    
	public function getArticleCategories($article_id) {
		$article_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_to_category batc
                LEFT JOIN " . DB_PREFIX . "blog_category bc ON bc.category_id = batc.category_id
                LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON bcd.category_id = bc.category_id
                WHERE batc.article_id = '" . (int)$article_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND bc.store_id = '" . (int)$this->config->get('config_store_id') . "'") or die(mysql_error());

		foreach ($query->rows as $result) {
			$article_category_data[$result['category_id']]['category_id'] = $result['category_id'];
			$article_category_data[$result['category_id']]['name'] = $result['name'];
		}

		return $article_category_data;
	}
    
    public function getProductRelated($article_id, $limit = null) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "'" . ($limit ? " LIMIT " . (int)$limit : ''));

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}
    
    public function getArticleRelated($article_id, $limit = null) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "'" . ($limit ? " LIMIT " . (int)$limit : ''));

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['related_id'];
		}

		return $article_related_data;
	}
    
    public function getArticleToProductRelated($product_id, $limit = null) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_product_related WHERE related_id = '" . (int)$product_id . "'" . ($limit ? " LIMIT " . (int)$limit : ''));

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['article_id'];
		}

		return $article_related_data;
	}
  
    public function getArticleGalleries($article_id) {
        $galleries = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_gallery
                 WHERE article_id = '" . (int)$article_id . "' ORDER BY sort_order ASC");

        if(!empty($query->rows)){
            foreach($query->rows as $row){
                if($row['type'] == 'IMG'){
                    $row['output'] = $this->prepareImage($row['path'], $row['width'], $row['height']);
                    $galleries[] = $row;
                }
                
                if($row['type'] == 'YOUTUBE'){
                    $row['output'] = $this->prepareYoutube($row['path'], $row['width'], $row['height']);
                    $galleries[] = $row;
                }
                
                if($row['type'] == 'SOUNDCLOUD'){
                    $row['output'] = $this->prepareSoundCloud($row['path'], $row['width'], $row['height']);
                    $galleries[] = $row;
                }
            }
        }
		return $galleries;
	}
    
    public function getArticleAuthor($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_author
                 WHERE article_id = '" . (int)$article_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}
    
	public function getArticles($data = array()) {
		$sql = "SELECT *, ba.article_id FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                LEFT JOIN " . DB_PREFIX . "blog_article_to_category bctc ON bctc.article_id = ba.article_id
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND ba.status = 1 AND ba.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND ba.date_published < NOW()";

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] != 0) {
			$sql .= " AND bctc.category_id = " . $this->db->escape($data['filter_category_id']) . "";
		}
        
        if (!empty($data['filter_author']) && $data['filter_author'] != 0) {
			$sql .= " AND ba.author_id = " . $this->db->escape($data['filter_author']) . "";
		}
        

		if (!empty($data['filter_title']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_title'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_title'])));

				foreach ($words as $word) {
					$implode[] = "bad.title LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

			}

			if (!empty($data['filter_title']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "bad.tags LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}

			$sql .= ")";
		}
        
        
		$sql .= " GROUP BY ba.article_id";

		$sort_data = array(
			'title',
			'sort_order',
			'date_published'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_published";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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
	}
    
	public function getPopularArticles($limit) {
		$sql = "SELECT *, ba.article_id AS articleId FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                LEFT JOIN " . DB_PREFIX . "blog_article_to_category bctc ON bctc.article_id = ba.article_id
                LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.article_id = ba2s.article_id)
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND ba.status = 1 AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.date_published < NOW()
                GROUP BY ba.article_id
                ORDER BY (SELECT count(*) FROM " . DB_PREFIX . "blog_comment bc WHERE bc.article_id = ba.article_id) DESC, ba.date_published  DESC
                LIMIT " . (int)$limit . "
                ";

		$query = $this->db->query($sql);

		return $query->rows;
	}
    
	public function getLatestArticles($limit) {
		$sql = "SELECT *, ba.article_id FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                LEFT JOIN " . DB_PREFIX . "blog_article_to_category bctc ON bctc.article_id = ba.article_id
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                    AND ba.status = 1 AND ba.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.date_updated < NOW() 
                GROUP BY ba.article_id
                ORDER BY ba.date_published DESC
                LIMIT " . (int)$limit . "
                ";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getArticleDescriptions($article_id) {
		$article_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'tags'             => $result['tags'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description'],
				'content'      => $result['content']
			);
		}

		return $article_description_data;
	}

	public function getPopularTags() {
		$tags_data = array();

		$query = $this->db->query("SELECT tags FROM " . DB_PREFIX . "blog_article_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $result) {
            $tags = array_filter(array_map('trim', explode(',', $result['tags'])));
			if(!empty($tags)){
                foreach($tags as $tag){
                    if(!isset($tags_data[$tag])){
                        $tags_data[$tag] = 1;
                    }else{
                        $tags_data[$tag]++;
                    }
                }
            }
		}

		return $tags_data;
	}

	public function getTotalArticles($data) {
        $sql = "SELECT COUNT(DISTINCT ba.article_id) AS total FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                LEFT JOIN " . DB_PREFIX . "blog_article_to_category bctc ON bctc.article_id = ba.article_id
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND ba.status = 1 AND ba.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ba.date_published < NOW() ";

        if (!empty($data['filter_category_id']) && $data['filter_category_id'] != 0) {
			$sql .= " AND bctc.category_id = " . $this->db->escape($data['filter_category_id']) . "";
		}

        if (!empty($data['filter_author']) && $data['filter_author'] != 0) {
			$sql .= " AND ba.author_id = " . $this->db->escape($data['filter_author']) . "";
		}
        
		$sort_data = array(
			'title',
			'sort_order',
			'date_added'
		);


		if (!empty($data['filter_title']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_title'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_title'])));

				foreach ($words as $word) {
					$implode[] = "bad.title LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

			}

			if (!empty($data['filter_title']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "bad.tags LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}

			$sql .= ")";
		}     
        
        
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

        $query = $this->db->query($sql);

        return isset($query->row['total']) ? $query->row['total'] : 0;
	}
       
    private function prepareImage($path, $width, $height)
    {
        if(!$width) $width = 1000;
        if(!$height) $height = 400;
        $path = $this->model_tool_image->resize($path, $width, $height);
        return  '<img src="'. $path . '" alt="media" />';

    }
    
    private function prepareYoutube($path, $width, $height)
    {
        if(!$width) $width = '100%';
        if(!$height) $height = 400;
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $path, $matches);
        $id = isset($matches[1]) ? $matches[1] : 0;
        $path = "https://www.youtube.com/embed/". $id ."?rel=0&showinfo=0&color=white&iv_load_policy=3";
    
        return '<iframe id="ytplayer" type="text/html" width="'.$width.'" height="'.$height.'"
                                src="'. $path.'"
                                frameborder="0" allowfullscreen></iframe> ';
    }
    
    private function prepareSoundCloud($path, $width, $height)
    {
        if(!$width) $width = '100%';
        if(!$height) $height = 170;
        
        if(!@file_get_contents('http://soundcloud.com/oembed?format=js&url='.$path.'&iframe=true')) return false;
        $getValues=file_get_contents('http://soundcloud.com/oembed?format=js&url='.$path.'&iframe=true');
        $decodeiFrame=substr($getValues, 1, -2);
        $jsonObj = json_decode($decodeiFrame);
        return str_replace(array( 'height="400"', 'width="100%"'),array('height="'.$height.'"', 'width="'.$width.'"'), $jsonObj->html);
        
    }

	/* Author */
	public function getAuthor($author_id) {
		$query = $this->db->query("SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_author ba
                 LEFT JOIN  ". DB_PREFIX . "blog_author_description bad ON bad.author_id = ba.author_id
                 WHERE ba.author_id = '" . (int)$author_id . "' AND  bad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getAuthors($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "blog_author";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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
	}

	public function getAuthorDescriptions($author_id) {
		$author_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_author_description WHERE author_id = '" . (int)$author_id . "'");

		foreach ($query->rows as $result) {
			$author_description_data[$result['language_id']] = array(
				'description'      => $result['description']
			);
		}

		return $author_description_data;
	}

	public function getTotalAuthors() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_author");

		return $query->row['total'];
	}	


	/* Category */
	public function getCategory($blog_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "blog_category bc
                LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.category_id = bcd.category_id)
                WHERE bc.category_id = '" . (int)$blog_category_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'"
                . " AND bc.status = '1' AND bc.store_id = '" . (int)$this->config->get('config_store_id') . "' ");

		return $query->row;
	}

	public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category bc
                LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.category_id = bcd.category_id)
                WHERE bc.parent_id = '" . (int)$parent_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND bc.status = '1' AND bc.store_id = '" . (int)$this->config->get('config_store_id') . "' 
                ORDER BY bc.sort_order, LCASE(bcd.name)");

		return $query->rows;
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_category bc
                WHERE bc.parent_id = '" . (int)$parent_id . "' AND bc.status = '1' AND bc.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}
    
    public function getCategoryPath($category_id){
        
        $category = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category bc WHERE bc.category_id = " .(int)($category_id));
        if($category->row['parent_id'] != 0){
               return $this->getCategoryPath($category->row['parent_id']) . '_' . $category_id;
        }
        return $category_id;
   }
    
    /* comment */
    public function addComment($article_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "blog_comment
                SET
                article_id = '" . (int)$article_id . "',
                name = '" . $this->db->escape($data['name']) . "',
                email = '" . $this->db->escape($data['email']) . "',
                content = '" . $this->db->escape($data['content']) . "',
                status = '" . $this->db->escape($data['status']) . "',
                date_added = NOW()");
        

    }

	public function getComment($comment_id) {
		$query = $this->db->query("SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_comment bc
                 WHERE bc.comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function getComments($article_id, $data = array()) {
		$sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_comment bc
                 WHERE bc.article_id = ".(int)$article_id." AND bc.status = 1";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_email'])) {
			$sql .= " WHERE email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		if (!empty($data['filter_date_added'])) {
			$sql .= " WHERE date_added LIKE '" . $this->db->escape($data['date_added']) . "%'";
		}

		$sort_data = array(
			'name',
			'email',
			'date_added',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
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
	}

	public function getTotalComments() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_comment");

		return $query->row['total'];
	}

	public function getTotalCommentsForArticle($article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_comment WHERE article_id = " . (int)$article_id . " AND status = 1");

        return $query->row['total'];
	}

	/* Setting */
	public function getSetting($property) {
		$settings = $this->getSettings();

        return isset($settings[$property]) ? $settings[$property] : null;
	}

	public function getSettings() {
		$sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_settings";
        
		$query = $this->db->query($sql);

		return $query->row;
	}

}
