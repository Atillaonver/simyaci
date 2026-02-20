<?php
namespace Opencart\Catalog\Model\Extension\Bytao\Total;
class Localtax extends \Opencart\System\Engine\Model {
	
	public function getTotal(array &$totals, array &$taxes, float &$total): void {
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_address'])) {
			$query = $this->getRates();
			
			if($this->customer->isLogged() && $this->customer->getGroupId()==2 ){
				
			} else {
				
				if( $query && $this->session->data['shipping_address']['country_id']== 223 ){
					
					if(isset($query['tax_rate'])){
						$value = ($total/100) * $query['tax_rate'];
						$totals[] = [
							'extension'  => 'bytao',
							'code'       => 'localtax',
							'title'      => 'Local Tax',
							'value'      =>  $value,
							'sort_order' => (int)$this->config->get('total_localtax_sort_order')
						];
						$total += (float)$value;
					}
				}
			}
		}else{
			//$this->log->write('invalid postcode ');
		}
	}
	
	public function getRates() {
		if (isset($this->session->data['shipping_address']['postcode'])){
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "localtax_rates` WHERE zipcode = '".$this->session->data['shipping_address']['postcode']."' ");
			return isset($query->row)?$query->row:[];
		}
		return [];
	}
	
	
}