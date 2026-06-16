<?php
require_once __DIR__ . '/auth.php';
requireRole('penyemak');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard_penyemak.php'); exit; }
$ids = array_filter(array_map('intval', $_POST['ids'] ?? []));
if (empty($ids)) { header('Location: dashboard_penyemak.php'); exit; }
$db   = getDB();
$nama = $_SESSION['nama'] ?? $_SESSION['username'];
foreach ($ids as $id) {
    $db->prepare("UPDATE permohonan SET status='MENUNGGU_PENGARAH_JAB', penyemak_id=?, nama_penyemak=?, tarikh_penyemak=datetime('now','+8 hours') WHERE id=? AND status='MENUNGGU_PENYEMAK'")
       ->execute([$_SESSION['user_id'], $nama, $id]);
}
header('Location: dashboard_penyemak.php?success=1'); exit;
