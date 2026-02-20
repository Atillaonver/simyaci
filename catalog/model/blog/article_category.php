<?php
namespace Opencart\Catalog\Model\Blog;
class ArticleCategory extends \Opencart\System\Engine\Model {
	
	public function getArticleCategory(int $article_category_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "article_category` c LEFT JOIN `" . DB_PREFIX . "article_category_description` cd ON (c.`article_category_id` = cd.`article_category_id`) WHERE c.`article_category_id` = '" . (int)$article_category_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND c.`status` = '1'");

		return $query->row;
	}

	public function getArticleCategories(int $parent_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "article_category` c LEFT JOIN `" . DB_PREFIX . "article_category_description` cd ON (c.`article_category_id` = cd.`article_category_id`) LEFT JOIN `" . DB_PREFIX . "article_category_to_store` c2s ON (c.`article_category_id` = c2s.`article_category_id`) WHERE c.`parent_id` = '" . (int)$parent_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'  AND c.`status` = '1' ORDER BY c.`sort_order`, LCASE(cd.`name`)");

		return $query->rows;
	}

	public function getParentArticleCategory($article_category_id): int {
		$query = $this->db->query("SELECT parent_id FROM `" . DB_PREFIX . "article_category` WHERE `article_category_id` = '" . (int)$article_category_id . "'");

		return isset($query->row['parent_id'])?$query->row['parent_id']:0;
	}
	
	
	public function getArticleCategoryRelated($categories) {
		foreach($categories AS $article_category){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_category_related  WHERE article_category_id = '" . (int)$article_category['article_category_id'] . "' group by related_id LIMIT 4");
			if($query->rows)
				return $query->rows;
		}
		return [];
	}
	
	public function getTotalArticleCategories(){
		$returnData = [];
		$query = $this->db->query("SELECT COUNT(*)as total,acd.name,acd.article_category_id FROM " . DB_PREFIX . "article_to_article_category a2ac LEFT JOIN " . DB_PREFIX . "article_category_description acd ON(a2ac.article_category_id = acd.article_category_id)  WHERE acd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY a2ac.article_category_id");
		
		return $query->rows;
	}
	
	public function getCategoryArticles(array $data=[]): array {
		$returnData = [];
		$SQL ="SELECT i.*,id.title,id.header,awd.name as writer FROM `" . DB_PREFIX . "article` i LEFT JOIN `" . DB_PREFIX . "article_description` id ON (i.`article_id` = id.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_article_category` a2ac ON (i.`article_id` = a2ac.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_to_article_writer` a2aw ON (i.`article_id` = a2aw.`article_id`) LEFT JOIN `" . DB_PREFIX . "article_writer_description` awd ON (awd.`article_writer_id` = a2aw.`article_writer_id`) WHERE a2aw.article_id = i.article_id AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'  ORDER BY i.`date_modified` ";
		
		if (!empty($data['filter_category_id'])) {
			$SQL .= " AND a2ac.`article_category_id` = '" . (int)$data['filter_category_id'] . "'";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$SQL .= " DESC";
		} else {
			$SQL .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$SQL .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		
		
		$query = $this->db->query($SQL);
		return $query->rows;
	}
	
	public function getTotalCategoryArticles(array $data=[]): int {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "article` a LEFT JOIN `" . DB_PREFIX . "article_to_article_category` a2ac ON (a.article_id = a2ac.article_id )  WHERE a.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND a.`status` = '1' AND a2ac.article_category_id='".(int)$data['filter_category_id']."'");

		return $query->row['total'];
	}
	
}
