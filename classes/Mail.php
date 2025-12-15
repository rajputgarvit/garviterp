<?php
class Mail {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $debug = false;
    private $secure;

    public function __construct() {
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->port = defined('SMTP_PORT') ? SMTP_PORT : 465;
        $this->user = defined('SMTP_USER') ? SMTP_USER : '';
        $this->pass = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'ssl';
    }

    public function send($to, $subject, $body, $isHtml = true) {
        if (empty($this->host) || empty($this->user) || empty($this->pass)) {
            error_log("SMTP Configuration missing");
            return false;
        }

        $socketParams = $this->secure === 'ssl' ? 'ssl://' : '';
        $server = $socketParams . $this->host;

        $socket = fsockopen($server, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Connect failed: $errstr ($errno)");
            return false;
        }

        if (!$this->serverCmd($socket, "220")) return false;

        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $hello = 'EHLO ' . $serverName;
        fputs($socket, "$hello\r\n");
        if (!$this->serverCmd($socket, "250")) {
            fputs($socket, "HELO " . $serverName . "\r\n");
            if (!$this->serverCmd($socket, "250")) return false;
        }

        fputs($socket, "AUTH LOGIN\r\n");
        if (!$this->serverCmd($socket, "334")) return false;

        fputs($socket, base64_encode($this->user) . "\r\n");
        if (!$this->serverCmd($socket, "334")) return false;

        fputs($socket, base64_encode($this->pass) . "\r\n");
        if (!$this->serverCmd($socket, "235")) return false;

        fputs($socket, "MAIL FROM: <{$this->user}>\r\n");
        if (!$this->serverCmd($socket, "250")) return false;

        fputs($socket, "RCPT TO: <$to>\r\n");
        if (!$this->serverCmd($socket, "250")) return false;

        fputs($socket, "DATA\r\n");
        if (!$this->serverCmd($socket, "354")) return false;

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8";
        $headers[] = "To: $to";
        $headers[] = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">";
        $headers[] = "Reply-To: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">";
        $headers[] = "X-Mailer: Acculynce Mailer";
        $headers[] = "Message-ID: <" . md5(uniqid(time())) . "@" . ($_SERVER['SERVER_NAME'] ?? 'acculynce.com') . ">";
        $headers[] = "Subject: $subject";
        $headers[] = "Date: " . date("r");

        fputs($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        if (!$this->serverCmd($socket, "250")) return false;

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private function serverCmd($socket, $expected) {
        $response = '';
        while (substr($response, 3, 1) != ' ') {
            if (!($response = fgets($socket, 256))) return false;
        }
        if (substr($response, 0, 3) != $expected) {
            error_log("SMTP Error: $response");
            return false;
        }
        return true;
    }
}
