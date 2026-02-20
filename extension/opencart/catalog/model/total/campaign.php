<?php
namespace Opencart\Catalog\Model\Extension\Opencart\Total;
class Campaign extends \Opencart\System\Engine\Model {
	public function getTotal(array &$totals, array &$taxes, float &$total): void {
		
		if (isset($this->session->data['coupon'])) {
			$this->load->language('extension/opencart/total/coupon', 'coupon');

			$this->load->model('marketing/coupon');
			
			$status = true;
			$coupon_product_data = [];
			$email        = '';

			$coupon_info = $this->model_marketing_coupon->getCouponByCode($this->session->data['coupon']);

			if ($coupon_info) {
				$discount_total = 0;
				
				if($coupon_info['uses_customer'] == '1'){
					//if(isset($this->session->data['account']) && ($this->session->data['account'] == "guest")){
					if(isset($this->session->data['customer']['email'] ) )
					{
						$email = $this->session->data['customer']['email'];
					}
					else
					{
						$email = $this->customer->getEmail();
					}

					$email_query = $this->db->query("SELECT cp.* FROM `" . DB_PREFIX . "coupon_personal` cp LEFT JOIN `" . DB_PREFIX . "coupon` c ON (c.code = cp.code)  LEFT JOIN `" . DB_PREFIX . "coupon_to_store` c2s ON (c.coupon_id=c2s.coupon_id) WHERE cp.code = '" . $coupon_info['code'] . "' AND email = '" . $email . "' AND (cp.date_start < NOW()) AND (cp.date_end > NOW()) AND cp.status = '0'");

					if(!isset($email_query->num_rows))
					{
						$status = false;
					}
					
				}
			
				$products = $this->cart->getProducts();

				if (!$coupon_info['product']) {
					$sub_total = $this->cart->getSubTotal();
				} 
				else 
				{
					$sub_total = 0;

					foreach ($products as $product) {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$sub_total += $product['total'];
						}
					}
				}

				if ($coupon_info['type'] == 'F') {
					$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
				}

				foreach ($products as $product) {
					$discount = 0;

					if (!$coupon_info['product']) {
						$status = true;
					} else {
						$status = in_array($product['product_id'], $coupon_info['product']);
					}

					if ($status) {
						if ($coupon_info['type'] == 'F') {
							$discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
						} elseif ($coupon_info['type'] == 'P') {
							$discount = $product['total'] / 100 * $coupon_info['discount'];
						}

						if ($product['tax_class_id']) {
							$tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

							foreach ($tax_rates as $tax_rate) {
								if ($tax_rate['type'] == 'P') {
									$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
								}
							}
						}
					}

					$discount_total += $discount;
				}

				if ($coupon_info['shipping'] && isset($this->session->data['shipping_method']['cost']) && isset($this->session->data['shipping_method']['tax_class_id'])) {
					if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
						$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

						foreach ($tax_rates as $tax_rate) {
							if ($tax_rate['type'] == 'P') {
								$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
							}
						}
					}

					$discount_total += $this->session->data['shipping_method']['cost'];
				}

				// If discount greater than total
				if ($discount_total > $total) {
					$discount_total = $total;
				}
				
				if ($discount_total < 1) {
					$status = false;
				}
				

				if ( $status ) {
					
					$totals[] = [
						'extension'  => 'opencart',
						'code'       => 'coupon',
						'title'      => sprintf($this->language->get('coupon_text_coupon'), $this->session->data['coupon']),
						'value'      => -$discount_total,
						'sort_order' => (int)$this->config->get('total_coupon_sort_order')
					];

					$total -= $discount_total;
				}
			}
		}
	
		$isloged = $this->customer->isLogged();
		$groupId = $isloged?$this->customer->getGroupId():0;
		
		if((!$isloged) ||($isloged &&($groupId!=2)) ){
			
			$this->load->language('total/campaign');
			$cartProducts = $this->cart->getProducts();
			
			$camp = false;
			$campaign_info = $this->getCampaigns();
			if ($campaign_info) {
				
				
				$sub_total = $this->cart->getSubTotal();
				
				foreach( $campaign_info as $campaign){
					$discount_total = 0;
					
					switch($campaign['campaign_type']){
						case "d": // indirim
							{
								if ($campaign['type'] == "P" )/* P % percentage */
								{
									if($campaign['products']){
										foreach ($campaign['products'] as $productn) {
											foreach ($this->cart->getProducts() as $product) {
												if (isset($product['product_id'])){
													if ( $product['product_id'] == $productn['product_id']) {
														$discount = $product['total'] / 100 * $campaign['discount'];
														$discount_total += $discount;
														$camp=true;
													}
												}
											}
										}
										
										if($camp)
										{
											
											$totals[] = [
												'extension'  => 'opencart',
												'code' => 'campaign',
												'title'=> sprintf($this->language->get('text_campaign'), $campaign['name']),
												'value'=> -$discount_total,
												'sort_order' => $this->config->get('campaign_sort_order')
												];
											$total -= $discount_total;
										}
									}
									else
									{
										foreach ($this->cart->getProducts() as $product) {
											//if (isset($product['product_id'])){
												
													$discount = $product['total'] / 100 * $campaign['discount'];
													$discount_total += $discount;
													
												
											//}
										}
										
										$total_data[] = [
															'code'       => 'campaign',
															'title'      => sprintf($this->language->get('text_campaign'), $campaign['name']),
															'value'      => -$discount_total,
															'sort_order' => $this->config->get('campaign_sort_order')
														];

													$total -= $discount_total;
									}
									
									
								} 
								else if ($campaign['type'] == "F" )
								{
									/* F fixed */
									foreach ($campaign['products'] as $productn) {
										foreach ($this->cart->getProducts() as $product) {
											if (isset($productn['product_id'])){
												if ( $product['product_id'] == $productn['product_id']) {
													$discount = $campaign['discount'];
													$discount_total += $discount;
													$camp=true;
												}
											}
										}
									}
								}
							}	
							break;
						
						case "s":// discount second
							{
								if ($campaign['type'] == "P" )
								{
									/* P % percentage */
									
									if($campaign['products_free']){
										foreach ($campaign['products_free'] as $productf) {
											
											foreach ($cartProducts as $product) {
												if ( $product['product_id'] == $productf['product_id']) {
													foreach ($cartProducts as $_product) {
														foreach ($campaign['products'] as $cproduct) {
															if ( $_product['product_id'] == $cproduct['product_id']) {
																
																$discount = $product['total'] / 100 * $campaign['discount'];
																$discount_total += $discount;
																$totals[] = [
																	'extension'  => 'opencart',
																	'code'       => 'campaign',
																	'title'      => sprintf($this->language->get('text_campaign'), $campaign['name']),
																	'value'      => -$discount_total,
																	'sort_order' => $this->config->get('campaign_sort_order')
																];

																$total -= $discount_total;
															}
														}
													}
												}
											}
										}
									}
		
									foreach ($campaign['products'] as $productn) {
										foreach ($this->cart->getProducts() as $product) {
											if ( $product['product_id'] == $productn['product_id']) {
												foreach ($campaign['products_free'] as $productf) {
													if ( $product['product_id'] == $productf['product_id']) {
															$discount = $product['total'] / 100 * $campaign['discount'];
															$discount_total += $discount;
															$camp=true;
														}
												}
												
											}
										}
									}
									
								} 
								else if ($campaign['type'] == "F" )
								{
									/* F fixed */
									
									if($campaign['products_free']){
										foreach ($campaign['products_free'] as $productf) {
											
											foreach ($cartProducts as $product) {
												if ( $product['product_id'] == $productf['product_id']) {
													foreach ($cartProducts as $_product) {
														foreach ($campaign['products'] as $cproduct) {
															if ( $_product['product_id'] == $cproduct['product_id']) {
																
																$discount =  $product['total'] - $campaign['discount'];;
																$discount_total += $discount;
																$totals[] = [
																	'extension'  => 'opencart',
																	'code'       => 'campaign',
																	'title'      => sprintf($this->language->get('text_campaign'), $campaign['name']),
																	'value'      => -$discount_total,
																	'sort_order' => $this->config->get('campaign_sort_order')
																	];

																$total -= $discount_total;
															}
														}
													}
												}
											}
										}
									}
		
								}
							}	
							break;
						
						case "F":// Gift product
							{
								$discount=0;
								if($campaign['products_free'])
								{
									foreach ($campaign['products_free'] as $productf) {
										
										foreach ($cartProducts as $product) {
											if ( $product['product_id'] == $productf['product_id']) {
												foreach ($cartProducts as $_product) {
													foreach ($campaign['products'] as $cproduct) {
														if ( $_product['product_id'] == $cproduct['product_id']) {
															if($discount>$product['price']){
																$discount = $product['price'];
															}elseif($discount==0){
																$discount = $product['price'];
															}
															
														}
													}
												}
											}
										}
										
									}
								}
								if($discount!=0){
									$discount_total += $discount;
									$totals[] = [
										'extension'  => 'opencart',
										'code'       => 'campaign',
										'title'      => sprintf($this->language->get('text_campaign'), $campaign['name']),
										'value'      => -$discount_total,
										'sort_order' => $this->config->get('campaign_sort_order')
									];
									$total -= $discount_total;
								}
											
							}
							break;
							
						case "c":
							{
								$discount=0;
								$qty=0;
								$dat=[];
								$cproducts=$this->cart->getProducts();
								
								foreach ($campaign['products'] as $productn) {
										foreach ($cproducts as $product) {
											if ( $product['product_id'] == $productn['product_id']) {
												$dat[] = [
													'prc' => $product['price'],
													'pId' => $product['product_id'],
												 	'qty' => $product['quantity']
												];
												$qty += $product['quantity'];
											}
										}
										
								}	
								
								
								if($qty>1)
								{
									array_multisort($dat, SORT_ASC );
									$q=0;
									$qt=0;
									for ($i = 1; $i <= ($qty/2); $i++) {
										
										if($dat[$q]['qty']>$qt){
											$qt++;
										}else{
											$q++;
											$qt=1;
										}
										
										$discount = $discount + ($dat[$q]['prc'] / 100) * $campaign['discount'];
									
										
									}
									
								}
								
								if($discount!=0)
								{
									$discount_total += $discount;
									$totals[] = [
										'extension'  => 'opencart',
										'code'       => 'campaign',
										'title'      => sprintf($this->language->get('text_campaign'), $campaign['name']),
										'value'      => -$discount_total,
										'sort_order' => $this->config->get('campaign_sort_order')
										];
									$total -= $discount_total;
								}
							
							}
							break;
							
					}
					
				}
				
			}
		}
		
	
	
	}

	public function confirm(array $order_info, array $order_total): int {
		$code = '';

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$code = substr($order_total['title'], $start, $end - $start);
		}

		$this->load->model('checkout/campaign');

		$campaign_info = $this->model_checkout_campaign->getCampaign();

		if ($campaign_info) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "campaign_history` SET campaign_id = '" . (int)$campaign_info['campaign_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', customer_id = '" . (int)$order_info['customer_id'] . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");
		}
		
		return 0;
	}

	public function unconfirm(int $order_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "campaign_history` WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getCampaigns(){
		$campaigns = [];
		
		$qSQL="SELECT * FROM `" . DB_PREFIX . "campaign` c LEFT JOIN `" . DB_PREFIX . "campaign_to_store` cts ON(c.campaign_id = cts.campaign_id ) WHERE status = 1 AND ( NOW() BETWEEN date_start AND date_end ) AND cts.store_id='".(int)$this->config->get('config_store_id')."'";
		
		$query = $this->db->query($qSQL);
		foreach($query->rows as $row){
			$products =[];
			$productsfree =[];
		
			$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "campaign_product` WHERE campaign_id = '".$row['campaign_id']."' AND free='0'");
			if($_products){
				foreach($_products->rows as $product){
					$products[] = ['product_id' => $product['productId']];
				}
			}
			
			$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "campaign_category` cc ON(pc.category_id = cc.category_id) WHERE cc.campaign_id = '".$row['campaign_id']."' AND free='0' GROUP BY pc.product_id ");
			if($_products){
				foreach( $_products->rows as $product){
					$products[] = ['product_id' => $product['productId']];
				}
			}
			
			$_products_free = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "campaign_product` WHERE campaign_id = '".$row['campaign_id']."' AND free='1'");
			if($_products_free){
				foreach($_products_free->rows as $ppproduct){
					$productsfree[] = ['product_id' => $ppproduct['productId']];
				}
			}
			
			$_products_free = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "campaign_category` cc ON(pc.category_id = cc.category_id) WHERE cc.campaign_id = '".$row['campaign_id']."' AND cc.free='1' GROUP BY pc.product_id ");
			if($_products_free){
				foreach( $_products_free->rows as $pproduct){
					$productsfree[] = ['product_id' => $pproduct['productId']];
				}
			}
			
			
			$campaigns[] = [
				'campaign_id' => $row['campaign_id'],
				'name' => $row['name'],
				'shipping' => $row['shipping'],
				'type' => $row['type'],
				'campaign_type' => $row['campaign_type'],
				'discount' => $row['discount'],
				'total' => $row['total'],
				'uses_total' => $row['uses_total'],
				'uses_customer' => $row['uses_customer'],
				'products' => $products,
				'products_free' => $productsfree,
			];
			 
		}
		

		return $campaigns;
	}
	

}
