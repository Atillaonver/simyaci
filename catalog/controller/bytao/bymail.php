<?php
namespace Opencart\Catalog\Controller\Bytao;
class Bymail extends \Opencart\System\Engine\Controller {
	
	public $O = ['SUBJECT'=>'','TO'=>'who','HOST'=>'localhost','UNAME'=>'','PSSWRD'=>'','PORT'=>'587','BODY'=>'','FROM'=>'kimden mail','FROMN'=>'kimden isim'];
	
	public function index():void {
		$this->response->setOutput('');
	}
	
	public function sendMail(array $data):string { 
		
		$this->setTO('SUBJECT',$data['subject']);
		$this->setTO('UNAME',$this->config->get('config_mail_smtp_username'));
		$this->setTO('HOST',$this->config->get('config_mail_smtp_hostname'));
		$this->setTO('TO',$data['to']);
		$this->setTO('FROM',$data['from']);
		$this->setTO('FROMN',$data['from_name']);
		$this->setTO('BODY',$data['message']);
		$this->setTO('PORT',$this->config->get('config_mail_smtp_port'));
		$this->setTO('PSSWRD',html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8'));
		
			
		$hostname = $this->O['FROMN'];
		$hostnamearray = explode('.', $hostname);
		$hostname = $hostnamearray[0];
		
		$from = $this->O['FROM'];
	    $toemail = $this->O['TO'];
	    $subject = $this->O['SUBJECT'];
	    $message = $this->O['BODY'];
	    $engine = $this->config->get('config_mail_engine')?$this->config->get('config_mail_engine'):'';
	    
	    $headers  = "MIME-Version: 1.0\r\n"; 
		$headers .= "From: $from\r\n"; 
		$headers .= "X-Mailer: PHP/" . phpversion() ."\r\n";
		$headers .= "Content-type: text/html\r\n";
		$headers .= "X-Priority: 3\r\n"; 
		$headers .= "Priority: Urgent\r\n";
		$headers .= "Importance: High\r\n"; 
		$headers .= "X-MSMail-Priority: High\r\n"; 
		$result = mail($toemail, html_entity_decode($subject, ENT_QUOTES, 'UTF-8'), $message, $headers );
	    
	   
	        
	    //    $result = mail($toemail, $subject, $message, "From: $from" );
	        if ( $result ) {
	            return '';
	        } else {
	            return 'FAIL';
	        }
	    /*
	     if ( $engine== "mail" ) {
	    } 
	    elseif ( $engine == "smtp" ) 
	    {
	    	$this->log->write('engine:'.$engine);
	        ob_start(); //start capturing output buffer because we want to change output to html
			
	        $mail = new  Opencart\Catalog\Controller\Bytao\PHPMailer;

	        $mail->SMTPDebug = 2;
	        $mail->IsSMTP();
	        //$mail->Host = 'relay-hosting.secureserver.net';
	        $mail->Host = $this->O['HOST'];
	        
	        $mail->SMTPAuth = false;

	        $mail->From = $from;
	        $mail->FromName = $this->O['FROMN'];
	        $mail->AddAddress($toemail);

	        $mail->Subject = $subject;
	        $mail->Body = $message;

	        $mailresult = $mail->Send();
	        $mailconversation = nl2br(htmlspecialchars(ob_get_clean())); //captures the output of PHPMailer and htmlizes it
	        if ( !$mailresult ) {
	            return 'FAIL: ' . $mail->ErrorInfo . '<br />' . $mailconversation;
	        } else {
	            return $mailconversation;
	        }
	    } 
	    elseif ( $engine == "sendmail" ) 
	    {
	        $cmd = "cat - << EOF | /usr/sbin/sendmail -t 2>&1\nto:$toemail\nfrom:$from\nsubject:$subject\n\n$message\n\nEOF\n";
	        $mailresult = shell_exec($cmd);
	        if ( $mailresult == '' ) { //A blank result is usually successful
	            return '';
	        } else {
	            return "The sendmail command returned what appears to be an error: " . $mailresult . "<br />\n<br />";
	        }
	    } 
	    else 
	    {
	        return 'FAIL (Invalid sendmethod variable in POST data)';
	    }
	    */
	} 
	
	public function setTO($set, $to){
		$this->O[$set] = $to ;
	}
	
}