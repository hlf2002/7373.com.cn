-- API 相关数据表结构（兼容现有表结构）

-- 1. API 密钥表（扩展现有表）
-- 注意：现有表已有字段：id, company_id, key_value, access_key, key_name, is_active, created_at, last_used_at
-- 需要添加的字段：
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS secret_key VARCHAR(128) DEFAULT NULL COMMENT '私有密钥(重命名为secret_key)' AFTER access_key;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS status TINYINT NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1正常' AFTER secret_key;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS request_limit INT NOT NULL DEFAULT 1000 COMMENT '日请求限制' AFTER status;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS request_today INT NOT NULL DEFAULT 0 COMMENT '今日请求次数' AFTER request_limit;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS last_request_date DATE DEFAULT NULL COMMENT '最后请求日期' AFTER request_today;

-- 2. 测试记录表
CREATE TABLE IF NOT EXISTS api_tests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL COMMENT '所属企业ID',
    access_key VARCHAR(128) NOT NULL COMMENT '使用的API密钥',
    external_user_id VARCHAR(128) NOT NULL COMMENT '外部用户ID',
    test_token VARCHAR(64) NOT NULL UNIQUE COMMENT '测试令牌',
    callback_url VARCHAR(512) DEFAULT NULL COMMENT '回调URL',
    status ENUM('pending', 'completed', 'expired') DEFAULT 'pending' COMMENT '测试状态',
    mbti_type VARCHAR(4) DEFAULT NULL COMMENT 'MBTI类型',
    test_data JSON DEFAULT NULL COMMENT '测试详细数据',
    completed_at TIMESTAMP NULL DEFAULT NULL COMMENT '完成时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    INDEX idx_external_user (external_user_id),
    INDEX idx_token (test_token),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API测试记录表';

-- 3. 回调日志表
CREATE TABLE IF NOT EXISTS api_callbacks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    test_id INT UNSIGNED NOT NULL COMMENT '测试记录ID',
    callback_url VARCHAR(512) NOT NULL COMMENT '回调URL',
    response_status INT DEFAULT NULL COMMENT 'HTTP响应状态码',
    response_body TEXT DEFAULT NULL COMMENT '响应内容',
    retry_count INT NOT NULL DEFAULT 0 COMMENT '重试次数',
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending' COMMENT '回调状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_test (test_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API回调日志表';
