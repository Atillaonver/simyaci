<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
		// Local
		ini_set('display_errors', 1);
		define('DIR_PUBLIC', 'C:/xampp/htdocs/');
		define('DIR_STORAGE', DIR_PUBLIC.'bystorage/');
		define('URL_SERVER', 'http://localhost/ADM25/admn/');
		define('DIR_OPENCART', DIR_PUBLIC.'ADM25/');
		define('DIR_SYSTEM', DIR_PUBLIC . 'bysystem/');
		define('HTTP_SERVER', URL_SERVER);
		define('DIR_ADMIMAGE', DIR_OPENCART.'image/');
		define('DIR_CLIENT', DIR_PUBLIC.'ADM25/');
		
		define('HTTP_CATALOG', 'http://localhost/ADM25/');
		define('URL_IMAGE', 'http://localhost/ADM25/image/');

		define('DB_HOSTNAME', 'localhost');
		define('DB_USERNAME', 'root');
		define('DB_PASSWORD', '');
		define('DB_DATABASE', 'u779062265_bytao');
	
	} else {
	//Live
	define('DIR_PUBLIC', '/home/u779062265/domains/');
	define('DIR_STORAGE', DIR_PUBLIC.'bystorage/');
	define('URL_SERVER', 'https://simyaci.tr/admn/');
	define('DIR_OPENCART', DIR_PUBLIC.'simyaci.tr/public_html/');
	define('DIR_SYSTEM', DIR_PUBLIC . 'bysystem/');
	define('HTTP_SERVER', URL_SERVER);
	define('DIR_ADMIMAGE', DIR_OPENCART.'image/');
	define('DIR_CLIENT', DIR_PUBLIC.'public_html/');

	define('HTTP_CATALOG', 'https://simyaci.tr/');
	define('URL_IMAGE', 'https://simyaci.tr/image/');
	
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', 'u779062265_byuser');
	define('DB_PASSWORD', '80Xb#kiG>x');
	define('DB_DATABASE', 'u779062265_bytao');
	
}	
	
	// DB
    

    define('APPLICATION', 'Admin');
    define('HTTPS_ST', '0');

// DIR

define('DIR_APPLICATION', DIR_OPENCART . 'admn/');
define('DIR_EXTENSION', DIR_OPENCART . 'extension/');
define('DIR_IMAGE', DIR_OPENCART . 'image/');
define('DIR_PDF', DIR_CLIENT . 'pdf/');
define('DIR_CATALOG', DIR_OPENCART . 'catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
define('DIR_IMPORT', DIR_STORAGE . 'import/');

define('DB_DRIVER', 'mysqli');
define('DB_PORT', '3306');
define('DB_PREFIX', 'by_');

define('OPENCART_SERVER', 'https://www.opencart.com/');
define('S_POSITION', 'header');

