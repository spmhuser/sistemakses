<?php
session_name('sistem_akses');
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

if (isset($_SESSION['user_id'])) { redirectByRole(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (!$username || !$password) {
        $error = 'Sila isi username dan kata laluan.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] === 'admin' ? 'admin_it' : $user['role'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['jabatan']  = $user['jabatan'];
            redirectByRole();
        } else {
            $error = 'Username atau kata laluan tidak sah.';
        }
    }
}
$roleLabels = ['pemohon'=>'Pemohon','pengarah_jab'=>'Pengarah Jabatan','pengarah_jtik'=>'Pengarah JTIK','admin_it'=>'Admin IT','admin'=>'Admin'];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk – Borang Capaian Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ease-ios: cubic-bezier(0.25, 0.1, 0.25, 1);
            --spring: cubic-bezier(0.34, 1.25, 0.64, 1);
            --spring-soft: cubic-bezier(0.22, 1, 0.36, 1);
            --ios-bg: #0b1120;
            --ios-card: #151d2e;
            --ios-separator: rgba(255,255,255,0.08);
            --ios-label: #e2e8f0;
            --ios-secondary: #94a3b8;
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.5), 0 8px 24px rgba(0,0,0,0.35);
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Segoe UI', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        @keyframes iosFadeUp {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes iosFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        @keyframes iosGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .login-wrapper {
            display: flex; min-height: 100vh;
            background: var(--ios-bg);
            animation: iosFadeUp 0.6s var(--spring-soft) both;
        }
        .left-panel {
            flex: 1;
            background: linear-gradient(-45deg, #0a1628, #0d2137, #1a3a5c, #1e4976);
            background-size: 400% 400%;
            animation: iosGradient 12s ease infinite;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 60px 48px; color: #fff; text-align: center;
            position: relative; overflow: hidden;
        }
        .left-panel::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(circle at 15% 85%, rgba(250, 204, 21, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 85% 15%, rgba(37, 99, 235, 0.15) 0%, transparent 45%);
            pointer-events: none;
        }
        .left-panel > * { position: relative; z-index: 1; }
        .logo-circle {
            width: 96px; height: 96px; border-radius: 22px;
            background: linear-gradient(145deg, #facc15, #eab308);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px; font-size: 44px;
            box-shadow: 0 12px 32px rgba(250, 204, 21, 0.35), inset 0 1px 0 rgba(255,255,255,0.3);
            animation: iosFloat 4s var(--ease-ios) infinite;
            transition: transform 0.4s var(--spring);
        }
        .logo-circle:hover { transform: scale(1.08) rotate(-4deg); }
        .sys-title { font-size: 2rem; font-weight: 700; line-height: 1.15; margin-bottom: 10px; letter-spacing: -0.03em; color: #fff; }
        .sys-sub { font-size: 0.95rem; opacity: 0.75; margin-bottom: 40px; color: #dbeafe; }
        .quote-box {
            border-left: 3px solid #facc15; padding: 16px 20px; text-align: left;
            font-size: 0.9rem; max-width: 380px; line-height: 1.55;
            background: rgba(255, 255, 255, 0.06); border-radius: 0 var(--radius-md) var(--radius-md) 0;
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        }
        .kod-borang { margin-top: 36px; font-size: 0.75rem; opacity: 0.45; color: #93c5fd; }
        .right-panel {
            width: 440px; min-width: 400px;
            display: flex; flex-direction: column; justify-content: center;
            padding: 48px 44px; background: #0b1120;
        }
        .login-card {
            background: #151d2e; border-radius: var(--radius-xl);
            padding: 36px 32px; box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.06);
            animation: iosFadeUp 0.7s var(--spring-soft) 0.1s both;
        }
        .panel-title { font-size: 1.6rem; font-weight: 700; color: #f1f5f9; margin-bottom: 6px; letter-spacing: -0.03em; }
        .panel-sub { font-size: 0.9rem; color: var(--ios-secondary); margin-bottom: 28px; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: var(--ios-label); margin-bottom: 8px; }
        .input-group-text {
            background: rgba(0,0,0,0.3) !important; border: none !important;
            border-radius: var(--radius-md) 0 0 var(--radius-md) !important;
            color: var(--ios-secondary) !important; padding: 0 14px;
        }
        .form-control {
            border: none !important; border-radius: 0 var(--radius-md) var(--radius-md) 0 !important;
            padding: 14px 16px !important; font-size: 0.95rem !important;
            background: rgba(0,0,0,0.3) !important; color: var(--ios-label) !important;
            transition: box-shadow 0.35s var(--ease-ios), background 0.2s, transform 0.35s var(--spring) !important;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control { background: rgba(0,0,0,0.45) !important; }
        .input-group:focus-within { box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.2); border-radius: var(--radius-md); }
        .btn-login {
            background: linear-gradient(180deg, #2563eb 0%, #1e4976 100%);
            border: none; border-radius: var(--radius-md); padding: 15px;
            font-weight: 600; font-size: 1rem; color: #fff; margin-top: 8px;
            box-shadow: 0 4px 16px rgba(30, 73, 118, 0.35), inset 0 1px 0 rgba(255,255,255,0.15);
            transition: transform 0.35s var(--spring), box-shadow 0.35s var(--ease-ios), background 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 28px rgba(30, 73, 118, 0.4), 0 0 0 1px rgba(250, 204, 21, 0.2);
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); color: #fff;
        }
        .btn-login:active { transform: scale(0.97); transition-duration: 0.12s; }
        .alert-danger {
            border: none; border-radius: var(--radius-md);
            background: rgba(255, 59, 48, 0.1); color: #d70015; font-size: 0.85rem;
            animation: iosFadeUp 0.4s var(--spring-soft) both;
        }
        .footer-note { margin-top: 24px; font-size: 0.74rem; color: var(--ios-secondary); text-align: center; line-height: 1.6; }
        .role-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 20px; justify-content: center; }
        .role-chip {
            background: rgba(250,204,21,0.1); color: #facc15;
            font-size: 0.72rem; font-weight: 600; padding: 5px 12px; border-radius: 20px;
            transition: transform 0.3s var(--spring), background 0.2s;
        }
        .role-chip:hover { transform: scale(1.06); background: rgba(250, 204, 21, 0.2); }
        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; }
            .left-panel { padding: 40px 24px; min-height: 280px; }
            .right-panel { width: 100%; min-width: unset; padding: 24px 20px 40px; }
            .login-card { padding: 28px 22px; border-radius: var(--radius-lg); }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="left-panel">
        <div class="logo-circle">⚡</div>
        <div class="sys-title">BORANG CAPAIAN<br>SISTEM</div>
        <div class="sys-sub">Majlis Bandaraya Seberang Perai</div>
        <div class="quote-box">"Setiap permohonan capaian sistem hendaklah mendapat kelulusan Pengarah JTIK sebelum akses diberikan."</div>
        <div class="kod-borang">KOD BORANG: 119/D35 &nbsp;|&nbsp; KEMASKINI: 10/2025</div>
    </div>
    <div class="right-panel">
        <div class="login-card">
        <div class="panel-title">Log Masuk</div>
        <div class="panel-sub">Sila masukkan maklumat pengguna anda</div>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem">
                <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text" name="username" class="form-control" autofocus required
                           placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Kata Laluan</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan kata laluan">
                </div>
            </div>
            <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                <i class="bi bi-box-arrow-in-right me-2"></i>Log Masuk
            </button>
        </form>
        <div class="role-chips">
            <?php foreach($roleLabels as $u=>$l): ?>
            <span class="role-chip"><?=$l?></span>
            <?php endforeach; ?>
        </div>
        <div class="footer-note">
            Demo: admin/admin &nbsp;|&nbsp; pemohon1/user123 &nbsp;|&nbsp; pengarah_jab/pengarah123<br>
            pengarah_jtik/jtik123 &nbsp;|&nbsp; admin_it/it123
            <br><br>&copy; <?= date('Y') ?> Majlis Bandaraya Seberang Perai
        </div>
        </div>
    </div>
</div>
</body>
</html>
