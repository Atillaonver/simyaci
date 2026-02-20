<?php
namespace Opencart\Catalog\Controller\Bytao;
class Script extends \Opencart\System\Engine\Controller {
	private $error = array();
	private $version = '1.0.0';
	private $cPth = 'bytao/script';
	private $C = 'script';
	private $ID = 'script_id';
	private $model ;
	
	private function getFunc($f='',$addi=''){
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}
	
	private function getML($ML=''){
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}
	
	
	public function index():void {
		$this->load->model('bytao/script');
		$output = '';
		
		$this->response->addHeader('Content-type: text/css; charset: UTF-8');
		$this->response->setOutput($output);
	}
	
	public function head(array $D):string 
	{
		$rString='';
		$this->getML('M');
		if(!isset($D['route'])){return '';}
	
		$layoutID = $this->model->{$this->getFunc('get','LayoutId')}($D['route']);
		$rString = $this->render($this->model->{$this->getFunc('get')}('head',0),'');
		
		if(isset($this->request->get['route'])){
			$rString .= $this->render($this->model->{$this->getFunc('get')}('head',$layoutID),$D);
		}
		return $rString = html_entity_decode($rString, ENT_QUOTES, 'UTF-8');
	}
	
	public function body(array $D):string 
	{
		$rString='';
		$this->getML('M');
		if(!isset($D['route'])){return '';}
		
		$layoutID = $this->model->{$this->getFunc('get','LayoutId')}($D['route']);
		$rString = $this->render($this->model->{$this->getFunc('get')}('body_header',0),'');
		
		if(isset($this->request->get['route'])){
			$rString .= $this->render($this->model->{$this->getFunc('get')}('body_header',0),$D);
		}
		return $rString = html_entity_decode($rString, ENT_QUOTES, 'UTF-8');
	}
	
	public function footer(array $D):string 
	{
		$rString='';
		$this->getML('M');
		if(!isset($D['route'])){return '';}
		
		$layoutID = $this->model->{$this->getFunc('get','LayoutId')}($D['route']);
		
		$rString = $this->render($this->model->{$this->getFunc('get')}('body_footer',0),'');
		if(isset($this->request->get['route'])){
			$rString .= $this->render($this->model->{$this->getFunc('get')}('body_footer',$layoutID),$D);
		}
		return $rString = html_entity_decode($rString, ENT_QUOTES, 'UTF-8');
	}

