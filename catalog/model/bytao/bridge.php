<?php
namespace Opencart\Catalog\Model\Bytao;
class Bridge extends \Opencart\System\Engine\Model {
    
	public function mailSend() {
		// deneme;
		
		return isset($this->request->get['sended'])?(int)$this->request->get['sended']:0;	
	}
	
}
