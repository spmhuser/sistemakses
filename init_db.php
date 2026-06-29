<?php
require_once __DIR__ . '/config.php';

// Safety guard — must pass ?confirm=RESET to wipe and rebuild
if (($_GET['confirm'] ?? '') !== 'RESET') {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:#2C5488;padding:40px">
        ⚠️ Halaman ini akan PADAM semua data.<br><br>
        Tambah <code>?confirm=RESET</code> pada URL jika anda pasti ingin set semula database.
    </h2>');
}

$db = getDB();

$db->exec("DROP TABLE IF EXISTS permohonan_peranan");
$db->exec("DROP TABLE IF EXISTS permohonan_sistem");
$db->exec("DROP TABLE IF EXISTS permohonan");
$db->exec("DROP TABLE IF EXISTS users");
$db->exec("DROP TABLE IF EXISTS gaji");
$db->exec("DROP TABLE IF EXISTS senarai_sistem");
$db->exec("DROP TABLE IF EXISTS jabatan_pengarah");
$db->exec("DROP TABLE IF EXISTS sistem_admin");
$db->exec("DROP TABLE IF EXISTS penyemak");
$db->exec("DROP TABLE IF EXISTS audit_trail");

$db->exec("
    CREATE TABLE users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        username      TEXT UNIQUE NOT NULL,
        password      TEXT NOT NULL,
        role          TEXT NOT NULL,
        nama          TEXT,
        no_kakitangan TEXT,
        jawatan       TEXT,
        gred_jawatan  TEXT,
        jabatan       TEXT,
        telefon       TEXT,
        created_at    DATETIME DEFAULT (datetime('now','+8 hours'))
    )
");

