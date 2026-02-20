<?php
namespace Opencart\Admin\Controller\Bytao;

class Trivia extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/trivia';
	private $C = 'trivia';
	private $ID = 'trivia_id';
	private $Tkn = 'user_token';
	private $model ;
	
	public function getPth(): string {
		return $this->cPth;
	}
	
	private function getFunc($f='',$addi=''): string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''): void{
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
			
		}
	}
	
	public function install(): void{
		$this->getML('ML');
		$this->model->{$this->getFunc('install')}();
		$this->document->setTitle($this->language->get('heading_title'));
		$this->getList();
		
	}
	
	public function index():void {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['copy'] = $this->url->link($this->cPth.'|copy', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getList();
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C, $data));
	}

	public function list(): void {
		$this->response->setOutput($this->getList());
	}
	
	protected function getList():string  {
		$this->getML('ML');
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['action'] = $this->url->link($this->cPth.'|list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

		$data['items'] =[];
		$this->load->model('tool/image');
		
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$item_total = $this->model->{$this->getFunc('getTotal','s')}();
		$results = $this->model->{$this->getFunc('get','s')}($filter_data);

		foreach ($results as $result) {
			if (!empty($result) && is_file(DIR_IMAGE . $result['image'])) {
				$thumb = $this->model_tool_image->resize($result['image'], 100, 100);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$data['items'][] = array(
				$this->ID 			=> $result[$this->ID],
				'name'      => $result['name'],
				'thumb'      => $thumb,
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'status_val'    => $result['status'],
				'edit'      => $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url),
				'manage'      => $this->url->link($this->cPth.'|manage', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&trivia_id=' . $result[$this->ID] . $url),
				'run'      => $this->url->link($this->cPth.'|run', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url),
				'view'      => $this->url->link($this->cPth.'|view', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'='  . $result[$this->ID] . $url),
				'questions'      => $this->url->link($this->cPth.'|quest', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&'.$this->ID.'=' . $result[$this->ID] . $url)
			);
		}


		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

	
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
	
		return $this->load->view($this->cPth.'/'.$this->C.'_list', $data);
	}

	public function save(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post[$this->ID]) {
				$json[$this->ID] = $this->model->{$this->getFunc('add')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit')}((int)$this->request->post[$this->ID], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function form():void {
		$this->getML('ML');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['save'] = $this->url->link($this->cPth.'|save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data[$this->ID] =0;
		$trivia_info =[];
		
		if (isset($this->request->get[$this->ID]) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$trivia_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = $this->request->get[$this->ID];
		}

		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if ($trivia_info) {
			$data['name'] = $trivia_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if ($trivia_info) {
			$data['maxq'] = $trivia_info['maxq'];
		} else {
			$data['maxq'] = '25';
		}
		
		if ($trivia_info) {
			$data['trivia_session'] = $trivia_info['trivia_session'];
		} else {
			$data['trivia_session'] = '1';
		}
		
		if ($trivia_info) {
			$data['board'] = $trivia_info['board'];
		} else {
			$data['board'] = '10,20';
		}
		
		if ($trivia_info) {
			$data['image'] = $trivia_info['image'];
		} else {
			$data['image'] = '';
		}
		
		if ($trivia_info) {
			$data['logo'] = $trivia_info['logo'];
		} else {
			$data['logo'] = '';
		}
		
		if ($trivia_info) {
			$data['trivia'] = $trivia_info['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if ($trivia_info) {
			$data['sponsor'] = $trivia_info['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		

		if ($trivia_info && is_file(DIR_IMAGE . $trivia_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($trivia_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if ($trivia_info && is_file(DIR_IMAGE . $trivia_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($trivia_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if ($trivia_info && is_file(DIR_IMAGE . $trivia_info['trivia'])) {
			$data['thumb_trivia'] = $this->model_tool_image->resize($trivia_info['trivia'], 100, 100);
		} else {
			$data['thumb_trivia'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if ($trivia_info && is_file(DIR_IMAGE . $trivia_info['sponsor'])) {
			$data['thumb_sponsor'] = $this->model_tool_image->resize($trivia_info['sponsor'], 100, 100);
		} else {
			$data['thumb_sponsor'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if ($trivia_info) {
			$data['status'] = $trivia_info['status'];
		} else {
			$data['status'] = true;
		}
		
		if ($trivia_info) {
			$data['time'] = $trivia_info['time'];
		} else {
			$data['time'] = 60;
		}
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_form', $data));
	}
	
	// hatalı
	public function questions() {
		$this->getML('ML');
		$this->document->setTitle($this->language->get('heading_title'));
		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['copy'] = $this->url->link($this->cPth.'|copy', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'|delete', $this->Tkn.'=' . $this->session->data[$this->Tkn]);

		$data['list'] = $this->getListq();
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$this->document->addScript('https://code.jquery.com/ui/1.13.2/jquery-ui.js');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C, $data));

		$data['quests'] = [];

		$filter_data = [];

		$this->load->model('tool/image');
		$quest_total = $this->model->{$this->getFunc('getTotal','Quests')}($this->request->get[$this->ID]);
		$results = $this->model->{$this->getFunc('get','Quests')}($this->request->get[$this->ID],$filter_data);
		
		foreach ($results as $result) {
			
			if (!empty($result) && is_file(DIR_IMAGE . $result['correct_image'])) {
				$thumb = $this->model_tool_image->resize($result['correct_image'], 100, 100);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$data['quests'][] = array(
				'quest_id' => $result['quest_id'],
				'quest'      => $result['quest'],
				'correct'      => $result['correct'],
				'sort_order'      => $result['sort_order'],
				'thumb'      => $thumb,
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'|formq', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&quest_id=' . $result['quest_id'] . '&'.$this->ID.'=' . $this->request->get[$this->ID] . $url)
			);
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		
		$data['sort_title'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=qd.title' . $url);
		$data['sort_status'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=q.status' . $url);

		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}

		
		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $quest_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|listq', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($quest_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($quest_total - $this->config->get('config_pagination_admin'))) ? $quest_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $quest_total, ceil($quest_total / $this->config->get('config_pagination_admin')));
		

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header'); 
		$data['tabstores'] = $this->load->controller('common/stores'); 
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_quest_list', $data));
	}
	
	public function quest():void {
		$this->getML('L');
		$this->document->setTitle($this->language->get('heading_title'));
		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'|questform', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'='.$this->request->get[$this->ID] . $url);
		$data['copy'] = $this->url->link($this->cPth.'|questcopy', $this->Tkn.'=' . $this->session->data[$this->Tkn] .'&'.$this->ID.'='.$this->request->get[$this->ID]. $url);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] .'&'.$this->ID.'='.$this->request->get[$this->ID]. $url);
		$data['delete'] = $this->url->link($this->cPth.'|questdelete', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'='.$this->request->get[$this->ID]);
		
		$data['repair'] = $this->url->link($this->cPth.'|questlist', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'='.$this->request->get[$this->ID]);

		$data['list'] = $this->getQuestList();
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_quest', $data));
	}
	
	public function questform():void {
		$this->getML('ML');
		
		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		);

		$data['save'] = $this->url->link($this->cPth.'|questsave', $this->Tkn.'=' . $this->session->data[$this->Tkn]. '&'.$this->ID.'=' . $this->request->get[$this->ID].$url );
		$data['back'] = $this->url->link($this->cPth.'|quest', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'=' . $this->request->get[$this->ID].$url);
		$data[$this->ID] = $this->request->get[$this->ID];
		
		$quest_info=[];
		
		if (isset($this->request->get['quest_id'])) {
			$data['quest_id']=$this->request->get['quest_id'];
			$quest_info = $this->model->{$this->getFunc('get','Quest')}($this->request->get['quest_id']);
			$data['quest_description']=$this->model->{$this->getFunc('get','QuestDescriptions')}($this->request->get['quest_id']);
		}else{
			$data['quest_description']=[];
			$data['quest_id']=0;
		}
		
		if (!empty($quest_info)) {
			$data['image'] = $this->repareImg($quest_info['image']);
		} else {
			$data['image'] = '';
		}
		
		
		if (!empty($quest_info)) {
			$data['correct_image'] = $this->repareImg($quest_info['correct_image']);
		} else {
			$data['correct_image'] = '';
		}
		
		$this->log->write('correct_image:'.$data['correct_image']);
		
		if (!empty($quest_info)) {
			$data['counter'] = $quest_info['counter'];
		} else {
			$data['counter'] = '60';
		}
		
		if (!empty($quest_info)) {
			$data['sort_order'] = $quest_info['sort_order'];
		} else {
			$data['sort_order'] = '0';
		}
		
		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (!empty($quest_info) && is_file(DIR_IMAGE . $data['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($data['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (!empty($quest_info) && is_file(DIR_IMAGE . $data['correct_image'])) {
			$data['thumb_correct'] = $this->model_tool_image->resize($data['correct_image'], 100, 100);
		} else {
			$data['thumb_correct'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		
		if (!empty($quest_info)) {
			$data['status'] = $quest_info['status'];
		} else {
			$data['status'] = true;
		}
		if (!empty($quest_info)) {
			$data['correct'] = $quest_info['correct'];
		} else {
			$data['correct'] = 'a';
		}
		
		
		
		$this->load->model('bytao/common');
		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C.'_quest_form', $data));
	}
	
	protected function repareImg(string $image = ''):string {
		if(!$image) return '';
		$iPath = $image;
		$PP = $this->session->data['path'];
		
		$expo = explode('/',$image);
		if(count($expo)>1){
			$_expOld =[];
			$_expNew =[];
			foreach($expo as $exp){
				if(!$exp){
					$_expOld[]= $PP.'1';
					$_expNew[]= $PP;
					
				}else{
					$_expOld[]= $exp;
					$_expNew[]= $exp;
				}
			}
			
			$oImg = implode('/',$_expOld);
			$nImg = implode('/',$_expNew);
		}else{
			$oImg = $image;
			$nImg = $image;
		}
		
		
		$_name = explode('.',basename($oImg));
		$name = by_SEO($_name[0]).'.' . $_name[1];
		
		$oldfile = DIR_IMAGE . $oImg;
		$this->log->write('old:'.$oldfile);
		
		$path= '';
		if (is_file($oldfile)) {
			$directories = explode('/', $nImg );
			array_pop($directories);
			
			foreach($directories as $directory){
				if($path == ''){
						$path = by_SEO($directory);
				}
				else
				{
						$path .= '/' . by_SEO($directory);
				}
				if(!is_dir(DIR_IMAGE . $path)){
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}
			$newfile = $path.'/'.$name;
			$this->log->write('New:'.DIR_IMAGE.$newfile);
			
			if (!copy($oldfile, DIR_IMAGE . $newfile)) {
			    $this->log->write('not copied:'. $newfile);
			}else{
				$this->log->write('copied:'. $newfile);
				//unlink(DIR_IMAGE.$_image);
				return $newfile;
			}
			
		}
		return $image;
	}

	public function questsave(): void {
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['quest_description'] as $language_id => $value) {
			if ((oc_strlen(trim($value['quest'])) < 3) || (oc_strlen(trim($value['quest'])) >255)) {
				$this->error['quest'][$language_id] = $this->language->get('error_question');
			}
		}


		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			if (!$this->request->post['quest_id']) {
				$json['quest_id'] = $this->model->{$this->getFunc('add','Quest')}($this->request->post);
			} else {
				$this->model->{$this->getFunc('edit','Quest')}((int)$this->request->post['quest_id'], $this->request->post);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function questdelete(): void {
		$this->getML('ML');
		$json = [];

		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} else {
			$selected = [];
		}

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			
			foreach ($selected as $item_id) {
				$this->model->{$this->getFunc('delete','Quest')}($item_id);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function questlist(): void {
		$this->response->setOutput($this->getQuestList());
	}
	
	protected function getQuestList():string  {
		$this->getML('ML');
		$trivia_id = isset($this->request->get[$this->ID])?$this->request->get[$this->ID]:0;
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'id.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
		

		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}
		if (isset($this->request->get['page'])){$url .= '&page=' . $this->request->get['page'];}
		
		$data['action'] = $this->url->link($this->cPth.'|questlist', $this->Tkn.'=' . $this->session->data[$this->Tkn] .'&'.$this->ID.'='. $trivia_id . $url);

		$data['items'] = [];
		$this->load->model('tool/image');
		
		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		$item_total = $this->model->{$this->getFunc('getTotal','Quests')}($trivia_id);
		$results = $this->model->{$this->getFunc('get','Quests')}($trivia_id,$filter_data);

		foreach ($results as $result) {
			
			if (!empty($result) && is_file(DIR_IMAGE . $result['image'])) {
				$thumb = $this->model_tool_image->resize($result['image'], 100, 100);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$data['items'][] =[
				'quest_id' => $result['quest_id'],
				'quest'      => $result['quest'],
				'correct'      => $result['correct'],
				'sort_order'      => $result['sort_order'],
				'thumb'      => $thumb,
				'status'    => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'      => $this->url->link($this->cPth.'|questform', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&quest_id=' . $result['quest_id'] . '&'.$this->ID.'=' . $trivia_id . $url)
				
			];
		}


		$url = '';
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}
		if (isset($this->request->get['page'])) {$url .= '&page=' . $this->request->get['page'];}

		$data['sort_title'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=id.title' . $url);
		$data['sort_sort_order'] = $this->url->link('catalog/information.list', $this->Tkn.'=' . $this->session->data[$this->Tkn] . '&sort=i.sort_order' . $url);

		$url = '';
		if (isset($this->request->get['sort'])){$url .= '&sort=' . $this->request->get['sort'];}
		if (isset($this->request->get['order'])){$url .= '&order=' . $this->request->get['order'];}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $item_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link($this->cPth.'|questlist', $this->Tkn.'=' . $this->session->data[$this->Tkn].'&'.$this->ID.'='. $trivia_id  . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($item_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($item_total - $this->config->get('config_pagination_admin'))) ? $item_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $item_total, ceil($item_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
	
		return $this->load->view($this->cPth.'/'.$this->C.'_quest_list', $data);
	}

	
	public function run(){
		if (isset($this->request->get[$this->ID])) {
			$this->getML('ML');
			$this->document->setTitle($this->language->get('heading_title'));
			
			$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			
			$data['session'] = $trivia;
			$this->session->data['trivia_session'] = $trivia['trivia_session'];
			$this->model->{$this->getFunc('add','Event')}($this->request->get[$this->ID]);
			$data['maxQ'] = $this->model->{$this->getFunc('get','QuestsTotal')}($this->request->get[$this->ID]);
			
			$data['base'] = $server = HTTP_SERVER;
			$data['URL_IMAGE'] = URL_IMAGE;
			$data['lang'] = $this->language->get('code');
			$data['direction'] = $this->language->get('direction');
			$data[$this->Tkn] = $this->session->data[$this->Tkn];
			$data[$this->ID] = $this->request->get[$this->ID];
			
			
		
			if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
				$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
			} else {
				$data['icon'] = '';
			}
			
			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
			} else {
				$data['logo'] = '';
			}
			
			if (is_file(DIR_IMAGE .  $data['session']['trivia'])) {
				$data['trivia'] = URL_IMAGE . $data['session']['trivia'];
			} else {
				$data['trivia'] = '';
			}
			
			if (is_file(DIR_IMAGE . $data['session']['logo'])) {
				$data['trivia_logo'] = URL_IMAGE . $data['session']['logo'];
			} else {
				$data['trivia_logo'] = '';
			}
			
			if (is_file(DIR_IMAGE . $data['session']['sponsor'])) {
				$data['sponsor'] = URL_IMAGE. $data['session']['sponsor'];
			} else {
				$data['sponsor'] = '';
			}
			
			$this->log->write('config URL:'.$this->config->get('config_url'));
			
			//$data['QR'] = by_QRLink($this->config->get('config_url'),200);
			$data['QR'] =  '';//$this->model->{$this->getFunc('qr')}($this->request->get[$this->ID]);
			
			$data['start']= $this->load->view($this->cPth.'/'.$this->C. '_run_body_start', $data);
			$this->response->setOutput($this->load->view($this->cPth.'/'.$this->C. '_run', $data));
		} else {
			$sort = 'name';
		}
	}
	
	public function start(){
		$json = [];
		$this->getML('ML');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		$data['session'] = $trivia;
		$this->session->data['trivia'] = $trivia;
		
		
		$data['base'] = $server = HTTP_SERVER;
		$data['URL_IMAGE'] = URL_IMAGE;
		$data['lang'] = $this->language->get('code');
		
	
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE . $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'list',0);
		$list =$this->model->{$this->getFunc('getActive','QuestClients')}($this->request->get[$this->ID]);
		$data['lists'] = $this->clientList($list);
		$json['content']['online'] =1;
		$json['content']['title'] = $this->language->get('txt_client_list');
		$json['content']['board'] = $this->load->view($this->cPth.'/'.$this->C. '_run_body_firstboard', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	protected function clientList(array $lData=[]){
		$rString='';
		
		if($lData){
			foreach ($lData as $client) {
				$rString.= '<li>'.str_pad($client['client_order'],2, ' ', STR_PAD_LEFT).'-' .($client['username']?' '.$client['username']:($client['name']?' '.$client['name']:'')).'</li>';
			}
			
			
			
		}
		return $rString;
	}
	
	public function finish(){
		$json = [];
		
		$this->getML('ML');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$data['equal'] = isset($this->request->get['equal'])?$this->request->get['equal']:0;
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		$data['session'] = $trivia;
				
		$data['base'] = $server = HTTP_SERVER;
		$data['URL_IMAGE'] = URL_IMAGE;
	
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE . $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		
		$sort_order = $this->model->{$this->getFunc('get','Quest')}($this->request->get[$this->ID],$data['equal']);
		$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'finish',$sort_order);
		$this->model->{$this->getFunc('next','Session')}($this->request->get[$this->ID]);
		
		$json['content']['finish'] =$this->load->view($this->cPth.'/'.$this->C. '_run_body_finish', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	public function next(){
		$json = [];
		$this->getML('ML');
		
		if (isset($this->request->get['act'])) {
			close:
			
			switch($this->request->get['act']){
				case '0':
					{
						$sort_order = isset($this->request->get['quest'])?(int)$this->request->get['quest']:1;
						$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
						
						if($trivia['maxq'] < $sort_order){
							$json['brd']='brd';
							$json['act'] = 3;
						}else{
							$json['brd']='brd';
							$json['id']='board';
							$json['frsbrd']='board';
							$json['act']=1;
						}
						
						if($sort_order !=1 ){
							$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'close',0);
							$json['title']=$this->language->get('txt_client_topten');
							$data['q'] = $sort_order-1;
							$data['lists'] = $this->model->{$this->getFunc('get','TopTen')}($this->request->get[$this->ID]);
							$json['content']=$this->load->view($this->cPth.'/'.$this->C. '_run_client_topten', $data);
						}else{
							$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'list',0);
							$json['title']=$this->language->get('txt_client_list');
							$data['lists'] = $this->model->{$this->getFunc('get','ActiveQuestClients')}($this->request->get[$this->ID]);
							$json['content']=$this->load->view($this->cPth.'/'.$this->C. '_run_client_list', $data);;
						}
					}
					break;
				
				case '1':// soru
					{
						$this->load->model('tool/image');
						$sort_order = isset($this->request->get['quest'])?(int)$this->request->get['quest']:1;
						$quest = $this->model->{$this->getFunc('get','NQuest')}($sort_order,$this->request->get[$this->ID]);
						
						if (($quest['image']) && is_file(DIR_IMAGE . $quest['image'])) {
							$image = $this->model_tool_image->resize($quest['image'], 670, 600);
						} else {
							$image = '';
						}
						$qData = [];
						
						$qData['image'] =$image;
						$qData['quest'] =$quest['quest'];
						$qData['answer_a'] =$quest['answer_a'];
						$qData['answer_b'] =$quest['answer_b'];
						$qData['answer_c'] =$quest['answer_c'];
						$qData['answer_d'] =$quest['answer_d'];
						
						$json['qview']=$this->load->view($this->cPth.'/'.$this->C. '_run_quest_view', $qData);
						$json['title']= sprintf($this->language->get('txt_quest'),$sort_order);
						$json['time']= $quest['counter'];
						$json['act']=2;
						$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'quest',$sort_order);
						$this->model->{$this->getFunc('update','Quest')}($this->request->get[$this->ID],$quest['quest']);
					}
					break;
				
				case '2': // cevap
					{
						$this->load->model('tool/image');
						$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'close',0);
						$sort_order = isset($this->request->get['quest'])?(int)$this->request->get['quest']:1;
						$quest = $this->model->{$this->getFunc('get','NQuest')}($sort_order,$this->request->get[$this->ID]);
						
						if (is_file(DIR_IMAGE . $quest['correct_image'])){
							$image = $this->model_tool_image->resize($quest['correct_image'], 670, 600);
						} else {
							$image = '';
						}
						
						$qData = [];
						$qData['correct_image'] = $image;
						$qData['quest'] =$quest['quest'];
						$qData['correct'] =$quest['correct'];
						$qData['answer_a'] =$quest['answer_a'];
						$qData['answer_b'] =$quest['answer_b'];
						$qData['answer_c'] =$quest['answer_c'];
						$qData['answer_d'] =$quest['answer_d'];
						
						$json['aview']=$this->load->view($this->cPth.'/'.$this->C. '_run_answer_view', $qData);
						$json['title']= sprintf($this->language->get('txt_answer'),$sort_order);
						
						$this->setToDB();
						
						$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
						
						if($trivia['board']){
							$_board=explode(',',$trivia['board']);
						}
						$json['act'] = 1;
						if (in_array($sort_order, $_board)) {
							$json['act'] = 0;
						}
						
						if($trivia['maxq']<= $sort_order){
							$json['act'] = 0;
						}
						
						$json['qq'] = $sort_order+1;
					}
					break;
				
				case '3':// finish
					{
						$lists = $this->model->{$this->getFunc('get','TopTen')}($this->request->get[$this->ID]);
						$sort_order = isset($this->request->get['quest'])?(int)$this->request->get['quest']:1;
						
						if(isset($lists[0]['quest']) && isset($lists[1]['quest'])&& $lists[0]['quest'] == $lists[1]['quest']&& $lists[0]['time'] == $lists[1]['time']){
							$json['act']=1;
							$json['qq'] = $sort_order+1;
							
						}else{
							$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'finish',$sort_order);
							$this->model->{$this->getFunc('next','Session')}($this->request->get[$this->ID]);
							$json['finish'] = 'brd';
						}
					}
					break;
					
				default:
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nextq(){
		$json = [];
		
		$this->getML('ML');
		$this->load->model('tool/image');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$data['equal'] = isset($this->request->get['equal'])?$this->request->get['equal']:0;
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE .$trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		$this->model->{$this->getFunc('next','Quest')}($this->request->get[$this->ID]);
		$sort_order = $this->model->{$this->getFunc('get','Quest')}($this->request->get[$this->ID],$data['equal']);
		$quest = $this->model->{$this->getFunc('get','NQuest')}($sort_order,$this->request->get[$this->ID]);
		if ($quest){
			
		}
			if (($quest['image']) && is_file(DIR_IMAGE . $quest['image'])) {
				$image = $this->model_tool_image->resize($quest['image'], 670, 600);
			} else {
				$image = '';
			}
			$data['image'] =	$image;
			$data['quest'] =	$quest['quest'];
			$data['answer_a'] =	$quest['answer_a'];
			$data['answer_b'] =	$quest['answer_b'];
			$data['answer_c'] =	$quest['answer_c'];
			$data['answer_d'] =	$quest['answer_d'];
		$data['title']= sprintf($this->language->get('txt_quest'),$sort_order);
		
		$json['content']['question']=$this->load->view($this->cPth.'/'.$this->C. '_run_body_question', $data);
		
		$json['content']['order']	= 	$sort_order;
		$json['time']	= 	$trivia['time'];
		$json['act']	=	2;
		$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'quest',$sort_order);
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nexta(){
		$json = [];
		$this->getML('ML');
		$this->load->model('tool/image');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$data['equal'] = isset($this->request->get['equal'])?$this->request->get['equal']:0;
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
		
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE . $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
						
		$sort_order = $this->model->{$this->getFunc('get','Quest')}($this->request->get[$this->ID],$data['equal']);
		$_board=[];
		if($trivia['board']){
			$_board = explode(',',$trivia['board']);
		}
		if (in_array($sort_order, $_board)) {
			$data['board'] = 1;
		}
		
		if($trivia['maxq']<= $sort_order){
			$data['topboard'] = 1;
		}
	
		$quest = $this->model->{$this->getFunc('get','NQuest')}($sort_order,$this->request->get[$this->ID]);
		if ($quest && is_file(DIR_IMAGE . $quest['correct_image'])){
			$image = $this->model_tool_image->resize($quest['correct_image'], 670, 600);
		} else {
			$image = '';
		}
		
		$data['correct_image'] = $image;
		$data['quest'] =isset($quest['quest'])?$quest['quest']:0;
		$data['correct'] =isset($quest['correct'])?$quest['correct']:'';
		$data['answer_a'] =isset($quest['answer_a'])?$quest['answer_a']:'';
		$data['answer_b'] =isset($quest['answer_b'])?$quest['answer_b']:'';
		$data['answer_c'] =isset($quest['answer_c'])?$quest['answer_c']:'';
		$data['answer_d'] =isset($quest['answer_d'])?$quest['answer_d']:'';
		$data['title']= sprintf($this->language->get('txt_answer'),$sort_order);
		$json['content']['ansver']=$this->load->view($this->cPth.'/'.$this->C. '_run_body_ansver', $data);
		
		$json['qq'] = (int)$sort_order + 1;
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function board(){
		$json = [];
		
		$this->getML('ML');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
				
		$data['base'] = $server = HTTP_SERVER;
		$data['URL_IMAGE'] = URL_IMAGE;
	
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE . $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		$sort_order = $this->model->{$this->getFunc('get','Quest')}($this->request->get[$this->ID]);
		$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'list',0);
		$data['lists'] = $this->model->{$this->getFunc('get','ActiveQuestClients')}($this->request->get[$this->ID]);
		
		$data['q'] = $sort_order;
		$json['content']['title']=$this->language->get('txt_client_list');
		$json['content']['board']=$this->load->view($this->cPth.'/'.$this->C. '_run_body_board', $data);
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function topboard(){
		$json = [];
		
		$this->getML('ML');
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		$data[$this->ID] = $this->request->get[$this->ID];
		$data['equal'] = isset($this->request->get['equal'])?$this->request->get['equal']:0;
		$trivia = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
				
		$data['base'] = $server = HTTP_SERVER;
		$data['URL_IMAGE'] = URL_IMAGE;
	
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = URL_IMAGE. $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = URL_IMAGE. $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE .  $trivia['trivia'])) {
			$data['trivia'] = URL_IMAGE . $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['trivia_logo'] = URL_IMAGE . $trivia['logo'];
		} else {
			$data['trivia_logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['sponsor'])) {
			$data['sponsor'] = URL_IMAGE. $trivia['sponsor'];
		} else {
			$data['sponsor'] = '';
		}
		
		$lists = $this->model->{$this->getFunc('get','TopTen')}($this->request->get[$this->ID]);
		
		$sort_order = isset($this->request->get['quest'])?(int)$this->request->get['quest']:1;
						
		if(isset($lists[0]['quest']) && isset($lists[1]['quest'])&& $lists[0]['quest'] == $lists[1]['quest']&& $lists[0]['time'] == $lists[1]['time']){
			$data['overN'] = 1;
			$data['equal'] = $data['equal'] + 1;
		} else {
			$sort_order = $this->model->{$this->getFunc('get','Quest')}($this->request->get[$this->ID]);
			$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'close',0);
			
		}
		
		$list = $this->model->{$this->getFunc('getActive','QuestClients')}($this->request->get[$this->ID]);
		$data['lists'] = $list;
		$data['q'] = $sort_order;
		$json['content']['title']=$this->language->get('txt_client_topten');
		$json['content']['board']=$this->load->view($this->cPth.'/'.$this->C. '_run_body_lastboard', $data);
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function frsbrd(){
		$json = [];
		if (isset($this->request->get[$this->ID])) {
			$this->getML('M');
			$data['lists'] = $this->model->{$this->getFunc('get','ActiveQuestClients')}($this->request->get[$this->ID]);
			$json['content']=$this->load->view($this->cPth.'/'.$this->C. '_run_client_list', $data);;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function addevent(){
		$json = [];
		if(isset($this->request->get[$this->ID])){
			$this->getML('M');
			$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'close',0);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function eventClose(){
		$json = [];
		if(isset($this->request->get[$this->ID])){
			$this->getML('M');
			$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'close',0);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function online(){
		$json = [];
		
		if(isset($this->request->get[$this->ID])){
			$this->getML('M');
			
			$json['all'] = $this->model->{$this->getFunc('getActive','QuestClientsAns')}($this->request->get[$this->ID],$this->request->get['qq']);{
				
			$vData['participants'] = $this->model->{$this->getFunc('getActive','QuestClients')}($this->request->get[$this->ID]);
			
			$json['participants'] = $this->load->view($this->cPth.'/'.$this->C. '_run_participant_list', $vData);
			$json['event'] = 'participant_list';
			//$json['waitfor'] = $res;
			}
		}
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function closesession(){
		
		$this->getML('M');
		
		$this->model->{$this->getFunc('update','Event')}($this->request->get[$this->ID],'finish',0);
		$this->model->{$this->getFunc('next','Session')}($this->request->get[$this->ID]);
		
		$this->response->redirect($this->url->link($this->cPth.'/'.$this->C. '_run_quest', $this->Tkn.'=' . $this->session->data[$this->Tkn]));
	}
	
	public function dbrepair(){
		$json = [];
		$this->getML('M');
		
		$all = $this->model->{$this->getFunc('getAll','Quests')}();
		foreach($all as $quest){
			$send =[
				'quest_id'=> $quest['quest_id'],
				'image'=> $this->repareImg($quest['image']),
				'correct_image'=> $this->repareImg($quest['correct_image'])
			];
			
			usleep(20000);
			$this->model->{$this->getFunc('repair','Quest')}($send);
			//$this->log->write('Array:'.print_r($send,TRUE));
		}
		$json['success']='all';
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
}