<?php
namespace Opencart\Admin\Model\Blog;

class Article extends \Opencart\System\Engine\Model {
	
	public function addArticle(array $data): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article` SET `sort_order` = '" .(isset($data['sort_order'])?(int)$data['sort_order']:0 ) . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`type_id` = '" . (isset($data['type_id']) ? (int)$data['type_id'] : 0) . "',`limage` = '" . (isset($data['limage']) ? $this->db->escape($data['limage']) : '') . "',`himage` = '" . (isset($data['himage']) ? $this->db->escape($data['himage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`store_id` = '" . (int)$store_id . "',`status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "',`date_added` = NOW(), `date_modified` = NOW()");

		$article_id = $this->db->getLastId();

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_description` SET `article_id` = '" . (int)$article_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		
		
		// SEO URL
		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $language_id => $keyword) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_id', `value` = '" . (int)$article_id . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '".(isset($data['article_description'][$language_id]['title'])?$data['article_description'][$language_id]['title']:"" )."',route='article/article'");
			}

		}

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $article_category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_article_category` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `article_category_id` = '" . (int)$article_category_id . "'");
			}
		}


		if (isset($data['article_writer_id'])) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_article_writer` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `article_writer_id` = '" . (int)$data['article_writer_id'] . "'");
			
		}

		$this->cache->delete('article');

		return $article_id;
	}

	public function editArticle(int $article_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "article` SET `sort_order` = '" . (isset($data['sort_order'])?(int)$data['sort_order']:0 )  . "', `bottom` = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "',`type_id` = '" . (isset($data['type_id']) ? (int)$data['type_id'] : 0) . "',`limage` = '" . (isset($data['limage']) ? $this->db->escape($data['limage']) : '') . "',`himage` = '" . (isset($data['himage']) ? $this->db->escape($data['himage']) : '') . "',`fimage` = '" . (isset($data['fimage']) ? $this->db->escape($data['fimage']) : '') . "',`status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "',`date_added` = '". $this->db->escape((string)$data['date_added'])."',`date_modified` = NOW() WHERE `article_id` = '" . (int)$article_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_description` SET `article_id` = '" . (int)$article_id . "', `language_id` = '" . (int)$language_id . "', `title` = '" . $this->db->escape($value['title']) . "',`header` = '" . $this->db->escape($value['header']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_id' AND `value` = '" . (int)$article_id . "'");

		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $language_id => $keyword) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_id', `value` = '" . (int)$article_id . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '".(isset($data['article_description'][$language_id]['title'])?$data['article_description'][$language_id]['title']:"" )."',route='article/article'");
			}

		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_category` WHERE `article_id` = '" . (int)$article_id . "'");

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $article_category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_article_category` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `article_category_id` = '" . (int)$article_category_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_writer` WHERE `article_id` = '" . (int)$article_id . "'");
		if (isset($data['article_writer_id'])) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_to_article_writer` SET `article_id` = '" . (int)$article_id . "', `store_id` = '" . (int)$store_id . "', `article_writer_id` = '" . (int)$data['article_writer_id'] . "'");
			
		}
		
		$this->cache->delete('article');
	}

	public function deleteArticle(int $article_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_category` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_writer` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_review` WHERE `article_id` = '" . (int)$article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_id' AND `value` = '" . (int)$article_id . "'");

		$this->cache->delete('article');
	}

	public function getArticle(int $article_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article` WHERE `article_id` = '" . (int)$article_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getArticles(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "article` i LEFT JOIN `" . DB_PREFIX . "article_description` id ON (i.`article_id` = id.`article_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' and i.`store_id` = '" . (int)$store_id . "'";

			$sort_data = [
				'id.title',
				'i.sort_order',
				'i.type_id'
			];
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.`title`";
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

			return isset($query->rows)?$query->rows:[];
	}
	
	public function getArticleCategories(int $article_id): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$QueryData=[];
		$sql = "SELECT * FROM `" . DB_PREFIX . "article_to_article_category` a2ac LEFT JOIN `" . DB_PREFIX . "article_category_description` acd ON(a2ac.`article_category_id` = acd.`article_category_id`) WHERE a2ac.`article_id` = '" . (int)$article_id . "' and `language_id` = '" . (int)$this->config->get('config_language_id') . "' and a2ac.`store_id` = '" . (int)$store_id . "'";
		$query = $this->db->query($sql);
		foreach($query->rows as $catagory){
			$QueryData[]=[
				'name' => $catagory['name'],
				'article_category_id' => $catagory['article_category_id']
			];
			
		}	
			return $QueryData;
	}
	
	public function getArticleWriter(int $article_id): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$QueryData=[];
		$sql = "SELECT * FROM `" . DB_PREFIX . "article_to_article_writer` a2aw LEFT JOIN `" . DB_PREFIX . "article_writer_description` awd ON(a2aw.`article_writer_id` = awd.`article_writer_id`) WHERE a2aw.`article_id` = '" . (int)$article_id . "' and awd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' and a2aw.`store_id` = '" . (int)$store_id . "' LIMIT 1";
		
		$query = $this->db->query($sql);
		
		return $query->row;
	}
	
	public function getArticleDescriptions(int $article_id): array {
		$article_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_description` WHERE `article_id` = '" . (int)$article_id . "'");

		foreach ($query->rows as $result) {
			$article_description_data[$result['language_id']] = [
				'title'            => $result['title'],
				'header'           => $result['header'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			];
		}

		return $article_description_data;
	}

	public function getArticleSeoUrls(int $article_id): array {
		$article_seo_url_data = [];
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_id' AND `value` = '" . (int)$article_id . "' and store_id ='".(int) $store_id."'");

		foreach ($query->rows as $result) {
			$article_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $article_seo_url_data;
	}

	public function getTotalArticles(): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article`  WHERE store_id='".(int)$store_id."'");

		return (int)$query->row['total'];
	}

	public function getTotalArticlesByArticleCategoryId(int $article_category_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_to_article_category` WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		return (int)$query->row['total'];
	}

	public function getArticleTypes():array {
		$types=[];
		return [];
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_type`");

		foreach ($query->rows as $result) {
			$types[] = [
				'type_id' =>$result['type_id'],
				'name' =>$result['name']
			];
		}
		return $types;
	}
}
