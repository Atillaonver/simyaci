<?php
namespace Opencart\Admin\Model\Bytao;
class Common extends \Opencart\System\Engine\Model {
	
	public function getStoreLanguages(array $data = []): array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		if ($data) 
		{
			$sql = "SELECT * FROM `" . DB_PREFIX . "language`";

			$sort_data = [
				'name',
				'code',
				'sort_order'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY `" . $data['sort'] . "`";
			} else {
				$sql .= " ORDER BY `sort_order`, `name`";
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
		else 
		{
			$language_data = $this->cache->get('admin.'.(int)$store_id.'.language');
			if (!$language_data) {
				$config = $this->getSetting('config',$store_id);
				$language_data = [];
				$lArray = isset($config['config_store_languages'])?implode(",",$config['config_store_languages']):[];
				
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code IN ('".(str_replace(",","','",$lArray))."') ORDER BY `sort_order`, `name`");

				foreach ($query->rows as $result) {
					if (!$result['extension']) {
						$image = HTTP_SERVER;
					} else {
						$image = HTTP_CATALOG . 'extension/' . $result['extension'] . '/admin/';
					}

					$language_data[$result['code']] = [
						'language_id' => $result['language_id'],
						'name'        => $result['name'],
						'code'        => $result['code'],
						'image'       => $image . 'language/' . strtolower($result['code']) . '/' . strtolower($result['code']) . '.png',
						'locale'      => $result['locale'],
						'extension'   => $result['extension'],
						'sort_order'  => $result['sort_order'],
						'status'      => $result['status']
					];
				}

				$this->cache->set('admin.'.(int)$store_id.'.language', $language_data);
				
			}
			return $language_data;
		}
	}
	
	public function getStoreLanguageByCode(string $code): int {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($code) . "'");

		$language = $query->row;

		return isset($language['language_id'])?$language['language_id']:0;
	}
	
	public function getStoreCurrencies(array $data = []): array {
		$store_id = isset($this->session->data['store_id']) ? $this->session->data['store_id']:0;
		
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "currency`";

			$sort_data = [
				'title',
				'code',
				'value',
				'date_modified'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY `" . $data['sort'] . "`";
			} else {
				$sql .= " ORDER BY `title`";
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
			$currency_data = $this->cache->get('currency.'.(int)$store_id);

			if (!$currency_data) {
				$currency_data = [];
				$config = $this->getSetting('config');
				$lArray = implode("','",$config['config_store_currencies']);
				
				
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "currency` WHERE code IN ('".$lArray."') ORDER BY `sort_order`");

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
	}
	
	public function getUserStores(int $user_id): array {
		$user_store_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user_to_store` WHERE `user_id` = '" . (int)$user_id . "'");

		foreach ($query->rows as $result) {
			$user_store_data[] = $result['store_id'];
		}

		return $user_store_data;
	}

	public function setSetting(string $code , array $data, int $store_id = 0): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = 'ctrl_prod', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '" . (int)$store_id . "', `code` = 'ctrl_prod', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value)) . "', `serialized` = '1'");
				}
			}
		}
	}
	
	public function getSetting(string $code, int $store_id = 0): array {
		$setting_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$setting_data[$result['key']] = $result['value'];
			} else {
				$setting_data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $setting_data;
	}
	
	public function getApi(int $api_id):array
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api` WHERE api_id = '" . (int)$api_id . "'");

		return $query->row;
	}


	public function cacheClear($ctype = 'home',$cval = ''):array
	{
		$action=[];
		
		$storeId = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		switch($storeId)
		{
			case 0:
			switch($ctype)
			{
				case 'home':
					$files = glob(DIR_CACHE . 'home.*.*');
					if($files)
					{
						foreach($files as $file)
						{
							if(is_file($file)){
								if(file_exists($file))
								{
									unlink($file);
								}
							}
						}
					}
				break;
				case 'cid':
					$files = glob(DIR_CACHE . 'category/cat.'.$this->request->get['cid'] . '*.*');
					if($files)
					{
						foreach($files as $file)
						{
							if(is_dir($file)){
								if((rrmdir($file)) === false){
									//return false;
								}
							}
							elseif(is_file($file)){
								if(file_exists($file))
								{
									unlink($file);
								}
							}

						}
					}
					break;
				case 'pid':
					$files = glob(DIR_CACHE . 'product/prod.'.$cval . '*.*');
					if($files)
					{
						foreach($files as $file)
						{
							if(file_exists($file))
							{
								unlink($file);
							}
						}
					}

					break;
				}
			break;
			case 1:
			case 2:

				$setting = $this->getSetting('config', $storeId);
				$api_info= $this->getApi($this->config->get('config_api_id'));
				
				switch($ctype)
				{
					case 'home':
						$tUrl = $setting['config_url'] . '/index.php?route=api/cache/clear&bytao=sender&home=1';
						break;
					case 'cid':
						$tUrl = $setting['config_url'] . '/index.php?route=api/cache|clear&bytao=sender&cid='.$cval;
						break;
					case 'pid':
						$tUrl = $setting['config_url'] . '/index.php?route=api/cache|clear&bytao=sender&pid='.$cval;
						break;
					default:
						$tUrl = $setting['config_url'] . '/index.php?route=api/cache|clear&bytao=sender&all=1';
				}
				
				$curl = curl_init();
				if(substr($setting['config_url'], 0, 5) == 'https'){
					curl_setopt($curl, CURLOPT_PORT, 443);
				}

				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLINFO_HEADER_OUT, true);
				curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_URL, $tUrl);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($api_info));
				$json = curl_exec($curl);
				
