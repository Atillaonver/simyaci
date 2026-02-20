<?php

namespace Opencart\Admin\Model\Bytao;

static $registry = null;


// Error Handler
function error_handler_for_export_import($errno, $errstr, $errfile, $errline) {
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


function fatal_error_shutdown_handler_for_export_import()
{
	$last_error = error_get_last();
	if (($last_error) && ($last_error['type'] === E_ERROR)) {
		// fatal error
		error_handler_for_export_import(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}


class Export extends \Opencart\System\Engine\Model
{
	private $error = [];
	protected $posted_categories = '';
	protected $null_array = [];
	protected $posted_manufacturers = '';
	protected $titles = [];
	protected $TITLES = [];
	protected $formats = [];
	protected $Languages = [];
	
	protected $version = '4.13.1';



	public function download($data,$offset = null, $rows = null, $min_id = null, $max_id = null)
	{
		// we use our own error handler
		global $registry;
		$registry = $this->registry;
		
		set_error_handler('\Opencart\Admin\Model\Bytao\error_handler_for_export_import',E_ALL);
		register_shutdown_function('\Opencart\Admin\Model\Bytao\fatal_error_shutdown_handler_for_export_import');
		
		$this->posted_categories = $this->getPostedCategories();

		try
		{
			if (version_compare(phpversion(), '7.2.', '<')) {
				throw new \Exception( $this->language->get( 'error_php_version' ) );
			}
			require( DIR_EXTENSION.'export_import/system/library/export_import/vendor/autoload.php' );
			
			$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

			// find out whether all data is to be downloaded
			$all = !isset($offset) && !isset($rows) && !isset($min_id) && !isset($max_id);

			$this->posted_categories = $this->getPostedCategories();
			//$this->posted_manufacturers = $this->getPostedManufacturers();

			// set appropriate timeout limit
			set_time_limit( 1800 );

			$languages = $this->getLanguages();
			$default_language_id = $this->getDefaultLanguageId();

			// create a new workbook
			$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();			
			$this->setFormats();

			$workbook->getDefaultStyle()->getFont()->setName('Arial');
			$workbook->getDefaultStyle()->getFont()->setSize(10);
			//$workbook->getDefaultStyle()->getAlignment()->setIndent(0.5);
			$workbook->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
			$workbook->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			$workbook->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);

			// pre-define some commonly used styles
			
			
			// create the worksheets
			$worksheet_index = 0;
			
			$workbook->setActiveSheetIndex(0);

			// create the worksheets
			if(isset($data['orders'])){
				$worksheet_index = 0;
				$workbook->setActiveSheetIndex($worksheet_index++);
				$worksheet       = $workbook->getActiveSheet();
				$worksheet->setTitle( $data['texts']['orders'] );

				$this->populateDataWorksheet( $worksheet, $data, $box_format, $text_format, $offset, $rows, $min_id, $max_id );

				$worksheet->freezePaneByColumnAndRow( 1, 2 );
				$workbook->setActiveSheetIndex(0);
				$datetime        = date('Y-m-d');

				$filename        = $data['texts']['orders'].'-'.$datetime.'.xlsx';
				
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
				
				$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Xlsx');
				$objWriter->setPreCalculateFormulas(false);
				$objWriter->save('php://output');
			}


			if(isset($data['productIDs'])){

				$worksheet_index = 0;
				$this->setProdTitles();
				$workbook->setActiveSheetIndex($worksheet_index++);
				$worksheet       = $workbook->getActiveSheet();
				$worksheet->setTitle( $data['texts']['products'] );
				$this->populateProductsWorksheet($worksheet,isset($data['productIDs'])?$data['productIDs']:[]);
				$worksheet->freezePaneByColumnAndRow( 2, 2 );
				
				
				$workbook->createSheet();
				$workbook->setActiveSheetIndex($worksheet_index++);
				$worksheet = $workbook->getActiveSheet();
				$worksheet->setTitle( $data['texts']['references'] );
				$this->populateReferenceDataWorksheet( $worksheet, $this->getLanguages(), $this->formats['box'], $this->formats['text'],$data['texts'], $offset, $rows, $min_id, $max_id);
				$worksheet->freezePaneByColumnAndRow( 1, 2 );



				$workbook->setActiveSheetIndex(0);
				$datetime        = date('Y-m-d');
				$filename        = $this->session->data['long_name'].'-'.$data['texts']['products'].'-'.$datetime.'-downloaded.xlsx';
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
				$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Xlsx');
    			//$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');  
				$objWriter->setPreCalculateFormulas(false);
				$objWriter->save('php://output');


			}

			if(isset($data['customers'])){

				$worksheet_index = 0;
				$workbook->setActiveSheetIndex($worksheet_index++);
				$worksheet       = $workbook->getActiveSheet();
				$worksheet->setTitle('customers' );
				$this->populateCustomerDataWorksheet( $worksheet, $data, $box_format, $text_format, $offset, $rows, $min_id, $max_id );
				$worksheet->freezePaneByColumnAndRow( 1, 2 );

				$workbook->setActiveSheetIndex(0);
				$datetime        = date('Y-m-d');
				$filename        = $this->session->data['name'].'-customers-'.$datetime.'-downloaded.xlsx';
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');
				$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Xlsx');
				//$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Excel2007');
				$objWriter->setPreCalculateFormulas(false);
				$objWriter->save('php://output');
			}

			// Clear the spreadsheet caches
			
			if(isset($data['catalog'])){
				
				
				$datetime        = date('Y-m-d');
				$filename        = $this->session->data['name'].'-ornek-'.$datetime.'-downloaded.pdf';
				
				header('Content-Type: application/pdf');
				header('Content-Disposition: attachment;filename="'.$filename.'"');
				header('Cache-Control: max-age=0');

				//$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'PDF');
				$objWriter->save('php://output');
				
				
			}
			

			// Clear the spreadsheet caches
			$this->clearSpreadsheetCache();
			exit;

		} catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['export_import_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return;
		}
	}
	
	protected function setOrderTitles():void {
		$_titles = explode(',','Product Id:12:product_id:text,Status:16:status:text,Model Cod:16:model:text,Name(en):40:name:text,Category:140:categories:text,Colors Group:40:color:text,Colors:40:colors:text,Sizes:40:sizes:text,Price:16:price:price,Sale Price:16:sale_price:price,Whole Sale Price:16:whole_sale_price:price,Description (en):140:description:text,2.Description (en):140:description_alt:text,Bullet Points (en):140:bullet:text,Meta Title(en):100:meta_title:text,Meta Description(en):100:meta_description:text,Meta Tag Keywords(en):100:meta_keyword:text,Product Tag(en):100:tag:text,Material:50:metarial:text');
		
		foreach($_titles as $_title){
			$title = explode(':',$_title);
			$this->titles[$title[2]]=[
				'code' => $title[2],
				'title' => $title[0],
				'type' => $title[3],
				'size' => $title[1]
			];
			$this->TITLES[$title[0]]=[
				'code' => $title[2],
				'title' => $title[0],
				'type' => $title[3],
				'size' => $title[1]
			];
			
		}
		return;
	}
	
	protected function setProdTitles():void {
		$_titles = explode(',','Product Id:12:product_id:text:int,Status:16:status:text:int,Subtract:16:subtract:text:int,Model:16:model:text:text,Name(en):40:name:text:text,Category:140:categories:text:text,Colors Group:40:color:text:text,Colors:40:colors:text:text,A/W/R:5:colort:text:text,Sizes:40:sizes:text:text,Quantity:8:quantity:text:int,Price:20:price:price:float,Sale Price:20:sale_price:price:float,Whole Sale Price:20:whole_sale_price:price:float,3XL Price:20:3xl_price:price:float,4XL Price:20:4xl_price:price:float,Description(en):140:description:text:text,Description Alt(en):140:description_alt:text:text,Bullet(en):140:bullet:text:text,Meta Title(en):100:meta_title:text:text,Meta Description(en):100:meta_description:text:text,Meta Keyword(en):100:meta_keyword:text:text,Product Tag(en):100:tag:text:text,Material:50:material:text:text');
		$this->titles = [];
		
		foreach($_titles as $_title){
			$title = explode(':',$_title);
			$this->titles[$title[2]]=[
				'code' => $title[2],
				'title' => $title[0],
				'type' => $title[3],
				'size' => $title[1],
				'veri' => $title[4],
			];
			$this->TITLES[$title[0]]=[
				'code' => $title[2],
				'title' => $title[0],
				'type' => $title[3],
				'size' => $title[1],
				'veri' => $title[4]
			];
		}
		return;
	}

	protected function setFormats():void {
		
		$this->formats['box']= [
				'fill' => [
					'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'F0F0F0'],
					'endColor'   => ['rgb' => 'F0F0F0']
				],
				'borders' => [
					'right' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						'color'       => ['rgb' => 'FFF0F0']
					]
				]
				
		];
			
		$this->formats['text'] = [
				'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL]
				/*,
				'alignment' => array(
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
					'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
				*/
		];
			
		$this->formats['price'] = [
				'numberFormat' => ['formatCode' => '######0.00'],
				'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
				/*
				,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrap' => false,'indent' => 0
				*/
				]
		];
			
			
		$this->formats['weight'] = [
				'numberFormat' => ['formatCode' => '##0.00'],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
					/*
					,
					'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
					*/
				]
		];
		
		
		$this->formats['first_cols'] = [
			'font'	=> ['color' => ['rgb' => '000000'],'bold' => TRUE],
			'fill' => [
					'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => 'F0F0F0'],
					'endColor'   => ['rgb' => 'F0F0F0']
			],
			'alignment' => [
				'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			]
		];

		
		$this->formats['standart'] = [
			'fill'           => [
				'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => ['rgb' => 'EFEFEF'],
				'endColor'   => ['rgb' => 'EFEFEF']
				
			]];
		
		$this->formats['reverse'] = [
			'fill'           => [
				'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => ['rgb' => 'EFEFEF'],
				'endColor'   => ['rgb' => 'EFEFEF']
				
			]
		];
		
		$this->formats['red'] = [
			'font'	=> ['color' => ['rgb' => 'FFFF0000'],'bold' => TRUE],
			'alignment' => [
				'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			]
		];
		$this->formats['red_reverse'] = [
			'font'	=> ['color' => ['rgb' => 'FF000000'],'bold' => TRUE],
			'alignment' => [
				'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			]
		];
		
		
		$this->formats['default'] = [
			'alignment' => [
				'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'wrap'      => false
			],
			'alignment' => [
				'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				'vertical'  => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				'wrap'      => false
			]
		];	
		
		$this->formats['align'] = [
			
		];	
		
	}



	protected function populateDataWorksheet( & $worksheet,$dataN, & $box_format, & $text_format, $offset = null, $rows = null, & $min_id = null, & $max_id = null )
	{
		if(isset($dataN['orders'])){
			$titles = explode(',',$dataN['text_order_titles']);
		}

		if(isset($dataN['products'])){
			$titles = explode(',',$this->config->get('config_product_export_title'));
		}

		$j = 0;
		foreach($titles as $title){
			$_title = explode(':',$title);
			$worksheet->getColumnDimensionByColumn($j++)->setWidth($_title[1]);
		}

		// The heading row and column styles
		$this->setFormats();
		$data = [];
		$i = 1;
		$j = 0;
		foreach($titles as $title){
			$_title = explode(':',$title);
			$data[$j++] = $_title[0];
		}

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $this->formats['box'] );

		// The actual categories data
		$i += 1;
		$j = 0;

		if(isset($dataN['orders'])){
			$len    = count($dataN['orders']);
			$min_id = $dataN['orders'][0]['order_id'];
			$max_id = $dataN['orders'][$len - 1]['order_id'];
			foreach($dataN['orders'] as $row){
				$worksheet->getRowDimension($i)->setRowHeight(26);
				$data = [];
				$data[$j++] = $row['order_id'];
				$data[$j++] = $row['invoice_no'];
				$data[$j++] = $row['date_added'];
				$data[$j++] = $row['store_name'];
				$data[$j++] = $row['email'];
				$data[$j++] = $row['status'];
				$data[$j++] = $row['telephone'];
				$data[$j++] = $row['name'];
				$data[$j++] = $row['country'];
				$data[$j++] = $row['zone'];
				$data[$j++] = $row['shipping_address'];
				$content = '';
				foreach($row['product'] as $product){
					$content .= $product['name'].' ';
					$content .= $product['model'].' ';
					foreach($product['option'] as $option){
						$content .= $option['name'].' ';
						$content .= $option['value'].' ';
					}
					$content .= 'quantity:'.$product['quantity'].' ';
				}

				$data[$j++] = html_entity_decode($content,ENT_QUOTES,'UTF-8');
				$data[$j++] = $row['total'];
				$data[$j++] = $row['tax'];
				$data[$j++] = $row['comment'];
				$genres = [];
				foreach($row['product'] as $product){
					$genres [] = $product['gender'].' ';
				}
				$data[$j++] = implode(',',$genres);
				
				$names = [];
				foreach($row['product'] as $product){
					$names [] = $product['model'].' ';
				}
				$data[$j++] = implode(',',$names);
				
				$cats = [];
				foreach($row['product'] as $product){
					$cats [] = $product['cat'].' ';
				}
				$data[$j++] = implode(',',$cats);
				

				//$data[$j++] = html_entity_decode($row['meta_keyword'][$language['code']],ENT_QUOTES,'UTF - 8');

				$this->setCellRow( $worksheet, $i, $data );
				$i += 1;
				$j = 0;
			}
		}

		if(isset($dataN['products'])){

			$len    = count($dataN['products']);
			$min_id = $dataN['products'][0]['product_id'];
			$max_id = $dataN['products'][$len - 1]['product_id'];
			foreach($dataN['products'] as $row){
				foreach($dataN['options'] as $option){
					$data = [];
					$worksheet->getRowDimension($i)->setRowHeight(26);
					if($option['option_parent_value']){
						$data[$j++] = $row['product_id'];
						$data[$j++] = $row['model'];
						$data[$j++] = $row['category'];
						$data[$j++] = $row['name'];
						$data[$j++] = $row['name'];
						$data[$j++] = $row['model'];
						$data[$j++] = $row['description'];
						$data[$j++] = $row['option_parent_value'];
						$data[$j++] = $row['option_value'];
						$data[$j++] = $row['quantity'];
						$data[$j++] = $row['price'];
						$data[$j++] = $row['total'];

						$this->setCellRow( $worksheet, $i, $data );

						$i += 1;
						$j = 0;
					}



				}
			}
		}



		$this->setColumnStyles( $worksheet, $styles, 2, $i - 1 );
	}


	protected function populateProductDataWorksheet_EX( & $worksheet,$dataN, & $box_format, & $text_format, $offset = null, $rows = null, & $min_id = null, & $max_id = null )
	{

		$languages           = $this->getLanguages();
		$default_language_id = $this->getDefaultLanguageId();
		//$_titles = explode(',',$this->config->get('config_product_export_title'));
		$_titles             = explode(',','Product Id:12:product_id,Status:16:status,Model Cod:16:model,Name(en):40:name,Category:140:categories,Colors Group:40:color,Colors:40:colors,Sizes:40:sizes,Price:16:price,Sale Price:16:sale_price,Whole Sale Price:16:whole_sale_price,Paragraph 1 (en):40:paragraph1,Paragraph 2 (en):40:paragraph2,Bullet Points (en):40:bullet,Meta Description(en):100:meta_description,Meta Key word(en):100:meta_keyword,Material:50:metarial');


		$j                   = 0;
		foreach($_titles as $title){
			$_title = explode(':',$title);
			$worksheet->getColumnDimensionByColumn($j++)->setWidth($_title[1]);
		}

		// The heading row and column styles
		
		$data = [];
		$i = 1;
		$j = 0;
		foreach($_titles as $title){
			$_title = explode(':',$title);
			$data[$j++] = $_title[0];
		}



		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );

		$i += 1;
		$j       = 0;

		$reverse = true;
		
		if(isset($dataN['productIDs'])){

			foreach($dataN['productIDs'] as $product_id){
				$reverse = !$reverse;
				$product = $this->getProduct( $languages, $default_language_id,$product_id );
				$_options= [];
				if($product['options'])
				{
					foreach($product['options'] AS $option)
					{
						$_options[$option['gentype']][$option['option']][] = $option['option_value'];
					}
					$product['options'] = $_options;
				}



				foreach($_titles as $_title){
					$nTitle = explode(':',$_title);
					$pdata = [];
					if(isset($nTitle[4])){
						$table = $nTitle[4];
					} else{
						$table    = 'product';
					}
					


					$position = strpos($nTitle[0], '(');
					if($position === false){
						switch($nTitle[2])
						{
							case 'color':
									if(isset(${$table}['options']['radio']))
									{
										$color = '';
										foreach(${$table}['options']['radio'] as $key => $value)
										{
											$color = $key;
										}
										$pdata[$j++] = $key;
									}
									else
									{
										$pdata[$j++] = '';
									}
							break;

							case 'colors':
									$color = '';
									foreach(${$table}['options']['radio'] as $key=> $value)
									{
										$color = $key;
									}
									$pdata[$j++] = isset(${$table}['options']['radio'][$color])? implode(' - ',${$table}['options']['radio'][$color]):'';
							break;

							case 'sizes':
									$pdata[$j++] = isset(${$table}['options']['select']['Size'])? implode(' - ',${$table}['options']['select']['Size']) :'';
							break;

							default:
								$pdata[$j++] = isset(${$table}[$nTitle[2]])? ${$table}[$nTitle[2]]:'';
						}

					}
					else
					{
						foreach($languages as $language){
							$pdata[$j++] = html_entity_decode(isset(${$table}[$nTitle[2]][$language['code']])?${$table}[$nTitle[2]][$language['code']]:'',ENT_QUOTES,'UTF-8');



						}
					}
				}


				$this->setCellRow( $worksheet, $i, $pdata );
				$i += 1;
				$j = 0;

			}
		}



		//$this->setColumnStyles( $worksheet, $first_cols_format, 1, 3 );
		$this->setColumnStyles( $worksheet, $first_cols_format, 2, $i - 1 );
	}

	// Product Export XLS
	protected function populateProductsWorksheet( &$worksheet,$product_ids) {
		
		$languages = $this->getLanguages();
		$default_language_id = $this->getDefaultLanguageId();
		
		$i = 1;
		$j = 1;
		$ij = 1;
		$styles = [];
		$reverStyle = [];
		$topRowStyles = [];
		$rStyles = [];
		$data = [];
		
		$table    = 'product';
		$SIZES = $this->getOptionValues('size');
		$allSizes=['2XS','XS','S','M','L','XL','2XL','3XL','4XL'];
		
		foreach($this->titles as $code => $prodtitle){
			if($prodtitle['title']=='Sizes'){
				foreach($allSizes as $key => $SIZE){
					$worksheet->getColumnDimensionByColumn($j)->setWidth(6);
					$data[$j++] = $SIZE;
					$styles[$j] = $this->formats['text'];
					$rStyles[$j] = $this->formats['red'];
					$reverStyle[$j] = $this->formats['red_reverse'];
					$topRowStyles[$j] = $this->formats['first_cols'];
				}
			}else{
				$worksheet->getColumnDimensionByColumn($j)->setWidth($prodtitle['size']);
				$styles[$j] = $this->formats[$prodtitle['type']];
				$rStyles[$j] = $this->formats['red'];
				$reverStyle[$j] = $this->formats['red_reverse'];
				$data[$j++] = $prodtitle['title'];
				$topRowStyles[$j] = $this->formats['first_cols'];
			}
		}
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data,$this->formats['default'],$topRowStyles );
		

		// The actual products data
		$i += 1;
		$j = 1;
		$reverse = true;
		foreach($product_ids as $product_id)
		{
			$reverse = ! $reverse;
			$product = $this->getProduct( $languages, $default_language_id,$product_id );
			$_options= [];
			if($product['options'])
			{
				foreach($product['options'] AS $option)
				{
					$_options[$option['gentype']][$option['option']][$option['option_value']] = [
						'opt_value' 	=> $option['option_value'],
						'opt_quantity' => $option['quantity'],
						'opt_type' => $option['type']
					];
				}
				$product['options'] = $_options;
				
			}
			
			$pdata=[];
			$cData=[];
			$subtract = false;
			foreach($this->titles as $code => $prodtitle){
				$position = strpos($prodtitle['title'], '(');
				if($position === false)
				{
					switch($prodtitle['code'])
					{
						case 'color':
							{
								if(isset(${$table}['options']['radio']))
								{
									$color = '';
									foreach(${$table}['options']['radio'] as $key => $value)
									{
										$color = $key;
									}
									$pdata[$j++] = $key;
								}
								else
								{
									$pdata[$j++] = '';
								}
							}
							break;
						case 'colors':
							{
								$color = '';
								if(isset(${$table}['options']['radio'])){
									foreach(${$table}['options']['radio'] as $key=> $value)
									{
										$color = $key;
									}
									$cData[] = $firtKey = array_key_first(${$table}['options']['radio'][$color]);
									$_j = $j++;
									$pdata[$_j] ='';
									
									if(isset(${$table}['options']['radio'][$color])){
										$pdata[$_j] = $firtKey;
										$optType = isset(${$table}['options']['radio'][$color][$firtKey]['opt_type'])? ${$table}['options']['radio'][$color][$firtKey]['opt_type']:'';
										switch($optType){
											case 0: $pdata[$j++] = 'A'; break;
											case 1: $pdata[$j++] = 'R'; break;
											case 2: $pdata[$j++] = 'W'; break;
											default:$pdata[$j++] = '';
										}
										
									}else{
										$pdata[$j++] = '';
									}	
									
									
								}
								else{
									$pdata[$j++] = '';
									$pdata[$j++] = '';
								}	
							}
							break;
						case 'colort':
							{
								/*
								$_j = $j++;
								$pdata[$_j] ='';
								
								if(isset(${$table}['options']['radio'][$color])){
									
									$pdata[$_j] = isset(${$table}['options']['radio'][$color]['opt_type'])?${$table}['options']['radio'][$color]['opt_type']:'';
								}
								*/
								
							}
							break;	
						case '4xl_price': $pdata[$j++] = $this->getSizePrice($product['options'],$SIZES,'4XL'); break;
						case '3xl_price': $pdata[$j++] = $this->getSizePrice($product['options'],$SIZES,'3XL'); break;
						case 'sizes':
							{
								foreach($allSizes as $SIZE){
									if(isset(($product['options']['select']['Size'][$SIZE]))){
										switch($product['options']['select']['Size'][$SIZE]['opt_type']){
											case 0: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE]['opt_quantity']:'A'; break;
											case 1: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE]['opt_quantity']:'R'; break;
											case 2: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE]['opt_quantity']:'W'; break;
											default:$pdata[$j++] = '';
										}
									}else{
										$pdata[$j++] =$subtract?'':'';
									}
								}
							}
							break;
						case 'subtract':
							{
								if(${$table}[$prodtitle['code']]){
									$subtract =true;
								}
								$pdata[$j++] = $subtract?${$table}[$prodtitle['code']]:'';
							}
							break;
						default:
								$pdata[$j++] = isset(${$table}[$prodtitle['code']])? ${$table}[$prodtitle['code']]:'';
					}
				}
				else
				{
					foreach($languages as $language){
						if(isset(${$table}[$prodtitle['code']][$language['code']])){
							$content = ${$table}[$prodtitle['code']][$language['code']];
							$_content = explode('+',$content);
							if($prodtitle['code']=='bullet'){
								$content = '';
								foreach($_content as $part){
									if($part){
										$content .= 'æ '.$part.chr(10);
									}
								}
							}
							
							$pdata[$j++] = html_entity_decode($content,ENT_QUOTES,'UTF-8');
						}else{
							$pdata[$j++] = '';
						}
					}
				}
			}
			
			
			
			if ($subtract)
			{
				$this->setCellRow( $worksheet, $i, $pdata, $this->formats['default'], $rStyles );
			}
			else
			{
				$this->setCellRow( $worksheet, $i, $pdata, $this->formats['default'], $styles );
			}
			
			$i += 1;
			
			$color = '';
			if(isset(${$table}['options']['radio'])){
				foreach(${$table}['options']['radio'] as $key=> $value)
				{
					$color = $key;
				}	
				
				foreach(${$table}['options']['radio'][$color] as $_color)
				{
					if (! in_array($_color['opt_value'], $cData)) 
					{
						$j = 1;
						foreach($this->titles as $title){
							if($title['title']=='Colors') break;
							$pdata[$j++] = '';
						}
						
						$pdata[$j++] = $_color['opt_value'];
						
						$cData[] = $_color['opt_value'];
						
						switch($_color['opt_type']){
							case 0: $pdata[$j++] = 'A'; break;
							case 1: $pdata[$j++] = 'R'; break;
							case 2: $pdata[$j++] = 'W'; break;
						}
						
						
						foreach($SIZES as $SIZE){
							if(isset($product['options']['select']['Size'][$SIZE['name']]['opt_quantity'])){
								switch($product['options']['select']['Size'][$SIZE['name']]['opt_type']){
									case 0: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE['name']]['opt_quantity']:'A'; break;
									case 1: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE['name']]['opt_quantity']:'R'; break;
									case 2: $pdata[$j++] = $subtract?$product['options']['select']['Size'][$SIZE['name']]['opt_quantity']:'W'; break;
									default:$pdata[$j++] = '';
								}
								//$pdata[$j++] = $product['options']['select']['Size'][$SIZE['name']]['opt_quantity'];
							}else{
								$pdata[$j++] ='';
							}
						}
						
						//$pdata[$j++] = $_color['opt_quantity'];
						
						while($j<(count($this->titles)+count($SIZES))){
							$pdata[$j++] ='';
						}
						
						if ($subtract){
							$this->setCellRow( $worksheet, $i, $pdata, $this->formats['default'], $rStyles );
						}else{
							$this->setCellRow( $worksheet, $i, $pdata, $this->formats['default'], $styles );
						}
						$i += 1;
					}
				}
			}
			
			$j = 1;
			
		}
		
	}
	
	protected  function getSizePrice($options,$SIZES,$name){
		foreach($SIZES as $SIZE){
			if(isset(($options['select']['Size'][$name]))){
				return isset($options['select']['Size'][$name]['price'])?$options['select']['Size'][$name]['price']:'';
			}else{
				return '';
			}
		}
	}
	
	
	
	
	protected function getOptionValues(string $opt='' )
	{
		$sizes=['2XS','XS','S','M','L','XL','2XL','3XL','4XL'];
		$language_id = $this->getDefaultLanguageId();
		
		$sql         = "SELECT oVi.* FROM `".DB_PREFIX."option_value_description` oVi ";
		$sql 		.= "LEFT JOIN `".DB_PREFIX."option_value` oV ON (oV.option_value_id = oVi.option_value_id)"; 
		$sql 		.= "LEFT JOIN `".DB_PREFIX."option_description` od ON (od.option_id = oVi.option_id) "; 
		$sql 		.= "WHERE oVi.language_id='".(int)$language_id."' AND LOWER(od.name) LIKE '".$opt."' ORDER BY oV.sort_order ASC";
		$query = $this->db->query( $sql );

		$option_value_ids = [];
		if(isset($query->rows)){
			foreach($query->rows as $row){
				$option_id       = $row['option_id'];
				$option_value_id = $row['option_value_id'];
				$name = htmlspecialchars_decode($row['name']);
				if (in_array($name, $sizes)) {
					$option_value_ids[$name] = [
						'id' => $option_value_id,
						'name' => $name
					];
				}
			}
		}
		
		return $option_value_ids;
	}


	protected function getProduct( & $languages, $default_language_id, $product_id = 0, $offset = null, $rows = null, $min_id = null, $max_id = null )
	{
		$sql = "SELECT ";
		$sql .= "  p.product_id,";
		$sql .= "  GROUP_CONCAT( DISTINCT CAST(pc.category_id AS CHAR(11)) SEPARATOR \",\" ) AS categories,";
		$sql .= "  p.location,";
		$sql .= "  p.quantity,";
		$sql .= "  p.model,";
		//$sql .= "  m.name AS manufacturer,";
		$sql .= "  p.image AS image_name,";
		$sql .= "  p.shipping,";
		$sql .= "  p.cost_price,";
		$sql .= "  p.sale_price,";
		$sql .= "  p.our_price,";
		$sql .= "  p.retail_price,";
		$sql .= "  p.whole_sale_price,";
		$sql .= "  p.price,";
		$sql .= "  p.points,";
		$sql .= "  p.date_added,";
		$sql .= "  p.date_modified,";
		$sql .= "  p.date_available,";
		$sql .= "  p.weight,";
		$sql .= "  wc.unit AS weight_unit,";
		$sql .= "  p.length,";
		$sql .= "  p.width,";
		$sql .= "  p.height,";
		$sql .= "  p.status,";
		$sql .= "  p.tax_class_id,";
		$sql .= "  p.sort_order,";
		//$sql .= "  ua.keyword,";
		$sql .= "  p.stock_status_id, ";
		$sql .= "  mc.unit AS length_unit, ";
		$sql .= "  p.subtract, ";
		$sql .= "  p.minimum, ";
		$sql .= "  p.material, ";
		$sql .= "  GROUP_CONCAT( DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR \",\" ) AS related ";
		$sql .= "FROM `".DB_PREFIX."product` p ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_to_category` pc ON p.product_id=pc.product_id ";

		//if($this->posted_categories){
			$sql .= " LEFT JOIN `".DB_PREFIX."product_to_category` pc2 ON p.product_id=pc2.product_id ";
		//}

		//$sql .= "LEFT JOIN `".DB_PREFIX."seo_url` s ON  p.product_id=s.value ";
		//$sql .= "LEFT JOIN `".DB_PREFIX."manufacturer` m ON m.manufacturer_id = p.manufacturer_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."weight_class_description` wc ON wc.weight_class_id = p.weight_class_id ";
		$sql .= "  AND wc.language_id=$default_language_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."length_class_description` mc ON mc.length_class_id=p.length_class_id ";
		$sql .= "  AND mc.language_id=$default_language_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_related` pr ON pr.product_id=p.product_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON p2s.product_id=p.product_id ";
		$sql .= "WHERE p.product_id = '".(int)$product_id ."'";
		$sql .= " LIMIT 1";
		$sql .= "; ";
		

		$results = $this->db->query( $sql );
		$ROW = isset($results->row)?$results->row:[];
		
		$product_descriptions = $this->getProductDescriptions( $languages,$product_id, $offset, $rows, $min_id, $max_id );
		foreach($languages as $language){
			$language_code = $language['code'];
			
			if(isset($product_descriptions[$language_code])){
				
				
				$ROW['name'][$language_code] = $product_descriptions[$language_code]['name'];
				
				$description = $product_descriptions[$language_code]['description'];//html_entity_decode(nl2br(,FALSE), ENT_QUOTES , 'UTF - 8');

				$_description = explode(':::',$description);
				if(count($_description)>1)
				{
					$ROW['description'][$language_code] = isset($_description[0])?preg_replace('/\s+/',' ',strip_tags(html_entity_decode(nl2br($_description[0],FALSE), ENT_QUOTES , 'UTF-8'))):'';
					$ROW['description_alt'][$language_code] = isset($_description[1])?preg_replace('/\s+/',' ',strip_tags(html_entity_decode(nl2br($_description[1],FALSE), ENT_QUOTES , 'UTF-8'))):'';
					$ROW['bullet'][$language_code] = isset($_description[2])?preg_replace('/\s+/',' ',strip_tags(html_entity_decode(nl2br($_description[2],FALSE), ENT_QUOTES , 'UTF-8'))):'';
				}
				else
				{
					$ROW['description'][$language_code] = html_entity_decode(nl2br($description,FALSE), ENT_QUOTES , 'UTF-8');
					$ROW['description_alt'][$language_code] = $product_descriptions[$language_code]['description_alt'];
					$ROW['bullet'][$language_code] = $product_descriptions[$language_code]['bullet'];
				}
				$ROW['meta_title'][$language_code] = $product_descriptions[$language_code]['meta_title'];
				$ROW['meta_description'][$language_code] = $product_descriptions[$language_code]['meta_description'];
				$ROW['meta_keyword'][$language_code] = $product_descriptions[$language_code]['meta_keyword'];
				$ROW['tag'][$language_code] = $product_descriptions[$language_code]['tag'];
				$ROW['url'][$language_code] = $product_descriptions[$language_code]['url'];
			}
			else
			{
				$ROW['name'][$language_code] = '';
				$ROW['description'][$language_code] = '';
				$ROW['description_alt'][$language_code] = '';
				$ROW['bullet'][$language_code] = '';
				$ROW['meta_title'][$language_code] = '';
				$ROW['meta_description'][$language_code] = '';
				$ROW['meta_keyword'][$language_code] = '';
				$ROW['tag'][$language_code] = '';
				$ROW['url'][$language_code] = '';
			}
		}
		
		if(isset($ROW['categories']))
		{
			
			$categories = explode(',',$results->row['categories']);
			$_categories = [];
			
			foreach($categories as $category_id)
			{
				
				$name = '';
				
				$sql  = "SELECT GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') AS name FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (cp.category_id = c2s.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cp.category_id ='".(int)$category_id."' AND c2s.store_id='".(int)$this->session->data['store_id']."'";
				$cQuery = $this->db->query($sql);

				if(isset($cQuery->row['name']))
				{
					$name = html_entity_decode($cQuery->row['name'],ENT_QUOTES,'UTF-8');
					//$this->log->write('name:'.$name);
					if(count($_categories) == 0)
					{
						$_categories[] = $name;
					}
					else
					{
						$dd = false;
						for($i = 1; $i <= count($_categories); $i++)
						{

							$konum = strpos($name, $_categories[$i - 1]);
							if($konum !== false )
							{
								if(strlen($_categories[$i - 1]) < strlen($name))
								{
									$_categories[$i - 1] = $name;
								}


								$dd = false;
								break;


							}
							else
							{

								$dd = true;

							}
						}
						if($dd)
						{
							$_categories[] = $name;
						}
					}



				}

			}
			if(count($_categories) > 0)
			{
				$ROW['categories'] = implode(' , ',$_categories);
			}
		}else{
			
			$ROW['categories'] = '';
		}

		
		$product_option_values = $this->getProductOptionValues( $product_id);
		$ROW['options'] = $product_option_values;

		return $ROW;
	}


	protected function getProductDescriptions( & $languages,$product_id = 0, $offset = null, $rows = null, $min_id = null, $max_id = null )
	{
		// some older versions of OpenCart use the 'product_tag' table
		$exist_table_product_tag = false;
		$query = $this->db->query( "SHOW TABLES LIKE '".DB_PREFIX."product_tag'" );

		// query the product_description table for each language
		$product_descriptions = [];
		foreach($languages as $language){
			$language_id   = $language['language_id'];
			$language_code = $language['code'];
			$sql           = "SELECT p.product_id, ".(($exist_table_product_tag) ? "GROUP_CONCAT(pt.tag SEPARATOR \",\") AS tag, " : "")."pd.* ";
			
			$sql .= "FROM `".DB_PREFIX."product` p ";
			$sql .= "LEFT JOIN `".DB_PREFIX."product_description` pd ON pd.product_id=p.product_id AND pd.language_id='".(int)$language_id."' ";
			$sql .= "WHERE p.product_id='$product_id';";
			$query = $this->db->query( $sql );
			
			$url   = '';
			$urlsql= "SELECT uk.keyword as url FROM `".DB_PREFIX."url_keyword` uk LEFT JOIN `".DB_PREFIX."url_alias` ua ON (uk.url_alias_id = ua.url_alias_id) WHERE ua.query = 'product_id=" . (int)$product_id . "' AND uk.language_id='".(int)$language_id."' AND ua.store_id ='".(int)$this->session->data['store_id']."' ";
			$urlquery = $this->db->query( $urlsql );
			if(isset($urlquery->row['url']))
			{
				$url = $urlquery->row['url'];
			}
			else
			{
				$urlsql = "SELECT keyword as url FROM `".DB_PREFIX."url_alias`  WHERE query = 'product_id=" . (int)$product_id . "' AND store_id ='".(int)$this->session->data['store_id']."' ";
				$urlquery = $this->db->query( $urlsql );
				if(isset($urlquery->row['url']))
				$url = $urlquery->row['url'];
			}
			$query->row['url'] = $url;
			$product_descriptions[$language_code] = $query->row;
		}
		return $product_descriptions;
	}

	protected function getProductSEOKeywords( &$languages, $min_id, $max_id ) {
		$sql  = "SELECT s.* FROM `".DB_PREFIX."seo_url` s ";
		if ($this->posted_categories) {
			$sql .= "LEFT JOIN `".DB_PREFIX."product_to_category` pc ON pc.product_id=s.value ";
		}
		if ($this->posted_manufacturers) {
			$sql .= "LEFT JOIN `".DB_PREFIX."product` p ON p.product_id=s.value ";
		}
		$sql .= "WHERE s.`key` = 'product_id' AND ";
		if ($this->posted_categories) {
			$sql .= "pc.category_id IN ".$this->posted_categories." AND ";
		}
		if ($this->posted_manufacturers) {
			$sql .= "p.manufacturer_id IN ".$this->posted_manufacturers." AND ";
		}
		$sql .= "s.`value` >= '".(int)$min_id."' AND ";
		$sql .= "s.`value` <= '".(int)$max_id."' ";
		$sql .= "ORDER BY s.value, s.store_id, s.language_id";
		$query = $this->db->query( $sql );
		$seo_keywords = [];
		foreach ($query->rows as $row) {
			$product_id = (int)$row['value'];
			$store_id = (int)$row['store_id'];
			$language_id = (int)$row['language_id'];
			if (!isset($seo_keywords[$product_id])) {
				$seo_keywords[$product_id] = [];
			}
			if (!isset($seo_keywords[$product_id][$store_id])) {
				$seo_keywords[$product_id][$store_id] = [];
			}
			$seo_keywords[$product_id][$store_id][$language_id] = $row['keyword'];
		}
		$results = [];
		foreach ($seo_keywords as $product_id=>$val1) {
			foreach ($val1 as $store_id=>$val2) {
				$keyword = [];
				foreach ($languages as $language) {
					$language_id = $language['language_id'];
					$language_code = $language['code'];
					$keyword[$language_code] = isset($val2[$language_id]) ? $val2[$language_id] : '';
				}
				$results[] = array(
					'product_id'    => $product_id,
					'store_id'      => $store_id,
					'keyword'       => $keyword
				);
			}
		}
		return $results;
	}



	protected function getProductUrlAlias( & $languages,$product_id = 0, $offset = null, $rows = null, $min_id = null, $max_id = null )
	{

		$product_url_keyword = [];
		foreach($languages as $language){
			$language_id   = $language['language_id'];
			$language_code = $language['code'];

			$sql  = "SELECT uk.keyword ";
			$sql .= "FROM `".DB_PREFIX."url_keyword` uk ";
			$sql .= "LEFT JOIN `".DB_PREFIX."url_alias` ua ON (uk.url_alias_id = ua.url_alias_id) ";
			$sql .= "WHERE ua.query = 'product_id=" . (int)$product_id . "' AND uk.language_id='".(int)$language_id."' AND ua.store_id ='".(int)$this->session->data['store_id']."' ;";
			$query = $this->db->query( $sql );

			$product_url_keyword[$language_code] = $query->row;
		}
		return $product_url_keyword;
	}

	
	protected function getAdditionalImages( $product_id,$color_id  )
	{
		$default_language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT DISTINCT image ";
		$sql .= " FROM `".DB_PREFIX."product_image` ";
		$sql .= " WHERE product_id ='$product_id' AND color_id='$color_id' ";
		$sql .= " ORDER BY product_id,sort_order;";

		$result = $this->db->query( $sql );
		$images = '';

		foreach($result->rows as $image){
			$images .= HTTPS_IMAGE.$image['image'].',';
		}

		return substr($images,0, - 1)?substr($images,0, - 1):'';
	}

	protected function getProductOptions( $product_id )
	{
		// get default language id
		$language_id    = $this->getDefaultLanguageId();

		// Opencart versions from 2.0 onwards use product_option.value instead of the older product_option.option_value
		$sql            = "SHOW COLUMNS FROM `".DB_PREFIX."product_option` LIKE 'value'";
		$query          = $this->db->query( $sql );

		$exist_po_value = ($query->num_rows > 0) ? true : false;

		// DB query for getting the product options
		if($exist_po_value){
			$sql = "SELECT p.product_id, po.option_id, po.value AS option_value, po.required, od.name AS `option` FROM ";
		}
		else
		{
			$sql = "SELECT p.product_id, po.option_id, po.option_value, po.required, od.name AS `option` FROM ";
		}
		$sql .= "( SELECT p1.product_id ";
		$sql .= "  FROM `".DB_PREFIX."product` p1 ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON p2s.product_id=p1.product_id ";

		if($this->posted_categories){
			$sql .= "LEFT JOIN `".DB_PREFIX."product_to_category` pc ON pc.product_id=p1.product_id ";
		}
		$sql .= "WHERE p1.product_id='$product_id'";
		$sql .= "  ORDER BY p1.product_id ASC ";
		$sql .= ") AS p ";
		$sql .= "INNER JOIN `".DB_PREFIX."product_option` po ON po.product_id=p.product_id ";
		$sql .= "INNER JOIN `".DB_PREFIX."option_description` od ON od.option_id=po.option_id AND od.language_id='".(int)$language_id."' ";
		$sql .= "ORDER BY p.product_id ASC, po.option_id ASC";
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	protected function getProductOptionValues( $product_id )
	{
		$language_id = $this->getDefaultLanguageId();

		$sql  = "SELECT ";
		$sql .= "  p.product_id, pov.option_id, pov.option_value_id,pov2.option_value_id AS parent_option_value_id , pov.parent_id, pov.type,pov.quantity, pov.subtract, od.name AS `option`, ovd.name AS option_value, ovd2.name AS option_parent_value,ov.child,o.type AS gentype, ";
		$sql .= "  pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix ";
		$sql .= "FROM ";
		$sql .= "( SELECT p1.product_id ";
		$sql .= "  FROM `".DB_PREFIX."product` p1 ";
		$sql .= " LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON p2s.product_id=p1.product_id ";
		$sql .= " WHERE p1.product_id = '$product_id' AND p2s.store_id='".(int)$this->session->data['store_id'] ."'";
		$sql .= "  ORDER BY product_id ASC ";
		$sql .= ") AS p ";
		$sql .= "INNER JOIN `".DB_PREFIX."product_option_value` pov ON pov.product_id=p.product_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_option_value` pov2 ON pov2.product_option_value_id = pov.parent_id ";
		$sql .= "INNER JOIN `".DB_PREFIX."option_value_description` ovd ON ovd.option_value_id = pov.option_value_id AND ovd.language_id='".(int)$language_id."' ";
		$sql .= "LEFT JOIN `".DB_PREFIX."option_value_description` ovd2 ON ovd2.option_value_id = pov2.option_value_id AND ovd2.language_id='".(int)$language_id."' ";
		$sql .= "INNER JOIN `".DB_PREFIX."option_description` od ON od.option_id=ovd.option_id AND od.language_id='".(int)$language_id."' ";
		$sql .= "INNER JOIN `".DB_PREFIX."option_value` ov ON ov.option_value_id=ovd.option_value_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."option` o ON o.option_id = ov.option_id ";
		$sql .= "ORDER BY p.product_id ASC, pov.sort_order ASC";
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	protected function getPostedCategories()
	{
		$posted_categories = '';
		if(isset($this->request->post['categories'])){
			if(count($this->request->post['categories']) > 0){
				foreach($this->request->post['categories'] as $category_id){
					$posted_categories .= ($posted_categories == '') ? '(' : ',';
					$posted_categories .= $category_id;
				}
				$posted_categories .= ')';
			}
		}
		return $posted_categories;
	}

	protected function getCategoryDescriptions( & $languages, $offset = null, $rows = null, $min_id = null, $max_id = null )
	{
		// query the category_description table for each language
		$category_descriptions = [];
		foreach($languages as $language){
			$language_id   = $language['language_id'];
			$language_code = $language['code'];
			$sql           = "SELECT c.category_id, cd.* ";
			$sql .= "FROM `".DB_PREFIX."category` c ";
			$sql .= "LEFT JOIN `".DB_PREFIX."category_description` cd ON cd.category_id=c.category_id AND cd.language_id='".(int)$language_id."' ";
			if(isset($min_id) && isset($max_id)){
				$sql .= "WHERE c.category_id BETWEEN $min_id AND $max_id ";
			}
			$sql .= "GROUP BY c.`category_id` ";
			$sql .= "ORDER BY c.`category_id` ASC ";
			if(isset($offset) && isset($rows)){
				$sql .= "LIMIT $offset,$rows; ";
			}
			else
			{
				$sql .= "; ";
			}
			$query = $this->db->query( $sql );
			$category_descriptions[$language_code] = $query->rows;
		}
		return $category_descriptions;
	}

	protected function getCategories( & $languages,$key = 0, $exist_meta_title = true, $exist_seo_url_table = false, $offset = null, $rows = null, $min_id = null, $max_id = null )
	{
		$default_language_id = $this->getDefaultLanguageId();

		$sql                 = "SELECT c.*,(SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR ' > ') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$default_language_id . "' GROUP BY cp.category_id) AS path";
		$sql .= " FROM `".DB_PREFIX."category` c ";
		$sql .= "LEFT JOIN `".DB_PREFIX."url_alias` ua ON ua.query=CONCAT('category_id=',c.category_id) ";

		$sql .= "GROUP BY c.`category_id` ";
		$sql .= "ORDER BY path ASC ; ";

		$results               = $this->db->query( $sql );

		$category_descriptions = $this->getCategoryDescriptions( $languages, $offset, $rows, $min_id, $max_id );
		foreach($languages as $language){
			$language_code = $language['code'];
			foreach($results->rows as $key=>$row){

				if(isset($category_descriptions[$language_code][$key])){
					$results->rows[$key]['name'][$language_code] = $category_descriptions[$language_code][$key]['name'];
					$results->rows[$key]['description'][$language_code] = $category_descriptions[$language_code][$key]['description'];
					if($exist_meta_title){
						$results->rows[$key]['meta_title'][$language_code] = $category_descriptions[$language_code][$key]['meta_title'];
					}
					$results->rows[$key]['meta_description'][$language_code] = $category_descriptions[$language_code][$key]['meta_description'];
					$results->rows[$key]['meta_keyword'][$language_code] = $category_descriptions[$language_code][$key]['meta_keyword'];
				}
				else
				{
					$results->rows[$key]['name'][$language_code] = '';
					$results->rows[$key]['cId'][$language_code] = 0;
					$results->rows[$key]['description'][$language_code] = '';
					if($exist_meta_title){
						$results->rows[$key]['meta_title'][$language_code] = '';
					}
					$results->rows[$key]['meta_description'][$language_code] = '';
					$results->rows[$key]['meta_keyword'][$language_code] = '';
				}

			}
		}
		return $results->rows;
	}

	protected function populateCategoryDataWorksheet( & $worksheet,$languages, & $box_format, & $text_format, $text_lang = array(), $offset = null, $rows = null, & $min_id = null, & $max_id = null)
	{

		$default_language_id = $this->getDefaultLanguageId();

		$j                   = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(10);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(30);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(30);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(10);

		// The heading row and column styles
		$styles              = [];
		$data = [];
		$i            = 1;
		$j            = 0;
		$MaxProductId = $this->getMaxProductId();

		$data[$j++] = $text_lang['category_id'];
		$data[$j++] = $text_lang['category_name'];
		$data[$j++] = $text_lang['last_product_id'];
		$data[$j++] = $MaxProductId + 1;
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		$i += 1;
		$j          = 0;


		$categories = $this->getCategories( $languages );

		foreach($categories as $category){
			$data = [];
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$data[$j++] = $category['category_id'];
			$data[$j++] = html_entity_decode(($category['path']?$category['path'].'&nbsp;&nbsp;&gt;&nbsp;&nbsp;':'').$category['name']['en'],ENT_QUOTES,'UTF-8');

			$this->setCellRow( $worksheet, $i, $data );
			$i += 1;
			$j = 0;


		}



		$this->setColumnStyles( $worksheet, $styles, 2, $i - 1 );
	}

	protected function populateReferenceDataWorksheet( & $worksheet,$languages, & $box_format, & $text_format, $text_lang = array(), $offset = null, $rows = null, & $min_id = null, & $max_id = null)
	{

		$language_id = $this->getDefaultLanguageId();

		$j           = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(15);


		// The heading row and column styles
		$styles      = [];

		$rPage = [];

		$r            = 0;
		$c            = 0;
		$MaxProductId = $this->getMaxProductId();
		$rPage[0] = [];
		$rPage[0][$c++] = $text_lang['last_product_id'];
		$rPage[1] = [];
		$rPage[1][0] = $MaxProductId;

		$maxRow = 1;

		$sql    = "SELECT od.option_id as optionId, o.type as optionType, od.name AS optionName FROM `".DB_PREFIX."option_description` od LEFT JOIN `".DB_PREFIX."option` o ON o.option_id = od.option_id WHERE o.store_id='".(int)$this->session->data['store_id'] ."' AND od.language_id='".(int)$language_id."' ORDER BY od.name";
		$query = $this->db->query( $sql );



		foreach($query->rows as $row){
			switch($row['optionType'])
			{
				case 'radio':
				case 'select':
				if($row['optionName'] != 'Radio' && $row['optionName'] != 'Select'&& $row['optionName'] != 'Length')
				{
					//$this->log->write('optionName:'.$row['optionName']);
					$col_num = $c++;
					$row_num = 1;

					$rPage[0][$col_num] = htmlspecialchars_decode($row['optionName']);
					$worksheet->getColumnDimensionByColumn($col_num)->setWidth(strlen($row['optionName']) + 4);

					$sql = "SELECT name FROM `".DB_PREFIX."option_value_description` ";
					$sql .= "WHERE option_id ='".(int)$row['optionId']."' AND language_id='".(int)$language_id."'";

					$tquery   = $this->db->query( $sql );
					//$this->log->write('ARRAY:'.print_r($tquery ,true));
					$rowcount = 0;
					foreach($tquery->rows as $res){
						$rowcount++;
						$nrow = $row_num++;
						if(!isset($rPage[$nrow])) $rPage[$nrow] = [];
						$rPage[$nrow][$col_num] = htmlspecialchars_decode($res['name']);
					}
					if($maxRow < $rowcount)$maxRow = $rowcount;
				}
				break;
				default:
			}

		}



		$ctypes = ['','Jackets & Coats','Hats & Gloves','Bags','Accessories'];
		$sql = "SELECT cp.category_id AS category_id,c1.ctype AS ctype1,c2.ctype AS ctype2, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' > ') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (cp.category_id = c2s.category_id) WHERE cd1.language_id = '" . (int)$language_id . "' AND cd2.language_id = '" . (int)$language_id . "'  AND c2s.store_id='".(int)$this->session->data['store_id']."' GROUP BY cp.category_id ORDER BY cd1.name,cd2.name,c2.parent_id  ASC";

		$categories = $this->db->query( $sql );

		foreach($ctypes as $key =>$ctype)
		{

			if($key)
			{
				$col_num = $c++;
				$row_num = 1;

				$rPage[0][$col_num] = $ctype;
				$worksheet->getColumnDimensionByColumn($col_num)->setWidth(80);

				$rowcount = 0;
				foreach($categories->rows as $category){
					if((int)$category['ctype1'] == (int)$key)
					{

						$rowcount++;
						$nrow = $row_num++;
						if(!isset($rPage[$nrow])) $rPage[$nrow] = [];
						$rPage[$nrow][$col_num] = html_entity_decode($category['name'],ENT_QUOTES,'UTF-8');
					}
				}
				if($maxRow < $rowcount)$maxRow = $rowcount;
			}
		}

		$worksheet->getRowDimension(1)->setRowHeight(50);

		$data = [];
		for($j = 0; $j <= $col_num; $j++)
		{

			$data[$j] = isset($rPage[0][$j])?$rPage[0][$j]:'';
		}

		$this->setCellRow( $worksheet, 1, $data, $box_format );

		//$this->log->write('ARRAY:'.print_r( $rPage,true));

		for($i = 1; $i <= $maxRow; $i++)
		{
			$data = [];
			for($j = 0; $j <= $col_num; $j++)
			{
				$data[$j] = isset($rPage[$i][$j])?$rPage[$i][$j]:'';
			}

			$worksheet->getRowDimension($i + 1)->setRowHeight(30);
			$this->setCellRow( $worksheet, $i + 1, $data );
		}


		$this->setColumnStyles( $worksheet, $styles, 2, $i - 1 );
	}

	protected function populateCustomerDataWorksheet( & $worksheet,$dataN, & $box_format, & $text_format, $offset = null, $rows = null, & $min_id = null, & $max_id = null )
	{
		if(isset($dataN['customers'][0])){
			$j = 0;
			foreach($dataN['customers'][0] as $key=> $value){
				$worksheet->getColumnDimensionByColumn($j++)->setWidth(50);
			}
		}


		// The heading row and column styles
		$styles = array(

			'alignment' => array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => false
			)
		);
		$data = [];


		if(isset($dataN['customers'][0])){
			$i = 1;
			$j = 0;
			foreach($dataN['customers'][0] as $key=> $value){
				$data[$j++] = $key;
			}
		}




		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );

		$i += 1;
		$j       = 0;

		$reverse = true;
		$first_cols_format = array(
			'fill'           => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color'     => array('rgb'=> 'ffff00')
			),

			'alignment' => array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			)
			/**/
		);

		$standart_format = array(
			'fill'           => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color'     => array('rgb'=> 'ffff00')
			),

			'alignment' => array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			)
			/**/
		);

		$reverse_format = array(
			'fill'           => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color'     => array('rgb'=> 'EFEFEF')
			),

			'alignment' => array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => true,
				'indent'    => 0
			)
			/**/
		);
		if(isset($dataN['customers'])){
			foreach($dataN['customers'] as $customer )
			{

				foreach($customer as $key=> $value)
				{


					$pdata[$j++] = $value;
				}

				$this->setCellRow( $worksheet, $i, $pdata );
				$i += 1;
				$j = 0;
			}

			$this->setColumnStyles( $worksheet, $first_cols_format, 2, $i - 1 );
		}
	}

	protected function setColumnStyles( &$worksheet, &$styles, $min_row, $max_row ) {
		if ($max_row < $min_row) {
			return;
		}
		foreach ($styles as $col=>$style) {
			$from = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col).$min_row;
			$to = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col).$max_row;
			$range = $from.':'.$to;
			$worksheet->getStyle( $range )->applyFromArray( $style, false );
		}
	}
	
	protected function setCellRow( $worksheet, $row/*1-based*/, $data, &$default_style=null, &$styles=null ) {
		
		if (!empty($default_style)) {
			$worksheet->getStyle( "$row:$row" )->applyFromArray( $default_style , false );
		}
		if (!empty($styles)) {
			foreach ($styles as $col=>$style) {
				$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray($style,false);
			}
		}
		$worksheet->fromArray( $data, null, 'A'.$row, true );
	}

	protected function setCell( & $worksheet, $row/*1-based*/, $col/*0-based*/, $val, & $style = null )
	{
		$worksheet->setCellValueByColumnAndRow( $col, $row, $val );

		if(!empty($style)){
			$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray( $style, false );
		}
	}
	
	

	protected function clearSpreadsheetCache()
	{
		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');

		if($files){
			foreach($files as $file){
				if(file_exists($file)){
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}

	protected function getDefaultLanguageId()
	{
		$code        = $this->config->get('config_language');

		$sql         = "SELECT language_id FROM `".DB_PREFIX."language` WHERE code = '$code'";
		$result      = $this->db->query( $sql );
		$language_id = 1;
		if($result->rows){
			foreach($result->rows as $row){
				$language_id = $row['language_id'];
				break;
			}
		}

		return $language_id;
	}

	protected function getLanguages()
	{
		$query = $this->db->query( "SELECT * FROM `".DB_PREFIX."language` WHERE `status`=1 ORDER BY `code`" );
		return $query->rows;
	}





	
	
	
	
	
	
	
		/* UPLOAD */
	public function upload( $filename, $check,$incremental = false )
	{
		global $registry;
		$registry = $this->registry;
		
		set_error_handler('\Opencart\Admin\Model\Bytao\error_handler_for_export_import',E_ALL);
		register_shutdown_function('\Opencart\Admin\Model\Bytao\fatal_error_shutdown_handler_for_export_import');

		try
		{
			if (version_compare(phpversion(), '7.2.', '<')) {
				// php version isn't high enough
				throw new \Exception( $this->language->get( 'error_php_version' ) );
			}

			$this->session->data['export_import_nochange'] = 1;

			// enable auto_load from system/library/export_import
			require( DIR_EXTENSION.'export_import/system/library/export_import/vendor/autoload.php' );

			// Use the PhoOffice/PhpSpreadsheet package from https://github.com/PHPOffice/PhpSpreadsheet
			$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

			// parse uploaded spreadsheet file
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
			$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
			
			$this->clearCache();
			$this->session->data['export_import_nochange'] = 0;
			$this->{'upload'.$reader->getSheet(0)->getTitle()}($reader,$check);

			return true;


		} catch(Exception $e){
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['export_import_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return false;
		}

		return TRUE;
	}

	protected function uploadProducts( & $reader,$check)
	{
		$data    = $reader->getSheet(0);
		if($data == null){
			return;
		}

		$first_row 		= [];
		$products 		= [];
		$i              = 0;
		$k              = $data->getHighestRow();
		$MaxProductId   = $this->getMaxProductId();
		$old_product_id = 0;

		// Products
		for($i = 0; $i < $k; $i += 1){
			
			if($i == 0){
				$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $data->getHighestColumn() );
				for($j = 0; $j <= $max_col; $j += 1){
					$first_row[] = $this->getCell($data,$i,$j);
				}
				
				continue;
			}
			$j = 1;
			$product_id = $this->getCell($data,$i,1);
			
			foreach($this->titles as $code => $prodtitle){
				$position = oc_strpos($prodtitle['title'], '(');
				if($position === false){
					if($code=='product_id'){
						if($old_product_id != $product_id)
							{
								$opCount = 0;
							}else{
								if(isset($opCount)){
									$opCount++;
								}else{
									$opCount = 0;
								}
							}
					}
					
					$products[$product_id][$code] = $this->getCell($data,$i,$j++);
				}
				else
				{
					$pTitle = explode('(',$prodtitle['title']);
					if(isset($pTitle[1])){
						$_pTitle = explode(')',$pTitle[1]);
						if(isset($_pTitle[1])){
							$language_code = $_pTitle[0];
							$language_code=($language_code=='en')?'en-gb':$language_code;
							$products[$product_id][$code][$language_code] = htmlspecialchars($this->getCell($data,$i,$j++));
						}
					}
				}
			}

			$old_product_id = $product_id;

		}

		$languages            = $this->getLanguages();
		$option_ids           = $this->getOptionIds();
		$option_value_ids     = $this->getOptionValueIds();
		$option_seo_value_ids = $this->getOptionValueIds(true);
		
		
		
		$this->inToDb($products,$check);
		
		/*
		switch($upType)
		{
			// Tüm veriler
			case 0:$this->productToDb($products);break;
			
			//STOK & Fiyat
			case 1: $this->productPriceStockToDb($products);break;
			
			//SEO keyword
			case 2:$this->productUrlToDb($products);break;
			
			//CATEGORIES
			case 3:$this->productCategoryToDb($products);break;
			
			//IMAGES
			case 4: $this->productImageToDb($products);break;
				
			// Status
			case 5: $this->productStatusToDb($products); break;
			
			//DESCRIPTION
			case 6:$this->productDescriptionToDb($products);break;
			
			//Material	
			case 7: $this->productMaterialToDb($products);break;
			
			//Sale price	
			case 9: $this->productSalePriceToDb($products);break;
			
			//all prices	
			case 10: $this->productPricesToDb($products);break;
			
			
			
			case 8://DESCRIPTION
				{
					foreach($products as $product_id=>$product){

						$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
						foreach($languages AS $language)
						{
							$dsql = "INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language['language_id']. "'";

							$dsql .= isset($product['name'][$language['code']])? ", name = '". $this->db->escape($product['name'][$language['code']]) . "'":"', name = ''";
							$description = isset($product['paragraph1'][$language['code']])?$product['paragraph1'][$language['code']]:"";
							$description .= isset($product['paragraph2'][$language['code']])?":::".$product['paragraph2'][$language['code']]:"::: ";
							$description .= isset($product['bullet'][$language['code']])?":::".$product['bullet'][$language['code']]:"::: ";
							
							$dsql .= ", description = '" . $this->db->escape($description) . "'";
							$dsql .= isset($product['meta_keyword'][$language['code']])?", meta_keyword = '" . $this->db->escape($product['meta_keyword'][$language['code']]) . "'":", meta_keyword = '' ";
							
							$dsql .= isset($product['meta_keyword'][$language['code']])?", tag = '" . $this->db->escape($product['meta_keyword'][$language['code']]) . "'":", tag = '' ";
							
							$dsql .= isset($product['name'][$language['code']])?", meta_title = '". $this->db->escape($product['name'][$language['code']]) . "'":", meta_title = ''";

							$dsql .= isset($product['paragraph1'][$language['code']])?", meta_description = '" .$this->session->data['store_name_long']." ". $this->db->escape($product['paragraph1'][$language['code']]) . "'":", meta_description = ''";
							
							//$dsql .= isset($product['paragraph1'][$language['code']])?', meta_description = "' . $this->db->escape($product['paragraph1'][$language['code']]) . '"':', meta_description = ""';
							
							$this->db->query($dsql);
						}
					}
				}
				break;
			default:

		}
		*/
	}
	
	protected function inToDb(array $IDS=[],array $checks=[]){
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$languages = $this->getLanguages();
		$this->setProdTitles();
		
		$this->load->model('catalog/product');
		
		foreach($IDS as $rec_id => $ROW){
			
			$rec_info = $this->model_catalog_product->getProduct($rec_id);
			
			if($rec_info){
				foreach($checks as $check){
					switch($this->$this->titles[$check]['veri']){
						case 'int':
							$content = (int)$ROW[$this->titles[$check]['code']];
							break;
						case 'float':
							$content = (float)$ROW[$this->titles[$check]['code']];
							break;
						case 'text':
							$content = $this->db->escape($ROW[$this->titles[$check]['code']]);
						break;
						
					}
					
					switch($this->$this->titles[$check]['code']){
						case 'categories':
							{
								if($ROW['categories']){
									$_categories = explode(',',$ROW['categories']);
									$cats        = [];
									foreach($_categories AS $_category){
										$cats[] = $this->getCategoryId($_category);
									}
									$ROW['categories'] = implode(',',$cats);
								}

								$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '0' WHERE product_id='".(int)$rec_id."'");

								if($ROW['categories']){
									$categories = explode(',',$ROW['categories']);
									foreach($categories as $category_id){
										$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$rec_id."'");

										if($query_ptc->num_rows > 0){
											$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '1' WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$rec_id."'");
										}
										else
										{
											$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$rec_id . "', category_id = '" . (int)$category_id . "', updated='1'");
										}

									}
									$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$rec_id."' GROUP BY category_id");

									foreach($query_ptc->rows AS $category)
									{
										if(!in_array($category['category_id'], $categories))
										{
											$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$rec_id."' and category_id='".$category['category_id']."'");

										}
									}
								}
							}
							break;
						case 'image':
							
						
							break;
						case 'color':// color group
							{
								$optionIds = [];
								$total_quantity = 0;
								$this_shearling = false;
								$this_leather = false;
								$this_goat = false;
								$konum = strpos(strtolower(trim($ROW['color'])), 'shearling');
								if($konum !== false)
								{
									$this_shearling = true;
								}

								$konum = strpos(strtolower(trim($ROW['color'])), 'leather');
								if($konum !== false)
								{
									$this_leather = true;
								}

								$konum = strpos(strtolower(trim($ROW['color'])), 'goat');
								if($konum !== false)
								{
									$this_goat = true;
								}

								$option_id = $option_ids[by_SEO($ROW['color'])];
								$optionIds[] = $option_id;
								$option_values      = $option_value_ids[$option_id];
								$product_option_ids = $this->getProductOptionIds( $product_id );
							}
							break;
						case 'colors':// colors
							{
								$option_id = $option_ids[by_SEO($ROW['color'])];
								$optionIds[] = $option_id;
								$option_values      = $option_value_ids[$option_id];
								$product_option_ids = $this->getProductOptionIds( $rec_id );

								//Renkler
								$product_option_id  = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
								if($product_option_id == 0 ){
									// new option
									$sql               = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($rec_id,$option_id,'',1)";
									$this->db->query($sql);
									$product_option_id = $this->db->getLastId();
								}

								if($ROW['colors']){
									$_colors = [];

									$colors   = explode('-',$ROW['colors']);
									$osort    = 0;
									$quantity = 1;
									$total_quantity += $quantity;

									$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE  product_id='".(int)$rec_id."' AND  option_id ='".$option_id."' AND product_option_id = '".$product_option_id."'");

									foreach($colors as $ocolor){
										if(isset($option_values[by_SEO($ocolor)])){
											$option_value_id = $option_values[by_SEO($ocolor)];
										}
										else
										{
											$option_value_id = 0;
										}

										$type      = 1;
										$parent_id = 0;
										$_colors[] = $option_value_id;
										$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$rec_id','$option_id','$option_value_id','$parent_id','$quantity','0','1','0','+','0','+','0','+','$osort')";
										$this->db->query($sql);
										$osort++;
									}
								}	
							}
							break;
						case 'sizes':// Sizes
							{
								if($ROW['sizes']){
									$option_id = $option_ids['Size'];
									$optionIds[] = $option_id;
									$option_values     = $option_value_ids[$option_id];
									$product_option_id = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
									if($product_option_id == 0 ){
										// new option
										$sql= "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($rec_id,$option_id,'',1)";
										$this->db->query($sql);
										$product_option_id = $this->db->getLastId();
									}

									$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$rec_id."' AND  option_id ='".$option_id."'");

									$_sizes = [];
									$sizes = explode(' - ',$ROW['sizes']);
									$osort = 0;
									foreach($sizes as $size){
										$option_value_id = $option_values[by_SEO($size)];
										$type            = 0;
										$parent_id       = 0;
										$quantity        = 1;
										$total_quantity += $quantity;
										$_sizes[] = $option_value_id;
										
										if(($ROW['price']>$ROW['sale_price'])&& $ROW['sale_price']!=0){
											$nPrice =$ROW['sale_price'];
										}else{
											$nPrice =$ROW['price'];
										}
										if($nPrice>0 && $nPrice<500 ){
											switch($size){
												case '3XL': $price = 35;break;
												case '4XL':	$price = 50;break;
												default:$price = 0;
											}
										}else if($nPrice>500 && $nPrice<1000 ){
											switch($size){
												case '3XL': $price = 70;break;
												case '4XL':	$price = 100;break;
												default:$price = 0;
											}
										}else if($nPrice>1000 && $nPrice<1500 ){
											switch($size){
												case '3XL': $price = 100;break;
												case '4XL':	$price = 150;break;
												default:$price = 0;
											}
										}else if($nPrice>1500 && $nPrice<2000 ){
											switch($size){
												case '3XL': $price = 125;break;
												case '4XL':	$price = 200;break;
												default:$price = 0;
											}
										}else{
											switch($size){
												case '3XL': $price = 150;break;
												case '4XL':	$price = 250;break;
												default:$price = 0;
											}
										}
										
										$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$rec_id','$option_id','$option_value_id','$parent_id','$quantity','$type','0','$price','+','0','+','0','+','$osort')";
										$osort++;
										$this->db->query($sql);
									}

									$query_pov = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$rec_id."' AND  option_id ='".$option_id."' ");
									foreach($query_pov->rows AS $poption){
										if(!in_array($poption['option_value_id'], $_sizes)){
											$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id='".(int)$poption['option_value_id']."'");
										}
									}
								}

									foreach($product_option_ids AS $key => $value){
										if(!in_array($key, $optionIds)){
											$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_id='".(int)$value."' AND product_id='".(int)$rec_id."'");
											$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE  product_id='".(int)$rec_id."' AND product_option_id='".$value ."'");
										}
									}
							}
							break;
							
						default:
							
							$this->db->query("UPDATE `".DB_PREFIX."product` SET ".$this->titles[$check]['code']."='".$content."' WHERE product_id='$rec_id'");
					
					}
				}
			}else{
				$rec_info = $this->model_catalog_product->getProduct($rec_id);
			}
		}	
	}
	
	protected function productToDb(array $IDS=[]){
		$store_id = (int)$this->session->data['store_id'];
		foreach($products as $product_id => $product){
			$category_ids = '';
			if($product['categories'])
			{
				$_categories = explode('>',$product['categories']);
				$cats        = [];
				$parent = 0;
				foreach($_categories AS $_category)
				{
					$categoryX = explode(',',$_category);
					if(isset($categoryX[1]))
					{
						$cats[] = $this->getCategoryIdV2(trim($categoryX[0]));
						$cats[] = $this->getCategoryIdV2(trim($categoryX[1]));
					}
					else
					{
						$parent = $cats[] = $this->getCategoryIdV2(trim($categoryX[0]));
					}

				}
				$category_ids = implode(',',$cats);
			}

			if(substr(trim(strtolower($product_id)),0,1) != '*' && trim($product_id) != '' ){
				$qSql ='';
				//new
				if($this->isNew($product_id)){
					$qSql = "INSERT INTO " . DB_PREFIX . "product SET product_id = '$product_id', model = '" . $this->db->escape($product['model']) . "',";
					$qSql .= isset($product['location'])?"location = '" . $this->db->escape($product['location']) . "', ":"";
					$qSql .= isset($product['minimum'])?"minimum = '" . (int)$product['minimum'] . "', ":"minimum = '1', ";
					$qSql .= isset($product['subtract'])?"subtract = '" . (int)$product['subtract']  . "', ":"subtract = 0, ";
					$qSql .= "stock_status_id = '6', ";
					$qSql .= isset($product['date_available'])?"date_available = '" . $this->db->escape($product['date_available']) . "', ":"";
					$qSql .= isset($product['manufacturer_id'])?"manufacturer_id = '" . (int)$product['manufacturer_id']  . "', ":"";
					$qSql .= "shipping = '1', ";
					$qSql .= isset($product['price'])?"price = '" . (float)$product['price'] . "', ":"";
					$qSql .= isset($product['cost_price'])?"cost_price = '" . (float)$product['cost_price']  . "', ":"";
					$qSql .= isset($product['sale_price'])?"sale_price = '" . (float)$product['sale_price']  . "', ":"";
					$qSql .= isset($product['our_price'])?"our_price = '" . (float)$product['our_price']  . "', ":"";
					$qSql .= isset($product['whole_sale_price'])?"whole_sale_price = '" . (float)$product['whole_sale_price']  . "', ":"";
					$qSql .= isset($product['points'])?"points = '" . (int)$product['points']  . "', ":"";
					$qSql .= isset($product['weight'])?"weight = '" . (float)$product['weight']  . "', ":"";
					$qSql .= "weight_class_id = '1', ";
					$qSql .= isset($product['length'])?"length = '" . (float)$product['length']  . "', ":"";
					$qSql .= "length_class_id = '1', ";
					$qSql .= isset($product['width'])?"width = '" . (float)$product['width']  . "', ":"";
					$qSql .= isset($product['height'])?"height = '" . (float)$product['height']  . "', ":"";
					$qSql .= isset($product['status'])?"status = '" . (int)$product['status']  . "', ":"status = true, ";
					$qSql .= isset($product['best'])?"best = '" . (int)$product['best']  . "', ":"";
					$qSql .= isset($product['outlink'])?"outlink = '" . (int)$product['outlink']  . "', ":"";

					$qSql .= isset($product['mpage'])?"mpage = '" . (int)$product['mpage']  . "', ":"";

					$qSql .= "tax_class_id = '9', ";
					$qSql .= isset($product['sort_order'])?"sort_order = '" . (int)$product['sort_order']  . "', ":"";
					$qSql .= isset($product['msize'])?"msize = '" . (int)$product['msize']  . "', ":"";
					$qSql .= isset($product['mbody'])?"mbody = '" . (int)$product['mbody']  . "', ":"";
					$qSql .= isset($product['material'])?"material='".$this->db->escape($product['material'])."', ":"";
					$qSql .= " date_added = NOW() ;";
					$this->db->query($qSql);
		
					foreach($languages AS $language){
						$dsql = 'INSERT INTO ' . DB_PREFIX . 'product_description SET product_id = "' . (int)$product_id . '", language_id = "' . (int)$language['language_id']. '"';
						$dsql .= isset($product['name'][$language['code']])? ', name = "'. $this->db->escape($product['name'][$language['code']]) . '"':', name = ""';
						$dsql .= isset($product['description'][$language['code']])?', description = "' . $this->db->escape($product['description'][$language['code']]) . '"':', description = ""';
						$dsql .= isset($product['description_alt'][$language['code']])?', description_alt = "' . $this->db->escape($product['description_alt'][$language['code']]) . '"':', description_alt = ""';
						$dsql .= isset($product['bullet'][$language['code']])?', bullet = "' . $this->db->escape($product['bullet'][$language['code']]) . '"':', bullet = ""';
						$dsql .= isset($product['meta_keyword'][$language['code']])?', meta_keyword = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', meta_keyword = ""';
						$dsql .= isset($product['meta_keyword'][$language['code']])?', tag = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', tag = ""';
						$dsql .= isset($product['name'][$language['code']])?', meta_title = "' . $this->db->escape($product['name'][$language['code']]) . '"':', meta_title = ""';
						$dsql .= isset($product['meta_description'][$language['code']])?', meta_description = "' .$this->db->escape($product['meta_description'][$language['code']]) . '"':', meta_description = ""';
						$this->db->query($dsql);
					}
					
					//SEO URL
					foreach ($languages as $language) {
						$keywords = isset($product['meta_title'][$language['code']])?$product['meta_title'][$language['code']]:'';
						$language_id = $language['language_id'];
						$language_code = $language['code'];
						$keyword = by_SEO($keyword);
						if($keywords != ''){
							$sql  = "INSERT INTO `".DB_PREFIX."seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES ('$store_id', '$language_id', 'product_id', '$product_id', '".$this->db->escape($keyword)."');";
							$this->db->query( $sql );
						}
					}


					//Kategoriler
					$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '0' WHERE product_id='".(int)$product_id."'");
									
					if($category_ids != ''){
						$categories = explode(',',$category_ids);
						foreach($categories as $category_id){
							$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");
							if($query_ptc->num_rows > 0){
								$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '1' WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");
							}
							else
							{
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "', updated='1'");
							}
						}
									
						$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' GROUP BY category_id");
						foreach($query_ptc->rows AS $category){
							if(!in_array($category['category_id'], $categories)){
								$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' and category_id='".$category['category_id']."'");

							}
						}
					}

					$optionIds = [];
					$total_quantity = 0;
					$this_shearling = false;
					$this_leather = false;
					$this_goat = false;
					$konum = strpos(strtolower(trim($product['color'])), 'shearling');
					if($konum !== false)
					{
						$this_shearling = true;

					}

					$konum = strpos(strtolower(trim($product['color'])), 'leather');
					if($konum !== false)
					{
						$this_leather = true;

					}

					$konum = strpos(strtolower(trim($product['color'])), 'goat');
					if($konum !== false)
					{
						$this_goat = true;

					}

					$option_id = $option_ids[by_SEO($product['color'])];
					$optionIds[] = $option_id;
					$option_values      = $option_value_ids[$option_id];
					$product_option_ids = $this->getProductOptionIds( $product_id );

					//Renkler
					$product_option_id  = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
					if($product_option_id == 0 ){
						// new option
						$sql               = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$option_id,'',1)";
						$this->db->query($sql);
						$product_option_id = $this->db->getLastId();
					}

					if($product['colors']){
						$_colors = [];

						$colors   = explode('-',$product['colors']);
						$osort    = 0;
						$quantity = 1;
						$total_quantity += $quantity;

						$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE  product_id='".(int)$product_id."' AND  option_id ='".$option_id."' AND product_option_id = '".$product_option_id."'");

						foreach($colors as $ocolor){
							if(isset($option_values[by_SEO($ocolor)])){
								$option_value_id = $option_values[by_SEO($ocolor)];
							}
							else
							{
								$option_value_id = 0;
							}

							$type      = 1;
							$parent_id = 0;
							$_colors[] = $option_value_id;
							$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$option_id','$option_value_id','$parent_id','$quantity','0','1','0','+','0','+','0','+','$osort')";
							$this->db->query($sql);
							$osort++;
						}
					}

					// sizelar
					if($product['sizes'])																																																{
					$option_id = $option_ids['Size'];
					$optionIds[] = $option_id;
					$option_values     = $option_value_ids[$option_id];
					$product_option_id = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
					if($product_option_id == 0 ){
						// new option
						$sql               = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$option_id,'',1)";
						$this->db->query($sql);
						$product_option_id = $this->db->getLastId();
					}

					$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$product_id."' AND  option_id ='".$option_id."'");

					$_sizes = [];
					$sizes = explode(' - ',$product['sizes']);
					$osort = 0;
					foreach($sizes as $size){
						$option_value_id = $option_values[by_SEO($size)];
						$type            = 0;
						$parent_id       = 0;
						$quantity        = 1;
						$total_quantity += $quantity;
						$_sizes[] = $option_value_id;
						switch($size){
							case '3XL':
							case 'XXXL':
								if($this_shearling) $price = 100;
								elseif($this_leather) $price = 75;
								else $price = 0;
								break;
							case '4XL':
							case 'XXXXL':
								if($this_shearling) $price = 200;
								elseif($this_leather) $price = 150;
								else $price = 200;
								break;
							default:
								$price = 0;
						}
						$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$option_id','$option_value_id','$parent_id','$quantity','$type','0','$price','+','0','+','0','+','$osort')";
						$osort++;
						$this->db->query($sql);
					}

					$query_pov = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$product_id."' AND  option_id ='".$option_id."' ");
					foreach($query_pov->rows AS $poption){
						if(!in_array($poption['option_value_id'], $_sizes)){
							$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id='".(int)$poption['option_value_id']."'");
						}
					}
				}

					foreach($product_option_ids AS $key => $value){
						if(!in_array($key, $optionIds)){
							$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_id='".(int)$value."' AND product_id='".(int)$product_id."'");
							$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE  product_id='".(int)$product_id."' AND product_option_id='".$value ."'");
						}
					}

					//images
					$this->db->query( "DELETE FROM `".DB_PREFIX."product_image` WHERE product_id='".(int)$product_id."'");

					$product_image_path = DIR_IMPORT_IMG.strtolower($product['model']);
					$directories        = glob($product_image_path . '/*', GLOB_ONLYDIR);
					if(!$directories){
						$directories = [];
					}

					$option_id     = $option_ids[by_SEO($product['color'])];
					$option_values = $option_seo_value_ids[$option_id];
					$firstImage    = '';
					$dev           = true;
					$_colors = [];
					$colors = explode('-',$product['colors']);
					foreach($colors as $_directory)																																																																					{
					$sort_files = [];
					$dcolor = by_SEO($_directory);
					if(isset($option_values[by_SEO($dcolor)])){
						$option_value_id = $option_values[by_SEO($dcolor)];
					}
					else
					{
						$option_value_id = 0;
					}

					$first = true;
					if($option_value_id){
						$path   = '';
						$files  = glob($product_image_path . '/' .$dcolor .'/'  . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);
						$fcount = 0;
						if(count($files) > 0){
							foreach($files as $file){
								$_file    = explode('/',$file);
								$filename = end($_file);
								$_filename= explode('.',$filename);
								$sort     = explode('-',$_filename[0]);
								$_sort    = (int)end($sort);
								if($_sort == "01"){
									$sort_files[0] = $filename;
									if($dev){
										$firstImage = 'catalog/products/'.strtolower($product['model']).'/'.$dcolor.'/'.$filename;
										$dev        = false;
										$first = false;
									}
									$sort_order = 0;
								}
								else
								{
									if($first && $firstImage == '' ){
										$firstImage = 'catalog/products/'.strtolower($product['model']).'/'.$dcolor.'/'.$filename;
										$first      = false;
									}
									$fcount++;
									$sort_files[$fcount] = $filename;
									$sort_order = $fcount;
								}
								$imFile = $product_image_path . '/' .$dcolor .'/'.$filename;
								$newPath= DIR_IMAGE .'catalog/products/'.strtolower($product['model']).'/'.$dcolor;
								$newFile= $newPath.'/'.$filename;
								if(!is_dir($newPath)){
									$paths = explode('/', 'catalog/products/'.strtolower($product['model']).'/'.$dcolor);
									$path  = '';
									foreach($paths as $value){
										if($path == ''){
											$path = $value;
										}
										else
										{
											$path = $path . '/' . $value;
										}
										if(!is_dir(DIR_IMAGE . $path)){
											@mkdir(DIR_IMAGE . $path, 0777);
										}
									}
								}
								if(!copy($imFile,$newFile )){
									$this->log->write("failed to copy $file...");
								}
								$sql = "INSERT INTO `".DB_PREFIX."product_image` (`product_id`,`image`,`sort_order`,`color_id`,`class` ) VALUES ($product_id,'".$this->db->escape('catalog/products/'.strtolower($product['model']).'/'.$dcolor .'/'.$filename)."','".(int)$sort_order."','".$this->db->escape($option_value_id)."','')";
								$this->db->query($sql);
							}
							$dev = false;
						}
					}
				}
					if(!isset($this->session->data['store_id'])){
						$this->session->data['store_id'] = 0;
					}
					$this->db->query("INSERT INTO `".DB_PREFIX."product_to_store` (`product_id`,`store_id`) VALUES('".(int)$product_id."','".(int)$this->session->data['store_id']."');");

					$qSql = "UPDATE `".DB_PREFIX."product` SET quantity='$total_quantity', image='$firstImage'";
					$qSql .= isset($product['size_chart_id'])?"size_chart_id = '" . (int)$product['size_chart_id']  . "', ":"";
					$qSql .= isset($product['material_id'])?"material_id = '" . (int)$product['material_id']  . "', ":"";
					$qSql .= isset($product['productcare_id'])?"productcare_id = '" . (int)$product['productcare_id']  . "', ":"";
					$qSql .= isset($product['measurement_id'])?"measurement_id = '" . (int)$product['measurement_id']  . "', ":"";
					$qSql .= " WHERE product_id='$product_id'";
					$this->db->query($qSql);
				}
				else // Update
				{
					// Aciklamalar
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
					foreach($languages AS $language){
						$dsql = 'INSERT INTO ' . DB_PREFIX . 'product_description SET product_id = "' . (int)$product_id . '", language_id = "' . (int)$language['language_id']. '"';
						$dsql .= isset($product['name'][$language['code']])? ', name = "'. $this->db->escape($product['name'][$language['code']]) . '"':', name = ""';
						$dsql .= isset($product['description'][$language['code']])?', description = "' . $this->db->escape($product['description'][$language['code']]) . '"':', description = ""';
						$dsql .= isset($product['description_alt'][$language['code']])?', description_alt = "' . $this->db->escape($product['description_alt'][$language['code']]) . '"':', description_alt = ""';
						$dsql .= isset($product['bullet'][$language['code']])?', bullet = "' . $this->db->escape($product['bullet'][$language['code']]) . '"':', bullet = ""';
						$dsql .= isset($product['meta_keyword'][$language['code']])?', meta_keyword = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', meta_keyword = ""';
						$dsql .= isset($product['meta_keyword'][$language['code']])?', tag = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', tag = ""';
						$dsql .= isset($product['meta_title'][$language['code']])?', meta_title = "' . $this->db->escape($product['meta_title'][$language['code']]) . '"':', meta_title = ""';
						$dsql .= isset($product['meta_description'][$language['code']])?', meta_description = "' .$this->db->escape($product['meta_description'][$language['code']]) . '"':', meta_description = ""';
						$this->db->query($dsql);
					}

					//SEO URL
					$sql = "DELETE FROM `".DB_PREFIX."seo_url` WHERE `key`='product_id' AND `key`='".(int)$product_id. "' ;";
					$this->db->query( $sql );
					
					
					foreach ($languages as $language) {
						$keywords = isset($product['meta_title'][$language['code']])?$product['meta_title'][$language['code']]:'';
						$language_id = $language['language_id'];
						$language_code = $language['code'];
						$keyword = by_SEO($keyword);
						if($keywords != ''){
							$sql  = "INSERT INTO `".DB_PREFIX."seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES ('$store_id', '$language_id', 'product_id', '$product_id', '".$this->db->escape($keyword)."');";
							$this->db->query( $sql );
						}
					}
					
					//Kategoriler
					$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '0' WHERE product_id='".(int)$product_id."'");
					if($category_ids){
						$categories = explode(',',$category_ids);
						foreach($categories as $category_id){
							$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");
							if($query_ptc->num_rows > 0){
								$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '1' WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");
							}
							else
							{
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "', updated='1'");
							}
						}
						$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' GROUP BY category_id");
						foreach($query_ptc->rows AS $category){
							if(!in_array($category['category_id'], $categories)){
								$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' and category_id='".$category['category_id']."'");
							}
						}
					}

					$optionIds = [];
					$total_quantity = 0;
					$this_shearling = false;
					$this_leather = false;
					$this_goat = false;
					
					$konum = strpos(strtolower(trim($product['color'])), 'shearling');
					if($konum !== false){
						$this_shearling = true;
					}
					
					$konum = strpos(strtolower(trim($product['color'])), 'leather');
					if($konum !== false){
						$this_leather = true;
					}
					
					$konum = strpos(strtolower(trim($product['color'])), 'goat');
					if($konum !== false){
						$this_goat = true;
					}
					
					$option_id = $option_ids[by_SEO($product['color'])];
					$optionIds[] = $option_id;
					$option_values      = $option_value_ids[$option_id];
					$product_option_ids = $this->getProductOptionIds($product_id );
					//Renkler
					$product_option_id  = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
					if($product_option_id == 0 ){
						// new option
						$sql               = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$option_id,'',1)";
						$this->db->query($sql);
						$product_option_id = $this->db->getLastId();
					}
					if($product['colors']){
						$_colors = [];

						$colors   = explode('-',$product['colors']);

						$osort    = 0;
						$quantity = 10;
						$total_quantity += $quantity;

						$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE  product_id='".(int)$product_id."' AND  option_id ='".$option_id."' AND product_option_id = '".$product_option_id."'");

						foreach($colors as $ocolor){
							if(isset($option_values[by_SEO($ocolor)])){
								$option_value_id = $option_values[by_SEO($ocolor)];
							}
							else
							{
								$option_value_id = 0;
							}
							$type      = 1;
							$parent_id = 0;
							$_colors[] = $option_value_id;
							$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$option_id','$option_value_id','$parent_id','$quantity','0','0','0','+','0','+','0','+','$osort')";
							$this->db->query($sql);
							$osort++;
						}
					}

					// sizelar
					if($product['sizes']){
						$option_id = $option_ids['size'];
						$optionIds[] = $option_id;
						$option_values     = $option_value_ids[$option_id];
						$product_option_id = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
						if($product_option_id == 0 ){
							// new option
							$sql               = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$option_id,'',1)";
							$this->db->query($sql);
							$product_option_id = $this->db->getLastId();
						}

						$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$product_id."' AND  option_id ='".$option_id."'");

						$_sizes = [];
						$sizes = explode('-',$product['sizes']);
						$osort = 0;
						foreach($sizes as $size){
							$option_value_id = $option_values[by_SEO($size)];
							$type            = 0;
							$parent_id       = 0;
							$quantity        = 1;
							$total_quantity += $quantity;
							$_sizes[] = $option_value_id;
							switch($size)
							{
								case '3XL':
								case 'XXXL':
									if($this_shearling) $price = 100;
									elseif($this_leather) $price = 75;
									else $price = 0;

									break;
								case '4XL':
								case 'XXXXL':
									if($this_shearling) $price = 200;
									elseif($this_leather) $price = 150;
									else $price = 0;
									break;
								default:
									$price = 0;
							}
							$sql = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$option_id','$option_value_id','$parent_id','$quantity','$type','0','$price','+','0','+','0','+','$osort')";
							$osort++;
							$this->db->query($sql);
						}
						$query_pov = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_id='".(int)$product_id."' AND  option_id ='".$option_id."' ");
						foreach($query_pov->rows AS $poption){
							if(!in_array($poption['option_value_id'], $_sizes)){
								$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id='".(int)$poption['option_value_id']."'");
							}
						}
					}
					foreach($product_option_ids AS $key => $value){
						if(!in_array($key, $optionIds)){
							$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_id='".(int)$value."' AND product_id='".(int)$product_id."'");
							$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE  product_id='".(int)$product_id."' AND product_option_id='".$value ."'");

						}
					}

					//images
					$this->db->query( "DELETE FROM `".DB_PREFIX."product_image` WHERE product_id='".(int)$product_id."'");
					$product_image_path = DIR_IMPORT_IMG.strtolower(trim($product['model']));
					$directories        = glob($product_image_path . '/*', GLOB_ONLYDIR);
					if(!$directories){
						$directories = [];
					}
					$option_id     = $option_ids[by_SEO($product['color'])];
					$option_values = $option_seo_value_ids[$option_id];
					$firstImage    = '';

					$dev           = true;
					$_colors = [];
					$colors = explode('-',$product['colors']);
					foreach($colors as $_directory){
						$sort_files = [];
						$dcolor = by_SEO($_directory);
						if(isset($option_values[by_SEO($dcolor)])){
							$option_value_id = $option_values[by_SEO($dcolor)];
						}
						else
						{
							$option_value_id = 0;
						}
						$first = true;
						if($option_value_id){
							$path   = '';
							$files  = glob($product_image_path . '/' .$dcolor .'/'  . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);
							$fcount = 0;
							if(count($files) > 0){
								foreach($files as $file){
									$_file    = explode('/',$file);
									$filename = end($_file);
									$_filename= explode('.',$filename);
									$sort     = explode('-',$_filename[0]);
									$_sort    = end($sort);
									//$this->log->write('sort:'.$_sort);

									if($_sort == "01"){
										$sort_files[0] = $filename;
										if($dev){
											$firstImage = 'catalog/products/'.strtolower(trim($product['model'])).'/'.$dcolor.'/'.$filename;
											$dev        = false;
											$first = false;
										}
										$sort_order = 0;
									}
									else
									{
										if($first && $firstImage == '' ){
											$firstImage = 'catalog/products/'.strtolower(trim($product['model'])).'/'.$dcolor.'/'.$filename;
											$first      = false;
										}
										$fcount++;
										$sort_files[$fcount] = $filename;
										$sort_order = $fcount;
									}

									$imFile = $product_image_path . '/' .$dcolor .'/'.$filename;
									$newPath= DIR_IMAGE .'catalog/products/'.strtolower(trim($product['model'])).'/'.$dcolor;
									$newFile= $newPath.'/'.$filename;
									if(!is_dir($newPath)){
										$paths = explode('/', 'catalog/products/'.strtolower(trim($product['model'])).'/'.$dcolor);
										$path  = '';
										foreach($paths as $value){
											if($path == ''){
												$path = $value;
											}
											else
											{
												$path = $path . '/' . $value;
											}
											if(!is_dir(DIR_IMAGE . $path)){
												@mkdir(DIR_IMAGE . $path, 0777);
											}
										}
									}
									if(!copy($imFile,$newFile )){
										$this->log->write("failed to copy $file...");
									}
									$sql = "INSERT INTO `".DB_PREFIX."product_image` (`product_id`,`image`,`sort_order`,`color_id`,`class` ) VALUES ($product_id,'".$this->db->escape('catalog/products/'.strtolower(trim($product['model'])).'/'.$dcolor .'/'.$filename)."','".(int)$sort_order."','".$this->db->escape($option_value_id)."','')";
									$this->db->query($sql);
								}
								$dev = false;
							}
						}
					}
					$qSql = "UPDATE `".DB_PREFIX."product` SET image='$firstImage', model='".$product['model']."', quantity='".$total_quantity."'";
					$qSql .= isset($product['price'])?", price='".(float)$product['price']."'":"";
					$qSql .= isset($product['whole_sale_price'])?", whole_sale_price='".(float)$product['whole_sale_price']."'":"";
					$qSql .= isset($product['status'])?", status='".(int)$product['status']."'":"";
					$qSql .= isset($product['sale_price'])?", sale_price='".(float)$product['sale_price']."' ":"";
					$qSql .= isset($product['material'])?", material='".$this->db->escape($product['material'])."' ":"";
					$qSql .= " WHERE product_id='$product_id'";
					$this->db->query($qSql);
				}
			}
		}
	}
	
	protected function productCategoryToDb(array $IDS=[]){
		foreach($IDS as $product_id=>$product){
			if($product['categories'])
			{
				$_categories = explode(',',$product['categories']);
				$cats        = [];
				foreach($_categories AS $_category)
				{
					$cats[] = $this->getCategoryId($_category);
				}
				$product['categories'] = implode(',',$cats);
			}

			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '0' WHERE product_id='".(int)$product_id."'");

			if($product['categories'])
			{
				$categories = explode(',',$product['categories']);
				foreach($categories as $category_id)
				{

					$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");

					if($query_ptc->num_rows > 0){
						$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET updated = '1' WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");
					}
					else
					{
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "', updated='1'");
					}

				}
				$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' GROUP BY category_id");

				foreach($query_ptc->rows AS $category)
				{
					if(!in_array($category['category_id'], $categories))
					{
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' and category_id='".$category['category_id']."'");

					}
				}
			}


		}
	}
	protected function productStatusToDb(array $IDS=[]){
		foreach($IDS as $product_id=>$product){
			$this->db->query("UPDATE `".DB_PREFIX."product` SET status='".(int)$product['status']."' WHERE product_id='$product_id'");
		}
	}
	protected function productMaterialToDb(array $IDS=[]){
		foreach($IDS as $product_id=>$product){
			$qSql = "UPDATE `".DB_PREFIX."product` SET material='".$product['material']."'";
			$qSql .= " WHERE product_id='$product_id' ";
			$this->db->query($qSql);
		}
	}
	protected function productPricesToDb(array $IDS=[]){
		foreach($IDS as $product_id=>$product){
			$total_quantity = 0;
			$qSql = "UPDATE `".DB_PREFIX."product` SET price='".(float)$product['price']."'";
			$qSql .= isset($product['sale_price'])?", sale_price='".(float)$product['sale_price']."'":"";
			$qSql .= isset($product['whole_sale_price'])?", whole_sale_price='".(float)$product['whole_sale_price']."'":"";
			$qSql .= " WHERE product_id='$product_id' ";
			$this->db->query($qSql);
		}
	}
	protected function productSalePriceToDb(array $IDS=[]){
		foreach($IDS as $product_id=>$product){
			
			$total_quantity = 0;
			$qSql = "UPDATE `".DB_PREFIX."product` SET sale_price='".(float)$product['sale_price']."' WHERE product_id='$product_id' ";
			$this->db->query($qSql);
		}
	}
		
	
	protected function productUrlToDb(array $IDS=[]){
		foreach( $IDS as $product_id => $product){
			$store_id = (int)$this->session->data['store_id'];
			$sql = "DELETE FROM `".DB_PREFIX."seo_url` WHERE `key`='product_id' AND `key`='".(int)$product_id. "' ;";
			$this->db->query( $sql );
			
			$keywords = $product_seo_keyword['keywords'];
			foreach ($languages as $language) {
				$language_id = $language['language_id'];
				$language_code = $language['code'];
				
				if(isset($product['meta_title'][$language['code']])){
					$keyword = by_SEO($product['meta_title'][$language['code']]);
					if ($keyword != '') {
						$sql  = "INSERT INTO `".DB_PREFIX."seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES ('$store_id', '$language_id', 'product_id', '$product_id', '".$this->db->escape($keyword)."');";
					$this->db->query( $sql );
					}
				}
			}
		
		}
	}
	
	protected function productImageToDb(array $IDS = []){
		
		foreach($products as $product_id=>$product){
			$firstImage = '';
			if($product['option']){
				$product_option_ids = $this->getProductOptionIds( $product_id );

				$osort              = [];
				$total_quantity   = 0;
				$old_parent_value = '';
				$sort_order       = $csort_order      = 0;

				$productImages    = $this->getProductAdditionalImages($product_id);
				$this->db->query( "DELETE FROM `".DB_PREFIX."product_image` WHERE product_id='".(int)$product_id."'");

				foreach($product['option'] as $option){

					$option_id         = $option_ids[$option['option']];
					$option_value_id   = $option_value_ids[$option_id][$option['option_value']];
					$product_option_id = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;

					if($option['images']){
						$image_sort_order = 0;
						$oImages          = explode(',',$option['images']);
						foreach($oImages AS $oImage ){
							$image_name = $oImage;
							$image_name = str_replace('//image/catalog//products/','/',$image_name);

							$imgName    = parse_url($image_name);
							$pathN      = $imgName['path'];
							$pathN      = str_replace('/image/catalog/products/','',$pathN);


							if($firstImage == '') $firstImage = 'catalog/products/'.$pathN;

							$path       = '';
							$directories= explode('/', dirname(str_replace('../', '',  'catalog/products/'.$pathN )));
							foreach($directories as $directory){
								if($path == ''){
									$path = $directory;
								}
								else
								{
									$path = $path . '/' . $directory;
								}
								if(!is_dir(DIR_IMAGE . $path)){
									@mkdir(DIR_IMAGE . $path, 0777);
								}
							}

							$resim = file_get_contents($image_name);
							if($resim){
								if(!is_file(DIR_IMAGE .'catalog/products/'.$pathN ))
								file_put_contents(DIR_IMAGE .'catalog/products/'.$pathN ,$resim);
							}

							$newPath = 'catalog/products/'.$pathN;

							if(isset($productImages[$newPath])){
								$product_image_id = $productImages['catalog/products/'.$pathN];
								$sql              = "INSERT INTO `".DB_PREFIX."product_image` (`product_image_id`,`product_id`,`image`,`sort_order`,`color_id`,`class` ) VALUES ($product_image_id,$product_id,'".$this->db->escape($newPath)."','$image_sort_order','".$this->db->escape($option_value_id)."','')";
								$this->db->query($sql);
							}
							else
							{
								$sql = "INSERT INTO `".DB_PREFIX."product_image` (`product_id`,`image`,`sort_order`,`color_id`,`class` ) VALUES ($product_id,'".$this->db->escape($newPath)."',$sort_order,'".$this->db->escape($option_value_id)."','')";
								$this->db->query($sql);
							}
							$image_sort_order++;
						}
					}
				}
			}
			$qSql = "UPDATE `".DB_PREFIX."product` SET image='$firstImage' WHERE product_id='$product_id'";

			$this->db->query($qSql);
		}
	}
	
	protected function productDescriptionToDb(array $IDS=[]){
		foreach($IDS as $product_id => $product){
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

			foreach($languages AS $language)
			{
				$dsql = 'INSERT INTO ' . DB_PREFIX . 'product_description SET product_id = "' . (int)$product_id . '", language_id = "' . (int)$language['language_id']. '"';

				$dsql .= isset($product['name'][$language['code']])? ', name = "'. $this->db->escape($product['name'][$language['code']]) . '"':', name = ""';

				$dsql .= isset($product['description'][$language['code']])?', description = "' . $this->db->escape($product['description'][$language['code']]) . '"':', description = ""';
				$dsql .= isset($product['description_alt'][$language['code']])?', description_alt = "' . $this->db->escape($product['description_alt'][$language['code']]) . '"':', description_alt = ""';
				
				$dsql .= isset($product['bullet'][$language['code']])?', bullet = "' . $this->db->escape($product['bullet'][$language['code']]) . '"':', bullet = ""';
				
				$dsql .= isset($product['meta_keyword'][$language['code']])?', meta_keyword = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', meta_keyword = ""';
				$dsql .= isset($product['meta_keyword'][$language['code']])?', tag = "' . $this->db->escape($product['meta_keyword'][$language['code']]) . '"':', tag = ""';
				$dsql .= isset($product['name'][$language['code']])?', meta_title = "' .$this->session->data['store_name_long'].' | '. $this->db->escape($product['name'][$language['code']]) . '"':', meta_title = ""';

				$dsql .= isset($product['meta_description'][$language['code']])?', meta_description = "' .$this->session->data['store_name_long'].' '. $this->db->escape($product['meta_description'][$language['code']]) . '"':', meta_description = ""';

				$this->db->query($dsql);
			}
		}
	}
	
	public function defaultProduct(int $product_id): void {
		
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {
			$product_data = $product_info;
			$product_data['model'] =$product_data['model'].'-cpy';
			$product_data['sku'] = '';
			$product_data['upc'] = '';
			$product_data['status'] = '0';

			$product_data['product_attribute'] = $this->model_catalog_product->getAttributes($product_id);
			$product_data['product_category'] = $this->model_catalog_product->getCategories($product_id);
			$product_data['product_description'] = $this->model_catalog_product->getDescriptions($product_id);
			$product_data['product_discount'] = $this->model_catalog_product->getDiscounts($product_id);
			$product_data['product_download'] = $this->model_catalog_product->getDownloads($product_id);
			$product_data['product_filter'] = $this->model_catalog_product->getFilters($product_id);
			$pImages = $this->model_catalog_product->getImages($product_id);
			$product_image =[];
			foreach($pImages as $pImage){
				$color_id = $pImage['color_id'];
				$product_image[$color_id][]=$pImage;
			}
			$product_data['product_image'] = $product_image;
			
			$product_data['product_layout'] = $this->model_catalog_product->getLayouts($product_id);
			$product_data['product_option'] = $this->model_catalog_product->getOptions($product_id);
			$product_data['product_subscription'] = $this->model_catalog_product->getSubscriptions($product_id);
			$product_data['product_related'] = $this->model_catalog_product->getRelated($product_id);
			$product_data['product_reward'] = $this->model_catalog_product->getRewards($product_id);
			$product_data['product_special'] = $this->model_catalog_product->getSpecials($product_id);
			$product_data['product_store'] = $this->model_catalog_product->getStores($product_id);

			$this->model_catalog_product->addProduct($product_data);
		}
	}
	
	
	
	protected function getCategoryId($path)
	{

		$language_id = $this->getDefaultLanguageId();
		$categoryIds = [];
		$_path = explode('>',$path);

		$parent= 0;
		foreach($_path as $last)
		{
			$sql = 'SELECT cd.category_id FROM `'.DB_PREFIX.'category_description` cd LEFT JOIN `'.DB_PREFIX.'category` c ON c.category_id= cd.category_id WHERE  cd.name LIKE "'.htmlEntities(trim($last)).'" AND cd.language_id="'.(int)$language_id.'" AND c.parent_id = "'.(int)$parent.'" LIMIT 1';
			$cQuery = $this->db->query($sql);
			$categoryIds[] = $parent = isset($cQuery->row['category_id'])? $cQuery->row['category_id']:0;
		}
		return implode(',',$categoryIds);
	}
	
	protected function getCategoryIdV2($path)
	{
		$language_id = $this->getDefaultLanguageId();
		$sql = 'SELECT cd.category_id FROM `'.DB_PREFIX.'category_description` cd LEFT JOIN `'.DB_PREFIX.'category` c ON c.category_id= cd.category_id LEFT JOIN `'.DB_PREFIX.'category_to_store` c2s ON c2s.category_id = cd.category_id WHERE  cd.name LIKE "'.htmlEntities(trim($path)).'" AND cd.language_id="'.(int)$language_id.'" AND c2s.store_id="'.(int)$this->session->data['store_id'].'" LIMIT 1';
		$cQuery = $this->db->query($sql);
		
		return isset($cQuery->row['category_id'])? $cQuery->row['category_id']:0;
	}


	protected function getProductAdditionalImages($product_id)
	{
		$sql = "SELECT product_image_id, product_id, image,color_id,class,sort_order FROM `".DB_PREFIX."product_image` WHERE product_id='".(int)$product_id."'";
		$query                 = $this->db->query( $sql );
		$old_product_image_ids = [];
		foreach($query->rows as $row){
			$product_image_id = $row['product_image_id'];
			$product_id       = $row['product_id'];
			$image_name       = $row['image'];
			$old_product_image_ids[$image_name] = $product_image_id;
		}
		return $old_product_image_ids;
	}

	protected function getOptionIds($seo = true)
	{
		$language_id = $this->getDefaultLanguageId();
		$sql         = "SELECT option_id, name FROM `".DB_PREFIX."option_description` WHERE language_id='".(int)$language_id."'";
		$query      = $this->db->query( $sql );
		$option_ids = [];
		foreach($query->rows as $row){
			$option_id = $row['option_id'];

			if($seo)
			{
				$name = by_SEO(htmlspecialchars_decode($row['name']));
			}
			else
			{
				$name = htmlspecialchars_decode($row['name']);
			}
			$option_ids[$name] = $option_id;
		}

		return $option_ids;
	}
	
	protected function getOptionValueIds($seo = true)
	{
		$language_id = $this->getDefaultLanguageId();
		$sql         = "SELECT option_id, option_value_id, name FROM `".DB_PREFIX."option_value_description` ";
		$sql .= "WHERE language_id='".(int)$language_id."'";

		$query            = $this->db->query( $sql );

		$option_value_ids = [];

		foreach($query->rows as $row){
			$option_id       = $row['option_id'];
			$option_value_id = $row['option_value_id'];
			if($seo)
			{
				$name = by_SEO(htmlspecialchars_decode($row['name']));
			}
			else
			{
				$name = htmlspecialchars_decode($row['name']);
			}

			$option_value_ids[$option_id][$name] = $option_value_id;
		}
		return $option_value_ids;
	}

	protected function getProductOptionIds( $product_id )
	{
		$sql = "SELECT product_option_id, option_id FROM `".DB_PREFIX."product_option` ";
		$sql .= "WHERE product_id='".(int)$product_id."'";
		$query              = $this->db->query( $sql );
		$product_option_ids = [];
		foreach($query->rows as $row){
			$product_option_id = $row['product_option_id'];
			$option_id         = $row['option_id'];
			$product_option_ids[$option_id] = $product_option_id;
		}
		return $product_option_ids;
	}

	protected function deleteUnlistedProductOptions( & $unlisted_product_ids )
	{
		foreach($unlisted_product_ids as $product_id){
			$sql = "DELETE FROM `".DB_PREFIX."product_option` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}

	protected function deleteProductOption( $product_id )
	{
		$sql = "SELECT product_option_id, product_id, option_id FROM `".DB_PREFIX."product_option` WHERE product_id='".(int)$product_id."'";
		$query                  = $this->db->query( $sql );
		$old_product_option_ids = [];
		foreach($query->rows as $row){
			$product_option_id = $row['product_option_id'];
			$product_id        = $row['product_id'];
			$option_id         = $row['option_id'];
			$old_product_option_ids[$product_id][$option_id] = $product_option_id;
		}
		if($old_product_option_ids){
			$sql = "DELETE FROM `".DB_PREFIX."product_option` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_option_ids;
	}

	protected function getProductOptionValueIds($product_id,$color_id)
	{
		$language_id = $this->getDefaultLanguageId();

		$sql         = "SELECT od.option_value_id AS opvaId, od.name AS opname FROM `".DB_PREFIX."option_value_description` od ";
		$sql .= " LEFT JOIN `".DB_PREFIX."product_option_value` pov ON (pov.option_value_id = od.option_value_id) WHERE pov.product_id='".(int)$product_id."' AND od.language_id='".(int)$language_id."'";

		$query      = $this->db->query( $sql );

		$option_ids = [];
		foreach($query->rows as $row){
			$option_id = $row['opvaId'];
			$name      = htmlspecialchars_decode($row['opname']);
			$option_ids[$name] = $option_id;
		}


		return isset($option_ids[$color_id])?$option_ids[$color_id]:'';
	}

	protected function getProductOptionValuesIds( $product_id,$product_option_id )
	{
		$sql = "SELECT * FROM `".DB_PREFIX."product_option_value` WHERE product_id='".(int)$product_id."' AND product_option_id='".(int)$product_option_id."'";

		$query         = $this->db->query( $sql );

		$option_values = [];
		foreach($query->rows as $row){
			$option_values[$row['option_value_id']] = array(
				"product_option_value_id"=>$row['product_option_value_id'],
				"parent_id"              =>$row['parent_id'],
				"update"                 =>0
			);
		}
		return $option_values;
	}


	public function getMaxProductId()
	{
		$query = $this->db->query( "SELECT MAX(product_id) as max_product_id FROM `".DB_PREFIX."product_to_store`" );
		
		if(isset($query->row['max_product_id'])){
			$max_id = (int)$query->row['max_product_id'] + 1;
		}
		else
		{
			$max_id = 1;
		}
		return $max_id;
	}

	public function isNew($product_id)
	{
		$query = $this->db->query( "SELECT COUNT(*) as ncount FROM `".DB_PREFIX."product` WHERE product_id= '$product_id' " );
		
		if(isset($query->row['ncount'])){
			$max_id = $query->row['ncount'];
		}
		else
		{
			$max_id = 0;
		}
		return ($max_id)?FALSE:TRUE;
	}
	
	protected function getCell(&$worksheet,$row,$col,$default_val='') {
//		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index, PhpSpreadsheet now uses 1-based column index
		$row += 1; // we use 0-based, PhpSpreadsheet uses 1-based row index
		$val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
		if ($val===null) {
			$val = $default_val;
		}
		return $val;
	}
	

	protected function startsWith( $haystack, $needle ) {
		if (oc_strlen( $haystack ) < oc_strlen( $needle )) {
			return false;
		}
		return (oc_substr( $haystack, 0, oc_strlen($needle) ) == $needle);
	}

	protected function endsWith( $haystack, $needle ) {
		if (oc_strlen( $haystack ) < oc_strlen( $needle )) {
			return false;
		}
		return (oc_substr( $haystack, oc_strlen($haystack)-oc_strlen($needle), oc_strlen($needle) ) == $needle);
	}
	
	
	protected function clean( & $str, $allowBlanks = false )
	{
		$result = "";
		$n      = oc_strlen( $str );
		for($m = 0; $m < $n; $m++){
			$ch = oc_substr( $str, $m, 1 );
			if(($ch == " ") && (!$allowBlanks) || ($ch == "\n") || ($ch == "\r") || ($ch == "\t") || ($ch == "\0") || ($ch == "\x0B")){
				continue;
			}
			$result .= $ch;
		}
		return $result;
	}

	protected function multiquery( $sql )
	{
		foreach(explode(";\n", $sql) as $sql){
			$sql = trim($sql);
			if($sql){
				$this->db->query($sql);
			}
		}
	}


	protected function moreRewardCells( $i, & $j, & $worksheet, & $reward )
	{
		return;
	}

	protected function clearCache()
	{
		$this->cache->delete('*');
	}



	public function uploadXLS( $filename, $type,$incremental = false )
	{
		global $registry;
		$registry = $this->registry;
		set_error_handler('error_handler_for_export_import',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export_import');

		try
		{
			$this->session->data['export_import_nochange'] = 1;

			// we use the PHPExcel package from https://github.com / PHPOffice / PHPExcel
			$cwd = getcwd();
			$dir = version_compare(VERSION,'3.0','>=') ? 'library/export_import' : 'PHPExcel';
			chdir( DIR_SYSTEM.$dir );
			require_once( 'Classes/PHPExcel.php' );
			chdir( $cwd );

			// Memory Optimization
			if($this->config->get( 'export_import_settings_use_import_cache' )){
				$cacheMethod   = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				$cacheSettings = array(' memoryCacheSize '=> '16MB'  );
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			}

			// parse uploaded spreadsheet file
			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader     = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
			$this->clearCache();
			$this->session->data['export_import_nochange'] = 0;

			$this->uploadProductsXLS( $reader,$type);

			return true;


		} catch(Exception $e){
			$errstr = $e->getMessage();
			$errline= $e->getLine();
			$errfile= $e->getFile();
			$errno  = $e->getCode();
			$this->session->data['export_import_error'] = array('errstr' =>$errstr,'errno'  =>$errno,'errfile'=>$errfile,'errline'=>$errline );

			return false;
		}

		return TRUE;
	}

	protected function uploadProductsXLS( & $reader,$upType)
	{
		$this->opt['options'] = [];
		$this->opt['changed'] = [];
		$this->opt['products'] = [];

		$sheetData = [];
		$sheetsNames = $reader->getSheetNames();
		foreach($sheetsNames as $sheetsName)
		{
			$sheetData[$sheetsName] = $reader->getSheetByName( $sheetsName );
		}

		if(isset($sheetData['oc_option_value_description']))
		{
			$recData = $sheetData['oc_option_value_description'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					continue;
				}
				$j = 1;



				if(isset($this->opt['options'][$this->getCell($recData,$i,3)]))
				{
					if(!isset($this->opt['options'][$this->getCell($recData,$i,3)][$this->getCell($recData,$i,4)]))
					{
						$this->opt['options'][$this->getCell($recData,$i,3)][$this->getCell($recData,$i,4)] = $this->getCell($recData,$i,1);
					}

				}
				else
				{
					$this->opt['options'][$this->getCell($recData,$i,3)] = [];
					$this->opt['options'][$this->getCell($recData,$i,3)][$this->getCell($recData,$i,4)] = $this->getCell($recData,$i,1);

				}
			}

		}

		/**/
		if(isset($sheetData['oc_product']))
		{
			$recData = $sheetData['oc_product'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($recData,$i,$j);
					}
					continue;
				}
				$j    = 1;

				$vals = [];
				foreach($titles as $_title){
					if($j == 1)
					{
						$product_id = $this->getCell($recData,$i,$j++);
						$vals[] = $product_id;
					}
					else
					{
						$vals[] = $this->getCell($recData,$i,$j++);
					}
				}
				if($this->isExist($product_id))
				{

				}
				else
				{

				}
				$sql = "INSERT INTO `oc_product` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
				$this->db->query($sql);
			}

		}

		if(isset($sheetData['oc_product_description']))
		{
			$recData = $sheetData['oc_product_description'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($recData,$i,$j);
					}
					continue;
				}
				$j    = 1;

				$vals = [];
				foreach($titles as $_title){
					$vals[] = $this->db->escape($this->getCell($recData,$i,$j++));
				}

				$sql = "INSERT INTO `oc_product_description` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
				$this->db->query($sql);

			}
		}



		if(isset($sheetData['oc_product_option']))
		{
			$recData = $sheetData['oc_product_option'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						if($this->getCell($recData,$i,$j) != 'product_option_id')
						{
							$titles[] = $this->getCell($recData,$i,$j);
						}

					}
					continue;
				}
				$j      = 1;
				$oldOne = $this->getCell($recData,$i,$j++);
				$this->opt['products'][$oldOne] = 0;
				$vals = [];

				foreach($titles as $_title){
					$vals[] = $this->getCell($recData,$i,$j++);
				}

				$sql               = "INSERT INTO `oc_product_option` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
				$this->db->query($sql);
				$product_option_id = $this->db->getLastId();
				$this->opt['products'][$oldOne] = $product_option_id;

			}
		}


		if(isset($sheetData['oc_product_option_value']))
		{
			$recData = $sheetData['oc_product_option_value'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						if($this->getCell($recData,$i,$j) != 'product_option_value_id')
						{
							$titles[] = $this->getCell($recData,$i,$j);
						}

					}
					continue;
				}
				$j    = 2;

				$vals = [];
				foreach($titles as $_title){

					if($j == 2)
					{
						$oldOne = $this->getCell($recData,$i,2);$j++;
						$vals[] = $this->opt['products'][$oldOne] ;
					}
					else
					if($j == 4)
					{
						$optId = $this->getCell($recData,$i,4);$j++;
						$vals[] = $optId;
					}
					else
					if($j == 5)
					{
						$optValId = $this->getCell($recData,$i,5);$j++;
						//fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y - m - d G:i:s') . " - " . print_r($optValId,TRUE). " \n");
						$vals[] = $this->isOptionExist($optId,$optValId);
					}
					else
					{
						$vals[] = $this->db->escape($this->getCell($recData,$i,$j++));
					}
				}

				$sql = "INSERT INTO `oc_product_option_value` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";

				$this->db->query($sql);

			}
		}



		/*

		if(isset($sheetData['oc_product_reward'])){
		$recData=$sheetData['oc_product_reward'];

		$i = 0;
		$k = $recData->getHighestRow();
		$titles = [];
		for($i = 0; $i < $k; $i += 1)
		{
		if($i == 0)
		{
		$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
		for($j = 1; $j <= $max_col; $j += 1)
		{
		$titles[] = $this->getCell($recData,$i,$j);
		}
		continue;
		}
		$j = 1;

		$vals=[];
		foreach($titles as $_title)
		{
		$vals[]=$this->getCell($recData,$i,$j++);
		}

		$sql = "INSERT INTO `oc_product_reward` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
		$this->db->query($sql);
		}
		}
		*/
		if(isset($sheetData['oc_product_to_category']))
		{
			$recData = $sheetData['oc_product_to_category'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($recData,$i,$j);
					}
					continue;
				}
				$j    = 1;

				$vals = [];
				foreach($titles as $_title){
					$vals[] = $this->getCell($recData,$i,$j++);
				}

				$sql = "INSERT INTO `oc_product_to_category` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."');";
				$this->db->query($sql);
			}
		}

		if(isset($sheetData['oc_product_to_layout']))
		{
			$recData = $sheetData['oc_product_to_layout'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($recData,$i,$j);
					}
					continue;
				}
				$j    = 1;

				$vals = [];
				foreach($titles as $_title){
					$vals[] = $this->getCell($recData,$i,$j++);
				}

				$sql = "INSERT INTO `oc_product_to_layout` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
				$this->db->query($sql);
			}
		}

		if(isset($sheetData['oc_product_to_store']))
		{
			$recData = $sheetData['oc_product_to_store'];

			$i       = 0;
			$k       = $recData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $recData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $recData->getHighestColumn() );
					for($j = 1; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($recData,$i,$j);
					}
					continue;
				}
				$j    = 1;

				$vals = [];
				foreach($titles as $_title){
					$vals[] = $this->getCell($recData,$i,$j++);
				}

				$sql = "INSERT INTO `oc_product_to_store` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
				$this->db->query($sql);
			}
		}


		if(isset($sheetData['oc_product_image']))
		{
			$imaData = [];
			$imaData = $sheetData['oc_product_image'];

			$i       = 0;
			$k       = $imaData->getHighestRow();
			$titles  = [];
			for($i = 0; $i < $k; $i += 1){
				if($i == 0){
					//$max_col = PHPExcel_Cell::columnIndexFromString( $imaData->getHighestColumn() );
					$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $imaData->getHighestColumn() );

					for($j = 2; $j <= $max_col; $j += 1){
						$titles[] = $this->getCell($imaData,$i,$j);
					}
					//fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y - m - d G:i:s') . " - " . print_r($titles,TRUE). " \n");
					continue;
				}
				else
				{
					$j    = 2;

					$vals = [];
					foreach($titles as $_title){
						$nVal = '';
						if($_title == 'color_id')
						{
							$nVal = $this->getCell($imaData,$i,$j++);
							//fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y - m - d G:i:s') . " - " . print_r($nVal,TRUE). " \n");
							if($nVal && isset($this->opt['changed'][$nVal]))
							{
								$vals[] = $this->opt['changed'][$nVal];
							}
							else
							{
								$vals[] = '';
							}

						}
						else
						{
							$vals[] = $this->getCell($imaData,$i,$j++);
						}


					}

					$sql = "INSERT INTO `oc_product_image` (`".implode("`,`",$titles)."`) VALUES ('".implode("','",$vals)."')";
					$this->db->query($sql);
				}
			}
		}

		/**/




	}



	public function downloadXLS($qry,$offset = null, $rows = null, $min_id = null, $max_id = null)
	{
		// we use our own error handler
		global $registry;
		$registry = $this->registry;
		set_error_handler('error_handler_for_export_import',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export_import');
		$this->log->write('downloadXLS ');
		// Use the PHPExcel package from http://phpexcel.codeplex.com /
		$cwd      = getcwd();
		chdir( DIR_SYSTEM.'PHPExcel' );
		require_once( 'Classes/PHPExcel.php' );
		chdir( $cwd );

		// find out whether all data is to be downloaded
		$all      = !isset($offset) && !isset($rows) && !isset($min_id) && !isset($max_id);
		$cacheMethod   = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize'=> '16MB' );
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		// Memory Optimization
		/*
		if ($this->config->get( 'export_import_settings_use_export_cache' )) {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array( 'memoryCacheSize'  => '16MB' );
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		}
		$this->posted_categories = $this->getPostedCategories();
		*/

		try
		{

			// create a new workbook
			$workbook   = new PHPExcel();

			// set some default styles
			$workbook->getDefaultStyle()->getFont()->setName('Arial');
			$workbook->getDefaultStyle()->getFont()->setSize(10);
			//$workbook->getDefaultStyle()->getAlignment()->setIndent(0.5);
			$workbook->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$workbook->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$workbook->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

			// pre - define some commonly used styles
			$box_format = array(
				'fill'           => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color'     => array('rgb'=> 'ffa500')
				),

				'alignment' => array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false,
					'indent'    => 0
				)
				/**/
			);

			$reverse_format = array(
				'fill'           => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color'     => array('rgb'=> 'DEDEDE')
				),

				'alignment' => array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false,
					'indent'    => 0
				)
				/**/
			);




			$text_format = array(

				'numberformat' => array(
					'code'=> PHPExcel_Style_NumberFormat::FORMAT_TEXT
				),
				'alignment'       => array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false,
					'indent'    => 0
				)
				/**/
			);
			$price_format = array(
				'numberformat' => array(
					'code'=> '######0.00'
				),
				'alignment'       => array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,

					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false,
					'indent'    => 0
					/**/
				)
			);

			$weight_format = array(
				'numberformat' => array(
					'code'=> '##0.00'
				),
				'alignment'       => array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false,
					'indent'    => 0
					/**/
				)
			);

			$worksheet_index = 0;
			$sql             = "SELECT table_name FROM information_schema.tables WHERE (table_name LIKE '%".DB_PREFIX."product%' OR table_name LIKE '%".DB_PREFIX."option%' ) AND table_schema LIKE '%gendb%'";
			//$sql = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE ' % ".DB_PREFIX."option % ' AND table_schema LIKE ' % gendb % '";
			$results         = $this->db->query( $sql );

			foreach($results->rows as $table)
			{
				$table_name = $table['table_name'];

				if($table_name != '')
				{
					if($worksheet_index != 0)
					{
						$workbook->createSheet();
					}
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle($table_name );
					$this->populateDataWorksheetXLS( $worksheet,$table_name, $box_format, $text_format);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				}
			}

			$workbook->setActiveSheetIndex(0);
			$datetime = date('Y-m-d-H-m');
			$filename = $this->session->data['store_name'].'-xls-'.$datetime.'-downloaded.xlsx';
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
			$objWriter= PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');
			$objWriter->setPreCalculateFormulas(false);
			$objWriter->save('php://output');

			// Clear the spreadsheet caches
			$this->clearSpreadsheetCache();
			exit;

		} catch(Exception $e){
			$errstr = $e->getMessage();
			$errline= $e->getLine();
			$errfile= $e->getFile();
			$errno  = $e->getCode();
			$this->session->data['export_import_error'] = array('errstr' =>$errstr,'errno'  =>$errno,'errfile'=>$errfile,'errline'=>$errline );
			if($this->config->get('config_error_log')){
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return;
		}
	}

	protected function populateDataWorksheetXLS( & $worksheet,$table_name, & $box_format, & $text_format )
	{
		$j      = 0;
		$styles = [];
		$data = [];
		$sql               = "SHOW COLUMNS FROM ".$table_name;
		$table_name_result = $this->db->query( $sql );
		if(isset($table_name_result->rows))
		{
			$names = [];
			foreach($table_name_result->rows as $colname)
			{
				$sW = explode('(',$colname['Type']);

				if(isset($sW[1]))
				{
					$_setW = explode(')',$sW[1]);
					$setW  = ($_setW[0] == 0)?20:$_setW[0];
				}
				else
				{
					$setW = 20;
				}
				$worksheet->getColumnDimensionByColumn($j++)->setWidth($setW);
				$data[$j++] = $colname['Field'];
			}
		}
		/*
		if($qry==''){
		$sql = "SELECT * FROM ".$table_name;
		}else{
		$sql = "SELECT * FROM ".$table_name." WHERE product_id > 40000 ";
		}
		*/

		$konum = strpos($table_name, 'oc_prod');
		if($konum === false)
		{
			$sql = "SELECT * FROM ".$table_name;
		}
		else
		{
			$sql = "SELECT * FROM ".$table_name." WHERE product_id > 40000 ";
		}

		//$sql = "SELECT * FROM ".$table_name;
		$results = $this->db->query( $sql );


		$i       = 1;
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );

		// The actual categories data
		$i += 1;
		$j = 0;

		if(isset($results->rows)){
			foreach($results->rows as $row){
				$data = [];
				$worksheet->getRowDimension($i)->setRowHeight(26);
				foreach($row as $ind=>$col)
				{

					$data[$j++] = html_entity_decode($col,ENT_QUOTES,'UTF-8');
				}
				$this->setCellRow( $worksheet, $i, $data );
				$i += 1;
				$j = 0;
			}
		}

		$this->setColumnStyles( $worksheet, $styles, 2, $i - 1 );
	}

	protected function isExist($id):bool
	{
		$query = $this->db->query( "SELECT COUNT(*) as ncount FROM `".DB_PREFIX."product` WHERE product_id = '".(int)$id."' " );
		
		return $query->row['ncount'];
	}

	protected function isOptionExist($optId,$optValId)
	{
		$eKey = '';
		foreach( $this->opt['options'][$optId] as $key => $value)
		{
			if($value == $optValId)
			{
				$eKey = trim($key);
			}
		}


		if($eKey)
		{

			$sql = "SELECT option_value_id as ncount FROM `".DB_PREFIX."option_value_description` WHERE option_id= '".(int)$optId."' AND name LIKE '" . $this->db->escape($eKey) . "' LIMIT 1" ;


			$query = $this->db->query($sql);

			if(isset($query->row['ncount'])){

				$max_id = $query->row['ncount'];
				$this->opt['changed'][$optValId] = $max_id;
				return $max_id;
			}


			$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$optId . "', image = ''");
			$option_value_id = $this->db->getLastId();

			$this->opt['changed'][$optValId] = $option_value_id;

			$languages = $this->getLanguages();
			foreach($languages as $language)
			{
				$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language['language_id'] . "', option_id = '" . (int)$optId . "', name = '" . $this->db->escape($eKey) . "'");
			}

			return $option_value_id;


		}
		return 0;
	}


	public function downloadZip($data,$offset = null, $rows = null, $min_id = null, $max_id = null)
	{
		
	
	}

	protected function getStoreIdsForProducts() {
		$sql =  "SELECT product_id, store_id FROM `".DB_PREFIX."product_to_store` ps;";
		$store_ids = [];
		$result = $this->db->query( $sql );
		foreach ($result->rows as $row) {
			$productId = $row['product_id'];
			$store_id = $row['store_id'];
			if (!isset($store_ids[$productId])) {
				$store_ids[$productId] = [];
			}
			if (!in_array($store_id,$store_ids[$productId])) {
				$store_ids[$productId][] = $store_id;
			}
		}
		return $store_ids;
	}


	protected function getLayoutsForProducts() {
		$sql  = "SELECT pl.*, l.name FROM `".DB_PREFIX."product_to_layout` pl ";
		$sql .= "LEFT JOIN `".DB_PREFIX."layout` l ON pl.layout_id = l.layout_id ";
		$sql .= "ORDER BY pl.product_id, pl.store_id;";
		$result = $this->db->query( $sql );
		$layouts = [];
		foreach ($result->rows as $row) {
			$productId = $row['product_id'];
			$store_id = $row['store_id'];
			$name = $row['name'];
			if (!isset($layouts[$productId])) {
				$layouts[$productId] = [];
			}
			$layouts[$productId][$store_id] = $name;
		}
		return $layouts;
	}


	protected function getProducts( &$languages, $default_language_id, $offset=null, $rows=null, $min_id=null, $max_id=null ) {
		$sql  = "SELECT ";
		$sql .= "  p.product_id,";
		$sql .= "  GROUP_CONCAT( DISTINCT CAST(pc.category_id AS CHAR(11)) SEPARATOR \",\" ) AS categories,";
		$sql .= "  p.sku,";
		$sql .= "  p.upc,";
		$sql .= "  p.ean,";
		$sql .= "  p.jan,";
		$sql .= "  p.isbn,";
		$sql .= "  p.mpn,";
		$sql .= "  p.location,";
		$sql .= "  p.quantity,";
		$sql .= "  p.model,";
		$sql .= "  m.name AS manufacturer,";
		$sql .= "  p.image AS image_name,";
		$sql .= "  p.shipping,";
		$sql .= "  p.price,";
		$sql .= "  p.points,";
		$sql .= "  p.date_added,";
		$sql .= "  p.date_modified,";
		$sql .= "  p.date_available,";
		$sql .= "  p.weight,";
		$sql .= "  wc.unit AS weight_unit,";
		$sql .= "  p.length,";
		$sql .= "  p.width,";
		$sql .= "  p.height,";
		$sql .= "  p.status,";
		$sql .= "  p.tax_class_id,";
		$sql .= "  p.sort_order,";
		$sql .= "  p.stock_status_id, ";
		$sql .= "  mc.unit AS length_unit, ";
		$sql .= "  p.subtract, ";
		$sql .= "  p.minimum, ";
		$sql .= "  p.master_id, ";
		$sql .= "  p.variant, ";
		$sql .= "  p.override, ";
		$sql .= "  GROUP_CONCAT( DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR \",\" ) AS related ";
		$sql .= "FROM `".DB_PREFIX."product` p ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_to_category` pc ON p.product_id=pc.product_id ";
		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `".DB_PREFIX."product_to_category` pc2 ON p.product_id=pc2.product_id ";
		}
		$sql .= "LEFT JOIN `".DB_PREFIX."manufacturer` m ON m.manufacturer_id = p.manufacturer_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."weight_class_description` wc ON wc.weight_class_id = p.weight_class_id ";
		$sql .= "  AND wc.language_id=$default_language_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."length_class_description` mc ON mc.length_class_id=p.length_class_id ";
		$sql .= "  AND mc.language_id=$default_language_id ";
		$sql .= "LEFT JOIN `".DB_PREFIX."product_related` pr ON pr.product_id=p.product_id ";
		if (isset($min_id) && isset($max_id)) {
			$sql .= "WHERE p.product_id BETWEEN $min_id AND $max_id ";
			if ($this->posted_categories) {
				$sql .= "AND pc2.category_id IN ".$this->posted_categories." ";
			}
		} else if ($this->posted_categories) {
			$sql .= "WHERE pc2.category_id IN ".$this->posted_categories." ";
		}
		if ($this->posted_manufacturers) {
			$sql .= (strpos($sql," WHERE ",0)===false) ? "WHERE " : "AND ";
			$sql .= "p.manufacturer_id IN ".$this->posted_manufacturers." ";
		}
		$sql .= "GROUP BY p.product_id ";
		$sql .= "ORDER BY p.product_id ";
		if (isset($offset) && isset($rows)) {
			$sql .= "LIMIT $offset,$rows; ";
		} else {
			$sql .= "; ";
		}
		$results = $this->db->query( $sql );
		$product_descriptions = $this->getProductDescriptions( $languages, $offset, $rows, $min_id, $max_id );
		foreach ($languages as $language) {
			$language_code = $language['code'];
			foreach ($results->rows as $key=>$row) {
				if (isset($product_descriptions[$language_code][$key])) {
					$results->rows[$key]['name'][$language_code] = $product_descriptions[$language_code][$key]['name'];
					$results->rows[$key]['description'][$language_code] = $product_descriptions[$language_code][$key]['description'];
					$results->rows[$key]['description_alt'][$language_code] = $product_descriptions[$language_code][$key]['description_alt'];
					$results->rows[$key]['bullet'][$language_code] = $product_descriptions[$language_code][$key]['bullet'];
					$results->rows[$key]['meta_title'][$language_code] = $product_descriptions[$language_code][$key]['meta_title'];
					$results->rows[$key]['meta_description'][$language_code] = $product_descriptions[$language_code][$key]['meta_description'];
					$results->rows[$key]['meta_keyword'][$language_code] = $product_descriptions[$language_code][$key]['meta_keyword'];
					$results->rows[$key]['tag'][$language_code] = $product_descriptions[$language_code][$key]['tag'];
				} else {
					$results->rows[$key]['name'][$language_code] = '';
					$results->rows[$key]['description'][$language_code] = '';
					$results->rows[$key]['meta_title'][$language_code] = '';
					$results->rows[$key]['meta_description'][$language_code] = '';
					$results->rows[$key]['meta_keyword'][$language_code] = '';
					$results->rows[$key]['tag'][$language_code] = '';
				}
			}
		}
		return $results->rows;
	}

	
	
	
	
	
	
	
	/* UPLOAD NEWEST */
	
	public function getUpload($file){
		global $registry;
		$registry = $this->registry;
		$filename = DIR_IMPORT.$file;
		set_error_handler('\Opencart\Admin\Model\Bytao\error_handler_for_export_import',E_ALL);
		register_shutdown_function('\Opencart\Admin\Model\Bytao\fatal_error_shutdown_handler_for_export_import');
		try
		{
			$this->session->data['export_import_nochange'] = 1;
			// enable auto_load from system/library/export_import
			require( DIR_EXTENSION.'export_import/system/library/export_import/vendor/autoload.php' );

			$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

			// parse uploaded spreadsheet file
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
			$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
			$data = $reader->getSheet(0);
			if($data == null){
				return [];
			}
			$max_col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString( $data->getHighestColumn() );
			$rData=[];
			$rData['row'] = $max_row = $data->getHighestRow();
			$rData['file'] = $file;
			$pcount=0;
			for($j = 0; $j <= $max_col+1; $j += 1){
				if(!$j){
					$rData['titles']['img'] = 'IMG';
				}else{
					$cell=$this->getCell($data,0,$j);
					if($cell){
						$rData['titles'][by_SEO($cell)] = $cell;
					}
				}
			}
			
			for($i = 1; $i <= $max_row-1; $i += 1){
				$model='';
				$color='';
				$rData['cell'][$i]['img'] ='';
				for($j = 1; $j <= $max_col; $j += 1){
					$cell = $this->getCell($data,$i,$j);
					switch(by_SEO($this->getCell($data,0,$j))){
						case 'product-id': $cell?$pcount++:''; break;
						case 'model' : $model =by_SEO($cell); break;
						case 'colors' : $color =by_SEO($cell); break;
					}
					$rData['cell'][$i][by_SEO($this->getCell($data,0,$j))] = $cell?$cell:'';
				}
				
				$rData['cell'][$i]['img'] = is_dir(DIR_IMAGE .'catalog/import/products/'.$model.'/'.$color )?1:0;
			}
			
			$rData['pcount'] = $pcount;
			
			$this->toJSON($filename,$rData);
			$this->clearCache();
			$this->session->data['export_import_nochange'] = 0;
			return $rData;
			
		} catch(Exception $e){
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$this->session->data['export_import_error'] = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
			}
			return [];
		}
		
	}
	
	protected function toJSON($filename,$rData){
		$file=explode('.',$filename);
		$jsonData = json_encode($rData);
		file_put_contents($file[0].'.json', $jsonData);
	}
	
	public function setImport($cData){
		$sizes=['2XS','XS','S','M','L','XL','2XL','3XL','4XL'];
		$oPrice =['3XL Price'=>0,'2XL Price'=>0,'4XL Price'=>0];
		$languages = $this->getLanguages();
		$this->load->model('catalog/product');
		$product_descriptions = [];
		$lang = [];
		$allowed = [
			'.ico',
			'.jpg',
			'.jpeg',
			'.png',
			'.gif',
			'.webp',
			'.JPG',
			'.JPEG',
			'.PNG',
			'.GIF'
		];
		
		foreach($languages as $language){
			$language_id   = $language['language_id'];
			$language_code = $language['code'];
			$lCode = explode('-',$language_code);
			$LC = 'en';
			if(isset($lCode[1])){
				$LC = $lCode[0];
			}else{
				$lCode = explode('_',$language_code);
				$LC = isset($lCode[1])?$lCode[0]:'en';
			}
			$lang[$LC] = $language_id;
		}
			
		$this->setProdTitles();
		$option_ids           = $this->getOptionIds();
		$option_value_ids     = $this->getOptionValueIds();
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		
		
		$optionIds=[];
		//$optionIds[] = $size_ids;
		$product_id = 0;
		$subtract = 0;
		$status = 0;
		$aCell = $cData['json']['cell'];
		
		
		foreach($cData['json']['cell'] as $sorder => $ROW){
			
			if($ROW['product-id']!= '')
			{ // product
				
				$product_id = $ROW['product-id'];
				$subtract = (int)$ROW['subtract']?$ROW['subtract']:0;
				$status = (int)$ROW['status']?$ROW['status']:0;
				$total_quantity = 10;
				$sSort =0; 
				
				if($this->isNew($product_id)){
					$this->addDefaultProduct($product_id);
					$model = by_SEO($ROW['model']);
					if(!is_dir(DIR_IMAGE .'catalog/products/'.strtolower($model))){
						$directory = DIR_IMAGE . 'catalog/products/' . strtolower($model);
						mkdir($directory , 0777);
					}
		
					$isNew = TRUE;
				}else{
					$isNew = FALSE;
				}
				
				$this_shearling = false;
				$this_leather = false;
				$this_goat = false;
				$isSub = FALSE;
				$option_values = [];
				
				$konum = strpos(strtolower(trim($ROW['colors-group'])), 'shearling');
				if($konum !== false){$this_shearling = true;}
				$konum = strpos(strtolower(trim($ROW['colors-group'])), 'leather');
				if($konum !== false){$this_leather = true;}
				$konum = strpos(strtolower(trim($ROW['colors-group'])), 'goat');
				if($konum !== false){$this_goat = true;}
				
				$option_id = 0;
				
				$color_group_id = $ROW['colors-group']?$option_ids[by_SEO($ROW['colors-group'])]:0;
				if($color_group_id){
					$option_values = $option_value_ids[$color_group_id];
					$optionIds[] = $color_group_id;
				}else{
					$option_values = [];
				}
				
				
				//$size_id = $option_ids['A/W/R'];
				
				/*
				$SQL = "DELETE FROM `".DB_PREFIX."product_option` WHERE product_id='".(int)$product_id."' AND option_id = '".(int)$size_id."'";
				$this->db->query($SQL);
				
				$SQL = "DELETE FROM ".DB_PREFIX."product_option_value WHERE product_id='".(int)$product_id."'";
				$this->db->query($SQL);
				*/
				$size_values = [];
				$size_id = 0;
				
				
				$_colors = [];
				$_sizes = [];
				
				$oPrice['4XL Price'] = isset($ROW['4xl_price'])?$ROW['4xl_price']:'';
				$oPrice['3XL Price'] = isset($ROW['3xl_price'])?$ROW['3xl_price']:'';
				$oPrice['2XL Price'] = isset($ROW['2xl_price'])?$ROW['2xl_price']:'';
				$TYPE = isset($ROW['2xl_price'])?$ROW['2xl_price']:'';
				
				foreach($cData['checks'] as $check){
					
					if(in_array($check,$sizes,true))
					{
						$type            = 0;
						$sVal = $ROW[strtolower($check)];
						if($sVal!='-')
						{
							if(!$size_id)
							{
								$size_id = $option_ids['size'];
								$size_values = $option_value_ids[$size_id];
								$optionIds[] = $size_id;
							}
						
							if(isset($product_option_ids[$size_id])&& (int)$product_option_ids[$size_id] != 0){
								$product_option_id =  (int)$product_option_ids[$size_id];
							}else{
								$product_option_id = 0;
							}
						
						
						
							if($product_option_id == 0 ){
								$SQL = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$size_id,'',1)";
								$this->db->query($SQL);
								$product_option_id = $this->db->getLastId();
								$product_option_ids = $this->getProductOptionIds( $product_id );
							}
						
						
							if(!isset($size_values[by_SEO($check)])){
								$SQL = "INSERT INTO `" . DB_PREFIX . "option_value` SET `option_id` = '" . (int)$size_id . "'";
								$this->db->query($SQL);

								$size_value_id = $this->db->getLastId();
								$SQL = "INSERT INTO `" . DB_PREFIX . "option_value_description` SET `option_value_id` = '" . (int)$size_value_id . "', `language_id` = '" . (int)$language_id . "', `option_id` = '" . (int)$size_id . "', `name` = '" . $this->db->escape($check) . "'";
								$this->db->query($SQL);
								$option_value_ids     = $this->getOptionValueIds();
								$size_values = $option_value_ids[$size_id];
							}
						
							$size_value_id = $size_values[by_SEO($check)];
						
						
							switch($sVal){
								case '+': break;
								case '1':$type = 1;break;
								case '2':$type = 2; break;
							}
						
							$parent_id       = 0;
							$quantity        = (int)$ROW[strtolower($check)];
							$total_quantity += $quantity;
							$price = isset($oPrice[$check.' Price'])?$oPrice[$check.' Price']:0;
							
							$SQL = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$size_id','$size_value_id','$parent_id','$quantity','$type','0','$price','+','0','+','0','+','$sSort')";
							$sSort++;
							
							//$this->log->write($SQL);
							$this->db->query($SQL);
						}
						
						
					} 
					else if(isset($oPrice[$check])&& !$isSub)
					{
						$VAR = strtolower(str_replace(' ','_',$check));
						$VAL = $oPrice[$check];
						//$SQL = "UPDATE `".DB_PREFIX."product` SET ".$VAR."='".(float)$VAL."' WHERE product_id='$product_id'";
						//$this->db->query($SQL);
					} 
					else if(by_SEO($check)=='category' && !$isSub )
					{
						if($ROW['category']){
							$_categories = explode(',',$ROW['category']);
							$cats        = [];
							foreach($_categories AS $_category){
								$cats[] = $this->getCategoryId($_category);
							}
							$ROW['category'] = implode(',',$cats);
						}
						$SQL="UPDATE " . DB_PREFIX . "product_to_category SET updated = '0' WHERE product_id='".(int)$product_id."'";
						
						//$this->log->write('category:'.$SQL);
						$this->db->query($SQL);
						
						if($ROW['category']){
							$categories = explode(',',$ROW['category']);
							foreach($categories as $category_id){
								$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'");

								if($query_ptc->num_rows > 0){
									$SQL = "UPDATE " . DB_PREFIX . "product_to_category SET updated = '1' WHERE category_id = '" . (int)$category_id . "' AND product_id='".(int)$product_id."'";
									//$this->log->write('SQL:'.$SQL);
									$this->db->query($SQL);
								}
								else
								{
									$SQL ="INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "', updated='1'";
									//$this->log->write('SQL:'.$SQL);
									$this->db->query($SQL);
								}

							}
							$query_ptc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' GROUP BY category_id");

							foreach($query_ptc->rows AS $category)
							{
								if(!in_array($category['category_id'], $categories))
								{
									$SQL = "DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id='".(int)$product_id."' and category_id='".$category['category_id']."'";
									//$this->log->write('SQL:'.$SQL);
									$this->db->query($SQL);

								}
							}
						}	
					} 
					else if(by_SEO($check)=='colors-group' && !$isSub )
					{ 
						if($color_group_id){
							$option_values      = $option_value_ids[$color_group_id];
							$product_option_ids = $this->getProductOptionIds( $product_id );
						}
					} 
					else if(by_SEO($check)=='colors')
					{ 
						$product_option_id  = isset($product_option_ids[$color_group_id]) ? $product_option_ids[$color_group_id] : 0;
						
						if($product_option_id == 0 ){
							// new option
							$SQL = "INSERT INTO `".DB_PREFIX."product_option` (`product_id`,`option_id`,`value`,`required` ) VALUES ($product_id,$color_group_id,'',1)";
							//$this->log->write('SQL:'.$SQL);
							$this->db->query($SQL);
							
							$product_option_id = $this->db->getLastId();
							$product_option_ids = $this->getProductOptionIds( $product_id );
						}else{
							//option tanımlı ozaman güncelle
						}
						$model = $ROW['model'];
						
						$colors = $this->getCellParts($aCell,$sorder,by_SEO($check));
						
						if($colors['colors'])
						{
							$osort    = 0;
							$quantity = 1;
							$total_quantity += $quantity;

							$SQL = "DELETE FROM " . DB_PREFIX . "product_option_value WHERE  product_id='".(int)$product_id."' AND  option_id ='".$color_group_id."' AND product_option_id = '".$product_option_id."'";
							//$this->log->write('SQL:'.$SQL);
							$this->db->query($SQL);
							
							$osort = 0;
							foreach($colors['colors'] as $ocolor => $val){
								if(isset($option_values[by_SEO($ocolor)])){
									$color_value_id = $option_values[by_SEO($ocolor)];
								}
								else
								{
									$color_value_id = 0;
								}
								$type=0;
								switch($val){
									case 'R':$type=1;break;
									case 'W':$type=2;break;
									case 'A':
									default:$type=0;break;
								}
								
								$parent_id = 0;
								$_colors[] = $color_value_id;
								$SQL = "INSERT INTO `".DB_PREFIX."product_option_value` (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`parent_id`,`quantity`,`type`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`sort_order` ) VALUES ('$product_option_id','$product_id','$color_group_id','$color_value_id','$parent_id','$quantity','$type','$subtract','0','+','0','+','0','+','$osort')";
								$this->db->query($SQL);
								
								if(is_dir(DIR_IMAGE .'catalog/import/products/'.$model.'/'.by_SEO($ocolor)))
								{
									$directory = DIR_IMAGE . 'catalog/products/' . strtolower($model);
									if (!is_dir($directory)) {
										mkdir($directory , 0777);
									}
									$directory.='/'.by_SEO($ocolor);
									if (!is_dir($directory)) {
										mkdir($directory , 0777);
									}
									
									
									$paths = glob(DIR_IMAGE .'catalog/import/products/'.$model.'/'.by_SEO($ocolor) . '*{/,.jpg,.jpeg,.png,.gif,.webp,.JPG,.JPEG,.PNG,.GIF}', GLOB_BRACE);
		
									foreach($paths as $path){
										if (is_file($path) && in_array(substr($path, strrpos($path, '.')), $allowed)) 
										{
											$file = basename($path);
											move_uploaded_file($path, $directory . $file);
											$SQL = "INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape( 'catalog/products/' . strtolower($model).'/'.by_SEO($ocolor).'/'.$file). "', color_id='". (int)$color_value_id . "', sort_order='". (int)$rowI['SortNumber'] . "'";
											$this->db->query($SQL);
										}
									}
									$product_image_id = $this->db->getLastId();
									//$this->db->query("INSERT INTO " . DB_PREFIX . "product_image_description SET product_image_id = '" . (int)$product_image_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', description = '" . $this->db->escape($rowI['Content']) . "'");
								}
								else
								{
									$directory = DIR_IMAGE . 'catalog/products/' . strtolower($model);
									if (!is_dir($directory)) {
										mkdir($directory , 0777);
									}
									$directory.='/'.by_SEO($ocolor);
									if (!is_dir($directory)) {
										mkdir($directory , 0777);
									}
								}
								
								
								$osort++;
							}
						}
					} 
					else if($check=='A/W/R')
					{ 
					//
					}
					else if(by_SEO($check)=='url')
					{
						$sql = "DELETE FROM `".DB_PREFIX."seo_url` WHERE `key`='product_id' AND `key`='".(int)$product_id. "' ;";
						$this->db->query( $sql );
						foreach ($languages as $language) {
							$language_id = $language['language_id'];
							$language_code = $language['code'];
							$lCode = explode('-',$language['code']);
							
							if(isset($ROW['meta-title-'.$lCode[0]])){
								$keyword = by_SEO($ROW['meta-title-'.$lCode[0]]);
								if ($keyword != '') {
									$SQL  = "INSERT INTO `".DB_PREFIX."seo_url` (`store_id`, `language_id`, `key`, `value`, `keyword`) VALUES ('$store_id', '$language_id', 'product_id', '$product_id', '".$this->db->escape($keyword)."');";
									//$this->log->write($SQL);
								$this->db->query($SQL);
								}
							}
						}
					
					}
					else 
					{
						$pTitle = explode('(',$check);
						if(isset($pTitle[1]))
						{
							//$_pTitle = explode(')',$pTitle[1]);
							$check = strpos($check, ')')?$check:$check.')';
							$_pTitle = str_replace(')','',$pTitle[1]);
							//if(isset($_pTitle[1]))
							//{
								$LC = 'en';
								if($_pTitle){
									$LC =  $_pTitle;
								}else{
									$lCode = explode('_', $_pTitle);
									$LC = isset($lCode[1])?$lCode[0]:$LC;
								}
								
								$language_id = $lang[$LC];
								
								$VAR = $this->TITLES[str_replace(' (','(',$check)]['code'];
								if(isset($ROW[by_SEO($pTitle[0]).'-'.$LC]))
								{
									$VAL = $ROW[by_SEO($pTitle[0]).'-'.$LC];	
									$SQL = "SELECT * FROM `".DB_PREFIX."product_description` WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id. "'";
									$DEscr = $this->db->query($SQL);
									//$this->log->write('Array:'.print_r($DEscr,TRUE));
									if(isset($DEscr->rows)&&$DEscr->rows){
										//$this->log->write($product_id.'--'.$VAR.' Update:'.print_r($VAL,TRUE));
										$SQL = "UPDATE `".DB_PREFIX."product_description` SET ".$VAR."='".$this->db->escape($VAL)."' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id. "'";
										$this->db->query($SQL);
									}
									else
									{
										//$this->log->write($VAR.' Insert:'.print_r($VAL,TRUE));
										$SQL = "INSERT INTO `".DB_PREFIX."product_description` SET ".$VAR."='".$this->db->escape($VAL)."', product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id. "'";
										$this->db->query($SQL);
									}
								}
								else
								{
									$this->log->write('yok:'.$ROW[by_SEO($VAR)]);
									//$this->log->write($check.':'.print_r($ROW,TRUE));
								}
							//}
						}
						else
						{
							$VAR = $this->TITLES[$check]['code'];
							if(isset($ROW[$VAR])|| isset($ROW[by_SEO($VAR)]))
							{
								switch($VAR){
									case 'text': 
										$VAL = isset($ROW[$VAR])?$ROW[$VAR]:(isset($ROW[by_SEO($VAR)])?$ROW[by_SEO($VAR)]:'');
										break;
									default:
										$VAL = isset($ROW[$VAR])?$ROW[$VAR]:(isset($ROW[by_SEO($VAR)])?$ROW[by_SEO($VAR)]:0);
								}	
								$SQL = "UPDATE `".DB_PREFIX."product` SET ".$VAR."='".$VAL."' WHERE product_id='$product_id'";
								//$this->log->write('$SQL:'.$SQL);
								$this->db->query($SQL);
							}
							else
							{
								//$this->log->write($VAR.':'.print_r($ROW,TRUE));
							}
						}	
					}
				}
				
			}
			else
			{
				$isSub = TRUE;
			}
		}
	}
	
	private function getCellParts(array $cells,int $sorder,string $col):array {
		$return = [];
		$return['colors']=[];
		$cont = FALSE;
		if(isset($cells[$sorder][$col])&& trim($cells[$sorder][$col])!=''){
			
			$return['colors'][$cells[$sorder][$col]] = $cells[$sorder][$col];
			
		}
		
		while( $cont==FALSE){
			$sorder++;
			
			if(!isset($cells[$sorder]['product-id']))
			{
				$cont = TRUE;
				
			}
			else if($cells[$sorder]['product-id']!='')
			{
				$cont = TRUE;
			}
			else {
				
				$return['colors'][$cells[$sorder][$col]] = $cells[$sorder][$col];
			}
			
		}
		
		
		return $return;
	}
	
	private function addDefaultProduct(int $product_id =0 ):int {
		$store_id = isset($this->session->data['store_id'])?$this->session->data['store_id']:0;
		$product_id = !$product_id ? $this->getMaxProductId():$product_id;
		
		$SQL = "INSERT INTO `" . DB_PREFIX . "product` SET `product_id` = '" . (int)$product_id . "',`quantity` = '10', `minimum` = '1', `subtract` = '0', `stock_status_id` = '6', `shipping` = '1', `weight_class_id` = '1', `length_class_id` = '1',`tax_class_id` = '9', `status` = '1',`date_added` = NOW(), `date_modified` = NOW(), `version` ='1'";
		$this->db->query($SQL);
		$this->log->write('SQL:'.$SQL);
		
		$SQL = "INSERT INTO `" . DB_PREFIX . "product_to_store` SET `product_id` = '" . (int)$product_id . "', `store_id` = '" . (int)$store_id . "'";
		$this->db->query($SQL);
		
		return $product_id;
	}
	
}

?>