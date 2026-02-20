<?php
namespace Opencart\Catalog\Controller\Bytao;
class Document extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('bytao/document');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('bytao/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_downloads'),
			'href' => $this->url->link('bytao/document', 'language=' . $this->config->get('config_language'))
		];

		$this->load->model('bytao/document');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = $this->config->get('config_pagination');

		$data['documents'] = [];

		$results = $this->model_bytao_document->getDocuments();

		foreach ($results as $result) {
			if (is_file(DIR_DOWNLOAD . $result['filename'])) {
				$data['documents'][] = [
					'simage'   => $result['simage'],
					'bimage'   => $result['bimage'],
					'mask'   => $result['mask'],
					'name'   => $result['name'],
					'title'   => $result['title'],
					'href'       => $this->url->link('bytao/document|download', 'language=' . $this->config->get('config_language').'&document_id='.$result['document_id'])
				];
			}
		}
		$data['HTTP_IMAGE'] = HTTPS_IMAGE;
		$data['himage'] =$this->config->get('config_header_image');
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('bytao/footer');
		$data['header'] = $this->load->controller('bytao/header',['root'=>'bytao-page']);

		$this->response->setOutput($this->load->view('bytao/document', $data));
	}

	public function download(): void {
		$this->load->model('bytao/document');

		if (isset($this->request->get['document_id'])) {
			$document_id = (int)$this->request->get['document_id'];
		} else {
			$document_id = 0;
		}

		$document_info = $this->model_bytao_document->getDocument($document_id);

		if ($document_info) {
			$file = DIR_DOWNLOAD . $document_info['filename'];
			$mask = basename($document_info['mask']);

			if (!headers_sent()) {
				if (is_file($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}

					readfile($file, 'rb');

					$this->model_bytao_document->addReport($document_id, $this->request->server['REMOTE_ADDR']);

					exit();
				} else {
					exit(sprintf($this->language->get('error_not_found'), basename($file)));
				}
			} else {
				exit($this->language->get('error_headers_sent'));
			}
		} else {
			$this->response->redirect($this->url->link('bytao/document', 'language=' . $this->config->get('config_language')));
		}
	}
}
