<?php
require_once __DIR__ . '/config.php';

// Safety guard — must pass ?confirm=RESET to wipe and rebuild
if (($_GET['confirm'] ?? '') !== 'RESET') {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:#831843;padding:40px">
        ⚠️ Halaman ini akan PADAM semua data.<br><br>
        Tambah <code>?confirm=RESET</code> pada URL jika anda pasti ingin set semula database.
    </h2>');
}

$db = getDB();

$db->exec("DROP TABLE IF EXISTS permohonan_peranan");
$db->exec("DROP TABLE IF EXISTS permohonan_sistem");
$db->exec("DROP TABLE IF EXISTS permohonan");
$db->exec("DROP TABLE IF EXISTS users");

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
        status                TEXT NOT NULL DEFAULT 'MENUNGGU_PENYEMAK',
        tarikh_pemohon        DATETIME DEFAULT (datetime('now','+8 hours')),
        penyemak_id           INTEGER,
        nama_penyemak         TEXT,
        tarikh_penyemak       DATETIME,
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

$users = [
    ['pemohon1',       password_hash('user123',      PASSWORD_DEFAULT), 'pemohon',          'Ahmad Fadzil bin Ismail',           'MB001234', 'Pegawai Tadbir',              'N41',    'Jabatan Perbendaharaan',  '04-5399000'],
    ['pemohon2',       password_hash('user123',      PASSWORD_DEFAULT), 'pemohon',          'Siti Norzahra binti Rashid',        'MB001235', 'Pembantu Tadbir',             'N19',    'Jabatan Kejuruteraan',    '04-5399001'],
    ['penyemak',       password_hash('semak123',     PASSWORD_DEFAULT), 'penyemak',         'Nurul Hidayah binti Sulaiman',      'MB000300', 'Penolong Pegawai Tadbir',     'N32',    'JTIK',                   '04-5399005'],
    ['pengarah_jab',   password_hash('pengarah123',  PASSWORD_DEFAULT), 'pengarah_jab',     'Mohd Azhar bin Abdul Karim',        'MB000100', 'Pengarah Jabatan',            'JUSA C', 'Jabatan Perbendaharaan',  '04-5399010'],
    ['pengarah_jtik',  password_hash('jtik123',      PASSWORD_DEFAULT), 'pengarah_jtik',    'Abdul Fikri Ridzauudin b Abdullah', 'MB000050', 'Pengarah JTIK',               'JUSA C', 'JTIK',                   '04-5399020'],
    ['admin_it',       password_hash('it123',        PASSWORD_DEFAULT), 'admin_it',         'Razif bin Hamdan',                  'MB000200', 'Pegawai Teknologi Maklumat',  'F41',    'JTIK',                   '04-5399030'],
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
                    <tr><td>penyemak</td><td>semak123</td><td><span class="badge bg-secondary">Penyemak</span></td><td>Nurul Hidayah</td></tr>
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
