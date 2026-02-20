<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
class Category extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('extension/opencart/module/category');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = [];
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}
		
		if ($this->customer->isLogged()) {
			$groupId = $this->customer->getGroupId();
		}else{
			$groupId =0;
		}

		$this->load->model('catalog/category');
		
		$this->load->model('catalog/product');

		$data['categories'] = [];
		
		$menu_data = $this->cache->get('module.category.' . (int)$this->config->get('config_language_id').'.'.$groupId.'.'.$this->config->get('config_store_id'));
		
		
		if (!$menu_data) {
			$categories = $this->model_catalog_category->getCategories(0);
			foreach ($categories as $category) {
				$children_data = [];

				//if ($category['category_id'] == $data['category_id']) {
					$children = $this->model_catalog_category->getCategories($category['category_id']);

					foreach ($children as $child) {
						$filter_data = [
							'filter_category_id'  => $child['category_id'], 
							'filter_sub_category' => true
						];

						$children_data[] = [
							'type'	=> '0',
							'category_id' => $child['category_id'],
							'name'        => $child['name'],
							'name_count'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
							
							'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_' . $child['category_id'])
						];
					}
				//}

				$filter_data = [
					'filter_category_id'  => $category['category_id'],
					'filter_sub_category' => true
				];
				
				if ($this->model_catalog_category->hasAddCateogories($category['category_id'],1)){
					$children_data[] = [
						'type'	=> '1',
						'name'  => 'NEW ARRIVALS',
						'category_id'=>$category['category_id'],
						'children' =>  [],
						'count' => '0',
						'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_newarriwals')
						];
				}	
				
				if ($this->model_catalog_category->hasAddCateogories($category['category_id'],2)){
					$children_data[] = [
						'type'	=> '2',
						'name'  => 'BEST SELLERS',
						'category_id'=> $category['category_id'],
						'children' =>  [],
						'count' => '0',
						'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_best')
						];
				}
				
				if ($this->model_catalog_category->hasAddCateogories($category['category_id'],3)){
					$children_data[] = [
						'type'	=> '3',
						'name'  => 'GIFT IDEAS',
						'category_id'=>$category['category_id'],
						'children' =>  [],
						'count' => '0',
						'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_gift')
						];
				}
				
				if ($this->model_catalog_category->hasAddCateogories($category['category_id'],4)){
					$children_data[] = [
						'type'	=> '4',
						'name'  => 'SALE ITEMS',
						'category_id'=> $category['category_id'],
						'children' =>  array(),
						'count' => '0',
						'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_sale')
						];
				}
				
				if ($this->model_catalog_category->hasAddCateogories($category['category_id'],5)){
					$children_data[] = [
						'type'	=> '5',
						'name'  => 'CLEARANCE',
						'category_id'=> $category['category_id'],
						'children' =>  [],
						'count' => '0',
						'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_clearance')
						];
				}
			
				$data['categories'][] = [
					'category_id' => $category['category_id'],
					'name'        => $category['name'],
					'name_count'  => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'children'    => $children_data,
					'href'        => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'])
				];
			}
			$this->cache->set('module.category.' . (int)$this->config->get('config_language_id').'.'.$groupId.'.'.$this->config->get('config_store_id'), $data['categories']);	
		}else{
			$data['categories']  = $menu_data;
		}


		return $this->load->view('extension/opencart/module/category', $data);
	}
}
