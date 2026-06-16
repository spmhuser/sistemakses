<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
define('DB_PATH', __DIR__ . '/database.db');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

/* ============================================================
 *  NILAI LALAI (fallback + sumber benih untuk init_db.php)
 *  Tetapan sebenar diuruskan oleh admin melalui tetapan.php
 *  dan disimpan dalam jadual: sistem, fungsi, had_kuasa_preset.
 * ============================================================ */
function defaultSistem() {
    return [
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
    ];
}

// Jenis had kuasa: kod => label
function defaultFungsi() {
    return [
        'penyedia'     => 'PENYEDIA',
        'pengemaskini' => 'PENGEMAS KINI',
        'penyemak'     => 'PENYEMAK',
        'pelapor'      => 'PELAPOR',
        'pengesah'     => 'PENGESAH',
        'pelulus'      => 'PELULUS',
        'penghapus'    => 'PENGHAPUS',
    ];
}

// Had kuasa lalai per peranan — 1 = boleh, 0 = tidak
function defaultHadKuasa() {
    return [
        'pengguna_jab'  => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>0,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>0],
        'pic_jabatan'   => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>0],
        'admin_sistem'  => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>1],
        'ketua_jabatan' => ['penyedia'=>0,'pengemaskini'=>0,'penyemak'=>1,'pelapor'=>1,'pengesah'=>1,'pelulus'=>1,'penghapus'=>0],
        'db_sub'        => ['penyedia'=>1,'pengemaskini'=>1,'penyemak'=>1,'pelapor'=>1,'pengesah'=>0,'pelulus'=>0,'penghapus'=>1],
    ];
}

define('SENARAI_PERANAN', [
    'pengguna_jab'  => 'PENGGUNA JAB',
    'pic_jabatan'   => 'PIC JABATAN',
    'admin_sistem'  => 'ADMIN SISTEM',
    'ketua_jabatan' => 'KETUA JABATAN',
    'db_sub'        => 'DB/SUB',
]);

/* ------------------------------------------------------------
 *  Pemuat tetapan dinamik (dari DB, fallback ke nilai lalai)
 * ------------------------------------------------------------ */
function tetapanSistem() {                 // [id => nama] — aktif sahaja
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows = getDB()->query("SELECT id, nama FROM sistem WHERE aktif = 1 ORDER BY urutan, id")->fetchAll();
        if ($rows) {
            $cache = [];
            foreach ($rows as $r) $cache[(int)$r['id']] = $r['nama'];
            return $cache;
        }
    } catch (Throwable $e) { /* jadual belum wujud */ }
    return $cache = defaultSistem();
}

function tetapanFungsiAktif() {            // [kod => label] — aktif sahaja
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows = getDB()->query("SELECT kod, nama FROM fungsi WHERE aktif = 1 ORDER BY urutan, id")->fetchAll();
        if ($rows) {
            $cache = [];
            foreach ($rows as $r) $cache[$r['kod']] = $r['nama'];
            return $cache;
        }
    } catch (Throwable $e) {}
    return $cache = defaultFungsi();
}

function tetapanFungsiSemua() {            // [kod => label] — termasuk yang dinyahaktif (untuk papar rekod lama)
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows = getDB()->query("SELECT kod, nama FROM fungsi ORDER BY urutan, id")->fetchAll();
        if ($rows) {
            $cache = [];
            foreach ($rows as $r) $cache[$r['kod']] = $r['nama'];
            return $cache;
        }
    } catch (Throwable $e) {}
    return $cache = defaultFungsi();
}

function tetapanHadKuasa() {               // [peranan => [kod => 0/1]]
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows = getDB()->query("SELECT role, fungsi_kod, boleh FROM had_kuasa_preset")->fetchAll();
        if ($rows) {
            $cache = [];
            foreach ($rows as $r) $cache[$r['role']][$r['fungsi_kod']] = (int)$r['boleh'];
            return $cache;
        }
    } catch (Throwable $e) {}
    return $cache = defaultHadKuasa();
}

// Pemalar untuk keserasian dengan kod sedia ada
define('SENARAI_SISTEM', tetapanSistem());
define('SENARAI_FUNGSI', array_keys(tetapanFungsiAktif()));
define('HAD_KUASA',      tetapanHadKuasa());

function statusLabel($status) {
    return match($status) {
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
        'MENUNGGU_PENGARAH_JAB' => 'badge-warning',
        'MENUNGGU_JTIK'         => 'badge-info',
        'DILULUSKAN'            => 'badge-success',
        'TIDAK_DILULUSKAN'      => 'badge-danger',
        'AKSES_DIBERIKAN'       => 'badge-primary',
        default                 => 'badge-secondary',
    };
}

function fungsiLabel($fungsi) {
    $map = tetapanFungsiSemua();
    return $map[$fungsi] ?? strtoupper($fungsi);
}

function tujuanLabel($tujuan) {
    return match($tujuan) {
        'baru'       => 'Permohonan Baru',
        'kemaskini'  => 'Kemaskini Capaian',
        'pembatalan' => 'Pembatalan Capaian',
        default      => $tujuan,
    };
}
