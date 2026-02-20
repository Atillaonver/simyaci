<?php  
namespace Opencart\Catalog\Controller\Bytao;
use \Opencart\System\Helper as Helper;
class Newsletter extends \Opencart\System\Engine\Controller {

	private $version = '1.0.0';
	private $cPth = 'bytao/newsletter';
	private $C = 'newsletter';
	private $ID = 'newsletter_id';
	private $model ;
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function index():string {
		$this->getML('L');
		$data['loged'] = $this->customer->isLogged();
		$data['customer_email'] = $this->customer->getEmail();
		
		$data['action'] = $this->url->link('bytao/newsletter.subscribe', 'language=' . $this->config->get('config_language'));
		
		$data['subscribe_url'] = $this->url->link($this->cPth.'.subscribe','language=' . $this->config->get('config_language'));
		$data['unsubscribe_url'] = $this->url->link($this->cPth.'.unsubscribe','language=' . $this->config->get('config_language'));
		
		return $this->load->view($this->cPth, $data);
	}
	
	public function subscribe():void  {
		$this->getML('ML');
		$json = [];
		
		if (isset($this->request->post['email']) && $this->request->post['email'] && ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL))) {
			$json['error']['email'] = $this->language->get('error_email');
		}
		
		if (!$json && isset($this->request->post['email'])) {
			if (!$this->model->{$this->getFunc('is','Subscribed')}($this->request->post['email'])) {
				$this->model->{$this->getFunc('subscribe')}($this->request->post['email']);
				$json['success'] = $this->language->get('text_success_subscribe');
			}else{
				$json['error']['email'] = $this->language->get('error_subscribe');
			}
		}	
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function unsubscribe():void {
		$this->getML('ML');
		$json = [];
		
		if ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}
		
		if (!$json) {
			if (!$this->model->{$this->getFunc('is','Subscribed')}($this->request->post['email'])) {
				$this->model->{$this->getFunc('unsubscribe')}($this->request->post['email']);
				$json['success'] = $this->language->get('text_success_unsubscribe');
			}else{
				$json['error']['email'] = $this->language->get('error_undescribe');
			}
		}	
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getwidget( array $cDatat=[]):string {
		
		$this->getML('ML');
		if(isset($cDatat['col_content_id'])){
			
			$parts = explode(',', $cDatat['col_content_id']);
		
			if (isset($parts[1])) {
				${$this->ID} = (int)$parts[1];
			} else {
				${$this->ID}  = 0;
			}

			
			$group_info = $this->model->{$this->getFunc('get','Module')}(${$this->ID});
			if ($group_info) {
				
				$description = $this->model->{$this->getFunc('get','ModuleDescription')}(${$this->ID});
				$data['newsletter_module_id'] = ${$this->ID};
				$data['action'] = $this->url->link('bytao/newsletter.subscribe');
				$data['description'] = $description['description'];
				$data['social'] = $description['social'];

				return $this->load->view($this->cPth.'_widget', $data);
			} 
		}
		
		return '';
	}
	
	
}
?>