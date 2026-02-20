<?php
namespace Opencart\Admin\Model\Bytao;
class Newsletter extends \Opencart\System\Engine\Model {

 	/* NEWSLETTER */
	public function installModule():void {

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."newsletter_email'";
		$query = $this->db->query( $sql );
		if( count($query->rows) <=0 )
			$this->createTables();
	}

	protected function createTables(){
		$sql = [];

		$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsletter_email` (
                  `email_id` int(11) NOT NULL AUTO_INCREMENT,
                  `template_id` int(11) DEFAULT NULL,
                  `language_id` int(11) DEFAULT NULL,
                  `subject` varchar(200) DEFAULT NULL,
                  `attach` varchar(200),
                  `message` text,
                  `customer_group_id` int(11) DEFAULT NULL,
                  `affiliate` varchar(255),
                  `customer` varchar(255),
                  `product` varchar(255),
                  `defined` varchar(255),
                  `special` varchar(255),
                  `latest` varchar(255),
                  `popular` varchar(255),
                  `defined_categories` varchar(255),
                  `categories` varchar(255) DEFAULT NULL,
                  `defined_products` varchar(255),
                  `defined_products_more` varchar(255),
                  `only_selected_language` int(11) DEFAULT NULL,
                  `store_id` int(11) DEFAULT NULL,
                  `to` varchar(200),
                  `date_added` datetime DEFAULT NULL,
                  PRIMARY KEY (`email_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

		";
		$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsletter_subscribe` (
                      `subscribe_id` int(11) NOT NULL AUTO_INCREMENT,
                      `customer_id` int(11) DEFAULT '0',
                      `store_id` int(11) DEFAULT NULL,
                      `email` varchar(200) DEFAULT NULL,
                      `action` tinyint(4) DEFAULT '1',
                      PRIMARY KEY (`subscribe_id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
		";
		$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsletter_template` (
                  `template_id` int(11) NOT NULL AUTO_INCREMENT,
                  `store_id` int(11) DEFAULT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `hits` tinyint(4) DEFAULT '0',
                  `template_file` varchar(200) DEFAULT NULL,
                  `is_default` tinyint(1) DEFAULT '0',
                  `date_added` datetime DEFAULT NULL,
                  `ordering` int(11) DEFAULT NULL,
                  `date_modified` datetime DEFAULT NULL,
                  PRIMARY KEY (`template_id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                ";
		$sql[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."newsletter_template_description` (
                  `template_id` int(11) DEFAULT NULL,
                  `language_id` int(11) DEFAULT NULL,
                  `subject` varchar(200) DEFAULT NULL,
                  `template_message` text
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
				
		$sql[] = "CREATE TABLE `".DB_PREFIX."newsletter_history` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `language_id` int(11) NOT NULL,
				  `template_id` int(11) NOT NULL,
				  `public_id` int(11) NOT NULL,
				  `store_id` int(11) NOT NULL,
				  `to` varchar(255) NOT NULL,
				  `subject` text,
				  `message` text,
				  `date_added` datetime DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		foreach( $sql as $q ){
			$query = $this->db->query( $q );
		}

	}
	
	public function addNewsletter($data){
		
	}
	
	public function getBody($data) {
		$body = '';
		$this->load->model("bytaonewsletter/template");
		$template = $this->model_bytaonewsletter_template->getTemplate($data['template_id']);

     $d = array(
        'template_description' => [],

    );

    $template = $this->model_bytaonewsletter_template->getTemplate($data['template_id']);

    $template = array_merge($d,$template);

    if( empty($template['template_description']) ){ 
        $template['template_description'][$data['language_id']]['template_message']  = '{messages}';
    }


		$template_description = $template['template_description'][$data['language_id']];
    $data['message'] = str_replace( "{messages}", "", $data['message']);
		$body = str_replace( "{messages}", $data['message'], $template_description['template_message'] );
		
		$setting = array('thead' => '',
					'tprice' => $this->language->get('text_send_email_tprice'),
					'tspecial' => $this->language->get('text_send_email_tspecial'),
				);
		
		$lstproducts = $data['lstproduct'];
		
		$this->load->model("bytaonewsletter/product");
		if(isset($lstproducts['selected'])) {
			$setting['thead'] = $this->language->get('text_send_email_selected');
			$html_selected = $this->model_bytaonewsletter_product->genListProducts($lstproducts['selected'], $setting);
			$body = str_replace( "{products}", $html_selected, $body );
			
		} else {
			$body = str_replace( "{products}", '',$body );
		}
		if(isset($lstproducts['special'])) {
			$setting['thead'] = $this->language->get('text_send_email_special');
			$html_special = $this->model_bytaonewsletter_product->genListProducts($lstproducts['special'], $setting);
			$body = str_replace( "{special}", $html_special,$body );
		} else {
			$body = str_replace( "{special}", '',$body );
		}
		if(isset($lstproducts['latest'])) {
			$setting['thead'] = $this->language->get('text_send_email_latest');
			$html_latest = $this->model_bytaonewsletter_product->genListProducts($lstproducts['latest'], $setting);  
			$body = str_replace( "{latest}", $html_latest,$body );
		} else {
			$body = str_replace( "{latest}", '',$body );
		}
		if(isset($lstproducts['popular'])) {
			$setting['thead'] = $this->language->get('text_send_email_popular');
			$html_popular = $this->model_bytaonewsletter_product->genListProducts($lstproducts['popular'], $setting);
			$body = str_replace( "{popular}", $html_popular,$body );
		} else {
			$body = str_replace( "{popular}", '',$body );
		}
		if(isset($lstproducts['category'])) {
			$setting['thead'] = $this->language->get('text_send_email_category');
			$html_category = $this->model_bytaonewsletter_product->genListProducts($lstproducts['category'], $setting);
			$body = str_replace( "{products_form_categories}", $html_category,$body );
		} else {
			$body = str_replace( "{products_form_categories}", '',$body );
		}
    
		return $body;
	}
	
	public function send($data) {
        require_once(DIR_SYSTEM . 'library/mail_pav.php');
        
        $message  = '<html dir="ltr" lang="en">' . PHP_EOL;
        $message .= '<head>' . PHP_EOL;
        $message .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . PHP_EOL;
        $message .= '<title>' . $data['subject'] . '</title>' . PHP_EOL;
        $message .= '</head>' . PHP_EOL;
		$body = html_entity_decode($this->getBody($data), ENT_QUOTES, 'UTF-8');
		
		
        $message .= '<body style="padding:0;margin:0;">' . $body . '</body>' . PHP_EOL;
        $message .= '</html>' . PHP_EOL;

        $message = str_replace(array(chr(3)), '', $message);		

        $emails_count = count($data['emails']);

        $newsletter_id = $this->addHistory(array(
                            'to' => $data['to'],
                            'subject' => $data['subject'],
                            'message' => $data['message'],
                            'store_id' => $data['store_id'],
                            'template_id' => $data['template_id'],
                            'language_id' => $data['language_id'],
							'date_added' => date('Y-m-d H:i:s'),
                            'queue' => ($this->config->get('ne_throttle') && ($emails_count > $this->config->get('ne_throttle_count'))) ? 0 : $emails_count,
                            'recipients' => $emails_count
                        ));

        $attachments = [];
        $attachments_count = count($data['attachments_upload']);

        if ($attachments_count && $data['attachments_count']) {
            for ($i=0; $i < $attachments_count; $i++) {
                if (is_uploaded_file($data['attachments_upload']['attachment_'.$i]['tmp_name'])) {
                    $filename = $data['attachments_upload']['attachment_'.$i]['name'];
                    
                    $path = dirname(DIR_DOWNLOAD) . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $newsletter_id;

                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    
                    if (is_dir($path)) {
                        move_uploaded_file($data['attachments_upload']['attachment_'.$i]['tmp_name'], $path . DIRECTORY_SEPARATOR . $filename);
                    }
                    
                    if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                        $attachments[] = array(
                            'filename' => $filename,
                            'path'     => $path . DIRECTORY_SEPARATOR . $filename
                        );
                    }
                }
            }
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $store_url = (defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG);
        } else {
            $store_url = HTTP_CATALOG;
        }

        if (isset($data['store_id']) && $data['store_id'] > 0) {
            $this->load->model('setting/store');
            $store = $this->model_setting_store->getStore($this->request->post['store_id']);
            if ($store) {
                $url = rtrim($store['url'], '/') . '/';
            } else {
                $url = $store_url;
            }
        } else {
            $url = $store_url;
        }

        $dom = new DOMDocument;
		libxml_use_internal_errors(true);
        $dom->loadHTML($message);
		libxml_clear_errors();
		/*
        foreach ($dom->getElementsByTagName('a') as $node) {
            if ($node->hasAttribute('href')) {
                $link = $node->getAttribute('href');
                if ((strpos($link, 'http://') === 0) || (strpos($link, 'https://') === 0)) {
                    $add_key = ((strpos($link, '{key}') !== false) || (strpos($link, '%7Bkey%7D') !== false));
                    $node->setAttribute('href', $url );
                }
            }
        }*/
        
        $message = $dom->saveHTML();

        $this->load->model('setting/store');

        $store_info = $this->model_setting_store->getStore($data['store_id']);
        if ($store_info) {
            $store_name = $store_info['name'];
        } else {
            $store_name = $this->config->get('config_name');
        }

        $this->load->model('setting/setting');
        $store_info = $this->model_setting_setting->getSetting('config', $data['store_id']);

        foreach ($data['emails'] as $email => $info) {

            if ($this->config->get('ne_throttle')) {
                if ($emails_count > $this->config->get('ne_throttle_count')) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "ne_queue SET email = '" . $this->db->escape($email) . "', firstname = '" . $this->db->escape($info['firstname']) . "', lastname = '" . $this->db->escape($info['lastname']) . "', history_id = '" . $this->db->escape($newsletter_id) . "'");
                    continue;
                }
            }

