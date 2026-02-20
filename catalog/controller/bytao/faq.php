<?php  
namespace Opencart\Catalog\Controller\Bytao;
class Faq extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/faq';
	private $C = 'faq';
	private $ID = 'faq_id';
	private $model ;
	
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}

	
	public function index() {
		$this->getML('ML');
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home(true)
		];
		
		$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link($this->cPth)
		];
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['rows_title'] = $this->language->get('rows_title');
		
		$data['rows'] = $this->model->{$this->getFunc('get','Questions')}();
		
		$hData = [
					'ctrl'   => 'faq',
					'route'   => $this->cPth,
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);

		$this->response->setOutput($this->load->view($this->cPth, $data));
		 
	}
    
    
   
}
?>