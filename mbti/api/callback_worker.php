<?php
/**
 * 回调处理脚本
 * 由测试完成后自动调用，或通过定时任务触发
 */

require_once __DIR__ . '/../business/db.php';

echo "开始处理回调...\n";

// 获取待回调的记录
$pdo = getDB();
$stmt = $pdo->query("
    SELECT ac.id, ac.test_id, ac.callback_url, ac.retry_count, at.test_token, at.external_user_id, at.mbti_type, at.test_data, at.company_id
    FROM api_callbacks ac
    JOIN api_tests at ON ac.test_id = at.id
    WHERE ac.status = 'pending' AND ac.retry_count < 3
    LIMIT 10
");
$callbacks = $stmt->fetchAll();

if (empty($callbacks)) {
    echo "没有待处理的回调\n";
    exit;
}

foreach ($callbacks as $callback) {
    echo "处理回调 ID: {$callback['id']}, URL: {$callback['callback_url']}\n";

    // 准备回调数据
    $callbackData = [
        'external_user_id' => $callback['external_user_id'],
        'test_token' => $callback['test_token'],
        'mbti_type' => $callback['mbti_type'],
        'test_data' => $callback['test_data'],
        'timestamp' => time()
    ];

    // 发送回调
    $ch = curl_init($callback['callback_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callbackData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // 更新回调状态
    $stmt = $pdo->prepare("
        UPDATE api_callbacks
        SET response_status = ?, response_body = ?, retry_count = retry_count + 1,
            status = CASE WHEN ? >= 200 AND ? < 300 THEN 'success' ELSE 'failed' END,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$httpCode, $response ?: $curlError, $httpCode, $httpCode, $callback['id']]);

    echo "回调完成, HTTP状态码: {$httpCode}\n";
}

echo "回调处理完成\n";
