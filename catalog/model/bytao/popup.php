<?php
namespace Opencart\Catalog\Model\Bytao;
class Popup extends \Opencart\System\Engine\Model {
	
	public function getPopup():array  {
		$sql = "SELECT p.*,pd.description AS updescription, pd.status AS upstatus, pd.image AS upimage FROM " . DB_PREFIX . "popup p LEFT JOIN " . DB_PREFIX . "popup_description pd ON (p.popup_id = pd.popup_id)  WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_start < NOW() AND p.date_end > NOW() AND p.status='1' AND p.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		$query = $this->db->query($sql);
		return isset($query->rows)?$query->rows:[];
	}
	
}