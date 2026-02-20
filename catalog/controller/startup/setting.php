<?php
namespace Opencart\Catalog\Controller\Startup;
class Setting extends \Opencart\System\Engine\Controller {
	public function index(): void {
	    	
		$this->load->model('setting/store');
		$this->load->model('setting/setting');
		
		$hostname = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . str_replace('www.', '', $this->request->server['HTTP_HOST']) . rtrim(dirname($this->request->server['PHP_SELF']), '/.\\') . '/';
       
		$run_store_as = $this->model_setting_store->runAs();
		$runer_store_as = $this->model_setting_store->runerAs();
		$HTTPS_ST = HTTPS_ST;
		
		if (((int)$runer_store_as == (int)$HTTPS_ST) && $run_store_as) {
			$store_info = $this->model_setting_store->getStore($run_store_as);
			$HTTPS_ST = $run_store_as;
			define('RUN_AS', $run_store_as);
			define('RUN_AS_URL', HTTP_SERVER);
		}else{
			if (defined('RUN_AS'))runkit7_constant_remove('RUN_AS');
			if (defined('RUN_AS_URL')) runkit7_constant_remove('RUN_AS_URL');
			$store_info = $this->model_setting_store->getStoreByHostname($hostname);
		}
		
		
		
		// Store
		if ($store_info) {
			$this->config->set('config_store_id', $store_info['store_id']);
		} elseif (isset($this->request->get['store_id'])) {
			$this->config->set('config_store_id', (int)$this->request->get['store_id']);
		} else {
			$this->config->set('config_store_id', $HTTPS_ST);
		}

		if (!$store_info) {
			$this->config->set('config_url', HTTP_SERVER);
		}

		
		
		// Settings
		
		if (!$run_store_as || $run_store_as != $HTTPS_ST ) {
			$results = $this->model_setting_setting->getSettings($this->config->get('config_store_id'));
		}else{
			$this->config->set('config_store_id', $run_store_as);
			$results = $this->model_setting_setting->getSettings($run_store_as);
		}
		
		foreach ($results as $result) {
			if (!$result['serialized']) {
				$this->config->set($result['key'], $result['value']);
			} else {
				$this->config->set($result['key'], json_decode($result['value'], true));
			}
		}
		
		
		
		if(HTTPS_ST == 0 && $run_store_as == 0){
			$this->config->set('config_url', HTTP_SERVER);
		}
		
		
		// Url
		$this->registry->set('url', new \Opencart\System\Library\Url($this->config->get('config_url')));

		// Set time zone
		if ($this->config->get('config_timezone')) {
			date_default_timezone_set($this->config->get('config_timezone'));

			// Sync PHP and DB time zones.
			$this->db->query("SET time_zone = '" . $this->db->escape(date('P')) . "'");
		}

		// Response output compression level
		if ($this->config->get('config_compression')) {
			$this->response->setCompression((int)$this->config->get('config_compression'));
		}
	}
}