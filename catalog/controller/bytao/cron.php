<?php
namespace Opencart\Catalog\Controller\Bytao;
class Cron extends \Opencart\System\Engine\Controller {
	public function index():void {
		
	}
	
	public function adm():void {
		$this->log->write('******** Cron Admin ********');
	}
}
