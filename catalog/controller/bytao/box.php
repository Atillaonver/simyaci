<?php
namespace Opencart\Catalog\Controller\Bytao;
class Box extends \Opencart\System\Engine\Controller {
	private $version = '1.0.0';
	private $cPth = 'bytao/box';
	private $C = 'box';
	private $ID = 'box_id';
	private $model ;
	private $LYMD = [] ;
	private $Cr = 0 ;
	
	private function fill():void {
		$this->LYMD=[
			'0'  => ['row','w3-row','by-row'],
			'00' => ['container','w3-container','by-container'],
			'11' => ['col-md-12','w3-col','col-md-12'],
			'12' => ['col-md-6','w3-half','col-md-6'],
			'23' => ['col-md-8','w3-twothird','col-md-8'],
			'13' => ['col-md-4','w3-third','col-md-4'],
			'14' => ['col-md-3','w3-quarter','col-md-3'],
			'34' => ['col-md-9','w3-threequarter','col-md-9'],
			'56' => ['col-md-10','w3-col','col-md-10'],
			];
		$this->Cr=$this->config->get('config_store_core');	
	}
	
	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''):void {
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
		$this->fill();
	}
	
	
	public function index():void {
		
		$this->response->setOutput($this->load->view($this->cPth, $data));
	}
	
	public function getwidget( array $cDatat=[]):string {
		$this->getML('ML');
		$this->document->addStyle('cdn/css/bytao_box.css');
		
		
		if(isset($cDatat['col_content_id'])){
			$parts = explode(',', $cDatat['col_content_id']);
			
			if (isset($parts[1])) {
				$this->ID = (int)$parts[1];
				
				$layout['11111111']='<div class="rower r11111111"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="iner">11111111</div></div></div>';
				
				$layout['11114444']='<div class="rower r11114444"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="inner">11110000</div><div class="inner">00001111</div></div></div>';
				
				$layout['11331133']='<div class="rower r11331133"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].'">11001100</div><div class="'.$this->LYMD['12'][$this->Cr].' inner">00110011</div></div></div>';
				
				$layout['11345674']='<div class="rower r11345674"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].'"><div class="'.$this->LYMD['0'][$this->Cr].' inner">11000000</div><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].' inner">00001000</div><div class="'.$this->LYMD['12'][$this->Cr].' inner">00000100</div></div></div><div class="'.$this->LYMD['14'][$this->Cr].'"><div class="'.$this->LYMD['0'][$this->Cr].' inner">00100000</div><div class="'.$this->LYMD['0'][$this->Cr].' inner">00000010</div></div><div class="'.$this->LYMD['14'][$this->Cr].' inner">00010001</div></div></div>';
				
				
				
				$layout['11341178']='<div class="rower r11341178"><div class="'.$this->LYMD['12'][$this->Cr].' inner">11001100</div><div class="'.$this->LYMD['12'][$this->Cr].'"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00100000</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00010000</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00000010</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00000001</div></div></div></div>';
				
				$layout['12241677']='<div class="rower r12241677"><div class="'.$this->LYMD['14'][$this->Cr].' inner">10001000</div><div class="'.$this->LYMD['34'][$this->Cr].' inner"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['23'][$this->Cr].' bbor inner">01100000</div><div class="'.$this->LYMD['13'][$this->Cr].' bbor inner">00010000</div></div><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['13'][$this->Cr].' bbor inner">00000100</div><div class="'.$this->LYMD['23'][$this->Cr].' bbor inner">00000011</div></div></div></div>';
				
				$layout['12335633']='<div class="rower r12335633"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].' fRight inner">00110011</div><div class="'.$this->LYMD['12'][$this->Cr].'"><div class="'.$this->LYMD['0'][$this->Cr].'"><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">10000000</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">01000000</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00001000</div><div class="'.$this->LYMD['12'][$this->Cr].' bbor inner">00000100</div></div></div></div></div>';
				
				
				$results = $this->model->{$this->getFunc('get','es')}($this->ID);
				
				$matrix=[];
				$this->load->model('tool/image');
			
				foreach ($results as $result) {
						$col=1;
						$positions = explode('-',$result['position']);
						$fVal=0;
						foreach($positions as $pos)
						{
							if($pos == '1'){
								if(!$fVal){
									$fVal = $col;	
								}
								$matrix[$col] = $fVal;	
							}
							
							$col++;			
						}
					}
					
					ksort($matrix);
					$mkey = implode($matrix);
					
					$rowcontent = isset($layout[$mkey])?$layout[$mkey]:$mkey;
					
					foreach ($results as $result) {
						$boxcontent ='';
						if (is_file(DIR_IMAGE . html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8'))) {
							if($result['link']){
								$boxcontent .='<a class="link-href" href="'.$result['link'].'" title="'.$result['name'].'">';
								$boxcontent .='<img alt="'.$result['name'].'" src="'.HTTPS_IMAGE.html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8').'" class="w3-image">';
								$boxcontent .='</a>';
							}else{
								$boxcontent .='<img alt="'.$result['name'].'" src="'.HTTPS_IMAGE.html_entity_decode(by_move($result['image']), ENT_QUOTES, 'UTF-8').'" class="w3-image">';
							}	
						}
						$rowcontent = str_replace(str_replace("-", "", $result['position']), $boxcontent, $rowcontent);
					}
					$data['rows'][] = $rowcontent;
			} 
			return $this->load->view($this->cPth.'_widget', $data);

		}
		return '';
	}
	
	private function getCols($matrix,$box_id)
	{
		$counter = 1;
		for($i = 0; $i <= 6; $i++)
		{
			if($matrix[$i]['box_id'] == $box_id && $matrix[$i + 1]['box_id'] == $box_id )
			{
				$counter++;
			}
		}
		return $counter;
	}
	
	private function getRows($matrix,$box_id)
	{
		$counter = 1;
		for($i = 0; $i <= 3; $i++)
		{
			if($matrix[$i]['box_id'] == $box_id && $matrix[$i + 4]['box_id'] == $box_id )
			{
				$counter++;
			}
		}
		return $counter;
	}

}
