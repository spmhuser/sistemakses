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

// Guard: tapis sistem yang permohonannya masih dalam proses (elak hantaran berganda)
$inProc = [];
$ipStmt = $db->prepare("
    SELECT DISTINCT ps.bil
    FROM permohonan_sistem ps
    JOIN permohonan p ON p.id = ps.permohonan_id
    WHERE p.user_id = ?
      AND p.status IN ('MENUNGGU_PENGARAH_JAB','MENUNGGU_JTIK','DILULUSKAN')
");
$ipStmt->execute([$_SESSION['user_id']]);
foreach ($ipStmt->fetchAll() as $r) { $inProc[(int)$r['bil']] = true; }
$_POST['sistem'] = array_values(array_filter($_POST['sistem'] ?? [], fn($b) => !isset($inProc[(int)$b])));
if (empty($_POST['sistem'])) { header('Location: borang_permohonan.php?error=inproc'); exit; }

// Guna data rasmi dari Sistem Gaji (abai input yang dihantar)
$nama          = $g['nama'];
$no_kakitangan = $g['no_kakitangan'];
$jawatan       = $g['jawatan'];
$gred_jawatan  = $g['gred_jawatan'];
$jabatan       = $g['jabatan'];
$telefon       = $g['telefon'];

$no_rujukan = 'BCS-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

$namaMap    = getSenaraiSistem(true);       // id_sistem => nama (sekali sahaja)
$sistemList = $_POST['sistem'];

try {
    $db->beginTransaction();

    // 1) Rekod permohonan induk
    $stmt = $db->prepare("
        INSERT INTO permohonan (no_rujukan,user_id,nama,no_kakitangan,jawatan,gred_jawatan,jabatan,telefon,tujuan)
        VALUES (?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([$no_rujukan, $_SESSION['user_id'], $nama, $no_kakitangan, $jawatan, $gred_jawatan, $jabatan, $telefon, $tujuan]);
    $pid = $db->lastInsertId();

    // 2) Rekod sistem — statement disediakan SEKALI, dilaksana berulang dalam transaksi
    $s = $db->prepare("INSERT INTO permohonan_sistem (permohonan_id,bil,nama_sistem,catatan,peranan_sistem,penyedia,pengemaskini,penyemak,pelapor,pengesah,pelulus,penghapus) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sistemList as $bilRaw) {
        $bil = (int)$bilRaw;
        if (!isset($namaMap[$bil])) continue;   // langkau sistem tidak sah/tidak aktif
        $hk = $_POST["had_kuasa_{$bil}"] ?? [];
        $s->execute([
            $pid, $bil, $namaMap[$bil],
            trim($_POST["catatan_{$bil}"] ?? ''),
            trim($_POST["peranan_sistem_{$bil}"] ?? ''),
            isset($hk['penyedia'])?1:0, isset($hk['pengemaskini'])?1:0, isset($hk['penyemak'])?1:0,
            isset($hk['pelapor'])?1:0, isset($hk['pengesah'])?1:0, isset($hk['pelulus'])?1:0, isset($hk['penghapus'])?1:0,
        ]);
    }

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    header('Location: borang_permohonan.php?error=simpan'); exit;
}

logAudit($pid, 'PERMOHONAN_BARU', 'Permohonan dihantar (' . $tujuan . ')');
header('Location: dashboard_pemohon.php?success=1'); exit;
