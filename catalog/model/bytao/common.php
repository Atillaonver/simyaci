<?php
namespace Opencart\Catalog\Model\Bytao;
class Common extends \Opencart\System\Engine\Model {	
	
	public function getCss(int $type):int {
		$query = $db->query("SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '" . (int)$this->config->get('config_store_id'). "' AND type='".(int)$type."' LIMIT 1");
		
		return isset($query->row['css_id'])?$query->row['css_id']:0;
	}
	
	public function getStoreLanguages(): array {
		
		$language_data = $this->cache->get('store.'.(int)$this->config->get('config_store_id') .'.language');
		
		if (!$language_data) {
			$language_data = [];
			$lArray = implode(",",$this->config->get('config_store_languages'));
				
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code IN ('".(str_replace(",","','",$lArray))."') ORDER BY `sort_order`, `name`");
			
			foreach ($query->rows as $result) {
				$image = HTTP_SERVER;

				if (!$result['extension']) {
					$image .= 'catalog/';
				} else {
					$image .= 'extension/' . $result['extension'] . '/catalog/';
				}
				$code = explode('-',$result['code']);
				$language_data[$result['code']] = [
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'image'       => $image . 'language/' . $result['code'] . '/' . $result['code'] . '.png',
					'locale'      => $result['locale'],
					'extension'   => $result['extension'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				];
			}

			$this->cache->set('store.'.(int)$this->config->get('config_store_id') .'.language', $language_data);
		}

		return $language_data;
	}
	
	public function getStoreCurrencies(): array {
	    $store_id = $this->config->get('config_store_id');
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "currency` WHERE `status` = '1' ORDER BY `title` ASC";
		$lArray = implode("','",$this->config->get('config_store_currencies'));
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE `status` = '1' AND code IN ('".$lArray."') ORDER BY `code`");
		

		$currency_data = $this->cache->get('currency.'.(int)$store_id);

		if (!$currency_data) {
			$currency_data = [];

			$query = $this->db->query($sql);

			foreach ($query->rows as $result) {
				$currency_data[$result['code']] = [
					'currency_id'   => $result['currency_id'],
					'title'         => $result['title'],
					'code'          => $result['code'],
					'symbol_left'   => $result['symbol_left'],
					'symbol_right'  => $result['symbol_right'],
					'decimal_place' => $result['decimal_place'],
					'value'         => $result['value'],
					'status'        => $result['status'],
					'date_modified' => $result['date_modified']
				];
			}

			$this->cache->set('currency.'.(int)$store_id, $currency_data);
		}

		return $currency_data;
	}
	
	public function getBottomCategories():array {
		$sql="SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.bottom > 0 AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";
		
		$query = $this->db->query($sql);
		

		return $query->rows;
	}

	public function getSeoUrlById(int $seo_url_id): array {
		$query = $this->db->query("SELECT *,l.code AS language FROM `" . DB_PREFIX . "seo_url` su LEFT JOIN `" . DB_PREFIX . "language` l ON (su.language_id=l.language_id) WHERE seo_url_id = '" . $seo_url_id . "' LIMIT 1");
		return $query->row;
	}

	public function getProductOptionImage(int $product_id,int $color_id):string{
		
		$query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id. "' AND color_id='".(int)$color_id."' ORDER BY sort_order ASC LIMIT 1");
		
		return isset($query->row['image'])?$query->row['image']:'';
	}
	
	public function getProductColorCount(int $product_id):int{
		$query = $this->db->query("SELECT count(*) FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id. "'GROUP BY `color_id`,`product_id`");
		
		return count($query->rows);
	}
	
	public function getHomeProducts(array $data = []): array {
		
		$product_data = [];
		$this->load->model('catalog/product');
		
		$sql = "SELECT p2a.`product_id` FROM `" . DB_PREFIX . "product_to_addcategory` p2a LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON(p2a.product_id = p2s.product_id) LEFT JOIN `" . DB_PREFIX . "product` p ON(p.product_id = p2a.product_id)  WHERE p2a.`type` = 'mpage' AND p2s.store_id ='".(int)$this->config->get('config_store_id')."' AND p.status='1'  GROUP BY p2a.`product_id`";
		

		if (isset($data['start']) && $data['start'] < 0) {
			//
		}else{
			$data['start'] = 0;
		}

		if (isset($data['limit']) && $data['limit'] < 1) {
			//
		}else{
			$data['limit'] = 20;
		}
		
		$sql .= " ORDER BY p2a.`sort_order`";
		
		$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		

		

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}
	
	public function getSecondImg(int $product_id):string {
		$SQL = "SELECT color_id FROM " . DB_PREFIX . "product_image pi LEFT JOIN " . DB_PREFIX . "product p ON (pi.image = p.image) WHERE pi.product_id = '" . (int)$product_id. "' ";
		
		$query = $this->db->query($SQL);
		
		if(!isset($query->row['color_id'])){
			return '';
		}else{
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id. "' AND color_id='".(int)$query->row['color_id']."' ORDER BY sort_order ASC LIMIT 2");
			if(isset($query->rows) and isset($query->rows[1])){
				return $query->rows[1]['image'];
			}else{
				return '';
			}
				
		}
		
		
	}
	
	public function setOrderToken($order_id,$token){
		$this->db->query("UPDATE `" . DB_PREFIX . "order_pdf` SET `order_id` = '" . (int)$order_id . "',`pdf_id` = '" . $this->db->escape($token) . "'");
		
	}
}
