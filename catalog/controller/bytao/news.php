<?php  
namespace Opencart\Catalog\Controller\Bytao;
class News extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/news';
	private $C = 'news';
	
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
	
	public function index():void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';
		
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];
		
		$limit = $this->config->get('config_pagination');
		
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$filter_data = [
			'sort'  => 'n.sort_order',
			'order' => ' ASC',
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		];
		
		$data['witems'] = [];
		$data['newses'] =$this->url->link('bytao/news', 'language=' . $this->config->get('config_language'));
		$item_total = $this->model->{$this->getFunc('getTotal','es')}();
		$items = $this->model->{$this->getFunc('get','es')}($filter_data);
		if ($items) {
			$this->load->model('tool/image');
			foreach ($items as $result) {
				if (isset( $result['image']) && is_file(DIR_IMAGE . $result['image'])) {
					$data['witems'][] = array(
						'title'  => $result['title'],
						'description'  => $result['description'],
						'image' => $this->model_tool_image->resize($result['image'],isset($lData['thumb_width'])?$lData['thumb_width']:400,isset($lData['thumb_height'])?$lData['thumb_height']:400),
					);
				}
			}
			$view = $this->load->view($this->cPth.'_widget', $data);
		} 
		
		$url = '';
		if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
	
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('bytao/news', 'language=' . $this->config->get('config_language') . $url . '&page={page}')
		]);
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($item_total - $limit)) ? $item_total : ((($page - 1) * $limit) + $limit), $item_total, ceil($item_total / $limit));
		
		
		
		
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('bytao/footer');
		$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-news']);
		
		$this->response->setOutput($this->load->view($this->cPth,$data));
	}
	
	public function getwidget(array $lData):string {
		$view='';
		$this->getML('ML');
		
		$filter_data = [
			'sort'  => 'n.sort_order',
			'order' => ' ASC',
			'start' => 0,
			'limit' => 3
		];
		
		$data['witems'] = [];
		$data['newses'] =$this->url->link('bytao/news', 'language=' . $this->config->get('config_language'));
		
		$items = $this->model->{$this->getFunc('get','s')}($filter_data);
		if ($items) {
			$this->load->model('tool/image');
			$data['text_all'] = $this->language->get('text_all');
			$data['title'] = $this->language->get('text_widget_title');
			foreach ($items as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$data['witems'][] = array(
						'title'  => $result['title'],
						'description'  => $result['description'],
						'image' => $this->model_tool_image->resize($result['image'],isset($lData['thumb_width'])?$lData['thumb_width']:400,isset($lData['thumb_height'])?$lData['thumb_height']:400),
					);
				}
			}
			$view = $this->load->view($this->cPth.'_widget', $data);
		} 
		return $view;
	}
	
}
?>