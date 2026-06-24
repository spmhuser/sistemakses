<?php
// Endpoint semakan kakitangan baru dalam Sistem Gaji
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$no = trim($_POST['no_kakitangan'] ?? '');

if ($no === '') {
    echo json_encode(['ok' => false, 'msg' => 'Sila masukkan No. Kakitangan.']);
    exit;
}

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT nama, jabatan FROM gaji WHERE no_kakitangan = ?");
    $stmt->execute([$no]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'ok'         => true,
            'registered' => true,
            'nama'       => $row['nama'],
            'jabatan'    => $row['jabatan'],
        ]);
    } else {
        echo json_encode(['ok' => true, 'registered' => false]);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Ralat sistem semasa semakan. Sila cuba lagi.']);
}
