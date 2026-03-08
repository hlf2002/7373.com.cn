<?php
$host = 'smtp.feishu.cn';
$port = 465;
$errno = '';
$errstr = '';

echo "Trying to connect to $host:$port...\n";

$socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 30);

if (!$socket) {
    echo "FAILED: $errno - $errstr\n";
    exit;
}

echo "Connected!\n";

$response = fgets($socket, 515);
echo "Response: $response\n";

if (substr($response, 0, 3) != '220') {
    echo "Expected 220, got: $response\n";
    fclose($socket);
    exit;
}

// EHLO
fputs($socket, "EHLO $host\r\n");
echo "Sent EHLO\n";

$lines = '';
while ($line = fgets($socket, 515)) {
    $lines .= $line;
    if (substr($line, 3, 1) == ' ') break;
}
echo "EHLO Response: $lines\n";

// AUTH LOGIN
fputs($socket, "AUTH LOGIN\r\n");
echo "Sent AUTH LOGIN\n";

$response = fgets($socket, 515);
echo "Response: $response\n";

fputs($socket, base64_encode('mbtisystem@7373.com.cn') . "\r\n");
echo "Sent username\n";

$response = fgets($socket, 515);
echo "Response: $response\n";

fputs($socket, base64_encode('9STsLynzXuOz3LE1') . "\r\n");
echo "Sent password\n";

$response = fgets($socket, 515);
echo "Response: $response\n";

if (substr($response, 0, 3) != '235') {
    echo "Auth failed!\n";
    fclose($socket);
    exit;
}

echo "Auth OK!\n";

// MAIL FROM
fputs($socket, "MAIL FROM:<mbtisystem@7373.com.cn>\r\n");
$response = fgets($socket, 515);
echo "MAIL FROM: $response\n";

// RCPT TO
fputs($socket, "RCPT TO:<lifenghu@qq.com>\r\n");
$response = fgets($socket, 515);
echo "RCPT TO: $response\n";

// DATA
fputs($socket, "DATA\r\n");
$response = fgets($socket, 515);
echo "DATA: $response\n";

$email = "From: mbtisystem@7373.com.cn\r\n";
$email .= "To: lifenghu@qq.com\r\n";
$email .= "Subject: Test\r\n";
$email .= "\r\n";
$email .= "Test email body\r\n";
$email .= ".\r\n";

fputs($socket, $email);
$response = fgets($socket, 515);
echo "Message: $response\n";

fputs($socket, "QUIT\r\n");
fclose($socket);

echo "Done!\n";
