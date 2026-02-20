<?php
namespace Opencart\Admin\Controller\Bytao;
class Dashboard extends \Opencart\System\Engine\Controller {
	
	private $version = '1.0.0';
	private $cPth = 'bytao/dashboard';
	private $C = 'dashboard';
	private $Tkn = 'user_token';
	private $model ;
	
	public function index() {
		$this->load->model('bytao/dashboard');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$results = $this->model_bytao_dashboard->getViewed();

		foreach ($results as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);
			if (is_file(DIR_IMAGE . html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($product_info['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			if ($product_info) {
				$data['vProducts'][] = [
					'image'      => $image,
					'name'    => $product_info['name'],
					'model'   => $product_info['model'],
					'viewed'  => $result['viewed']
				];
			}
		}
		
		
		$results = $this->model_bytao_dashboard->getPurchased();

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			
			$data['pProducts'][] = [
				'image'      => $image,
				'name'     => $result['name'],
				'model'    => $result['model'],
				'quantity' => $result['quantity'],
				'counter' => $result['counter'],
				'total'    => $this->currency->format($result['total'], $this->config->get('config_currency'))
			];
		}
		
		
		
		$data[$this->Tkn] = $this->session->data[$this->Tkn];
		
		return $this->load->view($this->cPth, $data);
	}
	
}