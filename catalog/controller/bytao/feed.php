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

		$product_data = [];

		$products = $this->model_catalog_product->getProducts();

		$this->log->write('PRODUCTS: ' . print_r($products, true));

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

	protected function hiveX(array $TITLES=[], array $hive=[]):array
	{
		$hiveRows=[];

		if ($hive) {
			//$pImages = $hive['images'];
			if (isset($hive['options']) && count($hive['options'])) {
			}
			$pImages = $hive['images'];
			$maxTot = count($pImages);
			$tot = 0;
			//$this->log->write($maxTot.' :'.print_r($pImages,TRUE));
			$man = false;
			$woman = false;
			$child = false;
			$type ='';
			$price = 0;
			$old_price = 0;
			$product_id=0;
			$rHive=[];
			foreach ($TITLES as $KEY=> $value) {
				$rHive[$KEY]='';
			}
			foreach ($hive as $KEY=> $value) {
				switch ($KEY) {
					case 'name': $rHive['Title'] = $rHive['Image alt text'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'image':
						$rHive['Image Src'] = HTTPS_IMAGE.strip_tags($value);
						break;
					case 'meta_title': $rHive['SEO title'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'product_id':
						$url = $this->url->link('product/product', 'product_id=' . $value);
						$_url = explode('/',$url);
						$last = end($_url);
						$_last = explode('.',$last);
						$product_id = $value;
						$rHive['URL handle'] = oc_strtolower($_last[0]);
						break;
					case 'meta_description': $rHive['SEO description'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'meta_keyword': $rHive['Tags'] = str_replace(',',' ',$value);break;
					case 'sku': $rHive['SKU'] = strip_tags($value);break;
					case 'model': $rHive['product.metafields.custom.model']=strip_tags($value);break;
					case 'status': $rHive['Status'] = 'active';break;
					case 'price':
						$rHive['Price'] = $rHive['Price / International'] = $price = $value;break;
					case 'old_price': $old_price = $value;break;
					case 'quantity':
						$rHive['Inventory quantity'] = (int)$value;break;

					case 'description': $rHive['Description'] = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8')) ;break;
				}
			}

			$rHive['Compare-at price']=$old_price?$old_price:'';
			$rHive['Compare-at price / International']=$old_price?$old_price:'';


			$categories = $this->model_catalog_product->getCategories($product_id);
			$categ = '';
			foreach ($categories as $ind => $category) {
				$path = $this->getPath($category['category_id']);
				if ($path) {
					$categ = '';
					foreach (explode('_', $path) as $path_id) {
						$category_info = $this->model_catalog_category->getCategory($path_id);
						if ($category_info) {
							if (!$categ) {
								$categ = $category_info['name'];
							} else {
								$categ .= ' > ' . str_replace('&amp;','&',$category_info['name']);
							}
						}
					}
					if ((strpos($categ , "for Women" ) !== false) || (strpos($categ , "FOR WOMEN" ) !== false) || (strpos($categ , "WOMEN'S " ) !== false)) {
						$woman=TRUE;
					}
					
					if ((strpos($categ , "for Men " ) !== false) || (strpos($categ , "FOR MEN " ) !== false ) || ( strpos($categ , "MEN'S " ) !== false && strpos($categ , "WOMEN'S " ) === false )) {
						$man=TRUE;
					}
				}
			}

			$rHive['product.metafields.custom.grup'] = strip_tags($categ);
			

			if ($woman && $man) {
				$gender = 'Unisex';
			} else if ($woman) {
				$gender = 'Female';
			} else if ($man) {
				$gender = 'Male';
			} else {
				$gender = '';
			}
			$rHive['product.metafields.custom.pgender'] = strip_tags($gender);

			$URLh = oc_strtolower($rHive['URL handle']);

			if (strpos($URLh , "hat-" ) !== false || strpos($URLh , "-hat" ) !== false) {
				$type ='hat';
			}
			if (strpos($URLh, "glove-" ) !== false || strpos($URLh , "-glove" ) !== false || strpos($URLh , "gloves-" ) !== false) {
				$type ='glove';
			}
			if (strpos($URLh , "jacket-" ) !== false || strpos($URLh, "jackets" ) !== false) {
				$type ='jacket';
			}
			if (strpos($URLh , "coat-" ) !== false || strpos($URLh , "-coat" ) !== false || strpos($URLh , "-Coat" ) !== false || strpos($URLh , "Coat" ) !== false) {
				$type ='coat';
			}
			if (strpos($URLh , "vest-" ) !== false || strpos($URLh , "-vest" ) !== false) {
				$type ='vest';
			}
			if (strpos($URLh , "mittens-" ) !== false || strpos($URLh , "-mittens" ) !== false) {
				$type ='mittens';
			}


			if (strpos($URLh, "shearling-" ) !== false ) {
				$material ='shearling';
			}
			if (strpos($URLh , "sheepskin-" ) !== false ) {
				$material ='sheepskin';
			}

			if (isset($hive['material']) && $hive['material']) {
				$material = $hive['material'];
			}

			$size_chart = ($type?$type.' '.($gender=='Male'?'boy':($gender=='Female'?'girl':'')).$type.'size ':'') ;

			$rHive['Tags'] = ($gender?$gender.' ':'').$size_chart. ($material?$material .' ':''). $rHive['Tags'];
			$rHive['Charge tax'] = "TRUE";
			$rHive['Published on online store'] = "TRUE";
			$rHive['Gift card'] = "FALSE";
			$rHive['Image position'] = "1";
			$rHive['Requires shipping']="YES";
			$rHive['Fulfillment service']="manual";
			$rHive['Inventory tracker'] = 'shopify';
			$rHive['Inventory policy']="continue";
			$rHive['Vendor'] = $this->config->get('config_name');
			$rHive['Google Shopping / Custom product']="FALSE";
			$rHive['Google Shopping / Gender']= $gender;
			$rHive['Google Shopping / Age group']="adult";
			$rHive['Product category']=$categ ;//"Apparel & Accessories > Clothing";
			$rHive['Google Shopping / Google product category']="Apparel & Accessories > Clothing > Outerwear";
			$rHive['Type']= $material.' '.$type;
			$color_images=[];

			if (isset($hive['options']) && count($hive['options'])) {
				if (count($hive['options']) > 1) {
					foreach ($hive['options'][0]['product_option_value'] as $order1 => $opt) {
						$rHive['Option1 name']= 'Color';
						$rHive['Option1 value']= $opt['name'];
						$rHive['Variant image'] = $opt['images'] ? HTTPS_IMAGE.$opt['images']:'';
						//$color_images = $opt['all_images'];

						$color_images = array_merge($color_images, $opt['all_images']);

						if (isset($color_images[$tot]['image']) && $tot < $maxTot) {
							//$this->log->write($tot.'->:'.print_r($pImages[$tot]['image'],TRUE));
							$rHive['Image Src'] = HTTPS_IMAGE.strip_tags($color_images[$tot]['image']);
							$rHive['Variant image'] = HTTPS_IMAGE.strip_tags($color_images[$tot]['image']);
							//$rHive['Image position'] = $tot + 1;
						}

						//$this->log->write('color_images:'.print_r($opt['all_images'],TRUE));

						foreach ($hive['options'][1]['product_option_value'] as $order2 => $option) {
							$rHive['Option2 name'] = 'Size';
							$rHive['Option2 value'] = $option['name'];
							//$rHive['Variant image URL'] = $option['images'] ? HTTPS_IMAGE.$option['images']:'';
							if ($hive['options'][1]['type']=='select') {
								if ($option['price_prefix']=="+") {
									$rHive['Price'] = $rHive['Price / International'] = (float)$price + (float)$option['price'];
								} elseif ($option['price_prefix']=="-") {
									$rHive['Price'] = $rHive['Price / International'] = (float) $price - (float) $option['price'];
								} else {
									$rHive['Price'] = $price;
									$rHive['Price / International'] = $price;
								}
								$rHive['Inventory quantity'] = '10';//$option['quantity'];
							}

							if (isset($color_images[$tot]['image']) && $tot < $maxTot) {
								$rHive['Image Src'] = HTTPS_IMAGE.strip_tags($color_images[$tot]['image']);
								$rHive['Image position'] = $tot + 1;
							}
							$tot += 1;

							$hiveRows[]= array_values($rHive);
							//foreach($TITLES as $KEY=> $value){$rHive[$KEY]='';}
						}
					}
				} else {
					foreach ($hive['options'][0]['product_option_value'] as $order => $option) {
						$rHive['Option1 name']='Color';
						$rHive['Option1 value']=$option['name'];
						$rHive['Variant image'] = $option['images'] ? HTTPS_IMAGE.$option['images']:'';

						if ($hive['options'][0]['type']=='select') {
							if ($option['price_prefix']=="+") {
								$rHive['Price'] = $rHive['Price / International'] = (float)$price + (float)$option['price'];
							} elseif ($option['price_prefix']=="-") {
								$rHive['Price'] = $rHive['Price / International'] = (float)$price - (float)$option['price'];
							} else {
								$rHive['Price'] = $rHive['Price / International'] = $price;
							}

							$rHive['Inventory quantity'] = '10';//$option['quantity'];
						}

						$hiveRows[]= array_values($rHive);
						//foreach($TITLES as $KEY=> $value){$rHive[$KEY]='';}
					}
				}
			} else {
				$hiveRows[]= array_values($rHive);
			}
		}
		return $hiveRows;
	}

	protected function hiveN(array $TITLES=[], array $hive=[]):array
	{
		$hiveRows=[];

		if ($hive) {
			//$pImages = $hive['images'];
			if (isset($hive['options']) && count($hive['options'])) {
			}
			$row=0;
			$col=0;

			$pImages = $hive['images'];
			$maxTot = count($pImages);
			$tot = 0;
			//$this->log->write($maxTot.' :'.print_r($pImages,TRUE));
			$man = false;
			$woman = false;
			$child = false;
			$type = '';
			$tURL = '';
			$price = 0;
			$old_price = 0;
			$product_id=0;
			$empty=[];
			foreach ($TITLES as $KEY=> $value) {
				$empty[$KEY]='';
			}
			$matris=[];
			$matris[] = $empty;
			foreach ($hive as $KEY=> $value) {
				switch ($KEY) {
					case 'name': $matris[0]['Title'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'image':
						$rHive['Image Src'] = HTTPS_IMAGE.strip_tags($value);
						break;
					case 'meta_title': $matris[0]['SEO title'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'product_id':
						$url = $this->url->link('product/product', 'product_id=' . $value);
						$_url = explode('/',$url);
						$last = end($_url);
						$_last = explode('.',$last);
						$product_id = $value;
						$tURL = $matris[0]['URL handle'] = oc_strtolower($_last[0]);

						break;
					case 'meta_description': $matris[0]['SEO description'] = strip_tags(str_replace('&amp;','&',$value));break;
					case 'meta_keyword': $matris[0]['Tags'] = str_replace(',',' ',$value);break;
					case 'sku': $matris[0]['SKU'] = strip_tags($value);break;
					case 'model': $matris[0]['product.metafields.custom.model']=strip_tags($value);break;
					
					case 'clearance': $matris[0]['product.metafields.custom.clearance']=(int)$value?'true':'false';break;
					case 'new_arriwals': $matris[0]['product.metafields.custom.newarrivals']=(int)$value?'true':'false';break;
					case 'best': $matris[0]['product.metafields.custom.bestseller']=(int)$value?'true':'false';break;
					case 'sale': $matris[0]['product.metafields.custom.sale']=(int)$value?'true':'false';break;
				
					case 'status': $matris[0]['Status'] = 'active';break;
					case 'material': $matris[0]['material'] = 'active';break;
					case 'price':
						$price = $value;break;
					case 'old_price': $old_price = $value;break;
					case 'quantity':
						$rHive['Inventory quantity'] = (int)$value;break;

					case 'description': $matris[0]['Description'] = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8')) ;break;
					case 'bullet':
						$BLT = '<ul>';
						$V = explode('æ',$value);
						foreach ($V as $bullet ) {
							if ($bullet) {
								$BLT .= '<li>'.$bullet.'</li>';
							}
						}
						$BLT .= '</ul>';
						$matris[0]['Description'] = '<p>'.$matris[0]['Description'].'</p>'.(html_entity_decode($BLT, ENT_QUOTES, 'UTF-8'));break;
				}
			}


			$categories = $this->model_catalog_product->getCategories($product_id);
			$categ = '';
			foreach ($categories as $ind => $category) {
				$path = $this->getPath($category['category_id']);
				if ($path) {
					$categ = '';
					foreach (explode('_', $path) as $path_id) {
						$category_info = $this->model_catalog_category->getCategory($path_id);
						if ($category_info) {
							if (!$categ) {
								$categ = $category_info['name'];
							} else {
								$categ .= ' > ' . str_replace('&amp;','&',$category_info['name']);
							}
						}
					}
					if ((strpos($categ , "for Women" ) !== false) || (strpos($categ , "FOR WOMEN" ) !== false) || (strpos($categ , "WOMEN'S " ) !== false)) {
						$woman=TRUE;
					}
					if ((strpos($categ , "for Men " ) !== false) || (strpos($categ , "FOR MEN " ) !== false ) || ( strpos($categ , "MEN'S " ) !== false && strpos($categ , "WOMEN'S " ) === false )) {
						$man=TRUE;
					}
				}
			}

			$matris[$row]['product.metafields.custom.grup'] = strip_tags($categ);
			

			if ($woman && $man) {
				$gender = 'Unisex';
			} else if ($woman) {
				$gender = 'Female';
			} else if ($man) {
				$gender = 'Male';
			} else {
				$gender = '';
			}

			if (strpos($matris[0]['URL handle'] , "product_id" ) !== false) {
				$tURL = oc_strtolower(by_SEO($matris[0]['Title'])."-".by_SEO($matris[0]['product.metafields.custom.model']));
			} 
			
			$URLh = oc_strtolower($tURL);
			
			if (strpos($categ , "Bag" ) !== false || strpos($categ , "Backpack" ) !== false|| strpos($categ , "Luggage" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Handbags, wallets & cases > Backpacks in Luggage & Bags";
				$matris[$row]['Product category']="Apparel & Accessories > Handbags, wallets & cases > Backpacks in Luggage & Bags";
			} else if (strpos($URLh , "-wallet" ) !== false || strpos($URLh , "-case" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Handbags, wallets & cases > Wallets & Money Clips";
				$matris[$row]['Product category']="Apparel & Accessories > Handbags, wallets & cases > Wallets & Money Clips";
			} else if (strpos($URLh , "-pen" ) !== false || strpos($URLh , "-box" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Office Supplies > Filing & Organization > Pen & Pencil Cases";
				$matris[$row]['Product category']="Office Supplies > Filing & Organization > Pen & Pencil Cases";
			} else if (strpos($URLh , "-valet" ) !== false || strpos($URLh , "-case" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Home & Garden > Household Storage Containers > Household Storage Containers";
				$matris[$row]['Product category']="Home & Garden > Household Storage Containers > Household Storage Containers";
			} else if (strpos($URLh , "-band" ) !== false || strpos($URLh , "-watch" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Jewelry > Watch Accessories > Watch Bands";
				$matris[$row]['Product category']="Apparel & Accessories > Apparel & Accessories > Jewelry > Watch Accessories > Watch Bands";
			}else{
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Clothing > Outerwear";
			}
			
			if (strpos($URLh , "hat-" ) !== false || strpos($URLh , "-hat" ) !== false) {
				$matris[$row]['Product category']="Apparel & Accessories > Hats & Beanies";
				$type ='hat';
			}
			
			if (strpos($URLh, "glove-" ) !== false || strpos($URLh , "-glove" ) !== false || strpos($URLh, "mitten-" ) !== false || strpos($URLh , "-mitten" ) !== false) {
				$matris[$row]['Product category']="Apparel & Accessories > Clothing Accessories > Gloves & Mittens";
				$type ='glove';
			}
			
			if (strpos($URLh , "leashe-" ) !== false || strpos($URLh, "leash" ) !== false || strpos($URLh, "-leash" ) !== false ) {
				$matris[$row]['Product category']="Animals & Pet Supplies > Pet Supplies > Pet Leashes > Standart Leashes";
				$type ='leashe';
			}
			if (strpos($URLh , "jacket-" ) !== false || strpos($URLh, "jackets" ) !== false || strpos($URLh, "-jacket" ) !== false || strpos($URLh, "-shirt" ) !== false) {
				$matris[$row]['Product category']="Apparel & Accessories > Clothing";
				$type ='jacket';
			}
			if (strpos($URLh , "coat-" ) !== false || strpos($URLh , "-coat" ) !== false || strpos($URLh , "-Coat" ) !== false || strpos($URLh , "Coat" ) !== false) {
				$matris[$row]['Product category']="Apparel & Accessories > Clothing";
				$type ='coat';
			}
			if (strpos($URLh , "vest-" ) !== false || strpos($URLh , "-vest" ) !== false) {
				$matris[$row]['Product category']="Apparel & Accessories > Clothing";
				$type ='vest';
			}
			if (strpos($URLh , "mitten-" ) !== false || strpos($URLh , "-mitten" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Clothing Accessories > Gloves & Mittens";
				$matris[$row]['Product category']="Apparel & Accessories > Mittens";
				$type ='mittens';
			}
			
			if (strpos($URLh , "valet-" ) !== false || strpos($URLh , "-valet" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']="Apparel & Accessories > Wallets";
				$matris[$row]['Product category']="Apparel & Accessories > Wallets";
				$type ='valet';
			}

			if (strpos($URLh , "briefcase-" ) !== false || strpos($URLh , "-briefcase" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']=$matris[$row]['Product category']="Apparel & Accessories > Briefcases";
				$type ='briefcase';
			}

			if (strpos($URLh , "cardholder-" ) !== false || strpos($URLh , "-cardholder" ) !== false) {
				
				$matris[$row]['Google Shopping / Google product category']= $matris[$row]['Product category']="Apparel & Accessories > card holders";
				$type ='cardholder';
			}

			if (strpos($URLh , "bag-" ) !== false || strpos($URLh , "-bag" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']=$matris[$row]['Product category']="Apparel & Accessories > Travel Bags / Luggage";
				$type ='bag';
			}

			if (strpos($URLh , "shoulderbag-" ) !== false || strpos($URLh , "-shoulderbag" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']= $matris[$row]['Product category']="Apparel & Accessories > Shoulderbag";
				$type ='shoulderbag';
			}

			if (strpos($URLh , "backpack-" ) !== false || strpos($URLh , "-backpack" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']= $matris[$row]['Product category']="Apparel & Accessories > backpacks";
				$type ='backpack';
			}

			if (strpos($URLh , "case-" ) !== false || strpos($URLh , "-case" ) !== false) {
				$matris[$row]['Google Shopping / Google product category']=$matris[$row]['Product category']="Apparel & Accessories > cases";
				$type ='case';
			}

			if (strpos($URLh , "pen-case-" ) !== false || strpos($URLh , "-pen-case" ) !== false) {
				$type ='pen-case';
				$matris[$row]['Google Shopping / Google product category']=$matris[$row]['Product category']="Apparel & Accessories > pen cases";
			}

			if (strpos($URLh , "pouch-" ) !== false || strpos($URLh , "-pouch" ) !== false) {
				$type ='pouch';
				$matris[$row]['Google Shopping / Google product category']=$matris[$row]['Product category']="Apparel & Accessories > Pouchs";
			}

			$material ='leather';

			if (strpos($URLh, "shearling-" ) !== false ) {
				$material ='shearling';
			}
			if (strpos($URLh , "sheepskin-" ) !== false ) {
				$material ='sheepskin';
			}
			if (strpos($URLh , "cowhide-" ) !== false || strpos($URLh , "-cowhide" ) !== false ) {
				$material ='cowhide';
			}

			if (isset($hive['material']) &&$hive['material']) {
				$material = $hive['material'];
			}

			$size_chart = ($type?$type.' '.($gender=='Male'?'boy':($gender=='Female'?'girl':'')).$type.'size ':'') ;

			$matris[$row]['Tags'] = ($gender?$gender.' ':' ').$size_chart. ($material?$material .' ':' '). $matris[$row]['Tags'];
			$matris[$row]['Charge tax'] = "TRUE";
			$matris[$row]['Published on online store'] = "TRUE";
			$matris[$row]['Gift card'] = "FALSE";
			$matris[$row]['Requires shipping']="YES";
			$matris[$row]['Vendor'] = $this->config->get('config_name');
			$matris[$row]['Google Shopping / Custom product']="FALSE";
			$matris[$row]['Google Shopping / Gender']= $gender;
			$matris[$row]['Google Shopping / Age group']="adult";
			
			$matris[$row]['Type']= $material.' '.$type;
			$color_images=[];

			$matris[$row]['Option1 name']= 'Color';
			if (isset($hive['options'][1]['product_option_value'])){
				$matris[$row]['Option2 name'] = 'Size';
			}else{
				$matris[$row]['Option2 name'] = '';
			}
			
			foreach ($hive['options'][0]['product_option_value'] as $order1 => $opt) {
				if ($opt['type']!=2) {
					if (isset($hive['options'][1]['product_option_value'])) 
					{
						foreach ($hive['options'][1]['product_option_value'] as $order2 => $option) {
							if ($hive['options'][1]['type']=='select') {
								if ($option['price_prefix']=="+") {
									$matris[$row]['Price'] = $matris[$row]['Price / International'] = (float)$price + (float)$option['price'];
									$matris[$row]['Compare-at price']=$old_price?$old_price+ (float)$option['price']:'';
									$matris[$row]['Compare-at price / International']=$old_price?$old_price+ (float)$option['price']:'';
								} elseif ($option['price_prefix']=="-") {
									$matris[$row]['Price'] = $matris[$row]['Price / International'] = (float) $price - (float) $option['price'];
									$matris[$row]['Compare-at price']=$old_price?$old_price - (float)$option['price']:'';
									$matris[$row]['Compare-at price / International']=$old_price?$old_price - (float)$option['price']:'';
								} else {
									$matris[$row]['Price'] = $price;
									$matris[$row]['Price / International'] = $price;
									$matris[$row]['Compare-at price']=$old_price?$old_price:'';
									$matris[$row]['Compare-at price / International']=$old_price?$old_price:'';
								}
								$matris[$row]['Variant image'] = HTTPS_IMAGE.strip_tags($opt['variant_image']);
								//$matris[$row]['URL handle'] = $matris[0]['URL handle'];
								$matris[$row]['Variant Grams']="0";
								$matris[$row]['Fulfillment service']="manual";
								$matris[$row]['Inventory tracker'] = 'shopify';
								$matris[$row]['Inventory policy']="continue";
								$matris[$row]['Inventory quantity'] = '10';//$option['quantity'];
							}
							$matris[$row]['URL handle'] = $tURL;//.'-'.by_SEO($opt['name']).'-'.by_SEO($option['name']);
							$matris[$row]['Option1 value'] = $opt['name'];
							$matris[$row]['Option2 value'] = $option['name'];
							$row++;
							$matris[$row] = $empty;
						}
					} 
					else 
					{
						if ($opt['price_prefix']=="+") {
							$matris[$row]['Price'] = $matris[$row]['Price / International'] = (float)$price + (float)$opt['price'];
							$matris[$row]['Compare-at price']=$old_price?$old_price + (float)$opt['price']:'';
							$matris[$row]['Compare-at price / International']=$old_price?$old_price + (float)$opt['price']:'';
						} elseif ($opt['price_prefix']=="-") {
							$matris[$row]['Price'] = $matris[$row]['Price / International'] = (float) $price - (float) $opt['price'];
							$matris[$row]['Compare-at price']=$old_price?$old_price - (float)$opt['price']:'';
							$matris[$row]['Compare-at price / International']=$old_price?$old_price - (float)$opt['price']:'';
						} else {
							$matris[$row]['Price'] = $price;
							$matris[$row]['Price / International'] = $price;
							$matris[$row]['Compare-at price']=$old_price?$old_price :'';
							$matris[$row]['Compare-at price / International']=$old_price?$old_price :'';
						}
						//$matris[$row]['URL handle'] = $matris[0]['URL handle'];
						$matris[$row]['URL handle'] = $tURL;//.'-'.by_SEO($opt['name']);
						$matris[$row]['Variant image'] = HTTPS_IMAGE.strip_tags($opt['variant_image']);
						$matris[$row]['Variant Grams']="0";
						$matris[$row]['Fulfillment service']="manual";
						$matris[$row]['Inventory tracker'] = 'shopify';
						$matris[$row]['Inventory policy']="continue";
						$matris[$row]['Inventory quantity'] = '10';//$option['quantity'];
						$matris[$row]['Option1 value'] = $opt['name'];
						$row++;
						$matris[$row] = $empty;
					}
				}
			}
			$tot=0;
			foreach ($hive['options'][0]['product_option_value'] as $order1 => $opt) {
				foreach ($opt['all_images'] as $image) {
					if (!isset($matris[$tot])){ 
						$matris[] = $empty;
					}
					$matris[$tot]['Image Src'] = HTTPS_IMAGE.strip_tags($image['image']);
					$matris[$tot]['Image position'] = $tot + 1;
					$matris[$tot]['URL handle'] = $tURL;
					$tot++;
				}
			}
			$tot=0;
			foreach ($hive['options'][0]['product_option_value'] as $order1 => $opt) {
				if ($opt['type']!=2) {
					if (isset($hive['options'][1]['product_option_value'])) 
					{
						foreach ($hive['options'][1]['product_option_value'] as $order2 => $option) {
							if (!isset($matris[$tot])) {
								$matris[] = $empty;
							}
							$matris[$tot]['Variant image'] = HTTPS_IMAGE.strip_tags($opt['variant_image']);
							$tot++;
						}
					}
					else
					{
						if (!isset($matris[$tot])) {
							$matris[] = $empty;
						}
						$matris[$tot]['Variant image'] = HTTPS_IMAGE.strip_tags($opt['variant_image']);
						$tot++;
					}
				}
			}
			
			
			
		
		}
		return $matris;
	}
}
