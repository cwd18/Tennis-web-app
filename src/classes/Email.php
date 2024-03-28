<?php
namespace TennisApp;

class Email {
    private $phpmailer;
    private $email_config;

    public function __construct($email_config)
    {
        $this->phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true); 
        $this->phpmailer->isSMTP();    // Use SMTP
        $this->phpmailer->SMTPAuth   = true; // Authentication on
        $this->phpmailer->CharSet    = 'UTF-8'; // Character encoding
        $this->phpmailer->isHTML(true); // Set as HTML email
        $this->email_config = $email_config;
    }

    private function setSmtp($config)
    {
        $this->phpmailer->Host = $this->email_config[$config]['server']; // Server address
        $this->phpmailer->SMTPSecure = $this->email_config[$config]['security']; // Type of security
        $this->phpmailer->Port = $this->email_config[$config]['port']; 
        $this->phpmailer->Username = $this->email_config[$config]['username']; 
        $this->phpmailer->Password = $this->email_config[$config]['password']; 
        $this->phpmailer->SMTPDebug = $this->email_config[$config]['debug']; // Debug method
        $this->phpmailer->setFrom($this->phpmailer->Username);
    }
    
    public function sendEmail($replyTo, $to, $subject, $message, $altmessage): bool
    {
        if (strcmp($replyTo, 'charles.davies18@gmail.com') == 0) {
            $config = 'charles.davies18';
        } else {
            $config = 'tennisfixtures42';
        }
        $this->setSmtp($config);
        $this->phpmailer->addReplyTo($replyTo);
        $this->phpmailer->addAddress($to);
        $this->phpmailer->Subject = $subject;
        $this->phpmailer->Body = $message;  // HTML body
        $this->phpmailer->AltBody = $altmessage; // Plain text body
        $this->phpmailer->send();     
        $this->phpmailer->clearAllRecipients(); 
        return true;
    }
}