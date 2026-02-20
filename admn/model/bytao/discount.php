<?php
namespace Opencart\Admin\Model\Bytao;
class Discount extends \Opencart\System\Engine\Model
{

	public function addDiscount($data)
	{
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$this->db->query("INSERT INTO " . DB_PREFIX . "discount SET name = '" . $this->db->escape($data['name']) . "',  discount_type = '" . (int)$data['discount_type'] . "', discount_value = '" . (float)$data['discount_value'] . "',date_start = '" . $this->db->escape((string)$data['date_start']) . "', date_end = '" . $this->db->escape((string)$data['date_end']) . "',toall = '" . (int)$data['toall'] . "',status = '" . (int)$data['status'] . "',store_id = '" . (int)$store_id . "'");

		$discount_id = $this->db->getLastId();

		if(isset($data['discount_category']))
		{

			foreach($data['discount_category'] as $category_id)
			{
				$this->db->query("INSERT INTO " . DB_PREFIX . "discount_to_category SET discount_id = '" . (int)$discount_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		return $discount_id;
	}

	public function editDiscount($discount_id, $data)
	{
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$this->db->query("UPDATE " . DB_PREFIX . "discount SET name = '" . $this->db->escape($data['name']) . "', discount_type = '" . (int)$data['discount_type'] . "', discount_value = '" . (float)$data['discount_value'] . "', date_start = '" . $this->db->escape((string)$data['date_start']) . "', date_end = '" . $this->db->escape((string)$data['date_end']) . "', toall = '" . (int)$data['toall'] . "',status = '" . (int)$data['status'] . "' WHERE discount_id = '" . (int)$discount_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "discount_to_category WHERE discount_id = '" . (int)$discount_id . "'");
		if(isset($data['discount_category']))
		{
			foreach($data['discount_category'] as $category_id)
			{
				$this->db->query("INSERT INTO " . DB_PREFIX . "discount_to_category SET discount_id = '" . (int)$discount_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

	}

	public function deleteDiscount($discount_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "discount WHERE discount_id = '" . (int)$discount_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "discount_image WHERE discount_id = '" . (int)$discount_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "discount_image_description WHERE discount_id = '" . (int)$discount_id . "'");

	}

	public function getDiscount($discount_id)
	{
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "discount WHERE discount_id = '" . (int)$discount_id . "'");

		return $query->row;
	}

	public function getDiscounts($data = []):array
	{
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$sql = "SELECT * FROM " . DB_PREFIX . "discount WHERE store_id='". (int)$store_id . "'";

		$sort_data = array(
			'name',
			'status'
		);

		if(isset($data['sort']) && in_array($data['sort'], $sort_data))
		{
			$sql .= " ORDER BY " . $data['sort'];
		}
		else
		{
			$sql .= " ORDER BY name";
		}

		if(isset($data['order']) && ($data['order'] == 'DESC'))
		{
			$sql .= " DESC";
		}
		else
		{
			$sql .= " ASC";
		}

		if(isset($data['start']) || isset($data['limit']))
		{
			if($data['start'] < 0)
			{
				$data['start'] = 0;
			}

			if($data['limit'] < 1)
			{
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return isset($query->rows)?$query->rows:[];
	}

	public function getTotalDiscounts():int
	{
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "discount WHERE store_id='". (int)$store_id . "'";

		$query = $this->db->query($sql);

		return isset($query->row['total'])?(int)$query->row['total']:0;
	}

	public function putDiscounts($discount_id,$status)
	{
		$products = array();
		$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "discount_to_category` cc ON(pc.category_id = cc.category_id) WHERE cc.discount_id = '".(int)$discount_id."'  GROUP BY pc.product_id ");
		if($_products)
		{
			if($status)
			{

			}
			else
			{
				foreach( $_products->rows as $product)
				{
					$this->db->query("UPDATE " . DB_PREFIX . "product SET sale_price = '0' WHERE product_id='" . (int)$product['productId'] . "'");
				}

			}
		}
		return 	$products;
	}



	public function resetDiscounts($discount_id)
	{
		$_products = [];

		$discount  = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "discount WHERE discount_id = '" . (int)$discount_id . "' LIMIT 1");

		if(isset($discount->row['status']) && $discount->row['status'])
		{
			$qSql      = "UPDATE " . DB_PREFIX . "product SET sale_price = 0";
			$store_id = $discount->row['store_id'];
			
			if(isset($discount->row['toall']) && $discount->row['toall'])
			{
				$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_store` WHERE store_id='".(int) $store_id."'");
			}
			else
			{
				$_products = $this->db->query("SELECT pc.product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "discount_to_category` cc ON(pc.category_id = cc.category_id) WHERE cc.discount_id = '".(int)$discount_id."'  GROUP BY pc.product_id ");
			}
			
			foreach( $_products->rows as $product)
			{
				$this->db->query($qSql." WHERE product_id='" . (int)$product['productId'] . "'");
			}
			return 	TRUE;
		}
		return 	false;
	}

	public function applyDiscounts($discount_id)
	{
		$_products = [];

		$discount  = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "discount WHERE discount_id = '" . (int)$discount_id . "' LIMIT 1");

		if(isset($discount->row['status']) && $discount->row['status'])
		{
			$store_id = $discount->row['store_id'];
			$qSql     = '';
			switch($discount->row['discount_type']){
				case 0:
					$qSql = "UPDATE " . DB_PREFIX . "product SET sale_price = floor(price -((price / 100)* ".(float)ceil($discount->row['discount_value'])."))";
					break;
				case 1:
					$qSql = "UPDATE " . DB_PREFIX . "product SET sale_price = price - ".(float)ceil($discount->row['discount_value']);
					break;
				case 2:
					$qSql = "UPDATE " . DB_PREFIX . "product SET sale_price = 0";
					break;
			}
				
			if($discount->row['toall']=='1')
			{
				$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_store` WHERE store_id='".(int) $store_id."'");
			}
			else
			{
				$_products = $this->db->query("SELECT pc.product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "discount_to_category` cc ON(pc.category_id = cc.category_id) WHERE cc.discount_id = '".(int)$discount_id."'  GROUP BY pc.product_id ");
			}
			
			
			foreach( $_products->rows as $product)
			{
				$this->db->query($qSql." WHERE product_id='" . (int)$product['productId'] . "'");
			}
			return 	TRUE;
		}

		return 	false;
	}

	public function getDiscountCategories($discount_id)
	{
		$discount_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "discount_to_category WHERE discount_id = '" . (int)$discount_id . "'");

		foreach($query->rows as $result)
		{
			$discount_category_data[] = $result['category_id'];
		}

		return $discount_category_data;
	}

	public function getDiscountImages($discount_id)
	{
		$discount_image_data = array();

		$discount_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "discount_image WHERE discount_id = '" . (int)$discount_id . "' ORDER BY sort_order ASC");

		foreach($discount_image_query->rows as $discount_image)
		{
			$discount_image_description_data = array();

			$discount_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "discount_image_description WHERE discount_image_id = '" . (int)$discount_image['discount_image_id'] . "' AND discount_id = '" . (int)$discount_id . "'");

			foreach($discount_image_description_query->rows as $discount_image_description)
			{
				$discount_image_description_data[$discount_image_description['language_id']] = array('title'=> $discount_image_description['title']);
			}

			$discount_image_data[] = array(
				'discount_image_description'=> $discount_image_description_data,
				'link'                      => $discount_image['link'],
				'image'                     => $discount_image['image'],
				'sort_order'                => $discount_image['sort_order']
			);
		}

		return $discount_image_data;
	}

}