	public function render(string $scrpt,array|string $sData):string
	{
		
		$textScript = $scrpt;
		$scriptProductId=[];
		$scriptTotal=0;
		$orderId=0;
		$headScript='';
		$bodyHeadScript='';
		$bodyFootScript='';
		if(isset($sData['route'])){
			switch($sData['route']){
				case 'bytao/home':break;
				case 'product/product':
					if(isset($sData['pdata'])&&$sData['pdata']){
						$loopFind = [
							"productId"			=> "{product_id}",
							"productName"		=> "{name}",
					      	"productPrice"		=> "{productPrice}",
					      	"productFPrice"		=> "{productFPrice}",
					      	"productBrand"		=> "{brand}",
					      	"productCategory"	=> "{category}",
					      	"productListname"	=> "{listname}",
					      	"productAmount"		=> "{amount}",
					      	"index"				=> "{index}"
						];
						 

						$loopReplace = array(
							"productId"			=> $sData['pdata']['product_id'],
							"productName"		=> $sData['pdata']['name'],
					      	"productPrice"		=> $sData['pdata']['price'],
					      	"productFPrice"		=> $sData['pdata']['fPrice'],
					      	"productBrand"		=> $sData['pdata']['manufacturer'],
					      	"productCategory"	=> $sData['pdata']['category'],
					      	"productListname"	=> "Product",
					      	"productAmount"		=> $sData['pdata']['amount'],
					      	"index"				=> 0
						);
						$textScript = str_replace($loopFind, $loopReplace, $textScript);
					}					
					break;
					
				case 'product/category':
					{
						$text_scripts = explode('</loop>',$scrpt);
						if(count($text_scripts) > 1)
						{
							foreach($text_scripts as $text_script) {
								$loopScript = explode('<loop>',$text_script);
								
								if(count($loopScript)>1)
								{
									$textScript .= $loopScript[0];
									$loopString = $loopScript[1];
									$looText = [];
									foreach ($products as $product) 
									{
										$index = count($looText);
										$loopFind = [
											"item_id"		=> "{product_id}",
											"item_name"		=> "{name}",
									      	"price"			=> "{price}",
									      	"productPrice"	=> "{productPrice}",
									      	"item_brand"	=> "{brand}",
									      	"item_category"	=> "{category}",
									      	"item_list_name"=> "{listname}",
									      	"index"			=> "{index}"
										];
										 
	      
										$loopReplace = array(
											'orderId' =>$orderId,
											'productId' =>$product['product_id'],
											'productName' =>$product['name'],
											//'variant' => $this->model_catalog_product->getProductCategory($product['product_id']),
											//'productPrice' => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
											'price' => $product['price'],
											'productPrice' => $product['price'],
											'productAmount' => $product['quantity']
										);
										
										$loopStringN =  $loopString;
										$textScript .= str_replace($loopFind, $loopReplace, $loopStringN);
										
									}
									
									
									
								}else{
									$textScript .= $loopScript[0];
								}
							}
						}
						else{
							$textScript = $text_scripts[0];
						}
					}
					break;
				case 'product/search':break;
				case 'checkout/cart':
					{
						$text_scripts = explode('::loopend::',$scrpt);
						if(count($text_scripts) > 1)
						{
							foreach($text_scripts as $text_script) {
								$loopScript = explode('::loop::',$text_script);
								
								if(count($loopScript)>1)
								{
									$textScript .= $loopScript[0];
									$loopString = $loopScript[1];
									$looText = [];
									if(isset($sData['pdata'])&&$sData['pdata']){
										foreach ($sData['pdata'] as $product) 
										{
											$index = count($looText);
											$loopFind = [
												"item_id"		=> "{product_id}",
												"item_name"		=> "{name}",
										      	"price"			=> "{price}",
										      	"productPrice"	=> "{productPrice}",
										      	"item_brand"	=> "{brand}",
										      	"item_category"	=> "{category}",
										      	"item_list_name"=> "{listname}",
										      	"index"			=> "{index}",
										      	"total"			=> "{total}"
											];
											 
		      
											$loopReplace = array(
												'orderId' =>$orderId,
												'productId' =>$product['product_id'],
												'productName' =>$product['name'],
												//'variant' => $this->model_catalog_product->getProductCategory($product['product_id']),
												//'productPrice' => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
												'price' => $product['price'],
												'productPrice' => $product['price'],
												'productAmount' => $product['quantity'],
												'total' => ($product['quantity']*$product['price'])
											);
											
											$loopStringN =  $loopString;
											$textScript .= str_replace($loopFind, $loopReplace, $loopStringN);
											
										}
									}
									
									
								}else{
									$textScript .= $loopScript[0];
								}
							}
						}
						else{
							$textScript = $text_scripts[0];
						}
						
					}
					break;
				case 'checkout/success':break;
				default:
				
			}	
		}
		
			
		
		/*		
		$find = array(
			'{orderId}',
			'{prodId}',
			'{total}',
			'{currency}'
		);
			
		$replace = array(
			'orderId' =>$orderId,
			'prodId' => '['.implode( ',',$scriptProductId ).']',
			'total' => $scriptTotal,
			//'currency' => $this->currency->getCode()
		);
		$find = array(
			'{productId}',
			'{productPrice}',
			'{currency}'
		);

		$replace = array(
			'productId' => $this->request->get['product_id'],
			'productPrice' => ($data['special']?$product_info['special']:$product_info['price']),
			'currency' => $this->currency->getCode()
		);
			
		$textScript .= str_replace($find, $replace, $this->script->getHead());
		*/
		return $textScript;
	}	
			
}