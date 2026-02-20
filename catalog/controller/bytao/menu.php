<?php
namespace Opencart\Catalog\Controller\Bytao;
class Menu extends \Opencart\System\Engine\Controller {
	
	private $error = [];
	private $version = '1.0.1';
	private $cPth = 'bytao/menu';
	private $C = 'menu';
	private $ID = 'menu_id';
	private $model ;
	private $controlls = ['pages','references','customer_sector','blog','video','news','logo','prod','art_category','art','project','firm'];
	private $menuTypes = ['url' => 'URL','category' => 'Kategori','static_page' => 'statik Sayfa','information' => 'Bilgi Sayfaları','product' => 'Ürün','controller' => 'Yönetim','html'  => "HTML"];
	private $lngs = [];
	
	public function getPth():string {
		return $this->cPth;
	}

	private function getFunc($f='',$addi=''):string {
		return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
	}

	private function getML($ML=''):void {
		switch($ML){
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
			
		}
	}
	
	public function index():string {
		
		return '';
	}
	
	public function getdropdown($hData=[]):string {
		
		$this->getML('ML');
		$data['items'] = [];
		$menuItems = [];
		$max = $this->model->{$this->getFunc('get','MaxGroup')}();
		$data['LOGN'] = $this->customer->isLogged();
		
		for($i = 1; $i < count($max)+1 ; $i++){
			
			$menuItems[]= $this->model->{$this->getFunc('get','Dropdown')}(0,0,$i);
		}
		
		
		
		if(isset($hData['logo'])&& $hData['logo'] != ''){
			$data['logo'] = $hData['logo'];
		}else{
			$data['logo'] = '';
		}
		
		if(isset($hData['logo_negative'])&& $hData['logo'] != ''){
			$data['logo_negative'] = $hData['logo_negative'];
		}else{
			$data['logo_negative'] = '';
		}
		
		if(isset($hData['home'])){
			$data['home'] = $hData['home'];
		}else{
			$data['home'] = '';
		}
		
		if(isset($hData['lang'])){
			$data['lang'] = $hData['lang'];
		}else{
			$data['lang'] = '';
		}
		
		$data['language'] = $this->load->controller('bytao/language');
		
		$this->load->model('catalog/category');
		$this->load->model('bytao/common');
		$this->lngs = $this->model_bytao_common->getStoreLanguages();
		$data['core'] = $this->config->get('config_store_core'); 
		
		foreach($menuItems as $ind => $mItems){
			foreach($mItems as $item){
				$itemData=[];
				if($item['menu']['type']=='controller'){
					switch($item['menu']['item']){
						case 'prod':
							{
								$this->load->model('bytao/prod');
								$items = $this->model_bytao_prod->getProdCats();
								foreach($items as $PC){
									$itemData[]=[
										'menu_id' 	=> $PC['prod_cat_id'],
										'title' 	=> $PC['name'],
										'class' 	=> 'pcat',
										'target' 	=> 0,
										'href'    	=> $this->url->link('bytao/prod_cat', 'language=' . $this->config->get('config_language') .'&prod_cat_id=' . $PC['prod_cat_id'])
									];
								}
							}
							break;
						case 'firm':
							{
								$this->load->model('bytao/firm');
								$items = $this->model_bytao_firm->getFirms();
								foreach($items as $PC){
									$itemData[]=[
										'menu_id' 	=> $PC['firm_id'],
										'title' 	=> $PC['title'],
										'class' 	=> 'frm',
										'target' 	=> 0,
										'href'    	=> $this->url->link('bytao/firm', 'language=' . $this->config->get('config_language') . '&firm_id=' .  $PC['firm_id'])
									];
								}
							}
							break;
						case 'art_category':
							{
								$this->load->model('bytao/artcategory');
								$items = $this->model_bytao_artcategory->getArtCategories(0,TRUE);
								foreach($items as $PC){
									$itemData[]=[
										'menu_id' 	=> $PC['art_category_id'],
										'title' 	=> $PC['name'],
										'class' 	=> 'acat',
										'target' 	=> 0,
										'href'    	=> $this->url->link('bytao/art_category', 'language=' . $this->config->get('config_language') .'&art_category_id=' . $PC['art_category_id'],false)
									];
								}
							}
							break;
						default:
					}
					
				}
				else
				{
					if($item['sub']){
						foreach($item['sub'] as $sub){
							$childData = [];
							if($sub['sub']){
								foreach($sub['sub'] as $child){
									$grandchildData = [];
									if($child['sub']){
										foreach($child['sub'] as $gChild){
											$resg = strpos($gChild['menu']['menu_class'],"blank");
											if ($resg === false){$posg=0;}else{$posg=1;}
											$grandchildData[]=[
												'menu_id' 	=> $gChild['menu']['menu_id'],
												'title' 	=> $gChild['menu']['title'],
												'login' 	=> $gChild['menu']['login'],
												'show_title' 	=> $gChild['menu']['show_title'],
												'class' 	=> $gChild['menu']['menu_class'],
												'pclass' 	=> $gChild['menu']['parent_class'],
												'target' 	=> $posg,
												'active'    => $this->Active($gChild['menu']),
												'href'    	=> $this->Link($gChild['menu'])
											];
										}
									}
									$res = strpos($child['menu']['menu_class'],"blank");
									if ($res === false){$pos=0;}else{$pos=1;}
									$childData[]=[
										'menu_id' 	=> $child['menu']['menu_id'],
										'title' 	=> $child['menu']['title'],
										'login' 	=> $child['menu']['login'],
										'show_title' 	=> $child['menu']['show_title'],
										'class' 	=> $child['menu']['menu_class'],
										'pclass' 	=> $child['menu']['parent_class'],
										'target' 	=> $pos,
										'href'    	=> $this->Link($child['menu']),
										'active'    => $this->Active($child['menu']),
										'subs'		=>	$grandchildData
									];
								}
							}
							
							$res = strpos($sub['menu']['menu_class'],"blank");
							if ($res === false){$pos=0;}else{$pos=1;}
							$itemData[]=[
								'menu_id' 	=> $sub['menu']['menu_id'],
								'title' 	=> $sub['menu']['title'],
								'login' 	=> $sub['menu']['login'],
								'show_title' 	=> $sub['menu']['show_title'],
								'class' 	=> $sub['menu']['menu_class'],
								'pclass' 	=> $sub['menu']['parent_class'],
								'href'    	=> $this->Link($sub['menu']),
								'active'    => $this->Active($sub['menu']),
								'target' 	=> $pos,
								'subs'		=> $childData
							];
						}
					}
				}
				
				
				$res = strpos($item['menu']['menu_class'],"blank");
				if ($res === false){$pos=0;}else{$pos=1;}
				if($item['menu']['published']=='1'){
					//$data['items'][$ind][]=[
					$data['items'][]=[
						'menu_id' 	=> $item['menu']['menu_id'],
						'title' 	=> $item['menu']['title'],
						'login' 	=> $item['menu']['login'],
						'show_title' 	=> $item['menu']['show_title'],
						'class' 	=> $item['menu']['menu_class'],
						'pclass' 	=> $item['menu']['parent_class'],
						'target' 	=> $pos,
						'href'    	=> $this->Link($item['menu']),
						'active'    => $this->Active($item['menu']),
						'subs'		=> $itemData
					];
				}
			}
		}
		
		
		
		return $this->load->view($this->cPth, $data);
	}
	
