<?php
namespace Opencart\Catalog\Controller\Startup;
class SeoUrl extends \Opencart\System\Engine\Controller {
	private array $data = [];
	public function index() {
	    	
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
			$this->load->model('design/seo_url');
			$this->load->model('design/layout');
			$this->load->model('localisation/language');
			if (isset($this->request->get['_route_'])) {
				//$this->log->write('_route_:'.print_r($this->request->get['_route_'],TRUE));
				
				$parts = explode('/', $this->request->get['_route_']);
				if (oc_strlen(end($parts)) == 0) {
					array_pop($parts);
				}
				if(count($parts)){
				    foreach ($parts as $key => $value) {
    					$seo_url_info = $this->model_design_seo_url->getSeoUrlByKeyword($value);
    					if ($seo_url_info) {
    						if($seo_url_info['key']=='route'){
    							$this->request->get['route']= html_entity_decode($seo_url_info['value'], ENT_QUOTES, 'UTF-8');;
    						}elseif($seo_url_info['key']=='language'){
    							$this->request->get['language'] = html_entity_decode($seo_url_info['value'], ENT_QUOTES, 'UTF-8');
    						}else{
    							$this->request->get[$seo_url_info['key']] = html_entity_decode($seo_url_info['value'], ENT_QUOTES, 'UTF-8');
    							$this->request->get['route']= html_entity_decode($seo_url_info['route'], ENT_QUOTES, 'UTF-8');;
    						}
    						unset($parts[$key]);
    					}else{
							$this->request->get['route'] = $this->config->get('action_error');
    					}
    				}
                }
                else
                {
                    	$this->request->get['route'] = $this->config->get('action_default');
                }
                
				if (!isset($this->request->get['route'])) {
					$this->request->get['route'] = $this->config->get('action_default');
				}

				if ($parts) {
					$this->request->get['route'] = $this->config->get('action_error');
				}
			} 
			elseif(isset($this->request->get['route']))
			{
				//$this->log->write('route:'.print_r($this->request->get,TRUE));
				$this->request->get['route'] = $this->request->get['route'];
			} else {
				
				
				$route = $this->model_design_layout->getRoute($this->config->get('config_layout_id'),$this->config->get('config_store_id'));
				//$this->log->write('else Route:'.print_r($this->config->get('config_layout_id'),TRUE).'-'.print_r($this->config->get('config_layout_id'),TRUE));
				//$this->log->write('else Route:'.print_r($this->config->get('config_store_id'),TRUE));
				//$this->log->write('else Route:'.print_r($route,TRUE));
				$this->request->get['route'] = $route;
			}
		}

		return null;
	}

	public function rewrite(string $link): string {
		$url_info = parse_url(str_replace('&amp;', '&', $link));
		
		
		// Build the url
		$url = '';

		if ($url_info['scheme']) {
			$url .= $url_info['scheme'];
		}

		$url .= '://';

		if ($url_info['host']) {
			$url .= $url_info['host'];
		}

		if (isset($url_info['port'])) {
			$url .= ':' . $url_info['port'];
		}

		parse_str($url_info['query'], $query);

		// Start changing the URL query into a path
		$paths = [];
		
		// Parse the query into its separate parts
		$parts = explode('&', $url_info['query']);
		$language='';
		foreach ($parts as $part) {
			$pair = explode('=', $part);
			if (isset($pair[1])) {
				if($pair[0]=='language' ){
					$language = $pair[1];
				}
			}
		}
		
		$lcount = count($this->config->get('config_store_languages'));
		$language_id = $language<>''?$this->model_localisation_language->getLanguageIdByCode($language):0;
		
		foreach ($parts as $part) {
			$pair = explode('=', $part);

			if (isset($pair[0])) {
				$key = (string)$pair[0];
			}

			if (isset($pair[1])) {
				$value = (string)$pair[1];
			} else {
				$value = '';
			}
			
			$index = $key . '=' . $value.'-'.$language_id;

			if (!isset($this->data[$index])) {
				$this->data[$index] = $this->model_design_seo_url->getSeoUrlByKeyValue((string)$key, (string)$value,(int)$language_id);
			}

			if ($this->data[$index]) {
				$paths[] = $this->data[$index];
				unset($query[$key]);
			}
		}
		unset($query['route']);
		$sort_order = [];

		foreach ($paths as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $paths);

		// Build the path
		$url .= str_replace('/index.php', '', $url_info['path']);
		
		foreach ($paths as $result) {
			if (($result['key'] == 'language' && $lcount > 1) || $result['key'] != 'language') {
					$url .= '/' . $result['keyword'];
			}
		}

		// Rebuild the URL query
		if ($query) {
			$url .= '?' . str_replace(['%2F'], ['/'], http_build_query($query));
		}
		
		return $url;
	}
}
	

