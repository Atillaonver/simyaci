<?php
namespace Opencart\Catalog\Controller\Bytao;
class Jqscript extends \Opencart\System\Engine\Controller {
	
	private $jqscriptTypes = array( 'core','menu','desktop' ,'tablet' ,'mobile' ,'typo' ); // 1,2,3,4,5,6
	
	
	public function index($jqscript = []):void {
		$this->load->model('bytao/jqscript');
		$output = '';
		$store_id 	= $jqscript['store_id'];
		$typ   		= $jqscript['type'];
		$ver 		= $jqscript['version'];
		$name = $jqscript['type'];
		$cdn = DIR_CDN.'js/bytao-'.$name.'-'.$store_id.'-'.$ver.'.js';
		if( !file_exists($cdn) || ! filesize($cdn) ){
			$content  = html_entity_decode($jqscript['jqscript']); 
			$this->writeToCdn( DIR_CDN.'js/bytao-', $name.'-'.$store_id.'-'.$ver,$content, 'js' );
			if(file_exists(DIR_CDN.'js/bytao-'.$name.'-'.$store_id.'-'.((int)$ver-1).'.js')){
				unlink(DIR_CDN.'js/bytao-'.$name.'-'.$store_id.'-'.((int)$ver-1).'.js');
			} 
		}
	}
	
	public static function writeToCdn(string $folder,string $file,string $value,string $e='js' ):void{
			$file = $folder  . preg_replace('/[^A-Z0-9\._-]/i', '', $file).'.'.$e ;
			$handle = fopen($file, 'w');
	    	fwrite($handle, ($value));
	    	fclose($handle);
	    	//file_put_contents($file,$value);
		}
	
	public static function process( string $content , string $url ){
		global $cssURL;   $cssURL = $url;
		
		$content = str_replace('IMAGEROUTE', HTTPS_IMAGE , $content);
		$content = str_replace('CDNROUTE', CDN_ROUTE , $content);
		$content = str_replace('CDN_ROUTE', CDN_ROUTE , $content);
		$content = str_replace('HTTP_SERVER', HTTPS_IMAGE , $content);
		$content = str_replace('HTTP_ASSETS', HTTP_SERVER.'assets/' , $content);
		$content = str_replace('HTTP_IMAGE', HTTPS_IMAGE , $content);
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
		
		$this->load->model('bytao/jqscript');
		$allJqscript = $this->model_bytao_jqscript->getJqscriptByStore();
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		$data['base'] = $this->config->get('config_url');
		$store_id = (int)$this->config->get('config_store_id');
		
		$data['alljqscript'] =[];
		foreach($allJqscript as $jqscript){
			$data['alljqscript'][]=[
				'link' => $jqscript['version'],
				'name' => $jqscript['type'].'-'.$store_id.'-'.$jqscript['version']
			]; 
		}
		return $this->load->view('bytao/jqscript_head', $data);
	}		

}