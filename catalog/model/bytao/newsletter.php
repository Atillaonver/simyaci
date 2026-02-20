<?php
namespace Opencart\Catalog\Model\Bytao;
class Newsletter extends \Opencart\System\Engine\Model {	
    
    public function isNewsletterSubscribed($email):bool {    
        $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE email = '" . $email . "' AND newsletter = '1'");
        if ($query->num_rows > 0) return true;
        
        $query = $this->db->query("SELECT email FROM " . DB_PREFIX . "newsletter_subscribe WHERE email = '" . $email . "'");
        if ($query->num_rows > 0) return true;

        return false;
    }
    
	public function subscribeNewsletter(string $email):void {
	    $query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE email = '" . $email . "'");
	    if ($query->num_rows > 0) {
	        $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '1' WHERE customer_id = '" . (int)$query->row['customer_id'] . "'");
	    } else {
	        $this->db->query("INSERT INTO " . DB_PREFIX . "newsletter_subscribe (email) VALUES ('" . $email . "')");
	    }
	}
	
	public function unsubscribeNewsletter(string $email):void {
	    $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE email = '" . $email . "'");
	    $this->db->query("DELETE FROM " . DB_PREFIX . "newsletter_subscribe WHERE email = '" . $email . "'");
	}
	
	public function getNewsletterModule(int $module_id):array {
		$sql="SELECT * FROM " . DB_PREFIX . "newsletter_module WHERE newsletter_module_id='".(int)$module_id."' LIMIT 1";
		$query = $this->db->query($sql);
		return isset($query->row)?$query->row:[];
	}
	
	public function getNewsletterModuleDescription (int $module_id):array {
		$descriptionData=[];
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "newsletter_module_description WHERE newsletter_module_id='".(int)$module_id."' AND language_id='".(int)$this->config->get('config_language_id')."' LIMIT 1");
		
		
		return isset($query->row)?$query->row:[];
	}
	
	
	
}