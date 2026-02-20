<?php
namespace Opencart\Catalog\Model\Bytao;
class Contract extends \Opencart\System\Engine\Model {
	public function generatePdf(int $order_id): void
	{
		$this->log->write('generatePdf:' . $order_id);

		// idempotent
		if ($this->getPdfToken($order_id)) {
			return;
		}

		$this->load->model('account/order');
		$this->load->model('catalog/information');

		$order = $this->model_account_order->getOrder($order_id);
		if (!$order)
			return;

		$information_id = (int)$this->config->get('config_checkout_id');
		$information = $this->model_catalog_information->getInformation($information_id);
		if (!$information)
			return;

		$token = $this->createPdfToken($order_id);

		$data = [
			'customer_name' => $order['firstname'] . ' ' . $order['lastname'],
			'order_id'      => $order_id,
			'order_date'    => date('d.m.Y', strtotime($order['date_added'])),
			'store_name'    => $this->config->get('config_name'),
			'contract_text' => html_entity_decode($information['description'], ENT_QUOTES, 'UTF-8')
		];

		$html = $this->load->view('bytao/pdf/contract', $data);

		require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');

		$pdf = new \TCPDF('P','mm','A4',true,'UTF-8',false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetFont('dejavusans','',10);
		$pdf->AddPage();
		$pdf->writeHTML($html);

		if (ob_get_length())
			ob_end_clean();

		$dir = DIR_PDF . 'contracts/';
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
			@touch($dir . 'index.html');
		}

		$file = $dir . "satis-sozlesmesi-{$order_id}-{$token}.pdf";
		$pdf->Output($file, 'F');

		$this->log->write('PDF CREATED:' . $file);
	}
	

	public function generatePdfX(int $order_id): void
	{
		$this->log->write('generatePdf:' . $order_id);

		if (!$order_id) {
			return;
		}

		// Token al / üret (idempotent)
		$token = $this->getPdfToken($order_id);
		if (!$token) {
			$token = $this->createPdfToken($order_id);
		}

		$base_dir = DIR_PDF . 'contracts/';

		if (!is_dir($base_dir)) {
			mkdir($base_dir, 0775, true);
			@touch($base_dir . 'index.html');
		}

		$file = $base_dir . 'satis-sozlesmesi-' . $order_id . '-' . $token . '.pdf';
		$this->log->write('TCPDF about to write file:' . $file);

		if (is_file($file)) {
			return;
		}


		$this->load->model('account/order');
		$this->load->model('catalog/information');

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
			'contract_text' => html_entity_decode($information['description'], ENT_QUOTES, 'UTF-8')
		];

		//$html = $this->load->view('bytao/pdf/contract', $data);
		$html = '<h1>PDF TEST</h1><p>Order ID: ' . $order_id . '</p>';
		$contract_text = html_entity_decode($information['description'], ENT_QUOTES, 'UTF-8');
		$contract_text = preg_replace('#<(script|style|iframe|img)[^>]*>.*?</\1>#si','',$contract_text);
		
		
		require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');

		$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetAutoPageBreak(true, 15);
		$pdf->setCellHeightRatio(1.25);

		$pdf->setFontSubsetting(true);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');

		if (ob_get_length()) {
			ob_end_clean();
		}

		$pdf->Output($file, 'F');


		$this->log->write('generated Pdf:' . $file);
	}

	public function getPdfToken(int $order_id): string
	{
		$this->log->write('getPdfToken:'.$order_id);
		$query = $this->db->query("SELECT token FROM `" . DB_PREFIX . "order_pdf` WHERE order_id = '" . (int)$order_id . "'");
		return $query->num_rows ? $query->row['token'] : '';
	}

	public function createPdfToken(int $order_id, string $type = 'contract'): string
	{
		$token = bin2hex(random_bytes(16)); // 32 char
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_pdf` SET order_id = '" . (int)$order_id . "', token = '" . $this->db->escape($token) . "',type = '" . $this->db->escape($type) . "', date_added = NOW()
    ");
	$this->log->write('createPdfToken:'.$token);
		return $token;
	}

	public function isValidPdfToken(int $order_id, string $token): bool
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_pdf` WHERE order_id = '" . (int)$order_id . "' AND token = '" . $this->db->escape($token) . "' LIMIT 1 ");
		$this->log->write('isValidPdfToken:'.$token);
		return (bool)$query->num_rows;
	}

}