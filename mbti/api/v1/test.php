<?php
/**
 * 发起测试接口
 * POST /api/v1/test
 *
 * 参数 (URL):
 * - access_key: 访问密钥 (必选)
 * - external_user_id: 外部用户ID (必选)
 * - callback_url: 回调URL (可选)
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
$callback_url = isset($_GET['callback_url']) ? $_GET['callback_url'] : '';

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

// 生成测试令牌
$test_token = generateToken();

// 创建测试记录
$test_id = createTestRecord(
    $keyInfo['company_id'],
    $access_key,
    $external_user_id,
    $test_token,
    $callback_url
);

// 返回结果 - 跳转到测试页面
$test_url = 'https://mbti.7373.com.cn/testing.html?token=' . $test_token;

apiResponse(200, 'success', [
    'test_url' => $test_url,
    'test_token' => $test_token,
    'external_user_id' => $external_user_id
]);
