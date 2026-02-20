<?php
namespace Opencart\Admin\Model\Bytao;
class Trivia extends \Opencart\System\Engine\Model {
	
	public function addTrivia($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "trivia SET name = '" . $this->db->escape($data['name']) . "',image = '" . $this->db->escape(isset($data['image'])?$data['image']:'') . "', logo = '" . $this->db->escape(isset($data['logo'])?$data['logo']:'') . "',sponsor = '" . $this->db->escape(isset($data['sponsor'])?$data['sponsor']:'') . "',trivia = '" . $this->db->escape(isset($data['trivia'])?$data['trivia']:'') . "', trivia_session = '" . (int)$data['trivia_session'] . "',maxq = '" . (int)$data['maxq'] . "',time = '" . (int)$data['time'] . "',board = '" . $this->db->escape($data['board']) . "', status = '" . (int)$data['status'] . "'");

		$trivia_id = $this->db->getLastId();
		return $trivia_id;
	}
	
	public function copyTrivia($trivia_id) {
		$qData = $this->getTrivia($trivia_id);
		$qData['name']=$qData['name'].' Copy';
		$qData['maxq']=0;
		$qData['status']=0;
		$qData['board']='';
		$new_trivia_id= $this->addTrivia($qData);
		
		$sql = "SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE trivia_id='".(int)$trivia_id."' ";
		$query = $this->db->query($sql);
		if(isset($query->rows)){
			foreach($query->rows as $quest){
				$theQ = $quest;
				$theQ['trivia_id'] = $new_trivia_id;
				$theQ['quest_description'] = $this->getQuestDescriptions($quest['quest_id']);
				$this->addQuest($theQ);
			}
		}
		return $new_trivia_id;
	}
	
	public function editTrivia($trivia_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "trivia SET name = '" . $this->db->escape($data['name']) . "',image = '" . $this->db->escape(isset($data['image'])?$data['image']:'') . "', logo = '" . $this->db->escape(isset($data['logo'])?$data['logo']:'') . "',sponsor = '" . $this->db->escape(isset($data['sponsor'])?$data['sponsor']:'') . "',trivia = '" . $this->db->escape(isset($data['trivia'])?$data['trivia']:'') . "',  trivia_session = '" . (int)$data['trivia_session'] . "', maxq = '" . (int)$data['maxq'] . "',time = '" . (int)$data['time'] . "',board = '" . $this->db->escape($data['board']) . "', status = '" . (int)$data['status'] . "' WHERE trivia_id = '" . (int)$trivia_id . "'");
	}
	
	public function repairTrivia($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "trivia SET image = '" . $this->db->escape($data['image']) . "', logo = '" . $this->db->escape($data['logo']) . "',sponsor = '" . $this->db->escape($data['sponsor']) . "',trivia = '" . $this->db->escape($data['trivia']) . "' WHERE trivia_id = '" . (int)$data['trivia_id'] . "'");
	}

	public function deleteTrivia($trivia_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "trivia WHERE trivia_id = '" . (int)$trivia_id . "'");
		
		$this->db->query("DELETE t1,t2 FROM " . DB_PREFIX . "trivia_quest t1 INNER JOIN " . DB_PREFIX . "trivia_quest_description t2 ON t2.quest_id = t1.quest_id WHERE t1.trivia_id = '" . (int)$trivia_id . "' ;");
		
	}
	
	public function getTrivia($trivia_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "trivia WHERE trivia_id = '" . (int)$trivia_id . "' LIMIT 1");

		return $query->row;
	}
	
	public function getTriviaActive() {
		$query = $this->db->query("SELECT trivia_id FROM " . DB_PREFIX . "trivia WHERE status = '1' LIMIT 1");

		return isset($query->row['trivia_id'])?$query->row['trivia_id']:0;
	}
	
	public function getTrivias($data = []) {
		$sql = "SELECT * FROM " . DB_PREFIX . "trivia";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getTotalTrivias(){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "trivia");
		return $query->row['total'];
	}
	
	public function addTriviaQuest($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_quest SET trivia_id = '" . (int)$data['trivia_id'] . "',correct = '" . $this->db->escape($data['correct']) . "',counter = '" . (int)$data['counter'] . "',sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "'");

		$quest_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET image = '" . $this->db->escape($data['image']) . "' WHERE quest_id = '" . (int)$quest_id . "'");
		}
	
	if (isset($data['correct_image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET correct_image = '" . $this->db->escape($data['correct_image']) . "' WHERE quest_id = '" . (int)$quest_id . "'");
		}

		foreach ($data['quest_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_quest_description SET quest_id = '" . (int)$quest_id . "', language_id = '" . (int)$language_id . "', quest = '" . $this->db->escape($value['quest']) . "', answer_a = '" . $this->db->escape($value['answer_a']) . "',answer_b = '" . $this->db->escape($value['answer_b']) . "',answer_c = '" . $this->db->escape($value['answer_c']) . "',answer_d = '" . $this->db->escape($value['answer_d']) . "'");
		}
		return $quest_id;
	}

	public function deleteTriviaQuest($quest_id) {
		$this->db->query("DELETE t1,t2 FROM " . DB_PREFIX . "trivia_quest t1 INNER JOIN " . DB_PREFIX . "trivia_quest_description t2 ON t2.quest_id = t1.quest_id WHERE t1.quest_id = '" . (int)$quest_id . "' ;");

	}

	public function editTriviaQuest($quest_id, $data){
		
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET correct = '" . $this->db->escape($data['correct']) . "',counter = '" . (int)$data['counter'] . "',sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "' WHERE quest_id = '" . (int)$quest_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET image = '" . $this->db->escape($data['image']) . "' WHERE quest_id = '" . (int)$quest_id . "'");
		}
	
	if (isset($data['correct_image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET correct_image = '" . $this->db->escape($data['correct_image']) . "' WHERE quest_id = '" . (int)$quest_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "trivia_quest_description WHERE quest_id = '" . (int)$quest_id . "'");

		
		foreach ($data['quest_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_quest_description SET quest_id = '" . (int)$quest_id . "', language_id = '" . (int)$language_id . "', quest = '" . $this->db->escape($value['quest']) . "', answer_a = '" . $this->db->escape($value['answer_a']) . "',answer_b = '" . $this->db->escape($value['answer_b']) . "',answer_c = '" . $this->db->escape($value['answer_c']) . "',answer_d = '" . $this->db->escape($value['answer_d']) . "'");
		}
	
	}
	
	public function repairTriviaQuest($data){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET image = '" . $this->db->escape($data['image']) . "',correct_image = '" . $this->db->escape($data['correct_image']) . "' WHERE quest_id = '" . (int)$data['quest_id'] . "'");

	}
	
	public function getTriviaQuestDescriptions($quest_id) {
		$quest_description_data = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest_description WHERE quest_id = '" . (int)$quest_id . "'");
		foreach ($query->rows as $result) {
			$quest_description_data[$result['language_id']] = array(
				'quest'             => $result['quest'],
				'answer_a'      	=> $result['answer_a'],
				'answer_b' 			=> $result['answer_b'],
				'answer_c'     		=> $result['answer_c'],
				'answer_d'     		=> $result['answer_d']
			);
		}

		return $quest_description_data;
	}

	public function getTotalTriviaQuests($trivia_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "trivia_quest WHERE trivia_id='".(int)$trivia_id."' ");

		return $query->row['total'];
	}
	
	public function getTriviaQuest($quest_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE quest_id='".(int)$quest_id."' LIMIT 1";

		$query = $this->db->query($sql);

		return $query->row;
	}
	
	public function getTriviaQuestsTotal($trivia_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "trivia_quest WHERE trivia_id='".(int)$trivia_id."' AND status='1' ");
		return $query->row['total'];
	} 
	
	public function getTriviaQuests($trivia_id,$data = []){
		$sql = "SELECT * FROM " . DB_PREFIX . "trivia_quest q LEFT JOIN " . DB_PREFIX . "trivia_quest_description qd ON(q.quest_id = qd.quest_id ) WHERE q.trivia_id='".(int)$trivia_id."' AND qd.language_id='". (int)$this->config->get('config_language_id')."' ";

		$sort_data = array(
			'qd.quest',
			'q.sort_order',
			'q.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY q.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getAllTriviaQuests(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest");
		return $query->rows;
	}
	
	public function getTriviaSessions($trivia_id){
		$tData=[];
		
		$query =$this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session WHERE trivia_id='".(int)$trivia_id."' GROUP BY trivia_session ORDER BY trivia_session DESC");
		if(isset($query->rows)){
			foreach($query->rows as $row){
				if(isset($row['trivia_session'])){
					$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_client c ON c.client_id = ts.client_id WHERE trivia_id='".(int)$trivia_id."'  AND trivia_session='".(int)$row['trivia_session']."' ORDER BY ts.quest DESC,ts.time ASC");
					
					if(isset($query2->rows)){
						foreach($query2->rows as $rowN){
							$answer = $this->getClientAnswers($rowN['client_id'],$trivia_id);
							$tData[$row['trivia_session']][]=array(
								'client_id'=>$rowN['client_id'],
								'client_order'=>$rowN['client_order'],
								'username'=>$rowN['username'],
								'mail'=>$rowN['email'],
								'name'=>$rowN['name'],
								'quest'=>$rowN['quest'],
								'time'=>$rowN['time'],
								'ans'=>$answer,
							);
						}
					}
				}
			}
		}
		return $tData;
	}
	
	public function getTriviaNSession($trivia_id){
		$tData=[];
		if(isset($this->session->data['trivia_session'])){
			$query2 = $this->db->query("SELECT *,(SELECT count(*) AS total FROM " . DB_PREFIX . "trivia_session_out WHERE trivia_id = '".(int)$trivia_id ."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id = c.client_id  LIMIT 1) AS cout ,IF(ts.date_update >(NOW() - interval 1 minute),1,0) AS online FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_client c ON c.client_id=ts.client_id WHERE ts.trivia_id='".(int)$trivia_id."'  AND ts.trivia_session='".(int)$this->session->data['trivia_session']."' ORDER BY ts.quest DESC,ts.time ASC");
			
			if(isset($query2->rows)){
				foreach($query2->rows as $rowN){
					$answer = $this->getClientNAnswers($rowN['client_id'],$trivia_id);
					$tData[]=array(
						'online'=>$rowN['online'],
						'cout'=>$rowN['cout'],
						'client_id'=>$rowN['client_id'],
						'client_order'=>$rowN['client_order'],
						'username'=>$rowN['username'],
						'mail'=>$rowN['email'],
						'name'=>$rowN['name'],
						'quest'=>$rowN['quest'],
						'time'=>$rowN['time'],
						'init'=>$rowN['init'],
						'ans'=>$answer,
					);
				}
			}
		}
		return $tData;
	}
	
	public function approveTriviaClient($trivia_id,$client_id){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_session SET init='1' WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."'");
	}
	
	public function outTriviaClient($trivia_id,$client_id){
		$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session_out SET client_id='".(int)$client_id."',trivia_id='".(int)$trivia_id."', trivia_session='".(int)$this->session->data['trivia_session']."'");
		
	}
	
	public function inTriviaClient($trivia_id,$client_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "trivia_session_out WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."'");
	}
	
	public function hideTriviaClient($trivia_id,$client_id){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_session SET init='2' WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."'");
	}
	
	public function unhideTriviaClient($trivia_id,$client_id,$init=0){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_session SET init='".(int)$init."' WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."'");
	}
	
	public function getActiveTriviaQuestClients($trivia_id = 0){
		$qData=[];
		$sql = "SELECT *,(SELECT count(*) AS total FROM " . DB_PREFIX . "trivia_session_out WHERE trivia_id = '".(int)$trivia_id ."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id = c.client_id  LIMIT 1) AS cout,IF(ts.date_update >(NOW() - interval 1 minute),1,0) AS online FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_client c ON c.client_id = ts.client_id WHERE trivia_id='".(int)$trivia_id."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND ts.init != '2' ORDER BY ts.quest DESC,ts.time ASC";
		$query2 = $this->db->query($sql);
		if(isset($query2->rows)){
			foreach($query2->rows as $row){
				$answer = $this->getClientAnswers($row['client_id'],$trivia_id);
				$qData[]=array(
					'cout'=>$row['cout'],
					'online'=>$row['online'],
					'client_id'=>$row['client_id'],
					'username'=>$row['username'],
					'client_order'=>$row['client_order'],
					'name'=>$row['name'],
					'quest'=>$row['quest'],
					'time'=>$row['time'],
					'init'=>$row['init'],
					'ans'=>$answer,
				);
			}
		}
		return $qData;
	}
	
	public function getQuestTriviaClients($trivia_id = 0,$session_id = 0,$client_id = 0){
		$qSql="SELECT * FROM " . DB_PREFIX . "trivia_session_answer WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$session_id."' AND client_id='".(int)$client_id."' ORDER BY sort_order ASC";
		$query2 = $this->db->query($qSql);
		
		return isset($query2->rows)?$query2->rows:[];
	}
	
	public function getTriviaAction($trivia_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session_action WHERE trivia_id = '".(int)$trivia_id."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' LIMIT 1");

		return isset($query->row['action'])?$query->row['action']:'';
	}

	public function getActiveQuestAnsvered($trivia_id = 0,$quest=0){
	
	}
		
	public function getActiveTriviaQuestClientsAns($trivia_id = 0,$quest=0){
			$sql  = "SELECT DISTINCT count(*) AS QQ,";
			
			$sql .= "(SELECT COUNT(*) FROM " . DB_PREFIX . "trivia_session WHERE trivia_id='".(int)$trivia_id."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND active='".(int)$quest."')AS CC,";
			
			$sql .= "(SELECT COUNT(*) FROM " . DB_PREFIX . "trivia_session WHERE trivia_id='".(int)$trivia_id."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND init != '2')AS DD";
			
			$sql .= " FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_client c ON c.client_id=ts.client_id WHERE ts.trivia_id='".(int)$trivia_id."' AND ts.trivia_session='".(int)$this->session->data['trivia_session']."' GROUP BY ts.active";
		
		
			$query2 = $this->db->query($sql);
			$groups = isset($query2->row['QQ'])?(int)$query2->row['QQ']:'';
			$aClients = isset($query2->row['CC'])?(int)$query2->row['CC']:0;
			$clients = isset($query2->row['DD'])?(int)$query2->row['DD']:0;
			
			if($aClients == $clients){
				return 1;
			}else{
				return 0;
			}
		
	}
	
	public function getTriviaTopTen($trivia_id = 0){
		$qData=[];
		$query2 = $this->db->query("SELECT *,(SELECT count(*) AS total FROM " . DB_PREFIX . "trivia_session_out WHERE trivia_id = '".(int)$trivia_id ."'  AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id = c.client_id  LIMIT 1) AS cout FROM " . DB_PREFIX . "trivia_session ts LEFT JOIN " . DB_PREFIX . "trivia_client c ON (c.client_id=ts.client_id) WHERE ts.trivia_id='".(int)$trivia_id."'  AND ts.trivia_session='".(int)$this->session->data['trivia_session']."' AND ts.init != '2' ORDER BY ts.quest DESC,ts.time ASC");
		if(isset($query2->rows)){
			foreach($query2->rows as $row){
				$answer = $this->getClientAnswers($row['client_id'],$trivia_id);
				
				$qData[]=array(
					'cout'=>$row['cout'],
					'client_id'=>$row['client_id'],
					'client_order'=>$row['client_order'],
					'username'=>$row['username'],
					'name'=>$row['name'],
					'quest'=>$row['quest'],
					'time'=>$row['time'],
					'init'=>$row['init'],
					'ans'=>$answer,
				);
			}
			
		}
		return $qData;
	}
	
	public function getTriviaClientAnswers($client_id,$trivia_id){
		
		if(isset($this->session->data['trivia_session'])){
			$qSql="SELECT * FROM " . DB_PREFIX . "trivia_session_answer WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."' ORDER BY sort_order ASC";
			$query2 = $this->db->query($qSql);
		}
		return isset($query2->rows)?$query2->rows:[];
	}
	
	public function getTriviaClientNAnswers($client_id,$trivia_id){
		$tData=[];
		if(isset($this->session->data['trivia_session'])){
			$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session_answer WHERE trivia_id='".(int)$trivia_id."' AND trivia_session='".(int)$this->session->data['trivia_session']."' AND client_id='".(int)$client_id."' ORDER BY sort_order ASC");
			if(isset($query2->rows)){
				foreach($query2->rows as $ANS){
					$tData[$ANS['sort_order']][]=array(
						'sort_order' => $ANS['sort_order'],
		                'answer' => $ANS['answer'],
		                'correct' => $ANS['correct'],
		                'time' => $ANS['time'],
		                'added' => $ANS['added_time']
					);
				}
			}
		
		}
		return $tData;
	}
	
	public function getTriviaNQuest($sort_order,$trivia_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_quest tq LEFT JOIN " . DB_PREFIX . "trivia_quest_description tqd ON tq.quest_id = tqd.quest_id WHERE tq.trivia_id='".(int)$trivia_id."' AND tq.sort_order='".(int)$sort_order."' AND tqd.language_id ='" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
	public function getTriviaQuestFiles($quest_id) {
		$quest_file_data = [];

		$quest_file_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "quest_file WHERE quest_id = '" . (int)$quest_id . "' AND parent_id='0' ORDER BY sort_order ASC");

		foreach ($quest_file_query->rows as $quest_file) {
			$quest_file_description_data = [];

			$quest_quest_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "quest_file_description WHERE quest_file_id = '" . (int)$quest_file['quest_file_id'] . "' AND quest_id = '" . (int)$quest_id . "'");

			foreach ($quest_quest_description_query->rows as $quest_file_description) {
				$quest_file_description_data[$quest_file_description['language_id']] = array('title' => $quest_file_description['title']);
			}

			$quest_file_data[] = array(
				'quest_file_description' => $quest_file_description_data,
				'quest_file_id'          => $quest_file['quest_file_id'],
				'quest_parent_class'          => $quest_file['quest_parent_class'],
				'quest_style'          => $quest_file['quest_style'],
				'quest_type'          => $quest_file['quest_type'],
				'link'                     => $quest_file['link'],
				'file'                    => $quest_file['file'],
				'mobile_file'                    => $quest_file['mobile_file'],
				'date_start'                    => $quest_file['date_start'],
				'date_end'                    => $quest_file['date_end'],
				'status'                    => $quest_file['status'],
				'sort_order'               => $quest_file['sort_order']
			);
		}

		return $quest_file_data;
	}
	
	public function getTriviaSubQuestFiles($parent_id) {
		$quest_file_data = [];

		$quest_file_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "quest_file WHERE parent_id = '" . (int)$parent_id . "' ORDER BY sort_order ASC");

		foreach ($quest_file_query->rows as $quest_file) {
			$quest_file_description_data = [];

			$quest_file_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "quest_file_description WHERE quest_file_id = '" . (int)$quest_file['quest_file_id'] . "' AND quest_id = '" . (int)$quest_file['quest_id'] . "'");


			foreach ($quest_file_description_query->rows as $quest_file_description) {
				$quest_file_description_data[$quest_file_description['language_id']] = array('title' => $quest_file_description['title']);
			}

			$quest_file_data[] = array(
				'quest_file_description' => $quest_file_description_data,
				'quest_parent_class'      => $quest_file['quest_parent_class'],
				'quest_style'      		  => $quest_file['quest_style'],
				'quest_type'      		  => $quest_file['quest_type'],
				'position'                 => $quest_file['position'],
				'link'                     => $quest_file['link'],
				'file'                    => $quest_file['file'],
				'mobile_file'             => $quest_file['mobile_file'],
				'sort_order'               => $quest_file['sort_order']
			);
		}

		return $quest_file_data;
	}

	public function sortTriviaQuestOrder($key_id,$sort_order){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_quest SET sort_order='".(int)$sort_order."' WHERE quest_id ='".(int)$key_id."'");
		
		return;
	}	

	public function isTriviaQuestInStore($quest_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "quest WHERE quest_id = '" . (int)$quest_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function addTriviaEvent($trivia_id){
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trivia_session_action WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(int)$this->session->data['trivia_session']."'");
		
		if($query->rows){
			//$this->log->write('update:start'.$this->session->data['trivia_session']);
			$this->db->query("UPDATE " . DB_PREFIX . "trivia_session_action SET action = 'start', action_exp = '0' WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(int)$this->session->data['trivia_session']."'");
		}else{
			//$this->log->write('add:start'.$this->session->data['trivia_session']);
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session_action SET trivia_id = '" . (int)$trivia_id . "', trivia_session='".(int)$this->session->data['trivia_session']."', action = 'start', action_exp = '0'");
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "trivia_session_active SET trivia_id = '" . (int)$trivia_id . "', trivia_session='".(int)$this->session->data['trivia_session']."', status = '1', date_start = NOW() ");
			
		}
		
		return;
	}
	
	public function updateTriviaEvent($trivia_id,$action,$exp){
		//$this->log->write('update:'.$action.'.'.$this->session->data['trivia_session']);
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_session_action SET action = '".$this->db->escape($action)."', action_exp = '".(int)$exp."' WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(int)$this->session->data['trivia_session']."'");
		return;
	}
	
	public function nextTriviaQuest($trivia_id){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia_session_active SET active_question = (active_question + 1) WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(int)$this->session->data['trivia_session']."'");
		
	}
	
	public function getTriviaQuestOrder($trivia_id,$equal=0){
		$query = $this->db->query("SELECT active_question FROM " . DB_PREFIX . "trivia_session_active WHERE trivia_id = '" . (int)$trivia_id . "'  AND trivia_session='".(isset($this->session->data['trivia_session'])?(int)$this->session->data['trivia_session']:0)."' AND status='1'");
		return isset($query->row['active_question'])?((int)$query->row['active_question']+$equal):0 ;
	}
	
	public function nextTriviaSession($trivia_id){
		$this->db->query("UPDATE " . DB_PREFIX . "trivia SET `trivia_session` = (`trivia_session` + 1) WHERE trivia_id = '" . (int)$trivia_id . "'");
	}

	public function addTriviaAnswer($adata=array()){
		if(isset($adata['quest_id'])&& (int)$adata['quest_id']){
			$SQL = "SELECT * FROM " . DB_PREFIX . "trivia_quest WHERE trivia_id='".(int) $adata['trivia_id']."' AND sort_order='".(int)$adata['quest_id']."' AND correct='".$this->db->escape($adata['ans'])."' LIMIT 1";
			$correct = 0;
			$query = $this->db->query($SQL);
			if(isset($query->row['correct'])){
				$SQL ="UPDATE " . DB_PREFIX . "trivia_session SET `quest` = (`quest`+1),`time` =(`time`+" .(int)($adata['tm'])  . "),`active` = '".(int)$adata['quest_id']."' WHERE trivia_id='".(int) $adata['trivia_id']."' AND client_id='".(int)$adata['client_id']."'  AND trivia_session='".(int)$adata['trivia_session']."'";
				$this->db->query($SQL);
				$correct = 1;
			}else{
				$SQL = "UPDATE " . DB_PREFIX . "trivia_session SET `active` = '".(int)$adata['quest_id']."'  WHERE trivia_id='".(int) $adata['trivia_id']."' AND client_id='".(int)$adata['client_id']."'  AND trivia_session='".(int)$adata['trivia_session']."'";
				$this->db->query($SQL);
			}
			
			$SQL ="INSERT INTO " . DB_PREFIX . "trivia_session_answer SET trivia_id='".(int) $adata['trivia_id']."', client_id='".(int)$adata['client_id']."', trivia_session='".(int)$adata['trivia_session']."',sort_order='".(int)$adata['quest_id']."', answer='".$this->db->escape($adata['ans'])."', correct='".$correct ."', time='".(int)($adata['tm']) ."'";
			$this->db->query($SQL);
		}
		return $adata;
	}
	
	public function qrTrivia($trivia_id,$size=140){
		$qrStr='';
		
		require_once(DIR_SYSTEM .'library/shared/phpqrcode/qrlib.php');
		
    	//$QR = new \Opencart\System\Library\Shared\QRcode();
	    $codeContents = $this->config->get('config_url')?$this->config->get('config_url'):'https://www.bugunnelerizledim.com/';
	    $fileName = $codeContents.'?t='.md5($trivia_id).'.png';
	    
	    $pngAbsoluteFilePath = DIR_IMAGE.'catalog/'.($this->session->data['path']?$this->session->data['path'].'/':'').$fileName;
	    $urlRelativeFilePath = 'catalog/'.($this->session->data['path']?$this->session->data['path'].'/':'').$fileName;

	    if (!file_exists($pngAbsoluteFilePath)) {
	    	//$QR = new \Opencart\System\Library\Shared\phpqrcode\QRcode;
	        //QRcode::png($codeContents, $pngAbsoluteFilePath);
	        $qrStr = $urlRelativeFilePath;
	    } else {
	       $qrStr = $urlRelativeFilePath;
	    }
	   	
		return $qrStr;
	}
	
}