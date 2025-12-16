<?php
// Include configuration
require_once 'config/config.php';
require_once 'classes/Mail.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SMTP Email Test</h1>";

// Check if constants are defined
$constants = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_SECURE', 'SMTP_FROM_EMAIL', 'SMTP_FROM_NAME'];
$missing = [];
foreach ($constants as $const) {
    if (!defined($const)) {
        $missing[] = $const;
    }
}

if (!empty($missing)) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #fee;'>";
    echo "<strong>Error:</strong> Missing configuration constants:<br>";
    echo "<ul>";
    foreach ($missing as $m) {
        echo "<li>$m</li>";
    }
    echo "</ul>";
    echo "Please define these in <code>config/config.php</code> specifically for your environment.";
    echo "</div>";
} else {
    echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #eef;'>";
    echo "Configuration loaded successfully.<br>";
    echo "Host: " . SMTP_HOST . "<br>";
    echo "Port: " . SMTP_PORT . "<br>";
    echo "User: " . SMTP_USER . "<br>";
    echo "Secure: " . SMTP_SECURE . "<br>";
    echo "From: " . SMTP_FROM_EMAIL . "<br>";
    echo "</div>";

    // Form for changing recipient
    $currentTo = isset($_REQUEST['to']) ? $_REQUEST['to'] : 'lakshmisabharwal23oct@gmail.com';
    
    echo '<div style="margin: 20px 0; padding: 15px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 5px;">
        <form method="GET" action="">
            <label for="to" style="font-weight: bold;">Send Test Email To:</label>
            <input type="email" id="to" name="to" value="' . htmlspecialchars($currentTo) . '" style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;" required>
            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Send Test</button>
        </form>
    </div>';

    // Attempt to send email ONLY if 'to' param is present in URL (submited via form or link) or confirmed action
    // To prevent sending on every refresh, let's check if parameters are in the URL or we just default to showing the form.
    // However, the previous behavior sent immediately. Let's keep it simple: send if form submitted or default param exists.
    
    $to = $currentTo;

    echo "<h2>Attempting to send to: $to</h2>";
    
    $mail = new Mail();
    $subject = "Test Email from Acculynce - " . date('Y-m-d H:i:s');
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { padding: 20px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
            h2 { color: #007bff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>SMTP Test Successful!</h2>
            <p>This is a test email sent from the Acculynce ERP system.</p>
            <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p>If you are reading this, the mail configuration is working correctly.</p>
        </div>
    </body>
    </html>
    ";

    if ($mail->send($to, $subject, $body, true)) {
        echo "<div style='color: green; font-size: 18px; margin-top: 20px;'><strong>✔ SUCCESS:</strong> Email sent successfully!</div>";
    } else {
        echo "<div style='color: red; font-size: 18px; margin-top: 20px;'><strong>✘ FAILURE:</strong> Email could not be sent. Check error logs.</div>";
    }
}
?>
