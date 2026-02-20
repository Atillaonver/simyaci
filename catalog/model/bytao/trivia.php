<?php
namespace Opencart\Catalog\Model\Bytao;
class Trivia extends \Opencart\System\Engine\Model {
    
    public function addTriviaClient($data=[]) {
		$new=0;
		$trivia_id = 0;
		$sessionID= $this->session->getID();
		
		if(isset($data['email'])){
			
			$trivia_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_trivia WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($data['email'])) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
			
			if (isset($trivia_query->row['trivia_id'])) {
				$trivia_id = $trivia_query->row['trivia_id'];
				$data['trivia_id']= $trivia_id;
				if(isset($this->session->data['trivia_id']) && $this->session->data['trivia_id']){
					$sQuery = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session WHERE trivia_id = '" .(int)$this->session->data['trivia_id']  . "' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND trivia_id = '" . (int)$trivia_id . "' LIMIT 1");
					if(!isset($sQuery->row['trivia_order'])){
						$this->addSession($data);
					}
				}
			}
			else
			{
				$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_trivia SET email = '" . $this->db->escape(utf8_strtolower($data['email']))  . "', name = '" . $this->db->escape(utf8_strtolower($data['name'])) . "',username = '" . $this->db->escape($data['username']) . "',store_id = '" . (int)$this->config->get('config_store_id') . "', date_added = NOW()");
				$trivia_id = $this->db->getLastId();
				$data['trivia_id'] = $trivia_id;
				$this->addSession($data);	
			}
			
			
		}
		
