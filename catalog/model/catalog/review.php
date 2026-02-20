<?php
namespace Opencart\Catalog\Model\Catalog;
class Review extends \Opencart\System\Engine\Model {
	
	public function addReview(int $product_id, array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "review` SET `author` = '" . $this->db->escape((string)$data['name']) . "', `customer_id` = '" . (int)$this->customer->getId() . "', `product_id` = '" . (int)$product_id . "', `text` = '" . $this->db->escape((string)$data['text']) . "', `rating` = '" . (int)$data['rating'] . "', `date_added` = NOW()");

		return $this->db->getLastId();
	}

	public function getReviewsByProductId($product_id, int $start = 0, int $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}
		if((int)$product_id ){
			$query = $this->db->query("SELECT r.`author`, r.`rating`, r.`text`, r.`date_added` FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.`product_id` = p.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) WHERE r.`product_id` = '" . (int)$product_id . "' AND p.`date_available` <= NOW() AND p.`status` = '1' AND r.`status` = '1' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.`date_added` DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;
		}
		return [];	
	}

	public function getTotalReviewsByProductId($product_id): int {
		if((int)$product_id ){
			$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.`product_id` = p.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) WHERE p.`product_id` = '" . (int)$product_id . "' AND p.`date_available` <= NOW() AND p.`status` = '1' AND r.`status` = '1' AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

			return (int)$query->row['total'];
		}
		return 0;
	}

	public function getReviews():array {
		$query = $this->db->query("SELECT r.* FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.date_available <= NOW() AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.product_id DESC ");

		return isset($query->rows)?$query->rows:[];
	}
	public function getLatestReviews(int $limit):array {
		$query = $this->db->query("SELECT r.`author`, r.`rating`, r.`text`, r.`date_added`,p.*,pd.name FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.product_id DESC LIMIT ".$limit);

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getSelectedReviews(string $selected):array {
		$sql = "SELECT r.`author`, r.`rating`, r.`text`, r.`date_added`,p.*,pd.name FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE r.review_id IN ('".$selected."')  AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY p.product_id DESC ";
		
		
		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}
	
	
}
