<?php
namespace Opencart\Catalog\Controller\Bytao;
class Cart extends \Opencart\System\Engine\Controller {
	public function index(string $isx = ''): string {
		
		$this->load->language('common/cart');

		$totals = [];
		$taxes = $this->cart->getTaxes();
		$total = 0;

		$this->load->model('checkout/cart');
		
		$logged = $this->customer->isLogged();
		$data['groupId'] = $groupId = $logged ? $this->customer->getGroupId():0;
		
		if ( $logged || !$this->config->get('config_customer_price')) {
			($this->model_checkout_cart->getTotals)($totals, $taxes, $total);
		}

		$data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		// Products
		$data['products'] = [];

		$products = $this->model_checkout_cart->getProducts();

		foreach ($products as $product) {
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = (float)$this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$description = '';

			if ($product['subscription']) {
				$trial_price = $this->currency->format($this->tax->calculate($product['subscription']['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$trial_cycle = $product['subscription']['trial_cycle'];
				$trial_frequency = $this->language->get('text_' . $product['subscription']['trial_frequency']);
				$trial_duration = $product['subscription']['trial_duration'];

				if ($product['subscription']['trial_status']) {
					$description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
				}

				$price = $this->currency->format($this->tax->calculate($product['subscription']['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$cycle = $product['subscription']['cycle'];
				$frequency = $this->language->get('text_' . $product['subscription']['frequency']);
				$duration = $product['subscription']['duration'];

				if ($duration) {
					$description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
				} else {
					$description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
				}
			}
			

			$data['products'][] = [
				'cart_id'      => $product['cart_id'],
				'thumb'        => $product['image'],
				'name'         => $product['name'],
				'model'        => $product['model'],
				'option'       => json_encode($product['option']),
				'subscription' => $description,
				'quantity'     => $product['quantity'],
				'price'        => $price,
				'total'        => $total,
				'href'         => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
			];
		}

		// Gift Voucher
		$data['vouchers'] = [];

		$vouchers = $this->model_checkout_cart->getVouchers();

		foreach ($vouchers as $key => $voucher) {
			$data['vouchers'][] = [
				'key'         => $key,
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
			];
		}

		// Totals
		$data['totals'] = [];

		foreach ($totals as $total) {
			$data['totals'][] = [
				'title' => $total['title'],
				'text'  => $this->currency->format((float)$total['value'], $this->session->data['currency'])
			];
		}

		$json['itemcount'] = $this->cart->countProducts();
		
		$data['list'] = $this->url->link('bytao/cart.infox', 'language=' . $this->config->get('config_language'));
		$data['product_remove'] = $this->url->link('checkout/cart.remove', 'language=' . $this->config->get('config_language'));
		$data['voucher_remove'] = $this->url->link('checkout/voucher.remove', 'language=' . $this->config->get('config_language'));
		
		
		$data['checkout'] = $data['cart'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'));
		if($groupId != 2){
			$data['checkout'] = $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'));
		}
		
		
		if($isx){ $x = $isx; }else{ $x=''; }

		return $this->load->view('bytao/cart'.$x, $data);
	}

	public function info(): void {
		$this->response->setOutput($this->index());
	}
	
	public function infox() {
		$this->response->setOutput($this->index('x'));
	}
	
	public function infoy() {
		$this->response->setOutput($this->index('y'));
	}
	
	public function items():string	 {
		$tot = $this->cart->countProducts();
		return (string)$tot;
	}
	
	public function getcart():void {
		$json=[];
		
		$json['itemcount'] = $this->cart->countProducts();
		$json['cart'] = $this->index('x');
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
}