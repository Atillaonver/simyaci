<?php
namespace Opencart\Catalog\Model\Bytao;
class Forms extends \Opencart\System\Engine\Model {
	public function getForms(int $forms_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "forms` i LEFT JOIN `" . DB_PREFIX . "forms_description` id ON (i.`forms_id` = id.`forms_id`) WHERE i.`forms_id` = '" . (int)$forms_id . "' AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'");

		return $query->row;
	}
	

	public function getFormss(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "forms` i LEFT JOIN `" . DB_PREFIX . "forms_description` id ON (i.`forms_id` = id.`forms_id`) WHERE id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1' ORDER BY i.`sort_order`, LCASE(id.`title`) ASC");

		return $query->rows;
	}
	
	public function getFormsData(int $forms_id): array {
		$query = $this->db->query("SELECT DISTINCT i.*,(SELECT date_added FROM `" . DB_PREFIX . "forms_to_customer` f2c WHERE f2c.`customer_id` = '" . (int)$this->customer->getId() . "' AND f2c.`forms_id` = '" . (int)$forms_id . "' ) as date_added,(SELECT form_data FROM `" . DB_PREFIX . "forms_to_customer` f2c WHERE f2c.`customer_id` = '" . (int)$this->customer->getId() . "' AND f2c.`forms_id` = '" . (int)$forms_id . "' ) as form_data FROM `" . DB_PREFIX . "forms` i LEFT JOIN `" . DB_PREFIX . "forms_description` id ON (i.`forms_id` = id.`forms_id`) WHERE i.`forms_id` = '" . (int)$forms_id . "' AND id.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND i.`store_id` = '" . (int)$this->config->get('config_store_id') . "' AND i.`status` = '1'");
		
		return $query->row;
	}
	
	public function submitForms(array $fData=[]): void {
		$query = $this->db->query("SELECT COUNT(*) As Total FROM `" . DB_PREFIX . "forms_to_customer`  WHERE `forms_id` = '" . (int)$fData['forms_id']. "' AND `customer_id` = '" . (int)$this->customer->getId()."'" );

		if($query->row['Total']>0){
			$this->db->query("UPDATE `" . DB_PREFIX . "forms_to_customer` SET `forms_id` = '" . (int)$fData['forms_id'] . "', `customer_id` = '" . (int)$this->customer->getId() . "',`form_data` = '".serialize($fData)."' WHERE `forms_id` = '" . (int)$fData['forms_id']. "' AND `customer_id` = '" . (int)$this->customer->getId()."'");
		}else{
			$this->db->query("INSERT INTO `" . DB_PREFIX . "forms_to_customer` SET `forms_id` = '" . (int)$fData['forms_id'] . "', `customer_id` = '" . (int)$this->customer->getId() . "',`form_data` = '".serialize($fData)."',`date_added` = NOW() ");
		}
	}

}
