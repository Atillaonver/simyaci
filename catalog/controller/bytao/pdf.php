<?php
namespace Opencart\Catalog\Controller\Bytao;
class Pdf extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/pdf';
	private $C = 'pdf';
	private $ID = 'pdf_id';
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
		$this->load->model('tool/image');
		
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';
		$data['fimage'] = $this->config->get('config_footer_image')?$this->config->get('config_footer_image'):'';
			
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];
		
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';

		if (isset($this->request->get['prod_id'])) {
			$prod_id = $this->request->get['prod_id'];
		} else {
			$prod_id = 0;
		}
		
		$ITEM = $this->model->{$this->getFunc('get')}($prod_id);
		if($ITEM){
			$this->document->setTitle($ITEM['meta_title']);
			$this->document->setDescription($ITEM['meta_description']);
			$this->document->setKeywords($ITEM['meta_keyword']);
			$prod_opts =	$this->config->get('ctrl_prod_opt');
			
			$lid = $this->config->get('config_language_id');
			$data['opt_titles']=[];
			foreach($prod_opts as $prod_opt){
				$data['opt_titles'][$prod_opt['code']]= $prod_opt['opt'][$lid]['name'];
			}
			
			$data['heading_title'] = $ITEM['name'];
			$data['title'] = $ITEM['title'];
			$data['prod_opts'] = $ITEM['opt'];
			$data['description'] = $ITEM['description'];
			$data['description2'] = $ITEM['description2'];
			$data['ref'] = implode('<br/><strong>Ref:</strong>',(explode(',',$ITEM['ref'])));
			
			if (is_file(DIR_IMAGE . $ITEM['image'])) {
				$data['image'] =$ITEM['image'];
			}
			
			if (is_file(DIR_IMAGE . $ITEM['bimage'])) {
				$data['bimage'] =$ITEM['bimage'];
			}
			
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-prod']);
			$this->response->setOutput($this->load->view($this->cPth, $data));
			
		}else{
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('bytao/prod_cat', 'language=' . $this->config->get('config_language') . $url)
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-prod-cat']);

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
		
	}
	
	public function widget($lData=[]){
		$this->getML('ML');
		if (isset($lData['limit'])) {
			$limit = (int)$lData['limit'];
		} else {
			$limit  = 2;
		}
		
		$data['witems'] = array();
		$items = $this->model->{$this->getFunc('get','s')}($limit);
		if ($items) {
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			$data['title'] = $this->language->get('text_widget_title');
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['witems'][] = array(
						'url'  => $result['url'],
						'title'  => $result['title'],
						'description'  => $result['description'],
						'image' => $this->model_tool_image->resize($result['image'],$lData['thumb_width']?$lData['thumb_width']:400,$lData['thumb_height']?$lData['thumb_height']:400),
					);
				}
			}
			return $this->load->view($this->cPth.'_widget', $data);
		} 
	}

	public function generate(array $cData=[]) {
		if(!$cData)return '';
		$ctype =$cData['ctype'];
		$type_id =$cData['type_id'];
		
		if ($ctype == "Invoice" &&(!isset($this->request->get['order_id'])||!$this->request->get['order_id'])) return false;
		$this->load->model('bytao/pdf');
		
		if ($ctype == "Invoice"){return $this->model_bytao_pdf->getInvoice([$this->request->get['order_id']], false);}
		if ($ctype == "catalog"){return $this->model_bytao_pdf->getCatalog();}
		if ($ctype == "forms"){
			$pdf = $this->model_bytao_pdf->getForms($type_id);
			return $pdf;
		}
		if ($ctype == "Information") {
			return $this->model_bytao_pdf->getInformation([$this->request->get['order_id']],$this->request->get['information_id'] ,true);
		}
		
		return '';
	}
	
	public function agreement(){
		$agr=[];
		if (isset($this->request->get['order_id'])) {
			$agr['order_id'] = $this->request->get['order_id'];
		}
		
		$this->load->model('bytao/pdf');
		return $this->model_bytao_pdf->getContract($agr,false);
		
	}
	
	public function generateContract($route, $args)
	{
		$this->load->model('bytao/pdf');
		$order_id = $args[0];
		$order_status_id = $args[1];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		if ($order_info) {
			if (!in_array($order_info['order_status_id'], array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status'))) && in_array($order_status_id, array_merge((array)$this->config->get('config_processing_status'), (array)$this->config->get('config_complete_status')))) {
				$agr=[];
				$agr['order_id'] = $order_id;
				$this->model_bytao_pdf->createContract($agr);
			}	
				
		}
		return ;
	}
	
	public function download(): void
	{

		$token = $this->request->get['token'] ?$this->request->get['token']: '';
		if (!$token) {
			exit('Invalid token');
		}

		$this->load->model('bytao/pdf');
		$order_id = $this->model_bytao_pdf->getOrderIdByToken($token);

		if (!$order_id) {
			exit('Not found');
		}

		$file = DIR_PDF . "contracts/satis-sozlesmesi-".$order_id."-".$token.".pdf";
		//$this->log->write('is_file before:'.print_r($file,TRUE));
		if (file_exists($file)) {
			//$this->log->write('is_file:'.print_r($file,TRUE));
			// 5. HEADERS
			header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
			header('Content-Type: application/pdf');
			header('Content-Length: ' . filesize($file));
			header('Content-Disposition: inline; filename="satis-sozlesmesi.pdf"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');

			readfile($file);
			exit;
		}
		
	}

}
