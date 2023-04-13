<?php

namespace corsica\framework\utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use corsica\framework\config\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
ini_set("pcre.jit", "0");
class Email
{
    //CONFIGURACIÓN
    private $myphpmailer = null;
    private $logger = null;
    
    public function __construct()
    {
        $this->myphpmailer = new PHPMailer();
        $this->myphpmailer->IsSMTP(); // telling the class to use SMTP
        $this->myphpmailer->SMTPAuth   = true;                  // enable SMTP authentication
        $this->myphpmailer->SMTPSecure = 'tls';
        //$this->myphpmailer->SMTPSecure = 'ssl';
        $this->myphpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        
        $mailconf = Config::getMail();
        $this->myphpmailer->CharSet = "UTF-8";
        $this->myphpmailer->Host       = $mailconf->smtp; // sets the SMTP server
        $this->myphpmailer->Username   = $mailconf->user; // SMTP account username
        $this->myphpmailer->Password   = $mailconf->password;
        $this->myphpmailer->SMTPDebug = 0;//4 full

        //logger
        $this->logger  = new Logger('email');
        $logpath = Config::getLogPath('email');
        $loglevel = Config::getLogLevel('email');
        $this->logger->pushHandler(new StreamHandler($logpath, $loglevel));

    }

    public function notificar( $to, $subject, $body)
    {
        $mailconf = Config::getMail(); 
        $this->myphpmailer->SetFrom($mailconf->user, $mailconf->name);
        //$this->myphpmailer->AddReplyTo($mailconf->user, $mailconf->name);
        if (is_array($to)) {
            foreach($to as $t) {
                $this->myphpmailer->addAddress($t);
            }
        } else {
             $this->myphpmailer->addAddress($to);
        }

        $this->myphpmailer->Subject  = $subject;
        $this->myphpmailer->Body     = $body;
        $this->myphpmailer->isHTML(true);   
        
        if (!$this->myphpmailer->send()) {
            return (object) array("error",$this->myphpmailer->ErrorInfo);
        } else {
            return 0;
        }
    }

    public function send($emailfrom,$namefrom, $to, $subject, $body, $filename = false)
    {
        $config = new Config(); 
        $mailconf = Config::getMail();       
         $respuesta = array( "to" => $to, "subject" => $subject, "body" => $body, "filename" => $filename);
        $respuesta['rutaarchivo'] = $config->getParam("app", "cotiza-path") . $filename;


        
        $this->myphpmailer->SetFrom($mailconf->user, $namefrom);
        $this->myphpmailer->AddReplyTo($emailfrom, $namefrom);

        if (is_array($to)) {
            foreach($to as $t) {
                $this->myphpmailer->addAddress($t);
            }
        } else {
             $this->myphpmailer->addAddress($to);
        }
        $this->myphpmailer->addCC($emailfrom);
        $this->myphpmailer->Subject  = $subject;
        $this->myphpmailer->Body     = $body;
        //$this->myphpmailer->isHTML(true);    

        if ($filename !== false)
            $this->myphpmailer->addAttachment($config->getParam("app", "cotiza-path") . $filename, $filename, 'base64', 'application/octet-stream');

        if (!$this->myphpmailer->send()) {
            $respuesta['mensaje'] = $this->myphpmailer->ErrorInfo;
        } else {
            $respuesta['mensaje'] = "Enviado OK!";
        }
        $this->logger->notice("Envío email",$respuesta);
        return $respuesta;
    }

}
