<?php
namespace Opencart\Catalog\Controller\Bytao;



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Bymail extends \Opencart\System\Engine\Controller {
	
	public function index():void {
		$this->response->setOutput($this->load->view('error/not_found', $data));
	}
	
	public function sendMail($to, $subject, $message) { $mail = new PHPMailer(true); try { 
		$mail->isSMTP(); 
		$mail->Host = 'smtp.example.com'; 
		// SMTP sunucusunu belirtin 
		$mail->SMTPAuth = true; $mail->Username = 'sizin_mail_adresiniz@example.com'; 
		// SMTP kullanıcı adı 
		$mail->Password = 'sifre'; 
		// SMTP şifresi 
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
		$mail->Port = 587; 
		// Alıcılar 
		$mail->setFrom('sizin_mail_adresiniz@example.com', 'Gonderen Adi'); 
		$mail->addAddress($to); 
		// İçerik 
		$mail->isHTML(true); 
		$mail->Subject = $subject; $mail->Body = $message; 
		$mail->send(); 
		return 'E-posta başarıyla gönderildi.'; 
	} catch (Exception $e) { 
		return 'E-posta gönderimi başarısız: ' . $mail->ErrorInfo; } 
	} 
}