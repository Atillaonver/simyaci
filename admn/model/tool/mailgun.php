<?php
namespace Opencart\Admin\Model\Tool;
static $registry = null;

// Error Handler
function error_handler_for_mailgun($errno, $errstr, $errfile, $errline) {
	global $registry;

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}

	$config = $registry->get('config');
	$url = $registry->get('url');
	$request = $registry->get('request');
	$session = $registry->get('session');
	$log = $registry->get('log');

	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $errors . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	if (($errors=='Warning') || ($errors=='Unknown')) {
		return true;
	}

	$dir = 'extension';
	if (($errors != "Fatal Error") && isset($request->get['route']) && ($request->get['route']!="$dir/export_import/download"))  {
		if ($config->get('config_error_display')) {
			echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
		}
	} else {
		$session->data['export_import_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
		$token = $request->get['user_token'];
		$link = $url->link( "$dir/export_import", 'user_token='.$token );
		header('Status: ' . 302);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $link));
		exit();
	}

	return true;
}

function fatal_error_shutdown_handler_for_mailgun()
{
	$last_error = error_get_last();
	if (($last_error) && ($last_error['type'] === E_ERROR)) {
		// fatal error
		error_handler_for_mailgun(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

class Mailgun extends \Opencart\System\Engine\Model {
	
}