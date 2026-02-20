<?php
namespace Opencart\Admin\Controller\Bytao;
class Expenses extends \Opencart\System\Engine\Controller {
	private $error = [];
	private $cPth = 'bytao/expenses';
	private $C = 'expenses';
	private $ID = 'expenses_id';
	private $Tkn = 'user_token';
	private $model;
	
	private function getFunc($f='',$addi=''):string {
		return $f; //$f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C)));
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			case 'L':
				$this->load->language($this->cPth);
				break;
			case 'ML':
			case 'LM':
				$this->load->language($this->cPth);
				$this->load->model($this->cPth); 
				$this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};
				break;
			default:
		}
	}
	
	public function index(): void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['heading_title'] = $this->language->get('heading_title');

		$url = '';

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];


		if (isset($this->request->get['group_id'])) {
				$data['group_id']=$this->request->get['group_id'];
			}else{
				$data['group_id']=0;
			}
			

		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		
		$data['header'] = $this->load->controller('common/header');  
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth, $data));
	}

}