<?php
require_once __DIR__ . '/auth.php';
requireRole('pemohon');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: borang_permohonan.php'); exit; }

$tujuan = trim($_POST['tujuan'] ?? '');

$db = getDB();

// Sumber rasmi: ambil profil kakitangan dari Sistem Gaji (ikut No. Kakitangan pengguna)
$ustmt = $db->prepare("SELECT no_kakitangan FROM users WHERE id = ?");
$ustmt->execute([$_SESSION['user_id']]);
$urow  = $ustmt->fetch();
$noKak = $urow['no_kakitangan'] ?? '';

$gstmt = $db->prepare("SELECT * FROM gaji WHERE no_kakitangan = ?");
$gstmt->execute([$noKak]);
$g = $gstmt->fetch();

// Sekat jika data tiada dalam Sistem Gaji
if (!$g)      { header('Location: borang_permohonan.php?error=gaji'); exit; }
if (!$tujuan) { header('Location: borang_permohonan.php'); exit; }

// Guna data rasmi dari Sistem Gaji (abai input yang dihantar)
$nama          = $g['nama'];
$no_kakitangan = $g['no_kakitangan'];
$jawatan       = $g['jawatan'];
$gred_jawatan  = $g['gred_jawatan'];
$jabatan       = $g['jabatan'];
$telefon       = $g['telefon'];

$no_rujukan = 'BCS-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

$stmt = $db->prepare("
    INSERT INTO permohonan (no_rujukan,user_id,nama,no_kakitangan,jawatan,gred_jawatan,jabatan,telefon,tujuan)
    VALUES (?,?,?,?,?,?,?,?,?)
");
$stmt->execute([$no_rujukan, $_SESSION['user_id'], $nama, $no_kakitangan, $jawatan, $gred_jawatan, $jabatan, $telefon, $tujuan]);
$pid = $db->lastInsertId();

// Insert sistem
$sistemList = $_POST['sistem'] ?? [];
foreach (getSenaraiSistem(true) as $bil => $namaSistem) {
    if (in_array((string)$bil, $sistemList)) {
        $catatan       = trim($_POST["catatan_{$bil}"]       ?? '');
        $perananSistem = trim($_POST["peranan_sistem_{$bil}"] ?? '');
        $hk = $_POST["had_kuasa_{$bil}"] ?? [];
        $hkVals = [];
        foreach (SENARAI_FUNGSI as $f) $hkVals[$f] = isset($hk[$f]) ? 1 : 0;
        $s = $db->prepare("INSERT INTO permohonan_sistem (permohonan_id,bil,nama_sistem,catatan,peranan_sistem,penyedia,pengemaskini,penyemak,pelapor,pengesah,pelulus,penghapus) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $s->execute([$pid,$bil,$namaSistem,$catatan,$perananSistem,$hkVals['penyedia'],$hkVals['pengemaskini'],$hkVals['penyemak'],$hkVals['pelapor'],$hkVals['pengesah'],$hkVals['pelulus'],$hkVals['penghapus']]);
    }
}

logAudit($pid, 'PERMOHONAN_BARU', 'Permohonan dihantar (' . $tujuan . ')');
header('Location: dashboard_pemohon.php?success=1'); exit;
