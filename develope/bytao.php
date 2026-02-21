<?php
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
		}/*
		else{
			if(!is_file(DIR_CDNALL . $file))
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
					if(!is_dir(DIR_CDNALL . $path))
					{
						@mkdir(DIR_CDNALL . $path, 0777);
					}
				}
				copy(DIR_CDN.$file, DIR_CDNALL.$file);
			}
		}*/
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

function icnv (string $string)
{
	return iconv('UTF-8','ASCII//TRANSLIT',str_replace(['–'], '-', $string));
}

function generateToken($length = 32)
{
	return bin2hex(random_bytes($length));
}

function by_QRLink(string $url, int $size = 140,string $descriptionLong = '',string $heading_title='', string $errorCorrectionLevel = 'H', string $margin = '0', string $encoding='UTF-8'){
	
		$QR  = '<span>';
		$QR .='<img longdesc="'.$descriptionLong.'" crossorigin="anonymous" style="width:'.$size.'px;height:' . $size .'px;" src="'; 
		$QR .='https://chart.googleapis.com/chart?choe='.$encoding.'&chs='.$size.'x'.$size.'&cht=qr&chld='.$errorCorrectionLevel.'|'.$margin.'&chl='.urlencode($url).'" alt="' . $heading_title . '" ></span>';
		return $QR;
	}
	
function by_text_move(string $text, bool $to_placeholder = true) {
	if (empty($text)) return $text;

	if ($to_placeholder) {
		// --- KAYDETME / TAŞIMA MODU ---
		// simyaci.tr linklerini yakala -> |IMGROUTE| çevir ve dosyayı çek
		$pattern = '/src=["\'](https:\/\/www\.simyaci\.tr\/image\/([^"\']+))["\']/';
		
		return preg_replace_callback($pattern, function($matches) {
			$relative_path = $matches[2];
			
			// Resmi alt siteye kopyala (Hızır Servis)
			by_move($relative_path);
			
			// Veritabanına |IMGROUTE| ile kaydet
			return 'src="|IMGROUTE|' . $relative_path . '"';
		}, $text);
		
	} else {
		// --- GÖSTERME / YAYINLAMA MODU ---
		// |IMGROUTE| gördüğünde yerel resim yoluna çevir
		return str_replace('|IMGROUTE|', 'image/', $text);
	}
}
