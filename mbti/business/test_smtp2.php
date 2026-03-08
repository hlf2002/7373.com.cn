<?php
$host = 'smtp.feishu.cn';
$port = 465;
$user = 'mbtisystem@7373.com.cn';
$pass = '9STsLynzXuOz3LE1';
$from = 'mbtisystem@7373.com.cn';
$to = 'lifenghu@qq.com';

$socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 30);
if (!$socket) {
    die("Connection failed: $errno - $errstr");
}

$response = fgets($socket, 515);
echo "Connected: $response\n";

fputs($socket, "EHLO $host\r\n");
while ($line = fgets($socket, 515)) {
    if (substr($line, 3, 1) == ' ') break;
}

// 使用 AUTH PLAIN
$auth = base64_encode("\0$user\0$pass");
fputs($socket, "AUTH PLAIN $auth\r\n");

$response = fgets($socket, 515);
echo "Auth: $response\n";

if (substr($response, 0, 3) != '235') {
    die("Auth failed!");
}

fputs($socket, "MAIL FROM:<$from>\r\n");
fgets($socket, 515);

fputs($socket, "RCPT TO:<$to>\r\n");
fgets($socket, 515);

fputs($socket, "DATA\r\n");
fgets($socket, 515);

$email = "From: $from\r\n";
$email .= "To: $to\r\n";
$email .= "Subject: =?UTF-8?B?" . base64_encode('测试邮件') . "?=\r\n";
$email .= "Content-Type: text/html; charset=UTF-8\r\n";
$email .= "\r\n";
$email .= "<html><body><h1>测试成功</h1></body></html>\r\n";
$email .= ".\r\n";

fputs($socket, $email);
$response = fgets($socket, 515);
echo "Message: $response\n";

fputs($socket, "QUIT\r\n");
fclose($socket);

echo "Done!\n";
