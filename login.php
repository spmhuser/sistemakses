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
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk – Borang Capaian Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;font-family:'Segoe UI','Inter',sans-serif}
        body{min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:linear-gradient(120deg,#F1F6FD 0%,#FFFFFF 55%,#FFFFFF 100%);padding:24px;}
        .auth-card{position:relative;display:flex;width:100%;max-width:1050px;
            min-height:min(90vh,660px);height:auto;border-radius:30px;overflow:hidden;background:#FFFFFF;
            box-shadow:0 30px 70px rgba(50,25,50,0.30);}

        /* LEFT - IT image with curved right edge */
        .img-side{position:relative;flex:1.08;
            clip-path:ellipse(82% 100% at 18% 50%);
            background:url('assets/login-it.jpg') center/cover no-repeat;
            display:flex;flex-direction:column;justify-content:space-between;
            padding:42px 40px;color:#fff;}
        .img-side::before{content:'';position:absolute;inset:0;
            background:linear-gradient(150deg,rgba(18,38,64,0.80) 0%,rgba(40,95,170,0.60) 50%,rgba(31,188,212,0.50) 100%);}
        .img-side > *{position:relative;z-index:1;}
        .brand-top{display:flex;align-items:center;gap:13px;}
        .brand-badge{width:52px;height:52px;border-radius:14px;
            background:linear-gradient(135deg,#1FBCD4,#2E73D8);
            display:flex;align-items:center;justify-content:center;font-size:26px;color:#fff;
            box-shadow:0 6px 16px rgba(0,0,0,0.25);}
        .brand-text b{display:block;font-size:1rem;font-weight:800;letter-spacing:0.4px;line-height:1.2;text-shadow:0 1px 6px rgba(0,0,0,0.3);}
        .brand-text span{font-size:0.78rem;opacity:0.95;text-shadow:0 1px 5px rgba(0,0,0,0.3);}
        .img-caption{max-width:340px;}
        .img-caption h2{font-size:1.7rem;font-weight:800;line-height:1.25;margin-bottom:10px;text-shadow:0 2px 12px rgba(0,0,0,0.35);}
        .img-caption p{font-size:0.92rem;opacity:0.92;line-height:1.6;}
        .img-foot{font-size:0.74rem;opacity:0.8;letter-spacing:0.5px;}

        /* RIGHT - form */
        .form-side{flex:1;display:flex;flex-direction:column;justify-content:center;
            padding:48px clamp(28px,4vw,56px);}
        .welcome{font-size:1.85rem;font-weight:800;color:#1E3A5F;line-height:1.2;margin-bottom:6px;}
        .welcome-sub{font-size:0.98rem;color:#5C5560;margin-bottom:22px;}
        .field-group{margin-bottom:16px;}
        .field-group label{display:block;font-size:0.95rem;font-weight:700;color:#3A2E40;margin-bottom:8px;}
        .input-pill{display:flex;align-items:center;gap:11px;background:#fff;
            border:1.5px solid #DCE6F2;border-radius:13px;padding:14px 16px;
            transition:border-color 0.2s,box-shadow 0.2s;}
        .input-pill:focus-within{border-color:#2F86DD;box-shadow:0 0 0 3px rgba(46,115,216,0.16);}
        .input-pill i{font-size:1.15rem;color:#2E73D8;}
        .input-pill input{flex:1;border:none;outline:none;background:transparent;font-size:1rem;color:#2D2433;font-weight:500;}
        .input-pill input::placeholder{color:#A89AA2;font-weight:400;}
        .toggle-eye{cursor:pointer;color:#2E73D8;transition:opacity 0.15s;}
        .toggle-eye:hover{opacity:0.7;}
        .forgot{display:block;text-align:right;font-size:0.84rem;color:#2E73D8;text-decoration:none;font-weight:600;margin:-8px 0 22px;}
        .forgot:hover{text-decoration:underline;}
        .btn-login{position:relative;overflow:hidden;width:100%;cursor:pointer;
            display:inline-flex;align-items:center;justify-content:center;gap:10px;
            background:linear-gradient(135deg,#2E73D8 0%,#1FBCD4 100%);border:none;border-radius:13px;
            padding:15px;font-weight:800;font-size:1.08rem;color:#fff;letter-spacing:0.3px;
            box-shadow:0 8px 22px rgba(46,115,216,0.42);
            transition:transform 0.22s cubic-bezier(.2,.8,.2,1),box-shadow 0.22s,filter 0.22s;}
        .btn-login::after{content:'';position:absolute;top:0;left:-130%;width:65%;height:100%;
            background:linear-gradient(120deg,transparent,rgba(255,255,255,0.55),transparent);
            transform:skewX(-20deg);transition:left 0.55s ease;}
        .btn-login:hover::after{left:130%;}
        .btn-login:hover{filter:brightness(1.06);transform:translateY(-3px);box-shadow:0 14px 32px rgba(31,188,212,0.55);}
        .btn-login:active{transform:translateY(1px) scale(0.99);}
        .login-alert{background:#FFE2E0;color:#B42318;border:1.5px solid #F7B9B5;border-radius:11px;
            padding:11px 15px;font-size:0.9rem;font-weight:600;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
        .demo-note{margin-top:16px;font-size:0.78rem;color:#7A7079;text-align:center;line-height:1.7;
            border-top:1px solid #EADFD3;padding-top:16px;}
        .demo-note b{color:#1E3A5F;}

        .new-staff{margin-top:22px;margin-bottom:14px;text-align:center;}
        .new-staff .ns-label{display:block;font-size:0.92rem;color:#4A3F48;margin-bottom:10px;font-weight:700;}
        .btn-semak-open{width:100%;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:10px;
            background:linear-gradient(135deg,#1E3A5F 0%,#2C5488 100%);color:#fff;
            border:2px solid rgba(255,255,255,0.28);border-radius:14px;padding:15px;
            font-weight:800;font-size:1.02rem;letter-spacing:0.2px;transition:all 0.2s;
            box-shadow:0 6px 16px rgba(30,58,95,0.42);
            animation:pulseAttn 2s ease-in-out infinite;}
        .btn-semak-open .ns-ic{font-size:1.35rem;}
        .btn-semak-open:hover{filter:brightness(1.10);transform:translateY(-2px);
            box-shadow:0 12px 28px rgba(30,58,95,0.55);animation:none;}
        @keyframes pulseAttn{0%,100%{box-shadow:0 6px 16px rgba(30,58,95,0.42);}
            50%{box-shadow:0 6px 16px rgba(30,58,95,0.42),0 0 0 9px rgba(44,84,136,0.22);}}

        /* MODAL SEMAKAN */
        .modal-overlay{position:fixed;inset:0;background:rgba(45,20,40,0.55);
            -webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);
            display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
        .modal-overlay.show{display:flex;}
        .modal-box{background:#fff;border-radius:20px;width:100%;max-width:450px;padding:30px 28px;
            box-shadow:0 24px 60px rgba(45,20,40,0.42);animation:pop 0.25s ease;}
        @keyframes pop{from{opacity:0;transform:translateY(14px) scale(0.97)}to{opacity:1;transform:none}}
        .modal-box .m-icon{width:62px;height:62px;border-radius:17px;margin:0 auto 14px;
            background:linear-gradient(135deg,#2E73D8,#1FBCD4);display:flex;align-items:center;justify-content:center;
            font-size:30px;color:#fff;box-shadow:0 8px 20px rgba(46,115,216,0.4);}
        .modal-box h3{font-size:1.3rem;font-weight:800;color:#1E3A5F;text-align:center;margin-bottom:7px;}
        .modal-box .m-sub{font-size:0.9rem;color:#5C5560;text-align:center;line-height:1.55;margin-bottom:20px;}
        .modal-box label{display:block;font-size:0.92rem;font-weight:700;color:#3A2E40;margin-bottom:7px;}
        .semak-result{margin-top:16px;border-radius:12px;padding:14px 16px;font-size:0.92rem;font-weight:600;line-height:1.6;display:none;}
        .semak-result.show{display:block;}
        .semak-result.ok{background:#DFF7E6;color:#15803D;border:1.5px solid #A7E8BE;}
        .semak-result.no{background:#FFE2E0;color:#B42318;border:1.5px solid #F7B9B5;}
        .m-actions{display:flex;gap:10px;margin-top:18px;}
        .btn-close-m{background:#F1F6FD;color:#1E3A5F;border:none;border-radius:12px;padding:13px 18px;font-weight:700;cursor:pointer;font-size:0.95rem;transition:background 0.2s;}
        .btn-close-m:hover{background:#E8EFF9;}
        .btn-semak{flex:1;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;
            background:linear-gradient(135deg,#2E73D8,#1FBCD4);border:none;border-radius:12px;padding:13px;
            font-weight:800;font-size:0.98rem;color:#fff;box-shadow:0 6px 16px rgba(46,115,216,0.38);transition:all 0.2s;}
        .btn-semak:hover{filter:brightness(1.06);transform:translateY(-2px);}

        @media(max-width:840px){
            .auth-card{flex-direction:column;height:auto;}
            .img-side{flex:none;clip-path:none;min-height:180px;padding:26px 28px;}
            .img-caption{display:none}
            .form-side{padding:34px 28px;}
        }
    </style>
</head>
<body>
<div class="auth-card">
    <!-- LEFT: IT-themed image with curved edge -->
    <div class="img-side">
        <div class="brand-top">
            <div class="brand-badge"><i class="bi bi-shield-lock"></i></div>
            <div class="brand-text">
                <b>BORANG CAPAIAN SISTEM</b>
                <span>Majlis Bandaraya Seberang Perai</span>
            </div>
        </div>
        <div class="img-caption">
            <h2>Selamat kembali ke Sistem Capaian!</h2>
            <p>Urus permohonan capaian sistem ICT anda dengan selamat dan teratur.</p>
        </div>
        <div class="img-foot">KOD BORANG: 119/D35 &nbsp;|&nbsp; KEMASKINI: 10/2025</div>
    </div>

    <!-- RIGHT: login form -->
    <div class="form-side">
        <div class="welcome">Selamat Datang! <span style="font-size:1.5rem">&#128075;</span></div>
        <div class="welcome-sub">Sila log masuk untuk meneruskan</div>

        <?php if ($error): ?>
            <div class="login-alert"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="field-group">
                <label for="username">Username</label>
                <div class="input-pill">
                    <i class="bi bi-person"></i>
                    <input type="text" id="username" name="username" autofocus required
                           placeholder="Masukkan username anda"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>

            <div class="field-group">
                <label for="password">Kata Laluan</label>
                <div class="input-pill">
                    <i class="bi bi-lock"></i>
                    <input type="password" id="password" name="password" required placeholder="Masukkan kata laluan">
                    <i class="bi bi-eye-slash toggle-eye" id="togglePw" onclick="togglePw()"></i>
                </div>
            </div>

            <a href="#" class="forgot" onclick="return false;">Lupa kata laluan? Hubungi Admin IT</a>

            <button type="submit" class="btn-login">Log Masuk <i class="bi bi-arrow-right"></i></button>
        </form>
    </br>
        <div class="new-staff">
            <span class="ns-label">&#128075; Kakitangan baru? Semak dulu data anda berdaftar/belum oleh Unit Gaji sebelum mohon capaian</span>
            <button type="button" class="btn-semak-open" onclick="openSemak()">
                <i class="bi bi-person-badge ns-ic"></i> Semak Status
                <i class="bi bi-arrow-right-circle"></i>
            </button>
        </div>

        <div class="demo-note">
            <b>Akaun Demo</b><br>
            <b>Pemohon:</b> pemohon1 &middot; pemohon2 <span style="opacity:0.7">(/ user123)</span><br>
            <b>Pengarah Jab:</b> pengarah_jab &middot; pengarah_kej &middot; pengarah_kesihatan &middot; pengarah_rancang <span style="opacity:0.7">(/ pengarah123)</span><br>
            <b>Pengarah JTIK:</b> pengarah_jtik <span style="opacity:0.7">(/ jtik123)</span><br>
            <b>Admin IT:</b> admin_it (1-9) &middot; admin_it2 (10-18) &middot; admin_it3 (19-27) <span style="opacity:0.7">(/ it123)</span><br>
            <span style="opacity:0.75">&copy; <?= date('Y') ?> Majlis Bandaraya Seberang Perai</span>
        </div>
    </div>
</div>

<!-- MODAL: Semakan Kakitangan Baru -->
<div class="modal-overlay" id="semakModal">
    <div class="modal-box">
        <div class="m-icon"><i class="bi bi-person-badge"></i></div>
        <h3>Semak Pendaftaran Kakitangan</h3>
        <div class="m-sub">Untuk kakitangan baru — semak sama ada data anda telah didaftarkan dalam <b>Sistem Gaji</b> sebelum memohon capaian sistem.</div>
        <label for="semakNo">No. Kakitangan</label>
        <div class="input-pill">
            <i class="bi bi-hash"></i>
            <input type="text" id="semakNo" placeholder="Cth: MB001234" autocomplete="off"
                   onkeydown="if(event.key==='Enter'){doSemak();return false;}">
        </div>
        <div class="semak-result" id="semakResult"></div>
        <div class="m-actions">
            <button type="button" class="btn-close-m" onclick="closeSemak()">Tutup</button>
            <button type="button" class="btn-semak" id="btnSemak" onclick="doSemak()">Semak <i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<script>
function openSemak(){ document.getElementById('semakModal').classList.add('show'); setTimeout(function(){document.getElementById('semakNo').focus();},50); }
function closeSemak(){
    document.getElementById('semakModal').classList.remove('show');
    var r=document.getElementById('semakResult'); r.className='semak-result'; r.innerHTML='';
    document.getElementById('semakNo').value='';
}
document.getElementById('semakModal').addEventListener('click',function(e){ if(e.target===this) closeSemak(); });

function escapeHtml(s){ return (s||'').replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }

function doSemak(){
    var no  = document.getElementById('semakNo').value.trim();
    var r   = document.getElementById('semakResult');
    var btn = document.getElementById('btnSemak');
    if(!no){ r.className='semak-result no show'; r.innerHTML='&#9888;&#65039; Sila masukkan No. Kakitangan.'; return; }
    btn.disabled=true; btn.style.opacity=0.7;
    var fd=new FormData(); fd.append('no_kakitangan', no);
    fetch('semak_kakitangan.php',{method:'POST',body:fd})
      .then(function(res){return res.json();})
      .then(function(d){
        btn.disabled=false; btn.style.opacity=1;
        if(!d.ok){ r.className='semak-result no show'; r.innerHTML='&#9888;&#65039; '+(d.msg||'Ralat.'); return; }
        if(d.registered){
            r.className='semak-result ok show';
            r.innerHTML='&#9989; <b>Data anda telah didaftarkan dalam Sistem Gaji.</b><br>Nama: '+escapeHtml(d.nama)+'<br>Jabatan: '+escapeHtml(d.jabatan)+'<br>Anda boleh meneruskan permohonan capaian sistem.';
        } else {
            r.className='semak-result no show';
            r.innerHTML='&#9888;&#65039; <b>Data belum dimasukkan dalam Sistem Gaji.</b><br>Sila maklum <b>Unit Gaji</b> untuk mendaftar data anda terlebih dahulu sebelum boleh mohon capaian sistem.';
        }
      })
      .catch(function(){ btn.disabled=false; btn.style.opacity=1; r.className='semak-result no show'; r.innerHTML='&#9888;&#65039; Ralat sambungan. Sila cuba lagi.'; });
}

function togglePw(){
    var pw = document.getElementById('password');
    var ic = document.getElementById('togglePw');
    if (pw.type === 'password'){ pw.type='text'; ic.className='bi bi-eye toggle-eye'; }
    else { pw.type='password'; ic.className='bi bi-eye-slash toggle-eye'; }
}
</script>
</body>
</html>
