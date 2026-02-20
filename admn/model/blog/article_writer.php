<?php
namespace Opencart\Admin\Model\Blog;
class ArticleWriter extends \Opencart\System\Engine\Model {
	
	public function addArticleWriter(array $data): int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_writer` SET `sort_order` = '" . (int)$data['sort_order'] . "',`store_id` = '" . (int)$store_id . "'");

		$article_writer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_writer` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");
		}
		
		foreach ($data['article_writer_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_writer_description` SET `article_writer_id` = '" . (int)$article_writer_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// SEO URL
		if (isset($data['article_writer_seo_url'])) {
			foreach ($data['article_writer_seo_url'] as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_writer_id', `value` = '" . (int)$article_writer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
			}
		}

		$this->cache->delete('article_writer');

		return $article_writer_id;
	}

	public function editArticleWriter(int $article_writer_id, array $data): void {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("UPDATE `" . DB_PREFIX . "article_writer` SET `sort_order` = '" . (int)$data['sort_order'] . "' WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "article_writer` SET `image` = '" . $this->db->escape((string)$data['image']) . "' WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_writer_description` WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");
		
		foreach ($data['article_writer_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "article_writer_description` SET `article_writer_id` = '" . (int)$article_writer_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', `description` = '" . $this->db->escape($value['description']) . "', `meta_title` = '" . $this->db->escape($value['meta_title']) . "', `meta_description` = '" . $this->db->escape($value['meta_description']) . "', `meta_keyword` = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_writer_id' AND `value` = '" . (int)$article_writer_id . "'");

		if (isset($data['article_writer_seo_url'])) {
			foreach ($data['article_writer_seo_url'] as $language_id => $keyword) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `key` = 'article_writer_id', `value` = '" . (int)$article_writer_id . "', `keyword` = '" . $this->db->escape($keyword) . "'");
			}
		}


		$this->cache->delete('article_writer');
	}

	public function deleteArticleWriter(int $article_writer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_writer` WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_writer_id' AND `value` = '" . (int)$article_writer_id . "'");

		$this->cache->delete('article_writer');
	}

	public function getArticleWriter(int $article_writer_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article_writer` WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");

		return $query->row;
	}

	public function getArticleWriters(array $data = []): array {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT aw.*,awd.name FROM `" . DB_PREFIX . "article_writer` aw LEFT JOIN `" . DB_PREFIX . "article_writer_description` awd ON(awd.article_writer_id = aw.article_writer_id)WHERE awd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND aw.store_id='".(int)$store_id."'";
		
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND awd.`name` LIKE '" . $this->db->escape((string)$data['filter_name'] . '%') . "'";
		}

		$sort_data = [
			'awd.name',
			'aw.sort_order'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY awd.`name`";
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

	public function getArticleWriterDescriptions(int $article_writer_id): array {
		$article_writer_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_writer_description` WHERE `article_writer_id` = '" . (int)$article_writer_id . "'");

		foreach ($query->rows as $result) {
			$article_writer_description_data[$result['language_id']] = [
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'description'      => $result['description']
			];
		}

		return $article_writer_description_data;
	}
	
	public function getArticleWriterSeoUrls(int $article_writer_id): array {
		$article_writer_seo_url_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `key` = 'article_writer_id' AND `value` = '" . (int)$article_writer_id . "'");

		foreach ($query->rows as $result) {
			$article_writer_seo_url_data[$result['language_id']] = $result['keyword'];
		}

		return $article_writer_seo_url_data;
	}

	public function getTotalArticleWriters(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_writer`");

		return (int)$query->row['total'];
	}
}
