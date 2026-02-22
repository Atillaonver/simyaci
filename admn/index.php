<?php
// Version
define('VERSION', '4.0.2.3');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

require_once(DIR_OPENCART . 'develope/bytao.php');

// Framework
require_once(DIR_SYSTEM . 'framework.php');
