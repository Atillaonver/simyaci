<?php
namespace Opencart\Catalog\Model\Extension\Bytao\Total;
class Campaign extends \Opencart\System\Engine\Model {
	public function getTotal(array &$totals, array &$taxes, float &$total): void {
		$isloged = $this->customer->isLogged();
		$groupId = $isloged ? $this->customer->getGroupId():0;
		
		if((!$isloged) ||($isloged &&($groupId!=2)) ){
			$this->load->language('extension/bytao/total/campaign', 'campaign');
			$cartProducts = $this->cart->getProducts();
			
			$camp = false;
			$campaign_info = $this->getCampaigns();
			if ($campaign_info) {
				$sub_total = $this->cart->getSubTotal();
				
				foreach( $campaign_info as $campaign){
					$discount_total = 0;
					
					switch($campaign['campaign_type']){
						case "d": // discount 
							{
								if ($campaign['type'] == "P" )/* P % percentage */
								{
									//$this->log->write('products:'.print_r( $campaign['products'],true));
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
												'extension'  => 'bytao',
												'code'       => 'campaign',
												'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
												'value'      => -$discount_total,
												'sort_order' => (int)$this->config->get('total_campaign_sort_order')
											];
											$total -= $discount_total;
										}
									}
									else
									{
										//$this->log->write('Campaign:'.print_r($campaign_info,TRUE));
										foreach ($this->cart->getProducts() as $product) {
											//if (isset($product['product_id'])){
												
													$discount = $product['total'] / 100 * $campaign['discount'];
													$discount_total += $discount;
													
												
											//}
										}
										
										$totals[] = [
											'extension'  => 'bytao',
											'code'       => 'campaign',
											'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
											'value'      => -$discount_total,
											'sort_order' => (int)$this->config->get('total_campaign_sort_order')
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
						
						case "s":// Second one discount
							{
								if ($campaign['type'] == "P" )
								{
									/* P % percentage */
									
									if($campaign['products_free']){
										foreach ($campaign['products_free'] as $productf) {
											
											foreach ($cartProducts as $product) {
												if ( $product['product_id'] == $productf['product_id']) {
													//$this->log->write('Total:'.print_r($productf,TRUE));
													
													foreach ($cartProducts as $_product) {
														foreach ($campaign['products'] as $cproduct) {
															if ( $_product['product_id'] == $cproduct['product_id']) {
																
																$discount = $product['total'] / 100 * $campaign['discount'];
																$discount_total += $discount;
																$totals[] = [
																	'extension'  => 'bytao',
																	'code'       => 'campaign',
																	'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
																	'value'      => -$discount_total,
																	'sort_order' => (int)$this->config->get('total_campaign_sort_order')
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
																	'extension'  => 'bytao',
																	'code'       => 'campaign',
																	'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
																	'value'      => -$discount_total,
																	'sort_order' => (int)$this->config->get('total_campaign_sort_order')
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
										'extension'  => 'bytao',
										'code'       => 'campaign',
										'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
										'value'      => -$discount_total,
										'sort_order' => (int)$this->config->get('total_campaign_sort_order')
									];
									$total -= $discount_total;
								}
											
							}
							break;
							
						case "c":// Second one free
							{
								$discount=0;
								$qty=0;
								$dat=array();
								$cproducts=$this->cart->getProducts();
								
								foreach ($campaign['products'] as $productn) {
										foreach ($cproducts as $product) {
											if ( $product['product_id'] == $productn['product_id']) {
												$dat[] = array(
													'prc' => $product['price'],
													'pId' => $product['product_id'],
												 	'qty' => $product['quantity']
												 	
												);
												$qty += $product['quantity'];
											}
										}
										
								}	
								
								
								if($qty>1)
								{
									array_multisort($dat, SORT_ASC );
									$q=0;
									$qt=0;
									//$this->log->write('Total:'.print_r($dat,TRUE));
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
										'extension'  => 'bytao',
										'code'       => 'campaign',
										'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
										'value'      => -$discount_total,
										'sort_order' => (int)$this->config->get('total_campaign_sort_order')
									];

									$total -= $discount_total;
								}
							
							}
							break;
						
						case "o":// Other one free
							{
								$discount=0;
								$qty=0;
								$dat=array();
								$cproducts = $this->cart->getProducts();
								
								foreach ($campaign['products'] as $productn) {
										foreach ($cproducts as $product) {
											if ( $product['product_id'] == $productn['product_id']) {
												$dat[] = array(
													'prc' => $product['price'],
													'pId' => $product['product_id'],
												 	'qty' => $product['quantity']
												 	
												);
												$qty += $product['quantity'];
											}
										}
										
								}	
								
								
								if($qty>1)
								{
									array_multisort($dat, SORT_ASC );
									$q=0;
									$qt=0;
									//$this->log->write('Total:'.print_r($dat,TRUE));
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
										'extension'  => 'bytao',
										'code'       => 'campaign',
										'title'      => sprintf($this->language->get('campaign_text_campaign'), $campaign['name']),
										'value'      => -$discount_total,
										'sort_order' => (int)$this->config->get('total_campaign_sort_order')
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
	
	public function getCampaigns(): array{
		$campaigns = [];
		
		$qSQL = "SELECT * FROM `" . DB_PREFIX . "campaign` c LEFT JOIN `" . DB_PREFIX . "campaign_to_store` cts ON(c.campaign_id = cts.campaign_id ) WHERE status = 1 AND ( NOW() BETWEEN date_start AND date_end ) AND cts.store_id='".(int)$this->config->get('config_store_id')."'";
		
		$query = $this->db->query($qSQL);
		
		foreach($query->rows as $row){
			$prodBuy = [];
			$prodGet = [];
		
			$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "campaign_product` WHERE campaign_id = '".$row['campaign_id']."' AND buyed='1'");
			if($_products){
				foreach($_products->rows as $product){
					$prodBuy[] = ['product_id' => $product['productId']];
				}
			}
			
			$_products = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "campaign_category` cc ON(pc.category_id = cc.category_id) WHERE cc.campaign_id = '".$row['campaign_id']."' AND buyed='1' GROUP BY pc.product_id ");
			if($_products){
				foreach( $_products->rows as $product){
					$prodBuy[] = ['product_id' => $product['productId']];
				}
			}
			
			$_products_free = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "campaign_product` WHERE campaign_id = '".$row['campaign_id']."' AND buyed='0'");
			if($_products_free){
				foreach($_products_free->rows as $ppproduct){
					$prodGet[] = ['product_id' => $ppproduct['productId']];
				}
			}
			
			$_products_free = $this->db->query("SELECT product_id AS productId FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "campaign_category` cc ON(pc.category_id = cc.category_id) WHERE cc.campaign_id = '".$row['campaign_id']."' AND cc.buyed='0' GROUP BY pc.product_id ");
			if($_products_free){
				foreach( $_products_free->rows as $pproduct){
					$prodGet[] = ['product_id' => $pproduct['productId']];
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
				'products' => $prodBuy,
				'products_free' => $prodGet,
			];
			 
		}
		
		//$this->log->write('campaigns:'.print_r($campaigns,TRUE));
		return $campaigns;
	}
	
}
