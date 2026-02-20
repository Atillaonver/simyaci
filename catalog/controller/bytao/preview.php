<?php
namespace Opencart\Catalog\Controller\Bytao;
class Preview extends \Opencart\System\Engine\Controller {
	/*
	 * Opencart\Catalog\Controller\Api\Account\Login.Index
	 *
	 * @Example
	 *
	 * $url = 'https://www.yourdomain.com/index.php?route=api/account/login&language=en-gb&store_id=0';
	 *
	 * $request_data = [
	 * 		'username' => 'Default',
	 *		'key'      => ''
	 * ];
	 *
	 * $curl = curl_init();
	 *
	 * curl_setopt($curl, CURLOPT_URL, $url);
	 * curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 * curl_setopt($curl, CURLOPT_HEADER, false);
	 * curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	 * curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	 * curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	 * curl_setopt($curl, CURLOPT_POST, 1);
	 * curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);
	 *
	 * $response = curl_exec($curl);
 	 *
	 * $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	 *
	 * curl_close($curl);
	 *
	 * if ($status == 200) {
	 *		$api_token = json_decode($response, true);
	 *
	 * 		if (isset($api_token['api_token'])) {
	 *
	 * 			// You can now store the session cookie as a var in the your current session or some of persistent storage
	 * 			$session_id = $api_token['api_token'];
	 * 		}
	 * }
	 *
	 * $url = 'http://www.yourdomain.com/opencart-master/upload/index.php?route=api/sale/order.load&language=en-gb&store_id=0&order_id=1';
	 *
	 * $curl = curl_init();
	 *
	 * curl_setopt($curl, CURLOPT_URL, $url);
	 * curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	 * curl_setopt($curl, CURLOPT_HEADER, false);
	 * curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	 * curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	 * curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	 * curl_setopt($curl, CURLOPT_POST, 1);
	 * curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);
	 *
	 * // Add the session cookie so we don't have to login again.
	 * curl_setopt($curl, CURLOPT_COOKIE, 'OCSESSID=' . $session_id);
	 *
	 * $response = curl_exec($curl);
	 *
	 * curl_close($curl);
	 *
	 */
	public function index(): void {
		$path = $this->request->get['path'];
		$data['language']=$this->config->get('config_language');
		$this->load->language($path);
		
		$this->response->setOutput($this->load->view($path, $data));
	}
}
