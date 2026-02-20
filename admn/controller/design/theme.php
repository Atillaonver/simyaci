<?php
namespace Opencart\Admin\Controller\Design;
class Theme extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('design/theme');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/theme', 'user_token=' . $this->session->data['user_token'])
		];

		$data['stores'] = [];

		$this->load->model('setting/store');

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = [
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			];
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['store_id'] = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/theme', $data));
	}

	public function history(): void {
		$this->load->language('design/theme');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$data['histories'] = [];

		$this->load->model('design/theme');
		$this->load->model('setting/store');

		$history_total = $this->model_design_theme->getTotalThemes();

		$results = $this->model_design_theme->getThemes(($page - 1) * $limit, $limit);

		foreach ($results as $result) {
			$store_info = $this->model_setting_store->getStore($result['store_id']);

			if ($store_info) {
				$store = $store_info['name'];
			} else {
				$store = '';
			}

			$data['histories'][] = [
				'store_id'   => $result['store_id'],
				'store'      => ($result['store_id'] ? $store : $this->language->get('text_default')),
				'route'      => $result['route'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'       => $this->url->link('design/theme.template', 'user_token=' . $this->session->data['user_token']),
				'delete'     => $this->url->link('design/theme.delete', 'user_token=' . $this->session->data['user_token'] . '&theme_id=' . $result['theme_id'])
			];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $history_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('design/theme.history', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($history_total - $limit)) ? $history_total : ((($page - 1) * $limit) + $limit), $history_total, ceil($history_total / $limit));

		
		$this->response->setOutput($this->load->view('design/theme_history', $data));
	}

	public function path(): void {
		$this->load->language('design/theme');
		$this->load->model('design/theme');
		$themes = [];
		$results = $this->model_design_theme->getThemes(0,0);
		foreach($results as $theme){
			$themes[$theme['route']]=1;
		}
		
		$json = [];

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		// Default templates
		$json['directory'] = [];
		$json['file'] = [];

		$directory = DIR_CATALOG . 'view/template';

		if (substr(str_replace('\\', '/', realpath($directory . '/' . $path)), 0, strlen($directory)) == $directory) {
			// We grab the files from the default template directory
			$files = glob(rtrim(DIR_CATALOG . 'view/template/' . $path, '/') . '/*');

			foreach ($files as $file) {
				if (is_dir($file)) {
					$json['directory'][] = [
						'name' => basename($file),
						'path' => trim($path . '/' . basename($file), '/')
					];
				}

				if (is_file($file)) {
					$json['file'][] = [
						'name' => basename($file),
						'path' => trim($path . '/' . basename($file), '/'),
						'edited'=> (isset($themes[trim($path . '/' . basename($file,'.twig'), '/')])?1:0)
					];
				}
			}
		}

		if (!$path) {
			$json['directory'][] = [
				'name' => $this->language->get('text_extension'),
				'path' => 'extension',
			];
		}

		// Extension templates
		$json['extension'] = [];

		// List all the extensions
		if ($path == 'extension') {
			$directories = glob(DIR_EXTENSION . '*', GLOB_ONLYDIR);

			foreach ($directories as $directory) {
				$json['extension']['directory'][] = [
					'name' => basename($directory),
					'path' => 'extension/' . basename($directory)
				];
			}
		}

		// List extension sub directories directories
		if (substr($path, 0, 10) == 'extension/') {
			$route = '';

			$part = explode('/', $path);

			$extension = $part[1];

			unset($part[0]);
			unset($part[1]);

			if (isset($part[2])) {
				$route = implode('/', $part);
			}

			$safe = true;

			if (substr(str_replace('\\', '/', realpath(DIR_EXTENSION . $extension)), 0, strlen(DIR_EXTENSION)) != DIR_EXTENSION) {
				$safe = false;
			}

			$directory = DIR_EXTENSION . $extension . '/catalog/view/template';

			if (substr(str_replace('\\', '/', realpath($directory . '/' . $route)), 0, strlen($directory)) != $directory) {
				$safe = false;
			}

			if ($safe) {
				$files = glob(rtrim(DIR_EXTENSION . $extension . '/catalog/view/template/' . $route, '/') . '/*');

				sort($files);

				foreach ($files as $file) {
					if (is_dir($file)) {
						$json['extension']['directory'][] = [
							'name' => basename($file),
							'path' => $path . '/' . basename($file)
						];
					}

					if (is_file($file)) {
						$json['extension']['file'][] = [
							'name' => basename($file),
							'path' => $path . '/' . basename($file),
							'edited'=> (isset($themes[$path . '/' . basename($file,'.twig')])?1:0)
						];
					}
				}
			}
		}

		if ($path) {
			$json['back'] = [
				'name' => $this->language->get('button_back'),
				'path' => urlencode(substr($path, 0, strrpos($path, '/'))),
			];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function template(): void {
		$this->load->language('design/theme');

		$json = [];
		
		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		// Default template load
		$directory = DIR_CATALOG . 'view/template';

		if (is_file($directory . '/' . $path) && (substr(str_replace('\\', '/', realpath($directory . '/' . $path)), 0, strlen($directory)) == $directory)) {
			$json['code'] = file_get_contents(DIR_CATALOG . 'view/template/' . $path);
		}

		// Extension template load
		if (substr($path, 0, 10) == 'extension/') {
			$part = explode('/', $path);

			$extension = $part[1];

			unset($part[0]);
			unset($part[1]);

			$route = implode('/', $part);

			$safe = true;

			if (substr(str_replace('\\', '/', realpath(DIR_EXTENSION . $extension)), 0, strlen(DIR_EXTENSION)) != DIR_EXTENSION) {
				$safe = false;
			}

			$directory = DIR_EXTENSION . $extension . '/catalog/view/template';

			if (substr(str_replace('\\', '/', realpath($directory . '/' . $route)), 0, strlen($directory)) != $directory) {
				$safe = false;
			}

			if ($safe && is_file($directory . '/' . $route)) {
				$json['code'] = file_get_contents($directory . '/' . $route);
			}
		}

		// Custom template load
		$this->load->model('design/theme');
		$json['path'] = $path;
		$path=str_replace('.twig','',$path);
		
		$theme_info = $this->model_design_theme->getTheme($path);
		if ($theme_info) {
			$json['url'] = $this->url->link('design/theme.preview', 'user_token=' . $this->session->data['user_token'].'&path='.$path);
			$json['code'] = html_entity_decode($theme_info['code']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function save(): void {
		$this->load->language('design/theme');

		$json = [];

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'design/theme')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (substr($path, -5) != '.twig') {
			$json['error'] = $this->language->get('error_twig');
		}

		if (!$json) {
			$this->load->model('design/theme');

			$pos = strpos($path, '.');

			$this->model_design_theme->editTheme(($pos !== false) ? substr($path, 0, $pos) : $path, $this->request->post['code']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function reset(): void {
		$json = [];

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		$directory = DIR_CATALOG . 'view/template';

		if (is_file($directory . '/' . $path) && (substr(str_replace('\\', '/', realpath($directory . '/' . $path)), 0, strlen($directory)) == $directory)) {
			$json['code'] = file_get_contents(DIR_CATALOG . 'view/template/' . $path);
		}

		// Extension template load
		if (substr($path, 0, 10) == 'extension/') {
			$part = explode('/', $path);

			$extension = $part[1];

			unset($part[0]);
			unset($part[1]);

			$route = implode('/', $part);

			$safe = true;

			if (substr(str_replace('\\', '/', realpath(DIR_EXTENSION . $extension)), 0, strlen(DIR_EXTENSION)) != DIR_EXTENSION) {
				$safe = false;
			}

			$directory = DIR_EXTENSION . $extension . '/catalog/view/template';

			if (substr(str_replace('\\', '/', realpath($directory . '/' . $route)), 0, strlen($directory)) != $directory) {
				$safe = false;
			}

			if ($safe && is_file($directory . '/' . $route)) {
				$json['code'] = file_get_contents($directory . '/' . $route);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete(): void {
		$this->load->language('design/theme');

		$json = [];

		if (isset($this->request->get['theme_id'])) {
			$theme_id = (int)$this->request->get['theme_id'];
		} else {
			$theme_id = 0;
		}

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'design/theme')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('design/theme');

			$this->model_design_theme->deleteTheme($theme_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function preview():void {
		$token = oc_token(64);
		$path = isset($this->request->get['path'])?$this->request->get['path']:'';
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$this->load->model('setting/store');
		$store_info = $this->model_setting_store->getStore($store_id);
		if ($store_info) {
				$this->response->redirect($store_info['url'] . 'index.php?route=bytao/preview&language=' . $this->config->get('config_language') . '&path='.$path);
		} else {
			$this->response->redirect(HTTP_CATALOG . 'index.php?route=bytao/preview&language=' . $this->config->get('config_language') . '&path=' . $path);
		}
		
		
	}
}
