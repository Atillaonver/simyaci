<?php
namespace Opencart\Admin\Controller\Common;
class Logout extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->user->logout();

		unset($this->session->data['user_token']);
		/* TODO bytao  session empty*/
		unset($this->session->data['user_id']);
		unset($this->session->data['store_id']);
		unset($this->session->data['store_url']);
		unset($this->session->data['logo']);
		unset($this->session->data['url']);
		unset($this->session->data['name']);
		unset($this->session->data['long_name']);
		
		
		$this->response->redirect($this->url->link('common/login'));
	}
}