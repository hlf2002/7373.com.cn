<?php
require_once 'db.php';

$result = sendEmail('lifenghu@qq.com', '123456');
echo $result ? 'OK' : 'FAILED';
