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
            $_SESSION['role']     = $user['role'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['jabatan']  = $user['jabatan'];
            redirectByRole();
        } else {
            $error = 'Username atau kata laluan tidak sah.';
        }
    }
}
$roleLabels = ['pemohon'=>'Pemohon','pengarah_jab'=>'Pengarah Jabatan','pengarah_jtik'=>'Pengarah JTIK','admin_it'=>'Admin IT'];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk – Sistem Capaian Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;font-family:'Segoe UI',sans-serif}
        .login-wrapper{display:flex;min-height:100vh}
        .left-panel{
            flex:1;background:linear-gradient(160deg,#0a1628 0%,#003087 50%,#1565c0 100%);
            display:flex;flex-direction:column;justify-content:center;align-items:center;
            padding:60px 40px;color:#fff;text-align:center;position:relative;overflow:hidden;
        }
        .left-panel::before{content:'';position:absolute;inset:0;
            background:radial-gradient(circle at 20% 80%,rgba(255,255,255,0.06) 0%,transparent 50%),
                        radial-gradient(circle at 80% 20%,rgba(255,255,255,0.06) 0%,transparent 50%);}
        .logo-circle{width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.15);
            border:3px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;
            margin:0 auto 24px;font-size:46px;}
        .sys-title{font-size:1.9rem;font-weight:800;line-height:1.2;margin-bottom:8px;text-shadow:0 2px 8px rgba(0,0,0,0.3);}
        .sys-sub{font-size:0.9rem;opacity:0.7;margin-bottom:36px;}
        .quote-box{border-left:4px solid rgba(255,255,255,0.5);padding:12px 18px;text-align:left;
            font-style:italic;opacity:0.8;font-size:0.88rem;max-width:360px;}
        .kod-borang{margin-top:32px;font-size:0.75rem;opacity:0.5;}
        .right-panel{width:420px;min-width:380px;background:#e8f0fe;display:flex;flex-direction:column;justify-content:center;padding:50px 44px;}
        .panel-title{font-size:1.3rem;font-weight:700;color:#003087;margin-bottom:4px;}
        .panel-sub{font-size:0.85rem;color:#6c757d;margin-bottom:28px;}
        .form-label{font-size:0.83rem;font-weight:600;color:#444;}
        .form-control{border-radius:8px;border:1px solid #bfdbfe;padding:10px 14px;font-size:0.9rem;background:#fff;}
        .form-control:focus{border-color:#1976d2;box-shadow:0 0 0 3px rgba(0,48,135,0.15);}
        .btn-login{background:#003087;border:none;border-radius:8px;padding:11px;font-weight:600;font-size:0.95rem;transition:background 0.2s, box-shadow 0.2s;}
        .btn-login:hover{background:#0d47a1;box-shadow:0 4px 14px rgba(0,48,135,0.35);}
        .footer-note{margin-top:auto;padding-top:36px;font-size:0.74rem;color:#adb5bd;text-align:center;}
        .role-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:16px;}
        .role-chip{background:#e8f0fe;color:#003087;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:12px;}
        @media(max-width:768px){.left-panel{display:none}.right-panel{width:100%;min-width:unset;}}
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="left-panel">
        <div class="logo-circle"><i class="bi bi-rocket-takeoff-fill"></i></div>
        <div class="sys-title">SISTEM CAPAIAN SISTEM</div>
        <div class="sys-sub">Majlis Bandaraya Seberang Perai</div>
        <div class="quote-box">"Setiap permohonan capaian sistem hendaklah mendapat kelulusan Pengarah JTIK sebelum akses diberikan."</div>
        <div class="kod-borang">KOD BORANG: 119/D35 &nbsp;|&nbsp; KEMASKINI: 10/2025</div>
    </div>
    <div class="right-panel">
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
            Demo: pemohon1/user123 &nbsp;|&nbsp; pengarah_jab/pengarah123<br>
            pengarah_jtik/jtik123 &nbsp;|&nbsp; admin_it/it123
            <br><br>&copy; <?= date('Y') ?> Majlis Bandaraya Seberang Perai
        </div>
    </div>
</div>
</body>
</html>
