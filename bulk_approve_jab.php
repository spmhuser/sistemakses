<?php
require_once __DIR__ . '/auth.php';
requireRole('pengarah_jab');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard_pengarah_jab.php'); exit; }
$ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
if (empty($ids)) { header('Location: dashboard_pengarah_jab.php'); exit; }
$db   = getDB();
$nama = $_SESSION['nama'] ?? $_SESSION['username'];
foreach ($ids as $id) {
    $db->prepare("UPDATE permohonan SET status='MENUNGGU_JTIK', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=? AND status='MENUNGGU_PENGARAH_JAB'")
       ->execute([$_SESSION['user_id'], $nama, $id]);
}
header('Location: dashboard_pengarah_jab.php?success=1'); exit;
