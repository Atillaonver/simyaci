<?php
namespace Opencart\Catalog\Controller\Bytao;
class Css extends \Opencart\System\Engine\Controller {
	
	private $cssTypes = array( 'core','menu','desktop' ,'tablet' ,'mobile' ,'typo' ); // 1,2,3,4,5,6
	
	
	public function index($css = []):void {
		$this->load->model('bytao/css');
		$output = '';
		$store_id 	= $css['store_id'];
		$typ   		= (int)$css['type'];
		$ver 		= $css['version'];
		$name = $this->cssTypes[$typ-1];
		$cdn = DIR_CDN.'css/bytao-'.$name.'-'.$store_id.'-'.$ver.'.css';
		if( !file_exists($cdn) || ! filesize($cdn) ){
			$content  = $this->process($css['css'], $cdn ); 
			$this->writeToCdn( DIR_CDN.'css/bytao-', $name.'-'.$store_id.'-'.$ver,$content, 'css' );
			if(file_exists(DIR_CDN.'css/bytao-'.$name.'-'.$store_id.'-'.((int)$ver-1).'.css')){
				unlink(DIR_CDN.'css/bytao-'.$name.'-'.$store_id.'-'.((int)$ver-1).'.css');
			} 
		}
	}
	
	public static function writeToCdn(string $folder,string $file,string $value,string $e='css' ):void {
			$file = $folder  . preg_replace('/[^A-Z0-9\._-]/i', '', $file).'.'.$e ;
			$handle = fopen($file, 'w');
	    	fwrite($handle, ($value));
	    	fclose($handle);
	    	//file_put_contents($file,$value);
		}
	
		
	public static function process( string $content , string $url ){
		global $cssURL;   $cssURL = $url;
		
		$content = str_replace('|IMGROUTE|', HTTPS_IMAGE , $content);
		$content = str_replace('IMAGEROUTE', HTTPS_IMAGE , $content);
		$content = str_replace('CDNROUTE', CDN_ROUTE , $content);
		$content = str_replace('CDN_ROUTE', CDN_ROUTE , $content);
		$content = str_replace('HTTP_SERVER', HTTPS_IMAGE , $content);
		$content = str_replace('HTTP_ASSETS', HTTP_SERVER.'assets/' , $content);
		$content = str_replace('HTTP_IMAGE', HTTPS_IMAGE , $content);

		// Ana siteden gelen tam linkleri yakala, alt siteye çek ve yolu düzelt
		if (function_exists('by_move')) {
			$pattern = '/url\(["\']?(https:\/\/www\.simyaci\.tr\/image\/([^"\'\)]+))["\']?\)/';
			$content = preg_replace_callback($pattern, function($matches) {
				$relative_path = $matches[2];
				by_move($relative_path);
				return "url('" . HTTPS_IMAGE . $relative_path . "')";
			}, $content);
		}
		$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
		$content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $content);
		$content = str_replace(array("&amp;", "&gt;","&quot;"), array("&", ">",'"'), $content);
		$content = preg_replace('/[ ]+([{};,:])/', '\1', $content);
		$content = preg_replace('/([{};,:])[ ]+/', '\1', $content);
		$content = preg_replace('/(\}([^\}]*\{\})+)/', '}', $content);
		$content = preg_replace('/<\?(.*?)\?>/mix', '', $content);
		//$content = preg_replace_callback('/url\(([^\)]*)\)/', $this->callbackReplaceURL($content),$content);
		
        return $content;	
	}
	
	public static function callbackReplaceURL( string $matches):string {
        $url = str_replace(array('"', '\''), '', $matches[1]);
        global $cssURL;
        $url = $this->converturl( $url, $cssURL );
        return "url('$url')";
    }
    
    public static function converturl(string $url, string $cssurl):string {
        $base = dirname($cssurl);
        if (preg_match('/^(\/|http)/', $url))
            return $url;
        /*absolute or root*/
        while (preg_match('/^\.\.\//', $url)) {
            $base = dirname($base);
            $url = substr($url, 3);
        }

        $url = $base . '/' . $url;
        return $url;
    }

	public function addhead():string{
		
		$this->load->model('bytao/css');
		$allcss = $this->model_bytao_css->getCssByStore();
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		$data['base'] = HTTP_SERVER; //$this->config->get('config_url');
		$store_id = (int)$this->config->get('config_store_id');
		
		$data['allcss'] =array();
		foreach($allcss as $css){
			$data['allcss'][]=array(
				'link' => $css['version'],
				'name' => $this->cssTypes[(int)$css['type']-1].'-'.$store_id.'-'.$css['version']
			); 
		}
		return $this->load->view('bytao/css_head', $data);
	}		

}