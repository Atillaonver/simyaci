<?php
namespace Opencart\Catalog\Controller\bytao;
class Contact extends \Opencart\System\Engine\Controller {
	
	public function index(): void {
		$this->load->language('bytao/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/contact', 'language=' . $this->config->get('config_language'))
		];

		$data['send'] = $this->url->link('bytao/bridge|contact', 'language=' . $this->config->get('config_language'));
		
		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize(html_entity_decode($this->config->get('config_image'), ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['cemail'] = $this->config->get('config_email');
		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');
		
		$data['locations'] = [];

		$this->load->model('localisation/location');

		foreach ((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation((int)$location_id);

			if ($location_info) {
				if (is_file(DIR_IMAGE . html_entity_decode($location_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($location_info['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
				} else {
					$image = '';
				}

				$data['locations'][] = [
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				];
			}
		}

		$data['name'] = $this->customer->getFirstName();
		$data['email'] = $this->customer->getEmail();

		// Captcha
		$this->load->model('setting/extension');

		$extension_info = $this->model_setting_extension->getExtensionByCode('captcha', $this->config->get('config_captcha'));

		if ($extension_info && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/' . $extension_info['extension'] . '/captcha/' . $extension_info['code']);
		} else {
			$data['captcha'] = '';
		}
		
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';
		

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$hData = [
					'ctrl'   => 'contact',
					'route'   => 'bytao/contact',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
		$data['footer'] = $this->load->controller('bytao/footer',$hData);
		$data['header'] = $this->load->controller('bytao/header',$hData);

		$this->response->setOutput($this->load->view('bytao/contact', $data));
	}

	public function send(): void {
		$this->load->language('bytao/contact');
		$json = [];

		$keys = [
			'name',
			'email',
			'enquiry'
		];

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}

		if (isset($this->request->post['token'])) {
			$resp = $this->load->controller('google/recaptcha|response',$this->request->post);
			$this->log->write('Array:'.print_r($resp,TRUE));
			//$json['error']['email'] = $this->language->get('error_email');
		}
		
		if ((Helper\Utf8\strlen($this->request->post['name']) < 3) || (Helper\Utf8\strlen($this->request->post['name']) > 32)) {
			$json['error']['name'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		if ((Helper\Utf8\strlen($this->request->post['enquiry']) < 10) || (Helper\Utf8\strlen($this->request->post['enquiry']) > 3000)) {
			$json['error']['enquiry'] = $this->language->get('error_enquiry');
		}
		
		

		// Captcha
		$this->load->model('setting/extension');

		$extension_info = $this->model_setting_extension->getExtensionByCode('captcha', $this->config->get('config_captcha'));

		if ($extension_info && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/' . $extension_info['extension'] . '/captcha/' . $extension_info['code'] . '|validate');

			if ($captcha) {
				$json['error']['captcha'] = $captcha;
			}
		}

		if (!$json) {
			if ($this->config->get('config_mail_engine')) {
				$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($this->config->get('config_email'));
				// Less spam and fix bug when using SMTP like sendgrid.
				$mail->setFrom($this->config->get('config_email'));
				$mail->setReplyTo($this->request->post['email']);
				$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
				$mail->setText($this->request->post['enquiry']);
				$mail->send();
			}

			$json['redirect'] = $this->url->link('bytao/contact|success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function success(): void {
		$this->load->language('bytao/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('bytao/contact', 'language=' . $this->config->get('config_language'))
		];

		$data['text_message'] = $this->language->get('text_message');

		$data['continue'] = $this->url->home();
		
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] = $this->config->get('config_header_image')?$this->config->get('config_header_image'):'';
		
			

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('bytao/footer');
		$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-contact']);

		$this->response->setOutput($this->load->view('bytao/success', $data));
	}
}
