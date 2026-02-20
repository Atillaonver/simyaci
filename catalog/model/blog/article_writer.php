<?php
namespace Opencart\Catalog\Model\Blog;
class ArticleWriter extends \Opencart\System\Engine\Model {
	public function getArticleWriter(int $article_writer_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_writer` m LEFT JOIN `" . DB_PREFIX . "article_writer_to_store` m2s ON (m.`article_writer_id` = m2s.`article_writer_id`) WHERE m.`article_writer_id` = '" . (int)$article_writer_id . "' AND m2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function getArticleWriters(array $data = []): array {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "article_writer` m LEFT JOIN `" . DB_PREFIX . "article_writer_to_store` m2s ON (m.`article_writer_id` = m2s.`article_writer_id`) WHERE m2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'";

			$sort_data = [
				'name',
				'sort_order'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY `" . $data['sort'] . "`";
			} else {
				$sql .= " ORDER BY `name`";
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
			$article_writer_data = $this->cache->get('article_writer.' . (int)$this->config->get('config_store_id'));

			if (!$article_writer_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_writer` m LEFT JOIN `" . DB_PREFIX . "article_writer_to_store` m2s ON (m.`article_writer_id` = m2s.`article_writer_id`) WHERE m2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' ORDER BY `name`");

				$article_writer_data = $query->rows;

				$this->cache->set('article_writer.' . (int)$this->config->get('config_store_id'), $article_writer_data);
			}

			return $article_writer_data;
		}
	}
}
