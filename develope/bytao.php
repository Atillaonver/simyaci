<?php
if (!function_exists('by_ADM')) {
    function by_ADM($value = null) {
        static $by_ADM = ""; 
        if ($value !== null) {
            $by_ADM = $value; 
        }
        return $by_ADM; 
    }
}

if (!function_exists('by_URL')) {
    function by_URL($value = null) {
        static $by_URL = ""; 
        if ($value !== null) {
            $by_URL = $value; 
        }
        return $by_URL; 
    }
}

function by_move(string $image)
{
	if($image)
	{
		if(!is_file(DIR_IMAGE . $image))
		{
			$image = str_replace('https://www.simyaci.tr/','',$image);
			//fwrite(fopen(DIR_LOGS . 'error.log', 'a')," MG:-" . (print_r(DIR_IMAGE.$image,TRUE)) . " \n");
			if(is_file(DIR_ADMIMAGE . $image))
			{
				
				$path        = '';
				$directories = explode('/', dirname(str_replace('../', '', $image )));
				foreach($directories as $directory)
				{
					if($path == '')
					{
						$path = $directory;
					}
					else
					{
						$path = $path . '/' . $directory;
					}
					if(!is_dir(DIR_IMAGE . $path))
					{
						@mkdir(DIR_IMAGE . $path, 0777);
					}
				}
				
				if(!copy(DIR_ADMIMAGE.$image, DIR_IMAGE.$image)){
					fwrite(fopen(DIR_LOGS . 'error.log', 'a'), "Resim Kopyalanamadı:-" . (print_r(DIR_ADMIMAGE.$image,TRUE)).' -> '. (print_r(DIR_IMAGE.$image,TRUE)) . " \n");
				}
				
			}else{
				fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y-m-d G:i:s') . " - " .  " *** Adm Resim yok:-" . (print_r(DIR_ADMIMAGE.$image,TRUE)) . " \n");
			}
		} else {
			
		}
	}
	return $image;
}

function by_cdn(string $file)
{
	if($file)
	{
		if(!is_file(DIR_CDN . $file))
		{
			if(is_file(DIR_CDNALL . $file))
			{
				$path        = '';
				$directories = explode('/', dirname(str_replace('../', '', $file )));
				foreach($directories as $directory)
				{
					if($path == '')
					{
						$path = $directory;
					}
					else
					{
						$path = $path . '/' . $directory;
					}
					if(!is_dir(DIR_CDN . $path))
					{
						@mkdir(DIR_CDN . $path, 0777);
					}
				}
				copy(DIR_CDNALL.$file, DIR_CDN.$file);
			}else{
				fwrite(fopen(DIR_LOGS . 'error.log', 'a'), date('Y-m-d G:i:s') . " - " .  " *** Adm Dosya yok:-" . (print_r(DIR_CDNALL.$file,TRUE)) . " \n");
			}
		}
	}
	return $file;
}

function css_process( string $content , string $route ){
	$content = str_replace('IMAGEROUTE', $route , $content);
	$content = str_replace('CDNROUTE', $route , $content);
	$content = str_replace('CDN_ROUTE', $route , $content);
	$content = str_replace('HTTP_SERVER', $route , $content);
	$content = str_replace('HTTP_ASSETS', $route.'assets/' , $content);
	$content = str_replace('HTTP_IMAGE', $route , $content);
	$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
	$content = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], ' ', $content);
	$content = str_replace(["&amp;", "&gt;","&quot;"], ["&", ">",'"'], $content);
	$content = preg_replace('/[ ]+([{};,:])/', '\1', $content);
	$content = preg_replace('/([{};,:])[ ]+/', '\1', $content);
	$content = preg_replace('/(\}([^\}]*\{\})+)/', '}', $content);
	$content = preg_replace('/<\?(.*?)\?>/mix', '', $content);
	
    return $content;	
}

function by_toRoute( string $content , string $route,$to = false ){
    if($to){
        $content = str_replace($route ,'URL_IMAGE',  $content);
    }else{
        $content = str_replace('URL_IMAGE', $route , $content);
    }
    return $content;	
}

function by_SEO (string $string, string $sub ="-"){
	
	$tr = ["ş","Ş","ı","ü","Ü","ö","Ö","ç","Ç","ş","Ş","ı","ğ","Ğ","İ","ö","Ö","Ç","ç","ü","Ü"];
	$TR = ["s","S","i","u","U","o","O","c","C","s","S","i","g","G","I","o","O","C","c","u","U"];
	$string = trim($string);
	$string = str_replace($tr,$TR,$string);
	$string = preg_replace("@[^a-z0-9\-_şıüğçİŞĞÜÇ]+@i",$sub,$string);
    $string = strtolower($string);
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    $string = preg_replace("/[\s-]+/", " ", $string);
    $string = preg_replace("/[\s_]/", $sub, $string);
    if(substr($string, -1)=="-"){
		$string = substr_replace($string ,"", -1);
	}
    return $string;
}

function by_QRLink(string $url, int $size = 140,string $descriptionLong = '',string $heading_title='', string $errorCorrectionLevel = 'H', string $margin = '0', string $encoding='UTF-8')
{
	$QR  = '<span>';
	$QR .='<img longdesc="'.$descriptionLong.'" crossorigin="anonymous" style="width:'.$size.'px;height:' . $size .'px;" src="'; 
	$QR .='https://chart.googleapis.com/chart?choe='.$encoding.'&chs='.$size.'x'.$size.'&cht=qr&chld='.$errorCorrectionLevel.'|'.$margin.'&chl='.urlencode($url).'" alt="' . $heading_title . '" ></span>';
	return $QR;
}

function icnv (string $string) {
	return iconv('UTF-8','ASCII//TRANSLIT',str_replace(['–'], '-', $string));
}

function byToken($length = 32) {
return bin2hex(random_bytes($length));
}



function by_text_move(string $text, bool $to_placeholder = true, $route = '') {
	if (empty($text)) return $text;

	if ($to_placeholder) {
		// --- KAYDETME / TAŞIMA MODU ---
		// 1. Dış kaynaklı (simyaci.tr) linkleri yakala
		$pattern_remote = '/(src|href)=["\']https?:\/\/(www\.)?simyaci\.tr\/image\/([^"\']+)["\']/';
		$text = preg_replace_callback($pattern_remote, function($matches) {
			$attr = $matches[1]; // src veya href
			$relative_path = $matches[3];
			
			// Resmi alt siteye kopyala (Hızır Servis)
			by_move($relative_path);
			
			// Veritabanına |IMGROUTE| ile kaydet
			return $attr . '="|IMGROUTE|' . $relative_path . '"';
		}, $text);

		// 2. Yerel tam yolları yakala (URL_IMAGE içeren)
		if (defined('URL_IMAGE')) {
			$local_url = preg_quote(URL_IMAGE, '/');
			$pattern_local = '/(src|href)=["\']' . $local_url . '([^"\']+)["\']/';
			$text = preg_replace($pattern_local, '$1="|IMGROUTE|$2"', $text);
		}

		// 3. Yerel rölatif yolları yakala (image/ ile başlayan)
		$pattern_relative = '/(src|href)=["\']image\/([^"\']+)["\']/';
		$text = preg_replace($pattern_relative, '$1="|IMGROUTE|$2"', $text);

		return $text;
		
	} else {
		// --- GÖSTERME / YAYINLAMA MODU ---
		// |IMGROUTE| gördüğünde yerel resim yoluna çevir
		$route = $route ?: (defined('URL_IMAGE') ? URL_IMAGE : 'image/');
		return str_replace('|IMGROUTE|', $route, $text);
	}
}
