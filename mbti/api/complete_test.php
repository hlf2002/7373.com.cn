<?php
/**
 * 测试完成处理
 * 由测试页面在用户完成测试后调用
 *
 * 参数 (POST):
 * - token: 测试令牌
 * - mbti_type: MBTI类型
 * - test_data: 测试详细数据(JSON)
 */

require_once __DIR__ . '/../business/db.php';

header('Content-Type: application/json; charset=utf-8');

// 验证请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 405, 'message' => '请求方法必须是 POST']);
    exit;
}

// 验证参数
if (!isset($_POST['token']) || empty($_POST['token'])) {
    echo json_encode(['code' => 400, 'message' => '缺少 token 参数']);
    exit;
}

if (!isset($_POST['mbti_type']) || empty($_POST['mbti_type'])) {
    echo json_encode(['code' => 400, 'message' => '缺少 mbti_type 参数']);
    exit;
}

$token = $_POST['token'];
$mbti_type = $_POST['mbti_type'];
$test_data = isset($_POST['test_data']) ? $_POST['test_data'] : null;

// 查询测试记录
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT at.*, ak.callback_url as api_callback_url
    FROM api_tests at
    LEFT JOIN api_keys ak ON at.access_key = ak.access_key
    WHERE at.test_token = ?
");
$stmt->execute([$token]);
$test = $stmt->fetch();

if (!$test) {
    echo json_encode(['code' => 404, 'message' => '无效的测试令牌']);
    exit;
}

if ($test['status'] === 'completed') {
    echo json_encode(['code' => 200, 'message' => '测试已完成']);
    exit;
}

// 更新测试记录
$stmt = $pdo->prepare("
    UPDATE api_tests
    SET status = 'completed',
        mbti_type = ?,
        test_data = ?,
        completed_at = NOW()
    WHERE id = ?
");
$stmt->execute([$mbti_type, $test_data, $test['id']]);

// 如果有回调URL，创建回调任务
$callback_url = $test['callback_url'] ?: $test['api_callback_url'];
if ($callback_url) {
    $stmt = $pdo->prepare("INSERT INTO api_callbacks (test_id, callback_url, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$test['id'], $callback_url]);
}

echo json_encode(['code' => 200, 'message' => 'success']);