		return $trivia_id;
	}

	public function addTriviaSession($data) {
		$lastId = 0;
		if(isset($this->session->data['trivia_id']) && $this->session->data['trivia_id']){
			
			$trivia_query = $this->db->query("SELECT count(*) as CT FROM " . DB_PREFIX . "trivia_session WHERE trivia_id = '".(int)$this->session->data['trivia_id']."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' LIMIT 1");
			
			$trivia_order = ((int)$trivia_query->row['CT'])+1;
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session SET trivia_id = '" .(int)$this->session->data['trivia_id']  . "', trivia_session='".(int)$this->session->data['session_id']."', trivia_id = '" . (int)$data['trivia_id'] . "',trivia_order = '" . $trivia_order. "'");
			
			$lastId = $this->db->getLastId();
		}
		
		return $lastId;	
	}
	
	public function isInTriviaSession($trivia_id,$trivia_session_id) {
		$trivia_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$trivia_session_id."' LIMIT 1");
		
		$trivia_order = isset($trivia_query->row['trivia_order'])?$trivia_query->row['trivia_order']:0;
		if(!$trivia_order){
			
			$trivia_query = $this->db->query("SELECT count(*) as CT FROM " . DB_PREFIX . "trivia_session WHERE trivia_id = '".(int)$trivia_id."'  AND trivia_session='".(int)$trivia_session_id."' LIMIT 1");
		
			$trivia_order = ((int)$trivia_query->row['CT'])+1;
		
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session SET trivia_id = '" .(int)$trivia_id  . "', trivia_session='".(int)$trivia_session_id."', trivia_id = '" . (int)$trivia_id . "',trivia_order = '" . $trivia_order. "'");
		}
		
		
		return isset($trivia_query->row['trivia_order'])?$trivia_query->row['trivia_order']:0;
	}
	
	public function getSortOrder($trivia_id,$trivia_session_id) {
		
		$trivia_query = $this->db->query("SELECT trivia_order FROM " . DB_PREFIX . "trivia_session WHERE trivia_id = '".(int)$trivia_id ."'  AND trivia_session='".(int)$trivia_session_id."' AND trivia_id = '" . (int)$trivia_id . "'  LIMIT 1");
		
		$trivia_order = isset($trivia_query->row['trivia_order'])?$trivia_query->row['trivia_order']:0;
		return 	$trivia_order;
	}

	public function isTriviaApproved($trivia,$data){
		if(isset($data['email'] )&& isset($trivia['trivia_session']) && isset($trivia['trivia_id']) ){
			//$sql="SELECT ts.* FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_trivia tc ON (tc.trivia_id = ts.trivia_id) WHERE ts.trivia_id = '" .(int)$trivia['trivia_id']  . "' AND ts.trivia_session='".(int)$trivia['trivia_session']."' AND ts.init='1' AND tc.email = '" . $data['email'] . "'  LIMIT 1";
			$sql="SELECT ts.* FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_trivia tc ON (tc.trivia_id = ts.trivia_id) WHERE ts.trivia_id = '" .(int)$trivia['trivia_id']  . "' AND ts.trivia_session='".(int)$trivia['trivia_session']."' AND tc.email = '" . $data['email'] . "'  LIMIT 1";
			$sQuery = $this->db->query($sql);
		}
		return isset($sQuery->row['trivia_order'])?$sQuery->row['trivia_order']:0;
	}
	
	public function getTriviaSession() {
		$store_id = (int)$this->config->get('config_store_id');
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "trivia WHERE store_id = '" . $store_id . "' AND status = '1' LIMIT 1");
		$this->log->write('getTriviaSession:'.print_r($query,TRUE),0,'bytao/trivia:model');
		return isset($query->row)?$query->row:[];
	}
	
	public function getTriviaQuest($trivia_id){
		
		$query = $this->db->query("SELECT active_question FROM " . DB_PREFIX . "trivia_session_active WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND status='1'");
		
		$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest tq LEFT JOIN " . DB_PREFIX . "trivia_quest_description tqd ON (tq.quest_id = tqd.quest_id) WHERE tq.trivia_id = '".(int)$trivia_id."' AND tqd.language_id='".(int)$this->config->get('config_language_id')."' AND tq.sort_order = '".(int)$query->row['active_question']."' LIMIT 1");

		return isset($query2->row['quest_id'])?$query2->row:[];
	}
	
	public function getTriviaSessionQuest($trivia_id,$sort_order) {
		$home_row_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest tq LEFT JOIN " . DB_PREFIX . "trivia_quest_description tqd ON (tq.quest_id = tqd.quest_id) WHERE tq.trivia_id = '".(int)$trivia_id."' AND tqd.language_id='".(int)$this->config->get('config_language_id')."' AND tq.sort_order = '".(int)$sort_order."' LIMIT 1");

		return isset($query->row['quest_id'])?$query->row:[];
	}
	/**/
	public function addTriviaAnswer($adata=[]){
		
		if(isset($adata['quest_id'])&& $adata['quest_id']){
			
			$sql="SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE trivia_id='".(int) $adata['trivia_id']."' AND sort_order='".(int)$adata['quest_id']."' AND correct='".$this->db->escape($adata['ans'])."' LIMIT 1";
			$correct=0;
			$query = $this->db->query($sql);
			if(isset($query->row['correct'])){
				$this->db->query("UPDATE " . DB_PREFIX . "trivia_session SET `quest` = (`quest`+1),`time` =(`time`+" .(int)($adata['tm'])  . "),`active` = '".(int)$adata['quest_id']."' WHERE trivia_id='".(int) $adata['trivia_id']."'  AND trivia_session='".(int)$adata['trivia_session']."'");
				$correct = 1;
			}else{
				$this->db->query("UPDATE " . DB_PREFIX . "trivia_session SET `active` = '".(int)$adata['quest_id']."'  WHERE trivia_id='".(int) $adata['trivia_id']."'  AND trivia_session='".(int)$adata['trivia_session']."'");
			}
			
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session_answer SET trivia_id='".(int) $adata['trivia_id']."', trivia_id='".(int)$adata['trivia_id']."', trivia_session='".(int)$adata['trivia_session']."',sort_order='".(int)$adata['quest_id']."', answer='".$this->db->escape($adata['ans'])."', correct='".$correct ."', time='".(int)($adata['tm']) ."'");
			
		}

		return $adata;
	}
	
	public function getTriviaAction($trivia_id,$session_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session_action WHERE trivia_id = '".(int)$trivia_id."'  AND trivia_session='".(int)$session_id."' LIMIT 1");

		return isset($query->row['trivia_id'])?$query->row:[];
	}

	public function getTriviaTopTen($trivia_id,$session_id){
		$qData=[];
		$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia c ON c.trivia_id=ts.trivia_id WHERE ts.trivia_id='".(int)$trivia_id."' AND ts.trivia_session='".(int)$session_id."' ORDER BY ts.quest DESC,ts.time ASC ");
		if(isset($query2->rows)){
			foreach($query2->rows as $row){
				$answer = $this->getTriviaAnswers($row['trivia_id'],$trivia_id,$session_id);
				$qData[]=array(
					'trivia_id'=>$row['trivia_id'],
					'trivia_order'=>$row['trivia_order'],
					'username'=>$row['username'],
					'name'=>$row['name'],
					'quest'=>$row['quest'],
					'time'=>$row['time'],
					'ans'=>$answer,
				);
			}
			
		}
		return $qData;
	}
	
	public function getTriviaAnswers($trivia_id,$session_id){
		$sql="SELECT * FROM " . DB_PREFIX . "trivia_session_answer WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$session_id."' ORDER BY sort_order ASC";
		
		$query2 = $this->db->query($sql);
		
		return isset($query2->rows)?$query2->rows:[];
	}
	
	public function getTriviaStatus($trivia_id,$trivia_session_id) {
		$trivia_query = $this->db->query("SELECT count(*) AS total FROM " . DB_PREFIX . "trivia_session_out WHERE trivia_id = '".(int)$trivia_id ."'  AND trivia_session='".(int)$trivia_session_id."' LIMIT 1");
		return 	isset($trivia_query->row['total'])?(int)$trivia_query->row['total']:0;
	}
	
}
