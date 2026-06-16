<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS permohonan_sistem");
    $pdo->exec("DROP TABLE IF EXISTS permohonan");
    $pdo->exec("DROP TABLE IF EXISTS users");

    $pdo->exec("
        CREATE TABLE users (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) UNIQUE NOT NULL,
            password      VARCHAR(255) NOT NULL,
            role          VARCHAR(50) NOT NULL,
            nama          VARCHAR(255),
            no_kakitangan VARCHAR(50),
            jawatan       VARCHAR(255),
            gred_jawatan  VARCHAR(50),
            jabatan       VARCHAR(255),
            telefon       VARCHAR(50),
            created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE permohonan (
            id                    INT AUTO_INCREMENT PRIMARY KEY,
            no_rujukan            VARCHAR(50) UNIQUE,
            user_id               INT NOT NULL,
            nama                  VARCHAR(255) NOT NULL,
            no_kakitangan         VARCHAR(50) NOT NULL,
            jawatan               VARCHAR(255) NOT NULL,
            gred_jawatan          VARCHAR(50) NOT NULL,
            jabatan               VARCHAR(255) NOT NULL,
            telefon               VARCHAR(50) NOT NULL,
            tujuan                VARCHAR(50) NOT NULL,
            status                VARCHAR(50) NOT NULL DEFAULT 'MENUNGGU_PENGARAH_JAB',
            tarikh_pemohon        DATETIME DEFAULT CURRENT_TIMESTAMP,
            pengarah_jab_id       INT,
            nama_pengarah_jab     VARCHAR(255),
            tarikh_pengarah_jab   DATETIME,
            pengarah_jtik_id      INT,
            kelulusan_jtik        VARCHAR(50),
            alasan_jtik           TEXT,
            tarikh_jtik           DATETIME,
            it_pemberi_nama       VARCHAR(255),
            it_pemberi_cop        VARCHAR(255),
            it_penyemak_nama      VARCHAR(255),
            it_penyemak_cop       VARCHAR(255),
            tarikh_it             DATETIME,
            created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE permohonan_sistem (
            id             INT AUTO_INCREMENT PRIMARY KEY,
            permohonan_id  INT NOT NULL,
            bil            INT NOT NULL,
            nama_sistem    VARCHAR(255) NOT NULL,
            catatan        TEXT,
            peranan_sistem VARCHAR(50) DEFAULT '',
            penyedia       TINYINT(1) DEFAULT 0,
            pengemaskini   TINYINT(1) DEFAULT 0,
            penyemak       TINYINT(1) DEFAULT 0,
            pelapor        TINYINT(1) DEFAULT 0,
            pengesah       TINYINT(1) DEFAULT 0,
            pelulus        TINYINT(1) DEFAULT 0,
            penghapus      TINYINT(1) DEFAULT 0,
            FOREIGN KEY (permohonan_id) REFERENCES permohonan(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $pw = password_hash('admin', PASSWORD_DEFAULT);
    $users = [
        ['pemohon1',       $pw, 'pemohon',          'Ahmad Fadzil bin Ismail',           'MB001234', 'Pegawai Tadbir',              'N41',    'Jabatan Perbendaharaan',  '04-5399000'],
        ['pemohon2',       $pw, 'pemohon',          'Siti Norzahra binti Rashid',        'MB001235', 'Pembantu Tadbir',             'N19',    'Jabatan Kejuruteraan',    '04-5399001'],
        ['pengarah_jab',   $pw, 'pengarah_jab',     'Mohd Azhar bin Abdul Karim',        'MB000100', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Perbendaharaan',  '04-5399010'],
        ['pengarah_jtik',  $pw, 'pengarah_jtik',    'Abdul Fikri Ridzauudin b Abdullah', 'MB000050', 'Pengarah JTIK',               'JUSA C', 'JTIK',                   '04-5399020'],
        ['admin_it',       $pw, 'admin_it',         'Razif bin Hamdan',                  'MB000200', 'Pegawai Teknologi Maklumat',  'F41',    'JTIK',                   '04-5399030'],
        ['admin',          $pw, 'admin',            'Administrator Sistem',              'MB000001', 'Administrator',               'F44',    'JTIK',                   '04-5399000'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username,password,role,nama,no_kakitangan,jawatan,gred_jawatan,jabatan,telefon) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    $ok = true;
    $msg = 'Database MySQL berjaya disediakan.';
} catch (Throwable $e) {
    $ok = false;
    $msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Setup Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/_includes.php'; sharedCSS(); ?>
    <style>
        body { background: #0b1120 !important; }
        .setup-card {
            border: 1px solid rgba(255,255,255,0.06); border-radius: 24px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
            animation: iosFadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
            background: #151d2e;
        }
        .setup-header {
            background: linear-gradient(180deg, #1e4976, #0d2137);
            color: #fff; padding: 20px 24px; font-weight: 700; letter-spacing: -0.02em;
        }
        .setup-header.fail { background: linear-gradient(180deg, #ff3b30, #d70015); }
        .setup-btn {
            display: block; text-align: center; width: 100%; padding: 15px;
            background: linear-gradient(180deg, #2563eb, #1e4976); color: #fff;
            border: none; border-radius: 14px; font-weight: 600; text-decoration: none; margin-top: 16px;
            box-shadow: 0 4px 16px rgba(30,73,118,0.3);
            transition: transform 0.35s cubic-bezier(0.34,1.25,0.64,1), box-shadow 0.35s;
        }
        .setup-btn:hover { color: #fff; transform: translateY(-2px) scale(1.02); box-shadow: 0 8px 28px rgba(30,73,118,0.35); }
        .setup-btn:active { transform: scale(0.97); }
        .table { border-radius: 12px; overflow: hidden; }
        .table thead { background: rgba(242,242,247,0.9); color: #86868b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .table tbody td { font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width:600px">
    <div class="setup-card shadow">
        <div class="setup-header <?= $ok ? '' : 'fail' ?>">
            <h5 class="mb-0"><?= $ok ? 'Database Berjaya Dibina' : 'Setup Gagal' ?></h5>
        </div>
        <div class="card-body p-4" style="background:#151d2e;color:#e2e8f0">
            <p class="mb-3"><?= htmlspecialchars($msg) ?></p>
            <?php if ($ok): ?>
            <p class="mb-3 fw-semibold text-primary-theme">Akaun demo:</p>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Username</th><th>Password</th><th>Role</th></tr></thead>
                <tbody>
                    <tr><td>admin</td><td>admin</td><td>Admin</td></tr>
                    <tr><td>pemohon1</td><td>admin</td><td>Pemohon</td></tr>
                    <tr><td>pemohon2</td><td>admin</td><td>Pemohon</td></tr>
                    <tr><td>pengarah_jab</td><td>admin</td><td>Pengarah Jabatan</td></tr>
                    <tr><td>pengarah_jtik</td><td>admin</td><td>Pengarah JTIK</td></tr>
                    <tr><td>admin_it</td><td>admin</td><td>Admin IT</td></tr>
                </tbody>
            </table>
            <a href="login.php" class="setup-btn">Pergi ke Login</a>
            <?php else: ?>
            <p class="text-muted small mb-0">Pastikan MySQL/MariaDB dalam XAMPP sudah di-start, kemudian refresh halaman ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
