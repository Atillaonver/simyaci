<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
class HTML extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
		
			$content = $setting['module_description'][$this->config->get('config_language_id')]['description'];
/*			
			$_content = explode('src="', $content);
			$parts=[];
			if (count($_content)>1) {
				foreach ($_content as $key =>$part){
					if ($key!= 0) {
						$_parts = explode('"',$part);
						by_move($_parts[0]);
						$part = implode('"',$_parts);
					}
					$parts[]=$part;
				}
			}
			
			$content =implode('src="', $parts);
			$content = str_replace('src="', HTTPS_IMAGE , $content);
*/
			$data['html'] = html_entity_decode(by_text_move($content,false,URL_IMAGE), ENT_QUOTES, 'UTF-8');

			return $this->load->view('extension/opencart/module/html', $data);
		} else {
			return '';
		}
	}
}