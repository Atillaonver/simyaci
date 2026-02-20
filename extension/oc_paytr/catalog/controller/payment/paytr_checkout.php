<?php
namespace Opencart\Catalog\Controller\Extension\OcPaytr\Payment;

require_once DIR_EXTENSION . 'oc_paytr/system/PaytrCore.php';
use PaytrCore;

class PaytrCheckout extends \Opencart\System\Engine\Controller
{
    private $error = array();
    private PaytrCore $paytr;
    private $oc_version = 'PAYTROC4';

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->paytr = new PaytrCore($registry);
    }

    public function index()
    {
        // Load Language
        $this->load->language('extension/oc_paytr/payment/paytr_checkout');
        // Page Layout Settings
        $data['page_layout'] = $this->config->get('payment_paytr_checkout_module_layout');
        // If a one page is not selected, a token is taken.
        if ($data['page_layout'] != 'onepage') {
            $data = $this->getToken();
        }
        // Return response
        return $this->load->view('extension/oc_paytr/payment/paytr_checkout', $data);
    }

    public function onepage()
    {
        $json = [];
        // Check Order ID and Payment Method
        if (!isset($this->session->data['order_id']) ||
            !isset($this->session->data['payment_method']) ||
            $this->session->data['payment_method']['code'] !== 'paytr_checkout.payment') {
            return $this->response->redirect($this->url->link('common/home'));
        }
        // Prepare Data
        $json['status'] = 'success';
        // Return Response
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form()
    {
        // Check Order ID, Payment Method and Layout
        if (!isset($this->session->data['order_id']) ||
            !isset($this->session->data['payment_method']) ||
            $this->session->data['payment_method']['code'] !== 'paytr_checkout.payment' ||
            $this->config->get('payment_paytr_checkout_module_layout') !== 'onepage') {
            return $this->response->redirect($this->url->link('common/home'));
        }
        // Set Page Attributes
        $this->document->setTitle($this->config->get('config_meta_title'));
        $this->document->setDescription($this->config->get('config_meta_description'));
        $this->document->setKeywords($this->config->get('config_meta_keyword'));
        // Get Token
        $data = $this->getToken();
        // Prepare Template Parts
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        // Return Response
        $this->response->setOutput($this->load->view('extension/oc_paytr/payment/paytr_checkout_onepage', $data));
    }

    public function callback(): void
    {
        // Check POST
        if (empty($_POST)) {
            echo '';
            exit;
        }
        // Load Model
        $this->load->model('checkout/order');
        // EFT or Iframe
        if (isset($_POST['payment_type']) && $_POST['payment_type'] == 'eft') {
            $this->paytr->chkHash($_POST, 'eft');
            $this->load->language('extension/oc_paytr/payment/paytr_eft_transfer');
            $this->paytr->eftCallback($_POST, $this->oc_version);
        } else {
            $this->paytr->chkHash($_POST, 'iframe');
            $this->load->language('extension/oc_paytr/payment/paytr_checkout');
            $this->paytr->iframeCallback($_POST, $this->oc_version);
        }
    }

    protected function getToken()
    {
        // Load Model
        $this->load->model('checkout/order');
        $this->load->model('localisation/currency');
        $this->load->model('checkout/cart');
        // Load Language
        $this->load->language('extension/oc_paytr/payment/paytr_checkout');
        // Order ID Control
        if (!isset($this->session->data['order_id'])) {
            return $this->response->redirect($this->url->link('common/home'));
        }
        // Get Order
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        // Get Products
        $products = $this->cart->getProducts();
        // Prepare Params
        $paytr_params = [
            'merchant_id' => $this->config->get('payment_paytr_checkout_merchant_id'),
            'merchant_key' => $this->config->get('payment_paytr_checkout_merchant_key'),
            'merchant_salt' => $this->config->get('payment_paytr_checkout_merchant_salt'),
            'test_mode' => $this->config->get('payment_paytr_checkout_test_mode'),
            'user_ip' => $this->getIp(),
            'email' => $order_info['email'],
        ];
        // Basket and Installments
        $userBasket = [];
        foreach ($products as $product) {
            $userBasket[] = [
                'name' => $product['name'],
                'price' => $this->currency->format($product['price'], $this->session->data['currency']),
                'quantity' => $product['quantity'],
            ];
        }
        $basket_installment = $this->paytr->iframe->getBasketMaxInstallment(
            $userBasket,
            $this->config
        );
        // Merged Params
        $paytr_params = array_merge($paytr_params, [
            'user_basket' => $basket_installment['user_basket'],
            'max_installment' => $basket_installment['max_installment'],
            'no_installment' => $basket_installment['max_installment'] == 1 ? 1 : 0,
        ]);
        // User Info
        $paytr_params['user_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $paytr_params['user_address'] = implode(' ', [
            $order_info['payment_address_1'],
            $order_info['payment_address_2'],
            $order_info['payment_postcode'],
            $order_info['payment_city'],
            $order_info['payment_zone'],
            $order_info['payment_iso_code_3']
        ]);
        $paytr_params['user_phone'] = $order_info['telephone'];
        // Order Details
        $paytr_params['merchant_oid'] = uniqid() . $this->oc_version . $order_info['order_id'];
        $paytr_params['currency'] = strtoupper($order_info['currency_code']);
        $paytr_params['payment_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;
        // URLs
        $paytr_params['merchant_ok_url'] = $this->url->link('checkout/success', '', true);
        $paytr_params['merchant_fail_url'] = $this->url->link('checkout/cart', '', true);
        $paytr_params['iframe_v2'] = ($this->config->get('payment_paytr_checkout_iframe_version')) ? 1 : 0;
        $paytr_params['iframe_v2_dark'] = ($this->config->get('payment_paytr_checkout_iframe_theme') === 'dark') ? 1 : 0;
        // Language
        if ($this->config->get('payment_paytr_checkout_lang') == 0) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            $paytr_params['lang'] = in_array($lang, ['tr', 'en']) ? $lang : 'en';
        } else {
            $paytr_params['lang'] = ($this->config->get('payment_paytr_checkout_lang') == 2 ? 'en' : 'tr');
        }
        // Prepare
        $data = [];
        // Load Curl
        if (function_exists('curl_version')) {
            $getToken = $this->paytr->iframe->getToken($paytr_params);
            if ($getToken['status'] == 'success') {
                $transaction = [
                    'order_id' => $order_info['order_id'],
                    'merchant_oid' => $paytr_params['merchant_oid'],
                    'total' => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
                    'is_failed' => 0,
                    'is_complete' => 0
                ];
                try {
                    if ($this->paytr->transaction->addTransaction($transaction, 'iframe')) {
                        $data['iframe_token'] = $getToken['iframe_token'];
                    } else {
                        $this->error['error_paytr_checkout_transaction_save'] = $this->language->get('error_paytr_checkout_transaction_save');
                    }
                } catch (Exception $e) {
                    $this->error['error_paytr_checkout_transaction_install'] = $this->language->get('error_paytr_checkout_transaction_install');
                }
            } else {
                $this->error['error_paytr_iframe_failed'] = $this->language->get('error_paytr_iframe_failed') . $getToken['status_message'];
            }
        } else {
            $this->error['error_paytr_checkout_curl'] = $this->language->get('error_paytr_checkout_curl');
        }
        if (!empty($this->error)) {
            $data['errors'] = $this->error;
        }
        // Return Response
        return $data;
    }

    protected function getIp()
    {
        return $_SERVER["HTTP_CLIENT_IP"] ?? $_SERVER["HTTP_X_FORWARDED_FOR"] ?? $_SERVER["REMOTE_ADDR"];
    }
}