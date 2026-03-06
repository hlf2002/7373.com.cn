<?php
require_once 'db.php';

startSession();

// 检查是否已登录
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password)) {
        $error = '请输入当前密码';
    } elseif (empty($new_password)) {
        $error = '请输入新密码';
    } elseif (strlen($new_password) < 8) {
        $error = '新密码至少8位';
    } elseif ($new_password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } else {
        $pdo = getDB();
        $company_id = $_SESSION['company_id'];

        // 验证当前密码
        $stmt = $pdo->prepare("SELECT password_hash FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();

        if (!$company || !password_verify($current_password, $company['password_hash'])) {
            $error = '当前密码错误';
        } else {
            // 更新密码
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE companies SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $company_id]);
            $success = '密码修改成功';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MBTI 用户中心 - 修改密码</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&amp;family=Quicksand:wght@500;600;700&amp;display=swap" rel="stylesheet"/>
<style type="text/tailwindcss">
        :root {
            --warm-beige: #F9F7F2;
            --soft-white: #FFFFFF;
            --sage-green: #7A9478;
            --sage-light: #E8EDE7;
            --terracotta: #D98E73;
            --terracotta-light: #F7EAE5;
            --text-main: #4A4A4A;
            --border-color: #E5E1D8;
        }
        body {
            font-family: 'Quicksand', 'Noto Sans SC', sans-serif;
            color: var(--text-main);
            background-color: var(--warm-beige);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
        .custom-shadow {
            box-shadow: 0 10px 40px -10px rgba(122, 148, 120, 0.12);
        }
        input:focus {
            outline: none;
            border-color: var(--sage-green) !important;
            ring: 2px solid rgba(122, 148, 120, 0.1);
        }
    </style>
<script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "warm-beige": "#F9F7F2",
                        "sage-green": "#7A9478",
                        "terracotta": "#D98E73",
                        "text-main": "#4A4A4A",
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "2xl": "2rem",
                        "3xl": "2.5rem"
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-warm-beige min-h-screen flex flex-col">
<header class="h-20 flex items-center px-10 border-b border-[#E5E1D8]/50 bg-warm-beige/80 backdrop-blur-md sticky top-0 z-50">
<div class="max-w-6xl mx-auto w-full flex items-center justify-between">
<a class="flex items-center gap-2 group transition-all" href="settings.php">
<div class="size-9 bg-sage-light rounded-lg flex items-center justify-center group-hover:bg-sage-green transition-colors">
<span class="material-symbols-outlined text-sage-green group-hover:text-white transition-colors">arrow_back</span>
</div>
<span class="text-sm font-bold text-sage-green">返回账号设置</span>
</a>
<div class="flex items-center gap-3">
<div class="size-8 bg-sage-green rounded-lg flex items-center justify-center text-white">
<span class="material-symbols-outlined !text-white !text-xl !fill-1">psychology</span>
</div>
<span class="text-xs font-bold uppercase tracking-widest text-gray-400">MBTI Admin Center</span>
</div>
</div>
</header>
<main class="flex-1 flex items-center justify-center p-6 md:p-10">
<div class="w-full max-w-xl">
<div class="bg-white rounded-3xl p-8 md:p-12 custom-shadow border border-white">

<?php if ($error): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-600 text-sm">
<?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-600 text-sm">
<?php echo htmlspecialchars($success); ?>
<a href="settings.php" class="underline font-bold">返回设置</a>
</div>
<?php endif; ?>

<div class="text-center mb-10">
<div class="inline-flex items-center justify-center size-16 bg-sage-light/30 rounded-2xl mb-6">
<span class="material-symbols-outlined !text-sage-green !text-4xl">lock_reset</span>
</div>
<h1 class="text-2xl font-bold text-gray-800">修改密码</h1>
<p class="text-gray-400 text-sm mt-2">为了您的账户安全，请定期更换密码。</p>
</div>
<form method="POST" class="space-y-6">
<div class="space-y-2">
<label class="text-sm font-bold text-gray-700 ml-1">当前密码</label>
<div class="relative">
<input name="current_password" class="w-full h-14 bg-warm-beige/30 border-gray-200 rounded-2xl px-5 text-gray-700 focus:ring-sage-green/20 focus:border-sage-green transition-all" placeholder="请输入当前使用的密码" type="password"/>
</div>
</div>
<div class="space-y-2">
<label class="text-sm font-bold text-gray-700 ml-1">新密码</label>
<div class="relative">
<input name="new_password" id="new_password" class="w-full h-14 bg-warm-beige/30 border-gray-200 rounded-2xl px-5 text-gray-700 focus:ring-sage-green/20 focus:border-sage-green transition-all" placeholder="请输入新密码" type="password" oninput="checkPasswordStrength()"/>
</div>
<div class="pt-2 px-1">
<div class="flex justify-between items-center mb-2">
<span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">密码强度</span>
<span id="strength-text" class="text-xs font-bold text-gray-400">未设置</span>
</div>
<div class="flex gap-2">
<div id="strength-1" class="h-1.5 flex-1 bg-gray-200 rounded-full"></div>
<div id="strength-2" class="h-1.5 flex-1 bg-gray-200 rounded-full"></div>
<div id="strength-3" class="h-1.5 flex-1 bg-gray-200 rounded-full"></div>
</div>
</div>
</div>
<div class="space-y-2">
<label class="text-sm font-bold text-gray-700 ml-1">确认新密码</label>
<div class="relative">
<input name="confirm_password" class="w-full h-14 bg-warm-beige/30 border-gray-200 rounded-2xl px-5 text-gray-700 focus:ring-sage-green/20 focus:border-sage-green transition-all" placeholder="请再次输入新密码" type="password"/>
</div>
</div>
<div class="bg-sage-light/20 rounded-2xl p-5 border border-sage-light/50">
<p class="text-xs font-bold text-sage-green mb-3 flex items-center gap-2">
<span class="material-symbols-outlined !text-sm">verified_user</span>
                            安全要求：
                        </p>
<ul class="grid grid-cols-2 gap-y-2">
<li class="flex items-center gap-2 text-xs text-gray-500">
<span class="material-symbols-outlined !text-sage-green !text-sm !font-bold">check_circle</span>
                                至少 8 个字符
                            </li>
<li class="flex items-center gap-2 text-xs text-gray-500">
<span class="material-symbols-outlined !text-sage-green !text-sm !font-bold">check_circle</span>
                                包含数字
                            </li>
<li class="flex items-center gap-2 text-xs text-gray-500" id="uppercase-check">
<span class="material-symbols-outlined !text-gray-300 !text-sm">circle</span>
                                包含大写字母
                            </li>
<li class="flex items-center gap-2 text-xs text-gray-500" id="special-check">
<span class="material-symbols-outlined !text-gray-300 !text-sm">circle</span>
                                包含特殊符号
                            </li>
</ul>
</div>
<div class="flex flex-col gap-4 pt-4">
<button class="w-full h-14 bg-sage-green text-white font-bold rounded-2xl shadow-lg shadow-sage-green/20 hover:bg-[#688266] transition-all transform active:scale-[0.98]" type="submit">
                            确认修改
                        </button>
<a href="settings.php" class="w-full h-14 bg-transparent text-gray-400 font-bold rounded-2xl hover:bg-gray-50 transition-all flex items-center justify-center">
                            取消
                        </a>
</div>
</form>
</div>
<p class="text-center mt-10 text-[10px] text-gray-400 uppercase tracking-[0.2em] font-bold">
                © 2023 MBTI Personality Analysis Platform - Security Center
            </p>
</div>
</main>

<script>
function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthText = document.getElementById('strength-text');
    const hasNumber = /\d/.test(password);
    const hasUppercase = /[A-Z]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    const isLongEnough = password.length >= 8;

    let strength = 0;
    if (isLongEnough) strength++;
    if (hasNumber) strength++;
    if (hasUppercase || hasSpecial) strength++;

    const colors = ['bg-gray-200', 'bg-red-400', 'bg-yellow-400', 'bg-sage-green'];
    const texts = ['未设置', '太简单', '中等强度', '强'];

    document.getElementById('strength-1').className = 'h-1.5 flex-1 rounded-full ' + colors[strength > 0 ? strength : 0];
    document.getElementById('strength-2').className = 'h-1.5 flex-1 rounded-full ' + colors[strength > 1 ? strength : 0];
    document.getElementById('strength-3').className = 'h-1.5 flex-1 rounded-full ' + colors[strength > 2 ? strength : 0];
    strengthText.textContent = texts[Math.min(strength, 3)];
    strengthText.className = 'text-xs font-bold ' + (strength === 3 ? 'text-sage-green' : strength === 2 ? 'text-yellow-500' : strength === 1 ? 'text-red-400' : 'text-gray-400');

    // Update requirement checks
    document.getElementById('uppercase-check').innerHTML = hasUppercase
        ? '<span class="material-symbols-outlined !text-sage-green !text-sm !font-bold">check_circle</span>包含大写字母'
        : '<span class="material-symbols-outlined !text-gray-300 !text-sm">circle</span>包含大写字母';
    document.getElementById('special-check').innerHTML = hasSpecial
        ? '<span class="material-symbols-outlined !text-sage-green !text-sm !font-bold">check_circle</span>包含特殊符号'
        : '<span class="material-symbols-outlined !text-gray-300 !text-sm">circle</span>包含特殊符号';
}
</script>

</body>
</html>
