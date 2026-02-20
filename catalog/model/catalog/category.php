<?php
namespace Opencart\Catalog\Model\Catalog;
class Category extends \Opencart\System\Engine\Model {
	
	public function getCategory(int $category_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`) WHERE c.`category_id` = '" . (int)$category_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND c.`status` = '1'");

		return $query->row;
	}

	public function getCategories(int $parent_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`) WHERE c.`parent_id` = '" . (int)$parent_id . "' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND c2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'  AND c.`status` = '1' ORDER BY c.`sort_order`, LCASE(cd.`name`) DESC");

		return $query->rows;
	}

	public function getFilters(int $category_id): array {
		$implode = [];

		$query = $this->db->query("SELECT `filter_id` FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$implode[] = (int)$result['filter_id'];
		}

		$filter_group_data = [];

		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.`filter_group_id`, fgd.`name`, fg.`sort_order` FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_group` fg ON (f.`filter_group_id` = fg.`filter_group_id`) LEFT JOIN `" . DB_PREFIX . "filter_group_description` fgd ON (fg.`filter_group_id` = fgd.`filter_group_id`) WHERE f.`filter_id` IN (" . implode(',', $implode) . ") AND fgd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.`filter_group_id` ORDER BY fg.`sort_order`, LCASE(fgd.`name`)");

			foreach ($filter_group_query->rows as $filter_group) {
				$filter_data = [];

				$filter_query = $this->db->query("SELECT DISTINCT f.`filter_id`, fd.`name` FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (f.`filter_id` = fd.`filter_id`) WHERE f.`filter_id` IN (" . implode(',', $implode) . ") AND f.`filter_group_id` = '" . (int)$filter_group['filter_group_id'] . "' AND fd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.`sort_order`, LCASE(fd.`name`)");

				foreach ($filter_query->rows as $filter) {
					$filter_data[] = [
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name']
					];
				}

				if ($filter_data) {
					$filter_group_data[] = [
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $filter_data
					];
				}
			}
		}

		return $filter_group_data;
	}

	public function getLayoutId($category_id): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_to_layout` WHERE `category_id` = '" . (int)$category_id . "' AND `store_id` = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}
	
	public function getParentCategory($category_id): int {
		$query = $this->db->query("SELECT parent_id FROM `" . DB_PREFIX . "category` WHERE `category_id` = '" . (int)$category_id . "'");

		return isset($query->row['parent_id'])?$query->row['parent_id']:0;
	}
	
	public function getRowOrder(string $type = 'mpage',int $category_id = 0): string {
		$sql="SELECT row_order FROM " . DB_PREFIX . "category_product_sort_order  WHERE ".($category_id? "category_id = '" . (int)$category_id . "' AND":"")." store_id = '" .(int)$this->config->get('config_store_id')."'";
		
		if ($type){
			$sql .= " AND sale_type = '".$type."' ";
		}
		$sql .= " LIMIT 1";
		$query = $this->db->query($sql);
		return isset($query->row['row_order'])?$query->row['row_order']:'';
	}
	
	
	
	
	/* byTAO */
	public function hasAddCateogories(int $category_id,int $type): bool {
		switch($type){
			case 3: $deg = "gift";
				break;
			case 2: $deg = "best";
				break;
			case 5: $deg = "clearance";
				break;
			case 4: $deg = "sale";
				break;
			case 1: $deg = "new_arriwals";
				break;
			default:
				$deg = "";
		}
		
		$cquery = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category  WHERE parent_id = '" . (int)$category_id . "' OR category_id = '" . (int)$category_id . "'");
		$total=0;
		
		foreach($cquery->rows as $cat){
			
			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product_to_category p2c  LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND p2c.category_id = '" . (int)$cat['category_id'] . "' AND p.".$deg."='1'";
			$pquery = $this->db->query($sql);
			$total += $pquery->row['total'];
		}
		
		if($total>0){
			return  TRUE;
		}else{
			return  FALSE;
		}
		return  FALSE;
	}
	
	public function getCategoryRelated($categories) {
		foreach($categories AS $category){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_related  WHERE category_id = '" . (int)$category['category_id'] . "' group by related_id LIMIT 4");
			if($query->rows)
				return $query->rows;
		}
		return array();
	}
}
