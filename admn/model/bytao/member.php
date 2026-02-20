<?php
namespace Opencart\Admin\Model\Bytao;
class Member extends \Opencart\System\Engine\Model {
	
	public function addMember($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "member SET sort_order = '" . (int)$data['sort_order'] . "',store_id = '".(int)$this->session->data['store_id']."',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "'");

		$member_id = $this->db->getLastId();
		
		foreach ($data['member_description'] as $language_id => $member_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "member_description SET member_id = '" . (int)$member_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($member_description['title']) . "', description = '" .  $this->db->escape($member_description['description']) . "'");
		}

		return $member_id;
	}

	public function editMember($member_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "member SET sort_order = '" . (int)$data['sort_order'] . "',status = '" . (int)$data['status'] . "',image = '" .  (isset($data['image'])?$this->db->escape($data['image']):'') . "',bimage = '" .  (isset($data['bimage'])?$this->db->escape($data['bimage']):'') . "',url = '" . (isset($data['url'])?$this->db->escape($data['url']):'') . "' WHERE member_id = '" . (int)$member_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "member_description WHERE member_id = '" . (int)$member_id . "'");
		foreach ($data['member_description'] as $language_id => $member_description) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "member_description SET member_id = '" . (int)$member_id . "', language_id = '" . (int)$language_id . "', title = '" .  $this->db->escape($member_description['title']) . "', description = '" .  $this->db->escape($member_description['description']) . "'");
		}
		
		return $member_id;
	}

	public function deleteMember($member_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "member WHERE member_id = '" . (int)$member_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "member_description WHERE member_id = '" . (int)$member_id . "'");
	}

	public function getMember($member_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "member  WHERE member_id = '" . (int)$member_id . "'");

		return $query->row;
	}

	public function getMembers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "member o LEFT JOIN " . DB_PREFIX . "member_description od ON o.member_id=od.member_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'";

		if(isset($data['filter_name'])){
				$sql .= "  AND (od.title LIKE '%". $this->db->escape($data['filter_name'])."%' OR  od.description LIKE '%". $this->db->escape($data['filter_name'])."%')";
				
			}
			
			$sort_data = [
				'od.title',
				'o.sort_order'
				];
				
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY o.sort_order";
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
	
	public function getMemberDescriptions($member_id) {
		$member_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "member_description WHERE member_id = '" . (int)$member_id . "'");

		foreach ($query->rows as $result) {
			$member_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'            => $result['description'],
			);
		}

		return $member_description_data;
	}
		
	public function getTotalMembers(array $data = []):int {
		$SQL = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "member o LEFT JOIN " . DB_PREFIX . "member_description od ON o.member_id=od.member_id   WHERE od.language_id= '".(int)$this->config->get('config_language_id')."' AND  o.store_id = '" . (int)$this->session->data['store_id'] . "'";
		
		if(isset($data['filter_name'])){
				$SQL .= "  AND (ad.name LIKE '%". $this->db->escape($data['filter_name'])."%' OR  ad.description LIKE '%". $this->db->escape($data['filter_name'])."%')";
				
		}
		
		$query = $this->db->query($SQL);
		
		return $query->row['total'];
	}

	public function isMemberInstore($member_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "member WHERE member_id = '" . (int)$member_id . "' AND store_id = '" . (int)$this->session->data['store_id'] . "'");
		if($query->row['total']>0)
			return false;
		else
			return true;
	}

	public function installMember(){
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."member'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "member` (`member_id` int(11) NOT NULL,`url` varchar(200) NOT NULL,`image` varchar(255) NOT NULL,`bimage` varchar(255) NOT NULL,`store_id` int(3) NOT NULL,`sort_order` int(11) NOT NULL,`status` tinyint(1) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "member_description` (`member_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`title` tinytext NOT NULL,`description` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "member` ADD PRIMARY KEY (`member_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "member_description` ADD PRIMARY KEY (`member_id`,`language_id`);";
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "member` MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;";
		
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}

	public function sortMember(int $member_id,int $sort_order):void {
		$this->db->query("UPDATE " . DB_PREFIX . "member set sort_order='".(int)$sort_order."' WHERE member_id = '" . (int)$member_id . "'");
	}
}