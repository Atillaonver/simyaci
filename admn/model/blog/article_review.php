<?php
namespace Opencart\Admin\Model\Blog;
class ArticleReview extends \Opencart\System\Engine\Model {
	public function addArticleReview(array $data): int {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_review` SET `author` = '" . $this->db->escape((string)$data['author']) . "', `article_id` = '" . (int)$data['article_id'] . "', `text` = '" . $this->db->escape(strip_tags((string)$data['text'])) . "', `rating` = '" . (int)$data['rating'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_added` = '" . $this->db->escape((string)$data['date_added']) . "'");

		$article_review_id = $this->db->getLastId();
		return $article_review_id;
	}

	public function editArticleReview(int $article_review_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "article_review` SET `author` = '" . $this->db->escape((string)$data['author']) . "', `article_id` = '" . (int)$data['article_id'] . "', `text` = '" . $this->db->escape(strip_tags((string)$data['text'])) . "', `rating` = '" . (int)$data['rating'] . "', `status` = '" . (bool)(isset($data['status']) ? $data['status'] : 0) . "', `date_added` = '" . $this->db->escape((string)$data['date_added']) . "', `date_modified` = NOW() WHERE `article_review_id` = '" . (int)$article_review_id . "'");

	}

	public function deleteArticleReview(int $article_review_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "article_review` WHERE `article_review_id` = '" . (int)$article_review_id . "'");

	}

	public function getArticleReview(int $article_review_id): array {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT ad.`title` FROM `" . DB_PREFIX . "article_description` ad WHERE pd.`article_id` = r.`article_id` AND ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "') AS article FROM `" . DB_PREFIX . "article_review` r WHERE r.`article_review_id` = '" . (int)$article_review_id . "'");

		return $query->row;
	}

	public function getArticleReviews(array $data = []): array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT r.`article_review_id`, ad.`title`, r.`author`, r.`rating`, r.`status`, r.`date_added` FROM `" . DB_PREFIX . "article_review` r LEFT JOIN `" . DB_PREFIX . "article_description` ad ON (r.`article_id` = ad.`article_id`) WHERE ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND r.store_id='".(int)$store_id."'";

		if (!empty($data['filter_article'])) {
			$sql .= " AND ad.`title` LIKE '" . $this->db->escape((string)$data['filter_article'] . '%') . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.`author` LIKE '" . $this->db->escape((string)$data['filter_author'] . '%') . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(r.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(r.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}

		$sort_data = [
			'ad.title',
			'r.author',
			'r.rating',
			'r.status',
			'r.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY r.`date_added`";
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

	public function getTotalArticleReviews(array $data = []): int {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_review` r LEFT JOIN `" . DB_PREFIX . "article_description` ad ON (r.`article_id` = ad.`article_id`) WHERE ad.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND r.`store_id` = '" . (int)$store_id . "'";

		if (!empty($data['filter_article'])) {
			$sql .= " AND ad.`title` LIKE '" . $this->db->escape((string)$data['filter_article'] . '%') . "'";
		}

		if (!empty($data['filter_author'])) {
			$sql .= " AND r.`author` LIKE '" . $this->db->escape((string)$data['filter_author'] . '%') . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND r.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(r.`date_added`) >= DATE('" . $this->db->escape((string)$data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(r.`date_added`) <= DATE('" . $this->db->escape((string)$data['filter_date_to']) . "')";
		}
		
		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalArticleReviewsAwaitingApproval(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_review` WHERE `status` = '0'");

		return (int)$query->row['total'];
	}
}
