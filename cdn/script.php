<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/javascript; charset=utf-8");

require_once('config.php');

function error_handler($errno, $errstr, $errfile, $errline)
{
	global $log, $config;

	// error suppressed with @
	if(error_reporting() === 0){
		return false;
	}

	switch($errno){
		case E_NOTICE:
		case E_USER_NOTICE:
		$error = 'Notice';
		break;
		case E_WARNING:
		case E_USER_WARNING:
		$error = 'Warning';
		break;
		case E_ERROR:
		case E_USER_ERROR:
		$error = 'Fatal Error';
		break;
		default:
		$error = 'Unknown';
		break;
	}
	$m=isset($_REQUEST['val2']) ?$_REQUEST['val2']:'';
	fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y-m-d G:i:s') . " - " . $error. " - > stylesheet-" . $m . " \n");

	return true;
}
set_error_handler('error_handler');

function writeToCache(string $folder,string $file,string $value,string $e='js' ){
	$file = $folder  . preg_replace('/[^A-Z0-9\._-]/i', '', $file).'.'.$e ;
	$handle = fopen($file, 'w');
	fwrite($handle, ($value));
	fclose($handle);
}

 function process( string $content , string $url ){
		global $cssURL;   $cssURL = $url;
		
		$content = str_replace('IMAGEROUTE', HTTPS_IMAGE , $content);
		$content = str_replace('HTTP_SERVER', HTTPS_IMAGE , $content);
		$content = str_replace('HTTP_ASSETS', HTTP_SERVER.'assets/' , $content);
		$content = str_replace('HTTP_IMAGE', HTTPS_IMAGE , $content);
		// $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
		// $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $content);
		// $content = str_replace(array("&amp;", "&gt;","&quot;"), array("&", ">",'"'), $content);
		// $content = preg_replace('/[ ]+([{};,:])/', '\1', $content);
		// $content = preg_replace('/([{};,:])[ ]+/', '\1', $content);
		// $content = preg_replace('/(\}([^\}]*\{\})+)/', '}', $content);
		/* $content = preg_replace('/<\?(.*?)\?>/mix', '', $content);*/
		
        return $content;	
}
		
//$db = new PDO("mysql:host=".DB_HOSTNAME.";dbname=".DB_DATABASE, DB_USERNAME, DB_PASSWORD);
$db = new mysqli( DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
} 
$db->set_charset("utf8");
$db->query("SET SQL_MODE = ''");

$output = '';

$store_id 	= isset($_REQUEST['val2'])?$_REQUEST['val2']:'0';
$typ   		= $_REQUEST['val1'];
$ver 		= $_REQUEST['val3'];

$sql = "SELECT * FROM " . DB_PREFIX . "jqscript WHERE store_id = '" . (int)$store_id. "' AND type='".$typ."' LIMIT 1";
$query = $db->query($sql);	

$rowData = '';
$version = 0;

if ($query && $query->num_rows > 0) {
    $row = $query->fetch_object();
    $version = $row->version;
    $rowData = html_entity_decode($row->jqscript);
}
$db->close();
//

if($rowData){
	$t = '/home/u779062265/domains/simyaci.tr/public_html/cdn/js/bytao-'.$typ.'-'.$store_id.'-'.$version.'.js';
	
	if( file_exists($t) && filesize($t) ){
	    //echo $rowData;
	    //fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y-m-d G:i:s') . " -- > stylesheet-" . $rowData . " \n");
	}else{
		//$content  = process( $rowData, $t ); 
		writeToCache('/home/u779062265/domains/simyaci.tr/public_html/cdn/js/', 'bytao-'.$typ.'-'.$store_id.'-'.$version,$rowData);
		unlink('/home/u779062265/domains/simyaci.tr/public_html/cdn/js/bytao-'.$typ.'-'.$store_id.'-'.((int)$version-1).'.js');
	}
	
	echo file_get_contents($t, 'r');
	
}else{
	echo "Ooopsii";
}


?>