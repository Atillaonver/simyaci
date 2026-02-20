<?php
namespace Opencart\Catalog\Controller\Bytao;
class Accepte extends \Opencart\System\Engine\Controller {
	public function index($ind=array()):string {
		$this->load->language('bytao/accepte');
		$data['politics'] = $this->url->link('information/information', 'information_id=9');
		if(isset($this->session->data['accepte'])) {
			return '';
		}else{
			return $this->load->view('bytao/accepte',$data);
		}
	}
	
	public function accepte():void
	{
		$json = array();
		$this->session->data['accepte'] = '1';
		$json['accepte'] = '1';
		$this->response->addHeader('Access-Control-Allow-Origin: *');
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
}
