<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sistemakses');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SENARAI_SISTEM', [
    1  => 'Sistem Kehadiran',
    2  => 'Sistem Cuti',
    3  => 'Sistem Pengurusan OT',
    4  => 'Sistem i-Bil',
    5  => 'Sistem Perolehan Bersepadu (SPB)',
    6  => 'Sistem Perakaunan & Belanjawan Bersepadu',
    7  => 'Sistem i Sweep',
    8  => 'Sistem Bajet Online 2.0',
    9  => 'Sistem Speedbiz',
    10 => 'Sistem eSurat',
    11 => 'Sistem Aduan',
    12 => 'Sistem Harta Tetap',
    13 => 'Sistem i-Lestari',
    14 => 'Sistem Pengurusan Kompaun (i-kompaun)',
    15 => 'Sistem Pengesanan Dokumen',
    16 => 'Sistem e-Best',
    17 => 'Sistem e-Janji',
    18 => 'Sistem e-Audit',
    19 => 'Sistem Sewa Gerai',
    20 => 'Sistem Pengurusan Aset & Stor',
    21 => 'Sistem i-Meeting',
    22 => 'Sistem Arahan Kerja (Kejuruteraan Sahaja)',
    23 => 'Sistem Smart Elits',
    24 => 'Sistem Pengurusan Inbois (Spi)',
    25 => 'Sistem Lesen Anjing (SPMH)',
    26 => 'Sistem Imunisasi',
    27 => 'Sistem Efiz',
]);

define('SENARAI_PERANAN', [
    'pengguna_jab'  => 'PENGGUNA JAB',
    'pic_jabatan'   => 'PIC JABATAN',
    'admin_sistem'  => 'ADMIN SISTEM',
    'ketua_jabatan' => 'KETUA JABATAN',
    'db_sub'        => 'DB/SUB',
]);

define('SENARAI_FUNGSI', [
    'penyedia', 'pengemaskini', 'penyemak', 'pelapor', 'pengesah', 'pelulus', 'penghapus'
]);

// Had kuasa preset per peranan — 1 = boleh, 0 = tidak
define('HAD_KUASA', [
    'pengguna_jab'  => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>0,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>0],
    'pic_jabatan'   => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>0],
    'admin_sistem'  => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>1],
    'ketua_jabatan' => ['penyedia'=>0,'pengemaskini'=>0,'penyemak'=>1,'pelapor'=>1,'pengesah'=>1,'pelulus'=>1,'penghapus'=>0],
    'db_sub'        => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>1],
]);

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

function dbNow() {
    return date('Y-m-d H:i:s');
}

function statusLabel($status) {
    return match($status) {
        'MENUNGGU_PENYEMAK'     => 'Menunggu Penyemak',
        'MENUNGGU_PENGARAH_JAB' => 'Menunggu Pengarah Jabatan',
        'MENUNGGU_JTIK'         => 'Menunggu Kelulusan JTIK',
        'DILULUSKAN'            => 'Diluluskan',
        'TIDAK_DILULUSKAN'      => 'Tidak Diluluskan',
        'AKSES_DIBERIKAN'       => 'Akses Diberikan',
        default                 => $status,
    };
}

function statusClass($status) {
    return match($status) {
        'MENUNGGU_PENYEMAK'     => 'badge-secondary',
        'MENUNGGU_PENGARAH_JAB' => 'badge-warning',
        'MENUNGGU_JTIK'         => 'badge-info',
        'DILULUSKAN'            => 'badge-success',
        'TIDAK_DILULUSKAN'      => 'badge-danger',
        'AKSES_DIBERIKAN'       => 'badge-primary',
        default                 => 'badge-secondary',
    };
}

function fungsiLabel($fungsi) {
    return match($fungsi) {
        'penyedia'     => 'PENYEDIA',
        'pengemaskini' => 'PENGEMAS KINI',
        'penyemak'     => 'PENYEMAK',
        'pelapor'      => 'PELAPOR',
        'pengesah'     => 'PENGESAH',
        'pelulus'      => 'PELULUS',
        'penghapus'    => 'PENGHAPUS',
        default        => strtoupper($fungsi),
    };
}

function tujuanLabel($tujuan) {
    return match($tujuan) {
        'baru'       => 'Permohonan Baru',
        'kemaskini'  => 'Kemaskini Capaian',
        'pembatalan' => 'Pembatalan Capaian',
        default      => $tujuan,
    };
}
