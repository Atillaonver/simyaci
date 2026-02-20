<?php

namespace Iyzipay;

class Curl
{
    public function exec($url, $options)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
		file_put_contents(DIR_LOGS . 'error.log', date('Y-m-d H:i:s') . ' URL - ' . print_r($url, true) . "\n", FILE_APPEND);
		file_put_contents(DIR_LOGS . 'error.log', date('Y-m-d H:i:s') . ' OPTIONS - ' . print_r($options, true) . "\n", FILE_APPEND);
        return curl_exec($ch);
    }
}