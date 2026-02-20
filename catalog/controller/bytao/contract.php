<?php
namespace Opencart\Catalog\Controller\bytao;
class Contract extends \Opencart\System\Engine\Controller
{
	public function generate(array $args = []): void
	{
		$order_id = $args['order_id'] ?? 0;
		if (!$order_id) { return;}
		$this->log->write('generate:');
		$order_id = $this->session->data['order_id'] ?? 0;
		if (!$order_id) {
			return;
		}

		$this->load->model('account/order');
		$this->load->model('catalog/information');
		$this->load->model('bytao/contract');

		$order = $this->model_account_order->getOrder($order_id);
		if (!$order) {
			return;
		}

		$information_id = (int)$this->config->get('config_checkout_id');
		if (!$information_id) {
			return;
		}

		$information = $this->model_catalog_information->getInformation($information_id);
		if (!$information) {
			return;
		}

		// Token (idempotent)
		$token = $this->model_bytao_contract->getPdfToken($order_id);
		if (!$token) {
			$token = $this->model_bytao_contract->createPdfToken($order_id);
		}

		$products = $this->model_account_order->getProducts($order_id);
		$totals   = $this->model_account_order->getTotals($order_id);

		$total_text = '';
		foreach ($totals as $total) {
			if ($total['code'] === 'total') {
				$total_text = $total['text'];
			}
		}

		// VIEW DATA (HYBRID)
		$data = [
			'customer_name' => $order['firstname'] . ' ' . $order['lastname'],
			'order_id'      => $order_id,
			'order_date'    => date('d.m.Y', strtotime($order['date_added'])),
			'products'      => $products,
			'total'         => $total_text,
			'store_name'    => $this->config->get('config_name'),
			'contract_text' => html_entity_decode($information['description'],ENT_QUOTES,'UTF-8')
			];

		// HTML artık VIEW’dan geliyor
		$html = $this->load->view('bytao/pdf/contract', $data);

		require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');

		$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');

		$base_dir = DIR_PDF . 'contracts/';
		if (!is_dir($base_dir)) {
			mkdir($base_dir, 0775, true);
			@touch($base_dir . 'index.html');
		}

		$file = $base_dir . 'satis-sozlesmesi-' . $order_id . '-' . $token . '.pdf';

		if (!is_file($file)) {
			$pdf->Output($file, 'F');
		}
	}

	public function download(): void
	{

		$token = $this->request->get['token'] ?? '';
		if (!$token) {
			exit('Invalid token');
		}

		$this->load->model('bytao/contract');
		$order_id = $this->model_bytao_contract->getOrderIdByToken($token);

		if (!$order_id) {
			exit('Not found');
		}

		$file = DIR_PDF . "contracts/satis-sozlesmesi-{$order_id}-{$token}.pdf";

		if (!is_file($file)) {
			$this->model_bytao_contract->generatePdf($order_id);
		}

		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="satis-sozlesmesi.pdf"');
		readfile($file);
		exit;
	}


}
