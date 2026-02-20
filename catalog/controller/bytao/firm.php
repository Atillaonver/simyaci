<?php
namespace Opencart\Catalog\Controller\Bytao;
class Firm extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/firm';
	private $C = 'firm';
	private $ID = 'firm_id';
	private $Tkn = 'user_token';
	private $storeId = 0;
	private $model ;
	private $modalTypes = array('text','stext', 'simage','collection','youtube' , 'slider','carousel' , 'module','control');//1,2,3,4,5,6,7,..
	
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
	
	public function index():void {
		$this->getML('ML');
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];
		
		if (isset($this->request->get[$this->ID])) {
			${$this->ID} = (int)$this->request->get[$this->ID];
		} else {
			${$this->ID} = 0;
		}

		$item_info = $this->model->{$this->getFunc('get')}(${$this->ID});

		if ($item_info) {
			$this->document->setTitle($item_info['meta_title']);
			$this->document->setDescription($item_info['meta_description']);
			$this->document->setKeywords($item_info['meta_keyword']);

			$data['breadcrumbs'][] = [
				'text' => $item_info['title'],
				'href' => $this->url->link($this->cPth, $this->ID.'=' .  ${$this->ID})
			];

			$data['heading_title'] = $item_info['title'];
			
			$data['HTTP_IMAGE'] = HTTPS_IMAGE;
			$data['himage'] = !$item_info['himage']?(!$this->config->get('config_header_image')?$this->config->get('config_header_image'):''):$item_info['himage'];
			$data['fimage'] = !$item_info['fimage']?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$item_info['fimage'];
			
			
			$data['rows'] = $this->load->controller('bytao/editor.rowBender',['ctrl'=>'page',$this->ID => ${$this->ID}]);

			$data['continue'] = $this->url->home();

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['column_right'] = $this->load->controller('common/column_right');
			$hData = [
					'route'   => 'bytao/firm',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);

			$this->response->setOutput($this->load->view($this->cPth, $data));
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link($this->cPth, $this->ID.'=' . ${$this->ID})
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->home();

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',['route'=>'error/not_found']);
			$data['header'] = $this->load->controller('bytao/header',['route'=>'error/not_found']);

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	public function agree():void {

		if (isset($this->request->get[$this->ID])) {
			${$this->ID} = (int)$this->request->get[$this->ID];
		} else {
			${$this->ID} = 0;
		}

		$output = '';

		$item_info = $this->model->{$this->getFunc('get')}(${$this->ID});

		if ($item_info) {
			$output .= html_entity_decode($item_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->addHeader('X-Robots-Tag: noindex');

		$this->response->setOutput($output);
	}
}
