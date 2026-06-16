<?php
require_once __DIR__ . '/auth.php';
requireRole('pemohon');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: borang_permohonan.php'); exit; }

$nama          = trim($_POST['nama']          ?? '');
$no_kakitangan = trim($_POST['no_kakitangan'] ?? '');
$jawatan       = trim($_POST['jawatan']       ?? '');
$gred_jawatan  = trim($_POST['gred_jawatan']  ?? '');
$jabatan       = trim($_POST['jabatan']       ?? '');
$telefon       = trim($_POST['telefon']       ?? '');
$tujuan        = trim($_POST['tujuan']        ?? '');

if (!$nama || !$no_kakitangan || !$jawatan || !$gred_jawatan || !$jabatan || !$telefon || !$tujuan) {
    header('Location: borang_permohonan.php'); exit;
}

$db = getDB();

$no_rujukan = 'BCS-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

$stmt = $db->prepare("
    INSERT INTO permohonan (no_rujukan,user_id,nama,no_kakitangan,jawatan,gred_jawatan,jabatan,telefon,tujuan)
    VALUES (?,?,?,?,?,?,?,?,?)
");
$stmt->execute([$no_rujukan, $_SESSION['user_id'], $nama, $no_kakitangan, $jawatan, $gred_jawatan, $jabatan, $telefon, $tujuan]);
$pid = $db->lastInsertId();

// Insert sistem
$sistemList = $_POST['sistem'] ?? [];
foreach (SENARAI_SISTEM as $bil => $namaSistem) {
    if (in_array((string)$bil, $sistemList)) {
        $catatan       = trim($_POST["catatan_{$bil}"]       ?? '');
        $perananSistem = trim($_POST["peranan_sistem_{$bil}"] ?? '');
        $hk = $_POST["had_kuasa_{$bil}"] ?? [];
        $hkKods = [];
        foreach (SENARAI_FUNGSI as $f) if (isset($hk[$f])) $hkKods[] = $f;
        $s = $db->prepare("INSERT INTO permohonan_sistem (permohonan_id,bil,nama_sistem,catatan,peranan_sistem,had_kuasa) VALUES (?,?,?,?,?,?)");
        $s->execute([$pid,$bil,$namaSistem,$catatan,$perananSistem,json_encode(array_values($hkKods))]);
    }
}

header('Location: dashboard_pemohon.php?success=1'); exit;
