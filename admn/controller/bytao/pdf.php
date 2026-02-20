<?php
namespace Opencart\Admin\Controller\Bytao;
class Pdf extends \Opencart\System\Engine\Controller
{
	private $version = '1.0.0';
	private $cPth = 'bytao/pdf';
	private $C = 'pdf';
	private $ID = 'pdf_id';
	private $Tkn = 'user_token';
	private $model ;

    private $fields = array(
        'admin',
        'attach',
        'barcode',
        'border_color',
        'color',
        'complete',
        'download',
        'font',
        'font_size',
        'logo',
        'logo_font',
        'logo_font_size',
        'logo_height',
        'logo_width',
        'order_complete',
        'order_image',
        'order_image_height',
        'order_image_width',
        'paging',
        'status'
    );

    private $language_fields = array(
        'after',
        'append',
        'before',
        'footer',
        'header',
        'prepend',
        'rtl',
        'title'
    );
    
	private function getFunc($f='', $addi=''):string
	{
		//return $f.str_replace(' ','',ucwords(str_replace('_',' ',$this->C))).$addi;
		return $f;
	}

	private function getML($ML=''):void
	{
		switch ($ML) {
			case 'M':$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			case 'L':$this->load->language($this->cPth); break;
			case 'ML':
			case 'LM':$this->load->language($this->cPth);$this->load->model($this->cPth); $this->model = $this->{'model_'.str_replace('/','_',$this->cPth)};break;
			default:
		}
	}

	public function install():void
	{
		$this->getML('ML');
		$menus = $this->model->{$this->getFunc('install')}();
		$this->getList();
	}
	

