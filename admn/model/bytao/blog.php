<?php
namespace Opencart\Admin\Model\Bytao;
class Blog extends \Opencart\System\Engine\Model {
	/* Article */
	public function addArticle($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article
                SET
                author_id = '" . (int)$data['author_id'] . "',
                article_list_gallery_display = '" . $data['article_list_gallery_display'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                status = '" . (int)$data['status'] . "',
                store_id = '" . (int)$this->session->data['store_id'] . "',
                status_comments = '" . (int)$data['status_comments'] . "',
                date_updated = '".$data['date_updated']."',
                date_published = '".$data['date_published']."',
                date_added = '".$data['date_added']."'");

		$article_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_article SET image = '" . $this->db->escape($data['image']) . "' WHERE article_id = '" . (int)$article_id . "'");
		}

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_description
                    SET
                    article_id = '" . (int)$article_id . "',
                    language_id = '" . (int)$language_id . "',
                    title = '" . $this->db->escape($value['title']) . "',
                    description = '" . $this->db->escape($value['description']) . "',
                    content = '" . $this->db->escape($value['content']) . "',
                    tags = '" . $this->db->escape($value['tags']) . "',
                    meta_title = '" . $this->db->escape($value['meta_title']) . "',
                    meta_description = '" . $this->db->escape($value['meta_description']) . "',
                    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
        

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
        
        $sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_settings";
    
		$settings = $this->db->query($sql)->row;
		if (isset($data['article_gallery'])) {
			foreach ($data['article_gallery'] as $article_gallery) {
                
                //prepare main image/video
                switch($article_gallery['type']){
                    case 'IMG':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_image_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_image_height'];
                        }
                        $article_gallery['output'] = $this->prepareImage($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                    case 'YOUTUBE':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_youtube_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_youtube_height'];
                        }
                        $article_gallery['output'] = $this->prepareYoutube($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                    case 'SOUNDCLOUD':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_soundcloud_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_soundcloud_height'];
                        }
                        $article_gallery['output'] = $this->prepareSoundCloud($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                }
                
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_gallery
                         SET article_id = '" . (int)$article_id . "',
                         path = '" . $this->db->escape($article_gallery['path']) . "',
                         width = '" . $this->db->escape($article_gallery['width']) . "',
                         height = '" . $this->db->escape($article_gallery['height']) . "',
                         type = '" . $this->db->escape($article_gallery['type']) . "',
                         output = '" . $this->db->escape($article_gallery['output']) . "',
                         sort_order = '" . (int)$article_gallery['sort_order'] . "'");
			}
		}
        
