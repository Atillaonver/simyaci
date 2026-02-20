<?php
namespace Opencart\Catalog\Controller\Google;
class Recaptcha extends \Opencart\System\Engine\Controller {
    public function index(): string {
    	
		if (!isset($this->request->get['route'])) return '';
		if (!$this->config->get('recaptcha_google_status')) return '';
		$_route = explode('/',$this->request->get['route']);
		$route = $_route[array_key_last($_route)];
			
		if (!in_array($route, (array)$this->config->get('recaptcha_google_captcha_page'))) return '';
		$this->document->addScript('https://www.google.com/recaptcha/api.js');
		$this->document->addScript('https://www.google.com/recaptcha/enterprise.js?render='.$this->config->get('recaptcha_google_site_key'));
		
		$data['site_key'] = $this->config->get('recaptcha_google_site_key');
		return $this->load->view('google/recaptcha', $data);
		
    }
    
    public function head(): string {
        return $this->load->view('google/recaptcha_head');
    }
    
    public function sform(): string {
		if (!isset($this->request->get['route'])) return '';
		if (!$this->config->get('recaptcha_google_status'))	return '';
		$_route = explode('/',$this->request->get['route']);
		$route = $_route[array_key_last($_route)];
		if (!in_array($route, (array)$this->config->get('recaptcha_google_captcha_page'))) return '';
		$data['site_key'] = $this->config->get('recaptcha_google_site_key');
		
        return $this->load->view('google/recaptcha_form',$data);
    }
    
    public function body(): string {
        $html = '';

        switch ($this->config->get('analytics_google_type')) {
            case 0:
                $html = $this->getGlobalSiteTag($this->config->get('analytics_google_measurement_id'));
                break;
            case 1:
                $html = $this->getGlobalSiteTag($this->config->get('analytics_google_tracking_id'));
                break;
            case 2:
                $html = $this->config->get('analytics_google_code');
                break;
        }

        return $this->load->view('google/recaptcha_body', $data);
    }
    

    private function getGlobalSiteTag($id): string {
    	$html .= '<script src="https://www.google.com/recaptcha/api.js?render=' . $id . '"></script>' . "\n";
    	$html = '<script>' . "\n";
      	$html .= 'function onClick(e) {' . "\n";
        $html .= 'e.preventDefault();' . "\n";
        $html .= 'grecaptcha.ready(function() {' . "\n";
        $html .= '  grecaptcha.execute(\'reCAPTCHA_site_key\', {action: \'submit\'}).then(function(token) {' . "\n";
              // Add your logic to submit to your backend server here.
        $html .= '});' . "\n";
        $html .= '});' . "\n";
      	$html .= '}' . "\n";
  		$html .= '</script>' . "\n";
    	
        return $html;
    }


	public function response(array $gelen = []): array
	{
		$json = [
			'success' => false,
			'score'   => 0
		];

		// ÖNEMLİ: JS'den gelen isim 'g-recaptcha-response' idi.
		// Eğer JS'de 'token' diye göndermediyseniz burayı düzeltmelisiniz.
		$token = $gelen['g-recaptcha-response'] ?? $gelen['token'] ?? null;

		if ($token) {
			$curlData = array(
			'secret'   => $this->config->get('recaptcha_google_secret_key'),
			'response' => $token
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlData));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Bazı sunucularda SSL sertifikası hatası verebilir, test için ekleyebiliriz:
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$curlResponse = curl_exec($ch);
			$curlError = curl_error($ch);
			curl_close($ch);

			if ($curlResponse) {
				$captchaResponse = json_decode($curlResponse, true);

				// Verinin gerçekten gelip gelmediğini kontrol edelim
				if (isset($captchaResponse['success'])) {
					$json['success'] = (bool)$captchaResponse['success'];
					$json['score']   = $captchaResponse['score'] ?? 0;
				}
			} else {
				// cURL bağlantı hatası varsa logla
				error_log('reCAPTCHA cURL Hatası: ' . $curlError);
			}
		}

		return $json;
	}
	public function resp(){
		$json = [];
		$token  = $_POST['token'];
    	$action = $_POST['action'];
    
		$curlData = array(
        'secret' => $this->config->get('recaptcha_google_secret_key'),
        'response' => $token
    	);

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlData));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $curlResponse = curl_exec($ch);

	    $captchaResponse = json_decode($curlResponse, true);

    	$json['res']=$captchaResponse['success'];
        $json['score']=$captchaResponse['score'];
        
    	
    	$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}