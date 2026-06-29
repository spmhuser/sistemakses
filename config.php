<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
define('DB_PATH', __DIR__ . '/database.db');

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
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

// Senarai sistem dari DB (jadual senarai_sistem). Fallback ke pemalar jika jadual tiada.
// Pulangan: [id_sistem => nama_sistem]
function getSenaraiSistem($activeOnly = true) {
    try {
        $db  = getDB();
        $sql = "SELECT id_sistem, nama_sistem FROM senarai_sistem";
        if ($activeOnly) $sql .= " WHERE status = 1";
        $sql .= " ORDER BY id_sistem";
        $rows = $db->query($sql)->fetchAll();
        if ($rows) {
            $out = [];
            foreach ($rows as $r) $out[(int)$r['id_sistem']] = $r['nama_sistem'];
            return $out;
        }
    } catch (Throwable $e) { /* fallback */ }
    return SENARAI_SISTEM;
}

// Pengarah yang bertanggungjawab bagi sesuatu jabatan (auto-assign workflow)
function getPengarahByJabatan($jabatan) {
    try {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM jabatan_pengarah WHERE kod_jabatan = ? AND status = 1 LIMIT 1");
        $st->execute([$jabatan]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}

// Senarai jabatan yang ditugaskan kepada seseorang pengarah (ikut no pekerja)
function getJabatanForPengarah($no_pekerja) {
    try {
        $db = getDB();
        $st = $db->prepare("SELECT kod_jabatan FROM jabatan_pengarah WHERE no_pekerja = ? AND status = 1");
        $st->execute([$no_pekerja]);
        return array_column($st->fetchAll(), 'kod_jabatan');
    } catch (Throwable $e) { return []; }
}

// Senarai unik nama jabatan (untuk dropdown/datalist)
function getJabatanList() {
    try {
        $db = getDB();
        $rows = $db->query("SELECT DISTINCT jabatan FROM gaji WHERE jabatan IS NOT NULL AND jabatan<>'' ORDER BY jabatan")->fetchAll();
        return array_column($rows, 'jabatan');
    } catch (Throwable $e) { return []; }
}

// Senarai penyemak IT (boleh diurus melalui tetapan_penyemak.php). Pulangan: array baris penuh.
function getPenyemakList($activeOnly = true) {
    try {
        $db  = getDB();
        $sql = "SELECT * FROM penyemak";
        if ($activeOnly) $sql .= " WHERE status = 1";
        $sql .= " ORDER BY nama";
        return $db->query($sql)->fetchAll();
    } catch (Throwable $e) { return []; }
}

// Senarai id_sistem yang ditugaskan kepada seorang Admin IT (Option A — multi admin per sistem)
function getSistemForAdmin($no_pekerja) {
    try {
        $db = getDB();
        $st = $db->prepare("SELECT id_sistem FROM sistem_admin WHERE no_pekerja = ? AND status = 1");
        $st->execute([$no_pekerja]);
        return array_map('intval', array_column($st->fetchAll(), 'id_sistem'));
    } catch (Throwable $e) { return []; }
}

// AUDIT TRAIL — rekod setiap tindakan
function logAudit($permohonan_id, $action, $catatan = '') {
    try {
        $db   = getDB();
        $no   = '';
        $nama = $_SESSION['nama'] ?? ($_SESSION['username'] ?? '');
        if (!empty($_SESSION['user_id'])) {
            $st = $db->prepare("SELECT no_kakitangan, nama FROM users WHERE id = ?");
            $st->execute([$_SESSION['user_id']]);
            if ($u = $st->fetch()) { $no = $u['no_kakitangan']; if (!$nama) $nama = $u['nama']; }
        }
        $ins = $db->prepare("INSERT INTO audit_trail (permohonan_id,processed_by,processed_name,action,catatan) VALUES (?,?,?,?,?)");
        $ins->execute([$permohonan_id, $no, $nama, $action, $catatan]);
    } catch (Throwable $e) { /* ignore */ }
}

function getAuditTrail($permohonan_id) {
    try {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM audit_trail WHERE permohonan_id = ? ORDER BY id ASC");
        $st->execute([$permohonan_id]);
        return $st->fetchAll();
    } catch (Throwable $e) { return []; }
}

function statusLabel($status) {
    return match($status) {
        'MENUNGGU_PENGARAH_JAB' => 'Menunggu Pengarah Jabatan',
        'MENUNGGU_JTIK'         => 'Menunggu Kelulusan Pengarah JTIK',
        'DILULUSKAN'            => 'Menunggu Akses JTIK',
        'TIDAK_DILULUSKAN'      => 'Tidak Diluluskan',
        'AKSES_DIBERIKAN'       => 'Akses Diberikan',
        default                 => $status,
    };
}

function statusClass($status) {
    return match($status) {
        'MENUNGGU_PENGARAH_JAB' => 'badge-warning',
        'MENUNGGU_JTIK'         => 'badge-info',
        'DILULUSKAN'            => 'badge-warning',
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
