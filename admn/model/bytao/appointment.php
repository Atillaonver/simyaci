<?php
namespace Opencart\Admin\Model\Bytao;

class Appointment extends \Opencart\System\Engine\Model {
	public function editAppointment(array $data):int {
		if(isset($data[1])&& isset($data[2])){
			$_date = explode('-',$data[1]);
			$date = $_date[0].'-'.$_date[1].'-'.$_date[2];
			
			if($data[0]=='0'){
				$SQL = "INSERT INTO " . DB_PREFIX . "appointment SET status = '0', store_id='".(int)$this->session->data['store_id']."',date_app = '" . $this->db->escape($date.' '.$data[2]) . ":00'";
				$this->db->query($SQL);
				
				return $this->db->getLastId();	
			}else{
				
				$this->db->query("UPDATE " . DB_PREFIX . "appointment SET date_app = '" . $this->db->escape($date.' '.$data[2]) . "' WHERE appointment_id = '" . (int)$data[0] . "'");
				return (int)$data[0];
			}
		}else{
			return 0;
		}
		
	}

	public function deleteAppointment($appointment_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "appointment WHERE appointment_id = '" . (int)$appointment_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "appointment_description WHERE appointment_id = '" . (int)$appointment_id . "'");
	}

	public function getAppointment($appointment_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "appointment  WHERE appointment_id = '" . (int)$appointment_id . "'");

		return isset($query->row)?$query->row:[];
	}

	public function getAppointments(array $data ):array {
		$sql = "SELECT * FROM " . DB_PREFIX . "appointment a    WHERE a.store_id = '" . (int)$this->session->data['store_id'] . "'";
 		if (isset($data['date_start']) || isset($data['date_end'])) {
			$sql .= " AND a.date_app >'" . $this->db->escape($data['date_start']) . "' AND a.date_app < '" . $this->db->escape($data['date_end'])."' ORDER BY a.date_app";
			$this->log->write('String:'.$sql);
			
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

		return isset($query->rows)?$query->rows:[];
	}
	
	public function getDateAppointments(string $date ):array {
		$sql = "SELECT * FROM " . DB_PREFIX . "appointment a WHERE a.store_id = '" . (int)$this->session->data['store_id'] . "' AND a.date_app LIKE '" . $this->db->escape($date) . "%' ORDER BY a.date_app";
		
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

		return isset($query->rows)?$query->rows:[];
	}
	
	
	public function getAppointmentDescriptions($appointment_id):array {
		$appointment_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "appointment_description WHERE appointment_id = '" . (int)$appointment_id . "'");

		foreach ($query->rows as $result) {
			$appointment_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'            => $result['description'],
			);
		}

		return $appointment_description_data;
	}
	
	public function getAppointmentImage(int $appointment_id,$type=0):array {
		$news_image_data = [];

		$news_image_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image WHERE appointment_id = '" . (int)$appointment_id . "'  AND type='".(int)$type."' ORDER BY sort_order ASC");

		foreach ($news_image_query->rows as $news_image) {
			$news_image_description_data = [];

			$news_image_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_image_description WHERE news_image_id = '" . (int)$news_image['news_image_id'] . "' AND appointment_id = '" . (int)$appointment_id . "'");

			foreach ($news_image_description_query->rows as $news_image_description) {
				$news_image_description_data[$news_image_description['language_id']] = array('title' => $news_image_description['title'],'subtitle' => $news_image_description['subtitle'],'description' => $news_image_description['description']);
			}

			$news_image_data[] = array(
				'news_image_description' => $news_image_description_data,
				'type'                     => $news_image['type'],
				'image'                    => $news_image['image'],
				'sort_order'               => $news_image['sort_order']
			);
		}

		return $news_image_data;
	}
		
	public function getTotalAppointments():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "appointment WHERE store_id = '" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}

	public function createAppointment($time,$date):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "appointment SET store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "'");

		$appointment_id = $this->db->getLastId();
		return $appointment_id;
	}
	
	public function isAppointmentInstore($appointment_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "appointment WHERE appointment_id = '" . (int)$appointment_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installAppointment():void{
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."appointment'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = [];
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "appointment` (`appointment_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "appointment_description` (`appointment_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "appointment` ADD PRIMARY KEY (`appointment_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "appointment_description` ADD PRIMARY KEY (`appointment_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "appointment` MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;";
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}