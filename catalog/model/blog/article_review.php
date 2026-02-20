<?php
namespace Opencart\Catalog\Model\Blog;
class ArticleReview extends \Opencart\System\Engine\Model {
	
	public function addArticleReview(int $article_id, array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "article_review` SET `author` = '" . $this->db->escape((string)$data['name']) . "', `customer_id` = '" . (int)$this->customer->getId() . "', `article_id` = '" . (int)$article_id . "', `text` = '" . $this->db->escape((string)$data['text']) . "', `rating` = '" . (int)$data['rating'] . "', `date_added` = NOW()");

		return $this->db->getLastId();
	}

	public function getArticleReviewsByArticleId($article_id, int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}
		if((int)$article_id ){
			$query = $this->db->query("SELECT r.`author`, r.`rating`, r.`text`, r.`date_added` FROM `" . DB_PREFIX . "article_review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.`article_id` = p.`article_id`) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`article_id` = pd.`article_id`) WHERE r.`article_id` = '" . (int)$article_id . "' AND p.`date_available` <= NOW() AND p.`status` = '1' AND r.`status` = '1' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;
		}
		return [];	
	}

	public function getTotalArticleReviewsByArticleId($article_id): int {
		if((int)$article_id ){
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "article_review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.`article_id` = p.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_description` pd ON (p.`article_id` = pd.`article_id`) WHERE p.`article_id` = '" . (int)$article_id . "' AND p.`date_available` <= NOW() AND p.`status` = '1' AND r.`status` = '1' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

			return (int)$query->row['total'];
		}
		return 0;
	}

	public function getArticleReviews():array {
		$query = $this->db->query("SELECT r.* FROM " . DB_PREFIX . "article_review r LEFT JOIN " . DB_PREFIX . "Article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.date_available <= NOW() AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.article_id DESC ");

		return isset($query->rows)?$query->rows:[];
	}
	public function getLatestArticleReviews(int $limit):array {
		$query = $this->db->query("SELECT r.`author`, r.`rating`, r.`text`, r.`date_added`,p.*,pd.name FROM " . DB_PREFIX . "article_review r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.article_id DESC LIMIT ".$limit);

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getSelectedArticleReviews(string $selected):array {
		$sql = "SELECT r.`author`, r.`rating`, r.`text`, r.`date_added`,p.*,pd.name FROM " . DB_PREFIX . "article_review r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE r.article_review_id IN ('".$selected."')  AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.article_id DESC ";
		
		
		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}
	
	
}
