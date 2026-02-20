<?php
// -------------------------------------------------
// ORTAM TESPİTİ (LOCAL / LIVE)
// -------------------------------------------------
if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    define('ENV', 'local');
} else {
    define('ENV', 'live');
}

// -------------------------------------------------
// ERROR REPORTING (PHP 8.1)
// -------------------------------------------------
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
//ini_set('display_errors', ENV === 'local' ? 1 : 0);

// -------------------------------------------------
// LOCAL AYARLAR
// -------------------------------------------------
if (ENV === 'local') {

    define('HTTP_SERVER', 'http://localhost/simyaci/');
    define('HTTPS_SERVER', 'http://localhost/simyaci/');

    define('URL_IMAGE', 'http://localhost/simyaci/image/');

    define('DIR_OPENCART', 'C:/laragon/www/simyaci/');
    define('DIR_SYSTEM', 'C:/laragon/www/bysystem/');
    define('DIR_STORAGE', 'C:/laragon/www/bystorage/');

    define('DB_HOSTNAME', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_DATABASE', 'simyaci');
}

// -------------------------------------------------
// LIVE (SUNUCU) AYARLAR
// -------------------------------------------------
if (ENV === 'live') {

    define('HTTP_SERVER', 'https://simyaci.tr/');
    define('HTTPS_SERVER', 'https://simyaci.tr/');

    define('URL_IMAGE', 'https://simyaci.tr/image/');

    define('DIR_OPENCART', '/home/u779062265/domains/simyaci.tr/public_html/');
    define('DIR_SYSTEM', '/home/u779062265/domains/bysystem/');
    define('DIR_STORAGE', '/home/u779062265/domains/bystorage/');
    


    define('DB_HOSTNAME', 'localhost');
    define('DB_USERNAME', 'u779062265_byuser');
    define('DB_PASSWORD', '80Xb#kiG>x');
    define('DB_DATABASE', 'u779062265_bytao');
}

// -------------------------------------------------
// ORTAK SABİTLER (OC4)
// -------------------------------------------------
define('DIR_ADMIMAGE', DIR_OPENCART.'image/');
define('DIR_CLIENT', DIR_OPENCART);
define('MEDIA', 'image/');
define('HTTPS_ST', '0');
define('HTTPS_IMAGE', HTTP_SERVER.MEDIA);
define('DIR_CDN', DIR_CLIENT . 'cdn/');
define('DIR_CDNALL', DIR_STORAGE . 'cdn/');
define('CDN_ROUTE', HTTP_SERVER.'cdn/');
define('DIR_FONT', DIR_CDN . 'fonts/');
define('S_POSITION', 'header');



define('APPLICATION', 'Catalog');

define('DIR_APPLICATION', DIR_OPENCART . 'catalog/');
define('DIR_EXTENSION', DIR_OPENCART . 'extension/');
define('DIR_IMAGE', DIR_OPENCART . MEDIA);
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');

define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
define('DIR_IMPORT', DIR_STORAGE . 'import/');

// -------------------------------------------------
// DATABASE
// -------------------------------------------------
define('DB_DRIVER', 'mysqli');
define('DB_PORT', '3306');
define('DB_PREFIX', 'by_');

// -------------------------------------------------
define('OPENCART_SERVER', 'https://www.opencart.com/');