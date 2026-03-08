<?php
/**
 * 回调接收页面
 * 用于接收 MBTI 测试结果的回调
 */

header('Content-Type: application/json; charset=utf-8');

// 记录日志
$logDir = __DIR__ . '/callback_logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/callback_' . date('Y-m-d') . '.log';

// 获取请求数据
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);
$method = $_SERVER['REQUEST_METHOD'];

// 构建日志内容
$logEntry = "========================================\n";
$logEntry .= "时间: " . date('Y-m-d H:i:s') . "\n";
$logEntry .= "方法: " . $method . "\n";
$logEntry .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$logEntry .= "UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";

if ($method === 'POST') {
    $logEntry .= "原始数据: " . $rawInput . "\n";
    if ($jsonData) {
        $logEntry .= "解析后:\n";
        $logEntry .= "  - external_user_id: " . ($jsonData['external_user_id'] ?? 'N/A') . "\n";
        $logEntry .= "  - test_token: " . ($jsonData['test_token'] ?? 'N/A') . "\n";
        $logEntry .= "  - mbti_type: " . ($jsonData['mbti_type'] ?? 'N/A') . "\n";
        $logEntry .= "  - test_data: " . ($jsonData['test_data'] ?? 'N/A') . "\n";
        $logEntry .= "  - timestamp: " . ($jsonData['timestamp'] ?? 'N/A') . "\n";
    }
} else {
    $logEntry .= "GET参数: " . http_build_query($_GET) . "\n";
}

$logEntry .= "========================================\n\n";

// 写入日志
file_put_contents($logFile, $logEntry, FILE_APPEND);

// 同时写入数据库（可选）
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=mbti_business;charset=utf8mb4', 'mbti_user', 'Mbti2024!@#');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($method === 'POST' && $jsonData) {
        $stmt = $pdo->prepare("
            INSERT INTO callback_logs (external_user_id, test_token, mbti_type, test_data, callback_url, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $jsonData['external_user_id'] ?? '',
            $jsonData['test_token'] ?? '',
            $jsonData['mbti_type'] ?? '',
            $jsonData['test_data'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
} catch (Exception $e) {
    // 数据库记录失败不影响响应
}

// 返回响应
echo json_encode([
    'success' => true,
    'message' => '回调接收成功',
    'received_at' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
