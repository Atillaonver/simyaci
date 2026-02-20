<?php
namespace Opencart\Catalog\Controller\Bytao;

class Forms extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('bytao/forms');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->home()
		];

		
		if (isset($this->request->get['forms_id'])) {
			$forms_id = (int)$this->request->get['forms_id'];
		} else {
			$forms_id = 0;
		}


		
		$this->load->model('bytao/forms');

		$forms_info = $this->model_bytao_forms->getForms($forms_id);

		if ($forms_info) {
			$this->document->setTitle($forms_info['meta_title']);
			$this->document->setDescription($forms_info['meta_description']);
			$this->document->setKeywords($forms_info['meta_keyword']);

			$data['breadcrumbs'][] = [
				'text' => $forms_info['title'],
				'href' => $this->url->link('bytao/forms', 'language=' . $this->config->get('config_language') . '&forms_id=' .  $forms_id)
			];

			$data['heading_title'] = $forms_info['title'];

			$data['description'] = html_entity_decode($forms_info['description'], ENT_QUOTES, 'UTF-8');
			$data['formData'] = unserialize($forms_info['formdata']);
			
			$data['HTTP_IMAGE'] = HTTPS_IMAGE;
			
			$data['himage'] = !isset($forms_info['himage'])?(!$this->config->get('config_header_image')?$this->config->get('config_header_image'):''):$forms_info['himage'];
			
			$data['fimage'] = !isset($forms_info['fimage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$forms_info['fimage'];
			
			$data['timage'] = !isset($forms_info['timage'])?(!$this->config->get('config_footer_image')?$this->config->get('config_footer_image'):''):$forms_info['timage'];
			

			$data['continue'] = $this->url->home();
			$data['action'] = $this->url->link('bytao/forms.submit', 'language=' . $this->config->get('config_language'));
			
			
			$data['forms_id'] = $forms_id;
			$data['toDate'] = date($this->language->get('date_format_short'));
			$data['customer']['name'] = $this->customer->isLogged()?$this->customer->getFirstName().''.$this->customer->getLastName():'';
			$data['customer']['tel'] = $this->customer->isLogged()?$this->customer->getTelephone():'';
			$data['customer']['email'] = $this->customer->isLogged()?$this->customer->getEmail():'';
			$data['customer']['homeopath'] = 'Dr. Kudret Parpar';
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$hData = [
					'ctrl'   => 'forms',
					'route'   => 'bytao/forms',
					'cdata'  => [],
					'tdata'  => [],
					'hmenu'  => false,
					'hfooter'=> false
				];
			$data['footer'] = $this->load->controller('bytao/footer',$hData);
			$data['header'] = $this->load->controller('bytao/header',$hData);
		
			$this->response->setOutput($this->load->view('bytao/forms', $data));
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('bytao/forms', 'language=' . $this->config->get('config_language') . '&forms_id=' . $forms_id)
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
			
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function submit():void{
		$json = [];
		$this->load->language('bytao/forms');
		$this->load->model('bytao/forms');
		
		$required ='"'.implode('","',$this->request->post['required']).'"';

		foreach($this->request->post as $key => $val){
			if (in_array($key, $this->request->post['required'])) {
				if (!oc_validate_length($this->request->post[$key], 3, 25)) {
					$json['error'][$key] = $this->language->get('error_input');
				}
			}	
		}
		if (!$json) {
			$this->model_bytao_forms->submitForms($this->request->post);
			$filterData=[
				"ctype"		=>	"forms",
				"type_id"	=>	$this->request->post['forms_id']
			];
			
			$forms_id = $this->request->post['forms_id'];
			$json['pdf'] = $pdf = $this->load->controller('bytao/pdf.generate',$filterData);
			/*
			//$mailgun = new MailgunClient($apiKey, $domain);
			
			require_once DIR_SYSTEM . 'library/mailgun.php';
			$mail = new \Opencart\System\Library\MailgunClient($this->config->get('config_mail_mailgun_api'), 'bytao.net.tr');
	        $to = $this->customer->getEmail();
	        $subject = 'OpenCart Mailgun Test (PDF Ekli)';
	        $message = 'Bu bir test e-postasıdır, ekte PDF bulunmaktadır.';
	        $from = 'noreply@bytao.net.tr';

	        // Eklenmek istenen dosya(lar)
	        

	        $result = $mail->sendEmail($to, $subject, $message, $from, $attachments);
	        
	        $attachments = [
	            DIR_CDN . 'forms/forms-'.$forms_id.'-'.$this->customer->getId(), // OpenCart'ta `upload/` klasörüne eklenmiş bir PDF
	            DIR_CDN . 'forms/forms-'.$forms_id.'-'.$this->customer->getId() // Eğer indirme klasöründe bir dosyan varsa
	        ];
	        
			//$mail = new \Opencart\System\Library\Mailgun\Mailgunclient($this->config->get('config_mail_mailgun_api'), 'bytao.net.tr');
			// Use the Mailgun class from mailgun/mailgun-php v4.2
			
			//$mg = Mailgun::create(getenv('API_KEY') ?: $this->config->get('config_mail_mailgun_api'), 'https://api.eu.mailgun.net'); 
			//$mg = Mailgun::create(getenv('API_KEY') ?: $this->config->get('config_mail_mailgun_api')); 

			// Compose and send your message.
			$result = $mg->messages()->send(
				'bytao.net.tr',
				[
					'from' => 'Mailgun Sandbox <postmaster@bytao.net.tr>',
					'to' => 'Atilla Onver <atillaonver@hotmail.com>',
					'subject' => 'Hello Atilla Onver',
					'text' => 'Congratulations Atilla Onver, you just sent an email with Mailgun! You are truly awesome!',
					'attachment'	=> $attachments
				]
			);*/

			
	        
			
			$json['success'] = $this->language->get('text_success');
		}	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function info(): void {
		if (isset($this->request->get['forms_id'])) {
			$forms_id = (int)$this->request->get['forms_id'];
		} else {
			$forms_id = 0;
		}

		$this->load->model('bytao/forms');

		$forms_info = $this->model_bytao_forms->getForms($forms_id);

		if ($forms_info) {
			$data['title'] = $forms_info['title'];
			$data['description'] = html_entity_decode($forms_info['description'], ENT_QUOTES, 'UTF-8');
			$data['formData'] = unserialize($forms_info['formdata']);

			$this->response->addHeader('X-Robots-Tag: noindex');
			$this->response->setOutput($this->load->view('byta/forms_info', $data));
		}
	}
	
	public function infoJ(): void {
		$json = [];
			   
		if (isset($this->request->get['forms_id'])) {
			$forms_id = (int)$this->request->get['forms_id'];
		} else {
			$forms_id = 0;
		}

		$this->load->model('bytao/forms');

		$forms_info = $this->model_bytao_forms->getForms($forms_id);

		if ($forms_info) {
			$json['title'] = $forms_info['title'];
			$data['description'] = html_entity_decode($forms_info['description'], ENT_QUOTES, 'UTF-8');
			$data['formData'] = unserialize($forms_info['formdata']);
			$json['view'] = $this->load->view('bytao/forms_info', $data);
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}