        if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_product_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "'");
			}
		}

        if (isset($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "'");
			}
		}
		
		if(isset($data['keyword'])){
			foreach($data['keyword'] as $language_id => $keyword){
					if(!empty($keyword)){
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$this->session->data['store_id'] . "', language_id = '" . (int)$language_id . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "',`title` = '".(isset($data['article_description'][$language_id]['title'])?$data['article_description'][$language_id]['title']:"" )."'");
					}
			}
		}
		


		return $article_id;
	}

	public function editArticle($article_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "blog_article
               SET
                author_id = '" . (int)$data['author_id'] . "',
                article_list_gallery_display = '" . $data['article_list_gallery_display'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                status = '" . (int)$data['status'] . "',
                status_comments = '" . (int)$data['status_comments'] . "',
                date_updated = '".$data['date_updated']."',
                date_published = '".$data['date_published']."',
                date_added = '".$data['date_added']."'
                WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_article SET image = '" . $this->db->escape($data['image']) . "' WHERE article_id = '" . (int)$article_id . "'");
		}
        
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_description
                    SET 
                    article_id = '" . (int)$article_id . "',
                    language_id = '" . (int)$language_id . "',
                    title = '" . $this->db->escape($value['title']) . "',
                    description = '" . $this->db->escape($value['description']) . "',
                    content = '" . $this->db->escape($value['content']) . "',
                    tags = '" . $this->db->escape($value['tags']) . "',
                    meta_title = '" . $this->db->escape($value['meta_title']) . "',
                    meta_description = '" . $this->db->escape($value['meta_description']) . "',
                    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_gallery WHERE article_id = '" . (int)$article_id . "'");
       
        $sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_settings";
    
		$settings = $this->db->query($sql)->row;
		if (isset($data['article_gallery'])) {
			foreach ($data['article_gallery'] as $article_gallery) {
                
                //prepare main image/video
                switch($article_gallery['type']){
                    case 'IMG':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_image_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_image_height'];
                        }
                        $article_gallery['output'] = $this->prepareImage($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                    case 'YOUTUBE':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_youtube_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_youtube_height'];
                        }
                        $article_gallery['output'] = $this->prepareYoutube($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                    case 'SOUNDCLOUD':
                        if(!$article_gallery['width']){
                            $article_gallery['width'] = $settings['gallery_soundcloud_width'];
                        }
                        if(!$article_gallery['height']){
                            $article_gallery['height'] = $settings['gallery_soundcloud_height'];
                        }
                        $article_gallery['output'] = $this->prepareSoundCloud($article_gallery['path'], $article_gallery['width'], $article_gallery['height']);
                        break;
                }
                
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_gallery
                         SET article_id = '" . (int)$article_id . "',
                         path = '" . $this->db->escape($article_gallery['path']) . "',
                         width = '" . $this->db->escape($article_gallery['width']) . "',
                         height = '" . $this->db->escape($article_gallery['height']) . "',
                         type = '" . $this->db->escape($article_gallery['type']) . "',
                         output = '" . $this->db->escape($article_gallery['output']) . "',
                         sort_order = '" . (int)$article_gallery['sort_order'] . "'");
			}
		}

        
        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_product_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "'");
			}
		}
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_related'])) {
			foreach ($data['article_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "blog_article_related SET article_id = '" . (int)$article_id . "', related_id = '" . (int)$related_id . "'");
			}
		}
        
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");
        
        if(isset($data['keyword'])){
        	
			foreach($data['keyword'] as $language_id => $keyword){
					if(!empty($keyword)){
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$this->session->data['store_id'] . "', language_id = '" . (int)$language_id . "', query = 'article_id=" . (int)$article_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
			}
		}

	}

	public function deleteArticle($article_id) {
	        try{
	            $this->db->query('START TRANSACTION');
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_description WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_to_category WHERE article = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_product_related WHERE related_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_related WHERE related_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_article_gallery WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "blog_comment WHERE article_id = '" . (int)$article_id . "'");
	            $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");
	            $this->db->query('COMMIT');
	        } catch (Exception $ex) {
	            $this->db->query('ROLLBACK');
	            return false;
	        }
	        
	        return true;
	}

	public function getArticle($article_id) {
		$query = $this->db->query("SELECT *
                FROM " . DB_PREFIX . "blog_article ba   
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                WHERE ba.article_id = '{$article_id}' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "' ");

        return $query->row;
	}
    
    public function getArticleSeoUrl($article_id) {
		$page_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'article_id=" . (int)$article_id . "'");

		foreach($query->rows as $result){
			$page_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $page_seo_url_data;
	}
    
	public function getArticleCategories($article_id) {
		$article_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_category_data[] = $result['category_id'];
		}

		return $article_category_data;
	}
    
    public function getProductRelated($article_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_product_related WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}
    
    public function getArticleRelated($article_id) {
		$article_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_related WHERE article_id = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_related_data[] = $result['related_id'];
		}

		return $article_related_data;
	}

	public function getArticleGalleries($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_gallery
                 WHERE article_id = '" . (int)$article_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}
    
	public function getArticles($data = array()) {
		$sql = "SELECT ba.*, bad.*, a.name as author FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                LEFT JOIN " . DB_PREFIX . "blog_author a ON (ba.author_id = a.author_id)
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba.store_id='". (int)$this->session->data['store_id'] . "'";

		if (!empty($data['filter_title'])) {
			$sql .= " AND bad.title LIKE '%" . $this->db->escape($data['filter_title']) . "%'";
		}

		$sql .= " GROUP BY ba.article_id";

		$sort_data = array(
			'title',
			'date_published',
			'author'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if($data['sort'] == 'author'){
                $data['sort'] = 'a.name';
            }
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


	public function getTotalArticles($data) {
		$sql = "SELECT COUNT(DISTINCT ba.article_id) AS total FROM " . DB_PREFIX . "blog_article ba
                LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.article_id = bad.article_id)
                WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba.store_id='". (int)$this->session->data['store_id'] . "'";

		if (!empty($data['filter_title'])) {
			$sql .= " AND bad.title LIKE '%" . $this->db->escape($data['filter_title']) . "%'";
		}

        $query = $this->db->query($sql);
        
		return $query->row['total'];
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
	
	/* author */
	public function addAuthor($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "blog_author SET name = '" . $this->db->escape($data['name']) . "', store_id = '" . (int)$this->session->data['store_id'] . "',sort_order = '" . (int)$data['sort_order'] . "'");

		$author_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_author SET image = '" . $this->db->escape($data['image']) . "' WHERE author_id = '" . (int)$author_id . "'");
		}

        foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_author_description SET author_id = '" . (int)$author_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "'");
		}



		return $author_id;
	}

	public function editAuthor($author_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "blog_author SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE author_id = '" . (int)$author_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_author SET image = '" . $this->db->escape($data['image']) . "' WHERE author_id = '" . (int)$author_id . "'");
		}

        $this->db->query("DELETE FROM " . DB_PREFIX . "blog_author_description WHERE author_id = '" . (int)$author_id . "'");

        foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_author_description SET author_id = '" . (int)$author_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "'");
		}


	}

	public function deleteAuthor($author_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_author WHERE author_id = '" . (int)$author_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_author_description WHERE author_id = '" . (int)$author_id . "'");

	}

	public function getAuthor($author_id) {
		$query = $this->db->query("SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_author WHERE author_id = '" . (int)$author_id . "'");

		return $query->row;
	}

	public function getAuthors($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "blog_author WHERE store_id = '" . (int)$this->session->data['store_id'] . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
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
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_author WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	/* Category */
	public function addCategory($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "blog_category
                SET parent_id = '" . (int)$data['parent_id'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                store_id = '" . (int)$this->session->data['store_id'] . "',
                status = '" . (int)$data['status'] . "',
                date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if(isset($data['keyword'])){
			foreach($data['keyword'] as $language_id => $keyword){
					if(!empty($keyword)){
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$this->session->data['store_id'] . "', language_id = '" . (int)$language_id . "', query = 'blog_category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
			}
		}

		return $category_id;
	}

	public function editCategory($category_id, $data) {

		$this->db->query("UPDATE " . DB_PREFIX . "blog_category
                 SET
                parent_id = '" . (int)$data['parent_id'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                status = '" . (int)$data['status'] . "',
                date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "blog_category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "blog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_category_id=" . (int)$category_id . "'");
		
		if(isset($data['keyword'])){
			foreach($data['keyword'] as $language_id => $keyword){
					if(!empty($keyword)){
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$this->session->data['store_id'] . "', language_id = '" . (int)$language_id . "', query = 'blog_category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
			}
		}


		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "blog_category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "blog_category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}


	}

	public function deleteCategory($category_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_category_path WHERE category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category_path WHERE path_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_category_description WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'blog_category_id=" . (int)$category_id . "'");

		$this->cache->delete('category');

	}

	public function getCategorySeoUrl($category_id) {
		$page_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'blog_category_id=" . (int)$category_id . "'");

		foreach($query->rows as $result){
			$page_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $page_seo_url_data;
	}
    
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "blog_category_path cp LEFT JOIN " . DB_PREFIX . "blog_category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "blog_category c LEFT JOIN " . DB_PREFIX . "blog_category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCategories($data = array()) {
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "blog_category_path cp LEFT JOIN " . DB_PREFIX . "blog_category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "blog_category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "blog_category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "blog_category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c1.store_id = '" . (int)$this->session->data['store_id'] . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

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

	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}
    

	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_category WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}
	
	/* Comment */
	public function addComment($data) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "blog_comment
                SET
                name = '" . $this->db->escape($data['name']) . "',
                email = '" . $this->db->escape($data['email']) . "',
                content = '" . $this->db->escape($data['content']) . "',
                status = '" . $this->db->escape($data['status']) . "'");

		$comment_id = $this->db->getLastId();

		return $comment_id;
	}

	public function editComment($comment_id, $data) {

        $this->db->query("UPDATE " . DB_PREFIX . "blog_comment
                SET
                name = '" . $this->db->escape($data['name']) . "',
                email = '" . $this->db->escape($data['email']) . "',
                content = '" . $this->db->escape($data['content']) . "',
                status = '" . $this->db->escape($data['status']) . "'
                WHERE comment_id = '" . (int)$comment_id . "'");


	}

	public function deleteComment($comment_id) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "blog_comment WHERE comment_id = '" . (int)$comment_id . "'");

	}

	public function getComment($comment_id) {
		$query = $this->db->query("SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_comment bc
                 WHERE bc.comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function getComments($data = array()) {
		$sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_comment bc  WHERE bc.store_id = '" . (int)$this->session->data['store_id'] . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_email'])) {
			$sql .= " AND email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added LIKE '" . $this->db->escape($data['date_added']) . "%'";
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
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_comment WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function getTotalArticleComments($article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "blog_comment WHERE article_id = " . (int)$article_id);

		return $query->row['total'];
	}

	/* Setting */
	public function editSettings($data) {

        $this->db->query("UPDATE " . DB_PREFIX . "blog_settings
                SET
                gallery_image_width = '".$data['gallery_image_width']."',
                gallery_image_height = '".$data['gallery_image_height']."',
                gallery_youtube_width = '".$data['gallery_youtube_width']."',
                gallery_youtube_height = '".$data['gallery_youtube_height']."',
                gallery_soundcloud_width = '".$data['gallery_soundcloud_width']."',
                gallery_soundcloud_height = '".$data['gallery_soundcloud_height']."',
                gallery_related_article_width = '".$data['gallery_related_article_width']."',
                gallery_related_article_height = '".$data['gallery_related_article_height']."',
                article_list_template = '".(isset($data['article_list_template'])?$data['article_list_template']:'')."',
                article_detail_template = '".(isset($data['article_detail_template'])?$data['article_detail_template']:'')."',
                article_related_template = '".(isset($data['article_related_template'])?$data['article_related_template']:'')."',
                article_page_limit = '".$data['article_page_limit']."',
                article_related_status = '".$data['article_related_status']."',
                article_scroll_related = '".$data['article_scroll_related']."',
                article_related_per_row = '".$data['article_related_per_row']."',
                product_related_status = '".$data['product_related_status']."',
                product_scroll_related = '".$data['product_scroll_related']."',
                product_related_per_row = '".$data['product_related_per_row']."',
                pagination_type = '".$data['pagination_type']."',
                himage = '".$data['himage']."',
                comments_approval = '".$data['comments_approval']."',
                comments_engine = '".$data['comments_engine']."',
                disqus_name = '".$data['disqus_name']."',
                facebook_id = '".$data['facebook_id']."',
                author_description = '".$data['author_description']."'
                ") or die(mysql_error());
        

	}

	public function getSetting($property) {
		$settings = $this->getSettings();
		return $settings[$property] ? $settings[$property] : null;
	}

	public function getSettings() {
		$sql = "SELECT DISTINCT *  FROM " . DB_PREFIX . "blog_settings";
        
		$query = $this->db->query($sql);

		return $query->row;
	}

	
	/* Setup */
	public function install(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."blog%'";
		$query = $this->db->query( $sql );
		if( count($query->rows) <=0 ){ 
			$this->createTables();
		}

	}
    
    public function uninstall(){
		
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."blog%'";
		$query = $this->db->query( $sql );
		if( count($query->rows) > 0 ){ 
			$this->dropTables();
		}
        
	}

	public function createTables(){
		$queries = array();
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_article` (
                `article_id` INT(11) NOT NULL AUTO_INCREMENT,
                `author_id` INT(11) NOT NULL,
                `article_list_gallery_display` ENUM('CLASSIC','SLIDER') NOT NULL DEFAULT 'CLASSIC',
                `sort_order` INT(3) NOT NULL DEFAULT '0',
                `image` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) NOT NULL,
                `status_comments` TINYINT(1) NOT NULL,
                `store_id` INT(3) NOT NULL,
                `date_added` DATETIME NOT NULL,
                `date_published` DATETIME NOT NULL,
                `date_updated` DATETIME NOT NULL,
                PRIMARY KEY (`article_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=1
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_article_description` (
                `article_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `content` TEXT NOT NULL,
                `tags` VARCHAR(255) NOT NULL,
                `meta_title` VARCHAR(255) NOT NULL,
                `meta_description` VARCHAR(255) NOT NULL,
                `meta_keyword` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`article_id`, `language_id`),
                INDEX `name` (`title`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_article_gallery` (
                `article_item_id` INT(11) NOT NULL AUTO_INCREMENT,
                `article_id` INT(11) NOT NULL,
                `path` VARCHAR(512) NULL DEFAULT NULL,
                `width` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `height` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                `type` ENUM('IMG','YOUTUBE','SOUNDCLOUD') NULL DEFAULT 'IMG',
                `output` TEXT NULL,
                `sort_order` INT(3) NOT NULL DEFAULT '0',
                PRIMARY KEY (`article_item_id`),
                INDEX `product_id` (`article_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=1
		";
		
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_article_related` (
                `article_id` INT(11) NOT NULL,
                `related_id` INT(11) NOT NULL,
                PRIMARY KEY (`related_id`, `article_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=FIXED
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_article_to_category` (
                `article_id` INT(11) NOT NULL,
                `category_id` INT(11) NOT NULL,
                PRIMARY KEY (`article_id`, `category_id`),
                INDEX `category_id` (`category_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=FIXED
		";
		
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_author` (
                `author_id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `image` VARCHAR(255) NULL DEFAULT NULL,
                `sort_order` INT(3) NOT NULL,
                `store_id` INT(3) NOT NULL,
                PRIMARY KEY (`author_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=1
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_author_description` (
                `author_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `description` TEXT NOT NULL,
                PRIMARY KEY (`author_id`, `language_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=FIXED
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_category` (
                `category_id` INT(11) NOT NULL AUTO_INCREMENT,
                `image` VARCHAR(255) NULL DEFAULT NULL,
                `parent_id` INT(11) NOT NULL DEFAULT '0',
                `column` INT(3) NOT NULL,
                `sort_order` INT(3) NOT NULL DEFAULT '0',
                `store_id` INT(3) NOT NULL DEFAULT '0',
                `status` TINYINT(1) NOT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`category_id`),
                INDEX `parent_id` (`parent_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=1
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_category_description` (
                `category_id` INT(11) NOT NULL,
                `language_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `meta_title` VARCHAR(255) NOT NULL,
                `meta_description` VARCHAR(255) NOT NULL,
                `meta_keyword` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`category_id`, `language_id`),
                INDEX `name` (`name`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_category_path` (
                `category_id` INT(11) NOT NULL,
                `path_id` INT(11) NOT NULL,
                `level` INT(11) NOT NULL,
                PRIMARY KEY (`category_id`, `path_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=FIXED
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_comment` (
                `comment_id` INT(11) NOT NULL AUTO_INCREMENT,
                `article_id` INT(11) NOT NULL,
                `store_id` INT(11) NOT NULL DEFAULT '0',
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NULL DEFAULT NULL,
                `content` TEXT NOT NULL,
                `status` TINYINT(4) NOT NULL DEFAULT '0',
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`comment_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
            AUTO_INCREMENT=1
		";
	
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_product_related` (
                `article_id` INT(11) NOT NULL,
                `related_id` INT(11) NOT NULL,
                PRIMARY KEY (`related_id`, `article_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=FIXED
		";
		$queries[] = "
            CREATE TABLE `".DB_PREFIX."blog_settings` (
                `settings_id` TINYINT(3) UNSIGNED NOT NULL,
                `gallery_image_width` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1000',
                `gallery_image_height` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '400',
                `gallery_youtube_width` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1000',
                `gallery_youtube_height` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '800',
                `gallery_soundcloud_width` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1000',
                `gallery_soundcloud_height` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '140',
                `gallery_related_article_width` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '100',
                `gallery_related_article_height` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '100',
                `article_list_template` VARCHAR(255) NOT NULL DEFAULT 'default.tpl',
                `article_detail_template` VARCHAR(255) NOT NULL DEFAULT 'default.tpl',
                `article_related_template` VARCHAR(255) NOT NULL DEFAULT 'default.tpl',
                `article_page_limit` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '5',
                `article_related_per_row` SMALLINT(5) UNSIGNED NOT NULL,
                `article_related_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
                `article_scroll_related` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
                `product_related_per_row` SMALLINT(5) UNSIGNED NOT NULL,
                `product_related_status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
                `product_scroll_related` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
                `product_per_row` SMALLINT(5) UNSIGNED NOT NULL,
                `pagination_type` ENUM('STANDARD','AJAX') NOT NULL DEFAULT 'STANDARD',
                `comments_engine` ENUM('LOCAL','DISQUS','FACEBOOK') NOT NULL DEFAULT 'LOCAL',
                `disqus_name` VARCHAR(255) NOT NULL,
                `himage` VARCHAR(255) NOT NULL,
                `facebook_id` VARCHAR(255) NOT NULL,
                `comments_approval` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
                `author_description` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1',
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`settings_id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=MyISAM
            ROW_FORMAT=DYNAMIC
		";
       

		foreach( $queries as $query ){
			$this->db->query( $query );
		}
        
        // INSERT DEFAULT SETTINGS
        $this->db->query("INSERT INTO `" . DB_PREFIX . "blog_settings` (`settings_id`, `gallery_image_width`, `gallery_image_height`, `gallery_youtube_width`, `gallery_youtube_height`, `gallery_soundcloud_width`, `gallery_soundcloud_height`, `article_list_template`, `article_detail_template`, `article_related_template`, `article_page_limit`, `article_related_per_row`, `article_related_status`, `article_scroll_related`, `product_related_per_row`, `product_related_status`, `product_scroll_related`, `product_per_row`, `pagination_type`, `comments_engine`, `disqus_name`, `facebook_id`, `comments_approval`, `author_description`, `date_modified`, `gallery_related_article_width`, `gallery_related_article_height`) VALUES
        (1, 1000, 400, 1000, 400, 1000, 140, 'grid_3_columns_with_thumbnails.tpl', 'default.tpl', 'type_2.tpl', 10, 3, 1, 0, 4, 1, 0, 3, 'AJAX', 'LOCAL', 'intellect', '185688598160957', 1, 1, '0000-00-00 00:00:00', 262, 343)");
        
        // INSERT LAYOUT
        $this->db->query("INSERT INTO " . DB_PREFIX . "layout SET name = 'Blog'");

		$layout_id = $this->db->getLastId();
        $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', store_id = '0', route = 'blog/blog'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', store_id = '0', route = 'blog/article'"); 
        
        $this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
        if(!empty($stores)){
            foreach($stores as $store){
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', store_id = '" . (int)$store['store_id'] . "', route = 'blog/blog'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "layout_route SET layout_id = '" . (int)$layout_id . "', store_id = '" . (int)$store['store_id'] . "', route = 'blog/article'"); 
            }
        }

		return true;
	}
    
	public function dropTables(){
		$queries = array();
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_article`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_article_description`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_article_gallery`
		";
		
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_article_related`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_article_to_category`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_author`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_author_description`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_category`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_category_description`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_category_path`
		";
		
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_comment`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_comment_description`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_product_related`
		";
		$queries[] = "
			DROP TABLE IF EXISTS `".DB_PREFIX."blog_settings`
		";

		foreach( $queries as $query ){
			$this->db->query( $query );
		}

        // Delete layout
        $query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout WHERE name = 'Blog'");

        if($query->row){
            $layout_id = $query->row['layout_id'];
            if($layout_id){
                $this->db->query("DELETE FROM " . DB_PREFIX . "layout WHERE layout_id = '" . (int)$layout_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int)$layout_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

            }
        }

        
		return true;
	}
    
    function installSampleData()
    {
         $this->uninstall();
         $this->install();
        $sql = " SHOW TABLES LIKE '".DB_PREFIX."blog%'";
		$query = $this->db->query( $sql );
        $queries = array();
		if( count($query->rows) > 0){
            
            $this->load->model('localisation/language');
            $data['languages'] = $this->model_localisation_language->getLanguages();
            
            $language_id = 2;
            foreach($data['languages'] as $language) {
            	if($language['language_id'] != 1) {
            		$language_id = $language['language_id'];
            	}
            }
            
            
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article` (`article_id`, `author_id`, `article_list_gallery_display`, `sort_order`, `image`, `status`, `status_comments`, `date_added`, `date_published`, `date_updated`) VALUES
                    (27, 0, 'CLASSIC', 2, 'catalog/blog/16.jpg', 1, 1, '2015-09-16 00:10:18', '2015-09-16 00:09:56', '2015-09-16 00:09:56'),
                    (18, 0, 'SLIDER', 2, 'catalog/blog/laptopmockup_sliderdy-150x150.jpg', 1, 1, '2015-09-16 00:10:24', '2015-09-16 00:00:02', '2015-09-16 00:00:02'),
                    (65, 0, 'CLASSIC', 0, 'catalog/blog/4.jpg', 1, 1, '2015-09-12 16:49:41', '2015-09-12 16:49:41', '2015-09-12 16:49:41'),
                    (64, 0, 'SLIDER', 0, 'catalog/blog/3.jpg', 1, 1, '2015-09-06 18:50:44', '2015-09-06 18:50:44', '2015-09-06 18:50:44'),
                    (25, 0, 'CLASSIC', 3, 'catalog/blog/5.jpg', 1, 1, '2015-09-03 01:09:25', '2015-09-03 01:09:25', '2015-09-03 01:09:25'),
                    (20, 0, 'CLASSIC', 1, 'catalog/blog/6.jpg', 1, 1, '2015-09-01 21:09:43', '2015-09-01 21:09:43', '2015-09-01 21:09:43'),
                    (63, 0, 'CLASSIC', 0, 'catalog/blog/7.jpg', 1, 1, '2015-08-27 00:08:35', '2015-08-26 22:16:12', '2015-08-26 22:16:12'),
                    (34, 0, 'CLASSIC', 7, 'catalog/blog/2-150x150.jpg', 1, 0, '2015-09-16 22:27:46', '2015-08-11 12:22:11', '2015-08-11 12:22:11'),
                    (66, 0, 'CLASSIC', 0, 'catalog/blog/9.jpg', 1, 1, '2014-01-21 15:01:26', '2014-01-21 15:01:26', '2014-01-21 15:01:26'),
                    (67, 0, 'CLASSIC', 0, 'catalog/blog/12.jpg', 1, 1, '2013-11-19 15:11:57', '2013-11-19 15:11:57', '2013-11-19 15:11:57'),
                    (68, 0, 'CLASSIC', 0, 'catalog/blog/13.jpg', 1, 1, '2014-10-19 15:15:12', '2014-10-19 15:15:12', '2014-10-19 15:15:12'),
                    (69, 0, 'CLASSIC', 0, 'catalog/blog/16.jpg', 1, 1, '2015-05-19 15:18:51', '2015-05-19 15:18:51', '2015-05-19 15:18:51'),
                    (70, 0, 'CLASSIC', 0, 'catalog/blog/15.jpg', 1, 1, '2014-10-19 15:23:19', '2014-10-19 15:23:19', '2014-10-19 15:23:19')
                ";
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article_description` (`article_id`, `language_id`, `title`, `description`, `content`, `tags`, `meta_title`, `meta_description`, `meta_keyword`) VALUES
                    (18, 1, 'Nulla dictum consequat lorem ac vehicula', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse&lt;br&gt;&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.he Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;', 'Fastor,Buy It,Professional', 'Nulla dictum consequat lorem ac vehicula.', '', ''),
                    (65, 1, 'Etiam ac aliquet ex nec volutpat orci', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula.&amp;nbsp;&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', 'Fastor,Passion, Buy It', 'Etiam ac aliquet ex, nec volutpat orci', '', ''),
                    (65, " . $language_id . ", 'Etiam ac aliquet ex nec volutpat orci', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula.&amp;nbsp;&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Cras ut tortor mollis, tristique magna feugiat, suscipit ligula. Mauris at nibh ornare, congue neque et, vestibulum nibh. Donec bibendum, sem maximus aliquam facilisis, lectus sapien vestibulum turpis, quis feugiat eros justo id ex. Etiam ac aliquet ex, nec volutpat orci. Vestibulum sit amet vestibulum lorem. Aliquam non risus ligula. Phasellus urna turpis, varius ut finibus et, luctus vitae lectus.&lt;/span&gt;&lt;/p&gt;', 'Media Center,Passion, Buy It', 'Etiam ac aliquet ex, nec volutpat orci', '', ''),
                    (25, " . $language_id . ", 'Phasellus ullamcorper lobortis metus', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, &lt;/p&gt;&lt;p&gt;The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price. &lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;br&gt;&lt;/p&gt;', 'Gaming, Media Center,Design', 'Phasellus ullamcorper lobortis metus', '', ''),
                    (20, " . $language_id . ", 'Curabitur sit amet nisl non erat suscipi', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&amp;nbsp;&lt;br&gt;&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World,&lt;/p&gt;&lt;p&gt; Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and &lt;/p&gt;&lt;p&gt;Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price. Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and&lt;br&gt;&lt;/p&gt;', 'Gaming, Media Center, Beatiful, Design, Awesome, Buy It', 'Curabitur sit amet nisl non erat suscipit ultricies sed a ante.', '', ''),
                    (63, 1, 'Salamcorper lobortis metus', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&amp;nbsp;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&amp;nbsp;&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks,&lt;br&gt;&lt;/p&gt;', 'Mobile, Love, Quality, Portfolio', 'Salamcorper lobortis metus', '', ''),
                    (34, " . $language_id . ", 'Fusce sapien urna feugiat sit amet sem eu', '&lt;p&gt;\r\n	Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;br&gt;&lt;/p&gt;', 'Media Center,Buy It, Gaming, Passion,Design', 'Fusce sapien urna, feugiat sit amet sem eu, vestibulum porta ex. ', '', ''),
                    (27, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Media Center, Beatiful, Design, Awesome, Buy It, Design, Quality', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (64, 1, ' Suspendisse dolor purus, laoreet nec', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo. Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Arius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo. &lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo. Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', 'Features, Buy It, Passion', ' Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio', '', ''),
                    (64, " . $language_id . ", ' Suspendisse dolor purus, laoreet nec', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo. Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Arius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo. Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;&amp;nbsp;Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio. Nunc nunc augue, auctor at porta eget, malesuada quis sapien. Phasellus lorem nulla, tristique eget tempor id, bibendum nec leo.&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;text-align: justify;&quot;&gt;Phasellus varius urna volutpat nisi tristique, at pellentesque metus gravida. Mauris nisl augue, tristique vel aliquet bibendum, eleifend ac metus.&lt;/span&gt;&lt;/p&gt;', 'Features, Buy It, Passion', ' Suspendisse dolor purus, laoreet nec tincidunt ut, dignissim vehicula odio', '', ''),
                    (18, " . $language_id . ", 'Nulla dictum consequat lorem ac vehicula', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse&lt;br&gt;&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.he Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;', 'Media Center,Buy It,Professional', 'Nulla dictum consequat lorem ac vehicula.', '', ''),
                    (25, 1, 'Phasellus ullamcorper lobortis metus', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, &lt;/p&gt;&lt;p&gt;The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price. &lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;br&gt;&lt;/p&gt;', 'Gaming, Fastor,Design', 'Phasellus ullamcorper lobortis metus', '', ''),
                    (20, 1, 'Curabitur sit amet nisl non erat suscipi', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&amp;nbsp;&lt;br&gt;&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World,&lt;/p&gt;&lt;p&gt; Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and &lt;/p&gt;&lt;p&gt;Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price. Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and&lt;br&gt;&lt;/p&gt;', 'Gaming, Fastor, Beatiful, Design, Awesome, Buy It', 'Curabitur sit amet nisl non erat suscipit ultricies sed a ante.', '', ''),
                    (63, " . $language_id . ", 'Salamcorper lobortis metus', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals.&amp;nbsp;&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&amp;nbsp;&lt;/p&gt;&lt;p&gt;&lt;br&gt;&lt;/p&gt;&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks,&lt;br&gt;&lt;/p&gt;', 'Mobile, Love, Quality, Portfolio', 'Salamcorper lobortis metus', '', ''),
                    (34, 1, 'Fusce sapien urna feugiat sit amet sem eu', '&lt;p&gt;\r\n	Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;/p&gt;\r\n', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.&lt;br&gt;&lt;/p&gt;', 'Fastor,Buy It, Gaming, Passion,Design', 'Fusce sapien urna, feugiat sit amet sem eu, vestibulum porta ex. ', '', ''),
                    (27, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Fastor, Beatiful, Design, Awesome, Buy It, Design, Quality', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (66, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Awesome, Buy It, Design, Quality', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (66, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', '', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (67, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Quality', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (67, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Quality', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (68, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Awesome', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (68, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Awesome', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', '');
               ";
               
               $queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article_description` (`article_id`, `language_id`, `title`, `description`, `content`, `tags`, `meta_title`, `meta_description`, `meta_keyword`) VALUES
                    (69, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Buy It, Design', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (69, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Buy It, Design', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (70, 1, 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Awesome', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', ''),
                    (70, " . $language_id . ", 'Donec ut nunc sit amet urna aliquet', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks.&lt;br&gt;&lt;/p&gt;', '&lt;p&gt;Shop Laptop feature only the best laptop deals on the market. By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;&lt;p&gt;By comparing laptop deals from the likes of PC World, Comet, Dixons, The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.The Link and Carphone Warehouse, Shop Laptop has the most comprehensive selection of laptops on the internet. At Shop Laptop, we pride ourselves on offering customers the very best laptop deals. From refurbished laptops to netbooks, Shop Laptop ensures that every laptop - in every colour, style, size and technical spec - is featured on the site at the lowest possible price.Shop Laptop feature only the best laptop deals on the market.&lt;/p&gt;', 'Design, Awesome', 'Donec ut nunc sit amet urna aliquet sodales et ut ipsum. I', '', '')
                    ";
            
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article_gallery` (`article_item_id`, `article_id`, `path`, `width`, `height`, `type`, `output`, `sort_order`) VALUES
                    (2746, 27, 'catalog/blog/8.jpg', 1272, 720, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/8-1272x720.jpg\" alt=\"media\" />', 1),
                    (2743, 20, 'catalog/blog/14.jpg', 1200, 600, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/14-1200x600.jpg\" alt=\"media\" />', 1),
                    (2738, 18, 'catalog/blog/slider-1.jpg', 1200, 600, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/slider-1-1200x600.jpg\" alt=\"media\" />', 2),
                    (2737, 18, 'catalog/blog/slider-2.jpg', 1200, 600, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/slider-2-1200x600.jpg\" alt=\"media\" />', 1),
                    (2741, 68, 'catalog/blog/blog-post-image.jpg', 1200, 908, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/blog-post-image-1200x908.jpg\" alt=\"media\" />', 1),
                    (2747, 69, 'catalog/blog/8.jpg', 1272, 720, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/8-1272x720.jpg\" alt=\"media\" />', 1),
                    (2744, 65, 'https://w.soundcloud.com/player/?visual=true&amp;url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F140783505&amp;show_artwork=true', 1200, 140, 'SOUNDCLOUD', '<iframe width=\"1200\" height=\"140\" scrolling=\"no\" frameborder=\"no\" src=\"https://w.soundcloud.com/player/?visual=true&url=http%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F140783505&show_artwork=true&amp=\"></iframe>', 3),
                    (2736, 25, 'https://www.youtube.com/watch?v=KU2xnNLalyk', 1200, 400, 'YOUTUBE', '<iframe id=\"ytplayer\" type=\"text/html\" width=\"1200\" height=\"400\"\r\n                                src=\"https://www.youtube.com/embed/KU2xnNLalyk?rel=0&showinfo=0&color=white&iv_load_policy=3\"\r\n                                frameborder=\"0\" allowfullscreen></iframe> ', 1),
                    (2728, 66, 'catalog/blog/10.jpg', 1200, 1009, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/10-1200x1009.jpg\" alt=\"media\" />', 1),
                    (2729, 67, 'catalog/blog/11.jpg', 1200, 1000, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/11-1200x1000.jpg\" alt=\"media\" />', 1),
                    (2732, 70, 'catalog/blog/14.jpg', 1200, 600, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/14-1200x600.jpg\" alt=\"media\" />', 1),
                    (2742, 64, 'catalog/blog/slider-1.jpg', 1200, 600, 'IMG', '<img src=\"http://localhost:8888/OpenCart/fastor/2.0.3/image/cache/catalog/blog/slider-1-1200x600.jpg\" alt=\"media\" />', 1)
            ";
                
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article_related` (`article_id`, `related_id`) VALUES
                    (18, 20),
                    (27, 20),
                    (64, 20),
                    (18, 34),
                    (64, 34),
                    (18, 64),
                    (27, 64),
                    (27, 65)
                ";
                $queries[] = "
                     INSERT INTO `" . DB_PREFIX . "blog_product_related` (`article_id`, `related_id`) VALUES
                     (27, 28),
                     (27, 30),
                     (27, 41),
                     (27, 47)
                 ";
	
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_article_to_category` (`article_id`, `category_id`) VALUES
                    (18, 31),
                    (20, 25),
                    (25, 30),
                    (25, 33),
                    (27, 17),
                    (27, 31),
                    (34, 28),
                    (34, 31),
                    (62, 30),
                    (63, 25),
                    (64, 17),
                    (65, 26),
                    (66, 17),
                    (66, 20),
                    (66, 24),
                    (67, 17),
                    (67, 20),
                    (67, 24),
                    (68, 17),
                    (68, 20),
                    (68, 24),
                    (69, 17),
                    (69, 20),
                    (69, 24),
                    (70, 17),
                    (70, 20),
                    (70, 24)
                ";
            
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_category` (`category_id`, `image`, `parent_id`, `column`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES 
                    (25, '', 0, 1, 9, 1, '2009-01-31 01:04:25', '2015-10-04 21:03:22'),
                    (27, '', 20, 0, 2, 1, '2009-01-31 01:55:34', '2015-10-04 21:06:17'),
                    (20, '', 0, 1, 1, 1, '2009-01-05 21:49:43', '2015-10-04 21:06:11'),
                    (24, '', 0, 1, 5, 1, '2009-01-20 02:36:26', '2015-10-04 21:02:31'),
                    (18, '', 0, 0, 2, 1, '2009-01-05 21:49:15', '2015-10-04 21:06:49'),
                    (17, '', 0, 1, 4, 1, '2009-01-03 21:08:57', '2015-10-04 21:02:20'),
                    (28, '', 25, 0, 1, 1, '2009-02-02 13:11:12', '2015-10-04 21:03:40'),
                    (26, '', 20, 0, 1, 1, '2009-01-31 01:55:14', '2015-10-04 21:03:12'),
                    (30, '', 25, 0, 1, 1, '2009-02-02 13:11:59', '2015-10-04 21:03:31'),
                    (31, '', 25, 0, 1, 1, '2009-02-03 14:17:24', '2015-10-04 21:04:00'),
                    (33, '', 0, 1, 6, 1, '2009-02-03 14:17:55', '2015-10-04 21:05:41'),
                    (34, '', 0, 4, 7, 1, '2009-02-03 14:18:11', '2015-10-04 21:06:45'),
                    (45, '', 18, 0, 0, 1, '2010-09-24 18:29:16', '2015-10-04 21:05:23'),
                    (46, '', 18, 0, 0, 1, '2010-09-24 18:29:31', '2015-09-15 22:10:08'),
                    (57, '', 0, 1, 3, 1, '2011-04-26 08:53:16', '2015-10-04 21:05:52');
                ";
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES 
                    (46, 1, 'Quotes', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Quotes', '', ''),
                    (18, 1, 'Links &amp; Quotes', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Links &amp; Quotes', '', ''),
                    (25, 1, 'Entertaiment', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Entertaiment', '', ''),
                    (34, 1, 'Events', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Events', '', ''),
                    (45, 1, 'Links', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Links', '', ''),
                    (24, 1, 'Company', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Company', '', ''),
                    (26, 1, 'Logistic', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Logistic', '', ''),
                    (31, 1, 'Theater', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Theater', '', ''),
                    (27, 1, 'Car', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Car', '', ''),
                    (20, 1, 'Enterprise', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Enterprise', '', ''),
                    (33, 1, 'Sports', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Sports', '', ''),
                    (57, 1, 'Stories', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Stories', '', ''),
                    (28, 1, 'Gaming', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Gaming', '', ''),
                    (30, 1, 'Film', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Film', '', ''),
                    (17, 1, 'Business', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Business', '', '');
                ";
            
            // Langs
            foreach($data['languages'] as $language) {
                if($language['language_id'] != 1) {
                    $language_ids = $language['language_id'];
                    $queries[] = "
                            INSERT INTO `" . DB_PREFIX . "blog_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES 
                            (46, $language_ids, 'Quotes', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Quotes', '', ''),
                            (18, $language_ids, 'Links &amp; Quotes', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Links &amp; Quotes', '', ''),
                            (25, $language_ids, 'Entertaiment', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Entertaiment', '', ''),
                            (34, $language_ids, 'Events', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Events', '', ''),
                            (45, $language_ids, 'Links', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Links', '', ''),
                            (24, $language_ids, 'Company', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Company', '', ''),
                            (26, $language_ids, 'Logistic', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Logistic', '', ''),
                            (31, $language_ids, 'Theater', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Theater', '', ''),
                            (27, $language_ids, 'Car', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Car', '', ''),
                            (20, $language_ids, 'Enterprise', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;\r\n', 'Enterprise', '', ''),
                            (33, $language_ids, 'Sports', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Sports', '', ''),
                            (57, $language_ids, 'Stories', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Stories', '', ''),
                            (28, $language_ids, 'Gaming', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Gaming', '', ''),
                            (30, $language_ids, 'Film', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Film', '', ''),
                            (17, $language_ids, 'Business', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', 'Business', '', '');
                        ";
                }
            }
            
			$queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_category_path` (`category_id`, `path_id`, `level`) VALUES 
                    (25, 25, 0),
                    (28, 25, 0),
                    (28, 28, 1),
                    (30, 25, 0),
                    (30, 30, 1),
                    (31, 25, 0),
                    (31, 31, 1),
                    (20, 20, 0),
                    (27, 20, 0),
                    (27, 27, 1),
                    (26, 20, 0),
                    (26, 26, 1),
                    (24, 24, 0),
                    (18, 18, 0),
                    (45, 18, 0),
                    (45, 45, 1),
                    (46, 18, 0),
                    (46, 46, 1),
                    (17, 17, 0),
                    (33, 33, 0),
                    (34, 34, 0),
                    (57, 57, 0);
                    ";
            
			
            
            $queries[] = "
                    INSERT INTO `" . DB_PREFIX . "blog_comment` (`comment_id`, `article_id`, `name`, `email`, `content`, `status`, `date_added`, `date_modified`) VALUES 
                    (12, 27, 'Mark Johanson', 'masrk@gmail.com', '&lt;span style=&quot;line-height: 17.1429px; text-align: justify;&quot;&gt;Curabitur sollicitudin mauris sed leo gravida tempus.&lt;/span&gt;', 1, '2015-08-15 00:00:00', '0000-00-00 00:00:00'),
                    (15, 27, 'Katrin Dester', 'kadsef@op.pl', '&amp;nbsp;Cras quis sapien varius, convallis lectus sit amet, facilisis odio. Duis viverra et nulla tincidunt posuere.&amp;nbsp;&lt;br&gt;', 1, '2015-08-31 23:41:19', '0000-00-00 00:00:00'),
                    (16, 63, 'Adam Ment', 'asder@gmail.com', 'Curabitur vel rutrum nibh, feugiat mattis risus. Aenean tempor ac libero ut consectetur. Cras quis sapien varius, convallis lectus sit amet, facilisis odio. Duis viverra et nulla tincidunt posuere. Sed convallis elementum nulla ut auctor. Donec scelerisque a justo consequat molestie. Maecenas sed mattis arcu. Nunc semper at orci in posuere.', 1, '2015-09-01 00:14:07', '0000-00-00 00:00:00'),
                    (17, 18, 'Anna Key', 'akeu@gmail.com', '&lt;span style=&quot;text-align: justify;&quot;&gt;Pellentesque in venenatis leo, vel feugiat libero. Nullam at erat a nisi tristique ultrices. Curabitur sollicitudin mauris sed leo gravida tempus.&lt;/span&gt;', 1, '2015-09-01 20:52:21', '0000-00-00 00:00:00'),
                    (18, 20, 'John Batton', 'aloes@gmail.com', '&lt;span style=&quot;text-align: justify;&quot;&gt;Maecenas feugiat justo tempus elementum rhoncus. Vestibulum sit amet egestas orci. Pellentesque in venenatis leo, vel feugiat libero. Nullam at erat a nisi tristique ultrices. Curabitur sollicitudin mauris sed leo gravida tempus.&amp;nbsp;&lt;/span&gt;', 1, '2015-09-01 20:53:04', '0000-00-00 00:00:00'),
                    (19, 27, 'John Detter', 'joer@gmail.com', 'Curabitur non interdum nisi. Maecenas pretium sodales consequat. Suspendisse faucibus condimentum orci, in efficitur neque bibendum id. Ut nec ipsum eros. Duis sagittis dui non mi lacinia, sit amet vulputate neque commodo. Ut eget pulvinar lorem. Integer dapibus tortor quis est volutpat maximus. Phasellus fermentum vel sapien pellentesque loborti', 1, '2015-10-04 23:10:09', '0000-00-00 00:00:00');
                ";
            
         
            foreach( $queries as $query ){
                $this->db->query( $query ) or die (mysql_error());
            }
            
            $query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout WHERE name = 'Blog'");

            if($query->row){
                $layout_id = $query->row['layout_id'];
            
            
                $this->load->model('setting/setting');
                $output["blog_category_module"] = array (
                  1 => 
                  array (
                    'heading_title' => array(
                        1 => 'Categories'
                    ),
                    'layout_id' => $layout_id,
                    'position' => 'column_right',
                    'sort_order' => '2',
                    'status' => '1',
                  ),
                ); 
                $this->model_setting_setting->editSetting( "blog_category", $output );
                $output = array();
                
                $output["blog_popular_module"] = array (
                  1 => 
                  array (
                    'heading_title' => array(
                        1 => 'Popular Posts'
                    ),
                    'layout_id' => $layout_id,
                    'position' => 'column_right',
                    'template' => 'default.tpl',
                    'status' => '1',
                    'thumb_width' => '70',
                    'thumb_height' => '92',
                    'articles_limit' => '3',
                    'sort_order' => '3',
                  ),
                ); 
                $this->model_setting_setting->editSetting( "blog_popular", $output );
                $output = array();
                
                $output["blog_tags_module"] = array (
                  1 => 
                  array (
                    'heading_title' => array(
                        1 => 'Popular Tags'
                    ),
                    'layout_id' => $layout_id,
                    'position' => 'column_right',
                    'status' => '1',
                    'sort_order' => '4',
                  ),
                ); 
                $this->model_setting_setting->editSetting( "blog_tags", $output );

                $output = array();

                
            }
		}
    }
    
	
	private function seoUrl($gString){
		$turkce = array("ş","Ş","ı","ü","Ü","ö","Ö","ç","Ç","ş","Ş","ı","ğ","Ğ","İ","ö","Ö","Ç","ç","ü","Ü");
		$duzgun = array("s","S","i","u","U","o","O","c","C","s","S","i","g","G","I","o","O","C","c","u","U");
		$string = str_replace($turkce,$duzgun,$gString);
		$string = preg_replace("@[^a-z0-9\-_şıüğçİŞĞÜÇ]+@i","-",$string);
		
		//Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
		$string = strtolower($string);
		//Strip any unwanted characters
		$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		//Clean multiple dashes or whitespaces
		$string = preg_replace("/[\s-]+/", " ", $string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "-", $string);
	    
		

		return $string;
	}
	
}
