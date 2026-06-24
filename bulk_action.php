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

    foreach ($ids as $id) {
        $p = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='MENUNGGU_PENGARAH_JAB'");
        $p->execute([$id]);
        $row = $p->fetch();
        if (!$row || !in_array($row['jabatan'], $myJab)) continue;

        if ($act === 'lulus') {
            $db->prepare("UPDATE permohonan SET status='MENUNGGU_JTIK', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?")
               ->execute([$_SESSION['user_id'], $_SESSION['nama'] ?? '', $id]);
            logAudit($id, 'PERAKUAN_JABATAN', 'Perakuan pukal');
        } else {
            $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?")
               ->execute([$_SESSION['user_id'], $_SESSION['nama'] ?? '', $id]);
            logAudit($id, 'TOLAK_JABATAN', 'Tolak pukal oleh Pengarah Jabatan');
        }
        $done++;
    }
}
elseif ($role === 'pengarah_jtik') {
    foreach ($ids as $id) {
        $p = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='MENUNGGU_JTIK'");
        $p->execute([$id]);
        $row = $p->fetch();
        if (!$row) continue;

        if ($act === 'lulus') {
            $db->prepare("UPDATE permohonan SET status='DILULUSKAN', kelulusan_jtik='DILULUSKAN', pengarah_jtik_id=?, tarikh_jtik=datetime('now','+8 hours') WHERE id=?")
               ->execute([$_SESSION['user_id'], $id]);
            logAudit($id, 'KELULUSAN_JTIK', 'Kelulusan pukal');
        } else {
            $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', kelulusan_jtik='TIDAK_DILULUSKAN', alasan_jtik=?, pengarah_jtik_id=?, tarikh_jtik=datetime('now','+8 hours') WHERE id=?")
               ->execute(['Tolak pukal oleh Pengarah JTIK', $_SESSION['user_id'], $id]);
            logAudit($id, 'TOLAK_JTIK', 'Tolak pukal');
        }
        $done++;
    }
}
else {
    header("Location: login.php"); exit;
}

header("Location: $back?bulk=$done"); exit;