	public function index() {
		$this->getML('ML');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$url = '';
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->Tkn.'=' . $this->session->data[$this->Tkn],)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url)
		];

		$data['add'] = $this->url->link($this->cPth.'.form', $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);
		$data['delete'] = $this->url->link($this->cPth.'.delete', 'user_token=' . $this->session->data['user_token']);
		$data['action'] = $this->url->link($this->cPth.'.save', 'user_token=' . $this->session->data['user_token']);

		$this->load->model('tool/image');
		$this->load->model('bytao/common');

		$data['languages'] = $this->model_bytao_common->getStoreLanguages();

        $data['fonts'] = $this->_getTcpdfFonts();

        foreach($this->fields as $field) {
            if (isset($this->request->post['module_pdf_invoice_' . $field])) {
                $data['module_pdf_invoice_' . $field] = $this->request->post['module_pdf_invoice_' . $field];
            } else {
                $data['module_pdf_invoice_' . $field] = $this->config->get('module_pdf_invoice_' . $field);
            }
        }

		if ($this->config->get('module_pdf_invoice_logo') && is_file(DIR_IMAGE . $this->config->get('module_pdf_invoice_logo'))) {
			$data['logo_thumb'] = $this->model_tool_image->resize($this->config->get('module_pdf_invoice_logo'), 100, 100);
		} else {
			$data['logo_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        foreach ($data['languages'] as $language) {
            $data['module_pdf_invoice_preview'][$language['language_id']] = $this->url->link('bytao/pdf.preview', 'user_token=' . $this->session->data['user_token'] . '&language_id=' . $language['language_id'], true);

            foreach($this->language_fields as $language_field) {
                $data['module_pdf_invoice_' . $language_field  . '_' . $language['language_id']] = $this->config->get('module_pdf_invoice_' . $language_field  . '_' . $language['language_id']);
                
            }
        }

		$this->document->addStyle('view/javascript/bootstrap/css/bootstrap-colorpicker.min.css');
		$this->document->addStyle('view/stylesheet/module/pdf_invoice.css');
		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-colorpicker.min.js');
		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');
        $this->document->addScript('view/javascript/module/pdf_invoice.js');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('bytao/pdf/extension', $data));
	}

	public function form():void
	{
		$this->getML('ML');
		if (isset($this->request->get[$this->ID])) {
			if (! $this->model->{$this->getFunc('is','InStore')}($this->request->get[$this->ID])) {
				$this->response->redirect($this->url->link($this->cPth,$this->Tkn.'=' . $this->session->data[$this->Tkn]));
			}
		}


		$this->document->setTitle($this->language->get('heading_title'));
		//$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		//$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');


		$data['ADM']= $this->user->getGroupId();
		$data['url']= isset($this->session->data['store_url'])?$this->session->data['store_url']:HTTP_SERVER;

		$data['text_form'] = !isset($this->request->get[$this->ID]) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['save'] = $this->url->link($this->cPth.'.save', $this->Tkn.'=' . $this->session->data[$this->Tkn]);
		$data['back'] = $this->url->link($this->cPth, $this->Tkn.'=' . $this->session->data[$this->Tkn] . $url);

		if (isset($this->request->get[$this->ID])) {
			$item_info = $this->model->{$this->getFunc('get')}($this->request->get[$this->ID]);
			$data[$this->ID] = (int)$this->request->get[$this->ID];
			$data[$this->C.'_description'] = $this->model->{$this->getFunc('get','Descriptions')}($this->request->get[$this->ID]);
			$data[$this->C.'_seo_url']=$this->model->{$this->getFunc('get','SeoUrls')}($this->request->get[$this->ID]);
			$data[$this->C.'_store'] = $this->model->{$this->getFunc('get','Stores')}($this->request->get[$this->ID]);
		} else {
			$data[$this->ID] = 0;
			$data[$this->C.'_description'] = [];
			$data[$this->C.'_seo_url']=[];
			$data[$this->C.'_store'] = [$this->session->data['store_id']];
		}


		$this->load->model('bytao/common');
		$data['languages'] = $languages = $this->model_bytao_common->getStoreLanguages();

		$sendData = ['control'=>$this->C];
		$data['editJS'] = $this->load->controller('bytao/editor.js',$sendData);
		$data['modals'] = $this->load->controller('bytao/editor.modal',$sendData);

		$data['editors']=[];


		foreach ($languages as $language) {
			$sendData=[
				$this->ID 		=> isset($this->request->get[$this->ID])?$this->request->get[$this->ID]:0,
				'language_id' 	=> $language['language_id'],
				'control' 		=> $this->C,
				'descriptions'	=> $data[$this->C.'_description']
			];
			$data['editors'][$language['language_id']] = $this->load->controller('bytao/editor.loadedit',$sendData);
		}


		$this->load->model('setting/store');
		$data['stores'] = [];
		$data['stores'][] = [
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		];

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = [
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			];
		}

		if (!empty($item_info)) {
			$data['bottom'] = $item_info['bottom'];
		} else {
			$data['bottom'] = 0;
		}

		if (!empty($item_info)) {
			$data['status'] = $item_info['status'];
		} else {
			$data['status'] = true;
		}

		if (!empty($item_info)) {
			$data['sort_order'] = $item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['store_id'] = $this->session->data['store_id'];
		$data[$this->Tkn] = $this->session->data[$this->Tkn];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->cPth.'_form', $data));
	}

	public function save(): void
	{
		$this->getML('ML');

		$json = [];

		if (!$this->user->hasPermission('modify', $this->cPth)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!empty($this->request->post['module_pdf_invoice_font']) && !file_exists(DIR_SYSTEM . 'library/shared/tcpdf/fonts/' . $this->request->post['module_pdf_invoice_font'] . '.php')) {
			$json['error']['warning'] = sprintf($this->language->get('error_font'), $this->request->post['module_pdf_invoice_font']);
		}

		if (!$json) {
			$this->model->{$this->getFunc('editPdf')}( $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	

	public function preview() {
		
		$this->getML('ML');
		$this->load->model('sale/order');

		$order_id = 0;

		if (!empty($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$result = $this->db->query("SELECT o.order_id FROM `" . DB_PREFIX . "order` o WHERE (" . implode(" OR ", $implode) . ") ORDER BY o.order_id DESC LIMIT 1");

				if ($result->row) {
					$order_id = $result->row['order_id'];
				} else {
					// Get any order
					$result = $this->db->query("SELECT o.order_id FROM `" . DB_PREFIX . "order` o ORDER BY o.order_id DESC LIMIT 1");

					if ($result->row) {
						$order_id = $result->row['order_id'];
					} else {
						trigger_error("Warning: requires at least one COMPLETE order to preview invoice pdf!");
						exit;
					}
				}
			}
		}

		$order_info = $this->model_sale_order->getOrder($order_id);

		if (!$order_info) {
			trigger_error("Warning: unable to find order = '{$order_id}'");
			return false;
		}

		// Overwrite language_id
		if (!empty($this->request->get['language_id'])) {
			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($this->request->get['language_id']);

			if ($language_info) {
				$order_info['language_id'] = $language_info['language_id'];
				$order_info['language_code'] = $language_info['code'];
			}
		}

		echo $this->model->{$this->getFunc('getInvoice')}(array($order_info), false);
		exit(0);
	}

	public function generate() {
		$this->getML('ML');
		if (isset($this->request->post['selected'])) {
			$selected = $this->request->post['selected'];
		} elseif (isset($this->request->get['selected'])) {
			$selected = $this->request->get['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$selected = array($this->request->get['order_id']);
		}

		if (!empty($selected)) {
			echo $this->model->{$this->getFunc('getInvoice')}($selected, false, true);
			exit(0);
		}
	}

	public function uninstall() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->deleteSetting('module_pdf_invoice');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/pdf_invoice')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        // Check font exists
        if (!empty($this->request->post['module_pdf_invoice_font']) && !file_exists(DIR_SYSTEM . 'library/shared/tcpdf/fonts/' . $this->request->post['module_pdf_invoice_font'] . '.php')) {
            $this->error['warning'] = sprintf($this->language->get('error_font'), $this->request->post['module_pdf_invoice_font']);
        }

		return !$this->error;
	}

    /**
     * Get tcpdf fonts from path: library/shared/tcpdf/fonts
     * @return array
     */
    protected function _getTcpdfFonts() {
        $fonts = array();

        $files = glob(DIR_SYSTEM . 'library/shared/tcpdf/fonts/*.php', GLOB_BRACE);

        $suffixes = array('bi', 'b', 'i');

        if ($files) {
            foreach ($files as $file) {
                $base_name = basename($file, '.php');

                foreach($suffixes as $suffix) {
                    $length = strlen($suffix);
                    if (substr($base_name, -$length) === $suffix) {
                        $base_name = substr($base_name, 0, strlen($base_name)-$length);
                    }
                }

                if (!isset($fonts[$base_name])) {
                    $fonts[$base_name] = array();
                }

                $fonts[$base_name][] = basename($file, '.php');
            }
        }

        return $fonts;
    }
}
