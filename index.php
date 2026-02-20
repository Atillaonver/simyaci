<?php
// Version

define('VERSION', '4.0.2.1');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit();
}

/**
* 
* TODO byTAO 
* 
*/
if(isset($_SERVER['HTTP_REFERER'])){
	define('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
}else{
	define('HTTP_REFERER', 'Direct');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Framework
require_once(DIR_SYSTEM . 'framework.php');
