<?php
namespace Opencart\Catalog\Controller\Bytao;
class Feed extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/feed';
	private $C = 'feed';
	private $Tkn = 'user_token';
	private $model ;
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function google_prods():void {
	
		$output  = '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$products = $this->model_catalog_product->getProducts();
		foreach ($products as $product) {
				
				if ($product['image'] && $product['status']==1) {/**/
					$image = '';
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
					
					if (strpos($product['image'], ' ') === false) {
						if (strpos($product['image'], '&') === false) {
							if ($image != '') {
						
					$output .= '<url>';
					$output .= '<loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>';
					$output .= '<changefreq>weekly</changefreq>';
					$output .= '<priority>1.0</priority>';
					$output .= '<image:image>';
					$output .= '<image:loc>' .$image . '</image:loc>';
					$output .= '<image:caption>' . str_replace('&','&amp;',$product['name']) . '</image:caption>';
					$output .= '<image:title>' .  str_replace('&','&amp;',$product['name']) . '</image:title>';
					$output .= '</image:image>';
					$output .= '</url>';
					
							}
						}
					}
				}
			}
			
		$this->load->model('catalog/category');

		$output .= $this->getCategories(0);
		$output .= '</urlset>';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);	
	}
	
	public function index() {
		
			$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
			$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
			$output .= '  <channel>';
			$output .= '  <title>' . $this->config->get('config_name') . '</title>';
			$output .= '  <description>' . $this->config->get('config_meta_description') . '</description>';
			$output .= '  <link>' . $this->config->get('config_url') . '</link>';

			$this->load->model('extension/feed/google_base');
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');

			$this->load->model('tool/image');

			$product_data = array();

			$google_base_categories = $this->model_extension_feed_google_base->getCategories();

			foreach ($google_base_categories as $google_base_category) {
				$filter_data = array(
					'filter_category_id' => $google_base_category['category_id'],
					'filter_filter'      => false
				);

				$products = $this->model_catalog_product->getProducts($filter_data);

				foreach ($products as $product) {
					if (!in_array($product['product_id'], $product_data) && $product['description']) {
						
						$product_data[] = $product['product_id'];
						
						$output .= '<item>';
						$output .= '<title><![CDATA[' . $product['name'] . ']]></title>';
						$output .= '<link>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</link>';
						$output .= '<description><![CDATA[' . strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) . ']]></description>';
						$output .= '<g:brand><![CDATA[' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>';
						$output .= '<g:condition>new</g:condition>';
						$output .= '<g:id>' . $product['product_id'] . '</g:id>';

						if ($product['image']) {
							$output .= '  <g:image_link>' . $this->model_tool_image->resize($product['image'], 500, 500) . '</g:image_link>';
							//$output .= '  <g:image_link>' . $this->model_tool_image->getProdImage($product['model'],$product['image'],500,500) . '</g:image_link>';
						} else {
							$output .= '  <g:image_link></g:image_link>';
						}

						$output .= '  <g:model_number>' . $product['model'] . '</g:model_number>';

						if ($product['mpn']) {
							$output .= '  <g:mpn><![CDATA[' . $product['mpn'] . ']]></g:mpn>' ;
						} else {
							$output .= '  <g:identifier_exists>false</g:identifier_exists>';
						}

						if ($product['upc']) {
							$output .= '  <g:upc>' . $product['upc'] . '</g:upc>';
						}

						if ($product['ean']) {
							$output .= '  <g:ean>' . $product['ean'] . '</g:ean>';
						}

						$currencies = array(
							'USD',
							'EUR',
							'GBP'
						);

						if (in_array($this->session->data['currency'], $currencies)) {
							$currency_code = $this->session->data['currency'];
							$currency_value = $this->currency->getValue($this->session->data['currency']);
						} else {
							$currency_code = 'USD';
							$currency_value = $this->currency->getValue('USD');
						}

						if ((float)$product['special']) {
							$output .= '  <g:price>' .  $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						} else {
							$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						}

						$output .= '  <g:google_product_category>' . $google_base_category['google_base_category_id'] . '</g:google_product_category>';

						$categories = $this->model_catalog_product->getCategories($product['product_id']);

						foreach ($categories as $category) {
							$path = $this->getPath($category['category_id']);

							if ($path) {
								$string = '';

								foreach (explode('_', $path) as $path_id) {
									$category_info = $this->model_catalog_category->getCategory($path_id);

									if ($category_info) {
										if (!$string) {
											$string = $category_info['name'];
										} else {
											$string .= ' &gt; ' . $category_info['name'];
										}
									}
								}

								$output .= '<g:product_type><![CDATA[' . $string . ']]></g:product_type>';
							}
						}

						$output .= '  <g:quantity>' . $product['quantity'] . '</g:quantity>';
						$output .= '  <g:weight>' . $this->weight->format($product['weight'], $product['weight_class_id']) . '</g:weight>';
						$output .= '  <g:availability><![CDATA[' . ($product['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>';
						$output .= '</item>';
					}
				}
			}

			$output .= '  </channel>';
			$output .= '</rss>';

			$this->response->addHeader('Content-Type: application/rss+xml');
			$this->response->setOutput($output);
		
	}
	
	protected function getCategories(int $parent_id, string $current_path = ''):string {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$output .= '<url>';
			$output .= '<loc>' . $this->url->link('product/category', 'path=' . $new_path) . '</loc>';
			$output .= '<changefreq>weekly</changefreq>';
			$output .= '<priority>0.7</priority>';
			$output .= '</url>';

			$products = $this->model_catalog_product->getProducts(array('filter_category_id' => $result['category_id']));

			foreach ($products as $product) {
				$image = '';
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
					
					if (strpos($product['image'], ' ') === false) {
						if (strpos($product['image'], '&') === false) {
							if ($image != '') {
						
									$output .= '<url>';
									$output .= '<loc>' . $this->url->link('product/product', 'path=' . $new_path . '&product_id=' . $product['product_id']) . '</loc>';
									$output .= '<changefreq>weekly</changefreq>';
									$output .= '<priority>1.0</priority>';
									$output .= '<image:image>';
									$output .= '<image:loc>' .$image . '</image:loc>';
									$output .= '<image:caption>' . str_replace('&','&amp;',$product['name']) . '</image:caption>';
									$output .= '<image:title>' .  str_replace('&','&amp;',$product['name']) . '</image:title>';
									$output .= '</image:image>';
									$output .= '</url>';
							}
						}
					}
			}

			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		return $output;
	}



	protected function getPath($parent_id, $current_path = '') {
		$category_info = $this->model_catalog_category->getCategory($parent_id);

		if ($category_info) {
			if (!$current_path) {
				$new_path = $category_info['category_id'];
			} else {
				$new_path = $category_info['category_id'] . '_' . $current_path;
			}

			$path = $this->getPath($category_info['parent_id'], $new_path);

			if ($path) {
				return $path;
			} else {
				return $new_path;
			}
		}
	}
	
	public function xml():void {
			$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
			$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
			$output .= '  <channel>';
			$output .= '  <title>' . $this->config->get('config_name') . '</title>';
			$output .= '  <description>' . $this->config->get('config_meta_description') . '</description>';
			$output .= '  <link>' . $this->config->get('config_url') . '</link>';
		
			$this->load->model('bytao/feed');
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');

			$this->load->model('tool/image');

			$product_data = array();

			$products = $this->model_catalog_product->getProducts();

			foreach ($products as $product) {
				if (!in_array($product['product_id'], $product_data) && $product['description']) {
					
					$product_data[] = $product['product_id'];
					
					$output .= '<item>';
					$output .= '<title><![CDATA[' . $product['name'] . ']]></title>';
					$output .= '<link>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</link>';
					$output .= '<description><![CDATA[' . strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) . ']]></description>';
					if($product['manufacturer']){
						$output .= '<g:brand><![CDATA[' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>';
					}
					$output .= '<g:condition>new</g:condition>';
					$output .= '<g:id>' . $product['product_id'] . '</g:id>';

					if ($product['image']) {
						
						$output .= '  <g:image_link>' . $this->model_tool_image->resize($product['image'],500,500) . '</g:image_link>';
					} else {
						$output .= '  <g:image_link></g:image_link>';
					}

					$output .= '  <g:model_number>' . $product['model'] . '</g:model_number>';

					if ($product['mpn']) {
						$output .= '  <g:mpn><![CDATA[' . $product['mpn'] . ']]></g:mpn>' ;
					} else {
						$output .= '  <g:identifier_exists>false</g:identifier_exists>';
					}

					if ($product['upc']) {
						$output .= '  <g:upc>' . $product['upc'] . '</g:upc>';
					}

					if ($product['ean']) {
						$output .= '  <g:ean>' . $product['ean'] . '</g:ean>';
					}

					$currencies = array(
						'USD',
						'EUR',
						'GBP'
					);

					if (in_array($this->session->data['currency'], $currencies)) {
						$currency_code = $this->session->data['currency'];
						$currency_value = $this->currency->getValue($this->session->data['currency']);
					} else {
						$currency_code = 'USD';
						$currency_value = $this->currency->getValue('USD');
					}

					if ((float)$product['special']) {
						//$output .= '  <g:price>' .  $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], true,true) . '</g:price>';
					} else {
						//$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
						$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], true,true) . '</g:price>';
						
					}

					//$output .= '  <g:google_product_category>' . $google_base_category['google_base_category_id'] . '</g:google_product_category>';

					$categories = $this->model_catalog_product->getCategories($product['product_id']);

					foreach ($categories as $category) {
						$path = $this->getPath($category['category_id']);

						if ($path) {
							$string = '';

							foreach (explode('_', $path) as $path_id) {
								$category_info = $this->model_catalog_category->getCategory($path_id);

								if ($category_info) {
									if (!$string) {
										$string = $category_info['name'];
									} else {
										$string .= ' &gt; ' . $category_info['name'];
									}
								}
							}

							$output .= '<g:product_type><![CDATA[' . $string . ']]></g:product_type>';
						}
					}

					$output .= '  <g:quantity>' . $product['quantity'] . '</g:quantity>';
					$output .= '  <g:weight>' . $this->weight->format($product['weight'], $product['weight_class_id']) . '</g:weight>';
					$output .= '  <g:availability><![CDATA[' . ($product['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>';
					$output .= '</item>';
				}
			}
			
			$output .= '  </channel>';
			$output .= '</rss>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
	}

	public function google():void
	{

		$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
		$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		$output .= '  <channel>';
		$output .= '  <title>' . $this->config->get('config_name') . '</title>';
		$output .= '  <description>' . $this->config->get('config_meta_description') . '</description>';
		$output .= '  <link>' . $this->config->get('config_url') . '</link>';

		$this->load->model('bytao/feed');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$product_data = array();

		$products = $this->model_catalog_product->getProducts();

		foreach ($products as $product) {
			if (!in_array($product['product_id'], $product_data) && $product['description']) {

				$product_data[] = $product['product_id'];

				$output .= '<item>';
				$output .= '<title><![CDATA[' . $product['name'] . ']]></title>';
				$output .= '<link>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</link>';
				$output .= '<description><![CDATA[' . strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) . ']]></description>';
				if ($product['manufacturer']) {
					$output .= '<g:brand><![CDATA[' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>';
				}
				$output .= '<g:condition>new</g:condition>';
				$output .= '<g:id>' . $product['product_id'] . '</g:id>';

				if ($product['image']) {

					$output .= '  <g:image_link>' . $this->model_tool_image->resize($product['image'],500,500) . '</g:image_link>';
				} else {
					$output .= '  <g:image_link></g:image_link>';
				}

				$output .= '  <g:model_number>' . $product['model'] . '</g:model_number>';

				if ($product['mpn']) {
					$output .= '  <g:mpn><![CDATA[' . $product['mpn'] . ']]></g:mpn>' ;
				} else {
					$output .= '  <g:identifier_exists>false</g:identifier_exists>';
				}

				if ($product['upc']) {
					$output .= '  <g:upc>' . $product['upc'] . '</g:upc>';
				}

				if ($product['ean']) {
					$output .= '  <g:ean>' . $product['ean'] . '</g:ean>';
				}

				$currencies = [
					'USD',
					'EUR',
					'GBP',
					'TRY'
				];

				if (in_array($this->session->data['currency'], $currencies)) {
					$currency_code = $this->session->data['currency'];
					$currency_value = $this->currency->getValue($this->session->data['currency']);
				} else {
					$currency_code = 'USD';
					$currency_value = $this->currency->getValue('USD');
				}

				$price='';
				$sale_price ='';

				if ((float)$product['old_price']) {
					$price = $this->currency->format($this->tax->calculate($product['old_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], true,true);
					$sale_price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], true,true);
					$output .= '  <g:price>' . str_replace(['$',',','£'],'',$price).' '.$currency_code. '</g:price>';
					$output .= '  <g:sale_price>' . str_replace(['$',',','£'],'',$sale_price).' '.$currency_code. '</g:sale_price>';
				} else {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], true,true);
					$output .= '  <g:price>' . str_replace('$','',$sale_price).' '.$currency_code. '</g:price>';
				}

				$categories = $this->model_catalog_product->getCategories($product['product_id']);

				foreach ($categories as $category) {
					$path = $this->getPath($category['category_id']);

					if ($path) {
						$string = '';

						foreach (explode('_', $path) as $path_id) {
							$category_info = $this->model_catalog_category->getCategory($path_id);

							if ($category_info) {
								if (!$string) {
									$string = $category_info['name'];
								} else {
									$string .= ' &gt; ' . $category_info['name'];
								}
							}
						}

						$output .= '<g:product_type><![CDATA[' . $string . ']]></g:product_type>';
					}
				}

				$output .= '  <g:quantity>' . $product['quantity'] . '</g:quantity>';
				$output .= '  <g:weight>' . $this->weight->format($product['weight'], $product['weight_class_id']) . '</g:weight>';
				$output .= '  <g:availability><![CDATA[' . ($product['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>';
				$output .= '</item>';
			}
		}

		$output .= '  </channel>';
		$output .= '</rss>';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
	}

	public function yandex()
	{
		$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
		$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		$output .= '  <channel>';
		$output .= '  <title>' . $this->config->get('config_name') . '</title>';
		$output .= '  <description>' . $this->config->get('config_meta_description') . '</description>';
		$output .= '  <link>' . $this->config->get('config_url') . '</link>';

		$this->load->model('bytao/feed');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$product_data = [];

		$products = $this->model_catalog_product->getProducts();

		foreach ($products as $product) {
			$output .= '<url>';
			$serverpath = explode('://', $this->url->link('product/product', 'product_id=' . $product['product_id'])) ;
			$output .= '<loc>' . $serverpath['0'] . '://' .$serverpath['1'] . '</loc>';
			$output .= '<changefreq>daily</changefreq>';
			$output .= '<priority>0.7</priority>';
			$output .= '</url>';
		}

		$this->load->model('catalog/category');

		$output .= $this->getCategories(0);

		$this->load->model('catalog/manufacturer');

		$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

		foreach ($manufacturers as $manufacturer) {
			$output .= '<url>';
			$serverpath = explode('://', $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'])) ;
			$output .= '<loc>' . $serverpath['0'] . '://' .$serverpath['1'] . '</loc>';
			$output .= '<changefreq>daily</changefreq>';
			$output .= '<priority>0.5</priority>';
			$output .= '</url>';

			$products = $this->model_catalog_product->getProducts(array('filter_manufacturer_id' => $manufacturer['manufacturer_id']));

			foreach ($products as $product) {
				$output .= '<url>';
				$serverpath = explode('://', $this->url->link('product/product', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id']));
				$output .= '<loc>' . $serverpath['0'] . '://' .$serverpath['1'] . '</loc>';

				$output .= '<changefreq>daily</changefreq>';
				$output .= '<priority>1.0</priority>';
				$output .= '</url>';
			}
		}

		$this->load->model('catalog/information');

		$informations = $this->model_catalog_information->getInformations();

		foreach ($informations as $information) {
			$output .= '<url>';
			$serverpath = explode('://', $this->url->link('information/information', 'information_id=' . $information['information_id']));
			$output .= '<loc>' . $serverpath['0'] . '://' .$serverpath['1'] . '</loc>';
			$output .= '<changefreq>weekly</changefreq>';
			$output .= '<priority>0.5</priority>';
			$output .= '</url>';
		}

		$output .= '</urlset>';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
	}

	public function shopify()
	{
		$TITLES=[];
		$TITLES['Title']='Title';
		$TITLES['URL handle']='URL handle';
		$TITLES['Description']='Description';
		$TITLES['Vendor']='Vendor';
		$TITLES['Product category']='Product category';
		$TITLES['Type']='Type';
		$TITLES['Tags']='Tags';
		$TITLES['Published on online store']='Published on online store';
		$TITLES['Status']='Status';
		$TITLES['SKU']='SKU';
		$TITLES['Barcode']='Barcode';
		$TITLES['Option1 name']='Option1 name';
		$TITLES['Option1 value']='Option1 value';
		$TITLES['Option2 name']='Option2 name';
		$TITLES['Option2 value']='Option2 value';
		$TITLES['Option3 name']='Option3 name';
		$TITLES['Option3 value']='Option3 value';
		$TITLES['Price']='Price';
		$TITLES['Price / International'] = 'Price / International';
		$TITLES['Compare-at price']='Compare-at price';
		$TITLES['Compare-at price / International']='Compare-at price / International';
		$TITLES['Cost per item']='Cost per item';
		$TITLES['Charge tax']='Charge tax';
		$TITLES['Tax code']='Tax code';
		$TITLES['Variant Grams'] = 'Variant Grams';
		$TITLES['Inventory tracker'] = 'Inventory tracker';
		$TITLES['Inventory quantity'] = 'Inventory quantity';
		$TITLES['Inventory policy'] = 'Inventory policy';
		$TITLES['Continue selling when out of stock']='Continue selling when out of stock';
		$TITLES['Weight value (grams)']='Weight value (grams)';
		$TITLES['Weight unit for display']='Weight unit for display';
		$TITLES['Requires shipping']='Requires shipping';
		$TITLES['Fulfillment service']='Fulfillment service';
		$TITLES['Image Src']='Image Src';
		$TITLES['Image position']='Image position';
		$TITLES['Image alt text']='Image alt text';
		$TITLES['Variant image']='Variant image';
		$TITLES['Gift card']='Gift card';
		$TITLES['SEO title']='SEO title';
		$TITLES['SEO description']='SEO description';
		$TITLES['Google Shopping / Google product category']='Google Shopping / Google product category';
		$TITLES['Google Shopping / Gender']='Google Shopping / Gender';
		$TITLES['Google Shopping / Age group']='Google Shopping / Age group';
		$TITLES['Google Shopping / MPN']='Google Shopping / MPN';
		$TITLES['Google Shopping / AdWords Grouping']='Google Shopping / AdWords Grouping';
		$TITLES['Google Shopping / AdWords labels']='Google Shopping / AdWords labels';
		$TITLES['Google Shopping / Condition']='Google Shopping / Condition';
		$TITLES['Google Shopping / Custom product']='Google Shopping / Custom product';
		$TITLES['Google Shopping / Custom label 0']='Google Shopping / Custom label 0';
		$TITLES['Google Shopping / Custom label 1']='Google Shopping / Custom label 1';
		$TITLES['Google Shopping / Custom label 2']='Google Shopping / Custom label 2';
		$TITLES['Google Shopping / Custom label 3']='Google Shopping / Custom label 3';
		$TITLES['Google Shopping / Custom label 4']='Google Shopping / Custom label 4';
		$TITLES['product.metafields.custom.model']='product.metafields.custom.model';
		$TITLES['product.metafields.custom.grup']='product.metafields.custom.grup';
		$TITLES['product.metafields.custom.pgender']='product.metafields.custom.pgender';
		$TITLES['product.metafields.custom.clearance']='product.metafields.custom.clearance';
		$TITLES['product.metafields.custom.newarrivals']='product.metafields.custom.newarrivals';
		$TITLES['product.metafields.custom.bestseller']='product.metafields.custom.bestseller';

		$this->load->model('bytao/feed');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$datetime        = date('Y-m-d-H-i-s');
		$sname= explode('.',$this->config->get('config_name'));
		if (oc_strtolower($sname[0])=='www') {
			$CSVname = "shopify-".(oc_strtolower($sname[1])).'-'.$datetime.".csv";
		} else {
			$CSVname = "shopify-".(oc_strtolower(trim($sname[0]))).'-'.$datetime.".csv";
		}

		$CSV = fopen(DIR_CACHE.$CSVname, "w");
		fputcsv($CSV, array_values($TITLES));

		$_TITLES = $TITLES;
		$products = $this->model_catalog_product->getProducts();
		foreach ($products as $product) {
			$product['options'] = $this->model_catalog_product->getOptions($product['product_id']);
			$product['images'] = $this->model_catalog_product->getImages($product['product_id']);

			$ROWS = $this->hiveN($TITLES,$product);
			foreach ($ROWS as $row) {
				fputcsv($CSV,$row);
			}
		}

		fclose($CSV); // Closing the File
		
		$this->response->addHeader('Content-Type: text/csv');
		$this->response->addHeader('Content-Disposition: attachment; filename='.$CSVname);
		$this->response->addHeader('Cache-Control: no-cache, no-store, must-revalidate');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->addHeader('Expires: 0');
		$this->response->setOutput(file_get_contents(DIR_CACHE.$CSVname));
	}


}