	private function Link($menu):string{
		
		$id = (int)$menu['item'];
		$url='';
		switch( $menu['type'] ){
			case 'category':
				//$parent = $this->model_catalog_category->getParentCategory($id);
				
				$path = $menu['parent_id'].'_'.$id;
				
				//if( $parent ){
				//	$path = $parent.'_'.$id;
				//}
				
				$url =  $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $id);
				break;
			case 'product':
				$url =   $this->url->link('product/product', 'language=' . $this->config->get('config_language') .'&product_id=' . $id,false,false);
				break;
			case 'information':
				$url =    $this->url->link('information/information', 'language=' . $this->config->get('config_language') .'&information_id=' . $id);
				break;
			case 'page':
				$url =    $this->url->link('bytao/page', 'language=' . $this->config->get('config_language') . '&page_id=' . $id);
				break;
			case 'blog':
				$url =    $this->url->link('bytao/blog', 'language=' . $this->config->get('config_language') .'&blog_id=' . $id);
				break;
			case 'manufacturer':
				$url =   $this->url->link('product/manufacturer.info', 'language=' . $this->config->get('config_language') .'manufacturer_id=' . $id);
				break;
			case 'url':
				$url = '';
				$mItm=[];
				$mUrls = explode(',',$menu['url']);
				
				foreach($mUrls as $mUrl){
					$m = explode('æ',$mUrl);
					$mItm[$m[0]] = isset($m[1])?$m[1]:'';
				}
				
				if(!(int)$m[0]){
					$url = $menu['url'];
				}else{
					foreach($this->lngs as $language){
						if($this->config->get('config_language') == $language['code']){
							$url = $mItm[$language['language_id']];
						}
					}
				}
				
				break;	
			default:
				
				
		}
		
		return $url;
	}
	
	private function Active($menu):string{
		
		$id = (int)$menu['item'];
		$url='';
		
		switch( $menu['type'] ){
			case 'category':
				
				if (isset($this->request->get['path'])&& $this->request->get['path']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='product/category'){
						return 1;
					} 
				break;
			case 'product':
				if (isset($this->request->get['product_id'])&& $this->request->get['product_id']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='product/product'){return 1;} 
				
				break;
			case 'information':
				if (isset($this->request->get['information_id'])&& $this->request->get['information_id']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='information/information'){return 1;} 
				
				break;
			case 'page':
			if (isset($this->request->get['page_id'])&& $this->request->get['page_id']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='bytao/page'){return 1;} 
				break;
			case 'blog':
				if (isset($this->request->get['blog_id'])&& $this->request->get['blog_id']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='bytao/blog'){return 1;} 
				break;
			case 'manufacturer':
				if (isset($this->request->get['manufacturer_id'])&& $this->request->get['manufacturer_id']==$id && isset($this->request->get['route'])&&$this->request->get['route']=='product/manufacturer.info'){return 1;} 
				
				break;
			case 'url':
				if (isset($this->request->get['route'])&&$this->request->get['route']==$menu['url']){return 1;} 
				break;	
		}
		
		return 0;
	}

}
