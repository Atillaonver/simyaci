<?php

namespace Opencart\Admin\Controller\Extension\OcPaytr\Payment;

require_once DIR_EXTENSION . 'oc_paytr/system/PaytrCore.php';
use PaytrCore;
class PaytrCheckout extends \Opencart\System\Engine\Controller
{
    private PaytrCore $paytr;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->paytr = new PaytrCore($registry);
    }

    public function checkTelephoneRequired() 
    {
    $this->load->model('setting/setting');
    $store_settings = $this->model_setting_setting->getSetting('config', 0);
    return ($store_settings['config_telephone_required'] ?? 0) == 1;
    }

    public function checkBillingSettings()
    {
    $this->load->model('setting/setting');
    $store_settings = $this->model_setting_setting->getSetting('config', 0);
    
    $is_address_required = ($store_settings['config_checkout_address'] ?? 0) == 1;
    $use_shipping_address = ($store_settings['config_checkout_payment_address'] ?? 0) == 1;
    
    return $is_address_required && !$use_shipping_address;
    }

    // Class Handler
    public function index(): void
    {
        // Load Language
        $this->load->language('extension/oc_paytr/payment/paytr_checkout');
        // Load Models
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');

        // SameSite kontrolü
       $store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;
    
        // Sistem ayarlarını yükle
        $this->load->model('setting/setting');
        $store_settings = $this->model_setting_setting->getSetting('config', $store_id);
    
        // SameSite Cookie ayarını kontrol et
        $samesite_setting = $store_settings['config_session_samesite'] ?? 'Lax';
    
        if (in_array($samesite_setting, ['Strict', 'None'])) {
        $data['warning_samesite'] = sprintf(
            $this->language->get('error_samesite'),
            $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
        );
        } else {
        $data['warning_samesite'] = '';
        }

        //telefon kontrolü
        $phone_setting = $store_settings['config_telephone_display'] ?? '1';
        $phone1_setting = $store_settings['config_telephone_required'] ?? '1';
/*        if (in_array($phone_setting, ['0'])) {
        $data['warning_phone'] = sprintf(
            $this->language->get('error_phone'),
            $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
        );
        }
        elseif (in_array($phone1_setting, ['0'])) {
        $data['warning_phone'] = sprintf(
            $this->language->get('error_phone'),
            $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
        );
*/
       if (in_array($phone_setting, ['0']) || in_array($phone1_setting, ['0'])) {
    $data['warning_phone'] = sprintf(
        $this->language->get('error_phone'),
        $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
    );
} else {
        $data['warning_phone'] = '';
        }

        //adres kontrolü
        $adres_setting = $store_settings['config_checkout_payment_address'] ?? '1';
        $adress_setting = $store_settings['config_checkout_shipping_address'] ?? '1';
        
        if (in_array($adres_setting, ['0']) || in_array($adress_setting, ['0'])) {
    $data['warning_adress'] = sprintf(
        $this->language->get('error_adres'),
        $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id)
    );
} else {
        $data['warning_adress'] = '';
        }


        // Set Page Title
        $this->document->setTitle($this->language->get('heading_title'));
        // All Language Keys
        $keys = [
            'heading_title', 'text_settings', 'test_mode', 'text_general', 'text_order_status', 'text_module_settings',
            'text_enabled', 'text_disabled', 'text_select', 'text_ins_total', 'entry_title',
            'entry_description', 'entry_merchant_id', 'entry_merchant_key', 'entry_merchant_salt',
            'entry_language', 'entry_total', 'entry_module_layout', 'entry_status', 'entry_sort_order',
            'entry_payment_complete', 'entry_payment_failed', 'entry_notify_status', 'entry_ins_total',
            'entry_order_total', 'entry_max_installments', 'help_paytr_checkout', 'help_total',
            'help_notify', 'help_ins_total', 'help_order_total', 'button_save', 'button_cancel','entry_iframe_version',
            'entry_iframe_theme', 'text_iframe_v1', 'text_iframe_v2', 'text_iframe_theme_light', 'text_iframe_theme_dark'
        ];
        // Set Language Keys
        foreach ($keys as $key) {
            $data[$key] = $this->language->get($key);
        }
        // Frontend Buttons
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');
        $data['save'] = $this->url->link('extension/oc_paytr/payment/paytr_checkout.save', 'user_token=' . $this->session->data['user_token']);
        $data['action'] = $this->url->link('extension/oc_paytr/payment/paytr_checkout', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);
        $data['paytr_icon_loader'] = '/extension/oc_paytr/catalog/view/theme/default/image/payment/spinner.gif';
        // Set User Token
        $data['user_token'] = $this->request->get['user_token'];
        // Breadcrumbs
        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('text_extensions'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/oc_paytr/payment/paytr_checkout', 'user_token=' . $this->session->data['user_token'], true),
            ],
        ];
        // Get Installment Array
        $data['installment_arr'] = $this->paytr->installmentOptions($this->language->get('code'), true);
        // Get Order Statuses
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        // Get Module Languages
        $data['language_arr'] = $this->language->get('code') == "tr"
            ? [0 => 'Otomatik', 1 => 'Türkçe', 2 => 'İngilizce']
            : [0 => 'Automatic', 1 => 'Turkish', 2 => 'English'];
        // Get Module Layout
        $data['module_layout'] = [
            'standard' => $this->language->get('text_module_layout_standard'),
            'onepage' => $this->language->get('text_module_layout_one')
        ];
        $data['iframe_version_options'] = ['1' => $this->language->get('text_iframe_v2'),'0' => $this->language->get('text_iframe_v1')
        ];
        $data['iframe_theme_options'] = ['light' => $this->language->get('text_iframe_theme_light'),'dark' => $this->language->get('text_iframe_theme_dark')
        ];
        // Get Input Fields
        $fields = [
            'title', 'description', 'test_mode', 'merchant_id', 'merchant_key', 'merchant_salt',
            'lang', 'total', 'module_layout', 'status', 'sort_order',
            'order_completed_id', 'order_canceled_id', 'notify', 'ins_total', 'order_total',
            'installment_number','iframe_version', 'iframe_theme'
        ];
        // Returns Fields and Fetches for Config
        foreach ($fields as $field) {
            $key = 'payment_paytr_checkout_' . $field;
            $data[$key] = isset($this->request->post[$key])
                ? ($field === 'title' || $field === 'description' || $field === 'merchant_id' || $field === 'merchant_key' || $field === 'merchant_salt'
                    ? trim($this->request->post[$key])
                    : $this->request->post[$key])
                : $this->config->get($key);
            // Special case for installment_number
            if ($field === 'installment_number' && !isset($this->request->post[$key])) {
                $data[$key] = $this->config->get($key) ?? 0;
            }
        }
        // Template Parts
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        // Return Response
        $this->response->setOutput($this->load->view('extension/oc_paytr/payment/paytr_checkout', $data));
    }

    // Update Function
    public function save(): void {
        // Load Language
        $this->load->language('extension/oc_paytr/payment/paytr_checkout');
        // Permission Check
        if (!$this->user->hasPermission('modify', 'extension/oc_paytr/payment/paytr_checkout')) {
            $this->error['warning'] = 1;
        }
        // Prepare Blank Json
        $json = [];
        // Validation Start
        $validations = [
            ['title', 'required', 'error_paytr_checkout_title'],
            ['description', 'required', 'error_paytr_checkout_description'],
            ['merchant_id', 'required', 'error_paytr_checkout_merchant_id'],
            ['merchant_id', 'is_numeric', 'error_paytr_checkout_merchant_id_val'],
            ['merchant_key', 'required', 'error_paytr_checkout_merchant_key'],
            ['merchant_key', 'strlen_between', 'error_paytr_checkout_merchant_key_len', 16, 16],
            ['merchant_salt', 'required', 'error_paytr_checkout_merchant_salt'],
            ['merchant_salt', 'strlen_between', 'error_paytr_checkout_merchant_salt_len', 16, 16],
            ['order_completed_id', 'required', 'error_paytr_checkout_order_completed_id'],
            ['order_canceled_id', 'required', 'error_paytr_checkout_order_canceled_id'],
        ];
        // Validation Catcher
        foreach ($validations as $validation) {
            $field = $validation[0];
            $method = $validation[1];
            $errorKey = $validation[2];
            $min = $validation[3] ?? null;
            $max = $validation[4] ?? null;

            if (!$this->validateField($field, $method, $min, $max)) {
                $json['error'] = $this->language->get($errorKey);
                break;
            }
        }
        // If There Is No Error
        if (empty($json)) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('payment_paytr_checkout', $this->request->post);
            $json = ['success' => $this->language->get('text_success')];
        }
        // Return Response
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Install Function
    public function install(): void
    {
        // Load Models
        $this->load->model('setting/setting');
        $this->load->model('extension/oc_paytr/payment/paytr_checkout');
        // Prepare Data
        $data['payment_paytr_checkout_lang'] = '0';
        $data['payment_paytr_checkout_notify'] = '0';
        $data['payment_paytr_checkout_ins_total'] = '0';
        $data['payment_paytr_checkout_order_total'] = '0';
        $data['payment_paytr_checkout_geo_zone_id'] = '0';
        $data['payment_paytr_checkout_total'] = '1';
        $data['payment_paytr_checkout_test_mode'] = '1';
        $data['payment_paytr_checkout_order_completed_id'] = '1';
        $data['payment_paytr_checkout_order_canceled_id'] = '10';
        $data['payment_paytr_checkout_module_layout'] = 'standard';
        $data['payment_paytr_checkout_sort_order'] = '1';
        // Permission Check
        if ($this->user->hasPermission('modify', 'extension/payment')) {
            $this->load->model('extension/oc_paytr/payment/paytr_checkout');
            $this->model_extension_oc_paytr_payment_paytr_checkout->install();
        }
        // Set Data
        $this->model_setting_setting->editSetting('payment_paytr_checkout', $data);
    }

    // Uninstall Function
    public function uninstall(): void
    {
        // Load Models
        $this->load->model('setting/setting');
        $this->load->model('extension/oc_paytr/payment/paytr_checkout');
        // Uninstall Commands
        $this->model_extension_oc_paytr_payment_paytr_checkout->uninstall();
        $this->model_setting_setting->deleteSetting('payment_paytr_checkout');
    }

    // Order Function
    public function order()
    {
        // Load Language
        $this->load->language('extension/oc_paytr/payment/paytr_checkout');
        // Check Status
        if ($this->config->get('payment_paytr_checkout_status')) {
            // Load Models
            $this->load->model('sale/order');
            $this->load->model('localisation/currency');
            // Get Order
            $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $data['entry_refund_transaction'] = $this->language->get('entry_refund_transaction');
            $data['entry_refund_total'] = $this->language->get('entry_refund_total');
            $data['entry_refund_total_paid'] = $this->language->get('entry_refund_total_paid');
            $data['entry_refund_status'] = $this->language->get('entry_refund_status');
            $data['entry_refund_status_message'] = $this->language->get('entry_refund_status_message');
            $data['entry_refund_refund'] = $this->language->get('entry_refund_refund');
            $data['entry_refund_refund_status'] = $this->language->get('entry_refund_refund_status');
            $data['entry_refund_refund_amount'] = $this->language->get('entry_refund_refund_amount');
            $data['entry_refund_refund_date'] = $this->language->get('entry_refund_refund_date');
            $data['paytr_icon_loader'] = '/extension/oc_paytr/catalog/view/theme/default/image/payment/spinner.gif';
            $data['user_token'] = $this->request->get['user_token'];
            $data['order_id'] = $order['order_id'];
            // Load Style
            $this->document->addStyle('view/javascript/paytr/paytr.css');
            // Return View
            return $this->load->view('extension/oc_paytr/payment/paytr_checkout_order', $data);
        }
    }

    // Order Details Function
    public function ajaxTransactions(): void
    {
        $json = array();
        $content = '';

        if (isset($this->request->get['order_id'])) {
            $this->load->model('sale/order');
            $this->load->language('extension/oc_paytr/payment/paytr_checkout');

            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if ($order_info) {

                $paytr_transactions = $this->paytr->transaction->getListTransactionsForRefund($order_info['order_id'], $this->language, 'iframe');

                if ($paytr_transactions['status']) {
                    foreach ($paytr_transactions['content'] as $transaction) {

                        $content .= '<tr>';
                        $content .= '<td>' . $transaction['merchant_oid'] . '</td>';
                        $content .= '<td>' . $transaction['total'] . ' ' . $order_info['currency_code'] . '</td>';
                        $content .= '<td>' . $transaction['total_paid'] . ' ' . $order_info['currency_code'] . '</td>';
                        $content .= '<td>' . $transaction['status'] . '</td>';
                        $content .= '<td>' . $transaction['status_message'] . '</td>';
                        $content .= '<td>' . $transaction['is_refunded'] . '</td>';
                        $content .= '<td>' . $transaction['refund_status'] . '</td>';
                        $content .= '<td>' . $transaction['refund_amount'] . ' ' . $order_info['currency_code'] . '</td>';

                        $content .= '<td>' . date('d-m-y H:i', strtotime($transaction['date_added'])) . '</td>';

                        if ($transaction['refund_form']) {
                            $content .= '<td>' . $transaction['input_refund'] . ' ' . $transaction['button_refund'] . '</td>';
                        } else {
                            $content .= '<td></td>';
                        }

                        $content .= '</tr>';
                    }
                } else {

                    $content .= '<tr><td colspan="9" class="text-center">' . $this->language->get('error_paytr_checkout_refund_incomplete') . '</td></tr>';
                }

                if (isset($paytr_transactions['count']) && $paytr_transactions['count'] >= 2) {
                    $json['count_msg'] = $this->language->get('error_paytr_checkout_refund_recurring');
                } else {
                    $json['count_msg'] = false;
                }

                $json['table'] = $content;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Refund Api Function
    public function ajaxRefundApi(): void
    {
        $json = array();

        if (isset($this->request->get['order_id']) && isset($this->request->get['amount']) && isset($this->request->get['moid'])) {

            $this->load->model('sale/order');
            $this->load->language('extension/oc_paytr/payment/paytr_checkout');

            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if (!$order_info) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_order_not_found');
                die(json_encode($json));
            }

            $paytr_transaction = $this->paytr->transaction->getTransactionByMerchantOID($this->request->get['moid'], 'iframe');

            if (!$paytr_transaction) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_not_found');
                die(json_encode($json));
            }

            $amount = str_replace('+', '', $this->request->get['amount']);
            $amount = str_replace(',', '.', $amount);

            if (empty($amount) || !is_numeric($amount)) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_null');
                die(json_encode($json));
            }

            if ($amount <= 0) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_zero');
                die(json_encode($json));
            }

            if ($paytr_transaction['is_refunded'] && $paytr_transaction['refund_status'] == 'partial') {

                $actually_total = $paytr_transaction['total'] - $paytr_transaction['refund_amount'];

                if (round($actually_total, 2) < $amount) {
                    $json['status'] = 'error';
                    $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_more');
                    die(json_encode($json));
                }
            } else {
                if ($paytr_transaction['total'] < $amount) {
                    $json['status'] = 'error';
                    $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_more');
                    die(json_encode($json));
                }
            }

            try {
                $refund_params = array();
                $refund_params['merchant_id'] = $this->config->get('payment_paytr_checkout_merchant_id');
                $refund_params['merchant_key'] = $this->config->get('payment_paytr_checkout_merchant_key');
                $refund_params['merchant_salt'] = $this->config->get('payment_paytr_checkout_merchant_salt');
                $refund_params['merchant_oid'] = $this->request->get['moid'];
                $refund_params['amount'] = $amount;

                // Do Refund
                $refund_response = $this->paytr->refund->doRefund($refund_params);

                $paytr_tr_refund_status = 'partial';

                if ($refund_response['status'] == 'success') {
                    if ($paytr_transaction['total'] == $amount && $paytr_transaction['total'] == $refund_response['return_amount']) {
                        $paytr_tr_refund_status = 'full';
                        $paytr_tr_refund_amount = $refund_response['return_amount'];
                    } else {
                        if ($paytr_transaction['is_refunded'] && $paytr_transaction['refund_status'] == 'partial') {
                            $paytr_tr_refund_amount = $paytr_transaction['refund_amount'] + $refund_response['return_amount'];

                            if ($paytr_tr_refund_amount == $paytr_transaction['total_paid']) {
                                $paytr_tr_refund_status = 'full';
                                $paytr_tr_refund_amount = $paytr_transaction['total_paid'];
                            }
                        } else {
                            $paytr_tr_refund_amount = $refund_response['return_amount'];
                        }
                    }

                    $update_paytr_tr_params = array();
                    $update_paytr_tr_params['merchant_oid'] = $paytr_transaction['merchant_oid'];
                    $update_paytr_tr_params['refund_status'] = $paytr_tr_refund_status;
                    $update_paytr_tr_params['refund_amount'] = $paytr_tr_refund_amount;

                    $this->paytr->transaction->updateTransactionForRefund($update_paytr_tr_params, 'iframe');

                    $json['status'] = 'success';
                    $json['status_message'] = $this->language->get('text_refund_refund_success');
                } else {
                    $json['status'] = $refund_response['status'];
                    $json['status_message'] = $refund_response['err_no'] . ' - ' . $refund_response['err_msg'];
                }

            } catch (Exception $exception) {
                $json['status'] = $refund_response['status'];
                $json['status_message'] = $refund_response['err_no'] . ' - ' . $refund_response['err_msg'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Category Function
    public function ajaxCategoryBased(): void
    {
        $json = [];
        $finish = [];
        $tree = $this->paytr->categoryParser($this->config->get('config_language_id'));
        $this->paytr->categoryParserClear($tree, 0, array(), $finish);
        $options = $data['payment_paytr_checkout_category_installment'] = $this->config->get('payment_paytr_checkout_category_installment');
        $json['categories'] = $finish;
        $json['result'] = $options;
        $json['installments'] = $this->paytr->installmentOptions($this->language->get('code'));
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Input Validation Function
    private function validateField(string $field, string $validation, int $min = null, int $max = null): bool
    {
        $value = $this->request->post['payment_paytr_checkout_' . $field] ?? null;
        $errorKey = 'paytr_checkout_' . $field;

        switch ($validation) {
            case 'required':
                if (empty($value)) {
                    $this->error[$errorKey] = 1;
                    return false;
                }
                break;
            case 'is_numeric':
                if (!is_numeric($value)) {
                    $this->error[$errorKey . '_val'] = 1;
                    return false;
                }
                break;
            case 'strlen_between':
                if (!empty($value) && (strlen($value) < $min || strlen($value) > $max)) {
                    $this->error[$errorKey . '_len'] = 1;
                    return false;
                }
                break;
            default:
                return false; // Unknown validation type
        }

        return true; // Validation passed
    }
}