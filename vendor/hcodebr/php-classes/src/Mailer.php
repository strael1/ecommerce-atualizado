<?php  

namespace Hcode;

use Rain\Tpl;

class Mailer {
  
  const USERNAME = "contatestephp7user@gmail.com";
  const PASSWORD = "r88488263";
  const NAME_FROM = "Mixpreço";
  private $email;

  public function __construct($toAddress, $toName, $subject, $tplName, $data = array()){

	// Configurar o template do nosso email 

    
    $config = array(

      $config_email = DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "views" .DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR,

      $config_email_cache = DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "views-cache" .DIRECTORY_SEPARATOR,

      "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. $config_email,
      "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]. $config_email_cache,
      "debug"         => false, // set to false to improve the speed
    );

    Tpl::configure( $config );
  
    $tpl = new Tpl;

    foreach ($data as $key => $value) {
    	$tpl->assign($key, $value);
    }

    $html = $tpl->draw($tplName, true);


	$this->mail = new \PHPMailer;

	$this->mail->isSMTP();
	
	$this->mail->SMTPDebug = 0;
	
	$this->mail->Debugoutput = 'html';


	$this->mail->Host = 'smtp.gmail.com';
	

	$this->mail->Port = 587;
	

	$this->mail->SMTPSecure = 'tls';


	$this->mail->SMTPAuth = true;
	

	$this->mail->Username = Mailer::USERNAME;
	

	$this->mail->Password = Mailer::PASSWORD;
	

	$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
	

	$this->mail->addAddress($toAddress, $toName);
	
	$this->mail->Subject = $subject;
	
	$this->mail->msgHTML($html);
	

	$this->mail->AltBody = 'This is a plain-text message body';
	
	$this->mail->addAttachment('images/phpmailer_mini.png');
    
  }

  public function send(){

  	 return $this->mail->send(); 
  
  }
};




?>