            $mail = new Mail_PAV();
            if ($this->config->get('ne_use_smtp')) {
                $mail_config = $this->config->get('ne_smtp');
                $mail->protocol = $mail_config[$data['store_id']]['protocol'];
                $mail->parameter = $mail_config[$data['store_id']]['parameter'];
                $mail->hostname = $mail_config[$data['store_id']]['host'];
                $mail->username = $mail_config[$data['store_id']]['username'];
                $mail->password = $mail_config[$data['store_id']]['password'];
                $mail->port = $mail_config[$data['store_id']]['port'];
                $mail->timeout = $mail_config[$data['store_id']]['timeout'];
                $mail->setFrom($mail_config[$data['store_id']]['email']);
            } else {
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
                $mail->setFrom($store_info['config_email']);
            }
            $mail->setTo($email);
            if ($this->config->get('ne_bounce')) {
                $mail->setReturn($this->config->get('ne_bounce_email'));
            }
            $mail->setSender($store_name);

            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['filename']);
            }

            $subject_to_send = $data['subject'];
            $message_to_send = str_replace(array('{key}', '%7Bkey%7D'), md5($this->config->get('ne_key') . $email), $message);

            if ($info) {
                $firstname = mb_convert_case($info['firstname'], MB_CASE_TITLE, 'UTF-8');
                $lastname = mb_convert_case($info['lastname'], MB_CASE_TITLE, 'UTF-8');

                $subject_to_send = str_replace(array('{name}', '{lastname}', '{email}'), array($firstname, $lastname, $email), $subject_to_send);
                $message_to_send = str_replace(array('{name}', '{lastname}', '{email}'), array($firstname, $lastname, $email), $message_to_send);

                $treward = $this->language->get("text_send_mail_treward");
				$reward = $treward . (isset($info['reward'])? $info['reward'] : 0);
				$subject_to_send = str_replace('{reward}', $reward, $subject_to_send);
				$message_to_send = str_replace('{reward}', $reward, $message_to_send);
            }
            
            
            $message_to_send = html_entity_decode($message_to_send, ENT_QUOTES, 'UTF-8');

            $mail->setSubject($subject_to_send);
            $mail->setHtml($message_to_send);

            $send_ok = $mail->send();

            $reties = (int)$this->config->get('ne_sent_retries');
            while (!$send_ok && $reties) {
                $send_ok = $mail->send();
                $reties--;
            }
        }

        if ($this->config->get('ne_throttle')) {
            if ($emails_count > $this->config->get('ne_throttle_count')) {
                $this->session->data['success'] = $this->language->get('text_throttle_success');
            } else {
                $this->session->data['success'] = $this->language->get('text_success');
            }
        } else {
            $this->session->data['success'] = $this->language->get('text_success');
        }

        if (count($data['emails']) == 1 && array_key_exists('check@isnotspam.com', $data['emails'])) {
            $this->session->data['success'] = sprintf($this->language->get('text_success_check'), $store_info['config_email']);
        }
    }
    
    public function addHistory(array $data):int {
        $this->db->query("INSERT INTO " . DB_PREFIX . "newsletter_history SET `to` = '" . $this->db->escape($data['to']) . "', public_id = '" . $this->db->escape(md5($data['subject'] . time())). "', store_id = '" . (int)$this->session->data['store_id'] . "', template_id = '" . (int)$data['template_id'] . "', language_id = '" . (int)$data['language_id'] . "', subject = '" . $this->db->escape($data['subject']) . "', message = '" . $this->db->escape($data['message']) . "', date_added='" .$data['date_added']. "'");
        $newsletter_id = $this->db->getLastId();
        return $newsletter_id;
    }
    
    public function getRecipientsWithRewardPoints() {
        $query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, cr.points FROM `" . DB_PREFIX . "customer` AS c INNER JOIN (SELECT customer_id, SUM(points) AS points FROM " . DB_PREFIX . "customer_reward GROUP BY customer_id) AS cr ON cr.customer_id = c.customer_id AND cr.points > '0' LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON c.email = ps.email WHERE ps.store_id='".(int)$this->session->data['store_id']."'");
        return isset($query->rows)?$query->rows:[];
    }

    public function getSubscribedRecipientsWithRewardPoints():array {
        $query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, cr.points FROM `" . DB_PREFIX . "customer` AS c INNER JOIN (SELECT customer_id, SUM(points) AS points FROM " . DB_PREFIX . "customer_reward GROUP BY customer_id) AS cr ON cr.customer_id = c.customer_id AND cr.points > '0' LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON c.email = ps.email WHERE c.newsletter = '1'");
        return isset($query->rows)?$query->rows:[];
    }

    public function getCustomers($data = []):array {
        $sql = "SELECT c.*, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON c.email = ps.email WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ps.store_id='".(int)$this->session->data['store_id']."'";

        $implode = [];

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
            $implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        if (!empty($data['filter_ip'])) {
            $implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
            $implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'name',
            'c.email',
            'customer_group',
            'c.status',
            'c.approved',
            'c.ip',
            'c.date_added'
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
        //echo $sql;die();
        $query = $this->db->query($sql);

        return isset($query->rows)?$query->rows:[];
    }

    public function getCustomer($customer_id):array {
        $query = $this->db->query("SELECT DISTINCT c.* FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON c.email = ps.email WHERE c.customer_id = '" . (int)$customer_id . "'  AND ps.store_id=".(int)$this->session->data['store_id']."'");

        return isset($query->row)?$query->row:[];
    }

    public function getAffiliates($data = []):array {
        $sql = "SELECT *, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM " . DB_PREFIX . "affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance FROM " . DB_PREFIX . "affiliate a LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON a.email = ps.email AND ps.store_id=".(int)$this->session->data['store_id']."'";

        $implode = [];

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(a.firstname, ' ', a.lastname) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "LCASE(a.email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
        }

        if (!empty($data['filter_code'])) {
            $implode[] = "a.code = '" . $this->db->escape($data['filter_code']) . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
            $implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(a.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'name',
            'a.email',
            'a.code',
            'a.status',
            'a.approved',
            'a.date_added'
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

        return isset($query->rows)?$query->rows:[];
    }

    public function getAffiliate($affiliate_id):array {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate a LEFT JOIN " . DB_PREFIX . "newsletter_subscribe ps ON a.email = ps.email WHERE affiliate_id = '" . (int)$affiliate_id . "' AND ps.store_id=".(int)$this->session->data['store_id']."'");

        return isset($query->row)?$query->row:[];
    }

	
	/* SUBSCRIBE */
	public function buildQuery($data = [], $get_total = false):string {		
		if($get_total){
			$sql = "SELECT count(s.subscribe_id) AS total
				FROM ".DB_PREFIX."newsletter_subscribe AS s
				LEFT JOIN ".DB_PREFIX."customer AS c ON s.customer_id = c.customer_id ";
		}else{
			$sql = "SELECT s.*,c.telephone,c.newsletter,c.customer_group_id,c.status,CONCAT(c.firstname, ' ', c.lastname) AS name 
				FROM ".DB_PREFIX."newsletter_subscribe AS s
				LEFT JOIN ".DB_PREFIX."customer AS c ON s.customer_id = c.customer_id ";
		}
		
		$limit_start = 0;
		$limit_end = isset($data['limit'])?$data['limit']:20;
		
		if(isset($data['page'])){
			$limit_start = (int)($data['page'] -1) * (int)$limit_end;
			$limit_start = $limit_start < 0 ? 0 :$limit_start;
		}
		
		$limit = " LIMIT ".$limit_start.",".$limit_end;

		$array_sorts = array("name",
							 "email",
							 "customer_group_id",
							 "s.action",
							 "s.store_id");
		$sort = "name";
		$order = "ASC";
		if(isset($data['sort'])){
			$sort = in_array($data['sort'], $array_sorts)?$data['sort']:$sort;
			$order = (isset($data['order'])&&!empty($data['order']))?$data['order']:$order;
		}
		$ordering = " ORDER BY `".$sort."` ".$order;
		$where = [];
		
		if(isset($data['filter'])){
			foreach($data['filter'] as $key=>$val){
				if(($key == "name" || $key == "email")){
					if(strlen($val) < 3)
						continue;
					if($key =="name")
						$key = " CONCAT(c.firstname, ' ', c.lastname) ";
					else
						$key = " s.{$key} ";
					$where[] = " {$key} LIKE '%".$this->db->escape($val)."%'";
				}elseif($val != NULL && $val !=""){
					$where[] = " {$key}=".$this->db->escape($val);
				}
			}
		}
		
		$where[] = " s.store_id=".(int)$this->session->data['store_id'];
		
		if($get_total){
			$limit = "";
			$ordering = "";
		}
		
		$sql .= !empty($where)?" WHERE ".implode(" AND ",$where):"".$ordering.$limit;

		return $sql;
	}
	
	public function getTotalSubscribers($data = []):int {
		$sql = $this->buildQuery($data, true);
		$query = $this->db->query($sql);
		if($query->num_rows >0){
			return $query->row['total'];
		}
		return 0;
	}
	
	public function getSubscribers(array $data=[]):array {		
		$sql = $this->buildQuery($data);
		$query = $this->db->query($sql);
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getCustomerGroups():array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY cg.sort_order ASC, cgd.name ASC");
		
		return isset($query->rows)?$query->rows:[];
	}
	
	public function updateAction($subscribe_id, $action = 1):bool{
		$query = $this->db->query("UPDATE ".DB_PREFIX."newsletter_subscribe SET `action`=".(int)$action." WHERE subscribe_id=".$subscribe_id);
		return true;
	}

	public function delete($subscribe_id):void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "newsletter_subscribe` WHERE subscribe_id = '" . (int)$subscribe_id . "'");
	}
	
	/* TEMPLATE */
	public function getTemplates($data = []):array {
		$sql = "SELECT * FROM ".DB_PREFIX."newsletter_template WHERE store_id='".(int)$this->session->data['store_id']."' LIMIT 0,20";
		$query = $this->db->query($sql);
		return isset($query->rows)?$query->rows:[];
	}
	
	public function getTemplate($template_id):array {
		$data = [];
		if(!empty($template_id)){
			$sql = "SELECT t.* FROM ".DB_PREFIX."newsletter_template AS t 
					WHERE t.template_id = ".$template_id;
			$query =  $this->db->query($sql);
			if($query->num_rows > 0){
				$data = $query->row;
				$sql2 = "SELECT td.* FROM ".DB_PREFIX."newsletter_template_description AS td WHERE td.template_id = ".$template_id;
				$query = $this->db->query($sql2);

				$data2 = ($query->num_rows > 0)?$query->rows:[];
				$languages = [];
				if($data2){
					foreach($data2 as $language){
						$languages[$language["language_id"]] = $language;
					}	
				}
				$data["template_description"] = $languages;
				
			}
		}
		return $data;
	}
	
	public function insertTemplate($data = []):bool{
		if(!empty($data["template"]["template_id"])){
			$sql = "UPDATE ".DB_PREFIX."newsletter_template SET ";
			$tmp = [];
			foreach( $data["template"] as $key => $value ){
				if( $key != "template_id" ){
					$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
				}
			}
			$tmp[]=" date_modified = NOW() ";
			$sql .= implode( " , ", $tmp );
			$sql .= " WHERE `template_id`=".$data["template"]["template_id"];
			$this->db->query($sql);
			$template_id = $data["template"]["template_id"];
		}else{
			$sql = "INSERT INTO ".DB_PREFIX."newsletter_template ( `";

			$tmp = [];
			unset($data["template"]["template_id"]);
			$vals = [];
			foreach( $data["template"] as $key => $value ){
				$vals[] = $this->db->escape($value);
				$tmp[] = $key;
			}
			$sql .= implode("` , `",$tmp)."`,`store_id`,`date_added`) VALUES ('".implode("','",$vals)."','".(int)$this->session->data['store_id']."',NOW()) ";
			
			$this->db->query($sql);
			$template_id = $this->db->getLastId();
		}
		if(!empty($template_id)){
			$sql = "DELETE FROM ".DB_PREFIX."newsletter_template_description WHERE `template_id`=".$template_id;
			$this->db->query($sql);
			$sql = "INSERT INTO ".DB_PREFIX."newsletter_template_description ( `template_id`,`language_id`,`subject`,`template_message` ) VALUES ";

			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();
			$tmp = [];
			foreach ($languages as $language) {
				$tmp[] = "(".$this->db->escape($template_id).",".$this->db->escape($language['language_id']).",'".$this->db->escape($data['template_description']['subject'][$language['language_id']])."','".$this->db->escape($data['template_description']['template_message'][$language['language_id']])."')";
			}
			$sql .= implode(",", $tmp).";";
			$this->db->query($sql);
			return true;
		}
		return false;
		
	}
	
	public function deleteTemplate($templates = []):bool{
		$check = false;
		if(!empty($templates)){
			
			foreach($templates as $template){
				$sql = "DELETE FROM ".DB_PREFIX."newsletter_template WHERE template_id = ".(int)$template;
				$sql2 = "DELETE FROM ".DB_PREFIX."newsletter_template_description WHERE template_id=".(int)$template;
				if($this->db->query($sql) && $this->db->query($sql2)){
					$check = true;
				}
			}
		}
		return $check;
	}
	
	public function copyTemplate($templates = []):bool{
		if(!empty($templates)){
			$check = false;
			foreach($templates as $template){
				$sql = "SELECT t.* FROM ".DB_PREFIX."newsletter_template AS t
						 WHERE t.template_id=".$template;
				$query = $this->db->query($sql);
				$data = [];
				if($query->num_rows){
					
					$data["template"] = $query->row;
					$data["template_description"] = [];
					unset($data["template"]["template_id"]);
					unset($data["template"]["date_added"]);
					unset($data["template"]["date_modified"]);
					$sql = "SELECT td.language_id,td.subject,td.template_message FROM ".DB_PREFIX."newsletter_template_description AS td
							WHERE td.template_id = ".$template;
					$query2 = $this->db->query($sql);
					if($query2->num_rows){
						foreach($query2->rows as $row){
							$data["template_description"]['subject'][$row["language_id"]] = $row["subject"];
							$data["template_description"]['template_message'][$row["language_id"]] = $row["template_message"];
						}
					}
					
				}

				if(!empty($data) && $this->insertTemplate($data)){
					$check = true;
				}

			}
			return $check;
		}
		return false;
	}

	/* DRAFT */
	public function getTotalDraft($data = []):int {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "newsletter_email` WHERE 1=1";
		
		if (isset($data['filter_date']) && !is_null($data['filter_date'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date']) . "')";
		}
		
		if (isset($data['filter_subject']) && !is_null($data['filter_subject'])) {
			$sql .= " AND LCASE(subject) LIKE '" . $this->db->escape(mb_strtolower($data['filter_subject'], 'UTF-8')) . "%'";
		}

		if (isset($data['filter_to']) && !is_null($data['filter_to'])) {
			$sql .= " AND `to` = '" . $this->db->escape($data['filter_to']) . "'";
		}

		
		$sql .= " AND `store_id` = '" . (int)$this->session->data['store_id'] . "'";
	
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function getListDraft($data = []):array {
		if ($data) {
			$sql = "SELECT `email_id`, `subject`, `date_added`, `to`, `store_id` FROM " . DB_PREFIX . "newsletter_email WHERE 1=1"; 
			
			if (isset($data['filter_date']) && !is_null($data['filter_date'])) {
				$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date']) . "')";
			}

			if (isset($data['filter_subject']) && !is_null($data['filter_subject'])) {
				$sql .= " AND LCASE(subject) LIKE '" . $this->db->escape(mb_strtolower($data['filter_subject'], 'UTF-8')) . "%'";
			}

			if (isset($data['filter_to']) && !is_null($data['filter_to'])) {
				$sql .= " AND `to` = '" . $this->db->escape($data['filter_to']) . "'";
			}

			$sql .= " AND `store_id` = '" . (int)$this->session->data['store_id'] . "'";
			
			$sort_data = array(
				'email_id',
				'subject',
				'date_added',
				'to',
				'store_id'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY `" . $data['sort'] . "`";	
			} else {
				$sql .= " ORDER BY date_added";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'ASC')) {
				$sql .= " ASC";
			} else {
				$sql .= " DESC";
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
			
			$results = $query->rows;
			
			return $results;
		} else {
			return [];
		}
	}

	public function detailDraft($email_id):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsletter_email WHERE email_id = '" . (int)$email_id . "'"); 
		$data = $query->row;

		if ($data) {
			$path = dirname(DIR_DOWNLOAD) . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . 'pavdraft_' . $data['email_id'];
			if (file_exists($path) && is_dir($path)) {
				$data['attachments'] = $this->attachments($path);
			} else {
				$data['attachments'] = [];
			}
		}

		$data['customer'] = unserialize($data['customer']);
		$data['affiliate'] = unserialize($data['affiliate']);
		$data['product'] = unserialize($data['product']);
		$data['defined_product'] = unserialize($data['defined_products']);
		$data['defined_product_more'] = unserialize($data['defined_products_more']);
		$data['defined_category'] = unserialize($data['defined_categories']);

		return $data;
	}

	public function saveDraft($data):int {

		$tmp =  [
			'template_id'=>0,
			'defined_product' => '',
			'defined_category' => '',
			'defined' => '',
			'defined_categories' => '',
			'defined_product_more' => '',
			'defined_products' => '',
		];
		$data = array_merge( $tmp, $data );

		if (isset($data['id']) && $data['id']) {
			$this->db->query("UPDATE " . DB_PREFIX . "newsletter_email SET `to` = '" . $this->db->escape($data['to']) . "', `template_id` = ".(int)$data['template_id'].",`language_id`= ".(int)$data['language_id'].", `subject`='".$this->db->escape($data['subject'])."', `message`='".$this->db->escape($data['message'])."',`customer_group_id`=".(int)$data['customer_group_id'].",`affiliate`='".$this->db->escape((isset($data['affiliate']) && $data['to'] == 'affiliate') ? serialize($data['affiliate']) : '')."',`customer`='". $this->db->escape((isset($data['customer']) && $data['to'] == 'customer') ? serialize($data['customer']) : '') ."', `product` = '" . $this->db->escape((isset($data['product']) && $data['to'] == 'product') ? serialize($data['product']) : '') . "', defined = '" . $this->db->escape($data['defined']) . "', special = '" . $this->db->escape($data['special']) . "',  latest = '" . $this->db->escape($data['latest']) . "',popular = '" . $this->db->escape($data['popular']) . "', `defined_categories` = '" . $this->db->escape(isset($data['defined_category']) ? serialize($data['defined_category']) : '') . "', categories = '" . $this->db->escape($data['defined_categories']) . "',`defined_products_more` = '" . $this->db->escape(isset($data['defined_product_more']) ? serialize($data['defined_product_more']) : '') . "', `defined_products` = '" . $this->db->escape(isset($data['defined_product']) ? serialize($data['defined_product']) : '') . "', `only_selected_language` = '" . (int)$data['only_selected_language'] . "'WHERE `email_id`=".(int)$data['id']);
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "newsletter_email SET `date_added`=NOW(),`to` = '" . $this->db->escape($data['to']) . "', store_id = '" . $sql .= " AND `store_id` = '" . (int)$this->session->data['store_id'] . "', `template_id` = ".(int)$data['template_id'].",`language_id`= ".(int)$data['language_id'].", `subject`='".$this->db->escape($data['subject'])."', `message`='".$this->db->escape($data['message'])."',`customer_group_id`=".(int)$data['customer_group_id'].",`affiliate`='".$this->db->escape((isset($data['affiliate']) && $data['to'] == 'affiliate') ? serialize($data['affiliate']) : '')."',`customer`='". $this->db->escape((isset($data['customer']) && $data['to'] == 'customer') ? serialize($data['customer']) : '') ."', `product` = '" . $this->db->escape((isset($data['product']) && $data['to'] == 'product') ? serialize($data['product']) : '') . "', defined = '" . $this->db->escape($data['defined']) . "', special = '" . $this->db->escape($data['special']) . "',  latest = '" . $this->db->escape($data['latest']) . "',popular = '" . $this->db->escape($data['popular']) . "', `defined_categories` = '" . $this->db->escape(isset($data['defined_category']) ? serialize($data['defined_category']) : '') . "', categories = '" . $this->db->escape($data['defined_categories']) . "',`defined_products_more` = '" . $this->db->escape(isset($data['defined_product_more']) ? serialize($data['defined_product_more']) : '') . "', `defined_products` = '" . $this->db->escape(isset($data['defined_product']) ? serialize($data['defined_product']) : '') . "', `only_selected_language` = '" . (int)$data['only_selected_language'] . "'");
			$data['id'] = $this->db->getLastId();
		}
		return $data['id'];
	}

	public function deleteDraft($email_id):void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "newsletter_email WHERE email_id = '" . (int)$email_id . "'");

		$path = dirname(DIR_DOWNLOAD) . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . 'pavdraft_' . $email_id;
		if (file_exists($path) && is_dir($path)) {
			$this->rrmdir($path);
		}
	}

	private function rrmdirDraft($dir):void {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file)) {
				$this->rrmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dir);
	}

	private function attachmentsDraft($dir):array {
		$attachments = [];

		$files = (array) glob($dir);
		if (!empty($files))
			foreach ($files as $file) {
				$attachments[] = [
					'filename' => basename($file),
					'path'     => $file
				];
			}

		return $attachments;
	}

	/* SEO URL */
	public function getUrlAlias($keyword):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($keyword) . "'AND language_id='$language_id' AND store_id='".(int)$this->session->data['store_id']."' ");

		return isset($query->row)?$query->row:[];
	}
	
	public function getNewUrlAlias($keyword,$language_id):array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias uk WHERE keyword = '" . $this->db->escape($keyword) . "' AND language_id='$language_id' AND store_id='".(int)$this->session->data['store_id']."' ");

		return isset($query->row)?$query->row:[];
	}


	/* PRODUCT */
	public function getProduct($product_id):array {
	
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id) AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return [];
		}
	}

	public function getProducts($data = []):array {
		
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special"; 
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";			
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}
		
			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";	
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";			
			}	
		
			if (!empty($data['filter_filter'])) {
				$implode = [];
				
				$filters = explode(',', $data['filter_filter']);
				
				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}
				
				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";				
			}
		}	

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";
		}
					
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		$sql .= " GROUP BY p.product_id";
		
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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
		
		$product_data = [];
				
		$query = $this->db->query($sql);
	
		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}
	
	public function getProductSpecials($data = []) {
		
		$customer_group_id	= 0;	
		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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

		$product_data = [];
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
		
		return $product_data;
	}
		
	public function getLatestProducts($limit):array {
		$customer_group_id	= 0;
				
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) { 
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);
		 	 
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}
	
	public function getPopularProducts($limit):array {
		$product_data = [];
		
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed, p.date_added DESC LIMIT " . (int)$limit);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
					 	 		
		return $product_data;
	}

	public function getBestSellerProducts($limit):array {
		$customer_group_id = 0;
		$product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) { 
			$product_data = [];
			
			$query = $this->db->query("SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);
			
			foreach ($query->rows as $result) { 		
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}
	
	public function getProductRelated($product_id):array {
		$product_data = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		foreach ($query->rows as $result) { 
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}
		
		return $product_data;
	}
		
	public function getTotalProducts(array $data):int {

		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total"; 
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";			
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}
		
			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";	
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";			
			}	
		
			if (!empty($data['filter_filter'])) {
				$implode = [];
				
				$filters = explode(',', $data['filter_filter']);
				
				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}
				
				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";				
			}
		}
		
		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}
		
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";				
		}
		
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function getTotalProductSpecials():int {

		
		$query = $this->db->query("SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))");
		
		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;	
		}
	}
	
	public function genListProducts(array $data,array $setting):string  {
		$html = '';
		if (!empty($data)) {
			$html .= '<h3>'.$setting['thead'].'</h3>';
			$html .= '<table border="0" cellpadding="1" cellspacing="1" style="width: 600px;">';
			foreach ($data as $item) {
				$html .= '<tr>';
				$html .= '<td><a target="_blank" href="'.$item['href'].'"><img title="' .$item['name']. '" src="'.$item['thumb'].'"/></a></td>';
				$html .= '<td><a target="_blank" href="'.$item['href'].'">' .$item['name']. '</a></td>';
				if(!empty($item['price'])){
					$html .= '<td>' .$setting['tprice'] . $item['price']. '</td>';
					if(!empty($item['special'])){
						$html .= '<td>' .$setting['tspecial'] . $item['special']. '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		
		return $html;
	}

	/* Module */
	public function getNewsletterModule(int $module_id):array {
		$sql="SELECT * FROM " . DB_PREFIX . "newsletter_module WHERE newsletter_module_id='".(int)$module_id."' LIMIT 1";
		$query = $this->db->query($sql);
		return isset($query->row)?$query->row:[];
	}
	
	public function getNewsletterModuleDescription (int $module_id):array {
		$descriptionData=[];
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsletter_module_description WHERE newsletter_module_id='".(int)$module_id."'");
		foreach($query->rows as $ROW ){
			$descriptionData[$ROW['language_id']]=[
				'description' => $ROW['description'],
				'social' => $ROW['social']
			];
		}
		
		return $descriptionData;
	}
	
	public function getNewsletterModules():array {
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		$sql="SELECT * FROM " . DB_PREFIX . "newsletter_module  WHERE store_id='".(int)$this->session->data['store_id']."'";
		$query = $this->db->query($sql);
		return isset($query->rows)?$query->rows:[];
	}
	
	public function updateNewsletterModule($data=[]){
		$store_id=isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		if(!empty($data["newsletter_module_id"])&& ["newsletter_module_id"]!='0'){
			$sql = "UPDATE ".DB_PREFIX."newsletter_template SET ";
			$tmp = [];
			foreach( $data["module"] as $key => $value ){
				if( $key != "newsletter_module_id" ){
					$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
				}
			}
			$sql .= implode( " , ", $tmp );
			$sql .= " WHERE `newsletter_module_id`=".$data["template"]["newsletter_module_id"];
			$this->db->query($sql);
			$newsletter_module_id = $data["module"]["newsletter_module_id"];
		}else{
			$sql = "INSERT INTO ".DB_PREFIX."newsletter_module ( `";

			$tmp = [];
			unset($data["module"]["newsletter_module_id"]);
			$vals = [];
			foreach( $data["module"] as $key => $value ){
				$vals[] = $this->db->escape($value);
				$tmp[] = $key;
			}
			$sql .= implode("` , `",$tmp)."`,`store_id`) VALUES ('".implode("','",$vals)."','".(int)$store_id."') ";
			
			$this->db->query($sql);
			$newsletter_module_id = $this->db->getLastId();
		}
		
		if(!empty($template_id)){
			$sql = "DELETE FROM ".DB_PREFIX."newsletter_module_description WHERE `newsletter_module_id`=".$template_id;
			$this->db->query($sql);
			$sql = "INSERT INTO ".DB_PREFIX."newsletter_module_description ( `newsletter_module_id`,`language_id`,`description`,`social` ) VALUES ";

			$this->load->model('bytao/common');
			$languages = $this->model_bytao_common->getStoreLanguages();
			$tmp = [];
			foreach ($languages as $language) {
				$tmp[] = "(".$this->db->escape($template_id).",".$this->db->escape($language['language_id']).",'".$this->db->escape($data['module_description'][$language['language_id']]['description'])."','".$this->db->escape($data['module_description'][$language['language_id']]['social'])."')";
			}
			$sql .= implode(",", $tmp).";";
			$this->db->query($sql);
			return true;
		}
	}
	
	
	
		
}
