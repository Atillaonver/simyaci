<?php
namespace Opencart\Admin\Model\Blog;
class ArticleCategory extends \Opencart\System\Engine\Model {
	
	public function addArticleCategory(array $data): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_category` SET `store_id` ='".$store_id."', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW(), `date_added` = NOW()");

		$article_category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_category` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		}

		foreach ($data['article_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_category_description` SET `article_category_id` = '" . (int)$article_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		
		// Related
		if (isset($data['article_category_related'])) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "article_category_related` WHERE `article_category_id` = '" . (int)$article_category_id . "'");
			foreach ($data['article_category_related'] as $related_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_category_related` SET `article_category_id` = '" . (int)$article_category_id . "', `related_id` = '" . (int)$related_id . "'");
			}
		}


		$this->load->model('design/seo_url');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		if (isset($data['article_category_seo_url'])) {
			foreach ($data['article_category_seo_url'] as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_category_id', `value`= '" . (int)$article_category_id . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '".(isset($data['article_category_description'][$language_id]['name'])?$this->db->escape($data['article_category_description'][$language_id]['name']):"" )."',route='blog/article_category'");
			}
		}

		return $article_category_id;
	}

	public function editArticleCategory(int $article_category_id, array $data): void {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "article_category` SET `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `sort_order` = '" . (int)$data['sort_order'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_modified` = NOW() WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_category` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_category_description` WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		foreach ($data['article_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_category_description` SET `article_category_id` = '" . (int)$article_category_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_category_related` WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		
		if (isset($data['article_category_related'])) {
			foreach ($data['article_category_related'] as $related_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "article_category_related` SET `article_category_id` = '" . (int)$article_category_id . "', `related_id` = '" . (int)$related_id . "'");
			}
		}
		
		// Delete the old path
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_category_id' AND `value` = '" . (int)$article_category_id . "'");

		$this->load->model('design/seo_url');
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		if (isset($data['article_category_seo_url'])) {
			foreach ($data['article_category_seo_url'] as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_category_id', `value` = '" .(int)$article_category_id . "', `keyword` = '" . $this->db->escape($keyword) . "',`title` = '".(isset($data['article_category_description'][$language_id]['name'])?$data['article_category_description'][$language_id]['name']:"" )."',route='blog/article_category'");
					
			}
		}
	}

	public function deleteArticleCategory(int $article_category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_category` WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_category_description` WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_to_article_category` WHERE `article_category_id` = '" . (int)$article_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_category_id' AND `value` = '" . (int)$article_category_id . "'");
		
	}

	public function getArticleCategory(int $article_category_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article_category` c LEFT JOIN `" . DB_PREFIX . "article_category_description` cd ON (c.`article_category_id` = cd.`article_category_id`) WHERE c.`article_category_id` = '" . (int)$article_category_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getArticleCategorys(array $data = [] ): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT acd.*,ac.sort_order,ac.status FROM `" . DB_PREFIX . "article_category` ac LEFT JOIN `" . DB_PREFIX . "article_category_description` acd ON (ac.`article_category_id` = acd.`article_category_id`) WHERE acd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND ac.store_id='".(int)$store_id."' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND acd.`name` LIKE '" . $this->db->escape((string)$data['filter_name']) . "'";
		}
		
		if (!empty($data['filter_status'])) {
			$sql .= " AND ac.`status` = '" . $this->db->escape((string)$data['filter_status']) . "'";
		}

		$sql .= " GROUP BY ac.`article_category_id`";

		$sort_data = [
			'name',
			'sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `sort_order`";
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

	public function getArticleCategoryDescriptions(int $article_category_id): array {
		$article_category_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_category_description` WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		foreach ($query->rows as $result) {
			$article_category_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $article_category_description_data;
	}

	public function getArticleCategorySeoUrls(int $article_category_id): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$article_category_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_category_id' AND `store_id` ='".(int)$store_id ."' AND `value` = '" .(int)$article_category_id . "'");

		foreach ($query->rows as $result) {
			$article_category_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $article_category_seo_url_data;
	}

	public function getTotalArticleCategorys(): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_category` c WHERE c.store_id = ".$store_id);

		return (int)$query->row['total'];
	}
	
	
	/*
	TODO byTAO
	*/
	public function getRelated(int $article_category_id): array {
		$related_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_category_related` WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		foreach ($query->rows as $result) {
			$related_data[] = $result['related_id'];
		}

		return $related_data;
	}
	
	public function getMainParentArticleCategory($article_category_id):int {
		$query = $this->db->query("SELECT parent_id AS parent FROM " . DB_PREFIX . "article_category WHERE article_category_id = '" . (int)$article_category_id . "'");
		
		return $query->row['parent'];
	}
	
}