				if(!$json){
					
					$this->error['warning'] = sprintf($this->language->get('error_curl'), curl_error($curl), curl_errno($curl));
				}
				else
				{
					$response = json_decode($json, true);
				
					if(isset($response['success']))
					{
						$action['log'] = $response['success'];
						$action['item'] = isset($response['success'])?$response['success']:'All';
					}
					else
					{
						$this->error['warning'] = sprintf($this->language->get('error_curl'), curl_error($curl), curl_errno($curl));
					}
					$action['error']=$this->error;
					curl_close($curl);
				}
			break ;
		}

		return $action;
	}

	public function updateSortorder($sort_order=0, $product_id=0, $category_id=0):void{
		if($category_id==0){
			$this->db->query("UPDATE " . DB_PREFIX . "product SET sort_order = '" . (int)$sort_order . "' WHERE product_id = '" . (int)$product_id . "'");
		}else{
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET sort_order = '" . (int)$sort_order . "' WHERE product_id = '" . (int)$product_id . "' AND category_id='".(int)$category_id ."'");
		}
		$this->cache->delete('mProducts.'.$this->session->data['store_id'] );
		
		
	}
	
	public function updateProductSortorder(int $sort_order, int $product_id, int $category_id,$typ = ''):void {
		
		if($typ!=''){
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_addcategory SET sort_order = '" . (int)$sort_order . "' WHERE product_id = '" . (int)$product_id . "' AND category_id='".(int)$category_id ."' AND type='".$this->db->escape($typ)."'");
			
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET sort_order = '" . (int)$sort_order . "' WHERE product_id = '" . (int)$product_id . "' AND category_id='".(int)$category_id ."'");
			
		}
	}
	
	public function getProductColorCount(int $product_id):int{
		$query = $this->db->query("SELECT count(*) FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id. "'GROUP BY `color_id`,`product_id`");
		
		return count($query->rows);
	}
	
	
	
	
	
	
	/* TODO byTAO migration Content */
	public function migrateContent():void {
		/*
		$nquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description");
		foreach($nquery->rows as $row){
			$cols = explode(':::',html_entity_decode(nl2br(($row['description']?$row['description']:''),FALSE), ENT_QUOTES , 'UTF-8'));
			$paragraph1 = isset($cols[0])?strip_tags($cols[0]):'';
			$paragraph2 = isset($cols[1])?strip_tags($cols[1]):'';
			$bullet = isset($cols[2])?strip_tags($cols[2]):'';
			$this->db->query("UPDATE " . DB_PREFIX . "product_description SET description ='". $this->db->escape($paragraph1)."',description_alt ='". $this->db->escape($paragraph2)."',bullet ='". $this->db->escape($bullet)."' WHERE product_id='".(int)$row['product_id']."' AND language_id='".(int)$row['language_id']."'") ;
			
		}
		*/
		
		$cquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description");
		$rows = [];
		foreach($cquery->rows as $row){
			$contents = explode("</by_content>",html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8'));
			foreach($contents as $content){
				$cont = explode("<by_content>",$content);
				if(isset($cont[1])){
					$rows[]=$cont[1];
				}
			}
			$description = implode(' ',$rows);
			if($description){
				$this->db->query("UPDATE " . DB_PREFIX . "category_description SET description ='". $this->db->escape($description)."' WHERE category_id='".(int)$row['category_id']."' AND language_id='".(int)$row['language_id']."'") ;
			}	
		}
		
		
		$iquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description");
		$rows = [];
		foreach($iquery->rows as $row){
			$contents = explode("</by_content>",html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8'));
			foreach($contents as $content){
				$cont = explode("<by_content>",$content);
				if(isset($cont[1])){
					$rows[]=$cont[1];
				}
			}
			$description = implode(' ',$rows);
			if($description){
				$this->db->query("UPDATE " . DB_PREFIX . "information_description SET description ='". $this->db->escape($description)."' WHERE information_id='".(int)$row['information_id']."' AND language_id='".(int)$row['language_id']."'") ;
			}
		}
	}
	
	
	/* TODO byTAO migration URL */
	public function migrateSeoUrl():void {
		$this->load->model('design/seo_url');
		$this->load->model('catalog/category');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias");
		foreach($query->rows as $row){
			$rquery =explode('=', $row['query']);
			$key	=$rquery[0];
			$value 	=isset($rquery[1])?$rquery[1]:'';
			
			$keyword = $row['keyword'];
			$language_id = $row['language_id'];
			$store_id = $row['store_id'];
			if(!$keyword){
				$nquery = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_keyword WHERE url_alias_id='".(int)$row['url_alias_id']."' AND language_id='".(int)$row['language_id']."' LIMIT 1");
				$keyword =isset($nquery->row['keyword'])?$nquery->row['keyword']:'';
			}
			
			switch($key){
				case 'product_id':
					$minquery = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "product_description` id WHERE  id.`product_id` = '".(int)$value."' AND id.language_id ='".(int)$language_id."' LIMIT 1 ");
					$title = isset($minquery->row['name'])?$minquery->row['name']:'';
					break;
					
				case 'page_id':
					$minquery = $this->db->query("SELECT `title` FROM `" . DB_PREFIX . "page_description` id WHERE  id.`page_id` = '".(int)$value."' AND id.language_id ='".(int)$language_id."' LIMIT 1 ");
					$title = isset($minquery->row['title'])?$minquery->row['title']:'';
					break;
					
				case 'category_id':
					$minquery = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "category_description` id WHERE  id.`category_id` = '".(int)$value."' AND id.language_id ='".(int)$language_id."' LIMIT 1 ");
					$title = isset($minquery->row['name'])?$minquery->row['name']:'';
					
					break;
				case 'information_id':
					$minquery = $this->db->query("SELECT `title` FROM `" . DB_PREFIX . "information_description` id WHERE  id.`information_id` = '".(int)$value."' AND id.language_id ='".(int)$language_id."' LIMIT 1 ");
					$title = isset($minquery->row['title'])?$minquery->row['title']:'';
					break;
				case 'article_id':
					break;
				default:
				$title = '';
			}
			
			if($key=='category_id'){
				
				$category_id = $value;
				$key='path';
				/*
				$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$row['store_id'] . "', `language_id` = '" . (int)$row['language_id'] . "', `key` = '".$key."', `value`= '" . $this->db->escape($category_id) . "', `keyword` = '" . $this->db->escape($keyword) . "', `title` = '" . $this->db->escape($title) . "'");
				
				
				$parent_path = $this->model_catalog_category->getPath($category_id);
				
				if (!$parent_path) {
					$path = $category_id;
				} else {
					$path = $parent_path . '_' . $category_id;
				}
				
				$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyValue('path', $parent_path, $store_id, $language_id);

				if ($seo_url_info) {
					$keyword = $seo_url_info['keyword'] . '/' . $keyword;
				}
				$key='path';
				$value = $path;
				*/
			}
			
			$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$row['store_id'] . "', `language_id` = '" . (int)$row['language_id'] . "', `key` = '".$key."', `value`= '" . $this->db->escape($value) . "', `keyword` = '" . $this->db->escape($keyword) . "', `title` = '" . $this->db->escape($title) . "'");
			
		}	
	}
	
	public function getPaths(int $category_id): array {
		$query = $this->db->query("SELECT `category_id`, `path_id`, `level` FROM `" . DB_PREFIX . "category_path` WHERE `category_id` = '" . (int)$category_id . "' ORDER BY `level` ASC");

		return $query->rows;
	}
	
	public function getPath(int $category_id): string {
		return implode('_', array_column($this->getPaths($category_id), 'path_id'));
	}
	
	public function getParent(int $category_id): int {
		$query = $this->db->query("SELECT `parent_id` FROM `" . DB_PREFIX . "category` WHERE `category_id` = '" . (int)$category_id . "' LIMIT 1");

		return isset($query->row['parent_id'])?$query->row['parent_id']:0;
	}
	
	
	public function confirmWord( string $keyword,string $key, int $item_id = 0, int $language_id = 0): bool {
		
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:$store_id;
		if($key=='category_id'){
			$sql = "SELECT count(*) AS total FROM `" . DB_PREFIX . "seo_url` WHERE (`keyword` = '" . $this->db->escape($keyword) . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "') && `path` != '%" . $item_id . "')";
		}else{
			$sql = "SELECT count(*) AS total FROM `" . DB_PREFIX . "seo_url` WHERE (`keyword` = '" . $this->db->escape($keyword) . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "') && (`keyword` = '" . $this->db->escape($keyword) . "' AND `".$key."` != '" . (int)$item_id . "' AND `language_id` = '" . (int)$language_id . "')";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total']>0?false:true;
	}

	
	public function getSeoUrls(array $data = []): array {
		
		$storeId = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` ";

		$implode = [];

		if (!empty($data['filter_title'])) {
			$implode[] = "`title` LIKE '%" . $this->db->escape((string)$data['filter_title']) . "%'";
		}
		
		if (!empty($data['filter_keyword'])) {
			$implode[] = "`keyword` LIKE '%" . $this->db->escape((string)$data['filter_keyword']) . "%'";
		}

		if (!empty($data['filter_key'])) {
			$implode[] = "`key` = '" . $this->db->escape((string)$data['filter_key']) . "'";
		}

		if (!empty($data['filter_value'])) {
			$implode[] = "`value` LIKE '" . $this->db->escape((string)$data['filter_value']) . "'";
		}

		$implode[] = "`store_id` = '" . (int)$storeId . "'";

		if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'keyword',
			'key',
			'value',
			'store_id',
			'language_id'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `title`";
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

	public function titledSeoUrl():void {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url");
		foreach($query->rows as $row){
			$title = '';
			switch($row['key']){
				case 'product_id':
					$minquery = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "product_description` id WHERE  id.`product_id` = '".(int)$row['value']."' AND id.language_id ='".(int)$row['language_id']."' LIMIT 1 ");
					$title = isset($minquery->row['name'])?$minquery->row['name']:'';
					break;
					
				case 'page_id':
					$minquery = $this->db->query("SELECT `title` FROM `" . DB_PREFIX . "page_description` id WHERE  id.`page_id` = '".(int)$row['value']."' AND id.language_id ='".(int)$row['language_id']."' LIMIT 1 ");
					$title = isset($minquery->row['title'])?$minquery->row['title']:'';
					break;
					
				case 'path':
					$path= explode('_',$row['value']);
					$category_id = end($path);
					$minquery = $this->db->query("SELECT `name` FROM `" . DB_PREFIX . "category_description` id WHERE  id.`category_id` = '".(int)$category_id."' AND id.language_id ='".(int)$row['language_id']."' LIMIT 1 ");
					$title = isset($minquery->row['name'])?$minquery->row['name']:'';
					
					break;
				case 'information_id':
					$minquery = $this->db->query("SELECT `title` FROM `" . DB_PREFIX . "information_description` id WHERE  id.`information_id` = '".(int)$row['value']."' AND id.language_id ='".(int)$row['language_id']."' LIMIT 1 ");
					$title = isset($minquery->row['title'])?$minquery->row['title']:'';
					break;
				case 'article_id':
					break;
				default:
				$title = '';
			}
			
			if($title){
				$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `title` = '" . $this->db->escape($title) . "' WHERE seo_url_id='".(int)$row['seo_url_id']."'");
			}
			
			
			
		}
		
		
		return;
	}

	
	public function getCategories($categories):array {
		$cData=[];
		
		foreach($categories as $category_id){
			$sql = "SELECT cp.`category_id` AS `category_id`, GROUP_CONCAT(cd1.`name` ORDER BY cp.`level` SEPARATOR ' > ') AS `name`  FROM `" . DB_PREFIX . "category_path` cp LEFT JOIN `" . DB_PREFIX . "category` c1 ON (cp.`category_id` = c1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category` c2 ON (cp.`path_id` = c2.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd1 ON (cp.`path_id` = cd1.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_description` cd2 ON (cp.`category_id` = cd2.`category_id`)  WHERE cd1.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND cd2.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND cp.`category_id` = '".$category_id."'";
			$query = $this->db->query($sql);
			 foreach($query->rows as $ROW){
			 	$cData[]=$ROW;
			}
		}
		
		
		
		return $cData;
	}
	
	public function getAddProducts(array $data = []):array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_to_addcategory p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND  p2c.type = '" . $this->db->escape($data['filter_type']) . "' AND p.status='1' AND p2s.store_id='".(int)$store_id ."' ORDER BY p2c.sort_order ASC";
		
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getProducts($products):array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$pData=implode("','",$products);
		
		$sql = "SELECT * FROM `" .  DB_PREFIX . "product_description` pd LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (pd.`product_id` = p2s.`product_id`) WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id='".(int)$store_id."' AND pd.`product_id` IN('".$pData."')  ";
		
		$query = $this->db->query($sql);
		return $query->rows;
		
	}
	
	public function getCategoryRowOrder($category_id,$sale_type=''){
		$query = $this->db->query("SELECT row_order AS rowOrder FROM " . DB_PREFIX . "category_product_sort_order WHERE category_id = '" . (int)$category_id . "' AND store_id='".(int)$this->session->data['store_id'] ."' ".($sale_type?" AND sale_type LIKE '".$sale_type."'":""));
		
		return isset($query->row['rowOrder'])?$query->row['rowOrder']:'';
	}
	
	public function setCategoryRowOrder($category_id,$row_order,$sale_type=''){
		
		$sql="SELECT count(*) AS total FROM " . DB_PREFIX . "category_product_sort_order WHERE category_id = '" . (int)$category_id . "'".($sale_type?" AND sale_type LIKE '".$sale_type."'":"")." AND store_id='".(int)$this->session->data['store_id'] ."'";
		
		$query = $this->db->query($sql);
		
		if($query->row['total']>0){
			$this->db->query("UPDATE " . DB_PREFIX . "category_product_sort_order SET row_order = '" . $this->db->escape($row_order) . "' WHERE category_id = '" . (int)$category_id . "'  AND store_id='".(int)$this->session->data['store_id'] ."' ".($sale_type?" AND sale_type='".$sale_type."'":""));
		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_product_sort_order SET row_order = '" . $this->db->escape($row_order) . "', category_id = '" . (int)$category_id . "' , store_id='".(int)$this->session->data['store_id'] ."' ".($sale_type?", sale_type='".$this->db->escape($sale_type)."'":""));
		}
		

	}

	public function OCCustomer(){
		
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "customer`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "address`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "customer_activity`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "customer_login`;");
		
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "address'");
		$aRows = $aQ->rows;
		$aQuery = $this->db->query("SELECT * FROM oc_address");
		foreach($aQuery->rows as $ROW_a){
			$aData=[];
			foreach($aRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'address_id': $aid = $ROW_a[$col]; break;
					case 'default':$default=0; break;
				}	
				$aData[$col] = isset($ROW_a[$col])?$ROW_a[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "address ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "customer'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_customer");
		$address_id = '';
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $key => $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'customer_id': $customer_id = $ROW_c[$col]; break;
					case 'customer_group_id': $customer_group_id = $ROW_c[$col]; break;
					case 'address_id': $address_id = $ROW_c[$col];break;
					case 'language_id': $language_id = '1';break;
					case 'code': $code = '';break;
				}	
				$cData[$col] = isset($ROW_c[$col]) ? $ROW_c[$col]:$$col;
			}
			
			$sql = "INSERT INTO ".DB_PREFIX . "customer ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
			if($address_id){
				$this->db->query("UPDATE ".DB_PREFIX . "address SET default='1' WHERE address_id='".(int)$address_id."'" );
			}
			
		}
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "customer_activity'");
		$aRows = $aQ->rows;
		$aaQuery = $this->db->query("SELECT * FROM oc_customer_activity");
		foreach($aaQuery->rows as $ROW_a){
			$aData=[];
			foreach($aRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'customer_activity_id': $customer_activity_id = $ROW_a['activity_id']; break;
				}	
				$aData[$col] = isset($ROW_a[$col])?$ROW_a[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "customer_activity ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "customer_login'");
		$aRows = $aQ->rows;
		$aaQuery = $this->db->query("SELECT * FROM oc_customer_login");
		foreach($aaQuery->rows as $ROW_a){
			$aData=[];
			foreach($aRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$aData[$col] = isset($ROW_a[$col])?$ROW_a[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "customer_login ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[]  = $key;
				$vals[] = $this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
			
	}

	public function OCCatalog(){
		
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_related`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_description`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value_description`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_description`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_image`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_image_description`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option_value`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_reward`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_related`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_addcategory`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_category`;");
		//$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_to_category` DROP PRIMARY KEY, ADD PRIMARY KEY( `type_id`);");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_layout`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_to_store`;");
		
		
		$query = $this->db->query("SELECT * FROM oc_category_related");
		foreach($query->rows as $ROW_c){
			$aData=[];
			$rQuery = $this->db->query("SELECT count(*) as total FROM oc_category_related WHERE category_id='".(int)$ROW_c['category_id']."' AND related_id='".(int)$ROW_c['related_id']."' ");
			if((int)$rQuery->row['total']<1){
				$this->db->query("INSERT INTO ".DB_PREFIX . "category_related (category_id,related_id) VALUES ('".(int)$ROW_c['category_id']."','".(int)$ROW_c['related_id']."')");
			}
		}
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "option'");
		$aRows = $aQ->rows;
		$query = $this->db->query("SELECT * FROM oc_option");
		foreach($query->rows as $ROW_c){
			$aData=[];
			foreach($aRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$aData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "option ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "option_description'");
		$aRows = $aQ->rows;
		$query = $this->db->query("SELECT * FROM oc_option_description");
		foreach($query->rows as $ROW_c){
			$aData=[];
			foreach($aRows as $ar){
					$col = $ar['COLUMN_NAME'];
					$aData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "option_description ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "option_value'");
		$aRows = $aQ->rows;
		$query = $this->db->query("SELECT * FROM oc_option_value");
		foreach($query->rows as $ROW_c){
			$aData=[];
			foreach($aRows as $ar){
					$col = $ar['COLUMN_NAME'];
					$aData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "option_value ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		$aQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "option_value_description'");
		$aRows = $aQ->rows;
		$query = $this->db->query("SELECT * FROM oc_option_value_description");
		foreach($query->rows as $ROW_c){
			$aData=[];
			foreach($aRows as $ar){
					$col = $ar['COLUMN_NAME'];
					$aData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "option_value_description ( `";
			$tmp = [];
			$vals = [];
			foreach( $aData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product'");
		$cRows = $cQ->rows;
		
		$query = $this->db->query("SELECT * FROM oc_product");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'master_id': $master_id = '0'; break;
					case 'variant': $variant = ''; break;
					case 'override': $override = '';break;
					case 'version': $version = '1';break;
					case 'image': $image = ($ROW_c['image'] != null) ?$ROW_c['image']:'';break;
				}	
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			
			$sql = "INSERT INTO ".DB_PREFIX . "product ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
			
		}
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_description'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_description");
		foreach($query->rows as $ROW_c){
			$cols = explode(':::',html_entity_decode(nl2br(($ROW_c['description']?$ROW_c['description']:''),FALSE), ENT_QUOTES , 'UTF-8'));
			$paragraph1 = isset($cols[0])?strip_tags($cols[0]):'';
			$paragraph2 = isset($cols[1])?strip_tags($cols[1]):'';
			$bullet 	= isset($cols[2])?strip_tags($cols[2]):'';
			
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'bullet': $bullet = $bullet; break;
					case 'description': $description = $paragraph2; break;
					case 'description_alt': $description_alt = $paragraph2; break;
				}	
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			
			$sql = "INSERT INTO ".DB_PREFIX . "product_description ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_image'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_image");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
					$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			
			$sql = "INSERT INTO ".DB_PREFIX . "product_image ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_image_description'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_image_description");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
					$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_image_description ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}


		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_option'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_option");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
					$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_option ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}


		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_option_value'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_option_value");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_option_value ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_reward'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_reward");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_reward ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		$query = $this->db->query("SELECT * FROM oc_product_related");
		foreach($query->rows as $ROW_c){
			$aData=[];
			$rQuery = $this->db->query("SELECT count(*) as total FROM oc_product_related WHERE product_id='".(int)$ROW_c['product_id']."' AND related_id='".(int)$ROW_c['related_id']."' ");
			if((int)$rQuery->row['total']<1){
				$this->db->query("INSERT INTO ".DB_PREFIX . "product_related (product_id,related_id) VALUES ('".(int)$ROW_c['product_id']."','".(int)$ROW_c['related_id']."')");
			}
		}
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_to_addcategory'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_to_addcategory");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_to_addcategory ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_to_category'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_to_category");
		foreach($query->rows as $ROW_c){
			
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_to_category ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			
			$rQuery = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$cData['product_id']."' AND category_id='".(int)$cData['category_id']."'");
			if((int)$rQuery->row['total']<1){
				$this->db->query( $sql );
			}
		}
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_to_layout'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_to_layout");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_to_layout ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "product_to_store'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_product_to_store");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:'';
			}
			$sql = "INSERT INTO ".DB_PREFIX . "product_to_store ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
			
	}	

	public function OCOrder(){
		
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order_fraud`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order_history`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order_option`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order_product`;");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "order_total`;");
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'language_code': $language_code = 'en-gb'; break;
					case 'subscription_id': $subscription_id = ''; break;
					case 'transaction_id': $transaction_id = '';break;
					case 'payment_address_id': $payment_address_id= '0';break;
					case 'shipping_address_id': $shipping_address_id = '0';break;
				}	
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			
			$sql = "INSERT INTO ".DB_PREFIX . "order ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
			
		}	
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order_fraud'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order_fraud");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
					$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "order_fraud ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}	
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order_history'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order_history");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
					$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "order_history ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}	
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order_option'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order_option");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "order_option ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}	
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order_product'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order_product");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'master_id': $master_id = ''; break;
				}	
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "order_product ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}
		
		
		
		$cQ = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . DB_PREFIX . "order_total'");
		$cRows = $cQ->rows;
		$query = $this->db->query("SELECT * FROM oc_order_total");
		foreach($query->rows as $ROW_c){
			$cData=[];
			foreach($cRows as $ar){
				$col = $ar['COLUMN_NAME'];
				switch($col){
					case 'extension': $extension = ''; break;
				}
				$cData[$col] = isset($ROW_c[$col])?$ROW_c[$col]:$$col;
			}
			$sql = "INSERT INTO ".DB_PREFIX . "order_total ( `";
			$tmp = [];
			$vals = [];
			foreach( $cData as $key => $value ){
				$tmp[] = $key;
				$vals[]=$this->db->escape($value);
			}				
			$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
			$this->db->query( $sql );
		}	
		
		
	}

}
