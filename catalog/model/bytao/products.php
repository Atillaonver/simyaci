<?php
class ModelBytaoProducts extends Model {
    
    public function getProduct($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
				
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}
	
	public function getProductByCode($product_code){
		$pdata=array();
		if($this->customer->getGroupId()==3){
					$status="p.status = '2'"; 
				}else if($this->customer->getGroupId()==2){
					$status="(p.status = '1' OR p.status = '3')";
				}else{
					$status="p.status = '1'"; 
				}
		$products = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_to_code WHERE code='".$product_code."'"); 
		if($products){
			foreach($products->rows as $product){
				$product_id=$product['product_id'];
				
				$sql = "SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, "; 
		
				$sql .="(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, ";
				$sql .="(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, ";
		
				$sql .="(SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' LIMIT 1) AS reward, ";
		
				$sql .="(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS stock_status, ";
		
				$sql .="(SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS weight_class, ";
		
				$sql .="(SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS length_class, ";
		
				$sql .="(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id LIMIT 1) AS rating, ";
		
				$sql .="(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ".$status." AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		
				$query = $this->db->query($sql);

				if($query->num_rows){
					// --> byTAO
					if($this->customer->isLogged()){
						switch($this->customer->getGroupId()){
							case 0: // Guest
							case 3: // tester customer
							case 1: // RetailSale customer
							if($query->row['sale_price']>0.01){
								$price=$query->row['price'];
								$special=$query->row['sale_price'];
							}else{
								$price=($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
								$special=$query->row['special'];
							}
							break;
							case 2:  // wholeSale customer
							/*if($query->row['sale_price']>0.01){
							$price=$query->row['price'];
							$special='';//$query->row['sale_price'];
							
							}else*/
							if($query->row['whole_sale_price']>0.01){
								$price=($query->row['discount'] ? $query->row['discount'] : $query->row['whole_sale_price']);
								$special='';//$query->row['special'];
							}else{
								$price=($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
								$special='';//$query->row['special'];
							}
							break;
						}
					} else{
				
						$price=($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
						$special=$query->row['sale_price'];
					}
					// >-- byTAO

			
			
		
					$pdata[] = array(
						'product_id'       => $query->row['product_id'],
						'name'             => $query->row['name'],
						'description'      => $query->row['description'],
						'meta_title'       => $query->row['meta_title'],
						'meta_description' => $query->row['meta_description'],
						'meta_keyword'     => $query->row['meta_keyword'],
						'tag'              => $query->row['tag'],
						'model'            => $query->row['model'],
						'sku'              => $query->row['sku'],
						'upc'              => $query->row['upc'],
						'ean'              => $query->row['ean'],
						'jan'              => $query->row['jan'],
						'isbn'             => $query->row['isbn'],
						'mpn'              => $query->row['mpn'],
						'location'         => $query->row['location'],
						'quantity'         => $query->row['quantity'],
						'stock_status'     => $query->row['stock_status'],
						'image'            => $query->row['image'],
						'manufacturer_id'  => $query->row['manufacturer_id'],
						'manufacturer'     => $query->row['manufacturer'],
						'price'            => $price,
						'whole_sale_price'          => $query->row['whole_sale_price'],
						'our_price'          => $query->row['our_price'],
						'cost_price'          => $query->row['cost_price'],
						'sale_price'          => $query->row['sale_price'],
						'retail_price'          => $query->row['price'],
				
						'special'          => $special,
						'reward'           => $query->row['reward'],
						'points'           => $query->row['points'],
						'tax_class_id'     => $query->row['tax_class_id'],
						'date_available'   => $query->row['date_available'],
						'weight'           => $query->row['weight'],
						'weight_class_id'  => $query->row['weight_class_id'],
						'length'           => $query->row['length'],
						'width'            => $query->row['width'],
						'height'           => $query->row['height'],
						'length_class_id'  => $query->row['length_class_id'],
						'subtract'         => $query->row['subtract'],
						'rating'           => round($query->row['rating']),
						'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
						'minimum'          => $query->row['minimum'],
						'sort_order'       => $query->row['sort_order'],
						'status'           => $query->row['status'],
						'size_chart_id'           => $query->row['size_chart_id'],
						'material'           => $query->row['material'],
						'material_id'           => $query->row['material_id'],
						'productcare_id'           => $query->row['productcare_id'],
						'measurement_id'           => $query->row['measurement_id'],
						'sale'           => $query->row['sale'],
						'gift'           => $query->row['gift'],
						'best'           => $query->row['best'],
						'clearance'           => $query->row['clearance'],
						'newarriwals'           => $query->row['new_arriwals'],
						'date_added'       => $query->row['date_added'],
						'date_modified'    => $query->row['date_modified'],
						'viewed'           => $query->row['viewed'],
						'colors'          => $this->getColors($query->row['product_id']),
					);
				} 
			}
			
		}		
		return $pdata;
	
	}

    public function getCategoryId($product_id) {
	     $sql = "SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id='" . $product_id . "'"; 
	     $query = $this->db->query($sql);
	     return $query->row;
	}
        
    public function getRandomProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
						
		$sql = "SELECT *, p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"; 
			
		$sql .= " GROUP BY p.product_id";
			
		$sort_data = array(				'pd.name',
				'p.model',
				'p.quantity',
				'p.price',
				'rating',
				'p.sort_order',
				'p.date_added'
		);	
			
		$sql .= " ORDER BY Rand()";
		
		$sql .= " LIMIT 0," . (int)$limit;
			
		$product_data = array();	
		$query = $this->db->query($sql);
                       
		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
					
		return $product_data;
	}
	
	public function getAlsoBoughtProducts($limit) {
		$product_data = array();
		
		if(isset($limit) && $limit>0)
		{
			if (isset($this->request->get['product_id'])) {
				$product_id = (int)$this->request->get['product_id'];
			} else {
				$product_id = 0;
			}
			
			if (isset($product_id) && $product_id > 0) {
				
				$cachestring = 'alsobought.product.L' . (int)$this->config->get('config_language_id') . '.S' . (int)$this->config->get('config_store_id') . '.G' . $this->config->get('config_customer_group_id') . '.P' .(int)$product_id . '.T' . (int)$limit;
				
				$product_data = $this->cache->get($cachestring);
				if (!$product_data) {
					$product_data = array();
						
					$query = $this->db->query("SELECT op.product_id FROM " . DB_PREFIX . "order_product op INNER JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)  WHERE EXISTS (SELECT 1 FROM " . DB_PREFIX . "order_product op1  WHERE op1.order_id = op.order_id AND op1.product_id = '" .(int)$product_id . "' ) AND op.product_id <> '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id LIMIT " . (int)$limit);
							
					foreach ($query->rows as $result) {
							$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
					}					
					$this->cache->set($cachestring, $product_data);	
				}
			}
		}
		return $product_data;
	}
	
     public function getMostViewedProducts($limit) {
		$product_data = array();
		
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed DESC LIMIT " . (int)$limit);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
					 	 		
		return $product_data;
	}
	
	public function getProductsRelated($limit) {
		$product_data = array();
		
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT " . (int)$limit);

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}

		return $product_data;
	}

	public function getColors($product_id){
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND o.type = 'radio' ORDER BY o.sort_order");
		
		
		
		foreach($product_option_query->rows as $product_option){
			$product_option_value_data = array();
			
			$product_option_value_query = $this->db->query("SELECT count(*) AS total FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND (pov.type='0' OR  pov.type='1')  ORDER BY pov.sort_order ASC");
			return $product_option_value_query->row['total'];
		}

		//return $product_option_data;
		return 0;
	}


}
