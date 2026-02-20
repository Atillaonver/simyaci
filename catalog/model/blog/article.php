<?php
namespace Opencart\Catalog\Model\Blog;
class Article extends \Opencart\System\Engine\Model {
	public function getArticle(int $article_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article` i LEFT JOIN `" . DB_PREFIX . "article_description` id ON (i.`article_id` = id.`article_id`) WHERE i.`article_id` = '" . (int)$article_id . "' AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'");

		return $query->row;
	}

	public function getArticles(): array {
		$query = $this->db->query("SELECT i.*,id.title,id.header,awd.name as writer FROM `" . DB_PREFIX . "article` i LEFT JOIN `" . DB_PREFIX . "article_description` id ON (i.`article_id` = id.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_article_writer` a2aw ON (i.`article_id` = a2aw.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_writer_description` awd ON (awd.`article_writer_id` = a2aw.`article_writer_id`) WHERE a2aw.article_id = i.article_id AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'  ORDER BY i.`date_modified` ASC");

		return $query->rows;
	}
	
	public function getRecentArticles(): array {
		$query = $this->db->query("SELECT i.*,id.title,id.header,awd.name as writer FROM `" . DB_PREFIX . "article` i LEFT JOIN `" . DB_PREFIX . "article_description` id ON (i.`article_id` = id.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_article_writer` a2aw ON (i.`article_id` = a2aw.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_writer_description` awd ON (awd.`article_writer_id` = a2aw.`article_writer_id`) WHERE a2aw.article_id = i.article_id AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'  ORDER BY i.`date_modified` ASC LIMIT 4");

		return $query->rows;
	}
	
	public function getTotalArticles(): int {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "article` WHERE `store_id` = '" . (int)$this->config->get('config_store_id') . "' AND `status` = '1'");

		return $query->row['total'];
	}

}