$db->exec("
    CREATE TABLE permohonan (
        id                    INTEGER PRIMARY KEY AUTOINCREMENT,
        no_rujukan            TEXT UNIQUE,
        user_id               INTEGER NOT NULL,
        nama                  TEXT NOT NULL,
        no_kakitangan         TEXT NOT NULL,
        jawatan               TEXT NOT NULL,
        gred_jawatan          TEXT NOT NULL,
        jabatan               TEXT NOT NULL,
        telefon               TEXT NOT NULL,
        tujuan                TEXT NOT NULL,
        status                TEXT NOT NULL DEFAULT 'MENUNGGU_PENGARAH_JAB',
        tarikh_pemohon        DATETIME DEFAULT (datetime('now','+8 hours')),
        pengarah_jab_id       INTEGER,
        nama_pengarah_jab     TEXT,
        tarikh_pengarah_jab   DATETIME,
        pengarah_jtik_id      INTEGER,
        kelulusan_jtik        TEXT,
        alasan_jtik           TEXT,
        tarikh_jtik           DATETIME,
        it_pemberi_nama       TEXT,
        it_pemberi_cop        TEXT,
        it_penyemak_nama      TEXT,
        it_penyemak_cop       TEXT,
        tarikh_it             DATETIME,
        created_at            DATETIME DEFAULT (datetime('now','+8 hours')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )
");

$db->exec("
    CREATE TABLE permohonan_sistem (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        permohonan_id  INTEGER NOT NULL,
        bil            INTEGER NOT NULL,
        nama_sistem    TEXT NOT NULL,
        catatan        TEXT,
        peranan_sistem TEXT DEFAULT '',
        penyedia       INTEGER DEFAULT 0,
        pengemaskini   INTEGER DEFAULT 0,
        penyemak       INTEGER DEFAULT 0,
        pelapor        INTEGER DEFAULT 0,
        pengesah       INTEGER DEFAULT 0,
        pelulus        INTEGER DEFAULT 0,
        penghapus      INTEGER DEFAULT 0,
        FOREIGN KEY (permohonan_id) REFERENCES permohonan(id)
    )
");

// Sistem Gaji — senarai kakitangan yang telah didaftar (rujukan semakan capaian)
$db->exec("
    CREATE TABLE gaji (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        no_kakitangan TEXT UNIQUE NOT NULL,
        nama          TEXT,
        jawatan       TEXT,
        gred_jawatan  TEXT,
        jabatan       TEXT,
        telefon       TEXT,
        status        TEXT DEFAULT 'AKTIF',
        created_at    DATETIME DEFAULT (datetime('now','+8 hours'))
    )
");

$gaji = [
    ['MB001234','Ahmad Fadzil bin Ismail',           'Pegawai Tadbir',              'N41',   'Jabatan Perbendaharaan','04-5399000'],
    ['MB001235','Siti Norzahra binti Rashid',        'Pembantu Tadbir',             'N19',   'Jabatan Kejuruteraan',  '04-5399001'],
    ['MB000100','Mohd Azhar bin Abdul Karim',        'Pengarah Jabatan',            'JUSA C','Jabatan Perbendaharaan','04-5399010'],
    ['MB000050','Abdul Fikri Ridzauudin b Abdullah', 'Pengarah JTIK',               'JUSA C','JTIK',                  '04-5399020'],
    ['MB000200','Razif bin Hamdan',                  'Pegawai Teknologi Maklumat',  'F41',   'JTIK',                  '04-5399030'],
    ['MB001300','Nurul Huda binti Salleh',           'Penolong Pegawai Tadbir',     'N29',   'Jabatan Kesihatan',     '04-5399040'],
    ['MB001301','Tan Wei Ming',                      'Juruteknik Komputer',         'FT19',  'Jabatan Perancangan',   '04-5399041'],
    ['MB000201','Nurul Izzati binti Karim',          'Pegawai Teknologi Maklumat',  'F41',   'JTIK',                  '04-5399031'],
    ['MB000202','Tan Chee Hong',                     'Pegawai Teknologi Maklumat',  'F41',   'JTIK',                  '04-5399032'],
];
$gstmt = $db->prepare("INSERT OR IGNORE INTO gaji (no_kakitangan,nama,jawatan,gred_jawatan,jabatan,telefon) VALUES (?,?,?,?,?,?)");
foreach ($gaji as $g) $gstmt->execute($g);

// Senarai Sistem (boleh diurus oleh Admin IT melalui tetapan_sistem.php)
$db->exec("
    CREATE TABLE senarai_sistem (
        id_sistem   INTEGER PRIMARY KEY,
        nama_sistem TEXT NOT NULL,
        kod_sistem  TEXT,
        status      INTEGER NOT NULL DEFAULT 1,
        created_at  DATETIME DEFAULT (datetime('now','+8 hours')),
        updated_at  DATETIME
    )
");
$ss = $db->prepare("INSERT OR IGNORE INTO senarai_sistem (id_sistem,nama_sistem,kod_sistem,status) VALUES (?,?,?,1)");
foreach (SENARAI_SISTEM as $idS => $namaS) { $ss->execute([$idS, $namaS, sprintf('SYS%02d', $idS)]); }

// Pengarah Jabatan (boleh diurus oleh Admin IT melalui tetapan_pengarah.php)
$db->exec("
    CREATE TABLE jabatan_pengarah (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        kod_jabatan   TEXT NOT NULL,
        nama_pengarah TEXT,
        no_pekerja    TEXT,
        status        INTEGER NOT NULL DEFAULT 1,
        created_at    DATETIME DEFAULT (datetime('now','+8 hours')),
        updated_at    DATETIME
    )
");
$jp = [
    ['Jabatan Perbendaharaan','Mohd Azhar bin Abdul Karim','MB000100'],
    ['Jabatan Kejuruteraan',  'Ir. Rosnah binti Yusof',    'MB000101'],
    ['Jabatan Kesihatan',     'Dr. Lim Chee Kong',         'MB000102'],
    ['Jabatan Perancangan',   'Hjh Faridah binti Omar',    'MB000103'],
];
$jpstmt = $db->prepare("INSERT INTO jabatan_pengarah (kod_jabatan,nama_pengarah,no_pekerja,status) VALUES (?,?,?,1)");
foreach ($jp as $j) $jpstmt->execute($j);

// Multi Admin IT mengikut sistem (Option A) — seed: Razif (MB000200) bagi semua sistem
$db->exec("
    CREATE TABLE sistem_admin (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        id_sistem   INTEGER NOT NULL,
        no_pekerja  TEXT,
        nama_admin  TEXT,
        status      INTEGER NOT NULL DEFAULT 1,
        created_at  DATETIME DEFAULT (datetime('now','+8 hours')),
        updated_at  DATETIME
    )
");
$sa = $db->prepare("INSERT INTO sistem_admin (id_sistem,no_pekerja,nama_admin,status) VALUES (?,?,?,1)");
$adminMap = [
    'MB000200' => ['Razif bin Hamdan',         range(1, 9)],
    'MB000201' => ['Nurul Izzati binti Karim', range(10, 18)],
    'MB000202' => ['Tan Chee Hong',            range(19, 27)],
];
foreach ($adminMap as $noPek => $info) {
    foreach ($info[1] as $idS) { $sa->execute([$idS, $noPek, $info[0]]); }
}

// Penyemak IT (boleh diurus melalui tetapan_penyemak.php) — auto-tarik ke borang pemberian akses
$db->exec("
    CREATE TABLE penyemak (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        nama        TEXT NOT NULL,
        jawatan     TEXT,
        no_pekerja  TEXT,
        status      INTEGER NOT NULL DEFAULT 1,
        created_at  DATETIME DEFAULT (datetime('now','+8 hours')),
        updated_at  DATETIME
    )
");
$ps = $db->prepare("INSERT INTO penyemak (nama,jawatan,no_pekerja,status) VALUES (?,?,?,1)");
$ps->execute(['Nurul Izzati binti Karim', 'Ketua Unit Teknologi Maklumat', 'MB000201']);

// Audit Trail — rekod setiap tindakan
$db->exec("
    CREATE TABLE audit_trail (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        permohonan_id  INTEGER,
        processed_by   TEXT,
        processed_name TEXT,
        action         TEXT,
        catatan        TEXT,
        processed_at   DATETIME DEFAULT (datetime('now','+8 hours'))
    )
");

$users = [
    ['pemohon1',       password_hash('user123',      PASSWORD_DEFAULT), 'pemohon',          'Ahmad Fadzil bin Ismail',           'MB001234', 'Pegawai Tadbir',              'N41', 'Jabatan Perbendaharaan',  '04-5399000'],
    ['pemohon2',       password_hash('user123',      PASSWORD_DEFAULT), 'pemohon',          'Siti Norzahra binti Rashid',        'MB001235', 'Pembantu Tadbir',             'N19', 'Jabatan Kejuruteraan',    '04-5399001'],
    ['pengarah_jab',       password_hash('pengarah123',  PASSWORD_DEFAULT), 'pengarah_jab',     'Mohd Azhar bin Abdul Karim',        'MB000100', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Perbendaharaan', '04-5399010'],
    ['pengarah_kej',       password_hash('pengarah123',  PASSWORD_DEFAULT), 'pengarah_jab',     'Ir. Rosnah binti Yusof',            'MB000101', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Kejuruteraan',   '04-5399011'],
    ['pengarah_kesihatan', password_hash('pengarah123',  PASSWORD_DEFAULT), 'pengarah_jab',     'Dr. Lim Chee Kong',                 'MB000102', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Kesihatan',      '04-5399012'],
    ['pengarah_rancang',   password_hash('pengarah123',  PASSWORD_DEFAULT), 'pengarah_jab',     'Hjh Faridah binti Omar',            'MB000103', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Perancangan',    '04-5399013'],
    ['pengarah_jtik',  password_hash('jtik123',      PASSWORD_DEFAULT), 'pengarah_jtik',    'Abdul Fikri Ridzauudin b Abdullah', 'MB000050', 'Pengarah JTIK',               'JUSA C', 'JTIK',                  '04-5399020'],
    ['admin_it',       password_hash('it123',        PASSWORD_DEFAULT), 'admin_it',         'Razif bin Hamdan',                  'MB000200', 'Pegawai Teknologi Maklumat',  'F41', 'JTIK',                   '04-5399030'],
    ['admin_it2',      password_hash('it123',        PASSWORD_DEFAULT), 'admin_it',         'Nurul Izzati binti Karim',          'MB000201', 'Pegawai Teknologi Maklumat',  'F41', 'JTIK',                   '04-5399031'],
    ['admin_it3',      password_hash('it123',        PASSWORD_DEFAULT), 'admin_it',         'Tan Chee Hong',                     'MB000202', 'Pegawai Teknologi Maklumat',  'F41', 'JTIK',                   '04-5399032'],
];

$stmt = $db->prepare("INSERT OR IGNORE INTO users (username,password,role,nama,no_kakitangan,jawatan,gred_jawatan,jabatan,telefon) VALUES (?,?,?,?,?,?,?,?,?)");
foreach ($users as $u) $stmt->execute($u);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Setup Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:600px">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white"><h5 class="mb-0">✅ Database Berjaya Dibina</h5></div>
        <div class="card-body">
            <p class="mb-3 fw-semibold">Akaun demo:</p>
            <table class="table table-bordered table-sm">
                <thead class="table-dark"><tr><th>Username</th><th>Password</th><th>Role</th><th>Nama</th></tr></thead>
                <tbody>
                    <tr><td>pemohon1</td><td>user123</td><td><span class="badge bg-primary">Pemohon</span></td><td>Ahmad Fadzil</td></tr>
                    <tr><td>pemohon2</td><td>user123</td><td><span class="badge bg-primary">Pemohon</span></td><td>Siti Norzahra</td></tr>
                    <tr><td>pengarah_jab</td><td>pengarah123</td><td><span class="badge bg-warning text-dark">Pengarah Jabatan</span></td><td>Mohd Azhar</td></tr>
                    <tr><td>pengarah_jtik</td><td>jtik123</td><td><span class="badge bg-info text-dark">Pengarah JTIK</span></td><td>Abdul Fikri</td></tr>
                    <tr><td>admin_it</td><td>it123</td><td><span class="badge bg-secondary">Admin IT</span></td><td>Razif Hamdan</td></tr>
                </tbody>
            </table>
            <a href="login.php" class="btn btn-primary w-100 mt-2">Pergi ke Login →</a>
        </div>
    </div>
</div>
</body>
</html>
