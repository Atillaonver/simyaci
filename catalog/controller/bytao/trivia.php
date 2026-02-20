<?php
namespace Opencart\Catalog\Controller\Bytao;
class Trivia extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.0';
	private $cPth = 'bytao/trivia';
	private $C = 'trivia';
	private $ID = 'trivia_id ';
	private $model ;
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	public function index(){
		
		$this->getML('ML');
		$language_id = $this->config->get('config_language_id');
		
		$trivia = $this->model->{$this->getFunc('get','Session')}();
		$this->log->write('Trivia:'.print_r($trivia,TRUE),0,$this->cPth);

		$server = HTTP_SERVER;
				
		$data['title'] = $this->document->getTitle();
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');
		$data['template'] = $this->config->get('config_template');
		$data['name'] = $this->config->get('config_name');
		
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = $server . MEDIA . $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . MEDIA . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (isset($trivia['logo']) && is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['tLogo'] = $trivia['logo'];
		} else {
			$data['tLogo'] = '';
		}
		
		if (isset($trivia['trivia']) && is_file(DIR_IMAGE . $trivia['trivia'])) {
			$data['trivia'] = $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo_negative'))) {
			$data['logo_negative'] = HTTPS_IMAGE. $this->config->get('config_logo_negative');
		} else {
			$data['logo_negative'] = '';
		}
		$data[$this->ID] = $trivia_id = isset($trivia[$this->ID])?$trivia[$this->ID]:0;
		$data['session_id'] = $session_id = isset($trivia['trivia_session'])?$trivia['trivia_session']:0;
		$data['sess'] = $this->session->getID();
		$data['time'] = isset($trivia['time'])?$trivia['time']:60;
		$actions = ['start','list'];
		$clientId = isset($this->session->data['client_id'])?$this->session->data['client_id']:0;
		$action = $this->model->{$this->getFunc('get','Action')}($trivia_id,$session_id);
		$approved = $this->model->{$this->getFunc('is','Approved')}($trivia,isset($this->request->post)?$this->request->post:[]);
		$data['ansq'] = isset($this->session->data['ansq'])?$this->session->data['ansq']:0;
		
		if ($trivia_id ==0 ) 
		{
			$this->response->setOutput($this->load->view($this->cPth . '_logout', $data));
		}
		elseif ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) 
		{
			$this->session->data['client_token'] = substr(bin2hex(openssl_random_pseudo_bytes(26)), 0, 26);
			
			$client_id = $clientId ? $clientId : $this->model->{$this->getFunc('add','Client')}($this->request->post);
			$data['client_id'] = $this->session->data['client_id'] = $client_id;
			$data['mail']= $mail = $this->request->post['email'];
			
				
			if (!is_dir(DIR_BASE_APP.'sess/'.$trivia_id )) {
				mkdir(DIR_BASE_APP.'sess/'.$trivia_id , 0777);
				mkdir(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id , 0777);
			}else if (!is_dir(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id )){
				mkdir(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id , 0777);
			}
			if(is_file(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id.'/users.json')){
				$aData = json_decode(file_get_contents(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id.'/users.json'),true);
			}else{
				$aData=[];
			}
			
			$aData[$mail]=array(
				'username' =>$this->request->post['username'],
				'name' =>$this->request->post['name'],
				'email' =>$mail,
				'client_id'=>$client_id
			);
			
			$jsonData = json_encode($aData);
			file_put_contents(DIR_BASE_APP.'sess/'.$trivia_id.'/'.$session_id.'/users.json', $jsonData);
			
			if($client_id){
				$data['so'] = $this->model->{$this->getFunc('get','SortOrder')}($client_id,$session_id,$trivia_id);
				$this->response->setOutput($this->load->view($this->cPth, $data));
			}
			else
			{
				$this->response->setOutput($this->load->view($this->cPth.'_login', $data));
			}
			//$this->log->write('Araf:'.$client_id);
		}
		else
		{
			if (isset($this->error['name'])) {
				$data['error_isim'] = $this->error['name'];
			} else {
				$data['error_isim'] = '';
			}

			if (isset($this->error['email'])) {
				$data['error_email'] = $this->error['email'];
			} else {
				$data['error_email'] = '';
			}
			
			if (isset($this->request->post['name'])) {
				$data['name'] = $this->request->post['name'];
			} else {
				$data['name'] = '';
			}

			if (isset($this->request->post['username'])) {
				$data['username'] = $this->request->post['username'];
			} else {
				$data['username'] = '';
			}

			if (isset($this->request->post['email'])) {
				$data['email'] = $this->request->post['email'];
			} else {
				$data['email'] = '';
			}
			$this->response->setOutput($this->load->view($this->cPth.'_login', $data));
		}
	}
	
	public function loged(){
		if(!isset($this->session->data[$this->ID]) || !isset($this->session->data['session_id']) || !isset($this->session->data['client_id'])) {
			//$this->response->redirect($this->url->link('bytao/client', '', 'SSL'));
		}
		$this->getML('ML');
		
		$language_id = $this->config->get('config_language_id');
		$actions = ['start','list'];
		$clientId = 0;
		$data[$this->ID] = $trivia_id = $this->session->data[$this->ID];
		$data['session_id'] = $session_id = $this->session->data['session_id'];
		$data['client_id'] = $client_id = $this->session->data['client_id'];
		$data['so'] = $this->model->{$this->getFunc('get','SortOrder')}($client_id,$session_id);
		
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');
		$data['template'] = $this->config->get('config_template');
		$data['name'] = $this->config->get('config_name');
		$trivia = $this->model->{$this->getFunc('get','Session')}();
		
		if(!$trivia){
			//$this->response->redirect($this->url->link('bytao/client', '', 'SSL'));
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = $server . MEDIA . $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . MEDIA . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['logo'])) {
			$data['tLogo'] = $trivia['logo'];
		} else {
			$data['tLogo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $trivia['trivia'])) {
			$data['trivia'] = $trivia['trivia'];
		} else {
			$data['trivia'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo_negative'))) {
			$data['logo_negative'] = $server . MEDIA . $this->config->get('config_logo_negative');
		} else {
			$data['logo_negative'] = '';
		}
		
		$data['time'] = $trivia['time'];
		
		
		$this->response->setOutput($this->load->view($this->cPth . '_client_loged', $data));
	}
	
	public function pasive(){
		$this->getML('ML');
		
		$trivia = $this->model->{$this->getFunc('get','Session')}();
		if($trivia){
			//$this->response->redirect($this->url->link('bytao/client', '', 'SSL'));
		}
		
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');
		$data['template'] = $this->config->get('config_template');
		$data['name'] = $this->config->get('config_name');
		
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$data['icon'] = $server . MEDIA . $this->config->get('config_icon');
		} else {
			$data['icon'] = '';
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . MEDIA . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo_negative'))) {
			$data['logo_negative'] = $server . MEDIA . $this->config->get('config_logo_negative');
		} else {
			$data['logo_negative'] = '';
		}
		
		
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/bytao/client_logout.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/bytao/client_logout.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/bytao/client_logout.tpl', $data));
		}
	}
	
	protected function validateForm() {
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
				$this->error['name'] = $this->language->get('error_isim');
			}
			
		
		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
	
	public function login(){
		$this->getML('ML');
		$json = [];
		
		$client_id = isset($this->request->post['cid'])?$this->request->post['cid']:0;
		$trivia_id = isset($this->request->post[$this->ID])?$this->request->post[$this->ID]:0;
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function online(){
		$this->getML('ML');
		$json = [];
		
		$client_id = isset($this->request->get['cid'])?$this->request->get['cid']:0;
		$session_id = isset($this->request->get['sid'])?$this->request->get['sid']:0;
		$trivia_id = isset($this->request->get[$this->ID])?$this->request->get[$this->ID]:0;
		$json['so'] = $this->model->{$this->getFunc('get','SortOrder')}($client_id,$session_id,$trivia_id);
		$json['out'] = $this->model->{$this->getFunc('get','ClientStatus')}($client_id,$session_id,$trivia_id);
		
		if($json['out']>0){
			$json['redirect']= $this->url->link('', '', 'SSL');	
			unset($this->session->data[$this->ID]);
			unset($this->session->data['session_id']);
			unset($this->session->data['client_id']);
			unset($this->session->data['ansq']);
		}
		if(!$json['so']){
			//$json['redirect']= $this->url->link('', '', 'SSL');	
		}
		
		$trivia = $this->model->{$this->getFunc('get','Session')}();				
		if($trivia_id && $trivia){
			
			$this->db->query("update " . DB_PREFIX . "trivia_session SET date_update = NOW() WHERE trivia_id = '".(int)$this->request->get[$this->ID]."' AND trivia_session='".(int)$session_id."' AND Client_id='".$client_id."' AND date_update < (NOW() - interval 1 minute)");
			
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session_action WHERE trivia_id = '".(int)$this->request->get[$this->ID]."' AND trivia_session='".(int)$session_id."' LIMIT 1");
			
			if(isset($query->row['action'])){
				
				$json['isIn'] = $this->model->{$this->getFunc('is','InSession')}($client_id,$trivia_id,$session_id);
				
				if($json['isIn']=='0'){
					//unset($this->session->data[$this->ID]);
					//unset($this->session->data['session_id']);
					//unset($this->session->data['client_id']);
					//$json['redirect']= $this->url->link('bytao/client', '', 'SSL');	
				}
				switch($query->row['action']){
					case 'quest':
						//$this->model_bytao_client->getTQuest($this->request->get[$this->ID]);
						$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE quest_id = '".(int)$query->row['action_exp']."' LIMIT 1");
						if(isset($query2->row['counter'])){
							$json['counter']= $query2->row['counter']; 
						}
						$json['quest'] = $this->session->data['quest'] =  $query->row['action_exp']; 
						$json['time']= $trivia['time']; 
						
						break;
					case 'close':$json['wait']=1; break;
					
					case 'wait':$json['until']=1; break;
					
					case 'finish':
						$lists = $this->model->{$this->getFunc('get','TopTen')}($trivia_id,$session_id);
						if(isset($lists[0]['quest']) && isset($lists[1]['quest'])&& $lists[0]['quest'] == $lists[1]['quest']&& $lists[0]['time'] == $lists[1]['time']&& (($lists[0]['client_id'] == $client_id)||($lists[1]['client_id'] == $client_id))){
							
							$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE quest_id = '".(int)$query->row['action_exp']."' LIMIT 1");
							if(isset($query2->row['counter'])){
								$json['counter']= $query2->row['counter']; 
							}
							$json['quest']= $query->row['action_exp']; 
							
						}else{
							$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session WHERE trivia_id = '".(int)$this->request->get[$this->ID]."' AND client_id='".$client_id."'  AND trivia_session='".(int)$session_id."' LIMIT 1");
							$tQuest='';
							if(isset($query2->row['quest'])){
								$tQuest = $query2->row['quest']; 
							}
							unset($this->session->data[$this->ID]);
							unset($this->session->data['session_id']);
							unset($this->session->data['client_id']);
							unset($this->session->data['ansq']);
							
							$json['finish']=sprintf($this->language->get('text_finish'),$tQuest);
						}
						break;
					default:
					break;
				}
			}
		}else{
			$json['redirect']= $this->url->link('bytao/trivia');	
		}
		
		
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	public function answered(){//new
		$json = [];
		if($this->request->get['cid']){
			$sessionID = $this->session->getID();
			
			$tvsid = $this->request->get['tvsid'];
			$tvid = $this->request->get['tvid'];
			$cid = $this->request->get['cid'];
			$quest = $this->request->get['quest'];
			$ans = $this->request->get['ans'];
			$tm = $this->request->get['tm'];
			$aData=[];
			if($cid&&$quest&&$ans&&$tm){
				$aData=array(
					$this->ID =>$tvid,
					'trivia_session' =>$tvsid,
					'client_id' =>$cid,
					'quest_id' =>$quest,
					'ans' =>$ans,
					'tm' =>$tm
				);
				//$json['res'] = $this->model_bytao_client->addAnswer($aData);
				$jsonData = json_encode($aData);
				if (!is_dir(DIR_BASE_APP.'sess/'.$tvid )) {
					mkdir(DIR_BASE_APP.'sess/'.$tvid , 0777);
					mkdir(DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid , 0777);
				}else if (!is_dir(DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid )){
					mkdir(DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid , 0777);
				}
				file_put_contents(DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid.'/'.$sessionID.'.json', $jsonData);
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function araf(){//new
		$json = [];
		if($this->request->get['cid']){
			
			$sessionID= $this->session->getID();
			
			$tvsid = $this->request->get['tvsid'];
			$tvid = $this->request->get['tvid'];
			$cid = $this->request->get['cid'];
			$mail = $this->request->get['sm'];
			$quest = $this->request->get['quest'];
			$ans = $this->request->get['ans'];
			$tm = $this->request->get['tm'];
			
			$aData=[];
			if($cid&&$quest&&$ans&&$tm&&$mail){
				$aData = json_decode(file_get_contents(DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid.'/users.json'),true);
				//$filename = '/home/u7092310/trivia2.bugunnelerizledim.com/sess/araf.txt';
				$filename = DIR_BASE_APP.'sess/'.$tvid.'/'.$tvsid.'/ansvers.txt';
				$data =  "trivia_id:$tvid,trivia_session:$tvsid,client_id:$cid,mail:$mail,quest:$quest,ans:$ans,tm:$tm\n";
				$maxRetries = 5;
				$retryDelay = 10000;
				$retryCount = 0;
				$success = false;

				while ($retryCount < $maxRetries && !$success) {
				    // Open the file for writing
				    $file = fopen($filename, 'a');

				    if ($file) {
				        if (flock($file, LOCK_EX)) { // Try to lock the file
				            fwrite($file, $data);// Write the data to the file
				            flock($file, LOCK_UN);// Unlock the file
				            fclose($file);// Close the file
				            $this->session->data['ansq'] = $quest;
				            $success = true;
				        } else {
				            fclose($file);// Failed to lock the file, close it
				            usleep($retryDelay);// Wait for a short period before retrying
				        }
				    } else {
				        $this->log->write("Could not open the file!");
				        break;
				    }

				    $retryCount++;
				}

				if (!$success) {
				    $json['err']= "Failed to write to the file after $maxRetries attempts.";
				} else {
				    $json['success']= "Data written successfully.";
				}
				
				
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}
	
	
}
