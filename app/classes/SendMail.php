<?php

class SendMail
{
    private $Email;
    private $ReceiverName;
    private $Subject;
    private $Content;

    private $SmtpHost;
    private $SmtpUsername;
    private $SmtpPassword;
    private $SmtpSecure;
    private $SmtpPort;
    private $SmtpSendFrom;
    private $Bcc;

    private $Err;

    public function __construct()
    {
        global $config;

        //Todo : Burayı daha sonra db den çekcek şekilde ayarla.
        //Mail Ayarlarını Çek

        $this->setSmtpHost($config["smtp_host"]);
        $this->setSmtpSecure($config["smtp_secure"]);
        $this->setSmtpUsername($config["smtp_username"]);
        $this->setSmtpPassword($config["smtp_password"]);
        $this->setSmtpPort($config["smtp_port"]);
        $this->setSmtpSendFrom($config["smtp_sendFrom"]);

    }

    /**
     * @param mixed $Bcc
     */
    public function setBcc($Bcc): void
    {
        $this->Bcc = $Bcc;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->Bcc;
    }

    /**
     * @param mixed $Email
     */
    public function setEmail($Email): void
    {
        $this->Email = $Email;
    }

    /**
     * @param mixed $ReceiverName
     */
    public function setReceiverName($ReceiverName): void
    {
        $this->ReceiverName = $ReceiverName;
    }

    /**
     * @param mixed $Subject
     */
    public function setSubject($Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * @param mixed $Content
     */
    public function setContent($Content): void
    {
        $this->Content = $Content;
    }

    /**
     * @param mixed $Err
     */
    public function setErr($Err): void
    {
        $this->Err = $Err;
    }

    /**
     * @param mixed $SmtpHost
     */
    public function setSmtpHost($SmtpHost): void
    {
        $this->SmtpHost = $SmtpHost;
    }

    /**
     * @param mixed $SmtpPassword
     */
    public function setSmtpPassword($SmtpPassword): void
    {
        $this->SmtpPassword = $SmtpPassword;
    }

    /**
     * @param mixed $SmtpPort
     */
    public function setSmtpPort($SmtpPort): void
    {
        $this->SmtpPort = $SmtpPort;
    }

    /**
     * @param mixed $SmtpSecure
     */
    public function setSmtpSecure($SmtpSecure): void
    {
        $this->SmtpSecure = $SmtpSecure;
    }

    /**
     * @param mixed $SmtpSendFrom
     */
    public function setSmtpSendFrom($SmtpSendFrom): void
    {
        $this->SmtpSendFrom = $SmtpSendFrom;
    }

    /**
     * @param mixed $SmtpUsername
     */
    public function setSmtpUsername($SmtpUsername): void
    {
        $this->SmtpUsername = $SmtpUsername;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->Content;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @return mixed
     */
    public function getReceiverName()
    {
        return $this->ReceiverName;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->Subject;
    }

    /**
     * @return mixed
     */
    public function getErr()
    {
        return $this->Err;
    }

    /**
     * @return mixed
     */
    public function getSmtpHost()
    {
        return $this->SmtpHost;
    }

    /**
     * @return mixed
     */
    public function getSmtpPassword()
    {
        return $this->SmtpPassword;
    }

    /**
     * @return mixed
     */
    public function getSmtpPort()
    {
        return $this->SmtpPort;
    }

    /**
     * @return mixed
     */
    public function getSmtpSecure()
    {
        return $this->SmtpSecure;
    }

    /**
     * @return mixed
     */
    public function getSmtpSendFrom()
    {
        return $this->SmtpSendFrom;
    }

    /**
     * @return mixed
     */
    public function getSmtpUsername()
    {
        return $this->SmtpUsername;
    }







    public function Send():bool{

        $PHPMailer = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $PHPMailer->isSMTP();                                            // Send using SMTP
            $PHPMailer->Host = $this->getSmtpHost();                    // Set the SMTP server to send through
            $PHPMailer->SMTPAuth = true;                                   // Enable SMTP authentication
            $PHPMailer->Username = $this->getSmtpUsername();                     // SMTP username
            $PHPMailer->Password = $this->getSmtpPassword();
            if ($this->getSmtpSecure() == "tls") {
                $PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            } else if ($this->getSmtpSecure() == "ssl") {
                $PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            }
            $PHPMailer->CharSet = 'UTF-8';
            $PHPMailer->SMTPAutoTLS = false; // SMTP password
            $PHPMailer->Port = $this->getSmtpPort();                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            //Recipients
            $PHPMailer->setFrom($this->getSmtpUsername(), $this->getSmtpSendFrom());
            $PHPMailer->addAddress($this->getEmail(), $this->getReceiverName());     // Add a recipient
            // Content
            $PHPMailer->isHTML(true);                                  // Set email format to HTML
            $PHPMailer->Subject = $this->getSubject();
            $PHPMailer->Body = $this->getContent();
            if ($this->getBcc())
                $PHPMailer->addBCC($this->getBcc());

            $PHPMailer->send();
            return true;
        } catch (Exception $e) {
            $this->setErr($PHPMailer->ErrorInfo);
            return false;
        }
    }


}