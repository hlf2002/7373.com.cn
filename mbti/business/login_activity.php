<?php
require_once 'db.php';

startSession();
requireLogin();

$company_id = $_SESSION['company_id'];
$pdo = getDB();

// 获取登录记录
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM login_logs WHERE company_id = ?");
$stmt->execute([$company_id]);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT * FROM login_logs WHERE company_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $company_id, PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$login_logs = $stmt->fetchAll();

// 获取用户信息
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MBTI 用户中心 - 登录活动详情</title>
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
            --sidebar-beige: #F2EFE9;
        }
        body {
            font-family: 'Quicksand', 'Noto Sans SC', sans-serif;
            color: var(--text-main);
            background-color: var(--warm-beige);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
            color: var(--sage-green);
        }
        .active-nav {
            background-color: var(--sage-light);
            color: var(--sage-green);
            font-weight: 600;
        }
        .active-nav .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400;
        }
        .custom-shadow {
            box-shadow: 0 4px 20px -2px rgba(122, 148, 120, 0.08);
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
                        "sidebar-beige": "#F2EFE9",
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-warm-beige text-text-main">
<div class="flex min-h-screen overflow-hidden">
<aside class="w-64 bg-sidebar-beige flex flex-col shrink-0 border-r border-[#E5E1D8]">
<div class="p-8 flex items-center gap-3">
<div class="size-10 bg-sage-green rounded-xl flex items-center justify-center text-white shadow-sm">
<span class="material-symbols-outlined !text-white !fill-1">psychology</span>
</div>
<div>
<h1 class="text-sage-green text-lg font-bold leading-tight">MBTI Admin</h1>
<p class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold">User Center</p>
</div>
</div>
<nav class="flex-1 px-4 py-4 space-y-2">
<a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/50 transition-colors group" href="index.php">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-sm font-medium opacity-80 group-hover:opacity-100">控制台</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/50 transition-colors group" href="#">
<span class="material-symbols-outlined">monitoring</span>
<span class="text-sm font-medium opacity-80 group-hover:opacity-100">数据统计</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/50 transition-colors group" href="#">
<span class="material-symbols-outlined">account_balance_wallet</span>
<span class="text-sm font-medium opacity-80 group-hover:opacity-100">充值中心</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/50 transition-colors group" href="#">
<span class="material-symbols-outlined">receipt_long</span>
<span class="text-sm font-medium opacity-80 group-hover:opacity-100">充值记录</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 rounded-xl active-nav transition-colors" href="settings.php">
<span class="material-symbols-outlined">settings</span>
<span class="text-sm">账号设置</span>
</a>
</nav>
<div class="p-6">
<div class="bg-white/40 rounded-2xl p-4 border border-white/60">
<p class="text-[10px] text-gray-500 mb-2 font-bold uppercase tracking-tighter">API Status</p>
<div class="flex items-center gap-2">
<div class="size-2 rounded-full bg-sage-green animate-pulse"></div>
<span class="text-xs font-medium text-sage-green">服务连接正常</span>
</div>
</div>
</div>
</aside>
<main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
<header class="h-16 bg-warm-beige flex items-center px-10 sticky top-0 z-10 border-b border-[#E5E1D8]/50">
<a class="flex items-center gap-2 text-sage-green text-sm font-bold hover:opacity-80 transition-opacity" href="settings.php">
<span class="material-symbols-outlined text-lg">arrow_back</span>
                返回账号设置
            </a>
</header>
<div class="p-10 space-y-8 max-w-6xl">
<div>
<h2 class="text-2xl font-bold text-gray-800">登录活动详情</h2>
<p class="text-gray-500 text-sm mt-1">监控您的账号活动，确保访问安全。如果您发现未授权的登录，请立即采取行动。</p>
</div>
<section class="bg-white rounded-3xl custom-shadow border border-white overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-sage-light/10 border-b border-warm-beige">
<th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">登录时间</th>
<th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">IP 地址</th>
<th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">登录地点</th>
<th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-wider">状态</th>
</tr>
</thead>
<tbody class="divide-y divide-warm-beige">
<?php if (!empty($login_logs)): ?>
<?php foreach ($login_logs as $log): ?>
<tr class="hover:bg-warm-beige/30 transition-colors">
<td class="px-8 py-5 text-sm text-gray-700 font-medium"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
<td class="px-8 py-5 text-sm text-gray-600 font-mono"><?php echo htmlspecialchars($log['login_ip'] ?: '未知'); ?></td>
<td class="px-8 py-5 text-sm text-gray-600"><?php echo htmlspecialchars($log['login_location'] ?: '未知'); ?></td>
<td class="px-8 py-5">
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-sage-light text-sage-green">
                                        成功
                                    </span>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="4" class="px-8 py-10 text-center text-gray-400">暂无登录记录</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>
<div class="bg-sage-light/40 border border-sage-green/10 rounded-2xl p-6 flex items-start gap-4">
<div class="p-2 bg-sage-green rounded-lg">
<span class="material-symbols-outlined !text-white !fill-1 text-xl leading-none">security</span>
</div>
<div>
<h4 class="font-bold text-gray-800 text-sm mb-1">账号安全建议</h4>
<p class="text-sm text-gray-600 leading-relaxed">
                        如果您发现异常登录，请立即修改密码并重置 API 密钥。我们建议您定期查看此列表以确保您的账号没有被他人非法访问。
                    </p>
</div>
</div>
<?php if ($total_pages > 1): ?>
<div class="flex items-center justify-between pt-4">
<p class="text-xs text-gray-400 font-medium uppercase tracking-widest">Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total); ?> of <?php echo $total; ?> records</p>
<div class="flex gap-2">
<?php if ($page > 1): ?>
<a href="?page=<?php echo $page - 1; ?>" class="size-9 flex items-center justify-center rounded-xl bg-white border border-warm-beige text-gray-400 hover:text-sage-green transition-colors">
<span class="material-symbols-outlined text-lg">chevron_left</span>
</a>
<?php endif; ?>
<?php for ($i = 1; $i <= $total_pages; $i++): ?>
<?php if ($i == $page): ?>
<span class="size-9 flex items-center justify-center rounded-xl bg-sage-green text-white font-bold text-sm"><?php echo $i; ?></span>
<?php elseif ($i <= 3 || $i > $total_pages - 3 || abs($i - $page) <= 1): ?>
<a href="?page=<?php echo $i; ?>" class="size-9 flex items-center justify-center rounded-xl bg-white border border-warm-beige text-gray-600 font-bold text-sm hover:border-sage-green transition-colors"><?php echo $i; ?></a>
<?php endif; ?>
<?php endfor; ?>
<?php if ($page < $total_pages): ?>
<a href="?page=<?php echo $page + 1; ?>" class="size-9 flex items-center justify-center rounded-xl bg-white border border-warm-beige text-gray-400 hover:text-sage-green transition-colors">
<span class="material-symbols-outlined text-lg">chevron_right</span>
</a>
<?php endif; ?>
</div>
</div>
<?php endif; ?>
</div>
<div class="p-10 mt-auto text-center">
<p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">© 2023 MBTI Personality Analysis Platform - Security Center</p>
</div>
</main>
</div>

</body>
</html>
