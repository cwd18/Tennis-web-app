<?php
namespace TennisApp;

class Email {

    protected $phpmailer;                                            // PHPMailer object
    protected $pdo;
    protected $server;
    protected $twig;


    public function __construct($email_config, $pdo, $server, $twig)
    {
        $this->phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true); // Create PHPMailer
        $this->phpmailer->isSMTP();                                  // Use SMTP
        $this->phpmailer->SMTPAuth   = true;                         // Authentication on
        $this->phpmailer->Host       = $email_config['server'];      // Server address
        $this->phpmailer->SMTPSecure = $email_config['security'];    // Type of security
        $this->phpmailer->Port       = $email_config['port'];        // Port
        $this->phpmailer->Username   = $email_config['username'];    // Username
        $this->phpmailer->Password   = $email_config['password'];    // Password
        $this->phpmailer->SMTPDebug  = $email_config['debug'];       // Debug method
        $this->phpmailer->CharSet    = 'UTF-8';                      // Character encoding
        $this->phpmailer->isHTML(true);                              // Set as HTML email
        $this->pdo = $pdo;
        $this->server = $server;
        $this->twig = $twig;
    }

    public function sendEmail($replyTo, $to, $subject, $message, $altmessage): bool
    {
        $this->phpmailer->addReplyTo($replyTo);
        $this->phpmailer->setFrom("tennisfixtures42@gmail.com");
        $this->phpmailer->addAddress($to);
        $this->phpmailer->Subject = $subject;
        $this->phpmailer->Body    = $message;  // HTML body
        $this->phpmailer->AltBody = $altmessage; // Plain text body
        $this->phpmailer->send();     
        $this->phpmailer->clearAllRecipients(); 
        return true;
    }
}