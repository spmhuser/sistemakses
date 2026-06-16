<?php
require_once __DIR__ . '/auth.php';
requireRole('pengarah_jtik');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard_pengarah_jtik.php'); exit; }
$ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
if (empty($ids)) { header('Location: dashboard_pengarah_jtik.php'); exit; }
$db   = getDB();
foreach ($ids as $id) {
    $db->prepare("UPDATE permohonan SET status='DILULUSKAN', kelulusan_jtik='LULUS', alasan_jtik='', pengarah_jtik_id=?, tarikh_jtik=datetime('now','+8 hours') WHERE id=? AND status='MENUNGGU_JTIK'")
       ->execute([$_SESSION['user_id'], $id]);
}
header('Location: dashboard_pengarah_jtik.php?success=1'); exit;
