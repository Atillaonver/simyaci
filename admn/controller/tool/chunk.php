<?php
namespace Opencart\Admin\Controller\Tool;

class Chunk extends \Opencart\System\Engine\Controller
{
	public function saveX(): void
	{
		$raw = file_get_contents('php://input');
		$data = json_decode($raw, true);

		if (!$data) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([
				'status'  => 'error',
				'message' => 'Invalid JSON'
			]));
			return;
		}

		$index = (int)($data['index'] ?? 0);
		$total = (int)($data['total'] ?? 0);
		$chunk = (string)($data['data'] ?? '');

		// Chunk dosyasını geçici dizinde tut
		$file = DIR_CACHE . '/chunk_buffer.json';

		$chunks = [];
		if (file_exists($file)) {
			$chunks = json_decode(file_get_contents($file), true) ?: [];
		}
		$chunks[$index] = $chunk;
		file_put_contents($file, json_encode($chunks));

		// Henüz tüm chunklar gelmediyse
		if (count($chunks) < $total) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['status' => 'part']));
			return;
		}

		// Tüm chunklar geldi → birleştir
		ksort($chunks);
		$combined = implode('', $chunks);
		unlink($file);

		$formData = json_decode($combined, true);

		// Eksik config alanlarını boş string ile tamamla
		$defaults = [
			'config_meta_title' => '',
			'config_name'       => '',
			'config_owner'      => ''
		];
		$formData = array_merge($defaults, $formData ?? []);

		// !!! Kritik: request->post içine yaz
		$this->request->post = $formData;

		$action = $this->request->get['action'] ?? '';
		if (!$action) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([
				'status'  => 'error',
				'message' => 'No action defined'
			]));
			return;
		}

		// Controller çağrısı
		$result = $this->load->controller($action, $formData);

		if (empty($result)) {
			$result = $this->response->getOutput();
		}

		$this->response->setOutput('');

		$decoded = json_decode($result, true);
		$payload = (json_last_error() === JSON_ERROR_NONE) ? $decoded : ['html' => $result];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([
			'status' => 'done',
			'result' => $payload
		]));
	}
	
	public function save(): void
	{
		$raw = file_get_contents('php://input');
		$data = json_decode($raw, true);

		if (!$data) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([
				'status'  => 'error',
				'message' => 'Invalid JSON'
			]));
			return;
		}

		$index = (int)($data['index'] ?? 0);
		$total = (int)($data['total'] ?? 0);
		$chunk = (string)($data['data'] ?? '');

		// Chunk dosyasını cache dizininde tut
		$file = DIR_CACHE . 'chunk_buffer.json';

		$chunks = [];
		if (file_exists($file)) {
			$chunks = json_decode(file_get_contents($file), true) ?: [];
		}
		$chunks[$index] = $chunk;
		file_put_contents($file, json_encode($chunks));

		if (count($chunks) < $total) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['status' => 'part']));
			return;
		}

		// Tüm chunklar geldi → birleştir ve temizle
		ksort($chunks);
		$combined = implode('', $chunks);
		@unlink($file);

		$formData = json_decode($combined, true) ?: [];

		// Eksik config alanlarını boş string ile tamamla (opsiyonel)
		$defaults = [
			'config_meta_title' => '',
			'config_name'       => '',
			'config_owner'      => ''
		];
		$formData = array_merge($defaults, $formData);

		// 1) [] suffix’li keyleri normalize et → array
		foreach ($formData as $key => $value) {
			if (substr($key, -2) === '[]') {
				$base = substr($key, 0, -2);
				if (!is_array($value)) {
					$value = [$value];
				}
				if (isset($formData[$base])) {
					$formData[$base] = array_merge((array)$formData[$base], $value);
				} else {
					$formData[$base] = $value;
				}
				unset($formData[$key]);
			}
		}

		// 2) Tekil alanlar string kalır, çoklu seçim alanları array olur
		foreach ($formData as $key => $value) {
			// Eğer zaten array ise dokunma (çoklu seçim)
			if (is_array($value))
				continue;

			// Çoklu seçim alanları (status, languages, currencies, display vs.)
			if (preg_match('/(_status|_languages|_currencies|_display)$/', $key)) {
				$formData[$key] = ($value === '' ? [] : [$value]);
			} else {
				// Tekil alanlar string olarak kalır
				$formData[$key] = (string)$value;
			}
		}

		// 3) POST zorlaması ve senkronizasyon
		$this->request->server['REQUEST_METHOD'] = 'POST';
		$this->request->post = $formData;
		$_POST = $formData;

		// Action kontrolü
		$action = $this->request->get['action'] ?? '';
		if (!$action) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([
				'status'  => 'error',
				'message' => 'No action defined'
			]));
			return;
		}

		// Controller çağrısı
		$result = $this->load->controller($action, $formData);
		if (empty($result)) {
			$result = $this->response->getOutput();
		}

		$this->response->setOutput('');

		// Yanıtı JSON olarak parse etmeyi dene
		$decoded = json_decode($result, true);
		$payload = (json_last_error() === JSON_ERROR_NONE) ? $decoded : ['html' => $result];

		// Son chunk yanıtı
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([
			'status' => 'done',
			'result' => $payload
		]));
	}
	
	
	
	
	
	
	
	
	
	
	private function respond($arr)
	{
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($arr));
	}
}