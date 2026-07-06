<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$role = $_SESSION['role'] ?? '';
$ids  = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? []))));
$act  = $_POST['bulk_action'] ?? '';
$db   = getDB();

$backMap = ['pengarah_jab' => 'dashboard_pengarah_jab.php', 'pengarah_jtik' => 'dashboard_pengarah_jtik.php'];
$back    = $backMap[$role] ?? 'login.php';

if (!$ids || !in_array($act, ['lulus', 'tolak'])) { header("Location: $back"); exit; }

$done = 0;

if ($role === 'pengarah_jab') {
    $me = $db->prepare("SELECT no_kakitangan FROM users WHERE id=?");
    $me->execute([$_SESSION['user_id']]);
    $noPek = ($me->fetch())['no_kakitangan'] ?? '';
    $myJab = getJabatanForPengarah($noPek);
    if (!$myJab) $myJab = [$_SESSION['jabatan'] ?? ''];

    // Statement disediakan SEKALI, dilaksana berulang dalam satu transaksi
    $sel      = $db->prepare("SELECT jabatan FROM permohonan WHERE id=? AND status='MENUNGGU_PENGARAH_JAB'");
    $updLulus = $db->prepare("UPDATE permohonan SET status='MENUNGGU_JTIK', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?");
    $updTolak = $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', pengarah_jab_id=?, nama_pengarah_jab=?, alasan_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?");

    $db->beginTransaction();
    foreach ($ids as $id) {
        $sel->execute([$id]);
        $row = $sel->fetch();
        if (!$row || !in_array($row['jabatan'], $myJab)) continue;
        if ($act === 'lulus') {
            $updLulus->execute([$_SESSION['user_id'], $_SESSION['nama'] ?? '', $id]);
            logAudit($id, 'PERAKUAN_JABATAN', 'Perakuan pukal');
        } else {
            $updTolak->execute([$_SESSION['user_id'], $_SESSION['nama'] ?? '', 'Tolak pukal oleh Pengarah Jabatan', $id]);
            logAudit($id, 'TOLAK_JABATAN', 'Tolak pukal oleh Pengarah Jabatan');
        }
        $done++;
    }
    $db->commit();
}
elseif ($role === 'pengarah_jtik') {
    $sel      = $db->prepare("SELECT id FROM permohonan WHERE id=? AND status='MENUNGGU_JTIK'");
    $updLulus = $db->prepare("UPDATE permohonan SET status='DILULUSKAN', kelulusan_jtik='DILULUSKAN', pengarah_jtik_id=?, tarikh_jtik=datetime('now','+8 hours') WHERE id=?");
    $updTolak = $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', kelulusan_jtik='TIDAK_DILULUSKAN', alasan_jtik=?, pengarah_jtik_id=?, tarikh_jtik=datetime('now','+8 hours') WHERE id=?");

    $db->beginTransaction();
    foreach ($ids as $id) {
        $sel->execute([$id]);
        if (!$sel->fetch()) continue;
        if ($act === 'lulus') {
            $updLulus->execute([$_SESSION['user_id'], $id]);
            logAudit($id, 'KELULUSAN_JTIK', 'Kelulusan pukal');
        } else {
            $updTolak->execute(['Tolak pukal oleh Pengarah JTIK', $_SESSION['user_id'], $id]);
            logAudit($id, 'TOLAK_JTIK', 'Tolak pukal');
        }
        $done++;
    }
    $db->commit();
}
else {
    header("Location: login.php"); exit;
}

header("Location: $back?bulk=$done"); exit;
