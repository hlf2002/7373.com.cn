<?php
/**
 * 查询测试结果接口
 * GET /api/v1/query
 *
 * 参数 (URL):
 * - access_key: 访问密钥 (必选)
 * - external_user_id: 外部用户ID (必选)
 * - sign: 签名 (必选)
 */

require_once __DIR__ . '/../business/db.php';
require_once __DIR__ . '/../business/api_common.php';

// 验证请求方法
requireMethod('GET');

// 验证必需参数
requireParams(['access_key', 'external_user_id', 'sign']);

// 获取参数
$access_key = $_GET['access_key'];
$external_user_id = $_GET['external_user_id'];

// 验证签名
$keyInfo = verifyAccessKey($access_key);
if (!$keyInfo) {
    apiResponse(401, '无效的 access_key 或密钥已禁用');
}

if (isset($keyInfo['error'])) {
    apiResponse(429, $keyInfo['error']);
}

if (!verifySign($_GET, $keyInfo['secret_key'])) {
    apiResponse(403, '签名验证失败');
}

// 查询测试结果
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT id, external_user_id, test_token, status, mbti_type, test_data, completed_at
    FROM api_tests
    WHERE company_id = ? AND external_user_id = ? AND status = 'completed'
    ORDER BY completed_at DESC
    LIMIT 1
");
$stmt->execute([$keyInfo['company_id'], $external_user_id]);
$result = $stmt->fetch();

if (!$result) {
    apiResponse(404, '未找到该用户的测试记录或测试未完成');
}

// 解析测试数据
$test_data = $result['test_data'] ? json_decode($result['test_data'], true) : null;

// 返回结果
apiResponse(200, 'success', [
    'external_user_id' => $result['external_user_id'],
    'mbti_type' => $result['mbti_type'],
    'completed_at' => $result['completed_at'],
    'test_data' => $test_data
]);
