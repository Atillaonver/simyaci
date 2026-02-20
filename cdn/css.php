<?php
require_once('config.php');

// Hataları yakalamak için log dosyası yolu (Yazma izni olduğundan emin olun)
$logPath = '/home/u779062265/domains/bystorage/logs/error.log';

// Tarayıcıya her zaman CSS döndüreceğimizi söylüyoruz
header("Access-Control-Allow-Origin= *");
header("Content-type: text/css; charset=UTF-8");

function process($content, $url) {
    global $cssURL;
    $cssURL = $url;
    
    $content = str_replace('CDN_', URL_SERVER , $content);
    $content = str_replace('IMGPATH_', URL_SERVER , $content);
    $content = str_replace('IMAGEROUTE', HTTPS_IMAGE , $content);
    $content = str_replace('HTTP_SERVER', HTTP_SERVER , $content);
    $content = str_replace('HTTP_ASSETS', HTTP_SERVER.'assets/' , $content);
    $content = str_replace('HTTP_IMAGE', HTTPS_IMAGE , $content);
    $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
    $content = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $content);
    $content = str_replace(["&amp;", "&gt;","&quot;"], ["&", ">",'"'], $content);
    $content = preg_replace('/[ ]+([{};,:])/', '\1', $content);
    $content = preg_replace('/([{};,:])[ ]+/', '\1', $content);
    return $content;
}

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($db->connect_error) {
    echo "/* Database Connection Error */"; // Tarayıcı hata almasın diye CSS yorumu olarak gönderiyoruz
    exit;
} 
$db->set_charset("utf8");

// Değişkenleri güvenli alalım
$store_id = isset($_REQUEST['val2']) ? (int)$_REQUEST['val2'] : 0;
$typ      = isset($_REQUEST['val1']) ? $_REQUEST['val1'] : 'core';

$types = [
    'typo' => 6, 'mobile' => 5, 'tablet' => 4, 
    'desktop' => 3, 'menu' => 2, 'core' => 1
];

$type_id = isset($types[$typ]) ? $types[$typ] : 1;
$name = $typ;
$sql = "SELECT * FROM " . DB_PREFIX . "css WHERE store_id = '$store_id' AND type='$type_id' LIMIT 1";
$query = $db->query($sql);

//fwrite(fopen( $logPath, 'a'), date('Y-m-d G:i:s') . " -- > " . (print_r($query,TRUE)) . " \n");

$rowData = '';
$version = 0;

if ($query && $query->num_rows > 0) {
    $row = $query->fetch_object();
    $version = $row->version;
    $rowData = html_entity_decode($row->css);
}
$db->close();

if (!empty($rowData)) {
    $cacheFile = '/home/u779062265/domains/simyaci.tr/public_html/cdn/css/bytao-' . $name . '-' . $store_id . '-' . $version . '.css';
    
    if (!file_exists($cacheFile) || filesize($cacheFile) == 0) {
        $processedContent = process($rowData, $cacheFile);
        file_put_contents($cacheFile, $processedContent);
        
        // Eski cache dosyasını temizle
        $oldFile =  '/home/u779062265/domains/simyaci.tr/public_html/cdn/css/bytao-' . $name . '-' . $store_id . '-' . ($version - 1) . '.css';
        if (file_exists($oldFile)) unlink($oldFile);
    }
    
    echo file_get_contents($cacheFile);
} else {
    echo "/* CSS content not found for type: $typ, store: $store_id */";
}
?>