<?php
class Mail {
    public function sendWithResend($to, $subject, $htmlBody) {
        $apiKey = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'accounts@acculynce.com';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Acculynce Accounts';
        
        $fromHeader = "$fromName <$fromEmail>";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'from' => $fromHeader,
            'to' => $to,
            'subject' => $subject,
            'html' => $htmlBody
        ]));

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log('Resend Curl Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log('Resend API Error: ' . $result);
            return false;
        }
    }
}
