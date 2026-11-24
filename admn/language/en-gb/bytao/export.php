<?php
// Heading
$_['heading_title']          = 'Inport Export';

// Text
$_['text_order_titles']           = 'Order Id:12,Invoice No:12,Date Added:16,Store Name:20,e-mail:30,status:30,Telephone:16,Name:30,Country:20,State:20,Shipping Address:90,Product:60,Total:40,Tax:40,Comment:40,Gender:40,Products:40,Categories:100';

$_['text_product_titles']           = 'Product Id:12,Model Cod:16,Option:20,Value:16,Stock:9,Price:16,Sale Price:16,Whole Sale Price:16,Categories :30,Name(en):40,SEO url(en):40,Description(en):100,Meta Title(en):100,Meta Description(en):100,Meta Key word(en):100,Tags(en):100,Material:40,Image Link:140';

$_['text_products']                          = 'products';
$_['text_orders']                          = 'orders';
$_['text_references']                          = 'References';
$_['text_category_id']                          = 'Category ID';
$_['text_category_name']                          = 'Category Name';
$_['text_last_product_id']                          = 'Last product ID';

$_['text_success']                          = 'Success: Export done!';
$_['text_success_settings']                 = 'Başarılı: Export / Import aracının ayarlarını başarıyla güncellediniz!';
$_['text_export_type_category']             = 'Kategoriler (kategori verileri ve filtreler dahil)';
$_['text_export_type_category_old']         = 'Kategoriler';
$_['text_export_type_product']              = 'Ürünler (ürün verileri, ek resimler, seçenekler,seçenek değerleri)';
$_['text_export_type_product_price']        = 'Fiyatlar (Ürün id, ürün adı, ürün model, Fiyat bilgileri )';
$_['text_export_type_product_old']          = 'Ürünler (ürün verileri, seçenekler, özel ürünler, indirimler, ödüller ve özellikler dahil)';
$_['text_export_type_option']               = 'Seçenek tanımları';
$_['text_export_type_attribute']            = 'Öznitelik tanımları';
$_['text_export_type_filter']               = 'Filtre tanımları';
$_['text_export_type_customer']             = 'Müşteriler';
$_['text_yes']                              = 'Evet';
$_['text_no']                               = 'Hayır';
$_['text_nochange']                         = 'Sunucuda herhangi bir veri değişmedi.';
$_['text_log_details']                      = 'See also \'System &gt; Error Logs\' for more details.';
$_['text_log_details_2_0_x']                = 'See also \'Tools &gt; Error Logs\' for more details.';
$_['text_log_details_2_1_x']                = 'See also \'System &gt; Tools &gt; Error Logs\' for more details.';
$_['text_loading_notifications']            = 'Gelen Mesajlar';
$_['text_retry']                            = 'Tekrarla';
$_['text_used_category_ids']                = 'Currently used category IDs are between %1 and %2.';
$_['text_used_product_ids']                 = 'Currently used product IDs are between %1 and %2.';

$_['entry_import']                          = 'Import from a XLS, XLSX or ODS spreadsheet file';
$_['entry_export']                          = 'Export requested data to a XLSX spreadsheet file.';

$_['button_import']                         = 'Import';
$_['button_export']                         = 'Export';

$_['error_permission']                      = 'Uyarı: Verme / Dışa Aktarma işlemlerini değiştirme izniniz yok!';
$_['error_upload']                          = 'Yüklenen elektronik tablo dosyasının doğrulama hataları var!';
$_['error_worksheets']                      = 'Dışa Aktar / İçe Aktar: Geçersiz çalışma sayfası adları';

$_['text_success']      = 'Success: You have modified downloads!';
$_['text_list']         = 'Download List';
$_['text_add']          = 'Add Download';
$_['text_edit']         = 'Edit Download';
$_['text_upload']       = 'Your file was successfully uploaded!';
$_['text_report']       = 'Report';

// Column
$_['column_name']       = 'Download Name';
$_['column_ip']         = 'IP';
$_['column_account']    = 'Accounts';
$_['column_store']      = 'Store';
$_['column_country']    = 'Country';
$_['column_date_added'] = 'Date Added';
$_['column_action']     = 'Action';

// Entry
$_['entry_name']        = 'Download Name';
$_['entry_filename']    = 'Filename';
$_['entry_mask']        = 'Mask';

// Help
$_['help_filename']     = 'You can upload via the upload button or use FTP to upload to the download directory and enter the details below.';
$_['help_mask']         = 'It is recommended that the filename and the mask are different to stop people trying to directly link to your downloads.';

// Error
$_['error_warning']     = 'Warning: Please check the form carefully for errors!';
$_['error_permission']  = 'Warning: You do not have permission to modify downloads!';
$_['error_name']        = 'Download Name must be between 3 and 64 characters!';
$_['error_filename']    = 'Filename must be between 3 and 128 characters!';
$_['error_directory']   = 'Downloads need to be within the storage/download directory!';
$_['error_exists']      = 'File does not exist!';
$_['error_mask']        = 'Mask must be between 3 and 128 characters!';
$_['error_file_type']   = 'Invalid file type!';
$_['error_product']     = 'Warning: This download cannot be deleted as it is currently assigned to %s products!';