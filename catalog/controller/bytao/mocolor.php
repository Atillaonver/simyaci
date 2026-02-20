<?php
class ControllerBytaoMocolor extends Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/mocolor';
	private $C = 'mocolor';
	private $ID = 'color_id';
	
	
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
		
		$data['logged'] = $this->customer->isLogged();
		if ($data['logged']) {
			$data['groupId'] = $GroupId = $this->customer->getGroupId();
		}else{
			$data['groupId'] = $GroupId = 0;
		}
		
				
		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p2c.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];	
			
		} else {
			$limit = $this->config->get('config_product_limit');
		}

		$data['breadcrumbs'] = array();

		/*$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home('SSL')
		);*/

		$data['currency_code'] = $this->session->data['currency'];
		$data['text_filter'] = $this->language->get('text_filter');
		
		
		if (isset($this->request->get['color_id'])) {
			$color_id = $this->request->get['color_id'];
		} else {
			$color_id = '';
		}
		
		$color_info = $this->model->{$this->getFunc('get')}($color_id);
		
		$data['subs']=array();
		if ($color_info){
			if(count($color_info)>1){
				$data['heading_title']=$this->language->get('heading_title');
				
			}else{
				$data['heading_title']= $color_info[0]['title'];
			}	
			
			foreach($color_info AS $subs){
					$colors=array();
					foreach($subs['subs'] AS $sub){
					
						$image = 'catalog/prod/'.$sub['code'].'.jpg';
						if (is_file(DIR_IMAGE . $image)) {
							$colors[] = array(
								'href'  => $this->url->link('product/product', 'product_code=' . $sub['code']),
								'title'  => $sub['title'],
								'code'  => $sub['code'],
								'image' => $this->model_tool_image->resize($image,900,900)
							);
						}
					}
					$data['subs'][]=array(
						'title' => $subs['title'],
						'colors' => $colors
					);
				}
				
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer',array('part_id'=>0));
			$data['header'] = $this->load->controller('bytao/header',array('menu_id'=>0,'amenu'=>true));
			
			$this->response->setOutput($this->load->view($this->cPth, $data));
		} 
		else 
		{
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

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->home('SSL');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('bytao/footer');
			$data['header'] = $this->load->controller('bytao/header');
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}