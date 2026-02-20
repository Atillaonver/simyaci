<?php
if (isset($_GET['url'])) {
    $url = urlencode($_GET['url']);
    $size = isset($_GET['size'])?urlencode($_GET['size']):'140';
    
    $qrCodeUrl = "https://chart.googleapis.com/chart?chs=$sizex$size&cht=qr&chl=$url";

    // Google'dan QR kodunu çek
    $qrCodeImage = file_get_contents($qrCodeUrl);

    // Doğru başlıkları ayarla ve görseli göster
    header('Content-Type: image/png');
    echo $qrCodeImage;
    exit;
}

?>