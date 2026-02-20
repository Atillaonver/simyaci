<?php
namespace Opencart\Catalog\Controller\Extension\Bytao\Startup;
class ThemeAston extends \Opencart\System\Engine\Controller {
	public function index(): void {
		if ($this->config->get('config_theme') == 'theme_aston' && $this->config->get('theme_theme_aston_status')) {
			// Add event via code instead of DB
			// Could also just set view/common/header/before
			$this->event->register('view/*/before', new \Opencart\System\Engine\Action('extension/bytao/startup/theme_aston.event'));
		}
	}

	public function event(string &$route, array &$args, mixed &$output): void {
		$override = ['common/header'];

		if (in_array($route, $override)) {
			$route = 'extension/bytao/' . $route;
		}
	}
}