<?php
namespace Opencart\Catalog\Model\Extension\OcPaytr\Payment;

class TotalPaytrCheckout extends \Opencart\System\Engine\Model
{
	public function getTotal($total) {
		$this->load->language('extension/oc_paytr/payment/paytr_checkout');

		$total['totals'][] = array(
			'code'       => 'paytr_checkout',
			'title'      => $this->language->get('text_total'),
			'value'      => max(0, $total['total']),
			'sort_order' => '8'
		);
	}
}