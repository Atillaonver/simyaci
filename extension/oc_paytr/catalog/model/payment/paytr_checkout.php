<?php
namespace Opencart\Catalog\Model\Extension\OcPaytr\Payment;

use Opencart\System\Engine\Model;

class PaytrCheckout extends Model
{
    public function getMethods(array $address): array {

        if (!$this->getStatus($address)) {
            return [];
        }

        $this->load->language('extension/oc_paytr/payment/paytr_checkout');

        return [
            'code' => 'paytr_checkout',
            'name' => $this->config->get('payment_paytr_checkout_title'),
            'sort_order' => $this->config->get('payment_paytr_checkout_sort_order'),
            'option' => [
                'payment' => [
                    'code' => 'paytr_checkout.payment',
                    'name' => $this->config->get('payment_paytr_checkout_description'),
                ]
            ],
        ];
    }

    private function getStatus(array $address): bool
    {
        $status = false;

        $this->load->model('checkout/order');

        $total = $this->getTotal();
        if (
            $this->config->get('payment_paytr_checkout_status')
            && ($total >= 0 && (float) $this->config->get('payment_paytr_checkout_total') <= $total)
        ) {
            $geoZoneId = (int)$this->config->get('payment_paytr_checkout_geo');
            $zoneId = 0;
            $countryId = 0;

            if (isset($address['country_id']) && isset($address['zone_id'])) {
                $zoneId = (int)$address['zone_id'];
                $countryId = (int)$address['country_id'];
            } elseif (isset($this->session->data['payment_address'])) {
                $zoneId = (int)$this->session->data['payment_address']['zone_id'];
                $countryId = (int)$this->session->data['payment_address']['country_id'];
            } elseif (isset($this->session->data['shipping_address'])) {
                $zoneId = (int)$this->session->data['shipping_address']['zone_id'];
                $countryId = (int)$this->session->data['shipping_address']['country_id'];
            }

            $zones = implode(',', [$zoneId, 0]);
            $query = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone"
                . " WHERE geo_zone_id = '" . $geoZoneId
                . "' AND country_id = '" . $countryId
                . "' AND zone_id IN (" . $zones . ")"
            );

            if (!$this->config->get('payment_paytr_checkout_geo') || $query->num_rows) {
                $status = true;
            }
        }

        return $status;
    }

    private function getTotal(): float
    {
        if (isset($this->session->data['order_id'])) {
            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if ($order) {
                return (float)$order['total'];
            }
        }

        $totals = [];
        $taxes = $this->cart->getTaxes();
        $total = 0.;

        $this->load->model('checkout/cart');
        ($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

        return $total;
    }
}