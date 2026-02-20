<?php
namespace Opencart\Admin\Model\Bytao;
class Faq extends \Opencart\System\Engine\Model {
	
	public function addFaq($data):int {
		$this->db->query("INSERT INTO " . DB_PREFIX . "faq SET store_id='" . (int)$this->session->data['store_id'] . "', status = '" . (int)$data['status'] . "'");

		$faq_id = $this->db->getLastId();

		foreach ($data['faq_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "faq_description SET faq_id = '" . (int)$faq_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', ansver = '" . $this->db->escape($value['ansver']) . "',image = '" . $this->db->escape($value['image']) . "'");
		}
		$this->cache->delete('faq');

		return $faq_id;
	}

	public function editFaq(int $faq_id, array $data):void {
		$this->db->query("UPDATE " . DB_PREFIX . "faq SET status = '" . (int)$data['status'] . "' WHERE faq_id = '" . (int)$faq_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'");

		foreach ($data['faq_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "faq_description SET faq_id = '" . (int)$faq_id . "', language_id = '" . (int)$language_id . "', question = '" . $this->db->escape($value['question']) . "', ansver = '" . $this->db->escape($value['ansver']) . "',image = '" . $this->db->escape($value['image']) . "'");
		}

		$this->cache->delete('faq');
	}

	public function deleteFaq(int $faq_id):void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "faq` WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "faq_description` WHERE faq_id = '" . (int)$faq_id . "'");
		$this->cache->delete('faq');
	}

	public function getFaq(int $faq_id):array {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "faq WHERE faq_id = '" . (int)$faq_id . "'");

		return $query->row;
	}

	public function getFaqs($data = [] ):array {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "faq f LEFT JOIN " . DB_PREFIX . "faq_description fd ON (f.faq_id = fd.faq_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.store_id='" . (int)$this->session->data['store_id'] . "' ORDER BY f.sort_order";


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
		} else {
			$faq_data = $this->cache->get('faq.' . (int)$this->config->get('config_language_id'));

			if (!$faq_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq f LEFT JOIN " . DB_PREFIX . "faq_description fd ON (f.faq_id = fd.faq_id) WHERE fd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND f.store_id='" . (int)$this->session->data['store_id'] . "' ORDER BY f.sort_order");

				$faq_data = $query->rows;

				$this->cache->set('faq.' . (int)$this->config->get('config_language_id'), $faq_data);
			}

			return $faq_data;
		}
	}

	public function getFaqDescriptions(int $faq_id):array {
		
		$faq_description_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'");
		$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);
		foreach ($query->rows as $result) {
			
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$thumb = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$thumb = $placeholder;
			}	
			
			$faq_description_data[$result['language_id']] = array(
				'question'            => $result['question'],
				'ansver'      => $result['ansver'],
				'image'      => $result['image'],
				'thumb'      => $thumb
			);
		}

		return $faq_description_data;
	}

	public function getTotalFaqs():int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "faq WHERE store_id='" . (int)$this->session->data['store_id'] . "'");

		return $query->row['total'];
	}



	public function sortFaq(int $faq_id,int $sort_order):void {
		$this->db->query("UPDATE " . DB_PREFIX . "faq SET sort_order = '" . (int)$sort_order . "' WHERE faq_id = '" . (int)$faq_id . "'");
		
		$this->cache->delete('faq');
	}

	public function installFaq():void {
		$sql = " SHOW TABLES LIKE '".DB_PREFIX."faq'";
		$query = $this->db->query( $sql );
		
		if( count($query->rows) <=0 ){
			$sql = array();
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "faq` (`faq_id` int(11) NOT NULL,`sort_order` int(3) NOT NULL DEFAULT 0,`store_id` int(3) NOT NULL DEFAULT 0,`status` tinyint(1) NOT NULL DEFAULT 1) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="CREATE TABLE `" . DB_PREFIX . "faq_description` (`faq_id` int(11) NOT NULL,`language_id` int(11) NOT NULL,`question` text NOT NULL,`ansver` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "faq` ADD PRIMARY KEY (`faq_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "page_description` ADD PRIMARY KEY (`faq_id`,`language_id`);";
			
			$sql[]  ="ALTER TABLE `" . DB_PREFIX . "faq` MODIFY  `faq_id` int(11) NOT NULL AUTO_INCREMENT;";
			
			
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}		
	}


}