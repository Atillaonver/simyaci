<?php
namespace Opencart\Catalog\Controller\Information;
class Information extends \Opencart\System\Engine\Controller {
	public function index() {
		
		$this->load->language('information/information');
		
		if (isset($this->request->get['information_id'])) {
			$this->load->model('catalog/information');
			
			$information_id = (int)$this->request->get['information_id'];
			$information_info = $this->model_catalog_information->getInformation($information_id);
			
			$data['breadcrumbs'] = [];
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->home()
			];
			
			
		} else {
			$information_id = 0;
		}
		
		if ($information_info) {
			
			$headers = apache_request_headers();
			$is_ajax = (isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest');

			if ($is_ajax) {
				
				$hData = [
					'content'   => $information_info['description'],
					'route'   => 'information/information',
				];
				
				$data['description'] = $this->load->controller('checkout/confirm.orderconfirm',$hData);
				$data['title'] = $information_info['title'];
				$this->response->addHeader('X-Robots-Tag: noindex');
				$this->response->setOutput($this->load->view('information/information_info', $data));
			}
			else
			{
				$this->document->setTitle($information_info['meta_title']);
				$this->document->setDescription($information_info['meta_description']);
				$this->document->setKeywords($information_info['meta_keyword']);
				$data['breadcrumbs'][] = [
					'text' => $information_info['title'],
					'href' => $this->url->link('information/information', 'language=' . $this->config->get('config_language') . '&information_id=' .  $information_id)
				];
				$data['heading_title'] = $information_info['title'];

				$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
				$data['page_script'] =  html_entity_decode($information_info['page_script'], ENT_QUOTES, 'UTF-8');

				$data['HTTP_IMAGE'] = HTTPS_IMAGE;

				$data['himage'] = !isset($information_info['himage'])?(!$this->config->get('config_header_image')?by_move($this->config->get('config_header_image')):''):by_move($information_info['himage']);

				$data['fimage'] = !isset($information_info['fimage'])?(!$this->config->get('config_footer_image')?by_move($this->config->get('config_footer_image')):''):by_move($information_info['fimage']);

				$data['timage'] = !isset($information_info['timage'])?(!$this->config->get('config_footer_image')?by_move($this->config->get('config_footer_image')):''):by_move($information_info['timage']);


				$data['continue'] = $this->url->home();

				$data['column_left'] = $this->load->controller('common/column_left');
				$data['column_right'] = $this->load->controller('common/column_right');
				$data['content_top'] = $this->load->controller('common/content_top');
				$data['content_bottom'] = $this->load->controller('common/content_bottom');
				
				$cHead = $this->config->get('config_store_head');
				if ((int)$cHead) {
					$data['footer'] = $this->load->controller('common/footer');
					$data['header'] = $this->load->controller('common/header');
				} else {
					$hData = [
						'ctrl'   => 'information',
						'route'   => 'information/information',
						'cdata'  => [],
						'tdata'  => [],
						'hmenu'  => false,
						'hfooter'=> false
					];
					$data['footer'] = $this->load->controller('bytao/footer',$hData);
					$data['header'] = $this->load->controller('bytao/header',$hData);
				}

				$this->response->setOutput($this->load->view('information/information', $data));
			}
			
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'language=' . $this->config->get('config_language') . '&information_id=' . $information_id)
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
			
			$cHead = $this->config->get('config_store_head');
			if ((int)$cHead) {
				$data['footer'] = $this->load->controller('common/footer');
				$data['header'] = $this->load->controller('common/header');
			} else {
				$hData = [
				'ctrl'   => 'not_found',
					'route'   => 'error/not_found',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
				$data['footer'] = $this->load->controller('bytao/footer',$hData);
				$data['header'] = $this->load->controller('bytao/header',$hData);
			}
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function info(): void {
		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$this->load->model('catalog/information');

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$data['title'] = $information_info['title'];
			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$this->response->addHeader('X-Robots-Tag: noindex');
			$this->response->setOutput($this->load->view('information/information_info', $data));
		}
	}
	
	public function infoJ(): void {
		$json = [];
			   
		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$this->load->model('catalog/information');

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$json['title'] = $information_info['title'];
			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
			$json['view'] = $this->load->view('information/information_info', $data);
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}