<?php

namespace Opencart\Catalog\Controller\Extension\OcbgTurnstile\Captcha;
/**
 * Class Turnstile
 *
 * @package Opencart\Catalog\Controller\Extension\OcbgTurnstile\Captcha
 */
class Turnstile extends \Opencart\System\Engine\Controller {
    /**
     * Index
     *
     * @return string
     */
    public function index(): string {
        $this->load->language('extension/ocbg_turnstile/captcha/turnstile');

        $data['key'] = $this->config->get('captcha_turnstile_key');
        $data['language'] = $this->config->get('config_language');

        $this->document->addScript('https://challenges.cloudflare.com/turnstile/v0/api.js');

        return $this->load->view('extension/ocbg_turnstile/captcha/turnstile', $data);
    }

    /**
     * Validate Captcha
     *
     * @return string
     */
    public function validate(): string {

        $this->load->language('extension/ocbg_turnstile/captcha/turnstile');

        if (!empty($this->request->post['cf-turnstile-response'])) {

            $cf_secret = $this->config->get('captcha_turnstile_secret');
            $cf_connecting_ip = $_SERVER["REMOTE_ADDR"];
            $cf_turnstile_response = $this->request->post['cf-turnstile-response'];

            $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";

            $data = array(
                "secret" => $cf_secret,
                "response" => $cf_turnstile_response,
                "remoteip" => $cf_connecting_ip
            );

            $options = array(
                "http" => array(
                    "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
                    "method" => "POST",
                    "content" => http_build_query($data)
                )
            );
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result = json_decode($result);

            if ($result->success === false) {
                return $this->language->get('error_captcha');
            } else {
                return '';
            }
        } else {
            return $this->language->get('error_captcha');
        }
    }
}
