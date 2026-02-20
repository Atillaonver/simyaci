<?php
namespace Opencart\Catalog\Controller\Startup;
class Language extends \Opencart\System\Engine\Controller {
	/**
	 * @var array<string, array<string, mixed>>
	 */
	private static array $languages = [];

	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->model('bytao/common');

		self::$languages = $this->model_bytao_common->getStoreLanguages();

		$language_info = [];

		// Set default language
		
		if (isset(self::$languages[$this->config->get('config_language')])) {
			$language_info = self::$languages[$this->config->get('config_language')];
		}

		// If GET has language var
		if (isset($this->request->get['language']) && isset(self::$languages[$this->request->get['language']])) {
			if(defined('RUN_AS')){
				$this->load->model('setting/setting');
				$code = $this->model_setting_setting->getSettingbyKey('config_language',RUN_AS);
				
				$language_info = self::$languages[$this->request->get['language']];
			}else{
				$language_info = self::$languages[$this->request->get['language']];
			}
		}

		if ($language_info) {
			// If extension switch add language directory
			if ($language_info['extension']) {
				$this->language->addPath('extension/' . $language_info['extension'], DIR_EXTENSION . $language_info['extension'] . '/catalog/language/');
			}

			// Set the config language_id key
			$this->config->set('config_language_id', $language_info['language_id']);
			$this->config->set('config_language', $language_info['code']);

			$this->load->language('default');
		}
	}

	/**
	 * After
	 *
	 * Override the language default values
	 *
	 * @param string       $route
	 * @param string       $prefix
	 * @param string       $code
	 * @param array<mixed> $output
	 *
	 * @return void
	 */
	public function after(&$route, &$prefix, &$code, &$output): void {
		if (!$code) {
			
			$code = $this->config->get('config_language');
		}
		
		// Use $this->language->load so it's not triggering infinite loops
		$this->language->load($route, $prefix, $code);
		
		if (isset(self::$languages[$code])) {
			$language_info = self::$languages[$code];

			$path = '';

			if ($language_info['extension']) {
				$extension = 'extension/' . $language_info['extension'];

				if (oc_substr($route, 0, strlen($extension)) != $extension) {
					$path = $extension . '/';
				}
			}

			// Use $this->language->load so it's not triggering infinite loops
			$this->language->load($path . $route, $prefix, $code);
		}
	}

}

class LanguageX extends \Opencart\System\Engine\Controller {
	private static $extension = '';

	public function index(): void {
		if (isset($this->request->get['language'])) {
			$code = (string)$this->request->get['language'];
		} else {
			$code = $this->config->get('config_language');
			if(defined('RUN_AS')){
				$this->load->model('setting/setting');
				$code = $this->model_setting_setting->getSettingbyKey('config_language',RUN_AS);
			}
		}
		
		$this->load->model('localisation/language');
		$language_info = $this->model_localisation_language->getLanguageByCode($code);
		
		if ($language_info) {
			// If extension switch add language directory
			if ($language_info['extension']) {
				self::$extension = $language_info['extension'];

				$this->language->addPath('extension/' . $language_info['extension'], DIR_EXTENSION . $language_info['extension'] . '/catalog/language/');
			}

			// Set the config language_id key
			$this->config->set('config_language_id', $language_info['language_id']);
			$this->config->set('config_language', $language_info['code']);
			$this->load->language('default');
		} 
		else 
		{
			$url_data = $this->request->get;

			if (isset($url_data['route'])) {
				$route = $url_data['route'];
			} else {
				$route = $this->config->get('action_default');
			}
			
			if (isset($url_data['language'])) {
				$language = $url_data['language'];
			} else {
				$language =  $this->config->get('config_language');
			}
			

			unset($url_data['route']);
			unset($url_data['language']);

			$url = '';

			if ($url_data) {
				$url .= '&' . urldecode(http_build_query($url_data));
			}

			// If no language can be found, we use the default one
			$this->response->redirect($this->url->link($route, 'language=' . $language . $url, true));
		}
	}
	
	// Fill the language up with default values
	public function after(&$route, &$prefix, &$code, &$output): void {
		if ($code) {
			$language = $code;
		} else {
			$language = $this->config->get('config_language');
		}

		// Use load->language so it's not triggering infinite loops
		if (oc_substr($route, 0, 10) != 'extension/' && self::$extension) {
			$this->load->language('extension/' . self::$extension . '/' . $route, $prefix, $language);
		}
	}
}




